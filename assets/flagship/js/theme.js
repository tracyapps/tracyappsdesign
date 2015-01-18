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

		slicknav: function() {
			if ( $.fn.slicknav ) {
				$('#after-header').slicknav({
					closeOnClick: true
				});
			}
		},


		//* FitVids Init
		loadFitVids: function() {
			if ( $.fn.fitVids ) {
				$( '#site-inner' ).fitVids();
			}
		}

	});

	// slick nav (mobile)
	$(function() {
		$(window).resize(function(){
			if( window.innerWidth <= 810 ) {
				if ( $.fn.slicknav ) {
					$('#after-header').slicknav({
						closeOnClick: true
					});
				}
			}
		});
	});

	//  sticky nav
	$(function() {
		if( window.innerWidth > 810 ) {
			// grab the initial top offset of the navigation
			var sticky_navigation_offset_top = $('#menu-after-header').offset().top;

			// our function that decides weather the navigation bar should have "fixed" css position or not.
			var sticky_navigation = function(){
				var scroll_top = $(window).scrollTop(); // our current vertical position from the top

				// if we've scrolled more than the navigation, change its position to fixed to stick to top, otherwise change it back to relative
				if (scroll_top > sticky_navigation_offset_top) {
					$('#menu-after-header').css({ 'position': 'fixed', 'top':0, 'left':0, 'width': '100%', 'display': 'block', 'z-index':350 });
				} else {
					$('#menu-after-header').css({ 'position': 'relative' });
				}
			};

			// run our function on load
			sticky_navigation();

			// and run it again every time you scroll
			$( window ).scroll(function() {
				sticky_navigation();
			});
		}
		$( window ).resize(function() {
			sticky_navigation();
		});
	});




	// animate anchors
	$( 'a[href^="#"]' ).bind('click',function(event){
		var $anchor = $(this);

		$( 'html, body' ).stop().animate({
			scrollTop: $($anchor.attr('href')).offset().top
		}, 3500,'easeOutCubic');
		/*
		 if you don't want to use the easing effects:
		 $('html, body').stop().animate({
		 scrollTop: $($anchor.attr('href')).offset().top
		 }, 1000);
		 */
		event.preventDefault();
	});

	// video BG
	$( '#video-frame' ).vide({
		mp4: the_base_theme_directory.theme_directory + '/video/background-video-reel.mp4',
		ogv: the_base_theme_directory.theme_directory + '/video/background-video-reel-large.ogv',
		webm: the_base_theme_directory.theme_directory + '/video/background-video-reel-large.webm'

	}, {
		posterType: 'none',
		autoplay: true,
		loop: true,
		position: '50% 50%'
	});

	var vph = $( window ).height();
	$( '.full-height' ).height(vph);

	$(document).ready(function() {

		function reset_demensions() {
			var doc_height = $(window).height();
			$( '.full-height' ).css( 'min-height', doc_height + 'px' );
		}

		reset_demensions();
		$( window ).resize(function() {
			reset_demensions();
		});

	});


	// Document ready.
	jQuery(function() {
		tracyappsdesign.skipLinks();
		tracyappsdesign.slicknav();
		tracyappsdesign.loadFitVids();
		jQuery( document ).gamajoAccessibleMenu();
	});

})( this, jQuery );

// jQuery(document).gamajoAccessibleMenu();
