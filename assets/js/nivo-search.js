/**
 * Nivo AJAX Search for WooCommerce
 * 
 * Professional vanilla JavaScript implementation
 * 
 * @package NivoSearch
 * @since 1.0.0
 */

(function (window, document) {
    'use strict';

    // Configuration
    const config = {
        selectors: {
            input: '.nivo-search-product-search',
            results: '.nivo-search-results',
            container: '.nivo-ajax-search-container'
        },
        classes: {
            loading: 'nivo-search-loading',
            hasResults: 'nivo-search-has-results',
            noResults: 'nivo-search-no-results',
            focused: 'nivo-search-focused'
        },
        settings: {
            minLength: (window.nivo_search && window.nivo_search.settings.min_length) || 2,
            delay: (window.nivo_search && window.nivo_search.settings.delay) || 200,
            maxResults: (window.nivo_search && window.nivo_search.settings.max_results) || 10
        },
        strings: (window.nivo_search && window.nivo_search.strings) || {}
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
        const event = new CustomEvent(`nivo_search:${eventName}`, {
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

        if (query.length < config.settings.minLength) {
            if (currentRequest) {
                currentRequest.abort();
                currentRequest = null;
            }
            clearResults(results, container);
            return;
        }

        searchTimeout = setTimeout(() => {
            if (currentRequest) {
                currentRequest.abort();
            }
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
        const useWcAjax = window.nivo_search.wc_ajax_url;
        const ajaxUrl = useWcAjax ? window.nivo_search.wc_ajax_url : window.nivo_search.ajax_url;
        
        if (!useWcAjax) {
            formData.append('action', 'nivo_search');
            formData.append('nonce', window.nivo_search.nonce);
        }

        triggerEvent('beforeSearch', { query, results, container });

        currentRequest = new XMLHttpRequest();
        currentRequest.open('POST', ajaxUrl);

        currentRequest.onload = function () {
            removeClass(container, config.classes.loading);
            currentRequest = null;

            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
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
            currentRequest = null;
            displayError(config.strings.error, results, container);
        };

        currentRequest.onabort = function () {
            removeClass(container, config.classes.loading);
            currentRequest = null;
        };

        currentRequest.send(formData);
    }

    /**
     * Display search results
     */
    function displayResults(data, results, container, query) {
        // Handle both old format (array) and new format (object with categories/products)
        const categories = data.categories || [];
        const products = data.products || (Array.isArray(data) ? data : []);

        if (categories.length === 0 && products.length === 0) {
            displayNoResults(results, container);
            return;
        }

        const settings = window.nivo_search && window.nivo_search.settings ? window.nivo_search.settings : {};
        const resultsStyle = `
            border: ${settings.results_border_width || 1}px solid ${settings.results_border_color || '#ddd'};
            border-radius: ${settings.results_border_radius || 4}px;
            background-color: ${settings.results_bg_color || '#ffffff'};
        `;

        results.style.cssText = resultsStyle;

        let html = '';

        // Add products section first
        if (products.length > 0) {
            if (categories.length > 0) {
                html += '<div class="nivo-search-products-section">';
                html += '<h4 class="nivo-search-section-title">Products</h4>';
            } else {
                html += '<div class="nivo-search-products-section">';
            }
            html += '<ul class="nivo-search-results-list">';
            products.forEach(function (product) {
                html += renderProductItem(product, query, settings);
            });
            html += '</ul>';
            html += '</div>';
        }

        // Add categories section second
        if (categories.length > 0) {
            html += '<div class="nivo-search-categories-section">';
            html += '<h4 class="nivo-search-section-title">Categories</h4>';
            html += '<ul class="nivo-search-categories-list">';
            categories.forEach(function (category) {
                html += renderCategoryItem(category, query, settings);
            });
            html += '</ul>';
            html += '</div>';
        }

        results.innerHTML = html;
        addClass(container, config.classes.hasResults);

        triggerEvent('resultsDisplayed', { categories, products, results, container, query });
    }

    /**
     * Highlight matching keywords
     */
    function highlightKeywords(text, query) {
        if (!text || !query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<span class="nivo-search-highlight">$1</span>');
    }

    /**
     * Render individual category item
     */
    function renderCategoryItem(category, query, settings) {
        const padding = settings.results_padding || 10;
        const highlightedTitle = highlightKeywords(category.title, query);
        
        return `<li class="nivo-search-category-item" style="padding: ${padding}px;">
                <a href="${category.url}" class="nivo-search-category-link">
                    <span class="nivo-search-category-title">${highlightedTitle}</span>
                    <span class="nivo-search-category-count">(${category.count})</span>
                </a>
            </li>`;
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
            ? `<img src="${product.image}" alt="${product.title}" class="nivo-search-product-image">`
            : '';

        const highlightedTitle = highlightKeywords(product.title, query);
        const skuHtml = (showSku && product.sku) ? ` <strong>(SKU: ${product.sku})</strong>` : '';
        const priceHtml = (showPrice && product.price) ? `<span class="nivo-search-product-price">${product.price}</span>` : '';
        
        const titleSkuHtml = `<div class="nivo-search-product-title-row">
            <span class="nivo-search-product-title">${highlightedTitle}${skuHtml}</span>
            ${priceHtml}
        </div>`;

        const descHtml = (showDescription && product.short_description) 
            ? `<span class="nivo-search-product-description">${highlightKeywords(product.short_description, query)}</span>` 
            : '';

        return `<li class="nivo-search-result-item" style="padding: ${padding}px;">
                <a href="${product.url}" class="nivo-search-product-link">
                    ${imageHtml}
                    <div class="nivo-search-product-info">
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
        const settings = window.nivo_search && window.nivo_search.settings ? window.nivo_search.settings : {};
        const resultsStyle = `
            border: ${settings.results_border_width || 1}px solid ${settings.results_border_color || '#ddd'};
            border-radius: ${settings.results_border_radius || 4}px;
            background-color: ${settings.results_bg_color || '#ffffff'};
        `;
        results.style.cssText = resultsStyle;
        results.innerHTML = `<p class="nivo-search-no-results-message">${config.strings.no_results}</p>`;
        addClass(container, config.classes.noResults);
        triggerEvent('noResults', { results, container });
    }

    /**
     * Display error message
     */
    function displayError(message, results, container) {
        results.innerHTML = `<p class="nivo-search-error-message">${message}</p>`;

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

        const clearBtn = container.querySelector('.nivo-search-clear-search');
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
            if (event.target.matches && event.target.matches('.nivo-search-clear-search')) {
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
    window.nivoSearchAPI = {
        config: config,
        triggerEvent: triggerEvent
    };

})(window, document);