<?php
/**
 * Product Index Manager
 *
 * Builds and maintains the wp_nivo_search_index table used by Fuzzy_Search.
 * Each published product is tokenised into individual word tokens, stored with
 * a field label (title, sku, category, tag) and a weight. The index is kept
 * up to date automatically via WordPress post-save and delete hooks.
 *
 * @package NivoSearch
 * @since 2.0.2
 */

namespace NivoSearch;

defined( 'ABSPATH' ) || exit;

/**
 * Product_Indexer Class
 *
 * Usage:
 *   // Create / update table (called from activation hook + Migrator):
 *   Product_Indexer::maybe_create_table();
 *
 *   // Get stats:
 *   $stats = Product_Indexer::get_stats(); // ['indexed', 'total', 'last_rebuilt']
 *
 *   // Trigger a full rebuild via WP-CLI or admin UI:
 *   $indexer = new Product_Indexer();
 *   $indexer->rebuild_all();
 *
 * @since 2.0.2
 */
class Product_Indexer {

	/**
	 * Unprefixed table name.
	 *
	 * @since 2.0.2
	 * @var string
	 */
	const TABLE_NAME = 'nivo_search_index';

	/**
	 * Return the full (prefixed) table name.
	 *
	 * @since 2.0.2
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Create the index table if it does not already exist.
	 *
	 * Uses dbDelta() so it is safe to call on every activation / migration.
	 *
	 * Schema:
	 *   id           – auto-increment primary key
	 *   product_id   – references wp_posts.ID
	 *   token        – individual word/term in lowercase (max 200 chars)
	 *   field        – where the token came from: title | sku | category | tag
	 *   weight       – relevance weight (title=3, sku=3, category=2, tag=1)
	 *
	 * @since 2.0.2
	 * @return void
	 */
	public static function maybe_create_table() {
		global $wpdb;

		$table           = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			product_id bigint(20) unsigned NOT NULL,
			token varchar(200) NOT NULL,
			field varchar(20) NOT NULL DEFAULT 'title',
			weight tinyint unsigned NOT NULL DEFAULT 1,
			PRIMARY KEY  (id),
			UNIQUE KEY unique_token_product_field (token(100),product_id,field),
			KEY idx_token (token(50)),
			KEY idx_product_id (product_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Register WordPress hooks to keep the index current.
	 *
	 * Called from Nivo_Ajax_Search::init_components().
	 *
	 * @since 2.0.2
	 * @return void
	 */
	public function init() {
		add_action( 'save_post_product',                    array( $this, 'on_product_save' ),   10, 1 );
		add_action( 'deleted_post',                         array( $this, 'on_product_delete' ), 10, 1 );
		add_action( 'woocommerce_product_set_stock_status', array( $this, 'on_product_save' ),   10, 1 );

		// WooCommerce CSV importer — fires after each product is created or updated
		// during a bulk import. save_post_product does not fire reliably in that flow.
		add_action( 'woocommerce_product_import_inserted',  array( $this, 'on_import_product' ), 10, 2 );
		add_action( 'woocommerce_product_import_updated',   array( $this, 'on_import_product' ), 10, 2 );

		// Full import finished — also bump the search result cache.
		add_action( 'woocommerce_product_import_inserted', array( 'NivoSearch\Nivo_Ajax_Search', 'invalidate_search_cache' ), 20 );
	}

	/**
	 * Re-index a product inserted or updated via the WooCommerce CSV importer.
	 *
	 * @since 2.0.2
	 * @param WC_Product $product    The imported product object.
	 * @param array      $data       Raw CSV data for the row (unused but part of hook signature).
	 * @return void
	 */
	public function on_import_product( $product, $data ) {
		if ( ! $product instanceof \WC_Product ) {
			return;
		}
		if ( $product->get_status() === 'publish' ) {
			$this->index_product( $product->get_id() );
		} else {
			$this->deindex_product( $product->get_id() );
		}
	}

	/**
	 * Re-index (or de-index) a product when it is saved.
	 *
	 * @since 2.0.2
	 * @param int $post_id WordPress post ID.
	 * @return void
	 */
	public function on_product_save( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'product' ) {
			return;
		}

		if ( $post->post_status === 'publish' ) {
			$this->index_product( $post_id );
		} else {
			$this->deindex_product( $post_id );
		}
	}

	/**
	 * Remove a product from the index when it is deleted.
	 *
	 * @since 2.0.2
	 * @param int $post_id WordPress post ID.
	 * @return void
	 */
	public function on_product_delete( $post_id ) {
		// get_post() still works before deletion for the post_type check.
		$post = get_post( $post_id );
		if ( $post && $post->post_type !== 'product' ) {
			return;
		}
		$this->deindex_product( $post_id );
	}

	/**
	 * Index a single product.
	 *
	 * Removes any existing rows for the product first, then inserts fresh tokens.
	 * Tokens are deduplicated per field before insertion.
	 *
	 * @since 2.0.2
	 * @param int $product_id WooCommerce product ID.
	 * @return void
	 */
	public function index_product( $product_id ) {
		global $wpdb;

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		// Remove stale rows first.
		$this->deindex_product( $product_id );

		$rows = array();

		// Title tokens (weight 3).
		foreach ( $this->tokenize( $product->get_name() ) as $token ) {
			$rows[] = array( 'token' => $token, 'field' => 'title', 'weight' => 3 );
		}

		// SKU tokens (weight 3) — also store the full SKU as a single token.
		$sku = $product->get_sku();
		if ( $sku ) {
			$sku_lower = strtolower( $sku );
			$rows[]    = array( 'token' => $sku_lower, 'field' => 'sku', 'weight' => 3 );
			foreach ( $this->tokenize( $sku ) as $token ) {
				$rows[] = array( 'token' => $token, 'field' => 'sku', 'weight' => 3 );
			}
		}

		// Category tokens (weight 2).
		$cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $cats ) ) {
			foreach ( $cats as $cat_name ) {
				foreach ( $this->tokenize( $cat_name ) as $token ) {
					$rows[] = array( 'token' => $token, 'field' => 'category', 'weight' => 2 );
				}
			}
		}

