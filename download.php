<?php
require( dirname(__FILE__) . '/wp-load.php' );
$id=$_GET['id'];
$title = get_post($id)->post_title;
$xydown_name=get_post_meta($id, 'xydown_name', true);
$xydown_size=get_post_meta($id, 'xydown_size', true);
$xydown_date=get_post_meta($id, 'xydown_date', true);
$xydown_version=get_post_meta($id, 'xydown_version', true);
$xydown_author=get_post_meta($id, 'xydown_author', true);
$xydown_downurl1=get_post_meta($id, 'xydown_downurl1', true);
$xydown_downurl2=get_post_meta($id, 'xydown_downurl2', true);
$xydown_downurl3=get_post_meta($id, 'xydown_downurl3', true);
$xydown_downurl4=get_post_meta($id, 'xydown_downurl4', true);
$xydown_downurl5=get_post_meta($id, 'xydown_downurl5', true);
$xydown_downurl6=get_post_meta($id, 'xydown_downurl6', true);
$xydown_downurl7=get_post_meta($id, 'xydown_downurl7', true);
$xydown_baidumima=get_post_meta($id, 'xydown_baidumima', true);
$xydown_360mima=get_post_meta($id, 'xydown_360mima', true);
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo dirname('http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]); ?>/wp-content/plugins/xydown/css/download.css" />
<title><?php echo $title;?>下载页面</title>
<meta name="keywords" content="<?php echo $title;?>" />
<meta name="description" content="<?php echo $title;?>下载" />
</head>
<body>
<div id="header">
	
</div>
<h1><a href="<?php echo dirname('http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]); ?>/?p=<?php echo $id;?>"><?php echo $title;?></a></h1>

<div class="clear"></div>

<div class="desc">
<h3>下载文件资源信息</h3>
<p>文件名称：<?php echo $xydown_name;?></p>
<p>文件大小：<?php echo $xydown_size;?></p>
<p>适用版本：<?php echo $xydown_version;?></p>
<p>更新日期：<?php echo $xydown_date;?></p>
<p>作者信息：<?php echo $xydown_author;?></p>
<p>网盘密码：<?php if($xydown_baidumima){?>百度网盘密码：<?php echo $xydown_baidumima;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php } if($xydown_360mima){?>360网盘密码：<?php echo $xydown_360mima;?><?php }?></p>
<p></p>
</div>
<div class="clear"></div>
<div class="list">
	下载列表：<?php if($xydown_downurl1){?><a href="<?php echo $xydown_downurl1;?>" target="_blank">百度网盘</a><?php } if($xydown_downurl4){?><a href="<?php echo $xydown_downurl4;?>" target="_blank">迅雷快传<font color="red">(推荐)</font></a><?php }if($xydown_downurl5){?><a href="<?php echo $xydown_downurl5;?>" target="_blank">360网盘<font color="red">(推荐)</font></a><?php }if($xydown_downurl6){?><a href="<?php echo $xydown_downurl6;?>" target="_blank">其他网盘</a><?php }if($xydown_downurl7){?><a href="<?php echo $xydown_downurl7;?>" target="_blank">官方下载<font color="red">(推荐)</font></a><?php }if($xydown_downurl2){?><a href="<?php echo $xydown_downurl2;?>" target="_blank">城通网盘<font color="red">(推荐)</font></a><?php } if($xydown_downurl3){?><a href="<?php echo $xydown_downurl3;?>" target="_blank"><font color="red">普通下载</font></a><?php }?>
</div>
<div class="clear"></div>

<div class="desc" style="border:none">

</div>
<div class="clear"></div>

<div class="desc"  style="border:1px solid #FCC;background:#FFE;">
	<p>下载说明</p>
	<ol style="padding:0 10px 0 25px;">
		<li>下载后文件若为压缩包格式，请安装RAR或者好压软件进行解压。</li>
		<li>文件比较大的时候，建议使用下载工具进行下载，浏览器下载有时候会自动中断，导致下载错误</li>
		<li>资源可能会由于内容问题被和谐，导致下载链接不可用，遇到此问题，请到文章页面进行反馈，我们会及时进行更新的。</li>
        <li>其他下载问题请自行搜索教程，这里不一一讲解</li>
	</ol>
</div>
<div class="sm" style="padding:5px">
	<p>声明：</p>
	<p>本站大部分下载资源收集于网络，只做学习和交流使用，版权归原作者所有，若为付费资源，请在下载后24小时之内自觉删除，若作商业用途，请到原网站购买，由于未及时购买和付费发生的侵权行为，与本站无关。本站发布的内容若侵犯到您的权益，请联系本站删除，我们将及时处理！</p>
</div>
<div class="clear"></div>

<div class="copy" style="text-align: center;margin-top:15px;">
	Copyright © 2015-2016 <a href="http://www.wuaicc.com/">宝贝吾爱淘</a> 版权所有 
</div>

</body>
</html>