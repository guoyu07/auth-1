<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
class Classification extends CI_Controller { 
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'apilibrary' );
		$this->load->library ( 'formhandler' );
		$this->form = $this->formhandler;
		$this->_initialize ();
	}
	function _initialize($name = 'classification', $id = 'cid') {
		$this->loadmodel->initialize ( array ('tablename' => $name, 'tableid' => $id ) );
	}
	 
	function index($pid=0) {
		$data ['uid'] = '';
		$p_config ['base_url'] = 'classification/index';
		$data = $this->loadmodel->getpage ( $p_config,$pid, 10, '', '*', 'listorder', 'ASC' );
		$this->load->view ( 'admin/sort/sort', $data );
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
		$id = ! empty ( $_GET ['sid'] ) ? $_GET ['sid'] : (! empty ( $_POST ['sid'] ) ? $_POST ['sid'] : NULL);
		$json ['msg'] = '操作成功';
		if ($status ['status'] == 'failure') {
			$json ['msg'] = isset ( $status ['msg'] ) ? $status ['msg'] : '操作失败';
			$this->user_auth->redirect ('admin/classification',$json ['msg'] );
			return '';
		}
		$this->user_auth->redirect ('admin/classification',$json ['msg'] );
	
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
			$getdata=$this->loadmodel->getdata(array('where'=>array('parentid'=>$id)));
			if(!count($getdata)){
				if($this->loadmodel->delete($id)){
					$json ['msg'] = '删除成功';
					$json ['status'] = 'success';
				}else{
					$json ['msg'] = '删除失败';
				}
				
			}else{
				$json ['msg'] = '请先删除子类';
			}
		}else{
			$json ['msg'] = '数据不存在';
		}
		
		$data ['catid'] = $id;
		isset ( $_POST ['vurl'] ) ? ($json ['vurl'] = $_POST ['vurl']) : '';
		$this->_set_output ( '', $data, 'ajax', $json );
	}
	
	 
	function _formvalidation() {
		$do = ! empty ( $_GET ['doajax'] ) ? $_GET ['doajax'] : (! empty ( $_POST ['doajax'] ) ? $_POST ['doajax'] : NULL);
		$this->load->library ( 'form_validation' );
		$val = $this->form_validation;
		$val->set_rules ( 'cid', '内容不存在', 'trim|xss_clean' );
		$val->set_rules ( 'category', '数据', 'trim|required|xss_clean' ); 
		$val->set_rules ( 'type', '数据', 'trim|required|xss_clean' ); 
		$val->set_rules ( 'subject', '名称', 'required|xss_clean' );
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
		$db ['type'] = $this->input->post ( 'type' ); 
		$db ['category']=$this->input->post ( 'category'); 
		$id = $val->set_value ( 'cid' );
		$datalist = $this->loadmodel->get_by_id ( $id );
		$status ['status'] = 'success';
		if ($datalist) {
			$db['updatetime']=time();
			$result = $this->loadmodel->update ( $db, $id );
		} else {
			$db['updatetime']=$db['dateline']=time();
			$result = $this->loadmodel->add ( $db );
		}
		if (! $result) {
			$status ['status'] = 'failure';
		}
		$status['cid']=$result;
		return $status;
	}
	
	function ajaxadd($cid=null){
		$category=$this->input->get_post('category');
		$type=$this->input->get_post('type');
		$db = $this->loadmodel->get_by_field ( $cid );
		$db['category']=$category;
		$db['type']=$type;
		$data ['datalist']=$db;
		$data['parameter']=$this->input->get_post('id');
		$json['data']=$this->load->view('admin/classification/classification_dopost', $data,true);
		$json['status'] = 'success';
		return $this->apilibrary->set_output($json);
		
	}
	function ajaxdopost(){
		$status = $this->_formvalidation ();
		if($status['status']=='failure'){
			return $this->apilibrary->set_output($status);
		}
		$where ['type'] = $this->input->post ( 'type' ); 
		$where ['category']=$this->input->post ( 'category'); 
		$config['where']=$where;
		$item = $this->loadmodel->getdata ($config );
		$string='';
		foreach ($item as $k =>$v) {
			$selected = $v['cid'] == $status['cid'] ? ' selected="selected"' : '';
			$string .= '<option value="' . $v['cid'] . '"' . $selected . '>' . $v['subject'] . '</option>';
		}
		$json['data']=$string;
		$json['status'] = 'success';
		$json['parameter'] = $this->input->post ( 'parameter' ); 
		return $this->apilibrary->set_output($json);
		
	}
	 
	 
	function _getUsabletype($default = NULL, $tname = 'usable_type[]') {
		$this->_initialize ( 'category', 'catid' );
		$model = $this->loadmodel->getpage ( array (), 0, 0, '', 'catid as value,catname as name,catid as extra' );
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