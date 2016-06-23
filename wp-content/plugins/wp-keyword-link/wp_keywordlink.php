<?php
/*
Plugin Name:WP Keyword Link
Plugin URI: http://liucheng.name/789/
Description: A SEO plugin that helps you to automatically link keywords to articles. And displays a list of posts similar to the current post. | 为你的wordpress博客添加关键词的链接，更多的内链和外链,更好的SEO! 给文章加上内部链接有利于增加搜索引擎收录。完美支持中英文关键词。最新增加相关文章的功能。
Author: 柳城
Version: 1.7
Author URI: http://liucheng.name/

*/


/* Constants */
define('WP_KEYWORDLINK_OPTION','wp_keywordlinkoption');
define('WP_TAGS2KEYWORD_OPTION','wp_tags2keywordoption');
define('WP_GLOBAL_OPTION','wp_global_option');
define('WP_PAGELIMIT_OPTION','wp_pagelimit_option');
define('WP_RELATED_POSTS_OPTION','wp_related_posts_option');

include_once("wp_csvsupport.php");
include_once("wp_similarity.php");

function wp_keywordlink_admininit()
{
	 // Add a page to the options section of the website
   if (current_user_can('manage_options')) 				
 		add_options_page("WP KeywordLink","WP KeywordLink", 8, __FILE__, 'wp_keywordlink_optionpage');
}

/**
*Loading language file...
*@
*/
function load_wp_language() {
		
		//Loading language file...
		$currentLocale = get_locale();
		if(!empty($currentLocale)) {
			$moFile = dirname(__FILE__) . "/wp_keywordlink-" . $currentLocale . ".mo";
			if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('wp_keywordlink', $moFile);
		}
}

/** load the language file **/
add_filter('init','load_wp_language');


function wp_keywordlink_topbarmessage($msg)
{
	 echo '<div class="updated fade" id="message"><p>' . $msg . '</p></div>';
}


