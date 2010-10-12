<?php
/*
Plugin Name: FeedWordPress
Plugin URI: http://feedwordpress.radgeek.com/
Description: simple and flexible Atom/RSS syndication for WordPress
Version: 2010.0602
Author: Charles Johnson
Author URI: http://radgeek.com/
License: GPL
*/

/**
 * @package FeedWordPress
 * @version 2010.0602
 */

# This uses code derived from:
# -	wp-rss-aggregate.php by Kellan Elliot-McCrea <kellan@protest.net>
# -	HTTP Navigator 2 by Keyvan Minoukadeh <keyvan@k1m.com>
# -	Ultra-Liberal Feed Finder by Mark Pilgrim <mark@diveintomark.org>
# according to the terms of the GNU General Public License.
#
# INSTALLATION: see readme.txt or <http://projects.radgeek.com/install>
#
# USAGE: once FeedWordPress is installed, you manage just about everything from
# the WordPress Dashboard, under the Syndication menu. To ensure that fresh
# content is added as it becomes available, you can convince your contributors
# to put your XML-RPC URI (if WordPress is installed at
# <http://www.zyx.com/blog>, XML-RPC requests should be sent to
# <http://www.zyx.com/blog/xmlrpc.php>), or update manually under the
# Syndication menu, or set up automatic updates under Syndication --> Settings,
# or use a cron job.

# -- Don't change these unless you know what you're doing...

define ('FEEDWORDPRESS_VERSION', '2010.0602');
define ('FEEDWORDPRESS_AUTHOR_CONTACT', 'http://radgeek.com/contact');

// Defaults
define ('DEFAULT_SYNDICATION_CATEGORY', 'Contributors');
define ('DEFAULT_UPDATE_PERIOD', 60); // value in minutes

if (isset($_REQUEST['feedwordpress_debug'])) :
	$feedwordpress_debug = $_REQUEST['feedwordpress_debug'];
else :
	$feedwordpress_debug = get_option('feedwordpress_debug');
	if (is_string($feedwordpress_debug)) :
		$feedwordpress_debug = ($feedwordpress_debug == 'yes');
	endif;
endif;
define ('FEEDWORDPRESS_DEBUG', $feedwordpress_debug);

define ('FEEDWORDPRESS_CAT_SEPARATOR_PATTERN', '/[:\n]/');
define ('FEEDWORDPRESS_CAT_SEPARATOR', "\n");

define ('FEEDVALIDATOR_URI', 'http://feedvalidator.org/check.cgi');

define ('FEEDWORDPRESS_FRESHNESS_INTERVAL', 10*60); // Every ten minutes

define ('FWP_SCHEMA_HAS_USERMETA', 2966);
define ('FWP_SCHEMA_USES_ARGS_TAXONOMY', 12694); // Revision # for using $args['taxonomy'] to get link categories
define ('FWP_SCHEMA_20', 3308); // Database schema # for WP 2.0
define ('FWP_SCHEMA_21', 4772); // Database schema # for WP 2.1
define ('FWP_SCHEMA_23', 5495); // Database schema # for WP 2.3
define ('FWP_SCHEMA_25', 7558); // Database schema # for WP 2.5
define ('FWP_SCHEMA_26', 8201); // Database schema # for WP 2.6
define ('FWP_SCHEMA_27', 9872); // Database schema # for WP 2.7
define ('FWP_SCHEMA_28', 11548); // Database schema # for WP 2.8
define ('FWP_SCHEMA_29', 12329); // Database schema # for WP 2.9
define ('FWP_SCHEMA_30', 12694); // Database schema # for WP 3.0

if (FEEDWORDPRESS_DEBUG) :
	// Help us to pick out errors, if any.
	ini_set('error_reporting', E_ALL & ~E_NOTICE);
	ini_set('display_errors', true);
	
	 // When testing we don't want cache issues to interfere. But this is
	 // a VERY BAD SETTING for a production server. Webmasters will eat your 
	 // face for breakfast if you use it, and the baby Jesus will cry. So
	 // make sure FEEDWORDPRESS_DEBUG is FALSE for any site that will be
	 // used for more than testing purposes!
	define('FEEDWORDPRESS_CACHE_AGE', 1);
	define('FEEDWORDPRESS_CACHE_LIFETIME', 1);
	define('FEEDWORDPRESS_FETCH_TIME_OUT', 60);
else :
	// Hold onto data all day for conditional GET purposes,
	// but consider it stale after 1 min (requiring a conditional GET)
	define('FEEDWORDPRESS_CACHE_LIFETIME', 24*60*60);
	define('FEEDWORDPRESS_CACHE_AGE', 1*60);
	define('FEEDWORDPRESS_FETCH_TIME_OUT', 10);
endif;

// Use our the cache settings that we want.
add_filter('wp_feed_cache_transient_lifetime', array('FeedWordPress', 'cache_lifetime'));

// Ensure that we have SimplePie loaded up and ready to go.
// We no longer need a MagpieRSS upgrade module. Hallelujah!
require_once(ABSPATH . WPINC . '/feed.php');

if (isset($wp_db_version)) :
	if ($wp_db_version >= FWP_SCHEMA_23) :
		require_once (ABSPATH . WPINC . '/registration.php'); 		// for wp_insert_user
	elseif ($wp_db_version >= FWP_SCHEMA_21) : // WordPress 2.1 and 2.2, but not 2.3
		require_once (ABSPATH . WPINC . '/registration.php'); 		// for wp_insert_user
		require_once (ABSPATH . 'wp-admin/admin-db.php'); 		// for wp_insert_category 
	elseif ($wp_db_version >= FWP_SCHEMA_20) : // WordPress 2.0
		require_once (ABSPATH . WPINC . '/registration-functions.php');	// for wp_insert_user
		require_once (ABSPATH . 'wp-admin/admin-db.php');		// for wp_insert_category
	endif;
endif;

require_once (ABSPATH . WPINC . '/class-feed.php');
require_once(dirname(__FILE__) . '/compatability.php'); // LEGACY API: Replicate or mock up functions for legacy support purposes
require_once(dirname(__FILE__) . '/feedwordpresshtml.class.php');
require_once(dirname(__FILE__) . '/feedwordpress-content-type-sniffer.class.php');

// Magic quotes are just about the stupidest thing ever.
if (is_array($_POST)) :
	$fwp_post = stripslashes_deep($_POST);
endif;

// Get the path relative to the plugins directory in which FWP is stored
preg_match (
	'|/wp-content/plugins/(.+)$|',
	dirname(__FILE__),
	$ref
);

if (isset($ref[1])) :
	$fwp_path = $ref[1];
else : // Something went wrong. Let's just guess.
	$fwp_path = 'feedwordpress';
endif;

function feedwordpress_admin_scripts () {
	global $fwp_path;

	wp_enqueue_script('post'); // for magic tag and category boxes
	if (!FeedWordPressCompatibility::test_version(FWP_SCHEMA_29)) : // < 2.9
		wp_enqueue_script('thickbox'); // for fold-up boxes
	endif;
	wp_enqueue_script('admin-forms'); // for checkbox selection

	wp_register_script('feedwordpress-elements', WP_PLUGIN_URL.'/'.$fwp_path.'/feedwordpress-elements.js');
	wp_enqueue_script('feedwordpress-elements');
}

// If this is a FeedWordPress admin page, queue up scripts for AJAX functions that FWP uses
// If it is a display page or a non-FeedWordPress admin page, don't.
if (is_admin() and isset($_REQUEST['page']) and preg_match("|^{$fwp_path}/|", $_REQUEST['page'])) :
	add_action('admin_print_scripts', 'feedwordpress_admin_scripts');

	wp_register_style('feedwordpress-elements', WP_PLUGIN_URL.'/'.$fwp_path.'/feedwordpress-elements.css');

	wp_enqueue_style('dashboard');
	wp_enqueue_style('feedwordpress-elements');

	if (function_exists('wp_admin_css')) :
		if (fwp_test_wp_version(FWP_SCHEMA_25)) :
			wp_admin_css('css/dashboard');
		endif;
	endif;
