/**
 * Global JavaScript for TracyAppsDesignLLC
 *
 * Includes all JS which is required within all sections of the theme.
 */

window.tracyappsdesign = window.tracyappsdesign || {};

(function( window, $, undefined ) {
	'use strict';

	var tracyappsdesign = window.tracyappsdesign;

	$.extend( tracyappsdesign, {

		//* Skip Link Focus Fix
		skipLinks: function() {
			var eventMethod,
				isWebkit = navigator.userAgent.toLowerCase().indexOf( 'webkit' ) > -1,
				isOpera  = navigator.userAgent.toLowerCase().indexOf( 'opera' )  > -1,
				isIe     = navigator.userAgent.toLowerCase().indexOf( 'msie' )   > -1;

			if ( ( isWebkit || isOpera || isIe ) && 'undefined' !== typeof( document.getElementById ) ) {
				eventMethod = ( window.addEventListener ) ? 'addEventListener' : 'attachEvent';
				window[ eventMethod ]( 'hashchange', function() {
					var element = document.getElementById( location.hash.substring( 1 ) );

					if ( element ) {
						if ( ! /^(?:a|select|input|button|textarea)$/i.test( element.tagName ) ) {
							element.tabIndex = -1;
						}

						element.focus();
					}
				}, false );
			}
		},

		//* Mobile Menu
		mobileNav: function() {
			var menuSelectors = [],
				menuSide      = 'right',
				name          = 'sidr-main',
				responsiveMenuButton = $( '<button type="button" id="responsive-menu-button" class="menu-button" aria-expanded="false"></button>' );

			if ( $( '#menu-header' ).length ) {
				menuSelectors.push( '#menu-header' );
			}

			if ( $( '#after-header' ).length ) {
				menuSelectors.push( '#menu-after-header' );
			}

			//* End here if we don't have a menu.
			if ( menuSelectors.length === 0 ) {
				return;
			}

			//* Add a responsive menu button.
			$( '#branding' ).before( responsiveMenuButton );

			//* Switch the menu side if a RTL langauge is in use.
			if ( $( 'body' ).hasClass( 'rtl' ) ) {
				menuSide = 'left';
			}

			//* Sidr menu init.
			responsiveMenuButton.sidr( {
				name: name,
				renaming: false,
				side: menuSide,
				source: menuSelectors.toString(),
				onOpen: function() {
					var navEl        = $( '#' + name ),
						navItems     = $( '#' + name + ' a' ),
						firstNavItem = navItems.first(),
						lastNavItem  = navItems.last();

					responsiveMenuButton.toggleClass( 'activated' ).attr( 'aria-expanded', true );
					$( '.site-container' ).on( 'click.CloseSidr', function( event ) {
						$.sidr( 'close', name );
						event.preventDefault();
					});
					// Add some attributes to the menu container.
					navEl.attr({ role: 'navigation', tabindex: '0' }).focus();
					// When focus is on the menu container.
					navEl.on( 'keydown.sidrNav', function( event ) {
						// If it's not the tab key then return.
						if ( 9 !== event.keyCode ) {
							return;
						}
						// When tabbing forwards and tabbing out of the last link.
						if ( lastNavItem[0] === event.target && ! event.shiftKey ) {
							responsiveMenuButton.focus();
							return false;
						// When tabbing backwards and tabbing out of the first link OR the menu container.
						} else if ( ( firstNavItem[0] === event.target || navEl[0] === event.target ) && event.shiftKey ) {
							responsiveMenuButton.focus();
							return false;
						}
					});
					// When focus is on the toggle button.
					responsiveMenuButton.on( 'keydown.sidrNav', function( event ) {
						// If it's not the tab key then return.
						if ( 9 !== event.keyCode ) {
							return;
						}
						// when tabbing forwards
						if ( responsiveMenuButton[0] === event.target && ! event.shiftKey ) {
							navEl.focus();
							return false;
						}
					});
				},
				onClose: function() {
					responsiveMenuButton.toggleClass( 'activated' ).attr( 'aria-expanded', false );
					$( '.site-container' ).off( 'click.CloseSidr' );
					// Remove the toggle button keydown event.
					responsiveMenuButton.off( 'keydown.sidrNav' );
				}
			});

			//* Close sidr menu if open on larger screens
			$( window ).resize(function() {
				if( window.innerWidth > 1023 ) {
					$.sidr('close', 'sidr-main');
					responsiveMenuButton.attr( 'aria-expanded', false );
				}
			});
		},

		//* FitVids Init
		loadFitVids: function() {
			if ( $.fn.fitVids ) {
				$( '#site-inner' ).fitVids();
			}
		}

	});

	// Document ready.
	jQuery(function() {
		tracyappsdesign.skipLinks();
		tracyappsdesign.mobileNav();
		tracyappsdesign.loadFitVids();
		jQuery( document ).gamajoAccessibleMenu();
	});
})( this, jQuery );

// jQuery(document).gamajoAccessibleMenu();
