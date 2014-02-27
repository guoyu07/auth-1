<?php


if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Oauth extends CI_Controller {
	var $access_token=null;
	function __construct() {
		parent :: __construct();
		$this->load->helper('url');
		$this->load->library('oauth_server');
		$this->load->library('session');
	 	$this->session->set_userdata('init', uniqid());
	 	//$this->output->enable_profiler(TRUE);
	}
	/**
	 * OAuth2的authorize接口
	 */
	 public function authorize(){
	 	// 获取查询字符串参数
		$params = array ();
		//申请应用时分配的AppKey。
		if ($client_id = $this->input->get('client_id')) {
			$params['client_id'] = trim($client_id);
		} else {
			$this->_fail('invalid_request', '请求缺少必要的参数 client_id', NULL, array (), 400);
		}

		// 客户端重定向URI
		if ($redirect_uri = $this->input->get('redirect_uri')) {
			$params['redirect_uri'] = trim($redirect_uri);
		} else {
			$this->_fail('invalid_request', '请求缺少必要的参数 redirect_uri', NULL, array (), 400);
			return;
		}

		//验证响应类型
		if ($response_type = $this->input->get('response_type')) {
			$response_type = trim($response_type);
			// 数组以便为将来的扩展
			$valid_response_types = array (
				'code'
			); 
			if (!in_array($response_type, $valid_response_types)) {
				$this->_fail('invalid_request', '不支持的响应类型使用这种方法获得的授权码，授权服务器不支持。支持的响应类型 \'' . implode('\' or ', $valid_response_types) . '\'.', $params['redirect_uri'], array (), 400);
				return;
			} else {
				$params['response_type'] = $response_type;
			}
		} else {
			$this->_fail('invalid_request', '请求缺少必要的参数 response_type', NULL, array (), 400);
			return;
		}
		// 验证的client_id和redirect_uri
		$client_details = $this->oauth_server->validate_client($params['client_id'], NULL, $params['redirect_uri']); 
		if ($client_details === FALSE) {
			$this->_fail('unauthorized_client', '未经授权的客户端', NULL, array (), 403);
			return;
		} else {
			//客户端是有效的，详细的session
			$this->session->set_userdata('client_details', $client_details);
		}
		
		//获取和验证的范围（S）
		if ($scope_string = $this->input->get('scope')) {
			$scopes = explode(',', $scope_string);
			$params['scope'] = $scopes;
		} else {
			$params['scope'] = array ();
		}
		 
		//检查的范围是有效的
		if (count($params['scope']) > 0) {
			foreach ($params['scope'] as $s) {
				$exists = $this->oauth_server->scope_exists($s);
				if (!$exists) {
					$this->_fail('无效的作用域', '所要求的范围无效,请参见作用域.', NULL, array (), 400);
					return;
				}
			}
		} else {
			$this->_fail('无效的作用域', '所要求的范围无效,请参见作用域.', NULL, array (), 400);
			return;
		}

		// 客户端是有效的，详细的会话
		$this->session->set_userdata('client_details', $client_details);
		//获取其他参数
		if ($state = $this->input->get('state')) {
			$params['state'] = trim($state);
		} else {
			$params['state'] = '';
		}
		//将params保存在会话中
		$this->session->set_userdata(array (
			'params' => $params
		));
		//将用户重定向到登录
		$this->oauth_server->redirect('oauth/sign_in' );
	 }
	 

	function sign_in() {
		// 检查用户是否已登录，如果将它们重定向到/授权
		if ($this->auth->is_logged_in()) {
			$this->oauth_server->redirect('oauth/authorise' );
		}
		$user_id = $this->auth->get_user_id();
		$client = $this->session->userdata('client_details');
		// 检查是否有客户端参数都存储
		if ($client == FALSE) {
			$this->_fail('invalid_request', '无客户端的详细信息已保存,您有没有删除您的cookies?', NULL, array (), 400);
			return;
		}
		// 错误
		$vars = array (
			'error' => FALSE,
			'error_messages' => array (),
			'client_name' => $client['name']
		);
	
		// 如果表单已提交
		
		if ($this->input->post('validate_user')) {
			$u = trim($this->input->post('username', TRUE));
			$p = trim($this->input->post('password', TRUE));
			// 验证用户名和密码
			if ($u == FALSE || empty ($u)) {
				$vars['error_messages'][] = '“账号”字段不能为空';
				$vars['error'] = TRUE;
			}
			if ($p == FALSE || empty ($p)) {
				$vars['error_messages'][] = '“密码”字段不能为空';
				$vars['error'] = TRUE;
			}
			// 检查登录并获得凭证
			if ($vars['error'] == FALSE) {
				$user = $this->auth->login($u, $p);
				if ($user == FALSE) {
					$vars['error_messages'][] = '无效的账号和/或密码';
					$vars['error'] = TRUE;
				} 
			}

			// 如果没有错误，则该用户已成功签约
			 
			if ($vars['error'] == FALSE) {
				$this->oauth_server->redirect('oauth/authorise' );
			}
		}
		
		$this->load->view('oauth_auth_server/sign_in', $vars);
	}

	/**
	 *退出
	 */
	function sign_out() {
		$this->session->sess_destroy();
		if ($redirect_uri = $this->input->get('redirect_uri')) {
			$this->oauth_server->redirect($redirect_uri );
		} else {
			$this->load->view('oauth_auth_server/sign_out');
		}

	}

	/**
	 * 当用户已经签署了在这里他们将被重定向到批准申请
	 */
	function authorise() {
		$user_id = $this->auth->get_user_id();
		$client = $this->session->userdata('client_details');
		$params = $this->session->userdata('params');
		
		//检查用户是否被签署的
		if ($user_id == FALSE) {
			$this->session->set_userdata('sign_in_redirect', array (
				'oauth',
				'authorise'
			));
			$this->oauth_server->redirect('oauth/sign_in' );
		}
		
		//检查客户端PARAMS存储
		if ($client == FALSE) {
			$this->_fail('invalid_request', 'No client details have been saved. Have you deleted your cookies?', NULL, array (), 400);
			return;
		}

		//检查请求参数仍保存
		if ($params == FALSE) {
			$this->_fail('invalid_request', 'No client details have been saved. Have you deleted your cookies?', NULL, array (), 400);
			return;
		}
		//用户授权的应用程序吗？
		$doauth = $this->input->post('doauth');
		if ($doauth) {
			switch ($doauth) {
				// 用户已批准的应用程序。
				case "Approve" :
					$authorised = FALSE;
					$action = 'newrequest';
					break;
					// 拒绝用户的应用
				case "Deny" :
					$error_params = array (
						'error' => 'access_denied',
						'error_description' => 'The resource owner or authorization server denied the request.'
					);
					if ($params['state']) {
						$error_params['state'] = $params['state'];
					}

					$redirect_uri = $this->oauth_server->redirect_uri($params['redirect_uri'], $error_params);
					$this->session->unset_userdata(array (
						'params' => '',
						'client_details' => '',
						'sign_in_redirect' => ''
					));
					$this->oauth_server->redirect($redirect_uri );
					break;

			}
		} else {
			// 用户是否已经有一个访问令牌？
			$authorised = $this->oauth_server->access_token_exists($user_id, $client['client_id']);
			if ($authorised) {
				$match = $this->oauth_server->validate_access_token($authorised->access_token, $params['scope']);
				$action = $match ? 'finish' : 'approve';
			} else {
				// 应用程序可以被自动批准？
				$action = ($client['auto_approve'] == 1) ? 'newrequest' : 'approve';
			}
		}
		
		switch ($action) {
			case 'approve' :
				$requested_scopes = $params['scope'];
				$scopes = $this->oauth_server->scope_details($requested_scopes);
				$vars = array (
					'client_name' => $client['name'],
					'scopes' => $scopes
				);
				$this->load->view('oauth_auth_server/authorise', $vars);
				break;
			case 'newrequest' :
				$code = $this->oauth_server->new_auth_code($client['client_id'], $user_id, $params['redirect_uri'], $params['scope'], $authorised['access_token']);
				$this->fast_code_redirect($params['redirect_uri'], $params['state'], $code);
				break;
			case 'finish' :
				$code = $this->oauth_server->new_auth_code($client['client_id'], $user_id, $params['redirect_uri'], $params['scope'], $authorised->access_token);
				$this->fast_token_redirect($params['redirect_uri'], $params['state'], $code);
				break;
		}
	}

	/**
	 * 生成一个新的访问令牌
	 */
	function access_token() {
		//获取查询字符串参数
		$params = array ();
		//客户端ID
		if ($client_id = $this->input->post('client_id')) {
			$params['client_id'] = trim($client_id);
		} else {
			$this->_fail('无效的请求', '请求缺少必要的参数，包括一个无效的参数值，或在其他格式不正确。客户端ID.', NULL, array (), 400, 'json');
			return;
		}

		//客户端的秘密
		if ($client_secret = $this->input->post('client_secret')) {
			$params['client_secret'] = trim($client_secret);
		} else {
			$this->_fail('无效的请求', '请求缺少必要的参数，包括一个无效的参数值，或在其他格式不正确。请参阅客户端的秘密.', NULL, array (), 400, 'json');
			return;
		}

		//客户端重定向URI
		if ($redirect_uri = $this->input->post('redirect_uri')) {
			$params['redirect_uri'] = urldecode(trim($redirect_uri));
		} else {
			$this->_fail('无效的请求', '请求缺少必要的参数，包括一个无效的参数值，或在其他格式不正确。重定向URI.', NULL, array (), 400, 'json');
			return;
		}

		if ($code = $this->input->post('code')) {
			$params['code'] = trim($code);
		} else {
			$this->_fail('无效的请求', '请求缺少必要的参数，包括一个无效的参数值，或在其他格式不正确。见代码.', NULL, array (), 400, 'json');
			return;
		}

		//验证授类型
		if ($grant_type = $this->input->post('grant_type')) {
			$grant_type = trim($grant_type);
			if (!in_array($grant_type, array ( 'authorization_code'))) {
				$this->_fail('无效的请求', '请求缺少必要的参数，包括一个无效的参数值，或在其他格式不正确。授权类型.', NULL, array (), 400, 'json');
				return;
			} else {
				$params['grant_type'] = $grant_type;
			}
		} else {
			$this->_fail('invalid_request', 'The request is missing a required parameter, includes an invalid parameter value, or is otherwise malformed. See grant_type.', NULL, array (), 400, 'json');
			return;
		}

		// 验证client_id和redirect_uri
		// 返回对象或FALSE
		$client_details = $this->oauth_server->validate_client($params['client_id'], $params['client_secret'], $params['redirect_uri']); 
		 //print_r($params);
		if ($client_details === FALSE) {
			$this->_fail('未经授权的客户端', '未授权的客户端请求一个授权码，使用此方法', NULL, array (), 403, 'json');
			return;
		}

		//回应授权类型
		switch ($params['grant_type']) {
			case "authorization_code" :
				// 验证认证码
				$session = $this->oauth_server->validate_auth_code($params['code'], $params['client_id'], $params['redirect_uri']);
				if ($session === FALSE) {
					$this->_fail('无效的请求', '授权码是无效的.', NULL, array (), 403, 'json');
					return;
				}
				// 生成一个新的access_token的（从会话中删除检查代码）
				$data = $this->oauth_server->get_access_token($session->id);
				$data['expires_in']=$this->config->item('sess_expiration');
				// 发送响应返回给应用程序
				$this->_response($data);
				return;

				break;
		}
	}

	/**
	 * 生成一个新的认证码，并重定向用户使用的网络服务器流量
	 * @access private
	 * @param string $redirect_uri
	 * @param string $state
	 * @param string $code
	 * @return void
	 */
	private function fast_code_redirect($redirect_uri = '', $state = '', $code = '') {
		$redirect_uri = $this->oauth_server->redirect_uri($redirect_uri, array (
			'code' => $code,
			'state' => $state
		));
		$this->session->unset_userdata(array (
			'params' => '',
			'client_details' => '',
			'sign_in_redirect' => ''
		));  
		$this->oauth_server->redirect($redirect_uri, 'location');
	}

	/**
	 *生成一个新的身份验证访问令牌，将用户重定向中使用的user-agent流
	 * @access private
	 * @param string $redirect_uri
	 * @param string $state
	 * @param string $code
	 * @return void
	 */
	private function fast_token_redirect($redirect_uri = '', $state = '', $code = '') {
		$redirect_uri = $this->oauth_server->redirect_uri($redirect_uri, array (
			'code' => $code,
			'state' => $state
		) );
		/**
		$this->session->unset_userdata(array (
			'params' => '',
			'client_details' => '',
			'sign_in_redirect' => ''
		));
		**/ 
		$this->oauth_server->redirect($redirect_uri );
	}

	/**
	 * 显示错误消息
	 * @access private
	 * @param mixed $msg
	 * @return string
	 */

	private function _fail($error, $description, $url = NULL, $params = array (), $status = 400, $output = 'html') {
		
		if ($url) { 
			$error_params = array (
				'error' => $error,
				'error_description' =>urlencode($description)
			);
			$params = array_merge($params, $error_params);
			$this->oauth_server->redirect_uri($url, $params);

		} else {
			switch ($output) {
				case 'html' :
				default :
					show_error('错误: ' . $error . '' . $description, $status);
					break;
				case 'json' :
					echo(json_encode(array (
						'error' => 1,
						'error_description' => '错误: ' . $error . '' . $description,
						'access_token' => NULL
					)));
					break;
			}

		}
	}

	/**
	 * JSON响应
	 * @access private
	 * @param mixed $msg
	 * @return string
	 */
	private function _response($msg) { 
		$this->output->set_status_header('200');
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output(json_encode($msg)); 
	}
}
?>