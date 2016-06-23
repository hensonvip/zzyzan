var ready = require('modules/ready');
var array_merge = require('modules/array-merge');
module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_custom_slidebox)
		return;
		
	var cache = {},
		config = {
			type : 'candy'
		};

	config = array_merge(config, window.THEME_CONFIG.theme_custom_slidebox);
		
	function bind(){
		cache.$container = document.querySelector('.theme_custom_slidebox-container');
		if(!cache.$container)
			return;
			
		cache.$main = cache.$container.querySelector('.area-main');
		cache.$blurs = cache.$container.querySelectorAll('.area-blur .item');
		
		eval(config.type + '();');
	}
	/**
	 * scroller
	 */
	function scroller(){
		var moving = false,
			last_x;
		
		function mousemove(e){
			if(!moving)
				moving = true;
				
			if(!last_x)
				last_x = e.clientX;

			if(cache.$main.scrollLeft >= 0)
				cache.$main.scrollLeft = cache.$main.scrollLeft - (last_x - e.clientX);
			
			last_x = e.clientX;
		}
		function mouseout(e){
			if(moving)
				moving = false;

			if(!e.target.width)
				last_x = 0;
		}
		
		cache.$main.addEventListener('mouseout', mouseout);
		cache.$main.addEventListener('mousemove', mousemove);
		
		/**
		 * blur
		 */
		blur();
		function blur(){
			cache.$as = cache.$main.querySelectorAll('a');
			cache.current_i = 0;
			cache.len = cache.$as.length;
			function event_hover(e){
				var current_i = this.getAttribute('data-i');
				if(cache.current_i == current_i)
					return false;
				cache.current_i = current_i;
				for(var i = 0; i < cache.len; i++){
					//console.log(i);
					cache.$blurs[i].classList.contains('active') && cache.$blurs[i].classList.remove('active');
					
					cache.$as[i].classList.contains('active') && cache.$as[i].classList.remove('active');
				}
				this.classList.add('active');
				cache.$blurs[current_i].classList.add('active');
			}
			for(var i = 0; i < cache.len; i++){
				cache.$as[i].setAttribute('data-i',i);
				cache.$as[i].addEventListener('mouseover', event_hover);
			}
		}
	}
	/**
	 * candy
	 */
	function candy(){
		cache.$mains = cache.$container.querySelectorAll('.area-main .item');
		cache.$thumbnails = cache.$container.querySelectorAll('.area-thumbnail .item');
		
		cache.len = cache.$thumbnails.length;
		cache.current_i = 0;
		
		function event_hover(e){
			var current_i = this.getAttribute('data-i');
			if(cache.current_i == current_i)
				return false;
			cache.current_i = current_i;
			for(var i = 0; i < cache.len; i++){
				cache.$blurs[i].classList.contains('active') && cache.$blurs[i].classList.remove('active');
				
				cache.$mains[i].classList.contains('active') && cache.$mains[i].classList.remove('active');
				
				cache.$thumbnails[i].classList.contains('active') && cache.$thumbnails[i].classList.remove('active');
			}
			this.classList.add('active');
			cache.$blurs[current_i].classList.add('active');
			cache.$mains[current_i].classList.add('active');
		}
		for(var i = 0; i < cache.len; i++){
			cache.$thumbnails[i].setAttribute('data-i',i);
			cache.$thumbnails[i].addEventListener('mouseover', event_hover);
		}
	}

	ready(bind);
}