<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Modelfield extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions('admin');
		$this->load->model('contentform');
		$this->load->library('Formhandler');
		$this->load->dbforge();
		$this->_initialize();
	}
	/**
	 * 预览模型
	 */
	function index($modelid = NULL, $pid = NULL) {
		$this->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_id($modelid);
		$this->_initialize();
		$config['base_url'] = 'admin/modelfield/index/' . $modelid . '/';
		$config['uri_segment'] = 5;
		$config['num_links'] = 4;
		$where['modelid'] = $modelid;
		$data = $this->loadmodel->getpage($config, $pid, 20, $where, '*', 'listorder', 'ASC');
		$data['model'] = $model;
		$data['modelid'] = $modelid;
		$data['modelkeywords'] = $this->_getmodelkeywords();
		$data['fieldsinc'] = $this->contentform->getinc('fields');
		$this->load->view('admin/model/modelfield', $data);
	}
	public function add($modelid = NULL) {
		$this->_AddEdit(null, $modelid);
	}
	public function edit($fieldid) {
		$this->_AddEdit($fieldid);
	}
	function display($fieldid = null, $field = 'isadd', $act = '1') {
		$model_field = $this->loadmodel->get_by_id($fieldid);
		$json['url'] = 'model';
		$json['status'] = 'success';
		if ($model_field) {
			if ($act == '1') {
				$json['data'] = '关闭';
				$db[$field] = 0;
			} else {
				$json['data'] = '开启';
				$db[$field] = 1;
			}
			$this->loadmodel->update($db, $fieldid);
			$json['href'] = site_url('admin/modelfield/display/' . $fieldid . '/' . $field . '/' . $db[$field]);
			$json['msg'] = '设置成功';
			$json['url'] = $fieldid ? 'admin/modelfield/display/' . $model_field['modelid'] : 'model';
		} else {
			$json['msg'] = '数据不存在';
			$json['status'] = 'failure';
		}
		$this->user_auth->set_output($json);
	}

	public function _AddEdit($fieldid = NULL, $modelid = NULL) {
		$model_field = $this->loadmodel->get_by_field($fieldid);
		if (!$model_field['modelid']) {
			$model_field['modelid'] = $modelid;
		}
		$data['field'] = $model_field;
		$data['fieldsselect'] = $this->_GetSelect('formtype', $model_field['formtype'], 'onchange="changeformtype(this.value)"');
		$data['issystemradio'] = $this->_GetRadio('issystem', $model_field['issystem']);
		$data['isposition'] = $this->_GetRadio('isposition', $model_field['isposition']);
		$data['isomnipotent'] = $this->_GetRadio('isomnipotent', $model_field['isomnipotent']);
		$data['isunique'] = $this->_GetRadio('isunique', $model_field['isunique']);
		$data['isbase'] = $this->_GetRadio('isbase', $model_field['isbase']);
		$data['issearch'] = $this->_GetRadio('issearch', $model_field['issearch']);
		$data['isadd'] = $this->_GetRadio('isadd', $model_field['isadd']);
		$data['issystem'] = $this->_GetRadio('issystem', $model_field['issystem']);
		$data['datatypes'] = $this->_DataTypes('datatypes', $model_field['datatypes']);
		$this->load->view('admin/modelfield/modelfield_do', $data);
	}
	function dopost() {
		$json['status'] = 'success';
		$json['msg'] = '成功';
		$status = $this->_formvalidation();
		if ($status['status'] == 'failure') {
			$json['msg'] = $status['msg'];
		}
		$json['url'] = 'admin/modelfield/index/' . $this->_GetDate('modelid');
		$this->user_auth->set_output($json);
	}
	function delete($fieldid = NULL) {
		$json['status'] = 'failure';
		$json['msg'] = '数据不存在';
		$json['action'] = 'ajax';
		isset ($_POST['vurl']) ? ($json['vurl'] = $_POST['vurl']) : '';
		if (is_null($fieldid)) {
			$this->user_auth->set_output($json);
			return FALSE;
		}
		$field = $this->loadmodel->get_by_id($fieldid);
		if (!$field) {
			$this->user_auth->set_output($json);
			return FALSE;
		}
		$this->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_id($field['modelid']);
		if (!$model) {
			$this->user_auth->set_output($json);
			return FALSE;
		}
		$this->_initialize();
		if ($this->loadmodel->delete($fieldid)) {
			$tablename = $field['issystem'] ? $model['tablename'] : $model['tablename'] . '_data';
			$this->dbforge->drop_column($tablename, $field['field']);
			$json['msg'] = '删除成功';
			$json['status'] = 'success';
		} else {
			$json['msg'] = '删除失败';
		}
		$this->user_auth->set_output($json);
	}
	function getlistorder($fieldid = NULL,$ajax = NULL){
		$data['datalist'] = $this->loadmodel->get_by_field($fieldid);
		$data['ajax']=$ajax;
		$this->load->view('admin/modelfield/getlistorder', $data);
	}
	function setlistorder($fieldid = NULL, $modelid = NULL){
		$json['url'] = $modelid ? 'admin/modelfield/index/' . $modelid : 'model';
		$json['msg'] = '操作成功';
		$this->user_auth->set_output($json);
	}
	function _formvalidation() {
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('formtype', '字段类', 'trim|required|xss_clean');
		$val->set_rules('issystem', '主表字段', 'trim|required|xss_clean');
		$val->set_rules('constraint', '长度', 'trim|required|xss_clean');
		$val->set_rules('defaultvalues', '默认值', 'trim|required|xss_clean');
		//$val->set_rules('comment', '注释', 'trim|required|xss_clean');
		$val->set_rules('field', '字段名', 'trim|required|xss_clean');
		$val->set_rules('name', '字段别名', 'trim|required|xss_clean');
		$val->set_rules('tips', '字段提示', 'trim|xss_clean');
		$val->set_rules('modelid', '模型', 'trim|xss_clean');
		$val->set_rules('pattern', '数据校验正则', 'trim|xss_clean');
		if ($val->run() == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ($strarry as $k => $v) {
				$str .= $v;
			}
			$status['status'] = 'failure';
			$status['msg'] = $str;
			return $status;
		}
		$fieldid = $this->_GetDate('fieldid');
		$model_field = $this->loadmodel->get_by_field($fieldid);
		$db['formtype'] = $val->set_value('formtype');
		$db['issystem'] = $val->set_value('issystem');
		$db['constraint'] = $val->set_value('constraint');
		$db['defaultvalues'] = $val->set_value('defaultvalues');
		$db['comment'] = $val->set_value('comment');
		$db['field'] = $val->set_value('field');
		$db['name'] = $db['comment'] = $val->set_value('name');
		$db['tips'] = $val->set_value('tips');
		$db['modelid'] = $val->set_value('modelid');
		$db['pattern'] = $val->set_value('pattern');
		$db['formattribute'] = $this->input->post('formattribute');
		$db['css'] = $this->input->post('css');
		$db['minlength'] = $this->input->post('minlength');
		$db['maxlength'] = $this->input->post('maxlength');
		$db['errortips'] = $this->input->post('errortips');
		$db['isadd'] = $this->input->post('isadd');
		$db['datatypes'] = $this->input->post('datatypes');
		$db['setting'] = $this->input->post('setting');
		$db['isbase'] = $this->input->post('isbase');
		$db['isposition'] = $this->input->post('isposition');
		//print_r($db);exit();
		if ($model_field['fieldid']) {
			$result = $this->loadmodel->update($db, $fieldid);
			$this->_UpdataDataBase($db['modelid'], $model_field, $db);
			$status['status'] = 'success';
			$status['msg'] = '更新成功';
			return $status;
		}

		$id = $db['fieldid']=$this->loadmodel->guid();
		$this->loadmodel->add($db);
		if ($id) {
			$this->_UpdataDataBase($db['modelid'], $model_field, $db);
			$status['status'] = 'success';
			$status['msg'] = '更新成功';
			return $status;
		}
	}
	function _add_column($tablename, $field, $fields) {
		if ($this->db->field_exists($field, $tablename)) { 
			$this->dbforge->modify_column($tablename, $fields);
			return TRUE;
		} else {
			$dh = $dt = array ();
			foreach ($fields as $k => $v) {
				foreach ($v as $dk => $dv) {
					if ($dk != 'name') {
						$dt[$dk] = $dv;
					}
				}
				$dh[$k] = $dt;
			} 
			$this->dbforge->add_column($tablename, $dh);
			return TRUE;
		}
	}
	/**
	 * 更新数据库
	 * Enter description here ...
	 * @param unknown_type $old
	 * @param unknown_type $new
	 */
	function _UpdataDataBase($modelid, $oldfield = array (), $newfield = array ()) {
		$this->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_id($modelid);
		$fd = $this->contentform->gefield($newfield);
		$fd['name'] = $newfield['field'];
		if ($oldfield['field']) {
			$fields[$oldfield['field']] = $fd;
		} else {
			$fields[$newfield['field']] = $fd;
		}
		
		if ($oldfield['issystem']) {
			if ($newfield['issystem']) {
				$this->_add_column($model['tablename'], $oldfield['field'], $fields);
			} else {
				if ($oldfield['field']&&$this->db->field_exists($model['tablename'], $oldfield['field'])) {
					$this->dbforge->drop_column($model['tablename'], $oldfield['field']);
				}
				$this->_add_column($model['tablename'] . '_data', $oldfield['field'], $fields);
			}
		} else {
			if ($newfield['issystem']) {
				if ($oldfield['field']&&$this->db->field_exists($model['tablename'] . '_data', $oldfield['field'])) {
					$this->dbforge->drop_column($model['tablename'] . '_data', $oldfield['field']);
				}
				$this->_add_column($model['tablename'], $oldfield['field'], $fields);
			} else {
				$this->_add_column($model['tablename'] . '_data', $oldfield['field'], $fields);
			}
		}

	}
	function _initialize($name = 'model_field', $id = 'fieldid') {
		$this->loadmodel->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}
	function _DataTypes($name, $default = NULL, $extra = NULL) {
		$fields = $this->contentform->getinc('datatypes');
		$data = array ();
		foreach ($fields as $k => $v) {
			$s['value'] = $k;
			$s['name'] = $v;
			$s['extra'] = '';
			$data[] = $s;
		}
		return $this->formhandler->Select($name, $data, '', strtoupper($default), $extra);

	}
	function _GetSelect($name, $default = NULL, $extra = NULL) {
		$fields = $this->contentform->getinc('addfields');
		$data = array ();
		foreach ($fields as $k => $v) {
			$s['value'] = $k;
			$s['name'] = $v;
			$s['extra'] = '';
			$data[] = $s;
		}
		return $this->formhandler->Select($name, $data, array (
			'class' => "rc_ins w500 required"
		), $default, $extra);
	}
	function _GetDate($name) {
		return isset ($_POST[$name]) ? $_POST[$name] : (isset ($_GET[$name]) ? $_GET[$name] : '0');
	}
	function _GetRadio($name, $default = NULL, $extra = NULL) {
		$data[0] = array (
			'value' => 0,
			'name' => '否',
			'extra' => ''
		);
		$data[1] = array (
			'value' => 1,
			'name' => '是',
			'extra' => ''
		);
		return $this->formhandler->Radio($name, $data, '', $default, $extra);
	}
	function _getmodelkeywords() {
		$data = array (
			'id',
			'catid',
			'typeid',
			'title',
			'style',
			'keywords',
			'description',
			'url',
			'listorder',
			'status',
			'sysadd',
			'islink',
			'username',
			'inputtime',
			'updatetime',
			'dateline'
		);
		return $data;
	}
	function getformtype($f = '', $fieldid = '') {
		$method = $f;
		$data['type'] = $method;
		$data['model_field'] = $this->loadmodel->get_by_field($fieldid);
		$json['data'] = $this->load->view('admin/modelfield/modelfield_form', $data, true);
		$json['status'] = 'success';
		$json['action'] = 'ajax';
		$this->user_auth->set_output($json);
	}
	function lasmove($fieldid = NULL, $modelid = NULL) {
		$action = $modelid ? 'admin/modelfield/index/' . $modelid : 'modelfield';
		if ($this->_modelfieldmove($fieldid, '+1')) {
			$action = $modelid ? 'admin/modelfield/index/' . $modelid : 'model';
		}
		$this->user_auth->redirect($action);
	}
	function tnextmove($fieldid = NULL, $modelid = NULL) {
		$action = $modelid ? 'admin/modelfield/index/' . $modelid : 'modelfield';
		if ($this->_modelfieldmove($fieldid, '-1')) {
			$action = $modelid ? 'admin/modelfield/index/' . $modelid : 'model';
		}
		$this->user_auth->redirect($action);
	}
	function _modelfieldmove($fieldid, $action) {
		$this->_initialize('model_field', 'fieldid');
		$field = $this->loadmodel->get_by_id($fieldid);
		if (!$field) {
			return FALSE;
		}
		$listorder = ( int ) $field['listorder'] + $action;
		if ($listorder < 0) {
			$listorder = 0;
		}
		$data['listorder'] = $listorder;
		return $this->loadmodel->update($data, $fieldid);

	}

}