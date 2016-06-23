<?php
/**
 * Template name: Storage download page
 */
if(!class_exists('theme_custom_storage'))
	die(___('Lacking the class theme_custom_storage'));

$target_post = theme_custom_storage::get_decode_post();
?>
<?php get_header();?>
<div class="g">
	<div class="panel singular-post singular-download">
		<div class="heading">
			<h2 class="entry-title"><i class="fa fa-file fa-fw"></i> <?= sprintf(___('You are ready to download "%s"'),'<a href="' . theme_cache::get_permalink($target_post->ID) . '">' . theme_cache::get_the_title($target_post->ID) . '</a>');?></h2>
		</div>
		<div class="entry-content content-reset">
			<?php
			/*if(have_posts()){
				while(have_posts()){
					the_post();
					the_content();
				}
			}*/
			?>
		</div>
		<?php theme_custom_storage::download_info($target_post->ID);?>
	</div>
</div>
<?php get_footer();?>