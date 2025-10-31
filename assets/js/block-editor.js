/**
 * Gutenberg Block Editor Script
 *
 * @package NASFWC
 * @since 1.0.0
 */

(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl, ColorPicker, ToggleControl } = wp.components;
    const { createElement: el } = wp.element;

    registerBlockType('NASFWC/ajax-search', {
        title: 'Nivo AJAX Search',
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
                el('div', {},
                    el('div', {
                        style: {
                            display: 'flex',
                            alignItems: 'center',
                            gap: '8px',
                            marginBottom: '10px',
                            padding: '8px',
                            background: '#f0f0f0',
                            borderRadius: '4px'
                        }
                    },
                        el('svg', {
                            width: '20',
                            height: '20',
                            viewBox: '0 0 24 24',
                            fill: 'none',
                            stroke: '#667eea',
                            strokeWidth: '2',
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round'
                        },
                            el('circle', { cx: '11', cy: '11', r: '8' }),
                            el('path', { d: 'm21 21-4.35-4.35' })
                        ),
                        el('strong', { style: { color: '#667eea', fontSize: '14px' } }, 'Nivo AJAX Search')
                    ),
                    el('div', {
                        className: 'NASFWC-ajax-search-container NASFWC-block-preview',
                        style: {
                            backgroundColor: backgroundColor,
                            border: `1px solid ${borderColor}`,
                            borderRadius: '4px',
                            padding: '10px',
                            display: 'flex',
                            alignItems: 'center'
                        }
                    },
                        showIcon && el('svg', { 
                            className: 'NASFWC-search-icon',
                            width: '18',
                            height: '18',
                            viewBox: '0 0 24 24',
                            fill: 'none',
                            stroke: 'currentColor',
                            strokeWidth: '2',
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                            style: { marginRight: '8px', color: textColor, flexShrink: 0 }
                        },
                            el('circle', { cx: '11', cy: '11', r: '8' }),
                            el('path', { d: 'm21 21-4.35-4.35' })
                        ),
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
                )
            ];
        },

        save: function() {
            return null; // Rendered via PHP
        }
    });
})();