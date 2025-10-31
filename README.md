# Nivo AJAX Search for WooCommerce

🚀 **Professional live product search with AI-powered features for WooCommerce stores**

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0+-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## ✨ Features

### 🔍 **Real-time AJAX Search**
- **Instant Results** - Live search as you type
- **Debounced Input** - Optimized performance with 300ms delay
- **Multi-field Search** - Title, SKU, tags, and descriptions
- **Relevance Scoring** - Intelligent result ranking

### 🤖 **AI-Powered Intelligence**
- **Typo Correction** - Automatic spelling mistake fixes
- **Synonym Support** - Expanded search with related terms
- **Smart Ranking** - Title > SKU > Tags > Description priority
- **Query Understanding** - Enhanced search algorithms

### 🎨 **Modern Interface**
- **Responsive Design** - Works on all devices
- **Customizable Styling** - Colors, icons, and layouts
- **Loading States** - Professional user feedback
- **Accessibility Ready** - WCAG compliant

### ⚙️ **Easy Integration**
- **Shortcode Support** - `[NASFWC_ajax_search]`
- **Gutenberg Block** - Visual block editor integration
- **Widget Ready** - Add to any widget area
- **Developer Friendly** - Extensive hooks and filters

## 🚀 Quick Start

### Installation

1. **Upload** the plugin files to `/wp-content/plugins/nivo-ajax-search-for-woocommerce/`
2. **Activate** the plugin through WordPress admin
3. **Configure** settings in WooCommerce → Nivo AJAX Search
4. **Add** search form using shortcode or Gutenberg block

### Basic Usage

#### Shortcode
```php
[NASFWC_ajax_search]
```

#### With Custom Options
```php
[NASFWC_ajax_search placeholder="Find products..." show_icon="true" style="width: 100%;"]
```

#### Gutenberg Block
Search for "Nivo AJAX Search" in the block editor and customize via inspector panel.

## 📋 Requirements

- **WordPress** 5.0 or higher
- **WooCommerce** 5.0 or higher  
- **PHP** 7.4 or higher
- **Modern Browser** with JavaScript enabled

## ⚙️ Configuration

### General Settings
- **Enable AJAX Search** - Toggle real-time functionality
- **Enable AI Features** - Activate intelligent search
- **Search Results Limit** - Maximum results (1-50)
- **Minimum Characters** - Trigger threshold (1-5)
- **Search Delay** - Debounce timing (100-1000ms)

### Display Options
- **Product Images** - Show thumbnails in results
- **Product Prices** - Display pricing information
- **Add to Cart** - Quick purchase buttons

### AI Features
- **Typo Correction** - Fix common spelling errors
- **Synonym Support** - Expand with related terms

## 🎯 Shortcode Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `placeholder` | string | "Search products..." | Input placeholder text |
| `container_class` | string | "nasfwc-ajax-search-container" | Container CSS class |
| `input_class` | string | "nasfwc-product-search" | Input CSS class |
| `results_class` | string | "nasfwc-search-results" | Results CSS class |
| `show_icon` | boolean | true | Display search icon |
| `style` | string | "" | Inline CSS styles |

## 🔧 Developer Hooks

### Filters
```php
// Modify search arguments
add_filter('NASFWC_search_args', function($args, $query) {
    return $args;
}, 10, 2);

// Customize search results
add_filter('NASFWC_search_results', function($results, $query) {
    return $results;
}, 10, 2);

// Modify individual result items
add_filter('NASFWC_search_result_item', function($result, $product, $query) {
    return $result;
}, 10, 3);
```

### Actions
```php
// Plugin loaded
add_action('NASFWC_plugin_loaded', function($plugin) {
    // Custom initialization
});

// Components loaded
add_action('NASFWC_components_loaded', function($plugin) {
    // Add custom components
});
```

### JavaScript Events
```javascript
// Search initialized
$(document).on('NASFWC:init', function(e, data) {
    console.log('Search initialized');
});

// Results displayed
$(document).on('NASFWC:resultsDisplayed', function(e, data) {
    console.log('Results:', data.products);
});
```

## 🏗️ Architecture

### File Structure
```
nivo-ajax-search-for-woocommerce/
├── assets/
│   ├── css/
│   │   ├── nasfwc-search.css
│   │   └── admin.css
│   └── js/
│       ├── nasfwc-search.js
│       ├── admin.js
│       ├── admin-react.js
│       └── block-editor.js
├── includes/
│   ├── admin/
│   │   └── Admin_Settings.php
│   └── classes/
│       ├── Enqueue.php
│       ├── Gutenberg_Block.php
│       ├── Plugin.php
│       ├── Search_Algorithm.php
│       └── Shortcode.php
├── vendor/ (Composer autoloader)
├── composer.json
└── nivo-ajax-search-for-woocommerce.php
```

### Key Classes
- **`Plugin`** - Main plugin controller (Singleton)
- **`Search_Algorithm`** - AI-powered search logic
- **`Enqueue`** - Asset management
- **`Shortcode`** - Shortcode functionality
- **`Gutenberg_Block`** - Block editor integration
- **`Admin_Settings`** - Configuration interface

## 🐛 Troubleshooting

### Common Issues

**Search not working?**
- Ensure WooCommerce is active
- Check AJAX is enabled in settings
- Verify JavaScript console for errors

**No results showing?**
- Check minimum character setting
- Verify products are published
- Test with different search terms

**Styling issues?**
- Check theme CSS conflicts
- Use browser developer tools
- Add custom CSS if needed

## 📝 Changelog

### Version 1.0.0
- Initial release
- Real-time AJAX search
- AI-powered features
- Gutenberg block integration
- Modern React admin interface
- Comprehensive customization options

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the GPL v2 License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Nazmun Sakib**
- Website: [nazmunsakib.com](https://nazmunsakib.com)
- GitHub: [@nazmunsakib](https://github.com/nazmunsakib)

## 🙏 Acknowledgments

- WordPress community for excellent documentation
- WooCommerce team for robust e-commerce platform
- React team for modern UI framework

---

⭐ **If you find this plugin helpful, please consider giving it a star!**