endif;

if (!FeedWordPress::needs_upgrade()) : // only work if the conditions are safe!

	# Syndicated items are generally received in output-ready (X)HTML and
	# should not be folded, crumpled, mutilated, or spindled by WordPress
	# formatting filters. But we don't want to interfere with filters for
	# any locally-authored posts, either.
	#
	# What WordPress should really have is a way for upstream filters to
	# stop downstream filters from running at all. Since it doesn't, and
	# since a downstream filter can't access the original copy of the text
	# that is being filtered, what we will do here is (1) save a copy of the
	# original text upstream, before any other filters run, and then (2)
	# retrieve that copy downstream, after all the other filters run, *if*
	# this is a syndicated post

	add_filter('the_content', 'feedwordpress_preserve_syndicated_content', -10000);
	add_filter('the_content', 'feedwordpress_restore_syndicated_content', 10000);
	
	add_action('atom_entry', 'feedwordpress_item_feed_data');
	
	# Filter in original permalinks if the user wants that
	add_filter('post_link', 'syndication_permalink', 1);

	# When foreign URLs are used for permalinks in feeds or display
	# contexts, they need to be escaped properly.
	add_filter('the_permalink', 'syndication_permalink_escaped');
	add_filter('the_permalink_rss', 'syndication_permalink_escaped');
	
	add_filter('post_comments_feed_link', 'syndication_comments_feed_link');

	# WTF? By default, wp_insert_link runs incoming link_url and link_rss
	# URIs through default filters that include `wp_kses()`. But `wp_kses()`
	# just happens to escape any occurrence of & to &amp; -- which just
	# happens to fuck up any URI with a & to separate GET parameters.
	remove_filter('pre_link_rss', 'wp_filter_kses');
	remove_filter('pre_link_url', 'wp_filter_kses');
	
	# Admin menu
	add_action('admin_menu', 'fwp_add_pages');
	add_action('admin_notices', 'fwp_check_debug');

	add_action('admin_menu', 'feedwordpress_add_post_edit_controls');
	add_action('save_post', 'feedwordpress_save_post_edit_controls');

	add_action('admin_footer', array('FeedWordPress', 'admin_footer'));
	
	# Inbound XML-RPC update methods
	add_filter('xmlrpc_methods', 'feedwordpress_xmlrpc_hook');
	
	# Outbound XML-RPC ping reform
	remove_action('publish_post', 'generic_ping'); // WP 1.5.x
	remove_action('do_pings', 'do_all_pings', 10, 1); // WP 2.1, 2.2
	remove_action('publish_post', '_publish_post_hook', 5, 1); // WP 2.3

	add_action('publish_post', 'fwp_publish_post_hook', 5, 1);
	add_action('do_pings', 'fwp_do_pings', 10, 1);
	add_action('feedwordpress_update', 'fwp_hold_pings');
	add_action('feedwordpress_update_complete', 'fwp_release_pings');

	# Hook in logging functions only if the logging option is ON
	$update_logging = get_option('feedwordpress_update_logging');
	if ($update_logging == 'yes') :
		add_action('post_syndicated_item', 'log_feedwordpress_post', 100);
		add_action('update_syndicated_item', 'log_feedwordpress_update_post', 100);
		add_action('feedwordpress_update', 'log_feedwordpress_update_feeds', 100);
		add_action('feedwordpress_check_feed', 'log_feedwordpress_check_feed', 100);
		add_action('feedwordpress_update_complete', 'log_feedwordpress_update_complete', 100);
	endif;
	
	if (FeedWordPress::update_requested()) :
		if (FEEDWORDPRESS_DEBUG) :
			add_action('post_syndicated_item', 'debug_out_feedwordpress_post', 100);
			add_action('update_syndicated_item', 'debug_out_feedwordpress_update_post', 100);
			add_action('feedwordpress_update', 'debug_out_feedwordpress_update_feeds', 100);
			add_action('feedwordpress_check_feed', 'debug_out_feedwordpress_check_feed', 100);
			add_action('feedwordpress_update_complete', 'debug_out_feedwordpress_update_complete', 100);
		endif;

		add_action('feedwordpress_check_feed_complete', 'debug_out_feedwordpress_feed_error', 100, 3);
	endif;

	# Cron-less auto-update. Hooray!
	$autoUpdateHook = get_option('feedwordpress_automatic_updates');
	if ($autoUpdateHook != 'init') :
		$autoUpdateHook = 'shutdown';
	endif;
	add_action($autoUpdateHook, 'feedwordpress_auto_update');

	add_action('init', 'feedwordpress_update_magic_url');

	# Default sanitizers
	add_filter('syndicated_item_content', array('SyndicatedPost', 'resolve_relative_uris'), 0, 2);
	add_filter('syndicated_item_content', array('SyndicatedPost', 'sanitize_content'), 0, 2);

else :
	# Hook in the menus, which will just point to the upgrade interface
	add_action('admin_menu', 'fwp_add_pages');
endif; // if (!FeedWordPress::needs_upgrade())

function feedwordpress_auto_update () {
	if (FeedWordPress::stale()) :
		$feedwordpress = new FeedWordPress;
		$feedwordpress->update();
	endif;
} /* feedwordpress_auto_update () */

function feedwordpress_update_magic_url () {
	global $wpdb;

	// Explicit update request in the HTTP request (e.g. from a cron job)
	if (FeedWordPress::update_requested()) :
		$feedwordpress = new FeedWordPress;
		$feedwordpress->update(FeedWordPress::update_requested_url());
		
		if (FEEDWORDPRESS_DEBUG and count($wpdb->queries) > 0) :
			$mysqlTime = 0.0;
			$byTime = array();
			foreach ($wpdb->queries as $query) :
				$time = $query[1] * 1000000.0;
				$mysqlTime += $query[1];
				if (!isset($byTime[$time])) : $byTime[$time] = array(); endif;
				$byTime[$time][] = $query[0]. ' // STACK: ' . $query[2];   
			endforeach;
			krsort($byTime);
       
			foreach ($byTime as $time => $querySet) :
				foreach ($querySet as $query) :
					print "[".(sprintf('%4.4f', $time/1000.0)) . "ms] $query\n";
				endforeach;
			endforeach;
			echo "[feedwordpress] $wpdb->num_queries queries. $mysqlTime seconds in MySQL. Total of "; timer_stop(1); print " seconds.";
		endif;


    		// Magic URL should return nothing but a 200 OK header packet
		// when successful.
		exit;
	endif;
} /* feedwordpress_magic_update_url () */

################################################################################
## LOGGING FUNCTIONS: log status updates to error_log if you want it ###########
################################################################################

function log_feedwordpress_post ($id) {
	$post = wp_get_single_post($id);
	error_log("[".date('Y-m-d H:i:s')."][feedwordpress] posted "
		."'{$post->post_title}' ({$post->post_date})");
}

function log_feedwordpress_update_post ($id) {
	$post = wp_get_single_post($id);
	error_log("[".date('Y-m-d H:i:s')."][feedwordpress] updated "
		."'{$post->post_title}' ({$post->post_date})"
		." (as of {$post->post_modified})");
}

function log_feedwordpress_update_feeds ($uri) {
	error_log("[".date('Y-m-d H:i:s')."][feedwordpress] update('$uri')");
}

