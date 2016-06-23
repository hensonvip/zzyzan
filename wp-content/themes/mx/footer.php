<?php
/**
 * ad
 */
if(class_exists('theme_adbox') && !empty(theme_adbox::display_frontend('above-footer'))){
	?>
	<div class="g"><div class="ad-container ad-above-footer"><?= theme_adbox::display_frontend('above-footer');?></div></div>
	<?php
}
?>
<footer id="footer">
	<?php if(!wp_is_mobile()){ ?>
		
		<div class="g">
			<div class="widget-area row">
				<?php if(!theme_cache::dynamic_sidebar('widget-area-footer')){ ?>
					<div class="g-desktop-1-1">
						<div class="panel">
							<div class="content">
								<div class="page-tip">
									<?= status_tip('info', ___('Please set some widgets in footer.'));?>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>

			<!-- links -->
			<?php if(theme_cache::is_home()){ ?>
				<div class="widget panel links-container">
					<div class="heading">
						<h2 class="widget-title">
							<i class="fa fa-link"></i> <?= ___('Links');?>
						</h2>
					</div>
					<div class="content">
						<?php
						/**
						 * links
						 */
						theme_cache::wp_nav_menu([
							'theme_location'    => 'links-footer',
							'container'         => 'nav',
							'menu_class'        => 'menu',
							'menu_id' 			=> 'links-footer',
							'fallback_cb'       => 'custom_navwalker::fallback',
							'walker'            => new custom_navwalker
						]);
						?>
					</div>
				</div>
			<?php } ?>
		</div><!-- /.g -->
		<p class="footer-meta copyright">
			<?= class_exists('theme_user_code') ? theme_user_code::get_frontend_footer_code() : null;?>
		</p>
	<?php }else{ ?>
		<div class="g"><p><?= class_exists('theme_user_code') ? theme_user_code::get_frontend_footer_code() : null;?></p></div>
	<?php } ?>
	
</footer>
<a href="#" id="back-to-top" class="fa fa-arrow-up fa-2x" title="<?= ___('Back to top');?>"></a>
<?php wp_footer();?></body></html>