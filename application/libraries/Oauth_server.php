<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');
/**
 * OAuth 2.0 authorisation server library
 * 
 * @category  Library
 * @package   CodeIgniter
 * @author    Alex Bilbie <alex@alexbilbie.com>
 * @copyright 2012 Alex Bilbie
 * @license   MIT Licencse http://www.opensource.org/licenses/mit-license.php
 * @version   Version 0.2
 * @link      https://github.com/alexbilbie/CodeIgniter-OAuth-2.0-Server
 */
class Oauth_server {
	/**
	 * CodeIgniter instance.
	 * 
	 * @var $ci
	 * @access public
	 */
	protected $ci;

	/**
	 * Constructor
	 * 
	 * @access public
	 * @return void
	 */

	public function __construct() {
		$this->ci = get_instance();
		$this->ci->load->database();
	}

	/**
	 * 验证客户端的凭据
	 * @param string $client_id     客户端ID
	 * @param mixed  $client_secret 客户端secred
	 * @param mixed  $redirect_uri  重定向URI
	 * @access public
	 * @return bool|object
	 */
	public function validate_client($client_id = '', $client_secret = NULL, $redirect_uri = NULL) {
		$params = array (
			'client_id' => $client_id,
			
		);

		if ($client_secret !== NULL) {
			$params['client_secret'] = $client_secret;
		}

		if ($redirect_uri !== NULL) {
			$params['redirect_uri'] = $redirect_uri;
		}
		$client_check_query = $this->ci->db->select(array (
			'name',
			'client_id',
			'auto_approve'
		))->get_where('oauth_applications', $params);
		if ($client_check_query->num_rows() === 1) {
			return $client_check_query->row_array();
		} else {
			return FALSE;
		}
	}

	/**
	 * 生成一个新的检查代码，一旦用户批准的应用程序
	 * @param mixed $client_id    客户端ID
	 * @param mixed $user_id      的用户ID
	 * @param mixed $redirect_uri 客户端重定向URI
	 * @param array $scopes       客户端请求的作用域
	 * @param mixed $access_token 可选的访问令牌被更新，新的授权码
	 * @access public
	 * @return string
	 */
	public function new_auth_code($client_id = '', $user_id = '', $redirect_uri = '', $scopes = array (), $access_token = NULL) {
		//用新的代码更新现有的会话 
		if ($access_token !== NULL) {
			$code = md5(time() . uniqid());
			$this->ci->db->where(array (
				'type_id' => $user_id,
				'type' => 'user',
				'client_id' => $client_id,
				'access_token' => $access_token
			))->update('oauth_sessions', array (
				'code' => $code,
				'stage' => 'request',
				'redirect_uri' => $redirect_uri, // 重定向的URI的应用程序可能已被更新
				'last_updated' => time()
			));

			return $code;
		} else {
			//创建一个新的OAuth会话

			//删除任何现有的会话只是要确保
			$this->ci->db->delete('oauth_sessions', array (
				'client_id' => $client_id,
				'type_id' => $user_id,
				'type' => 'user'
			));

			$code = md5(time() . uniqid());
			$this->ci->db->insert('oauth_sessions', array (
				'client_id' => $client_id,
				'redirect_uri' => $redirect_uri,
				'type_id' => $user_id,
				'type' => 'user',
				'code' => $code,
				'first_requested' => time(),
				'last_updated' => time(),
				'access_token' => NULL
			));

			$session_id = $this->ci->db->insert_id();

			//添加作用域
			foreach ($scopes as $scope) {
				$scope = trim($scope);
				if (trim($scope) !== '') {
					$this->ci->db->insert('oauth_session_scopes', array (
						'session_id' => $session_id,
						'scope' => $scope
					));
				}
			}
		}
		return $code;
	}

	/**
	 * 验证授权码
	 * @param string $code         授权码
	 * @param string $client_id    客户端ID
	 * @param string $redirect_uri 客户端重定向URI
	 * @access public
	 * @return bool 如果授权代码是无效的，否则返回对象
	 */
	public function validate_auth_code($code = '', $client_id = '', $redirect_uri = '') {
		$validate = $this->ci->db->select(array (
			'id',
			'type_id'
		))->get_where('oauth_sessions', array (
			'client_id' => $client_id,
			'redirect_uri' => $redirect_uri,
			'code' => $code
		));
		if ($validate->num_rows() === 0) {
			return FALSE;
		} else {
			return $validate->row();
		}
	}

