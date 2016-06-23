var ready = require('modules/ready');

module.exports = function(){
	ready(init);
	function init(){
		var $inputs = document.querySelectorAll('.text-select');
		if(!$inputs[0])
			return false;
		for(var i = 0, len = $inputs.length; i < len; i++){
			$inputs[i].addEventListener('click',function(){
				this.select();
			})
		}
	}
}