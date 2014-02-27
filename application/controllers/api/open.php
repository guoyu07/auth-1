<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Open extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('apilibrary');
		$this->api = $this->apilibrary;
		$this->load->model('apimodel', 'apimodel', TRUE);
		//$this->output->enable_profiler ( TRUE );
	}
	function _initialize($name = 'model', $id = 'modelid') {
		$this->apimodel->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}
	function statistics($catid=null,$id=null){
		$id = $this->input->get_post('id')?$this->input->get_post('id'):$id;
		$this->_initialize ( 'users_column', 'id' );
		$category = $this->apimodel->get_by_id ( $catid );
		$this->_initialize ( 'model', 'modelid' );
		$model = $this->apimodel->get_by_id ( $category ['modelid'] );
		$this->_initialize ( $model ['tablename'], 'id' );
		$content = $this->apimodel->get_by_id ( $id );
		$db['pageview']=$content['pageview']+1;
		$this->apimodel->update($db,$id);
		$this->api->set_output($db);
	}
	function browser_statistics() {
		$this->load->library('user_agent');
		$this->_initialize('statistics','sid'); 
		$this->set_browser_statistics();
		$data = $this->get_browser_statistics();
		//$this->output->enable_profiler ( TRUE );
		$this->api->set_output($data);
	}
	function set_browser_statistics() {
		$db['dateline'] = time();
		$db['ip'] = $this->input->ip_address();
		$db['browser'] = $this->agent->browser();
		$db['browserversion'] = $this->agent->version();
		$db['os'] = $this->agent->platform();
		$db['robot'] = $this->agent->robot();
		$db['referrer'] = $this->agent->referrer();
		$db['agent'] = $this->input->user_agent();
		$query = $this->apimodel->add($db);
	}
	function get_browser_statistics() {
		$day = date('Y-m-d');
		$days=$this->apimodel->getdata(array('where'=>"from_unixtime(dateline, '%Y-%m-%d') = '{$day}'",'select'=>'COUNT(*) AS count'));
		$visitors['day'] = $days[0]['count'];
		$days=$this->apimodel->getdata(array('select'=>'COUNT(*) AS count'));
		$visitors['all'] = $days[0]['count'];
		return $visitors;
	}
	function getall($action = NULL, $catid = NULL) {
		if (!$action) {
			return FALSE;
		}
		if (!$catid) {
			return FALSE;
		}
		$this->_initialize('category', 'catid');
		$category = $this->apimodel->get_by_id($catid);
		$modelid = $category['modelid'];
		$this->_initialize();
		$model = $this->apimodel->get_by_id($modelid);
		$this->_initialize($model['tablename'], 'id');
		$wheren = $this->input->get_post('wn');
		$wherev = $this->input->get_post('wv');
		$sn = $this->input->get_post('sn');
		$sv = $this->input->get_post('sv');
		if ($wheren) {
			$wheren = urldecode($wheren);
			$wherev = urldecode($wherev);
			$wn = explode('|', $wheren);
			$wv = explode('|', $wherev);
			if (count($wn) <= count($wv)) {
				foreach ($wn as $k => $v) {
					if ($v) {
						$this->apimodel->where(array (
							$v => $wv[$k]
						));
					}
				}
			}
		}
		$datas = $this->apimodel-> $action ();
		//print_r($this->db->last_query());
		if (!count($datas)) {
			$data['status'] = 'failure';
			$data['msg'] = '数据不存在,请认真填写.';
		} else {
			$data['status'] = 'success';
			$data['data'] = $datas;
		}
		$this->api->set_output($data);
	}
	function add($catid = NULL) {
		$this->load->library('contentlib');
		$data = $this->contentlib->validation($catid);
		$this->api->set_output($data);
	}
	function update($catid = NULL) {
		$this->load->library('contentlib');
		$data = $this->contentlib->validation($catid);
		$this->api->set_output($data);
	}
}