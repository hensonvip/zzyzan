var ready = require('modules/ready');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var array_merge = require('modules/array-merge');

module.exports = function(){
	'use strict';
	
	if(!window.THEME_CONFIG.theme_custom_point)
		return;
		
	var cache = {},
		config = {
			process_url : ''
		};

	config = array_merge(config, window.THEME_CONFIG.theme_custom_point);

	
	ready(set_point);

	function set_point(){
		cache.$user_id = document.getElementById('theme_custom_point-special-user-id');
		
		cache.$user_point = document.getElementById('theme_custom_point-special-point');
		
		cache.$user_event = document.getElementById('theme_custom_point-special-event');
		
		cache.$user_set = document.getElementById('theme_custom_point-special-set');

		cache.$user_id.addEventListener('blur',event_blur_get_point);
		cache.$user_set.addEventListener('click',event_set_user_point);
		
	}
	function event_blur_get_point(){
		var $this = this,
			urls = '&user-id=' + $this.value + '&type=' + $this.getAttribute('data-ajax-type');
			
		if($this.value === '')
			return false;
			
		ajax_loading_tip('loading',window.THEME_CONFIG.lang.M01);
		var xhr = new XMLHttpRequest();
		xhr.open('GET',config.process_url + urls);
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				
				try{data = JSON.parse(xhr.responseText);}catch(err){data = xhr.responseText}
				
				if(data && data.status){
					ajax_loading_tip(data.status,data.msg);
				}else{
					ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
				}
			}else{
				ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
			}
			xhr = null;
		};
		xhr.onerror = function(){
			ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
		};
		xhr.send();
		
		return false;
		
	}
	function event_set_user_point(){
		var validates = [
				cache.$user_id,
				cache.$user_point,
				cache.$user_event
			],
			urls = '';
		for(var i = 0, len = validates.length; i < len;i++){
			if(validates[i].value === ''){
				validates[i].focus();
				return false;
			}
			urls += '&special[' + validates[i].getAttribute('data-ajax-field') + ']=' + validates[i].value;
		}
		urls += '&type=special';
		
		ajax_loading_tip('loading',window.THEME_CONFIG.lang.M01);
		
		var xhr = new XMLHttpRequest();
		xhr.open('GET',config.process_url + urls);
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				
				try{data = JSON.parse(xhr.responseText);}catch(e){data = xhr.responseText}
				
				if(data && data.status){
					ajax_loading_tip(data.status,data.msg);
				}else{
					ajax_loading_tip('error',data);
				}
			}else{
				ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
			}
			xhr = null;
		};
		xhr.onerror = function(){
			ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
		};
		xhr.send();
		
		return false;
	}
}