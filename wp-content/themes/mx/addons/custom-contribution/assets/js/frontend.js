var ready = require('modules/ready');
var array_merge = require('modules/array-merge');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var validate = require('modules/validate');
var placeholder = require('modules/placeholder');
var paseHTML = require('modules/parse-html');

module.exports = function(){
	'use strict';

	if(!window.THEME_CONFIG.theme_custom_contribution)
		return;
		
	var cache = {},
		config = {
			fm_id : 			'fm-ctb',
			file_area_id : 		'ctb-file-area',
			file_btn_id : 		'ctb-file-btn',
			file_id : 			'ctb-file',
			file_tip_id : 		'ctb-file-tip',
			files_id : 			'ctb-files',

			edit : false,
			thumbnail_id : false,
			attachs : false,
			cats : false,
			
			default_size : 'large',
			process_url : '',
			
			lang : {
				M01 : 'Loading, please wait...',
				M02 : 'Uploading {0}/{1}, please wait...',
				M03 : 'Click to delete',
				M04 : '{0} files have been uploaded.',
				M05 : 'Source',
				M06 : 'Click to view source',
				M07 : 'Set as cover.',
				M08 : 'Optional: some description',
				M09 : 'Insert',
				M10 : 'Preview',
				M11 : 'Large size',
				M12 : 'Medium size',
				M13 : 'Small size',
				M14 : 'Please select a category',
				E01 : 'Sorry, server is busy now, can not respond your request, please try again later.'
			}
		};
		
	config = array_merge(config, window.THEME_CONFIG.theme_custom_contribution);
	
	function I(e){
		return document.getElementById(e);
	}
	function init(){
		ready(bind);
	}
	function bind(){
		cache.$fm = 				I('fm-ctb');
		cache.$post_id = 			I('ctb-post-id');
		cache.$post_title = 		I('ctb-title');
		cache.$post_content = 		I('ctb-content');
		cache.$post_excerpt = 		I('ctb-excerpt');
		cache.$file_area = 			I('ctb-file-area');
		cache.$file_btn = 			I('ctb-file-btn');
		cache.$file = 				I('ctb-file');
		cache.$files = 				I('ctb-files');
		cache.$download_point = 	I('theme_custom_download_point');
		
		cache.$file_progress_container = I('ctb-file-progress-container');
		cache.$file_progress = 		I('ctb-file-progress');
		
		cache.$file_completion_tip = I('ctb-file-completion');
		
		cache.$split_number = 		I('ctb-split-number');

		cache.$batch_insert = 		I('ctb-batch-insert-btn');

		if(!cache.$fm) 
			return false;
		I('fm-ctb-loading').style.display = 'none';
		cache.$fm.style.display = 'block';
		
		cache.$post_title.focus();

		
		load_thumbnails();
		
		upload();

		cats();
		
		toggle_reprint_group();
		
		fm_validate();	

		save_split_number();
		
		restore_split_number();
		
		/** auto save */
		auto_save.bind();

		batch_insert();

	}

	function save_split_number(){
		var helper = function(){
			localStorage.setItem('ctb-split-number',this.value);
		}
		cache.$split_number.addEventListener('change',helper);
	}
	function restore_split_number(){
		var number = parseInt(localStorage.getItem('ctb-split-number'));
		if(number > 0)
			cache.$split_number.value = number;
	}
	/**
	 * auto save
	 */
	var auto_save = {
		config : {
			save_interval : 30,
			lang : {
				M01 : 'You have a auto save version, do you want to restore? Auto save last time is {time}.',
				M02 : 'Restore completed.',
				M03 : 'The data has saved your browser.'
			}
		},
		timer : false,
		bind : function(){
			var that = this;
			/** set save key */
			this.save_key = 'auto-save-' + cache.$post_id.value;
			
			this.check_version();
			
			/** save data per x seconds */
			this.timer = setInterval(function(){
				that.save();
			}, this.config.save_interval * 1000);

			cache.$quick_save = I('ctb-quick-save');
			if(cache.$quick_save){
				cache.$quick_save.addEventListener('click',function(){
					if(cache.$post_title.value === ''){
						cache.$post_title.focus();
					}else{
						that.save();
						ajax_loading_tip('success',that.config.lang.M03,3);
					}
				})
			}
		},
		get : function(){
			var data = localStorage.getItem(this.save_key);
			return data ? JSON.parse(data) : false;
		},
		/** action check version */
		check_version : function(){ 
			var data = this.get();
			if(!data || !data.can_restore)
				return false;
			
			var msg = this.config.lang.M01.replace('{time}',data.time);
			if(!confirm(msg))
				return false;
			
			this.restore();
			ajax_loading_tip('success',this.config.lang.M02,3);
		},
		/** preview thumbnail */
		get_data_preview : function(){
			var $insert_btns = document.querySelectorAll('.ctb-insert-btn');
			if(!$insert_btns[0])
				return false;
				
			var $img_links = document.querySelectorAll('.img-link'),
				$img_thumbnail_urls = document.querySelectorAll(' .img-link img'),
				$thumbnail_ids = document.querySelectorAll('.img-thumbnail-checkbox'),
				
				data = {};
			for(var i=0, len=$insert_btns.length; i<len; i++){
				data[$thumbnail_ids[i].value] = {
					'attach-page-url' :  $insert_btns[i].getAttribute('data-attach-page-url'),
					full : {
						url : $img_links[i].href,
					},
					large : {
						url : $insert_btns[i].getAttribute('data-large-url'),
						width : $insert_btns[i].getAttribute('data-width'),
						height : $insert_btns[i].getAttribute('data-height')
					},
					thumbnail : {
						url : $img_thumbnail_urls[i].src
					},
					'attach-id' : $thumbnail_ids[i].value
				}
			}
			return data;
		},
		del : function(){
			localStorage.removeItem(this.save_key);
			clearInterval(this.timer);
		},
		/** action save */
		save : function(){
			var data = {
				can_restore : false
			};
				
			/** title */
			if(cache.$post_title.value !== ''){
				data.title = cache.$post_title.value;
				data.can_restore = true;
			}

			/** excerpt */
			if(cache.$post_excerpt.value !== ''){
				data.excerpt = cache.$post_excerpt.value;
				data.can_restore = true;
			}

			/** content */
			var post_content = get_editor_content();
			if(post_content !== ''){
				data.content = post_content;
				data.can_restore = true;
			}
			
			
			
			/** storage */
			if(document.querySelector('.theme_custom_storage-group')){
				data.storage = {};
				cache.$storage_items = document.querySelectorAll('.theme_custom_storage-item');
				if(cache.$storage_items[0]){
					for(var i=0, len=cache.$storage_items.length; i<len; i++){
						if(!data.storage[i])
							data.storage[i] = {};
						var ph = cache.$storage_items[i].getAttribute('data-placeholder');
						data.storage[i] = {
							url : I('theme_custom_storage-' + ph + '-url').value,
							download_pwd : I('theme_custom_storage-' + ph + '-download-pwd').value,
							extract_pwd : I('theme_custom_storage-' + ph + '-extract-pwd').value
						};
						var $name = I('theme_custom_storage-' + ph + '-name');
						if($name)
							data.storage[i].name = $name.value;
					}
				}
				data.can_restore = true;
			}

			// henson add
			/*download point */
			if(cache.$download_point.value !== ''){
				data.download_point = cache.$download_point.value;
				data.can_restore = true;
			}
			// henson end

			/** preset tags */
			var $tags = document.querySelectorAll('.ctb-preset-tag:checked');
			if($tags[0]){
				data.preset_tags = {};
				for(var i=0,len=$tags.length;i<len;i++){
					if(!data.preset_tags[i])
						data.preset_tags[i] = {};
					data.preset_tags[i] = $tags[i].id;
				}
				data.can_restore = true;
			}

			/** custom tags */
			var $custom_tags = document.querySelectorAll('.ctb-custom-tag');
			if($custom_tags[0]){
				data.custom_tags = {};
				for(var i=0,len=$custom_tags.length;i<len;i++){
					if(!data.custom_tags[$custom_tags[i].id])
						data.custom_tags[$custom_tags[i].id] = {};
					data.custom_tags[$custom_tags[i].id] = $custom_tags[i].value;
				}
				data.can_restore = true;
			}
			/** source */
			var $source = document.querySelector('.theme_custom_post_source-source-radio:checked');
			if($source){
				data.source = {
					source : $source.value,
					reprint_url : I('theme_custom_post_source-reprint-url').value,
					reprint_author : I('theme_custom_post_source-reprint-author').value
				};
				data.can_restore = true;
			}

			/** preview */
			data.preview = this.get_data_preview();
			if(data.preview){
				data.can_restore = true;
			}

			/** cover id */
			var $cover = document.querySelector('.img-thumbnail-checkbox:checked');
			if($cover){
				data.cover_id = $cover.value;
				data.can_restore = true;
			}

			/** time */
			var d = new Date();
			data.time = d.getFullYear() + '-' + d.getMonth() + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes();
			/** save */
			localStorage.setItem(this.save_key,JSON.stringify(data));
		},

		/** action restore */
		restore : function(){
			var data = this.get();
			if(!data || !data.can_restore)
				return false;
			/** post title */
			if(data.title)
				cache.$post_title.value = data.title;
				
			/** post excerpt */
			if(data.excerpt)
				cache.$post_excerpt.value = data.excerpt;

			/** post content */
			if(data.content)
				set_editor_content(data.content);
				
			/** storage */
			if(data.storage && document.querySelector('.theme_custom_storage-group')){
				var $storage_container = I('theme_custom_storage-container');
				var tpl = $storage_container.getAttribute('data-tpl');
				$storage_container.innerHTML = '';
				for(var i in data.storage){
					var $tpl = paseHTML(tpl.replace(/\%placeholder\%/g,i));
					/** name */
					var $name = $tpl.querySelector('#theme_custom_storage-' + i + '-name');
					if($name){
						$name.value = data.storage[i].name;
					}
					/** url */
					var $url = $tpl.querySelector('#theme_custom_storage-' + i + '-url');
					$url.value = data.storage[i].url;
					/** url */
					var $download_pwd = $tpl.querySelector('#theme_custom_storage-' + i + '-download-pwd');
					$download_pwd.value = data.storage[i].download_pwd;
					/** url */
					var $extract_pwd = $tpl.querySelector('#theme_custom_storage-' + i + '-extract-pwd');
					$extract_pwd.value = data.storage[i].extract_pwd;

					$storage_container.appendChild($tpl);			
				}
			}

			/** download_point henson add */
			if(data.download_point)
				cache.$download_point.value = data.download_point;

			/** preset tags */
			if(data.preset_tags){
				for(var i in data.preset_tags){
					var $preset_tag = I(data.preset_tags[i]);
					if($preset_tag)
						$preset_tag.checked = true;
				}
			}

			/** custom tags */
			if(data.custom_tags){
				for(var i in data.custom_tags){
					var $custom_tag = I(i);
					if($custom_tag)
						$custom_tag.value = data.custom_tags[i];
				}
			}
			
			/** source */
			if(data.source){
				if(data.source.source){
					var $item = I('theme_custom_post_source-source-' + data.source.source),
						$reprint_url = I('theme_custom_post_source-reprint-url'),
						$reprint_author = I('theme_custom_post_source-reprint-author');
					if($reprint_url)
						$reprint_url.value = data.source.reprint_url;
					if($reprint_url)
						$reprint_author.value = data.source.reprint_author;
					if($item)
						$item.checked = true;
				}
			}

			/** Preview */
			if(data.preview){
				/** set cover id */
				if(data.cover_id)
					config.thumbnail_id = data.cover_id;
				for(var i in data.preview){
					append_tpl(convert_to_preview_tpl_args(data.preview[i]));
				}
			}
		}
	};
	function convert_to_preview_tpl_args(data){
		var preview_args = {
			full : {
				url : data.full.url
			},
			large : {
				url : data.large.url,
				width : data.large.width,
				height : data.large.height
			},
			thumbnail : {
				url : data.thumbnail.url
			},
			'attach-id' : data['attach-id']
		};
		preview_args['attach-page-url'] = data['attach-page-url'];
		return preview_args;
	}
	function set_editor_content(s){
		var ed = tinymce.editors['ctb-content'];
		if(ed && !ed.isHidden()){
			tinymce.editors['ctb-content'].setContent(s);
		}else{
			cache.$post_content.value = s;
		}
	}
	function get_editor_content(){
		var ed = tinymce.editors['ctb-content'];
		if(ed && !ed.isHidden()){
			return tinymce.editors['ctb-content'].getContent();
		}else{
			return cache.$post_content.value;
		}
	}

	function send_to_editor(s) {
		var ed = tinymce.editors['ctb-content'];
		if(ed && !ed.isHidden()){
			ed.execCommand('mceInsertContent', false, s);
		}else if(typeof(QTags) != 'undefined'){
			QTags.insertContent(s);
		}else{
			cache.$post_content.value += s;
		}
	}
	function load_thumbnails(){
		if( !config.edit || !config.attachs )
			return false;
			
		for(var i in config.attachs){
			//console.log(config.attachs[i]);
			append_tpl(config.attachs[i]);
		}
	}
	/**
	 * upload
	 */
	
	function upload(){
		cache.$file.addEventListener('change', file_select);
		cache.$file.addEventListener('drop', file_drop);
		cache.$file.addEventListener('dragover', dragover);
	}
	function dragover(evt){
		evt.stopPropagation();
		evt.preventDefault();
		evt.dataTransfer.dropEffect = 'copy';
	}
	function file_drop(e){
		e.stopPropagation();
		e.preventDefault();
		cache.files = e.dataTransfer.files;
		cache.file_count = cache.files.length;
		cache.file = cache.files[0];
		cache.file_index = 0;
		file_upload(cache.files[0]);
	}
	/**
	 * file_select
	 */
	function file_select(e){
		e.stopPropagation();
		e.preventDefault();
		cache.files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
		cache.file_count = cache.files.length;
		cache.file = cache.files[0];
		cache.file_index = 0;
		file_upload(cache.files[0]);
		cache.$file_progress.style.width = '1px';

	}
	/**
	 * file_upload
	 */
	function file_upload(file){
		file_submission(file);
	}
	/**
	 * file_submission
	 */
	function file_submission(file){
		file_beforesend_callback();
		var fd = new FormData(),
			xhr = new XMLHttpRequest();

		fd.append('type','upload');
		fd.append('theme-nonce',window.DYNAMIC_REQUEST['theme-nonce']);
		fd.append('img',file);
		
		xhr.open('post',config.process_url);
		xhr.onload = function(){
			if (xhr.status >= 200 && xhr.status < 400) {
				file_complete_callback(xhr.responseText);
			}else{
				file_error_callback(xhr.responseText);
			}
			xhr = null;
		};
		
		
		xhr.upload.onprogress = function(e){
			if (e.lengthComputable) {
				var percent = (e.loaded * cache.file_index) / (e.total * cache.file_count) * 100;
				cache.$file_progress.style.width = percent + '%';
			}
		};
		xhr.send(fd);
	}
	function file_beforesend_callback(){
		var tx = placeholder(config.lang.M02, cache.file_index + 1,cache.file_count);
		uploading_tip('loading',tx);
	}
	function file_error_callback(msg){
		msg = msg ? msg : config.lang.E01;
		uploading_tip('error',msg);
	}
	/** 
	 * upload_started
	 */
	//function upload_started(i,file,count){
	//	var t = placeholder(config.lang.M02, i,count);
	//	uploading_tip('loading',t);
	//}
	function file_complete_callback(data){
		try{data = JSON.parse(data)}catch(error){}
		cache.file_index++;
		/** 
		 * success
		 */
		if(data && data.status === 'success'){
			/** append to tpl */
			append_tpl(data);
			
			/** preset editor content */
			//var editor_content = get_img_content_tpl({
			//	attach_page_url : data['attach-page-url'],
			//	width : data.large.width,
			//	height : data.large.height,
			//	img_url : data[config.default_size].url
			//});
			
			
			//}
			/** set content to editor */
			//send_to_editor(editor_content);
		
			/** 
			 * check all thing has finished, if finished
			 */
			if(cache.file_count === cache.file_index){
				var tx = placeholder(config.lang.M04, cache.file_index,cache.file_count);
				uploading_tip('success',tx);
				cache.$file.value = '';
			/**
			 * upload next file
			 */
			}else{
				file_upload(cache.files[cache.file_index]);
			}
		/** 
		 * no success
		 */
		}else{
			/** 
			 * notify current file is error
			 */
			if(cache.file_index > 0){
				//error_file_tip(cache.files[cache.file_index - 1]);
			}
			/** 
			 * if have next file, continue to upload next file
			 */
			if(cache.file_count > cache.file_index){
				file_upload(cache.files[cache.file_index]);
			/** 
			 * have not next file, all complete
			 */
			}else{
				cache.is_uploading = false;
				if(data && data.status === 'error'){
					file_error_callback(data.msg);
				}else{
					file_error_callback(config.lang.E01);
					console.error(data);
				}
				/** 
				 * reset file input
				 */
				cache.$file.value = '';

			}
		}
	}/** end file_complete_callback */
	
	function append_tpl(data){
		var $tpl = get_preview_tpl(data);
			
		cache.$files.style.display = 'block';
		cache.$files.appendChild($tpl);
		$tpl.style.display = 'block';
	}
	/**
	args = {
		attach-page-url : ...
		full : {
			url : ...,
			height : ...,
			width : ...
		},
		large : {
			url : ...,
			height : ...,
			width : ...
		}
		thumbnail : {
			url : ...,
			height : ...,
			width : ...
		}
		attach-id : ...
	}
	 */
	function get_preview_tpl(args){
		if(!cache.$post_title)
			cache.$post_title = I('ctb-title');
			
		var $tpl = document.createElement('div'),
			M10 = cache.$post_title == '' ? config.lang.M10 : cache.$post_title.value,
			content = '<a class="img-link" href="' + args.full.url + '" target="_blank" title="' + config.lang.M06 + '">' + 
					'<img src="' + args.thumbnail.url + '" alt="' + M10 +'" >' +
				'</a>' +
				
				'<a href="javascript:;" class="btn btn-default btn-block ctb-insert-btn" id="ctb-insert-' + args['attach-id'] + '" data-size="large" data-attach-page-url="' + args['attach-page-url'] + '" data-width="' + args.large.width + '" data-height="' + args.large.height + '" data-large-url="' + args.large.url + '" ><i class="fa fa-plug"></i> ' + config.lang.M09 + '</a>' +
				
				'<input type="radio" name="ctb[thumbnail-id]" id="img-thumbnail-' + args['attach-id'] + '" value="' + args['attach-id'] + '" hidden class="img-thumbnail-checkbox" required >' +
				
				'<label for="img-thumbnail-' + args['attach-id'] + '" class="ctb-set-cover-btn"><i class="fa fa-star"></i> ' + config.lang.M07 + '</label>' +
				
				'<input type="hidden" name="ctb[attach-ids][]" value="' + args['attach-id'] + '" >';
				
		$tpl.id = 'img-' + args['attach-id'];
		$tpl.setAttribute('class','thumbnail-tpl g-phone-1-2 g-tablet-1-3 g-desktop-1-4');
		$tpl.innerHTML = content;
		$tpl.style.display = 'none';
		
		/**
		 * set as cover
		 */
		if((!config.thumbnail_id && !cache.first_cover) || (args['attach-id'] == config.thumbnail_id)){
			$tpl.querySelector('.img-thumbnail-checkbox').checked = true;
			cache.first_cover = true;
		}
		/**
		 * insert
		 */
		var $insert_btn = $tpl.querySelectorAll('.ctb-insert-btn'),
			send_content_helper = function(){
				/** send to editor */
				send_to_editor(get_img_content_tpl({
					attach_page_url : this.getAttribute('data-attach-page-url'),
					width : this.getAttribute('data-width'),
					height : this.getAttribute('data-height'),
					img_url : args[this.getAttribute('data-size')].url
				}));
			};
		for(var i = 0, len = $insert_btn.length; i < len; i++){
			$insert_btn[i].addEventListener('click',send_content_helper,false);
		}

		return $tpl;
	}
	/**
	 * 
	data = {
		attach_page_url : '',
		img_url : '',
		width : '',
		height : ''
	}
	 */
	function get_img_content_tpl(data){
		var title = cache.$post_title == '' ? config.lang.M10 : cache.$post_title.value;
		return '<p><a href="' + data.attach_page_url + '" title="' + title + '" target="_blank" >' + 
			'<img src="' + data.img_url + '" alt="' + title + '" width="'+ data.width + '" height="'+ data.height + '">' +
		'</a></p>';
	}

	/**
	 * The tip when pic is uploading
	 *
	 * @param string status 'loading','success' ,'error'
	 * @param string text The content of tip
	 * @return 
	 * @version 1.0.1
	 */
	function uploading_tip(status,text){
		/** 
		 * uploading status
		 */
		if(!status || status === 'loading'){
			ajax_loading_tip(status,text);
			//cache.$file_progress_tx.innerHTML = text;
			cache.$file_progress_container.style.display = 'block';
			cache.$file_area.style.display = 'none';
			//cache.$file_completion_tip.style.display = 'none';
		/** 
		 * success status
		 */
		}else{
			ajax_loading_tip(status,text);
			//cache.$file_completion_tip.innerHTML = status_tip(status,text)
			//cache.$file_completion_tip.style.display = 'block';
			cache.$file_progress_container.style.display = 'none';
			cache.$file_area.style.display = 'block';
		}
	}/** end uploading_tip */

	function batch_insert(){
		cache.$batch_insert.addEventListener('click', event_batch_insert_imgs);
	}
	function event_batch_insert_imgs(){
		var data = auto_save.get_data_preview(),
			content = [],
			index = 0;
			
		if(!data)
			return false;

		var len = Object.keys(data).length;
			
		for(var i in data){
			
			content.push(get_img_content_tpl({
				attach_page_url : data[i]['attach-page-url'],
				img_url : data[i].large.url,
				width : data[i].large.width,
				height : data[i].large.height
			}));

			/** nextpage checked */
			if(cache.$split_number.value > 0 && 
				index < len - 1 && 
				(index + 1) % cache.$split_number.value == 0
			){
				content.push(' <!--nextpage--> ');
			}
			
			index++;
		}
		if(content)
			send_to_editor(content.join(''));
	}
	function fm_validate(){
		var m = new validate();
			m.process_url = config.process_url;
			m.loading_tx = config.lang.M01;
			m.error_tx = config.lang.E01;
			m.$fm = cache.$fm;
			m.before = function(fd){
				fd.append('ctb[post-content]',get_editor_content());
			};
			m.done = function(data){
				if(config.edit){
					cache.$fm.querySelector('.submit').removeAttribute('disabled');
				}
				if(data.status === 'success'){
					/** delete auto save */
					auto_save.del();
				}
			};
			m.init();
	}
	function cats(){
		if(!config.cats)
			return false;
		cache.$cat_container = I('ctb-cat-container');
		if(!cache.$cat_container)
			return;
			
		create_parent_cats();
		
		/** create no select option */
		function create_null_opt($container){
			var $null_opt = document.createElement('option');
			$null_opt.innerHTML = config.lang.M14;
			$null_opt.value = '';
			$container.appendChild($null_opt);
		}
		/** crreate parent select */
		function create_parent_cats(){
			cache.$parent_cat = document.createElement('select');
			cache.$parent_cat.name = 'ctb[cats][]';
			cache.$parent_cat.classList.add('ctb-cat') ;
			cache.$parent_cat.classList.add('form-control');
			cache.$parent_cat.required = true;
			create_null_opt(cache.$parent_cat);
			for(var i in config.cats){
				if(config.cats[i].parent > 0)
					continue;
				var $opt = document.createElement('option');
				$opt.value = config.cats[i].term_id;
				$opt.title = config.cats[i].description;
				$opt.innerHTML = config.cats[i].name;
				cache.$parent_cat.appendChild($opt);
			}
			/** event */
			cache.$parent_cat.addEventListener('change', event_cat_change);
			cache.$cat_container.appendChild(cache.$parent_cat);
		}
		function remove_next($curr){
			var $next = $curr.nextElementSibling;
			if($next){
				$next.parentNode.removeChild($next);
				remove_next($curr);
			}
		}
		function event_cat_change(){
			/** remove other */
			remove_next(this);
			
			var parent_cid = this.value,
				$cat_child = cache['$cat_' + parent_cid];
			
			if(!parent_cid)
				return;
			if($cat_child){
				cache.$cat_container.appendChild($cat_child);
				return;
			}
			for(var i in config.cats){
				if(config.cats[i].parent != parent_cid)
					continue;
				var $opt = document.createElement('option');
				$opt.value = config.cats[i].term_id;
				$opt.title = config.cats[i].description;
				$opt.innerHTML = config.cats[i].name;
				if(!$cat_child){
					$cat_child = document.createElement('select');
					$cat_child.name = 'ctb[cats][]';
					$cat_child.classList.add('ctb-cat') ;
					$cat_child.classList.add('form-control');
					create_null_opt($cat_child);
					$cat_child.addEventListener('change',event_cat_change);
				}
				$cat_child.appendChild($opt);
			}
			if($cat_child)
				cache.$cat_container.appendChild($cat_child);
		}
	}
	function toggle_reprint_group(){
		var $radios = document.querySelectorAll('.theme_custom_post_source-source-radio'),
			$inputs = document.querySelectorAll('.theme_custom_post_source-inputs'),
			action = function($radio){
				var target = $radio.getAttribute('target'),
					$target = I(target);
				for(var i=0,len=$inputs.length;i<len;i++){
					if($target && $target.id === target && $radio.checked){
						$target.style.display = 'block';
						var $input = $target.querySelector('input');
						if($input.value.trim() === '')
							$input.focus();
					}else{
						$inputs[i].style.display = 'none';
					}
				}
			},
			event_select = function(){
				action(this)
			};
		for(var i = 0, len = $radios.length; i < len; i++){
			action($radios[i]);
			$radios[i].addEventListener('change', event_select);
		}
	}

	init();
}