# NivoSearch: WooCommerce Ajax Search Plugin

**The fastest free WooCommerce search plugin. Live results as customers type, typo correction, add to cart from results, and zero configuration required.**

[![WordPress](https://img.shields.io/badge/WordPress-5.6%2B-blue)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588a)](https://woocommerce.com)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://php.net)
[![License](https://img.shields.io/badge/License-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-2.0.2-orange)](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)

[Live Demo](https://nivosearch.com/live-demo-woocommerce-product-search/) · [Documentation](https://nivosearch.com/documentation/) · [WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)

---

## Why Your Store Needs a Better WooCommerce Search

WooCommerce's built-in search makes customers wait for a full page reload to see results. It has no typo correction, no SKU search, and no way to add a product to the cart from the search bar. Customers who mistype a product name get zero results and leave.

NivoSearch fixes all of that. It turns your WooCommerce search bar into a live, instant search that shows product images, prices, and an add-to-cart button as the customer types, without any page reload.

---

## What You Get for Free

### Instant Search Results

Products appear in a dropdown within milliseconds of the first keystroke. No page reload, no waiting. Customers see the product image, name, price, and stock status right in the search bar.

### Fuzzy Search and Typo Correction

Two layers of typo protection come built in. First, a dictionary of 300+ common e-commerce misspellings automatically corrects terms like "nikie" to "nike" or "iphnoe" to "iphone". Second, an intelligent fuzzy engine catches any other typo that isn't in the dictionary. If NivoSearch auto-corrects a search, it shows a "Did you mean?" hint so customers know what happened.

You can also add your own correction rules: up to 10 custom rules are included free.

### Add to Cart From the Search Bar

Simple products include an add-to-cart button directly in the search dropdown. Customers can shop without ever leaving the page they are on. The WooCommerce cart updates instantly. For products with size or color options, a link takes customers straight to the product page.

### Search by SKU and Variation SKU

Customers and staff who search by product code will find the exact product. NivoSearch searches parent product SKUs and individual variation SKUs, so searching "TSHIRT-RED-XL" returns the T-Shirt product even if that code belongs to a specific color and size variation.

### Stock Status, Category Badges, and Rich Results

Every search result shows what the customer needs to decide: the product image, price (including sale price), a stock badge (In Stock / Out of Stock / On Backorder), and category labels. Short product descriptions are shown below the title.

### Multiple Search Bars, One Plugin

Create unlimited search presets, each with its own style, search scope, and display settings. Put a compact search bar in the header and a full-featured one on the shop page. Each preset has its own shortcode.

### Search History

The last 8 searches are saved in the customer's browser and shown as suggestions when they click the search bar again. No server data is stored.

### Mobile Search Overlay

On phones and small screens, NivoSearch opens a full-screen search overlay so customers can search comfortably without the regular search bar being too small to use.

### Category and Tag Search

Matching product categories and tags appear in their own section above the product results, so customers can jump straight to a full category if that is what they are looking for.

### Works in Multiple Languages

NivoSearch works with WPML and Polylang. Search results are automatically filtered to the customer's active language. Accented characters like "café" and "cafe" are treated as the same search, so customers find products regardless of how they type.

### Google Analytics Integration

Every search is automatically tracked as a search event in Google Analytics 4, Universal Analytics, or Google Tag Manager. No extra setup is needed.

---

## How to Install

**Option 1: From WordPress admin (easiest)**

1. Go to Plugins, then Add New
2. Search for "NivoSearch"
3. Click Install Now, then Activate
4. A default search bar is created automatically. Paste `[nivo_search]` into any page to display it.

**Option 2: Upload manually**

1. Download the ZIP file from [WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)
2. Go to Plugins, then Add New, then Upload Plugin
3. Upload the ZIP file and activate

---

## Setting Up Your Search Bar

### Create a Search Preset

Go to **NivoSearch > Search Presets > Add New**. Each preset controls:

- What to search: product title, description, SKU, categories, tags
- What to show in results: image, price, SKU, description, stock badge, category badge, add-to-cart button
- How it looks: colors, width, border, fonts, all customizable with a live preview
- Behavior: minimum characters to start searching, result limit, typing delay

When you publish a preset, NivoSearch generates a shortcode automatically. Paste it anywhere: pages, posts, widgets, or template files.

### Replace Your Theme's Search Box Automatically

Go to **NivoSearch > Settings**, open the Integrations tab, and enable "Replace theme search form". NivoSearch will replace your existing WooCommerce search box with the NivoSearch bar automatically, no shortcode needed. This works with all major themes including Astra, OceanWP, Flatsome, Kadence, GeneratePress, and Storefront.

### Use the Gutenberg Block

Search for "NivoSearch" in the block inserter and drop it into any page. Choose a preset from the block settings panel.

---

## Search Accuracy Settings

Go to **NivoSearch > Settings > Search Accuracy** to control:

- **Typo Tolerance:** on by default. Corrects common misspellings using the built-in dictionary.
- **Fuzzy Search:** on by default. Catches any typo not in the dictionary using an intelligent search index.
- **"Did you mean?":** shows customers a clickable correction hint when a typo was auto-corrected.
- **Search Index:** NivoSearch builds and maintains a search index automatically. You can rebuild it manually from this page if needed.

### Custom Typo Rules

Go to **NivoSearch > Typo Rules** to add your own corrections for brand names, product-specific terms, or any misspellings common to your store. Free accounts can store up to 10 custom rules. Rules can be imported and exported as a CSV file.

---

## Theme Compatibility

NivoSearch works with all WooCommerce-compatible themes, including:

- Astra, OceanWP, Kadence, GeneratePress, Blocksy, Flatsome, Storefront, WoodMart, Hello Elementor
- Full Site Editing (FSE) block themes: Twenty Twenty-Four, Twenty Twenty-Five, and all block themes

---

## Plugin Compatibility

| Category | Compatible plugins |
|---|---|
| Multilingual | WPML, Polylang, TranslatePress |
| Page builders | Gutenberg, Elementor, Beaver Builder, WPBakery |
| Caching | WP Rocket, W3 Total Cache, LiteSpeed Cache, WP Super Cache |
| Analytics | Google Analytics 4, Universal Analytics, Google Tag Manager |
| WooCommerce | HPOS, variable products, grouped products, external products |

---

## Requirements

| | Minimum | Recommended |
|---|---|---|
| WordPress | 5.6 | 6.6+ |
| WooCommerce | 7.0 | 9.0+ |
| PHP | 7.4 | 8.1+ |

---

## Frequently Asked Questions

**Does NivoSearch replace the default WooCommerce search?**
Yes, completely. The default WooCommerce search makes customers wait for a page reload, has no typo correction, and cannot search SKUs. NivoSearch replaces it with a live Ajax search bar that shows results in a styled dropdown as customers type.

**Does it work without writing any code?**
Yes. Install, activate, and paste the shortcode. Everything is configured from the WordPress admin. No PHP, no CSS, no code required.

**Does it show product images in search results?**
Yes. Product thumbnails, title, price, stock badge, and category are shown in each result by default. You can turn each element on or off per preset.

**Can customers add products to the cart from search results?**
Yes. Simple products have an add-to-cart button with an optional quantity selector directly in the search dropdown. The WooCommerce cart updates instantly. Variable products link to their product page.

**Does it handle typos?**
Yes. Two layers: a built-in dictionary of 300+ e-commerce misspellings, plus a fuzzy search engine for any typo not in the dictionary. You can also add custom correction rules for your own brand names and products.

**Does it search product SKUs?**
Yes. NivoSearch searches parent product SKUs and individual variation SKUs. If a customer or staff member searches "TSHIRT-RED-XL", NivoSearch finds the T-Shirt product because that is a variation SKU.

**Does it work with WPML or Polylang?**
Yes. The current language is sent with every Ajax search request, and results are filtered to match. Accented characters are normalized so "café" and "cafe" return the same products.

**Is it GDPR compliant?**
Yes. NivoSearch collects no personal data and sends nothing to external servers. Search history is stored only in the customer's own browser. Google Analytics tracking is opt-in and can be disabled in Settings.

**What happens to presets and settings if the plugin is deleted?**
All data is preserved by default. To remove everything on deletion, enable "Delete all data on uninstall" in Settings before removing the plugin.

**Does it work with caching plugins?**
Yes. NivoSearch results are served fresh via Ajax and are not affected by page-level caching. It is compatible with WP Rocket, LiteSpeed Cache, W3 Total Cache, and WP Super Cache.

---

## For Developers

NivoSearch provides PHP filters, PHP actions, and JavaScript events for customization.

### PHP Filters

```php
// Change search arguments
add_filter( 'nivo_search_args', function( $args ) {
    $args['limit'] = 10;
    return $args;
} );

// Modify the results array
add_filter( 'nivo_search_results', function( $results, $query ) {
    return $results;
}, 10, 2 );

// Add custom data to a result item
add_filter( 'nivo_search_result_item', function( $item, $product ) {
    $item['badge'] = get_post_meta( $product->get_id(), '_custom_field', true );
    return $item;
}, 10, 2 );

// Add custom typo corrections
add_filter( 'nivo_search_typo_corrections', function( $corrections ) {
    $corrections['airpod'] = 'airpods';
    return $corrections;
} );

// Exclude products dynamically
add_filter( 'nivo_search_excluded_ids', function( $ids ) {
    $ids[] = 99;
    return $ids;
} );

// Disable assets on specific pages
add_filter( 'nivo_search_should_enqueue_assets', function( $should ) {
    return is_checkout() ? false : $should;
} );
```

### PHP Actions

```php
add_action( 'nivo_search_before_search', function( $query, $args ) {}, 10, 2 );
add_action( 'nivo_search_after_search',  function( $results, $query ) {}, 10, 2 );
add_action( 'nivo_search_index_rebuilt', function( $stats ) {} );
add_action( 'nivo_search_preset_saved',  function( $post_id, $meta ) {}, 10, 2 );
```

### JavaScript Events

```javascript
document.addEventListener( 'nivo_search:resultsDisplayed', ( e ) => {
    console.log( e.detail.results );
} );

document.addEventListener( 'nivo_search:didYouMeanClicked', ( e ) => {
    console.log( 'Correction applied:', e.detail.correctedQuery );
} );

// All available events:
// nivo_search:init
// nivo_search:beforeSearch
// nivo_search:resultsDisplayed  (e.detail.results)
// nivo_search:noResults         (e.detail.query)
// nivo_search:error
// nivo_search:resultsCleared
// nivo_search:focus
// nivo_search:blur
// nivo_search:didYouMeanClicked (e.detail.correctedQuery)
```

---

## Changelog

### 2.0.2
- Variation-level SKU search: searching a variation SKU returns the parent product
- Fuzzy search engine: index-based intelligent typo matching
- Search index with automatic maintenance and manual rebuild option
- "Did you mean?" clickable correction hint
- Typo Rules page: add, edit, import, and export custom correction rules (10 free)
- Diacritical normalization: café and cafe return the same results
- WPML and Polylang support
- Block theme support (Twenty Twenty-Four, Twenty Twenty-Five, all FSE themes)
- Theme search form auto-replacement with preset selector
- Search history panel (last 8 searches, stored in browser)
- Mobile search overlay for phones and small screens
- Google Analytics 4 and GTM automatic search event tracking
- Search results page integration
- Performance: N+1 product loading and SKU lookup both fixed

### 2.0.1
- 300+ built-in e-commerce typo corrections
- Custom typo rules with bulk CSV import and export
- Correction tracking for store analysis

### 1.0.0
- Add-to-cart from search results with Ajax mini-cart update
- Quantity selector in results
- Variable product support
- Stock status badges
- Category badges
- Short product description in results
- 5-minute transient caching with automatic invalidation
- "View All Results" sticky footer link in the dropdown

### 1.2.0
- Database migration system for safe future updates
- Safe uninstall: all data preserved by default
- Activation race condition fixed

### 1.0.0 — 1.1.0
- Initial release: unlimited presets, Gutenberg block, shortcode

---

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Author

**Nazmun Sakib** · [nazmunsakib.com](https://nazmunsakib.com)

[WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/) · [Support Forum](https://wordpress.org/support/plugin/nivo-ajax-search-for-woocommerce/) · [GitHub](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)
