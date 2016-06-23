var click_handle = require('modules/click-handle');
var paseHTML = require('modules/parse-html');

module.exports = function(){
	this.$add = false;
	this.$container = false;
	this.new_tpl_callback = false;

	var that = this,
		cache = {};
		
		
	this.init = function(){
		if(!that.$add || !that.$container)
			return;
			
		cache.$items = that.$container.querySelectorAll('.tpl-item');
		cache.$dels = that.$container.querySelectorAll('.del');
		/** bind add */
		bind_click_add();
		
		if(!cache.$items[0])
			return;

		bind_items();
	};
	function bind_items(){
		for(var i = 0, len = cache.$items.length; i < len; i++){
			/** add callback */
			if(typeof(that.new_tpl_callback) == 'function')
				that.new_tpl_callback(cache.$items[i]);
			/** bind delete */
			bind_click_del(cache.$dels[i]);
		}
	}
	function bind_click_del($del){
		$del.addEventListener(click_handle, event_click_del);
	}
	function event_click_del(e){
		e.preventDefault();
		var target_id = this.getAttribute('data-target'),
			$target = document.getElementById(target_id);
		if(window.jQuery){
			var $t = jQuery($target);
			$t.fadeOut('slow',function(){
				$t.remove();
			}).css({
				'border':'2px solid #d54e21'
			});
		}else{
			$target.style.borderWidth = '2px';
			$target.style.borderColor = '#d54e2';
			setTimeout(function(){
				$target.parentNode.removeChild($target);
			}, 1000);
		}
	}
	function bind_click_add(){
		that.$add.addEventListener(click_handle, event_click_add);
	}
	function event_click_add(e){
		e.preventDefault();
		var tpl = that.$container.getAttribute('data-tpl').replace(/\%placeholder\%/ig, +new Date()),
			$new_item = paseHTML(tpl.trim());
		/** bind del */
		bind_click_del($new_item.querySelector('.del'));
		
		/** add callback */
		if(typeof(that.new_tpl_callback) == 'function')
			that.new_tpl_callback($new_item);
			
		that.$container.appendChild($new_item);
		/** focus first input */
		var $first_input = $new_item.querySelector('input');
		if($first_input)
			$first_input.focus();
	}
}