var ready = require('modules/ready');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var array_merge = require('modules/array-merge');
var uploader = require('modules/uploader');
var paseHTML = require('modules/parse-html');
var tpl_control = require('modules/tpl-control');

module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_custom_slidebox)
		return;
	
	var cache = {},
		config = {
			process_url : ''
		};
	config = array_merge(config, window.THEME_CONFIG.theme_custom_slidebox);
	
	ready(bind);
	function bind(){
		
		cache.$container = document.getElementById('theme_custom_slidebox-container');
		cache.$add = document.getElementById('theme_custom_slidebox-add');
		if(!cache.$container || !cache.$add)
			return;

		var controler = new tpl_control();
		controler.$add = cache.$add;
		controler.$container = cache.$container;
		controler.new_tpl_callback = function($item){
			bind_upload({
				$item : $item,
				$url : $item.querySelector('.upload-img-url'),
				$file : $item.querySelector('input[type="file"]')
			});
		};
		controler.init();
		
	}
	function bind_upload(args){
		new uploader({
			url : config.process_url,
			$item : args.$item,
			$file : args.$file,
			paramname : 'img',
			onselect : function(){
				ajax_loading_tip('loading',window.THEME_CONFIG.lang.M01);
			},
			onalways : function(data,i,file,count) {
				if(data && data.status === 'success'){
					args.$url.value = data.url;
					if(args.$item){
						args.$item.querySelector('.img-preview').src = data.url;
					}
					ajax_loading_tip('success',data.msg,3);
				}else if(data && data.status === 'error'){
					ajax_loading_tip(data.status,data.msg,3);
				}else{
					ajax_loading_tip('error',data);
				}
			}
		});
	}
}