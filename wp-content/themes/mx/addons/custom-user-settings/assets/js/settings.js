var ready = require('modules/ready');
var validate = require('modules/validate');
var array_merge = require('modules/array-merge');
module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_custom_user_settings)
		return;
		
	var cache = {},
		config = {
			process_url : '',
		};

	config = array_merge(config, window.THEME_CONFIG.theme_custom_user_settings);
	
	function init(){
		ready(bind);
	}
	function bind(){
		cache.$fm = document.querySelector('.user-form');
		if(!cache.$fm)
			return;
			
		fm_validate(cache.$fm);
	}
	function fm_validate($fm){
		var m = new validate();
			m.process_url = config.process_url;
			m.loading_tx = window.THEME_CONFIG.lang.M01;
			m.error_tx = window.THEME_CONFIG.lang.E01;
			m.$fm = $fm;
			m.init();
	}

	init();
}