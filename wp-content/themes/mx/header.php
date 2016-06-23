<!DOCTYPE html><html lang="<?= theme_cache::get_bloginfo('language');?>"><head>
<meta charset="<?= theme_cache::get_bloginfo('charset');?>">
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta http-equiv="Cache-Control" content="no-transform"><![endif]-->
<meta name="renderer" content="webkit">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
<meta name="author" content="INN STUDIO">
<meta name="theme-color" content="#FF4081">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?= theme_cache::get_bloginfo('pingback_url');?>">
<?php wp_head();?></head>
<body <?php body_class(); ?>>
<?php
/** 
 * menu menu-mobile
 */
if(wp_is_mobile()){
	?>
	<div class="nav-slide menu-mobile header-nav-slide">
		<a href="<?= theme_cache::home_url();?>" class="nav-slide-header">
			<?= theme_cache::get_bloginfo('name');?>
		</a>
		<?php
		if(theme_cache::is_user_logged_in()){
			theme_cache::wp_nav_menu([
				'theme_location'    => 'menu-mobile-login',
				'container'         => 'nav',
				'container_class'   => '',
				'menu_class'        => 'menu',
				'menu_id' 			=> 'menu-mobile',
				'fallback_cb'       => 'custom_navwalker::fallback',
				'walker'            => new custom_navwalker
			]);
		}else{
			theme_cache::wp_nav_menu([
				'theme_location'    => 'menu-mobile',
				'container'         => 'nav',
				'container_class'   => '',
				'menu_class'        => 'menu',
				'menu_id' 			=> 'menu-mobile',
				'fallback_cb'       => 'custom_navwalker::fallback',
				'walker'            => new custom_navwalker
			]);
		}	
		?>
	</div>
	<?php
}
/**
 * account menu
 */
if(wp_is_mobile() && theme_cache::is_user_logged_in()){
	$active_tab = get_query_var('tab');
	if(!$active_tab)
		$active_tab = 'dashboard';
	?>
	<div class="nav-slide header-nav-account-menu">
		<a href="<?= theme_cache::get_author_posts_url(theme_cache::get_current_user_id());?>" class="nav-slide-header">
			<img src="<?= theme_cache::get_avatar_url(theme_cache::get_current_user_id());?>" width="48" height="48" alt="avatar" class="avatar">
			<span class="author-name"><?= theme_cache::get_the_author_meta('display_name',theme_cache::get_current_user_id());?></span>
		</a>
		<ul class="menu">
			<?php
			$account_navs = apply_filters('account_navs',[]);
			if(!empty($account_navs)){
				foreach($account_navs as $k => $v){
					$active_class = $k === $active_tab ? ' active ' : null;
					?>
					<li class="<?= theme_custom_account::is_page() ? $active_class : null;?>"><?= $v;?></li>
					<?php
				}
			}
			?>
			<li><a href="<?= wp_logout_url(get_current_url());?>"><i class="fa fa-sign-out fa-fw"></i> <?= ___('Log-out');?></a></li>
		</ul>
	</div>
<?php } ?>

