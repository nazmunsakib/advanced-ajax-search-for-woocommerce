/**
 * WordPress React Admin Settings
 *
 * @package AASFWC
 * @since 1.0.0
 */

const { useState, useEffect, render } = wp.element;
const { __ } = wp.i18n;

const SettingsApp = () => {
    const [settings, setSettings] = useState({});
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState('');
    const [activeTab, setActiveTab] = useState('general');

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await fetch(aasfwcAdmin.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'aasfwc_get_settings',
                    nonce: aasfwcAdmin.nonce
                })
            });
            const data = await response.json();
            if (data.success) {
                setSettings(data.data);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        } finally {
            setLoading(false);
        }
    };

    const saveSettings = async () => {
        setSaving(true);
        try {
            const response = await fetch(aasfwcAdmin.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'aasfwc_save_settings',
                    nonce: aasfwcAdmin.nonce,
                    settings: JSON.stringify(settings)
                })
            });
            const data = await response.json();
            if (data.success) {
                setMessage(data.data.message);
                setTimeout(() => setMessage(''), 3000);
            }
        } catch (error) {
            console.error('Error saving settings:', error);
        } finally {
            setSaving(false);
        }
    };

    const resetSettings = async () => {
        if (!confirm(__('Are you sure you want to reset all settings to default values?', 'advanced-ajax-search-for-woocommerce'))) {
            return;
        }
        setSaving(true);
        try {
            const response = await fetch(aasfwcAdmin.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'aasfwc_reset_settings',
                    nonce: aasfwcAdmin.nonce
                })
            });
            const data = await response.json();
            if (data.success) {
                setSettings(data.data.settings);
                setMessage(__('Settings reset to defaults successfully!', 'advanced-ajax-search-for-woocommerce'));
                setTimeout(() => setMessage(''), 3000);
            }
        } catch (error) {
            console.error('Error resetting settings:', error);
        } finally {
            setSaving(false);
        }
    };

    const updateSetting = (key, value) => {
        setSettings(prev => ({ ...prev, [key]: value }));
    };

    const renderToggle = (key, checked) => {
        return wp.element.createElement(
            'label',
            { className: 'aasfwc-toggle' },
            wp.element.createElement('input', {
                type: 'checkbox',
                checked: !!checked,
                onChange: (e) => updateSetting(key, e.target.checked ? 1 : 0)
            }),
            wp.element.createElement('span', { className: 'aasfwc-toggle-slider' })
        );
    };

    const renderRange = (key, value, min, max, step = 1) => {
        return wp.element.createElement(
            'div',
            { className: 'aasfwc-range-control' },
            wp.element.createElement('input', {
                type: 'range',
                className: 'aasfwc-range-slider',
                min: min,
                max: max,
                step: step,
                value: value || min,
                onChange: (e) => updateSetting(key, parseInt(e.target.value))
            }),
            wp.element.createElement('input', {
                type: 'number',
                className: 'aasfwc-range-value',
                min: min,
                max: max,
                value: value || min,
                onChange: (e) => updateSetting(key, parseInt(e.target.value))
            })
        );
    };

    const renderTextInput = (key, value, placeholder = '') => {
        return wp.element.createElement('input', {
            type: 'text',
            className: 'aasfwc-text-input',
            value: value || '',
            placeholder: placeholder,
            onChange: (e) => updateSetting(key, e.target.value)
        });
    };

    const renderColorPicker = (key, value) => {
        return wp.element.createElement('input', {
            type: 'color',
            className: 'aasfwc-color-picker',
            value: value || '#000000',
            onChange: (e) => updateSetting(key, e.target.value)
        });
    };

    const renderSettingRow = (label, description, control) => {
        return wp.element.createElement(
            'div',
            { className: 'aasfwc-setting-row' },
            wp.element.createElement(
                'div',
                { className: 'aasfwc-setting-info' },
                wp.element.createElement('div', { className: 'aasfwc-setting-label' }, label),
                wp.element.createElement('div', { className: 'aasfwc-setting-description' }, description)
            ),
            wp.element.createElement('div', { className: 'aasfwc-setting-control' }, control)
        );
    };

    const renderSearchBarPreview = () => {
        const searchBarStyle = {
            width: (settings.search_bar_width || 600) + 'px',
            maxWidth: '100%',
            borderRadius: (settings.border_radius || 4) + 'px',
            border: `${settings.border_width || 1}px solid ${settings.border_color || '#ddd'}`,
            backgroundColor: settings.bg_color || '#fff',
            padding: `${settings.padding_vertical || 10}px 45px`,
            margin: settings.center_align ? '0 auto' : '0',
            display: 'flex',
            alignItems: 'center',
            pointerEvents: 'none'
        };

        const searchIcon = wp.element.createElement('svg', {
            key: 'icon',
            width: '18',
            height: '18',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            strokeWidth: '2',
            strokeLinecap: 'round',
            strokeLinejoin: 'round',
            style: { marginRight: '8px', color: '#666' }
        }, [
            wp.element.createElement('circle', { key: 'c', cx: '11', cy: '11', r: '8' }),
            wp.element.createElement('path', { key: 'p', d: 'm21 21-4.35-4.35' })
        ]);

        const barChildren = [];
        if (settings.show_search_icon) {
            barChildren.push(searchIcon);
        }
        barChildren.push(wp.element.createElement('input', {
            key: 'input',
            type: 'text',
            placeholder: settings.placeholder_text || 'Search products...',
            readOnly: true,
            style: { border: 'none', outline: 'none', flex: 1, background: 'transparent', pointerEvents: 'none' }
        }));

        return wp.element.createElement(
            'div',
            { className: 'aasfwc-live-preview' },
            wp.element.createElement('h3', {}, __('Search Bar Preview', 'advanced-ajax-search-for-woocommerce')),
            wp.element.createElement('p', { className: 'aasfwc-preview-note' }, __('Preview only - not interactive', 'advanced-ajax-search-for-woocommerce')),
            wp.element.createElement(
                'div',
                { className: 'aasfwc-preview-container' },
                wp.element.createElement('div', { className: 'aasfwc-preview-search-bar', style: searchBarStyle }, barChildren)
            )
        );
    };

    const renderSearchResultsPreview = () => {
        const resultsStyle = {
            borderRadius: (settings.results_border_radius || 4) + 'px',
            border: `${settings.results_border_width || 1}px solid ${settings.results_border_color || '#ddd'}`,
            backgroundColor: settings.results_bg_color || '#fff',
            padding: `${settings.results_padding || 10}px`,
            pointerEvents: 'none'
        };

        const renderPreviewItem = (title, price, sku) => {
            if (!settings.show_images && !settings.show_price && !settings.show_sku && !settings.show_description) {
                return wp.element.createElement(
                    'div',
                    { className: 'aasfwc-preview-result-item', style: { padding: '10px', borderBottom: '1px solid #eee' } },
                    wp.element.createElement('div', { style: { fontWeight: 'bold' } }, title)
                );
            }

            const itemChildren = [];
            
            if (settings.show_images) {
                itemChildren.push(wp.element.createElement('div', { key: 'img', style: { width: '50px', height: '50px', background: '#ddd', borderRadius: '4px', flexShrink: 0 } }));
            }
            
            const infoChildren = [];
            const titleRowChildren = [];
            const titleContent = [wp.element.createElement('span', { key: 'title', style: { fontWeight: 'bold' } }, title)];
            
            if (settings.show_sku) {
                titleContent.push(wp.element.createElement('strong', { key: 'sku', style: { marginLeft: '8px', color: '#999', fontSize: '13px' } }, `(SKU: ${sku})`));
            }
            
            titleRowChildren.push(wp.element.createElement('div', { key: 'title-wrap' }, titleContent));
            
            if (settings.show_price) {
                titleRowChildren.push(wp.element.createElement('div', { key: 'price', style: { color: '#666', fontSize: '14px', fontWeight: '600' } }, price));
            }
            
            infoChildren.push(wp.element.createElement('div', { key: 'title-row', style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '4px' } }, titleRowChildren));
            
            if (settings.show_description) {
                infoChildren.push(wp.element.createElement('div', { key: 'desc', style: { color: '#666', fontSize: '13px', marginTop: '4px' } }, 'Sample product description...'));
            }
            
            itemChildren.push(wp.element.createElement('div', { key: 'info', style: { flex: 1 } }, infoChildren));
            
            return wp.element.createElement('div', { className: 'aasfwc-preview-result-item', style: { display: 'flex', gap: '10px', padding: '10px', borderBottom: '1px solid #eee' } }, itemChildren);
        };

        return wp.element.createElement(
            'div',
            { className: 'aasfwc-live-preview' },
            wp.element.createElement('h3', {}, __('Search Results Preview', 'advanced-ajax-search-for-woocommerce')),
            wp.element.createElement('p', { className: 'aasfwc-preview-note' }, __('Preview only - not interactive', 'advanced-ajax-search-for-woocommerce')),
            wp.element.createElement(
                'div',
                { className: 'aasfwc-preview-container' },
                wp.element.createElement(
                    'div',
                    { className: 'aasfwc-preview-results', style: resultsStyle },
                    renderPreviewItem('Sample Product 1', '$29.99', '001'),
                    renderPreviewItem('Sample Product 2', '$39.99', '002'),
                    renderPreviewItem('Sample Product 3', '$39.99', '003')
                )
            )
        );
    };

    if (loading) {
        return wp.element.createElement(
            'div',
            { className: 'aasfwc-loading-screen' },
            wp.element.createElement(
                'div',
                { className: 'aasfwc-loading-content' },
                wp.element.createElement('div', { className: 'aasfwc-spinner' }),
                wp.element.createElement('p', { className: 'aasfwc-loading-text' }, 'Loading settings...')
            )
        );
    }

    return wp.element.createElement(
        'div',
        { className: 'aasfwc-settings-container' },
        wp.element.createElement(
            'div',
            { className: 'aasfwc-settings-header' },
            wp.element.createElement(
                'div',
                {},
                wp.element.createElement('h1', {}, aasfwcAdmin.strings.title),
                wp.element.createElement('p', { className: 'description' }, __('Configure your AJAX search with intelligent features', 'advanced-ajax-search-for-woocommerce'))
            ),
            wp.element.createElement(
                'div',
                { style: { display: 'flex', gap: '10px' } },
                wp.element.createElement('button', { className: 'aasfwc-reset-button', disabled: saving, onClick: resetSettings }, __('Reset Settings', 'advanced-ajax-search-for-woocommerce')),
                wp.element.createElement('button', { className: 'aasfwc-save-button', disabled: saving, onClick: saveSettings }, saving ? aasfwcAdmin.strings.saving : aasfwcAdmin.strings.save)
            )
        ),

        message && wp.element.createElement(
            'div',
            { className: 'aasfwc-notice aasfwc-notice-success' },
            wp.element.createElement('span', { className: 'aasfwc-notice-icon' }, '✓'),
            wp.element.createElement('p', {}, message)
        ),

        wp.element.createElement(
            'div',
            { className: 'aasfwc-tab-nav' },
            wp.element.createElement('button', { className: activeTab === 'general' ? 'active' : '', onClick: () => setActiveTab('general') }, __('General', 'advanced-ajax-search-for-woocommerce')),
            wp.element.createElement('button', { className: activeTab === 'search' ? 'active' : '', onClick: () => setActiveTab('search') }, __('Search Scope', 'advanced-ajax-search-for-woocommerce')),
            wp.element.createElement('button', { className: activeTab === 'searchbar' ? 'active' : '', onClick: () => setActiveTab('searchbar') }, __('Search Bar', 'advanced-ajax-search-for-woocommerce')),
            wp.element.createElement('button', { className: activeTab === 'results' ? 'active' : '', onClick: () => setActiveTab('results') }, __('Search Results', 'advanced-ajax-search-for-woocommerce')),
            wp.element.createElement('button', { className: activeTab === 'ai' ? 'active' : '', onClick: () => setActiveTab('ai') }, __('AI Features', 'advanced-ajax-search-for-woocommerce'))
        ),

        wp.element.createElement(
            'div',
            { className: 'aasfwc-tab-content' + ((activeTab === 'searchbar' || activeTab === 'results') ? ' aasfwc-with-preview' : '') },
            
            activeTab === 'searchbar' && wp.element.createElement(
                'div',
                { className: 'aasfwc-preview-layout' },
                wp.element.createElement(
                    'div',
                    { className: 'aasfwc-controls-panel' },
                    wp.element.createElement('div', { className: 'aasfwc-setting-group' },
                        renderSettingRow(__('Placeholder Text', 'advanced-ajax-search-for-woocommerce'), __('Text shown in empty search field', 'advanced-ajax-search-for-woocommerce'), renderTextInput('placeholder_text', settings.placeholder_text, 'Search products...')),
                        renderSettingRow(__('Width', 'advanced-ajax-search-for-woocommerce'), __('Maximum width in pixels', 'advanced-ajax-search-for-woocommerce'), renderRange('search_bar_width', settings.search_bar_width, 200, 1200, 50)),
                        renderSettingRow(__('Border Width', 'advanced-ajax-search-for-woocommerce'), __('Border thickness', 'advanced-ajax-search-for-woocommerce'), renderRange('border_width', settings.border_width, 0, 10, 1)),
                        renderSettingRow(__('Border Color', 'advanced-ajax-search-for-woocommerce'), __('Border color', 'advanced-ajax-search-for-woocommerce'), renderColorPicker('border_color', settings.border_color)),
                        renderSettingRow(__('Border Radius', 'advanced-ajax-search-for-woocommerce'), __('Rounded corners', 'advanced-ajax-search-for-woocommerce'), renderRange('border_radius', settings.border_radius, 0, 50, 1)),
                        renderSettingRow(__('Background Color', 'advanced-ajax-search-for-woocommerce'), __('Background', 'advanced-ajax-search-for-woocommerce'), renderColorPicker('bg_color', settings.bg_color)),
                        renderSettingRow(__('Padding Vertical', 'advanced-ajax-search-for-woocommerce'), __('Top/bottom padding', 'advanced-ajax-search-for-woocommerce'), renderRange('padding_vertical', settings.padding_vertical, 0, 50, 1)),
                        renderSettingRow(__('Center Align', 'advanced-ajax-search-for-woocommerce'), __('Center the search bar', 'advanced-ajax-search-for-woocommerce'), renderToggle('center_align', settings.center_align)),
                        renderSettingRow(__('Show Search Icon', 'advanced-ajax-search-for-woocommerce'), __('Display search icon', 'advanced-ajax-search-for-woocommerce'), renderToggle('show_search_icon', settings.show_search_icon))
                    )
                ),
                renderSearchBarPreview()
            ),

            activeTab === 'results' && wp.element.createElement(
                'div',
                { className: 'aasfwc-preview-layout' },
                wp.element.createElement(
                    'div',
                    { className: 'aasfwc-controls-panel' },
                    wp.element.createElement('div', { className: 'aasfwc-setting-group' },
                        wp.element.createElement('h3', { style: { marginTop: 0 } }, __('Display Options', 'advanced-ajax-search-for-woocommerce')),
                        renderSettingRow(__('Show Thumbnail', 'advanced-ajax-search-for-woocommerce'), __('Display product images', 'advanced-ajax-search-for-woocommerce'), renderToggle('show_images', settings.show_images)),
                        renderSettingRow(__('Show Price', 'advanced-ajax-search-for-woocommerce'), __('Display product price', 'advanced-ajax-search-for-woocommerce'), renderToggle('show_price', settings.show_price)),
                        renderSettingRow(__('Show Short Description', 'advanced-ajax-search-for-woocommerce'), __('Display product excerpt', 'advanced-ajax-search-for-woocommerce'), renderToggle('show_description', settings.show_description)),
                        renderSettingRow(__('Show SKU', 'advanced-ajax-search-for-woocommerce'), __('Display product SKU', 'advanced-ajax-search-for-woocommerce'), renderToggle('show_sku', settings.show_sku)),
                        wp.element.createElement('h3', {}, __('Styling', 'advanced-ajax-search-for-woocommerce')),
                        renderSettingRow(__('Border Width', 'advanced-ajax-search-for-woocommerce'), __('Border thickness', 'advanced-ajax-search-for-woocommerce'), renderRange('results_border_width', settings.results_border_width, 0, 10, 1)),
                        renderSettingRow(__('Border Color', 'advanced-ajax-search-for-woocommerce'), __('Border color', 'advanced-ajax-search-for-woocommerce'), renderColorPicker('results_border_color', settings.results_border_color)),
                        renderSettingRow(__('Border Radius', 'advanced-ajax-search-for-woocommerce'), __('Rounded corners', 'advanced-ajax-search-for-woocommerce'), renderRange('results_border_radius', settings.results_border_radius, 0, 50, 1)),
                        renderSettingRow(__('Background Color', 'advanced-ajax-search-for-woocommerce'), __('Background', 'advanced-ajax-search-for-woocommerce'), renderColorPicker('results_bg_color', settings.results_bg_color)),
                        renderSettingRow(__('Padding', 'advanced-ajax-search-for-woocommerce'), __('Inner padding', 'advanced-ajax-search-for-woocommerce'), renderRange('results_padding', settings.results_padding, 0, 50, 1))
                    )
                ),
                renderSearchResultsPreview()
            ),

            activeTab === 'general' && wp.element.createElement('div', { className: 'aasfwc-setting-group' },
                renderSettingRow(__('Enable AJAX Search', 'advanced-ajax-search-for-woocommerce'), __('Enable real-time search', 'advanced-ajax-search-for-woocommerce'), renderToggle('enable_ajax', settings.enable_ajax)),
                renderSettingRow(__('Results Limit', 'advanced-ajax-search-for-woocommerce'), __('Maximum results', 'advanced-ajax-search-for-woocommerce'), renderRange('search_limit', settings.search_limit, 1, 50)),
                renderSettingRow(__('Minimum Characters', 'advanced-ajax-search-for-woocommerce'), __('Trigger threshold', 'advanced-ajax-search-for-woocommerce'), renderRange('min_chars', settings.min_chars, 1, 5)),
                renderSettingRow(__('Search Delay (ms)', 'advanced-ajax-search-for-woocommerce'), __('Debounce delay', 'advanced-ajax-search-for-woocommerce'), renderRange('search_delay', settings.search_delay, 100, 1000, 100)),
                wp.element.createElement('div', { className: 'aasfwc-shortcode-box', style: { background: '#f0f6fc', border: '1px solid #0073aa', borderRadius: '8px', padding: '20px', marginTop: '20px', gridColumn: '1 / -1' } },
                    wp.element.createElement('h3', { style: { margin: '0 0 10px 0', color: '#0073aa' } }, __('How to Use', 'advanced-ajax-search-for-woocommerce')),
                    wp.element.createElement('p', { style: { margin: '0 0 15px 0', color: '#646970' } }, __('Use shortcode or Gutenberg block to display the search form:', 'advanced-ajax-search-for-woocommerce')),
                    wp.element.createElement('div', { style: { marginBottom: '15px' } },
                        wp.element.createElement('strong', { style: { display: 'block', marginBottom: '8px', color: '#1d2327' } }, __('Shortcode:', 'advanced-ajax-search-for-woocommerce')),
                        wp.element.createElement('div', { style: { display: 'flex', gap: '10px', alignItems: 'center' } },
                            wp.element.createElement('code', { style: { flex: 1, background: '#fff', padding: '12px 15px', borderRadius: '4px', fontSize: '14px', fontFamily: 'monospace', border: '1px solid #ddd' } }, '[aasfwc_ajax_search]'),
                            wp.element.createElement('button', { 
                                type: 'button',
                                className: 'button button-primary',
                                style: { padding: '10px 20px' },
                                onClick: (e) => {
                                    const btn = e.target;
                                    const originalText = btn.textContent;
                                    const textarea = document.createElement('textarea');
                                    textarea.value = '[aasfwc_ajax_search]';
                                    textarea.style.position = 'fixed';
                                    textarea.style.opacity = '0';
                                    document.body.appendChild(textarea);
                                    textarea.select();
                                    try {
                                        document.execCommand('copy');
                                        btn.textContent = __('Copied!', 'advanced-ajax-search-for-woocommerce');
                                        setTimeout(() => { btn.textContent = originalText; }, 2000);
                                    } catch (err) {
                                        console.error('Copy failed:', err);
                                    }
                                    document.body.removeChild(textarea);
                                }
                            }, __('Copy', 'advanced-ajax-search-for-woocommerce'))
                        )
                    ),
                    wp.element.createElement('div', {},
                        wp.element.createElement('strong', { style: { display: 'block', marginBottom: '8px', color: '#1d2327' } }, __('Gutenberg Block:', 'advanced-ajax-search-for-woocommerce')),
                        wp.element.createElement('p', { style: { margin: 0, color: '#646970' } }, __('Search for "Advanced AJAX Search" block in the block editor.', 'advanced-ajax-search-for-woocommerce'))
                    )
                )
            ),

            activeTab === 'search' && wp.element.createElement('div', { className: 'aasfwc-setting-group' },
                renderSettingRow(__('Search in Title', 'advanced-ajax-search-for-woocommerce'), __('Search product titles', 'advanced-ajax-search-for-woocommerce'), renderToggle('search_in_title', settings.search_in_title)),
                renderSettingRow(__('Search in SKU', 'advanced-ajax-search-for-woocommerce'), __('Search product SKUs', 'advanced-ajax-search-for-woocommerce'), renderToggle('search_in_sku', settings.search_in_sku)),
                renderSettingRow(__('Search in Description', 'advanced-ajax-search-for-woocommerce'), __('Search full product descriptions', 'advanced-ajax-search-for-woocommerce'), renderToggle('search_in_content', settings.search_in_content)),
                renderSettingRow(__('Search in Short Description', 'advanced-ajax-search-for-woocommerce'), __('Search product excerpts', 'advanced-ajax-search-for-woocommerce'), renderToggle('search_in_excerpt', settings.search_in_excerpt)),
                renderSettingRow(__('Search in Categories', 'advanced-ajax-search-for-woocommerce'), __('Search categories', 'advanced-ajax-search-for-woocommerce'), renderToggle('search_in_categories', settings.search_in_categories))
            ),

            activeTab === 'ai' && wp.element.createElement('div', { className: 'aasfwc-setting-group' },
                renderSettingRow(__('Typo Correction', 'advanced-ajax-search-for-woocommerce'), __('Auto-fix spelling mistakes', 'advanced-ajax-search-for-woocommerce'), renderToggle('enable_typo_correction', settings.enable_typo_correction)),
                renderSettingRow(__('Synonym Support', 'advanced-ajax-search-for-woocommerce'), __('Expand with related terms', 'advanced-ajax-search-for-woocommerce'), renderToggle('enable_synonyms', settings.enable_synonyms))
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('aasfwc-settings-root');
    if (root) {
        render(wp.element.createElement(SettingsApp), root);
    }
});