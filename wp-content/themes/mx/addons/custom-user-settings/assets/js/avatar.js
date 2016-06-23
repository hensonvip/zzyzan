var ready = require('modules/ready');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var array_merge = require('modules/array-merge');
module.exports = function(){
	'use strict';
	
	if(!window.THEME_CONFIG.theme_custom_user_settings)
		return;

	var cache = {},
		config = {
			process_url : ''
		};
	config = array_merge(config, window.THEME_CONFIG.theme_custom_user_settings);
	
	function init(){
		
		ready(bind);
	}
	function I(e){
		return document.getElementById(e);
	}
	function bind(){
		cache.$fm = I('fm-change-avatar');
		if(!cache.$fm)
			return;
		cache.$crop_container = I('cropper-container');
		cache.$avatar_preview = I('avatar-preview');
		cache.$crop_done_btn = I('cropper-done-btn');
		cache.$base64 = I('avatar-base64');
		upload();
	}
	function upload(){
		cache.$file = I('file');
		
		cache.$file.addEventListener('drop', file_select , false);
		cache.$file.addEventListener('change', file_select , false);

		cache.$fm.addEventListener('submit',validate,false);
		
		function file_select(e){
			e.stopPropagation();  
			e.preventDefault();  
			cache.files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
			cache.file = cache.files[0];
			file_read(cache.file);
		}
		function file_read(file){
			var	reader = new FileReader();
			reader.onload = function (e) {
				if(file.type.indexOf('image') === -1){
					alert('Invaild file type.');
					return false;
				}

				cache.$crop_container.innerHTML = '<img src="' + reader.result + '" alt="cropper">';
				cache.$crop_container.style.display = 'block';
				
				cache.$crop_img = cache.$crop_container.querySelector('img');
				
				cache.$avatar_preview.style.display = 'block';

				/** load crop module */
				//require.ensure(['./cropper'],function(){
					
				//});
				cache.cp = new Cropper(cache.$crop_img,{
					aspectRatio : 1,
					preview : '#avatar-preview',
					minCropBoxWidth : 150,
					minCropBoxHeight : 150
				});
				cache.$crop_done_btn.style.display = 'block';
			};
			reader.readAsDataURL(file);	
		}
		function validate(){
			var xhr = new XMLHttpRequest(),
				fd = new FormData(),
				$submit = cache.$fm.querySelector('[type=submit]');
			/**
			 * tip
			 */
			ajax_loading_tip('loading',window.THEME_CONFIG.lang.M01);
			$submit.setAttribute('disabled',true);

			fd.append('theme-nonce',window.DYNAMIC_REQUEST['theme-nonce']);
			fd.append('type','avatar');
			fd.append('b4',cache.cp.getCroppedCanvas().toDataURL('image/jpeg',0.75));
			
			xhr.open('POST',config.process_url);
			xhr.send(fd);
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
					
					if(data && data.status === 'success'){
						ajax_loading_tip(data.status,data.msg);
						location.reload();
					}else if(data && data.status === 'error'){
						ajax_loading_tip(data.status,data.msg);
						$submit.removeAttribute('disabled');
					}else{
						ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
						$submit.removeAttribute('disabled');
					}
				}else{
					ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
					$submit.removeAttribute('disabled');
				}
			};
			xhr.onerror = function(){
				ajax_loading_tip('error',window.THEME_CONFIG.lang.E01);
				$submit.removeAttribute('disabled');
			}

		}
	}
	init();
}