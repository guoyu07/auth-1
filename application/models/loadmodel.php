<?php

if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Loadmodel extends MY_Model {
	public function __construct() {
		parent :: __construct();
		//$this->load->database(); 
	}
	function _initialize($name = '', $id = 'id') {
		$this->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}
	function data($data) {
		$init = array (
			'tablename' => $data['tablename'],
			'tableid' => isset ($data['id']) ? $data['id'] : 'id'
		);
		$this->initialize($init);
		$offset = isset ($data['offset']) ? $data['offset'] : 0;
		$row_count = isset ($data['row_count']) ? $data['row_count'] : 0;
		$where = isset ($data['where']) ? $data['where'] : NULL;
		$select = isset ($data['select']) ? $data['select'] : '*';
		$order = isset ($data['order']) ? $data['order'] : '';
		$by = isset ($data['by']) ? $data['by'] : 'DESC';
		$wherein = isset ($data['wherein']) ? $data['wherein'] : NULL;
		$or_like = isset ($data['or_like']) ? $data['or_like'] : NULL;
		return $this->getdata($data, $offset, $row_count, $where, $select, $order, $by, $wherein, $or_like);
	}
	function datapage($data) {
		$init = array (
			'tablename' => $data['tablename'],
			'tableid' => isset ($data['id']) ? $data['id'] : 'id'
		);
		$this->initialize($init);
		$offset = isset ($data['offset']) ? $data['offset'] : 0;
		$row_count = isset ($data['row_count']) ? $data['row_count'] : 0;
		$where = isset ($data['where']) ? $data['where'] : NULL;
		$select = isset ($data['select']) ? $data['select'] : '*';
		$order = isset ($data['order']) ? $data['order'] : '';
		$by = isset ($data['by']) ? $data['by'] : 'DESC';
		$wherein = isset ($data['wherein']) ? $data['wherein'] : NULL;
		$or_like = isset ($data['or_like']) ? $data['or_like'] : NULL;
		return $this->getpage($data, $offset, $row_count, $where, $select, $order, $by, $wherein, $or_like);
	}
	
	function getsettings() {
		$init = array (
			'tablename' => 'settings',
			'tableid' => 'id'
		);
		$this->initialize($init);
		$settings=$this->getdata();
		foreach($settings as $k => $v){
			$data[$v['name']]=$v;
		}
		return $data;
	}
	function getbrand($data) {
		$this->initialize(array (
			'tablename' => 'brand',
			'tableid' => 'id'
		));
		return $this->getdata($data);
	}
	function sortcolumn($data){
		$this->initialize(array (
			'tablename' => 'sort',
			'tableid' => 'id'
		));
		$sort = $this->get_by_id($data['id']);
		$data['wherein']=array('name'=>'id','value'=>explode(",",$sort['usable_type']));
		return $this->genTree($this->column($data),'id');
	}
	function column($data) {
		$this->initialize(array (
			'tablename' => $this->config->item('user_auth_users_column_table', 'user_auth'),
			'tableid' => 'id'
		));
		return $this->getdata($data);
	}
	function get_model_column_by_cid_data($data){
		$pl['column']=$this->get_category_id($data);
		$data['modelid']=$pl['column']['modelid'];
		$pl['data']=$this->get_model_by_data($data);
		return $pl;
	}
	
	function get_model_category_by_cid_page($data){
		$pl['column']=$this->get_category_id($data);
		$data['modelid']=$pl['column']['modelid'];
		$pl['data']=$this->get_model_by_page($data);
		return $pl;
	}
	function get_model_category_by_cid_data_join($data){
		$pl['column']=$this->get_category_id($data);
		$data['modelid']=$pl['column']['modelid'];
		$pl['data']=$this->get_model_by_data_join($data);
		return $pl;
	}
	function get_model_by_data_join($data) {
		$this->initialize(array (
			'tablename' => 'model',
			'tableid' => 'modelid'
		));
		$modelid = isset ($data['modelid']) ? $data['modelid'] : '0';
		$model = $this->get_by_id($modelid);
		$this->initialize(array (
			'tablename' => $model['tablename'],
			'tableid' => 'id'
		));
		return $this->getjoindata($data);
	}
	
	function get_category_id($data) {
		$id=$data['columnid'];
		$this->initialize(array (
			'tablename' => $this->config->item('user_auth_users_column_table', 'user_auth'),
			'tableid' => 'id'
		));
		return $this->get_by_id($id);
	}
	function get_model_by_data($data) {
		$this->initialize(array (
			'tablename' => 'model',
			'tableid' => 'modelid'
		));
		$modelid = isset ($data['modelid']) ? $data['modelid'] : '0';
		$model = $this->get_by_id($modelid);
		$this->initialize(array (
			'tablename' => $model['tablename'],
			'tableid' => 'id'
		));
		return $this->getdata($data);

	}
	function get_model_by_page($data) {
		$this->initialize(array (
			'tablename' => 'model',
			'tableid' => 'modelid'
		));
		$modelid = isset ($data['modelid']) ? $data['modelid'] : '0';
		$model = $this->get_by_id($modelid);
		$this->initialize(array (
			'tablename' => $model['tablename'],
			'tableid' => 'id'
		));
		$data['uri_segment']=isset ($data['uri_segment']) ? $data['uri_segment'] : 4;
		$offset = isset ($data['offset']) ? $data['offset'] : 0;
		$row_count = isset ($data['row_count']) ? $data['row_count'] : 0;
		$where = isset ($data['where']) ? $data['where'] : NULL;
		$select = isset ($data['select']) ? $data['select'] : '*';
		$order = isset ($data['order']) ? $data['order'] : '';
		$by = isset ($data['by']) ? $data['by'] : 'DESC';
		$wherein = isset ($data['wherein']) ? $data['wherein'] : NULL;
		$like = isset ($data['like']) ? $data['like'] : NULL;
		return $this->getpage($data, $offset, $row_count, $where, $select, $order, $by, $wherein, $like);
	}
	
	
	function getmodelcategory($data) {
		$this->initialize(array (
			'tablename' => 'category',
			'tableid' => 'columnid'
		));
		$columnid = isset ($data['columnid']) ? $data['columnid'] : '0';
		$category = $this->get_by_id($columnid);
		$data['base_url'] = $category['pdurl'];
		$this->initialize(array (
			'tablename' => 'model',
			'tableid' => 'modelid'
		));
		$modelid = isset ($category['modelid']) ? $category['modelid'] : '0';
		$model = $this->get_by_id($modelid);
		$this->initialize(array (
			'tablename' => $model['tablename'],
			'tableid' => 'id'
		));
		
		$offset = isset ($data['offset']) ? $data['offset'] : 0;
		$row_count = isset ($data['row_count']) ? $data['row_count'] : 0;
		$where = isset ($data['where']) ? $data['where'] : NULL;
		$select = isset ($data['select']) ? $data['select'] : '*';
		$order = isset ($data['order']) ? $data['order'] : '';
		$by = isset ($data['by']) ? $data['by'] : 'DESC';
		$wherein = isset ($data['wherein']) ? $data['wherein'] : NULL;
		$like = isset ($data['like']) ? $data['like'] : NULL;
		return $this->getpage($data, $offset, $row_count, $where, $select, $order, $by, $wherein, $like);

	}
	
	function getmodelpage($data) {
		$this->initialize(array (
			'tablename' => 'users_column',
			'tableid' => 'id'
		));
		$columnid = isset ($data['columnid']) ? $data['columnid'] : '0';
		$category = $this->get_by_id($columnid);
		$this->initialize(array (
			'tablename' => 'page',
			'tableid' => 'columnid'
		));
		$data['content'] = $this->get_by_id($category['id']);
		$data['column'] = $category;
		return $data;

	}
	function updataurl($tablename, $id, $category) {
		$columnid = $category['id'];
		$this->_initialize($tablename, 'id');
		if ($this->input->get_post('url')) {
			$db['url'] = $this->input->get_post('url');
		} else {
			$db['url'] = ('content/index/' . $columnid . '/' . $id);
		}
		$this->update($db, $id);
	}
	function datacopy($columnid = NULL, $id = NULL) {
		if (!$columnid) {
			return FALSE;
		}
		$category = $this->get_by_id($columnid);
		if (is_null($category)) {
			return FALSE;
		}
		if ($category['type'] == 1) {
			return FALSE;
		}
		if (empty ($category['modelid'])) {
			return FALSE;
		}
		$modelid = $category['modelid'];
		$this->_initialize('model', 'modelid');
		$model = $this->get_by_id($modelid);

		$this->_initialize($model['tablename'], 'id');
		$content = $this->get_by_id($id);
		unset ($content['id']);
		$cid = $this->add($content);
		$this->_initialize($model['tablename'] . '_data', 'id');
		$content_data = $this->get_by_id($id);
		$content_data['id'] = $cid;
		$this->add($content_data);
		$this->updataurl($model['tablename'],$cid,$category);
		return 1;
	}
	
	function getcolumndata($data=array()){
		$this->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'));
		return $this->getdata($data);
	}
	function get_module_by_id($id){
		$this->_initialize('module');
		return $this->get_by_field($id);
	}
}



