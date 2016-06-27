module.exports = function(fn){
	'use strict';
	
	var last_y = window.pageYOffset,
		ticking = false;
	function on_scroll(){
		last_y = window.pageYOffset;
		request_ticking();
	}
	function request_ticking(){
		if(!ticking){
			requestAnimationFrame(update);
			ticking = true;
		}
	}
	function update(){
		fn(last_y);
		ticking = false;
	}
	window.addEventListener('scroll',on_scroll);
};