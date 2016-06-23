<div class="panel">
	<div class="content">
<form id="fm-change-password" class="user-form form-horizontal" method="post" action="javascript:;">
	<!-- current password -->
	<div class="form-group">
		<label class="control-label g-tablet-1-6">
			<label for="user-old-pwd"><?= ___('Current password');?></label>
		</label>
		<div class="g-tablet-5-6">
			<input id="user-old-pwd" name="user[old-pwd]" type="password" class="form-control" placeholder="<?= ___('Current password');?>" title="<?= ___('Type your current password');?>" required >
		</div>
	</div>
	<!-- new password -->
	<div class="form-group">
		<label class="control-label g-tablet-1-6">
			<label for="user-new-pwd-1"><?= ___('New password');?></label>
		</label>
		<div class="g-tablet-5-6">
			<input id="user-new-pwd-1" name="user[new-pwd-1]" type="password" class="form-control" placeholder="<?= ___('New password');?>" title="<?= ___('Type new password');?>" required >
		</div>
	</div>
	<div class="form-group">
		<label class="control-label g-tablet-1-6">
			<label for="user-new-pwd-2"><?= ___('Re-type new password');?></label>
		</label>
		<div class="g-tablet-5-6">
			<input id="user-new-pwd-2" name="user[new-pwd-2]" type="password" class="form-control" placeholder="<?= ___('Re-type new password');?>" title="<?= ___('Re-type new password');?>" required >
		</div>
	</div>
	<!-- submit -->
	<div class="form-group">
		<div class="g-tablet-1-6">&nbsp;</div>
		<div class="g-tablet-5-6">
			<div class="submit-tip"></div>
			<input type="hidden" name="type" value="pwd">
			<button type="submit" class="submit btn btn-success btn-block btn-lg" data-loading-text="<?= ___('Saving, please wait...');?>">
				<i class="fa fa-check"></i>
				<?= ___('Update password');?>
			</button>
		</div>
	</div>
</form>
	</div>
</div>