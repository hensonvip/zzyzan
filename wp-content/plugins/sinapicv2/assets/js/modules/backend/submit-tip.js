var ajax_loading_tip = require('modules/ajax-loading-tip');
var ready = require('modules/ready');
module.exports = function(){

	var cache = {},
		config = {
			lang : {
				M01 : 'Saving your settings, please wait...',
				M02 : 'Your settings have been saved.'
			}
		};

	ready(function(){
		submit();
	});

	function submit(){
		cache.fm = document.getElementById('backend-options-fm');
		if(!cache.fm)
			return;
		cache.fm.addEventListener('submit',function(){
			ajax_loading_tip('loading',config.lang.M01);
			return true;
		});
	}
}