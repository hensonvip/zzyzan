<?php
/** 
 * version 1.1.0
 */

class widget_rank extends WP_Widget{
	function __construct(){
		$this->alt_option_name = __CLASS__;
		parent::__construct(
			__CLASS__,
			___('Posts ranking') . ' - <small>' . theme_functions::$iden . '</small>',
			[
				'classname' => __CLASS__,
				'description'=> ___('Display different type posts ranking.'),
			]
		);
	}
	public static function frontend_display(array $args = [],$instance){
		$instance = array_merge([
			'title' => ___('Posts rank'),
			'posts_per_page' => 6,
			'date' => 'all',
			'orderby' => 'latest',
			'category__in' => [],
			'content_type' => 'img',
		],$instance);
		
		$title = esc_html($instance['title']);
		echo $args['before_title'];
		if(count($instance['category__in']) === 1 && isset($instance['category__in'][0])){ ?>
			<a class="link" href="<?= get_category_link($instance['category__in'][0]);?>" title="<?= sprintf(___('Views more about %s'),$title);?>">
				<i class="fa fa-bar-chart"></i> 
				<?= $title;?>
			</a>
			<a href="<?= get_category_link($instance['category__in'][0]);?>" title="<?= sprintf(___('Views more about %s'),$title);?>" class="more"><?= ___('More &raquo;');?></a>
		<?php }else{ ?>
			<i class="fa fa-bar-chart"></i> <?= $title;?>
		<?php } ?>
		
		<?php
		echo $args['after_title'];
		
		global $post;
		$query = theme_functions::get_posts_query([
			'category__in' => (array)$instance['category__in'],
			'posts_per_page' => (int)$instance['posts_per_page'],
			'date' => $instance['date'],
			'orderby' => $instance['orderby'],
		]);
		if($query->have_posts()){
			?>
			<ul class="list-group list-group-<?= $instance['content_type'];?> widget-orderby-<?= $instance['orderby'];?>">
				<?php
				foreach($query->posts as $post){
					setup_postdata($post);
					if($instance['content_type'] === 'tx'){
						theme_functions::widget_rank_tx_content([
							'meta_type' => $instance['orderby'],
						]);
					}else{
						theme_functions::widget_rank_img_content();
					}
				}
				wp_reset_postdata();
				?>
			</ul>
		<?php }else{ ?>
			<div class="page-tip not-found">
				<?= status_tip('info',___('No data yet.'));?>
			</div>
		<?php 
		}
	}
	function widget($args,$instance){
		echo $args['before_widget'];
		self::frontend_display($args,$instance);
		echo $args['after_widget'];
	}
	
	function form($instance = []){
		$instance = array_merge([
			'title'=> ___('Posts rank'),
			'posts_per_page' => 6,
			'date' => 'all',
			'category__in' => [],
			'content_type' => 'img',
			'orderby' => 'latest',
		],$instance);
		?>
		<p>
			<label for="<?= self::get_field_id('title');?>"><?= ___('Title (optional)');?></label>
			<input 
				id="<?= self::get_field_id('title');?>"
				class="widefat"
				name="<?= self::get_field_name('title');?>" 
				type="text" 
				value="<?= $instance['title'];?>" 
				placeholder="<?= ___('Title (optional)');?>"
			/>
		</p>
		<p>
			<label for="<?= self::get_field_id('posts_per_page');?>"><?= ___('Post number (required)');?></label>
			<input 
				id="<?= self::get_field_id('posts_per_page');?>"
				class="widefat"
				name="<?= self::get_field_name('posts_per_page');?>" 
				type="number" 
				value="<?= $instance['posts_per_page'];?>" 
				placeholder="<?= ___('Post number (required)');?>"
			/>
		</p>
		<p>
			<?= ___('Categories: ');?>
			<?php 
			self::get_cat_checkbox_list(
				self::get_field_name('category__in'),
				self::get_field_id('category__in'),
				$instance['category__in']
			);
			?>
		</p>
		<!-- date -->
		<p>
			<label for="<?= self::get_field_id('date');?>"><?= ___('Date');?></label>
			<select
				name="<?= self::get_field_name('date');?>" 
				class="widefat"				
				id="<?= self::get_field_id('date');?>"
			>
				<?php
				foreach(self::get_rank_data() as $k => $v){
					the_option_list($k,$v,$instance['date']);
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?= self::get_field_id('content_type');?>"><?= ___('Content type');?></label>
			<select 
				name="<?= self::get_field_name('content_type');?>" 
				class="widefat"
				id="<?= self::get_field_id('content_type');?>"
			>
				<?php
				/** 
				 * image type
				 */
				the_option_list('img',___('Image type'),$instance['content_type']);
				
				/** 
				 * text type
				 */
				the_option_list('tx',___('Text type'),$instance['content_type']);?>
			</select>
		</p>
		<p>
			<label for="<?= self::get_field_id('orderby');?>">
				<?= ___('Order by');?>
			</label>
			<select 
				name="<?= self::get_field_name('orderby');?>" 
				class="widefat"
				id="<?= self::get_field_id('orderby');?>"
			>
				
				<?php
				
				/** 
				 * orderby views
				 */
				if(class_exists('theme_post_views') && theme_post_views::is_enabled()){
					the_option_list('views',___('Most views'),$instance['orderby']);
				}
				
				/** 
				 * orderby thumb-up
				 */
				if(class_exists('theme_post_thumb') && theme_post_thumb::is_enabled()){
					the_option_list('thumb-up',___('Thumb up'),$instance['orderby']);
				}
				
				/** 
				 * orderby recommended
				 */
				if(class_exists('theme_recommended_post')){
					the_option_list('recommended',___('Recommended'),$instance['orderby']);
				}
				/** 
				 * orderby random
				 */
				the_option_list('random',___('Random'),$instance['orderby']);
				
				/** 
				 * orderby latest
				 */
				the_option_list('latest',___('Latest'),$instance['orderby']);
				
				?>
			</select>
		</p>
		<?php
	}
	public static function get_rank_data($key = null){
		$content = [
			'all' 			=> ___('All'),
			'daily' 		=> ___('Daily'),
			'weekly' 		=> ___('Weekly'),
			'monthly' 		=> ___('Monthly'),
		];
		if($key) 
			return isset($content[$key]) ? $content[$key] : false;
		return $content;
	}
	/**
	 * 
	 *
	 * @param 
	 * @return 
	 * @version 1.0.1
	 */
	private static function get_cat_checkbox_list($name,$id,$selected_cat_ids = []){
		$cats = theme_cache::get_categories(array(
			'hide_empty' => false,
		));
		
		if($cats){
			foreach($cats as $cat){
				if(in_array($cat->term_id,(array)$selected_cat_ids)){
					$checked = ' checked="checked" ';
					$selected_class = ' button-primary ';
				}else{
					$checked = null;
					$selected_class = null;
				}
			?>
			<label for="<?= $id;?>-<?= $cat->term_id;?>" class="item button <?= $selected_class;?>">
				<input 
					type="checkbox" 
					id="<?= $id;?>-<?= $cat->term_id;?>" 
					name="<?= esc_attr($name);?>[]" 
					value="<?= $cat->term_id;?>"
					<?= $checked;?>
				/>
					<?= esc_html($cat->name);?>
			</label>
			<?php 
			}
			unset($cats);
		}else{ ?>
			<p><?= ___('No category, pleass go to add some categories.');?></p>
		<?php }
	}
	function update($new_instance,$old_instance){
		return array_merge($old_instance,$new_instance);
	}
	public static function register_widget(){
		register_widget(__CLASS__);
	}
}
add_action('widgets_init','widget_rank::register_widget' );