function wp_keywordlink_showdefinitions()
{
 		 /* Retrieve the keyword definitions */ 
		 $links = get_option(WP_KEYWORDLINK_OPTION);

		 echo "<h3>";_e('Links','wp_keywordlink'); echo "</h3>";
		 
		 if ($links)
		 { 
             $wp_pagelimit = get_option(WP_PAGELIMIT_OPTION);
		     if ($wp_pagelimit) { $page_limit = $wp_pagelimit; } else {  $page_limit = '50'; }
			 $page_num = 1;
			 for($j=1;$j<=count($links);$j+=$page_limit){
				 $page_num++;
			 }
		    if ($_POST['wp_page']) { $wp_page = $_POST['wp_page']; } else { $wp_page = $page_num - 1; }
		?>

		<p><form name=wp_page_form id=wp_page_form method="post" action="">
		 <?php _e('Page:','wp_keywordlink'); ?>
		<?php
			for ($ii=1;$ii<$page_num;$ii++) { 
			  if($ii == $wp_page){
				  echo '<input class=button-primary type=submit name=wp_page value='.$ii.' />';
			  }else {
		          echo '<input type=submit name=wp_page value='.$ii.' />';
			  }
		    } ?>
		 <?php _e('Show:','wp_keywordlink'); ?>
		     <input type=text name=page_limit value=<?php echo $page_limit; ?> size=3 />
		 <?php _e('Search:','wp_keywordlink'); ?>
		     <input type=text name=search_k value="" size=12 />
		</form></p>
		<?php

		 if(isset($_POST['search_k']) && $_POST['search_k']) { } else { $links  = array_slice($links, ($wp_page-1)*$page_limit, $page_limit); }
		 #echo "<form name=delete_all_form method=post action=''>\n";
		 #echo "<input type=hidden name=action value=check_delete_all />\n";
  		 echo "<table class='widefat'>\n";		 	  
 	     echo "<thead><tr><th>#</th><th>Keyword</th><th>Link</th><th>Attributes</th><th>Action</th><th><input type=checkbox name=All onclick=checkAll('checkbox')></th></tr></thead>\n";
			 $cnt = ($wp_page-1)*$page_limit;
			 if(isset($_POST['search_k']) && $_POST['search_k']) { $cnt = 0; }
		 	 foreach ($links as $keyword => $details) 
		 	 {
				list($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN,$desc) = explode('|',$details);
				$cleankeyword = stripslashes($keyword);
				if(!$desc){ $desc = $cleankeyword; }
				$desc = stripslashes($desc);

				if( isset($_POST['search_k']) && $_POST['search_k'] ) { 
					$search_k = $_POST['search_k'];
				    if( preg_match("/$search_k/Ui",$cleankeyword) ) { 
					//echo $search_k;
					   if ($cnt++ % 2) echo '<tr class=alternate>'; else echo '<tr>';
						 echo "<td>$cnt</td><td><a title=\"$desc\">$cleankeyword</a></td><td><a href='$link'>";
						 if( function_exists(mb_strimwidth) ){ echo mb_strimwidth($link, 0, 35, '...'); } else { echo substr($link, 0, 35); }
						 echo "</a></td>";
						 
						/* show attributes */
						echo "<td>";				 
						if ($nofollow) echo "[nofollow]";
						if ($firstonly) echo "[first only]";
						if ($newwindow) echo "[new window]";
						if ($ignorecase) echo "[ignore case]";				
						if ($isaffiliate) echo "[affiliate]";
						if ($docomments) echo "[comments]";
						if ($zh_CN) echo "[zh_CN]";
						echo "</td>";				 
						 
						$urlsave_keyword = $keyword; 	
						$urlsave_url 	  = $link;
						$urlsave_desc    = $desc;
						
						echo "<td>";
						echo "<input type=button value=Edit onClick=\"javascript:WPEditKeyword('$urlsave_keyword','$urlsave_url',";
						echo (($nofollow=="0")?"0":"1") . "," . (($firstonly=="0")?"0":"1") . "," .(($newwindow=="0")?"0":"1"). "," .(($ignorecase=="0")?"0":"1") . "," . (($isaffiliate=="0")?"0":"1") . "," . (($docomments=="0")?"0":"1") . "," . (($zh_CN=="0")?"0":"1") . ");\"/>";
						echo "<input type=button value=Delete onClick=\"javascript:WPDeleteKeyword('$urlsave_keyword');\" />\n";
						echo "</td>";
						echo "<td><input type=checkbox name=checkbox value=$urlsave_keyword onclick=checkItem('All')></td>";
						echo "</tr>\n";
				   } else {
					   //echo "Not Found";
				   }
				} else {

			   if ($cnt++ % 2) echo '<tr class=alternate>'; else echo '<tr>';
				 echo "<td>$cnt</td><td><a title=\"$desc\">$cleankeyword</a></td><td><a href='$link'>";
				 if( function_exists(mb_strimwidth) ){ echo mb_strimwidth($link, 0, 35, '...'); } else { echo substr($link, 0, 35); }
				 echo "</a></td>";
				 
				/* show attributes */
				echo "<td>";				 
				if ($nofollow) echo "[nofollow]";
				if ($firstonly) echo "[first only]";
				if ($newwindow) echo "[new window]";
				if ($ignorecase) echo "[ignore case]";				
				if ($isaffiliate) echo "[affiliate]";
				if ($docomments) echo "[comments]";
				if ($zh_CN) echo "[zh_CN]";
				echo "</td>";				 
				 
				$urlsave_keyword = $keyword; 	
				$urlsave_url 	 = $link;
				$urlsave_desc    = $desc;
				
				echo "<td>";
				echo "<input type=button value=Edit onClick=\"javascript:WPEditKeyword('$urlsave_keyword','$urlsave_url','$urlsave_desc',";
				echo (($nofollow=="0")?"0":"1") . "," . (($firstonly=="0")?"0":"1") . "," .(($newwindow=="0")?"0":"1"). "," .(($ignorecase=="0")?"0":"1") . "," . (($isaffiliate=="0")?"0":"1") . "," . (($docomments=="0")?"0":"1") . "," . (($zh_CN=="0")?"0":"1") . ");\"/>";
				echo "<input type=button value=Delete onClick=\"javascript:WPDeleteKeyword('$urlsave_keyword');\" />\n";
				echo "</td>";
				echo "<td><input type=checkbox name=checkbox value=$urlsave_keyword onclick=checkItem('All')></td>";
				echo "</tr>\n";
				}
		 	 }
			 echo "</table>";
			 echo "<input class=button-primary style=float:right type=button value=Batch_Delete onclick=\"checkbox();\">";
			 #echo "</from>";
		 }  
		 else
		 	 _e('<p>No links have been defined!</p>','wp_keywordlink');
			 
		?>
		
		<!-- Support for the delete button , we use Javascript here -->
		<form name=delete_form id=delete_form method="post" action="">
		 <input type=hidden name=action value=delete />
		 <input type=hidden name=keyword value="" />
		</form>

		<script type="text/javascript"> 
		function checkbox()
		{
		var str=document.getElementsByName("checkbox");
		var objarray=str.length;
		var chestr="";
			for (i=0;i<objarray;i++)
			{
			  if(str[i].checked == true)
			  {
			   chestr+=str[i].value+"#";
			  }
			}
			if(chestr == "")
			{
			  alert("please select one keyword!!");
			}
			else
			{
			  //alert("复选框的值是："+chestr);
			  WPDeleteKeyword(chestr);
			}
		}

		function checkAll(str)   
		{   
			var a = document.getElementsByName(str);   
			var n = a.length;   
			for (var i=0; i<n; i++)   
			a[i].checked = window.event.srcElement.checked;   
		}   
		function checkItem(str)   
		{   
			var e = window.event.srcElement;   
			var all = eval("document.all."+ str);   
			if (e.checked)   
			{   
				var a = document.getElementsByName(e.name);   
				all.checked = true;   
				for (var i=0; i<a.length; i++)   
				{   
					if (!a[i].checked)  
					{   
						all.checked = false; break;  
					}   
				}  
			}   
			else   
				all.checked = false;   
		}  

		function WPDeleteKeyword(keyword)
		{
		   if (confirm('Are you sure you want to delete this keyword?'))
			 {
			   document.delete_form.keyword.value = keyword; 
			   document.delete_form.submit();
			 }
		} 
		
		function WPEditKeyword(keyword,url,desc,nofollow,firstonly,newwindow,ignorecase,isaffiliate,docomments,zh_CN)
		{
			 document.wp_keywordadd.keyword.value      = (keyword);
			 document.wp_keywordadd.link.value         = (url);		
			 document.wp_keywordadd.desc.value         = (desc);
			 document.wp_keywordadd.nofollow.checked   = (nofollow==1);
			 document.wp_keywordadd.firstonly.checked  = (firstonly==1);
			 document.wp_keywordadd.newwindow.checked  = (newwindow==1);
			 document.wp_keywordadd.ignorecase.checked = (ignorecase==1);
			 document.wp_keywordadd.isaffiliate.checked= (isaffiliate==1);
			 document.wp_keywordadd.docomments.checked= (docomments==1);
			 document.wp_keywordadd.zh_CN.checked= (zh_CN==1);	
			 window.location.hash = "keywordeditor"; 
		}		
		</script>
		<?php	 
			 

}

