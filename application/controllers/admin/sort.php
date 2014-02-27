<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

class Sort extends CI_Controller {
	var $category_type_system = '列表';
	var $category_type_page = '内容';
	var $category_type_link = '连接';
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'apilibrary' );
		$this->load->library ( 'formhandler' );
		$this->form = $this->formhandler;
		$this->loadmodel->_initialize ('sort');
	} 
	
	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid=0) {
		$data ['uid'] = '';
		$p_config ['base_url'] = 'sort/index';
		$data = $this->loadmodel->getpage ( $p_config,$pid, 10, '', '*', 'listorder', 'ASC' );
		$this->load->view ( 'admin/sort/welcome', $data );
	}
	
	 
	/**
	 * 添加
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function add(  $ajax = NULL) {
		$this->_doAddEdit ( NULL,   $ajax );
	}
	
	/**
	 * 编辑
	 */
	function edit($sid = NULL, $ajax = NULL) {
		$this->_doAddEdit ( $sid,   $ajax );
	
	}
	
	function dopost() {
		$status = $this->_formvalidation ();
		$id =$this->input->get_post('id');
		$json ['msg'] = '操作成功';
		if ($status ['status'] == 'failure') {
			$json ['msg'] = isset ( $status ['msg'] ) ? $status ['msg'] : '操作失败';
			$this->user_auth->redirect ('admin/sort/edit/'.$id,$json );
			return '';
		}
		$this->user_auth->redirect ('admin/sort',$json);
	
	}
	 
	 
	 
	/**
	 * 添加、编辑
	 * @param int $catid
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function _doAddEdit($sid = NULL,  $ajax = NULL) {
		$sort = $this->loadmodel->get_by_field ( $sid );
		$json ['status'] = 'success';
		$data ['datalist']=$sort;
		$data ['doajax'] = ($ajax == 'ajax') ? 1 : NULL; 
		$data ['usable_type'] = $this->_getUsabletype ($sort ['usable_type']);
		$this->_set_output ( 'admin/sort/sort_do', $data, $ajax, $json );
	}
	
	function delete($id=null){
		$column=$this->loadmodel->get_by_id($id);
		$json ['status'] = 'failure';
		if($column){
			if($this->loadmodel->delete($id)){
					$json ['msg'] = '删除成功';
					$json ['status'] = 'success';
				}else{
					$json ['msg'] = '删除失败';
				}
		}else{
			$json ['msg'] = '数据不存在';
		}
		
		$data ['catid'] = $id;
		isset ( $_POST ['vurl'] ) ? ($json ['vurl'] = $_POST ['vurl']) : '';
		$this->apilibrary->set_output ($json);
	}
	
	 
	function _formvalidation() {
		$do = ! empty ( $_GET ['doajax'] ) ? $_GET ['doajax'] : (! empty ( $_POST ['doajax'] ) ? $_POST ['doajax'] : NULL);
		$this->load->library ( 'form_validation' );
		$val = $this->form_validation;
		$val->set_rules ( 'subject', '类别名称', 'trim|required|xss_clean' ); 
		$val->set_rules ( 'usable_type', '所属栏目', 'required|xss_clean' );
		$val->set_rules ( 'listorder', '排序', 'trim|required|integer|xss_clean' ); 
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
		$db ['subject'] = $val->set_value ( 'subject' );
		$db ['listorder'] = $this->input->post ( 'listorder' ); 
		$db ['usable_type']=implode(',',$this->input->post ( 'usable_type' )); 
		$id = $this->input->get_post( 'id' );
		$category = $this->loadmodel->get_by_id ( $id );
		$status ['status'] = 'success';
		if ($category) {
			$db['updatetime']=time();
			$result =$id;
			$this->loadmodel->update ( $db, $id );
		} else {
			$db['updatetime']=$db['dateline']=time();
			$result =$db['id']=$this->loadmodel->guid();
			 $this->loadmodel->add ( $db );
		}
		if (! $result) {
			$status ['status'] = 'failure';
		}
		return $status;
	}
	
	 
	 
	 
	function _getUsabletype($default = NULL, $tname = 'usable_type[]') {
		$this->loadmodel->_initialize ( $this->config->item('user_auth_users_column_table', 'user_auth'));
		$model = $this->loadmodel->getpage ( array (), 0, 0, '', 'id as value,catname as name,id as extra' );
		if (! isset ( $model ['datalist'] )) {
			return '';
		}
		$datalist = $model ['datalist'];
		$default=explode(",",$default);
		return $this->form->Checkboxs ( $tname, $datalist,  $default );
	}
	 
	function _set_output($templates, $data = NULL, $do = NULL, $json = NULL) {
		if ($do == 'ajax') {
			return $this->apilibrary->set_output ( $json );
		}
		$this->load->view ( $templates, $data );
	}

} 