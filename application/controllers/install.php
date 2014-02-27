<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Install extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->helper('url');
	}
	function _display(){
		if (file_exists("install.lock")) {
			$this->lock();
			return 0;
		}
	}
	function lock() {
		$data = '';
		$this->load->view('install/lock', $data);
	}
	function index() {
		$data = '';
		$this->load->view('install/welcome', $data);
	}
	function discover() {
		$data = '';
		$this->load->view('install/discover', $data);
	}
	function database() {
		$data = '';
		$this->load->view('install/database', $data);
	}
	function dodatabase() {
		print_R($_POST);
		$this->database_decide();
	}
	function database_decide() {
		$data = '';
		$this->load->view('install/database_1', $data);
	}
	function initdata() {
		$data = '';
		$this->load->view('install/initdata', $data);
	}
	function complete() {
		$data = '';
		$this->load->view('install/complete', $data);
	}
}