	/**
	 * 生成一个新的访问令牌（或返回一个现有的）
	 * 
	 * @param string $session_id 会话ID号
	 * @access public
	 * @return string
	 */
	public function get_access_token($session_id = '') {
		// 如果已经存在一个访问令牌
		$exists_query = $this->ci->db->select('access_token,access_token as refresh_token,type_id as uid')->get_where('oauth_sessions', array (
			'id' => $session_id,
			'access_token IS NOT NULL' => NULL
		));

		// 如果已经存在一个访问令牌，返回和删除授权码
		if ($exists_query->num_rows() === 1) {
			// 删除的授权码
			$this->ci->db->where(array (
				'id' => $session_id
			))->update('oauth_sessions', array (
				'code' => NULL,
				'stage' => 'granted'
			));

			//返回的访问令牌
			$exists = $exists_query->row_array();
			return $exists;
		}
		// 访问令牌不存在创建和删除授权码
		else {
			
			$access_token = sha1(time() . uniqid());
			$updates = array (
				'code' => NULL,
				'access_token' => $access_token,
				'last_updated' => time(),
				'stage' => 'granted'
			);

			// Update the OAuth session
			$this->ci->db->where(array (
				'id' => $session_id
			))->update('oauth_sessions', $updates);

			// Update the session scopes with the access token
			$this->ci->db->where(array (
				'session_id' => $session_id
			))->update('oauth_session_scopes', array (
				'access_token' => $access_token
			));
			$exists_query = $this->ci->db->select('access_token,access_token as refresh_token,type_id as uid')->get_where('oauth_sessions', array (
			'id' => $session_id 
		));
		$exists = $exists_query->row_array();
			return $exists;
		}
	}

	/**
	 * 验证访问令牌
	 * 
	 * @param string $access_token 访问令牌
	 * @param array  $scopes       作用域来验证访问令牌
	 * @access public
	 * @return void
	 */
	public function validate_access_token($access_token = '', $scopes = array ()) {
		// 验证令牌存在
		$valid_token = $this->ci->db->where(array (
			'access_token' => $access_token
		))->get('oauth_sessions');

		// 访问令牌不存在
		if ($valid_token->num_rows() === 0) {
			return FALSE;
		}

		//访问令牌不存在，验证每个作用域
		else {
			$token = $valid_token->row();
			if (count($scopes) > 0) {
				foreach ($scopes as $scope) {
					$scope_exists = $this->ci->db->where(array (
						'access_token' => $access_token,
						'scope' => $scope
					))->count_all_results('oauth_session_scopes');

					if ($scope_exists === 0) {
						return FALSE;
					}
				}
				return TRUE;
			} else {
				return TRUE;
			}
		}

	}

	/**
	 * 测试如果一个用户已经授权的应用程序，并已获得一个访问令牌
	 * @param string $user_id   用户ID
	 * @param string $client_id 客户端ID
	 * @access public
	 * @return bool
	 */
	public function access_token_exists($user_id = '', $client_id = '') {
		$token_query = $this->ci->db->select('access_token')->get_where('oauth_sessions', array (
			'client_id' => $client_id,
			'type_id' => $user_id,
			'type' => 'user',
			'access_token != ' => '',
			'access_token IS NOT NULL' => NULL
		));

		if ($token_query->num_rows() === 1) {
			return $token_query->row();
		} else {
			return FALSE;
		}
	}

	/**
	 *检测是否在数据库中存在作用域
	 * @access public
	 * @return bool
	 */
	public function scope_exists($scope = '') {
		$exists = $this->ci->db->where('scope', $scope)->from('oauth_scopes')->count_all_results();
		return ($exists === 1) ? TRUE : FALSE;
	}

	/**
	 * 返回的详细信息作用域
	 * @access public
	 * @return object
	 */
	public function scope_details($scopes) {
		if (is_array($scopes)) {
			$scope_details = $this->ci->db->where_in('scope', $scopes)->get('oauth_scopes');
		} else {
			$scope_details = $this->ci->db->where('scope', $scopes)->get('oauth_scopes');
		}
		$scopes = array ();
		if ($scope_details->num_rows() > 0) {
			foreach ($scope_details->result() as $detail) {
				$scopes[] = array (
					'name' => $detail->name,
					'description' => $detail->description
				);
			}
		}
		return $scopes;
	}

	/**
	 * 产生的重定向URI的附加PARAMS
	 * @param string $redirect_uri   重定向URI
	 * @param array  $params          被附加到URL的参数
	 * @param string $query_delimeter 变量和URL之间的分隔符
	 * 
	 * @access public
	 * @return string
	 */
	public function redirect_uri($redirect_uri = '', $params = array (), $query_delimeter = '?') {
		if (stripos($redirect_uri, $query_delimeter)) {
			$redirect_uri = $redirect_uri . '&' . http_build_query($params);
		} else {
			$redirect_uri = $redirect_uri . $query_delimeter . http_build_query($params);
		}
		return $redirect_uri;
	}
	public function redirect($uri = '',   $method = 'location', $http_response_code = 302) {
		if (!preg_match('#^https?://#i', $uri)) {
			if (!preg_match('#^https?://#i', $uri)) {
				$uri = $this->ci->config->site_url(  $uri);
			} else {
				$uri =   $uri;
			}
		} 
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

	/**
	 * 登录到您的应用程序的用户。
	 *编辑此功能来满足您的需求。它必须返回一个TRUE或FALSE如果用户的ID是不正确的标志
	 * @param string $username The user's username
	 * @param string $password The user's password
	 * @access public
	 * @return string|bool
	 */
	public function validate_user($username = '', $password = '') {
		$data['uid'] = '43254325432';
		$data['email'] = 'luohai83@126.com';
		$data['username'] = 'sdink';
		return $data;
	}

}