function wp_keywordlink_addnew()
{
	global $last_link_options;
	list($last_link,$last_nofollow,$last_firstonly,$last_newwindow,$last_ignorecase,$last_isaffiliate,$last_docomments,$last_zh_CN,$last_desc) = explode("|",$last_link_options);
	?>
 		<h3><?php _e('Edit / Add a new link','wp_keywordlink');?></h3>
 		<a name="keywordeditor"></a><form name=wp_keywordadd id=wp_keywordadd method="post" action="">
		<input type=hidden name=action value=save />
		<table>
		<tr><td><label for=keyword>Keyword</label></td><td><input type=text name=keyword />*</td></tr>
		<tr><td><label for=link>Link</label></td><td><input type=text size=50 maxlength=400 name=link />*</td></tr>
		<tr><td><label for=desc>Description</label></td><td><input type=text size=50 maxlength=500 name=desc /></td></tr>
		<tr><td></td><td><input type=checkbox id=nofollow name=nofollow value="1" <?php if($last_nofollow) { echo 'checked';} ?>/><label for=nofollow>No Follow</label>
		| <input type=checkbox id=firstonly name=firstonly value="1" <?php if($last_firstonly) { echo 'checked';} ?>/>&nbsp;<label for=firstonly>First Match Only</label>
		| <input type=checkbox id=newwindow name=newwindow value="1" <?php if($last_newwindow) { echo 'checked';} ?>/>&nbsp;<label for=newwindow>New Window</label>
		| <input type=checkbox id=ignorecase name=ignorecase value="1" <?php if($last_ignorecase) { echo 'checked';} ?>/>&nbsp;<label for=ignorecase>Ignore case</label>
		| <input type=checkbox id=isaffiliate name=isaffiliate value="1" <?php if($last_isaffiliate) { echo 'checked';} ?>/>&nbsp;<label for=isaffiliate>Is Affiliate</label>
		| <input type=checkbox id=docomments name=docomments value="1" <?php if($last_docomments) { echo 'checked';} ?>/>&nbsp;<label for=docomments>Filter in comments?</label>		
		| <input type=checkbox id=zh_CN name=zh_CN value="1" <?php if($last_zh_CN) { echo 'checked';} ?>/>&nbsp;<label for=zh_CN>For zh_CN?</label>	
		</td></tr>
		<tr><td><input type=submit value="Save" /></td></tr></table>
		</form>

		<?php 
}

function wp_keywordlink_tag2keyword()
{
	if(isset($_POST["action"])) {
		if ($_POST["action"]=='tags2keyword_on') {wp_tags2keyword_updated(); }
	}
	?>
		 <p></p><h3 class="title"><?php _e('Auto change Post tags to Keyword:','wp_keywordlink'); ?>
		 <form name=tags2keyword_form id=tags2keyword_form method="post" action="" style="margin:0px;display: inline">
		 <input type="hidden" name="action" value="tags2keyword_on" />
		 <input type="hidden" name="tags2keyword" value="<?php $wp_tags2keyword = get_option(WP_TAGS2KEYWORD_OPTION); if($wp_tags2keyword) { echo "Disable";} else { echo "Enable";} ?>" />
		 <input type=submit class=button-primary name=tags2keyword_submit value="<?php $wp_tags2keyword = get_option(WP_TAGS2KEYWORD_OPTION); if($wp_tags2keyword) { _e('Disable','wp_keywordlink'); } else { _e('Enable','wp_keywordlink'); } ?>" />
		 </form>
		 </h3>
		 <p></p>

	<?php
}

function wp_tags2keyword_updated()
{
	if(isset($_POST['tags2keyword'])) {
		$tags2keyword = $_POST['tags2keyword'];
		if($tags2keyword == 'Enable') { $tags2keyword_v = '1'; } else {  $tags2keyword_v = '0';}
		update_option(WP_TAGS2KEYWORD_OPTION,$tags2keyword_v); 
        wp_keywordlink_topbarmessage(__('Congratulate, Updated success','wp_keywordlink'));
	}
}

function wp_tags2keyword_replace_content()
{
  global $wp_tags2keyword;
  $wp_tags2keyword = get_option(WP_TAGS2KEYWORD_OPTION);
  if ($wp_tags2keyword) { wp_tags2keyword_replace($content); add_filter('the_content','wp_tags2keyword_replace',101); }
}

