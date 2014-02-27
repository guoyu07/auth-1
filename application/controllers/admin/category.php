<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Category extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->load->library('apilibrary');
		$user = $this->user_auth->ucdata;
		$this->dbroles = $this->dbroles($user['roleid']);
		$this->loadmodel->_initialize($this->config->item('user_auth_users_column_table', 'user_auth'));
	}
	function dbroles($id = null) {
		$this->loadmodel->_initialize($this->config->item('user_auth_users_competence_table', 'user_auth'));
		return $this->loadmodel->get_by_id($id);
	}
	function index() {
		$this->ajaxgetcategory();
	}

	function genTree($items, $id = 'catid', $pid = 'parentid', $son = 'children') {
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
			} else {
				$tree[$item[$id]] = & $tmpMap[$item[$id]];
			}
		}
		unset ($tmpMap);
		return $tree;
	}
	function get_side() {
		$acl = explode(',', $this->dbroles['column']);
		$category = $this->loadmodel->where_in('id', $acl, 'id,catname,parentid');
		$data['datalist'] = $category;
		return $this->load->view('admin/menus/side', $data, true);
	}
	function ajaxgetcategory() {
		/**
		$acl = explode ( ',', $this->dbroles ['column'] );
		$category = $this->loadmodel->where_in('id',$acl,'id,catname,parentid');
		if($this->input->get_post('htmls')){
			$data['datalist']=$this->genTree($category);
			$json['data'] = $this->load->view('admin/menus/side', $data,true);
		}else{
			$data=array();
			foreach ($category as $key => $value) {
				if($value['parentid']){
					$value['url'] = site_url('admin/content/getcontent/' . $value['id']);
				}else{
					$value['url'] = site_url('admin/content/getcontent/' . $value['id']).'?parentid=1';
				}
				$value['url'] = site_url('admin/content/getcontent/' . $value['id']);
				$data[] = $value;
			}
			$json['data'] = $data;
		}
		**/
		$json['side'] = $this->get_side();
		$json['nav'] = $this->get_nav();
		return $this->apilibrary->set_output($json);
	}
	function get_nav() {
		$acl = explode(',', $this->dbroles['menus']);
		$this->loadmodel->_initialize($this->config->item('user_auth_users_menus_table', 'user_auth'));
		$menus = array ();
		$menu = $this->loadmodel->where_in('id', $acl);
		foreach ($menu as $k => $v) {
			$v['url'] = site_url('admin/' . $v['url']);
			$menus[$v['pid']][] = $v;
		}
		//print_r($menus);
		$data['menus'] = $menus;
		return $this->load->view('admin/menus/nav', $data, true);
	}

	function _set_output($templates, $data = NULL, $do = NULL, $json = NULL) {
		if ($do == 'ajax') {
			$json['data'] = $this->load->view($templates, $data, TRUE);
			return $this->apilibrary->set_output($json);
		}
		$this->load->view($templates, $data);
	}

}