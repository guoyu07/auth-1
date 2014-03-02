<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

define('STATUS_ACTIVATED', '1');
define('STATUS_NOT_ACTIVATED', '0');
class User_auth {
	private $error = array ();
	function __construct() {
		$this->ci = & get_instance();
		$this->ci->load->config('user_auth', TRUE);
		$this->ci->lang->load('user_auth');
		$this->ci->load->library('session');
		$this->ci->load->library('user_auth_event');
		$this->ci->load->database();
		$this->ci->load->model('user_auth/users');
		$this->allow='';
		$this->autologin();
	}
	function login($login, $password, $remember, $login_by_username, $login_by_email) {
		if ((strlen($login) > 0) AND (strlen($password) > 0)) {
			if ($login_by_username AND $login_by_email) {
				$get_user_func = 'get_user_by_login';
			} else {
				if ($login_by_username) {
					$get_user_func = 'get_user_by_username';
				} else {
					$get_user_func = 'get_user_by_email';
				}
			}
			if (!is_null($user = $this->ci->users-> $get_user_func ($login))) {
				$this->ci->load->library('phppass');
				if ($this->ci->phppass->CheckPassword($password, $user->password)) {
					if ($user->banned == 1) {
						$this->error['banned'] = $user->ban_reason;
					} else {
						$userdata['user_id'] = $user->id;
						$userdata['status'] = ($user->activated == 1) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED;
						$this->ci->session->set_userdata($userdata);
						if ($user->activated == 0) {
							$this->error['not_activated'] = '';
						} else {
							if ($remember) {
								$this->create_autologin($user->id);
							}
							$this->clear_login_attempts($login);
							$this->ci->users->update_login_info($user->id, $this->ci->config->item('login_record_ip', 'user_auth'), $this->ci->config->item('login_record_time', 'user_auth'));
							return TRUE;
						}
					}
				} else { // 失败 - 密码错误
					$this->increase_login_attempt($login);
					$this->error['password'] = 'auth_incorrect_password';
				}
			} else { // 失败 - 错误登录
				$this->increase_login_attempt($login);
				$this->error['login'] = 'auth_incorrect_login';
			}
		}
		return FALSE;
	}

	/**
	 *从网站上注销用户
	 */
	function logout() {
		$this->delete_autologin();
		$userdata['user_id'] = '';
		$userdata['status'] = '';
		$this->ci->session->set_userdata($userdata);
		$this->ci->session->sess_destroy();
	}

	/**
	 *检查如果用户登录测试，如果用户被激活，或没有。
	 */
	function is_logged_in($activated = TRUE) {
		//echo gettype($this->ci->session->userdata('status')).'||'.gettype($activated ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED).'<br/>';
		//echo ($this->ci->session->userdata('status'))===($activated ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED);
		return $this->ci->session->userdata('status') === ($activated ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED);
	}