function wp_keywordlink_global_options(){
	if(isset($_POST[action])){
		if($_POST[action] == 'global_options'){
			$match_num_from = $_POST[match_num_from];
			$match_num_to  =  $_POST[match_num_to];
			if(!$match_num_from || !$match_num_to){ $match_num_from= 2; $match_num_to = 3; }
			if($match_num_from > $match_num_to){ $match_num_from = $_POST[match_num_to]; $match_num_to = $_POST[match_num_from]; }
			$link_itself = $_POST[link_itself];
			if(!$link_itself){ $link_itself = 0; }
			$ignore_pre = $_POST[ignore_pre];
			if(!$ignore_pre){ $ignore_pre = 0; }
			$ignore_page = $_POST[ignore_page];
			if(!$ignore_page){ $ignore_page = 0; }
			$support_author = $_POST[support_author];
			if(!$support_author){ $support_author = 0; }
			$global_options = implode( "|", array($match_num_from, $match_num_to, $link_itself, $ignore_pre, $ignore_page, $support_author) );
		    update_option(WP_GLOBAL_OPTION,$global_options); 
            wp_keywordlink_topbarmessage(__('Congratulate, Updated success','wp_keywordlink'));
		}
	}
    
	$the_global_options = get_option(WP_GLOBAL_OPTION);
	if($the_global_options){
		list($match_num_from, $match_num_to, $link_itself, $ignore_pre, $ignore_page, $support_author) = explode("|", $the_global_options);
	}else{
		$match_num_from = 2;
		$match_num_to = 3;
		$link_itself = 0;
		$ignore_pre = 0;
		$ignore_page =1;
		$support_author =0;
	}
	?>
	<p><b><?php _e('Global options for keyword link, include tags2keyword.', 'wp_keywordlink'); ?></b></p>
	<form name=global_options id=global_options method=POST action=''>
	<input type=hidden name=action value=global_options>
    <p><label for=match_num><?php _e('Number of Keyword matched:', 'wp_keywordlink'); ?> </label><input type=text size=5 name=match_num_from value=<?php echo $match_num_from;?> /> - <input type=text size=5 name=match_num_to value=<?php echo $match_num_to;?> /> <a title="<?php _e('Disable if choose - First Match Only','wp_keywordlink');?>">[?]</a></p>
	<p><lable for=link_itself><?php _e('Keywords does not link itself:', 'wp_keywordlink'); ?> </lable><input type=checkbox name=link_itself value="1" <?php if($link_itself) { echo 'checked';} ?> /></p>
	<p><lable for=ignore_pre><?php _e('Ignore pre label:', 'wp_keywordlink'); ?> </lable><input type=checkbox name=ignore_pre value="1" <?php if($ignore_pre) { echo 'checked';} ?> /> <a title="<?php _e('Escape <pre>..</pre> label','wp_keywordlink');?>">[?]</a></p>
	<p><lable for=ignore_page><?php _e('Keywords does not link on Page:', 'wp_keywordlink'); ?> </lable><input type=checkbox name=ignore_page value="1" <?php if($ignore_page) { echo 'checked';} ?> /></p>
	<p><lable for=support_author><?php _e('Support Author:', 'wp_keywordlink'); ?> </lable><input type=checkbox name=support_author value="1" <?php if($support_author) { echo 'checked';} ?> /> <a title="<?php _e('Add a Link on wp_footer to suport liucheng.name. Thanks~','wp_keywordlink');?>">[?]</a></p>
	<p><input class=button-primary type=submit value="Save" /></p>
	</form>
	<?php
}

function wp_keywordlink_related_posts(){
	#echo "related_posts... coming soon~";
}

function wp_keywordlink_help()
{
	?>
		<h3><?php _e('Help','wp_keywordlink'); ?></h3>
		<p>
		<?php _e('The Keyword link plugin searches the contents of each of your posts for the above listed keywords. Each keyword found is automatically linked to the link you have specified. For each link you can also specify the following options: ','wp_keywordlink'); ?>
		</p>		
		<ul>
		<li><?php _e('<b>No Follow</b> - This adds a <em>rel= no follow</em> to the link.','wp_keywordlink'); ?></li>
		<li><?php _e('<b>First Match Only</b> - Only replace the first match of the word, ignore further mentions. Otherwise will replace 2 or 3.','wp_keywordlink'); ?></li>
		<li><?php _e('<b>New Window</b> - This adds a <em>target=_blank</em> to the link, forcing a new browser window on clicking.','wp_keywordlink'); ?></li>
		<li><?php _e('<b>Ignore Case</b> - Google, google and gooGLE are all fine.(<b>Not recommended, There may be problems</b>)','wp_keywordlink'); ?></li>
		<li><?php _e('<b>Is affiliate</b> - Allows you to tell your visitors that the link is an affiliate.','wp_keywordlink'); ?></li>
		<li><?php _e('<b>Filter in comments</b> - Also replace this keyword in post comments.','wp_keywordlink'); ?></li>
		<li><?php _e('<b>*For zh_CN</b> - For chinese keyword, or others language do not split by "space"','wp_keywordlink'); ?></li>
		<li><?php _e('<b>* Escape &lt;pre&gt;&lt;/pre&gt; region, or use custom tag &lt;wp_nokeywordlink&gt;&lt;/wp_nokeywordlink&gt; to escape keywordlink.</b>','wp_keywordlink'); ?></li>
		</ul>
		<p>
		<?php _e('Each link created by the plugin is contained in an &lt;span class= wp_keywordlink &gt; .. &lt;/span&gt; wrapper. This allows you to modify the links by adding a style to your themes style.css file.','wp_keywordlink'); ?></p>
		<p><?php _e('Affiliate links work a little different, they use &lt;span class= wp_keywordlink_affiliate &gt; .. &lt;/span&gt; allowing you to differentiate those paid for links from your internal links.','wp_keywordlink'); ?></p>		
		<p>
		Example style.css: 
      <pre>
		.wp_keywordlink { text-decoration: underline; }
		.wp_keywordlink_affiliate { font-weight: bold; }						
		</pre>				
		</p>

		<p>please Contact: <a href="http://liucheng.name/789/">柳城(liucheng)</a>, Email:<a href="mailto:i@liucheng.name">i@liucheng.name</a>
		</p>

	<?php
}

