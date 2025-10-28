/**
 * Gutenberg Block Editor Script
 *
 * @package AASFWC
 * @since 1.0.0
 */

(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl, ColorPicker, ToggleControl } = wp.components;
    const { createElement: el } = wp.element;

    registerBlockType('aasfwc/ajax-search', {
        title: 'AJAX Product Search',
        icon: 'search',
        category: 'woocommerce',
        description: 'Add an AJAX-powered product search box',
        
        attributes: {
            placeholder: {
                type: 'string',
                default: 'Search products...'
            },
            backgroundColor: {
                type: 'string',
                default: '#ffffff'
            },
            textColor: {
                type: 'string',
                default: '#333333'
            },
            borderColor: {
                type: 'string',
                default: '#dddddd'
            },
            showIcon: {
                type: 'boolean',
                default: true
            }
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { placeholder, backgroundColor, textColor, borderColor, showIcon } = attributes;

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Search Settings', initialOpen: true },
                        el(TextControl, {
                            label: 'Placeholder Text',
                            value: placeholder,
                            onChange: (value) => setAttributes({ placeholder: value })
                        }),
                        el(ToggleControl, {
                            label: 'Show Search Icon',
                            checked: showIcon,
                            onChange: (value) => setAttributes({ showIcon: value })
                        })
                    ),
                    el(PanelBody, { title: 'Style Settings', initialOpen: false },
                        el('p', {}, 'Background Color'),
                        el(ColorPicker, {
                            color: backgroundColor,
                            onChange: (value) => setAttributes({ backgroundColor: value })
                        }),
                        el('p', {}, 'Text Color'),
                        el(ColorPicker, {
                            color: textColor,
                            onChange: (value) => setAttributes({ textColor: value })
                        }),
                        el('p', {}, 'Border Color'),
                        el(ColorPicker, {
                            color: borderColor,
                            onChange: (value) => setAttributes({ borderColor: value })
                        })
                    )
                ),
                el('div', {
                    className: 'aasfwc-ajax-search-container aasfwc-block-preview',
                    style: {
                        backgroundColor: backgroundColor,
                        border: `1px solid ${borderColor}`,
                        borderRadius: '4px',
                        padding: '10px'
                    }
                },
                    showIcon && el('span', { className: 'aasfwc-search-icon' }, '🔍'),
                    el('input', {
                        type: 'text',
                        placeholder: placeholder,
                        style: {
                            backgroundColor: backgroundColor,
                            color: textColor,
                            border: 'none',
                            outline: 'none',
                            width: '100%'
                        },
                        disabled: true
                    })
                )
            ];
        },

        save: function() {
            return null; // Rendered via PHP
        }
    });
})();