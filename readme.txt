=== NivoSearch – Ajax Search for WooCommerce ===
Contributors: nazmunsakib
Donate link: https://nazmunsakib.com
Tags: woocommerce search, ajax search, product search, live search, woocommerce
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Instant Ajax product search for WooCommerce. Real-time results, fuzzy search, add-to-cart, SKU lookup, and unlimited search presets — free.

== Description ==

**NivoSearch** delivers a fast, lightweight **WooCommerce Ajax search** experience — instant product results appear as customers type, with no page reload required. Built for speed, conversion, and full customization, NivoSearch is a capable alternative to the slow default WooCommerce search.

Give your customers a smooth **live product search** experience that keeps them engaged and moving toward checkout — without paying for a premium plugin.

[» GitHub](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)

= Why Store Owners Choose NivoSearch =

**⚡ Instant Ajax Search Results** — Products appear in a dropdown as customers type. Real-time **WooCommerce product search** with no page reload means faster discovery and fewer abandoned sessions.

**🔁 Fuzzy Search & Typo Tolerance** — Customers who misspell product names still find what they need. NivoSearch automatically corrects common typos — so "iPhon" still finds "iPhone."

**🛒 Add to Cart from Search Results** — Shoppers can add products directly from the search dropdown without visiting a product page. Fewer clicks, more conversions.

**🔍 SKU Search** — B2B stores and repeat customers find products instantly by SKU. Supports exact and partial SKU matching.

**📂 Category & Tag Search** — Matching product categories and tags appear in dedicated sections, helping customers browse and discover related products.

**⭐ Rich Product Cards** — Each result shows the product image, title, price, SKU, short description, star ratings, and stock status — all configurable per preset.

**🗂️ Unlimited Search Presets** — Create separate search bars for your header, footer, sidebar, or any page. Each preset has its own search scope, display settings, and styling.

**🌍 Multilingual & Global Ready** — Translation-ready with a .pot file included. Compatible with WPML, Polylang, and TranslatePress for multilingual and multi-regional WooCommerce stores.

**🛠️ Developer-Friendly** — 14+ PHP hooks and filters, 8 JavaScript events, PSR-4 autoloading, nonce-secured AJAX endpoints. Clean architecture built to extend.

= WooCommerce Ajax Search Features =

**Search Capabilities**

* Real-time Ajax product search on every keystroke
* Search by product title, description, short description, and SKU
* Fuzzy search with automatic typo correction
* Synonym expansion — "phone" finds "mobile," "smartphone," and "cell phone"
* GTIN, UPC, EAN, and ISBN product identifier search
* Product attribute taxonomy search
* Category and tag search with dedicated result sections
* Exclude out-of-stock products from search results
* Configurable minimum character threshold (1–5 characters)
* Configurable results limit per preset (1–50 products)
* Smart debounce — prevents unnecessary server requests
* Configurable search delay per preset

**Display Options (per preset)**

* Product thumbnail image with lazy loading
* Product title with keyword highlighting
* Price — regular price, sale price, and variable product price ranges
* SKU display
* Short description excerpt
* Star ratings and review count
* Stock status badge — In Stock, Out of Stock, On Backorder
* Category badges on individual product results
* Add-to-cart button with optional quantity selector
* "View All Results" link to full WooCommerce search page
* Separate sections for matching categories and tags

**Unlimited Search Presets**

* Unlimited search presets stored as a native WordPress custom post type
* Independent search scope, display settings, and styling per preset
* Shortcode: `[nivo_search id="123"]`
* Gutenberg block with visual preset selector
* Color picker for search bar and results panel
* Custom CSS class support via shortcode attributes (`container_class`, `input_class`, `results_class`)

**Performance & Reliability**

* Single optimized WP_Query per search request
* Target response time under 200ms
* Database migration system for safe, zero-downtime updates
* Preset data preserved on plugin deletion by default
* WooCommerce HPOS (High-Performance Order Storage) compatible

**Developer Features**

* PSR-4 autoloading via Composer
* 14+ WordPress actions and filters
* 8 JavaScript custom events
* Nonce-secured Ajax endpoints
* Translation-ready — .pot file included
* WPML and Polylang compatible
* Inline PHPDoc documentation throughout