function log_feedwordpress_check_feed ($feed) {
	$uri = $feed['link/uri']; $name = $feed['link/name'];
	error_log("[".date('Y-m-d H:i:s')."][feedwordpress] Examining $name <$uri>");
}

function log_feedwordpress_update_complete ($delta) {
	$mesg = array();
	if (isset($delta['new'])) $mesg[] = 'added '.$delta['new'].' new posts';
	if (isset($delta['updated'])) $mesg[] = 'updated '.$delta['updated'].' existing posts';
	if (empty($mesg)) $mesg[] = 'nothing changed';

	error_log("[".date('Y-m-d H:i:s')."][feedwordpress] "
		.(is_null($delta) ? "Error: I don't syndicate that URI"
		: implode(' and ', $mesg)));
}

function debug_out_feedwordpress_post ($id) {
	$post = wp_get_single_post($id);
	print ("[".date('Y-m-d H:i:s')."][feedwordpress] posted "
		."'{$post->post_title}' ({$post->post_date})\n");
}

function debug_out_feedwordpress_update_post ($id) {
	$post = wp_get_single_post($id);
	print ("[".date('Y-m-d H:i:s')."][feedwordpress] updated "
		."'{$post->post_title}' ({$post->post_date})"
		." (as of {$post->post_modified})\n");
}

function debug_out_feedwordpress_update_feeds ($uri) {
	print ("[".date('Y-m-d H:i:s')."][feedwordpress] update('$uri')\n");
}

function debug_out_feedwordpress_check_feed ($feed) {
	$uri = $feed['link/uri']; $name = $feed['link/name'];
	print ("[".date('Y-m-d H:i:s')."][feedwordpress] Examining $name <$uri>\n");
}

function debug_out_feedwordpress_update_complete ($delta) {
	$mesg = array();
	if (isset($delta['new'])) $mesg[] = 'added '.$delta['new'].' new posts';
	if (isset($delta['updated'])) $mesg[] = 'updated '.$delta['updated'].' existing posts';
	if (empty($mesg)) $mesg[] = 'nothing changed';

	print ("[".date('Y-m-d H:i:s')."][feedwordpress] "
		.(is_null($delta) ? "Error: I don't syndicate that URI"
		: implode(' and ', $mesg))."\n");
}

function debug_out_feedwordpress_feed_error ($feed, $added, $dt) {
	if (is_wp_error($added)) :
		$mesgs = $added->get_error_messages();
		foreach ($mesgs as $mesg) :
			echo "[feedwordpress] Error updating [{$feed['link/uri']}]: $mesg\n";
		endforeach;		
	endif;
}

################################################################################
## TEMPLATE API: functions to make your templates syndication-aware ############
################################################################################

/**
 * is_syndicated: Tests whether the current post in a Loop context, or a post
 * given by ID number, was syndicated by FeedWordPress. Useful for templates
 * to determine whether or not to retrieve syndication-related meta-data in
 * displaying a post.
 *
 * @param int $id The post to check for syndicated status. Defaults to the current post in a Loop context.
 * @return bool TRUE if the post's meta-data indicates it was syndicated; FALSE otherwise 
 */ 
function is_syndicated ($id = NULL) {
	return (strlen(get_syndication_feed_id($id)) > 0);
} /* function is_syndicated() */

function get_syndication_source_link ($original = NULL, $id = NULL) {
	if (is_null($original)) : $original = FeedWordPress::use_aggregator_source_data();
	endif;

	if ($original) : $vals = get_post_custom_values('syndication_source_uri_original', $id);
	else : $vals = array();
	endif;
	
	if (count($vals) == 0) : $vals = get_post_custom_values('syndication_source_uri', $id);
	endif;
	
	if (count($vals) > 0) : $ret = $vals[0]; else : $ret = NULL; endif;

	return $ret;
} /* function get_syndication_source_link() */

function the_syndication_source_link ($original = NULL, $id = NULL) {
	echo get_syndication_source_link($original, $id);
}

function feedwordpress_display_url ($url, $before = 60, $after = 0) {
	$bits = parse_url($url);
	
	// Strip out crufty subdomains
  	$bits['host'] = preg_replace('/^www[0-9]*\./i', '', $bits['host']);

  	// Reassemble bit-by-bit with minimum of crufty elements
	$url = (isset($bits['user'])?$bits['user'].'@':'')
		.(isset($bits['host'])?$bits['host']:'')
		.(isset($bits['path'])?$bits['path']:'')
		.(isset($uri_bits['port'])?':'.$uri_bits['port']:'')
		.(isset($bits['query'])?'?'.$bits['query']:'');

	if (strlen($url) > ($before+$after)) :
		$url = substr($url, 0, $before).'…'.substr($url, 0 - $after, $after);
	endif;

	return $url;
}

function get_syndication_source ($original = NULL, $id = NULL) {
	if (is_null($original)) :
		$original = FeedWordPress::use_aggregator_source_data();
	endif;

	if ($original) :
		$vals = get_post_custom_values('syndication_source_original', $id);
	else :
		$vals = array();
	endif;
	
	if (count($vals) == 0) :
		$vals = get_post_custom_values('syndication_source', $id);
	endif;
	
	if (count($vals) > 0) :
		$ret = $vals[0];
	else :
		$ret = NULL;
	endif;

	if (is_null($ret) or strlen(trim($ret)) == 0) :
		// Fall back to URL of blog
		$ret = feedwordpress_display_url(get_syndication_source_link());
	endif;

	return $ret;
} /* function get_syndication_source() */

function the_syndication_source ($original = NULL, $id = NULL) { echo get_syndication_source($original, $id); }

function get_syndication_feed ($original = NULL, $id = NULL) {
	if (is_null($original)) : $original = FeedWordPress::use_aggregator_source_data();
	endif;

	if ($original) : $vals = get_post_custom_values('syndication_feed_original', $id);
	else : $vals = array();
	endif;

	if (count($vals) == 0) : $vals = get_post_custom_values('syndication_feed', $id);
	endif;
	
	if (count($vals) > 0) : $ret = $vals[0]; else : $ret = NULL; endif;

	return $ret;
} /* function get_syndication_feed() */

function the_syndication_feed ($original = NULL, $id = NULL) { echo get_syndication_feed($original, $id); }

function get_syndication_feed_guid ($original = NULL, $id = NULL) {
	if (is_null($original)) : $original = FeedWordPress::use_aggregator_source_data();
	endif;

	if ($original) : $vals = get_post_custom_values('syndication_source_id_original', $id);
	else : $vals = array();
	endif;
	
	if (count($vals) == 0) : $vals = array(get_feed_meta('feed/id', $id));
	endif;
	
	if (count($vals) > 0) : $ret = $vals[0]; else : $ret = NULL; endif;

	return $ret;
} /* function get_syndication_feed_guid () */

function the_syndication_feed_guid ($original = NULL, $id = NULL) { echo get_syndication_feed_guid($original, $id); }

function get_syndication_feed_id ($id = NULL) { list($u) = get_post_custom_values('syndication_feed_id', $id); return $u; }
function the_syndication_feed_id ($id = NULL) { echo get_syndication_feed_id($id); }

$feedwordpress_linkcache =  array (); // only load links from database once
function get_syndication_feed_object ($id = NULL) {
	global $feedwordpress_linkcache;

	$link = NULL;

	$feed_id = get_syndication_feed_id($id);
	if (strlen($feed_id) > 0):
		if (isset($feedwordpress_linkcache[$feed_id])) :
			$link = $feedwordpress_linkcache[$feed_id];
		else :
			$link = new SyndicatedLink($feed_id);
			$feedwordpress_linkcache[$feed_id] = $link;
		endif;
	endif;
	return $link;
}

