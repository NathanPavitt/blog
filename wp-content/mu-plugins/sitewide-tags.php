<?php
/*
Plugin Name: WordPress MU Sitewide Tags Pages
Plugin URI: http://ocaoimh.ie/wordpress-mu-sitewide-tags/
Description: Creates a blog where all the most recent posts on a WordPress MU site may be found.
Version: 0.4.0.1
Author: Donncha O Caoimh
Author URI: http://ocaoimh.ie/
*/
/*  Copyright 2008 Donncha O Caoimh (http://ocaoimh.ie/)
    With contributions by Ron Rennick(http://wpmututorials.com/), Thomas Schneider(http://www.im-web-gefunden.de/) and others.

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function sitewide_tags_options() {
	
	load_muplugin_textdomain('wpmu-sitewide-tags', MUPLUGINDIR.'/languages');
	if( get_sitewide_tags_option( 'tags_blog_public' ) === null )
		add_site_option( 'sitewide_tags_blog', array( 'tags_blog_public' => 1 ) );

	$tags_blog_enable = get_sitewide_tags_option( 'tags_blog_enabled' );
	?><h3><?php _e('Global Tags','wpmu-sitewide-tags') ?></h3> 
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Tags Blog','wpmu-sitewide-tags') ?></th> 
			<td>
				<label><input name="tags_blog_enabled" type="checkbox" id="tags_blog_enabled" value="1" <?php if( $tags_blog_enable == 1 ) { echo "checked='checked'"; } ?> /> <strong><?php _e("Enabled","wpmu-sitewide-tags"); ?></strong></label><br />
	<?php
	if( !$tags_blog_enable ) {
		echo "</td></tr></table>";
		return;
	}
	$tags_blog_public = get_sitewide_tags_option( 'tags_blog_public' );
	$tags_blog_pages = get_sitewide_tags_option( 'tags_blog_pages' );
	$tags_blog_pub_check = get_sitewide_tags_option( 'tags_blog_pub_check' );
	$tags_blog_postmeta = get_sitewide_tags_option( 'tags_blog_postmeta' );
	?>
				<p><?php _e( "You can create your post archive in a specific 'tags' blog of your choosing, or you can use the main blog of your site. Each has it's own pros and cons.","wpmu-sitewide-tags"); ?></p>
				<ol><li><input name="tags_blog" type="text" id="tags_blog" style="width: 35%" value="<?php echo attribute_escape( get_sitewide_tags_option( 'tags_blog', 'tags' ) ); ?>" size="45" /><br />
				<?php _e('<strong>Blogname</strong> of the blog your global tags and posts will live in. Blog will be created.','wpmu-sitewide-tags') ?></li>
				<li><label><input name="tags_blog_main_blog" type="checkbox" id="tags_blog_main_blog" value="1" <?php if( get_sitewide_tags_option( 'tags_blog_main_blog', 0 ) == 1 ) { echo "checked='checked'"; } ?> /> <strong><?php _e( "Post to main blog","wpmu-sitewide-tags" ); ?></strong></label><br />
				<?php _e('Create posts in your main blog. All posts will appear on the front page of your site. Remember to to add a post loop to home.php in the theme directory if it exists.','wpmu-sitewide-tags') ?></li></ol>
			</td> 
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Max Posts','wpmu-sitewide-tags') ?></th> 
			<td>
				<input name="tags_max_posts" type="text" id="tags_max_posts" style="width: 15%" value="<?php echo intval( get_sitewide_tags_option( 'tags_max_posts', 5000 ) ); ?>" size="5" />
				<br />
				<?php _e('The maximum number of posts stored in the tags blog.','wpmu-sitewide-tags') ?>
			</td> 
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Include Pages','wpmu-sitewide-tags') ?></th> 
			<td>
				<label><input name="tags_blog_pages" type="checkbox" id="tags_blog_pages" value="1" <?php if( $tags_blog_pages == 1 ) { echo "checked='checked'"; } ?> /> <strong><?php _e("Enabled","wpmu-sitewide-tags"); ?></strong></label><br />
			</td> 
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Privacy','wpmu-sitewide-tags') ?></th> 
			<td>
				<label><input type='radio' name='tags_blog_public' value='1' <?php echo ( $tags_blog_public == 1 ? 'checked="checked"' : '' ) ?> /> <?php _e('Tags pages can be indexed by search engines.','wpmu-sitewide-tags')?></label><br />
				<label><input type='radio' name='tags_blog_public' value='0' <?php echo ( $tags_blog_public == 0 ? 'checked="checked"' : '' ) ?> /> <?php _e('Tags pages will not be indexed by search engines.','wpmu-sitewide-tags')?></label>
				<br />
				<?php _e('Will your tags pages be visible to Google and other search engines?','wpmu-sitewide-tags');
		if( $tags_blog_public == 1 ) { ?>
				<input name="tags_blog_pub_check" type="hidden" value="0" /> 
<?php } else { ?>
			</td> 
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Non-Public Blogs','wpmu-sitewide-tags') ?></th> 
			<td>
				<label><input name="tags_blog_pub_check" type="checkbox" id="tags_blog_pub_check" value="1" <?php if( $tags_blog_pub_check == 1 ) { echo "checked='checked'"; } ?> /> <strong><?php _e("Enabled","wpmu-sitewide-tags"); ?></strong></label><br />
				<?php _e('Include posts from blogs not indexed by search engines.','wpmu-sitewide-tags'); 
		} ?>
			</td> 
			<tr valign="top"> 
				<th scope="row"><?php _e('Post Meta') ?></th> 
				<td>
					<textarea name="tags_blog_postmeta" id="tags_blog_postmeta" cols='40' rows='5'><?php echo $tags_blog_postmeta == '' ? '' : @implode( "\n", $tags_blog_postmeta ); ?></textarea>
					<br />
					<?php _e('If you want to copy custom fields with posts. One custom field per line.') ?>
				</td> 
			</tr>
		</tr>
	</table>
	<?php
}
add_action('wpmu_options', 'sitewide_tags_options');

function sitewide_tags_update_options() {
	global $wpdb, $current_site, $current_user;
	if( !$_POST[ 'tags_blog_enabled' ] ) {
		if( get_sitewide_tags_option( 'tags_blog_enabled' ) != $_POST[ 'tags_blog_enabled' ] ) 
			update_sitewide_tags_option( 'tags_blog_enabled', 0, true );
		return;
	}
	update_sitewide_tags_option( 'tags_blog_enabled', 1 );

	if( ( isset( $_POST[ 'tags_blog' ] ) || isset( $_POST[ 'tags_blog_main_blog' ] ) ) && isset( $_POST[ 'tags_blog_public' ] ) ) {
		if( $_POST[ 'tags_blog_main_blog' ] == 1 ) {
			if( $current_site->blog_id )
				$id = $current_site->blog_id;
			else
				$id = $wpdb->get_var( "SELECT blog_id FROM {$wpdb->blogs} WHERE domain = '{$current_site->domain}' AND path = '{$current_site->path}'" );
			if( $id ) {
				update_sitewide_tags_option( 'tags_blog_id', $id );
				update_sitewide_tags_option( 'tags_blog_main_blog', 1 );
			} else {
				update_sitewide_tags_option( 'tags_blog_main_blog', 0 );
			}
		} else {
			update_sitewide_tags_option( 'tags_blog_main_blog', 0 );
			$tags_blog = $_POST[ 'tags_blog' ];
			update_sitewide_tags_option( 'tags_blog', $tags_blog );
			if( constant( 'VHOST' ) == 'yes' ) {
				$domain = $tags_blog . '.' . $current_site->domain;
				$path = $current_site->path;
			} else {
				$domain = $current_site->domain;
				$path = trailingslashit( $current_site->path . $tags_blog );
			}
			$tags_blog_id = $wpdb->get_var( "SELECT blog_id FROM {$wpdb->blogs} WHERE domain = '$domain' AND path = '$path'" );
			if( $tags_blog_id ) {
				update_sitewide_tags_option( 'tags_blog_id', $tags_blog_id );
			} else {
				$wpdb->hide_errors();
				$id = wpmu_create_blog( $domain, $path, __( 'Global Posts','wpmu-sitewide-tags' ), $current_user->id , array( "public" => $_POST[ 'tags_blog_public' ] ), $current_site->id);
				update_sitewide_tags_option( 'tags_blog_id', $id );
				$wpdb->show_errors();
			}
		}
		$tags_blog_public = (int)$_POST[ 'tags_blog_public' ];
		update_sitewide_tags_option( 'tags_blog_public', $tags_blog_public );
		update_blog_option( $tags_blog_id, 'blog_public', $tags_blog_public );
		update_blog_status( $tags_blog_id, 'public', $tags_blog_public);
	}

	if( isset( $_POST[ 'tags_max_posts' ] ) )
		update_sitewide_tags_option( 'tags_max_posts', (int)$_POST[ 'tags_max_posts' ] );
	
	if( get_sitewide_tags_option( 'tags_blog_pages' ) != $_POST[ 'tags_blog_pages' ] )
		update_sitewide_tags_option( 'tags_blog_pages', (int)$_POST[ 'tags_blog_pages' ] );
			
	if( get_sitewide_tags_option( 'tags_blog_pub_check' ) != $_POST[ 'tags_blog_pub_check' ] ) {
		if( $tags_blog_public == 0 )
			update_sitewide_tags_option( 'tags_blog_pub_check', (int)$_POST[ 'tags_blog_pub_check' ] );
		else
			update_sitewide_tags_option( 'tags_blog_pub_check', 0 );
	}
		
	if( $_POST['tags_blog_postmeta'] != '' ) {
		$meta_keys = split( "\n", stripslashes( $_POST[ 'tags_blog_postmeta' ] ) );
		foreach( (array) $meta_keys as $key ) {
			$keys[] = trim( $key );
		}
		update_sitewide_tags_option( "tags_blog_postmeta", $keys );
	} else {
		update_sitewide_tags_option( "tags_blog_postmeta", '' );
	}
	
	// force write if changes saved
	update_sitewide_tags_option( true );
}
add_action( 'update_wpmu_options', 'sitewide_tags_update_options' );

function sitewide_tags_post( $post_id, $post ) {
	global $wpdb;

	if( !get_sitewide_tags_option( 'tags_blog_enabled' ) )
		return;

	// wp_insert_category()
	include_once(ABSPATH . 'wp-admin/includes/admin.php');

	$tags_blog_id = get_sitewide_tags_option( 'tags_blog_id' );
	if( !$tags_blog_id || $wpdb->blogid == $tags_blog_id )
		return;
	
	$allowed_post_types = apply_filters( 'sitewide_tags_allowed_post_types', array( 'post' => true ) );
	if ( !$allowed_post_types[$post->post_type] ) 
		return;

	$post_blog_id = $wpdb->blogid;
	$blog_status = get_blog_status($post_blog_id, "public");
	if ( $blog_status != 1 && ( $blog_status != 0 || get_sitewide_tags_option( 'tags_blog_public') == 1 || get_sitewide_tags_option( 'tags_blog_pub_check') == 0 ) )
		return;

	$post->post_category = wp_get_post_categories( $post_id );
	foreach( $post->post_category as $c ) {
		$cat = get_category( $c );
		$cats[] = array( 'name' => wp_specialchars( $cat->name ), 'slug' => wp_specialchars( $cat->slug ) );
	}

	$post->tags_input = implode( ', ', wp_get_post_tags( $post_id, array('fields' => 'names') ) );

	$post->guid = $post_blog_id . '.' . $post_id;

	$permalink = get_permalink( $post_id );
	$org_blog_id = $wpdb->blogid; // org_blog_id

	$meta_keys = apply_filters( 'sitewide_tags_meta_keys', get_sitewide_tags_option( 'tags_blog_postmeta', array() ) );
	if( is_array( $meta_keys ) && count( $meta_keys ) ) {
		foreach($meta_keys as $key) {
			$$key = get_post_meta( $post->ID, $key, true );
		}
	}
	
	switch_to_blog( $tags_blog_id );
	if( is_array( $cats ) && !empty( $cats ) ) {
		foreach( $cats as $t => $d ) {
			/* Here is where we insert the category */
			wp_insert_category( array('cat_name' => $d['name'], 'category_description' => $d['name'], 'category_nicename' => $d['slug'], 'category_parent' => '') );

			/* Now get the category ID to be used for the post */
			$category_id[] = $wpdb->get_var( "SELECT term_id FROM " . $wpdb->get_blog_prefix( $tags_blog_id ) . "terms WHERE slug = '" . $d['slug'] . "'" );
		}
	}

	$global_post = $wpdb->get_row( "SELECT * FROM {$wpdb->posts} WHERE guid = '{$post->guid}'" );
	if( $post->post_status != 'publish' && is_object( $global_post ) ) {
		wp_delete_post( $global_post->ID );
	} else {
		if( $global_post->ID != '' ) {
			$post->ID = $global_post->ID; // editing an old post
			/* using this kind of update not simple add: fixing permalink-problems */
			delete_post_meta( $global_post->ID, "permalink");
			
			if( is_array( $meta_keys ) && count( $meta_keys ) ) {
				foreach($meta_keys as $key) {
					delete_post_meta( $global_post->ID, $key );
				}
			}
		} else {
			unset( $post->ID ); // new post
		}
	}
	if( $post->post_status == 'publish' ) {
		$post->ping_status = 'closed';
		$post->comment_status = 'closed';

		/* Use the category ID in the post */
	        $post->post_category = $category_id;

		$p = wp_insert_post( $post );
		add_post_meta( $p, "permalink", $permalink );
		add_post_meta( $p, "blogid", $org_blog_id ); // org_blog_id
		if( is_array( $meta_keys ) && count( $meta_keys ) ) {
			foreach($meta_keys as $key) {
				if( $$key )
					add_post_meta( $p, $key, $$key );
			}
		}
	}
	restore_current_blog();
}
add_action('save_post', 'sitewide_tags_post', 10, 2);

