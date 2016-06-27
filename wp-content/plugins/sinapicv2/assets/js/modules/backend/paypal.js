var ready = require('modules/ready');

module.exports = function(){
	var cache = {};
	
	ready(paypal);
	
	function paypal(){
		bind_click();
		function create_form(){
			cache.$paypal_fm = document.createElement('form');
			cache.$paypal_fm.setAttribute('accept-charset','GBK');
			cache.$paypal_fm.name = '_xclick';
			cache.$paypal_fm.action = 'https://www.paypal.com/cgi-bin/webscr';
			cache.$paypal_fm.method = 'post';
			cache.$paypal_fm.target = '_blank';
			cache.$paypal_fm.style.display = 'none';
			document.body.appendChild(cache.$paypal_fm);
		}
		function create_inputs(name){
			var inputs_data = {
				'cmd' : '_donations',
				'item_name' : name,
				'amount' : '',
				'currency_code' : 'USD',
				'business' : 'kmvan.com@gmail.com',
				'lc' : 'US',
				'no_note' : '0'
			};
			for(var i in inputs_data){
				var $input = document.createElement('input');
				$input.type = 'hidden';
				$input.name = i;
				$input.value = inputs_data[i];
				cache.$paypal_fm.appendChild($input);
			}
		}
		function event_submit(){
			cache.$paypal_fm.submit();
		}
		function bind_click(){
			cache.$paypal_btn = document.getElementById('paypal_donate');
			if(!cache.$paypal_btn)
				return false;
			cache.$paypal_btn.addEventListener('click', function (e) {
				create_form();
				create_inputs(this.getAttribute('data-item-name'));
				event_submit();
			});
		}
	}
}