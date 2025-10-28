/**
 * Advanced AJAX Search for WooCommerce
 * 
 * Professional JavaScript implementation with extensible architecture
 * 
 * @package AASFWC
 * @since 1.0.0
 */

(function($, window, document) {
    'use strict';
    
    /**
     * AASFWC Search Class
     * 
     * @since 1.0.0
     */
    class AAsfwcSearch {
        
        /**
         * Constructor
         * 
         * @since 1.0.0
         * @param {Object} options Configuration options
         */
        constructor(options = {}) {
            this.config = $.extend(true, {
                selectors: {
                    input: '.aasfwc-product-search',
                    results: '.aasfwc-search-results',
                    container: '.aasfwc-ajax-search-container'
                },
                classes: {
                    loading: 'aasfwc-loading',
                    hasResults: 'aasfwc-has-results',
                    noResults: 'aasfwc-no-results'
                },
                settings: {
                    minLength: aasfwc_ajax_search.settings.min_length || 2,
                    delay: aasfwc_ajax_search.settings.delay || 300,
                    maxResults: aasfwc_ajax_search.settings.max_results || 10
                },
                strings: aasfwc_ajax_search.strings || {}
            }, options);
            
            this.searchTimeout = null;
            this.currentRequest = null;
            
            this.init();
        }
        
        /**
         * Initialize the search functionality
         * 
         * @since 1.0.0
         */
        init() {
            this.bindEvents();
            this.triggerEvent('init', this);
        }
        
        /**
         * Bind event handlers
         * 
         * @since 1.0.0
         */
        bindEvents() {
            $(document).on('input', this.config.selectors.input, (e) => {
                this.handleInput(e);
            });
            
            $(document).on('focus', this.config.selectors.input, (e) => {
                this.handleFocus(e);
            });
            
            $(document).on('blur', this.config.selectors.input, (e) => {
                setTimeout(() => this.handleBlur(e), 200);
            });
        }
        
        /**
         * Handle input events
         * 
         * @since 1.0.0
         * @param {Event} e Input event
         */
        handleInput(e) {
            const $input = $(e.target);
            const query = $input.val().trim();
            const $container = $input.closest(this.config.selectors.container);
            const $results = $container.find(this.config.selectors.results);
            
            clearTimeout(this.searchTimeout);
            
            if (this.currentRequest) {
                this.currentRequest.abort();
            }
            
            if (query.length < this.config.settings.minLength) {
                this.clearResults($results, $container);
                return;
            }
            
            this.searchTimeout = setTimeout(() => {
                this.performSearch(query, $results, $container);
            }, this.config.settings.delay);
        }
        
        /**
         * Handle focus events
         * 
         * @since 1.0.0
         * @param {Event} e Focus event
         */
        handleFocus(e) {
            const $input = $(e.target);
            const $container = $input.closest(this.config.selectors.container);
            
            $container.addClass('aasfwc-focused');
            this.triggerEvent('focus', { input: $input, container: $container });
        }
        
        /**
         * Handle blur events
         * 
         * @since 1.0.0
         * @param {Event} e Blur event
         */
        handleBlur(e) {
            const $input = $(e.target);
            const $container = $input.closest(this.config.selectors.container);
            
            $container.removeClass('aasfwc-focused');
            this.triggerEvent('blur', { input: $input, container: $container });
        }
        
        /**
         * Perform AJAX search
         * 
         * @since 1.0.0
         * @param {string} query Search query
         * @param {jQuery} $results Results container
         * @param {jQuery} $container Main container
         */
        performSearch(query, $results, $container) {
            $container.addClass(this.config.classes.loading);
            
            const requestData = {
                action: 'aasfwc_live_product_search',
                query: query,
                nonce: aasfwc_ajax_search.nonce
            };
            
            // Allow filtering of request data
            this.triggerEvent('beforeSearch', { query, requestData, results: $results, container: $container });
            
            this.currentRequest = $.ajax({
                url: aasfwc_ajax_search.ajax_url,
                type: 'POST',
                data: requestData,
                success: (response) => {
                    $container.removeClass(this.config.classes.loading);
                    
                    if (response.success) {
                        this.displayResults(response.data, $results, $container, query);
                    } else {
                        this.displayError(response.data?.message || this.config.strings.error, $results, $container);
                    }
                },
                error: (xhr, status, error) => {
                    $container.removeClass(this.config.classes.loading);
                    
                    if (status !== 'abort') {
                        this.displayError(this.config.strings.error, $results, $container);
                    }
                },
                complete: () => {
                    this.currentRequest = null;
                }
            });
        }
        
        /**
         * Display search results
         * 
         * @since 1.0.0
         * @param {Array} products Product results
         * @param {jQuery} $results Results container
         * @param {jQuery} $container Main container
         * @param {string} query Search query
         */
        displayResults(products, $results, $container, query) {
            // Ensure products is an array
            if (!Array.isArray(products)) {
                products = [];
            }
            
            if (products.length === 0) {
                this.displayNoResults($results, $container);
                return;
            }
            
            let html = '<ul class="aasfwc-search-results-list">';
            
            products.forEach((product) => {
                html += this.renderProductItem(product);
            });
            
            html += '</ul>';
            
            $results.html(html);
            $container.addClass(this.config.classes.hasResults);
            
            this.triggerEvent('resultsDisplayed', { products, results: $results, container: $container, query });
        }
        
        /**
         * Render individual product item
         * 
         * @since 1.0.0
         * @param {Object} product Product data
         * @return {string} HTML string
         */
        renderProductItem(product) {
            const imageHtml = product.image ? 
                `<img src="${product.image}" alt="${product.title}" class="aasfwc-product-image">` : '';
            
            return `<li class="aasfwc-search-result-item">
                <a href="${product.url}" class="aasfwc-product-link">
                    ${imageHtml}
                    <div class="aasfwc-product-info">
                        <span class="aasfwc-product-title">${product.title}</span>
                        <span class="aasfwc-product-price">${product.price}</span>
                    </div>
                </a>
            </li>`;
        }
        
        /**
         * Display no results message
         * 
         * @since 1.0.0
         * @param {jQuery} $results Results container
         * @param {jQuery} $container Main container
         */
        displayNoResults($results, $container) {
            $results.html(`<p class="aasfwc-no-results-message">${this.config.strings.no_results}</p>`);
            $container.addClass(this.config.classes.noResults);
            
            this.triggerEvent('noResults', { results: $results, container: $container });
        }
        
        /**
         * Display error message
         * 
         * @since 1.0.0
         * @param {string} message Error message
         * @param {jQuery} $results Results container
         * @param {jQuery} $container Main container
         */
        displayError(message, $results, $container) {
            $results.html(`<p class="aasfwc-error-message">${message}</p>`);
            
            this.triggerEvent('error', { message, results: $results, container: $container });
        }
        
        /**
         * Clear results
         * 
         * @since 1.0.0
         * @param {jQuery} $results Results container
         * @param {jQuery} $container Main container
         */
        clearResults($results, $container) {
            $results.empty();
            $container.removeClass(`${this.config.classes.hasResults} ${this.config.classes.noResults}`);
            
            this.triggerEvent('resultsCleared', { results: $results, container: $container });
        }
        
        /**
         * Trigger custom event
         * 
         * @since 1.0.0
         * @param {string} eventName Event name
         * @param {Object} data Event data
         */
        triggerEvent(eventName, data = {}) {
            $(document).trigger(`aasfwc:${eventName}`, data);
        }
    }
    
    // Initialize when DOM is ready
    $(document).ready(() => {
        window.aasfwcSearch = new AAsfwcSearch();
    });
    
})(jQuery, window, document);