<div class="nav-main top">
	<div class="g">
		<?php if(wp_is_mobile()){ ?>
			<a href="javascript:;" class="navicon toggle fa fa-navicon fa-fw" data-mobile-target=".menu-mobile" data-icon-active="fa-arrow-left" data-icon-original="fa-navicon"></a>
		<?php } ?>
		<?php
		/** 
		 * banner
		 */
		$header_img = get_header_image();
		if((bool)$header_img){ ?>
		<a class="logo" href="<?= theme_cache::home_url();?>" title="<?= theme_cache::get_bloginfo('name');?> - <?= theme_cache::get_bloginfo('description');?>">
			<img src="<?= $header_img; ?>" alt="<?= theme_cache::get_bloginfo('name');?>" width="100" height="40">
			<?php if(display_header_text()){ ?>
				<h1 hidden><?= theme_cache::get_bloginfo('name');?></h1>
				<span hidden><?= theme_cache::get_bloginfo('description');?></span>
			<?php } ?>
		</a>
		<?php } ?>

		
		<?php
		/** 
		 * menu-header
		 */
		if(!wp_is_mobile()){
			if(theme_cache::is_user_logged_in()){
				theme_cache::wp_nav_menu([
					'theme_location'    => 'menu-header-login',
					'container'         => 'nav',
					'container_class'   => 'menu-header',
					'menu_class'        => 'menu',
					'menu_id' 			=> 'menu-header',
					'fallback_cb'       => 'custom_navwalker::fallback',
					'walker'            => new custom_navwalker
				]);
			}else{
				theme_cache::wp_nav_menu([
					'theme_location'    => 'menu-header',
					'container'         => 'nav',
					'container_class'   => 'menu-header',
					'menu_class'        => 'menu',
					'menu_id' 			=> 'menu-header',
					'fallback_cb'       => 'custom_navwalker::fallback',
					'walker'            => new custom_navwalker
				]);
			}
		}
		?>
		
		<div class="tools">
			<!-- account btn -->
			<?php if(theme_cache::is_user_logged_in()){ ?>
				<?php if(wp_is_mobile()){ ?>
					<a class="tool tool-avatar" href="javascript:;" data-mobile-target=".header-nav-account-menu" >
						<img class="avatar" width="32" height="32" src="<?= theme_cache::get_avatar_url(theme_cache::get_current_user_id());?>" alt="avatar">
					</a>
				<?php }else{ ?>
					<div class="tool tool-me">
						<a href="<?= theme_cache::get_author_posts_url(theme_cache::get_current_user_id());?>">
							<img class="avatar" width="32" height="32" src="<?= theme_cache::get_avatar_url(theme_cache::get_current_user_id());?>" alt="avatar">&nbsp; <i class="fa fa-caret-down"></i>
						</a>
						<div class="box">
							<!-- points -->
							<div class="box-points">
								<?php if(class_exists('theme_custom_point')){ ?>
									<a href="<?= theme_custom_user_settings::get_tabs('history')['url'];?>">
										<?php if(theme_custom_point::get_point_img_url()){ ?>
											<img src="<?= theme_custom_point::get_point_img_url();?>" alt="" width="16" height="16">
										<?php }else{ ?>
											<i class="fa fa-diamond fa-fw"></i> 
										<?php } ?>
										<?= number_format(theme_custom_point::get_point(theme_cache::get_current_user_id()));?> 
										<?= theme_custom_point::get_point_name();?>
									</a>
								<?php } ?>
							</div>
							<ul>
								<?php
								$account_navs = apply_filters('account_navs',[]);
								if(!empty($account_navs)){
									$active_tab = get_query_var('tab');
									foreach($account_navs as $k => $v){
										$active_class = $k === $active_tab ? ' active ' : null;
										?>
										<li class="<?= theme_custom_account::is_page() ? $active_class : null;?>"><?= $v;?></li>
										<?php
									}
								}
								?>
								<li><a href="<?= wp_logout_url(get_current_url());?>"><i class="fa fa-sign-out fa-fw"></i> <?= ___('Log-out');?></a></li>
							</ul>
						</div>
					</div>
					

					<!-- notification -->
					<?php 
					if(class_exists('theme_notification')){
						$unread = theme_notification::get_count([
							'type' => 'unread'
						]);
						if($unread > 0){ 
							?>
							<a href="<?= theme_notification::get_tabs('notifications')['url'];?>" class="tool tool-notification " title="<?= ___('Your have new notification');?>">
								<i class="fa fa-bell fa-fw fa-spin"></i> 
							</a>
						<?php } ?>
					<?php } ?>

					<!-- pm -->
					<?php 
					if(class_exists('theme_custom_pm') && !theme_custom_pm::is_page() && theme_custom_pm::get_unread_count(theme_cache::get_current_user_id()) > 0){ 
						?>
						<a href="<?= theme_custom_pm::get_tabs('pm')['url'];?>" class="tool tool-pm" title="<?= ___('You have new P.M.');?>">
							<i class="fa fa-<?= theme_custom_pm::get_tabs('pm')['icon'];?> fa-fw fa-spin"></i>
						</a>
					<?php } ?>
				<?php }/** end if mobile */ ?>
			<?php }else{ ?>
				<a class="tool-login tool mx-account-btn" href="<?= esc_url(wp_login_url(get_current_url()));?>">
					<?= ___('Log-in');?>
				</a>
			<?php } /** end if login */?>
			
			<!-- search btn -->
			<a 
				class="tool search fa fa-search fa-fw fa-2x" 
				href="javascript:;" 
				data-toggle-target="#fm-search" 
				data-focus-target="#fm-search-s" 
				data-icon-active="fa-arrow-down" 
				data-icon-original="fa-search" 
				title="<?= ___('Search');?>" 
			></a>

		</div><!-- /.tools -->
	 
		<!-- search form -->
		<form id="fm-search" action="<?= theme_cache::home_url();?>/" data-focus-target="#fm-search-s">
			<input id="fm-search-s" name="s" class="form-control" placeholder="<?= ___('Please input search keyword');?>" value="<?= esc_attr(get_search_query())?>" type="search" required>
	    </form>
	</div><!--  /.g -->
</div><!-- /.nav-main -->
<div class="nav-main-placeholder"></div>

<?php
/**
 * ad
 */
if(!theme_cache::is_home() && class_exists('theme_adbox') && !empty(theme_adbox::display_frontend('below-header-menu'))){
	?>
	<div class="g"><div class="ad-container ad-below-header-menu"><?= theme_adbox::display_frontend('below-header-menu');?></div></div>
	<?php
}
?>