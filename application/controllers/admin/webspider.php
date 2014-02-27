<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Webspider extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		//$this->user_auth->check_uri_permissions('admin');
		$this->load->library('apilibrary');
		$this->load->library('webspiderlib');
		$this->_initialize();
	}
	function _initialize($name = 'collection_node', $id = 'nodeid') {
		$this->loadmodel->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}
	function gathering($id=null){
		$this->_initialize('collection_content', 'id');
		$nodetent = $this->loadmodel->get_by_id($id);
		$this->_initialize('collection_node', 'nodeid');
		$node = $this->loadmodel->get_by_id($nodetent['nodeid']);
		$html = $this->webspiderlib->get_content($nodetent, $node);
		//print_R($html)
		$status=$html;
		$status['status'] = 'success';
		$ins['data']=serialize($html);
		$ins['status'] = 1;
		$this->_initialize('collection_content', 'id');
		$this->loadmodel->update($ins, $id);
		$this->_initialize('collection_node', 'nodeid');
		$update['lastdate'] = time();
		$this->loadmodel->update($update, $nodetent['nodeid']);
		$this->apilibrary->set_output($status);
	}
	function content($id = null) {
		$node = $this->loadmodel->get_by_id($id);
		$this->_initialize('collection_content', 'id');
		$ids=NULL;
		if ($this->input->post('dosubmit')) {
			$idb = $this->input->post('ids');
			$ids = unserialize($idb);
		} else {
			$content = $this->loadmodel->getdata(array (
				'where' => array (
					'nodeid' => $id,
					'status' => 0
				),
				'select' => 'id'
			));
			foreach ($content as $k => $v) {
				$ids[] = $v['id'];
			}
		}
		if ($ids) {
			$cid = array_shift($ids);
			$ids = $ids;
			$nodetent = $this->loadmodel->get_by_id($cid);
			$html = $this->webspiderlib->get_content($nodetent, $node);
			$ins['data']=serialize($html);
			$ins['status'] = 1;
			$ins['collect_datetime'] = time();
			$this->loadmodel->update($ins, $cid);
			$this->_initialize();
			$update['lastdate'] = time();
			$this->loadmodel->update($update, $id);
			
			$count = count($ids);
			$ids = serialize($ids);
			
			$nodetent['title'] = '正在处理：' . $html['title'] . '<br/>剩余:' . $count . '条尚未处理';
			$data['ids'] = $ids;
		} else {
			$nodetent['title'] = '所有的生成完毕!';
			$data['ids'] = null;
		}
		$data['node'] = $node;
		$data['nodetent'] = $nodetent;
		$this->load->view('admin/webspider/content', $data);
	}
	function colurllist($id = null) {
		$data['dopost'] = $this->input->get('dopost');
		$node = $this->loadmodel->get_by_id($id);
		$urls = $this->webspiderlib->url_list($node, 1);
		$this->_initialize('collection_content', 'id');
		$count = 0;
		if (!empty ($urls)) {
			foreach($urls as $k =>$v){
				$db = $this->webspiderlib->get_url_lists($v, $node);
				foreach($db as $k =>$vs){
					$content['nodeid'] = $id;
					$content['url'] = $vs['url'];
					if(!$this->loadmodel->where($content)){
						$content['title'] = $vs['title'];
						$content['datetime'] = time();
						$this->loadmodel->add($content);
						$count++;
					}
					
				}
				
			}
		}
		echo '共采集' . $count . '个网址';
	}
	function test($id = null) {
		$data['dopost'] = $this->input->get('dopost');
		$node = $this->loadmodel->get_by_id($id);
		$urls = $this->webspiderlib->url_list($node, 1);
		if (!empty ($urls)) {
			$url = $this->webspiderlib->get_url_lists($urls[1], $node);
			foreach($url as $k =>$v){
				echo '网址:'.$v['url'].'<br/>'.'标题:'.$v['title'];
			}
		}

	}
}