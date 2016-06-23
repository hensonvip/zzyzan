var ready = require('modules/ready');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var parseHTML = require('modules/parse-html');
var param = require('modules/parse-obj-url');
var array_merge = require('modules/array-merge');

module.exports = function(){
	'use strict';
	if(!window.THEME_CONFIG.theme_custom_pm)
		return;
		
	var config = {
		lang : {
			M01 : 'Loading, please wait...',
			M02 : 'Enter to send P.M.',
			M03 : 'P.M. content',
			M04 : 'Send P.M.',
			M05 : 'Hello, I am %name%, welcome to chat with me what do you want.',
			M06 : 'P.M. is sending, please wait...',
			E01 : 'Sorry, server is busy now, can not respond your request, please try again later.'
		},
		uid : 'new',
		my_uid : '',
		userdata : {}
		
	},
	cache = {};
	
	config = array_merge(config,window.THEME_CONFIG.theme_custom_pm);
	
	/** init */
	init();
	function init(){
		ready(function(){
			tab_bind();
			if(!cache.$tabs_container)
				return false;
			new_tab_bind();
			comet();
			preset_receiver_bind();
		});
	};
	
	function tab_bind(){
		cache.$tabs_container = I('pm-tab');
		if(!cache.$tabs_container)
			return false;
		cache.$dialogs_container = document.querySelector('.pm-dialog-container');
		cache.$tmp_dialogs = document.querySelectorAll('.pm-dialog');
		cache.$tmp_tabs = cache.$tabs_container.querySelectorAll('a');
		cache.$dialog_new = I('pm-dialog-new');
		cache.$dialog_new_uid = I('pm-dialog-content-new');
		cache.$tabs = {};
		cache.$dialogs = {};


		/** hide loading tip */
		var $tip = I('pm-loading-tip');
		$tip.parentNode.removeChild($tip);
		
		/** show container */
		I('pm-container').style.display = 'block';
		
		cache.tab_count = cache.$tmp_tabs.length;
		
		for(var i=0; i<cache.tab_count; i++){
			var uid = cache.$tmp_tabs[i].getAttribute('data-uid'),
				$close = cache.$tmp_tabs[i].querySelector('.close');
			cache.$tabs[uid] = cache.$tmp_tabs[i];
			cache.$dialogs[uid] = cache.$tmp_dialogs[i];
			/** set user data */
			if(uid !== 'new'){
				config.userdata[uid] = {
					name : cache.$tabs[uid].querySelector('.author').innerHTML,
					avatar : cache.$tabs[uid].querySelector('img').src,
					url : cache.$tabs[uid].getAttribute('data-url')
				};
			}
			/** scroll bottom */
			scroll_dialog_bottom(uid);
			
			/** bind tab event */
			event_switch_tab(uid);
			
			/** bind close click */
			if($close)
				$close.addEventListener('click',event_close_click);

			/** bind msg submit */
			if(uid !== 'new')
				cache.$dialogs[uid].addEventListener('submit',event_submit_send_pm);
		}
		for(var i=0; i<cache.tab_count; i++){
			var uid = cache.$tmp_tabs[i].getAttribute('data-uid');
			/** set current tab */
			if(uid === config.uid){
				tab_toggle(uid);
				focus_content(uid);
				cache.$current_tab = cache.$tabs[uid];
			}
		}
	}
	function preset_receiver_bind(){
		/** check preset receiver */
		cache.preset_uid = get_hash_uid();
		if(!cache.preset_uid)
			return;
			
		if(!cache.$tabs[cache.preset_uid]){
			get_uid_from_server(cache.preset_uid);
		}else{
			tab_switch_it(cache.preset_uid);
		}
		
	}
	function get_hash_uid(){
		return location.hash && parseInt(location.hash.replace('#',''));
	}
	function event_switch_tab(uid){
		function helper(e){
			e.preventDefault();
			e.stopPropagation();
			tab_toggle(uid);
			focus_content(uid);
			show_new_msg(uid,'hide');
		}
		cache.$tabs[uid].addEventListener('click',helper);
	}
	function scroll_dialog_bottom(uid){
		var $list = cache.$dialogs[uid].querySelector('.pm-dialog-list');
		if($list)
			$list.scrollTop = $list.scrollHeight;
	}
	function is_current_tab(uid){
		return cache.$current_tab.getAttribute('uid') === uid;
	}
	function focus_content(uid){
		//console.log(I('pm-dialog-content-' + uid));
		I('pm-dialog-content-' + uid).focus();
	}
	function tab_toggle(uid){
		/** set current uid */
		config.uid = uid;
		for(var i in cache.$tabs){
			/** if current tab */
			//console.log(i , uid);
			if(i == uid){
				//console.log(cache.$tabs[uid]);
				/** display target dialog */
				cache.$dialogs[uid].style.display = 'block';
				/** add class for target tab */
				cache.$tabs[uid].classList.add('active');
				continue;
			}
			/** remove class for old tab */
			cache.$tabs[i].classList.remove('active');
			/** add class for dialog */
			cache.$dialogs[i].style.display = 'none';
		}
	}
	function tab_switch_it(uid){
		/** define tab nav */
		if(!cache.$tabs[uid]){
			cache.$tabs[uid] = I('pm-tab-' + uid);
		}
		/** if not current tab, hide others tab but the target tab */
		if(!is_current_tab(uid)){
			tab_toggle(uid)
		}
			
		/** focus */
		focus_content(uid);
	
	}
	function new_tab_bind(){
		cache.$dialog_new.addEventListener('submit', event_submit_new_tab);
	}
	function event_submit_new_tab(){
		var uid = cache.$dialog_new_uid.value;
		
		/** check uid is cache */
		if(cache.$dialogs[uid]){
			tab_switch_it(uid);
			return false;
		}
		get_uid_from_server(uid);
		
	}
	function create_tab(uid){
		cache.$tabs[uid] = parseHTML(get_tpl_tab(uid));
		cache.$tabs_container.appendChild(cache.$tabs[uid]);
		cache.$tmp_tabs = cache.$tabs_container.querySelectorAll('a');
	}
	function create_close(uid){
		var $close = parseHTML(get_tpl_close());
		$close.addEventListener('click',event_close_click);
		cache.$tabs[uid].appendChild($close);
	}
	function create_dialog(uid,msg){
		cache.$dialogs[uid] = parseHTML(get_tpl_dialog(uid,msg));
		cache.$dialogs_container.appendChild(cache.$dialogs[uid]);
	}
	function get_uid_from_server(uid){
		ajax_loading_tip('loading',config.lang.M01);
		
		var xhr = new XMLHttpRequest();
		xhr.open('get',config.process_url + '&type=get-userdata&uid=' + uid + '&theme-nonce=' + window.DYNAMIC_REQUEST['theme-nonce']);
		xhr.send();
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				try{data=JSON.parse(xhr.responseText)}catch(err){data=xhr.responseText}
				done(data);
			}else{
				fail();
			}
		};
		xhr.onerror = function(){
			ajax_loading_tip('error',config.lang.E01);
			cache.$dialog_new_uid.select();
		};
		function done(data){
			if(data.status === 'success'){
				/** set userdata cache */
				config.userdata[uid] = {
					avatar : data.avatar,
					name : data.name,
					url : data.url
				};
				/** tip */
				ajax_loading_tip(data.status,data.msg,3);
				
				/** clear new uid value */
				//cache.$dialog_new_uid.value = '';
				
				/** create tab */
				create_tab(uid);

				/** create close */
				create_close(uid);
				
				/** create init content */
				if(!data.histories){
					/** create dialog */
					//console.log(get_tpl_msg(uid,config.lang.M05.replace('%name%', config.userdata[uid].name)));
					create_dialog(uid,get_tpl_msg(uid,config.lang.M05.replace('%name%', config.userdata[uid].name)));
				}else{
					/** create dialog */
					create_dialog(uid,get_histories(data.histories));
				}
				
				/** set current tab */
				cache.$current_tab = cache.$tabs[uid];
				
				/** update count */
				cache.tab_count++;
				
				/** add click event */
				event_switch_tab(uid);

				/** switch new tab */
				tab_switch_it(uid);

				/** bind submit event */
				cache.$dialogs[uid].addEventListener('submit',event_submit_send_pm);
				
			}else if(data.status === 'error'){
				ajax_loading_tip(data.status,data.msg,3);
				cache.$dialog_new_uid.select();
			}else{
				ajax_loading_tip('error',data);
				cache.$dialog_new_uid.select();
			}
		}
	}
	function event_submit_send_pm(e){
		e.preventDefault();
		
		/** set disabled */
		var $submit = cache.$dialogs[config.uid].querySelector('button[type="submit"]');
		$submit.setAttribute('disabled',true);
		
		/** tip */
		ajax_loading_tip('loading',config.lang.M06);

		/** ajax start */
		var xhr = new XMLHttpRequest(),
			fd = new FormData(this);
		fd.append('type','send');
		fd.append('theme-nonce',window.DYNAMIC_REQUEST['theme-nonce']);
		fd.append('uid',config.uid);
		xhr.open('post',config.process_url);
		xhr.send(fd);
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				try{data=JSON.parse(xhr.responseText)}catch(err){data=xhr.responseText}
				done(data);
			}else{
				fail();
			}
		};
		xhr.onerror = fail;
		function done(data){
			if(data.status && data.status === 'success'){
				ajax_loading_tip(data.status,data.msg,3);
				focus_clear_input(config.uid);
			}else if(data.status && data.status === 'error'){
				ajax_loading_tip(data.status,data.msg,5);
			}else{
				ajax_loading_tip('error',data);
			}
			focus_content(config.uid);
			
			/** remove disabled */
			$submit.removeAttribute('disabled');
		}
		
		function fail(){
			ajax_loading_tip('error',config.lang.E01);
			focus_content(config.uid);
			
			/** remove disabled */
			$submit.removeAttribute('disabled');
		}
	}
	function event_close_click(e){
		e.preventDefault();
		e.stopPropagation();
		var $parent = this.parentNode,
			uid = $parent.getAttribute('data-uid');

		if(config.uid == uid){
			/** switch to new */
			tab_switch_it('new');
			
			/** set current tab */
			cache.$current_tab = cache.$tabs['new'];
		}
		
		/** update count */
		cache.tab_count--;

		/** remove tab */
		$parent.parentNode.removeChild($parent);

		/** remove dialog */
		cache.$dialogs[uid].parentNode.removeChild(cache.$dialogs[uid]);
		
		/** delete obj */
		delete cache.$tabs[uid];
		delete cache.$dialogs[uid];
		
		/** send remove uid to server */
		var xhr = new XMLHttpRequest(),
			fd = new FormData();
		xhr.open('post',config.process_url);
		fd.append('uid',uid);
		fd.append('theme-nonce',window.DYNAMIC_REQUEST['theme-nonce']);
		fd.append('type','remove-dialog');
		xhr.send(fd);
		
	}
	function insert_dialog_msg(uid,msg){
		var target_uid = uid;
		if(uid === 'me')
			target_uid = config.uid;

		var $dialog_list = cache.$dialogs[target_uid].querySelector('.pm-dialog-list');
		$dialog_list.appendChild(parseHTML(get_tpl_msg(uid,msg)));
		$dialog_list.scrollTop = $dialog_list.scrollHeight;
	}
	function get_histories(histories){
		var content = '';
		for(var i in histories){
			content += get_tpl_msg(histories[i]);
		}
		return content;
	}
	function get_tpl_tab(uid){
		return '<a id="pm-tab-' + uid + '" href="javascript:;" data-uid="' + uid + '" title="' + config.userdata[uid].name + '">' + 
			'<img src="' + config.userdata[uid].avatar + '" alt="avatar" class="avatar" width="24" height="24"> ' + 
				'<span class="author">' + config.userdata[uid].name + '</span>' + 
		'</a>';
	}
	function get_tpl_close(){
		return '<b class="close">&times;</b>'
	}
	function get_tpl_dialog(uid, msgs){
		if(!msgs)
			msgs = '';
		return '<form action="javascript:;" id="pm-dialog-' + uid + '" class="pm-dialog">' + 
			'<div class="form-group pm-dialog-list">' + 
				msgs + 
			'</div>' + 
			'<div class="form-group">' + 
				'<input type="text" id="pm-dialog-content-' + uid + '" name="content" class="pm-dialog-conteng form-control" placeholder="' + config.lang.M02 + '" required title="' + config.lang.M03 + '">' + 
			'</div>' + 
			'<div class="form-group">' + 
				'<button class="btn btn-success btn-block" type="submit"><i class="fa fa-check"></i>&nbsp;' + config.lang.M04 + '</button>' + 
			'</div>' + 
		'</form>';
	}
	function get_tpl_msg(uid,msg){
		var d = new Date(),
			d = date_format(d,'yyyy/MM/dd hh:mm:ss'),
			sender = uid === 'me' ? 'me' : 'sender';

		return '<section class="pm-dialog-' + sender + '">' + 
			'<div class="pm-dialog-bg">' + 
				'<h4>' + 
					'<span class="name"><a href="' + config.userdata[uid].url + '" target="_blank">' + config.userdata[uid].name + '</a></span> ' + 
					'<span class="date"> ' + d + ' </span>' + 
				'</h4>' + 
				'<div class="media-content">' + msg + '</div>' + 
			'</div>' + 
		'</section>';
	}
	function comet(){
		var xhr = new XMLHttpRequest();
		if(!cache.timestamp)
			cache.timestamp = window.DYNAMIC_REQUEST['theme_custom_pm']['timestamp'];
		xhr.open('get',config.process_url + '&' + param({
			type : 'comet',
			'theme-nonce' : window.DYNAMIC_REQUEST['theme-nonce'],
			timestamp : cache.timestamp
		}));
		xhr.send();
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data;
				try{data=JSON.parse(xhr.responseText)}catch(err){data=xhr.responseText}
				done(data);
			}else{
				fail();
			}
		};
		xhr.onerror = fail;
		function done(data){
			/** have new pm */
			if(data && data.status === 'success'){
				var author_uid = data.pm.pm_author,
					receiver_uid = data.pm.pm_receiver;
				cache.timestamp = data.timestamp;
				/** author is me msg */
				if(author_uid == config.my_uid && cache.$dialogs[receiver_uid]){
					//console.log('fuck');
					/** insert msg */
					insert_dialog_msg('me',data.pm.pm_content);
					/** clear current input content */
					focus_clear_input(receiver_uid);
				/** receiver is me */
				}else{
					/** set userdata */
					if(!config.userdata[author_uid]){
						config.userdata[author_uid] = {
							name : data.pm.pm_author_name,
							avatar : data.pm.pm_author_avatar,
							url : data.pm.url
						};
					}
					//console.log(cache.$dialogs[author_uid]);
					/** if dialog is not in lists */
					if(!cache.$dialogs[author_uid]){
						/** create tab */
						create_tab(author_uid);

						/** create close */
						create_close(author_uid);

						/** create dialog */
						create_dialog(author_uid);
						cache.$dialogs[author_uid].style.display = 'none';
						
						/** highlight tab */
						show_new_msg(author_uid);
						
						/** update count */
						cache.tab_count++;
						
						/** add click event */
						event_switch_tab(author_uid);

						/** bind submit event */
						cache.$dialogs[author_uid].addEventListener('submit',event_submit_send_pm);
					}
					/** insert msg */
					insert_dialog_msg(author_uid,data.pm.pm_content);
					
					/** highlight tab */
					if(config.uid != author_uid){
						show_new_msg(author_uid);
					}
				}
				comet();
			}else if(data && data.status === 'error'){
				if(data.code === 'timeout'){
					comet();
				}
			}else{
				setTimeout(function(){
					comet();
				},30000);
			}
		}
		function fail(){
			setTimeout(function(){
				comet();
			},30000);
		}
	}
	function show_new_msg(uid,type){
		if(type === 'hide'){
			cache.$tabs[uid].classList.remove('new-msg');
		}else{
			cache.$tabs[uid].classList.add('new-msg');
		}
	}
	function focus_clear_input(uid){
		if(!cache.$inputs)
			cache.$inputs = {};
		if(!cache.$inputs[uid])
			cache.$inputs[uid] = I('pm-dialog-content-' + uid);
		
		cache.$inputs[uid].focus();
		cache.$inputs[uid].value = '';
	}
	function date_format(d,fmt) { //author: meizz 
	    var o = {
	        "M+": d.getMonth() + 1, //月份 
	        "d+": d.getDate(), //日 
	        "h+": d.getHours(), //小时 
	        "m+": d.getMinutes(), //分 
	        "s+": d.getSeconds(), //秒 
	        "q+": Math.floor((d.getMonth() + 3) / 3), //季度 
	        "S": d.getMilliseconds() //毫秒 
	    };
	    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (d.getFullYear() + "").substr(4 - RegExp.$1.length));
	    for (var k in o)
	    	if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
	    return fmt;
	}

	function I(e){
		return document.getElementById(e);
	}
}