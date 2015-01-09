/*global jQuery */
/*jshint browser:true */
/*!
* FitVids 1.1
*
* Copyright 2013, Chris Coyier - http://css-tricks.com + Dave Rupert - http://daverupert.com
* Credit to Thierry Koblentz - http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/
* Released under the WTFPL license - http://sam.zoy.org/wtfpl/
*
*/

(function( $ ){

	"use strict";

	$.fn.fitVids = function( options ) {
		var settings = {
			customSelector: null
		};

		if(!document.getElementById('fit-vids-style')) {
			// appendStyles: https://github.com/toddmotto/fluidvids/blob/master/dist/fluidvids.js
			var head = document.head || document.getElementsByTagName('head')[0];
			var css = '.fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}';
			var div = document.createElement('div');
			div.innerHTML = '<p>x</p><style id="fit-vids-style">' + css + '</style>';
			head.appendChild(div.childNodes[1]);
		}

		if ( options ) {
			$.extend( settings, options );
		}

	return this.each(function(){
		var selectors = [
			"iframe[src*='player.vimeo.com']",
			"iframe[src*='youtube.com']",
			"iframe[src*='youtube-nocookie.com']",
			"iframe[src*='kickstarter.com'][src*='video.html']",
			"object",
			"embed"
		];

		if (settings.customSelector) {
			selectors.push(settings.customSelector);
		}

		var $allVideos = $(this).find(selectors.join(','));
		$allVideos = $allVideos.not("object object"); // SwfObj conflict patch

			$allVideos.each(function(){
				var $this = $(this);
				if (this.tagName.toLowerCase() === 'embed' && $this.parent('object').length || $this.parent('.fluid-width-video-wrapper').length) { return; }
				var height = ( this.tagName.toLowerCase() === 'object' || ($this.attr('height') && !isNaN(parseInt($this.attr('height'), 10))) ) ? parseInt($this.attr('height'), 10) : $this.height(),
				width = !isNaN(parseInt($this.attr('width'), 10)) ? parseInt($this.attr('width'), 10) : $this.width(),
				aspectRatio = height / width;
				if(!$this.attr('id')){
					var videoID = 'fitvid' + Math.floor(Math.random()*999999);
					$this.attr('id', videoID);
				}
				$this.wrap('<div class="fluid-width-video-wrapper"></div>').parent('.fluid-width-video-wrapper').css('padding-top', (aspectRatio * 100)+"%");
				$this.removeAttr('height').removeAttr('width');
			});
		});
	};
// Works with either jQuery or Zepto
})( window.jQuery || window.Zepto );

