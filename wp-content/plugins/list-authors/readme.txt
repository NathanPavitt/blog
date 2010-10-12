=== List Authors ===
Contributors: Takaitra
Tags: authors, widget, sidebar
Requires at least: 2.0.2
Tested up to: 2.9
Stable tag: trunk

A widget to display a list of site authors.

== Description ==

A widget to display a list of authors in your WordPress blog. Includes options to enable/disable the features mentioned below. Version 1.2 is completely updated to use the new widget API and is multi-widget enabled. Fully XHTML compliant.

#### Features:
* Choose between an HTML list or comma-separated list.
* Can show number of published author posts.
* Allows administrator to be excluded from the list.
* Choose between displaying usernames or full names in the list.
* Allows users with 0 posts to be excluded from the list.
* Can include links to author-specific RSS feeds.


== Installation ==

1. Download the List Authors zip file.
2. Extract the files to your WordPress plugins directory.
3. Activate the plugin via the WordPress Plugins tab.
4. Configure the widget and place it on your blog using the Widget configuration page.


== Frequently Asked Questions ==

= Can I sort the list of authors alphabetically or by post count? =

No. This widget depends on the standard template tag [wp_list_authors](http://codex.wordpress.org/Template_Tags/wp_list_authors) which does not provide this ability. I have created a [feature request](http://core.trac.wordpress.org/ticket/10329) and, if I have time, I'll create and submit a patch.

= Can I limit the number of authors listed in the widget? =

This is a common request from those who have many contributors to their WordPress site (sometimes hundreds). Unfortunately, the answer for now is the same as above.

= What if I have further questions? =

If you have any questions or comments, feel free to [leave a comment](http://www.takaitra.com/projects/list-authors) on the project page and I will respond as soon as I can.

== Screenshots ==

1. The List Authors widget showing two authors along with links to their respective RSS feeds.
2. The List Authors widget configuration.

== Changelog ==

= 1.2 =
* Updated to use the new widget API introduced in WordPress 2.8.
* Added the HTML/comma-separated list dropdown.
* Multi-widget enabled (it is now possible to have two or more copies of the same widget on one blog).

= 1.1.1 =
* Small fix to make the widget fully XHTML compliant.

= 1.1 =
* Initial release.

== Upgrade Notice ==

= 1.2 =
Major update to use the new widget API and enable multi-widget functionality.
