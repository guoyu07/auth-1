<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
class Templates extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'formhandler' );
		$this->load->library ( 'apilibrary' );
		$this->loadmodel->_initialize ('templates');
	}
	
	/**
	 * 主页
	 * Enter description here ...
	 */
	function index() {
		$data ['uid'] = '';
		$category = $this->loadmodel->get_all ( 0, 0, '', '', 'datetime' )->result_array ();
		$data ['datalist'] = $category;
		$data['type']=$this->_gettype();
		$this->load->view ( 'admin/templates/welcome', $data );
	}
	
	/**
	 * 添加
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function add($ajax = NULL) {
		$this->_doAddEdit ( $ajax );
	}
	
	/**
	 * 编辑
	 */
	function edit($id = NULL, $ajax = NULL) {
		$this->_doAddEdit ( $id, $ajax );
	
	}
	
	/**
	 * 添加、编辑 
	 */
	function _doAddEdit($id = NULL, $ajax = NULL) {
		$templates = $this->loadmodel->get_by_field ( $id );
		$data ['datalist'] = $templates;
		$json ['status'] = 'success';
		$data ['doajax'] = ($ajax == 'ajax') ? 1 : NULL;
		$this->_set_output ( 'admin/templates/templates_do', $data, $ajax, $json );
	}
	function delete($id = NULL) {
		$templates = $this->loadmodel->get_by_field ( $id );
		if (! $templates ['id']) {
			return FALSE;
		}
		$this->_deletefile ( $templates ['file'] );
		$json['url']='admin/templates'; 
		$json['msg']='操作失败';
		$json['status']='failure';
		if ($this->loadmodel->delete($id)){
			$json['msg']='操作成功';
			$json['status']='success';
		}
		
		return $this->apilibrary->set_output($json);
	}
	
	/**
	 * 添加、编辑 
	 */
	function dopost($id = NULL, $ajax = NULL) {
		$status = $this->_formvalidation ();
		$json ['msg'] = '操作成功';
		if ($status ['status'] == 'failure') {
			$json ['msg'] = '操作失败';
		}
		$this->user_auth->redirect ('admin/templates', $json ['msg'] );
	}
	function getcode($id) {
		$data = $this->loadmodel->get_by_id ( $id );
		$templatefile = FCPATH . 'assets/'.APPLICATION.'/templates/' ;
		$templatefiles = FCPATH . 'assets/'.APPLICATION.'/templates/'. $data ['file'];
		$fp = fopen ( $templatefiles . '.html', 'rb' );
		if ($fp) {
			$string = @fread ( $fp, filesize ( $templatefiles . '.html' ) );
		}
		fclose ( $fp );
		$data ['str'] = $string;
		$data ['files'] = $data ['file'];
		$data ['id'] = $data ['id'];
		$data ['templatefile'] = $templatefile;
		$this->load->library ( 'editors' );
		$data ['editors'] = $this->editors->getedit ( array ('value' => '', 'name' => 'content', 'id' => 'content' ),'kindeditorjs' );
		$this->load->view ( 'admin/templates/templates_file', $data );
	}
	function savefile() {
		$id = $this->input->post ( 'id' );
		$templates = $this->loadmodel->get_by_id ( $id );
		if (! $templates) {
			die ( '文件无法写入,请检查目录是否有可写' );
		}
		$data = $this->input->post ( 'data' );
		$templatefile = $this->input->post ( 'templatefile' );
		$files = $this->input->post ( 'files' );
		$this->loadmodel->update ( array ('data' => $data ), $id );
		$this->_MakeDir ( $templatefile );
		$fp = fopen ( $templatefile . $files . '.html', 'wb' );
		if (! $fp) {
			die ( '文件无法写入,请检查目录是否有可写' );
		}
		$length = fwrite ( $fp, $data );
		fclose ( $fp );
		$this->user_auth->redirect ('admin/templates','操作成功' );
	}
	function _deletefile($file) {
		$templatefiles = FCPATH . 'assets/'.APPLICATION.'/templates/'. $file . '.html';
		if (! is_file ( $templatefiles )) {
			return FALSE;
		}
		if (unlink ( $templatefiles )) {
			return TRUE;
		}
		return FALSE;
	}
	function _formvalidation($tid = NULL) {
		$do = ! empty ( $_GET ['doajax'] ) ? $_GET ['doajax'] : (! empty ( $_POST ['doajax'] ) ? $_POST ['doajax'] : NULL);
		$this->load->library ( 'form_validation' );
		$val = $this->form_validation;
		$val->set_rules ( 'name', '模板名称', 'trim|required|xss_clean' );
		$val->set_rules ( 'file', '模板文件名称', 'trim|required|xss_clean' );
		$val->set_rules ( 'type', '模板类型', 'trim|required|xss_clean' );
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
		$db ['type'] = $val->set_value ( 'type' );
		$tid = $this->input->get_post ( 'id' );
		$category = $this->loadmodel->get_by_id ( $tid );
		$status ['status'] = 'success';
		$db['updatetime']=time();
		if ($category) {
			$result=$tid;
			$this->loadmodel->update ( $db, $tid );
		} else {
			$db ['data'] = '';
			$result=$db ['id'] =$this->loadmodel->guid();
			$db['datetime']=$db['updatetime'];
			$this->loadmodel->add ( $db );
		}
		if (! $result) {
			$status ['status'] = 'failure';
		}
		return $status;
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
			$json ['data'] = $this->load->view ( $templates, $data, TRUE );
			return $this->apilibrary->set_output ( $json );
		}
		$this->load->view ( $templates, $data );
	}
	function _gettype(){
		return array('content'=>'内容页','page'=>'列表页');
	}
	function cache($do = 'null') {
		$do=$this->input->get_post('format');
		if ($this->deldir(APPPATH . 'views/'.APPLICATION.'/')) {
			$json['status'] = 'success';
			$json['msg'] = '模板缓存清除成功!';
			if ($do == 'json') {
				return $this->apilibrary->set_output($json);
			}else{
				$this->user_auth->redirect ('admin/templates', $json ['msg'] );
			}
		}
	}
	function deldir($dir, $virtual = false) {
		$ds = DIRECTORY_SEPARATOR;
		$dir = $virtual ? realpath($dir) : $dir;
		$dir = substr($dir, -1) == $ds ? substr($dir, 0, -1) : $dir;
		if (is_dir($dir) && $handle = opendir($dir)) {
			while ($file = readdir($handle)) {
				if ($file == '.' || $file == '..') {
					continue;
				}
				elseif (is_dir($dir . $ds . $file)) {
					$this->deldir($dir . $ds . $file);
				} else {
					unlink($dir . $ds . $file);
				}
			}
			closedir($handle);
			rmdir($dir);

		}
		return true;

	}
	
	function _MakeDir($dir_name, $mode = 0777) {
		$dir_name = str_replace ( "\\", "/", $dir_name );
		$dir_name = preg_replace ( "#(/" . "/+)#", "/", $dir_name );
		if (is_dir ( $dir_name ) !== false)
			Return true;
		$dir_name = explode ( "/", $dir_name );
		$dirs = '';
		foreach ( $dir_name as $dir ) {
			if (trim ( $dir ) != '') {
				$dirs .= $dir . "/";
				if (is_dir ( $dirs ) == false && @mkdir ( $dirs, $mode ) === false) {
					return false;
				} else {
					;
				}
			}
		}
		return true;
	} 
} 