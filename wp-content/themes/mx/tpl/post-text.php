<?php get_header();?>
<div class="g">
		<?php
		if(have_posts()){
			while(have_posts()){
				the_post();
				?>
				<div id="main" class="main">
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
					<?php comments_template();?>
					<?php theme_functions::the_related_posts();?>
				</div>
			<?php 
			}
		}else{ 
			?>
			
		<?php } ?>
	</div>
</div>
<?php get_footer();?>