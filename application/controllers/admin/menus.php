<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Menus extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library('apilibrary');
		$this->loadmodel->_initialize($this->config->item('user_auth_users_menus_table', 'user_auth'));
		$this->competencetype['1']['name'] ='使用手册';
		$this->competencetype['1']['value'] ='1';
		$this->competencetype['1']['extra'] ='';
		$this->competencetype['2']['name'] ='开发手册';
		$this->competencetype['2']['value'] ='2';
		$this->competencetype['2']['extra'] ='';
		$this->competencetype['3']['name'] ='用户管理';
		$this->competencetype['3']['value'] ='3';
		$this->competencetype['3']['extra'] ='';
		$this->competencetype['4']['name'] ='用户中心';
		$this->competencetype['4']['value'] ='4';
		$this->competencetype['4']['extra'] ='';
		$this->competencetype['5']['name'] ='开放平台';
		$this->competencetype['5']['value'] ='5';
		$this->competencetype['5']['extra'] ='';
		$this->competencetype['6']['name'] ='系统';
		$this->competencetype['6']['value'] ='6';
		$this->competencetype['6']['extra'] ='';
		$this->competencetype['7']['name'] ='开发手册';
		$this->competencetype['7']['value'] ='7';
		$this->competencetype['7']['extra'] ='';
	}
	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid = 0) {
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$p_config['base_url'] = ('admin/menus/index');
		$p_config['uri_segment'] = '5';
		if ($this->input->get_post('name')) {
			$name=$this->input->get_post('name');
			$or_link['subject'] = $or_link['url'] = $name;
			$select='*,replace( subject,   subject   , <em>fdsafdsa</em> ) as subject';
			$select='*';
			$data = $this->loadmodel->getpage($p_config, $pid, 20, '', $this->db->escape_like_str($select), 'datetime', 'DESC', '','', $or_link);
		} else {
			$data = $this->loadmodel->getpage($p_config, $pid, 20, '', '', 'datetime');
		}
		$data['roles'] = $this->competencetype;
		$this->load->view('admin/menus/welcome', $data);
	}

	/**
	 * 添加
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function add($id = NULL) {
		$this->_doAddEdit($id);
	}

	/**
	 * 编辑
	 */
	function edit($id = NULL) {
		$this->_doAddEdit($id);

	}
	/**
	 * 编辑
	 */
	function delete($mid = NULL) {
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
		if ($this->input->get_post('format')) {
			return $this->apilibrary->set_output($json);
		}
		$this->user_auth->redirect('admin/menus',$json);

	}
	/**
	 * 添加、编辑 
	 */
	function _doAddEdit($id = NULL) {
		$data['datalist'] = $this->loadmodel->get_by_field($id);
		$data['pids'] = $this->_getpids($data['datalist']['pid']);
		$this->load->view('admin/menus/menus_do', $data);
	}
	function _getpids($pid = 0) {
		$this->load->library('formhandler');
		$cofnigs = $this->competencetype;
		$roles = $cofnigs;
		return $this->formhandler->Select('pid', $roles, '', $pid);
	}
	function dopost() {
		$id = $this->input->get_post('id') ? $this->input->get_post('id') : NULL;
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('subject', '名称', 'trim|required|xss_clean');
		$val->set_rules('url', '描述', 'trim|required|xss_clean');
		$status['status'] = 'failure';
		if ($val->run() == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ($strarry as $k => $v) {
				$str .= $v;
			}
			$status['msg'] = $str;
			$this->user_auth->redirect('admin/menus',$status['msg']);
			return 0;
		}
		$url = $val->set_value('url');
		if (!strpos($url, "/")) {
			//$url = $url . '/index';
		}
		$db['subject'] = $val->set_value('subject');
		$db['url'] = $url;
		$db['pid'] = $this->input->get_post('pid');
		$db ['updatetime'] = time();
		$menu = $this->loadmodel->get_by_id($id);
		if ($menu) {
			$result = $id;
			$this->loadmodel->update($db, $id);
		} else {
			$result = $db ['id']=$this->loadmodel->guid();
			$db ['datetime']=$db ['updatetime'];
			$this->loadmodel->add($db);
		}
		$this->_permission($result);
		if (!$result) {
			$status['msg'] = '操作失败';
			$this->user_auth->redirect('admin/menus',$status);
			return 1;
		}
		$status['status'] = 'success';
		$status['msg'] = '操作成功';
		$this->user_auth->redirect('admin/menus',$status);
	}

	function _permission() {
		$menu = $this->loadmodel->get_all()->result_array();
		foreach ($menu as $k => $v) {
			$role[] = $v['id'];
		}
		$this->loadmodel->_initialize($this->config->item('user_auth_users_competence_table', 'user_auth'),'default');
		$roles['menus'] = implode(',', $role);
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