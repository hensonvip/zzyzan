<?php
/**
 * Template name: Sign
 */
$tabs = theme_custom_sign::get_tabs();
$tab_active = get_query_var('tab');

if(!isset($tabs[$tab_active]))
	$tab_active = 'login';
	
$redirect = get_query_var('redirect');

$avatar = theme_custom_sign::get_options('avatar-url');

/** log in html */
$i_have_account_html = function(){
	?>
	<div class="g-phone-1-2">
		<a class="btn btn-default btn-block" href="<?= theme_custom_sign::get_tabs('login')['url'];?>#main">
			<i class="fa fa-<?= theme_custom_sign::get_tabs('login')['icon'];?> fa-fw"></i>
			<?= ___('I have account');?>
		</a>
	</div>
	<?php
};
$register_new_account_html = function(){
	?>
	<div class="g-phone-1-2">
		<a class="btn btn-default btn-block" href="<?= theme_custom_sign::get_tabs('register')['url'];?>#main">
			<i class="fa fa-<?= theme_custom_sign::get_tabs('register')['icon'];?> fa-fw"></i>
			<?= ___('Register new account');?>
		</a>
	</div>
	<?php
};
$forgot_my_pwd_html = function(){
	?>
	<div class="g-phone-1-2">
		<a class="btn btn-default btn-block" href="<?= esc_url(theme_custom_sign::get_tabs('recover')['url']);?>#main">
			<i class="fa fa-<?= theme_custom_sign::get_tabs('recover')['icon'];?> fa-fw"></i>
			<?= ___('Forgot password?');?>
		</a>
	</div>
	<?php
};
/**
 * open sign
 */
