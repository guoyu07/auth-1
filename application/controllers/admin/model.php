<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Model extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'apilibrary' );
		$this->load->model('sitemodel');
		$this->initialize ();
	}
	function index() {
		$data = $this->loadmodel->getpage ( array (),'','','','*','datetime' );
		$this->load->view ( 'admin/model/model', $data );
	}
	
	/**
	 * 编辑
	 * Enter description here ...
	 */
	function edit($id = NULL) {
		$this->_doEditAdd ( $id );
	}
	/**
	 * 添加
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	function add($id = NULL) {
		$this->_doEditAdd ( $id );
	}
	
	/**
	 * 添加/编辑
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	function _doEditAdd($id = NULL, $doajax = NULL) {
		$model = $this->loadmodel->get_by_field ( $id );
		$data ['datalist'] = $model;
		$data ['doajax'] = $doajax;
		$this->load->view ( 'admin/model/model_do', $data );
	}
	
	/**
	 * 模型提交
	 */
	function dopost() {
		$status = $this->_formvalidation ();
		$modelid = ! empty ( $_GET ['modelid'] ) ? $_GET ['modelid'] : (! empty ( $_POST ['modelid'] ) ? $_POST ['modelid'] : NULL);
		$json ['msg'] = '操作成功';
		if ($status ['status'] == 'failure') {
			$json ['msg'] = isset ( $status ['msg'] ) ? $status ['msg'] : '操作失败';
			$this->user_auth->redirect ( 'admin/model', $json );
			return false;
		}
		$this->user_auth->redirect ('admin/model',$json );
	}
	function _formvalidation() {
		$this->load->library ( 'form_validation' );
		$val = $this->form_validation;
		$val->set_rules ( 'name', '模型名称', 'trim|required|xss_clean' );
		$val->set_rules ( 'tablename', '模型表名称', 'trim|required|xss_clean' );
		$val->set_rules ( 'sort', '排序', 'trim|xss_clean' );
		$val->set_rules ( 'description', '描述', 'trim|required|xss_clean' );
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
		$db ['tablename'] = $val->set_value ( 'tablename' );
		$db ['sort'] = $val->set_value ( 'sort' );
		$db ['updatetime'] = time();
		$db ['description'] = $val->set_value ( 'description' );
		$modelid = $this->input->get_post('modelid');
		$model = $this->loadmodel->get_by_id ( $modelid );
		$status ['status'] = 'success';
		if ($model) {
			$this->loadmodel->update ( $db, $modelid );
			$db ['modelid'] = $modelid;
			$this->_update ($db,$model, $modelid );
			$result = $modelid;
		} else {
			$db ['datetime'] =$db ['updatetime'];
			$result=$db ['modelid'] =$this->loadmodel->guid(); ;
			$this->loadmodel->add ( $db ); 
			$this->sitemodel->initialize ( $db );
			$this->sitemodel->initialize ( $db,'_data' );
		}
		if (! $result) {
			$status ['status'] = 'failure';
		}
		return $status;
	}
	 
	function _update($db, $model,$modelid) {
		$new_table_name = $db ['tablename'];
		$old_table_name = isset ( $model ['tablename'] ) ? $model ['tablename'] : '';
		$pf = $this->db->dbprefix;
		if ($this->db->table_exists ( $old_table_name )) {
			if ($new_table_name != $old_table_name) {
				$this->load->dbforge ();
				$this->dbforge->rename_table ( $old_table_name, $new_table_name );
			}
		} 
		if ($this->db->table_exists ( $old_table_name . '_data' )) {
			if ($new_table_name . '_data' != $old_table_name . '_data') {
				$this->load->dbforge ();
				$this->dbforge->rename_table ( $old_table_name . '_data', $new_table_name . '_data' );
			}
		} 
		$this->sitemodel->initialize ( $db );
		$this->sitemodel->initialize ( $db ,'_data');  
	} 
	function delete($id = NULL) {
		$json['status'] = 'failure';
		$json['msg'] = '数据不存在';
		$json['action']='ajax';
		isset ($_POST['vurl']) ? ($json['vurl'] = $_POST['vurl']) : '';
		if (is_null($id)) {
			$this->user_auth->set_output($json);
			return FALSE;
		}
		
		$model = $this->loadmodel->get_by_id($id);
		if (!$model) {
			$this->user_auth->set_output($json);
			return FALSE;
		}
			
		if ($this->loadmodel->delete($id)) {
			$tablename =$model['tablename'] ;
			$this->initialize('model_field', 'fieldid');
			$this->loadmodel->delete('',array('modelid'=>$id));
			$this->dbforge->drop_table($tablename);
			$this->dbforge->drop_table($tablename. '_data');
			$json['msg'] = '删除成功';
			$json['status'] = 'success';
		} else {
			$json['msg'] = '删除失败';
		}
		$this->user_auth->set_output($json);
	}
	function initialize($name = 'model', $id = 'modelid') {
		$this->loadmodel->initialize ( array ('tablename' => $name, 'tableid' => $id ) );
	} 
	function detect() {
		$data = $this->loadmodel->getpage ( array (), 0, 0, '', 'tablename,modelid' );
		$str = '';
		foreach ( $data ['datalist'] as $key => $value ) {
			if (! $this->db->table_exists ( $value ['tablename'] )) {
				$str .= $value ['tablename'] . '不存在';
			}
		}
		$api ['msg'] = $str;
		$this->apilibrary->set_output ( $api );
	}
	function initialization($modelid = NULL) {
		$model = $this->loadmodel->get_by_id ( $modelid );
		$this->load->model ( 'sitemodel' );
		$model = $this->loadmodel->get_by_id ( $modelid );
		$db=$model;
		$this->sitemodel->initmain ( $db );
		$this->sitemodel->initmain_data ( $db ); 
		$this->user_auth->redirect ('admin/model','修复成功' );
	}
	function preview($modelid = NULL) {
		$this->lang->load ( 'content' );
		$this->initialize ( 'model_field' );
		$where ['modelid'] = $modelid;
		$data = $this->loadmodel->getpage ( array (), 0, 0, $where, '*', 'listorder', 'ASC' );
		//require APPPATH . 'cache/libraries/content/fields/content_form.class.php';
		$content_form = new content_form ( $modelid, 1, $this->cache_api->category () );
		$forminfos = $content_form->get ();
		$data ['datalist'] = $forminfos;
		$this->load->view ( 'admin/content/preview', $data );
	}
	 
	
	
	function truncate($modelid = NULL, $ajax = NULL) {
		$model = $this->loadmodel->get_by_id ( $modelid );
		if($model){
			$this->db->truncate($model['tablename']); 
			$this->db->truncate($model['tablename'].'_data'); 
		}
		$data ['catid'] = $modelid;
		isset ( $_POST ['vurl'] ) ? ($json ['vurl'] = $_POST ['vurl']) : '';
		$json ['status'] = 'success';
		$json ['msg'] = '所有数据已清空';
		$json ['doajax'] = ($ajax == 'ajax') ? 1 : NULL;
		return $this->apilibrary->set_output ( $json );
	}
	function disabled($modelid=null,$value=0){
		$model = $this->loadmodel->get_by_id ( $modelid );
		if(!$model){
			$this->user_auth->redirect ('admin/model','数据不存在' );
		}
		$db['disabled']=$value;
		$this->loadmodel->update ( $db, $modelid );
		$this->user_auth->redirect ('admin/model','操作成功' );
		
	}
	function _getmodelkeywords() {
		$data = array ('id', 'catid', 'typeid', 'title', 'style', 'thumb', 'keywords', 'description', 'posids', 'url', 'listorder', 'status', 'sysadd', 'islink', 'username', 'inputtime', 'updatetime', 'content', 'readpoint', 'groupids_view', 'paginationtype', 'maxcharperpage', 'template', 'paytype', 'allow_comment', 'relation', 'pages','dateline' );
		return $data;
	}

} 