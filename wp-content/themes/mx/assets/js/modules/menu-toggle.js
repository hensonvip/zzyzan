var click_handle = require('./click-handle');

module.exports = function(){
	var $toggles = document.querySelectorAll('a[data-toggle-target]');
	if(!$toggles[0])
		return;

	var $last_click_btn,
		$last_target;

	function show_menu(){
		var icon_active = $last_click_btn.getAttribute('data-icon-active'),
			icon_original = $last_click_btn.getAttribute('data-icon-original');
		$last_target.classList.add('on');
		
		if(icon_active && icon_original){
			$last_click_btn.classList.remove(icon_original);
			$last_click_btn.classList.add(icon_active);
		}
		var focus_target = $last_click_btn.getAttribute('data-focus-target');
		if(focus_target){
			var $focus_target = document.querySelector(focus_target);
			if($focus_target){
				setTimeout(function(){
					$focus_target.focus();
				},500);
			}
		}
	}
	function hide_menu(e){
		if(e)
			e.preventDefault();
		var icon_active = $last_click_btn.getAttribute('data-icon-active'),
			icon_original = $last_click_btn.getAttribute('data-icon-original');
			
		$last_target.classList.remove('on');
		if(icon_active && icon_original){
			$last_click_btn.classList.remove(icon_active);
			$last_click_btn.classList.add(icon_original);
		}
	}
	function event_click(e){
		$last_target = document.querySelector(this.getAttribute('data-toggle-target'));
		$last_click_btn = this;

		/** hide */
		if($last_target.classList.contains('on')){
			hide_menu();
		}else{
			show_menu();
		}
	}
	for( var i = 0, len = $toggles.length; i < len; i++){
		$toggles[i].addEventListener(click_handle, event_click);
	}
}