		// Tag tokens (weight 1).
		$tags = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag_name ) {
				foreach ( $this->tokenize( $tag_name ) as $token ) {
					$rows[] = array( 'token' => $token, 'field' => 'tag', 'weight' => 1 );
				}
			}
		}

		/**
		 * Filter the token rows before they are inserted into the index.
		 *
		 * @since 2.0.2
		 * @param array      $rows       Array of ['token'=>string, 'field'=>string, 'weight'=>int].
		 * @param int        $product_id Product ID.
		 * @param WC_Product $product    Product object.
		 */
		$rows = apply_filters( 'nivo_search_index_tokens', $rows, $product_id, $product );

		// Deduplicate: keep the highest weight row for each token+field pair.
		$seen = array();
		foreach ( $rows as $row ) {
			$key = $row['token'] . '|' . $row['field'];
			if ( ! isset( $seen[ $key ] ) || $row['weight'] > $seen[ $key ]['weight'] ) {
				$seen[ $key ] = $row;
			}
		}

		$table = self::get_table_name();

		foreach ( $seen as $row ) {
			$wpdb->insert(
				$table,
				array(
					'product_id' => (int) $product_id,
					'token'      => $row['token'],
					'field'      => $row['field'],
					'weight'     => (int) $row['weight'],
				),
				array( '%d', '%s', '%s', '%d' )
			);
		}
	}

	/**
	 * Remove all index rows for a product.
	 *
	 * @since 2.0.2
	 * @param int $product_id Product ID.
	 * @return void
	 */
	public function deindex_product( $product_id ) {
		global $wpdb;
		$wpdb->delete(
			self::get_table_name(),
			array( 'product_id' => (int) $product_id ),
			array( '%d' )
		);
	}

	/**
	 * Rebuild the entire index from scratch.
	 *
	 * Truncates the table and re-indexes all published products in batches.
	 * Stores the rebuild timestamp and count in options.
	 *
	 * @since 2.0.2
	 * @param int $batch_size Number of products per batch (default 100).
	 * @return int Number of products indexed.
	 */
	public function rebuild_all( $batch_size = 100 ) {
		global $wpdb;

		// Clear existing index.
		$wpdb->query( 'TRUNCATE TABLE ' . self::get_table_name() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$page  = 1;
		$count = 0;

		do {
			$ids = get_posts( array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $batch_size,
				'paged'          => $page,
				'fields'         => 'ids',
			) );

			foreach ( $ids as $id ) {
				$this->index_product( $id );
				$count++;
			}

			$page++;
		} while ( count( $ids ) === $batch_size );

		update_option( 'nivo_search_index_last_rebuilt', time() );
		update_option( 'nivo_search_index_count', $count );

		return $count;
	}

	/**
	 * Return index statistics for the admin UI.
	 *
	 * @since 2.0.2
	 * @return array {
	 *   @type int $indexed      Number of distinct products in the index.
	 *   @type int $total        Total published products.
	 *   @type int $last_rebuilt Unix timestamp of the last full rebuild (0 = never).
	 * }
	 */
	public static function get_stats() {
		global $wpdb;

		$table = self::get_table_name();

		// Guard: table may not exist yet on a fresh install before activation.
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
		if ( ! $exists ) {
			return array( 'indexed' => 0, 'total' => 0, 'last_rebuilt' => 0 );
		}

		$indexed      = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT product_id) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total        = (int) wp_count_posts( 'product' )->publish;
		$last_rebuilt = (int) get_option( 'nivo_search_index_last_rebuilt', 0 );

		return compact( 'indexed', 'total', 'last_rebuilt' );
	}

	/**
	 * Tokenise a string into lowercase word tokens.
	 *
	 * Strips punctuation, splits on whitespace, removes tokens shorter than
	 * 2 characters, and removes common English stopwords.
	 *
	 * @since 2.0.2
	 * @param string $text Input text.
	 * @return string[] Array of tokens.
	 */
	private function tokenize( $text ) {
		if ( empty( $text ) ) {
			return array();
		}

		// Normalize diacritics (café → cafe) then lowercase.
		$text = Search_Algorithm::normalize_diacritics( $text );
		$text = preg_replace( '/[^\p{L}\p{N}\s]/u', ' ', $text );

		$tokens = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );

		$stopwords = array( 'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at',
			'to', 'for', 'of', 'with', 'by', 'is', 'it', 'as', 'be', 'are' );

		return array_values(
			array_filter(
				$tokens,
				static function ( $token ) use ( $stopwords ) {
					return strlen( $token ) >= 2 && ! in_array( $token, $stopwords, true );
				}
			)
		);
	}
}
