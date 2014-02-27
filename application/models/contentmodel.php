<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Contentmodel extends MY_Model {
	public function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'));
	}
	function _initialize($name=null,$id = 'id') {
		$this->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}
	public function setcontent($catid = NULL, $p = 0, $data = array()) {
		$column = $this->get_by_id($catid);
		if (!$column) {
			show_404();
			return FALSE;
		}
		if($column['islogin']){
			$this->user_auth->dologin();
		}
		if ($column['sethtml']) {
			$_GET['mkdir'] = 1;
		}
		if ($column['controller'] && $column['function'] ) {
			$CFG = $this->user_auth->load_plugins($column['controller'], 'plugins', $column);
			return $CFG-> $column['function'] ($catid);
		}
		if ($column['type'] == 0) {
			return $this->_colum_type_list($column, $p);
		}elseif ($column['type'] == 1) {
			return $this->_colum_type_page($column, $data);
		}elseif ($column['type'] == 2) {
			return $this->_colum_type_link($column, $data);
		}
	}
	function _colum_type_link($column, $data = array()) {
		$this->_initialize('page','columnid');
		$page = $data['datalist'] = $this->get_by_field($column['id']);
		$data['datalist']['title']=$page['title']?$page['title']:$column['catname'];
		if (!$page['id']) {
			die('连接不存在');
			return FALSE;
		} 
		$this->user_auth->redirect($page['content']);
		
	}
	function _colum_type_page($column, $data = array()) {
		$this->_initialize('page','columnid');
		$page = $data['datalist'] = $this->get_by_field($column['id']);
		$data['datalist']['title']=$page['title']?$page['title']:$column['catname'];
		$data['datalist']['content'] = getimage($page['content']);
		$tid = isset ($page['template']) && $page['template'] ? $page['template'] : $column['show_template'];
		$this->_initialize('templates');
		$template = $this->get_by_id($tid);
		if (is_null($template)) {
			die('模板不存在');
			return FALSE;
		}
		$data['columnid'] = $column['id'];
		$data['column'] = $column;
		$data['title'] = $data['datalist']['title'];
		$data['keywords'] = $data['datalist']['keywords'];
		$data['description'] = $data['datalist']['description'];  
		$this->load->template($this->router->directory. $template['file'], $data);
	}
	function _colum_type_list($column, $p = 0) {
		$data = $column;
		$data['title'] = $column['catname'];
		$tid = $column['category_template'];
		$this->_initialize('templates', 'id');
		$template = $this->get_by_id($tid);
		if (is_null($template)) {
			die('模板不存在');
			return FALSE;
		}
		$this->_initialize('model', 'modelid');
		$model = $this->get_by_id($column['modelid']);
		$data['tablename'] = '';
		if ($model) {
			$data['model'] = $model;
			$data['tablename'] = $model['tablename'];
		}
		$data['column'] = $column;
		$data['offset'] = $p;
		$this->load->template($this->router->directory. $template['file'], $data);
	}
}