<?php
global $current_user;
get_currentuserinfo();
?>
<div class="panel">
	<div class="content">
		<?php
		/**
		 * consume points to modify settings
		 */
		$disabled = null;
		if(class_exists('theme_custom_point')){
			$consume_points = abs(theme_custom_point::get_point_value('save-settings'));
			$user_points = theme_custom_point::get_point($current_user->ID);

			if($consume_points != 0){
				?>
				<div class="page-tip">
					<?php
					/**
					 * not enough points, can not modify
					 */
					if($user_points - $consume_points < 0){
						$disabled = 'disabled';
						echo status_tip(
							'info',
							sprintf(
								___('You have %1$s %2$s, You need to collect %3$s %2$s to modify the settings.'),
								'<strong>' . $user_points . '</strong>',
								theme_custom_point::get_point_name(),
								'<strong>' . ($consume_points - $user_points) . '</strong>'
							)
						);
					}else{
						echo status_tip(
							'info',
							sprintf(
								___('You have %1$s %2$s, modify settings will consume %3$s %2$s.'),
								'<strong>' . $user_points . '</strong>',
								theme_custom_point::get_point_name(),
								'<strong>' . $consume_points . '</strong>'
							)
						);
					}
					?>
				</div>
				<?php
			}
		}
		?>
		
<form id="fm-my-settings" class="user-form form-horizontal" method="post" action="javascript:;">
	<!-- avatar -->
	<div class="form-group">
		<div class="control-label g-tablet-1-6">
			<?php 
			$avatar = theme_cache::get_avatar($current_user->ID,100);
			?>
			<a href="<?= theme_cache::get_avatar_url($current_user->ID);?>" target="_blank" title="<?= ___('Views source image');?>"><?= $avatar;?></a>
		</div>
		<div class="g-tablet-5-6">
			<div class="form-control-static">
				<p><?= ___('My avatar');?></p>
				<p><a href="<?= theme_custom_user_settings::get_tabs('avatar')['url'];?>" class="btn btn-success btn-xs"><?= ___('Change avatar');?> <i class="fa fa-external-link"></i></a></p>
			</div>
		</div>
	</div>
	<fieldset <?= $disabled;?>>
	<legend><?= ___('Edit my profile');?></legend>
	<!-- uid -->
	<div class="form-group">
		<div class="control-label g-tablet-1-6">
			<abbr title="<?= ___('Unique identifier');?>">
				<?= ___('UID');?>
			</abbr>
		</div>
		<div class="g-tablet-5-6"><p class="form-control-static"><strong>
			<a href="<?= theme_cache::get_author_posts_url($current_user->ID);?>"><?= $current_user->user_nicename;?></a>
			</strong></p></div>
	</div>
	<!-- nickname -->
	<div class="form-group">
		<label for="my-settings-nickname" class="control-label g-tablet-1-6">
			<i class="fa fa-user"></i>
			<?= ___('Nickname');?>
		</label>
		<div class="g-tablet-5-6">
			<input name="user[nickname]" type="text" class="form-control" id="my-settings-nickname" placeholder="<?= ___('Please type nickname (required)');?>" title="<?= ___('Please type nickname');?>" value="<?= esc_attr($current_user->display_name);?>" required tabindex="1" >
		</div>
	</div>
	<!-- url -->
	<div class="form-group">
		<label for="my-settings-url" class="control-label g-tablet-1-6">
			<i class="fa fa-link"></i>
			<?= ___('Website / Blog');?>
		</label>
		<div class="g-tablet-5-6">
			<input name="user[url]" type="url" class="form-control" id="my-settings-url" placeholder="<?= ___('Your blog url (include http://)');?>" title="<?= ___('Please type your blog url');?>" value="<?= esc_url($current_user->user_url);?>" tabindex="1" >
		</div>
	</div>
	<!-- description -->
	<div class="form-group">
		<label for="my-settings-des" class="control-label g-tablet-1-6">
			<i class="fa fa-newspaper-o"></i>
			<?= ___('Description');?>
		</label>
		<div class="g-tablet-5-6">
			<textarea name="user[description]" class="form-control" id="my-settings-des" placeholder="<?= ___('Introduce yourself to everyone');?>" tabindex="1"><?= esc_attr(get_user_meta(theme_cache::get_current_user_id(),'description',true));?></textarea>
		</div>
	</div>
	<!-- submit -->
	<div class="form-group">
		<div class="g-tablet-1-6">&nbsp;</div>
		<div class="g-tablet-5-6">
			<div class="submit-tip"></div>
			<input type="hidden" name="type" value="settings">
			<button type="submit" class="submit btn btn-success btn-block btn-lg" data-loading-text="<?= ___('Saving, please wait...');?>">
				<i class="fa fa-check"></i>
				<?= ___('Save my settings');?>
			</button>
		</div>
	</div>
	</fieldset>
</form>
	</div>
</div>