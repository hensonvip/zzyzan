<div id="sidebar-container" class="g-desktop-1-4">
<div id="sidebar" class="widget-area" role="complementary">
<?php if(!theme_cache::dynamic_sidebar('widget-area-post')){
	?>
	<div class="panel">
		<div class="content">
			<div class="page-tip">
				<?= status_tip('info', ___('Please set some widgets in singular post.'));?>
			</div>
		</div>
	</div>
<?php } ?>
</div><!-- /.widget-area -->
</div><!-- /#sidebar-container -->