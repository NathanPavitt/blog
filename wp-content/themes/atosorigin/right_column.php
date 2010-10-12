<div id ="poll" class="side_blob">
	<div class="TopCorners"></div> 
	<div class="ContactMiddle" id="ContactTitle">
	<?php 
		$is_preview = new WP_Query("category_name=poll&showposts=1");
		$recent = new WP_Query("category_name=poll&showposts=1");
		
		while($recent->have_posts()) : $recent->the_post();
	?>
	<?php
		the_title ();
	?>
	<?php endwhile; ?>
	</div>
	
	<div class="ContactMiddle" id="Poll">
	<?php 
		$is_preview = new WP_Query("category_name=poll&showposts=1");
		$recent = new WP_Query("category_name=poll&showposts=1");
		
		while($recent->have_posts()) : $recent->the_post();
	?>
	<?php 
		the_content(); 
	?>
	
	<?php endwhile; ?>
	</div>
	
	<div class="BottomCorners"></div>
</div>

<?php if (function_exists('get_recent_comments')) { ?>
        <h2><?php _e('Recent Comments:'); ?></h2>
              
              <?php get_recent_comments(); ?>
              
        
        <?php } ?>   
         


<?php 
	if(function_exists("summmerOlympicsCountdown")){
?>
<div id ="olympics_countdown" class="side_blob" >
	
	<h2>Olympic Games</h2>
	<ul>
	<p><strong>London 2012 <br />27 July - 12 Aug 2012</strong></p>
	<object 
							classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" 
							codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" 
							width="180" 
							height="40" 
							id="main11" 
							align="middle">
							<param name="allowScriptAccess" value="sameDomain" />
							<param name="allowFullScreen" value="false" />
							<param name="movie" value="<?php bloginfo('template_directory'); ?>/animation/countdown.swf" />
							<param name="quality" value="high" />
							<param name="bgcolor" value="#EEEEEE" />	
							<embed src="<?php bloginfo('template_directory'); ?>/animation/countdown.swf" 
								quality="high" 
								bgcolor="#EEEEEE" 
								width="180" 
								height="40" 
								name="main11" 
								align="middle" 
								allowScriptAccess="sameDomain" 
								allowFullScreen="false" 
								type="application/x-shockwave-flash" 
								pluginspage="http://www.macromedia.com/go/getflashplayer" />
						</object>	
	</ul>	
	<h2>ParaOlympic Games</h2>
	<ul>
	<p><strong>London 2012 <br />29 Aug - 9 Sept 2012</strong><br /></p>
	<object 
							classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" 
							codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" 
							width="180" 
							height="40" 
							id="main11" 
							align="middle">
							<param name="allowScriptAccess" value="sameDomain" />
							<param name="allowFullScreen" value="false" />
							<param name="movie" value="<?php bloginfo('template_directory'); ?>/animation/countdownParaOlympics.swf" />
							<param name="quality" value="high" />
							<param name="bgcolor" value="#EEEEEE" />	
							<embed src="<?php bloginfo('template_directory'); ?>/animation/countdownParaOlympics.swf" 
								quality="high" 
								bgcolor="#EEEEEE" 
								width="180" 
								height="40" 
								name="main11" 
								align="middle" 
								allowScriptAccess="sameDomain" 
								allowFullScreen="false" 
								type="application/x-shockwave-flash" 
								pluginspage="http://www.macromedia.com/go/getflashplayer" />
						</object>
		</ul>
</div>
<?php 
	}
?> 

<div id ="subscribe"  class="side_blob">
	<a id="rss_subscribe_link" href="<?php bloginfo('rss2_url'); ?>">
		<img id="subscribe" src="<?php bloginfo('template_directory'); ?>/images/rss_subscribe.gif" alt="Subscribe to RSS Feeds"/>
	</a>
</div>

<div id ="categories" class="side_blob">
	<h2>Categories</h2>
	<ul>
		<?php wp_list_categories('show_count=1&title_li='); ?>
	</ul>
</div>

<div id ="archive" class="side_blob">
	<h2>Archives</h2>
	<ul>
		<?php wp_get_archives('type=monthly'); ?>
	</ul>
</div>

<?php /* If this is the frontpage */ if ( is_home() || is_page() ) { ?>
<div id ="links" class="side_blob">
	<?php wp_list_bookmarks('title_li=&category_before=&category_after='); ?>
</div>
<?php } ?>


<div id ="badge" class="side_blob">		
	
	<a id="blog_badge_link" href="#" style="border:none;text-decoration: none;">
	<img src="http://www.atosconsultingblog.co.uk/gfx/atos_origin_blog_badge.png" width="160" height="60" alt="Get the blog badge" style="border:none;"/>
		Support this blog, get the badge
	</a>
	<div id="blog_badge_dialog" class="" title="Display our blog badge" style="display:none;">
		Copy and paste this code to your blog:<br/> 
		<br/>
		&lt;script <br/>src="http://blog.atosorigin.com/wp-content/uploads/2010/07/badge.js" <br/> type="text/javascript"&gt; <br/> &lt;/script&gt;
	</div>
</div>