function wp_keywordlink_savenew()
{
      $links = get_option(WP_KEYWORDLINK_OPTION);
		
		$keyword = $_POST['keyword'];
		$link = $_POST['link'];
		$desc = $_POST['desc'];
		$nofollow = ($_POST['nofollow']=="1") ? "1" : "0";
 		$firstonly = ($_POST['firstonly']=="1") ? "1" : "0";
 		$newwindow = ($_POST['newwindow']=="1") ? "1" : "0";
 		$ignorecase = ($_POST['ignorecase']=="1") ? "1" : "0";
 		$isaffiliate = ($_POST['isaffiliate']=="1") ? "1" : "0";
 		$docomments = ($_POST['docomments']=="1") ? "1" : "0"; 
 		$zh_CN = ($_POST['zh_CN']=="1") ? "1" : "0"; 
		
		if ($keyword == '' || $link == '')
		{
		  wp_keywordlink_topbarmessage(__('Please enter both a keyword and URL','wp_keywordlink'));
		  return;     		  
		}

		if ( is_numeric($keyword) )
		{
		  wp_keywordlink_topbarmessage(__('Error: Can not use Number as keyword!','wp_keywordlink'));
		  return;     		  
		}

		if ( preg_match("/#/",$keyword) )
		{
		  wp_keywordlink_topbarmessage(__('Error: Can not use characters "#"','wp_keywordlink'));
		  return;     		  
		}
		
		if (isset($links[$keyword]))
		{
		  wp_keywordlink_topbarmessage(__('Existing keyword has been updated','wp_keywordlink'));
		}
				
 		/* Store the link */ 
	  $links[$keyword] = implode('|',array($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN,$desc));
	  
	  update_option(WP_KEYWORDLINK_OPTION,$links);      
}

function wp_keywordlink_deletekeyword()
{
	  $links = get_option(WP_KEYWORDLINK_OPTION);
      $keyword = $_POST['keyword'];

	  if( preg_match("/#/",$keyword) ){
		  $keywords = explode("#",$keyword);
	  }

	  if($keywords){
		  foreach($keywords as $keyword){
			  if (!isset($links[$keyword])) continue;
			  unset($links[$keyword]);
		  }
	  }else{		
		if (!isset($links[$keyword]))
		{
		  wp_keywordlink_topbarmessage(__('No such keyword, bizarre error!','wp_keywordlink'));
		  return;     		  
		}
		unset($links[$keyword]);
	  }
		update_option(WP_KEYWORDLINK_OPTION,$links);
} 

/**
 * Returns the URL to the directory where the plugin file is located
 * @since 3.0b5
 * @access private
 * @author Arne Brachhold
 * @return string The URL to the plugin directory
 */
if(!function_exists('wp_keywordlink_GetPluginUrl')) {
	function wp_keywordlink_GetPluginUrl() {
		
		//Try to use WP API if possible, introduced in WP 2.6
		if (function_exists('plugins_url')) return trailingslashit(plugins_url(basename(dirname(__FILE__))));
		
		//Try to find manually... can't work if wp-content was renamed or is redirected
		$path = dirname(__FILE__);
		$path = str_replace("\\","/",$path);
		$path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
		return $path;
	}
}

