<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

class Oauthapp extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'apilibrary' );
		$this->load->library ( 'formhandler' );
		$this->form = $this->formhandler;
		$this->_initialize ();
	}
	function _initialize($name = 'oauth_applications', $id = 'id') {
		$this->loadmodel->initialize ( array ('tablename' => $name, 'tableid' => $id ) );
	}
	
	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid=0) {
		$p_config ['base_url'] = 'oauthapp/index';
		$data = $this->loadmodel->getpage ( $p_config,$pid, 10, '', '*', 'id', 'ASC' );
		$data['appstatus']=$this->_getAppStatusRadio(1,1,1);
		$data['appradio']=$this->_getSethtmlRadio(1,1,1);
		$this->load->view ( 'admin/oauthapp/oauthapp', $data );
	}
	function user_authorize($appid,$pid=0){
		$app = $this->loadmodel->get_by_field ( $appid );
		if(!$app){
			$this->user_auth->Messager ('应用不存在', 'ouser_authapp' );
		}
		$this->_initialize ('ouser_auth_sessions');
		$p_config ['base_url'] = 'ouser_authapp/user_authorize/'.$appid;
		$p_config ['pagination'] = 'systempagination';
		$data = $this->loadmodel->getpage ( $p_config,$pid, 10, array('client_id'=>$app['client_id']), '*', 'id', 'ASC' );
		$data['appstore']=$app;
		$this->load->view ( 'admin/ouser_authapp/user_authorize', $data );
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
		$this->user_auth->redirect (  'admin/oauthapp/',$json ['msg'] );
	
	}
	function _doAddEdit($appid = NULL,   $ajax = NULL) {
		$app = $this->loadmodel->get_by_field ( $appid );
		$data ['datalist'] = $app;  
		$json ['status'] = 'success';
		$data ['doajax'] = ($ajax == 'ajax') ? 1 : NULL; 
		$data['auto_approve']=$this->_getSethtmlRadio('auto_approve',$app['auto_approve']);
		$data['autonomous']=$this->_getSethtmlRadio('autonomous',$app['autonomous']);
		$data['status']=$this->_getAppStatusRadio('status',$app['status']);
		$data['suspended']=$this->_getSethtmlRadio('suspended',$app['suspended']);
		$data['notes']=$this->_getSethtmlRadio('notes',$app['notes']);
		$this->_set_output ( 'admin/oauthapp/oauthapp_do', $data, $ajax, $json );
	}
	function _set_output($templates, $data = NULL, $do = NULL, $json = NULL) {
		if ($do == 'ajax') {
			return $this->apilibrary->set_output ( $json );
		}
		$this->load->view ( $templates, $data );
	}
	function _getAppStatusRadio($name='name',$default = NULL,$return=false) {
		$data ['development'] = array ('value' => 'development', 'name' => '开发', 'extra' => '' );
		$data ['pending'] = array ('value' => 'pending', 'name' => '挂起', 'extra' => '' );
		$data ['approved'] = array ('value' => 'approved', 'name' => '批准', 'extra' => '' );
		$data ['rejected'] = array ('value' => 'rejected', 'name' => '拒绝', 'extra' => '' );
		if($return){
			return $data;
		}
		return $this->form->Radio ( $name, $data, '', $default );
	}
	function _getSethtmlRadio($name='name',$default = NULL,$return=false) {
		$data ['1'] = array ('value' => '1', 'name' => '是', 'extra' => '' );
		$data ['0'] = array ('value' => '0', 'name' => '否', 'extra' => '' );
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
		$val->set_rules ( 'client_id', 'App Key', 'trim|xss_clean' );
		$val->set_rules ( 'client_secret', 'App Secret', 'trim|xss_clean' );
		$val->set_rules ( 'redirect_uri', '重定向URI', 'trim|required|xss_clean' );
		$val->set_rules ( 'auto_approve', '认证', 'trim|required|xss_clean' );
		$val->set_rules ( 'autonomous', '自主', 'trim|required|xss_clean' );
		$val->set_rules ( 'status', '状态', 'trim|required|xss_clean' );
		$val->set_rules ( 'suspended', '暂停', 'trim|required|xss_clean' );
		$val->set_rules ( 'notes', '通知', 'trim|required|xss_clean' ); 
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
		$db ['client_id'] = $val->set_value ( 'client_id' )?$val->set_value ( 'client_id' ):rand(1,9999999);
		$db ['client_secret'] = $val->set_value ( 'client_secret' )?$val->set_value ( 'client_secret' ):$this->_guid();
		$db ['redirect_uri'] = $val->set_value ( 'redirect_uri' );
		$db ['auto_approve'] = $val->set_value ( 'auto_approve' );
		$db ['autonomous'] = $val->set_value ( 'autonomous' );
		$db ['auto_approve'] = $val->set_value ( 'auto_approve' );
		$db ['status'] = $val->set_value ( 'status' ); 
		$db ['suspended'] = $val->set_value ( 'suspended' ); 
		$db ['notes'] = $val->set_value ( 'notes' ); 
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
	function _guid($h = '') {
		$charid = strtolower(md5(uniqid(mt_rand(), true)));
		$hyphen = $h;
		$uuid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
		return $uuid;
	}
	
} 