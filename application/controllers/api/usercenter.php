<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Usercenter extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('apilibrary');
		$this->load->library('user_auth');		
		//$this->load->library('ucenter');
		$this->api = $this->apilibrary;
		$this->load->helper('form');
		$this->load->library('form_validation');
		$init = array (
			'tablename' => 'category',
			'tableid' => 'catid'
		);
		$this->loadmodel->initialize($init);
	}
	function login() {
		$json['data'] = $this->load->view(APPLICATION . '/' . 'user_auth/login_ajax', '', true);
		$json['status'] = 'success';
		$this->apilibrary->set_output($json);
	}
	function dologin(){
		$this->ucenter->login();
	}
	function register() {
		$json['data'] = $this->load->view(APPLICATION . '/' . 'user_auth/register_ajax', '', true);
		$json['status'] = 'success';
		$this->apilibrary->set_output($json);
	}

	function getuser() {
		$json['status'] = 'success';
		$catid = $this->input->get_post('catid');
		if ($catid) {
			$category = $this->loadmodel->get_by_field($catid);
			if (($category['islogin'])&& (!($this->user_auth->is_logged_in()))) {
				$json['data'] = $this->load->view(APPLICATION . '/' . 'user_auth/login_ajax', '', true);
			}
		}
		if ($this->user_auth->is_logged_in()) {
			$json['userstatus'] = 1;
			$userinfo = $this->user_auth->ucdata;
			unset ($userinfo['password']);
			$json['userinfo'] = array (
				'notice' => $userinfo['notice'],
				'username' => $userinfo['username']
			);
		} else {
			$json['userstatus'] = 0;
		}
		$this->apilibrary->set_output($json);
	}
	function getpost($name) {
		return $this->input->get_post($name);
	}
	function checkemail() {
		$email = $this->getpost('email');
		if (!$this->auth->is_email_available($email)) {
			$data = array (
				'msg' => '邮箱可以使用',
				'res' => FALSE
			);
		} else {
			$data = array (
				'msg' => '邮箱已经被使用',
				'res' => TRUE
			);
		}
		$this->api->set_output($data);
	}
	function checkusername() {
		$username = $this->getpost('username');
		if (!$this->auth->is_username_available($username)) {
			$data = array (
				'msg' => '用户名可以使用',
				'res' => FALSE
			);
		} else {
			$data = array (
				'msg' => '用户名已经被使用',
				'res' => TRUE
			);
		}
		$this->api->set_output($data);
	} 
	function getauthorize(){
		$this->load->model('user_auth/oauth_user_model');
		$ucdata=$data['ucdata'] =$this->user_auth->ucdata;
		$json['ucenter']=$data['ucenter'] = $this->users->get_profile($ucdata['uid']);
		$json['data'] = $this->load->view(APPLICATION . '/' . 'ajax/usercenter/account_authorize', $data, true);
		$json['status'] = 'success';
		$this->api->set_output($json);
	}
	function getsecurity() {
		$ucdata=$data['ucdata'] =$this->user_auth->ucdata;
		$json['ucenter']=$data['ucenter'] = $this->users->get_profile($ucdata['uid']);
		//PRINT_r($this->db->last_query());
		$json['data'] = $this->load->view(APPLICATION . '/' . 'ajax/usercenter/security', $data, true);
		$json['status'] = 'success';
		$this->api->set_output($json);
	}
	function setsecurity() {
		$ucdata=$this->user_auth->ucdata;
		$this->load->model('user_auth/users_profile');
		$data['status'] = 'success';
		$data['msg'] = '操作成功';
		$this->users_profile->update($_POST, $ucdata['uid']);
		$this->api->set_output($data);
	}
	function getuserprofile() {
		$ucdata=$data['ucdata'] =$this->user_auth->ucdata;
		$json['ucenter']=$data['ucenter'] = $this->users->get_profile($ucdata['uid']);
		$json['data'] = $this->load->view(APPLICATION . '/' . 'ajax/usercenter/userprofile', $data, true);
		$json['status'] = 'success';
		$this->api->set_output($json);
	}
	function setuserprofile() {
		$ucdata=$this->user_auth->ucdata;
		$this->load->model('user_auth/users_profile');
		$data['status'] = 'success';
		$data['msg'] = '您的操作成功';
		$this->users_profile->update($_POST, $ucdata['uid']);
		$this->api->set_output($data);
	}
	function changepassword() {
		$val = $this->form_validation;
		$status['status'] = 'success';
		if (!$this->user_auth->is_logged_in()) {
			//$this->user_authredirect('auth');
			$status['status'] = 'failure';
			$status['msg'] = '请先登录';
		} else {
			$val = $this->form_validation;
			$val->set_rules('old_password', '当前密码', 'trim|required|xss_clean');
			$val->set_rules('new_password', '新密码', 'trim|required|xss_clean|min_length[' . $this->config->item('password_min_length', 'user_auth') . ']|max_length[' . $this->config->item('password_max_length', 'user_auth') . ']|alpha_dash');
			$val->set_rules('confirm_new_password', '确认新密码', 'trim|required|xss_clean|matches[new_password]');
			$val->set_rules('imgcode', '验证码', 'trim|xss_clean|required|callback__check_captcha');
			$data['errors'] = array ();
			if ($val->run()) {
				if ($this->user_auth->change_password($val->set_value('old_password'), $val->set_value('new_password'))) {
					$this->user_auth->logout();
					$status['msg'] = $this->lang->line('auth_message_password_changed');
				} else {
					$error = $this->user_auth->get_error_message();
					foreach ($error as $key => $value) {
						$status['old_password'] = $this->lang->line($value);
					}
					$status['status'] = 'failure';
				}
			} else {
				$status = $val->_error_array;
				$status['status'] = 'failure';
			}
		}
		$this->apilibrary->set_output($status);
	}

	function _check_captcha($v) {
		$this->load->library('authcheckcode');
		if ($this->authcheckcode->check($v)) {
			return TRUE;
		} else {
			$this->form_validation->set_message('_check_captcha', '验证码错误');
			return FALSE;
		}
	}

}