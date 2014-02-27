<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Oauth_resource_server {

	/**
	 * 访问令牌。
	 * 
	 * @var $_access_token
	 * @access private
	 */
	private $_access_token = NULL;

	/**
	 * 作用域的访问令牌的访问。
	 * @var $_scopes
	 * @access private
	 */
	private $_scopes = array ();

	/**
	 * 类型的访问令牌的拥有者。
	 * @var $_type
	 * @access private
	 */
	private $_type = NULL;

	/**
	 * ID的访问令牌的拥有者。
	 * @var $_type_id
	 * @access private
	 */
	private $_type_id = NULL;

	/**
	 * Constructor
	 * 构造函数
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->ci = get_instance();
		$this->ci->load->database();
		$this->init();
	}

	/**
	 * @access public
	 * @return void
	 */
	public function init() {
		// 尝试并获得通过一个的access_token或oauth_token参数的访问令牌
		switch ($this->ci->input->server('REQUEST_METHOD')) {
			default :
				$access_token = $this->ci->input->get('access_token');
				if (!$access_token) {
					$access_token = $this->ci->input->get('oauth_token');
				}
				break;

			case 'PUT' :
				$access_token = $this->ci->put('access_token'); // assumes you're using https://github.com/philsturgeon/codeigniter-restserver
				if (!$access_token) {
					$access_token = $this->ci->put('oauth_token');
				}
				break;

			case 'POST' :
				$access_token = $this->ci->input->post('access_token');
				if (!$access_token) {
					$access_token = $this->ci->input->post('oauth_token');
				}
				break;

			case 'DELETE' :
				$access_token = $this->ci->delete('access_token'); // assumes you're using https://github.com/philsturgeon/codeigniter-restserver
				if (!$access_token) {
					$access_token = $this->ci->delete('oauth_token');
				}
				break;
		}
		// 尝试并获得一个访问令牌的认证头信息
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (isset ($headers['Authorization'])) {
				$raw_token = trim(str_replace(array (
					'OAuth',
					'Bearer'
				), array (
					'',
					''
				), $headers['Authorization']));
				if (!empty ($raw_token)) {
					$access_token = $raw_token;
				}
			}
		}
		
		if ($access_token) {
			$session_query = $this->ci->db->get_where('oauth_sessions', array (
				'access_token' => $access_token,
				'stage' => 'granted'
			));
			
			if ($session_query->num_rows() === 1) {
				
				$session = $session_query->row();
				$this->_access_token = $session->access_token;
				$this->_type = $session->type;
				$this->_type_id = $session->type_id;
				
				$scopes_query = $this->ci->db->get_where('oauth_session_scopes', array (
					'access_token' => $access_token
				));
				
				if ($scopes_query->num_rows() > 0) {
					foreach ($scopes_query->result() as $scope) {
						$this->_scopes[] = $scope->scope;
					}
				}
			} else {
				$this->ci->output->set_status_header(403);
				$this->ci->output->set_output('无效的访问令牌');
			}
		} else {
			$this->ci->output->set_status_header(403);
			$this->ci->output->set_output('缺少访问令牌');
		}
	}

	/**
	 * 测验的访问令牌代表一个用户
	 * @access public
	 * @return string|bool
	 */
	public function is_user() {
		if ($this->_type === 'user') {
			return $this->_type_id;
		}

		return FALSE;
	}

	/**
	 * 测验的访问令牌代表applicatiom
	 * @access public
	 * @return string|bool
	 */
	public function is_anon() {
		if ($this->_type === 'anon') {
			return $this->_type_id;
		}
		return FALSE;
	}

	/**
	 * 测验的访问令牌，如果有一个特定的作用域
	 * @param mixed $scopes 作用域
	 * @access public
	 * @return string|bool
	 */
	public function has_scope($scopes) {
		if (is_string($scopes)) {
			if (in_array($scopes, $this->_scopes)) {
				return TRUE;
			}
			return FALSE;
		} elseif (is_array($scopes)) {
			foreach ($scopes as $scope) {
				if (!in_array($scope, $this->_scopes)) {
					return FALSE;
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>