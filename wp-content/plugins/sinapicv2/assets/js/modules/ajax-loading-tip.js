/**
 * ajax_loading_tip
 *
 * @param string t Message type. success/error/info/loading...
 * @param string s Message
 * @param int Timeout to hide(second)
 * @version 1.0.2
 */
var status_tip = require('modules/status-tip'); 
var click_handle = require('modules/click-handle');
var cache = {};
var doc = document;
module.exports = function(t,s,timeout){
		
	if(!cache)
		cache = {};

	if(!cache.si)
		cache.si = false;
		
	/** if first */
	if(!cache.$t_container){
		cache.$c = doc.createElement('i');
		cache.$c.setAttribute('class','btn-close fa fa-times fa-fw');
		
		cache.$t_container = doc.createElement('div');
		cache.$t_container.id = 'ajax-loading-container';
		
		cache.$t = doc.createElement('div');
		cache.$t.id = 'ajax-loading';
		
		cache.$t_container.appendChild(cache.$t)
		cache.$t_container.appendChild(cache.$c);
		doc.body.appendChild(cache.$t_container);
		
		cache.$c.addEventListener(click_handle,function(){
			action_close();
			clearInterval(cache.si);
		});
	}
		
	clearInterval(cache.si);
	if(timeout > 0){
		set_close_time(timeout);
		cache.si = setInterval(function(){
			timeout--;
			set_close_time(timeout);
			if(timeout <= 0){
				action_close();
				cache.$c.innerHTML = '';
				if(cache.si)
					clearInterval(cache.si);
			}
		},1000);
	}else{
		cache.$c.innerHTML = '';
	}
	/** hide */
	if(t === 'hide'){
		action_close();
	/** show */
	}else{
		setTimeout(function(){
			cache.$t_container.className = t + ' show';
		},1);
		cache.$t.innerHTML = status_tip(t,s);
	}
	function set_close_time(t){
		cache.$c.innerHTML = '<b class="number">' + t + '</b>';
	}
	function action_close(){
		cache.$t_container.classList.remove('show');
	}
};