var ajax_loading_tip = require('modules/ajax-loading-tip');
var ready = require('modules/ready');
var array_merge = require('modules/array-merge');

module.exports = function(){
	'use strict';
	
	if(!window.THEME_CONFIG.custom_post_point)
		return;
		
	var cache = {},
		config = {
			process_url : '',
		};
		
	config = array_merge(config, window.THEME_CONFIG.custom_post_point);
	
	function init(){
		ready(bind);
	}
		
	function bind(){
		cache.$btns = document.querySelectorAll('.post-point-btn');
		if(!cache.$btns[0])
			return false;
			
		for(var i = 0,len = cache.$btns.length; i<len; i++){
			cache.$btns[i].addEventListener('click',event_click);
		}
	}

	function event_click(e){
		e.preventDefault();
		e.stopPropagation();
		var post_id = this.getAttribute('data-post-id'),
			points = this.getAttribute('data-points');

		cache.$number = I('post-point-number-' + post_id);
		
		ajax_loading_tip('loading',window.THEME_CONFIG.lang.M01);
		
		var xhr = new XMLHttpRequest(),
			fd = new FormData();
		fd.append('post-id',post_id);
		fd.append('points',points);
		fd.append('theme-nonce',window.DYNAMIC_REQUEST['theme-nonce']);
		
		xhr.open('post',config.process_url);
		xhr.send(fd);
		
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				try{data = JSON.parse(xhr.responseText)}catch(err){data = xhr.responseText}
				
				if(data && data.status){
					done(data);
				}else{
					fail(data);
				}
			}else{
				ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
			}
		};
		xhr.onerror = function(){
			ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
		};

		function done(data){
			if(data.status === 'success'){
				ajax_loading_tip(data.status,data.msg,3);
				/** incre points to dom */
				cache.$number.innerHTML = data.points;
			}else{
				ajax_loading_tip(data.status,data.msg);
			}
		};
		function fail(text){
			ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
		}
		
	}
	function I(e){
		return document.getElementById(e);
	}

	init();
}