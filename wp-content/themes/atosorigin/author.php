<?php 

get_header();

if(isset($_GET['author_name'])) :
	$curauth = get_userdatabylogin($author_name);
else :
	$curauth = get_userdata(intval($author));
endif;
$current_user = wp_get_current_user();
$twitter_id = "atosoriginblog";
if (!is_null($curauth->twitter)){
	$twitter_id = $curauth->twitter;
} 

?>
<div id="left_column" style="width:300px;">
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
		      background: '#ffffff',
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
		}).render().setUser('<?php echo $twitter_id ?>').start();
		</script>
	</div>
	
	<div id ="linked_in" class="side_blob">
		<script type="text/javascript" src="http://www.linkedin.com/js/public-profile/widget-os.js"></script>
		<a class="linkedin-profileinsider-inline" href="<?php echo $curauth->linkedin; ?>"></a>
	</div>	
</div>

<div id="wide_column" style="width:595px">


	<div id="author_profile">
		<img id="author_image" src="<?php echo $curauth->yim; ?>"/>  		
		<span id="author_name"><?php echo $curauth->nickname; ?></span>
		<span id="author_website"><a href="<?php echo $curauth->user_url; ?>"><?php echo $curauth->user_url; ?></a></span>
		<span id="author_bio"><?php echo $curauth->user_description; ?></span>
	</div>

<!-- The Loop -->
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			
			<div id="post-<?php the_ID(); ?>" class="author_post">
				<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">	
					<div class="author_post_title">
						<?php the_title(); ?>
					</div>					
					<div >
						<?php the_excerpt(); ?>
					</div>
				</a>
			</div>
			

    <?php endwhile; else: ?>
        <p><?php _e('No posts by this author.'); ?></p>

    <?php endif; ?>

<!-- End Loop -->



</div>

<?php get_footer(); ?>
