<?php
/**
 * Template name: Account
 */
$active_tab = get_query_var('tab');
if(!$active_tab)
	$active_tab = 'dashboard';
?>
<?php get_header();?>
<div class="g">
	<div class="row">
		<div id="account-navbar" class="g-desktop-1-6">
			<ul class="nav nav-vertical">
				<?php
				$account_navs = apply_filters('account_navs',[]);
				if(!empty($account_navs)){
					foreach($account_navs as $k => $v){
						$active_class = $k === $active_tab ? ' active ' : null;
						?>
						<li class="<?= $active_class;?>"><?= $v;?></li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<div class="g-desktop-5-6">
			<div id="account-content">
				<?php include __DIR__ . '/tpl/page-account-' . $active_tab . '.php';?>
			</div>
		</div>
	</div><!-- /.row -->
</div>
<?php get_footer();?>