var ready = require('modules/ready');
module.exports = function(){
	'use strict';

	ready(bind);
	
	var cache = {};
	
	function bind(){
		cache.$thumbnail_container = document.querySelector('.attachment-slide-thumbnail');
		if(!cache.$thumbnail_container)
			return false;
			
		cache.$thumbnails = cache.$thumbnail_container.querySelectorAll('a');
		if(cache.$thumbnails.length <= 3)
			return false;

		cache.$thumbnail_active = cache.$thumbnail_container.querySelector('a.active');
		
		/** scroll it */
		cache.$thumbnail_container.scrollLeft = cache.$thumbnail_active.offsetLeft - 100;
	}
}