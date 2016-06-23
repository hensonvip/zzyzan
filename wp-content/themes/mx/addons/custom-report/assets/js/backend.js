var ready = require('modules/ready');
var tpl_control = require('modules/tpl-control');

module.exports = function(){
	ready(bind);
	function bind(){
		var ootpl = new tpl_control();
		ootpl.$add = document.getElementById('theme_custom_report-add');
		ootpl.$container = document.getElementById('theme_custom_report-container');
		ootpl.init();
	}
}