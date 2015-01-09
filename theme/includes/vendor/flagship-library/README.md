# Flagship Library

A collection of helpful functions and classes to make creating an awesome theme more enjoyable.

__Contributors:__ [Robert Neu](https://github.com/robneu)  
__Requires:__ WordPress 4.0, Hybrid Core 2.0.3  
__Tested up to:__ WordPress 4.0, Hybrid Core 2.0.3  
__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)  

There are a lot of things that go into a well-crafted theme. You need to make sure everything is SEO-friendly, you have to format commonly re-used bits of markup, and now more than ever you need to make it easy for your users to interact with your theme using the WordPress customizer.

The Flagship Library is a project designed to help with these things by delivering a modular, extensible library of reusable theme code. The primary reason it exists is for use in our [premium WordPress themes](http://flagshipwp.com), but we've released it open source for anyone else who would like to use or contribute to it.

## Basic Features

Most of the functionality in our library has to do with changing the markup and structure of our themes to suite our needs. If you find anything in the library you'd rather not use, you can either prevent it from loading entirely using the `flagship_library_includes` filter or by unhooking the functions directly.

Currently, the main features include:

- Filter markup to add classes using `hybrid_attr`
- Filter the content directory structure using `hybrid_content_template_hierarchy`
- Minor SEO adjustments
- A simple favicon loader
- A class to make the WordPress search form a11y compliant
- Template helper functions for displaying entry meta data
- Helper class for registering WordPress customizer options
- Helper class for building CSS output based on customizer input

## Extensions

In addition to the base features, we have also created a number of "extensions" which are only activated when a theme declares support for them. This list is likely to grow as we find new common features which we'd like to share throughout our theme collection. Currently, the following extensions are available:

### Breadcrumb Display

This is a simple customizer section which allows the user to choose where he or she would like breadcrumbs to display on their site. To enable this extension, add support for the [Hybrid Core breadcrumb trail]( https://github.com/justintadlock/breadcrumb-trail) feature.

`add_theme_support( 'breadcrumb-trail' );`

In addition to adding theme support, you'll also need to make sure a breadcrumb template [like the one in Compass](https://github.com/FlagshipWP/compass/blob/develop/theme/menu/breadcrumbs.php) exists in your theme. If you're building a theme based on Compass, you won't need to do any of this; however, if you're using a fully custom Hybrid Core theme you'll also need to add references to your breadcrumb template in your main templates using the `hybrd_get_menu()` function.

### Footer Widgets

Footer widgets are a common design pattern in both our themes and many other WordPress themes on the market. There's not a whole lot to this extension, it just streamlines the method of registering footer widget sidebars. If you'd like to use it, add support for Flagship footer widgets in your theme and include a number to represent the number of footer widget areas you'd like to register.

`add_theme_support( 'flagship-footer-widgets', 3 );`

Once support has been added, you'll have to create a template like [the one in Compass](https://github.com/FlagshipWP/compass/blob/develop/theme/sidebar/footer-widgets.php) to format the display of your footer widgets. Because the footer widgets are hooked in place, you won't need to do anything else. If for some reason you'd like to change the hook location, you can do so by unhooking the default location.

`remove_action( 'tha_footer_before', array( flagship_footer_widgets(), 'the_footer_widgets' ) );`

### Site Logo

The site logo extension is based on and very similar to the site logo feature built into Jetpack. We've done a bit of refactoring on the code, but generally speaking the feature is nearly identical to what's available in Jetpack. We wanted this functionality to be available to all of our customers regardless of whether or not they're using Jetpack, so it made sense to include it in our library.

Whenever Jetpack or the standalone Automattic site logo plugin are active, our class will deactivate itself and allow these plugins to function normally. To enable this extension, add support for the site-logo feature in your theme.

`add_theme_support( 'site-logo' );`

You can also pass in a size argument which will let you declare a custom size for your logo. To do this, simply add the size like this:

`add_theme_support( 'site-logo', array( 'size' => 'medium', ) );`

You can use any size image you want, including a custom size which you would need to add using the `add_image_size` [WordPress core function](http://codex.wordpress.org/Function_Reference/add_image_size).

In addition to adding support, you'll need to add a template tag to your site's header.php file wherever you'd like the logo to be displayed. The tag you'll need to add is:

`flagship_the_logo();`

We've opted for our own prefixed function which will fall back to the Jetpack and standalone plugin functions if either of them is active.

## The Future

We plan on keeping this library as lightweight as possible, but we will be adding to it as we learn and expand our theme collection. If you'd like to contribute to the library, we're happily accepting ideas, bug reports, pull requests, and general feedback both here on GitHub and on our [community forums](http://community.flagshipwp.com).
