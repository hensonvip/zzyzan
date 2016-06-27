<?php
namespace sinapicv2\inc;

class updater{
	public $dir;
	public $slug;
	public $file;
	public $checker_url;
	public $name;
	
	private $dir_basename;
	private $filename;
	private $response;
	private $plugin_data;
	private $plugin_activated;
	
	public function init(){
		$this->dir_basename = basename($this->dir);
		$this->filename = basename($this->file);
		$this->plugin = $this->dir_basename . '/' . $this->filename;
		
		\add_filter('pre_set_site_transient_update_plugins',  [ $this,'filter_pre_set_site_transient_update_plugins' ]);
		\add_filter('plugins_api', [ $this, 'filter_plugins_api' ], 10, 3);
		//\add_filter('upgrader_pre_install', [ $this, 'filter_upgrader_pre_install' ], 10, 2);
		\add_filter('upgrader_post_install', [ $this, 'filter_upgrader_post_install' ], 10, 3);
	}
	public function filter_pre_set_site_transient_update_plugins($transient){
		if (!isset($transient->checked[$this->plugin]))
			return $transient;

		$this->response = $this->get_response($this->checker_url);
		if(!$this->response)
			return $transient;

		$response = $this->to_wp_format($this->response);
		
		/** version compare */
		if(version_compare($transient->checked[$this->plugin], $response['new_version'], '>='))
			return $transient;
			
		/** have new version */
		$transient->response[$this->plugin] = (object)$response;
		$transient->response[$this->plugin]->plugin = $this->plugin;

		return $transient;
	}
	private function get_response($remote_url){
		$response = \wp_remote_get( $remote_url );

		if( \is_wp_error($response) || ($response['response']['code'] != 200) )
			return false;

		$response = json_decode($response['body'],true);
		if(!$response)
			return false;

		if(!isset($response['version']) || !isset($response['homepage']) || !isset($response['download_url']))
			return false;
			
		return $response;
	}
	public function filter_plugins_api($false, $action, $response){
		if(!isset($response->slug) || $response->slug !== $this->slug)
			return $false;
		
		$this->response = $this->get_response($this->checker_url);
		$response = (object)$this->response;
		/**
		 * last_update
		 */
		if(isset($this->response['last_update'])){
			unset($response->last_update);
			$response->last_updated = $this->response['last_update'];
		}
		$response->name = $this->name;
		/**
		 * active_installs
		 */
		$response->active_installs = mt_rand(1000,1000000);;
		/**
		 * rating
		 */
		$response->rating = 100;
		$response->num_ratings = mt_rand(1000,999999);
		/**
		 * donate_link
		 */
		$response->donate_link = 'http://ww3.sinaimg.cn/large/686ee05djw1eihtkzlg6mj216y16ydll.jpg';

		return $response;
	}
	public function filter_upgrader_pre_install($true, $args){
		$this->plugin_activated = \is_plugin_active( $this->iden );
	}
	private function get_root_dir($dir){
		if(!is_dir($dir))
			return $dir;
		
		$sub_dirs = glob($dir . '/*');
		if(!$sub_dirs)
			return $dir;
			
		$count = count($sub_dirs);

		if( $count > 1 )
			return $dir;
			
		if(is_dir($sub_dirs[0]))
			return $this->get_root_dir($sub_dirs[0]);
		return $sub_dirs[0];
	}
	public function filter_upgrader_post_install( $true, $hook_extra, $result ){
		if($result['destination_name'] == $this->slug){
			return $result;
			
		/** is download from github */
		}elseif($result['destination_name'] == $this->slug . '-master'){
			$root_dir = $this->get_root_dir($result['destination']);
			$new_destination = $result['local_destination'] . '/' . $this->slug . '/';
			rename($root_dir,$new_destination);
			$result['destination'] = $new_destination;
			$result['remote_destination'] = $new_destination;
			$result['destination_name'] = $this->slug;
			
		}
		return $result;
	}
	private function to_wp_format(array $response){
		return [
			'name' => $this->name,
			'slug' => $response['slug'],
			'new_version' => $response['version'],
			'url' => $response['homepage'],
			'package' => $response['download_url'],
			'sections' => $response['sections'],
		];
	}
}