function wp_keywordlink_optionpage()
{
      /* Perform any action */
		if(isset($_POST['action'])) {
			if ($_POST['action']=='save') wp_keywordlink_savenew(); 
			}
		if(isset($_POST['action'])) {
			if ($_POST['action']=='delete') wp_keywordlink_deletekeyword();
		}
		if(isset($_POST['action'])) {
			if ($_POST['action']=='importcvs') wp_keywordlink_cvsimport();
		}
		if(isset($_POST['page_limit'])) {
			if ($_POST['page_limit']) wp_pagelimit_updated();
		}
		if(isset($_POST['wp_keywordlink_deactivate_yes'])) {
			if ($_POST['wp_keywordlink_deactivate_yes']) {
				wp_keywordlink_deactivate();
			}
		}
		/*Note: exportcvs is called from the init action linked below */

		/* css */
		wp_keywordlink_css();
		
		/* Definition */
      echo '<div class="wrap"><div style="background: url('.wp_keywordlink_GetPluginUrl().'liucheng_name32.png) no-repeat;" class="icon32"><br /></div>';
		echo '<h2>WP Keyword Link</h2>';

		/* Introduction */ 
		echo '<p>'.__('This plugin automatically links keywords in your posts to their definition pages. And displays a list of posts similar to the current post.','wp_keywordlink').'</p>';	

		 PayPal_Donate();
?>

<div class="tabmenu">
<ul>
<li class="tabone"><a href="#" onmouseover="easytabs('1', '1');" onfocus="easytabs('1', '1');" onclick="return false;"  title="" id="tablink1">KeywordLink</a></li>
<li class="tabtwo"><a href="#" onmouseover="easytabs('1', '2');" onfocus="easytabs('1', '2');" onclick="return false;"  title="" id="tablink2">Options for KeywordLink</a></li>
<li class="tabthree"><a href="#" onmouseover="easytabs('1', '3');" onfocus="easytabs('1', '3');" onclick="return false;"  title="" id="tablink3">Related Posts</a></li>
<li class="tabfour"><a href="#" onmouseover="easytabs('1', '4');" onfocus="easytabs('1', '4');" onclick="return false;"  title="" id="tablink4">Help </a></li>
</ul>
</div>

<div id="tabcontent1">
<?php
		/* Show the existing options */
		wp_keywordlink_showdefinitions();
		
		/* Allow adding a new link */ 
		wp_keywordlink_addnew();
		
		/* Allow important and exporting to CVS */
		wp_keywordlink_cvsmenu();

		/* tag2keyword */
		wp_keywordlink_tag2keyword();

?>
<form name=wp_keywordlink_deactivate id=wp_keywordlink_deactivate method=POST action=''>
	<p>&nbsp;</p> 
	<p style="text-align: center;"> 
		<input type="checkbox" name="wp_keywordlink_deactivate_yes" value="1" />&nbsp;Yes(error?)<br /><br /> 
		<input type="submit" name="do" value="Clear KeywordLink" class="button" onclick="return confirm('Delete keywordlink when unknown error.\n当未知错误时删除关键词.请慎用\n\n Choose [Cancel] To Stop, [OK] To Uninstall.')" /> 
	</p> 
</form>
</div>

<div id="tabcontent2">
<?php
		/* Global Options for keyword link */
		wp_keywordlink_global_options();
?>
</div>

<div id="tabcontent3">
<?php
		/* Show related posts */
		wp_keywordlink_related_posts();
		wp_options_page();
		wp_similarly_help();
?>
</div>

<div id="tabcontent4">
<?php
		/* Show help information */
		wp_keywordlink_help(); 		
?>
</div>

<?php
	    #bobaiyou();
		echo '</div>';
}

/* wp_keywordlink_replace
 *
 * This is where everything happens... search the content and search for our set of keywords
 * and add the links in the right places. 
*/

function wp_keywordlink_replace($content,$iscomments)
{
	 global $wp_keywordlinks;
     $links = $wp_keywordlinks; 

	$the_global_options = get_option(WP_GLOBAL_OPTION);
	if($the_global_options){
		list($match_num_from, $match_num_to, $link_itself, $ignore_pre, $ignore_page, $support_author) = explode("|", $the_global_options);
	}else{
		$match_num_from = 2;
		$match_num_to = 3;
		$link_itself = 0;
		$ignore_pre = 0;
		$ignore_page =1;
		$support_author =0;
	}

     if ($links)
	 	 foreach ($links as $keyword => $details)
		 {
			   list($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN,$desc) = explode("|",$details);
			   
				// If this keyword is not tagged for replacement in comments we continue
				if ($iscomments && $docomments==0)
					continue;

				//如果是链接本身,则跳过.
				if($link_itself){
					$recent_url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
					if($link == $recent_url)
						continue;
				}
			   
				//跳过Page页面
				if( $ignore_page && is_page() )
					continue;

			   $cleankeyword = stripslashes($keyword); 

			   if(!$desc){ $desc = $cleankeyword; }
			   $desc = addcslashes($desc, '$');
		 		if ($isaffiliate)
		 		   $url  = "<span class='wp_keywordlink_affiliate'>";
		 		else
		 			$url  = "<span class='wp_keywordlink'>";
		 			
		 	   $url .= "<a href=\"$link\" title=\"$desc\"";

				if ($nofollow) $url .= ' rel="nofollow"';
				if ($newwindow) $url .= ' target="_blank"';
		 	   
		 	   $url .= ">".addcslashes($cleankeyword, '$')."</a>";
		 	   $url .= "</span>";
		 	   
				if ($firstonly) $limit = 1; else $limit= rand($match_num_from,$match_num_to);
				if ($ignorecase) $case = "i"; else $case="";

				// The regular expression comes from an older 
				// auto link plugin by Sean Hickey. It fixed the autolinking inside a link
				// problem. Thanks to [Steph] for the code.

		// we don't want to link the keyword if it is already linked.
		// so let's find all instances where the keyword is in a link and change it to &&&&&, which will be sufficient to avoid linking it. We use //&&&&&, since WP would pass that
        // the idea is come from 'kb-linker'
				  $ex_word = preg_quote($cleankeyword,'\'');
				  //ignore pre & ignore_keywordlink
			      if( $num_2 = preg_match_all("/<wp_nokeywordlink>.*?<\/wp_nokeywordlink>/is", $content, $ignore_keywordlink) )
					  for($i=1;$i<=$num_2;$i++)
						  $content = preg_replace( "/<wp_nokeywordlink>.*?<\/wp_nokeywordlink>/is", "%ignore_keywordlink_$i%", $content, 1);
				  if($ignore_pre){
					  if( $num_1 = preg_match_all("/<pre.*?>.*?<\/pre>/is", $content, $ignore_pre) )
						  for($i=1;$i<=$num_1;$i++)
							  $content = preg_replace( "/<pre.*?>.*?<\/pre>/is", "%ignore_pre_$i%", $content, 1);
				  }

                  //$content = preg_replace( '|(<a[^>]+>)(.*)('.$ex_word.')(.*)(</a[^>]*>)|U', '$1$2%&&&&&%$4$5', $content);
				  $content = preg_replace( '|(<img)([^>]*)('.$ex_word.')([^>]*)(>)|U', '$1$2%&&&&&%$4$5', $content);

        
				// For keywords with quotes (') to work, we need to disable word boundary matching
				$cleankeyword = preg_quote($cleankeyword,'\'');

				if ($zh_CN)
					$regEx = '\'(?!((<.*?)|(<a.*?)))(' . $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;
                elseif (strpos( $cleankeyword  , '\'')>0)
				    $regEx = '\'(?!((<.*?)|(<a.*?)))(' . $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;
				else
    				 $regEx = '\'(?!((<.*?)|(<a.*?)))(\b'. $cleankeyword . '\b)(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;	
				
				$content = preg_replace($regEx,$url,$content,$limit);

	//change our '%&&&&&%' things to $cleankeyword.
	##$content = str_replace( '%&&&&&%', $zn_word, $content);
	$content = str_replace( '%&&&&&%', stripslashes($ex_word), $content);

    //ignore pre & ignore_keywordlink
	if($ignore_pre){
		for($i=1;$i<=$num_1;$i++){
			$content = str_replace( "%ignore_pre_$i%", $ignore_pre[0][$i-1], $content);
		}
	}
	for($i=1;$i<=$num_2;$i++){
		$content = str_replace( "%ignore_keywordlink_$i%", $ignore_keywordlink[0][$i-1], $content);
	}
	}// end if($links)
	return $content; 
}

