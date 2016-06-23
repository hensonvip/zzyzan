var ready = require('modules/ready');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var array_merge = require('modules/array-merge');
var validate = require('modules/validate');

module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_point_lottery)
		return;

	var cache = {},
		config = {
			process_url : '',
			lang : {
				M01 : 'Results coming soon...'
			}
		};
		
	config = array_merge(config, window.THEME_CONFIG.theme_point_lottery);
	
	ready(bind);

	function bind(){
		cache.$hgihlight_point = I('modify-count');
		if(!cache.$hgihlight_point)
			return false;
		cache.$point_count = I('point-count');
		cache.$fm = I('fm-lottery');
		if(!cache.$fm)
			return false;
		submit();
	}
	function submit(){
		var vld = new validate();
		vld.process_url = config.process_url;
		vld.loading_tx = config.lang.M01;
		vld.error_tx = window.THEME_CONFIG.lang.E01;
		vld.$fm = cache.$fm;

		vld.done = done;
		vld.always = always;
		vld.init();
			
		function done(data){
			if(data.status === 'warning'){
				ajax_loading_tip(data.status,data.msg);
			}
			if(data.status !== 'error'){
				/** set new point */
				highlight_point(parseInt(data['new-points']) - parseInt(cache.$point_count.innerHTML));
			}
		}
		function always(){
			cache.$fm.querySelector('.submit').removeAttribute('disabled');
		}
	}
	function highlight_point(point){
		if(point > 0){
			cache.$hgihlight_point.classList.add('plus');
			cache.$hgihlight_point.innerHTML = '+' + point;
		}else{
			cache.$hgihlight_point.classList.add('mins');
			cache.$hgihlight_point.innerHTML = point;
		}
		cache.$hgihlight_point.style.display = 'inline';
		setTimeout(function(){
			cache.$hgihlight_point.classList.remove('plus');
			cache.$hgihlight_point.classList.remove('mins');
			cache.$hgihlight_point.style.display = 'none';
			cache.$point_count.innerHTML = parseInt(cache.$point_count.innerHTML) + point;
		},2000);
	}
	function I(e){
		return document.getElementById(e);
	}
}