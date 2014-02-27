<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Webspider extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library('apilibrary');
		$this->load->library('phpquery');
		$this->load->library('webspiderlib');
		$this->_initialize();
	}
	function _initialize($name = 'collection_node', $id = 'nodeid') {
		$this->loadmodel->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}
	function content($id = null) {
		$node = $this->loadmodel->get_by_id($id);
		$this->_initialize('collection_content', 'id');
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
			//$nodetent = $this->loadmodel->get_by_id(1);
			//print_r($nodetent);
			if (!$nodetent['status']) {
				$count = count($ids);
				$ids = serialize($ids);
				$ins = $this->_getcontent($nodetent['url'], $node);
				if ($ins['title']) {
					$ins['status'] = 1;
					$this->loadmodel->update($ins, $cid);
					$this->_initialize();
					$update['lastdate'] = time();
					$this->loadmodel->update($update, $id);
					$nodetent['title'] = '正在处理：' . $ins['title'] . '<br/>剩余:' . $count . '条尚未处理';
				} else {
					$nodetent['title'] = '内容处理中……';
				}
			}
			$data['ids'] = $ids;
		} else {
			$nodetent['title'] = '所有的生成完毕!';
			$data['ids'] = null;
		}
		$data['node'] = $node;
		$data['nodetent'] = $nodetent;
		$this->load->view('admin/webspider/content', $data);
	}
	/**
	 * 得到需要采集的网页列表页
	 * @param array $config 配置参数
	 * @param integer $num  返回数
	 */
	public  function url_list(&$config, $num = '') {
		$url = array();
		switch ($config['sourcetype']) {
			case '1'://序列化
				$num = empty($num) ? $config['pagesize_end'] : $num;
				for ($i = $config['pagesize_start']; $i <= $num; $i = $i + $config['par_num']) {
					$url[$i] = str_replace('(*)', $i, $config['urlpage']);
				}
				break;
			case '2'://多网址
				$url = explode("\r\n", $config['urlpage']);
				break;
			case '3'://单一网址
			case '4'://RSS
				$url[] = $config['urlpage'];
				break;
		}
		return $url;
	}
	function colurllist($id = null) {
		$node = $this->loadmodel->get_by_id($id);
		$data = $this->_geturlpage($node['urlpage'], 'href', $node);
		$this->_initialize('collection_content', 'id');
		$count = 0;
		foreach ($data as $v) {
			$content['url'] =(!preg_match('!^\w+://! i', $v)) ? $node['urlpage'] . $v : $v; //'http://www.sx.hrss.gov.cn'.$v;
			$content['nodeid'] = $id;
			if (!count($this->loadmodel->getdata(array (
					'where' => $content
				)))) {
				$this->loadmodel->add($content);
				$count++;
			}

		}
		echo '共采集' . $count . '个网址';
	}
	function test($id = null) {
		$data['dopost'] = $this->input->get('dopost');
		$node = $this->loadmodel->get_by_id($id);
		$this->phpquery->defaultCharset=$node['sourcecharset'];
		echo $this->_geturlpage($node['urlpage']);
	}
	function _geturlpage($url = null, $attr = false, $node = array ()) {
		$this->phpquery->newDocumentFile($url);
		$companies = pq("a");
		$data = array ();
		if (!$attr) {
			$data = $companies->html();
			return $data;
		}
		foreach ($companies as $company) {
			$url = pq($company)->attr('href');
			if ($node['url_contain']) {
				if (strpos($url, $node['url_contain'])) {
					$data[] = $url;
				}
			}

		}
		return $data;
	}
	function _getcontent($url = null, $node = array ()) {
		$this->_initialize('collection_content', 'id');
		$this->phpquery->newDocumentFile($url);
		if ($node['title_rule']) {
			$data['title'] = trim(pq($node['title_rule'])->text());
		} else {
			$data['title'] = '';
			return $data;
		}
		//print_R(pq($node['content_rule'])->text());
		if ($node['content_rule']) {
			$data['data'] = pq($node['content_rule'])->html();
		}
		return $data;
	}
}