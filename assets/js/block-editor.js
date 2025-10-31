/**
 * Gutenberg Block Editor Script
 *
 * @package NivoSearch
 * @since 1.0.0
 */

(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl, ColorPicker, ToggleControl } = wp.components;
    const { createElement: el } = wp.element;

    registerBlockType('nivo-search/ajax-search', {
        title: 'Nivo Search',
        icon: 'search',
        category: 'woocommerce',
        description: 'Add an AJAX-powered product search box',
        
        attributes: {},

        edit: function(props) {
            return el('div', {},
                el('div', {
                    style: {
                        display: 'flex',
                        alignItems: 'center',
                        gap: '8px',
                        marginBottom: '10px',
                        padding: '12px',
                        background: '#f0f0f0',
                        borderRadius: '4px',
                        border: '1px solid #ddd'
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
                    el('div', {},
                        el('strong', { style: { color: '#667eea', fontSize: '16px', display: 'block' } }, 'Nivo AJAX Search'),
                        el('p', { style: { margin: '4px 0 0 0', fontSize: '13px', color: '#666' } }, 'Styling controlled via global settings page')
                    )
                )
            );
        },

        save: function() {
            return null; // Rendered via PHP
        }
    });
})();