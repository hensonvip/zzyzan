var ready = require('modules/ready');
module.exports = function(){
	'use strict';

	ready(bdjs);
	
	function bdjs(){
		var $bdboxes = document.querySelectorAll('.bdsharebuttonbox');
		if(!$bdboxes[0])
			return false;
			
		var _bd_share_config = {
			common: {
				bdSnsKey: {},
				dText: false,
				bdMiniList: false,
				bdMini: 2,
				bdPic: false,
				bdStyle: false,
				bdSize: 16
			},
			share: [],
			//image: {},
			selectShare: false
		};
			
		for(var i = 0, len = $bdboxes.length; i < len; i++){
			var tar_id = 'bdshare_tag_' + i,
				share_json = JSON.parse($bdboxes[i].getAttribute('data-bdshare').replace(/\'/g,'"'));
			share_json.bdSign = 'off';
			share_json.tag = tar_id;
			$bdboxes[i].setAttribute('data-tag',tar_id);
			_bd_share_config.share.push(share_json);
			
		};
 		window._bd_share_config = _bd_share_config;
		setTimeout(function(){
			var $js = document.createElement('script');
			$js.src = 'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion=' + ~ (-new Date() / 36e5);		
			$js.async = true;
			document.getElementsByTagName('head')[0].appendChild($js);
		},5000);
	}
}