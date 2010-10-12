<?php

// Put functions into one big function we'll call at the plugins_loaded
// action. This ensures that all required plugin functions are defined.
function widget_authors_init() {

  // Check for the required plugin functions. This will prevent fatal
  // errors occurring when you deactivate the dynamic-sidebar plugin.
  if ( !function_exists('register_sidebar_widget') )
    return;

  function sanitize ( $name ) {
    $name = strtolower( $name ); // all lowercase
    $name = preg_replace( '/[^a-z0-9 ]/','', $name ); // nothing but a-z 0-9 and spaces
    $name = preg_replace( '/\s+/','-', $name ); // spaces become hyphens
    return $name;
  }

  // Options and default values for this widget
  function widget_authors_options() {
    return array(
      'Title' => "Authors",
      'Option Count' => false,
      'Exclude Administrator' => true,
      'Show Full Name' => false,
      'Hide Empty' => true,
      'Show Feed Image' => true
    );
  }

  // This is the function that outputs the Authors code.
  function widget_authors( $args ) {
    // $args is an array of strings that help widgets to conform to
    // the active theme: before_widget, before_title, after_widget,
    // and after_title are the array keys. Default tags: li and h2.
    extract( $args );

    // Each widget can store and retrieve its own options.
    // Here we retrieve any options that may have been set by the user
    // relying on widget defaults to fill the gaps.
    $options = array_merge( widget_authors_options(), get_option( 'widget_authors' ) );
    unset($options[0]); //returned by get_option(), but we don't need it

    // These lines generate our output. Widgets can be very complex
    // but as you can see here, they can also be very, very simple.
    echo $before_widget . $before_title . $options['Title'] . $after_title;

    // Translate yes/no values
    if ( count( $options ) )
    foreach ( $options as $k=>$v )
      if ( $k!='Title' && $k!='Style' )
	$options[$k] = $v ? 1 : 0;

    // Create feed image parameter
    $feed_image = $options['Show Feed Image'] ? '&feed_image='.LISTAUTHORS_URL.'feed-icon-14x14.png&feed=RSS feed' : '';
?>

<!-- Authors -->
<ul>
<?php wp_list_authors('optioncount='.$options['Option Count'].'&exclude_admin='.$options['Exclude Administrator'].'&show_fullname='.$options['Show Full Name'].'&hide_empty='.$options['Hide Empty'].$feed_image); ?>
</ul>
<!-- /Authors -->

<?
    echo $after_widget;
  }

  // This is the function that outputs the form to let the users edit
  // the widget's title. It's an optional feature that users cry for.
  function widget_authors_control() {
    // Each widget can store and retrieve its own options.
    // Here we retrieve any options that may have been set by the user
    // relying on widget defaults to fill the gaps.
    $options = $newoptions = get_option( 'widget_authors' );

    // If user is submitting custom option values for this widget
    if ( $_POST['authors-submit'] ) {
	    $newoptions['Title'] = strip_tags(stripslashes($_POST['authors-title']));
	    $newoptions['Option Count'] = isset($_POST['authors-option-count']);
	    $newoptions['Exclude Administrator'] = isset($_POST['authors-exclude-administrator']);
	    $newoptions['Show Full Name'] = isset($_POST['authors-show-full-name']);
	    $newoptions['Hide Empty'] = isset($_POST['authors-hide-empty']);
	    $newoptions['Show Feed Image'] = isset($_POST['authors-show-feed-image']);

	    // Save changes
	    if ( $options != newoptions ) {
		    $options = $newoptions;
		    update_option('widget_authors', $options);
	    }
    }
    $title = attribute_escape($options['Title']);
    $optioncount = $options['Option Count'] ? 'checked="checked"' : '';
    $exclude_admin = $options['Exclude Administrator'] ? 'checked="checked"' : '';
    $show_fullname = $options['Show Full Name'] ? 'checked="checked"' : '';
    $hide_empty = $options['Hide Empty'] ? 'checked="checked"' : '';
    $feed_image = $options['Show Feed Image'] ? 'checked="checked"' : '';

    // Here is our little form segment. Notice that we don't need a
    // complete form. This will be embedded into the existing form.
    // Be sure you format your options to be valid HTML attributes
    // before displaying them on the page.
?>
   <p><label for="authors-title"><?php _e('Title:'); ?> <input class="widefat" id="authors-title" name="authors-title" type="text" value="<?php echo $title; ?>" /></label></p>
                        <p>
                                <label for="authors-option-count"><input class="checkbox" type="checkbox" <?php echo $optioncount; ?> id="authors-option-count" name="authors-option-count" /> <?php _e('Show number of published posts'); ?></label>
                                <br />
                                <label for="authors-exclude-administrator"><input class="checkbox" type="checkbox" <?php echo $exclude_admin; ?> id="authors-exclude-administrator" name="authors-exclude-administrator" /> <?php _e('Exclude administrator'); ?></label>
                                <br />
                                <label for="authors-show-full-name"><input class="checkbox" type="checkbox" <?php echo $show_fullname; ?> id="authors-show-full-name" name="authors-show-full-name" /> <?php _e('Show full name'); ?></label>
                                <br />
                                <label for="authors-hide-empty"><input class="checkbox" type="checkbox" <?php echo $hide_empty; ?> id="authors-hide-empty" name="authors-hide-empty" /> <?php _e('Hide authors with 0 posts'); ?></label>
                                <br />
                                <label for="authors-show-feed-image"><input class="checkbox" type="checkbox" <?php echo $feed_image; ?> id="authors-show-feed_image" name="authors-show-feed-image" /> <?php _e('Show an RSS feed image and link'); ?></label>
                        </p>
 
    <input type="hidden" id="authors-submit" name="authors-submit" value="1" />
<?php
  }

  // This registers our widget so it appears with the other available
  // widgets and can be dragged and dropped into any active sidebars.
  register_sidebar_widget( 'Authors', 'widget_authors' );

  // This registers our optional widget control form. Because of this
  // our widget will have a button that reveals a 300x100 pixel form.
  register_widget_control( 'Authors', 'widget_authors_control', 220, 50 * count( widget_authors_options() ) );
}

// Run our code later in case this loads prior to any required plugins.
add_action( 'plugins_loaded', 'widget_authors_init' );

?>
