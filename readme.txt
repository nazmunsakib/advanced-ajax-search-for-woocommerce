=== Nivo AJAX Search for WooCommerce ===
Contributors: nazmunsakib
Tags: woocommerce, ajax search, product search, live search, fuzzy search
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional live product search with AI-powered fuzzy search and typo correction for WooCommerce stores.

== Description ==

**Nivo AJAX Search for WooCommerce** is a lightweight, modern, and powerful search plugin that enhances your WooCommerce store with real-time AJAX search functionality. Built with modern technologies and AI-powered algorithms, it provides the best search experience for your customers.

= 🚀 KEY FEATURES =

* **Real-time AJAX Search** - Instant results as you type
* **AI-Powered Fuzzy Search** - Levenshtein algorithm for smart matching
* **Automatic Typo Correction** - Fixes spelling mistakes (e.g., "shose" → "shoes")
* **Smart Relevance Ranking** - Intelligent result ordering
* **Multi-field Search** - Search in title, SKU, description, categories
* **Modern React Admin** - Beautiful interface with live preview
* **Gutenberg Block** - "Nivo AJAX Search" block included
* **Fully Customizable** - Colors, borders, padding, and more
* **Lightweight & Fast** - Optimized performance
* **Mobile Responsive** - Works perfectly on all devices
* **Developer Friendly** - Extensive hooks and filters
* **Translation Ready** - WPML compatible

= 🎯 WHY CHOOSE THIS PLUGIN? =

**vs Other Search Plugins:**

✅ **Better UI/UX** - Modern React-based admin with live preview
✅ **Lighter & Faster** - Clean code, no bloat
✅ **Easier to Use** - Intuitive settings, one-click shortcode copy
✅ **Modern Technology** - React, vanilla JavaScript, PSR-4 autoloading

= 🔍 SEARCH CAPABILITIES =

* Search in product titles
* Search in product SKUs
* Search in descriptions
* Search in short descriptions
* Search in categories
* Fuzzy matching with typo tolerance
* Synonym support
* Relevance scoring

= 🎨 CUSTOMIZATION OPTIONS =

**Search Bar Styling:**
* Custom placeholder text
* Adjustable width (200-1200px)
* Border width, color, and radius
* Background color
* Padding control
* Center alignment option
* Show/hide search icon
* Live preview

**Search Results Display:**
* Show/hide product images
* Show/hide prices
* Show/hide SKU
* Show/hide descriptions
* Custom border and background
* Padding control
* Live preview

= 💻 USAGE =

**Shortcode:**
`[NASFWC_ajax_search]`

**Gutenberg Block:**
Search for "Nivo AJAX Search" in the block editor.

**PHP Template:**
`<?php echo do_shortcode('[NASFWC_ajax_search]'); ?>`

= 🛠️ DEVELOPER FEATURES =

**Filters:**
* `NASFWC_search_args` - Modify search arguments
* `NASFWC_search_results` - Customize search results
* `NASFWC_search_result_item` - Modify individual items
* `NASFWC_typo_corrections` - Add custom typo corrections
* `NASFWC_synonyms` - Add custom synonyms

**Actions:**
* `NASFWC_plugin_loaded` - Plugin initialization
* `NASFWC_components_loaded` - Components loaded

**JavaScript Events:**
* `NASFWC:init` - Search initialized
* `NASFWC:resultsDisplayed` - Results displayed
* `NASFWC:noResults` - No results found

= 🌐 TRANSLATIONS =

The plugin is translation ready and includes:
* POT file for translations
* WPML compatible
* RTL support ready

= 📚 DOCUMENTATION =

Full documentation available at [GitHub](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)

= 💬 SUPPORT =

* [Support Forum](https://wordpress.org/support/plugin/nivo-ajax-search-for-woocommerce/)
* [GitHub Issues](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce/issues)

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for "Nivo AJAX Search for WooCommerce"
4. Click "Install Now" and then "Activate"
5. Go to WooCommerce → Nivo AJAX Search to configure

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Go to Plugins → Add New → Upload Plugin
4. Choose the ZIP file and click "Install Now"
5. Activate the plugin
6. Go to WooCommerce → Nivo AJAX Search to configure

= After Installation =

1. Go to WooCommerce → Nivo AJAX Search
2. Configure your search settings
3. Copy the shortcode `[NASFWC_ajax_search]`
4. Paste it into any page, post, or widget
5. Or use the "Nivo AJAX Search" Gutenberg block

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, this plugin requires WooCommerce to be installed and activated.

= Will it work with my theme? =

Yes! The plugin is designed to work with any WordPress theme. The search form inherits your theme's styling by default.

= Does it support variable products? =

Yes, the plugin searches through all WooCommerce product types including variable products.

= Can I customize the search results? =

Absolutely! You can customize colors, borders, padding, and what information to display (images, prices, SKU, descriptions).

= Does it work on mobile devices? =

Yes, the plugin is fully responsive and works perfectly on all devices.

= How does the fuzzy search work? =

The plugin uses the Levenshtein distance algorithm to find matches even with typos (up to 2 character differences).

= Can I search by SKU? =

Yes, enable "Search in SKU" in the Search Scope settings.

= Does it slow down my site? =

No! The plugin is lightweight and optimized for performance with debounced input and efficient queries.

= Can I translate the plugin? =

Yes, the plugin is translation ready and includes a POT file for translations.

= Is it compatible with WPML? =

Yes, the plugin is WPML compatible for multilingual stores.

= Can developers extend the plugin? =

Yes! The plugin includes extensive hooks, filters, and JavaScript events for customization.

= How do I display the search form? =

Use the shortcode `[NASFWC_ajax_search]` or the "Nivo AJAX Search" Gutenberg block.

== Screenshots ==

1. Modern React admin interface with live preview
2. Search bar customization options
3. Search results display settings
4. Live search in action on frontend
5. Gutenberg block in editor
6. Mobile responsive design
7. Search results with product images and prices
8. Settings page - General tab

== Changelog ==

= 1.0.0 - 2025 =
* Initial release
* Real-time AJAX search with debouncing
* AI-powered fuzzy search with Levenshtein algorithm
* Automatic typo correction
* Modern React-based admin interface
* Live preview for search bar and results
* Gutenberg block "Nivo AJAX Search"
* Shortcode `[NASFWC_ajax_search]` with one-click copy
* Multi-field search (Title, SKU, Description, Categories)
* Smart relevance ranking algorithm
* Fully customizable styling options
* Responsive design
* Accessibility ready (WCAG compliant)
* Security hardened (nonce verification, sanitization)
* Translation ready
* Reset settings functionality
* Theme-agnostic design
* Lightweight and fast
* Developer-friendly with hooks and filters

== Upgrade Notice ==

= 1.0.0 =
Initial release of Nivo AJAX Search for WooCommerce. Install now to enhance your store's search experience!

== Additional Info ==

**Credits:**
* Developed by [Nazmun Sakib](https://nazmunsakib.com)
* Built with React, WordPress REST API, and modern JavaScript

**Links:**
* [Plugin Homepage](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)
* [Documentation](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce/wiki)
* [Support](https://wordpress.org/support/plugin/nivo-ajax-search-for-woocommerce/)
* [GitHub Repository](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)

**Privacy:**
This plugin does not collect or store any personal data. All search queries are processed locally on your server.
