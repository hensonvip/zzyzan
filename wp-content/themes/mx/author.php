<?php get_header();?>
<?php
global $author;
$tab_active = get_query_var('tab');

$tabs = theme_custom_author_profile::get_tabs(null,$author);

if(empty($tab_active) || !isset($tabs[$tab_active]))
	$tab_active = 'profile';
	
?>
<div class="g">
	<h3 class="crumb-title">
		<?= theme_cache::get_avatar($author);?>
		<?= theme_cache::get_the_author_meta('display_name',$author);?> - <small><?= $tabs[$tab_active]['text'];?></small>
	</h3>
	<nav class="nav">
		<?php 
		foreach($tabs as $k => $v){
			$class_active = $tab_active === $k ? ' active ' : null;
			?>
			<a class="<?= $class_active;?>" href="<?= $v['url'];?>">
				<i class="fa fa-<?= $v['icon'];?> fa-fw"></i> 
				<?= $v['text'];?>
			</a>
		<?php } ?>					
	</nav>
	<div class="panel">
		<?php include __DIR__ . '/tpl/author-' . $tab_active . '.php';?>
	</div>
</div>
<?php get_footer();?>