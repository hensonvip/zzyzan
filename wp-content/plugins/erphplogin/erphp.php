<div class="wrap">
<?php 
if($_POST['Submit'] && current_user_can('administrator') && $_POST['Submit']=='保存设置')
{
	$erphplogin_qqid    = trim($_POST['erphplogin_qqid']);
	$erphplogin_qqkey    = trim($_POST['erphplogin_qqkey']);
	$erphplogin_sinaid = trim($_POST['erphplogin_sinaid']);
	$erphplogin_sinakey   = trim($_POST['erphplogin_sinakey']);
	$erphplogin_url   = trim($_POST['erphplogin_url']);
	
	$update_text=array('qqid','qqkey','sinaid','sinakey','erphploginurl');
	$update_erphplogin[] = update_option('erphplogin_qqid', $erphplogin_qqid);
	$update_erphplogin[] = update_option('erphplogin_qqkey', $erphplogin_qqkey);
	$update_erphplogin[] = update_option('erphplogin_sinaid', $erphplogin_sinaid);
	$update_erphplogin[] = update_option('erphplogin_sinakey', $erphplogin_sinakey);
	$update_erphplogin[] = update_option('erphplogin_url', $erphplogin_url);
	foreach($update_erphplogin as $k=>$v)
	{
		if($v)
		{
			$text .= '<div class="updated settings-error"><p>'.$update_text[$k].' 更新成功！</p></div>';
		}
	}
	if(empty($text))
	{
		$text = '<div class="updated settings-error"><p>没有更新任何信息</p></div>';
	}

}
$erphplogin_qqid    = get_option('erphplogin_qqid');
$erphplogin_qqkey    = get_option('erphplogin_qqkey');
$erphplogin_sinaid = get_option('erphplogin_sinaid');
$erphplogin_sinakey  = get_option('erphplogin_sinakey');
$erphplogin_url  = get_option('erphplogin_url');

if(!empty($text))
{
	echo '<div id="message">'.$text.'</div>';
}
?>

<form method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>" style="width: 70%; float: left;">

		<h2>Erphplogin社交设置</h2>
		<table class="form-table">
			<tr>
				<td valign="top" width="30%"><strong>qqid</strong><br />
				</td>
				<td><input type="text" id="erphplogin_qqid" name="erphplogin_qqid"
					value="<?php echo $erphplogin_qqid ; ?>" maxlength="50" size="50" />
				</td>
			</tr>
			<tr>
				<td valign="top" width="30%"><strong>qqkey</strong><br />
				</td>
				<td><input type="text" id="erphplogin_qqkey" name="erphplogin_qqkey"
					value="<?php echo $erphplogin_qqkey ; ?>" maxlength="50" size="50" />
				</td>
			</tr>
			<tr>
				<td valign="top" width="30%"><strong>sinaid</strong><br />
				</td>
				<td><input type="text" id="erphplogin_sinaid" name="erphplogin_sinaid"
					value="<?php echo $erphplogin_sinaid; ?>" maxlength="50"
					size="50" />
				</td>
			</tr>
			<tr>
				<td valign="top" width="30%"><strong>sinakey</strong><br />
				</td>
				<td><input type="text" id="erphplogin_sinakey" name="erphplogin_sinakey"
					value="<?php echo $erphplogin_sinakey; ?>" maxlength="100" size="50" />
				</td>
			</tr>
            <tr>
				<td valign="top" width="30%"><strong>返回地址</strong><br />
				</td>
				<td><input type="text" id="erphplogin_url" name="erphplogin_url"
					value="<?php echo $erphplogin_url; ?>" maxlength="100" size="50" /><br />（绑定登录后返回的地址，一般是首页或者个人中心页）
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<p class="submit">
						<input type="submit" name="Submit" value="保存设置" class="button-primary" />
					</p>
				</td>
			</tr>
		</table>
	</form>
</div>
<div style="display:none">www.mobantu.com</div>
