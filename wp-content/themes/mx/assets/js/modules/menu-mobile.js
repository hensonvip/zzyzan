var click_handle = require('modules/click-handle');
var ready = require('modules/ready');

module.exports = function(){
	'use strict';
	var cache = {};

	function bind(){
		cache.$toggles = document.querySelectorAll('a[data-mobile-target]');
		if(!cache.$toggles[0])
			return;
		
		create_layer();

		for( var i = 0, len = cache.$toggles.length; i < len; i++){
			cache.$toggles[i].addEventListener(click_handle, event_click);
		}
	}

	function create_layer(){
		cache.$layer = document.createElement('div');
		cache.$layer.id = 'mobile-on-layer';
		cache.$layer.addEventListener(click_handle, hide_menu);
		document.body.appendChild(cache.$layer);
	}

	function show_menu(){
		var icon_active = cache.$last_click_btn.getAttribute('data-icon-active'),
			icon_original = cache.$last_click_btn.getAttribute('data-icon-original');
		document.body.classList.add('menu-on');
		cache.$last_target.classList.add('on');
		if(icon_active && icon_original){
			cache.$last_click_btn.classList.remove(icon_original);
			cache.$last_click_btn.classList.add(icon_active);
		}
		var focus_target = cache.$last_click_btn.getAttribute('data-focus-target');
		if(focus_target){
			var $focus_target = document.querySelector(focus_target);
			if($focus_target){
				$focus_target.focus();
			}
		}
	}
	function hide_menu(){
		var icon_active = cache.$last_click_btn.getAttribute('data-icon-active'),
			icon_original = cache.$last_click_btn.getAttribute('data-icon-original');
			
		document.body.classList.remove('menu-on');
		cache.$last_target.classList.remove('on');
		if(icon_active && icon_original){
			cache.$last_click_btn.classList.remove(icon_active);
			cache.$last_click_btn.classList.add(icon_original);
		}
	}
	function event_click(e){
		cache.$last_target = document.querySelector(this.getAttribute('data-mobile-target'));
		cache.$last_click_btn = this;
		/** hide */
		if(cache.$last_target.classList.contains('on')){
			hide_menu();
		}else{
			show_menu();
		}
	}
	
	ready(bind);
}