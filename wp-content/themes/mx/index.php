<?php get_header();?>
<?php 
/**
 * slidebox
 */
//if(!wp_is_mobile() && class_exists('theme_custom_slidebox')){
	theme_custom_slidebox::display_frontend();
//} 
?>
<div class="g">

	<?php if(!wp_is_mobile()){ ?>
		<div class="recomm-container hidden-sm">
			<?php
			/**
			 * recommended box
			 */
			if(method_exists('theme_functions','the_recommended')){
				theme_functions::the_recommended();
			}
			?>
		</div>
	<?php } ?>

	<div class="row">
		<div id="main" class="g-desktop-3-4">
			
			<?php 
			/**
			 * homebox 
			 */
			theme_functions::the_homebox();
			?>
			
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
	
</div>
<?php get_footer();?>
