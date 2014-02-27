<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Weibo extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
	}
	function index(){
		$this->oauth('tencent');
	}
	public function oauth($fn='tencent') {
		$lib=$fn.'_weibolib';
		$this->load->library($lib);
		$this->$lib->start();
	}
	function api($fn='tencent',$api=''){
		$lib=$fn.'_weibolib';
		$this->load->library($lib);
		$this->$lib->api($api);
	}
	function resetoauth($fn='tencent',$api=''){
		$lib=$fn.'_weibolib';
		$this->load->library($lib);
		$this->$lib->ResetOauth($api);
	}
}