function get_feed_meta ($key, $id = NULL) {
	$ret = NULL;

	$link = get_syndication_feed_object($id);
	if (is_object($link) and isset($link->settings[$key])) :
		$ret = $link->settings[$key];
	endif;
	return $ret;
} /* get_feed_meta() */

function get_syndication_permalink ($id = NULL) {
	list($u) = get_post_custom_values('syndication_permalink', $id); return $u;
}
function the_syndication_permalink ($id = NULL) {
	echo get_syndication_permalink($id);
}

/**
 * get_local_permalink: returns a string containing the internal permalink
 * for a post (whether syndicated or not) on your local WordPress installation.
 * This may be useful if you want permalinks to point to the original source of
 * an article for most purposes, but want to retrieve a URL for the local
 * representation of the post for one or two limited purposes (for example,
 * linking to a comments page on your local aggregator site).
 *
 * @param $id The numerical ID of the post to get the permalink for. If empty,
 * 	defaults to the current post in a Loop context.
 * @return string The URL of the local permalink for this post.
 *
 * @uses get_permalink()
 * @global $feedwordpress_the_original_permalink
 *
 * @since 2010.0217
 */
function get_local_permalink ($id = NULL) {
	global $feedwordpress_the_original_permalink;
	
	// get permalink, and thus activate filter and force global to be filled
	// with original URL.
	$url = get_permalink($id);
	return $feedwordpress_the_original_permalink;
} /* get_local_permalink() */

/**
 * the_original_permalink: displays the contents of get_original_permalink()
 *
 * @param $id The numerical ID of the post to get the permalink for. If empty,
 * 	defaults to the current post in a Loop context.
 *
 * @uses get_local_permalinks()
 * @uses apply_filters
 *
 * @since 2010.0217
 */
function the_local_permalink ($id = NULL) {
	print apply_filters('the_permalink', get_local_permalink($id));
} /* function the_local_permalink() */

################################################################################
## FILTERS: syndication-aware handling of post data for templates and feeds ####
################################################################################

$feedwordpress_the_syndicated_content = NULL;
$feedwordpress_the_original_permalink = NULL;

function feedwordpress_preserve_syndicated_content ($text) {
	global $feedwordpress_the_syndicated_content;

	$globalExpose = (get_option('feedwordpress_formatting_filters') == 'yes');
	$localExpose = get_post_custom_values('_feedwordpress_formatting_filters');
	$expose = ($globalExpose or ((count($localExpose) > 0) and $localExpose[0]));

	if ( is_syndicated() and !$expose ) :
		$feedwordpress_the_syndicated_content = $text;
	else :
		$feedwordpress_the_syndicated_content = NULL;
	endif;
	return $text;
}

function feedwordpress_restore_syndicated_content ($text) {
	global $feedwordpress_the_syndicated_content;
	
	if ( !is_null($feedwordpress_the_syndicated_content) ) :
		$text = $feedwordpress_the_syndicated_content;
	endif;

	return $text;
}

function feedwordpress_item_feed_data () {
	// In a post context....
	if (is_syndicated()) :
?>
<source>
	<title><?php print htmlspecialchars(get_syndication_source()); ?></title>
	<link rel="alternate" type="text/html" href="<?php print htmlspecialchars(get_syndication_source_link()); ?>" />
	<link rel="self" href="<?php print htmlspecialchars(get_syndication_feed()); ?>" />
<?php
	$id = get_syndication_feed_guid();
	if (strlen($id) > 0) :
?>
	<id><?php print htmlspecialchars($id); ?></id>
<?php
	endif;
	$updated = get_feed_meta('feed/updated');
	if (strlen($updated) > 0) : ?>
	<updated><?php print $updated; ?></updated>
<?php
	endif;
?>
</source>
<?php
	endif;
}

/**
 * syndication_permalink: Allow WordPress to use the original remote URL of
 * syndicated posts as their permalink. Can be turned on or off by by setting in
 * Syndication => Posts & Links. Saves the old internal permalink in a global
 * variable for later use.
 *
 * @param string $permalink The internal permalink
 * @return string The new permalink. Same as the old if the post is not
 *	syndicated, or if FWP is set to use internal permalinks, or if the post
 *	was syndicated, but didn't have a proper permalink recorded.
 *
 * @uses FeedWordPress::munge_permalinks()
 * @uses get_syndication_permalink()
 * @global $feedwordpress_the_original_permalink
 */ 
function syndication_permalink ($permalink = '') {
	global $feedwordpress_the_original_permalink;
	
	// Save the local permalink in case we need to retrieve it later.
	$feedwordpress_the_original_permalink = $permalink;

	// Map this permalink to a post ID so we can get the correct permalink
	// even outside of the Post Loop. Props Björn.
	$id = url_to_postid($permalink);

	$munge = false;
	$link = get_syndication_feed_object($id);
	if (is_object($link)) :
		$munge = ($link->setting('munge permalink', 'munge_permalink', 'yes') != 'no');
	endif;

	if ($munge):
		$uri = get_syndication_permalink($id);
		$permalink = ((strlen($uri) > 0) ? $uri : $permalink);
	endif;
	return $permalink;
} /* function syndication_permalink () */

/**
 * syndication_permalink_escaped: Escape XML special characters in syndicated
 * permalinks when used in feed contexts and HTML display contexts.
 *
 * @param string $permalink
 * @return string
 *
 * @uses is_syndicated()
 * @uses FeedWordPress::munge_permalinks()
 *
 */
function syndication_permalink_escaped ($permalink) {
	if (is_syndicated() and FeedWordPress::munge_permalinks()) :
		// This is a foreign link; WordPress can't vouch for its not
		// having any entities that need to be &-escaped. So we'll do
		// it here.
		$permalink = esc_html($permalink);
	endif;
	return $permalink;
} /* function syndication_permalink_escaped() */ 

/**
 * syndication_comments_feed_link: Escape XML special characters in comments
 * feed links 
 *
 * @param string $link
 * @return string
 *
 * @uses is_syndicated()
 * @uses FeedWordPress::munge_permalinks()
 */
function syndication_comments_feed_link ($link) {
	global $feedwordpress_the_original_permalink, $id;

	if (is_syndicated() and FeedWordPress::munge_permalinks()) :
		// If the source post provided a comment feed URL using
		// wfw:commentRss or atom:link/@rel="replies" we can make use of
		// that value here.
		$source = get_syndication_feed_object();
		$replacement = NULL;
		if ($source->setting('munge comments feed links', 'munge_comments_feed_links', 'yes') != 'no') :
			$commentFeeds = get_post_custom_values('wfw:commentRSS');
			if (
				is_array($commentFeeds)
				and (count($commentFeeds) > 0)
				and (strlen($commentFeeds[0]) > 0)
			) :
				$replacement = $commentFeeds[0];
				
				// This is a foreign link; WordPress can't vouch for its not
				// having any entities that need to be &-escaped. So we'll do it
				// here.
				$replacement = esc_html($replacement);
			endif;
		endif;
		
		if (is_null($replacement)) :
			// Q: How can we get the proper feed format, since the
			// format is, stupidly, not passed to the filter?
			// A: Kludge kludge kludge kludge!
			$fancy_permalinks = ('' != get_option('permalink_structure'));
			if ($fancy_permalinks) :
				preg_match('|/feed(/([^/]+))?/?$|', $link, $ref);

				$format = (isset($ref[2]) ? $ref[2] : '');
				if (strlen($format) == 0) : $format = get_default_feed(); endif;

				$replacement = trailingslashit($feedwordpress_the_original_permalink) . 'feed';
				if ($format != get_default_feed()) :
					$replacement .= '/'.$format;
				endif;
				$replacement = user_trailingslashit($replacement, 'single_feed');
			else :
				// No fancy permalinks = no problem
				// WordPress doesn't call get_permalink() to
				// generate the comment feed URL, so the
				// comments feed link is never munged by FWP.
			endif;
		endif;
		
		if (!is_null($replacement)) : $link = $replacement; endif;
	endif;
	return $link;
} /* function syndication_comments_feed_link() */

