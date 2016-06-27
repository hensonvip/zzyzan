<?php
namespace sinapicv2;
class plugin_functions{
	/*
	 * status_tip
	 *
	 * @param mixed
	 * @return string
	 * @example status_tip('error','content');
	 * @example status_tip('error','big','content');
	 * @example status_tip('error','big','content','span');
	 * @version 2.0.3
	 */
	public static function status_tip(){
		$args = func_get_args();
			
		$defaults = array('type','size','content','wrapper');
		$types = array('loading','success','error','question','info','ban','warning');
		$sizes = array('small','middle','large');
		$wrappers = array('div','span');
		$type = null;
		$size = null;
		$wrapper = null;

		switch(count($args)){
			case 1:
				$content = $args[0];
				break;
			case 2:
				$type = $args[0];
				$content = $args[1];
				break;
			default:
				foreach($args as $k => $v){
					$$defaults[$k] = $v;
				}
		}
		if(!$type)
			$type = $types[0];
		if(!$size)
			$size = $sizes[0];
		if(!$wrapper)
			$wrapper = $wrappers[0];

		switch($type){
			case 'success':
				$icon = 'check-circle';
				break;
			case 'error' :
				$icon = 'times-circle';
				break;
			case 'info':
			case 'warning':
				$icon = 'exclamation-circle';
				break;
			case 'question':
			case 'help':
				$icon = 'question-circle';
				break;
			case 'ban':
				$icon = 'minus-circle';
				break;
			case 'loading':
			case 'spinner':
				$icon = 'circle-o-notch fa-spin';
				break;
			default:
				$icon = $type;
		}
		
		return '<' . $wrapper . ' class="tip-status tip-status-' . $size . ' tip-status-' . $type . '"><i class="fa fa-' . $icon . ' fa-fw"></i> ' . $content . '</' . $wrapper . '>';
	}
	/**
	 * get_current_url
	 *
	 * @return string
	 * @version 1.0.3
	 */
	function get_current_url(){
		static $cache = null;
		if($cache !== null)
			return $cache;
		$cache = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		return $cache;
	}
	/**
	 * Html compress
	 * 
	 * 
	 * @params string The html code
	 * @params array(optional) what need to clear
	 * @return string The clear html code
	 * @version 1.0.1
	 * 
	 */
	 
	public static function html_compress($html = null,$param = array()){
		if(!$html) return false;
		$param = $param ? $param : array("\n","\r","\t","\r\n");
		$html = str_replace($param,'',$html);
		return $html;
	}
	/**
	 * json_format
	 *
	 * @param object
	 * @return string
	 * @version 1.0.0
	 */
	public static function json_format($output){
		if(!empty($output)){
			/** Reduce the size but will inccrease the CPU load */
			$output = self::html_compress(json_encode($output));
			/**
			 * If the remote call, return the jsonp format
			 */
			if(isset($_GET['callback']) && !empty($_GET['callback'])){
				$jsonp = $_GET['callback'];
				$output = $jsonp. '(' .$output. ')';
			}
			return $output;
		}
	}
	
	/**
	 * encode
	 * @param string $string encoded string / decoded string
	 * @param string $operation (encode | decode), default: decode
	 * @param string $key key
	 * @param int $expiry key expiry time, 0 for forever
	 * @return string  base64_encode string
	 * @example
	 *   $a = authcode('abc', 'encode', 'key');
	 *   $b = authcode($a, 'decode', 'key');  // $b(abc)
	 *
	 *   $a = authcode('abc', 'encode', 'key', 3600);
	 *   $b = authcode('abc', 'decode', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
	 */
	public static function authcode($string, $operation = 'decode', $key = null, $expiry = 0) {

		$ckey_length = 4;

		$key = md5($key ? $key : 'innstudio');
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'decode' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : null;

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$string = $operation == 'decode' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);

		$result = null;
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if($operation == 'decode') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return false;
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}
	/**
	 * mk_dir
	 * 
	 * 
	 * @params string $target
	 * @return bool
	 * @version 1.0.1
	 * 
	 */
	public static function mk_dir($target = null){
		if(!$target) return false;
		$target = str_replace('//', '/', $target); 
		if(file_exists($target)) return is_dir($target); 

		if(@mkdir($target)){
			@chmod($target, 0777); 
			return true; 
		}else if(is_dir(dirname($target))){
			return false; 
		} 
		/* If the above failed, attempt to create the parent node, then try again. */
		if(($target != '/')&&(self::mk_dir(dirname($target)))){
			return self::mk_dir($target); 
		}
		return false; 
	}
}
?>