var ready = require('modules/ready');

module.exports = function(){
	'use strict';
	
	var selects = document.querySelectorAll('.archive-pagination select');
	if(!selects[0])
		return;
		
	function event_change(e){
		if(this.value)
			location.href = this.value;
	}
	function init(){
		for(var i = 0, len = selects.length; i < len; i++){
			selects[i].addEventListener('change',event_change);
		}
	}
	ready(init);
}