var ready = require('modules/ready');
var paseHTML = require('modules/parse-html');
module.exports = function(){
	'use strict';

	var cache = {};

	ready(bind);

	function bind(){
		cache.$container = document.getElementById('theme_custom_storage-container');
		if(!cache.$container)
			return;

		cache.$control_container = document.getElementById('theme_custom_storage-control');

		cache.$add = cache.$control_container.querySelector('.add');
		if(!cache.$add)
			return;
			
		cache.$items = cache.$container.querySelectorAll('.item');
		cache.$dels = cache.$container.querySelectorAll('.del');
		cache.len = cache.$items.length;

		bind_add();
		if(cache.len > 0){
			for(var i = 0; i < cache.len; i++){
				/** del */
				bind_del(cache.$dels[i]);
			}
		}
	}
	function bind_add(){
		cache.$add.addEventListener('click',function(){
			var tpl = cache.$container.getAttribute('data-tpl').replace(/\%placeholder\%/g, +new Date()),
				$new_item = paseHTML(tpl);
			/** append */
			cache.$container.appendChild($new_item);
			/** bind del */
			bind_del($new_item.querySelector('.del'));
			/** focus */
			$new_item.querySelector('input').focus();
		});
	}
	function bind_del($del){
		$del.addEventListener('click', function() {
			var target_id = this.getAttribute('data-target'),
			$target = document.getElementById(target_id);
			if(window.jQuery){
				var $t = jQuery($target);
				$t.fadeOut(1,function(){
					$t.remove();
				}).css({
					'background-color':'#d54e21'
				});
			}else{
				$target.parentNode.removeChild($target);
			}
		});
	}
}