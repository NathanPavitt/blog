<div id ="location" class="side_blob">

<?php 
	if ( is_404() || is_category() || is_day() || is_month() || is_year() || is_search() || is_paged() ) {
?> 

	<?php /* If this is a 404 page */ if (is_404()) { ?>
	<?php /* If this is a category archive */ } elseif (is_category()) { ?>
	<p>You are currently browsing the archives for the <?php single_cat_title(''); ?> category.</p>

	<?php /* If this is a yearly archive */ } elseif (is_day()) { ?>
	<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> archives
	for the day <?php the_time('l, F jS, Y'); ?>.</p>

	<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
	<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> archives
	for <?php the_time('F, Y'); ?>.</p>

	<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
	<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> archives
	for the year <?php the_time('Y'); ?>.</p>

	<?php /* If this is a monthly archive */ } elseif (is_search()) { ?>
	<p>You have searched the <a href="<?php echo bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> archives
	for <strong>'<?php the_search_query(); ?>'</strong>. If you are unable to find anything in these search results, you can try one of these links.</p>

	<?php /* If this is a monthly archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
	<p>You are currently browsing the <a href="<?php echo bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> archives.</p>

	<?php } ?>
<?php 
	}

?>
</div>


<div id ="twitter_feed" class="side_blob">
	<script src="http://widgets.twimg.com/j/2/widget.js"></script>
	<script>
	new TWTR.Widget({
	  version: 2,
	  type: 'profile',
	  rpp: 5,
	  interval: 6000,
	  width: 'auto',
	  height: 300,
	  theme: {
	    shell: {
	      background: '#333333',
	      color: '#ffffff'
	    },
	    tweets: {
	      background: '#f6f6f6',
	      color: '#000000',
	      links: '#D21241'
	    }
	  },
	  features: {
	    scrollbar: false,
	    loop: false,
	    live: true,
	    hashtags: true,
	    timestamp: true,
	    avatars: true,
	    behavior: 'all'
	  }
	}).render().setUser('atosoriginblog').start();
	</script>
</div>



         
        

<div id="authors" class="side_blob">
    <?php
	//$default_gravatar = urlencode( get_bloginfo( 'template_url') . "/images/fish_gravatar.jpg" );
    $default_gravatar = "";
    
	
    $order = 'user_nicename';
    $user_ids = $wpdb->get_col("SELECT ID FROM $wpdb->users WHERE user_login != 'admin' ORDER BY $order "); // query users
    foreach($user_ids as $user_id) : // start authors' profile "loop"
	    $user = get_userdata($user_id);
	    $email = strtolower($user->user_email);
	    $email_md5 = md5($email);
	    
	    
    ?>
    <a href="/?author=<?php echo $user->ID ?>">
	    <div class="mini_author">
	    	<img  class="gravatar" 	src="http://www.gravatar.com/avatar/<?php echo $email_md5 ?>?s=40&d=<?php echo $default_gravatar ?>" />
	    	<span class="author_name"	>
    			<?php echo $user->display_name ?>
    		</span>
	    </div>
    </a>
    <?php
    endforeach; // end of authors' profile 'loop'
    ?>

</div>