function sitewide_tags_post_delete( $post_id ) {
	/*
	 * what should we do if a post will be deleted and the tags blog feature is disabled?
	 * need an check if we have a post on the tags blog and if so - delete this
	 */
	global $wpdb;
	$tags_blog_id = get_sitewide_tags_option( 'tags_blog_id' );
	if( null === $tags_blog_id )
		return;

	if( $wpdb->blogid == $tags_blog_id )
		return;

	$post_blog_id = $wpdb->blogid;
	switch_to_blog( $tags_blog_id );
	$global_post_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid = '{$post_blog_id}.{$post_id}'" );
	if( null !== $global_post_id )
		wp_delete_post( $global_post_id );

	restore_current_blog();
}
add_action('delete_post', 'sitewide_tags_post_delete');

/**
 * remove all posts from a given blog ($blog_id != 0)
 * - used if a blog is deleted or marked as deactivat, spam, archive, mature
 * - also runs if a blog is switched to a none public blog (called by
 *   sitewide_tags_public_blog_update), more details on sitewide_tags_public_blog_update
 * removes some posts if the limit is reached ($blog_id == 0)
 * - triggered by other actions but without an given blog_id
 * - number of posts to delete in $max_to_del
 * 
 * @param $blog_id
 */
function sitewide_tags_remove_posts($blog_id = 0) {
	global $wpdb;
	$tags_blog_id = get_sitewide_tags_option( 'tags_blog_id' );
	$max_to_del = 10;
	
	if( !$tags_blog_id )
		return;

	/* actions on the tags blog */
	if ( ($blog_id == 0) && ($wpdb->blogid == $tags_blog_id) )
		return;		
	if ( $tags_blog_id == $blog_id )
		return;

	switch_to_blog( $tags_blog_id );

	if ( $blog_id != 0 ) {
		$posts = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '".$blog_id.".%'");
		if( is_array( $posts ) && !empty( $posts ) ) {
			foreach( $posts as $p_id ) {
				wp_delete_post( $p_id );
			}
		}
	} else {
		/* delete all posts over the max limit */
		if( mt_rand( 0, 10 ) ) {
			$allowed_post_types = apply_filters( 'sitewide_tags_allowed_post_types', array( 'post' => true ) );
			if( is_array( $allowed_post_types ) && !empty( $allowed_post_types ) ) {
				$post_types = array();
				foreach( $allowed_post_types as $k => $v ) {
					if( $v ) {
						$post_types[] = $k;
					}
				}
				if( is_array( $post_types ) && !empty( $post_types ) ) {
					if( count( $post_types ) > 1 ) 
						$where = "IN ('" . join( "','", $post_types ) . "') ";
					else
						$where = "= '" . $post_types[0] . "' ";
				} else {
					$where = "= 'post' ";
				}
				$posts = $wpdb->get_results( "SELECT ID, guid FROM {$wpdb->posts} WHERE post_status='publish' AND post_type {$where} ORDER BY ID DESC limit " . get_sitewide_tags_option( 'tags_max_posts', 5000 ) . ", " . $max_to_del );
				if( is_array( $posts ) && !empty( $posts ) ) {
					foreach( $posts as $p ) {
						if( preg_match('|^.*\.([0-9]+)$|', $p->guid, $matches) && intval( $matches[1] ) > 0 )
							wp_delete_post( $p->ID );
					}
				}
			}
		}
	}
	restore_current_blog();
}
/* complete blog actions ($blog_id != 0) */
add_action('delete_blog', 'sitewide_tags_remove_posts', 10, 1);
add_action('archive_blog', 'sitewide_tags_remove_posts', 10, 1);
add_action('deactivate_blog', 'sitewide_tags_remove_posts', 10, 1);
add_action('make_spam_blog', 'sitewide_tags_remove_posts', 10, 1);
add_action('mature_blog', 'sitewide_tags_remove_posts', 10, 1);
/* single post actions ($blog_id == 0) */
add_action("transition_post_status", 'sitewide_tags_remove_posts');

