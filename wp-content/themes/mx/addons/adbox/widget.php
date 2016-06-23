<?php
/**
 * @version 1.0.2
 */
class widget_adbox extends WP_Widget{
	function __construct(){
		$this->alt_option_name = __CLASS__;
		parent::__construct(
			__CLASS__,
			___('Advertisement code <small>(Custom)</small>'),
			array(
				'classname' => __CLASS__,
				'description'=> ___('Show your advertisement.'),
			)
		);
	}
	function widget($args,$instance){
		$device = wp_is_mobile() ? 'desktop' : 'mobile';
		
		if(isset($instance['type']) && $instance['type'] !== 'all' && $instance['type'] !== $device)
			return;
			
		$type = isset($instance['type']) ? $instance['type'] : 'all';
		echo $args['before_widget'];
		?>
		<div class="adbox">
			<?= stripslashes($instance['code']);?>
		</div>
		<?php
		echo $args['after_widget'];
	}
	function form($instance = []){
		$instance = array_merge([
			'title' =>___('Advertisement'),
			'type' => 'all',
			'code' => null,
		],$instance);
		?>
		<p>
			<label for="<?= self::get_field_id('type');?>"><?= ___('Type');?></label>
			<select 
				name="<?= self::get_field_name('type');?>"  
				class="widefat" 
				id="<?= self::get_field_id('type');?>" 
			>
				<?php the_option_list('all',___('All'),$instance['type']);?>
				<?php the_option_list('desktop',___('Desktop'),$instance['type']);?>
				<?php the_option_list('mobile',___('Mobile'),$instance['type']);?>
			</select>
		</p>
		<p>
			<label for="<?= self::get_field_id('code');?>"><?= ___('Code');?></label>
			<textarea 
				name="<?= self::get_field_name('code');?>" 
				id="<?= self::get_field_id('code');?>" 
				cols="30" 
				rows="10" 
				class="widefat" 
			><?= stripslashes($instance['code']);?></textarea>
			
		</p>
		<?php
	}
	function update($new_instance,$old_instance){
		return array_merge($old_instance,$new_instance);
	}
	public static function register_widget(){
		register_widget(__CLASS__);
	}

}
add_action('widgets_init','widget_adbox::register_widget');