################################################################################
## ADMIN MENU ADD-ONS: register Dashboard management pages #####################
################################################################################

function fwp_add_pages () {
	global $fwp_capability;
	global $fwp_path;

	$menu = array('Syndicated Sites', 'Syndication', $fwp_capability['manage_links'], $fwp_path.'/syndication.php', NULL);
	if (fwp_test_wp_version(FWP_SCHEMA_27)) :
		// add icon parameter
		$menu[] = WP_PLUGIN_URL.'/'.$fwp_path.'/feedwordpress-tiny.png';
	endif;

	call_user_func_array('add_menu_page', $menu);
	add_submenu_page($fwp_path.'/syndication.php', 'Syndicated Feeds & Updates', 'Feeds & Updates', $fwp_capability['manage_options'], $fwp_path.'/feeds-page.php');
	add_submenu_page($fwp_path.'/syndication.php', 'Syndicated Posts & Links', 'Posts & Links', $fwp_capability['manage_options'], $fwp_path.'/posts-page.php');
	add_submenu_page($fwp_path.'/syndication.php', 'Syndicated Authors', 'Authors', $fwp_capability['manage_options'], $fwp_path.'/authors-page.php');
	add_submenu_page($fwp_path.'/syndication.php', 'Categories'.FEEDWORDPRESS_AND_TAGS, 'Categories'.FEEDWORDPRESS_AND_TAGS, $fwp_capability['manage_options'], $fwp_path.'/categories-page.php');
	add_submenu_page($fwp_path.'/syndication.php', 'FeedWordPress Performance', 'Performance', $fwp_capability['manage_options'], $fwp_path.'/performance-page.php');
	add_submenu_page($fwp_path.'/syndication.php', 'FeedWordPress Diagnostics', 'Diagnostics', $fwp_capability['manage_options'], $fwp_path.'/diagnostics-page.php');
} /* function fwp_add_pages () */

function fwp_check_debug () {
	// This is a horrible fucking kludge that I have to do because the
	// admin notice code is triggered before the code that updates the
	// setting.
	if (isset($_POST['feedwordpress_debug'])) :
		$feedwordpress_debug = $_POST['feedwordpress_debug'];
	else :
		$feedwordpress_debug = get_option('feedwordpress_debug');
	endif;
	if ($feedwordpress_debug==='yes') :
?>
		<div class="error">
<p><strong>FeedWordPress warning.</strong> Debugging mode is <strong>ON</strong>.
While it remains on, FeedWordPress displays many diagnostic error messages,
warnings, and notices that are ordinarily suppressed, and also turns off all
caching of feeds. Use with caution: this setting is absolutely inappropriate
for a production server.</p>
		</div>
<?php
	endif;
} /* function fwp_check_debug () */

################################################################################
## fwp_hold_pings() and fwp_release_pings(): Outbound XML-RPC ping reform   ####
## ... 'coz it's rude to send 500 pings the first time your aggregator runs ####
################################################################################

$fwp_held_ping = NULL;		// NULL: not holding pings yet

function fwp_hold_pings () {
	global $fwp_held_ping;
	if (is_null($fwp_held_ping)):
		$fwp_held_ping = 0;	// 0: ready to hold pings; none yet received
	endif;
}

function fwp_release_pings () {
	global $fwp_held_ping;
	if ($fwp_held_ping):
		if (function_exists('wp_schedule_single_event')) :
			wp_schedule_single_event(time(), 'do_pings');
		else :
			generic_ping($fwp_held_ping);
		endif;
	endif;
	$fwp_held_ping = NULL;	// NULL: not holding pings anymore
}

function fwp_do_pings () {
	if (!is_null($fwp_held_ping) and $post_id) : // Defer until we're done updating
		$fwp_held_ping = $post_id;
	elseif (function_exists('do_all_pings')) :
		do_all_pings();
	else :
		generic_ping($fwp_held_ping);
	endif;
}

function fwp_publish_post_hook ($post_id) {
	global $fwp_held_ping;

	if (!is_null($fwp_held_ping)) : // Syndicated post. Don't mark with _pingme
		if ( defined('XMLRPC_REQUEST') )
			do_action('xmlrpc_publish_post', $post_id);
		if ( defined('APP_REQUEST') )
			do_action('app_publish_post', $post_id);
		
		if ( defined('WP_IMPORTING') )
			return;

		// Defer sending out pings until we finish updating
		$fwp_held_ping = $post_id;
	else :
		if (function_exists('_publish_post_hook')) : // WordPress 2.3
			_publish_post_hook($post_id);
		endif;
	endif;
}

	function feedwordpress_add_post_edit_controls () {
		add_meta_box('feedwordpress-post-controls', __('Syndication'), 'feedwordpress_post_edit_controls', 'post', 'side', 'high');
	} // function FeedWordPress::postEditControls
	
	function feedwordpress_post_edit_controls () {
		global $post;
		
		$frozen_values = get_post_custom_values('_syndication_freeze_updates', $post->ID);
		$frozen_post = (count($frozen_values) > 0 and 'yes' == $frozen_values[0]);

		if (is_syndicated($post->ID)) :
		?>
		<p>This is a syndicated post, which originally appeared at
		<cite><?php print esc_html(get_syndication_source(NULL, $post->ID)); ?></cite>.
		<a href="<?php print esc_html(get_syndication_permalink($post->ID)); ?>">View original post</a>.</p>
		
		<p><input type="hidden" name="feedwordpress_noncename" id="feedwordpress_noncename" value="<?php print wp_create_nonce(plugin_basename(__FILE__)); ?>" />
		<label><input type="checkbox" name="freeze_updates" value="yes" <?php if ($frozen_post) : ?>checked="checked"<?php endif; ?> /> <strong>Manual editing.</strong>
		If set, FeedWordPress will not overwrite the changes you make manually
		to this post, if the syndicated content is updated on the
		feed.</label></p>
		<?php
		else :
		?>
		<p>This post was created locally at this website.</p>
		<?php
		endif;
	} // function feedwordpress_post_edit_controls () */

	function feedwordpress_save_post_edit_controls ( $post_id ) {
		global $post;
		
		if (!isset($_POST['feedwordpress_noncename']) or !wp_verify_nonce($_POST['feedwordpress_noncename'], plugin_basename(__FILE__))) :
			return $post_id;
		endif;
	
		// Verify if this is an auto save routine. If it is our form has
		// not been submitted, so we don't want to do anything.
		if ( defined('DOING_AUTOSAVE') and DOING_AUTOSAVE ) :
			return $post_id;
		endif;
		
		// Check permissions
		if ( !current_user_can( 'edit_'.$_POST['post_type'], $post_id) ) :
			return $post_id;
		endif;
		
		// OK, we're golden. Now let's save some data.
		if (isset($_POST['freeze_updates'])) :
			update_post_meta($post_id, '_syndication_freeze_updates', $_POST['freeze_updates']);
			$ret = $_POST['freeze_updates'];
		else :
			delete_post_meta($post_id, '_syndication_freeze_updates');
			$ret = NULL;
		endif;
		
		return $ret;
	} // function feedwordpress_save_edit_controls

################################################################################
## class FeedWordPress #########################################################
################################################################################

