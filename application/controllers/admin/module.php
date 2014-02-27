<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Module extends CI_Controller {
	var $category_type_page = '列表';
	var $category_type_content = '内容';
	var $category_type_link = '连接';
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions('admin');
		$this->load->library('apilibrary');
		$this->load->library('formhandler');
		$this->form = $this->formhandler;
		$this->loadmodel->_initialize('module');
		$this->data ['isdb'] [0] = array ('name' => '关闭', 'value' => '0', 'extra' => '' );
		$this->data ['isdb'] [1] = array ('name' => '开启', 'value' => '1', 'extra' => '' );
		$this->data ['db'] = array ('subject', 'data' );
	}

	function index($pid = 0) {
		$data['uid'] = '';
		$p_config['base_url'] = 'module/index';
		$data = $this->loadmodel->getpage($p_config, $pid, 10, '', '*', 'listorder', 'ASC');
		$this->load->view('admin/module/welcome', $data);
	}

	/**
	 * 添加
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function add($ajax = NULL) {
		$this->_doAddEdit(NULL, $ajax);
	}

	/**
	 * 编辑
	 */
	function edit($sid = NULL, $ajax = NULL) {
		$this->_doAddEdit($sid, $ajax);

	}

	function dopost() {
		$status = $this->_formvalidation();
		$id =$this->input->get_post('id');
		$json['msg'] = '操作成功';
		if ($status['status'] == 'failure') {
			$json['msg'] = isset ($status['msg']) ? $status['msg'] : '操作失败';
			$this->user_auth->redirect('admin/module', $json);
			return '';
		}
		$this->user_auth->redirect('admin/module', $json);

	}

	/**
	 * 添加、编辑
	 * @param int $catid
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function _doAddEdit($sid = NULL, $ajax = NULL) {
		$datalist = $this->loadmodel->get_by_field($sid);
		$json['status'] = 'success';
		$data['datalist'] = $datalist;
		$data['parent'] = $this->_getParentClass($datalist['column'], 0, false);
		$data['type'] = $this->_getTypeSelect($datalist['type']);
		$data ['isdb'] = $this->formhandler->Radio ( 'isdb', $this->data ['isdb'], '', $datalist ['isdb'] );
		$data ['db'] = $this->_getdb ();
		$data ['getra_start_num'] = $this->_getra_start_num ();
		$this->load->view('admin/module/module_do', $data, $ajax, $json);
	}

	function delete($id = null) {
		$column = $this->loadmodel->get_by_id($id);
		$json['status'] = 'failure';
		if ($column) {
			if ($this->loadmodel->delete($id)) {
				$json['msg'] = '删除成功';
				$json['status'] = 'success';
			} else {
				$json['msg'] = '删除失败';
			}
		} else {
			$json['msg'] = '数据不存在';
		}

		$data['catid'] = $id;
		isset ($_POST['vurl']) ? ($json['vurl'] = $_POST['vurl']) : '';
		$this->user_auth->set_output($json);
	}
	function call($id = 0) {
		if (! $id) {
			$this->user_auth->redirect ();
		}
		$datalist = $this->loadmodel->get_by_id ( $id );
		if (! $datalist) {
			$this->user_auth->redirect ();
		}
		$modules = FCPATH . 'data/modules/m' . $id . '.php';
		$data ['modules'] = FCPATH . 'data/modules/m' . $id . '.php';
		$data ['datalist'] = $datalist;
		$data ['template'] = '&#60;!--{MODULE ' . $datalist ['id'] . '}--&#62;';
		$data ['xml'] = $this->user_auth->site_url ( 'modules/getmxl/' . $datalist ['id'], 1 );
		$data ['json'] = $this->user_auth->site_url ( 'modules/getjson/' . $datalist ['id'], 1 );
		$data ['javascript'] = '&#60script language="javascript" type="text/javascript" src="' . $this->user_auth->site_url ( 'modules/getjavascript/' . $datalist ['id'], 1 ) . '"&#62;&#60/script&#62;';
		$this->load->view ( 'admin/module/call', $data );
	
	}
	function _formvalidation() {
		$do = $this->input->get_post('doajax');
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('subject', '类别名称', 'trim|required|xss_clean');
		//$val->set_rules('column', '所属栏目', 'required|xss_clean');
		if ($val->run() == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ($strarry as $k => $v) {
				$str .= $v;
			}
			$json['status'] = 'failure';
			$json['msg'] = $str;
			return $json;
		}
		$db['subject'] = $this->input->get_post('subject');
		$db['name'] = $this->input->get_post('name');
		$db['listorder'] = $this->input->get_post('listorder');
		$db['column'] = $this->input->get_post('column');
		//$db['type'] = $this->input->get_post('type');
		$db['data'] = $this->input->get_post('data');
		$db['isdb'] = $this->input->get_post('isdb');
		$db['ispage'] = $this->input->get_post('ispage');
		
		if($this->input->get_post('startnum')){
			$db['startnum'] =$this->input->get_post('startnum');
		}
		if($this->input->get_post('endnum')){
			$db['endnum'] =$this->input->get_post('endnum');
		}
		if($this->input->get_post('num')){
			$db['num'] =$this->input->get_post('num');
		}
		$id = $this->input->get_post('id');
		$category = $this->loadmodel->get_by_id($id);
		$status['status'] = 'success';
		if ($category) {
			$db['updatetime'] = time();
			$this->loadmodel->update($db, $id);
		} else {
			$db['updatetime'] = $db['dateline'] = time();
			$id = $db['id']=$this->loadmodel->guid();
			$this->loadmodel->add($db);
		}
		if (!$id) {
			$status['status'] = 'failure';
		}
		if($db['db']==1){
			
		}elseif($db['db']==2){
			
		}{
			
		}
		$action='get_model_column_by_cid_data';
		$return ='module_'.$db['name'];
		$data['columnid']=$db['column'];
		$data['offset']=$db['startnum'];
		$data['row_count']=$db['endnum'];
		$str = '$' . $return . '=$ci->loadmodel->' . $action . '(' . arr_to_html($data) . ');';
		$code='<?php ' . $str . ' ?>';
		$dbs['code']=$code;
		$this->loadmodel->update($dbs, $id);
		return $status;
	}
	function _getTypeSelect($default = NULL, $tname = 'type') {
		$datalist[] = array (
			'value' => 'page',
			'name' => $this->category_type_page,
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'content',
			'name' => $this->category_type_content,
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'link',
			'name' => $this->category_type_link,
			'extra' => ''
		);
		return $this->form->Select($tname, $datalist, array (
			'class' => 'rc_ins w500'
		), $default,"onchange ='changetype(this.value)'");
	}
	function _getParentClass($parentid = NULL, $catid = NULL, $select = TRUE) {
		$where = array ();
		if ($catid) {
			$where['catid !='] = $catid;
		}
		$category = $this->loadmodel->getcolumndata(array('select'=>'id,catname,parentid'));
		$data['0']['value'] = 0;
		$data['0']['parentid'] = '-1';
		$data['0']['name'] = '无分类';
		$data['0']['extra'] = '';
		foreach ($category as $k => $v) {
			$v['value'] = $v['id'];
			$v['name'] = $v['catname'];
			$v['parentid'] = $v['parentid'];
			$v['extra'] = '';
			$data[$v['id']] = $v;
		}
		if ($select) {
			return $this->form->Select('parent', $data, array (
				'class' => 'rc_ins w500'
			), $parentid);
		}
		return $data;
	}
	function _getdb($dt = '') {
		$data ['1'] = array ('name' => '文章', 'value' => '1', 'extra' => '' );
		$data ['2'] = array ('name' => '文章分类', 'value' => '2', 'extra' => '' );
		$this->load->library ( 'formhandler' );
		return $this->formhandler->Radio ( 'db', $data, '', $dt );
	}
	function _getra_start_num($dt = '') {
		$data ['0'] = array ('name' => '获取部分数据', 'value' => '0', 'extra' => '' );
		$data ['1'] = array ('name' => '获取全部数据', 'value' => '1', 'extra' => '' );
		$this->load->library ( 'formhandler' );
		return $this->formhandler->Radio ( 'ispage', $data, '', $dt ? $dt : 0 );
	}}