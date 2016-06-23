<?php
if( post_password_required() || !comments_open() ) 
	return;
?>
<?php theme_functions::theme_respond();?>

<div id="comments" class="panel comment-wrapper <?= have_comments() ? null : 'none';?>">
	<div class="heading">
		<h2 class="have-comments-title title">
			<i class="fa fa-comments"></i> 
			<span id="comment-number-<?= $post->ID;?>">-</span> 
			<?= ___('Comments');?>
		</h2>
	</div>
	
	<ul id="comment-list-<?= $post->ID;?>" class="comment-list">
		<li class="comment media comment-loading">
			<div class="page-tip"><?= status_tip('loading',___('Loading, please wait...'));?></div>
		</li>
	</ul>
	
	<?php 
	if(theme_features::get_comment_pages_count($wp_query->comments) > 1){ 
		?>
		<div id="comment-pagination-container"></div>
	<?php } ?>

	<a href="#respond" class="btn btn-success btn-lg btn-block"><i class="fa fa-edit"></i> <?= ___('Write a comment');?></a>
</div><!-- /.comment-wrapper -->

