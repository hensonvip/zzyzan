var ready = require('modules/ready');
var validate = require('modules/validate');
var array_merge = require('modules/array-merge');

module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_custom_sign)
		return;
	
	var cache = {},
		config = {
			fm_login_id : 'fm-sign-login',
			fm_reg_id : 'fm-sign-register',
			fm_recover_id : 'fm-sign-recover',
			fm_reset_id : 'fm-sign-reset',
			process_url : ''
		};
	config = array_merge(config, window.THEME_CONFIG.theme_custom_sign);
	
	function init(){
		ready(function(){
			sign.init();
			recover.init();
			reset.init();
		});
	};
	/** 
	 * reset
	 */
	var reset = {
		init : function(){
			cache.$fm_reset = I(config.fm_reset_id);
			if(!cache.$fm_reset)
				return false;
			var m = new validate();
				m.process_url = config.process_url;
				m.done = function(data){
					if(data && data.status === 'success'){
						location.href = data.redirect;
					}
				};
				m.loading_tx = window.THEME_CONFIG.lang.M01;
				m.error_tx = window.THEME_CONFIG.lang.E01;
				m.$fm = cache.$fm_reset;
				m.init();
		}
	};
	/** 
	 * recover
	 */
	var recover = {
		init : function(){
			cache.$fm_recover = I(config.fm_recover_id);
				
			if(!cache.$fm_recover)
				return false;
			
			var m = new validate();
				m.process_url = config.process_url;
				m.loading_tx = window.THEME_CONFIG.lang.M01;
				m.error_tx = window.THEME_CONFIG.lang.E01;
				m.$fm = cache.$fm_recover;
				m.init();
		}
	};
	var sign = {
		init : function(){
			cache.$fm_login = I(config.fm_login_id);
			cache.$fm_reg = I(config.fm_reg_id);
			if(cache.$fm_login){

				var m = new validate();
					m.process_url = config.process_url;
					m.done = function(data){
						if(data && data.status === 'success'){
							location.hash = '';
							location.reload();
						}
					};
					m.loading_tx = window.THEME_CONFIG.lang.M01;
					m.error_tx = window.THEME_CONFIG.lang.E01;
					m.$fm = cache.$fm_login;
					m.init();
			}else if(cache.$fm_reg){
				var m = new validate();
					m.process_url = config.process_url;
					m.done = function(data){
						if(data && data.status === 'success'){
							location.hash = '';
							location.reload();
						}
					};
					m.loading_tx = window.THEME_CONFIG.lang.M01;
					m.error_tx = window.THEME_CONFIG.lang.E01;
					m.$fm = cache.$fm_reg;
					m.init();
			}
		}
	};
	function I(e){
		return document.getElementById(e);
	}

	init();
}