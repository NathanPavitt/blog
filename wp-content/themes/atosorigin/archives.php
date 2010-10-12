<?php
/*
Template Name: Archives
*/
?>

<?php get_header(); ?>
<div id="left_column">
	<?php get_sidebar(); ?> 
</div>

<div id="main_column" >

<?php get_search_form(); ?>

<h2>Archives by Month:</h2>
	<ul>
		<?php wp_get_archives('type=monthly'); ?>
	</ul>

<h2>Archives by Subject:</h2>
	<ul>
		 <?php wp_list_categories(); ?>
	</ul>

</div>

<?php get_footer(); ?>
