var status_tip = require('modules/status-tip');
var ready = require('modules/ready');
var array_merge = require('modules/array-merge');

module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_mailer)
		return;
		
	var cache = {},
		config = {
			process_url : ''
		};

	config = array_merge(config, window.THEME_CONFIG.theme_mailer);
	
	ready(bind);		

	function bind(){
		cache.$test_btn = I('theme_mailer-test-btn');
		cache.$test_mail = I('theme_mailer-test-mail');
		cache.$area = I('theme_mailer-area-btn');
		cache.$tip = I('theme_mailer-tip');

		cache.$from = I('theme_mailer-From');
		cache.$from_name = I('theme_mailer-FromName');
		cache.$host = I('theme_mailer-Host');
		cache.$port = I('theme_mailer-Port');
		cache.$secure = I('theme_mailer-SMTPSecure');
		cache.$username = I('theme_mailer-Username');
		cache.$pwd = I('theme_mailer-Password');
		

		if(!cache.$test_btn || !cache.$test_mail || !cache.$tip || !cache.$area)
			return false;

		cache.$test_btn.addEventListener('click', send_mail, false);
		
	}
	function send_mail(){
		if(cache.$test_mail.value === ''){
			cache.$test_mail.focus();
			return false;
		}else if(cache.$from.value === ''){
			cache.$from.focus();
			return false;
		}else if(cache.$from_name.value === ''){
			cache.$from_name.focus();
			return false;
		}else if(cache.$host.value === ''){
			cache.$host.focus();
			return false;
		}else if(cache.$port.value === ''){
			cache.$port.focus();
			return false;
		}else if(cache.$secure.value === ''){
			cache.$secure.focus();
			return false;
		}else if(cache.$username.value === ''){
			cache.$username.focus();
			return false;
		}else if(cache.$pwd.value === ''){
			cache.$pwd.focus();
			return false;
		}
		
		tip('loading',window.THEME_CONFIG.lang.M01);
		var xhr = new XMLHttpRequest(),
			fd = new FormData();

		fd.append('From',cache.$from.value);
		fd.append('FromName',cache.$from_name.value);
		fd.append('Host',cache.$host.value);
		fd.append('Port',cache.$port.value);
		fd.append('SMTPSecure',cache.$secure.value);
		fd.append('Username',cache.$username.value);
		fd.append('Password',cache.$pwd.value);
		fd.append('test',cache.$test_mail.value);
		
		xhr.open('post',config.process_url);
		xhr.send(fd);
		xhr.onload = function(){
			if (xhr.status >= 200 && xhr.status < 400) {
				var data;
				try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
				if(data && data.status){
					tip(data.status,data.msg);
				}else{
					tip('error',data);
				}
			}else{
				tip('error',xhr.responseText);
			}
			
		};
		xhr.onerror = function(){
			tip('error',window.THEME_CONFIG.lang.E01);
		};
	}
	function tip(t,s){
		if(t === 'hide'){
			cache.$area.style.display = 'block';
			cache.$tip.style.display = 'none';
			return false;
		}
		cache.$tip.innerHTML = status_tip(t,s);
		cache.$tip.style.display = 'block';
		if(t === 'loading'){
			cache.$area.style.display = 'none';
		}else{
			cache.$area.style.display = 'block';
		}
	}
	function I(e){
		return document.getElementById(e);
	}
}