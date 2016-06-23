var ready = require('modules/ready');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var array_merge = require('modules/array-merge');
var param = require('modules/parse-obj-url');

module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_point_lottery)
		return;

	var cache = {},
		config = {
			process_url : '',
			prefix_item_id : 'theme_point_lottery-item-',
			items_id : '.theme_point_lottery-item',
			add_id : 'theme_point_lottery-add',
			control_container_id : 'theme_point_lottery-control',
			tpl : '',
		};
		
	config = array_merge(config, window.THEME_CONFIG.theme_point_lottery);
	
	ready(function(){
		bind();
		check_redeem();
	});

	

	function bind(){
		add();
		del(jQuery(config.items_id));
	}
	function I(e,j){
		if(!j)
			return jQuery(document.getElementById(e));
		return document.getElementById(e);
	}
	function add(){
		var $add = I(config.add_id),
			$control_container = I(config.control_container_id);
		if(!$add[0]) return false;
		$add.on('click',function(){
			var $tpl = jQuery(config.tpl.replace(/\%placeholder\%/ig,get_random_int()));
			del($tpl);
			$control_container.before($tpl);
			$tpl.find('input').eq(0).focus();
		});
	
	}
	function del($tpl){
		$tpl.find('.delete').on('click',function(){
			I(jQuery(this).data('target'))
			.css('background','#d54e21')
			.fadeOut('slow',function(){
				jQuery(this).remove();
			})
		})
	}
	function get_random_int() {
		return +new Date();
	}
	function check_redeem(){
		cache.$tip = I('theme_point_lottery-tip',true);
		cache.$area_btns = I('theme_point_lottery-btns',true);
		cache.$user_id = I('theme_point_lottery-redeem-user-id',true);
		cache.$code = I('theme_point_lottery-redeem-code',true);
		cache.$submit = I('theme_point_lottery-check-redeem',true);

		function event_click(e){
			e.preventDefault();

			if(cache.$user_id.value === ''){
				cache.$user_id.focus();
				return false;
			}
			
			if(cache.$code.value === ''){
				cache.$code.focus();
				return false;
			}

			ajax_loading_tip('loading',window.THEME_CONFIG.lang.M01);
			
			var xhr = new XMLHttpRequest(),
			fd = {
				'user-id' : cache.$user_id.value,
				redeem : cache.$code.value,
				type : 'check-redeem'
			};
			xhr.open('get',config.process_url + '&' + param(fd));
			xhr.send();
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
					if(data.status){
						ajax_loading_tip(data.status,data.msg);
					}else{
						ajax_loading_tip('error',data);
					}
				}else{
					ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
				}
			};
			xhr.onerror = function(){
				ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
			}
		}
		cache.$submit.addEventListener('click', event_click);
	}
}