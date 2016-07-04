<?php
/**
 * @version 2.0.0
 */
class theme_custom_storage{
	public static $page_slug = 'storage-download';
	public static $post_meta_key = array(
		'key' => '_theme_custom_storage'
	);
	public static function init(){
		add_action('init', __CLASS__ . '::page_create');
		add_action('add_meta_boxes', __CLASS__ . '::meta_box_add');
		add_action('save_post_post', __CLASS__ . '::meta_box_save');

		add_action('template_redirect', __CLASS__ . '::template_redirect');
		
		//add_shortcode('post-stroage-download', __CLASS__ . '::add_shortcode');
		
		add_filter('wp_title', __CLASS__ . '::wp_title',10,2);	

		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_filter('theme_options_default', __CLASS__ . '::options_default');

		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;

		$post = self::get_decode_post();
		if($post)
			return theme_cache::get_the_title($post->ID) . $sep . ___('storage download') . $sep . theme_cache::get_bloginfo('name');
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1;
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-cloud-download"></i> <?= ___('Storage settings');?></legend>
			<p class="description"><?= ___('You can edit storage types here. They will display in contribution page.');?></p>
			<table class="form-table">
				<tbody>
				<tr>
					<th><label for="<?= __CLASS__;?>-enabled"><?= ___('Enable or not?');?></label></th>
					<td>
						<select name="<?= __CLASS__;?>[enabled]" id="<?= __CLASS__;?>-enabled" class="widefat">
							<?php the_option_list(-1,___('Disable'),self::get_options('enabled'));?>
							<?php the_option_list(1,___('Enable'),self::get_options('enabled'));?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="<?= __CLASS__;?>-enabled-display-name"><?= ___('Enable download item name?');?></label></th>
					<td>
						<select name="<?= __CLASS__;?>[enabled-display-name]" id="<?= __CLASS__;?>-enabled-display-name" class="widefat">
							<?php the_option_list(-1,___('Disable'),self::get_options('enabled-display-name'));?>
							<?php the_option_list(1,___('Enable'),self::get_options('enabled-display-name'));?>
						</select>
					</td>
				</tr>
				<tr>
					<th><?= ___('Control');?></th>
					<td>
						<a class="button button-primary" target="_blank" href="<?= theme_features::get_process_url([
							'action' => __CLASS__,
							'type' => 'meta-convert',
						]);?>"><i class="fa fa-exchange"></i> <?= ___('Convert to new version data');?></a>
						<span class="description"><?= ___('This operation only needs to be performed once if you are upgrade from a old version.');?></span> 
					</td>
				</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'enabled' => 1,
			'enabled-display-name' => -1,
		];
		return $opts;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
		if($key){
			if(isset($caches[$key])){
				return $caches[$key];
			}else{
				$caches[$key] = isset(self::options_default()[__CLASS__][$key]) ? self::options_default()[__CLASS__][$key] : false;
				return $caches[$key];
			}
		}
		return $caches;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function template_redirect(){
		if(self::is_page() && !self::get_decode_post()){
			wp_die(
				___('Sorry, this URL is invaild.'),
				___('Error'),
				[
					'response' => 403,
					'back_link' => true,
				]
			);
		}
		return;
	}
	public static function display_frontend_contribution($post_id){
		if(!self::is_enabled())
			return false;

		?>
		<div id="<?= __CLASS__;?>-container" data-tpl="<?= esc_attr(self::get_tpl_frontend_contribution());?>">
			<?php
			if(!$post_id){
				echo self::get_tpl_frontend_contribution(0);
			}else{
				$meta = array_filter((array)self::get_post_meta($post_id));
				if(!$meta){
					echo self::get_tpl_frontend_contribution(0,$meta);
				}else{
					foreach($meta as $k => $v){
						echo self::get_tpl_frontend_contribution($k,$meta);
					}
				}
			}
			?>
		</div>
		<div id="<?= __CLASS__;?>-control">
			<a href="javascript:;" id="<?= __CLASS__;?>-add" class="add btn btn-block btn-primary"><i class="fa fa-plus"></i> <?= ___('Add new link');?></a>
		</div>
		<?php
	}
	public static function get_tpl_frontend_contribution($placeholder = '%placeholder%', array $meta = []){

		$name = isset($meta[$placeholder]['name']) ? esc_attr($meta[$placeholder]['name']) : null;
		
		$storage_url = isset($meta[$placeholder]['url']) ? esc_attr($meta[$placeholder]['url']) : null;
		
		$storage_download_pwd = isset($meta[$placeholder]['download-pwd']) ? esc_attr($meta[$placeholder]['download-pwd']) : null;
		
		$storage_extract_pwd = isset($meta[$placeholder]['extract-pwd']) ? esc_attr($meta[$placeholder]['extract-pwd']) : null;

		$class_lg = 'g-tablet-2-4';
		$class_sm = 'g-tablet-1-4';
		ob_start();
		
		?>
		<div class="<?= __CLASS__;?>-item item" id="<?= __CLASS__;?>-item-<?= $placeholder;?>" data-placeholder="<?= $placeholder;?>">
			<a href="javascript:;" class="del" title="<?= ___('Delete this item');?>" data-target="<?= __CLASS__;?>-item-<?= $placeholder;?>"><i class="fa fa-times"></i></a>
			<div class="row">
				<?php 
				
				if(self::is_enabled_display_name()){ 
					$class = 'g-tablet-1-4'
					?>
					<div class="g-tablet-1-5">
						<input 
							type="text" 
							name="<?= __CLASS__;?>[<?= $placeholder;?>][name]" 
							id="<?= __CLASS__;?>-<?= $placeholder;?>-name" 
							class="form-control" 
							placeholder="<?= ___('Name (optional)');?>" 
							title="<?= ___('Name (optional)');?>" 
							value="<?= $name;?>" 
						>
					</div>
				<?php } ?>
				<div class="<?= self::is_enabled_display_name() ? 'g-tablet-2-5' : 'g-tablet-3-5';?>">
					<input 
						type="text" 
						class="form-control" 
						name="<?= __CLASS__;?>[<?= $placeholder;?>][url]" 
						id="<?= __CLASS__;?>-<?= $placeholder;?>-url" 
						title="<?= ___('Download page URL (include http://)');?>" 
						placeholder="<?= ___('Download page URL (include http://)');?>" 
						value="<?= $storage_url;?>" 
					>
				</div>
				<div class="g-tablet-1-5">
					<!-- <div class="input-group"> -->
						<!-- <label class="addon" for="<?= __CLASS__;?>-download-pwd"><i class="fa fa-key fa-fw"></i></label> -->
						<input 
							type="text" 
							class="form-control" 
							name="<?= __CLASS__;?>[<?= $placeholder;?>][download-pwd]" 
							id="<?= __CLASS__;?>-<?= $placeholder;?>-download-pwd" 
							title="<?= ___('Download password (optional)');?>" 
							placeholder="<?= ___('Download password (optional)');?>" 
							value="<?= $storage_download_pwd;?>" 
						>
					<!-- </div> -->
				</div>
				<div class="g-tablet-1-5">
					<!-- <div class="input-group"> -->
						<!-- <label class="addon" for="<?= __CLASS__;?>-url"><i class="fa fa-unlock fa-fw"></i></label> -->
						<input 
							type="text" 
							class="form-control" 
							name="<?= __CLASS__;?>[<?= $placeholder;?>][extract-pwd]" 
							id="<?= __CLASS__;?>-<?= $placeholder;?>-extract-pwd" 
							title="<?= ___('Extract password (optional)');?>" 
							placeholder="<?= ___('Extract password (optional)');?>" 
							value="<?= $storage_extract_pwd;?>" 
						>
					<!-- </div> -->
				</div>
			</div><!-- /.row -->
		</div><!-- /.item -->
		<?php
		$html = html_minify(ob_get_contents());
		ob_end_clean();
		return $html;
	}
	public static function get_post_meta($post_id){
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];
			
		$caches[$post_id] = array_filter((array)get_post_meta($post_id,self::$post_meta_key['key'],true));

		return $caches[$post_id];
		
	}
	public static function meta_box_add(){
		$screens = array( 'post' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				__CLASS__,
				___('File storage'),
				__CLASS__ . '::meta_box_display',
				$screen,
				'side'
			);
		}
	}
	public static function meta_box_save($post_id){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			return;
			
		if(!isset($_POST[__CLASS__])){
			// delete_post_meta($post_id,self::$post_meta_key['key']);
			return;
		}
			
		$new_meta = array_filter((array)$_POST[__CLASS__]);
			
		$old_meta = array_filter((array)self::get_post_meta($post_id));
		
		self::post_save($post_id, $old_meta, $new_meta);
		
	}
	public static function post_save($post_id, $old_meta = [], $new_meta = []){
		/** nothing to modify */
		if($old_meta == $new_meta)
			return;
			
		/** if empty, delete */
		if(!$new_meta){
			delete_post_meta($post_id,self::$post_meta_key['key']);
			return;
		}
		$delete = false;

		/** check */
		if(is_null_array($new_meta)){
			delete_post_meta($post_id,self::$post_meta_key['key']);
			return;
		}
		
		/** update */
		update_post_meta($post_id, self::$post_meta_key['key'],$new_meta);
	}
	public static function process(){
		theme_features::check_referer();
		if(!theme_cache::current_user_can('manage_options'))
			die;
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : false;
		switch($type){
			case 'meta-convert':
				ini_set('max_execution_time', 300);
				$query = new WP_Query([
					'nopaging' => true,
					'meta_key' => self::$post_meta_key['key'],
				]);
				if(!$query->have_posts())
					die('OK');
				echo 'Found: ' . count($query->posts) . '<br>';
				global $post;
				foreach($query->posts as $post){
					setup_postdata($post);
					$old_meta = self::get_post_meta($post->ID);
					$new_meta = [];
					foreach($old_meta as $k => $v){
						if(isset($v['download-pwd']))
							break 1;
						$new_meta[] = [
							'url' => $v['url'],
							'download-pwd' => $v['pwd'],
							'extract-pwd' => self::get_options('default-extract-pwd'),
						];
					}
					if(array_filter($new_meta)){
						update_post_meta($post->ID,self::$post_meta_key['key'],$new_meta);
						echo $post->ID . ' ... OK <br>';
					}
				}
				wp_reset_postdata();
				unset($query);
				die('ALL OK');

			default:
				
		}
	}
	public static function meta_box_display($post){
		$meta = array_filter((array)self::get_post_meta($post->ID));
		?>
		<div id="<?= __CLASS__;?>-container" data-tpl="<?= esc_attr(self::get_tpl_meta_box());?>">
			<?php
			if(!$meta){
				echo self::get_tpl_meta_box(0);
			}else{
				foreach($meta as $k => $v){
					echo self::get_tpl_meta_box($k,$meta);
				}
			}
			?>
		</div>
		<div id="<?= __CLASS__;?>-control">
			<a href="javascript:;" id="<?= __CLASS__;?>-add" class="add button button-primary"><?= ___('Add new item');?></a>
		</div>
		<?php
	}
	public static function is_enabled_display_name(){
		return self::get_options('enabled-display-name') == 1;
	}
	public static function get_tpl_meta_box($placeholder = '%placeholder%',array $meta = []){
		$name = isset($meta[$placeholder]['name']) ? esc_attr($meta[$placeholder]['name']) : null;
		
		$url = isset($meta[$placeholder]['url']) ? esc_attr($meta[$placeholder]['url']) : null;
		
		$download_pwd = isset($meta[$placeholder]['download-pwd']) ? esc_attr($meta[$placeholder]['download-pwd']) : null;
		
		$extract_pwd = isset($meta[$placeholder]['extract-pwd']) ? esc_attr($meta[$placeholder]['extract-pwd']) : null;

		ob_start();
		?>
		<p class="<?= __CLASS__;?>-item item" id="<?= __CLASS__;?>-item-<?= $placeholder;?>">
			<?php if(self::is_enabled_display_name()){ ?>
				<input 
					type="text" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][name]" 
					id="<?= __CLASS__;?>-<?= $placeholder;?>-name" 
					class="widefat" 
					placeholder="<?= ___('Name (optional)');?>" 
					title="<?= ___('Name (optional)');?>" 
					value="<?= $name;?>" 
				>
			<?php } ?>
			<input 
				type="text" 
				name="<?= __CLASS__;?>[<?= $placeholder;?>][url]" 
				id="<?= __CLASS__;?>-<?= $placeholder;?>-url" 
				class="widefat" 
				placeholder="<?= ___('Download page URL (include http://)');?>" 
				title="<?= ___('Download page URL (include http://)');?>" 
				value="<?= $url;?>" 
			>
			<input 
				type="text" 
				name="<?= __CLASS__;?>[<?= $placeholder;?>][download-pwd]" 
				id="<?= __CLASS__;?>-<?= $placeholder;?>-download-pwd" 
				class="widefat" 
				placeholder="<?= ___('Download password (optional)');?>" 
				title="<?= ___('Download password (optional)');?>" 
				value="<?= $download_pwd;?>" 
			>
			<input 
				type="text" 
				name="<?= __CLASS__;?>[<?= $placeholder;?>][extract-pwd]" 
				id="<?= __CLASS__;?>-<?= $placeholder;?>-extract-pwd" 
				class="widefat" 
				placeholder="<?= ___('Extract password (optional)');?>" 
				title="<?= ___('Extract password (optional)');?>" 
				value="<?= $extract_pwd;?>" 
			>
			<a href="javascript:;" class="del" data-target="<?= __CLASS__;?>-item-<?= $placeholder;?>">&uarr; <?= ___('Delete this item');?></a>
		</p>			
		<?php
		$html = html_minify(ob_get_contents());
		ob_end_clean();
		return $html;
	}
	public static function page_create(){
		if(!theme_cache::current_user_can('manage_options')) 
			return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '[post-' . self::$page_slug . ']',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Storage download'),
				'page_template'	=> 'page-' . self::$page_slug . '.php',
			)
		);
		
		$defaults = array(
			'post_content' 		=> '[post_content]',
			'post_name' 		=> null,
			'post_title' 		=> null,
			'post_status' 		=> 'publish',
			'post_type'			=> 'page',
			'comment_status'	=> 'closed',
		);
		foreach($page_slugs as $k => $v){
			theme_cache::get_page_by_path($k) || wp_insert_post(array_merge($defaults,$v));
		}
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null)
			$cache =  theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);

		return $cache;
	}
	public static function get_download_page_url($post_id = null){
		if($post_id === null){
			global $post;
			$post_id = $post->ID;
		}
		
		static $caches;
		if(isset($caches[$post_id]))
			return $caches[$post_id];
			
		$code_obj = array(
			'post-id' => (int)$post_id
		);
		$caches[$post_id] = esc_url(add_query_arg(array(
			'code' => base64_encode(authcode(json_encode($code_obj),'encode'))
			),self::get_url()));
		return $caches[$post_id];
	}
	public static function get_decode_post(){
		static $cache = null;
		if($cache !== null)
			return $cache;
		$code = isset($_GET['code']) && is_string($_GET['code']) ? base64_decode($_GET['code']) : null;
		if(!$code){
			$cache = false;
			return $cache;
		}
			
		$decode = authcode($code,'decode');
		
		if(!$decode){
			$cache = false;
			return $cache;
		}
			
		$decode = json_decode($decode,true);
		
		if(!isset($decode['post-id'])){
			$cache = false;
			return $cache;
		}

		$cache = theme_cache::get_post($decode['post-id']);
		return $cache;
	}
	public static function download_info($post_id){
		$meta = self::get_post_meta($post_id);
		?>
<div class="post-download">
	<?php foreach($meta as $k => $v){ ?>
		<fieldset class="post-download-module">
			<?php if(isset($v['name']) && !empty($v['name'])){ ?>
				<legend><span class="label label-success"><?= esc_html($v['name']);?></span></legend>
			<?php } ?>
			<div class="fieldset-content">
				<?php if(isset($v['download-pwd']) && !empty($v['download-pwd'])){ ?>
					<div class="form-group">
						<div class="input-group input-group-lg">
							<label for="<?= __CLASS__;?>-<?= $k;?>-download-pwd" class="addon" >
								<i class="fa fa-key"></i> <?= ___('Download password');?>
							</label>
							<input type="text" id="<?= __CLASS__;?>-<?= $k;?>-download-pwd" class="form-control pwd" value="<?= esc_html($v['download-pwd']);?>" size="5" onclick="this.select();" >
						</div>
					</div>
				<?php } ?>
				
				<?php if(isset($v['extract-pwd']) && !empty($v['extract-pwd'])){ ?>
					<div class="form-group">
						<div class="input-group input-group-lg">
							<label for="<?= __CLASS__;?>-<?= $k;?>-extract-pwd" class="addon" >
								<i class="fa fa-unlock"></i> <?= ___('Extract password');?>
							</label>
							<input type="text" id="<?= __CLASS__;?>-<?= $k;?>-extract-pwd" class="form-control pwd" value="<?= esc_html($v['extract-pwd']);?>" size="5" onclick="this.select();" >
						</div>
					</div>
				<?php } ?>

				<?php if(isset($v['url']) && !empty($v['url'])){ ?>
					<div class="form-group">
						<a 
							href="<?= $v['url'];?>" 
							class="btn btn-lg btn-success btn-block" 
							rel="nofollow"
							target="_blank" 
						>
							<i class="fa fa-cloud-download"></i> 
							<?= ___('Download now');?>
						</a>
					</div>
				<?php } ?>
			</div><!-- /.fieldset-content -->
		</fieldset>
	<?php } ?>
</div><!-- /.post-download -->
		<?php
	}
	public static function display_frontend(){
		global $post;
		$meta = self::get_post_meta($post->ID);
		if(!$meta)
			return;
		?>
		<a class="meta meta-post-storage" style="background:#58c780;" href="javascript:void(0)" title="<?= ___('Download');?>">
			<span class="tx"><?= ___('Download');?></span>
			<span id="post-storage-number-<?= $post->ID;?>" class="number">
				<?php
				if(class_exists('theme_post_views') && theme_post_views::is_enabled()){
					$number = (int)(theme_post_views::get_views($post->ID) * 0.5 - mt_rand(1,9));
					echo $number <= 0 ? '(' . 0 . ')' : '(' . number_format($number) . ')';
				}
				?>
			</span>
		</a>
		<script src="//cdn.bootcss.com/jquery/1.8.3/jquery.min.js"></script>
		<script>
			$(document).ready(function(){
				$('.meta-post-storage').click(function(event) {
					var data = "<?php echo $post->ID; ?>";
					var author_id = "<?php echo the_author_ID(); ?>";	//作者ID
					$.ajax({
					    type: 'POST',
					    url: '/wp-content/themes/mx/download-point.php',
					    data: {'post_id' : data, 'author_id' : author_id},
					    dataType: 'json',
					    beforeSend: function(){
					    	var beforeSendHtml = '<div id="ajax-loading-container" class="loading show"><div id="ajax-loading"><div class="tip-status tip-status-small tip-status-loading"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i> 加载中，请稍后...</div></div></div>';
					    	$('body').append(beforeSendHtml);
					    },
					    success: function(result){
					    	$('body').append(result.msg);
					    	if (result.status == 1) {
					    		var download_url = '<?= self::get_download_page_url($post->ID);?>';
					    		setTimeout(function(){
					    			// window.open(download_url);
					    			location.href = download_url;	    			
					    		}, 3000);
					    	}
					    }
					});
					return false;
				});
			});
			$('.btn-close').live('click', function(event) {
				$('.show').hide();
			});
		</script>
		<?php
	}
	public static function is_page(){
		return theme_cache::is_page(self::$page_slug);
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_custom_storage::init';
	return $fns;
});