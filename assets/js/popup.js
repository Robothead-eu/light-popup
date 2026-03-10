( function () {
	'use strict';

	if ( typeof LightPopupConfig === 'undefined' || ! LightPopupConfig.popups ) return;

	var isMobile = window.innerWidth < 768;

	// --- Frequency helpers ---

	var FREQ_SECONDS = { day: 86400, week: 604800, month: 2592000 };

	function storageKey( id ) {
		return 'lp_' + id;
	}

	function hasShown( popup ) {
		var freq = popup.frequency;
		if ( 'always' === freq ) return false;

		var key     = storageKey( popup.id );
		var storage = ( 'session' === freq ) ? sessionStorage : localStorage;
		var stored  = storage.getItem( key );

		if ( ! stored ) return false;
		if ( 'once' === freq ) return true;

		var elapsed = ( Date.now() - parseInt( stored, 10 ) ) / 1000;
		return elapsed < FREQ_SECONDS[ freq ];
	}

	function markShown( popup ) {
		var freq    = popup.frequency;
		var key     = storageKey( popup.id );
		var storage = ( 'session' === freq ) ? sessionStorage : localStorage;
		storage.setItem( key, String( Date.now() ) );
	}

	// --- Show / close ---

	function showPopup( popup ) {
		if ( ! popup.showOnDesktop && ! isMobile ) return;
		if ( ! popup.showOnMobile  &&   isMobile ) return;
		if ( hasShown( popup ) ) return;

		var dialog = document.getElementById( 'lp-popup-' + popup.id );
		if ( ! dialog ) return;

		dialog.showModal();
		markShown( popup );
	}

	function closePopup( dialog, triggerEl ) {
		dialog.close();
		if ( triggerEl && triggerEl.focus ) {
			triggerEl.focus();
		}
	}

	// --- Trigger setup ---

	function setupTriggers( popup ) {
		var triggered = false;
		var cleanups  = [];

		function fire() {
			if ( triggered ) return;
			triggered = true;
			cleanups.forEach( function ( fn ) { fn(); } );
			showPopup( popup );
		}

		function addTrigger( cfg ) {
			if ( ! cfg || ! cfg.type ) return;

			if ( 'time_delay' === cfg.type ) {
				var delay = parseFloat( cfg.value ) || 8;
				var timer = setTimeout( fire, delay * 1000 );
				cleanups.push( function () { clearTimeout( timer ); } );

			} else if ( 'scroll_depth' === cfg.type ) {
				var depth = parseFloat( cfg.value ) || 50;
				function onScroll() {
					var scrolled = ( window.scrollY + window.innerHeight ) / document.documentElement.scrollHeight * 100;
					if ( scrolled >= depth ) fire();
				}
				window.addEventListener( 'scroll', onScroll, { passive: true } );
				cleanups.push( function () { window.removeEventListener( 'scroll', onScroll ); } );

			} else if ( 'exit_intent' === cfg.type ) {
				if ( isMobile ) {
					// Mobile substitute: rapid upward scroll.
					var lastY    = window.scrollY;
					var lastTime = Date.now();
					function onMobileScroll() {
						var now   = Date.now();
						var delta = lastY - window.scrollY;
						if ( delta > 50 && ( now - lastTime ) < 200 ) fire();
						lastY    = window.scrollY;
						lastTime = now;
					}
					window.addEventListener( 'scroll', onMobileScroll, { passive: true } );
					cleanups.push( function () { window.removeEventListener( 'scroll', onMobileScroll ); } );
				} else {
					function onMouseLeave( e ) {
						if ( e.clientY < 10 ) fire();
					}
					document.addEventListener( 'mouseleave', onMouseLeave );
					cleanups.push( function () { document.removeEventListener( 'mouseleave', onMouseLeave ); } );
				}

			} else if ( 'click' === cfg.type ) {
				var selector = cfg.value;
				if ( ! selector ) return;
				function onClick( e ) {
					if ( e.target.closest( selector ) ) {
						e.preventDefault();
						fire();
					}
				}
				document.addEventListener( 'click', onClick );
				cleanups.push( function () { document.removeEventListener( 'click', onClick ); } );
			}
		}

		addTrigger( popup.trigger );
		if ( popup.secondaryTrigger ) addTrigger( popup.secondaryTrigger );
	}

	// --- Close handlers ---

	function setupCloseHandlers( popup ) {
		var dialog = document.getElementById( 'lp-popup-' + popup.id );
		if ( ! dialog ) return;

		// X button.
		var closeBtn = dialog.querySelector( '.lp-popup__close' );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function () {
				closePopup( dialog, null );
			} );
		}

		// Backdrop click.
		if ( popup.closeOnBackdrop ) {
			dialog.addEventListener( 'click', function ( e ) {
				if ( e.target === dialog ) closePopup( dialog, null );
			} );
		}

		// ESC is handled natively by <dialog>.
	}

	// --- Click-trigger shortcode links ---

	document.addEventListener( 'click', function ( e ) {
		var trigger = e.target.closest( '[data-lp-id]' );
		if ( ! trigger ) return;
		e.preventDefault();
		var id = parseInt( trigger.getAttribute( 'data-lp-id' ), 10 );
		var popup = LightPopupConfig.popups.find( function ( p ) { return p.id === id; } );
		if ( ! popup ) return;
		if ( hasShown( popup ) ) return;
		var dialog = document.getElementById( 'lp-popup-' + id );
		if ( ! dialog ) return;
		dialog.showModal();
		markShown( popup );

		// Close returns focus to the trigger element.
		dialog.addEventListener( 'close', function handler() {
			dialog.removeEventListener( 'close', handler );
			trigger.focus();
		} );
	} );

	// --- Init ---

	document.addEventListener( 'DOMContentLoaded', function () {
		LightPopupConfig.popups.forEach( function ( popup ) {
			setupCloseHandlers( popup );
			setupTriggers( popup );
		} );
	} );

} )();
