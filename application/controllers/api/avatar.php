<?php
class Avatar extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('avatarlib');
	}
	function show($uid = null) {
		$avatar = $this->avatarlib->avatar_show($uid, 'big', 1);
		header('Location: '. $avatar);
		exit;
	}
	function doavatar(){  
		$action='on'.$_GET['a']; 
		$data = $this->avatarlib->$action(); 
		echo $data;
	}
}