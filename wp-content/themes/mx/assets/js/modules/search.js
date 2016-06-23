module.exports = function(){
	var $btn = document.querySelector('.main-nav a.search');
		
	if(!$btn)
		return false;
		
	var $fm = document.querySelector($btn.getAttribute('data-toggle-target')),
		$input = $fm.querySelector('input[type="search"]'),
		event_submit = function(){
			if($input.value.trim() === ''){
				$input.focus();
				return false;
			}
		};
	function st(e){
		if(e)
			e.preventDefault();
		setTimeout(function(){
			$input.focus();
		},100);
	}
	$btn.addEventListener(click_handle,st);

	$fm.onsubmit = event_submit;
}