	/**
	 * 获得user_id
	 */
	function get_user_id() {
		return $this->ci->session->userdata('user_id');
	}
	function dologin($d = '') {
		if ($this->is_logged_in()) {
			return TRUE;
		}
		$url = $this->ci->config->site_url($d ? ($d . '/auth') : 'auth') . '?callback=' . $this->ci->uri->uri_string();
		$this->redirect($url);
	}
	private function autologin() {
		if (!$this->is_logged_in() AND !$this->is_logged_in(FALSE)) {
			$this->ci->load->helper('cookie');
			if ($cookie = get_cookie($this->ci->config->item('autologin_cookie_name', 'user_auth'), TRUE)) {
				$data = unserialize($cookie);
				if (isset ($data['key']) AND isset ($data['user_id'])) {
					$this->ci->load->model('user_auth/user_autologin');
					if (!is_null($user = $this->ci->user_autologin->get($data['user_id'], md5($data['key'])))) {
						$userdata['user_id'] = $user->id;
						$userdata['status'] = STATUS_ACTIVATED;
						$this->ci->session->set_userdata($userdata);
						$cookie_data['name'] = $this->ci->config->item('autologin_cookie_name', 'user_auth');
						$cookie_data['value'] = $cookie;
						$cookie_data['expire'] = $this->ci->config->item('autologin_cookie_life', 'user_auth');
						set_cookie($cookie_data);
						$this->ci->users->update_login_info($user->id, $this->ci->config->item('login_record_ip', 'user_auth'), $this->ci->config->item('login_record_time', 'user_auth'));
						return TRUE;
					}
				}
			}
		}
		$uid = $this->get_user_id();
		$user = $this->ci->users->get_by_id($uid);
		$this->ucdata = $user;
		$this->ci->load->set_vars('ucdata', $user);
		return FALSE;
	}
	/**
	 * 在网站上创建新的用户，并返回一些数据：
	 * user_id, username, password, email, new_email_key (if any).
	 */
	function create_user($username, $email, $password, $email_activation) {
		if ((strlen($username) > 0) AND !$this->ci->users->is_username_available($username)) {
			$this->error['username'] = 'auth_username_in_use';
		}
		elseif (!$this->ci->users->is_email_available($email)) {
			$this->error['email'] = 'auth_email_in_use';
		} else {
			$this->ci->load->library('phppass');
			$hashed_password = $this->ci->phppass->HashPassword($password);
			$data['username'] = $username;
			$data['password'] = $hashed_password;
			$data['email'] = $email;
			$data['last_ip'] = $this->ci->input->ip_address();
			if ($email_activation) {
				$data['new_email_key'] = md5(rand() . microtime());
				$this->ci->load->model('user_auth/user_temp', 'user_temp');
				$res = $this->ci->user_temp->create_user($data, !$email_activation);
				$res = $this->ci->users->create_user($data, !$email_activation);
				if (!is_null($res)) {
					//$data['user_id'] = $res['user_id'];
					$data['password'] = $password;
					unset ($data['last_ip']);
					return $data;
				}
			} else {
				$res = $this->ci->users->create_user($data, !$email_activation);
				if (!is_null($res)) {
					$data['user_id'] = $res['user_id'];
					$data['password'] = $password;
					unset ($data['last_ip']);
					return $data;
				}
			}

		}
		return NULL;
	}

	function is_username_available($username) {
		return ((strlen($username) > 0) AND $this->ci->users->is_username_available($username));
	}
	function is_email_available($email) {
		return ((strlen($email) > 0) AND $this->ci->users->is_email_available($email));
	}
	function change_email($email) {
		$user_id = $this->ci->session->userdata('user_id');
		if (!is_null($user = $this->ci->users->get_user_by_id($user_id, FALSE))) {
			$data['user_id'] = $user_id;
			$data['username'] = $user->username;
			$data['email'] = $email;
			if (strtolower($user->email) == strtolower($email)) {
				$data['new_email_key'] = $user->new_email_key;
				return $data;
			}
			elseif ($this->ci->users->is_email_available($email)) {
				$data['new_email_key'] = md5(rand() . microtime());
				$this->ci->users->set_new_email($user_id, $email, $data['new_email_key'], FALSE);
				return $data;
			} else {
				$this->error['email'] = 'auth_email_in_use';
			}
		}
		return NULL;
	}

