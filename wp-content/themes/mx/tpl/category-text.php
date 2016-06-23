<?php get_header();?>
<div class="g">
	<div id="main">
		<?php
		if(have_posts()){
			$loop_i = 0;
			foreach($wp_query->posts as $post){
				setup_postdata($post);
				theme_functions::archive_card_text([
					'classes' => '',
					'lazyload' => $loop_i <= 5 ? false : true,
				]);
				++$loop_i;
			}
			?>
		<?php }else{ ?>
			<?= status_tip('info',___('No content yet.'));?>
		<?php } ?>
		<div class="area-pagination archive-pagination">
			<?php theme_functions::pagination();?>
		</div>
	</div><!-- /#main -->
</div><!-- /.g -->
<?php get_footer();?>