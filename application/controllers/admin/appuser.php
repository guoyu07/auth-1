<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Appuser extends CI_Controller {
	var $data = array ();
	function __construct() {
		parent :: __construct();
		$this->allow = 'admin';
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions($this->allow);
		$this->load->library('apilibrary');
		$this->data['banned'][0] = array (
			'name' => '开放',
			'value' => '0',
			'extra' => 'onclick="tabradio(0)"'
		);
		$this->data['banned'][1] = array (
			'name' => '禁止',
			'value' => '1',
			'extra' => 'onclick="tabradio(1)"'
		);
		$this->data['activated'][0] = array (
			'name' => '关闭',
			'value' => '0',
			'extra' => 'onclick="tabradio(0)"'
		);
		$this->data['activated'][1] = array (
			'name' => '激活',
			'value' => '1',
			'extra' => 'onclick="tabradio(1)"'
		);
		$this->data['user'] = array (
			'uid',
			'username',
			'password',
			'email',
			'activated',
			'banned',
			'ban_reason'
		);
		$this->loadmodel->_initialize($this->config->item('user_auth_users_table', 'user_auth'));
	}
	public function index($p = 0) {
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$p_config['base_url'] = $this->allow.'/appuser/index';
		$p_config['uri_segment'] = 4;
		$data = $this->loadmodel->getpage($p_config, $p, 20, '', '*', 'created');
		$data['userroles'] = $this->userroles();
		$this->load->view($this->allow.'/auth/appuser_list', $data);
	}
	public function edit($uid = NULL) {
		$this->_Edit_Add($uid);
	}
	public function add() {
		$this->_Edit_Add();
	}
	function _Edit_Add($uid = NULL) {
		$datalist = $this->users->get_by_field($uid);
		$data = $userroles = array ();
		$this->load->library('formhandler');
		$roles =  $this->userroles();
		foreach ($roles as $k => $v) {
			$vs['value'] = $v['id'];
			$vs['name'] = $v['subject'];
			$vs['extra'] = '';
			$userroles[$v['id']] = $vs;
		}
		$datalist['radio_banned'] = $this->formhandler->Radio('banned', $this->data['banned'], '', $datalist['banned']);
		$datalist['radio_activated'] = $this->formhandler->Radio('activated', $this->data['activated'], '', $datalist['activated']);
		$datalist['select_roles'] = $this->formhandler->Select('roleid', $userroles, '', $datalist['roleid']);
		$data['datalist'] = $datalist;
		$this->load->view($this->allow.'/auth/appuser_do', $data);
	}
	/**
	 * 更改密码
	 */
	function changepassword() {
		$data = array ();
		$data['userinfo'] = $this->user_auth->ucdata;
		$this->load->view($this->allow.'/auth/changepassword', $data);
	}

	/**
	 * 提交 
	 */
	public function dopost() {
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('username', '用户名', 'trim|required|xss_clean');
		$val->set_rules('password', '密码', 'trim|xss_clean|min_length[' . $this->config->item('password_min_length', 'user_auth') . ']|max_length[' . $this->config->item('password_max_length', 'user_auth') . ']|alpha_dash');
		$val->set_rules('email', '邮箱', 'trim|required|xss_clean|valid_email');
		$val->set_rules('activated', '激活', 'trim|xss_clean');
		$val->set_rules('roleid', '禁止', 'trim|xss_clean');
		$val->set_rules('banned', '禁止', 'trim|xss_clean');
		$val->set_rules('ban_reason', '禁止原因', 'trim|xss_clean');
		if ($val->run() == FALSE) {
			$strarry = $this->data['user'];
			foreach ($strarry as $k => $v) {
				$str = $val->error($v, ' ', ' ');
				if ($str) {
					$sfs['msg']=$str;
					$this->user_auth->redirect($this->allow.'/appuser',$sfs);
					return 1;
				}
			}
		}
		$uid =$this->input->get_post('id');
		if ($this->input->get_post('password')) {
			$this->load->library('phppass');
			$db['password'] = $this->phppass->HashPassword($this->input->get_post('password'));
		}
		$db['email'] = $this->input->get_post('email');
		$db['ban_reason'] = $this->input->get_post('ban_reason');
		$db['roleid'] = $this->input->get_post('roleid');
		$activated = $this->input->get_post('activated');
		if ($uid) {
			$db['banned'] = $this->input->get_post('banned');
			$db['activated'] = $activated;
			$this->users->update($db, $uid);
			$this->user_auth->redirect($this->allow.'/appuser', array('msg'=>'修改成功'));
			return 1;
		} else {
			$db['username'] = $this->input->get_post('username');
			if ($this->users->create_user($db, $activated)) {
				$status['status'] = 'success';
				$status['d'] = '添加成功';
				$status['url'] = 'appuser';
				$this->user_auth->redirect($this->allow.$status['url'], $status);
				return 1;
			} else {
				$error = '';
				$errors = $this->user_auth->get_error_message();
				foreach ($errors as $k => $v) {
					$error .= $this->lang->line($v);
				}
				$errors['msg']=$error;
				$this->user_auth->redirect($this->allow.'/appuser', $errors);
			}

		}

	}
	function delete($uid = NULL, $do = NULL) {
		$ucdata = $this->users->get_by_field($uid);
		$roleid = $ucdata['roleid'];
		if ($roleid == 1) {
			$json['status'] = 'failure';
			$json['msg'] = '删除失败,创始人账号禁止删除';
		} else {
			if ($this->users->delete_user($uid)) {
				$json['status'] = 'success';
				$this->input->post('vurl') ? ($json['vurl'] = $this->input->post('vurl')) : '';
				$json['msg'] = '删除成功';
			} else {
				$json['status'] = 'failure';
				$json['msg'] = '删除失败';
			}
		}

		if ($do == 'ajax') {
			return $this->apilibrary->set_output($json);
		}
	}

	function userroles() {
		$this->loadmodel->_initialize($this->config->item('user_auth_users_competence_table', 'user_auth'));
		$data = $this->loadmodel->getdata(array (
			'select' => 'id,name,subject'
		));
		foreach ($data as $k => $v) {
			$daalist[$v['id']] = $v;
		}
		return $daalist;
	}
}