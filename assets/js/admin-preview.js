/**
 * NivoSearch — Preset Live Preview
 *
 * Drives the real frontend HTML/CSS markup rendered inside the admin meta box
 * so the preview looks pixel-identical to the site frontend.
 *
 * @package NivoSearch
 * @since   2.3.1
 */
/* global jQuery, window */
( function ( $ ) {
	'use strict';

	var pvContainer  = document.getElementById( 'nivo-pv-container' );
	var settingsCol  = document.querySelector( '.nivo-preset-settings' );

	if ( ! pvContainer || ! settingsCol ) { return; }

	var state = window.nivoPresetData || {};

	// ── Targets inside the preview ────────────────────────────────────────────
	var input   = pvContainer.querySelector( '.nivo-search-product-search' );
	var results = pvContainer.querySelector( '.nivo-search-results' );

	// ── Apply bar (input) styles ──────────────────────────────────────────────
	function applyBarStyles() {
		if ( ! input ) { return; }
		input.style.height      = ( parseInt( state.bar_height, 10 ) || 50 ) + 'px';
		input.style.borderColor = state.border_color || '#ddd';
		input.style.background  = state.bg_color     || '#fff';
		input.style.color       = state.text_color   || '#333';
	}

	// ── Apply results-panel styles ────────────────────────────────────────────
	function applyResultsStyles() {
		if ( ! results ) { return; }
		var textColor = state.results_text_color || '#333';
		results.style.borderColor = state.results_border_color || '#ddd';
		results.style.background  = state.results_bg_color     || '#fff';
		results.style.color       = textColor;
		// Cascade text color to child elements that may carry their own color rule.
		pvContainer.querySelectorAll(
			'.nivo-search-product-title, .nivo-search-product-description, ' +
			'.nivo-search-product-price, .nivo-search-product-sku, .nivo-search-section-title'
		).forEach( function ( el ) {
			el.style.color = textColor;
		} );
	}

	// ── Show / hide by toggle key ─────────────────────────────────────────────
	// Each class nivo-pv-toggle--{key} marks elements that belong to that toggle.
	function applyToggles() {
		var keys = [
			'images', 'price', 'sku', 'description',
			'stock_status', 'category_badge', 'add_to_cart', 'qty_selector', 'view_all',
		];

		keys.forEach( function ( key ) {
			var stateKey = 'show_' + key;
			var visible  = !! state[ stateKey ];

			// qty_selector requires add_to_cart to also be on
			if ( key === 'qty_selector' && ! state.show_add_to_cart ) {
				visible = false;
			}

			pvContainer.querySelectorAll( '.nivo-pv-toggle--' + key ).forEach( function ( el ) {
				el.style.display = visible ? '' : 'none';
			} );
		} );
	}

	// ── Placeholder text ──────────────────────────────────────────────────────
	function applyPlaceholder() {
		if ( ! input ) { return; }
		var field = settingsCol.querySelector( 'input[name="nivo_settings[placeholder]"]' );
		input.placeholder = ( field && field.value.trim() ) ? field.value : 'Search products…';
	}

	// ── Full render ───────────────────────────────────────────────────────────
	function renderAll() {
		applyBarStyles();
		applyResultsStyles();
		applyToggles();
		applyPlaceholder();
	}

	// Initial render on page load
	renderAll();

	// ── Map field names → state keys & update functions ───────────────────────
	var styleFieldMap = {
		// Search bar
		'nivo_settings[bar_height]'          : function ( v ) { state.bar_height           = v; applyBarStyles(); },
		'nivo_settings[bar_width]'           : function ( v ) { state.bar_width            = v; /* preview is fixed-width; track state only */ },
		'nivo_settings[border_color]'        : function ( v ) { state.border_color         = v; applyBarStyles(); },
		'nivo_settings[bg_color]'            : function ( v ) { state.bg_color             = v; applyBarStyles(); },
		'nivo_settings[text_color]'          : function ( v ) { state.text_color           = v; applyBarStyles(); },
		// Results panel
		'nivo_settings[results_width]'       : function ( v ) { state.results_width        = v; /* preview is fixed-width; track state only */ },
		'nivo_settings[results_border_color]': function ( v ) { state.results_border_color = v; applyResultsStyles(); },
		'nivo_settings[results_bg_color]'    : function ( v ) { state.results_bg_color     = v; applyResultsStyles(); },
		'nivo_settings[results_text_color]'  : function ( v ) { state.results_text_color   = v; applyResultsStyles(); },
		// General
		'nivo_settings[placeholder]'         : function ()    { applyPlaceholder(); },
	};

	// ── Listen: text / number inputs ──────────────────────────────────────────
	settingsCol.addEventListener( 'input', function ( e ) {
		var fn = styleFieldMap[ e.target.name ];
		if ( fn ) { fn( e.target.value ); }
	} );

	// ── Listen: checkboxes ────────────────────────────────────────────────────
	settingsCol.addEventListener( 'change', function ( e ) {
		var el = e.target;
		if ( el.type !== 'checkbox' ) { return; }
		// name is  "nivo_settings[show_images]" → key = "show_images"
		var key = el.name.replace( 'nivo_settings[', '' ).replace( ']', '' );
		if ( key.indexOf( 'show_' ) === 0 ) {
			state[ key ] = el.checked;
			applyToggles();
		}
	} );

	// ── Listen: wp-color-picker (iris:change — secondary path) ───────────────
	// Primary path: admin.js dispatches a native 'input' event on colour change,
	// which the listener above catches.  This iris:change binding is kept as a
	// belt-and-suspenders fallback for edge cases where the native event is lost.
	$( settingsCol ).on( 'iris:change', function ( e, ui ) {
		var fn = styleFieldMap[ e.target.name ];
		if ( fn ) { fn( ui.color.toString() ); }
	} );

	// ── Tab switching ─────────────────────────────────────────────────────────
	var tabs   = settingsCol.querySelectorAll( '.nivo-preset-tab' );
	var panels = settingsCol.querySelectorAll( '.nivo-preset-tab-panel' );

	tabs.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var target = this.dataset.tab;

			// Update tab active state
			tabs.forEach( function ( b ) {
				b.classList.toggle( 'nivo-preset-tab--active', b.dataset.tab === target );
			} );

			// Show/hide panels
			panels.forEach( function ( panel ) {
				if ( panel.dataset.panel === target ) {
					panel.removeAttribute( 'hidden' );
				} else {
					panel.setAttribute( 'hidden', '' );
				}
			} );

			// Re-paint color picker swatches that became visible.
			// iris() is already initialised for all .nivo-color-picker elements (by admin.js
			// on DOMReady) — we just need to call reflow() so each swatch recalculates
			// its dimensions now that the parent panel is no longer hidden.
			settingsCol.querySelectorAll( '.nivo-preset-tab-panel[data-panel="' + target + '"] .nivo-color-picker' ).forEach( function ( el ) {
				try {
					$( el ).iris( 'reflow' );
				} catch ( e ) { /* iris not yet attached — safe to ignore */ }
			} );
		} );
	} );

}( jQuery ) );
