/**
 * Admin JavaScript
 *
 * @package NivoSearch
 * @since 1.1.0
 */

// No admin JavaScript needed - settings page is static HTML
jQuery(document).ready(function ($) {
    $('.nivo-color-picker').wpColorPicker({
        defaultColor: false,
        change: function (event, ui) {
            // Propagate the chosen colour to the live preview.
            // admin-preview.js listens for native 'input' events on the settings
            // column, so we dispatch one here — more reliable than relying on the
            // iris:change jQuery event bubbling across the DOM.
            var el = event.target;
            if ( el ) {
                el.value = ui.color.toString();
                el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
            }
        },
        clear: function () { },
        hide: true,
        palettes: true
    });

});