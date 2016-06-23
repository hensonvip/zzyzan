<div class="mod-body">
	<?php
	if(have_posts()){
		?>
		<div class="row">
			<?php
			foreach($wp_query->posts as $post){
				setup_postdata($post);
				theme_functions::archive_card_lg([
					'classes' => 'g-phone-1-2 g-tablet-1-3 g-desktop-1-4',
				]);
			}
			?>
		</div>
	<?php }else{ ?>
		<div class="page-tip"><?= status_tip('info',___('No post yet.')); ?></div>
	<?php } ?>
</div>
<?php if($GLOBALS['wp_query']->max_num_pages > 1){ ?>
	<?= theme_functions::pagination();?>
<?php } ?>
</div>