var ready = require('modules/ready');
module.exports = function(){
	init();
	function init(){
		ready(bind);
	}
	function bind(){
		var $no_js = document.querySelectorAll('.hide-no-js'),
			$on_js = document.querySelectorAll('.hide-on-js');
		if($no_js[0]){
			for( var i = 0, len = $no_js.length; i < len; i++)
				$no_js[i].style.display = 'none';
		}
		if($on_js[0]){
			for( var i = 0, len = $on_js.length; i < len; i++)
				$on_js[i].style.display = 'none';
		}
	}
}