	/**
	 * 激活用户使用给定密钥
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function activate_user($user_id, $activation_key, $activate_by_email = TRUE) {
		$this->ci->users->purge_na($this->ci->config->item('email_activation_expire', 'user_auth'));
		if ((strlen($user_id) > 0) AND (strlen($activation_key) > 0)) {
			return $this->ci->users->activate_user($user_id, $activation_key, $activate_by_email);
		}
		return FALSE;
	}

	function activate($username, $key = '') {
		$this->ci->load->model('user_auth/users', 'users');
		$this->ci->load->model('user_auth/user_temp', 'user_temp');
		$result = FALSE;
		if ($this->ci->config->item('email_activation')) {
			$this->ci->user_temp->prune_temp();
		}
		if ($query = $this->ci->user_temp->activate_user($username, $key) AND $query->num_rows() > 0) {
			$row = $query->row_array();
			$del = $row['id'];
			unset ($row['id']);
			unset ($row['activation_key']);
			$user_id = $this->ci->users->create_user($row);
			if ($user_id) {
				$this->ci->user_auth_event->user_activated($user_id);
				$this->ci->user_temp->delete_user($del);
				$result = TRUE;
			}
		}
		return $result;
	}

	/**
	 * 用户设置新的密码钥匙，并返回用户的一些数据：
	 */
	function forgot_password($login) {
		if (strlen($login) > 0) {
			$user = $this->ci->users->get_user_by_login($login);
			if (!is_null($user)) {
				$data['uid'] = $user->id;
				$data['username'] = $user->username;
				$data['email'] = $user->email;
				$data['new_pass_key'] = md5(rand() . microtime());
				$this->ci->users->set_password_key($user->id, $data['new_pass_key']);
				return $data;
			} else {
				$this->error['login'] = 'auth_incorrect_email_or_username';
			}
		}
		return NULL;
	}

	/**
	 * 检查密码的密钥是有效的和用户进行身份验证。
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function can_reset_password($user_id, $new_pass_key) {
		if ((strlen($user_id) > 0) AND (strlen($new_pass_key) > 0)) {
			return $this->ci->users->can_reset_password($user_id, $new_pass_key, $this->ci->config->item('forgot_password_expire', 'user_auth'));
		}
		return FALSE;
	}

	/**
	 * 更换用户密码（忘记）用新的（由用户设定）
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function reset_password($user_id, $new_pass_key, $new_password) {
		if ((strlen($user_id) > 0) AND (strlen($new_pass_key) > 0) AND (strlen($new_password) > 0)) {
			$user = $this->ci->users->get_user_by_id($user_id, TRUE);
			if (!is_null($user)) {
				$this->ci->load->library('phppass');
				$hashed_password = $this->ci->phppass->HashPassword($new_password);
				if ($this->ci->users->reset_password($user_id, $hashed_password, $new_pass_key, $this->ci->config->item('forgot_password_expire', 'user_auth'))) {
					$this->ci->load->model('user_auth/user_autologin');
					$this->ci->user_autologin->clear($user->id);
					$data['user_id'] = $user_id;
					$data['username'] = $user->username;
					$data['email'] = $user->email;
					$data['new_password'] = $new_password;
					return $data;
				}
			}
		}
		return NULL;
	}

	/**
	 * 更改用户密码（仅当用户已登录）
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function change_password($old_pass, $new_pass) {
		$user_id = $this->ci->session->userdata('user_id');
		$user = $this->ci->users->get_user_by_id($user_id, TRUE);
		if (!is_null($user)) {
			$this->ci->load->library('phppass');
			if ($this->ci->phppass->CheckPassword($old_pass, $user->password)) {
				$hashed_password = $this->ci->phppass->HashPassword($new_pass);
				$this->ci->users->change_password($user_id, $hashed_password);
				return TRUE;
			} else {
				$this->error['old_password'] = 'auth_incorrect_password';
			}
		}
		return FALSE;
	}

	/**
	 * 更改用户的电子邮件（仅当用户已登录），并返回用户的一些数据： 
	 * user_id, username, new_email, new_email_key.
	 * 它被激活之前，不能用于新的电子邮件登录或通知。
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	function set_new_email($new_email, $password) {
		$user_id = $this->ci->session->userdata('user_id');
		$user = $this->ci->users->get_user_by_id($user_id, TRUE);
		if (!is_null($user)) {
			$this->ci->load->library('phppass');
			if ($this->ci->load->CheckPassword($password, $user->password)) {
				$data['user_id'] = $user_id;
				$data['username'] = $user->username;
				$data['email'] = $user->email;
				if ($user->email == $new_email) {
					$this->error['email'] = 'auth_current_email';
				}
				elseif ($user->new_email == $new_email) {
					$data['new_email_key'] = $user->new_email_key;
					return $data;
				}
				elseif ($this->ci->users->is_email_available($new_email)) {
					$data['new_email_key'] = md5(rand() . microtime());
					$this->ci->users->set_new_email($user_id, $new_email, $data['new_email_key'], TRUE);
					return $data;

				} else {
					$this->error['email'] = 'auth_email_in_use';
				}
			} else {
				$this->error['password'] = 'auth_incorrect_password';
			}
		}
		return NULL;
	}

	/**
	 * 激活新的电子邮件，如果电子邮件激活密钥是有效的。
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function activate_new_email($user_id, $new_email_key) {
		if ((strlen($user_id) > 0) AND (strlen($new_email_key) > 0)) {
			return $this->ci->users->activate_new_email($user_id, $new_email_key);
		}
		return FALSE;
	}

	/**
	 * 从网站上删除用户（仅当用户已登录）
	 * @param	string
	 * @return	bool
	 */
	function delete_user($password) {
		$user_id = $this->ci->session->userdata('user_id');
		$user = $this->ci->users->get_user_by_id($user_id, TRUE);
		if (!is_null($user)) {
			$this->ci->load->library('phppass');
			if ($this->ci->phppass->CheckPassword($password, $user->password)) { // success
				$this->ci->users->delete_user($user_id);
				$this->logout();
				return TRUE;
			} else {
				$this->error['password'] = 'auth_incorrect_password';
			}
		}
		return FALSE;
	}

