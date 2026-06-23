# NivoSearch – Ajax Search for WooCommerce

**Fast, free WooCommerce Ajax search with fuzzy matching, typo tolerance, GTIN lookup, and add-to-cart from results.**

[![WordPress](https://img.shields.io/badge/WordPress-5.6%2B-blue)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588a)](https://woocommerce.com)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://php.net)
[![License](https://img.shields.io/badge/License-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-2.3.0-orange)](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)

[Live Demo](https://nivosearch.com/live-demo-woocommerce-product-search/) · [Documentation](https://nivosearch.com/documentation/) · [WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)

---

## What is NivoSearch?

NivoSearch is a free WooCommerce product search plugin that replaces the default WooCommerce search with a real-time Ajax dropdown. Products appear as the customer types — no page reload, no sluggish database queries.

**The problem it solves:** WooCommerce's default search requires a full-page reload, returns plain links, has zero typo correction, and cannot search by SKU, GTIN, or product attributes. Customers who mistype a product name receive zero results and leave.

**How NivoSearch solves it:**
- Results appear in a styled dropdown within 200 ms
- 300+ built-in typo corrections catch common e-commerce misspellings
- An index-based fuzzy Levenshtein engine catches any typo not in the dictionary
- Searches titles, descriptions, SKUs, GTINs, categories, tags, and product attributes simultaneously
- Shoppers can add products to the cart directly from the search dropdown

---

## Features

### Search Engine

| Feature | Detail |
|---|---|
| Real-time Ajax search | Results on every keystroke, debounced per preset |
| Fuzzy search | Levenshtein distance matching against the search index |
| Typo tolerance | 300+ built-in corrections + up to 10 custom rules (free) |
| "Did you mean?" | Clickable correction hint displayed after auto-correction |
| SKU search | Exact and partial SKU matching |
| GTIN / UPC / EAN / ISBN | Reads all five standard GTIN meta fields |
| Attribute search | Searches all registered `pa_*` WooCommerce taxonomies |
| Category and tag search | Dedicated result sections above product results |
| Diacritical normalization | café = cafe, treated as equivalent in all queries |
| Multilingual | WPML and Polylang: language context forwarded on Ajax requests |
| Relevance ranking | Exact title > title starts-with > title contains > SKU > description |
| Exclude out-of-stock | Per-preset toggle |

### Display (per preset, all toggleable)

| Element | Notes |
|---|---|
| Product thumbnail | Lazy-loaded |
| Title | With keyword highlighting |
| Price | Sale price shown automatically |
| SKU | Inline next to title |
| Short description | Truncated excerpt |
| Stock badge | In Stock / Out of Stock / On Backorder |
| Category badges | All matching categories |
| Add-to-cart button | Ajax, with optional quantity selector |
| Variable product | Links to product page for option selection |
| Mini-cart update | WooCommerce fragment refresh after add-to-cart |
| View All Results | Sticky footer link |
| Search history | Last 8 queries, stored in `localStorage` |

### Theme Integration

- **Classic themes** — hooks `get_search_form` filter (Astra, OceanWP, Storefront, Flatsome, Kadence, GeneratePress)
- **Block / FSE themes** — hooks `render_block_core/search` filter (Twenty Twenty-Four, Twenty Twenty-Five, any FSE theme)
- **Preset selector** — choose which preset replaces the theme form
- **Mobile overlay** — full-screen overlay on screens narrower than 768 px

### Search Index

NivoSearch maintains a custom `wp_nivo_search_index` database table that tokenises product data and assigns field weights. The fuzzy engine runs a SQL prefix match on this table, then applies Levenshtein only on the small candidate set — never O(n_products).

- Automatic rebuild on product save, delete, or bulk import
- Manual rebuild in Settings > Search Accuracy > Search Index
- Stale warning when index has not been rebuilt in 24+ hours

### Integrations

- Google Analytics 4 — automatic `search` event
- Universal Analytics — automatic `search` event
- Google Tag Manager — pushed to `dataLayer`
- `nivoSearch` CustomEvent dispatched on `document` for custom integrations
- WooCommerce HPOS compatible

### Performance

- **Target response time:** under 200 ms
- **Batch loading:** single `wc_get_products` call per request — no N+1 product queries
- **Bulk SKU lookup:** single SQL query — no per-product queries
- **Caching:** 5-minute transient TTL, auto-invalidated on product and preset changes
- **Selective loading:** JS and CSS enqueued only on pages where a preset shortcode or block is present

---

## Installation

### Via WordPress Admin

1. Go to **Plugins > Add New**
2. Search for **NivoSearch**
3. Click **Install Now**, then **Activate**
4. A default preset is created automatically — paste `[nivo_search]` anywhere to display it

### Manual

1. Download the ZIP from [WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)
2. Go to **Plugins > Add New > Upload Plugin**, upload the ZIP, and activate

---

## Quick Start

### Option A — Shortcode

1. Go to **NivoSearch > Search Presets > Add New**
2. Configure search scope and display options in the Settings tab
3. Customize colors in the Style tab
4. Click **Publish** — the shortcode is generated automatically
5. Paste `[nivo_search id="5"]` into any page, post, widget, or template

### Option B — Replace Theme Search (no shortcode needed)

1. Go to **NivoSearch > Settings**
2. Under **Search Integration**, enable **Replace theme search form**
3. Choose which preset to use from the dropdown
4. Save — NivoSearch automatically replaces your theme's existing search box

### Option C — Gutenberg Block

Insert the **NivoSearch** block in any post or page and choose a preset from the block sidebar.

---

## Preset Configuration

Each preset is stored as a WordPress custom post type (`nivo_search_preset`) with four meta groups:

| Meta key | Settings |
|---|---|
| `_nivo_search_generale` | `limit`, `min_chars`, `placeholder`, `delay` |
| `_nivo_search_query` | `search_in_title`, `search_in_sku`, `search_in_content`, `search_in_excerpt`, `search_product_categories`, `search_product_tags`, `search_in_gtin`, `search_in_attributes`, `exclude_out_of_stock` |
| `_nivo_search_display` | `show_images`, `show_price`, `show_sku`, `show_description`, `show_stock_status`, `show_category_badge`, `show_add_to_cart`, `show_qty_selector`, `show_view_all` |
| `_nivo_search_style` | `bar_width`, `bar_height`, `border_color`, `bg_color`, `text_color`, `results_width`, `results_text_color`, `results_border_color`, `results_bg_color` |

---

## Developer Reference

### PHP Filters

```php
// Modify search query arguments
add_filter( 'nivo_search_args', function( $args ) {
    $args['limit'] = 10;
    return $args;
} );

// Modify the full results array
add_filter( 'nivo_search_results', function( $results, $query ) {
    return $results;
}, 10, 2 );

// Modify an individual result item
add_filter( 'nivo_search_result_item', function( $item, $product ) {
    $item['badge'] = get_post_meta( $product->get_id(), '_my_badge', true );
    return $item;
}, 10, 2 );

// Register custom typo corrections
add_filter( 'nivo_search_typo_corrections', function( $corrections ) {
    $corrections['airpod'] = 'airpods';
    return $corrections;
} );

// Override the custom rules limit (Pro example)
add_filter( 'nivo_search_max_custom_rules', function() {
    return PHP_INT_MAX;
} );

// Modify language query arguments
add_filter( 'nivo_search_language_query_args', function( $args, $lang ) {
    return $args;
}, 10, 2 );

// Dynamically exclude product IDs
add_filter( 'nivo_search_excluded_ids', function( $ids ) {
    $ids[] = 99;
    return $ids;
} );

// Prevent asset loading on specific pages
add_filter( 'nivo_search_should_enqueue_assets', function( $should ) {
    return is_checkout() ? false : $should;
} );
```

### PHP Actions

```php
add_action( 'nivo_search_before_search', function( $query, $args ) {}, 10, 2 );
add_action( 'nivo_search_after_search',  function( $results, $query ) {}, 10, 2 );

// Fires after full index rebuild — receives [ 'indexed' => int, 'total' => int ]
add_action( 'nivo_search_index_rebuilt', function( $stats ) {} );

add_action( 'nivo_search_preset_saved', function( $post_id, $meta ) {}, 10, 2 );
```

### JavaScript Events

```javascript
// All available events
const events = [
    'nivo_search:init',
    'nivo_search:beforeSearch',
    'nivo_search:resultsDisplayed',  // e.detail.results
    'nivo_search:noResults',         // e.detail.query
    'nivo_search:error',
    'nivo_search:resultsCleared',
    'nivo_search:focus',
    'nivo_search:blur',
    'nivo_search:didYouMeanClicked', // e.detail.correctedQuery
];

document.addEventListener( 'nivo_search:resultsDisplayed', ( e ) => {
    console.log( 'Results:', e.detail.results );
} );

document.addEventListener( 'nivo_search:didYouMeanClicked', ( e ) => {
    console.log( 'Correction applied:', e.detail.correctedQuery );
} );
```

---

## Architecture

### File Structure

```
nivo-ajax-search-for-woocommerce/
├── nivo-ajax-search-for-woocommerce.php   # Plugin header, activation/deactivation
├── includes/
│   ├── classes/
│   │   ├── Nivo_Ajax_Search.php           # Main controller, AJAX handler, result formatter
│   │   ├── Search_Algorithm.php           # WP_Query engine, typo correction, fuzzy fallback
│   │   ├── Product_Indexer.php            # Builds and maintains wp_nivo_search_index
│   │   ├── Fuzzy_Search.php               # Index-based Levenshtein search
│   │   ├── Typo_Manager.php               # Dictionary + custom rules CRUD
│   │   ├── Search_Analytics.php           # Correction tracking table
│   │   ├── Search_Preset_CPT.php          # CPT registration, meta boxes, save logic
│   │   ├── Migrator.php                   # Version-tracked DB migrations
│   │   ├── Search_Results_Page.php        # WP search results page integration
│   │   ├── Theme_Integration.php          # Classic + block theme form replacement
│   │   ├── Enqueue.php                    # Script/style enqueueing and localization
│   │   ├── Shortcode.php                  # [nivo_search] shortcode
│   │   ├── Gutenberg_Block.php            # Block registration and frontend render
│   │   └── Helper.php                     # Utilities, preset getter, CSS generator
│   ├── admin/
│   │   ├── Admin_Settings.php             # Settings, Typo Rules, Help tabs
│   │   └── Search_Optimization.php        # Typo Rules management page
│   └── data/
│       └── typo-corrections.php           # 300+ e-commerce misspelling corrections
├── assets/
│   ├── js/
│   │   ├── nivo-search.js                 # Frontend search (vanilla JS, no jQuery)
│   │   ├── admin.js                       # Admin color picker
│   │   └── block-editor.js                # Gutenberg block editor
│   └── css/
│       ├── nivo-search.css                # Frontend styles (CSS custom properties)
│       └── admin.css                      # Admin styles
├── uninstall.php                          # Safe cleanup on plugin deletion
└── composer.json                          # PSR-4 autoload config
```

### Search Flow

```
Customer types query
        │
        ▼
[nivo-search.js] debounced keystroke → Ajax POST to admin-ajax.php
        │
        ▼
[Nivo_Ajax_Search::handle_search()]
  → validate nonce, sanitize input
  → check transient cache (5-min TTL)
        │
        ├─ cache hit  → return cached JSON
        └─ cache miss
                │
                ▼
        [Search_Algorithm::search()]
          Pass 1:  WP_Query (title, description, SKU, categories, tags, attributes, GTIN)
          Pass 1b: Retry with raw/accented query if different from normalized
          Pass 2:  Typo correction retry   [gated: nivo_search_enable_typo_tolerance]
          Pass 3:  Fuzzy Levenshtein fallback [gated: nivo_search_enable_fuzzy_search]
                │
                ▼
        rank_results() → relevance scoring with configurable weights
                │
                ▼
        Format JSON → { products, categories, tags, did_you_mean }
                │
                ▼
[nivo-search.js] renders dropdown
  → product cards (image, title, price, SKU, stock, badges, add-to-cart)
  → category / tag sections
  → "Did you mean?" banner
  → search history panel
```

---

## Database

### `wp_nivo_search_index`

| Column | Type | Description |
|---|---|---|
| `id` | bigint(20) PK | Auto-increment |
| `product_id` | bigint(20) | WooCommerce product ID |
| `token` | varchar(100) | Tokenised word from product data |
| `field` | varchar(20) | Source field: `title`, `sku`, `description`, `attribute` |
| `weight` | tinyint(2) | Relevance weight (title=10, sku=8, description=3) |

Index on `(token, product_id)` for prefix-match fuzzy queries.

### `wp_nivo_search_corrections_log`

| Column | Type | Description |
|---|---|---|
| `id` | bigint(20) PK | Auto-increment |
| `original` | varchar(100) | Original misspelling submitted |
| `corrected` | varchar(100) | Correction that was applied |
| `count` | int(11) | Number of times this correction fired |
| `last_seen` | datetime | Timestamp of last occurrence |

---

## WordPress Options Reference

| Option | Default | Description |
|---|---|---|
| `nivo_search_db_version` | `2.3.0` | Stored DB schema version for migrations |
| `nivo_search_default_preset_created` | int | ID of auto-created default preset |
| `nivo_search_enable_ajax` | `1` | Global Ajax on/off toggle |
| `nivo_search_enable_fuzzy_search` | `1` | Index-based Levenshtein fallback |
| `nivo_search_enable_typo_tolerance` | `1` | Dictionary correction pass |
| `nivo_search_max_typo_distance` | `2` | Levenshtein distance (hardcoded, not shown in UI) |
| `nivo_search_show_did_you_mean` | `1` | Show correction hint in results |
| `nivo_search_auto_replace` | `no` | Auto-replace theme search form |
| `nivo_search_theme_preset_id` | `0` | Preset used for theme form replacement |
| `nivo_search_results_page` | `yes` | Show products on WP search results page |
| `nivo_search_ga_tracking` | `yes` | Fire GA4/GTM search event |
| `nivo_search_delete_data_on_uninstall` | `no` | Delete all data on plugin removal |
| `nivo_search_cache_ver` | int | Cache buster, incremented on product changes |

---

## Compatibility

### Themes

| Type | Themes |
|---|---|
| Block / FSE | Twenty Twenty-Four, Twenty Twenty-Five, and all FSE themes |
| Classic | Astra, OceanWP, Storefront, Flatsome, Hello Elementor, WoodMart, Kadence, GeneratePress, Blocksy |

### Plugins and Services

| Category | Compatible with |
|---|---|
| Multilingual | WPML, Polylang, TranslatePress |
| Page builders | Gutenberg, Elementor, Beaver Builder, WPBakery |
| Caching | WP Rocket, W3 Total Cache, WP Super Cache, LiteSpeed Cache |
| Analytics | Google Analytics 4, Universal Analytics, Google Tag Manager |
| WooCommerce | HPOS, variable products, grouped products, external products |

### Requirements

| | Minimum | Recommended |
|---|---|---|
| WordPress | 5.6 | 6.6+ |
| WooCommerce | 7.0 | 9.0+ |
| PHP | 7.4 | 8.1+ |

---

## Frequently Asked Questions

**How is NivoSearch different from the default WooCommerce search?**
The default WooCommerce search requires a full-page reload, has no typo correction, and cannot search SKUs, GTINs, or attributes. NivoSearch returns results in a dropdown within 200 ms, corrects typos, searches across all product data fields, and lets shoppers add items to the cart without leaving the page.

**Does it work with block themes like Twenty Twenty-Four?**
Yes. NivoSearch hooks into the `render_block_core/search` WordPress filter, which fires after every `core/search` block is rendered. When "Replace theme search form" is enabled, NivoSearch intercepts and replaces the block automatically.

**Does it support fuzzy search?**
Yes. Two independent layers: (1) a dictionary with 300+ e-commerce misspellings, and (2) an index-based Levenshtein fallback for any typo not in the dictionary. Both are toggled independently in Settings > Search Accuracy.

**Does it support GTIN / barcode search?**
Yes. NivoSearch reads all five standard GTIN meta fields. Customers searching by UPC, EAN-13, ISBN, or any GTIN identifier will find the exact matching product.

**Can shoppers add products to the cart from search results?**
Yes. Simple products have an inline add-to-cart button with an optional quantity selector. The WooCommerce mini-cart updates instantly via fragment refresh. Variable products show a link to the product page.

**Is it GDPR compliant?**
Yes. NivoSearch collects no user data and sends nothing to external servers. Search history is stored only in the customer's own browser localStorage. Google Analytics integration is opt-in.

**What happens to data if the plugin is deleted?**
All presets and settings are preserved by default. To remove all data, enable "Delete all data on uninstall" in Settings before deleting the plugin.

---

## Changelog

### 2.3.0
- Fuzzy search engine (index-based Levenshtein)
- Search index (`wp_nivo_search_index`) with automatic maintenance and rebuild UI
- "Did you mean?" suggestion with clickable re-search
- GTIN / UPC / EAN / ISBN search
- Product attribute search (all `pa_*` taxonomies)
- Typo Rules page — custom rules, CSV import/export, 10-rule free limit
- Diacritical normalization (accented characters treated as equivalent)
- WPML and Polylang support — language context forwarded on Ajax
- Block theme support via `render_block_core/search` filter
- Theme integration preset selector
- Search history panel (last 8 queries, `localStorage`)
- Mobile search overlay (screens under 768 px)
- Google Analytics / GTM integration (automatic `search` event)
- Search results page integration
- Redirect to WooCommerce shop page option
- N+1 product loading fixed
- N+1 SKU lookup fixed
- Fuzzy search and typo tolerance are now independent toggles

### 2.2.0
- 300+ built-in e-commerce typo corrections
- Custom typo rules CRUD with bulk CSV import/export
- Correction tracking analytics table

### 2.0.0
- Add-to-cart from search results (Ajax, mini-cart fragment update)
- Quantity selector in results
- Variable product support
- Stock status badges
- Category badges
- Short description in results
- Transient caching (5-min TTL)
- "View All Results" sticky footer link

### 1.2.0
- Database migration system
- Safe uninstall (data preserved by default)
- Activation race condition fixed

### 1.0.0 — 1.1.0
- Initial release, unlimited presets, Gutenberg block

---

## Contributing

Pull requests are welcome. Please follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

```bash
composer install
./vendor/bin/phpcs --standard=WordPress includes/
./vendor/bin/phpcbf --standard=WordPress includes/
```

---

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Author

**Nazmun Sakib** · [nazmunsakib.com](https://nazmunsakib.com)

[WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/) · [Support Forum](https://wordpress.org/support/plugin/nivo-ajax-search-for-woocommerce/) · [GitHub](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)
