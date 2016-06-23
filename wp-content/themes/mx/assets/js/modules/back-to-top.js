var scroll_to = require('modules/scroll-to');
var click_handle = require('modules/click-handle');
var ready = require('modules/ready');
module.exports = function(){
	'use strict';
	init();
	function init(){
		ready(function(){
			var $back = document.getElementById('back-to-top');
			if(!$back)
				return;
				
			$back.addEventListener(click_handle, function(e){
				e.preventDefault();
				scroll_to(0);
			});
		})
	}
}