=== WordPress Theme Framework ===
Contributors: aprokopenko
Description: Lightweight theme framework base with Model-View concept for developers who want to better organize their own custom themes.
Tags: mvc theme, theme boilerplate, oop theme, mini framework
Author: JustCoded / Alex Prokopenko
Author URI: http://justcoded.com/
Requires at least: 4.7
Tested up to: 5.8
License: GPL3
Stable tag: 3.0

Lightweight theme framework base with Model-View concept for developers who want to better organize their own custom themes.

== Description ==

Just Theme Framework is a lightweight theme framework base with Model-View concept for developers who want to better organize their own custom themes.

Our mini framework features:

* Better organized templates system.
* Easy Post Types or Taxonomies registration.
* Base Model class.
* Advanced Theme registration class.
* Easy creation of Admin options pages.
* Supports numerous plugins:
	* WooCommerce
	* Autoptimize, WP Super Cache (easy customization of scripts optimization)
	* Contact Form 7 (auto save all requests to DB)
	* Just Custom Fields
	* Just TinyMce Custom Styles
	* Just Responsive Images
	* Faker Press

We didn't added "Controllers" level, because WordPress has it's own routing system and replacing it with one more level
	will impact the site speed. So basically it's very close to standard WordPress theme with much better organization
	of custom logic parts, custom queries etc.

= Better organized templates system =

You can now place all templates under "views" folder with section-by-section break down. Search your templates easily
under `page`, `post`, `search` folders. Or create new folder for your new Custom Post type and place all your templates
there.

Modern layouts system, taken from Laravel and Yii PHP Frameworks. Under `views` folder you can have different
nested `layouts` to keep header and footer in the same place. All repeatable wrappers from section templates can be moved
just as a new layout.

The template system is patched with standard WordPress hooks, which were added in version 4.7. We support all WordPress
standard template hierarchy. Our patch just adds `/views/{section}` prefix to the list of available templates.

= Easy Post Types or Taxonomies registration =

Use our base PostType or Taxonomy class to register your own Post Types or Taxonomies. It has more intuitive options;
built-in support for redirects, if you don't need a single page.

A lot of registered constants simplify the development, because your IDE will help you to set correct supports values,
post statuses or order by values in WP_Query.

= Base Model class =

Base Model class has wrappers for WP_Query, which works correctly with Custom Archives of Custom Post Types.
Standard custom archives doesn't have pagination, if you want to print them manually.

= Advanced Theme registration class =

Nice organized Theme registration class, which has a lot of hooks to patch standard WordPress installation with better
security and SEO optimizations.

= Easy creation of Admin options pages =
Built in wrapper for Titan Framework to rapidly build Admin option pages

= Theme Build Example =
You can find theme build example here: https://github.com/justcoded/wordpress-theme-boilerplate.

Don't forget to copy `requirements.php` to your theme and require it at the top of theme `functions.php` file!

= Have a feedback? =
Write to us on our github repository:
https://github.com/justcoded/wordpress-theme-framework

== Installation ==

