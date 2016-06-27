var ready = require('modules/ready');
var window_scroll = require('modules/window-scroll');
var get_ele_top = require('modules/get-ele-top');

module.exports = function(){
	'use strict';

	ready(init);

	var cache = {};
	
	function init(){
		cache.$tab = I('backend-tab');
		if(!cache.$tab)
			return;

		cache.$tab_loading = document.querySelector('.backend-tab-loading');
		cache.$tab_header = cache.$tab.querySelector('.tab-header');
		cache.$tab_header_items = cache.$tab_header.querySelectorAll('.tab-item');
		cache.$tab_body = cache.$tab.querySelector('.tab-body');
		cache.$tab_body_items = cache.$tab_body.querySelectorAll('.tab-item');
		
		cache.len = cache.$tab_header_items.length;
		//console.log(cache.len);
		cache.last_active_i = 0;
		cache.active_i = 0;
		cache.actived = [];

		for( var i = 0; i < cache.len; i++){
			cache.$tab_header_items[i].setAttribute('data-i',i);
			cache.$tab_header_items[i].addEventListener(('ontouchstart' in document.documentElement ? 'touchstart' : 'mouseover'),event_tab_header_hover);
		}

		/** show tab */
		cache.$tab_loading.style.display = 'none';
		cache.$tab.style.display = 'block';

		/** show last tab */
		last_tab_init();

		/** nav init */
		tab_nav_init();

		/** nav fixed init */
		tab_nav_fixed_init();
	}
	
	function event_tab_header_hover(e){
		e.preventDefault();
		e.stopPropagation();
		
		cache.active_i = this.getAttribute('data-i');
		
		if(cache.last_active_i == cache.active_i)
			return false;
			
		/** hide last tab */
		action_tab_hide(cache.last_active_i);
		
		/** show current tab */
		action_tab_show(cache.active_i);

		/** hide last tab nav */
		action_tab_nav_hide(cache.last_active_i);

		/** show current tab nav */
		action_tab_nav_show(cache.active_i);

		/** init tab nav scroll */
		tab_nav_scroll_init();
		
		/** set last tab */
		cache.last_active_i = cache.active_i;
		
		/** set last tab to localStorage */
		localStorage.setItem('backend-tab-last-active',cache.active_i);
	}
	function last_tab_init(){
		cache.last_active_i = parseInt(localStorage.getItem('plugin-backend-tab-last-active'));
		if(!cache.last_active_i || cache.last_active_i > cache.len)
			cache.last_active_i = 0;
		cache.active_i = cache.last_active_i;
		action_tab_show(cache.last_active_i);
	}
	function action_tab_show(i){
		cache.$tab_header_items[i].classList.add('active');
		cache.$tab_body_items[i].classList.add('active');
	}
	function action_tab_hide(i){
		cache.$tab_header_items[i].classList.remove('active');
		cache.$tab_body_items[i].classList.remove('active');
	}
	/**
	 * nav
	 */
	function tab_nav_init(){
		cache.$nav_container = document.createElement('div');
		cache.$nav_container.className = 'tab-nav-container';
		cache.$tab_body.insertBefore(cache.$nav_container,cache.$tab_body.firstChild);

		cache.admin_bar_height = 32;
		cache.legend_tops = [];
		cache.$tab_nav = [];
		cache.$nav_items = [];
		cache.$tab_legends = [];
		
		for(var i = 0; i < cache.len; i++){
			nav_item_create(i);
		}
		/** show tab nav */
		action_tab_nav_show(cache.last_active_i);
	}
	function nav_item_create(i){
		cache.$tab_nav[i] = document.createElement('nav');
		cache.$tab_nav[i].className = 'tab-nav';

		cache.$nav_items[i] = {};
		cache.$tab_legends[i] = cache.$tab_body_items[i].querySelectorAll('legend');

		cache.last_tab_nav_active_i = 0;
		
		if(!cache.$tab_legends[i][0])
			return false;
			
		for(var j = 0, len = cache.$tab_legends[i].length; j < len; j++){
			/** get legend title */
			var title = cache.$tab_legends[i][j].innerHTML,
				text = cache.$tab_legends[i][j].textContent;
				/** create nav item */
				if(!cache.$nav_items[i])
					cache.$nav_items[i] = [];
				cache.$nav_items[i][j] = document.createElement('span');
				
			/** scroll to top */
			cache.$tab_legends[i][j].addEventListener('click',function(){
				scrollTo(0,0);
			});
			
			/** set legend id */
			cache.$tab_legends[i][j].id = encodeURI(text);
			
			/** set data */
			cache.$nav_items[i][j].setAttribute('data-hash',encodeURI(text));
			cache.$nav_items[i][j].setAttribute('data-i',i);
			cache.$nav_items[i][j].setAttribute('data-j',j);
			cache.$nav_items[i][j].innerHTML = title;
			
			/** bind click */
			cache.$nav_items[i][j].addEventListener('click',event_nav_item_click);
			
			/** append */
			cache.$tab_nav[i].appendChild(cache.$nav_items[i][j]);
		}
		cache.$nav_container.appendChild(cache.$tab_nav[i]);
	}
	function event_nav_item_click(e){
		e.preventDefault();
		var $legend = cache.$tab_legends[this.getAttribute('data-i')][this.getAttribute('data-j')],
			$parent = $legend.parentNode;
			
		scrollTo(0,get_ele_top($legend) - cache.admin_bar_height);
		
		history.pushState(null, null, '#' + this.getAttribute('data-hash'));
		
		$parent.classList.add('active');
		setTimeout(function(){
			$parent.classList.remove('active');
		},2000);
	}
	function action_tab_nav_show(i){
		cache.$tab_nav[i].classList.add('active');
	}
	function action_tab_nav_hide(i){
		cache.$tab_nav[i].classList.remove('active');
	}
	function tab_nav_fixed_init(){
		cache.nav_ori_top = get_ele_top(cache.$nav_container) - cache.admin_bar_height;
		cache.is_fixed = false;
		
		tab_nav_scroll_init();
	}
	function tab_nav_scroll_init(){
		if(cache.actived.indexOf(cache.active_i) !== -1)
			return;

		/** set first active */
		cache.$nav_items[cache.active_i][0].classList.add('active');
		
		/** set offset top */
		for(var j = 0, len = cache.$tab_legends[cache.active_i].length; j < len; j++){
			if(!cache.legend_tops[cache.active_i])
				cache.legend_tops[cache.active_i] = [];
			cache.legend_tops[cache.active_i][j] = parseInt(get_ele_top(cache.$tab_legends[cache.active_i][j]));
		}
		window_scroll(function(scroll_y){
			event_tab_nav_fixed(scroll_y);
			event_legends_scroll(scroll_y);
		});
		
		/** set actived */
		cache.actived.push(cache.active_i);
	}
	function event_legends_scroll(wot){

		var len = cache.legend_tops[cache.active_i].length;
		for(var i=0; i<len; i++){
			if((wot >= cache.legend_tops[cache.active_i][i] - cache.admin_bar_height*2) && (wot < cache.legend_tops[cache.active_i][i + 1])){
				
				if(cache.tab_nav_last_active_i !== i){
					for(var j = 0; j < len; j++){
						cache.$nav_items[cache.active_i][j].classList.remove('active');
					}
					cache.$nav_items[cache.active_i][i].classList.add('active');
					cache.tab_nav_last_active_i = i;
				}
			}
		}
	}
	function event_tab_nav_fixed(y){
		if(y >= cache.nav_ori_top){
			if(!cache.is_fixed){
				cache.$tab_nav[cache.active_i].style.top = cache.admin_bar_height + 'px';
				cache.$tab_nav[cache.active_i].style.position = 'fixed';
				cache.is_fixed = true;
			}
		}else{
			if(cache.is_fixed){
				cache.$tab_nav[cache.active_i].style.top = 0;
				cache.$tab_nav[cache.active_i].style.position = 'relative';
				cache.is_fixed = false;
			}
		}
	}
	function I(e){
		return document.getElementById(e);
	}
}