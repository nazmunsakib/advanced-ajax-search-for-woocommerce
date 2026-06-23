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
            minLength: 2,
            delay: 200,
            maxResults: 10
        },
        strings: (window.nivo_search && window.nivo_search.strings) || {}
    };

    const DEFAULT_MIN_LENGTH = 2;
    const DEFAULT_DELAY = 200;

    /** localStorage key for search history. */
    const HISTORY_KEY    = 'nivo_search_history';
    /** Maximum number of entries to keep in history. */
    const HISTORY_LIMIT  = 8;

    // State
    let searchTimeout = null;
    let currentRequest = null;
    /** Set to true when the user explicitly clicks a "Did you mean?" suggestion.
     *  Prevents the corrected search from showing the banner again. */
    let suppressDidYouMean = false;

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
     * Get settings for a specific container
     */
    function getContainerSettings(container) {
        let minLength = DEFAULT_MIN_LENGTH;
        let delay = DEFAULT_DELAY;

        // Check for preset settings
        const presetData = container.getAttribute('data-preset-settings');
        if (presetData) {
            try {
                const presetSettings = JSON.parse(presetData);
                if (presetSettings.min_chars !== undefined && presetSettings.min_chars !== '') {
                    minLength = parseInt(presetSettings.min_chars, 10);
                }
                // Delay might not be in preset settings usually, but if added later:
                if (presetSettings.delay !== undefined && presetSettings.delay !== '') {
                    delay = parseInt(presetSettings.delay, 10);
                }
            } catch (e) {
                // Silently fail and use defaults
            }
        }
        return { minLength, delay };
    }

    // ── Search History ─────────────────────────────────────────────────────

    /**
     * Read history from localStorage. Returns [] when storage is unavailable.
     */
    function historyGet() {
        try {
            return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
        } catch(e) {
            return [];
        }
    }

    /**
     * Prepend a query to history, deduplicate, trim to HISTORY_LIMIT.
     */
    function historySave(query) {
        if (!query || query.length < 2) return;
        try {
            const current = historyGet().filter(function(q) {
                return q.toLowerCase() !== query.toLowerCase();
            });
            current.unshift(query);
            localStorage.setItem(HISTORY_KEY, JSON.stringify(current.slice(0, HISTORY_LIMIT)));
        } catch(e) { /* storage unavailable */ }
    }

    /**
     * Remove a single entry from history.
     */
    function historyRemove(query) {
        try {
            const current = historyGet().filter(function(q) { return q !== query; });
            localStorage.setItem(HISTORY_KEY, JSON.stringify(current));
        } catch(e) {}
    }

    /**
     * Clear all history.
     */
    function historyClear() {
        try { localStorage.removeItem(HISTORY_KEY); } catch(e) {}
    }

    /**
     * Build and show the history panel inside the results element.
     * Called when the input is focused and empty.
     */
    function showHistory(results, container) {
        const history = historyGet();
        if (history.length === 0) return;

        const clearLabel = (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.clear_history)
            || 'Clear history';
        const recentLabel = (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.recent_searches)
            || 'Recent searches';

        let html = '<div class="nivo-search-history">';
        html += '<div class="nivo-search-history__header">';
        html += '<span class="nivo-search-history__title">' + escapeHtml(recentLabel) + '</span>';
        html += '<button type="button" class="nivo-search-history__clear" aria-label="' + escapeHtml(clearLabel) + '">' + escapeHtml(clearLabel) + '</button>';
        html += '</div>';
        html += '<ul class="nivo-search-history__list">';
        history.forEach(function(query) {
            html += '<li class="nivo-search-history__item">';
            html += '<button type="button" class="nivo-search-history__query" data-query="' + escapeHtml(query) + '">';
            html += '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
            html += escapeHtml(query);
            html += '</button>';
            html += '<button type="button" class="nivo-search-history__remove" data-query="' + escapeHtml(query) + '" aria-label="Remove">';
            html += '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            html += '</button>';
            html += '</li>';
        });
        html += '</ul></div>';

        results.innerHTML = html;
        addClass(container, config.classes.hasResults);

        // Bind interactions
        const clearBtn = results.querySelector('.nivo-search-history__clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                historyClear();
                clearResults(results, container);
            });
        }

        results.querySelectorAll('.nivo-search-history__query').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const q = btn.getAttribute('data-query');
                const input = container.querySelector(config.selectors.input);
                if (input && q) {
                    input.value = q;
                    performSearch(q, results, container);
                }
            });
        });

        results.querySelectorAll('.nivo-search-history__remove').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const q = btn.getAttribute('data-query');
                historyRemove(q);
                // Re-render history panel (or close if empty now)
                if (historyGet().length === 0) {
                    clearResults(results, container);
                } else {
                    showHistory(results, container);
                }
            });
        });
    }

    // ── End Search History ─────────────────────────────────────────────────

    /**
     * Handle input events
     */
    function handleInput(event) {
        const input = event.target;
        const query = input.value.trim();
        const container = closest(input, config.selectors.container);
        const results = container ? container.querySelector(config.selectors.results) : null;

        if (!container || !results) return;

        const containerSettings = getContainerSettings(container);

        clearTimeout(searchTimeout);

        if (query.length < containerSettings.minLength) {
            if (currentRequest) {
                currentRequest.abort();
                currentRequest = null;
            }
            // If the input was fully cleared, show history; otherwise clear panel.
            if (query.length === 0) {
                showHistory(results, container);
            } else {
                clearResults(results, container);
            }
            return;
        }

        searchTimeout = setTimeout(() => {
            if (currentRequest) {
                currentRequest.abort();
            }
            performSearch(query, results, container);
        }, containerSettings.delay);
    }

    /**
     * Handle focus events
     */
    function handleFocus(event) {
        const input = event.target;
        const container = closest(input, config.selectors.container);

        if (container) {
            // On mobile: intercept focus and open fullscreen overlay.
            if (isMobile() && !mobileOverlayOpen) {
                // Briefly blur so the native keyboard appears inside the overlay instead.
                input.blur();
                openMobileOverlay(container);
                return;
            }

            addClass(container, config.classes.focused);
            triggerEvent('focus', { input, container });

            const query = input.value.trim();
            const results = container.querySelector(config.selectors.results);
            const containerSettings = getContainerSettings(container);

            if (query.length === 0 && results) {
                // Input is empty — show history panel if history exists.
                showHistory(results, container);
            } else if (query.length >= containerSettings.minLength && results) {
                if (results.innerHTML.trim() !== '' && !results.querySelector('.nivo-search-history')) {
                    // Soft open: Restore view if we have cached (non-history) results.
                    if (results.querySelector('.nivo-search-no-results-message')) {
                        addClass(container, config.classes.noResults);
                    } else {
                        addClass(container, config.classes.hasResults);
                    }
                    // Restore close icon
                    const loaderIcons = container.querySelector('.nivo-search-loader-icons');
                    if (loaderIcons) addClass(loaderIcons, 'nivo-search-close');
                } else {
                    // No cached results, perform new search
                    performSearch(query, results, container);
                }
            }
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
                // removeClass(container, config.classes.focused); // Keep focus class if we want, or remove. 
                // Usually blur removes focus style, but let's keep specific logic simple.
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

        // Get preset ID from container
        const presetId = container.getAttribute('data-preset-id');
        if (presetId) {
            formData.append('preset_id', presetId);
        }

        // Pass current language for WPML / Polylang filtering on the server side.
        const lang = window.nivo_search && window.nivo_search.lang;
        if (lang) formData.append('lang', lang);

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
    /**
     * Fire a Google Analytics / GTM search event.
     * Supports GA4 (gtag), Universal Analytics (ga), and GTM dataLayer.
     * Only fires when ga_tracking is enabled in admin settings.
     *
     * @param {string} query  - The search term.
     * @param {number} count  - Number of results returned.
     */
    function trackSearchEvent(query, count) {
        if (!window.nivo_search || !window.nivo_search.ga_tracking) return;
        // GA4 / gtag.js
        if (typeof window.gtag === 'function') {
            window.gtag('event', 'search', { search_term: query, results_count: count });
        }
        // Universal Analytics
        if (typeof window.ga === 'function') {
            window.ga('send', 'event', 'NivoSearch', 'search', query, count);
        }
        // Google Tag Manager dataLayer
        if (Array.isArray(window.dataLayer)) {
            window.dataLayer.push({ event: 'nivo_search', search_term: query, results_count: count });
        }
        // Fire a native JS event so custom tracking can hook in.
        document.dispatchEvent(new CustomEvent('nivoSearch', { detail: { query: query, count: count } }));
    }

    function displayResults(data, results, container, query) {
        // Handle both old format (array) and new format (object with categories/products)
        const categories = data.categories || [];
        const products = data.products || (Array.isArray(data) ? data : []);

        // Fire GA/GTM tracking event.
        trackSearchEvent(query, categories.length + products.length);

        const clearBtn = container.querySelector('.nivo-search-loader-icons');
        if (clearBtn) {
            addClass(clearBtn, 'nivo-search-close'); // Show close icon when results display
        }

        if (categories.length === 0 && products.length === 0) {
            // Only show the suggestion if the user hasn't already clicked one.
            if (data.did_you_mean && !suppressDidYouMean) {
                displayNoResultsWithSuggestion(results, container, query, data.did_you_mean);
            } else {
                displayNoResults(results, container);
            }
            suppressDidYouMean = false; // reset after every display
            return;
        }

        const globalSettings = window.nivo_search && window.nivo_search.settings ? window.nivo_search.settings : {};
        // Prioritize settings from response (preset), fallback to global
        const settings = data.settings ? Object.assign({}, globalSettings, data.settings) : globalSettings;

        // Always cast boolean display flags — PHP serialises them as "0"/"1" strings.
        [
            'show_images', 'show_price', 'show_sku', 'show_description',
            'show_stock_status', 'show_category_badge',
            'show_add_to_cart', 'show_qty_selector', 'show_view_all'
        ].forEach(key => {
            if (settings[key] !== undefined) {
                settings[key] = parseInt(settings[key], 10);
            }
        });

        // "Did you mean?" suggestion banner (typo correction from fuzzy engine).
        // Suppressed when the user explicitly clicked a suggestion — avoids the
        // banner reappearing immediately after accepting the correction.
        let html = '';
        if (data.did_you_mean && !suppressDidYouMean) {
            const didYouMeanTemplate = (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.did_you_mean)
                ? window.nivo_search.strings.did_you_mean
                : 'Did you mean: %s?';
            const suggestionLabel = didYouMeanTemplate.replace(
                '%s',
                '<a href="#" class="nivo-did-you-mean-link" data-query="' + escapeHtml(data.did_you_mean) + '">' + escapeHtml(data.did_you_mean) + '</a>'
            );
            html += '<div class="nivo-did-you-mean">' + suggestionLabel + '</div>';
        }
        suppressDidYouMean = false; // reset after every display

        // Scrollable content wrapper — view-all sits outside this so it stays at the bottom
        html += '<div class="nivo-search-scrollable">';

        // Add categories section first
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

        // Add tags section second
        const tags = data.tags || [];
        if (tags.length > 0) {
            html += '<div class="nivo-search-tags-section">';
            html += '<h4 class="nivo-search-section-title">Tags</h4>';
            html += '<ul class="nivo-search-tags-list">';
            tags.forEach(function (tag) {
                html += renderTagItem(tag, query, settings);
            });
            html += '</ul>';
            html += '</div>';
        }

        // Add products section third
        if (products.length > 0) {
            if (categories.length > 0 || tags.length > 0) {
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

        // Close scrollable wrapper
        html += '</div>';

        // View All Results footer link — outside scrollable, always at bottom
        const showViewAll = settings.show_view_all === undefined ? 1 : settings.show_view_all;
        if (showViewAll !== 0) {
            const shopUrl = (window.nivo_search && window.nivo_search.shop_url) || '/?s=' + encodeURIComponent(query) + '&post_type=product';
            const viewAllUrl = shopUrl.indexOf('?') !== -1
                ? shopUrl + '&s=' + encodeURIComponent(query)
                : shopUrl + '?s=' + encodeURIComponent(query);
            const viewAllLabel = (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.view_all)
                ? window.nivo_search.strings.view_all.replace('%s', escapeHtml(query))
                : 'View all results for "' + escapeHtml(query) + '"';
            html += '<div class="nivo-search-view-all"><a href="' + escapeHtml(viewAllUrl) + '" class="nivo-search-view-all-link">' + viewAllLabel + '</a></div>';
        }

        results.innerHTML = html;
        addClass(container, config.classes.hasResults);

        // Save to search history after successful results.
        historySave(query);

        // Bind add-to-cart buttons (after innerHTML is set)
        bindAddToCartButtons(results);

        // Bind "Did you mean?" link click to re-run search with the suggestion.
        const didYouMeanLinks = results.querySelectorAll('.nivo-did-you-mean-link');
        const isMobilePanel = results.classList.contains('nivo-mobile-results');

        didYouMeanLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const suggestedQuery = link.getAttribute('data-query');
                if (!suggestedQuery) return;

                // Update the visible input — desktop OR mobile overlay input.
                if (isMobilePanel) {
                    // Mobile overlay: cloned input is inside .nivo-mobile-search-wrap
                    const mobileWrap = document.querySelector('.nivo-mobile-search-wrap');
                    if (mobileWrap) {
                        const mobileInput = mobileWrap.querySelector(config.selectors.input);
                        if (mobileInput) mobileInput.value = suggestedQuery;
                    }
                } else {
                    const input = container.querySelector(config.selectors.input);
                    if (input) input.value = suggestedQuery;
                }

                triggerEvent('didYouMeanClicked', { original: query, suggestion: suggestedQuery });

                // Prevent the corrected search from showing "Did you mean?" again.
                suppressDidYouMean = true;

                // Fade the banner out, then run the corrected search.
                const banner = link.closest('.nivo-did-you-mean');
                const runSearch = function() {
                    if (isMobilePanel) {
                        // Stay inside the mobile overlay flow.
                        const origContainer = mobileSourceInput
                            ? mobileSourceInput.closest(config.selectors.container)
                            : container;
                        performMobileSearch(suggestedQuery, results, origContainer || container, null);
                    } else {
                        performSearch(suggestedQuery, results, container);
                    }
                };

                if (banner) {
                    banner.classList.add('nivo-did-you-mean--hiding');
                    setTimeout(runSearch, 260);
                } else {
                    runSearch();
                }
            });
        });

        triggerEvent('resultsDisplayed', { categories, products, results, container, query });
    }

    /**
     * Escape HTML special characters
     */
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /**
     * Highlight matching keywords
     */
    function highlightKeywords(text, query) {
        if (!text) return '';
        const escapedText = escapeHtml(text);
        if (!query) return escapedText;

        // Escape query to match against escaped text and avoid regex injection
        const escapedQuery = escapeHtml(query).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return escapedText.replace(regex, '<span class="nivo-search-highlight">$1</span>');
    }

    /**
     * Render individual tag item
     */
    function renderTagItem(tag, query, settings) {
        const highlightedTitle = highlightKeywords(tag.title, query);

        return `<li class="nivo-search-tag-item">
                <a href="${escapeHtml(tag.url)}" class="nivo-search-tag-link">
                    <span class="nivo-search-tag-title">${highlightedTitle}</span>
                    <span class="nivo-search-tag-count">(${escapeHtml(tag.count)})</span>
                </a>
            </li>`;
    }

    /**
     * Render individual category item
     */
    function renderCategoryItem(category, query, settings) {
        const highlightedTitle = highlightKeywords(category.title, query);

        return `<li class="nivo-search-category-item">
                <a href="${escapeHtml(category.url)}" class="nivo-search-category-link">
                    <span class="nivo-search-category-title">${highlightedTitle}</span>
                    <span class="nivo-search-category-count">(${escapeHtml(category.count)})</span>
                </a>
            </li>`;
    }

    /**
     * Render stock status badge HTML
     */
    function renderStockBadge(status) {
        const labels = {
            instock:     (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.in_stock)     || 'In Stock',
            outofstock:  (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.out_of_stock)  || 'Out of Stock',
            onbackorder: (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.on_backorder)  || 'On Backorder',
        };
        const label = labels[status] || labels.instock;
        return '<span class="nivo-stock-badge nivo-stock-' + escapeHtml(status) + '">' + escapeHtml(label) + '</span>';
    }

    /**
     * Render individual product item
     */
    function renderProductItem(product, query, settings) {
        // All flags are integers (1/0) after the cast in displayResults().
        const showImages      = settings.show_images       === 1;
        const showPrice       = settings.show_price        === 1;
        const showSku         = settings.show_sku          === 1;
        const showDescription = settings.show_description  === 1;
        const showStock       = settings.show_stock_status === 1;
        const showCatBadge    = settings.show_category_badge === 1;
        const showAddToCart   = settings.show_add_to_cart  === 1;
        const showQty         = settings.show_qty_selector === 1;

        // Image — wrap in link only when there is an actual image to show.
        // An empty <a> would still occupy space in the flex row.
        const imageLinkHtml = (showImages && product.image)
            ? `<a href="${escapeHtml(product.url)}" class="nivo-search-product-link" tabindex="-1" aria-hidden="true"><img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.title)}" class="nivo-search-product-image" loading="lazy" width="60" height="60"></a>`
            : '';

        // Title (with inline SKU immediately after title text)
        const highlightedTitle = highlightKeywords(product.title, query);
        const skuInline = (showSku && product.sku)
            ? `<span class="nivo-search-product-sku">SKU: ${highlightKeywords(product.sku, query)}</span>`
            : '';

        // Price — current selling price only (no strikethrough regular/sale HTML)
        const priceHtml = (showPrice && product.current_price)
            ? `<span class="nivo-search-product-price">${product.current_price}</span>`
            : '';

        // Description
        const descHtml = (showDescription && product.short_description)
            ? `<span class="nivo-search-product-description">${highlightKeywords(product.short_description, query)}</span>`
            : '';


        // Stock badge
        const stockHtml = showStock
            ? renderStockBadge(product.stock_status || 'instock')
            : '';

        // Category badges (max 2)
        let catBadgesHtml = '';
        if (showCatBadge && product.categories && product.categories.length > 0) {
            catBadgesHtml = '<span class="nivo-search-category-badges">';
            product.categories.forEach(function(cat) {
                catBadgesHtml += `<a href="${escapeHtml(cat.url)}" class="nivo-cat-badge">${escapeHtml(cat.name)}</a>`;
            });
            catBadgesHtml += '</span>';
        }

        // Add to Cart button + quantity selector
        let addToCartHtml = '';
        if (showAddToCart && product.is_purchasable && product.is_in_stock) {
            const qtyHtml = (showQty && product.product_type === 'simple')
                ? `<span class="nivo-qty-wrapper">
                    <button type="button" class="nivo-qty-btn nivo-qty-minus" aria-label="Decrease quantity">−</button>
                    <input type="number" class="nivo-qty-input" value="1" min="1" max="99" aria-label="Quantity">
                    <button type="button" class="nivo-qty-btn nivo-qty-plus" aria-label="Increase quantity">+</button>
                   </span>`
                : '';

            const isVariable = product.product_type === 'variable';
            // Cart icon SVG — used for simple product add-to-cart buttons.
            const cartIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>`;
            // Checkmark icon for the "added" state.
            const checkIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="20 6 9 17 4 12"/></svg>`;
            // Chevron-right icon — used for variable products (go to product page).
            const arrowIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"/></svg>`;

            if (isVariable) {
                // Variables: chevron-right links to product page to select options.
                addToCartHtml = `<span class="nivo-add-to-cart-wrapper"><a href="${escapeHtml(product.url)}"
                    class="nivo-add-to-cart-btn nivo-atc-icon nivo-atc-variable"
                    title="Select options"
                    aria-label="Select options for ${escapeHtml(product.title)}"
                >${arrowIcon}</a></span>`;
            } else {
                addToCartHtml = `<span class="nivo-add-to-cart-wrapper">${qtyHtml}<button type="button"
                    class="nivo-add-to-cart-btn nivo-atc-icon"
                    data-product-id="${escapeHtml(String(product.id))}"
                    data-atc-url="${escapeHtml(product.add_to_cart_url)}"
                    data-product-url="${escapeHtml(product.url)}"
                    data-nonce="${escapeHtml(product.add_to_cart_nonce)}"
                    data-cart-icon="${cartIcon.replace(/"/g, '&quot;')}"
                    data-check-icon="${checkIcon.replace(/"/g, '&quot;')}"
                    title="Add to cart"
                    aria-label="Add ${escapeHtml(product.title)} to cart"
                >${cartIcon}</button></span>`;
            }
        } else if (showAddToCart && !product.is_in_stock) {
            addToCartHtml = `<span class="nivo-out-of-stock-label">${escapeHtml((window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.out_of_stock) || 'Out of Stock')}</span>`;
        }

        // ---- Two-column layout ----
        // [img] | [LEFT: title → desc → badges] | [RIGHT: price / qty+cart]
        //
        // Left and right are separate siblings — nothing from the right
        // column sits inside the left info column.

        const chips = [stockHtml, catBadgesHtml].filter(Boolean).join('');
        const chipsRow = chips ? `<div class="nivo-search-meta-chips">${chips}</div>` : '';

        // Right column: price on top, qty+cart below
        const actionsHtml = (priceHtml || addToCartHtml)
            ? `<div class="nivo-search-product-actions">${priceHtml}${addToCartHtml}</div>`
            : '';

        return `<li class="nivo-search-result-item">
                ${imageLinkHtml}
                <div class="nivo-search-product-info">
                    <a href="${escapeHtml(product.url)}" class="nivo-search-product-title-link">
                        <span class="nivo-search-product-title">${highlightedTitle}</span>${skuInline}
                    </a>
                    ${descHtml}
                    ${chipsRow}
                </div>
                ${actionsHtml}
            </li>`;
    }

    /**
     * Bind add-to-cart AJAX behaviour to buttons inside a results panel.
     */
    function bindAddToCartButtons(resultsEl) {
        // Quantity selector
        resultsEl.querySelectorAll('.nivo-qty-minus').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const input = btn.parentElement.querySelector('.nivo-qty-input');
                if (input) input.value = Math.max(1, parseInt(input.value, 10) - 1);
            });
        });
        resultsEl.querySelectorAll('.nivo-qty-plus').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const input = btn.parentElement.querySelector('.nivo-qty-input');
                if (input) input.value = Math.min(99, parseInt(input.value, 10) + 1);
            });
        });

        // Add to cart
        resultsEl.querySelectorAll('.nivo-add-to-cart-btn[data-product-id]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const productId  = btn.getAttribute('data-product-id');
                const atcUrl     = btn.getAttribute('data-atc-url');    // ?add-to-cart=X
                const productUrl = btn.getAttribute('data-product-url'); // product permalink
                const nonce      = btn.getAttribute('data-nonce');
                const wrapper    = btn.closest('.nivo-add-to-cart-wrapper');
                const qtyInput   = wrapper ? wrapper.querySelector('.nivo-qty-input') : null;
                const qty        = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;

                btn.classList.add('nivo-atc-loading');
                btn.disabled = true;
                // Show spinner while loading (save cart icon to restore later).
                const spinnerSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>`;
                btn.innerHTML = spinnerSvg;

                // Resolve WooCommerce AJAX add-to-cart URL.
                // Prefer wc_cart_params (WC's own localized template), then our
                // nivo_search.wc_cart_ajax_url, and fall back to redirect.
                let wcAjaxUrl = null;
                if (window.wc_cart_params && window.wc_cart_params.ajax_url) {
                    wcAjaxUrl = window.wc_cart_params.ajax_url.replace('%%endpoint%%', 'add_to_cart');
                } else if (window.nivo_search && window.nivo_search.wc_cart_ajax_url) {
                    wcAjaxUrl = window.nivo_search.wc_cart_ajax_url.replace('%%endpoint%%', 'add_to_cart');
                }

                if (wcAjaxUrl) {
                    // WC wc-ajax=add_to_cart only needs product_id + quantity.
                    // DO NOT send 'add-to-cart' — it triggers the standard WC hook
                    // (woocommerce_add_to_cart_action on wp_loaded) in addition to
                    // the AJAX handler, causing the product to be added twice.
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    formData.append('quantity',   qty);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', wcAjaxUrl);
                    xhr.onload = function() {
                        btn.classList.remove('nivo-atc-loading');
                        btn.disabled = false;
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res && res.error) {
                                // WC returned an error — send to product page.
                                window.location.href = productUrl;
                            } else {
                                // Success — show checkmark icon.
                                btn.classList.add('nivo-atc-added');
                                const checkIcon = btn.getAttribute('data-check-icon') || '✓';
                                btn.innerHTML = checkIcon;

                                // Update WooCommerce mini-cart fragments.
                                // WooCommerce's cart widgets listen to jQuery's
                                // 'added_to_cart' event on document.body.
                                if (res && res.fragments) {
                                    if (window.jQuery) {
                                        // Standard WC fragment injection + event.
                                        window.jQuery.each(res.fragments, function(key, value) {
                                            window.jQuery(key).replaceWith(value);
                                        });
                                        window.jQuery(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash]);
                                        window.jQuery(document.body).trigger('wc_fragments_refreshed');
                                    } else {
                                        // Non-jQuery fallback: inject fragments manually.
                                        Object.keys(res.fragments).forEach(function(selector) {
                                            const el = document.querySelector(selector);
                                            if (el) el.outerHTML = res.fragments[selector];
                                        });
                                        document.body.dispatchEvent(new CustomEvent('wc_fragment_refresh'));
                                    }
                                }

                                triggerEvent('addedToCart', { productId, qty });

                                // Revert to cart icon after 2 s.
                                setTimeout(function() {
                                    btn.classList.remove('nivo-atc-added');
                                    const cartIcon = btn.getAttribute('data-cart-icon') || '';
                                    btn.innerHTML = cartIcon;
                                }, 2000);
                            }
                        } catch(err) {
                            // Unexpected response — fall back to redirect with quantity.
                            window.location.href = atcUrl + (atcUrl.indexOf('?') !== -1 ? '&' : '?') + 'quantity=' + qty;
                        }
                    };
                    xhr.onerror = function() {
                        btn.classList.remove('nivo-atc-loading');
                        btn.disabled = false;
                        window.location.href = atcUrl + (atcUrl.indexOf('?') !== -1 ? '&' : '?') + 'quantity=' + qty;
                    };
                    xhr.send(formData);
                } else {
                    // No WC AJAX — redirect to add-to-cart URL with quantity.
                    window.location.href = atcUrl + (atcUrl.indexOf('?') !== -1 ? '&' : '?') + 'quantity=' + qty;
                }
            });
        });
    }

    /**
     * Display no results message
     */
    function displayNoResults(results, container) {
        results.innerHTML = `<p class="nivo-search-no-results-message">${config.strings.no_results}</p>`;
        addClass(container, config.classes.noResults);
        triggerEvent('noResults', { results, container });
    }

    /**
     * Display a no-results message combined with a "Did you mean?" suggestion.
     * Clicking the suggestion re-runs the search with the corrected query.
     *
     * @param {Element} results     The results panel element.
     * @param {Element} container   The search widget container.
     * @param {string}  query       The original (uncorrected) query the user typed.
     * @param {string}  suggestion  The corrected query suggested by the engine.
     */
    function displayNoResultsWithSuggestion(results, container, query, suggestion) {
        const didYouMeanTemplate = (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.did_you_mean)
            ? window.nivo_search.strings.did_you_mean
            : 'Did you mean: %s?';

        const suggestionLink =
            '<a href="#" class="nivo-did-you-mean-link" data-query="' + escapeHtml(suggestion) + '">' +
            escapeHtml(suggestion) +
            '</a>';

        const suggestionLabel = didYouMeanTemplate.replace('%s', suggestionLink);

        results.innerHTML =
            '<p class="nivo-search-no-results-message">' + config.strings.no_results + '</p>' +
            '<div class="nivo-did-you-mean nivo-did-you-mean--no-results">' + suggestionLabel + '</div>';

        addClass(container, config.classes.noResults);
        triggerEvent('noResults', { results, container, suggestion });
    }

    /**
     * Display error message
     */
    function displayError(message, results, container) {
        results.innerHTML = `<p class="nivo-search-error-message">${message}</p>`;
        // Make the panel visible so the error message is actually seen.
        addClass(container, config.classes.noResults);

        triggerEvent('error', { message, results, container });
    }

    /**
     * Clear results (Hard Clear)
     */
    function clearResults(results, container) {
        results.innerHTML = '';
        removeClass(container, config.classes.hasResults);
        removeClass(container, config.classes.noResults);

        // Hide close button when clearing results
        const loaderIcons = container.querySelector('.nivo-search-loader-icons');
        if (loaderIcons) {
            removeClass(loaderIcons, 'nivo-search-close');
        }

        triggerEvent('resultsCleared', { results, container });
    }

    /**
     * Handle clear button click
     */
    function handleClear(event) {
        // The event target might be the icon itself or the container, normalize to the button wrapper if needed
        const clearBtn = event.target;
        const container = closest(clearBtn, config.selectors.container);
        if (!container) return;

        const input = container.querySelector(config.selectors.input);
        const results = container.querySelector(config.selectors.results);

        if (input) {
            input.value = '';
            input.focus();
        }
        if (results) {
            clearResults(results, container);
        }
    }

    /**
     * Toggle clear button visibility
     * @deprecated Icon visibility is now handled by results display state
     */
    function toggleClearButton(input) {
        // Logic moved to displayResults and clearResults
    }

    /**
     * Handle click outside to close results (Soft Close)
     */
    function handleClickOutside(event) {
        // If click is inside any search container, ignore
        if (closest(event.target, config.selectors.container)) {
            return;
        }

        // Close all open search results (Soft Close - maintain HTML)
        const containers = document.querySelectorAll(config.selectors.container);
        containers.forEach(container => {
            const results = container.querySelector(config.selectors.results);
            if (results && (container.classList.contains(config.classes.hasResults) || container.classList.contains(config.classes.noResults))) {

                // Just remove visibility classes, DO NOT clear innerHTML
                removeClass(container, config.classes.hasResults);
                removeClass(container, config.classes.noResults);
                removeClass(container, config.classes.focused);

                // Hide close icon
                const loaderIcons = container.querySelector('.nivo-search-loader-icons');
                if (loaderIcons) {
                    removeClass(loaderIcons, 'nivo-search-close');
                }
            }
        });
    }

    // ── Mobile Search Mode ─────────────────────────────────────────────────

    /** Tracks whether the mobile overlay is currently open. */
    let mobileOverlayOpen = false;
    let mobileOverlayEl   = null;
    let mobileResultsEl   = null;
    let mobileSourceInput = null;  // original input that triggered the overlay

    /**
     * True when the viewport is in mobile range.
     */
    function isMobile() {
        return window.innerWidth <= 768;
    }

    /**
     * Open the mobile fullscreen search overlay for the given container.
     */
    function openMobileOverlay(container) {
        if (mobileOverlayOpen) return;
        mobileOverlayOpen = true;

        mobileSourceInput = container.querySelector(config.selectors.input);

        // Backdrop
        mobileOverlayEl = document.createElement('div');
        mobileOverlayEl.className = 'nivo-mobile-overlay';
        mobileOverlayEl.style.display = 'block';

        // Close on backdrop click
        mobileOverlayEl.addEventListener('click', function(e) {
            if (e.target === mobileOverlayEl) closeMobileOverlay();
        });

        // Search bar
        const searchWrap = document.createElement('div');
        searchWrap.className = 'nivo-mobile-search-wrap';

        // Back/close button
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'nivo-mobile-close-btn';
        closeBtn.setAttribute('aria-label', (window.nivo_search && window.nivo_search.strings && window.nivo_search.strings.close) || 'Close');
        closeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>';
        closeBtn.addEventListener('click', closeMobileOverlay);

        // Clone the input (same placeholder, preset ID, etc.)
        const inputWrap = document.createElement('div');
        inputWrap.className = 'nivo-mobile-input-wrap';

        const clonedInput = document.createElement('input');
        clonedInput.type = 'search';
        clonedInput.className = 'nivo-search-product-search';
        clonedInput.placeholder = mobileSourceInput ? mobileSourceInput.placeholder : '';
        clonedInput.autocomplete = 'off';
        clonedInput.setAttribute('aria-label', clonedInput.placeholder);

        inputWrap.appendChild(clonedInput);

        searchWrap.appendChild(closeBtn);
        searchWrap.appendChild(inputWrap);

        // Results pane
        mobileResultsEl = document.createElement('div');
        mobileResultsEl.className = 'nivo-search-results nivo-mobile-results';

        document.body.appendChild(mobileOverlayEl);
        document.body.appendChild(searchWrap);
        document.body.appendChild(mobileResultsEl);
        document.body.classList.add('nivo-mobile-open');

        // Copy preset settings so getContainerSettings works on the overlay
        // We re-use the original container as the "source of truth" for settings.
        // The results panel is wired directly by passing the container.
        clonedInput.focus();

        // Show history immediately on open (input is empty)
        showHistory(mobileResultsEl, searchWrap);

        // Enter key: submit search and redirect to the shop/results page.
        clonedInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const q = clonedInput.value.trim();
                if (!q) return;
                const shopUrl = (window.nivo_search && window.nivo_search.shop_url)
                    ? window.nivo_search.shop_url
                    : window.location.origin + '/';
                const sep = shopUrl.indexOf('?') !== -1 ? '&' : '?';
                window.location.href = shopUrl + sep + 's=' + encodeURIComponent(q);
            }
        });

        // Wire input events on the cloned input
        clonedInput.addEventListener('input', function() {
            const query = clonedInput.value.trim();
            const presetSettings = mobileSourceInput
                ? mobileSourceInput.closest(config.selectors.container)
                : null;
            const containerSettings = presetSettings
                ? getContainerSettings(presetSettings)
                : { minLength: DEFAULT_MIN_LENGTH, delay: DEFAULT_DELAY };

            clearTimeout(searchTimeout);

            if (query.length === 0) {
                mobileResultsEl.innerHTML = '';
                mobileResultsEl.classList.remove(config.classes.hasResults, config.classes.noResults);
                showHistory(mobileResultsEl, searchWrap);
                return;
            }

            if (query.length < containerSettings.minLength) {
                mobileResultsEl.innerHTML = '';
                mobileResultsEl.classList.remove(config.classes.hasResults, config.classes.noResults);
                return;
            }

            searchTimeout = setTimeout(function() {
                // Pass the original container for preset ID, but override the results el
                const origContainer = mobileSourceInput
                    ? mobileSourceInput.closest(config.selectors.container)
                    : searchWrap;

                performMobileSearch(query, mobileResultsEl, origContainer || searchWrap, clonedInput);
            }, containerSettings.delay);
        });

        triggerEvent('mobileOverlayOpened', { container });
    }

    /**
     * Perform search and render into the mobile results panel.
     * Like performSearch() but targets mobileResultsEl.
     */
    function performMobileSearch(query, resultsEl, container, inputEl) {
        addClass(resultsEl, config.classes.loading);

        const formData = new FormData();
        formData.append('s', query);

        const presetId = container.getAttribute('data-preset-id');
        if (presetId) formData.append('preset_id', presetId);

        const lang = window.nivo_search && window.nivo_search.lang;
        if (lang) formData.append('lang', lang);

        const useWcAjax = window.nivo_search.wc_ajax_url;
        const ajaxUrl   = useWcAjax ? window.nivo_search.wc_ajax_url : window.nivo_search.ajax_url;

        if (!useWcAjax) {
            formData.append('action', 'nivo_search');
            formData.append('nonce', window.nivo_search.nonce);
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl);
        xhr.onload = function() {
            removeClass(resultsEl, config.classes.loading);
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        displayResults(response.data, resultsEl, container, query);
                    } else {
                        displayNoResults(resultsEl, container);
                    }
                } catch(e) {
                    displayNoResults(resultsEl, container);
                }
            }
        };
        xhr.onerror = function() { removeClass(resultsEl, config.classes.loading); };
        xhr.send(formData);
    }

    /**
     * Close and destroy the mobile overlay.
     */
    function closeMobileOverlay() {
        if (!mobileOverlayOpen) return;
        mobileOverlayOpen = false;

        // Remove all injected elements
        document.querySelectorAll('.nivo-mobile-overlay, .nivo-mobile-search-wrap, .nivo-mobile-results').forEach(function(el) {
            el.parentNode && el.parentNode.removeChild(el);
        });

        document.body.classList.remove('nivo-mobile-open');
        mobileOverlayEl  = null;
        mobileResultsEl  = null;

        // Return focus to the original input
        if (mobileSourceInput) mobileSourceInput.blur();
        mobileSourceInput = null;

        triggerEvent('mobileOverlayClosed');
    }

    // Escape key closes overlay
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileOverlayOpen) closeMobileOverlay();
    });

    // ── End Mobile Search Mode ─────────────────────────────────────────────

    /**
     * Initialize search functionality
     */
    function init() {
        // Event delegation for input events
        document.addEventListener('input', function (event) {
            if (event.target.matches && event.target.matches(config.selectors.input)) {
                handleInput(event);
                // toggleClearButton removed from here
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

        // Event delegation for clear button - target the specific class
        document.addEventListener('click', function (event) {
            // Check if clicked element or its parent is the close icon
            if (event.target.matches('.nivo-search-close-icon') || event.target.closest('.nivo-search-close-icon')) {
                handleClear(event);
            }
        });

        // Click outside handler
        document.addEventListener('click', handleClickOutside);

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
        triggerEvent: triggerEvent,
        openMobileOverlay: openMobileOverlay,
        closeMobileOverlay: closeMobileOverlay,
        historySave: historySave,
        historyClear: historyClear,
    };

})(window, document);