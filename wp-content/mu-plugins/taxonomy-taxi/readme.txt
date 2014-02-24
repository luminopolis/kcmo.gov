=== Taxonomy Taxi ===
Contributors: postpostmodern
Donate link: http://www.heifer.org/
Tags: custom taxonomies, taxonomy
Requires at least: 3.2
Tested up to: 3.6
Stable tag: trunk

== Description ==
Automatically display custom taxonomy information in wp-admin/edit.php
Not tested with versions pre- 3.2 - requires PHP 5

== Installation ==
1. Place /taxonomi-taxi/ directory in /wp-content/plugins/
1. Add custom taxonomies manually, or through a plugin like [Custom Post Type UI](http://webdevstudios.com/support/wordpress-plugins/)
1. Your edit posts table (/wp-admin/edit.php) will now show all associated taxonomies automatically!

== Changelog ==
= .77 =
* Register column for pages

= .76 =
* Fix array functions when no posts

= .75 =
* Fix data to show in column on hierarchical post types

= .7 =
* Use wp_dropdown_categories() for filtering ui

= 0.61 =
* Fix for hierarchical post types

= .6 =
* Minor cleanup, use WP_Post class for post results

= 0.58 =
* Order taxonomies alphabetically, sortable column for custom post types *

= 0.57 =
* Minor code cleanup, screenshots *

= 0.56 =
* Fixed bug in post_type when clicking on custom taxonomy in edit table

= 0.55 =
* Fixed bug in filtering table multiple times ($post_type was being set to an array)
* Applies filters and actions only on wp-admin/edit.php, using `load-edit.php` action

= 0.51 =
* Fixed bug in table names

= 0.5 =
* First release

== Screenshots ==
1. hack
2. Custom 'sausage' taxonomy (for reference)
3. Displaying 'Sausage' Column, which can be filtered to a specific term through the added drop-down, or clicking on individual term.