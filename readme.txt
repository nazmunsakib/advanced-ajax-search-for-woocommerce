=== NivoSearch – Ajax Search for WooCommerce ===
Contributors: nazmunsakib
Donate link: https://nazmunsakib.com
Tags: ajax search for woocommerce, product search, live woocommerce search, woocommerce search, woocommerce product search
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

High-performance WooCommerce Ajax Search with instant results, fuzzy matching, and SKU/GTIN support. Built for speed, scalability, and conversions.

== Description ==

Looking for a fast, reliable Ajax search plugin for WooCommerce? NivoSearch delivers instant product results with intelligent matching, free, open-source, and built for scale.

NivoSearch is a professional-grade **WooCommerce Ajax Search** solution engineered to replace slow, limited default search with instant, intelligent results. Delivering responses in under **200ms**, it combines real-time Ajax technology, custom indexing architecture, and advanced relevance scoring to dramatically improve product discovery and conversion rates across stores of any size.

Unlike standard WooCommerce search that relies on basic SQL queries, NivoSearch uses an optimized inverted index to process product data efficiently. This ensures consistent performance whether you have 100 products or **100,000+ products**, making it the ideal WooCommerce Ajax Search plugin for growing and enterprise-level stores.

Key capabilities include **fuzzy search** for typo tolerance, **SKU-based lookup** for precise inventory matching, and support for global product identifiers (**GTIN, UPC, EAN, ISBN**) — features typically locked behind premium paywalls, included free. Customers find what they need faster, even with partial or misspelled queries, reducing bounce rates and increasing add-to-cart actions.

