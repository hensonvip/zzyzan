<?php
/**
 * template: Page rank
 */

get_header();


$active_tab = get_query_var('tab','recommend');

if(!theme_page_rank::get_tabs($active_tab))
	$active_tab = 'recommend';
	

?>
<div class="g">
	<nav class="nav">
		<?php
		foreach(theme_page_rank::get_tabs() as $k => $v){
			$active_class = $active_tab === $k ? 'class="active"' : null;
			?>
			<a <?= $active_class;?> href="<?= theme_page_rank::get_tabs($k)['url'];?>">
				<i class="fa fa-<?= theme_page_rank::get_tabs($k)['icon'];?>"></i> 
				<?= theme_page_rank::get_tabs($k)['tx'];?>
			</a>
		<?php } ?>
	</nav>
	<div class="panel-rank">
		<?php
		$include_filepath = __DIR__ . '/tpl/page-rank-' . $active_tab . '.php';
		if(is_file($include_filepath)){
			include $include_filepath;
		}else{
			?>
			<div class="content">
				<div class="page-tip">
					<?= status_tip('error',___('Can not find the include file.'));?>
				</div>
		</div>
			<?php
		}
		?>
	</div>
</div>
<?php get_footer();?>