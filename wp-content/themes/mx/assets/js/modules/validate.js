/**
 * validate
 *
 * @return object
 * @version 1.0.1
 */
var ajax_loading_tip = require('./ajax-loading-tip');
module.exports = function(){
	/** config */
	this.process_url = false;
	this.loading_tx = false;
	this.error_tx = 'Sorry, the server is busy, please try again later.';
	this.$fm = false;
	this.done_close = false;

	this.done = function(){};
	this.before = function(){};
	this.always = function(){};
	this.fail = function(){};
	
	var that = this,
		cache = {};
	this.init = function(){
		that.$fm.addEventListener('submit',ajax.init,false);
	};
	
	var ajax = {
		init : function(){
			
			if(!cache.$submit){
				cache.$submit = that.$fm.querySelector('.submit');
				cache.submit_ori_tx = cache.$submit.innerHTML;
				cache.submit_loading_tx = that.loading_tx ? that.loading_tx : cache.$submit.getAttribute('data-loading-text');
			}
			cache.$submit.innerHTML = cache.submit_loading_tx;
			
			cache.$submit.setAttribute('disabled',true);
			ajax_loading_tip('loading',cache.submit_loading_tx);

			var xhr = new XMLHttpRequest();
			fd = new FormData(that.$fm);
			fd.append('theme-nonce', window.DYNAMIC_REQUEST['theme-nonce']);
			
			/** callback before */
			that.before(fd);
			
			xhr.open('POST',that.process_url);
			xhr.send(fd);
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
					
					if(data && data.status){
						if(data.status === 'success'){
							if(that.done_close){
								ajax_loading_tip(data.status,data.msg,that.done_close);
							}else{
								ajax_loading_tip(data.status,data.msg);
							}
						}else if(data.status === 'error'){
							ajax_loading_tip(data.status,data.msg);
							if(data.code && data.code.indexOf('pwd') !== -1){
								var $pwd = that.$fm.querySelector('input[type=password]');
								$pwd && $pwd.select();
							}else if(data.code && data.code.indexOf('email') !== -1){
								var $email = that.$fm.querySelector('input[type=email]');
								$email && $email.select();
							}
							cache.$submit.removeAttribute('disabled');
						}
						cache.$submit.innerHTML = cache.submit_ori_tx;
						that.done(data);
					}else{
						ajax_loading_tip('error',that.error_tx);
						cache.$submit.removeAttribute('disabled');
						cache.$submit.innerHTML = cache.submit_ori_tx;
						that.fail(data);
					}
				that.always(data);
				}
			};/** onload */

			xhr.onerror = function(){
				ajax_loading_tip('error',that.error_tx);
				cache.$submit.removeAttribute('disabled');
				cache.$submit.innerHTML = cache.submit_ori_tx;
				that.fail();
			}
		}
	};
	return this;
};