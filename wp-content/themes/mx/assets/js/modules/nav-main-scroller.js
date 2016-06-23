var window_scroll = require('modules/window-scroll');
var ready = require('modules/ready');

module.exports = function(){
	'use strict';
	var cache = {};
	
	ready(bind)

	function bind(){
		cache.$menu = document.querySelector('.nav-main');
		if(!cache.$menu)
			return;
		cache.menu_height = cache.$menu.offsetHeight;
		cache.y = 0;
		cache.fold = false;
		/** first init */
		event_win_scroll(window.pageYOffset);
		
		window_scroll(event_win_scroll);
	}

	function hide(){
		if( !cache.fold ){
			cache.$menu.classList.add('fold');
			cache.$menu.classList.remove('top')
			cache.fold = true;
		}
	}
	function show(){
		if( cache.fold ){
			cache.$menu.classList.remove('fold');
			cache.fold = false;
		}
	}
	function event_win_scroll(scroll_y){
		if(scroll_y <= cache.menu_height){
			show();
			cache.$menu.classList.add('top');
		/**
		 * scroll down
		 */
		}else if( cache.y <= scroll_y ){
			hide();
		/**
		 * scroll up and delay show
		 */
		}else if( cache.y - scroll_y < 100 ) {
			show();
		}
		cache.y = scroll_y;
	}
}