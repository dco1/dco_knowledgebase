
<?php get_header(); ?>
	<div id="content" style="width:100%;">
		<?php do_action('before_main_loop');?>
		<?php do_shortcode('[knowledgebase]');?>
    </div><!-- #content -->
       	
<?php get_footer(); ?>