<?php
	require_once(dirname(__FILE__) . '/../../../wp-load.php');
	require_once(dirname(__FILE__) . '/addons/custom-point/custom-point.php');
	require_once(dirname(__FILE__) . '/addons/cache/cache.php');
	require_once(dirname(__FILE__) . '/addons/custom-post-download-point/custom-post-download-point.php');
	require_once(dirname(__FILE__) . '/addons/custom-post-storage/custom-post-storage.php');
	$post_id = $_POST['post_id'];
	if (!$post_id || !is_numeric($post_id)) {
		$result = array(
			'status' => -1,
			'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-times-circle fa-fw"></i>不要做坏事哦。</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
		);
	}
	if (theme_cache::is_user_logged_in()) {
		// 下载和下载积分按钮显示并且设置了下载积分才扣除
		if(class_exists('theme_custom_download_point') && theme_custom_download_point::is_enabled() && class_exists('theme_custom_storage') && theme_custom_storage::is_enabled()){
			$download_point_meta = theme_custom_download_point::get_post_meta($post_id);
			if($download_point_meta){
				$data = $wpdb->get_row("SELECT * FROM $wpdb->postmeta where meta_key='_theme_custom_download_point' and post_id={$post_id}");
				$download_point_arr = unserialize($data->meta_value);
				$download_point = (int)$download_point_arr['download_point'];	// 下载积分
				$current_user_id = theme_cache::get_current_user_id(); // 当前登录用户ID
				$total_point = number_format(theme_custom_point::get_point($current_user_id));	// 总积分
				if ($total_point - $download_point < 0) {
					$result = array(
						'status' => 0,
						'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-times-circle fa-fw"></i>抱歉，您的' . ___('Cat-paw') . '不足。</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
					);
				} else {
					// 减少积分
					theme_custom_point::decr_user_points($current_user_id, $download_point);
					$result = array(
						'status' => 1,
						'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-check-circle fa-fw"></i>成功扣除' . $download_point . '个' . ___('Cat-paw') . ',3秒后自动跳转到下载页面...</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
					);
				}
			} else {
				$result = array(
					'status' => 1,
					'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-check-circle fa-fw"></i>3秒后自动跳转到下载页面...</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
				);
			}
		}
	} else {
		$result = array(
			'status' => -2,
			'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-times-circle fa-fw"></i><a href="' . wp_login_url(theme_cache::get_permalink($post_id)) . '" title="' . ___('Go to log-in') . '">' . ___('Sorry, please log-in.') . '</a></div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
		);
	}
	echo json_encode($result);
?>