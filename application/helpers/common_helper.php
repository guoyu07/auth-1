<?php
function MakeDir($dir_name, $mode = 0777) {
	$dir_name = str_replace("\\", "/", $dir_name);
	$dir_name = preg_replace("#(/" . "/+)#", "/", $dir_name);
	if (is_dir($dir_name) !== false)
		Return true;
	$dir_name = explode("/", $dir_name);
	$dirs = '';
	foreach ($dir_name as $dir) {
		if (trim($dir) != '') {
			$dirs .= $dir . "/";
			if (is_dir($dirs) == false && @ mkdir($dirs, $mode) === false) {
				return false;
			} else {
				;
			}
		}
	}
	return true;
}
function build_string_url($name = '', $value = '') {
	$data = $_GET;
	$data[$name] = $value;
	return http_build_query($data);
}
//通过微秒时间获得唯一ID  
function get_unique() {
	list ($msec, $sec) = explode(" ", microtime());
	return $sec . intval($msec * 1000000);
}
function remote_download($url) {
	$img = Getcurl($url);
	$content_type = $img['header']['content_type'];
	$x = explode('/', $content_type);
	$filename = 'assets/' . APPLICATION . '/attachment/' . date('Y/m/d/');
	MakeDir($filename);
	$filename = $filename . get_unique() . '.' . end($x);
	$write_fd = @ fopen($filename, "wb");
	$v = Getcurl($url);
	@ fwrite($write_fd, $v['result']);
	@ fclose($write_fd);
	return '<img src="' . base_url() . $filename . '" />';
}
//抓取网页内容  
function Getcurl($url) {
	$url = str_replace('&amp;', '&', $url);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	//curl_setopt($curl, CURLOPT_REFERER,$url);  
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1; InfoPath.2)");
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	return array (
		'result' => $result,
		'header' => $info
	);
}

function build_url($cal = 'javascript') {
	$c = get_instance();
	return $c->config->base_url() . 'assets/' . APPLICATION . '/' . $cal . '/';
}
function base_build_url($cal = 'javascript') {
	$c = get_instance();
	return $c->config->base_url() . 'assets/themes/default/' . $cal . '/';
}

function string2array($data) {
	if ($data == '') {
		return array ();
	}
	@ eval ("\$array = $data;");
	return $array;
}

if (!function_exists('currenturl')) {
	function currenturl() {
		$CI = & get_instance();
		return $CI->config->site_url($CI->uri->uri_string());
	}
}
function getimage($d = null) {
	$sd =preg_replace("/[\n\r\t]*\{ATTACHMENT\}[\n\r\t]*/iss", build_url('attachment'), $d) ;
	$c = get_instance();
	return preg_replace("/[\n\r\t]*\{BASE_URL\}[\n\r\t]*/iss", $c->config->base_url(), $sd);
}
function setimage($d) {
	$sd=str_ireplace(build_url('attachment'), '{ATTACHMENT}', $d);
	$c = get_instance();
	return  str_ireplace($c->config->base_url(), '{BASE_URL}', $sd);
}

function getstring($string, $length, $dot = '...', $charset = 'utf-8') {
	$strlen = strlen($string);
	if ($strlen <= $length)
		return $string;
	$strcut = '';
	if (strtolower($charset) == 'utf-8') {
		$n = $tn = $noc = 0;
		while ($n < $strlen) {
			$t = ord($string[$n]);
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1;
				$n++;
				$noc++;
			}
			elseif (194 <= $t && $t <= 223) {
				$tn = 2;
				$n += 2;
				$noc += 2;
			}
			elseif (224 <= $t && $t <= 239) {
				$tn = 3;
				$n += 3;
				$noc += 3;
			}
			elseif (240 <= $t && $t <= 247) {
				$tn = 4;
				$n += 4;
				$noc += 4;
			}
			elseif (248 <= $t && $t <= 251) {
				$tn = 5;
				$n += 5;
				$noc += 5;
			}
			elseif ($t == 252 || $t == 253) {
				$tn = 6;
				$n += 6;
				$noc += 6;
			} else {
				$n++;
			}
			if ($noc >= $length)
				break;
		}

		if ($noc > $length)
			$n -= $tn;
		$strcut = substr($string, 0, $n);
	} else {
		$dotlen = strlen($dot);
		$maxi = $length - $dotlen -1;
		for ($i = 0; $i < $maxi; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++ $i] : $string[$i];
		}
	}

	return $strcut . $dot;
}

function arr_to_html($data) {
	if (is_array($data)) {
		$str = 'array(';
		$first = true;
		foreach ($data as $key => $val) {
			if (!$first) {
				$str .= ',';
			}
			$first = false;
			if (is_array($val)) {
				$str .= "'$key'=>" . arr_to_html($val);
			} else {
				if (stripos($val, '$') === 0) {
					$str .= "'$key'=>$val";
				}
				elseif (stripos($val, '(') === 0) {
					$str .= "'$key'=>$val";
				}
				elseif (stripos($val, 'EV') === 0) {
					$str .= "'$key'=>$val";
				}
				elseif (stripos($val, '<?') === 0) {
					$str .= "'$key'=>$val";
				}
				elseif (stripos($val, 'array') === 0) {
					$str .= "'$key'=>" . ($val) . "";
				} else {
					$str .= "'$key'=>'" . ($val) . "'";
				}
			}
		}
		return $str . ')';
	}
	return false;
}

function base64url_encode($plainText) {
	$base64 = base64_encode($plainText);
	$base64url = strtr($base64, '+/=', '-_,');
	return $base64url;
}

function base64url_decode($plainText) {
	$base64url = strtr($plainText, '-_,', '+/=');
	$base64 = base64_decode($base64url);
	return $base64;
}

function get_extension($name = '.') {
	$x = explode('.', $name);
	return end($x);
}