[&raquo; Documentation](https://nivosearch.com/documentation/) | [&raquo; Live Demo](https://nivosearch.com/live-demo-woocommerce-product-search/) | [&raquo; GitHub Repository](https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce)

== Why NivoSearch for WooCommerce Ajax Search ==

* **Performance-first architecture**: Custom indexing engine delivers <200ms response times, independently benchmarked against default WooCommerce search (1,240ms average).
* **Scalable by design**: Efficient database queries and caching strategies ensure smooth operation on high-traffic stores with large catalogs.
* **Conversion-optimized UX**: Ajax results update in real-time as users type, with add-to-cart buttons, pricing, and stock status visible instantly.
* **Developer-ready**: PSR-4 architecture, 15+ hooks/filters, JavaScript events, and full documentation for seamless integration.
* **100% free and open-source**: No tracking, no external requests, no premium upsells — truly GPL-licensed software.

== Core Features of WooCommerce Ajax Search ==

NivoSearch delivers a comprehensive set of capabilities designed for professional WooCommerce stores:

* **Ultra-fast Ajax engine** powered by inverted index technology for instant results on large catalogs
* **Intelligent fuzzy search** that matches products despite typos, partial keywords, or spelling variations
* **Advanced identifier search** supporting SKU, GTIN, UPC, EAN, and ISBN for barcode-driven inventory
* **Custom field integration** with dedicated support for Advanced Custom Fields (ACF)
* **Full-content search** across product titles, excerpts, descriptions, and metadata
* **Taxonomy-aware results** including categories, tags, and custom attributes with thumbnail support
* **One-click add to cart** directly within Ajax search results to reduce friction and boost conversions
* **Unlimited search presets** allowing unique configurations for headers, sidebars, footers, or landing pages
* **Context-aware deployment** with conditional logic to show different search behaviors based on page or user role
* **Relevance scoring engine** that prioritizes best-matching products using configurable weight rules

== Display Control in Ajax Search Results ==

NivoSearch provides granular control over how products appear in WooCommerce Ajax Search results, ensuring users see the most actionable information immediately:

* Product title with configurable length and formatting
* Dynamic pricing display showing regular, sale, or variable prices
* High-resolution image thumbnails with lazy loading for performance
* Short description excerpts to highlight key features and benefits
* SKU display for internal reference and precise product identification
* Add to Cart button with quantity selector for faster purchasing
* Star ratings and review counts to build social proof and trust
* Real-time stock status indicators (in stock, out of stock, backorder)
* Category and tag badges for improved navigation and filtering

== Customization Options ==

= Search Bar Customization =

Tailor the Ajax search input to match your store's design and UX goals:

* Customize colors, borders, icons, typography, and layout styles
* Define contextual placeholder text to guide user search intent
* Configure minimum character threshold before triggering search
* Enable/disable search animation effects and loading states
* Real-time preview while configuring presets in the admin panel

= Search Results Customization =

Control exactly how WooCommerce Ajax Search results are presented:

* Adjust result layout: list, grid, or compact view
* Set maximum result limits per query for optimal performance
* Toggle visibility of categories, tags, or attribute filters
* Enable/disable product title, description, or SKU display
* Configure relevance sorting: newest, price, popularity, or custom logic
* Add custom CSS classes for theme-specific styling

== Performance Benchmark ==

Independent testing conducted on a shared hosting environment with 50,000 WooCommerce products:

* Default WooCommerce Search average response time: 1,240ms
* NivoSearch average response time: 187ms
* Test methodology: 100 random product queries measuring time-to-first-result
* Database load reduction: ~65% fewer queries per search session

Benchmark methodology and reproducible WP-CLI scripts: https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce/tree/main/benchmarks

== Compatibility ==

* **Theme Support**: Hello Elementor, OceanWP, Hestia, Storefront, Astra, Avada, BeTheme, The7, Flatsome, Enfold, WoodMart, and all standard WooCommerce-compatible themes.
* **Page Builder Support**: Gutenberg (Block Editor), Elementor, Beaver Builder, WPBakery, Breakdance, Bricks, Oxygen, Divi, Avada Builder, Flatsome UX Builder.
* **Caching Plugins**: Works seamlessly with WP Rocket, W3 Total Cache, WP Super Cache, and server-level caching solutions.
* **Multilingual**: Translation-ready with full WPML and Polylang compatibility.

== Developer Resources ==

NivoSearch is built for developers who value clean code, extensibility, and performance:

= Extensibility =

* 15+ WordPress actions and filters for customizing search behavior
* JavaScript events for integrating with custom frontend frameworks
* REST API endpoints for headless WooCommerce implementations
* Template overrides for complete control over result markup

= Code Quality =

* Modern PSR-4 autoloading architecture
* Strict sanitization, validation, and nonce verification on all inputs
* WordPress Coding Standards compliant with PHPCS validation
* Accessibility-focused UI following WCAG 2.1 AA guidelines
* Comprehensive inline documentation and developer handbook

== Installation ==

= Automatic Installation =

1. Navigate to Plugins <span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji">→</span></span></span></span></span> Add New in your WordPress admin
2. Search for "NivoSearch" or "ajax search for woocommerce"
3. Click Install Now, then Activate Plugin
4. Create your first search preset in NivoSearch <span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji">→</span></span></span></span></span> Presets
5. Add the search form using the Gutenberg block or shortcode

= Manual Installation =

1. Download the plugin ZIP file from WordPress.org
2. Upload via Plugins <span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji">→</span></span></span></span></span> Add New <span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji">→</span></span></span></span></span> Upload Plugin
3. Activate the plugin
4. Configure your search preset and deploy to your theme

== Quick Start Guide ==

= Step 1: Create a Search Preset =
* Navigate to NivoSearch <span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji">→</span></span></span></span></span> Presets <span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji"><span aria-hidden="true" class="wp-exclude-emoji">→</span></span></span></span></span> Add New
* Name your preset (e.g., "Header Search", "Mobile Search")
* Configure result limit (recommended: 10-15 for optimal UX)
* Set minimum character threshold (recommended: 2-3)

= Step 2: Deploy to Your Theme =
* Use the Gutenberg block: Search for "NivoSearch" in the block inserter
* Or use shortcode: [nivo_search id="123"] replacing 123 with your preset ID
* Or integrate via PHP: <?php echo do_shortcode('[nivo_search id="123"]'); ?>

= Step 3: Test and Refine =
* Perform test searches to verify result relevance and speed
* Adjust relevance weights in preset settings if needed
* Enable analytics logging (optional) to track popular search terms

== Frequently Asked Questions ==

= What makes NivoSearch different from other WooCommerce Ajax Search plugins? =
NivoSearch is engineered for performance and scale. While many Ajax search plugins rely on standard WP_Query, NivoSearch uses a custom indexing engine to deliver results in under 200ms — even for stores with 100,000+ products. It also includes advanced features like GTIN/UPC/EAN/ISBN lookup and fuzzy matching at no cost, with a truly open-source, no-tracking philosophy.

= Is NivoSearch suitable for large WooCommerce stores? =
Yes. NivoSearch is built with scalability as a core principle. Its custom indexing architecture minimizes database load during live searches, and efficient caching strategies ensure consistent performance under high traffic. It has been benchmarked successfully on catalogs exceeding 100,000 products.

= How does NivoSearch improve conversion rates? =
By reducing search friction. Instant Ajax results mean customers find products faster. Fuzzy matching handles typos gracefully. SKU and GTIN support helps professional buyers locate exact items. Add-to-cart buttons in results shorten the purchase path. Together, these features reduce bounce rates and increase add-to-cart actions.

= Does NivoSearch work with caching plugins? =
Yes. NivoSearch is designed to work seamlessly with major caching solutions including WP Rocket, W3 Total Cache, and server-level caches. Ajax requests bypass page cache appropriately, while static assets are fully cacheable for optimal performance.

= Can I customize the search results design? =
Absolutely. NivoSearch provides extensive customization options for both the search input and results display. You can control layout, styling, visible fields, and behavior through the admin interface, with additional flexibility via CSS overrides and template files for developers.

= Is NivoSearch GDPR compliant? =
Yes. NivoSearch collects no user data, sends no external requests, and includes no tracking scripts. It is 100% self-hosted and GPL-licensed, giving you full control over your store's data privacy.

= How do I make WooCommerce search faster? =
Replace default search with NivoSearch, a WooCommerce Ajax Search plugin engineered for <200ms response times. Its custom indexing engine processes queries efficiently, reducing database load and delivering instant results even on stores with 100,000+ products. No code changes required — install, configure, and deploy in minutes.

== Screenshots ==

1. Live WooCommerce Ajax Search displaying instant results as users type with product images, pricing, and add-to-cart buttons
2. Unlimited search presets interface allowing unique configurations for different site areas
3. Advanced preset configuration panel showing relevance weighting, field selection, and display options
4. Mobile-responsive search results demonstrating seamless UX across devices

== Changelog ==

= 1.1.1 – Current Version =
* SEO: Optimized readme for "WooCommerce Ajax Search" keyword targeting
* GEO: Added performance benchmark section for AI citation readiness
* Compatibility: Verified with WordPress 6.8 and WooCommerce 9.0
* Performance: Minor query optimizations for large catalog efficiency

= 1.1.0 – December 24, 2025 =
* NEW: Unlimited search presets with independent styling and logic
* NEW: Enhanced Gutenberg block with live preset selection preview
* UPDATED: Improved shortcode parsing and PHP integration examples
* UPDATED: Refactored database queries for better large-catalog performance
* FIXED: Minor UI inconsistencies and stability improvements

= 1.0.1 – November 17, 2025 =
* UPDATED: Plugin name clarification for WordPress.org compliance
* UPDATED: Gutenberg block registration improvements

= 1.0.0 – November 2025 =
* Initial release of NivoSearch – Ajax Search for WooCommerce

== Upgrade Notice ==

= 1.1.1 =
This update includes SEO and GEO optimizations for better discoverability. No breaking changes. Safe to update.

= 1.1.0 =
Introduces unlimited search presets. Existing presets remain fully compatible. Review new preset options after updating.

== Developer ==

NivoSearch is developed and maintained by Nazmun Sakib, a WordPress engineer with 8+ years of experience building high-performance WooCommerce solutions. Active contributor to open-source WordPress projects, with a focus on scalable architecture, clean code, and user-centric design.

[&raquo; Portfolio](https://nazmunsakib.com) | [&raquo; GitHub](https://github.com/nazmunsakib) | [&raquo; LinkedIn](https://linkedin.com/in/nazmunsakib)

Privacy Commitment: NivoSearch collects no user data, sends no external analytics requests, and is 100% GPL-licensed. Your store, your data.