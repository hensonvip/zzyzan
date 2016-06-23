<?php get_header();?>
<div class="g">
	<div class="row">
		<?php
		if(have_posts()){
			while(have_posts()){
				the_post();
				?>
				<div id="main" class="main g-desktop-3-4">
					<?php theme_functions::page_content();?>
					
					<?php comments_template();?>
				</div>
				<?php include __DIR__ . '/sidebar.php';?>
			<?php 
			}
		}else{ 
			?>
			
		<?php } ?>
	</div>
</div>
<?php get_footer();?>