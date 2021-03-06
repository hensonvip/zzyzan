<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
require_once(dirname(__FILE__) . '/addons/custom-point/custom-point.php');
require_once(dirname(__FILE__) . '/addons/cache/cache.php');
require_once(dirname(__FILE__) . '/addons/custom-post-download-point/custom-post-download-point.php');
require_once(dirname(__FILE__) . '/addons/custom-post-storage/custom-post-storage.php');
$post_id = $_POST['post_id'];	//当前文章ID
$author_id = (int)$_POST['author_id'];	//作者ID
$current_user_id = (int)theme_cache::get_current_user_id(); // 当前登录用户ID
if (!$post_id || !is_numeric($post_id)) {
	$result = array(
		'status' => -1,
		'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-error"><i class="fa fa-times-circle fa-fw"></i>不要做坏事哦。</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
	);
	die(json_encode($result));
}
// 已登录
if (theme_cache::is_user_logged_in()) {
	// 判断是否有评论
	$comment_log = $wpdb->get_row("SELECT * FROM wp_comments where comment_post_ID={$post_id}");
	if (!$comment_log && $author_id !== $current_user_id){
		$result = array(
			'status' => 0,
			'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-error"><i class="fa fa-times-circle fa-fw"></i>抱歉，请评论后再下载。</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
		);
		die(json_encode($result));
	}
	// 文章的作者不用扣取积分
	if ($author_id === $current_user_id) {
		$result = array(
			'status' => 1,
			'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-check-circle fa-fw"></i>3秒后自动跳转到下载页面...</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
		);
		die(json_encode($result));
	}
	// 下载和下载积分按钮显示并且设置了下载积分才扣除
	if(class_exists('theme_custom_download_point') && theme_custom_download_point::is_enabled() && class_exists('theme_custom_storage') && theme_custom_storage::is_enabled()){
		$download_point_meta = theme_custom_download_point::get_post_meta($post_id);
		if($download_point_meta){
			$data = $wpdb->get_row("SELECT * FROM $wpdb->postmeta where meta_key='_theme_custom_download_point' and post_id={$post_id}");
			$download_point_arr = unserialize($data->meta_value);
			$download_point = (int)$download_point_arr['download_point'];	// 下载积分
			$total_point = (int)theme_custom_point::get_point($current_user_id);	// 总积分
			if ($total_point - $download_point < 0) {
				$result = array(
					'status' => 0,
					'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-error"><i class="fa fa-times-circle fa-fw"></i>抱歉，您的积分不足。</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
				);
				die(json_encode($result));
			} else {
				// 查询下载记录，是否下载过
				$download_log = $wpdb->get_row("SELECT * FROM wp_download_log where uid={$current_user_id} and post_id={$post_id}");

				// 有下载过，判断时间是否过期（3天），如果过期重新扣除积分
				if ($download_log) {
					// 过期
					if ((time() - $download_log->download_time) > 259200) {
						// 减少积分
						theme_custom_point::decr_user_points($current_user_id, $download_point);
						// 更新下载时间
						$wpdb->update('wp_download_log', array('download_time' => time()), array('id' => $download_log->id));
						$result = array(
							'status' => 1,
							'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-check-circle fa-fw"></i>离上次下载已超过3天，扣除您' . $download_point . '积分,3秒后自动跳转到下载页面...</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
						);
						die(json_encode($result));
					} else {
						// 没有过期
						$result = array(
							'status' => 1,
							'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-check-circle fa-fw"></i>3天内免积分下载，3秒后自动跳转到下载页面...</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
						);
						die(json_encode($result));
					}
				} else {
					// 减少积分
					theme_custom_point::decr_user_points($current_user_id, $download_point);
					// 将用户ID和文章ID写入数据库
					$wpdb->insert('wp_download_log', array('uid' => $current_user_id, 'post_id' => $post_id, 'download_time' => time()), array('%d','%d', '%d'));
					$result = array(
						'status' => 1,
						'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-check-circle fa-fw"></i>扣除您' . $download_point . '积分,3秒后自动跳转到下载页面...</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
					);
					die(json_encode($result));
				}
			}
		} else {
			$result = array(
				'status' => 1,
				'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-success"><i class="fa fa-check-circle fa-fw"></i>3秒后自动跳转到下载页面...</div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
			);
			die(json_encode($result));
		}
	}
} else {
	// 没有登录
	$result = array(
		'status' => -2,
		'msg' => '<div id="ajax-loading-container" class="success show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-error"><i class="fa fa-times-circle fa-fw"></i><a href="' . wp_login_url(theme_cache::get_permalink($post_id)) . '" title="' . ___('Go to log-in') . '">' . ___('Sorry, please log-in.') . '</a></div></div> <i class="btn-close fa fa-times fa-fw"></i></div>'
	);
	die(json_encode($result));
}
?>