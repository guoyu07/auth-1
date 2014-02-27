<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Sitemodel extends Ci_Model {
	var $tablename = '';
	var $tableid = '';
	public function __construct() {
		parent :: __construct();
		$this->load->model('fieldmodel');
	}
	/**
	 * 初始化
	 */
	function initialize($db, $data = '') {
		$action = 'initmain' . $data;
		$this-> $action ($db);
	} 
	function initmain($db){
		$this->fieldmodel->getbasic($db['modelid'],1,$db['tablename']);
	}
	function initmain_data($db){
		$this->fieldmodel->getextend($db['modelid'],1,$db['tablename'].'_data');
	}
}