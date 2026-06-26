# NivoSearch — WooCommerce Ajax Search Plugin

**Free WooCommerce Ajax search with live results, typo correction, SKU search, add-to-cart from results, multilingual support, and Google Analytics tracking. Works with every theme and page builder. Zero configuration required.**

[![WordPress](https://img.shields.io/badge/WordPress-5.6%2B-blue)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588a)](https://woocommerce.com)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://php.net)
[![License](https://img.shields.io/badge/License-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-2.0.2-orange)](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)

[Live Demo](https://nivosearch.com/live-demo-woocommerce-product-search/) · [Documentation](https://nivosearch.com/documentation/) · [WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)

---

## What Is NivoSearch?

NivoSearch is a free **WooCommerce search plugin** that replaces the default WooCommerce search bar with a real-time Ajax search. Products appear in a styled dropdown as the customer types — with images, prices, stock status, and an add-to-cart button — without any page reload.

The default WooCommerce search requires a full page reload, has no typo correction, cannot search SKUs, and shows results as plain links with no images or prices. NivoSearch solves every one of those problems and works on any WordPress theme, any page builder, and any WooCommerce store out of the box.

**Also known as:** WooCommerce live search, WooCommerce instant search, WooCommerce autocomplete search, WooCommerce search bar plugin, WooCommerce product search plugin.

---

## Features

### Instant WooCommerce Ajax Search

Products appear in a dropdown on every keystroke with no page reload. Results include the product image, name, price (including sale price), SKU, stock status, and category badge. Matching keywords are highlighted in bold.

### Typo Correction and Fuzzy Search

Two independent correction engines handle misspelled searches:

**Engine 1 — Typo Tolerance:** checks the search term against a built-in dictionary of 300+ common e-commerce misspellings. "bleutooth" finds Bluetooth. "niike" finds Nike. You can add up to 10 custom correction rules for your own brand names and products.

**Engine 2 — Fuzzy Search:** NivoSearch maintains a private index of every word in your product catalog. When the dictionary has no match, the fuzzy engine finds the closest word in that index using Levenshtein distance and returns matching products. This stays fast on large catalogs because it only runs distance calculations on a small candidate set, never on your full product table.

When either engine corrects a search, a **"Did you mean?"** suggestion appears. Customers click it to confirm and re-run the corrected search. Both engines have independent on/off toggles in **NivoSearch > Settings > Search Accuracy**.

### SKU Search and Variation SKU Search

NivoSearch searches both parent product SKUs and individual variation SKUs. Searching "SHIRT-RED-XL" returns the T-Shirt product even when that code belongs to a specific color and size variation. This works automatically whenever SKU search is enabled in a preset — no extra setup required.

### Add to Cart From Search Results

Simple products show an add-to-cart button with an optional quantity selector directly in the search dropdown. The WooCommerce cart updates instantly via Ajax. Variable products link to their product page for option selection.

### Search History

The last 8 searches are saved in the customer's browser (`localStorage`) and shown as suggestions when the search bar is focused. No data is sent to the server.

### Mobile Full-Screen Search Overlay

On screens under 768px, NivoSearch opens a full-screen search overlay instead of a small dropdown. Results are easy to read and tap on any phone.

### Category and Tag Search

Product categories and tags that match the search term appear in a separate section above product results. Customers can jump straight to a full category page.

### Stock Status, Badges, and Rich Results

Each result shows a stock badge (In Stock / Out of Stock / On Backorder), category labels, the current selling price, and the short product description.

### Unlimited Search Presets

Create as many search presets as you need. Each preset has its own search scope, display options, colors, sizing, and behavior settings. A header preset, a shop-page preset, and a sidebar preset can all run independently with different configurations. Each preset generates its own shortcode automatically.

---

## Multilingual WooCommerce Search

NivoSearch works with **WPML**, **Polylang**, and **TranslatePress** for multilingual WooCommerce stores.

On every Ajax search request, NivoSearch detects the current language using:
- **WPML:** reads `ICL_LANGUAGE_CODE` and passes it as the `lang` query argument
- **Polylang:** calls `pll_current_language()` and passes the result as the `lang` query argument
- **Both:** can be overridden with the `nivo_search_language_query_args` filter

Search results are automatically scoped to the active language. Categories and tags are also language-filtered.

**Diacritical normalization** is included: searching "cafe" finds products named "Café" and vice versa. Accented characters are stripped before indexing and searching, so customers find the right products regardless of how they type.

---

## Google Analytics and GTM Search Tracking

NivoSearch automatically tracks every search as a standard search event in:

- **Google Analytics 4** — fires `gtag('event', 'search', { search_term: query })`
- **Universal Analytics** — fires `ga('send', 'event', 'Search', 'WooCommerce Search', query)`
- **Google Tag Manager** — pushes `{ event: 'nivoSearch', search_term: query }` to `window.dataLayer`

Detection is automatic. If `gtag`, `ga`, or `dataLayer` is present on the page, NivoSearch uses whichever is available. All three are checked in priority order.

Tracking is **opt-in** and disabled by default. Enable it in **NivoSearch > Settings > Search Integration**. You can also listen to the `nivoSearch` custom DOM event for your own integrations:

```javascript
document.addEventListener('nivoSearch', (e) => {
    console.log('Search term:', e.detail.query);
});
```

---

## Theme Compatibility

NivoSearch works with every WooCommerce-compatible theme. It has been tested with:

**Popular classic themes:**
Astra, OceanWP, Kadence, GeneratePress, Blocksy, Flatsome, Storefront, WoodMart, Hello Elementor, Divi, Avada, BeTheme, Neve, Hestia, Porto, XStore, Electro, Woodmart

**Block and Full Site Editing (FSE) themes:**
Twenty Twenty-Four, Twenty Twenty-Five, and all FSE block themes. NivoSearch automatically replaces the native `core/search` block with a NivoSearch bar using the `render_block_core/search` filter.

### Auto-Replace Theme Search Form

Go to **NivoSearch > Settings > Search Integration** and enable **Replace theme search form**. NivoSearch hooks into `get_search_form` and replaces every instance of your theme's search form with a NivoSearch bar — in the header, sidebar, footer, and anywhere else it appears. Choose which preset to use from the same settings screen.

This works without shortcodes, without child themes, and without editing template files.

---

## Page Builder Compatibility

NivoSearch works with every major WordPress page builder:

| Page Builder | How to use NivoSearch |
|---|---|
| **Gutenberg (Block Editor)** | Search for "NivoSearch" in the block inserter and drop it in. Choose a preset from the block sidebar. |
| **Elementor** | Add an HTML widget and paste the shortcode `[nivo_search id="X"]`. Or use the auto-replace setting to replace the Elementor search widget automatically. |
| **Divi** | Add a Code module and paste the shortcode. |
| **WPBakery** | Add a Raw HTML element and paste the shortcode. |
| **Beaver Builder** | Add an HTML module and paste the shortcode. |
| **Bricks Builder** | Add a Code element and paste the shortcode. |
| **Oxygen Builder** | Add a Code block and paste the shortcode. |

The shortcode for each preset is shown in **NivoSearch > Search Presets** in the admin list view. Click to copy.

---

## Plugin Compatibility

| Category | Compatible plugins |
|---|---|
| Multilingual | WPML, Polylang, TranslatePress |
| Page builders | Gutenberg, Elementor, Divi, WPBakery, Beaver Builder, Bricks, Oxygen |
| Caching | WP Rocket, W3 Total Cache, LiteSpeed Cache, WP Super Cache, Autoptimize |
| Analytics | Google Analytics 4, Universal Analytics, Google Tag Manager |
| WooCommerce | HPOS (High-Performance Order Storage), variable products, grouped products, external products, WooCommerce Subscriptions |

---

## How to Install

**From WordPress admin (recommended):**

1. Go to Plugins, then Add New
2. Search for "NivoSearch"
3. Click Install Now, then Activate
4. Go to **NivoSearch > Settings > Search Integration**, enable **Replace theme search form**, and save. Done.

**Upload manually:**

1. Download the ZIP from [WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/)
2. Go to Plugins, then Add New, then Upload Plugin
3. Upload the ZIP and activate

---

## Settings Overview

### Search Presets

Go to **NivoSearch > Search Presets > Add New** to create a preset. Each preset controls:

- **General:** result limit (1–50), minimum characters to start searching (1–5), placeholder text, typing delay in ms
- **Search scope:** title, description, excerpt, SKU, categories, tags, out-of-stock exclusion
- **Display:** image, price, SKU, description, stock badge, category badge, add-to-cart button, quantity selector, view-all link
- **Style:** bar width, bar height, border color, background color, text color, results panel width, results panel background, results panel text color

### Search Accuracy

Go to **NivoSearch > Settings > Search Accuracy** to control:

- **Typo Tolerance** — on/off toggle for the dictionary correction engine
- **Fuzzy Search** — on/off toggle for the index-based Levenshtein engine
- **"Did you mean?"** — show or hide the correction hint in results
- **Search Index** — view index health and trigger a manual rebuild

### Typo Rules

Go to **NivoSearch > Typo Rules** to add custom correction rules for brand names, product-specific terms, or common misspellings in your store. Free tier includes up to 10 custom rules. Rules can be imported and exported as CSV.

### Search Integration

Go to **NivoSearch > Settings > Integrations** to control:

- **Replace theme search form** — auto-replace all theme search forms with a NivoSearch bar
- **Preset for theme form** — choose which preset replaces the theme form
- **Show products on search results page** — products appear on `/search?s=query`
- **Redirect to WooCommerce shop** — pressing Enter redirects to `/shop/?s=query`
- **Google Analytics tracking** — enable/disable automatic search event tracking

---

## Requirements

| | Minimum | Recommended |
|---|---|---|
| WordPress | 5.6 | 6.6+ |
| WooCommerce | 7.0 | 9.0+ |
| PHP | 7.4 | 8.2+ |

---

## For Developers

NivoSearch provides PHP filters, PHP actions, and JavaScript events for full customization.

### PHP Filters

```php
// Modify search arguments before the query runs
add_filter( 'nivo_search_args', function( $args, $query ) {
    $args['limit'] = 15;
    return $args;
}, 10, 2 );

// Modify the full results array before response is sent
add_filter( 'nivo_search_results', function( $results, $query ) {
    return $results;
}, 10, 2 );

// Add custom data to each result item
add_filter( 'nivo_search_result_item', function( $item, $product ) {
    $item['custom_badge'] = get_post_meta( $product->get_id(), '_my_field', true );
    return $item;
}, 10, 2 );

// Add custom typo corrections
add_filter( 'nivo_search_typo_corrections', function( $corrections ) {
    $corrections['airpod'] = 'airpods';
    $corrections['macbok'] = 'macbook';
    return $corrections;
} );

// Override the custom rules limit (Pro pattern)
add_filter( 'nivo_search_max_custom_rules', function() {
    return PHP_INT_MAX;
} );

// Dynamically exclude products from results
add_filter( 'nivo_search_excluded_ids', function( $ids ) {
    $ids[] = 99; // exclude product ID 99
    return $ids;
} );

// Prevent assets loading on specific pages
add_filter( 'nivo_search_should_enqueue_assets', function( $should ) {
    return is_checkout() ? false : $should;
} );

// Modify WPML/Polylang language query args
add_filter( 'nivo_search_language_query_args', function( $args, $lang ) {
    $args['suppress_filters'] = true;
    return $args;
}, 10, 2 );
```

### PHP Actions

```php
// Fires before the search query runs
add_action( 'nivo_search_before_search', function( $query, $args ) {
    // log or modify
}, 10, 2 );

// Fires after results are ranked, before response is sent
add_action( 'nivo_search_after_search', function( $results, $query ) {
    // log or modify
}, 10, 2 );

// Fires after the search index is fully rebuilt
add_action( 'nivo_search_index_rebuilt', function( $stats ) {
    // $stats['total'] = products indexed
} );

// Fires after a preset's meta is saved
add_action( 'nivo_search_preset_saved', function( $post_id, $meta ) {
    // react to preset changes
}, 10, 2 );
```

### JavaScript Events

```javascript
// Fires when results are shown
document.addEventListener('nivo_search:resultsDisplayed', (e) => {
    console.log(e.detail.results); // array of result objects
});

// Fires when a "Did you mean?" suggestion is clicked
document.addEventListener('nivo_search:didYouMeanClicked', (e) => {
    console.log('Corrected query:', e.detail.correctedQuery);
});

// Fires on every search (for custom GA or tracking)
document.addEventListener('nivoSearch', (e) => {
    console.log('Search term:', e.detail.query);
});

// All available events:
// nivo_search:init               — widget initialized
// nivo_search:beforeSearch       — before Ajax request is sent
// nivo_search:resultsDisplayed   — e.detail.results
// nivo_search:noResults          — e.detail.query
// nivo_search:error              — Ajax request failed
// nivo_search:resultsCleared     — dropdown closed/cleared
// nivo_search:focus              — search input focused
// nivo_search:blur               — search input blurred
// nivo_search:didYouMeanClicked  — e.detail.correctedQuery
// nivoSearch                     — fires on every search, e.detail.query
```

---

## Frequently Asked Questions

**Does NivoSearch replace the default WooCommerce search?**
Yes. Enable "Replace theme search form" in Settings and NivoSearch replaces every search form on your site automatically. No shortcode, no template edits needed.

**Does it work with Elementor / Divi / WPBakery?**
Yes. Paste the shortcode `[nivo_search id="X"]` into any HTML or code element in any page builder. Or use the auto-replace setting to replace theme search forms globally.

**Does it search product SKUs?**
Yes. Both parent SKUs and individual variation SKUs are searched. Searching a variation SKU returns the correct parent product.

**Does it work with WPML or Polylang?**
Yes. NivoSearch detects the active language on every search and scopes results to that language automatically. Both WPML and Polylang are supported natively.

**Does it work with my theme?**
Yes. NivoSearch works with all WooCommerce-compatible themes including Astra, OceanWP, Kadence, GeneratePress, Flatsome, Storefront, Divi, Avada, and all FSE block themes.

**Does it handle typos?**
Yes. Two engines: a 300+ word dictionary for common misspellings, and an index-based fuzzy engine for anything not in the dictionary. Both run automatically. You can also add up to 10 custom correction rules.

**Does it track searches in Google Analytics?**
Yes. When enabled in Settings, every search fires a search event to GA4, Universal Analytics, or GTM automatically. Tracking is opt-in and off by default.

**Does it slow down my site?**
No. Assets only load on pages that have a NivoSearch bar. Search results are cached for 5 minutes and invalidated automatically when products change.

**Is it GDPR compliant?**
Yes. No personal data is collected. Search history is stored in the customer's own browser only. GA tracking is opt-in.

**Can I have multiple search bars with different styles?**
Yes. Create unlimited presets. Each has its own settings and shortcode.

---

## Changelog

### 2.0.2
- Variation-level SKU search: searching a variation SKU returns the parent product
- Fuzzy search engine: index-based intelligent typo matching via Levenshtein distance
- Typo correction engine: 300+ built-in e-commerce misspelling corrections
- Search index with automatic maintenance and one-click manual rebuild
- "Did you mean?" clickable correction hint in results and no-results state
- Typo Rules admin page: add, edit, delete, import, and export custom rules (10 free)
- Diacritical normalization: café and cafe return the same results
- WPML and Polylang multilingual support: language-scoped results on every search
- Block and FSE theme support: auto-replaces the core/search block
- Classic theme auto-replace with preset selector
- Search history panel: last 8 searches stored in browser
- Mobile full-screen search overlay for screens under 768px
- Google Analytics 4, Universal Analytics, and GTM automatic search event tracking
- Products shown on the WordPress search results page (/search?s=query)
- Redirect to WooCommerce shop page on Enter
- Performance: all products loaded in one batch query, SKU ranking in one query
- Security: nonce verified on all request paths including wc-ajax, all queries use prepare()

### 1.0.0
- Add-to-cart from search results with instant Ajax mini-cart update
- Quantity selector in results, configurable per preset
- Variable product support with product page link
- Stock status badges (In Stock, Out of Stock, On Backorder)
- Category badges on each result
- Short product description in results
- Sale price display
- SKU shown inline next to product name
- 5-minute transient caching with automatic invalidation on product/preset changes
- "View All Results" sticky link at the bottom of the results panel
- Unlimited search presets with independent configuration
- Gutenberg block with preset selector
- Shortcode `[nivo_search id="X"]`
- Database migration system for safe future updates
- Safe uninstall: all data preserved by default, opt-in deletion available
- Activation race condition fixed for reliable fresh installs

---

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Author

**Nazmun Sakib** · [nazmunsakib.com](https://nazmunsakib.com)

[WordPress.org](https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/) · [Support Forum](https://wordpress.org/support/plugin/nivo-ajax-search-for-woocommerce/) · [GitHub](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)