$open_sign_html = function(){
	if(!class_exists('theme_open_sign'))
		return;
	?>
	<div class="open-login row">
		<div class="g-phone-1-2">
			<a href="<?= theme_open_sign::get_login_url('qq');?>" class="btn btn-primary btn-block">
				<i class="fa fa-qq fa-fw"></i> 
				<?= ___('Login from QQ');?>
			</a>
		</div>
		<div class="g-phone-1-2">
			<a href="javascript:;" class="btn btn-danger btn-block" disabled>
				<del>
					<i class="fa fa-weibo fa-fw"></i> 
					<?= ___('Login from Weibo');?>
				</del>
			</a>
		</div>
	</div>
	<?php
};
?>
<?php get_header();?>
<div class="g">
	<div id="main" class="main">
		<?php
		switch($tab_active){
			/**
			 * register
			 */
			case 'register':
			?>
<div class="<?= $avatar ? 'has-avatar' : null;?> panel mx-sign-panel mx-sign-panel-<?= $tab_active;?>">
	<div class="heading">
		
		<?php if(!empty($avatar)){ ?>
			<img class="avatar" src="<?= esc_url($avatar);?>" alt="avatar">
		<?php } ?>
		
		<h3><?= ___('Account register');?></h3>
	</div>
	<div class="content">
		<?php if(theme_cache::get_option('users_can_register')){ ?>
			<form action="javascript:;" id="fm-sign-register" >
				<div class="form-group">
					<div class="input-group">
						<label for="sign-nickname" class="addon"><i class="fa fa-user fa-fw"></i></label>
						<input name="user[nickname]" type="text" class="form-control" id="sign-nickname" placeholder="<?= sprintf(___('Your nickname, at least %d length'),theme_custom_sign::$min_display_name_length);?>" title="<?= sprintf(___('Please type nickname, at least %d length'),theme_custom_sign::$min_display_name_length);?>" required minlength="<?= theme_custom_sign::$min_display_name_length;?>" tabindex="1" autofocus >
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<label for="sign-email" class="addon"><i class="fa fa-at fa-fw"></i></label>
						<input name="user[email]" type="email" class="form-control" id="sign-email" placeholder="<?= ___('Please type email');?>" title="<?= ___('Please type email');?>" required tabindex="1">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<label for="sign-pwd" class="addon"><i class="fa fa-key fa-fw"></i></label>
						<input name="user[pwd]" type="password" class="form-control" id="sign-pwd" placeholder="<?= sprintf(___('Your password, at least %d length'),theme_custom_sign::$min_pwd_length);?>" title="<?= sprintf(___('Please type password, at least %d length'),theme_custom_sign::$min_pwd_length);?>" minlength="<?= theme_custom_sign::$min_pwd_length;?>" required tabindex="1">
					</div>
				</div>
				<div class="form-group form-group-submit">
					<button type="submit" class="btn btn-success btn-block btn-lg submit" data-loading-text="<?= ___('Processing, please wait...');?>" tabindex="1">
						<i class="fa fa-check"></i>
						<?= ___('Register &amp; Log-in');?>
					</button>
					<input type="hidden" name="type" value="register">
				</div>
				<div class="form-group text-center">
					<i class="fa fa-check-square-o"></i> 
					<?= sprintf(___('I am agree the %s.'),'<a href="' . theme_custom_sign::get_tos_url() . '" target="_blank">' . ___('TOS') . '</a>');?>
				</div>
			</form>
		<?php }else{ /** can not reigster */ ?>
			<div class="page-tip">
				<?= status_tip('info',___('Sorry, it is not the time, the site is temporarily closed registration.'));?>
			</div>
			<div class="page-tip">
				<a href="javascript:history.back();" class="btn btn-success btn-block"><i class="fa fa-arrow-left"></i> <?= ___('Back');?></a>
			</div>
		<?php } ?>
	</div><!-- /.content -->
<div class="form-group">
	<div class="row">
		<?php $i_have_account_html();?>
		<?php $forgot_my_pwd_html();?>
	</div>
</div><!-- /.main -->
<?php
/**
 * open sign
 */
// $open_sign_html();

				break;
			/**
			 * recover
			 */
			case 'recover':
				?>
<div class="panel mx-sign-panel mx-sign-panel-<?= $tab_active;?>">
	<div class="heading">
		<h3><?= ___('Recover password');?></h3>
	</div>

	<div class="content">
		<form action="javascript:;" id="fm-sign-recover">
			<div class="form-group"><?= ___('If you forgot your account password, you can recover your password by your account email. Please entry your account email, we will send a confirm email to it and reset your password.');?></div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-email" class="addon"><i class="fa fa-at fa-fw"></i></label>
					<input type="email" name="user[email]" id="sign-email" class="form-control" title="<?= ___('Please type email');?>" required tabindex="1" autofocus placeholder="<?= ___('Please type email');?>">
				</div>
			</div>
			<div class="form-group form-group-submit">
				<button type="submit" class="btn btn-success btn-block btn-lg submit" tabindex="1">
					<i class="fa fa-send"></i> 
					<?= ___('Send an email to confirm');?>
				</button>
				<input type="hidden" name="type" value="recover">
			</div>
		</form>
	</div><!-- /.content -->
</div><!-- /.panel -->
<div class="form-group">
	<div class="row">
		<?php $i_have_account_html();?>
		<?php $register_new_account_html();?>
	</div>
</div>
				<?php
				break;
			/** reset */
			case 'reset':
				$token = isset($_GET['token']) && is_string($_GET['token']) ? $_GET['token'] : null;
				$decode_token = theme_custom_sign::get_decode_token($token);
?>
<div class="panel mx-sign-panel mx-sign-panel-<?= $tab_active;?>">
	<div class="heading">
		<h3><?= ___('Reset my password');?></h3>
	</div>
	<div class="content">
		<?php if(!isset($decode_token['user_id']) || !isset($decode_token['user_email'])){ ?>
			<div class="page-tip">
				<?= status_tip('error',___('Sorry, the url is expired, please recover password again.'));?>
			</div>
			<div class="page-tip">
				<a href="<?= theme_custom_sign::get_tabs('recover')['url'];?>" class="btn btn-success btn-block"><i class="fa fa-history"></i> <?= ___('Recover password');?></a>
			</div>
		<?php }else{ ?>
			<p><?= sprintf(___('You are resetting %s password, please type your new password.'),'<strong>' . $decode_token['user_email'] . '</strong>');?></p>
			<form action="javascript:;" id="fm-sign-<?= $tab_active;?>">
				<div class="form-group">
					<div class="input-group">
						<label for="sign-pwd" class="addon"><i class="fa fa-key fa-fw"></i></label>
						<input type="password" name="user[pwd]" id="sign-pwd" class="form-control" title="<?= ___('Please type new password');?>" required tabindex="1" autofocus placeholder="<?= ___('Please type new password');?>">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<label for="sign-pwd-again" class="addon"><i class="fa fa-key fa-fw"></i></label>
						<input type="password" name="user[pwd-again]" id="sign-pwd-again" class="form-control" title="<?= ___('Retype new password');?>" required tabindex="1" placeholder="<?= ___('Retype new password');?>">
					</div>
				</div>
				<div class="form-group form-group-submit">
					<button type="submit" class="btn btn-success btn-block btn-lg submit" tabindex="1">
						<i class="fa fa-check"></i> 
						<?= ___('Reset');?>
					</button>
					<input type="hidden" name="user[token]" value="<?= isset($_GET['token']) && is_string($_GET['token']) ? $_GET['token'] : null;?>">
					<input type="hidden" name="type" value="reset">
				</div>
			</form>
		<?php } ?>
	</div>
</div>
<?php
				break;
			/**
			 * login
			 */
			case 'login':
			default:
				?>
<div class="<?= $avatar ? 'has-avatar' : null;?> panel mx-sign-panel mx-sign-panel-<?= $tab_active;?>">
	<div class="heading">

		<?php if(!empty($avatar)){ ?>
			<img class="avatar" src="<?= $avatar;?>" alt="avatar">
		<?php } ?>

		<h3>
			<?= ___('Account login');?>
		</h3>
	</div>
	<div class="content">
		<form action="javascript:;" id="fm-sign-login">
			<div class="form-group">
				<div class="input-group">
					<label for="sign-email" class="addon"><i class="fa fa-at fa-fw"></i></label>
					<input name="user[email]" type="email" class="form-control" id="sign-email" placeholder="<?= ___('Please type email');?>" title="<?= ___('Please type email');?>" required tabindex="1" autofocus>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-pwd" class="addon"><i class="fa fa-key fa-fw"></i></label>
					<input name="user[pwd]" type="password" class="form-control" id="sign-pwd" placeholder="<?= ___('Please type password');?>" title="<?= sprintf(___('Please type password, at least %d length'),theme_custom_sign::$min_pwd_length);?>" minlength="<?= theme_custom_sign::$min_pwd_length;?>" required tabindex="1">
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<label for="sign-remember" class="addon"><i class="fa fa-fw"></i></label>
					<label for="sign-remember">
						<input type="checkbox" name="user[remember]" id="sign-remember" value="1" checked tabindex="1" > <?= ___('Remember me');?>
					</label>
				</div>
			</div>
			<div class="form-group form-group-submit">
				<button type="submit" class="btn btn-lg btn-success btn-block submit" data-loading-text="<?= ___('Logging in, please wait...');?>" tabindex="1">
					<i class="fa fa-check"></i>
					<?= ___('Login');?>
				</button>
				<input type="hidden" name="type" value="login">
			</div>
		</form>
	</div><!-- /.content -->
</div><!-- /.panel -->
<div class="form-group">
	<div class="row">
		<?php $register_new_account_html();?>
		<?php $forgot_my_pwd_html();?>
	</div>
</div>
<?php
/**
 * open sign
 */
// $open_sign_html();

		} /** end switch */ ?>
		</div><!-- /.main.col-->
	</div><!-- /.row -->
</div>
<?php get_footer();?>