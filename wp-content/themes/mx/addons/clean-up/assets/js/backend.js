var ready = require('modules/ready');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var array_merge = require('modules/array-merge');
module.exports = function(){
	if(!window.THEME_CONFIG.theme_clean_up)
		return;
		
	var cache = {},
		config = {
			process_url : ''
		};

	config = array_merge(config, window.THEME_CONFIG.theme_clean_up);
	
	ready(bind);
	
	function bind(){
		cache.$btns = document.querySelectorAll('.theme_clean_up-btn');
		if(!cache.$btns[0])
			return;

		for(var i = 0, len = cache.$btns.length; i < len; i++){
			cache.$btns[i].addEventListener('click',event_click);
		}
	}
	function event_click(){
		ajax_loading_tip('loading',window.THEME_CONFIG.lang.M01);
		var xhr = new XMLHttpRequest();
		xhr.open('get',config.process_url + '&type=' + this.getAttribute('data-action'));
		xhr.send();
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				try{data=JSON.parse(xhr.responseText)}catch(err){data=xhr.responseText};
				if(data && data.status){
					ajax_loading_tip(data.status,data.msg);
				}else{
					ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
				}
			}else{
				ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
			}
		};
		xhr.onerror = function(){
			ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
		};
	}
}