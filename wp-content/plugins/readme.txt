=== Plugin Name ===
Contributors: Vadim Khukhryansky
Donate link: http://vadimk.com/
Tags: profile, author details, user data.
Requires at least: 2.0.2
Tested up to: 2.8.6
Stable tag: 0.1

Extra User Details is the simple plugin that allows you to add extra fields to the user profile page (e.g. social media links to Facebook, Twitter, LinkedIn profiels etc).

== Description ==

Extra User Details is the simple plugin that allows you to add extra fields to the user profile page (e.g. social media links to Facebook, Twitter, LinkedIn profiels etc).

Extra fields can be easily accessed in your templates like a general wordpress author details:

`<?php
global $wp_query;  
$curauth = $wp_query->get_queried_object();  
echo $curauth->field_name;
?>`

Plugin uses WP’s usermeta table. You can add and edit necessary fields at plugin options section in backend.

== Installation ==

Install it like other plugins, no special actions required.

1. Upload `extra_user_details.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ready to use. To configure - go to the Settings - Extra User Details section

== Frequently Asked Questions ==

= Will plugin be updated =

Maybe, anyway it's consistent to use.

== Changelog ==

= 0.1 =
* Initial release.