1. Download, unzip and upload to your WordPress plugins directory
2. Download, unzip and upload Titan Framework (https://wordpress.org/plugins/titan-framework/) plugin to your WordPress plugins directory
3. Activate Just Theme Framework and Titan Framework plugins within you WordPress Administration Backend
4. Copy `just-theme-framework-checker.php` file from plugin directory to your theme's root and include it at the top of your theme `functions.php` file.
5. You are okay now to start using the framework.

== Upgrade Notice ==

To upgrade remove the old plugin folder. After than follow the installation steps 1-2.

== Changelog ==

= Version 3.1.5 - 6 Sep 2021 =
    * PHP 8 support
    * Tested with WordPress 5.8
= Version 3.1 - 22 Aug 2019 =
    * New: Theme class property to disable gutenberg for the whole site or specific post types.
    * New: Autoptimize post-content filter to add link rel nofollow to external links.
    * New: Base class for Cron jobs, which wraps WordPress Cron API and simplify its usage.
    * New: Base class for REST Controller, which wraps WordPress REST Controller and simplify its usage. 
= Version 3.0.2 - 21 Feb 2019 =
    * Bugfix: Faker generated images doesn't have thumbnails.
= Version 3.0.1 - 14 Jan 2019 =
    * Hotfix: Added support for Gutenberg and REST API for Custom Post Types..    
= Version 3.0 - 18 Sep 2018 =
    * New: ACF code fields registration support
    * New: https://github.com/StoutLogic/acf-builder package dependency and class wrappers
= Version 2.3.0 - 22 Aug 2018 =
    * New: Page Builder updates and new builder fields for autocomplete of Posts and Terms.
= Version 2.2.0 - 27 June 2018 =
    * New: Support FakerPress plugin to generate PostTypes data with complex custom fields structure.
= Version 2.1.1 - 10 January 2017 =
	* New: Updated View component and layouts wrapping rendering. Replaced `layout_open()`/`layout_close()` methods with `extends()` method. Renamed `render()` method to `include()`
	* New: Moved Model meta getter methods to separate Postmeta and Termmeta objects.
= Version 2.0.1 - 27 November 2017 =
	* New: Removed theme hooks to patch WordPress-generated .htaccess (moved to starter package)
= Version 2.0 - 21 November 2017 =
    * New: Namespaces refactored
    * New: Package renamed
    * New: Should be added as "mu-plugin"
    * New: Now is a part of: https://github.com/justcoded/wordpress-starter
= Version 1.3.2 - 28 September 2017 =
    * Bugfix: Hide Loadmore button after load last page, fix multiclick
= Version 1.3.1 - 11 August 2017 =
    * Bugfix: Missing css and js for Page Builder widgets preview
= Version 1.3 - 10 August 2017 =
    * New: Ability to set preview images for widgets based on SiteOrigin Widgets Bundle.
= Version 1.2.6 - 9 August 2017 =
    * Hotfix: Page Builder 2.5.10 update. Removed notices (for some reason builder render called twice in new builder version).
    * Bugfix: Remove test info from load more helper
= Version 1.2.5 - 7 August 2017 =
    * Bugfix: Just Post Preview plugin support: templates hierarchy hook works wrong
= Version 1.2.4 - 1 August 2017 =
    * New: Added default Ajax Load More functionality
= Version 1.2.3 - 26 May 2017 =
	* Hotfix: PageBuilderWidget raise fatal error when Site Origin Widgets Bundle plugin is not installed or deactivated.
= Version 1.2.2 - 12 May 2017 =
	* Hotfix: Page Builder design options (background, borders) were missing in new version. Fixed.
= Version 1.2.1 - 12 May 2017 =
	* Improvement: Change wp hook to widgets_init for register_widget method
= Version 1.2 - 12 May 2017 =
	* Refactor: Update Page Builder classes to work with new Site Origin Page Builder 2.5+
	* New: Added version folder prefix for Page Builder to be able to have different patches inside in future.
	* New: Page Builder Row/Widget layouts changed structure to container - style container (instead of wrapper - container) to match origin Page Builder logic
	* New: Base class for quick widgets creating based on Site Origin Widgets Bundle pack
	* New: Autoptimize class has new exceptions for Wordfence plugin by default.
= Version 1.1.3 - 20 April 2017 =
	* New: Enables SVG uploads support by default
= Version 1.1.2 - 29 March 2017 =
	* Code style: Fixed code style according to latest WPCS 0.11
= Version 1.1.1 - 24 March 2017 =
	* Improvements: Support of Autoptimize plugin improved, added more hooks. Set to move jquery.js, CF7 scripts first, just before optimized cached file.
= Version 1.1 =
	* Bug fix: Allow wp-login.php when theme is active, but requirements are not met.
	* New: Support for ACF and JCF inside Models. Now you can use "field_{fieldname}" magic property or get_field() method inside models.
= Version 1.0 =
	* Our mini-framework launch.
