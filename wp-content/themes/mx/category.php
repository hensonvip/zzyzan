<?php get_header();?>
<div class="g">
	<?= theme_functions::get_crumb();?>
	<div id="main">
		<?php
		if(have_posts()){
			?>
			<div class="row">
				<?php
				$loop_i = 0;
				foreach($wp_query->posts as $post){
					setup_postdata($post);
					theme_functions::archive_card_sm([
						'classes' => 'g-desktop-1-4 g-tablet-1-2',
						'lazyload' => $loop_i <= 8 ? false : true,
					]);
					++$loop_i;
				}
				?>
			</div>
		<?php }else{ ?>
			<?= status_tip('info',___('No content yet.'));?>
		<?php } ?>
		<div class="area-pagination archive-pagination">
			<?php theme_functions::pagination();?>
		</div>
	</div><!-- /#main -->
</div><!-- /.g -->
<?php get_footer();?>