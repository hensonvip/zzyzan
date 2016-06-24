<?php
/**
 * Post form
 *
 * @return 
 * @version 1.0.0
 */
function post_form($post_id = null){

	$edit = false;
	$post_title = null;
	$post_content = null;
	$post_excerpt = null;

	
	/**
	 * edit
	 */
	if(is_numeric($post_id)){
		/**
		 * check post exists
		 */
		global $post;
		$post = theme_cache::get_post($post_id);
		if(!$post || 
			!theme_custom_contribution::in_edit_post_status($post->post_status) ||
			$post->post_type !== 'post'
		){
			?>
			<div class="page-tip"><?= status_tip('error',___('Sorry, the post does not exist.'));?></div>
			<?php
			wp_reset_postdata();
			return false;
		}
		/**
		 * check post category in collection category
		 */
		if(class_exists('theme_custom_collection') && !empty(theme_custom_collection::get_cat_ids())){
			foreach(get_the_category($post_id) as $v){
				if(in_array($v->term_id,theme_custom_collection::get_cat_ids())){
					?>
					<div class="page-tip"><?= status_tip('error',___('Sorry, collection edits feature is not supported currently.'));?></div>
					<?php
					wp_reset_postdata();
					return false;
				}
				break;
			}
		}
		/**
		 * check author
		 */
		if($post->post_author != theme_cache::get_current_user_id()){
			?>
			<div class="page-tip"><?= status_tip('error',___('Sorry, you are not the post author, can not edit it.'));?></div>
			<?php
			return false;
			wp_reset_postdata();
		}
		setup_postdata($post);
		
		/**
		 * check edit lock status
		 */
		$lock_user_id = theme_custom_contribution::wp_check_post_lock($post_id);
		if($lock_user_id){
			?>
			<div class="page-tip"><?= status_tip('error',___('Sorry, you can not edit this post now, because editor is editing. Please wait a minute...'));?></div>
			<?php
			return false;
		}
	
		$edit = true;
		$post_title = $post->post_title;
		$post_content = $post->post_content;
		$post_excerpt = stripslashes($post->post_excerpt);
		
	}

	?>
	
	<?= theme_custom_contribution::get_des();?>
	<div id="fm-ctb-loading" class="page-tip"><?= status_tip('loading',___('Loading, please wait...'));?></div>
	<form action="javascript:;" id="fm-ctb" class="form-horizontal" hidden>
		<div class="form-group">
			<label for="ctb-title" class="g-tablet-1-6 control-label">
				<?= ___('Post title');?>
			</label>
			<div class="g-tablet-5-6">
				<input 
					type="text" 
					name="ctb[post-title]"
					class="form-control" 
					id="ctb-title" 
					placeholder="<?= ___('Post title (require)');?>" 
					title="<?= ___('Post title must to write');?>" 
					value="<?= esc_attr($post_title);?>" 
					required 
					autofocus
				>
			</div>
		</div>
		<!-- post excerpt -->
		<div class="form-group">
			<label for="ctb-excerpt" class="g-tablet-1-6 control-label">
				<?= ___('Post excerpt');?>
			</label>
			<div class="g-tablet-5-6">
				<textarea name="ctb[post-excerpt]" id="ctb-excerpt" rows="3" class="form-control" placeholder="<?= ___('Your can write excerpt for describe the post, it will show every page nagination header.');?>"><?= $post_excerpt;?></textarea>
			</div>
		</div>
		<!-- post content -->
		<div class="form-group">
			<div class="g-tablet-1-1">
			<label for="ctb-content" >
				<?= ___('Post content');?>
			</label>
				<?php 
				wp_editor(
					$post_content,
					'ctb-content', 
					[
						'textarea_name' => 'ctb[post-content]',
						'drag_drop_upload' => false,
						'teeny' => false,
						'media_buttons' => false,
						'editor_class' => 'form-control',
					]
				);
				?>
			</div>
		</div>
		
		
		<!-- upload image -->
		<div class="form-group">
			<div class="g-tablet-1-6 control-label">
				<i class="fa fa-image"></i>
				<?= ___('Upload preview image');?>
			</div>
			<div class="g-tablet-5-6">
				<div id="ctb-file-area">
					<div class="" id="ctb-file-btn">
						<i class="fa fa-upload"></i>
						<?= ___('Select or Drag images');?>
						<input type="file" id="ctb-file" multiple >
					</div>
				</div>
				<!-- upload progress -->
				<div id="ctb-file-progress-container" class="progress">
					<div id="ctb-file-progress" class="progress-bar progress-bar-success progress-bar-striped active"></div>
				</div>
				
				<!-- file tool -->
				<div id="ctb-file-tool" class="row">
					<div class="g-tablet-1-2">
					<!-- batch insert -->
						<a href="javascript:;" id="ctb-batch-insert-btn" class="btn btn-primary btn-block">
							<i class="fa fa-plug"></i> <?= ___('Batch insert images to content');?>
						</a>
					</div>
					<div class="g-tablet-1-2">
						<select id="ctb-split-number" class="form-control" title="<?= ___('How many images to split with next-page tag?');?>">
							<option value="0"><?= ___('Do not use next-page tag');?></option>
							<?php for($i=1;$i<=10;++$i){ ?>
								<option value="<?= $i;?>"><?= sprintf(___('%d image(s) / page'),$i);?></option>
							<?php } ?>
						</select>
					</div>
				</div>

				
				<!-- file completion -->
				<div id="ctb-file-completion"></div>

				
				<!-- files -->
				<div id="ctb-files" class="row"></div>
				
			</div>
		</div>

		
<!-- storage -->
<?php
if(class_exists('theme_custom_storage') && theme_custom_storage::is_enabled()){
	?>
	<div class="form-group theme_custom_storage-group">
		<div class="g-tablet-1-6 control-label">
			<i class="fa fa-cloud-download"></i>
			<?= ___('Storage link');?>
		</div>
		<div class="g-tablet-5-6">
			<?= theme_custom_storage::display_frontend_contribution($post_id);?>
		</div>
	</div>
<?php 
/**
 * end theme_custom_storage
 */
}
?>
		
		<!-- theme_custom_download_point henson add -->
		<?php 
		if(class_exists('theme_custom_download_point') && theme_custom_download_point::is_enabled() && class_exists('theme_custom_storage') && theme_custom_storage::is_enabled()){
			if($edit){
				$download_point_meta = theme_custom_download_point::get_post_meta($post->ID);
			}else{
				$download_point_meta = null;
			}
			
			?>
			<div class="form-group">
				<div class="g-tablet-1-6 control-label">
					<i class="fa fa-money"></i> 
					下载积分
				</div>
				<div class="g-tablet-5-6">
					<div class="row theme_custom_download_point-inputs" id="theme_custom_download_point-input" >
						<div class="g-tablet-1-2">
							<div class="input-group">
								<input 
									type="text" 
									class="theme_custom_download_point" 
									name="theme_custom_download_point[download_point]" 
									id="theme_custom_download_point" 
									placeholder="留空为免费下载" 
									title="下载积分"
									value="<?= isset($download_point_meta['download_point']) ? esc_attr($download_point_meta['download_point']) : null;?>"
								>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } /** end theme_custom_download_point */ ?>
		



		<!-- cats -->
		<div class="form-group">
			<div class="g-tablet-1-6 control-label">
				<i class="fa fa-folder-open"></i>
				<?= ___('Category');?>
			</div>
			<div class="g-tablet-5-6" id="ctb-cat-container">
				<?php
				if($edit){
					$selected_cat_id = 0;
					$cats = get_the_category($post->ID);
					
					if(isset($cats[0]->term_id))
						$selected_cat_id = $cats[0]->term_id;
						
					foreach($cats as $cat){
						if($cat->parent != 0)
							$selected_cat_id = $cat->term_id;
					}
					wp_dropdown_categories([
						'id' => 'ctb-cat',
						'name' => 'ctb[cat]',
						'class' => 'form-control',
						'selected' => $selected_cat_id,
						'show_option_none' => ___('Select category'),
						'hierarchical' => true,
						'hide_empty' => false,
						'include' => (array)theme_custom_contribution::get_cat_ids(),
					]); 
				}
				?>
			</div>
		</div>





		
		<!-- tags -->
		<div class="form-group">
			<div class="g-tablet-1-6 control-label">
				<i class="fa fa-tags"></i> 
				<?= ___('Pop. tags');?>
			</div>
			<div class="g-tablet-5-6">
				<div class="checkbox-select">
					<?php
					$tags_args = [
						'orderby' => 'count',
						'order' => 'desc',
						'hide_empty' => 0,
						'number' => theme_custom_contribution::get_options('tags-number') ? theme_custom_contribution::get_options('tags-number') : 16,
					];
					$tags_ids = theme_custom_contribution::get_options('tags');
					if(empty($tag_ids)){
						$tags = get_tags($tags_args);
					}else{
						$tags = get_tags([
							'include' => implode($tags_ids),
							'orderby' => 'count',
							'order' => 'desc',
							'hide_empty' => 0,
						]);
					}
					/**
					 * edit
					 */
					if($edit){
						$exist_tags = [];
						$post_tags = get_the_tags($post->ID);
						if($post_tags){
							foreach($post_tags as $v){
								$v->selected = true;
								array_unshift($tags,$v);
							}
						}
					}
					foreach($tags as $tag){
						if($edit){
							if(isset($exist_tags[$tag->term_id])){
								continue;
							}else{
								$exist_tags[$tag->term_id] = 1;
							}
						}
						$tag_name = esc_html($tag->name);
						?>
						<label class="ctb-tag" for="ctb-tags-<?= $tag->term_id;?>">
							<input 
								class="ctb-preset-tag" 
								type="checkbox" 
								id="ctb-tags-<?= $tag->term_id;?>" 
								name="ctb[tags][]" 
								value="<?= $tag_name;?>"
								hidden 
								<?= isset($tag->selected) ? 'checked' : null;?>
							>
							<span class="label label-default">
								<?= $tag_name;?>
							</span>
						</label>
						
					<?php } ?>
				</div>
			</div>
		</div>
		
		<!-- custom tags -->
		<div class="form-group">
			<div class="g-tablet-1-6 control-label">
				<i class="fa fa-tag"></i> 
				<?= ___('Custom tags');?>
			</div>
			<div class="g-tablet-5-6">
				<div class="row">
					<?php for($i = 0;$i<=3;++$i){ ?>
						<div class="g-phone-1-2 g-tablet-1-4">
							<input id="ctb-custom-tag-<?= $i;?>" class="ctb-custom-tag form-control" type="text" name="ctb[tags][]" placeholder="<?= sprintf(___('Custom tag %d'),$i+1);?>" >
						</div>
					<?php } ?>
				</div>
			</div>
		</div>


		<!-- source -->
		<?php 
		if(class_exists('theme_custom_post_source') && theme_custom_post_source::is_enabled()){
			if($edit){
				$post_source_meta = theme_custom_post_source::get_post_meta($post->ID);
			}else{
				$post_source_meta = null;
			}
			
			?>
			<div class="form-group">
				<div class="g-tablet-1-6 control-label">
					<i class="fa fa-truck"></i> 
					<?= ___('Source');?>
				</div>
				<div class="g-tablet-5-6">
					<label class="radio-inline" for="theme_custom_post_source-source-original">
						<input 
							type="radio" 
							name="theme_custom_post_source[source]" 
							id="theme_custom_post_source-source-original" 
							value="original" 
							class="theme_custom_post_source-source-radio" 
							<?= !isset($post_source_meta['source']) || $post_source_meta['source'] === 'original' ? 'checked' : null;?> 
							target="theme_custom_post_source-input-original" 
						>
						<?= ___('Original');?>
					</label>
					<label class="radio-inline" for="theme_custom_post_source-source-reprint">
						<input 
							type="radio" 
							name="theme_custom_post_source[source]" 
							id="theme_custom_post_source-source-reprint" 
							value="reprint" 
							class="theme_custom_post_source-source-radio" 
							<?= isset($post_source_meta['source']) && $post_source_meta['source'] === 'reprint' ? 'checked' : null;?> 
							target="theme_custom_post_source-input-reprint" 
						>
						<?= ___('Reprint');?>
					</label>
					<div class="row theme_custom_post_source-inputs" id="theme_custom_post_source-input-reprint" >
						<div class="g-tablet-1-2">
							<div class="input-group">
								<label class="addon" for="theme_custom_post_source-reprint-url">
									<i class="fa fa-link"></i>
								</label>
								<input 
									type="url" 
									class="form-control" 
									name="theme_custom_post_source[reprint][url]" 
									id="theme_custom_post_source-reprint-url" 
									placeholder="<?= ___('The source of work URL, includes http://');?>" 
									title="<?= ___('The source of work URL, includes http://');?>"
									value="<?= isset($post_source_meta['reprint']['url']) ? $post_source_meta['reprint']['url'] : null;?>"
								>
							</div>
						</div>
						<div class="g-tablet-1-2">
							<div class="input-group">
								<label class="addon" for="theme_custom_post_source-reprint-author">
									<i class="fa fa-user"></i>
								</label>
								<input 
									type="text" 
									class="form-control" 
									name="theme_custom_post_source[reprint][author]" 
									id="theme_custom_post_source-reprint-author" 
									placeholder="<?= ___('Author');?>" 
									title="<?= ___('Author');?>"
									value="<?= isset($post_source_meta['reprint']['author']) ? esc_attr($post_source_meta['reprint']['author']) : null;?>"
								>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } /** end theme_custom_post_source */ ?>

		
		<!-- submit -->
		<div class="form-group">
			<div class="g-tablet-1-6">
				<a href="javascript:;" id="ctb-quick-save" class="btn btn-block btn-default btn-lg" title="<?= ___('The post data will be saved automatically per minute in your current borwser, you can also save it now manually.');?>">
					<i class="fa fa-save"></i> <?= ___('Quick save');?>
				</a>
			</div>
			<div class="g-tablet-5-6">
				
				<button type="submit" class="btn btn-lg btn-success btn-block submit" data-loading-text="<?= ___('Sending, please wait...');?>">
					<i class="fa fa-check"></i> 
					<?= $edit ? ___('Update') : ___('Submit');?>
				</button>
				<input type="hidden" id="ctb-post-id" name="post-id" value="<?= $edit ? $post->ID : 0;?>">
				<input type="hidden" name="type" value="post">
			</div>
		</div>
	</form>
	<?php
	wp_reset_postdata();
}

?>
<div class="panel">
	<div class="content">
		<?php
		if(isset($_GET['post']) && is_numeric($_GET['post'])){
			post_form($_GET['post']);
		}else{
			post_form();
		}
		?>
	</div>
</div>