#### 按长度排序
function my_sort_by_len($a, $b){
	if ( $a->name == $b->name ) return 0;
	return ( strlen($a->name) > strlen($b->name) ) ? -1 : 1;
}
######New: change Tags to Keywords
function wp_tags2keyword_replace($content)
{
	$the_global_options = get_option(WP_GLOBAL_OPTION);
	if($the_global_options){
		list($match_num_from, $match_num_to, $link_itself, $ignore_pre, $ignore_page, $support_author) = explode("|", $the_global_options);
	}else{
		$match_num_from = 2;
		$match_num_to = 3;
		$link_itself = 0;
		$ignore_pre = 0;
		$ignore_page =1;
		$support_author =0;
	}

	 $posttags = get_the_tags();
	 //echo "<pre>";
	 //print_r ($posttags);
	 //echo "</pre>";
	 if ($posttags) {
		 usort($posttags, "my_sort_by_len");
		 foreach($posttags as $tag) {
			 $link = get_tag_link($tag->term_id); 
			 $keyword = $tag->name;

			//如果是链接本身,则跳过.
			if($link_itself){
				$recent_url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
				if($link == $recent_url)
					continue;
			}

			//跳过Page页面
			if( $ignore_page && is_page() )
				continue;

			 $cleankeyword = stripslashes($keyword);
			 $url  = "<span class='wp_keywordlink_affiliate'>";
			 $url .= "<a href=\"$link\" title=\"".str_replace('%s',addcslashes($cleankeyword, '$'),__('View all posts in %s'))."\"";
			 $url .= ' target="_blank"';
			 $url .= ">".addcslashes($cleankeyword, '$')."</a>";
			 $url .= "</span>";
			 $limit = rand($match_num_from,$match_num_to);
			 $case="";
			 $zh_CN = "1";
			 
             // we don't want to link the keyword if it is already linked.
			 $ex_word = preg_quote($cleankeyword,'\'');
			 //ignore pre & ignore_keywordlink
			      if( $num_2 = preg_match_all("/<wp_nokeywordlink>.*?<\/wp_nokeywordlink>/is", $content, $ignore_keywordlink) )
					  for($i=1;$i<=$num_2;$i++)
						  $content = preg_replace( "/<wp_nokeywordlink>.*?<\/wp_nokeywordlink>/is", "%ignore_keywordlink_$i%", $content, 1);
				  if($ignore_pre){
					  if( $num_1 = preg_match_all("/<pre.*?>.*?<\/pre>/is", $content, $ignore_pre) )
						  for($i=1;$i<=$num_1;$i++)
							  $content = preg_replace( "/<pre.*?>.*?<\/pre>/is", "%ignore_pre_$i%", $content, 1);
				  }
				  
             //$content = preg_replace( '|(<a[^>]+>)(.*)('.$ex_word.')(.*)(</a[^>]*>)|U'.$case, '$1$2%&&&&&%$4$5', $content);
			 $content = preg_replace( '|(<img)([^>]*)('.$ex_word.')([^>]*)(>)|U', '$1$2%&&&&&%$4$5', $content);

				// For keywords with quotes (') to work, we need to disable word boundary matching
				$cleankeyword = preg_quote($cleankeyword,'\'');
				if ($zh_CN){
					$regEx = '\'(?!((<.*?)|(<a.*?)))('. $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;
				}
                elseif (strpos( $cleankeyword  , '\'')>0) {
				    $regEx = '\'(?!((<.*?)|(<a.*?)))(' . $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;
				}
				else {
    			    $regEx = '\'(?!((<.*?)|(<a.*?)))(\b'. $cleankeyword . '\b)(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;		
				}
				$content = preg_replace($regEx,$url,$content,$limit);

	//change our '%&&&&&%' things to $cleankeyword.
	##$content = str_replace( '&&&&&', $zn_word, $content);
	$content = str_replace( '%&&&&&%', stripslashes($ex_word), $content);

	//ignore pre & ignore_keywordlink
	if($ignore_pre){
		for($i=1;$i<=$num_1;$i++){
			$content = str_replace( "%ignore_pre_$i%", $ignore_pre[0][$i-1], $content);
		}
	}
	for($i=1;$i<=$num_2;$i++){
		$content = str_replace( "%ignore_keywordlink_$i%", $ignore_keywordlink[0][$i-1], $content);
	}
		 }// end foreach 
	 } // end if($posttags)
    return $content; 
}

