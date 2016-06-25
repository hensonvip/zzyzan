<?php get_header();?>
<style>
	body.in-text-mode #main{max-width:100%; width: 75%; float: left;}
</style>
<div class="g">
	<div class="row">
		<?php
		if(have_posts()){
			while(have_posts()){
				the_post();
				?>
				<div id="main" class="main g-desktop-3-4">
					<?php theme_functions::singular_content();?>
					
					<?php theme_functions::adjacent_posts();?>
					
					<?php
					/**
					 * ad
					 */
					if(class_exists('theme_adbox') && !empty(theme_adbox::display_frontend('below-adjacent-post'))){
						?>
						<div class="ad-container ad-below-adjacent-post"><?= theme_adbox::display_frontend('below-adjacent-post');?></div>
						<?php
					}
					?>
					<?php theme_functions::the_related_posts();?>
					<?php comments_template();?>
				</div>
				<?php include __DIR__ . '/../sidebar-post.php';?>
			<?php 
			}
		}else{ 
			?>
			
		<?php } ?>
	</div>
</div>
<?php get_footer();?>