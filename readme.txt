=== NivoSearch – Ajax Search for WooCommerce ===
Contributors: nazmunsakib
Donate link: https://nivosearch.com
Tags: woocommerce search, ajax search, product search, live search, search form
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Ajax Search plugin, free. Live product search with typo tolerance, fuzzy matching, SKU search, and add-to-cart from results.

== Description ==

**NivoSearch** is a free WooCommerce Ajax search plugin that turns your WooCommerce search bar into a live autocomplete search. Products appear in a dropdown as customers type, with no page reload and no coding required. Works with any WooCommerce store, any theme, and any page builder, right out of the box.

The default WooCommerce search makes customers wait for a full page to reload and cannot handle typos or SKU searches. NivoSearch solves all of that. It corrects spelling mistakes automatically, searches product SKUs, and lets shoppers add items to the cart directly from the search results, all in under 200 ms.

[Live Demo](https://nivosearch.com/live-demo-woocommerce-product-search/) | [Documentation](https://nivosearch.com/documentation/)

—

= What NivoSearch Does for Your Customers =

**Products appear as they type**
Customers see matching products in a dropdown immediately, with no page reload. This WooCommerce instant search works on every keystroke, so results update as the customer refines their query. Each result shows the product image, name, price, SKU, stock status, and category.

**Spelling mistakes are corrected automatically**
NivoSearch uses two separate correction engines. The first checks a built-in dictionary of over 300 common e-commerce misspellings. "bleutooth" finds Bluetooth products. "niike" finds Nike products. If the dictionary has no match, the second engine scans the NivoSearch product index and finds the word in your catalog that is closest to what the customer typed. A "Did you mean?" hint appears in both cases so customers can confirm the correction with one click. Both engines work automatically with no setup needed.

**Customers can add to cart without leaving the page**
Simple products show an add-to-cart button directly in the search results. Shoppers can set a quantity and add to the cart without opening the product page. The cart count updates instantly.

**Product codes and SKUs work**
Customers and staff who search by product code find the exact product. NivoSearch searches parent SKUs and individual variation SKUs, so searching a variation code returns the right product.

**Results are organized and easy to scan**
Products appear in a relevance-ranked list. Categories and tags that match the search appear in their own sections above the product results. Matching keywords are highlighted in the product title.

**Search history saves time**
The last 8 searches are saved and shown when the customer focuses on the search bar. One tap re-runs any previous search.

**Works on mobile**
On phones and small screens, the search opens in a full-screen overlay so results are easy to read and tap.

—

= What You Get Free (That Other Plugins Charge For) =

The most WooCommerce Ajax search plugins charge for features. NivoSearch includes all of these for free:

* Typo correction with 300+ built-in rules, and up to 10 custom rules you add yourself
* Fuzzy search that finds products even with unusual misspellings
* SKU search: parent SKU and variation-level SKU both searched automatically
* Add-to-cart button directly in the WooCommerce search results
* Unlimited WooCommerce search bars, each with its own settings and style
* Block and FSE theme support: Twenty Twenty-Four, Twenty Twenty-Five, and any FSE theme
* WPML and Polylang multilingual WooCommerce search
* Mobile full-screen search overlay
* Google Analytics 4 and Google Tag Manager search event tracking

—

= NivoSearch vs the Default WooCommerce Search =

| | NivoSearch | Default WooCommerce |
| — | — | — |
| Results appear as you type | Yes | No, requires page reload |
| Spelling correction | Yes, 300+ rules | No |
| Searches SKUs | Yes, parent and variation | No |
| Add to cart from results | Yes | No |
| Images and prices in results | Yes | No |
| Mobile search overlay | Yes | No |
| Search history | Yes | No |
| Multiple search bar configurations | Yes, unlimited | No |

—

= Full Feature List =

**What It Searches**

* Product names: exact, partial, and starts-with matching
* Product descriptions and short descriptions
* Product SKUs: parent SKU and individual variation SKUs
* Product categories, shown in their own section
* Product tags, shown in their own section
* Accented characters treated as equivalent: searching "cafe" finds cafe and café
* Language-scoped results with WPML and Polylang
* Option to hide out-of-stock products per search bar

**Typo Tolerance and Fuzzy Search**

NivoSearch uses two independent correction engines that run in sequence when a customer's search returns no exact results.

Engine 1, Typo Tolerance: checks the search term against a built-in dictionary of over 300 common e-commerce misspellings, plus up to 10 custom rules you add yourself in the Typo Rules page. If a match is found, the search runs again automatically using the corrected spelling.

Engine 2, Fuzzy Search: NivoSearch keeps a private search index of every word in your product catalog. If the dictionary has no correction for the search term, the fuzzy engine scans the index for the word that is closest to what the customer typed, then returns products that contain it. This index-based approach stays fast on stores with tens of thousands of products because it only runs distance calculations on a small set of candidates, not on your entire catalog.

When either engine finds a correction, a "Did you mean?" suggestion appears in the results. Customers click it to confirm and re-run the corrected search. Both engines have their own independent on/off toggle in NivoSearch > Settings > Search Accuracy.

**What Appears in the Results**

* Product image, loaded without slowing down the page
* Product name with matching keywords highlighted in bold
* Product SKU shown next to the name
* Current selling price, with the sale price shown automatically when active
* Short description
* Stock status: In Stock, Out of Stock, or On Backorder
* Category badges shown on each product card
* Add-to-cart button with optional quantity selector for simple products
* Cart updates instantly after adding, with no page reload
* Variable products show a link to choose options on the product page
* "View All Results" link always visible at the bottom of the results panel
* Recent searches shown when the customer clicks the search bar

**Setup and Placement**

* Shortcode: paste `[nivo_search id="123"]` into any page, post, widget, or template
* Gutenberg block: add the NivoSearch block in the block editor and pick a preset
* Auto-replace: one setting replaces your theme's existing search box everywhere, no shortcode needed
* Works with all classic themes: Astra, OceanWP, Storefront, Flatsome, Kadence, GeneratePress, and others
* Works with all block and FSE themes: Twenty Twenty-Four, Twenty Twenty-Five, and others
* Mobile overlay: full-screen search on screens under 768 px wide
* Search results page: NivoSearch product results appear on the native WordPress search results page (yoursite.com/?s=query), so customers who press Enter still see WooCommerce products
* Optional redirect to shop: pressing Enter can redirect to the WooCommerce shop page with the search term pre-filled, giving customers a full product grid view

**Search Presets**

* Create unlimited search presets, each with its own independent configuration
* Each preset controls its own search scope, result display options, and visual style
* Color settings: search bar background, border, text, results panel background, and result text
* Width and height controls for the search bar
* Minimum characters before a search starts: 1 to 5
* Maximum results to show: 1 to 50
* Search delay: how long to wait after the customer stops typing before searching
* Custom CSS class support via shortcode attributes

**Performance**

* Search results are cached for 5 minutes and automatically refreshed when products change
* All matching products are loaded in a single database call
* Responds in under 200 ms on standard hosting
* Plugin scripts and styles only load on pages where a search bar is placed

**Analytics and Integrations**

* Google Analytics 4: automatic search event after each WooCommerce Ajax search
* Universal Analytics: automatic search event after each search
* Google Tag Manager: search data pushed to the data layer automatically
* Custom JavaScript event available for your own tracking integrations
* WooCommerce HPOS: fully compatible
* Analytics tracking can be disabled in Settings

**Privacy**

* No personal data is collected by the plugin
* No data is sent to external servers
* Search history is stored in the customer's own browser only, never on your server
* Google Analytics integration is opt-in and off by default
* Your presets and settings are kept when you deactivate or delete the plugin
* You can choose to delete all plugin data on uninstall in Settings

—

== Frequently Asked Questions ==

= Do I need any coding skills to use NivoSearch? =

No. You can set up NivoSearch entirely from the WordPress admin. Install the plugin, go to NivoSearch > Settings, enable "Replace theme search form," and your existing search box is automatically replaced. No shortcodes, no code, no developer needed.

= What is NivoSearch? =

NivoSearch is a free WooCommerce Ajax search plugin. It replaces or supplements the default WooCommerce search with a real-time dropdown that shows product results as customers type, with images, prices, stock status, and an add-to-cart button.

= How is NivoSearch different from the default WooCommerce search? =

The default WooCommerce search requires a full page reload, returns plain links with no images or prices, cannot correct typos, and cannot search by SKU. NivoSearch shows results in a styled dropdown in under 200 ms, corrects over 300 common misspellings, searches parent and variation SKUs, and lets shoppers add items to the cart without leaving the page.

= What happens if a customer makes a typo? =

NivoSearch uses two search correction engines that run in sequence when the original search returns no results.

First, Typo Tolerance checks the search term against a built-in dictionary of over 300 common e-commerce misspellings, plus any custom rules you have added. If a correction is found, the search runs again automatically with the corrected spelling, and a "Did you mean?" hint appears so the customer can confirm.

If no dictionary correction exists, Fuzzy Search takes over. NivoSearch keeps a private index of every word in your product catalog. The fuzzy engine finds the word in that index that is closest to what the customer typed, then returns matching products. A "Did you mean?" hint still appears so the customer can confirm.

Both engines run only when the original search returns no results, so they never interfere with exact matches. Both have their own on/off toggle in NivoSearch > Settings > Search Accuracy. You can use either engine independently or run both together.

= Does it search product SKUs? =

Yes. NivoSearch searches both parent product SKUs and individual variation SKUs. If a staff member or customer searches a variation code, NivoSearch returns the correct parent product.

= Can customers add products to the cart from the search results? =

Yes. Simple products show an add-to-cart button with an optional quantity field directly in the results. The cart updates instantly without a page reload. Variable products show a link to the product page to choose options.

= Does it work with my theme? =

Yes. NivoSearch works with all classic themes including Astra, OceanWP, Storefront, Flatsome, Kadence, and GeneratePress. It also works with all block and FSE themes including Twenty Twenty-Four and Twenty Twenty-Five.

= Do I need to use a shortcode? =

No. Enable "Replace theme search form" in NivoSearch > Settings > Search Integration. NivoSearch automatically replaces your theme's existing search form everywhere it appears. The shortcode is available if you want to add a separate search bar in a specific location.

= Can I have different search bars with different styles in different places? =

Yes. NivoSearch uses presets. Each preset is a separate configuration with its own search settings, display options, and colors. Create as many presets as you need and place each one using its shortcode or Gutenberg block.

= Does it work with WPML or Polylang for multilingual stores? =

Yes. NivoSearch detects the active language and scopes search results to that language automatically on every search.

= Does NivoSearch handle accented characters? =

Yes. Searching "cafe" finds products named "Café" and vice versa. Accented and non-accented versions of the same word are treated as equivalent.

= Does NivoSearch slow down my website? =

No. The plugin's scripts and styles only load on pages where a NivoSearch bar is placed. The search runs in the background after the page loads and has no effect on your page load time.

= Is it GDPR compliant? =

Yes. NivoSearch collects no personal data and sends nothing to external servers. The search history feature stores data only in the customer's own browser. Google Analytics integration is opt-in and disabled by default.

= What happens to my settings if I delete the plugin? =

Deleting the plugin keeps all your presets and settings by default. If you want to remove everything, enable "Delete all data on uninstall" in NivoSearch > Settings before deleting.

= Is it compatible with WooCommerce HPOS? =

Yes. NivoSearch is fully compatible with WooCommerce High-Performance Order Storage.

= How do I track what customers are searching for? =

Enable Google Analytics tracking in NivoSearch > Settings > Search Integration. Each search fires a standard search event to GA4, Universal Analytics, or Google Tag Manager automatically.

= What is the best free WooCommerce Ajax search plugin? =

NivoSearch is a free WooCommerce Ajax search plugin that includes features most competitors charge for: real-time WooCommerce instant search with autocomplete, typo tolerance using a 300+ word dictionary, fuzzy search via an index-based engine, SKU and variation SKU search, add-to-cart from results, search history, mobile overlay, WPML and Polylang support, and unlimited WooCommerce search bars. There is no subscription and no feature locks. It works on any theme, including block and FSE themes, without coding.

—

== Installation ==

= Automatic Installation (Recommended) =

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for **NivoSearch**
3. Click **Install Now**, then **Activate**
4. Go to **NivoSearch > Settings > Search Integration** and enable **Replace theme search form** to get started immediately

= Manual Installation =

1. Download the plugin ZIP from WordPress.org
2. Go to **Plugins > Add New > Upload Plugin**
3. Upload the ZIP and click **Install Now**
4. Activate the plugin

= Quick Start (5 minutes) =

**Option A: Replace your theme's existing search box (no shortcode needed)**

1. Go to **NivoSearch > Settings**
2. Under Search Integration, turn on **Replace theme search form**
3. Save. Your theme's search box is now a NivoSearch bar.

**Option B: Add a custom search bar anywhere**

1. Go to **NivoSearch > Search Presets > Add New**
2. Set your results limit, minimum characters, and placeholder text
3. Choose what to search and what to display in the results
4. Customize colors in the Style tab
5. Click **Publish**
6. Copy the shortcode from the preset list and paste it anywhere

—

== For Developers ==

= PHP Filters =

`nivo_search_args` – Modify search query arguments before execution.
`nivo_search_results` – Modify the full results array before the response is sent.
`nivo_search_result_item` – Modify an individual product result item.
`nivo_search_shortcode_html` – Override the complete shortcode HTML output.
`nivo_search_localize_data` – Modify the JavaScript localization data passed to the frontend.
`nivo_search_should_enqueue_assets` – Return false to prevent asset loading on specific pages.
`nivo_search_typo_corrections` – Register a custom typo correction map (array of misspelling to correction).
`nivo_search_max_custom_rules` – Override the custom typo rules limit.
`nivo_search_language_query_args` – Modify language query arguments for WPML or Polylang.
`nivo_search_excluded_ids` – Dynamically exclude product IDs from search results.

= PHP Actions =

`nivo_search_before_search` – Fires before the search query executes.
`nivo_search_after_search` – Fires after results are ranked and before the response is sent.
`nivo_search_index_rebuilt` – Fires after the search index is fully rebuilt.
`nivo_search_preset_saved` – Fires after a preset's meta is saved.

= JavaScript Events =

`nivo_search:init` – Search widget initialized on the page.
`nivo_search:beforeSearch` – Fired before a search request is sent.
`nivo_search:resultsDisplayed` – Fired after results are shown in the dropdown.
`nivo_search:noResults` – Fired when the search returns zero products.
`nivo_search:error` – Fired when a search request fails.
`nivo_search:resultsCleared` – Fired when the results dropdown is cleared.
`nivo_search:focus` – Fired when the search input receives focus.
`nivo_search:blur` – Fired when the search input loses focus.
`nivo_search:didYouMeanClicked` – Fired when the customer clicks a "Did you mean?" suggestion.

—

== Screenshots ==

1. Live Ajax search results: product image, title, price, SKU, and add-to-cart button appear as the customer types
2. "Did you mean?" suggestion shown after a typo is auto-corrected
3. Search preset list: unlimited presets with shortcode shown in the admin list view
4. Preset configuration: search scope, display toggles, and color controls in one screen
5. NivoSearch Settings: Search Accuracy cards showing Search Index, Fuzzy Search, Typo Tolerance, and Did You Mean
6. NivoSearch Settings: Search Integration cards showing Replace theme form, Redirect to shop, and GA tracking
7. Typo Rules page: custom correction rules with add, edit, delete, and CSV import and export

—

== Changelog ==

= 2.0.2 =
* NEW: Fuzzy search engine: finds close product name matches when no exact results are found
* NEW: Search index built and maintained automatically, with one-click rebuild in Settings
* NEW: "Did you mean?" suggestion with a clickable link to re-search using the corrected spelling
* NEW: Variation SKU search: searching a variation SKU returns the parent product
* NEW: Typo Rules settings page: add up to 10 custom correction rules, import and export as CSV
* NEW: Typo correction engine with 300+ built-in e-commerce misspelling corrections
* NEW: Accented characters treated as equivalent (cafe finds cafe and café)
* NEW: WPML and Polylang support: language scoped automatically on each search
* NEW: Block and FSE theme support: replaces the Gutenberg search block automatically
* NEW: Preset selector for theme form replacement: choose which preset replaces your theme search box
* NEW: Search history panel: last 8 searches shown when the search bar is focused
* NEW: Mobile search overlay: full-screen search opens on small screens
* NEW: Google Analytics 4, Universal Analytics, and GTM: automatic search event on each search
* NEW: Products shown on the WordPress search results page
* NEW: Redirect to WooCommerce shop page when customer presses Enter
* IMPROVED: Fuzzy search and typo tolerance are now separate, independent toggles
* IMPROVED: Products loaded in one batch database call instead of one call per product
* IMPROVED: SKU ranking uses one database query instead of one per product
* SECURITY: Nonce verified on all request paths including wc-ajax
* SECURITY: Preset settings sanitized before sending in JSON response
* SECURITY: All database queries use prepared statements

= 1.0.0 =
* NEW: Add-to-cart button in search results with instant cart update and no page reload
* NEW: Quantity selector in search results, configurable per preset
* NEW: Variable product support: link to product page for option selection
* NEW: Stock status badge in results: In Stock, Out of Stock, On Backorder
* NEW: Category labels on each product result
* NEW: Short description shown below the product name in results
* NEW: Sale price shown automatically when active
* NEW: SKU shown inline next to the product name
* NEW: 5-minute result cache, cleared automatically when products or presets change
* NEW: "View All Results" link pinned to the bottom of the results panel
* NEW: Database migration system for safe plugin updates
* NEW: Preset data preserved by default on plugin deletion
* NEW: "Delete all data on uninstall" opt-in setting
* NEW: Unlimited search presets
* NEW: Gutenberg block with preset selector
* FIX: Default preset not created reliably on fresh install
* FIX: Deactivation now correctly flushes rewrite rules
* FIX: Custom CSS class attributes on the shortcode now applied to the HTML output

—

== Upgrade Notice ==

= 2.0.2 =
Major update. After upgrading, go to NivoSearch > Settings > Search Accuracy and click Rebuild Index to update the search index with your current product catalog.

—

== Privacy ==

NivoSearch collects no personal data, sends nothing to external servers, and includes no third-party tracking scripts. It is 100% self-hosted and GPL-licensed. Search history is stored only in the customer's own browser and is never sent to your server. Google Analytics integration is opt-in and disabled by default.

== Developer ==

NivoSearch is developed and maintained by [Nazmun Sakib](https://nazmunsakib.com).

[GitHub](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce) | [Documentation](https://nivosearch.com/documentation/) | [Support](https://wordpress.org/support/plugin/nivo-ajax-search-for-woocommerce/)
