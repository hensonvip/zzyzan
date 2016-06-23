var ready = require('modules/ready');
var array_merge = require('modules/array-merge');
var array_replace = require('modules/array-replace');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var status_tip = require('modules/status-tip');
var param = require('modules/parse-obj-url');
var validate = require('modules/validate');

module.exports = function(){
	'use strict';
	if(!window.THEME_CONFIG.theme_custom_collection)
		return;
		
	var cache = {},
		config = {
			process_url : '',
			tpl_input : '',
			tpl_preview : '',
			min_posts : 5,
			max_posts : 10,
			lang : {
				M01 : 'Loading, please wait...',
				M02 : 'A item has been deleted.',
				M03 : 'Getting post data, please wait...',
				M04 : 'Previewing, please wait...',
				E01 : 'Sorry, server is busy now, can not respond your request, please try again later.',
				E02 : 'Sorry, the minimum number of posts is %d.',
				E03 : 'Sorry, the maximum number of posts is %d.',
				E04 : 'Sorry, the post id must be number, please correct it.'
			}
		};
	config = array_merge(config, window.THEME_CONFIG.theme_custom_collection);
	
	ready(init);
	
	function init(){
		upload();
		list();
		preview();
		post();
	}
	function get_posts_count(){
		return document.querySelectorAll('.clt-list').length;
	}
	function preview(){
		var $preview = I('clt-preview');
		cache.$preview_container = I('clt-preview-container');
		
		if(!$preview)
			return false;

		function event_preview_click(e){
			if(e)
				e.preventDefault();
			var lists_count = get_posts_count();
			
			if(lists_count < config.min_posts){
				ajax_loading_tip('error',config.lang.E02,3);
				return false;
			}else if(lists_count > config.max_posts){
				ajax_loading_tip('error',config.lang.E03,3);
				return false;
			}

			show_preview();
		}
		$preview.addEventListener('click',event_preview_click);

		function show_preview(){
			var $lists = document.querySelectorAll('.clt-list'),
				tpl = '';
			/**
			 * loop lists
			 */
			for(var i = 0, len = $lists.length; i < len; i++){
				/**
				 * check empty input
				 */
				var $requires = $lists[i].querySelectorAll('[required]');
				/**
				 * loop requires
				 */
				for(var j = 0, l = $requires.length; j < l; j++){
					if($requires[j].value.trim() === ''){
						ajax_loading_tip('error',$requires[j].getAttribute('title'),3);
						$requires[j].focus();
						return false;
					}
				}
				var id = $lists[i].getAttribute('data-id'),
					$imgs = $lists[i].querySelectorAll('img'),
					thumbnail_url = $imgs[$imgs.length - 1].src;

				/**
				 * create tpl
				 */
				tpl += array_replace(
					config.tpl_preview,
					[
						'%hash%',
						'%title%',
						'%thumbnail%',
						'%content%'
					],
					[
						id,
						I('clt-list-post-title-' + id).value,
						thumbnail_url,
						I('clt-list-post-content-' + id).value
					]
				);
				//console.log(config.tpl_preview);
			}
			//console.log(tpl);
			preview_done(tpl);

		}
		function preview_done(tpl){
			cache.$preview_container.innerHTML = tpl;
			//location.hash = '#' + cache.$preview_container.id;
		}

	}
	function list(){
		var _cache = {},
			$lists = document.querySelectorAll('.clt-list');
			
		if(!$lists[0])
			return false;
			
		_cache.$add = I('clt-add-post');
		_cache.$container = I('clt-lists-container');
		
			
		/**
		 * bind the lsits
		 */
		for(var i = 0, len = $lists.length; i < len; i++){
			bind_list($lists[i]);
		}
		/**
		 * bind the add list btn
		 */
		add_list();
		/**
		 * action add new psot
		 */
		function add_list(){
			var helper = function(e){
				if(e)
					e.preventDefault();
				/** too many posts */
				if(get_posts_count() >= config.max_posts){
					ajax_loading_tip('error',config.lang.E03,3);
					return false;
				}
				var rand = Date.now(),
					$tmp = document.createElement('div'),
					$new_list;
					
				$tmp.innerHTML = get_input_tpl(rand);
				$new_list = $tmp.firstChild;

				$new_list.classList.add('delete');
				_cache.$container.appendChild($new_list);
				/** bind list */
				bind_list($new_list);
				
				setTimeout(function(){
					$new_list.classList.remove('delete');
				},1);

			};
			_cache.$add.addEventListener('click', helper);
		}
		function get_input_tpl(placeholder){
			return config.tpl_input.replace(/%placeholder%/g,placeholder);
		}

		
		/**
		 * bind list
		 */
		function bind_list($list){
			if(!$list)
				return false;

			var placeholder = $list.getAttribute('data-id');
			
			/** bind delete action */
			del(placeholder);
			
			/** bind post id input blur action */
			show_post(placeholder);
			
			/**
			 * delete action
			 */
			function del(placeholder){
				var helper = function(e){
					if(e)
						e.preventDefault();
					/** not enough posts */
					if(get_posts_count() <= config.min_posts){
						ajax_loading_tip('error',config.lang.E02,3);
						return false;
					}
					$list.classList.add('delete');
					setTimeout(function(){
						$list.parentNode.removeChild($list);
					},500);
				};
				I('clt-list-del-' + placeholder).addEventListener('click', helper);;
			}

			/**
			 * get post data action
			 */
			function show_post(placeholder){
				post_id_blur();
				function post_id_blur(){
					var $post_id = I('clt-list-post-id-' + placeholder),
						helper = function(evt){
							evt.preventDefault();
							
							var post_id = this.value;
							if(post_id.trim() === '')
								return false;

							if(isNaN(post_id.trim()) === true){
								this.select();
								ajax_loading_tip('error',config.lang.E04,3);
								return false;
							}
							/**
							 * if no exist cache, get data from server
							 */
							if(!get_post_cache_data(post_id)){
								ajax(post_id,placeholder,this);
							/**
							 * get post data from cache
							 */
							}else{
								set_post_data(post_id,placeholder);
							}
						}
					$post_id.addEventListener('change',helper,false);
					$post_id.addEventListener('blur',helper,false);
				}
				function ajax(post_id,placeholder,$post_id){
					/**
					 * loading tip
					 */
					ajax_loading_tip('loading',config.lang.M03);
					
					var xhr = new XMLHttpRequest(),
						ajax_data = {
							'type' : 'get-post',
							'post-id' : post_id,
							'theme-nonce' : window.DYNAMIC_REQUEST['theme-nonce']
						};
					xhr.open('GET',config.process_url + '&' + param(ajax_data));
					xhr.send();
					xhr.onload = function(){
						if(xhr.status >= 200 && xhr.status < 400){
							var data;
							try{data = JSON.parse(xhr.responseText)}catch(err){data = xhr.responseText}
							done(data);
						}else{
							ajax_loading_tip('error',config.lang.E01);
						}
					};
					xhr.onerror = function(){
						ajax_loading_tip('error',config.lang.E01);
					};
					function done(data){
						if(data && data.status === 'success'){
							/** set cache */
							set_post_cache(post_id,data);
							/** set to html */
							set_post_data(post_id,placeholder);
							/** tip */
							ajax_loading_tip(data.status,data.msg,3);
						}else if(data && data.status === 'error'){
							/** set cache */
							set_post_cache(post_id,data);
							/** focus post id */
							$post_id.select();
							/** tip */
							ajax_loading_tip(data.status,data.msg,3);
						}else{
							ajax_loading_tip('error',data);
						}
					}
				}
				/**
				 * set post data to cache
				 */
				function set_post_cache(post_id,data){
					if(cache.posts && cache.posts[post_id])
						return false;
						
					if(!cache.posts)
						cache.posts = {};

					cache.posts[post_id] = {
						'thumbnail' : data.thumbnail,
						'title' : data.title,
						'excerpt' : data.excerpt
					};
				}
				function get_post_cache_data(post_id,key){
					if(!cache.posts || !cache.posts[post_id])
						return false;
						
					if(!key)
						return cache.posts[post_id];

					return cache.posts[post_id][key];
						
				}
				/**
				 * set post data to html
				 */
				function set_post_data(post_id,placeholder){
					var $content = I('clt-list-post-content-' + placeholder),
						$thumbnail = I('clt-list-thumbnail-' + placeholder),
						$thumbnail_url = I('clt-list-thumbnail-url-' + placeholder);
					
					if(cache.posts[post_id].title)
						I('clt-list-post-title-' + placeholder).value = cache.posts[post_id].title;

					if(cache.posts[post_id].excerpt && $content.value.trim() === '')
						$content.value = cache.posts[post_id].excerpt;
						
					if(cache.posts[post_id].thumbnail){
						$thumbnail.src = cache.posts[post_id].thumbnail.url;
						$thumbnail_url.value = cache.posts[post_id].thumbnail.url;
					}
				}/** end set_post_data */
			}/** end show_post */
		}/** end bind_list */
	}/** end list */
	function post(){
		var _cache = {};
		_cache.$fm = I('fm-clt');

		if(!_cache.$fm)
			return false;

		var sm = new validate();
		sm.$fm = _cache.$fm;
		sm.process_url = config.process_url;
		sm.error_tx = config.lang.E01;
		sm.init();
	}
	function upload(){
		var $file = I('clt-file'),
			_cache = {};

		_cache.$cover = I('clt-cover');
		_cache.$progress = I('clt-file-progress');
		_cache.$tip = I('clt-file-tip');
		_cache.$progress_bar = I('clt-file-progress-bar');
		_cache.$progress_tx = I('clt-file-progress-tx');
		_cache.$thumbnail_id = I('clt-thumbnail-id');
		_cache.$file_area = I('clt-file-area');

		if(!$file)
			return false;

		$file.addEventListener('change',file_select);
		$file.addEventListener('drop',file_drop);
		$file.addEventListener('dragover',file_select);

		function file_drop(e){
			e.stopPropagation();
			e.preventDefault();
			_cache.files = e.dataTransfer.files;
			file_upload(_cache.files[0]);
		}
		function file_select(e){
			e.stopPropagation();
			e.preventDefault();
			_cache.files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
			file_upload(_cache.files[0]);
		}
		function file_upload(file){
			var	reader = new FileReader();
			reader.onload = function (e) {
				submission(file);
			};
			reader.readAsDataURL(file);
		}
		function submission(file){

			/** loading tip */
			progress_tip('loading',config.lang.M01);
			
			var fd = new FormData(),
				xhr = new XMLHttpRequest();

			fd.append('type','add-cover');
			fd.append('theme-nonce',window.DYNAMIC_REQUEST['theme-nonce']);
			fd.append('img',file);
			xhr.open('post',config.process_url);
			xhr.send(fd);
			xhr.upload.onprogress = function(e){
				if (e.lengthComputable) {
					var percent = e.loaded / e.total * 100;		
					_cache.$progress_bar.style.width = percent + '%';
				}
			};
			xhr.onload = function(){
				if (xhr.status >= 200 && xhr.status < 400) {
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(err){data = xhr.responseText}
					if(data && data.status === 'success'){
						_cache.$cover.src = data.thumbnail.url;
						_cache.$thumbnail_id.value = data['attach-id'];
						ajax_loading_tip(data.status,data.msg,3);
					}else if(data && data.status === 'error'){
						ajax_loading_tip(data.status,data.msg);
					}else{
						ajax_loading_tip('error',data);
					}
				}else{
					ajax_loading_tip('error',config.lang.E01);
				}
				progress_tip('hide');
			};
			xhr.onerror = function(){
				ajax_loading_tip('error',config.lang.E01);
			};
		}
		function progress_tip(t,s){
			if(t === 'hide'){
				_cache.$progress.style.display = 'none';
				_cache.$file_area.style.display = 'block';
				return false;
			}
			_cache.$file_area.style.display = 'none'
			_cache.$progress.style.display = 'block';
			_cache.$progress_bar.style.width = '10%';
			_cache.$progress_tx.innerHTML = status_tip(t,s);
				
		}
	}
	function I(e){
		return document.getElementById(e);
	}
}