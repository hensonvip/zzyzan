<?php get_header();?>
<div class="g">
	<div id="main">
		<form id="fm-search-page" action="<?= theme_cache::home_url(); ?>/">
			<label for="search-page-s"><i class="fa fa-search fa-fw"></i></label>
			<input type="search" id="search-page-s" name="s" class="form-control" value="<?= esc_attr(get_search_query());?>" placeholder="<?= ___('Type keywords to search');?>" title="<?= ___('Type keywords to search');?>">
		</form>
		<?php
		if(have_posts()){
			?>
			<div class="row">
				<?php
				$loop_i = 0;
				foreach($wp_query->posts as $post){
					setup_postdata($post);
					theme_functions::archive_card_lg([
						'classes' => 'g-tablet-1-2 g-tablet-1-4',
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
</div><!-- /.container -->
<?php get_footer();?>