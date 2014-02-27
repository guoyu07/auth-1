<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Permission extends CI_Controller {
	public $competence_table='company_users_competence';
	public $permission_table='company_users_permission';
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library('apilibrary');
		$this->loadmodel->_initialize($this->config->item('user_auth_users_permission_table', 'user_auth'));
		$this->competencetype = array (
			1 => array (
				'name' => '前台',
				'value' => '1',
				'extra' => '',
				'allow'=>''
			),
			2 => array (
				'name' => '后台',
				'value' => '2',
				'extra' => '',
				'allow'=>'admin'
			)
		);
	}

	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid = 0) {
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$p_config['base_url'] = 'admin/permission/index';
		$p_config['uri_segment'] =4;
		if ($this->input->get_post('name')) {
			$link['subject'] = $link['url'] = $this->input->get_post('name');
			$data = $this->loadmodel->getpage($p_config, $pid, 20, '', '*', 'datetime', 'DESC', '', '', $link);
		} else {
			$data = $this->loadmodel->getpage($p_config, $pid, 20, '', '*', 'datetime');
		}
		$data['roles'] = $this->competencetype;
		$this->load->view('admin/user_auth/permission', $data);
	}

	/**
	 * 添加
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function add($mid = NULL, $ajax = NULL) {
		$this->_doAddEdit($mid, $ajax);
	}

	/**
	 * 编辑
	 */
	function edit($mid = NULL, $ajax = NULL) {
		$this->_doAddEdit($mid, $ajax);

	}
	/**
	 * 编辑
	 */
	function delete($mid = NULL, $do = NULL) {
		$menu = $this->loadmodel->get_by_id($mid);
		if (!$menu) {
			$json['status'] = 'failure';
			$json['msg'] = '删除失败,数据不存在';
		} else
			if ($this->loadmodel->delete($mid)) {
				$json['status'] = 'success';
				$this->input->post('vurl') ? ($json['vurl'] = $this->input->post('vurl')) : '';
				$json['msg'] = '删除成功';
			} else {
				$json['status'] = 'failure';
				$json['msg'] = '删除失败';
			}
		if ($do == 'ajax') {
			return $this->apilibrary->set_output($json);
		}

	}
	/**
	 * 添加、编辑 
	 */
	function _doAddEdit($mid = NULL, $ajax = NULL) {
		$data['datalist'] = $this->loadmodel->get_by_field($mid);
		$data['pids'] = $this->_getpids($data['datalist']['pid']);
		$this->load->view('admin/user_auth/permission_do', $data);
	}
	function _getpids($pid = 0) {
		$this->load->library('formhandler');
		$cofnigs = $this->competencetype;
		$roles = $cofnigs;
		return $this->formhandler->Select('pid', $roles, '', $pid);
	}
	function dopost() {
		$id = $this->input->get_post('id');
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('subject', '名称', 'trim|required|xss_clean');
		$val->set_rules('url', 'URL', 'trim|required|xss_clean');
		$status['status'] = 'failure';
		if ($val->run() == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ($strarry as $k => $v) {
				$str .= $v;
			}
			$status['msg'] = $str;
			$this->user_auth->redirect('admin/permission/edit/'.$id,$status);
			return 0;
		}
		$url = $val->set_value('url');
		if (!strpos($url, "/")) {
			$url = $url . '/index';
		}
		$db['subject'] = $this->input->get_post('subject');
		$db['url'] = $url;
		$db['updatetime'] = time();
		$db['pid'] = $this->input->get_post('pid');
		$new = $this->input->get_post('new');
		$odb = $this->loadmodel->get_by_id($id?$id:NULL);
		if ($odb) {
			$result = $id;
			$this->loadmodel->update($db, $id);
		} else {
			$result =$this->_add_permission($db);
		}
		if($new){
			$subject=$db['subject'];
			$url=$this->input->get_post('url');
			$db['url']=$url.'/dopost';
			$db['subject']=$subject.'提交';
			$this->_add_permission($db);
			$db['url']=$url.'/edit';
			$db['subject']=$subject.'更改';
			$this->_add_permission($db);
			$db['url']=$url.'/add';
			$db['subject']=$subject.'添加';
			$this->_add_permission($db);
			$db['url']=$url.'/delete';
			$db['subject']=$subject.'删除';
			$this->_add_permission($db);
			$db['url']=$url.'/dodelete';
			$db['subject']=$subject.'提交删除';
			$this->_add_permission($db);
			$db['url']=$url.'/passwd_jump';
			$db['subject']=$subject.'密码跳转';
			$this->_add_permission($db);
			$db['url']=$url.'/detailed';
			$db['subject']=$subject.'详细';
			$this->_add_permission($db);
		}
		
		$this->_permission($result);
		if (!$result) {
			$status['msg'] = '操作失败';
			$this->user_auth->redirect('admin/permission', $status);
			return 1;
		}
		$status['status'] = 'success';
		$status['msg'] = '操作成功';
		$this->user_auth->redirect('admin/permission', $status);
	}
	function _add_permission($db){
		$db['datetime'] = $db['updatetime'];
		$db['id']=$this->loadmodel->uuid();
		$this->loadmodel->add($db);
		return $db['id'];
	}
	function _permission() {
		$menu = $this->loadmodel->get_all()->result_array();
		foreach ($menu as $k => $v) {
			$role[] = $v['id'];
		}
		$roles['acl'] = implode(',', $role);
		$roles['updatetime']=time();
		$this->loadmodel->_initialize($this->config->item('user_auth_users_competence_table','user_auth'),'default');
		$this->loadmodel->update($roles, 2);

	}
	/**
	 * 输出
	 * Enter description here ...
	 * @param unknown_type $templates
	 * @param unknown_type $data
	 * @param unknown_type $do
	 * @param unknown_type $json
	 */
	function _set_output($templates, $data = NULL, $do = NULL, $json = NULL) {
		if ($do == 'ajax') {
			$json['data'] = $this->load->view($templates, $data, TRUE);
			return $this->apilibrary->set_output($json);
		}
		$this->load->view($templates, $data);
	}

}