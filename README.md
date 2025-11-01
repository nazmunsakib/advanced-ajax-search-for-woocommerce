# AJAX Product Search for WooCommerce - Nivo Search

🚀 **The most advanced FREE WooCommerce search plugin with AI-powered features and professional performance**

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0+-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 🏆 **Why Choose Nivo Search Over Other Free Plugins?**

| Feature | Nivo Search | Other Free Plugins |
|---------|-------------|---------------------|
| **Performance** | ⚡ Optimized single query (premium-level) | ❌ Multiple slow queries |
| **AI Features** | ✅ Typo correction + Synonym support | ❌ Basic search only |
| **Category Search** | ✅ Separate category results | ❌ Mixed or no categories |
| **Modern Admin** | ✅ React-based settings panel | ❌ Basic HTML forms |
| **Live Preview** | ✅ Real-time style preview | ❌ No preview |
| **Search Scope** | ✅ Title, SKU, Description, Categories | ❌ Limited fields |
| **Relevance Scoring** | ✅ Intelligent ranking algorithm | ❌ Basic relevance |
| **Out of Stock Control** | ✅ Hide/show out of stock products | ❌ No control |
| **Developer Friendly** | ✅ 15+ hooks and filters | ❌ Limited customization |

## ✨ **Unique Features**

### 🔍 **High-Performance Search Engine**
- **Single Query Optimization** - Premium-level performance approach
- **200ms Response Time** - Faster than most free alternatives
- **Smart Debouncing** - Prevents unnecessary server requests
- **Intelligent Caching** - Optimized for high-traffic stores

### 🤖 **AI-Powered Intelligence** (Optional)
- **Advanced Typo Correction** - 25+ common spelling fixes
- **Synonym Expansion** - "phone" finds "mobile", "smartphone", "cell phone"
- **Smart Query Processing** - Enhanced search understanding
- **Relevance Scoring** - Title > SKU > Description priority

### 📂 **Category Search Innovation**
- **Separate Category Results** - Categories shown independently from products
- **No Category Product Mixing** - Clean, organized results
- **Category Count Display** - Shows number of products per category
- **Optional Feature** - Enable/disable as needed

### 🎨 **Professional Interface**
- **Modern React Admin** - Professional settings experience
- **Live Style Preview** - See changes in real-time
- **Responsive Design** - Perfect on all devices
- **Customizable Everything** - Colors, borders, spacing, layout

### ⚙️ **Advanced Integration**
- **Shortcode Support** - `[nivo_search]` with custom attributes
- **Gutenberg Block** - Visual block editor integration
- **Multiple Search Scopes** - Title, SKU, Description, Short Description, Categories
- **Out of Stock Control** - Hide/show based on inventory
- **Developer Hooks** - 15+ filters and actions for customization

## 🚀 **Quick Start**

### **Installation**
1. **Upload** plugin to `/wp-content/plugins/nivo-ajax-search-for-woocommerce/`
2. **Activate** through WordPress admin
3. **Configure** in WooCommerce → NivoSearch
4. **Add** search using shortcode or Gutenberg block

### **Basic Usage**

#### **Simple Shortcode**
```php
[nivo_search]
```

#### **Advanced Shortcode**
```php
[nivo_search placeholder="Find products..." show_icon="true" style="width: 100%;"]
```

#### **Gutenberg Block**
Search "Nivo Search" in block editor → Customize in inspector panel

## 📋 **Requirements**

- **WordPress** 5.0+ (Tested up to 6.8)
- **WooCommerce** 5.0+ (Compatible with latest)
- **PHP** 7.4+ (Optimized for PHP 8.0+)
- **Modern Browser** with JavaScript enabled

## ⚙️ **Configuration Tabs**

### **General Settings**
- **AJAX Search** - Enable/disable real-time search
- **Results Limit** - Maximum results (1-50)
- **Minimum Characters** - Search trigger (1-5 chars)
- **Search Delay** - Debounce timing (100-1000ms)

### **Search Scope** (What to search)
- **Product Title** - ✅ Enabled by default
- **Product SKU** - ✅ Enabled by default  
- **Product Description** - ❌ Optional
- **Short Description** - ❌ Optional
- **Categories** - ❌ Optional
- **Exclude Out of Stock** - ❌ Optional

### **Search Bar Styling** (Live Preview)
- **Width, Colors, Borders** - Full customization
- **Padding, Radius, Alignment** - Professional styling
- **Search Icon, Placeholder** - UI elements

### **Search Results Styling** (Live Preview)
- **Show Images, Prices, SKU** - Display options
- **Border, Background, Padding** - Result styling
- **Short Description** - Additional info

### **AI Features** (Optional)
- **Typo Correction** - Fix spelling mistakes
- **Synonym Support** - Expand search terms