function wp_keywordlink_replace_content($content)
{
	return wp_keywordlink_replace($content,false);
}

function wp_keywordlink_replace_comments($content)
{
	return wp_keywordlink_replace($content,true);
}

function wp_pagelimit_updated()
{
	if(isset($_POST['page_limit'])) {
		$page_limit = $_POST['page_limit'];
		update_option(WP_PAGELIMIT_OPTION,$page_limit); 
        wp_keywordlink_topbarmessage(__('Congratulate, Updated success','wp_keywordlink'));
	}
}

/* wp_keywordlink_init
 *
 * As we are now called for both the content and comments we will be
 * doing some steps several times. To avoid slowing down things too
 * much the repetative bits are cached here.
*/

function wp_keywordlink_init()
{
	  global $wp_keywordlinks;
     $wp_keywordlinks = get_option(WP_KEYWORDLINK_OPTION);
     /*if ($wp_keywordlinks) {
       if(function_exists(krsort)) { krsort($wp_keywordlinks); }
	 }*/
}

function wp_keywordlink_last_options()
{
	 global $last_link_options;
	 $links = get_option(WP_KEYWORDLINK_OPTION);
     if($links)
		 $last_link_options = array_pop($links);	
}


function to_suport_author(){
	$the_global_options = get_option(WP_GLOBAL_OPTION);
	if($the_global_options){
		list($match_num_from, $match_num_to, $link_itself, $ignore_pre, $ignore_page, $support_author) = explode("|", $the_global_options);
	}else{
		$match_num_from = 2;
		$match_num_to = 3;
		$link_itself = 0;
		$ignore_pre = 0;
		$ignore_page =1;
		$support_author =0;
	}
	if($support_author){
		echo '<!-- WP Keyword Link by liucheng.name -->';
	}
}

function bobaiyou(){
	echo "<p>";
	echo "</p>";
}

function PayPal_Donate(){
	?>
<!--<div style="float: right;">-->
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" style="margin:0px;display:inline">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1">
</form>
<!--</div>-->
    <?php
}

function wp_keywordlink_css(){
	echo '<style type="text/css">
	.tabmenu {background-color:#db307a; color:#272727; height:26px;margin:0;}
	.tabmenu ul {margin:0px; padding:0px; list-style:none; }
	.tabmenu li {display:inline; line-height:26px;margin:0 -3px 0 0;}
	.tabmenu li a {color:#fff; text-decoration:none; padding:5px 17px 5px 17px; border-left:1px solid #fff; font-weight:bold; font-size:14px;}
	.tabmenu li.tabone a { border-left:none;}
	.tabmenu li.tabtwo a { }
	.tabmenu li.tabfour a { }
	.tabmenu li.tabthree a { padding:5px 25px 5px 25px;}
	.tabmenu li a.tabactive {background-color:#3dc7d6;   position:relative;}
	#tabcontent1,#tabcontent2,#tabcontent3,#tabcontent4 {padding:0px 6px; font-size:12px; margin-bottom:10px;overflow:hidden;height:100%;}
	</style><script type="text/javascript" language="javascript" src="'.wp_keywordlink_GetPluginUrl().'js/easytabs.js"></script>';
}


 /* Tie the module into Wordpress */
add_action('admin_menu','wp_keywordlink_admininit');
add_filter('the_content','wp_keywordlink_replace_content',100);
add_filter('comment_text','wp_keywordlink_replace_comments',100);

## WooCommerce
add_filter('woocommerce_content','wp_keywordlink_replace_content',100);


## bbpress
add_filter('bbp_get_topic_content','wp_keywordlink_replace_content',100);
add_filter('bbp_get_reply_content','wp_keywordlink_replace_content',100);


add_action('init','wp_keywordlink_checkcvs');
add_action('init','wp_keywordlink_init');
add_action('init','wp_tags2keyword_replace_content');
add_action('init','wp_keywordlink_last_options');
#add_action('admin_head','wp_keywordlink_css');
add_action('wp_footer','to_suport_author');

function wp_keywordlink_deactivate(){
		global $wpdb;
		$wpdb->query("DELETE FROM $wpdb->options WHERE option_name =  'wp_keywordlinkoption' LIMIT 1");
}
?>