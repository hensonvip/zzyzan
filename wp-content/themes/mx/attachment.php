<?php get_header();?>
<div class="g">
		<?php
		if(have_posts()){
			while(have_posts()){
				the_post();
				?>
				<div id="main" class="main">
					<article id="post-<?= $post->ID;?>" <?php post_class(['singular-post panel']);?>>
						<h2 class="entry-title">
							<?= sprintf(___('The attachment of %s'),'<a href="' . theme_cache::get_permalink($post->post_parent) . '">' . theme_cache::get_the_title($post->post_parent) . '</a>');?>
						</h2>

						<div class="entry-content content-reset">
							<?php the_content();?>
						</div>
						
						<footer class="entry-footer">
							<?php
							/** 
							 * post-share
							 */
							if(class_exists('theme_post_share') && theme_post_share::is_enabled()){
								?>
								<div class="post-meta post-share">
									<?= theme_post_share::display();?>
								</div>
								<?php
							} /** end post-share */
							?>
							
						</footer>
					</article>
				</div>
			<?php 
			}
		}else{ 
			?>
			
		<?php } ?>
	</div>
</div>
<?php get_footer();?>