## 🎯 **Shortcode Attributes**

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `placeholder` | string | "Search products..." | Input placeholder text |
| `container_class` | string | "nivo-ajax-search-container" | Container CSS class |
| `input_class` | string | "nivo-search-product-search" | Input CSS class |
| `results_class` | string | "nivo-search-results" | Results CSS class |
| `show_icon` | boolean | true | Display search icon |
| `style` | string | "" | Inline CSS styles |

## 🔧 **Developer Hooks** (15+ Available)

### **PHP Filters**
```php
// Customize search arguments
add_filter('nivo_search_args', function($args, $query) {
    $args['limit'] = 20; // Custom limit
    return $args;
}, 10, 2);

// Modify search results
add_filter('nivo_search_results', function($results, $query) {
    // Add custom data
    return $results;
}, 10, 2);

// Customize individual products
add_filter('nivo_search_result_item', function($result, $product, $query) {
    $result['custom_field'] = get_post_meta($product->get_id(), 'custom', true);
    return $result;
}, 10, 3);

// Add custom typo corrections
add_filter('nivo_search_typo_corrections', function($corrections) {
    $corrections['custm'] = 'custom';
    return $corrections;
});

// Add custom synonyms
add_filter('nivo_search_synonyms', function($synonyms) {
    $synonyms['mobile'] = ['phone', 'smartphone'];
    return $synonyms;
});
```

### **JavaScript Events**
```javascript
// Search lifecycle events
document.addEventListener('nivo_search:init', function(e) {
    console.log('Search initialized');
});

document.addEventListener('nivo_search:resultsDisplayed', function(e) {
    console.log('Results:', e.detail.products);
    console.log('Categories:', e.detail.categories);
});

document.addEventListener('nivo_search:noResults', function(e) {
    console.log('No results found');
});
```

## 🏗️ **Architecture**

### **File Structure**
```
nivo-ajax-search-for-woocommerce/
├── assets/
│   ├── css/
│   │   ├── nivo-search.css
│   │   └── admin.css
│   └── js/
│       ├── nivo-search.js
│       ├── admin.js
│       ├── admin-react.js
│       └── block-editor.js
├── includes/
│   ├── admin/
│   │   └── Admin_Settings.php
│   └── classes/
│       ├── Enqueue.php
│       ├── Gutenberg_Block.php
│       ├── Nivo_Ajax_Search.php
│       ├── Search_Algorithm.php
│       └── Shortcode.php
├── vendor/ (Composer autoloader)
├── composer.json
├── readme.txt
└── nivo-ajax-search-for-woocommerce.php
```

### **Key Classes**
- **`Nivo_Ajax_Search`** - Main plugin controller (Singleton)
- **`Search_Algorithm`** - High-performance search with AI features
- **`Enqueue`** - Asset management
- **`Shortcode`** - Shortcode functionality
- **`Gutenberg_Block`** - Block editor integration
- **`Admin_Settings`** - React-based configuration interface

## 🐛 **Troubleshooting**

### **Performance Issues**
- **Slow search?** → Check if multiple search plugins are active
- **No results?** → Verify search scope settings (Title should be enabled)
- **Categories not showing?** → Enable "Search in Categories" in Search Scope

### **Styling Issues**
- **Search bar looks wrong?** → Use live preview in Search Bar settings
- **Results not styled?** → Check Search Results settings
- **Theme conflicts?** → Use browser dev tools to identify CSS conflicts

### **AI Features**
- **Typo correction not working?** → Enable in AI Features tab
- **Synonyms not expanding?** → Enable Synonym Support in AI Features
- **Want custom corrections?** → Use `nivo_search_typo_corrections` filter

## 🚀 **Performance Comparison**

| Metric | Nivo Search | Typical Free Plugin |
|--------|-------------|---------------------|
| **Search Speed** | ~200ms | ~500-1000ms |
| **Database Queries** | 1 optimized | 3-5 separate |
| **Memory Usage** | Low | Medium-High |
| **Admin Interface** | Modern React | Basic HTML |
| **Customization** | 15+ hooks | 2-3 hooks |

## 📝 **Changelog**

### **Version 1.0.0**
- ✅ **High-performance search engine** (single query optimization)
- ✅ **AI-powered features** (typo correction + synonyms)
- ✅ **Category search** with separate results
- ✅ **Modern React admin** with live preview
- ✅ **Advanced search scope** (Title, SKU, Description, Categories)
- ✅ **Out of stock control** (hide/show inventory)
- ✅ **Gutenberg block** integration
- ✅ **15+ developer hooks** for customization
- ✅ **Professional styling** options
- ✅ **Mobile responsive** design

## 📄 **License**

GPL v2 - Professional features available for free!

## 👨💻 **Author**

**Nazmun Sakib**  
🌐 [nazmunsakib.com](https://nazmunsakib.com) | 🐙 [@nazmunsakib](https://github.com/nazmunsakib)

## 🙏 **Credits**

- Performance optimization using premium techniques
- WordPress & WooCommerce communities
- React team for modern admin interface

---

⭐ **Love this plugin? Give it a star and help others discover it!**