/*! Gamajo Accessible Menu - v1.0.0 - 2014-09-08
* https://github.com/GaryJones/accessible-menu
* Copyright (c) 2014 Gary Jones; Licensed MIT */
;(function( $, window, document, undefined ) {
	'use strict';

	var pluginName = 'gamajoAccessibleMenu',
	hoverTimeout = [];

	// The actual plugin constructor
	function Plugin( element, options ) {
		this.element = element;
		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.opts = $.extend( {}, $.fn[pluginName].options, options );
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend( Plugin.prototype, {
		init: function() {
		$( this.element )
		.on( 'mouseenter.' + pluginName, this.opts.menuItemSelector, this.opts, this.menuItemEnter )
		.on( 'mouseleave.' + pluginName, this.opts.menuItemSelector, this.opts, this.menuItemLeave )
		.find( 'a' )
		.on( 'focus.'  + pluginName + ', blur.' + pluginName, this.opts, this.menuItemToggleClass );
		},

		/**
		* Add class to menu item on hover so it can be delayed on mouseout.
		*
		* @since 1.0.0
		*/
		menuItemEnter: function( event ) {
			// Clear all existing hover delays
			$.each( hoverTimeout, function( index ) {
				$( '#' + index ).removeClass( event.data.hoverClass );
				clearTimeout( hoverTimeout[index] );
			});
			// Reset list of hover delays
			hoverTimeout = [];

			$( this ).addClass( event.data.hoverClass );
		},

		/**
		* After a short delay, remove a class when mouse leaves menu item.
		*
		* @since 1.0.0
		*/
		menuItemLeave: function( event ) {
			var $self = $( this );
			// Delay removal of class
			hoverTimeout[this.id] = setTimeout( function() {
				$self.removeClass( event.data.hoverClass );
			}, event.data.hoverDelay );
		},

		/**
		* Toggle menu item class when a link fires a focus or blur event.
		*
		* @since 1.0.0
		*/
		menuItemToggleClass: function( event ) {
			$( this ).parents( event.data.menuItemSelector ).toggleClass( event.data.hoverClass );
		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[ pluginName ] = function( options ) {
		this.each( function() {
			if ( ! $.data( this, 'plugin_' + pluginName ) ) {
				$.data( this, 'plugin_' + pluginName, new Plugin( this, options ) );
			}
		});

	// chain jQuery functions
	return this;
	};

	$.fn[ pluginName ].options = {
		// The CSS class to add to indicate item is hovered or focused
		hoverClass: 'menu-item-hover',

		// The delay to keep submenus showing after mouse leaves
		hoverDelay: 250,

		// Selector for general menu items. If you remove the default menu item
		// classes, then you may want to call this plugin with this value
		// set to something like 'nav li' or '.menu li'.
		menuItemSelector: '.menu-item'
	};
})( jQuery, window, document );

/*! Sidr - v1.2.1 - 2013-11-06
 * https://github.com/artberri/sidr
 * Copyright (c) 2013 Alberto Varela; Licensed MIT */
(function(e){var t=!1,i=!1,n={isUrl:function(e){var t=RegExp("^(https?:\\/\\/)?((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|((\\d{1,3}\\.){3}\\d{1,3}))(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*(\\?[;&a-z\\d%_.~+=-]*)?(\\#[-a-z\\d_]*)?$","i");return t.test(e)?!0:!1},loadContent:function(e,t){e.html(t)},addPrefix:function(e){var t=e.attr("id"),i=e.attr("class");"string"==typeof t&&""!==t&&e.attr("id",t.replace(/([A-Za-z0-9_.\-]+)/g,"sidr-id-$1")),"string"==typeof i&&""!==i&&"sidr-inner"!==i&&e.attr("class",i.replace(/([A-Za-z0-9_.\-]+)/g,"sidr-class-$1")),e.removeAttr("style")},execute:function(n,s,a){"function"==typeof s?(a=s,s="sidr"):s||(s="sidr");var r,d,l,c=e("#"+s),u=e(c.data("body")),f=e("html"),p=c.outerWidth(!0),g=c.data("speed"),h=c.data("side"),m=c.data("displace"),v=c.data("onOpen"),y=c.data("onClose"),x="sidr"===s?"sidr-open":"sidr-open "+s+"-open";if("open"===n||"toggle"===n&&!c.is(":visible")){if(c.is(":visible")||t)return;if(i!==!1)return o.close(i,function(){o.open(s)}),void 0;t=!0,"left"===h?(r={left:p+"px"},d={left:"0px"}):(r={right:p+"px"},d={right:"0px"}),u.is("body")&&(l=f.scrollTop(),f.css("overflow-x","hidden").scrollTop(l)),m?u.addClass("sidr-animating").css({width:u.width(),position:"absolute"}).animate(r,g,function(){e(this).addClass(x)}):setTimeout(function(){e(this).addClass(x)},g),c.css("display","block").animate(d,g,function(){t=!1,i=s,"function"==typeof a&&a(s),u.removeClass("sidr-animating")}),v()}else{if(!c.is(":visible")||t)return;t=!0,"left"===h?(r={left:0},d={left:"-"+p+"px"}):(r={right:0},d={right:"-"+p+"px"}),u.is("body")&&(l=f.scrollTop(),f.removeAttr("style").scrollTop(l)),u.addClass("sidr-animating").animate(r,g).removeClass(x),c.animate(d,g,function(){c.removeAttr("style").hide(),u.removeAttr("style"),e("html").removeAttr("style"),t=!1,i=!1,"function"==typeof a&&a(s),u.removeClass("sidr-animating")}),y()}}},o={open:function(e,t){n.execute("open",e,t)},close:function(e,t){n.execute("close",e,t)},toggle:function(e,t){n.execute("toggle",e,t)},toogle:function(e,t){n.execute("toggle",e,t)}};e.sidr=function(t){return o[t]?o[t].apply(this,Array.prototype.slice.call(arguments,1)):"function"!=typeof t&&"string"!=typeof t&&t?(e.error("Method "+t+" does not exist on jQuery.sidr"),void 0):o.toggle.apply(this,arguments)},e.fn.sidr=function(t){var i=e.extend({name:"sidr",speed:200,side:"left",source:null,renaming:!0,body:"body",displace:!0,onOpen:function(){},onClose:function(){}},t),s=i.name,a=e("#"+s);if(0===a.length&&(a=e("<div />").attr("id",s).appendTo(e("body"))),a.addClass("sidr").addClass(i.side).data({speed:i.speed,side:i.side,body:i.body,displace:i.displace,onOpen:i.onOpen,onClose:i.onClose}),"function"==typeof i.source){var r=i.source(s);n.loadContent(a,r)}else if("string"==typeof i.source&&n.isUrl(i.source))e.get(i.source,function(e){n.loadContent(a,e)});else if("string"==typeof i.source){var d="",l=i.source.split(",");if(e.each(l,function(t,i){d+='<div class="sidr-inner">'+e(i).html()+"</div>"}),i.renaming){var c=e("<div />").html(d);c.find("*").each(function(t,i){var o=e(i);n.addPrefix(o)}),d=c.html()}n.loadContent(a,d)}else null!==i.source&&e.error("Invalid Sidr Source");return this.each(function(){var t=e(this),i=t.data("sidr");i||(t.data("sidr",s),"ontouchstart"in document.documentElement?(t.bind("touchstart",function(e){e.originalEvent.touches[0],this.touched=e.timeStamp}),t.bind("touchend",function(e){var t=Math.abs(e.timeStamp-this.touched);200>t&&(e.preventDefault(),o.toggle(s))})):t.click(function(e){e.preventDefault(),o.toggle(s)}))})}})(jQuery);
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
