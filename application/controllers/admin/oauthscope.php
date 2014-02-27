<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

class Oauthscope extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'apilibrary' );
		$this->load->library ( 'formhandler' );
		$this->form = $this->formhandler;
		$this->_initialize ();
	}
	function _initialize($name = 'oauth_scopes', $id = 'id') {
		$this->loadmodel->initialize ( array ('tablename' => $name, 'tableid' => $id ) );
	}
	
	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid=0) {
		$data ['uid'] = '';
		$p_config ['base_url'] = 'oauthscope/index';
		$data = $this->loadmodel->getpage ( $p_config,$pid, 10, '', '*', 'id', 'ASC' );
		$this->load->view ( 'admin/oauthscope/oauthscope', $data );
	}
	/**
	 * 添加
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function add($parentid = NULL, $ajax = NULL) {
		$this->_doAddEdit (  $parentid, $ajax );
	}
	
	/**
	 * 编辑
	 */
	function edit($catid = NULL, $ajax = NULL) {
		$this->_doAddEdit ( $catid,  $ajax );
	}
	function dopost() {
		$status = $this->_formvalidation ();
		$catid = ! empty ( $_GET ['id'] ) ? $_GET ['id'] : (! empty ( $_POST ['id'] ) ? $_POST ['id'] : NULL);
		$json ['msg'] = '操作成功';
		if ($status ['status'] == 'failure') {
			$json ['msg'] = isset ( $status ['msg'] ) ? $status ['msg'] : '操作失败';
		}
		$this->user_auth->redirect ('admin/oauthscope' , $json ['msg'] );
	
	}
	function _doAddEdit($appid = NULL,   $ajax = NULL) {
		$app = $this->loadmodel->get_by_field ( $appid );
		$data ['datalist'] = $app;  
		$json ['status'] = 'success';
		$data ['doajax'] = ($ajax == 'ajax') ? 1 : NULL;  
		$this->_set_output ( 'admin/oauthscope/oauthscope_do', $data, $ajax, $json );
	}
	function _set_output($templates, $data = NULL, $do = NULL, $json = NULL) {
		if ($do == 'ajax') {
			return $this->apilibrary->set_output ( $json );
		}
		$this->load->view ( $templates, $data );
	}
 
	function _formvalidation() {
		$do = ! empty ( $_GET ['doajax'] ) ? $_GET ['doajax'] : (! empty ( $_POST ['doajax'] ) ? $_POST ['doajax'] : NULL);
		$this->load->library ( 'form_validation' );
		$val = $this->form_validation;
		$val->set_rules ( 'id', '数据不存在', 'trim|xss_clean' );
		$val->set_rules ( 'name', '作用域名称', 'trim|required|xss_clean' );
		$val->set_rules ( 'scope', '作用域标识', 'trim|required|xss_clean' );
		$val->set_rules ( 'description', '作用域描述', 'trim|required|xss_clean' ); 
		if ($val->run () == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ( $strarry as $k => $v ) {
				$str .= $v;
			}
			$json ['status'] = 'failure';
			$json ['msg'] = $str;
			return $json;
		}
		$db ['name'] = $val->set_value ( 'name' );
		$db ['scope'] = $val->set_value ( 'scope' );
		$db ['description'] = $val->set_value ( 'description' ); 
		$id = $val->set_value ( 'id' );
		$category = $this->loadmodel->get_by_id ( $id );
		$status ['status'] = 'success';
		if ($category) {
			$id = $this->loadmodel->update ( $db, $id );
		} else {
			$id = $this->loadmodel->add ( $db );
			 $_GET ['id'] =$id;
		}
		if (!$id) {
			$status ['status'] = 'failure';
		}
		return $status;
	}
	
} 