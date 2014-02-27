<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Authorization extends CI_Controller {
	function __construct() {
		parent :: __construct();
	}
	function error_404(){
		$data=array(); 
		$data['heading'] = "404 Page Not Found";
		$data['message'] = "The page you requested was not found.";
		$data['webmasteremail'] = $this->config->item ( 'webmasteremail');
		$this->load->view ( 'admin/user_auth/error_404', $data );
	}
	function deny(){
		$this->error_404();
	}
}