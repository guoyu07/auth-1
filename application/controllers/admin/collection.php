<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Collection extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions('admin');
		$this->load->library('apilibrary');
		$this->load->library('phpquery');
		$this->load->library('form_validation');
		$this->_initialize();
	}
	function _initialize($name = 'collection_node', $id = 'nodeid') {
		$this->loadmodel->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}

	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid=0) {
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$p_config['base_url'] = 'admin/collection/index';
		$p_config['uri_segment'] = '4';
		$data = $this->loadmodel->getpage($p_config, $pid, 10, '', '*', 'nodeid', 'DESC');
		$category = $this->loadmodel->get_all(0, 0, '', '*', 'nodeid', 'ASC')->result_array();
		$this->load->view('admin/collection/collection', $data);
	}

	function doprogram() {
		$val = $this->form_validation;
		$val->set_rules('id', '内容不存在', 'trim|xss_clean');
		$val->set_rules('nodeid', '内容不存在', 'trim|required|xss_clean');
		$val->set_rules('columnid', '内容不存在', 'trim|required|xss_clean');
		$val->set_rules('program', '内容', 'xss_clean|callback__programs_check');
		$id = $this->input->post('id');
		$nodeid = $this->input->post('nodeid');
		$columnid = $this->input->post('columnid');
		$program = $this->input->post('program');
		if ($val->run() == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ($strarry as $k => $v) {
				$str .= $v;
			}
			$json['status'] = 'failure';
			$json['msg'] = $str;
			$this->user_auth->redirect('admin/collection/changerelease/' . $nodeid . '/' . $columnid, $json);
		}

		foreach ($program as $k => $v) {
			$data[$k] = array (
				'n' => $k,
				'v' => $v
			);
		}
		$program = serialize($data);
		$db['status'] = 0;
		$db['program'] = $program;
		$db['columnid'] = $columnid;
		$db['nodeid'] = $nodeid;
		$db['siteid'] = 1;
		//$db['ids'] =$this->input->get_post('ids');
		$this->_initialize('collection_history', 'id');
		$ids=$this->input->get_post('ids');
		$db['ids']=$ids;
		return $this->_Start_collection($db);
		/***
		if ($this->loadmodel->add($db)) {
			$ids=$this->input->get_post('ids');
			$db['ids']=$ids;
			return $this->_Start_collection($db);
		
			$this->user_auth->redirect('admin/collection', array (
				'msg' => '添加成功'
			));
			return '';
		}
		$this->user_auth->redirect('admin/collection', array (
			'msg' => '添加失败'
		));
		***/
	}
	function _programs_check($attr) {
		if (count($attr)) {
			return TRUE;
		} else {
			$this->form_validation->set_message('_programs_check', '请填写完整');
			return FALSE;
		}
	}
	
	function release($id = null,$pid = 0) {
		$node = $this->loadmodel->get_by_id($id);
		$this->_initialize('collection_content','id');
		$where['nodeid']=$id;
		$status=$this->input->get_post('status');
		if(isset($_GET['status'])){
			$where['status']=$status;
		}
		$p_config['base_url'] = 'admin/collection/release/' . $id;
		$p_config['uri_segment'] = 5;
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$data = $this->loadmodel->getpage($p_config, $pid, 20, $where, '*', 'id', 'ASC');
		$data['node'] = $node;
		$data['status_type']['0']='未采集';
		$data['status_type']['1']='已采集';
		$data['status_type']['2']='已导入';
		$this->load->view('admin/collection/collection_content', $data);
	}
	function _getParentClass($parentid = NULL, $id = NULL, $select = TRUE) {
		$this->loadmodel->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'));
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
	function import_program_add($id = 0 ){
		$data['ids']=$ids=$this->input->get_post('ids');
		$columnid=$this->input->get_post('columnid');
		if(!$columnid){
			
		}
		$node = $this->loadmodel->get_by_id($id);
		$this->_initialize('collection_history', 'id');
		$count = $this->loadmodel->getdata(array (
			'where' => array (
				'nodeid' => $id,
				'columnid' => $columnid,
				'status' => 0
			)
		));
		$this->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'), 'id');
		$category = $this->loadmodel->get_by_id($columnid);
		$data['node'] = $node;
		$data['category'] = $category;
		$data['node'] = $node;
		$data['category'] = $category;
		$this->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_field($category['modelid']);
		$this->_initialize('model_field', 'modelid');
		$where['modelid'] = $model['modelid'];
		$p_config['base_url'] = 'collection/getchangerelease/' . $id . '/' . $columnid;
		$p_config['pagination'] = 'systempagination';
		$p_config['cmt'] = ',inituser_getgoodsinfo';
		$p_config['uri_segment'] = '6';
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$fields = $this->loadmodel->getpage($p_config, 0, 0, $where, '*', 'listorder', 'ASC');
		$gethidden = $this->_gethidden();
		foreach ($fields['datalist'] as $key => $v) {
			if (in_array($v['field'], $gethidden)) {
				continue;
			}
			if ($v['isbase']) {
				$info['base'][$v['field']] = $v;
			} else {
				$info['senior'][$v['field']] = $v;
			}
		}
		$data['fields'] = $info;
		$this->load->view('admin/collection/changerelease', $data);
	}
	/**
	 * 复制
	 */
	function duplicate($id = null){
		$status['msg'] = '数据不存在';
		$node = $this->loadmodel->get_by_id($id);
		if(!$node){
			$this->user_auth->redirect('admin/collection', $status);
		}
		unset($node['nodeid']);
		$db=$node;
		$result = $this->loadmodel->add($db);
		if($result){
			$status['msg'] = '复制成功';
		}else{
			$status['msg'] = '复制失败';
		}
		$this->user_auth->redirect('admin/collection', $status);
		
	}
	function import($id = null){
		$ids = $this->input->get_post('ids');
		$type = $this->input->get_post('type');
		$node = $this->loadmodel->get_by_id($id);
		if ($type == 'all') {
			$ids='';
		} else {
			$ids = implode(',', $ids);
		}
		$data['parent'] = $this->_getParentClass('', '', false);
		$data['ids']=$ids;
		$data['node'] = $node;
		/***
		$history['funs']='result_array';
		$history['from']='collection_history';
		$history['from_id']='columnid';
		$history['join']=$this->config->item('user_auth_users_column_table', 'user_auth');
		$history['join_id']='id';
		$history['select']='J.catname,F.*'; 
		$data['collection_history'] =$this->loadmodel->join($history);
		*/
		$this->load->view('admin/collection/import', $data);
	}
	function import_content($id = 0){
		$ids=$this->input->get_post('ids');
		$node = $this->loadmodel->get_by_id($id);
		$historyid=$this->input->get_post('historyid');
		$this->_initialize('collection_history', 'id');
		$history = $this->loadmodel->get_by_id($historyid);
		$history['ids']=$ids;
		return $this->_Start_collection($history);
		
	}
	function changerelease($id = 0, $columnid = 0) {
		$node = $this->loadmodel->get_by_id($id);
		$this->_initialize('collection_history', 'id');
		$count = $this->loadmodel->getdata(array (
			'where' => array (
				'nodeid' => $id,
				'columnid' => $columnid,
				'status' => 0
			)
		));
		if (count($count)) {
			return $this->_Start_collection($count[0]);
		}
		if ($this->input->get('dotype') == 'iframe') {
			$url = $this->input->get('url') ? $this->input->get('url') : $this->user_auth->system_site_url('collection');
			$str = '<script>parent.window.location.assign("' . $url . '")</script>';
			exit ($str);
		}

		$this->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'), 'id');
		$category = $this->loadmodel->get_by_id($columnid);
		$data['node'] = $node;
		$data['category'] = $category;
		$data['node'] = $node;
		$data['category'] = $category;
		$this->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_field($category['modelid']);
		$this->_initialize('model_field', 'modelid');
		$where['modelid'] = $model['modelid'];
		$p_config['base_url'] = 'collection/getchangerelease/' . $id . '/' . $columnid;
		$p_config['pagination'] = 'systempagination';
		$p_config['cmt'] = ',inituser_getgoodsinfo';
		$p_config['uri_segment'] = '6';
		$p_config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
		$fields = $this->loadmodel->getpage($p_config, 0, 0, $where, '*', 'listorder', 'ASC');
		$gethidden = $this->_gethidden();
		foreach ($fields['datalist'] as $key => $v) {
			if (in_array($v['field'], $gethidden)) {
				continue;
			}
			if ($v['isbase']) {
				$info['base'][$v['field']] = $v;
			} else {
				$info['senior'][$v['field']] = $v;
			}
		}
		$data['fields'] = $info;
		$this->load->view('admin/collection/changerelease', $data);
	}
	function getchangerelease($id = 0, $columnid = 0, $p = 0) {
		$selet = 'title,data';
		$type = $this->input->get('type');
		$rid = $this->input->get('rid');
		$node = $this->loadmodel->get_by_field($id);
		$data['datalist'] = $node;
		$data['rid'] = $rid;
		$this->load->view('admin/collection/iframe_getchangerelease', $data);
	}

	function add($parentid = NULL, $ajax = NULL) {
		$this->_doAddEdit(NULL, $parentid, $ajax);
	}
	function edit($columnid = NULL, $ajax = NULL) {
		$this->_doAddEdit($columnid, '', $ajax);
	}
	function _doAddEdit($columnid = NULL, $parentid = NULL, $ajax = NULL) {
		$node = $this->loadmodel->get_by_field($columnid);
		$data['datalist'] = $node;
		$json['status'] = 'success';
		$data['doajax'] = ($ajax == 'ajax') ? 1 : NULL;
		$this->_set_output('admin/collection/collection_do', $data, $ajax, $json);
	}
	function dopost() {
		$status = $this->_formvalidation();
		$columnid = !empty ($_GET['id']) ? $_GET['id'] : (!empty ($_POST['id']) ? $_POST['id'] : NULL);
		$json['msg'] = '操作成功';
		if ($status['status'] == 'failure') {
			$json['msg'] = isset ($status['msg']) ? $status['msg'] : '操作失败';
			$this->user_auth->redirect('admin/collection', $json);
			return '';
		}
		$this->user_auth->redirect('admin/collection', $json);
	}
	function _formvalidation() {
		$do = !empty ($_GET['doajax']) ? $_GET['doajax'] : (!empty ($_POST['doajax']) ? $_POST['doajax'] : NULL);
		$val = $this->form_validation;
		$val->set_rules('id', '内容不存在', 'trim|xss_clean');
		$val->set_rules('name', '采集项目名', 'trim|required|xss_clean');
		$val->set_rules('sourcecharset', '采集页面编码', 'trim|required|xss_clean');
		$val->set_rules('urlpage', '序列网址', 'trim|required|xss_clean');
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
		$db['name'] = $this->input->post('name');
		$db['siteid'] = $this->input->post('siteid');
		$db['sourcecharset'] = $this->input->post('sourcecharset');
		$db['sourcetype'] = $this->input->post('sourcetype');
		$db['urlpage'] = $this->input->post('urlpage');
		$db['pagesize_start'] = $this->input->post('pagesize_start');
		$db['pagesize_end'] = $this->input->post('pagesize_end');
		$db['page_base'] = $this->input->post('page_base');
		$db['par_num'] = $this->input->post('par_num');
		$db['url_contain'] = $this->input->post('url_contain');
		$db['url_except'] = $this->input->post('url_except');
		$db['url_start'] = $this->input->post('url_start');
		$db['url_end'] = $this->input->post('url_end');
		$db['title_rule'] = $this->input->post('title_rule');
		$db['title_html_rule'] = $this->input->post('title_html_rule');
		$db['author_rule'] = $this->input->post('user_author_rule');
		$db['author_html_rule'] = $this->input->post('user_author_html_rule');
		$db['comeform_rule'] = $this->input->post('comeform_rule');
		$db['comeform_html_rule'] = $this->input->post('comeform_html_rule');
		$db['time_rule'] = $this->input->post('time_rule');
		$db['time_html_rule'] = $this->input->post('time_html_rule');
		$db['content_rule'] = $this->input->post('content_rule');
		$db['content_html_rule'] = $this->input->post('content_html_rule');
		$db['content_page_start'] = $this->input->post('content_page_start');
		$db['content_page_end'] = $this->input->post('content_page_end');
		$db['content_page_rule'] = $this->input->post('content_page_rule');
		$db['content_page'] = $this->input->post('content_page');
		$db['content_nextpage'] = $this->input->post('content_nextpage');
		$db['down_attachment'] = $this->input->post('down_attachment');
		$db['watermark'] = $this->input->post('watermark');
		$db['coll_order'] = $this->input->post('coll_order');
		$db['customize_config'] = $this->input->post('customize_config');
		$id = $val->set_value('id');
		$category = $this->loadmodel->get_by_id($id);
		$status['status'] = 'success';
		if ($category) {
			$result = $this->loadmodel->update($db, $id);
		} else {
			$result = $this->loadmodel->add($db);
		}
		if (!$result) {
			$status['status'] = 'failure';
		}
		return $status;
	} /**
									 * 输出
									 * Enter description here ...
									 * @param unknown_type $templates
									 * @param unknown_type $data
									 * @param unknown_type $do
									 * @param unknown_type $json
									 */
	function _set_output($templates, $data = NULL, $do = NULL, $json = NULL) {
		if ($do == 'ajax') {
			$json['data'] = $this->load->view($templates, $data, TRUE);
			return $this->apilibrary->set_output($json);
		}
		$this->load->view($templates, $data);
	}
	function _gethidden() {
		return array (
			'dateline',
			'updatetime',
			'updateuid',
			'updateusername',
			'uid',
			'username',
			'columnid',
			'status',
			'typeid',
			'groupids_view',
			'readpoint'
		);
	}
	/**
	 * 查看采集文章列表
	 */
	function content_detailed($id=null){
		$this->_initialize('collection_content', 'id');
 	 	$db = $this->loadmodel->get_by_field($id);
 	 	$data=unserialize($db['data']);
 	 	$status['status'] = 'success';
 	 	$status['content'] =$data['content'];
 	 	$this->apilibrary->set_output($status);
	}
 	/**
 	 * 删除采集文章列表
 	 */
 	 function content_delete($id=null){
 	 	$this->_initialize('collection_content', 'id');
 	 	$db = $this->loadmodel->get_by_id($id);
 	 	$status['status'] = 'success';
 	 	$status['url'] = site_url('admin/competence');
 	 	if($db&&$this->loadmodel->delete($id)){
 	 		$status['msg'] = '删除成功';
 	 	}else{
 	 		$status['msg'] = '删除失败';
 	 	}
 	 	$this->apilibrary->set_output($status);
 	 }
	function _Start_collection($db = array ()) {

		$program = unserialize($db['program']);
		$columnid = $db['columnid'];
		$nodeid = $db['nodeid'];

		$this->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'), 'id');
		$category = $this->loadmodel->get_by_id($columnid);

		$modelid = $category['modelid'];
		$this->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_id($modelid);
		$this->_initialize('model_field', 'modelid');
		$where['modelid'] = $modelid;
		$modelfields = $this->loadmodel->getpage(array (), 0, 0, $where, '*', 'listorder', 'ASC');
		$fields = $modelfields['datalist'];

		foreach ($fields as $k => $v) {
			if ($v['issystem']) {
				$info['base'][$v['field']] = 0;
			} else {
				$info['senior'][$v['field']] = 0;
			}
		}
		$ucdata = $this->user_auth->ucdata;
		$info['base']['username'] = $info['base']['upusername'] = $ucdata['username'];
		$info['base']['upuid'] =$info['base']['uid'] = $ucdata['id'];
		$info['base']['datetime'] = $info['base']['updatetime'] = time();
		
		$this->_initialize('collection_content', 'id');
		if(isset($db['ids'])&&$db['ids']){
			$content_d['wherein']['name']='id';
			$content_d['wherein']['value']=explode(",",$db['ids']);	
		}else{
			$content_d['where']['nodeid']=$nodeid;
			//$content_d['where']['status']=2;	
		}
		$content = $this->loadmodel->getdata($content_d);
		//print_R($this->db->last_query());print_R($program);exit;
		$info['base']['columnid'] = $columnid;
		
		$count = 0;
		$settings = $this->loadmodel->settings();
		foreach ($content as $key => $value) {
			$unserialize = unserialize($value['data']);
			foreach ($program as $y => $l) {
				if ($l['n'] == 'copyfrom') {
					$unserialize[$l['n']] = isset($unserialize[$l['v']]) ? $unserialize[$l['v']] : $settings['website_name']['value'];
					$l['v'] ='copyfrom';
				}
				if ($l['n'] == 'copyfromurl') {
					$unserialize[$l['n']] = isset($unserialize[$l['v']]) ? $unserialize[$l['v']] : $this->config->base_url();
					$l['v'] ='copyfromurl';
				}
				/**
				 * if ($l['n'] == 'description') {
					$unserialize['description'] = lib_replace_end_tag($unserialize['content'] ,20,'');
					$l['v'] ='description';
				}
				if ($l['n'] == 'keywords') {
					$unserialize['keywords'] = lib_replace_end_tag($unserialize['content'] ,20,'');
					$l['v'] ='keywords';
				}
				 */
				if ($l['v']) {
					if (isset ($info['base'][$l['n']])) {
						$info['base'][$l['n']] = $unserialize[$l['v']];
					}
					elseif (isset ($info['senior'][$l['n']])) {
						$info['senior'][$l['n']] = $unserialize[$l['v']];
					}
				}

			}
			
			if (!$info['senior']['content']) {
				continue;
			}
			$this->_initialize($model['tablename'], 'id');
			if(!$this->loadmodel->where(array('title'=>$info['base']['title'],'columnid'=>$columnid))){
				$id = $this->loadmodel->add($info['base']);
				$this->_initialize($model['tablename'] . '_data', 'id');
				$info['senior']['id'] = $id;
				$this->loadmodel->add($info['senior']);
				$this->updataurl($model['tablename'], $columnid, $id);
				
				$this->_initialize('collection_content', 'id');
				$update['status'] = 2; 
				$update['import_datetime'] = time();
				$content = $this->loadmodel->update($update, $value['id']);
		
				$count++;
			}
					
			
		}
		//print_R($info);print_R($content);print_R($program);exit;
		
		$status['msg'] = '共从采集导入' . $count . '条内容 ';
		$this->user_auth->redirect('admin/collection', $status);
 
	}
	function updataurl($tablename, $columnid, $id) {
		$this->_initialize($tablename, 'id');
		$this->loadmodel->update(array (
			'url' => ('content/index/' . $columnid . '/' . $id)
		), $id);
	}
}