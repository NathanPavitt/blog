<?php
/**
* This is trhe main index page for the atos origin theme
* 
*/
get_header();

// this needs to the the category id of the poll category
// which may be diferent of diferent installs
query_posts($query_string . '&cat=-16');
wp_get_current_user();
?>
		<div id="left_column">
			<?php get_sidebar(); ?> 
		</div>

<!-- start of main page 2 -->
		<div id="main_column">
			<?php if (have_posts()) : ?>
		
				<?php while (have_posts()) : the_post(); ?>
					<?php 
						$this_author = get_the_author(); 
								
					?>
					<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
						
						<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

						
						<div class="person">
							<img src="<?php the_author_yim(); ?>"/>
							<span class="person_name"><?php the_author_posts_link(); ?></span>
							<span class="person_date"><?php the_time('F jS, Y') ?></span>
							<span class="person_comments"><?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></span>
						</div>

						<div class="entry">
							<?php the_content('Read the rest of this entry &raquo;'); ?>
						</div>
		
						<p class="postmetadata">
						<?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  
						</p>
					</div>
		
				<?php endwhile; ?>
		
				<div class="navigation">
					<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
					<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
				</div>
		
			<?php else : ?>
		
				<h2 class="center">Not Found</h2>
				<p class="center">Sorry, but you are looking for something that isn't here.</p>
				<?php get_search_form(); ?>
		
			<?php endif; ?>
		</div>

		<div id="right_column">
			<?php include "right_column.php" ?>
		</div>
	
<?php get_footer(); ?>