/**
 * called as an action if the public state for a blog is switched
 * - if a blog becomes not public - all posts in the tags blog will be removed
 * - bug on 1.5.1: update_option_blog_public is only triggered if the public state 
 *   is changed from the backend - from edit blog as siteadmin the action isn't
 *   running and the state in the blogs backend isn't changed
 *
 * @param int $old - old public state
 * @param int $new - new state, public == 1, not public == 0
 */
function sitewide_tags_public_blog_update($old, $new) {
	global $wpdb;
	$tags_blog_id = get_sitewide_tags_option( 'tags_blog_id' );

	if( !$tags_blog_id )
		return;

	/* the tags blog */
	if ( $tags_blog_id == $wpdb->blogid )
		return;
	
	if ($new == 0 ) {
		sitewide_tags_remove_posts($wpdb->blogid);
	}
}
add_action('update_option_blog_public', 'sitewide_tags_public_blog_update', 10, 2);

function sitewide_tags_post_link( $link, $post ) {
	global $wpdb;
	$tags_blog_id = get_sitewide_tags_option( 'tags_blog_id' );
	if( !$tags_blog_id )
		return $link;

	if( $wpdb->blogid == $tags_blog_id ) {
		$url = get_post_meta( $post->ID, "permalink", true );
		if( $url )
			return $url;
	}
	return $link;
}
add_filter('post_link', 'sitewide_tags_post_link', 10, 2);

function sitewide_tages_pages_filter( $post_types ) {
	if( get_sitewide_tags_option( 'tags_blog_pages' ) )
		$post_types = array_merge( $post_types, array( 'page' => true ) );
	return $post_types;
}
add_filter( 'sitewide_tags_allowed_post_types', 'sitewide_tages_pages_filter' );

function get_sitewide_tags_option( $key, $default = false ) {
	static $tags_options = '1';
	if( $tags_options == '1' ) {
		$tags_options = get_site_option('sitewide_tags_blog');
	}
	if( is_array( $tags_options ) ) {
		if( $key == 'all' )
			return $tags_options;
		elseif( isset( $tags_options[$key] ) )
			return $tags_options[$key];
	}
	return get_site_option($key, $default);
}

function update_sitewide_tags_option( $key, $value = '', $flush = false ) {
	static $tags_options = '1';
	if( $tags_options == '1' ) {
		// don't save unless something has changed
		if( $key === true )
			return;
		$tags_options = get_site_option('sitewide_tags_blog');
	}
	if( !$tags_options ) {
		$tags_options = array();
	}
	if( $key !== true)
		$tags_options[$key] = $value;
	if( $flush || $key === true )
		return update_site_option( 'sitewide_tags_blog', $tags_options );
}

?>
