module.exports = function(){
	'use strict';
	if(!window.THEME_CONFIG.theme_dynamic_request)
		return;
	var d = document.createElement('script');
	d.src = window.THEME_CONFIG.theme_dynamic_request.process_url;
	document.getElementsByTagName('head')[0].appendChild(d);
	console.log(window.DYNAMIC_REQUEST);
	setTimeout(function(){
		console.log(window.DYNAMIC_REQUEST);
	},5000);
}