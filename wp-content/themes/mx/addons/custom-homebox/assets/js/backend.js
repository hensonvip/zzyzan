var ready = require('modules/ready');
var tpl_control = require('modules/tpl-control');
module.exports = function(){
	'use strict';
	
	var cache = {};

	ready(bind);
	
	function bind(){
		var controler = new tpl_control();
		controler.$container = document.getElementById('theme_custom_homebox-container');
		controler.$add = document.getElementById('theme_custom_homebox-add');
		controler.init();
	}
}