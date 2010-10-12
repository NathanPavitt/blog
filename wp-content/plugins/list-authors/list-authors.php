<?php
/*
Plugin Name: List Authors
Plugin URI: http://www.takaitra.com/projects/list-authors
Description: Adds a widget to display a list of site authors. Can be configured to include post counts and links to author RSS feeds.
Version: 1.2
Author: Matthew Toso
Author URI: http://www.takaitra.com/
*/

/*  Copyright 2009  Matthew Toso  (email : takaitra@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

// Set the location constant
define('LISTAUTHORS_URL', WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');
if ( version_compare( $GLOBALS['wp_version'], '2.8', '>=' ) ) {
	// Use class-based widget approach introduced in WordPress 2.8
	if ( !class_exists( 'ListAuthorsWidget' ) ) {
		clean_author_options();
		include_once( 'class-list-authors.php' );
	}
} else {
	// Use legacy code for compatibility with pre-2.8 installations
	include_once( 'prebaker.php' );
}

function clean_author_options() {
	// Option names changed for v1.2 so let's start over if needed
	$options =  get_option( 'widget_authors' );
	if ( isset( $options['Title'] ) ) {
		delete_option( 'widget_authors' );
	}
}
