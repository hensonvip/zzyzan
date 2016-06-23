<?php
/*
Feature Name:	theme_clean_up
Feature URI:	http://www.inn-studio.com/theme_clean_up
Version:		3.0.0
Description:	optimizate your database
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
class theme_clean_up{
	public static function init(){
		add_action('advanced_settings', __CLASS__ . '::display_backend',20);
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		add_action('backend_js_config', __CLASS__ . '::backend_js_config');
	}
	public static function display_backend(){
				
		?>
		<fieldset>
			<legend><i class="fa fa-fw fa-database"></i> <?= ___('Database Optimization');?></legend>
			<p class="description"><?= ___('If your site works for a long time, maybe will have some redundant data in the database, they will reduce the operating speed of the your site, recommend to clean them regularly.');?></p>
			<p class="description"><strong><?= ___('Attention: this action will be auto clean up all theme cache.');?></strong></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?= ___('Clean redundant post data');?></th>
						<td>
							<p>
								<a 
									href="javascript:;"
									class="button <?= __CLASS__;?>-btn" 
									data-action="redundant-posts" 
									data-tip-target="<?= __CLASS__;?>-redundant-posts"
								><?= ___('Delete revision &amp; draft &amp; auto-draft &amp; trash posts');?></a>
							</p>
							<p>
								<a 
									href="javascript:;"
									class="button <?= __CLASS__;?>-btn" 
									data-action="orphan-postmeta"
									data-tip-target="<?= __CLASS__;?>-tip-orphan-postmeta"
								><?= ___('Delete orphan post meta');?></a>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Clean redundant comment data');?></th>
						<td>
							<p><a 
								href="javascript:;"
								class="button <?= __CLASS__;?>-btn" 
								data-action="redundant-comments"
								data-tip-target="<?= __CLASS__;?>-tip-redundant-comments"
							><?= ___('Delete moderated &amp; spam &amp; trash comments');?></a></p>
							<p><a 
								href="javascript:;"
								class="button <?= __CLASS__;?>-btn" 
								data-action="orphan-commentmeta"
								data-tip-target="<?= __CLASS__;?>-tip-orphan-commentmeta"
							><?= ___('Delete orphan comment meta');?></a></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Clean redundant other data');?></th>
						<td>
							<p><a 
								class="button <?= __CLASS__;?>-btn" 
								data-action="orphan-relationships"
								data-tip-target="<?= __CLASS__;?>-tip-orphan-relationships"
							><?= ___('Delete orphan relationship');?></a></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Optimizate the WP Database');?></th>
						<td>
							<p><a 
								class="button <?= __CLASS__;?>-btn" 
								data-action="optimizate"
								data-tip-target="<?= __CLASS__;?>-tip-database-optimization"
							><?= ___('Optimizate Now');?></a></p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	
	public static function process(){

		theme_features::check_referer();
		
		$output = [];
		
		$type = isset($_GET['type']) ? $_GET['type'] : null;

		if(!theme_cache::current_user_can('manage_options'))
			die;
			
		timer_start();
		global $wpdb;
		switch($type){
			/** 
			 * revision
			 */
			case 'redundant-posts':
				$sql = $wpdb->prepare(
					"
					DELETE posts,term,postmeta 
					FROM `$wpdb->posts`posts 
					LEFT JOIN `$wpdb->term_relationships` term
					ON (posts.ID = term.object_id)
					LEFT JOIN `$wpdb->postmeta` postmeta 
					ON (posts.ID = postmeta.post_id)
					WHERE posts.post_type = '%s'
					OR posts.post_status = '%s'
					OR posts.post_status = '%s'
					OR posts.post_status = '%s'
					",
					'revision',
					'draft',
					'auto-draft',
					'trash'
				);

				break;
			/** 
			 * edit_lock
			 */
			case 'orphan-postmeta':
				$sql = $wpdb->prepare(
					"
					DELETE FROM `$wpdb->postmeta`
					WHERE `meta_key` = '%s'
					OR `post_id`
					NOT IN (SELECT `ID` FROM `$wpdb->posts`)
					",
					'_edit_lock'
				);
				break;
			
			/** 
			 * moderated
			 */
			case 'redundant-comments':
				$sql = $wpdb->prepare(
					"
					DELETE FROM `$wpdb->comments`
					WHERE `comment_approved` = '%s'
					OR `comment_approved` = '%s'
					OR `comment_approved` = '%s'
					",
					'0','spam','trash'
				);
				break;
			/** 
			 * commentmeta
			 */
			case 'orphan-commentmeta':
				$sql = 
				"
				DELETE FROM `$wpdb->commentmeta`
				WHERE `comment_ID` 
				NOT IN (SELECT `comment_ID` FROM `$wpdb->comments`)
				";
				
				break;
			/** 
			 * relationships
			 */
			case 'orphan-relationships':
				$sql = $wpdb->prepare(
					"
					DELETE FROM `$wpdb->term_relationships`
					WHERE `term_taxonomy_id` = %d 
					AND `object_id` 
					NOT IN (SELECT `id` FROM `$wpdb->posts`)
					",
					1
				);
				break;
			/** 
			 * optimizate
			 */
			case 'optimizate':
				$sql = 'SHOW TABLE STATUS FROM `'.DB_NAME.'`';
				$results = $wpdb->get_results($sql);
				foreach($results as $v){
					$sql = 'OPTIMIZE TABLE '.$v->Name;
					$wpdb->get_results($sql);
				}
				break;
				
			default:
				$output['status'] = 'error';
				$output['msg'] = ___('No param');
				die(theme_features::json_format($output));
		}
				
		if($type !== 'optimizate') $wpdb->query($sql);
		
		/** flush cache */
		wp_cache_flush();
			
		$output['status'] = 'success';
		$output['msg'] = sprintf(___('Database updated in %s s.'),timer_stop());
		
		die(theme_features::json_format($output));
	}
	public static function backend_js_config(array $config){
		$config[__CLASS__] = [
			'process_url' => theme_features::get_process_url(array('action'=>__CLASS__)),
		];
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_clean_up::init';
	return $fns;
});