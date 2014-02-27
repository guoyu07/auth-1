<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Competence extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->loadmodel->_initialize($this->config->item('user_auth_users_competence_table', 'user_auth'));
	}
	function index($pid = 0) {
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$p_config['base_url'] = 'admin/competence/index';
		if ($this->input->get_post('name')) {
			$link['subject'] = $link['url'] = $this->input->get_post('name');
			$data = $this->loadmodel->getpage($p_config, $pid, 20, '', '*', 'datetime', 'DESC', '', '', $link);
		} else {
			$data = $this->loadmodel->getpage($p_config, $pid, 20, '', '*', 'datetime', 'DESC');
		}
		$this->load->view('admin/user_auth/competence', $data);
	}
	function add($rid = NULL) {
		$this->_EditAdd($rid);
	}
	function edit($rid = NULL) {
		$rd = $this->loadmodel->get_by_id($rid);
		if (!$rd) {
			$this->user_auth->redirect('admin/competence', '数据不存在');
		}
		$this->_EditAdd($rid);
	}

	function _EditAdd($rid = NULL) {
		$this->load->library('formhandler');
		$db = $this->loadmodel->get_by_id($rid);
		$role = explode(',', $db['acl']);
		$mrole = explode(',', $db['menus']);
		$crole = explode(',', $db['column']);
		$this->loadmodel->_initialize($this->config->item('user_auth_users_permission_table', 'user_auth'));
		$result = $this->loadmodel->get_all(0, 0, '', 'id as value,subject as name,id as extra,id,pid')->result_array();
		$category = array ();
		foreach ($result as $key => $value) {
			$category[$value['pid']][] = $value;
		}
		
		$acl1 = $acl2 = array ();
		$data['acl_1'] = $this->formhandler->Checkboxs('acl[]', $category['1'], $role);
		$data['acl_2'] = $this->formhandler->Checkboxs('acl[]', $category['2'], $role);
		
		
		$this->loadmodel->_initialize($this->config->item('user_auth_users_menus_table', 'user_auth'));
		$menus = $this->loadmodel->get_all(0, 0, '', 'id as value,subject as name,pid as extra,pid')->result_array();
		$data['menus'] = $this->formhandler->Checkboxs('menus[]', $menus, $mrole);
		
		$this->loadmodel->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'));
		$categorys = $this->loadmodel->get_all(0, 0, '', 'id as value,catname as name,id as extra,id')->result_array();
		$data['column'] = $this->formhandler->Checkboxs('category[]', $categorys, $crole);
		
		$data['datalist'] = $db;
		$data['type'] = 'rbac';
		$this->load->view('admin/user_auth/competence_do', $data);
	}
	/**
	 * 提交 
	 */
	public function dopost() {
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('subject', '用户角色名', 'trim|xss_clean|required');
		$val->set_rules('acl', '权限', 'required');
		$status['status'] = 'success';
		$status['url'] = site_url('admin/competence');
		if ($val->run() == FALSE) {
			$strarry = $val->_error_array;
			foreach ($strarry as $k => $v) {
				$str = $val->error($v, ' ', ' ');
				if ($str) {
					$status['msg'] =$str;
					$this->user_auth->redirect('admin/competence',$status );
					return 1;
				}
			}
		}
		
		$id = $this->input->get_post('id');
		$db['subject'] = $val->set_value('subject');
		$db['acl'] = @ implode(',', $this->input->get_post('acl'));
		$db['menus'] = @ implode(',', $this->input->get_post('menus'));
		$db['column'] = @ implode(',', $this->input->get_post('category'));
		$odb = $this->loadmodel->get_by_id($id);
		if ($odb) {
			$db['updatetime']=time();
			$this->loadmodel->update($db, $id);
			$status['msg'] = '修改成功';
			$this->user_auth->redirect('admin/competence',$status);
			return 1;
		} else {
			$db['name'] = $this->input->get_post('username');
			$status['msg'] = '添加成功';
			$db['id']=$this->loadmodel->uuid();
			$db['datetime'] = $db['updatetime']=time();
			if ($this->loadmodel->add($db)) {
				$this->user_auth->redirect($status['url'], $status);
				return 1;
			}
			$status['msg'] = '操作失败';
			$this->user_auth->redirect('admin/competence',$status);
		}

	}
}