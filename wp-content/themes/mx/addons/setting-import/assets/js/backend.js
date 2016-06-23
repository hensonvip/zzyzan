var ajax_loading_tip = require('modules/ajax-loading-tip');
var ready = require('modules/ready');
var uploader = require('modules/uploader');
var array_merge = require('modules/array-merge');
module.exports = function(){
	'use strict';
	if(!window.THEME_CONFIG.theme_import_settings)
		return;
		
	var cache = {},
		config = {
			process_url : ''
		};

	config = array_merge(config, window.THEME_CONFIG.theme_import_settings);
	
	ready(file_import);
	
	function I(e){
		return document.getElementById(e);
	}
	function file_import(){
		cache.$file = I('theme_import_settings-file');
		if(!cache.$file)
			return false;
		
		new uploader({
			$file : cache.$file,
			url : config.process_url + '&type=import',
			onselect : onselect,
			onalways : onalways,
			onerror : onerror
		});
		
		function onselect(){
			ajax_loading_tip('loading',window.THEME_CONFIG.lang.M01);
		}
		function onalways(data){
			if(data.status === 'success'){
				ajax_loading_tip(data.status,data.msg);
				location.reload(true);
			}else if(data.status === 'error'){
				ajax_loading_tip(data.status,data.msg);
			}else{
				ajax_loading_tip('error',data);
			}
		}
		function onerror(){
			ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
		}
	}
}