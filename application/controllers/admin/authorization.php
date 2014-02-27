<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Authorization extends CI_Controller {
	function __construct() {
		parent::__construct ();
	}
	function index() {
		$data = '';
		$this->load->view ( 'admin/user/authaction', $data );
	}
	function deny() {
		$data = '';
		$this->load->view ( 'admin/user/deny', $data );
	}

} 