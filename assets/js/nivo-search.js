<<<<<<<< HEAD:assets/js/nivo-search.js
/**
========
﻿/**
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
 * Nivo AJAX Search for WooCommerce
 * 
 * Professional vanilla JavaScript implementation
 * 
<<<<<<<< HEAD:assets/js/nivo-search.js
 * @package NivoSearch
========
 * @package NASFWC
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
 * @since 1.0.0
 */

(function (window, document) {
    'use strict';

    // Configuration
    const config = {
        selectors: {
<<<<<<<< HEAD:assets/js/nivo-search.js
            input: '.nivo_search-product-search',
            results: '.nivo_search-results',
            container: '.nivo-ajax-search-container'
        },
        classes: {
            loading: 'nivo_search-loading',
            hasResults: 'nivo_search-has-results',
            noResults: 'nivo_search-no-results',
            focused: 'nivo_search-focused'
        },
        settings: {
            minLength: (window.nivo_search && window.nivo_search.settings.min_length) || 2,
            delay: (window.nivo_search && window.nivo_search.settings.delay) || 300,
            maxResults: (window.nivo_search && window.nivo_search.settings.max_results) || 10
        },
        strings: (window.nivo_search && window.nivo_search.strings) || {}
========
            input: '.nasfwc-product-search',
            results: '.nasfwc-search-results',
            container: '.nasfwc-ajax-search-container'
        },
        classes: {
            loading: 'nasfwc-loading',
            hasResults: 'nasfwc-has-results',
            noResults: 'nasfwc-no-results',
            focused: 'nasfwc-focused'
        },
        settings: {
            minLength: (window.NASFWC_ajax_search && window.NASFWC_ajax_search.settings.min_length) || 2,
            delay: (window.NASFWC_ajax_search && window.NASFWC_ajax_search.settings.delay) || 300,
            maxResults: (window.NASFWC_ajax_search && window.NASFWC_ajax_search.settings.max_results) || 10
        },
        strings: (window.NASFWC_ajax_search && window.NASFWC_ajax_search.strings) || {}
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
    };

    // State
    let searchTimeout = null;
    let currentRequest = null;

    /**
     * Find closest parent element with selector
     */
    function closest(element, selector) {
        while (element && element !== document) {
            if (element.matches && element.matches(selector)) {
                return element;
            }
            element = element.parentElement;
        }
        return null;
    }

    /**
     * Add class to element
     */
    function addClass(element, className) {
        if (element && element.classList) {
            element.classList.add(className);
        }
    }

    /**
     * Remove class from element
     */
    function removeClass(element, className) {
        if (element && element.classList) {
            element.classList.remove(className);
        }
    }

    /**
     * Trigger custom event
     */
    function triggerEvent(eventName, data = {}) {
<<<<<<<< HEAD:assets/js/nivo-search.js
        const event = new CustomEvent(`nivo_search:${eventName}`, {
========
        const event = new CustomEvent(`NASFWC:${eventName}`, {
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
            detail: data,
            bubbles: true
        });
        document.dispatchEvent(event);
    }

    /**
     * Handle input events
     */
    function handleInput(event) {
        const input = event.target;
        const query = input.value.trim();
        const container = closest(input, config.selectors.container);
        const results = container ? container.querySelector(config.selectors.results) : null;

        if (!container || !results) return;

        clearTimeout(searchTimeout);

        if (currentRequest) {
            currentRequest.abort();
        }

        if (query.length < config.settings.minLength) {
            clearResults(results, container);
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query, results, container);
        }, config.settings.delay);
    }

    /**
     * Handle focus events
     */
    function handleFocus(event) {
        const input = event.target;
        const container = closest(input, config.selectors.container);

        if (container) {
            addClass(container, config.classes.focused);
            triggerEvent('focus', { input, container });
        }
    }

    /**
     * Handle blur events
     */
    function handleBlur(event) {
        const input = event.target;
        const container = closest(input, config.selectors.container);

        setTimeout(() => {
            if (container) {
                removeClass(container, config.classes.focused);
                triggerEvent('blur', { input, container });
            }
        }, 200);
    }

    /**
     * Perform AJAX search
     */
    function performSearch(query, results, container) {
        addClass(container, config.classes.loading);

        const formData = new FormData();
        formData.append('s', query);
        
        // Use WooCommerce AJAX if available
<<<<<<<< HEAD:assets/js/nivo-search.js
        const useWcAjax = window.nivo_search.wc_ajax_url;
        const ajaxUrl = useWcAjax ? window.nivo_search.wc_ajax_url : window.nivo_search.ajax_url;
        
        if (!useWcAjax) {
            formData.append('action', 'nivo_search');
            formData.append('nonce', window.nivo_search.nonce);
========
        const useWcAjax = window.NASFWC_ajax_search.wc_ajax_url;
        const ajaxUrl = useWcAjax ? window.NASFWC_ajax_search.wc_ajax_url : window.NASFWC_ajax_search.ajax_url;
        
        if (!useWcAjax) {
            formData.append('action', 'NASFWC_ajax_search');
            formData.append('nonce', window.NASFWC_ajax_search.nonce);
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
        }

        triggerEvent('beforeSearch', { query, results, container });

        currentRequest = new XMLHttpRequest();
        currentRequest.open('POST', ajaxUrl);

        currentRequest.onload = function () {
            removeClass(container, config.classes.loading);

            if (currentRequest.status === 200) {
                try {
                    const response = JSON.parse(currentRequest.responseText);
                    if (response.success) {
                        displayResults(response.data, results, container, query);
                    } else {
                        displayError(
                            (response.data && response.data.message) || config.strings.error,
                            results,
                            container
                        );
                    }
                } catch (error) {
                    displayError(config.strings.error, results, container);
                }
            } else {
                displayError(config.strings.error, results, container);
            }
        };

        currentRequest.onerror = function () {
            removeClass(container, config.classes.loading);
            displayError(config.strings.error, results, container);
        };

        currentRequest.onabort = function () {
            removeClass(container, config.classes.loading);
        };

        currentRequest.send(formData);
    }

    /**
     * Display search results
     */
    function displayResults(products, results, container, query) {
        if (!Array.isArray(products)) {
            products = [];
        }

        if (products.length === 0) {
            displayNoResults(results, container);
            return;
        }

<<<<<<<< HEAD:assets/js/nivo-search.js
        const settings = window.nivo_search && window.nivo_search.settings ? window.nivo_search.settings : {};
========
        const settings = window.NASFWC_ajax_search && window.NASFWC_ajax_search.settings ? window.NASFWC_ajax_search.settings : {};
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
        const resultsStyle = `
            border: ${settings.results_border_width || 1}px solid ${settings.results_border_color || '#ddd'};
            border-radius: ${settings.results_border_radius || 4}px;
            background-color: ${settings.results_bg_color || '#ffffff'};
        `;

        results.style.cssText = resultsStyle;

<<<<<<<< HEAD:assets/js/nivo-search.js
        let html = '<ul class="nivo_search-results-list">';
========
        let html = '<ul class="nasfwc-search-results-list">';
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js

        products.forEach(function (product) {
            html += renderProductItem(product, query, settings);
        });

        html += '</ul>';

        results.innerHTML = html;
        addClass(container, config.classes.hasResults);

        triggerEvent('resultsDisplayed', { products, results, container, query });
    }

    /**
     * Highlight matching keywords
     */
    function highlightKeywords(text, query) {
        if (!text || !query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
<<<<<<<< HEAD:assets/js/nivo-search.js
        return text.replace(regex, '<span class="nivo_search-highlight">$1</span>');
========
        return text.replace(regex, '<span class="nasfwc-highlight">$1</span>');
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
    }

    /**
     * Render individual product item
     */
    function renderProductItem(product, query, settings) {
        const showImages = settings.show_images === 1;
        const showPrice = settings.show_price === 1;
        const showSku = settings.show_sku === 1;
        const showDescription = settings.show_description === 1;
        const padding = settings.results_padding || 10;
        
        const imageHtml = (showImages && product.image)
<<<<<<<< HEAD:assets/js/nivo-search.js
            ? `<img src="${product.image}" alt="${product.title}" class="nivo_search-product-image">`
========
            ? `<img src="${product.image}" alt="${product.title}" class="nasfwc-product-image">`
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
            : '';

        const highlightedTitle = highlightKeywords(product.title, query);
        const skuHtml = (showSku && product.sku) ? ` <strong>(SKU: ${product.sku})</strong>` : '';
<<<<<<<< HEAD:assets/js/nivo-search.js
        const priceHtml = (showPrice && product.price) ? `<span class="nivo_search-product-price">${product.price}</span>` : '';
        
        const titleSkuHtml = `<div class="nivo_search-product-title-row">
            <span class="nivo_search-product-title">${highlightedTitle}${skuHtml}</span>
========
        const priceHtml = (showPrice && product.price) ? `<span class="nasfwc-product-price">${product.price}</span>` : '';
        
        const titleSkuHtml = `<div class="nasfwc-product-title-row">
            <span class="nasfwc-product-title">${highlightedTitle}${skuHtml}</span>
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
            ${priceHtml}
        </div>`;

        const descHtml = (showDescription && product.short_description) 
<<<<<<<< HEAD:assets/js/nivo-search.js
            ? `<span class="nivo_search-product-description">${highlightKeywords(product.short_description, query)}</span>` 
            : '';

        return `<li class="nivo_search-result-item" style="padding: ${padding}px;">
                <a href="${product.url}" class="nivo_search-product-link">
                    ${imageHtml}
                    <div class="nivo_search-product-info">
========
            ? `<span class="nasfwc-product-description">${highlightKeywords(product.short_description, query)}</span>` 
            : '';

        return `<li class="nasfwc-search-result-item" style="padding: ${padding}px;">
                <a href="${product.url}" class="nasfwc-product-link">
                    ${imageHtml}
                    <div class="nasfwc-product-info">
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
                        ${titleSkuHtml}
                        ${descHtml}
                    </div>
                </a>
            </li>`;
    }

    /**
     * Display no results message
     */
    function displayNoResults(results, container) {
<<<<<<<< HEAD:assets/js/nivo-search.js
        const settings = window.nivo_search && window.nivo_search.settings ? window.nivo_search.settings : {};
========
        const settings = window.NASFWC_ajax_search && window.NASFWC_ajax_search.settings ? window.NASFWC_ajax_search.settings : {};
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
        const resultsStyle = `
            border: ${settings.results_border_width || 1}px solid ${settings.results_border_color || '#ddd'};
            border-radius: ${settings.results_border_radius || 4}px;
            background-color: ${settings.results_bg_color || '#ffffff'};
        `;
        results.style.cssText = resultsStyle;
<<<<<<<< HEAD:assets/js/nivo-search.js
        results.innerHTML = `<p class="nivo_search-no-results-message">${config.strings.no_results}</p>`;
========
        results.innerHTML = `<p class="nasfwc-no-results-message">${config.strings.no_results}</p>`;
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
        addClass(container, config.classes.noResults);
        triggerEvent('noResults', { results, container });
    }

    /**
     * Display error message
     */
    function displayError(message, results, container) {
<<<<<<<< HEAD:assets/js/nivo-search.js
        results.innerHTML = `<p class="nivo_search-error-message">${message}</p>`;
========
        results.innerHTML = `<p class="nasfwc-error-message">${message}</p>`;
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js

        triggerEvent('error', { message, results, container });
    }

    /**
     * Clear results
     */
    function clearResults(results, container) {
        results.innerHTML = '';
        removeClass(container, config.classes.hasResults);
        removeClass(container, config.classes.noResults);

        triggerEvent('resultsCleared', { results, container });
    }

    /**
     * Handle clear button click
     */
    function handleClear(event) {
        const clearBtn = event.target;
        const container = closest(clearBtn, config.selectors.container);
        if (!container) return;

        const input = container.querySelector(config.selectors.input);
        const results = container.querySelector(config.selectors.results);

        if (input) {
            input.value = '';
            input.focus();
            clearBtn.style.display = 'none';
        }
        if (results) {
            clearResults(results, container);
        }
    }

    /**
     * Toggle clear button visibility
     */
    function toggleClearButton(input) {
        const container = closest(input, config.selectors.container);
        if (!container) return;

<<<<<<<< HEAD:assets/js/nivo-search.js
        const clearBtn = container.querySelector('.nivo_search-clear-search');
========
        const clearBtn = container.querySelector('.nasfwc-clear-search');
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
        if (clearBtn) {
            clearBtn.style.display = input.value.length > 0 ? 'block' : 'none';
        }
    }

    /**
     * Initialize search functionality
     */
    function init() {
        // Event delegation for input events
        document.addEventListener('input', function (event) {
            if (event.target.matches && event.target.matches(config.selectors.input)) {
                handleInput(event);
                toggleClearButton(event.target);
            }
        });

        // Event delegation for focus events
        document.addEventListener('focus', function (event) {
            if (event.target.matches && event.target.matches(config.selectors.input)) {
                handleFocus(event);
            }
        }, true);

        // Event delegation for blur events
        document.addEventListener('blur', function (event) {
            if (event.target.matches && event.target.matches(config.selectors.input)) {
                handleBlur(event);
            }
        }, true);

        // Event delegation for clear button
        document.addEventListener('click', function (event) {
<<<<<<<< HEAD:assets/js/nivo-search.js
            if (event.target.matches && event.target.matches('.nivo_search-clear-search')) {
========
            if (event.target.matches && event.target.matches('.nasfwc-clear-search')) {
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
                handleClear(event);
            }
        });

        triggerEvent('init');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose public API
<<<<<<<< HEAD:assets/js/nivo-search.js
    window.nivo_searchSearch = {
========
    window.NASFWCSearch = {
>>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58:assets/js/nasfwc-search.js
        config: config,
        triggerEvent: triggerEvent
    };

})(window, document);