= Coming in Pro =

A Pro extension is in development with advanced capabilities for high-volume WooCommerce stores:

* **Custom index engine** — sub-100ms search on catalogs with 100,000+ products
* **Advanced Custom Fields (ACF)** — search any ACF field value
* **REST API endpoints** — headless WooCommerce and mobile app search
* **Template override system** — full control over result item HTML
* **Grid and compact layouts** — alternative result display modes
* **Search analytics dashboard** — track trending queries and zero-result searches
* **Role-based display** — show different search bars to different user roles
* **Page-based conditional display** — enable or disable by post type or URL

= Compatibility =

* **Themes:** Storefront, Astra, OceanWP, Flatsome, Hello Elementor, WoodMart, Kadence, GeneratePress, and all standard WooCommerce-compatible themes
* **Page builders:** Gutenberg, Elementor, Beaver Builder, WPBakery
* **Caching:** WP Rocket, W3 Total Cache, WP Super Cache, LiteSpeed Cache
* **Multilingual:** WPML, Polylang, TranslatePress — translation-ready (.pot file included)
* **WooCommerce:** HPOS compatible, supports variable products, grouped products, and external products

== Installation ==

= Automatic Installation (Recommended) =

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for **NivoSearch**
3. Click **Install Now**, then **Activate**
4. Go to **NivoSearch → Search Presets → Add New**
5. Configure your preset settings and styling
6. Copy the generated shortcode and paste it anywhere on your site

= Manual Installation =

1. Download the plugin ZIP from WordPress.org
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP file and click **Install Now**
4. Activate the plugin and go to **NivoSearch → Search Presets**

== Quick Start ==

1. Go to **NivoSearch → Search Presets → Add New**
2. Set your results limit, minimum characters, and placeholder text
3. Choose what to search: title, SKU, description, categories, tags
4. Choose what to display: images, price, SKU, descriptions
5. Customize colors and sizing using the built-in style controls
6. Click **Publish** — your shortcode is generated automatically
7. Paste `[nivo_search id="123"]` into any page, post, widget, or template

== Developer Hooks ==

= PHP Filters =

`nivo_search_args` — Modify search query arguments before execution.
`nivo_search_results` — Modify the full results array before the JSON response.
`nivo_search_result_item` — Modify an individual product result item.
`nivo_search_shortcode_html` — Override the complete shortcode HTML output.
`nivo_search_localize_data` — Modify the JavaScript localization data object.
`nivo_search_should_enqueue_assets` — Return false to conditionally skip asset loading.
`nivo_search_typo_corrections` — Register a custom typo correction map (array of misspelling → correction).
`nivo_search_synonyms` — Register synonym groups (array of term → array of synonyms).

= JavaScript Events =

`nivo_search:init` — Search widget initialized.
`nivo_search:beforeSearch` — Fired before an Ajax request is sent.
`nivo_search:resultsDisplayed` — Fired after results are rendered in the dropdown.
`nivo_search:noResults` — Fired when the search returns zero products.
`nivo_search:error` — Fired when an Ajax error occurs.
`nivo_search:resultsCleared` — Fired when the results dropdown is cleared.
`nivo_search:focus` — Fired when the search input receives focus.
`nivo_search:blur` — Fired when the search input loses focus.

== Frequently Asked Questions ==

= Does NivoSearch replace the default WooCommerce search? =

No. NivoSearch adds a separate Ajax search widget deployed via shortcode or Gutenberg block. The default WooCommerce search continues to work normally alongside it.

= How do I add the search bar to my site? =

Create a preset under **NivoSearch → Search Presets → Add New**, then copy its shortcode (e.g. `[nivo_search id="5"]`) and paste it into any page, post, widget area, or theme template file. You can also insert the NivoSearch Gutenberg block directly in the block editor.

= Can I create multiple search bars with different settings? =

Yes — unlimited presets is a core feature. Create as many as you need, each with its own search scope, display options, colors, and sizing. Common use cases: a compact header search, a full-width homepage search bar, and a sidebar widget.

