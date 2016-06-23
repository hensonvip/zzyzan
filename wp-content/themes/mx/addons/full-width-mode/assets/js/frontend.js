var ready = require('modules/ready');
var array_merge = require('modules/array-merge');
var click_handle = require('modules/click-handle');

module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_full_width_mode)
		return;
		
	var cache = {},
		config = {
			key : 'full-width-mode',
			lang : {
				M01 : 'Full width mode'
			}
		};
		
	config = array_merge(config, window.THEME_CONFIG.theme_full_width_mode);

	init();
	function init(){
		ready(bind);
	}
	function bind(){
		cache.$main = I('main');
		cache.$side = I('sidebar-container');
		
		if(!cache.$main || !cache.$side)
			return;
			
		if(!create_btn())
			return;
			
		cache.$btn.addEventListener(click_handle, event_click);

		if(localStorage.getItem(config.key) == 1){
			expand();
		}
	}
	function reset_media(){
		if(window.jQuery){
			jQuery(window).resize();
		}
		try{
			require('addons/page-nagination-ajax/assets/js/frontend').page_nagi.reset_nagi_style();
		}catch(e){}
	}
	function expand(set){
		cache.$btn.classList.remove('fa-angle-right');
		cache.$btn.classList.add('fa-angle-left');
		cache.$main.classList.add('expand');
		cache.$side.classList.add('expand');
		reset_media();
		if(set)
			localStorage.setItem(config.key,1);
	}
	function reset(){
		cache.$btn.classList.remove('fa-angle-left');
		cache.$btn.classList.add('fa-angle-right');
		cache.$main.classList.remove('expand');
		cache.$side.classList.remove('expand');
		reset_media();
		localStorage.removeItem(config.key);
	}
	function is_expanded(){
		return cache.$main.classList.contains('expand');
	}
	function event_click(){
		if(is_expanded()){
			reset();
		}else{
			expand(true);
		}
	}
	function create_btn(){
		var $container = document.querySelector('.singular-post');
		if(!$container)
			return false;
		var $i = document.createElement('i');
		$i.id = 'full-width-mode';
		$i.title = config.lang.M01;
		$i.setAttribute('class','fa fa-angle-right fa-2x');
		$container && $container.appendChild($i);
		cache.$btn = $i;
		return true;
	}
	function I(e){
		return document.getElementById(e);
	}
}