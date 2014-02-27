<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Avatar extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'avatarlib' );
	}
	function index() {
		$data ['uid'] = $this->user_auth->ucdata['uid'];
		$data ['avatarflash'] = $this->avatarlib->uc_avatar ( $data ['uid'] );
		$data ['avatarhtml'] = $this->avatarlib->avatar_show ( $data ['uid'], 'big' );
		$this->load->view('admin/user_auth/avatar', $data);
	}
	function doavatar(){  
		$action='on'.$_GET['a']; 
		$data = $this->avatarlib->$action(); 
		echo $data;
	}
}