<?php
/*
Template Name: 演示页面
*/
?>
<?php
/**
 * Modified By zzyzan.com
**/
?>
<?php
    require_once(dirname(__FILE__) . '/addons/custom-post-download-demourl/custom-post-download-demourl.php');
    $post_id = $_GET['id'];
    $meta = theme_custom_download_demourl::get_post_meta($post_id);
    $demo = theme_custom_download_demourl::get_text($meta['download_demourl']);
    if(empty($demo)){
        Header('Location:/');
    }
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="小乖乖资源共享网在线演示" />
<meta name="keywords" content="演示" />
<link rel="shortcut icon" href="favicon.ico" />
<title>网站演示：<?php echo get_the_title($post_id); ?> – 小乖乖</title>
<style type="text/css">
html body{color:#eee;font-family:Verdana,Helvetica,Arial,sans-serif;font-size:10px;height:100%;margin:0;overflow:hidden;padding:0;width:100%;}#header-bar{background:#333;font-size:10px;z-index:100;margin:0;padding:0;color:#eee;height:36px;line-height:36px;}#header-bar a.site-loopback{background-position:left top;background-repeat:no-repeat;display:block;float:right;margin-left:-10px;text-indent:-9999px;}#header-bar .preview-logo{height:36px;width:118px;margin-right:30px;}#header-bar p.meta-data{float:left;margin:0;padding:0;}#header-bar p.meta-data p{display:inline;margin:0;}#header-bar p.meta-data a{color:#e6f6f6;text-decoration:none;}#header-bar p.meta-data a.back{border-left:1px solid #545454;margin-left:10px;padding-left:15px;}#header-bar p.meta-data a:hover,#header-bar p.meta-data a.activated{color:#FFFFFF;}#header-bar div.close-header{float:left;height:29px;margin-left:15px;width:30px;}#header-bar div.close-header a#close-button{background-repeat:no-repeat;display:block;overflow:hidden;color:#fff;text-decoration:initial;}#header-bar div.close-header a#close-button:hover,#header-bar div.close-header a#close-button.activated{background-position:0 -12px;}#header-bar span.preview{color:#D2D1D0;display:none;font-family:MgOpen Modata,Tahoma,Geneva;font-size:13px;letter-spacing:1px;margin-left:10px;padding-left:20px;text-decoration:none;}
#preview-frame{background-color:#FFFFFF;width:100%;}.preview-logo{background:url("images/demo-logo.png") no-repeat 0px 3px;text-indent:-9999px;display:block;float:right;}
</style>
<script src="http://apps.bdimg.com/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript">
    var calcHeight = function() {
        var headerDimensions = $('#header-bar').height();
        $('#preview-frame').height($(window).height() - headerDimensions);
    }      
    $(document).ready(function() {
        calcHeight();
        $('#header-bar a.close').mouseover(function() {
            $('#header-bar a.close').addClass('activated');
        }).mouseout(function() {
            $('#header-bar a.close').removeClass('activated');
        });
    });      
    $(window).resize(function() {
        calcHeight();
    }).load(function() {
        calcHeight();
    });
</script>
</head>
<body>
<div id="header-bar">
<div class="close-header">
<script type="text/javascript">document.write("<a id=\"close-button\" title=\"关闭工具条\" class=\"close\" href=\"<?php echo $demo; ?>\">X</a>");</script>
</div>
<p class="meta-data">
<script type="text/javascript">document.write("<a target=\"_blank\" class=\"close\" href=\"<?php echo $demo; ?>\">移除顶部</a>");</script> <a class="back" href="<?php echo get_permalink($post_id); ?>">返回原文：<?php echo get_the_title($post_id); ?></a> <a class="back" href="/">返回首页</a>
</p>
<a class="preview-logo" href="/" title="小乖乖资源共享网">龙笑天下</a>
</div>
<script type="text/javascript">
document.write("<iframe id=\"preview-frame\" src=\"<?php echo $demo; ?>\" name=\"preview-frame\" frameborder=\"0\" noresize=\"noresize\"></iframe>");
</script>
</body>
</html>