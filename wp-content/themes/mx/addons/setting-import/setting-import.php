<?php
/*
Feature Name:	theme_import_settings
Feature URI:	http://www.inn-studio.com
Version:		3.0.2
Description:	theme_import_settings
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/

class theme_import_settings{
	public static function init(){
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		add_action('backend_js_config', __CLASS__ . '::backend_js_config'); 
		add_action('advanced_settings', __CLASS__ . '::display_backend',99);		
	}
	public static function display_backend(){
		
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-exchange"></i> <?= ___('Import &amp; export theme settings');?></legend>
			<p class="description">
				<?= ___('You can select the settings file to upload and restore settings if you have the *.txt file. If you want to export the settings backup, please click the export button.');?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?= ___('Import');?></th>
						<td>
							<a href="javascript:;" id="<?= __CLASS__;?>-import" class="button">
								<i class="fa fa-history"></i> 
								<?= ___('Select a setting file to restore');?>
								<input id="<?= __CLASS__;?>-file" type="file" />
							</a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Export');?></th>
						<td>
							<a href="<?= theme_features::get_process_url([
								'action' => __CLASS__,
								'type' => 'export',
							]);?>" id="<?= __CLASS__;?>-export" class="button"><i class="fa fa-cloud-download"></i> <?= ___('Start export settings file');?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	/**
	 * Process
	 * 
	 * 
	 * @return 
	 * @version 1.0.0
	 * 
	 */
	public static function process(){

		theme_features::check_referer();
		
		if(!theme_cache::current_user_can('manage_options'))
			die;
			
		$output = [];
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;

		switch($type){
			case 'import':
				$file = isset($_FILES['file']) ? $_FILES['file'] : false;
				if(!$file || $file['error'] != 0){
					die(theme_features::json_format([
						'status' => 'error',
						'msg' => ___('Invalid file.'),
					]));
				}
				$contents = json_decode(base64_decode(file_get_contents($file['tmp_name'])),true);
				if(is_array($contents) && !empty($contents)){
					set_theme_mod('theme_options',$contents);
					die(theme_features::json_format([
						'status' => 'success',
						'msg' => ___('Settings has been restored, refreshing page, please wait...'),
					]));
				/**
				 * invalid contents
				 */
				}else{
					die(theme_features::json_format([
						'status' => 'error',
						'msg' => ___('Invalid file content.'),
					]));
				}
				break;
			/**
			 * export
			 */
			case 'export':
				$contents = base64_encode(json_encode(theme_options::get_options()));
				/**
				 * write content to a tmp file
				 */
				$tmp = tmpfile();
				$filepath = stream_get_meta_data($tmp)['uri'];
				file_put_contents($filepath,$contents);
				/**
				 * output file download
				 */
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($filepath));
				
				$download_fn = ___('Backup') ;
				$download_fn .= '-' . theme_cache::get_bloginfo('name');
				$download_fn .= '-' . theme_functions::$iden;
				$download_fn .= '-' . date('Ymd-His') . '.bk';
				
				header('Content-Disposition: attachment; filename=" ' . $download_fn . '"');
				
				readfile($filepath); 

				die;
		}

		die(theme_features::json_format($output));
	}
	public static function backend_js_config(array $config){
		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url([
				'action' => __CLASS__
			]),
		];
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_import_settings::init';
	return $fns;
});