// class FeedWordPress: handles feed updates and plugs in to the XML-RPC interface
class FeedWordPress {
	var $strip_attrs = array (
		      array('[a-z]+', 'style'),
		      array('[a-z]+', 'target'),
	);
	var $uri_attrs = array (
			array('a', 'href'),
			array('applet', 'codebase'),
			array('area', 'href'),
			array('blockquote', 'cite'),
			array('body', 'background'),
			array('del', 'cite'),
			array('form', 'action'),
			array('frame', 'longdesc'),
			array('frame', 'src'),
			array('iframe', 'longdesc'),
			array('iframe', 'src'),
			array('head', 'profile'),
			array('img', 'longdesc'),
			array('img', 'src'),
			array('img', 'usemap'),
			array('input', 'src'),
			array('input', 'usemap'),
			array('ins', 'cite'),
			array('link', 'href'),
			array('object', 'classid'),
			array('object', 'codebase'),
			array('object', 'data'),
			array('object', 'usemap'),
			array('q', 'cite'),
			array('script', 'src')
	);

	var $feeds = NULL;

	# function FeedWordPress (): Contructor; retrieve a list of feeds 
	function FeedWordPress () {
		$this->feeds = array ();
		$links = FeedWordPress::syndicated_links();
		if ($links): foreach ($links as $link):
			$this->feeds[] = new SyndicatedLink($link);
		endforeach; endif;
	} // FeedWordPress::FeedWordPress ()

	# function update (): polls for updates on one or more Contributor feeds
	#
	# Arguments:
	# ----------
	# *    $uri (string): either the URI of the feed to poll, the URI of the
	#      (human-readable) website whose feed you want to poll, or NULL.
	#
	#      If $uri is NULL, then FeedWordPress will poll any feeds that are
	#      ready for polling. It will not poll feeds that are marked as
	#      "Invisible" Links (signifying that the subscription has been
	#      de-activated), or feeds that are not yet stale according to their
	#      TTL setting (which is either set in the feed, or else set
	#      randomly within a window of 30 minutes - 2 hours).
	#
	# Returns:
	# --------
	# *    Normally returns an associative array, with 'new' => the number
	#      of new posts added during the update, and 'updated' => the number
	#      of old posts that were updated during the update. If both numbers
	#      are zero, there was no change since the last poll on that URI.
	#
	# *    Returns NULL if URI it was passed was not a URI that this
	#      installation of FeedWordPress syndicates.
	#
	# Effects:
	# --------
	# *    One or more feeds are polled for updates
	#
	# *    If the feed Link does not have a hardcoded name set, its Link
	#      Name is synchronized with the feed's title element
	#
	# *    If the feed Link does not have a hardcoded URI set, its Link URI
	#      is synchronized with the feed's human-readable link element
	#
	# *    If the feed Link does not have a hardcoded description set, its
	#      Link Description is synchronized with the feed's description,
	#      tagline, or subtitle element.
	#
	# *    The time of polling is recorded in the feed's settings, and the
	#      TTL (time until the feed is next available for polling) is set
	#      either from the feed (if it is supplied in the ttl or syndication
	#      module elements) or else from a randomly-generated time window
	#      (between 30 minutes and 2 hours).
	#
	# *    New posts from the polled feed are added to the WordPress store.
	# 
	# *    Updates to existing posts since the last poll are mirrored in the
	#      WordPress store.
	#
	function update ($uri = null, $crash_ts = null) {
		global $wpdb;

		if (FeedWordPress::needs_upgrade()) : // Will make duplicate posts if we don't hold off
			return NULL;
		endif;
		
		if (!is_null($uri)) :
			$uri = trim($uri);
		else : // Update all
			update_option('feedwordpress_last_update_all', time());
		endif;

		do_action('feedwordpress_update', $uri);

		if (is_null($crash_ts)) :
			$crash_dt = (int) get_option('feedwordpress_update_time_limit');
			if ($crash_dt > 0) :
				$crash_ts = time() + $crash_dt;
			else :
				$crash_ts = NULL;
			endif;
		endif;
		
		// Randomize order for load balancing purposes
		$feed_set = $this->feeds;
		shuffle($feed_set);

		// Loop through and check for new posts
		$delta = NULL;
		foreach ($feed_set as $feed) :
			if (!is_null($crash_ts) and (time() > $crash_ts)) : // Check whether we've exceeded the time limit
				break;
			endif;

			$pinged_that = (is_null($uri) or ($uri=='*') or in_array($uri, array($feed->uri(), $feed->homepage())));

			if (!is_null($uri)) : // A site-specific ping always updates
				$timely = true;
			else :
				$timely = $feed->stale();
			endif;

			if ($pinged_that and is_null($delta)) :			// If at least one feed was hit for updating...
				$delta = array('new' => 0, 'updated' => 0);	// ... don't return error condition 
			endif;

			if ($pinged_that and $timely) :
				do_action('feedwordpress_check_feed', $feed->settings);
				$start_ts = time();
				$added = $feed->poll($crash_ts);
				do_action('feedwordpress_check_feed_complete', $feed->settings, $added, time() - $start_ts);

				if (is_array($added)) : // Success
					if (isset($added['new'])) : $delta['new'] += $added['new']; endif;
					if (isset($added['updated'])) : $delta['updated'] += $added['updated']; endif;
				endif;
			endif;
		endforeach;

		do_action('feedwordpress_update_complete', $delta);

		return $delta;
	}

	function stale () {
		if (get_option('feedwordpress_automatic_updates')) :
			// Do our best to avoid possible simultaneous
			// updates by getting up-to-the-minute settings.
			
			$last = get_option('feedwordpress_last_update_all');
		
			// If we haven't updated all yet, give it a time window
			if (false === $last) :
				$ret = false;
				update_option('feedwordpress_last_update_all', time());
			
			// Otherwise, check against freshness interval
			elseif (is_numeric($last)) : // Expect a timestamp
				$freshness = get_option('feedwordpress_freshness');
				if (false === $freshness) : // Use default
					$freshness = FEEDWORDPRESS_FRESHNESS_INTERVAL;
				endif;
				$ret = ( (time() - $last) > $freshness);

			 // This should never happen.
			else :
				FeedWordPress::critical_bug('FeedWordPress::stale::last', $last, __LINE__);
			endif;

		else :
			$ret = false;
		endif;
		return $ret;
	} // FeedWordPress::stale()
	
	function update_requested () {
		return (
			isset($_REQUEST['update_feedwordpress'])
			and $_REQUEST['update_feedwordpress']
		);
	} // FeedWordPress::update_requested()

	function update_requested_url () {
			$ret = null;
			
			if (($_REQUEST['update_feedwordpress']=='*')
			or (preg_match('|^http://.*|i', $_REQUEST['update_feedwordpress']))) :
				$ret = $_REQUEST['update_feedwordpress'];
			endif;

			return $ret;
	} // FeedWordPress::update_requested_url()

