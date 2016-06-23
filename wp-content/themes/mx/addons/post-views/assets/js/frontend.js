var ready = require('modules/ready');

module.exports = function(){
	'use strict';
	
	if(!window.THEME_CONFIG.theme_post_views)
		return;
		
	function set_views(){
		if(window.DYNAMIC_REQUEST && window.DYNAMIC_REQUEST.theme_post_views){
			for(var k in window.DYNAMIC_REQUEST.theme_post_views){
				var $view = document.getElementById('post-views-number-' + k);
				if($view)
					$view.innerHTML = window.DYNAMIC_REQUEST.theme_post_views[k];
			}
		}
	}
	ready(set_views);
}