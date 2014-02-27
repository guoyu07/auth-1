<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
} 
class Column extends CI_Controller {
	var $category_type_system = '列表';
	var $category_type_page = '内容';
	var $category_type_link = '连接';
	function __construct() {
		parent :: __construct(); 
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->loadmodel->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'));
		$this->load->library('formhandler');
		$this->load->library('apilibrary');
		$this->form = $this->formhandler;
	}
	/**
	 * 主页
	 */
	function index($pid = 0) {
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$p_config['base_url'] = 'admin/column/index';
		$p_config['uri_segment'] = '4';
		if ($this->input->get_post('name')) {
			$link['catname'] = $this->input->get_post('name');
			$data = $this->loadmodel->getpage($p_config, $pid, 20, '', '*', 'datetime', 'DESC', '', '', $link);
		} else {
			$data = $this->loadmodel->getpage($p_config, $pid, 20, '', '*', 'datetime', 'DESC');
		}
		$this->load->view('admin/column/welcome', $data);
	}
	/**
	 * 添加
	 */
	function add($id = NULL) {
		$this->_EditAdd($id);
	}
	/**
	 * 编辑
	 */
	function edit($id = NULL) {
		$rd = $this->loadmodel->get_by_id($id);
		if (!$rd) {
			$this->user_auth->redirect('admin/column', array('msg'=>'数据不存在'));
		}
		$this->_EditAdd($id);
	}
	/**
	 * 删除
	 */
	function delete($id=null){
		$db= $this->loadmodel->get_by_id($id); 
		if (!$db) {
			$json['status'] = 'failure';
			$json['msg'] = '删除失败,数据不存在';
		} else
			if ($this->loadmodel->delete($id)) {
				$json['status'] = 'success';
				$this->input->get_post('referer') ? ($json['referer'] = $this->input->get_post('referer')) : '';
				$json['msg'] = '删除成功';
			} else {
				$json['status'] = 'failure';
				$json['msg'] = '删除失败';
			}
		if ($this->input->get_post('format')) {
			return $this->apilibrary->set_output($json);
		}
		$this->user_auth->redirect('admin/column',$json); 
	}
	/**
	 * 提交
	 */
	function dopost() {
		$status = $this->_formvalidation();
		$id= !empty ($_GET['catid']) ? $_GET['id'] : (!empty ($_POST['id']) ? $_POST['id'] : NULL);
		$json['msg'] = '操作成功';
		if ($status['status'] == 'failure') {
			$json['msg'] = isset ($status['msg']) ? $status['msg'] : '操作失败';
			$this->user_auth->redirect('admin/column',$json);
			return '';
		}
		$this->user_auth->redirect('admin/column',$json);
	}
	function _formvalidation() {
		$do = !empty ($_GET['doajax']) ? $_GET['doajax'] : (!empty ($_POST['doajax']) ? $_POST['doajax'] : NULL);
		$this->load->library('form_validation');
		$val = $this->form_validation;
		$val->set_rules('parent', '上级栏目', 'trim|required|xss_clean');
		$val->set_rules('catname', '栏目名称', 'trim|required|xss_clean');
		$val->set_rules('description', '描述', 'trim|xss_clean');
		$val->set_rules('catdir', '目录', 'trim|xss_clean');
		$val->set_rules('parentdir', '父目录', 'trim|xss_clean');
		$val->set_rules('sethtml', '生成静态', 'trim|xss_clean');
		$val->set_rules('category_template', '栏目页模板', 'trim|xss_clean');
		$val->set_rules('list_template', '列表页模板', 'trim|xss_clean');
		$val->set_rules('show_template', '内容页模板', 'trim|xss_clean');
		$val->set_rules('ismenu', '是否在导航显示', 'trim|xss_clean');
		$val->set_rules('type', '类型', 'trim|required|xss_clean');
		$val->set_rules('modelid', '模型', 'trim|xss_clean');
		$val->set_rules('listorder', '排序', 'trim|xss_clean');
		$val->set_rules('purl', '生成文件名', 'trim|xss_clean');
		$val->set_rules('image', '图片', 'trim|xss_clean');
		$val->set_rules('options', '选项', 'trim|xss_clean');
		$val->set_rules('defaultvalue', '默认值', 'trim|xss_clean');
		$val->set_rules('child', '子栏目', 'trim|xss_clean');
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
		$db['catname'] = $val->set_value('catname');
		$db['parentid'] = $val->set_value('parent');
		$db['description'] = $val->set_value('description');
		$db['catdir'] = $val->set_value('catdir');
		$db['parentdir'] = $val->set_value('parentdir');
		$db['sethtml'] = $val->set_value('sethtml');
		$db['category_template'] = $val->set_value('category_template');
		$db['list_template'] = $val->set_value('list_template');
		$db['show_template'] = $val->set_value('show_template');
		$db['ismenu'] = $val->set_value('ismenu');
		$db['type'] = $val->set_value('type');
		$db['modelid'] = $val->set_value('modelid');
		$db['listorder'] = $val->set_value('listorder');
		$db['purl'] = $val->set_value('purl');
		$db['image'] = setimage($val->set_value('image'));
		$db['usabletype'] = $this->input->get_post('usabletype');
		$db['options'] = $val->set_value('options');
		$db['defaultvalue'] = $val->set_value('defaultvalue');
		$db['controller'] = $this->input->get_post('controller');
		$db['islogin'] = $this->input->get_post('islogin');
		$db['letter'] = $this->input->get_post('letter');
		$db['function'] = $this->input->get_post('function')?$this->input->get_post('function'):'index';
		$db['child'] = $this->input->get_post('child')?$this->input->get_post('child'):'0';
		$id = $this->input->get_post('id');
		$odb = $this->loadmodel->get_by_id($id);
		//print_R($odb);exit;
		$status['status'] = 'success';
		if ($odb) {
			$db['updatetime'] = time();
			//$result = $id;
			$result =$this->loadmodel->update($db, $id);
			$this->_update($id, $db);
		} else {
			$db['updatetime'] = $db['datetime'] = time();
			//$result = $db['id'] = $this->loadmodel->uuid();
			$result =$this->loadmodel->add($db);
			$this->_update($result, $db);
		}
		if (!$result) {
			$status['status'] = 'failure';
		}
		$this->_permission($result);
		return $status;
	}
	function _update($catid = NULL, $db = NULL) {
		$this->load->model('aliasmodel');
		if (!$catid) {
			return FALSE;
		}
		$parentdir = '';
		if ($db['parentdir']) {
			$parentdir = $db['parentdir'] . '/';
		}
		//$data ['purl'] = $parentdir . $db ['catdir'] . '.html';
		$data['url'] = $this->input->get_post('purl') ? $this->input->get_post('purl') : 'lists/index/' . $catid;
		$data['pdurl'] = 'lists/index/' . $catid;
		$adata['value'] = $catid;
		$adata['url'] = $data['url'];
		$adata['type'] = $db['type'];
		$adata['alias'] = 'column';
		$this->aliasmodel->add($adata);
		$this->loadmodel->update($data, $catid);
		$category = $this->loadmodel->get_by_field($catid);
		//$this->load->unlink($category);
		$category = $this->loadmodel->get_all()->result_array();
		$s=$this->genTree($category);
		$vs=$this->_update_arrchildid($s);
		 
	}
	function _update_arrchildid($data=array()){
		foreach ($data as $k =>$v) {
			if(isset($v['children'])){
				$children=implode('|',$v['children']['children']);
				$this->loadmodel->update(array('arrchildid'=>$children),$v['catid']);
				unset($v['children']['children']);
				$this->_update_arrchildid($v['children']);
			}
		}
	}
	function _EditAdd($id = NULL) {
		$odb = $this->loadmodel->get_by_field($id);
		$parentid=0;
		if ($odb['parentid']) {
			$parentid = $odb['parentid'];
		}
		$data['datalist'] = $odb;
		$data['parent'] = $this->_getParentClass($parentid, $id, false);
		$data['sethtml'] = $this->_getSethtmlRadio('sethtml',$odb['sethtml']);
		$data['islogin'] = $this->_getSethtmlRadio('islogin',$odb['islogin']);
		$data['ismenu'] = $this->_getIsmenuRadio($odb['ismenu']);
		$data['model'] = $this->_getModelSelect($odb['modelid']);
		$json['status'] = 'success';
		$data['show_template'] = $this->_gettemplates('show_template', $odb['show_template']);
		$data['list_template'] = $this->_gettemplates('list_template', $odb['list_template']);
		$data['category_template'] = $this->_gettemplates('category_template', $odb['category_template']);
		$data['type'] = $this->_getTypeSelect($odb['type']);
		$data['usabletype'] = $this->_getUsabletype($odb['usabletype']);
		$this->load->library('editors');
		$data['editors'] = $this->editors->getedit(array (), 'kindeditorjs');
		$data['datalist']['image'] =  getimage($data['datalist']['image']);
		$this->load->view('admin/column/column_do', $data);
	}
	function genTree($items, $id = 'id', $pid = 'parentid', $son = 'children') {
		$tree = array (); //格式化的树
		$tmpMap = array (); //临时扁平数据
		foreach ($items as $item) {
			$tmpMap[$item[$id]] = $item;
		}
		foreach ($items as $item) {
			if (isset ($tmpMap[$item[$pid]]) && $item[$id] != $item[$pid]) {
				if (!isset ($tmpMap[$item[$pid]][$son])) {
					$tmpMap[$item[$pid]][$son] = array ();
				}
				$tmpMap[$item[$pid]][$son][$item[$id]] = & $tmpMap[$item[$id]];
				$tmpMap[$item[$pid]][$son]['children'][] = & $tmpMap[$item[$id]][$id];
			
			} else {
				$tree[$item[$id]] = & $tmpMap[$item[$id]];
			}
		}
		unset ($tmpMap);
		return $tree;
	}
	/**
	 * 获取分类
	 * @param unknown_type $parentid
	 * @param unknown_type $id
	 * @param unknown_type $select
	 */
	function _getParentClass($parentid = NULL, $id = NULL, $select = TRUE) {
		$where = array ();
		if ($id) {
			$where['id !='] = $id;
		}
		$category = $this->loadmodel->get_all(0, 0, $where, 'id,catname,parentid', 'listorder', 'ASC')->result_array();
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
	function _getModelSelect($default = NULL, $tname = 'modelid') {
		$model = $this->__getmodel('modelid,modelid AS value,modelid AS extra,tablename,name');
		$model_data['0'] = array (
			'extra' => '',
			'value' => '0',
			'name' => '请选择'
		);
		$cards = array_merge($model_data, $model);
		return $this->form->Select($tname, $cards, array (
			'class' => 'rc_ins w500'
		), $default);
	}
	function _getUsabletype($default = NULL, $tname = 'usabletype') {
		$datalist[] = array (
			'value' => '0',
			'name' => '无类别',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'large_text',
			'name' => '较大文本',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'rich_text',
			'name' => '富文本',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'url',
			'name' => 'URL',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'image',
			'name' => '图像',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'file',
			'name' => '文件',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'boolean',
			'name' => '布尔值',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'dropdown',
			'name' => '下拉列表',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'selectbox',
			'name' => '选择框',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'date',
			'name' => '日期 ',
			'extra' => ''
		);
		$datalist[] = array (
			'value' => 'time',
			'name' => '时间',
			'extra' => ''
		);
		return $this->form->Select($tname, $datalist, array (
			'class' => 'rc_ins w500'
		), $default);
	}
	function _getTypeSelect($default = NULL, $tname = 'type') {
		$datalist[] = array (
			'value' => '1',
			'name' => $this->category_type_page,
			'extra' => ''
		);
		$datalist[] = array (
			'value' => '0',
			'name' => $this->category_type_system,
			'extra' => ''
		);
		$datalist[] = array (
			'value' => '2',
			'name' => $this->category_type_link,
			'extra' => ''
		);
		return $this->form->Select($tname, $datalist, array (
			'class' => 'rc_ins w500'
		), $default);
	}
	function _getSethtmlRadio($name='sethtml',$default = NULL) {
		$data[] = array (
			'value' => '1',
			'name' => '是',
			'extra' => ''
		);
		$data[] = array (
			'value' => '0',
			'name' => '否',
			'extra' => ''
		);
		return $this->form->Radio($name, $data, '', $default);
	}
	function _gettemplates($name = NULL, $default = NULL, $type = NULL) {
		$this->loadmodel->_initialize('templates', 'datetime');
		$where = '';
		if ($type) {
			$where['type'] = $type;
		}
		$templates = $this->loadmodel->get_all(0, 0, $where, '*', 'datetime', 'DESC')->result_array();
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
		return $this->form->Select($name, $data, array (
			'class' => 'rc_ins w500 required'
		), $default);
	}
	
	
	function getcolumn(){
		$data['column']=$this->loadmodel->get_all(0, 0, 0, 'id,catname,parentid', 'listorder', 'ASC')->result_array();
		$this->load->view('admin/column/getcolumn', $data);
	}
	function ajaxgetcolumn(){
		$category=$this->loadmodel->get_all(0, 0, 0, 'id,catname,parentid', 'listorder', 'ASC')->result_array();
		foreach ($category as $key => $value) {
				if($value['parentid']){
					$value['url'] = site_url('admin/content/getcontent/' . $value['id']);
				}else{
					$value['url'] = site_url('admin/content/getcontent/' . $value['id']).'?parentid=1';
				}
				
				$data[] = $value;
			}
			
		$json['data'] = $data;
		return $this->apilibrary->set_output($json);
	}
	function _getIsmenuRadio($default = NULL) {
		$data[] = array (
			'value' => '1',
			'name' => '是',
			'extra' => ''
		);
		$data[] = array (
			'value' => '0',
			'name' => '否',
			'extra' => ''
		);
		return $this->form->Radio('ismenu', $data, '', $default);
	}
	function __getmodel($select = '*') {
		$this->loadmodel->_initialize('model', 'modelid');
		$model = $this->loadmodel->getpage(array (), 0, 0, '', $select,'datetime');
		if (!isset ($model['datalist'])) {
			return '';
		}
		$data = array ();
		foreach ($model['datalist'] as $k => $v) {
			$data[$v['modelid']] = $v;
		}
		return $data;
	}
	function _permission() {
		$menu = $this->loadmodel->get_all()->result_array();
		foreach ($menu as $k => $v) {
			$role[] = $v['id'];
		}
		$this->loadmodel->_initialize($this->config->item('user_auth_users_competence_table', 'user_auth'),'default');
		$roles['column'] = implode(',', $role);
		$this->loadmodel->update($roles, 2);
		//echo $this->db->last_query();exit;
	}
}