= Does it support fuzzy search and typo correction? =

Yes. NivoSearch includes fuzzy search with automatic typo correction. A customer searching for "iPhon" or "bleutooth headpones" will still get relevant results. The typo correction map is filterable via `nivo_search_typo_corrections` for adding custom terms.

= Can customers add products to the cart directly from search results? =

Yes. NivoSearch includes an add-to-cart button inside the search results dropdown. Customers can add simple products without leaving the page. An optional quantity selector is also available per preset.

= Does it search by SKU? =

Yes. NivoSearch supports both exact and partial SKU matching. It also supports GTIN, UPC, EAN, and ISBN product identifier search for stores that use standard product barcodes.

= Will it work with my theme? =

NivoSearch uses scoped CSS classes prefixed with `nivo-` to prevent styling conflicts. It has been tested with Storefront, Astra, OceanWP, Flatsome, Hello Elementor, WoodMart, Kadence, and GeneratePress. For any styling conflict, override the styles using `.nivo-search-*` CSS selectors or add a custom CSS class via the `container_class` shortcode attribute.

= Is it compatible with multilingual plugins? =

Yes. NivoSearch is fully translation-ready and ships with a `.pot` file. It is compatible with WPML, Polylang, and TranslatePress.

= Is it compatible with WooCommerce HPOS? =

Yes. NivoSearch is fully compatible with WooCommerce High-Performance Order Storage (HPOS).

= What happens to my presets if I delete the plugin? =

By default, all your presets and settings are preserved when you delete the plugin — reinstalling restores everything instantly. To perform a complete clean removal, enable **Delete all data on uninstall** in **NivoSearch → Settings** before deleting.

= Is NivoSearch GDPR compliant? =

Yes. NivoSearch collects no user data, sends no data to external servers, and contains no tracking scripts. It is 100% self-hosted and GPL-licensed.

= Can developers extend NivoSearch? =

Yes. NivoSearch provides 14+ PHP filters and actions, 8 JavaScript events, nonce-secured Ajax endpoints, and PSR-4 autoloading via Composer. See the Developer Hooks section above for the complete reference.

== Screenshots ==

1. Live Ajax search results — product image, title, price, and SKU displayed as the user types
2. Search preset list — unlimited presets with shortcode shown in the list view
3. Preset configuration panel — search scope, display options, and styling in one screen
4. Category and tag results displayed in dedicated sections above product results
5. NivoSearch Settings page — Data & Privacy options

== Changelog ==

= 1.2.0 =
* FIX: Activation race condition — default preset now reliably created on fresh install
* FIX: Deactivation hook now correctly flushes rewrite rules
* FIX: Shortcode `container_class`, `input_class`, and `results_class` attributes now applied to rendered HTML
* FIX: Duplicate clear button — suppressed browser-native X icon on `type="search"` inputs
* NEW: Database migration system for safe, zero-downtime plugin updates
* NEW: Safe uninstall — preset data is preserved by default when the plugin is deleted
* NEW: "Data & Privacy" settings card with opt-in data deletion toggle
* IMPROVED: `aria-label` on search input and `aria-live` on results panel for screen readers
* IMPROVED: Submit button restored for keyboard navigation and accessibility users
* IMPROVED: Default preset now pre-populated with all current settings keys on fresh install

= 1.1.1 =
* Compatibility: Verified with WordPress 6.8 and WooCommerce 9.0

= 1.1.0 =
* NEW: Unlimited search presets with independent styling and logic
* NEW: Enhanced Gutenberg block with live preset selection
* UPDATED: Improved shortcode parsing

= 1.0.1 =
* UPDATED: Plugin name clarification for WordPress.org compliance
* UPDATED: Gutenberg block registration improvements

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.0 =
Stability and accessibility update. Existing presets are automatically migrated. No manual action required.

== Developer ==

NivoSearch is developed and maintained by [Nazmun Sakib](https://nazmunsakib.com).

[» GitHub](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)

Privacy: NivoSearch collects no user data, sends no external analytics, and is 100% GPL-licensed.
