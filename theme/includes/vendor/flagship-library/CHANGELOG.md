# Changelog for Flagship Library

## 1.2.1

Fixed an incorrect textomain that made it in during development.

## 1.2.0

This release adds a few helper classes for working with the WordPress customizer and makes some under-the-hook improvements to the existing extensions and classes. The following classes were added:

- Flagship_Customizer_Base
- Flagship_Style_Builder

The customizer base is an abstract class designed to make registering customizer options within a theme a bit less labor-intensive. It contains data sanitizaiton methods and some default hooks to register your customizer options and assets. All of our existing customizer classes have been updated to use this base class to reduce duplicate code.

The style builder is an interface for building and registering custom CSS based on user selections within the customizer. It's based on the style builder used in the Make theme by The Theme Foundry.

Other enhancements include:

- Added SVG logo support
- Fixed n bug with asset URLs on Windows installations
- Fixed a display bug which caused the site title to be hidden by default
- Escaped all instances of the library asset URI
- General code cleanup

## 1.1.0

Major backwards-compatible rewrite. All internal files were re-structured and organized for a more future-friendliness. Extensions were added in a modular way so that theme authors can take advantage of them if they choose to do so. In addition to the restructuring and cleanup, we also added or improved the following features:

- Breadcrumb Display Extension
- Site Logo Extension
- Footer Widgets Extension

## 1.0.0

First public release.
