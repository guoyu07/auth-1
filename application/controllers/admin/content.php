<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Content extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions('admin');
		$this->load->library('formhandler');
		$this->load->library('apilibrary');
		$this->loadmodel->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'));
	}

	/**
	 * 主页
	 * Enter description here ...
	 */
	function index() {
		$column = $this->loadmodel->get_all(0, 0, '', 'columnid,catname,parentid', 'listorder', 'ASC')->result_array();
		$data['datalist'] = $column;
		$this->load->view('admin/content/content', $data);
	}
	function submits($columnid = NULL, $id = null, $action = null, $types = null) {
		$numberid = $this->input->get_post('checkboxs');
		if (count($numberid) > 0) {
			$where_in = $numberid;
			$column = $this->loadmodel->get_by_id($columnid);
			if (!$column['modelid']) {
				exit ();
			}
			$this->loadmodel->initialize('model', 'modelid');
			$model = $this->loadmodel->get_by_id($column['modelid']);
			$this->loadmodel->initialize($model['tablename'], 'id');
			$datalist = $this->loadmodel->where_in('id', $where_in);
		} else {
			$datalist = array ();
		}
		$templates = $this->input->get_post('templates') ? 'ajax/' . $this->input->get_post('templates') . '_content_model' : 'ajax/content_model';
		$data['datalist'] = $datalist;
		$data['action'] = '删除';
		$data['javascriptfun'] = 'delete_numberidtables';
		$json['data'] = trim($this->load->view('admin/' . $templates, $data, true));
		$json['status'] = 'success';
		$json['ids'] = implode(',', $numberid);
		if ($types) {

		}
		$this->apilibrary->set_output($json);
	}
	/**
	 * 单页
	 */
	function _colum_type_page($columnid = NULL, $do = NULL) {
		$column = $this->loadmodel->get_by_field($columnid);
		$this->loadmodel->_initialize('page','columnid');
		$page = $this->loadmodel->get_by_field($columnid);
		if (!$page['columnid']) {
			$page['columnid'] = $columnid;
		}
		$page['content'] = getimage($page['content']);
		$this->load->library('editors');
		$data['editors'] = $this->editors->getedit(array (
			'value' => $page['content'],
			'name' => 'content',
			'id' => 'content'
		));
		$data['datalist'] = $page;
		$json['status'] = 'success';
		$data['template'] = $this->_gettemplates('template', $page['template']);
		$data['doajax'] = ($do == 'ajax') ? 1 : NULL;
		$data['column'] = $column;
		$this->_set_output('admin/content/getcontent', $data, $do, $json);
	}
	function _colum_type_link($columnid = NULL){
		$column = $this->loadmodel->get_by_id($columnid);
		$this->loadmodel->_initialize('page','columnid');
		$page = $this->loadmodel->get_by_field($columnid);
		if (!$page['columnid']) {
			$page['columnid'] = $columnid;
		} 
		$data['datalist'] = $page;
		$json['status'] = 'success';
		$data['column'] = $column;
		$this->_set_output('admin/content/colum_type_link', $data);
	}
	/**
	 *表列页
	 */
	function _colum_type_list($columnid = NULL, $do = NULL, $pid = 0) {
		$column = $this->loadmodel->get_by_id($columnid);
		if (!$column['modelid']) {
			exit ();
		}
		$this->loadmodel->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_id($column['modelid']);

		$this->loadmodel->_initialize('model_field', 'fieldid');
		$model_field = $this->loadmodel->getdata(array (
			'where' => array (
				'modelid' => $column['modelid'],
				'isposition' => 1
			),
			'by' => 'listorder',
			'select' => 'name,field'
		));
		$this->loadmodel->_initialize($model['tablename'] );
		$config['base_url'] = 'admin/content/getcontent/' . $columnid;
		$config['uri_segment'] = 5;
		$config['num_links'] = 5;
		$or_like = '';
		if ($this->input->get_post('parentid')) {
			$where = array ();
		} else {
			$where['columnid'] = $columnid;
		}
		if ($this->input->get_post('title')) {
			$or_like['title'] = $_GET['title'];
		}
		if ($this->input->get_post('starttime')) {
			$where['dateline  >'] = strtotime($_GET['starttime']);
		}
		if ($this->input->get_post('stoptime')) {
			$where['dateline  <'] = strtotime($_GET['stoptime']);
		}
		$data = $this->loadmodel->getpage($config, $pid, 30, $where, '*', 'datetime', 'DESC', '', $or_like);
		$data['column'] = $column;
		$data['columnid'] = $columnid;
		$data['model'] = $model;
		$data['model_field'] = $model_field;
		$json['status'] = 'success';
		$data['doajax'] = ($do == 'ajax') ? 1 : NULL;
		$data['type'] = $this->input->get_post('type');
		$data['callback'] = $this->input->get_post('callback');
		$data['templates'] = $this->input->get_post('templates') ? $this->input->get_post('templates') : 'getpage';
		$this->_set_output('admin/content/' . $data['templates'], $data, $do, $json);
	}
	/**
	 * 加载内容
	 * Enter description here ...
	 * @param   $columnid 分类
	 * @param   $rid 回收站
	 * @param   $pid 分页
	 * @param   $do ajax
	 */
	function getcontent($columnid = NULL, $pid = 0, $do = NULL) {
		$column = $this->loadmodel->get_by_id($columnid);
		if (!$column) {
			exit ();
		}
		if($column['controller']){
			$CFG=$this->user_auth->load_plugins($column['controller'],'plugins/admin');
			if($CFG){
				return $CFG-> $column['function'] ($columnid);
			}
		}
		if ($column['type'] == '0') {
			return $this->_colum_type_list($columnid, $do, $pid);
		}elseif ($column['type'] == '1') {
			return $this->_colum_type_page($columnid, $do);
		}elseif ($column['type'] == '2') {
			return $this->_colum_type_link($columnid, $do);
		}

	}
	/**
	 * 添加
	 * @param int $parentid
	 * @param unknown_type $ajax
	 */
	function add($columnid = NULL, $ajax = NULL) {
		$this->_doAddEdit(NULL, $columnid, $ajax);
	}

	/**
	 * 删除
	 * Enter description here ...
	 * @param unknown_type $columnid
	 * @param unknown_type $id
	 * @param unknown_type $ajax
	 */
	function delete($id = NULL, $columnid = NULL, $ajax = NULL) {
		$json = $this->_actionall($id, $columnid, 'delete');
		$data['columnid'] = $columnid;
		isset ($_POST['referer']) ? ($json['referer'] = $_POST['referer']) : '';
		return $this->apilibrary->set_output($json);
	}

	/**
	 * 加入回收站
	 * Enter description here ...
	 * @param unknown_type $columnid
	 * @param unknown_type $id
	 * @param unknown_type $ajax
	 */
	function recyclebin($id = NULL, $columnid = NULL, $ajax = NULL) {
		$json = $this->_actionall($id, $columnid, 0);
		$data['columnid'] = $columnid;
		isset ($_POST['referer']) ? ($json['referer'] = $_POST['referer']) : '';
		$this->_set_output('', $data, $ajax, $json);
	}
	/**
	 * 还原回收站
	 * Enter description here ...
	 * @param unknown_type $columnid
	 * @param unknown_type $id
	 * @param unknown_type $ajax
	 */
	function alsosource($id = NULL, $columnid = NULL, $ajax = NULL) {
		$json = $this->_actionall($id, $columnid);
		$data['columnid'] = $columnid;
		isset ($_POST['referer']) ? ($json['referer'] = $_POST['referer']) : '';
		$json['status'] = 'success';
		$json['doajax'] = ($ajax == 'ajax') ? 1 : NULL;
		$this->_set_output('', $data, $ajax, $json);
	}
	/**
	 * 操作
	 * Enter description here ...
	 */
	function _actionall($id = NULL, $columnid = NULL, $action = '1') {
		$column = $this->loadmodel->get_by_id($columnid);
		if (!$column['modelid']) {
			exit ();
		}
		$this->loadmodel->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_id($column['modelid']);
		$this->loadmodel->_initialize($model['tablename'], 'id');
		$delete = $this->loadmodel->get_by_id($id);
		if ($action === 'delete') {
			$delete = $this->loadmodel->delete($id);
			if ($delete) {
				$this->loadmodel->_initialize($model['tablename'] . '_data', 'id');
				$this->loadmodel->delete($id);
				$json['status'] = 'success';
				$json['msg'] = '操作成功';
				return $json;
			}
			$action = 0;
		}
		if (!$delete) {
			$json['status'] = 'failure';
			$json['msg'] = '数据不存在';
		} else {
			if ($this->loadmodel->update(array (
					'status' => $action
				), $id)) {
				$json['status'] = 'success';
				$json['msg'] = '操作成功';
			} else {
				$json['status'] = 'failure';
				$json['msg'] = '操作失败';
			}

		}
		return $json;
	}
	/**
	 * 编辑
	 */
	function edit($id = NULL, $columnid = NULL, $ajax = NULL) {
		$this->_doAddEdit($id, $columnid, $ajax);

	}

	/**
	 * 添加、编辑 
	 */
	function _doAddEdit($id = NULL, $columnid = NULL, $ajax = NULL) {
		$column = $this->loadmodel->get_by_field($columnid);
		$columns = $this->loadmodel->getpage(array (), 0, 0, null, '*', 'listorder', 'ASC');
		$this->loadmodel->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_field($column['modelid']);
		$this->loadmodel->_initialize($model['tablename'], 'id');
		$content = $this->loadmodel->get_by_field($id);
		$this->loadmodel->_initialize($model['tablename'] . '_data', 'id');
		$content_data = $this->loadmodel->get_by_field($id);
		if ($content['columnid'] != $columnid) {
			$content['columnid'] = $columnid;
		}
		$cards = array_merge($content, $content_data);
		$this->loadmodel->_initialize('model_field', 'modelid');
		$where['modelid'] = $model['modelid'];
		$fields = $this->loadmodel->getpage(array (), 0, 0, $where, '*', 'listorder', 'ASC');
		$this->load->model('contentform');
		$this->contentform->initialize(array (
			'fields' => $fields['datalist'],
			'columns' => $column
		));
		$data = $this->contentform->getcontent($cards);
		$data['content'] = $content;
		$data['columnid'] = $columnid;
		$json['status'] = 'success';
		$json['content'] = $cards;
		$data['column'] = $column;
		$data['doajax'] = ($ajax == 'ajax') ? 1 : NULL;
		$this->_set_output('admin/content/page_do', $data, $ajax, $json);

	}
	function link_dopost(){
		$json['status'] = 'success';
		$json['msg'] = '成功';
		$columnid = $this->input->get_post('columnid');
		$column = $this->loadmodel->get_by_id($columnid);
		$this->load->library('form_validation');
		$val = $this->form_validation;
		
		$val->set_rules('columnid', '内容不存在', 'trim|required|xss_clean'); 
		$val->set_rules('content', '连接地址', 'trim|required|prep_url'); 
		if ($val->run() == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ($strarry as $k => $v) {
				$str .= $v;
			}
			$json['status'] = 'failure';
			$json['msg'] = $str;
		}else{
			$db['content'] = setimage($this->input->post('content'));
			$this->loadmodel->_initialize('page', 'columnid');
			$content = $this->loadmodel->get_by_id($columnid);
			$status['status'] = 'success';
			if ($content) {
				$db['updatetime'] = time();
				$status['msg'] = '更新成功';
				$result = $this->loadmodel->update($db, $columnid);
			} else {
				$db['columnid'] = $columnid;
				$db['dateline'] = time();
				$status['msg'] = '添加成功';
				$result = $this->loadmodel->add($db);
			}
			if (!$result) {
				$status['msg'] = '操作失败';
				$status['status'] = 'failure';
			}
			if ($status['status'] == 'failure') {
				$json['msg'] = $status['msg'];
			}
		}
		$this->user_auth->redirect('admin/content/getcontent/' . $columnid, $json);
	}
	function dopost() {
		$json['status'] = 'success';
		$json['msg'] = '成功';
		$columnid = $this->input->get_post('columnid');
		$status = $this->_formvalidation();
		if ($status['status'] == 'failure') {
			$json['msg'] = $status['msg'];
		}
		$this->user_auth->redirect('admin/content/getcontent/' . $columnid, $json);
	}
	function dopgaepost() {
		$columnid = $this->input->get_post('columnid');
		$json['status'] = 'success';
		$json['msg'] = '成功';
		$status = $this->_formpagevalidation($columnid);
		if ($status['status'] == 'failure') {
			$json['msg'] = $status['msg'];
		}
		$this->user_auth->redirect('admin/content/getcontent/' . $columnid, $json);
	}
	function _formpagevalidation($columnid) {
		if (!$columnid) {
			return FALSE;
		}
		$column = $this->loadmodel->get_by_id($columnid);
		if (is_null($column)) {
			return FALSE;
		}
		if ($column['type'] == 1) {
			return FALSE;
		}
		if (empty ($column['modelid'])) {
			return FALSE;
		}
		$modelid = $column['modelid'];
		$this->loadmodel->_initialize('model', 'modelid');
		$mode = $this->loadmodel->get_by_id($modelid);
		$this->loadmodel->_initialize('model_field', 'modelid');
		$where['modelid'] = $modelid;
		$modelfields = $this->loadmodel->getpage(array (), 0, 0, $where, '*', 'listorder', 'ASC');
		$fields = $modelfields['datalist'];
		$this->load->library('form_validation');
		$val = $this->form_validation;
		foreach ($fields as $k => $v) {
			if ($v['isadd']) {
				$val->set_rules($v['field'], $v['name'], $v['pattern']);
			}
		}
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
		$settings = $this->loadmodel->settings();
		foreach ($fields as $k => $v) {
			
			if ($v['formtype'] == 'images' || $v['formtype'] == 'image' || $v['formtype'] == 'editor' || $v['formtype'] == 'downfiles') {
				$_POST[$v['field']] = setimage($this->input->post($v['field']));
			} else {
				//$_POST[$v ['field']]=getstring($_POST[$v ['field']],$v['maxlength'],'');
			}
			if ($v['formtype'] == 'datetime') {
				$_POST[$v['field']] = strtotime($this->input->post($v['field']));
			}
			if ($v['field'] == 'copyfrom') {
				$_POST[$v['field']] = $this->input->get_post($v['field']) ? $this->input->get_post($v['field']) : $settings['website_name']['value'];
			}
			if ($v['field'] == 'copyfromurl') {
				$_POST[$v['field']] = $this->input->get_post($v['field']) ? $this->input->get_post($v['field']) : $this->config->base_url();
			}
			if ($v['isadd']) {
				$vs = $this->input->get_post($v['field']) ? $this->input->get_post($v['field']) : '0';
				if ($vs) {
					if ($v['issystem']) {
						$info['base'][$v['field']] = $vs;
					} else {
						$info['senior'][$v['field']] = $vs;
					}
				}

			}
		}
		$ucdata = $this->user_auth->ucdata;
		$info['base']['upuid'] = $ucdata['id'];
		$info['base']['upusername'] = $ucdata['username'];
		$id = $this->input->get_post('id');
		$this->loadmodel->_initialize();
		//$column = $this->loadmodel->get_by_id($columnid);
		$this->loadmodel->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_id($column['modelid']);
		$this->loadmodel->_initialize($model['tablename'], 'id');
		$content = $this->loadmodel->get_by_id($id);
		
		//print_r($info);exit;
		
		$info['base']['columnid'] = $columnid;
		if (isset ($info['base'])) {
			if ($content) {
				$info['base']['updatetime'] = time();
				$this->loadmodel->update($info['base'], $id);
			} else {
				$info['base']['columnid'] = $columnid;
				$info['base']['uid'] = $ucdata['id'];
				$info['base']['username'] = $ucdata['username'];
				if(!isset($info['base']['datetime'])&&!($info['base']['datetime'])){
					$info['base']['datetime']=time();
				}
				$info['base']['updatetime'] = time();
				$id = $this->loadmodel->add($info['base']);
			}
		}
		$this->loadmodel->_initialize($model['tablename'] . '_data', 'id');
		$content_data = $this->loadmodel->get_by_id($id);
		if (isset ($info['senior'])) {
			if ($content_data) {
				$this->loadmodel->update($info['senior'], $id);
			} else {
				$info['senior']['id'] = $id;
				$this->loadmodel->add($info['senior']);
			}
		}
		$this->loadmodel->updataurl($model['tablename'], $id, $column);
	}
	 
	function _formvalidation() {
		$do = $this->input->get_post('do');
		$this->load->library('form_validation');
		$column = $this->loadmodel->get_by_id($this->input->get_post('columnid'));
		$val = $this->form_validation;
		$val->set_rules('columnid', '内容不存在', 'trim|required|xss_clean');
		$val->set_rules('title', '标题', 'trim|xss_clean');
		$val->set_rules('keywords', '关键词', 'trim|required|xss_clean');
		$val->set_rules('description', '描述', 'trim|required|xss_clean');
		$val->set_rules('summary', '简介', 'trim|required|xss_clean');
		$val->set_rules('content', '内容', 'trim|required');
		$val->set_rules('template', '模板', 'trim|xss_clean');
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
		$db['title'] = $val->set_value('title');
		$db['keywords'] = $val->set_value('keywords');
		$db['description'] = $val->set_value('description');
		$db['keywords'] = $val->set_value('keywords');
		$db['summary'] = $val->set_value('summary');
		$db['content'] = setimage($this->input->post('content'));
		$db['template'] = $val->set_value('template');
		$columnid = $val->set_value('columnid');
		$this->loadmodel->_initialize('page', 'columnid');
		$content = $this->loadmodel->get_by_id($columnid);
		$status['status'] = 'success';
		if ($content) {
			$db['updatetime'] = time();
			$status['msg'] = '更新成功';
			$result = $this->loadmodel->update($db, $columnid);
		} else {
			$db['columnid'] = $columnid;
			$db['dateline'] = time();
			$status['msg'] = '添加成功';
			$result = $this->loadmodel->add($db);
		}
		if (!$result) {
			$status['msg'] = '操作失败';
			$status['status'] = 'failure';
		}
		//$this->load->unlink($column);
		return $status;
	}
	function _gettemplates($name = NULL, $default = NULL, $type = NULL) {
		$this->loadmodel->_initialize('templates');
		$where = '';
		if ($type) {
			$where['type'] = $type;
		}
		$templates = $this->loadmodel->get_all(0, 0, $where, '*', 'id', 'ASC')->result_array();
		$data[] = array (
			'value' => '0',
			'name' => '无模板',
			'extra' => ''
		);
		foreach ($templates as $k => $v) {
			$s['value'] = $v['id'];
			$s['name'] = $v['name'];
			$s['extra'] = '';
			$data[] = $s;
		}
		return $this->formhandler->Select($name, $data, array (
			'class' => 'rc_ins w500 required'
		), $default);
	}
	function iframe() {
		extract($_GET);
		$data['title'] = isset ($title) ? $title : '';
		$data['starttime'] = isset ($starttime) ? $starttime : '';
		$data['stoptime'] = isset ($stoptime) ? $stoptime : '';
		$data['action'] = isset ($action) ? $action : '';
		$data['q'] = isset ($q) ? $q : '';
		$this->load->view('admin/content/search_iframe', $data);
	}
	function repairdata($columnid = NULL) {
		$column = $this->loadmodel->get_by_field($columnid);
		$this->loadmodel->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_field($column['modelid']);
		$this->loadmodel->_initialize($model['tablename'], 'id');
		$sb = $this->loadmodel->getdata();
		foreach ($sb as $key => $value) {
			$this->updataurl($model['tablename'], $value['id'], $column);
		}
		$json['status'] = 'failure';
		$json['msg'] = '操作失败';
		$json['url'] = 'admin/content/getcontent/' . $columnid;
		$json['msg'] = '操作成功';
		$json['status'] = 'success';
		$this->user_auth->set_output($json);
	}
	function copy($id = NULL, $columnid = NULL, $ajax = NULL) {
		$json['status'] = 'failure';
		$json['msg'] = '操作失败';
		$json['url'] = 'admin/content/getcontent/' . $columnid;
		if ($this->loadmodel->datacopy($columnid, $id)) {
			$json['msg'] = '操作成功';
			$json['status'] = 'success';
		}
		$this->user_auth->set_output($json);
	}
	function truncate($columnid = NULL, $ajax = NULL) {
		$json['status'] = 'failure';
		$json['msg'] = '操作失败';
		if ($this->loadmodel->truncate(array (
				'columnid' => $columnid
			))) {
			$json['refresh'] = true;
			$json['status'] = 'success';
			$json['msg'] = '所有数据已清空';
			$json['doajax'] = ($ajax == 'ajax') ? 1 : NULL;
			return $this->apilibrary->set_output($json);
		}
		return $this->apilibrary->set_output($json);
	}
	function browse($id = NULL, $columnid = NULL, $ajax = NULL) {
		$column = $this->loadmodel->get_by_field($columnid);
		$columns = $this->loadmodel->getpage(array (), 0, 0, null, '*', 'listorder', 'ASC');
		$this->loadmodel->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_field($column['modelid']);
		$this->loadmodel->_initialize($model['tablename'], 'id');
		$content = $this->loadmodel->get_by_field($id);
		$this->loadmodel->_initialize($model['tablename'] . '_data', 'id');
		$content_data = $this->loadmodel->get_by_field($id);
		if ($content['columnid'] != $columnid) {
			$content['columnid'] = $columnid;
		}
		$cards = array_merge($content, $content_data);
		$this->loadmodel->_initialize('model_field', 'modelid');
		$where['modelid'] = $model['modelid'];
		$fields = $this->loadmodel->getpage(array (), 0, 0, $where, '*', 'listorder', 'ASC');
		$fieldsdatalist = array ();
		foreach ($fields['datalist'] as $k => $v) {
			$v['isadd'] = 0;
			$fieldsdatalist[$v['fieldid']] = $v;

		}
		$this->load->library('fields/contentform');
		$this->contentform->initialize(array (
			'fields' => $fieldsdatalist,
			'columns' => $columns['datalist']
		));
		$data = $this->contentform->get($cards);
		$data['content'] = $content;
		$data['columnid'] = $columnid;
		$json['status'] = 'success';
		$json['content'] = $cards;
		$data['doajax'] = ($ajax == 'ajax') ? 1 : NULL;
		$this->_set_output('admin/content/page_browse', $data, $ajax, $json);
	}
	
	/**
	 * 输出
	 * Enter description here ...
	 * @param unknown_type $templates
	 * @param unknown_type $data
	 * @param unknown_type $do
	 * @param unknown_type $json
	 */
	function _set_output($templates = NULL, $data = NULL, $do = NULL, $json = NULL) {
		if ($do == 'ajax') {
			if ($templates) {
				$json['data'] = $this->load->view($templates, $data, TRUE);
			}
			return $this->apilibrary->set_output($json);
		}
		$this->load->view($templates, $data);
	}

}