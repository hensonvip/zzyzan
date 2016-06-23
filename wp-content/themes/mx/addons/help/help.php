<?php
class theme_help{
	public static function init(){
		add_action('help_settings', __CLASS__ . '::display_backend');
		add_action('backend_js_config', __CLASS__ . '::backend_js_config'); 
	}
	
	public static function display_backend(){
		$theme_data = wp_get_theme();
		$theme_meta_origin = theme_functions::theme_meta_translate();
		$is_oem = isset($theme_meta_origin['oem']) ? true : false;
		$theme_meta = isset($theme_meta_origin['oem']) ? $theme_meta_origin['oem'] : $theme_meta_origin;
		?>
<fieldset>
	<legend><i class="fa fa-fw fa-info-circle"></i> <?= ___('Theme Information');?></legend>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?= ___('Theme name');?></th>
				<td><?= $theme_meta['name'];?></td>
			</tr>
			<tr>
				<th scope="row"><?= ___('Theme version');?></th>
				<td><?= $theme_data->display('Version');?></td>
			</tr>
			<tr>
				<th scope="row"><?= ___('Theme edition');?></th>
				<td><?= $theme_meta_origin['edition'];?></td>
			</tr>
			<tr>
				<th scope="row"><?= ___('Theme description');?></th>
				<td><p><?= $theme_meta['des'];?></p></td>
			</tr>
			<tr>
				<th scope="row"><?= ___('Theme URI');?></th>
				<td><a href="<?= esc_url($theme_meta['theme_url'])?>" target="_blank"><?= esc_url($theme_meta['theme_url'])?></a></td>
			</tr>
			<tr>
				<th scope="row"><?= ___('Theme author');?></th>
				<td><?= $theme_meta['author']?></td>
			</tr>
			<tr>
				<th scope="row"><?= ___('Author site');?></th>
				<td><a href="<?= esc_url($theme_meta['author_url'])?>" target="_blank"><?= esc_url($theme_meta['author_url'])?></a></td>
			</tr>
			<tr>
				<th scope="row"><?= ___('Feedback and technical support');?></th>
				<td>
				
					<?php if(isset($theme_meta['email'])){ ?>
						<p><?= ___('E-Mail');?> <a href="mailto:<?= $theme_meta['email'];?>"><?= $theme_meta['email'];?></a></p>
					<?php } ?>
					
					<?php if(isset($theme_meta['qq'])){ ?>
						<p><?= ___('QQ');?>
							<?php if(isset($theme_meta['qq']['link'])){ ?>
								<a target="_blank" href="<?= esc_url($theme_meta['qq']['link']);?>"><?= $theme_meta['qq']['number'];?></a>
							<?php }else{ ?>
								<?= $theme_meta['qq']['number'];?>
							<?php } ?>
						</p>
					<?php } ?>
					
					<?php if(isset($theme_meta['qq_group'])){ ?>
						<p><?= ___('QQ group');?>
							<?php if(isset($theme_meta['qq_group']['link'])){ ?>
								<a target="_blank" href="<?= esc_url($theme_meta['qq_group']['link']);?>"><?= $theme_meta['qq_group']['number'];?></a>
							<?php }else{ ?>
								<?= $theme_meta['qq_group']['number'];?>
							<?php } ?>
						</p>
					<?php } ?>
				</td>
			</tr>
			<?php if(!$is_oem){ ?>
				<tr>
					<th scope="row"><?= ___('Donate');?></th>
					<td>
						<p>
							<!-- paypal -->
							<a id="paypal_donate" href="javascript:;" title="<?= ___('Donation by Paypal');?>">
								<img src="//ww2.sinaimg.cn/large/686ee05djw1ella1kv74cj202o011wea.jpg" alt="<?= ___('Donation by Paypal');?>" width="96" height="37"/>
							</a>
							<!-- alipay -->
							<a id="alipay_donate" target="_blank" href="http://ww3.sinaimg.cn/mw600/686ee05djw1eihtkzlg6mj216y16ydll.jpg" title="<?= ___('Donation by Alipay');?>">
								<img width="96" height="37" src="//ww1.sinaimg.cn/large/686ee05djw1ellabpq9euj202o011dfm.jpg" alt="<?= ___('Donation by Alipay');?>"/>
							</a>
							<!-- wechat -->
							<a id="wechat_donate" target="_blank" href="http://ww4.sinaimg.cn/mw600/686ee05djw1exukpkk4fwj20fr0f940r.jpg" title="<?= ___('Donation by Wechat');?>">
								<img width="96" height="37" src="//ww3.sinaimg.cn/large/686ee05djw1exul2142tvj202o0113ya.jpg" alt="<?= ___('Donation by Wechat');?>"/>
							</a>
						</p>
					</td>
				</tr>
			<?php }else{ ?>
			<tr>
				<th scope="row"><?= ___('Theme core');?></th>
				<td><a href="<?= esc_url($theme_meta['core']['url'])?>" target="_blank"><?= $theme_meta['core']['name']?></a></td>
			</tr>
			<?php } ?>


		</tbody>
	</table>
</fieldset>
	<?php
	}
	public static function backend_js_config(array $config){
		$config[__CLASS__] = [
			'lang' => [
				'M01' => sprintf(___('Donate to INN STUDIO (%s)'),theme_features::get_theme_info('name')),
			],
		];
		return $config;
	}
}
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_help::init';
	return $fns;
});