	function get_error_message() {
		return $this->error;
	}

	/**
	 * 保存数据的用户的自动登录
	 * @param	int
	 * @return	bool
	 */
	private function create_autologin($user_id) {
		$this->ci->load->helper('cookie');
		$key = substr(md5(uniqid(rand() . get_cookie($this->ci->config->item('sess_cookie_name')))), 0, 16);
		$this->ci->load->model('user_auth/user_autologin');
		$this->ci->user_autologin->purge($user_id);
		if ($this->ci->user_autologin->set($user_id, md5($key))) {
			$cookie_data['name'] = $this->ci->config->item('autologin_cookie_name', 'user_auth');
			$cookie_data['expire'] = $this->ci->config->item('autologin_cookie_life', 'user_auth');
			$cookie_data['value'] = serialize(array (
				'user_id' => $user_id,
				'key' => $key
			));
			set_cookie($cookie_data);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 清除用户的自动登录数据
	 * @return	void
	 */
	private function delete_autologin() {
		$this->ci->load->helper('cookie');
		if ($cookie = get_cookie($this->ci->config->item('autologin_cookie_name', 'user_auth'), TRUE)) {
			$data = unserialize($cookie);
			$this->ci->load->model('user_auth/user_autologin');
			$this->ci->user_autologin->delete($data['user_id'], md5($data['key']));
			delete_cookie($this->ci->config->item('autologin_cookie_name', 'user_auth'));
		}
	}

	/**
	 * 检查如果登录尝试超过最大登录尝试（指定配置）
	 */
	function is_max_login_attempts_exceeded($login) {
		if ($this->ci->config->item('login_count_attempts', 'user_auth')) {
			$this->ci->load->model('user_auth/login_attempts');
			return $this->ci->login_attempts->get_attempts_num($this->ci->input->ip_address(), $login) >= $this->ci->config->item('login_max_attempts', 'user_auth');
		}
		return FALSE;
	}

	/**
	 * 对于给定的IP地址和登录尝试次数增加
	 * （如果登录尝试被计数）
	 * @param	string
	 * @return	void
	 */
	private function increase_login_attempt($login) {
		if ($this->ci->config->item('login_count_attempts', 'user_auth')) {
			if (!$this->is_max_login_attempts_exceeded($login)) {
				$this->ci->load->model('user_auth/login_attempts');
				$this->ci->login_attempts->increase_attempt($this->ci->input->ip_address(), $login);
			}
		}
	}

	private function clear_login_attempts($login) {
		if ($this->ci->config->item('login_count_attempts', 'user_auth')) {
			$this->ci->load->model('user_auth/login_attempts');
			$this->ci->login_attempts->clear_attempts($this->ci->input->ip_address(), $login, $this->ci->config->item('login_attempt_expire', 'user_auth'));
		}
	}

	function check_uri_permissions($uri = null, $allow = TRUE) {
		if (!$this->is_logged_in()) {
			$this->dologin($uri);
			exit ();
		}
		$this->ci->load->library('acl');
		$uid = $this->get_user_id();
		if (!$uid) {
			$this->dologin($uri);
			exit ();
		}
		$meun = $this->ci->acl->getacl($uri);
		if (!$meun) {
			$this->deny_access('deny');
		}
	}
	function deny_access($uri = 'deny') {
		$this->ci->load->helper('url');
		if ($uri == 'login') {
			redirect($this->ci->config->item('user_auth_login_uri', 'user_auth'), 'location');
		} else
			if ($uri == 'banned') {
				redirect($this->ci->config->item('user_auth_banned_uri', 'user_auth'), 'location');
			} else {
				redirect($this->ci->config->item('user_auth_deny_uri', 'user_auth'), 'location');
			}
		exit;
	}
	function set_output($json = array ()) {
		if ($this->ci->input->get_post('request')) {
			$this->ci->load->library('apilibrary');
			return $this->ci->apilibrary->set_output($json);
		}
		$this->redirect($json['url'], $json);
		return FALSE;
	}
	 
	function  load_plugins($class, $directory = 'plugins', $parameter = '') {
		static $_classes = array ();
		if (isset ($_classes[$class])) {
			return $_classes[$class];
		}
		$name = FALSE;
		foreach (array (
				APPPATH,
				BASEPATH
			) as $path) {
			if (file_exists($path . $directory . '/' . $class . '.php')) {
				$name = $class;
				if (class_exists($name) === FALSE) {
					require ($path . $directory . '/' . $class . '.php');
				}
				break;
			}
		}
		$sf = FCPATH . ASSETS . '/' . APPLICATION . '/';
		if (file_exists($sf . $directory . '/' . $class . '.php')) {
			$name = $class;
			if (class_exists($name) === FALSE) {
				require ($sf . $directory . '/' . $class . '.php');
			}
		}
		$CI =& get_instance();
		if (!$name) {
			$CI->$class = FALSE;
		} else {
			$CI->$class = new $name ($parameter);
		}
		return $CI->$class;
	}
	public function redirect($uri = '', $q = '', $method = 'location', $http_response_code = 302) {
		if (!preg_match('#^https?://#i', $uri)) {
			$allow=substr_count($this->allow,'/')?$this->allow:$this->allow.'/';
			$uri = $this->ci->config->site_url($allow.$uri);
		}
		$uri .= $q ? "?" . http_build_query($q) : "";
		switch ($method) {
			case 'refresh' :
				header("Refresh:0;url=" . $uri);
				break;
			default :
				header("Location: " . $uri, TRUE, $http_response_code);
				break;
		}
		exit ();
	}
	public function view($view, $vars = array(), $return = FALSE)
	{
		$allow=substr_count($this->allow,'/')?$this->allow:$this->allow.'/';
		return $this->ci->load->view($allow.$view,$vars,$return);
	}
	function site_url($uri = '') {
		return $this->ci->config->site_url($uri);
	}
	function guid($h = '') {
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
		$hyphen = chr(45); // "-"
		//$uuid = chr(123) // "{"
		$uuid = substr($charid, 0, 8) . $hyphen .
		substr($charid, 8, 4) . $hyphen .
		substr($charid, 12, 4) . $hyphen .
		substr($charid, 16, 4) . $hyphen .
		substr($charid, 20, 12);
		//chr(125); // "}"
		return $uuid;
	}
	function json($var) {
		header('Content-type: application/json');
		return $this->json_encode($var);
	}
	function json_encode($var) {
		if (function_exists('json_encode')) {
			return json_encode($var);
		} else {
			$this->ci->load->library('json');
			return $this->ci->json->encodeUnsafe($var);
		}
	}
}