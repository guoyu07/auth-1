<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Oauth {
	public $client_id = '';
	public $client_secret = '';
	public $debug = false;
	public $scope = array ();
	public $params = array ();
	public $accessTokenURL = 'http://127.0.0.1:8086/oauth/access_token';
	public $authorizeURL = 'http://127.0.0.1:8086/oauth';
	public $apiUrlHttp = 'http://127.0.0.1:8086/api/';
	public $apiUrlHttps = 'http://127.0.0.1:8086/api/';
	public $prefix='oauth_';

	function __construct() {
		$this->ci = & get_instance();
		$this->ci->load->library('session');
	}
	function initialize($params = array ()) {
		if (count($params) > 0) {
			foreach ($params as $key => $val) {
				if (isset ($this-> $key)) {
					$this-> $key = $val;
				}
			}
		}
	}
	/**
	 * 获取授权URL
	 * @param $redirect_uri 授权成功后的回调地址，即第三方应用的url
	 * @param $response_type 授权类型，为code
	 * @return string
	 */
	public function getAuthorizeURL($redirect_uri, $response_type = 'code', $type = 'josn',$params=array()) {
		$params['client_id']=$this->client_id;
		$params['redirect_uri']=$redirect_uri;
		$params['response_type']=$response_type;
		$params['type']=$type;
		$params['scope']=$this->scope;
		return $this->authorizeURL . '?' . http_build_query($params);
	}
	/**
	 * 获取请求token的url
	 * @param $code 调用authorize时返回的code
	 * @param $redirect_uri 回调地址，必须和请求code时的redirect_uri一致
	 * @return string
	 */
	public function getAccessToken($code, $redirect_uri) {
		$params = array (
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type' => 'authorization_code',
			'code' => $code,
			'redirect_uri' => $redirect_uri
		);
		$this->params = $params;
		return $this->accessTokenURL . '?' . http_build_query($params);
	}
	/**
	* 刷新授权信息
	* 此处以SESSION形式存储做演示，实际使用场景请做相应的修改
	*/
	public function refreshToken() {
		$params = array (
			'client_id' => self :: $client_id,
			'client_secret' => self :: $client_secret,
			'grant_type' => 'refresh_token',
			'refresh_token' => $_SESSION['t_refresh_token']
		);
		$url = self :: $accessTokenURL . '?' . http_build_query($params);
		$r = Http :: request($url);
		@ parse_str($r, $out);
		if ($out['access_token']) { //获取成功
			$_SESSION['t_access_token'] = $out['access_token'];
			$_SESSION['t_refresh_token'] = $out['refresh_token'];
			$_SESSION['t_expire_in'] = $out['expire_in'];
			return $out;
		} else {
			return $r;
		}
	}
	/**
	 * 验证授权是否有效
	 */
	public function checkOAuthValid() {
		$r = json_decode($this->api('user/info'), true);
		if ($r['data']['name']) {
			return true;
		} else {
			return false;
		}
	}
	public function api($command=null, $params = array (), $method = 'POST', $multi = false,$extheaders=array()) {
		$prefix = $this->prefix;
		$access_token=$this->ci->session->userdata($prefix.'access_token');
		$openid=$this->ci->session->userdata($prefix.'openid');
		if ($access_token) { 
			$params['access_token'] = $access_token;
			$params['oauth_consumer_key'] = $this->client_id;
			$params['openid'] = $openid;
			$params['oauth_version'] = '2.a';
			$params['clientip'] = $this->getClientIp();
			$params['scope'] = $this->scope;
			$params['appfrom'] = 'sdk1.2258';
			$params['seqid'] = time();
			$params['serverip'] = $_SERVER['SERVER_ADDR'];
			$url = $this->apiUrlHttps . trim($command, '/');
		} else if ($this->ci->session->userdata ($prefix.'openid') && $this->ci->session->userdata ($prefix.'openkey')) {  
				$params['appid'] = $this->client_id;
				$params['openid'] = $this->ci->session->userdata($prefix.'openid');
				$params['openkey'] = $this->ci->session->userdata($prefix.'openkey');
				$params['clientip'] = $this->getClientIp();
				$params['reqtime'] = time();
				$params['wbversion'] = '1';
				$params['pf'] = 'sdk1.2258'; 
				$params['sig'] = time();
			}
	 
		$r = $this->HttpRequest($url, $params, $method, $multi,$extheaders);
		$r = preg_replace('/[^\x20-\xff]*/', "", $r);  
		$r = iconv("utf-8", "utf-8//ignore", $r); 
		//调试信息
		if ($this->debug) {
			echo '<pre>';
			echo '接口：' . $url;
			echo '<br>请求参数：<br>';
			print_r($params);
			echo '返回结果：' . $r;
			echo '</pre>';
		}
		return json_decode($r,true);
	}

	/**
	 * 发起一个HTTP/HTTPS的请求
	 * @param $url 接口的URL 
	 * @param $params 接口参数   array('content'=>'test', 'format'=>'json');
	 * @param $method 请求类型    GET|POST
	 * @param $multi 图片信息
	 * @param $extheaders 扩展的包头信息
	 * @return string
	 */
	public function HttpRequest($url, $params = null, $method = 'POST', $multi = false, $extheaders = array ()) {
		if (!function_exists('curl_init')) {
			exit ('Need to open the curl extension');
		}
		$params = $params ? $params : $this->params;
		$method = strtoupper($method);
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_USERAGENT, 'OAuth2.0');
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ci, CURLOPT_TIMEOUT, 3);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ci, CURLOPT_HEADER, false);
		$headers = (array) $extheaders;
		switch ($method) {
			case 'POST' :
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty ($params)) {
					if ($multi) {
						foreach ($multi as $key => $file) {
							$params[$key] = '@' . $file;
						}
						curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
						$headers[] = 'Expect: ';
					} else {
						curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
					}
				}
				break;
			case 'DELETE' :
			case 'GET' :
				$method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty ($params)) {
					$url = $url . (strpos($url, '?') ? '&' : '?') . (is_array($params) ? http_build_query($params) : $params);
				}
				break;
		}
		 
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
		curl_setopt($ci, CURLOPT_URL, $url);
		if ($headers) {
			curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
		}

		$response = curl_exec($ci);
		curl_close($ci);
		return $response;
	}
	public function getClientIp() {
		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else
			if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
				$ip = getenv("HTTP_X_FORWARDED_FOR");
			else
				if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
					$ip = getenv("REMOTE_ADDR");
				else
					if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
						$ip = $_SERVER['REMOTE_ADDR'];
					else
						$ip = "unknown";
		return ($ip);
	}

}