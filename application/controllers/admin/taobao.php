<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Taobao extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->_initialize();
		$this->load->model('taobaomodel');
		$this->load->library('taobaoke');
		$this->load->library('apilibrary');
		$this->taobaoke->app_key = '21252919';
		$this->taobaoke->app_secret = 'c703d9a1b026bb6e3dc6933f225dad08';
		$this->taobaoke->apiUrlHttps = 'http://gw.api.taobao.com/router/rest';
	}
	function _initialize($name = 'item', $id = 'item_id') {
		$this->loadmodel->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}

	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid = 0) {
		$data['uid'] = '';
		$p_config['base_url'] = 'taobao/index';
		$p_config['pagination'] = 'systempagination';
		$data = $this->loadmodel->getpage($p_config, $pid, 10, '', '*', 'dateline', 'ASC');
		$data['column_id'] = $this->_getColumnSelect('column_id', '', 1);
		$this->load->view('admin/taobao/taobao', $data);
	}
	function dopost($pid = 0) {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pramga: no-cache");
		if ($this->input->get('go')) {
			$result = $this->_getTaobaoShop($pid);
			if (isset ($result['error_response'])) {
				$data['pagination'] = '';
				$data['datalist'] = array ();
			} else {
				$config['base_url'] = 'taobao/dopost';
				$config['total_rows'] = $result['taobaoke_items_get_response']['total_results'];
				$config['per_page'] = 10;
				$config['query_string'] = '?' . $_SERVER['QUERY_STRING'];
				$this->load->library('systempagination');
				$this->systempagination->initialize($config);
				$data['pagination'] = $this->systempagination->create_links();
				$data['datalist'] = $result['taobaoke_items_get_response']['taobaoke_items']['taobaoke_item'];
			}
		} else {
			$data['cid'] = $this->_itemcats('cid');
			$data['pagination'] = '';
			$data['datalist'] = array ();
		}
		$data['sethtmlradio'] = $this->_getSethtmlRadio('item[radio]');
		$data['column_id'] = $this->_getColumnSelect('column_id');
		$this->load->view('admin/taobao/taobao_post', $data);

	}
	function import() {
		$result = $this->_getTaobaoShop();
		if (isset ($result['error_response'])) {
			$datalist = array ();
		} else {
			$datalist = $result['taobaoke_items_get_response']['taobaoke_items']['taobaoke_item'];
		}
		//PRINT_R($result);
		foreach ($datalist as $k => $v) {
			$value = $v;
			$value['user_id'] = $this->auth->ucdata['uid'];
			$value['column_id'] = 0;
			$value['dateline'] = time();
			$sid = $this->taobaomodel->add($value);
		}
		$json['status'] = 'success';
		$json['msg'] = '发布成功!';
		return $this->apilibrary->set_output($json);
	}
	function dotaobao() {
		$item = $this->input->post('item');
		$serialize = $this->input->post('serialize');
		$column_id = $this->input->post('column_id');
		foreach ($item as $k => $v) {
			$d = $serialize[$v];
			$value = unserialize($d);
			$value['user_id'] = $this->auth->ucdata['uid'];
			$value['column_id'] = $column_id;
			$value['dateline'] = time();
			$sid = $this->taobaomodel->add($value);
		}
		$json['status'] = 'success';
		$json['msg'] = '发布成功!';
		return $this->apilibrary->set_output($json);
	}
	function _itemcats($name = 'name', $default = NULL, $return = false) {
		$params['parent_cid'] = 0;
		$result = $this->taobaoke->api('taobao.itemcats.get', $params);
		if (isset ($result['error_response'])) {
			return '';
		}
		$datalist = $result['itemcats_get_response']['item_cats']['item_cat'];
		foreach ($datalist as $k => $v) {
			$s['value'] = $v['cid'];
			$s['name'] = $v['name'];
			$s['extra'] = $v['cid'];
			$data[$v['cid']] = $s;
		}
		if ($return) {
			return $data;
		}
		$this->load->library('formhandler');
		return $this->formhandler->Select($name, $data, '', $default);

	}
	function _getSethtmlRadio($name = 'name', $default = NULL, $return = false) {
		$data['1'] = array (
			'value' => '1',
			'name' => '是',
			'extra' => ''
		);
		$data['0'] = array (
			'value' => '0',
			'name' => '否',
			'extra' => ''
		);
		if ($return) {
			return $data;
		}
		$this->load->library('formhandler');
		return $this->formhandler->Radio($name, $data, '', $default, '', 'SethtmlRadio');
	}
	function _getColumnSelect($name = 'name', $default = NULL, $return = false) {
		$this->_initialize('category', 'catid');
		$config = array (
			'select' => 'catid as value,catname as name,catid as catid,catid'
		);
		$data = $this->loadmodel->getdata($config);
		if ($return) {
			foreach ($data as $key => $value) {
				$datas[$value['catid']] = $value;
			}
			return $datas;
		}
		$this->load->library('formhandler');
		return $this->formhandler->Select($name, $data, '', $default, '', 'SethtmlRadio');
	}
	function _getTaobaoShop($pid = 0) {
		$params['fields'] = "num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location,volume";
		$params['page_no'] = $pid;
		$params['page_size'] = 15;
		if ($this->input->get_post('cid')) {
			$params['cid'] = $this->input->get_post('cid');
		}
		if ($this->input->get_post('keyword')) {
			$params['keyword'] = $this->input->get_post('keyword');
		}
		if ($this->input->get_post('sort')) {
			$params['sort'] = $this->input->get_post('sort');
		}
		if ($this->input->get_post('start_price') && $this->input->get_post('end_price')) {
			$params['start_price'] = $this->input->get_post('start_price');
			$params['end_price'] = $this->input->get_post('end_price');
		}
		if ($this->input->get_post('start_commissionRate') && $this->input->get_post('end_commissionRate')) {
			$params['start_commissionRate'] = $this->input->get_post('start_commissionRate');
			$params['end_commissionRate'] = $this->input->get_post('end_commissionRate');
		}
		if ($this->input->get_post('start_commissionNum') && $this->input->get_post('end_commissionNum')) {
			$params['start_commissionNum'] = $this->input->get_post('start_commissionNum');
			$params['end_commissionNum'] = $this->input->get_post('end_commissionNum');
		}
		if ($this->input->get_post('start_totalnum') && $this->input->get_post('end_totalnum')) {
			$params['start_totalnum'] = $this->input->get_post('start_totalnum');
			$params['end_totalnum'] = $this->input->get_post('end_totalnum');
		}
		if ($this->input->get_post('start_credit') && $this->input->get_post('end_credit')) {
			$params['end_credit'] = $this->input->get_post('end_credit');
			$params['start_credit'] = $this->input->get_post('start_credit');
		}
		$result = $this->taobaoke->api('taobao.taobaoke.items.get', $params);
		return $result;
	}

}