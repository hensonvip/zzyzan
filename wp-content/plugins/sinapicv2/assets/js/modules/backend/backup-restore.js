var ready = require('modules/ready');
var array_merge = require('modules/array-merge');
var ajax_loading_tip = require('modules/ajax-loading-tip');
var param = require('modules/parse-obj-url');
module.exports = function(){


var cache = {},
	config = {

		process_url : '',
		
		lang : {
			E01 : 'Error code: ',
			E02 : 'Program error, can not continue to operate. Please try again or contact author. ',
			
			M01 : 'Getting backup config data, please wait... ',
			M02 : 'Current processing: ',
			M03 : 'Downloading, you can restore the pictures to post after the download is complete. ',
			M04 : 'Download completed, you can perform a restore operation. ',
			M05 : 'Current file has been downloaded, skipping it. ',
			M06 : 'The data is being restored , please wait...',
		}
	};

config = array_merge(config, window.PLUGIN_CONFIG_sinapicv2);

ready(init);

function init(){
	var oobackup = new backup(),
		oorestore = new restore();
	oobackup.init();
	oorestore.init();
}

function backup(){
	
	this.init = function(){
		cache.$backup_btn = I('sinapicv2-backup-btn');
		cache.$backup_btns = I('sinapicv2-backup-btns');
		cache.$backup_tip = I('sinapicv2-backup-tip');
		
		cache.$backup_btn.addEventListener('click', function (e) {
			//cache.$backup_btns.style.display = none;
			ajax_loading_tip('loading', config.lang.M01);
			var xhr = new XMLHttpRequest();
			xhr.open('get',config.process_url + '&type=get_backup_data');
			xhr.send();
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data = xhr.responseText;
					try{data=JSON.parse(xhr.responseText)}catch(err){}
					backup_done(data);
				}else{
					ajax_loading_tip('error',config.lang.E02);
				}
			};
			xhr.onerror = function(){
				ajax_loading_tip('error',config.lang.E02);
			};
		});
	}
	/** 
	 * backup done
	 */
	function backup_done(data){
		if(data.status === 'success'){
			var posts = data.posts;
			cache.imgs = [];
			cache.img_index = 0;
			for(var i in posts){
				if(posts[i]['imgs']){
					for(var j in posts[i]['imgs']){
						cache.imgs.push({
							url : posts[i]['imgs'][j],
							post_id : posts[i]['id']
						});
					}
				}
			}
			/** 
			 * download start, come on~!!
			 */
			ajax_download();
			
		}else if(data.status === 'error'){
			ajax_loading_tip('error',data.msg);
		}else{
			ajax_loading_tip('error',data);
		}
	}
	function ajax_download(){
		var img = cache.imgs[cache.img_index],
			imgs_len = cache.imgs.length,
			next_img_index = cache.img_index + 1;
		/** 
		 * all complete
		 */
		if(imgs_len < next_img_index){
			ajax_loading_tip('success',config.lang.M04);
			return false;
		}
		if(cache.img_index === 0) 
			ajax_loading_tip('loading',config.lang.M02 + (cache.img_index + 1) +'/' + imgs_len);
			
		cache.img_index++;
		var xhr = new XMLHttpRequest();
		var ajax_param = param({
			post_id : img.post_id,
			img_url : img.url,
			type : 'download'
		});
		xhr.open('get',config.process_url + '&' + ajax_param);
		xhr.send();
		xhr.onload = function(){
			if(xhr.status >= 200 && xhr.status < 400){
				var data = xhr.responseText;
				try{data=JSON.parse(xhr.responseText)}catch(err){}
				if(data.status === 'success'){
					/** 
					 * download next
					 */
					if(data.skip){
						ajax_loading_tip('loading',config.lang.M05 + ' ' + config.lang.M02 + next_img_index + '/' + imgs_len);
					}else{
						ajax_loading_tip('loading',config.lang.M02 + next_img_index + '/' + imgs_len);
					}
					ajax_download();
				}else if(data.status === 'error'){
					ajax_loading_tip('error',data.msg + ' ' + config.lang.M02 + next_img_index + '/' + imgs_len);
					ajax_download();
				}else{
					ajax_loading_tip('error',config.lang.E02);
					console.log(data);
				}
			}else{
				ajax_loading_tip('error',config.lang.E02);
			}
		};
		xhr.onerror = function(){
			ajax_loading_tip('error',config.lang.E02);
		};
	}
}

function restore(){
	this.init = function(){
		cache.$server_to_space_btn = I('sinapicv2-restore-server-to-host-btn');
		cache.$space_to_server_btn = I('sinapicv2-restore-host-to-server-btn');
		cache.$restore_tip = I('sinapicv2-restore-tip');
		cache.$restore_btns = I('sinapicv2-restore-btns');
		
		server_to_space();
		
		space_to_server();
	
	}
	
	function server_to_space(){
		cache.$server_to_space_btn.addEventListener('click',function(){
			ajax_loading_tip('loading',config.lang.M06);
			var xhr = new XMLHttpRequest();
			xhr.open('get',config.process_url + '&type=restore-sina-to-local');
			xhr.send();
			xhr.onload = function(){
				var data = xhr.responseText;
				try{data=JSON.parse(xhr.responseText)}catch(err){}
				done(data);
			};
			xhr.onerror = function(){
				ajax_loading_tip('error',config.lang.E02);
			};
		});
	}
	function space_to_server(){
		cache.$space_to_server_btn.addEventListener('click',function(){
			ajax_loading_tip('loading',config.lang.M06);
			var xhr = new XMLHttpRequest();
			xhr.open('get',config.process_url + '&type=restore-local-to-sina');
			xhr.send();
			xhr.onload = function(){
				var data = xhr.responseText;
				try{data=JSON.parse(xhr.responseText)}catch(err){}
				done(data);
			};
			xhr.onerror = function(){
				ajax_loading_tip('error',config.lang.E02 + '<br/>' + data.responseText);
			};
		});
	}
	function done(data){
		if(data && data.status){
			ajax_loading_tip(data.status,data.msg);
		}else{
			ajax_loading_tip('error',config.E02);
		}
	}
}
function I(e){
	return document.getElementById(e);
}
}