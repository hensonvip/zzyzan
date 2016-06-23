<?php
global $current_user;
get_currentuserinfo();
?>
<div class="panel">
	<div class="content">
		<?php
		/**
		 * consume points to modify avatar
		 */
		$disabled = null;
		if(class_exists('theme_custom_point')){
			$consume_points = abs(theme_custom_point::get_point_value('save-avatar'));
			$user_points = theme_custom_point::get_point($current_user->ID);
			if($consume_points != 0){
				?>
				<div class="page-tip">
					<?php
					/**
					 * not enough points, can not modify
					 */
					if($consume_points > $user_points){
						$disabled = 'disabled';
						echo status_tip(
							'info',
							sprintf(
								___('You have %1$s %2$s, You need to collect %3$s %2$s to modify the avatar.'),
								'<strong>' . $user_points . '</strong>',
								theme_custom_point::get_point_name(),
								'<strong>' . ($consume_points - $user_points) . '</strong>'
							)
						);
					}else{
						echo status_tip(
							'info',
							sprintf(
								___('You have %1$s %2$s, modify avatar will consume %3$s %2$s.'),
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
<form id="fm-change-avatar" class="user-form form-horizontal" method="post" action="javascript:;">
	<fieldset <?= $disabled;?>>
		<legend class="label label-<?= $disabled ? 'default' : 'success';?>"><?= ___('Change my avatar');?></legend>
	<!-- current avatar -->
	<div class="form-group">
		<div class="control-label g-tablet-1-6">
			<?= ___('Current avatar');?>
		</div>
		<div class="g-tablet-5-6">
			<div class="current-avatar">
				<?= theme_cache::get_avatar($current_user->ID,100);?>
			</div>
		</div>
	</div>
	<!-- new avatar -->
	<div class="form-group">
		<div class="control-label g-tablet-1-6">
			<?= ___('New avatar');?>
		</div>
		<div class="g-tablet-5-6">
			<div class="row">
				<div class="g-tablet-1-2">
					<div id="cropper-container"></div>
				</div>
				<div class="g-tablet-1-2">
					<div id="avatar-preview"></div>
				</div>
			</div>
			

			<div class="submit-tip"></div>
			<textarea name="base64" id="avatar-base64" hidden></textarea>
			<input type="hidden" name="type" value="avatar">
			<button id="cropper-done-btn" type="submit" class="submit btn btn-success btn-block btn-lg" data-loading-text="<?= ___('Saving, please wait...');?>">
				<i class="fa fa-check"></i>
				<?= ___('Save my avatar');?>
			</button>
			
			<a href="javascript:;" id="new-avatar-btn" class="file-btn-container btn btn-default btn-block">
				<i class="fa fa-plus"></i>
				<?= ___('Upload a new avatar');?>
				<input type="file" id="file">
			</a>
		</div>
	</div>
	<!-- submit -->
	</fieldset>
</form>
	</div>
</div>