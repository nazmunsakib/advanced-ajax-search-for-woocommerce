/**
 * Admin JavaScript
 *
 * @package NASFWC
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Toggle AI settings based on AI enable checkbox
    $('input[name="NASFWC_enable_ai"]').on('change', function() {
        const $aiSettings = $('.NASFWC-ai-settings');
        if ($(this).is(':checked')) {
            $aiSettings.show();
        } else {
            $aiSettings.hide();
        }
    }).trigger('change');
    

    
    // Settings form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Validate search limit
        const searchLimit = parseInt($('input[name="NASFWC_search_limit"]').val());
        if (searchLimit < 1 || searchLimit > 50) {
            alert('Search limit must be between 1 and 50');
            isValid = false;
        }
        
        // Validate minimum characters
        const minChars = parseInt($('input[name="NASFWC_min_chars"]').val());
        if (minChars < 1 || minChars > 5) {
            alert('Minimum characters must be between 1 and 5');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});