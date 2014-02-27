<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Settings extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library('formhandler');
		$this->load->library('apilibrary');
		$this->_initialize();
	}
	function _initialize($name = 'settings', $id = 'id') {
		$this->loadmodel->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}

	/**
	 * 主页
	 * Enter description here ...
	 */
	function index() {
		$data['uid'] = '';
		$settings = $this->loadmodel->get_all(0, 0, '', '*', 'id', 'ASC')->result_array();
		$data['datalist'] = $settings;
		$this->load->view('admin/settings/settings', $data);
	}
	function dosettings() {
		$settings = $this->loadmodel->get_all(0, 0, '', '*', 'id', 'ASC')->result_array();
		$data['datalist'] = $settings;
		$this->load->view('admin/settings/settings_do', $data);
	}

	/**
	 * 添加
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function add($id = NULL, $ajax = NULL) {
		$this->_doAddEdit($id, $ajax);
	}

	/**
	 * 编辑
	 */
	function edit($id = NULL, $ajax = NULL) {
		$this->_doAddEdit($id, $ajax);

	}

	/**
	 * 添加、编辑 
	 */
	function _doAddEdit($id = NULL, $ajax = NULL) {
		$data = $this->loadmodel->get_by_field($id);
		$json['data'] = $this->load->view('admin/settings/settings_dopost', $data, TRUE);
		$json['status'] = 'success';
		return $this->apilibrary->set_output($json);
	}
	function delete($id = null,$action=null) {
		$db = $this->loadmodel->get_by_id($id);
		$json['status'] = 'failure';
		if ($db) {
			if ($this->loadmodel->delete($id)) {
				$json['msg'] = '删除成功';
				$json['status'] = 'success';
			} else {
				$json['msg'] = '删除失败';
			}
		} else {
			$json['msg'] = '数据不存在';
		}
		$json['url']='settings';
		$data['catid'] = $id;
		$this->user_auth->set_output($json);
	}
	function dopostkey() {
		$id = !empty ($_GET['id']) ? $_GET['id'] : (!empty ($_POST['id']) ? $_POST['id'] : NULL);
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('name', '名称', 'trim|required|xss_clean');
		$val->set_rules('description', '描述', 'trim|required|xss_clean');
		$val->set_rules('value', '值', 'trim|required');
		$val->set_rules('type', '类型', 'trim|required|required');
		$status['status'] = 'failure';
		if ($val->run() == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ($strarry as $k => $v) {
				$str .= $v;
			}
			$status['msg'] = $str;
			return $this->apilibrary->set_output($status);
		}
		$db['name'] = $val->set_value('name');
		$db['description'] = $val->set_value('description');
		$db['value'] = $val->set_value('value');
		$db['type'] = $val->set_value('type');
		$category = $this->loadmodel->get_by_id($id);
		if ($category) {
			$db['updatetime'] = time();
			$result = $this->loadmodel->update($db, $id);
		} else {
			$db['dateline'] = $db['updatetime'] = time();
			$result = $this->loadmodel->add($db);
		}
		if (!$result) {
			$status['msg'] = '操作失败';
			return $this->apilibrary->set_output($status);
		}
		$status['status'] = 'success';
		$status['msg'] = '操作成功';
		return $this->apilibrary->set_output($status);
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