	function syndicate_link ($name, $uri, $rss) {
		global $wpdb;

		// Get the category ID#
		$cat_id = FeedWordPress::link_category_id();
		
		// WordPress gets cranky if there's no homepage URI
		if (!isset($uri) or strlen($uri)<1) : $uri = $rss; endif;
		
		if (function_exists('wp_insert_link')) : // WordPress 2.x
			if (FeedWordPressCompatibility::test_version(0, FWP_SCHEMA_21)) :
				// Morons.
				$name = $wpdb->escape($name);
				$uri = $wpdb->escape($uri);
				$rss = $wpdb->escape($rss);
				
				// Comes in as a single category
				$linkCats = $cat_id;
			else :
				// Comes in as an array of categories
				$linkCats = array($cat_id);
			endif;

			$link_id = wp_insert_link(array(
				"link_name" => $name,
				"link_url" => $uri,
				"link_category" => $linkCats,
				"link_rss" => $rss
			));
		else : // WordPress 1.5.x
			$result = $wpdb->query("
			INSERT INTO $wpdb->links
			SET
				link_name = '".$wpdb->escape($name)."',
				link_url = '".$wpdb->escape($uri)."',
				link_category = '".$wpdb->escape($cat_id)."',
				link_rss = '".$wpdb->escape($rss)."'
			");
			$link_id = $wpdb->insert_id;
		endif;
		return $link_id;
	} // function FeedWordPress::syndicate_link()

	/*static*/ function syndicated_status ($what, $default) {
		$ret = get_option("feedwordpress_syndicated_{$what}_status");
		if (!$ret) :
			$ret = $default;
		endif;
		return $ret;
	} /* FeedWordPress::syndicated_status() */

	function on_unfamiliar ($what = 'author', $override = NULL) {
		$set = array(
			'author' => array('create', 'default', 'filter'),
			'category' => array('create', 'tag', 'default', 'filter'),
		);
		
		if (is_string($override)) :
			$ret = strtolower($override);
		else :
			$ret = NULL;
		endif;

		if (!is_numeric($override) and !in_array($ret, $set[$what])) :
			$ret = get_option('feedwordpress_unfamiliar_'.$what);
			if (!is_numeric($ret) and !in_array($ret, $set[$what])) :
				$ret = 'create';
			endif;
		endif;

		return $ret;
	} // function FeedWordPress::on_unfamiliar()

	function null_email_set () {
		$base = get_option('feedwordpress_null_email_set');

		if ($base===false) :
			$ret = array('noreply@blogger.com'); // default
		else :
			$ret = array_map('strtolower',
				array_map('trim', explode("\n", $base)));
		endif;
		$ret = apply_filters('syndicated_item_author_null_email_set', $ret);
		return $ret;

	} /* FeedWordPress::null_email_set () */

	function is_null_email ($email) {
		$ret = in_array(strtolower(trim($email)), FeedWordPress::null_email_set());
		$ret = apply_filters('syndicated_item_author_is_null_email', $ret, $email);
		return $ret;
	} /* FeedWordPress::is_null_email () */

	function use_aggregator_source_data () {
		$ret = get_option('feedwordpress_use_aggregator_source_data');
		return apply_filters('syndicated_post_use_aggregator_source_data', ($ret=='yes'));
	}

	/**
	 * FeedWordPress::munge_permalinks: check whether or not FeedWordPress
	 * should rewrite permalinks for syndicated items to reflect their
	 * original location.
	 *
	 * @return bool TRUE if FeedWordPress SHOULD rewrite permalinks; FALSE otherwise
	 */
	/*static*/ function munge_permalinks () {
		return (get_option('feedwordpress_munge_permalink', /*default=*/ 'yes') != 'no');
	} /* FeedWordPress::munge_permalinks() */

	function syndicated_links () {
		$contributors = FeedWordPress::link_category_id();
		if (function_exists('get_bookmarks')) :
			$links = get_bookmarks(array("category" => $contributors));
		else: 
			$links = get_linkobjects($contributors); // deprecated as of WP 2.1
		endif;
		return $links;
	} // function FeedWordPress::syndicated_links()

	function link_category_id () {
		global $wpdb, $wp_db_version;

		$cat_id = get_option('feedwordpress_cat_id');
		
		// If we don't yet have the category ID stored, search by name
		if (!$cat_id) :
			$cat_id = FeedWordPressCompatibility::link_category_id(DEFAULT_SYNDICATION_CATEGORY);

			if ($cat_id) :
				// We found it; let's stamp it.
				update_option('feedwordpress_cat_id', $cat_id);
			endif;

		// If we *do* have the category ID stored, verify that it exists
		else :
			$cat_id = FeedWordPressCompatibility::link_category_id((int) $cat_id, 'cat_id');
		endif;
		
		// If we could not find an appropriate link category,
		// make a new one for ourselves.
		if (!$cat_id) :
			$cat_id = FeedWordPressCompatibility::insert_link_category(DEFAULT_SYNDICATION_CATEGORY);

			// Stamp it
			update_option('feedwordpress_cat_id', $cat_id);
		endif;

		return $cat_id;
	} // function FeedWordPress::link_category_id()

	# Upgrades and maintenance...
	function needs_upgrade () {
		global $wpdb;
		$fwp_db_version = get_option('feedwordpress_version');
		$ret = false; // innocent until proven guilty
		if (!$fwp_db_version or $fwp_db_version < FEEDWORDPRESS_VERSION) :
			// This is an older version or a fresh install. Does it
			// require a database upgrade or database initialization?
			if ($fwp_db_version <= 0.96) :
				// Yes. Check to see whether this is a fresh install or an upgrade.
				$syn = $wpdb->get_col("
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = 'syndication_feed'
				");
				if (count($syn) > 0) : // contains at least one syndicated post
					$ret = true;
				else : // fresh install; brand it as ours
					update_option('feedwordpress_version', FEEDWORDPRESS_VERSION);
				endif;
			elseif ($fwp_db_version < 2009.0707) :
				// We need to clear out any busted AJAX crap
				if (fwp_test_wp_version(FWP_SCHEMA_HAS_USERMETA)) :
					$wpdb->query("
					DELETE FROM $wpdb->usermeta
					WHERE LOCATE('feedwordpress', meta_key)
					AND LOCATE('box', meta_key);
					");
				endif;
				update_option('feedwordpress_version', FEEDWORDPRESS_VERSION);
			else :
				// No. Just brand it with the new version.
				update_option('feedwordpress_version', FEEDWORDPRESS_VERSION);
			endif;
		endif;
		return $ret;
	}

	function upgrade_database ($from = NULL) {
		global $wpdb;

		if (is_null($from) or $from <= 0.96) : $from = 0.96; endif;

		switch ($from) :
		case 0.96:
			 // Dropping legacy upgrade code. If anyone is still
			 // using 0.96 and just now decided to upgrade, well, I'm
			 // sorry about that. You'll just have to cope with a few
			 // duplicate posts.

			// Mark the upgrade as successful.
			update_option('feedwordpress_version', FEEDWORDPRESS_VERSION);
		endswitch;
		echo "<p>Upgrade complete. FeedWordPress is now ready to use again.</p>";
	} /* FeedWordPress::upgrade_database() */

	function has_guid_index () {
		global $wpdb;
		
		$found = false; // Guilty until proven innocent.

		$results = $wpdb->get_results("
		SHOW INDEXES FROM {$wpdb->posts}
		");
		if ($results) :
			foreach ($results as $index) :
				if (isset($index->Column_name)
				and ('guid' == $index->Column_name)) :
					$found = true;
				endif;
			endforeach;
		endif;
		return $found;
	} /* FeedWordPress::has_guid_index () */
	
	function create_guid_index () {
		global $wpdb;
		
		$wpdb->query("
		CREATE INDEX {$wpdb->posts}_guid_idx ON {$wpdb->posts}(guid)
		");
	} /* FeedWordPress::create_guid_index () */
	
	function remove_guid_index () {
		global $wpdb;
		
		$wpdb->query("
		DROP INDEX {$wpdb->posts}_guid_idx ON {$wpdb->posts}
		");
	}

	/*static*/ function fetch ($url) {
		$feed = new SimplePie();
		$feed->set_feed_url($url);
		$feed->set_cache_class('WP_Feed_Cache');
		$feed->set_file_class('WP_SimplePie_File');
		$feed->set_content_type_sniffer_class('FeedWordPress_Content_Type_Sniffer');
		$feed->set_cache_duration(FeedWordPress::cache_duration());
		$feed->init();
		$feed->handle_content_type();
		
		if ($feed->error()) :
			$ret = new WP_Error('simplepie-error', $feed->error());
		else :
			$ret = $feed;
		endif;
		return $ret;
	} /* FeedWordPress::fetch () */
	
	function clear_cache () {
		global $wpdb;
		
		// The WordPress SimplePie module stores its cached feeds as
		// transient records in the options table. The data itself is
		// stored in `_transient_feed_{md5 of url}` and the last-modified
		// timestamp in `_transient_feed_mod_{md5 of url}`. Timeouts for
		// these records are stored in `_transient_timeout_feed_{md5}`.
		// Since the md5 is always 32 characters in length, the
		// option_name is always over 32 characters.
		$ret = $wpdb->query("
		DELETE FROM {$wpdb->options}
		WHERE option_name LIKE '_transient%_feed_%' AND LENGTH(option_name) > 32
		");
		return (int) ($ret / 4); // Each transient has 4 rows: the data, the modified timestamp; and the timeouts for each
	} /* FeedWordPress::clear_cache () */

	function cache_duration () {
		$duration = NULL;
		if (defined('FEEDWORDPRESS_CACHE_AGE')) :
			$duration = FEEDWORDPRESS_CACHE_AGE;
		endif;
		return $duration;
	}
	function cache_lifetime ($duration) {
		// Check for explicit setting of a lifetime duration
		if (defined('FEEDWORDPRESS_CACHE_LIFETIME')) :
			$duration = FEEDWORDPRESS_CACHE_LIFETIME;

		// Fall back to the cache freshness duration
		elseif (defined('FEEDWORDPRESS_CACHE_AGE')) :
			$duration = FEEDWORDPRESS_CACHE_AGE;
		endif;
		
		// Fall back to WordPress default
		return $duration;
	} /* FeedWordPress::cache_lifetime () */

	# Utility functions for handling text settings
	function negative ($f, $setting) {
		$nego = array ('n', 'no', 'f', 'false');
		return (isset($f[$setting]) and in_array(strtolower($f[$setting]), $nego));
	}

	function affirmative ($f, $setting) {
		$affirmo = array ('y', 'yes', 't', 'true', 1);
		return (isset($f[$setting]) and in_array(strtolower($f[$setting]), $affirmo));
	}


	# Internal debugging functions
	function critical_bug ($varname, $var, $line) {
		global $wp_version;

		echo '<p>There may be a bug in FeedWordPress. Please <a href="'.FEEDWORDPRESS_AUTHOR_CONTACT.'">contact the author</a> and paste the following information into your e-mail:</p>';
		echo "\n<plaintext>";
		echo "Triggered at line # ".$line."\n";
		echo "FeedWordPress version: ".FEEDWORDPRESS_VERSION."\n";
		echo "WordPress version: {$wp_version}\n";
		echo "PHP version: ".phpversion()."\n";
		echo "\n";
		echo $varname.": "; var_dump($var); echo "\n";
		die;
	}
	
	function noncritical_bug ($varname, $var, $line) {
		if (FEEDWORDPRESS_DEBUG) : // halt only when we are doing debugging
			FeedWordPress::critical_bug($varname, $var, $line);
		endif;
	}
	
	function val ($v, $no_newlines = false) {
		ob_start();
		var_dump($v);
		$out = ob_get_contents(); ob_end_clean();
		
		if ($no_newlines) :
			$out = preg_replace('/\s+/', " ", $out);
		endif;
		return $out;
	} /* FeedWordPress:val () */

	function diagnostic ($level, $out) {
		global $feedwordpress_admin_footer;

		$output = get_option('feedwordpress_diagnostics_output', array());
		$show = get_option('feedwordpress_diagnostics_show', array());
		
		$diagnostic_nesting = count(explode(":", $level));

		if (in_array($level, $show)) :
			foreach ($output as $method) :
				switch ($method) :
				case 'echo' :
					echo "<div><pre><strong>Diag".str_repeat('====', $diagnostic_nesting-1).'|</strong> '.esc_html($out)."</pre></div>";
					break;
				case 'admin_footer' :
					$feedwordpress_admin_footer[] = $out;
					break;
				case 'error_log' :
					error_log('[feedwordpress]' . $out);
					break;
				endswitch;
			endforeach;
		endif;
	} /* FeedWordPress::diagnostic () */
	
	function admin_footer () {
		global $feedwordpress_admin_footer;
		foreach ($feedwordpress_admin_footer as $line) :
			echo '<div><pre>'.esc_html($line).'</pre></div>';
		endforeach;
	} /* FeedWordPress::admin_footer () */
} // class FeedWordPress

$feedwordpress_admin_footer = array();

require_once(dirname(__FILE__) . '/syndicatedpost.class.php');
require_once(dirname(__FILE__) . '/syndicatedlink.class.php');

################################################################################
## XML-RPC HOOKS: accept XML-RPC update pings from Contributors ################
################################################################################

function feedwordpress_xmlrpc_hook ($args = array ()) {
	$args['weblogUpdates.ping'] = 'feedwordpress_pong';
	return $args;
}

function feedwordpress_pong ($args) {
	$feedwordpress = new FeedWordPress;
	$delta = @$feedwordpress->update($args[1]);
	if (is_null($delta)):
		return array('flerror' => true, 'message' => "Sorry. I don't syndicate <$args[1]>.");
	else:
		$mesg = array();
		if (isset($delta['new'])) { $mesg[] = ' '.$delta['new'].' new posts were syndicated'; }
		if (isset($delta['updated'])) { $mesg[] = ' '.$delta['updated'].' existing posts were updated'; }

		return array('flerror' => false, 'message' => "Thanks for the ping.".implode(' and', $mesg));
	endif;
}

require_once(dirname(__FILE__) . '/relative_uri.class.php');

// take your best guess at the realname and e-mail, given a string
define('FWP_REGEX_EMAIL_ADDY', '([^@"(<\s]+@[^"@(<\s]+\.[^"@(<\s]+)');
define('FWP_REGEX_EMAIL_NAME', '("([^"]*)"|([^"<(]+\S))');
define('FWP_REGEX_EMAIL_POSTFIX_NAME', '/^\s*'.FWP_REGEX_EMAIL_ADDY."\s+\(".FWP_REGEX_EMAIL_NAME.'\)\s*$/');
define('FWP_REGEX_EMAIL_PREFIX_NAME', '/^\s*'.FWP_REGEX_EMAIL_NAME.'\s*<'.FWP_REGEX_EMAIL_ADDY.'>\s*$/');
define('FWP_REGEX_EMAIL_JUST_ADDY', '/^\s*'.FWP_REGEX_EMAIL_ADDY.'\s*$/');
define('FWP_REGEX_EMAIL_JUST_NAME', '/^\s*'.FWP_REGEX_EMAIL_NAME.'\s*$/');

function parse_email_with_realname ($email) {
	if (preg_match(FWP_REGEX_EMAIL_POSTFIX_NAME, $email, $matches)) :
		($ret['name'] = $matches[3]) or ($ret['name'] = $matches[2]);
		$ret['email'] = $matches[1];
	elseif (preg_match(FWP_REGEX_EMAIL_PREFIX_NAME, $email, $matches)) :
		($ret['name'] = $matches[2]) or ($ret['name'] = $matches[3]);
		$ret['email'] = $matches[4];
	elseif (preg_match(FWP_REGEX_EMAIL_JUST_ADDY, $email, $matches)) :
		$ret['name'] = NULL; $ret['email'] = $matches[1];
	elseif (preg_match(FWP_REGEX_EMAIL_JUST_NAME, $email, $matches)) :
		$ret['email'] = NULL;
		($ret['name'] = $matches[2]) or ($ret['name'] = $matches[3]);
	else :
		$ret['name'] = NULL; $ret['email'] = NULL;
	endif;
	return $ret;
}

