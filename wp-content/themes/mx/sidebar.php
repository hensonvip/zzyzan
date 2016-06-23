<div id="sidebar-container" class="g-desktop-1-4">
<div id="sidebar" class="widget-area" role="complementary">
	<?php
	/** 
	 * home widget
	 */
	if(theme_cache::is_home() && !theme_cache::dynamic_sidebar('widget-area-home')){
		?>
		<div class="panel">
			<div class="content">
				<div class="page-tip">
					<?= status_tip('info', ___('Please set some widgets in homepage.'));?>
				</div>
			</div>
		</div>
		<?php
	/** 
	 * archive widget
	 */
	}else if((theme_cache::is_category() || theme_cache::is_archive() || theme_cache::is_search()) && !theme_cache::dynamic_sidebar('widget-area-archive')){
		?>
		<div class="panel">
			<div class="content">
				<div class="page-tip">
					<?= status_tip('info', ___('Please set some widgets in archive.'));?>
				</div>
			</div>
		</div>
		<?php
	/** 
	 * page widget
	 */
	}else if(theme_cache::is_page() && !theme_cache::dynamic_sidebar('widget-area-page')){
		?>
		<div class="panel">
			<div class="content">
				<div class="page-tip">
					<?= status_tip('info', ___('Please set some widgets in singular page.'));?>
				</div>
			</div>
		</div>
		<?php
	/** 
	 * 404 widget
	 */
	}else if(theme_cache::is_404() && !theme_cache::dynamic_sidebar('widget-area-404')){
		?>
		<div class="panel">
			<div class="content">
				<div class="page-tip">
					<?= status_tip('info', ___('Please set some widgets in 404 page.'));?>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div><!-- /.widget-area -->
</div><!-- /#sidebar-container -->