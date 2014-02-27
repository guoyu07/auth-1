<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

class Oauth extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'apilibrary' );
		$this->load->library ( 'formhandler' );
		$this->form = $this->formhandler;
		$this->_initialize ();
	}
	function _initialize($name = 'oauth', $id = 'id') {
		$this->loadmodel->initialize ( array ('tablename' => $name, 'tableid' => $id ) );
	}
	
	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid=0) {
		$p_config ['base_url'] = 'oauth/index';
		$data = $this->loadmodel->getpage ( $p_config,$pid, 10, '', '*', 'id', 'ASC' );
		$data['appstatus']=$this->_getAppStatusRadio(1,1,1);
		$this->load->view ( 'admin/oauth/oauth', $data );
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
		$this->user_auth->redirect ( 'admin/oauth',$json ['msg']);
	
	}
	function _doAddEdit($appid = NULL,   $ajax = NULL) {
		$app = $this->loadmodel->get_by_field ( $appid );
		$data ['datalist'] = $app;  
		$json ['status'] = 'success';
		$data ['doajax'] = ($ajax == 'ajax') ? 1 : NULL; 
		$data['appstatus']=$this->_getAppStatusRadio('is_close',$app['is_close']);
		$this->_set_output ( 'admin/oauth/oauth_do', $data, $ajax, $json );
	}
	function _set_output($templates, $data = NULL, $do = NULL, $json = NULL) {
		if ($do == 'ajax') {
			return $this->apilibrary->set_output ( $json );
		}
		$this->load->view ( $templates, $data );
	}
	function _getAppStatusRadio($name='is_close',$default = NULL,$return=false) {
		$data ['1'] = array ('value' => '1', 'name' => '开启', 'extra' => '' );
		$data ['0'] = array ('value' => '0', 'name' => '关闭', 'extra' => '' ); 
		if($return){
			return $data;
		}
		return $this->form->Radio ( $name, $data, '', $default );
	}
	 
	function _formvalidation() {
		$do = ! empty ( $_GET ['doajax'] ) ? $_GET ['doajax'] : (! empty ( $_POST ['doajax'] ) ? $_POST ['doajax'] : NULL);
		$this->load->library ( 'form_validation' );
		$val = $this->form_validation;
		$val->set_rules ( 'id', '数据不存在', 'trim|xss_clean' );
		$val->set_rules ( 'name', '应用名称', 'trim|required|xss_clean' );
		$val->set_rules ( 'file', '接口名称', 'trim|required|xss_clean' );
		$val->set_rules ( 'logo', 'logo', 'trim|required|xss_clean' );
		$val->set_rules ( 'is_close', '是否关闭', 'trim|required|xss_clean' );
		$val->set_rules ( 'config', '配置信息', 'trim|required' ); 
		$val->set_rules ( 'description', '描述', 'trim' ); 
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
		$db ['file'] = $val->set_value ( 'file' );
		$db ['logo'] = $val->set_value ( 'logo' );
		$db ['is_close'] = $val->set_value ( 'is_close' );
		$db ['config'] = $val->set_value ( 'config' ); 
		$db ['description'] = $val->set_value ( 'description' ); 
		$id = $val->set_value ( 'id' );
		$category = $this->loadmodel->get_by_id ( $id );
		$status ['status'] = 'success';
		if ($category) {
			$id = $this->loadmodel->update ( $db, $id );
		} else {
			$id = $this->loadmodel->add ( $db );
		}
		if (!$id) {
			$status ['status'] = 'failure';
		}
		return $status;
	}
 
	
} 