<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Content extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->model('contentmodel');
		$this->_initialize();
	}
	
	function get($s = '',$p=0) {
		$this->_initialize('alias', 'aid');
		$alias = $this->loadmodel->where(array('url'=>$s),'row_array');
		if(isset($alias['type'])&&$alias['type']==0){
			return $this->contentmodel->setcontent($alias['value'],$p);
		}elseif(isset($alias['type'])&&$alias['type']==1){
			return $this->contentmodel->setcontent($alias['value']);
		}
	}
	function _initialize($name = 'users_column', $id = 'id') {
		$this->loadmodel->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}
	public function index($columnid = NULL, $id = NULL) {
		$column = $this->loadmodel->get_by_id($columnid);
		if (!$column) {
			show_404();
		}
		if ($column['type'] == 1) {
		}
		if ($column['sethtml']) {
			$_GET['mkdir'] = 1;
		}
		return $this->__content($column, $id);
	}
	function __content($column, $id) {
		$this->_initialize('model', 'modelid');
		$model = $this->loadmodel->get_by_id($column['modelid']);
		$this->_initialize($model['tablename'], 'id');
		$content = $this->loadmodel->get_by_id($id);
		if(!$content){
			$this->user_auth->redirect();
		}
		if(isset($content['islink'])&&$content['islink']){
			$this->user_auth->redirect($content['url']);
		}
		$this->_initialize($model['tablename'] . '_data', 'id');
		$content_data = $this->loadmodel->get_by_field($id);
		$content_data['content'] = getimage($content_data['content'],1);
		$this->_initialize('templates', 'tid');
		$tid = $content_data['template'] ? $content_data['template'] : $column['show_template'];
		$this->_initialize('templates', 'id');
		if($content_data['islink']){
			return $this->user_auth->redirect($content_data['links']);
		}
		$template = $this->loadmodel->get_by_id($tid);
		if (is_null($template)) {
			show_404('模板不存在');
			return FALSE;
		}
		$data['datalist'] = array_merge($content_data,$content);
		$data['title'] = $data['datalist']['title'];
		$data['keywords'] = $data['datalist']['keywords'];
		$data['description'] = $data['datalist']['description'];
		$data['column'] = $column;
		$data['ajax']=$this->input->get_post('ajax');
		$this->load->view($template['file'], $data);
	}
	

}