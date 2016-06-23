var array_merge = require('modules/array-merge');

module.exports = function(config){
	'use strict';
	var defaults = {
		$file : false,
		url : '',
		paramname : 'file',
		maxsize : 1048*1024*2, /** 2mb */
		maxfiles : 50,
		interval : 3000,
		onselect : function(file,file_index,file_count){},
		status_success : function(data,file_index,file,count){},
		onalways : function(data,file_index,file,count){},
		onprogress : function(percent,file_index,file,count){},
		status_error : function(data,file_index,file,count){},
		onerror : function(data,file_index,file,count){}
	};
	config = array_merge(defaults, config);
	
	if(!config.$file) 
		return false;
		
	var files,
		file,
		file_count = 0,
		file_index = 0,
		start_time,
		is_uploading = false,
		all_complete = false;


	init();
	function init(){
		config.$file.addEventListener('change', handle_change);
		config.$file.addEventListener('drop', handle_drop);
	};

	function handle_drop(e){
		e.stopPropagation();
		e.preventDefault();
		files = e.dataTransfer.files;
		file_count = files.length;
		file = files[0];
		file_index = 0;
		file_upload(files[0]);
	}
	function handle_change(e){
		e.stopPropagation();
		e.preventDefault();
		files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
		file_count = files.length;
		file = files[0];
		file_index = 0;
		file_upload(files[0]);
	}
	function file_upload(file){
		start_time = new Date();
		config.onselect(file,file_index,file_count);
		submission(file);
	}
	function submission(file){
		if(is_uploading) 
			return;
		is_uploading = true;
		var fd = new FormData(),
		xhr = new XMLHttpRequest();
		fd.append(config.paramname,file);
		xhr.open('post',config.url);
		xhr.send(fd);
		
		xhr.onload = function(){
			if (xhr.status >= 200 && xhr.status < 400) {
				complete(xhr);
			}else{
				error(status,file_index,file,file_count);
			}
			is_uploading = false;
			xhr = null;
		};
		xhr.onerror = function(){
			config.onerror(i,file,file_count);
		};
		xhr.upload.onprogress = function(e){
			if (e.lengthComputable) {
				var percent = (e.loaded * cache.file_index) / (e.total * cache.file_count) * 100;
				config.onprogress(percent,i,file,file_count);
			}
		};
	}
	function complete(xhr){
		var data = xhr.responseText;
		try{data = JSON.parse(xhr.responseText);
		}catch(error){}
		file_index++;
		if(data.status === 'success'){
			config.status_success(data,file_index,file,file_count);
			if(file_count === file_index){
				all_complete = true;
				is_uploading = false;
				config.$file.value = '';
			}else{
				upload_next(files[file_index]);
			}
		}else{
			config.status_error(data,file_index,file,file_count);
			if(file_count > file_index){
				upload_next(files[file_index]);
			}else{
				config.$file.value = '';
			}
		}
		config.onalways(data,file_index,file,file_count);

	}
	function upload_next(file){
		var end_time = new Date(),
		interval_time = end_time - start_time,
		timeout = config.interval - interval_time,
		timeout = timeout < 0 ? 0 :timeout;
		setTimeout(function(){
			file_upload(file);
		},timeout);
	}
}