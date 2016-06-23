<?php
global $current_user;
get_currentuserinfo();

if(theme_custom_point::get_point_img_url()){
	$point_icon = '<img src="' . theme_custom_point::get_point_img_url() . '" alt="icon" width="15" height="15">';
}else{
	$point_icon = '<i class="fa fa-diamond fa-fw"></i>';
}
?>
<div class="panel">
	<div class="content">
		<?= theme_custom_point_bomb::get_des();?>
		<div class="page-tip" id="fm-bomb-loading"><?= status_tip('loading',___('Loading, please wait...'));?></div>
		<form class="form-horizontal" action="post" id="fm-bomb" method="javascript:;">
			<div class="form-group">
				<div class="g-tablet-2-5">
					<div class="bomb-area bomb-area-attacker">
						<p>
							<img id="bomb-attacker-avatar" src="<?= theme_cache::get_avatar_url($current_user->ID);?>" alt="<?= ___('Avatar');?>" class="avatar" width="100" height="100" >
						</p>
						<p class="bomb-area-meta">
							<?= $point_icon;?>
							<strong id="bomb-attacker-points">
								<?= theme_custom_point::get_point($current_user->ID);?>
							</strong>
						</p>
						<p class="bomb-area-meta">
							<?= esc_html($current_user->display_name);?>
						</p>
					</div>					
				</div><!-- .g-tablet-2-5 -->
			
				<div class="g-tablet-1-5 bomb-area-points">
					<h4><?= ___('Bomb number');?></h4>
					<?php
					$points = theme_custom_point_bomb::get_point_values();
					
					$i = 0;
					foreach($points as $point){
						++$i;
						?>
						<label for="bomb-point-<?= $point;?>" class="label label-default">
							<?= $point_icon;?>
							<?= $point;?>
							<input type="radio" name="points" id="bomb-point-<?= $point;?>" class="bomb-points" value="<?= $point;?>" <?= $i === 1 ? 'checked' : null;?> hidden>
						</label>
					<?php } ?>
				</div>
				<div class="g-tablet-2-5">
					<div class="bomb-area bomb-area-target">
						<p>
							<img id="bomb-target-avatar" src="<?= theme_functions::$avatar_placeholder;?>" alt="<?= ___('Avatar');?>" class="avatar" width="100" height="100" >
						</p>
						<p class="bomb-area-meta">
							<?= $point_icon;?>
							<strong id="bomb-target-points">
								-
							</strong>
						</p>
						<p class="bomb-area-meta" id="bomb-target-name">
							-<?= ___('Target name');?>-
						</p>
						<p>
							<span>
								<input id="bomb-target" type="text" name="target" class="form-control" placeholder="<?= sprintf(___('Target UID, e.g. %d'),105844);?>" value="<?= isset($_GET['target']) && is_numeric($_GET['target']) ? (int)$_GET['target'] : null;?>" required >
							</span>
						</p>
					</div>			
				</div><!-- .row -->
			</div><!-- .form-group -->
			<div class="form-group">
				<div class="g-desktop-1-1">
					<input type="text" class="form-control text-center" name="says" id="bomb-says" placeholder="<?= ___('Say something to target?');?>" maxlength="30">
				</div>
			</div>
			<div class="form-group">
				<div class="g-desktop-1-1">
					<input type="hidden" name="type" value="bomb">
					<button class="submit btn btn-success btn-lg btn-block" type="submit" data-loading-tx="<?= ___('Bombing...');?>" disabled>
						<i class="fa fa-bomb"></i> 
						<?= ___('Bomb!');?>
					</button>
				</div>
			</div>
		</form>
	</div>
</div><!-- /.content -->