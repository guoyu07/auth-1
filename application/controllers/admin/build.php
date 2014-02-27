<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Build extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->load->library('zip');
	}
	function index() {
		$path = './index.php';
		$this->zip->read_file($path, TRUE); 
		$path = './.htaccess';
		$this->zip->read_file($path, TRUE); 
		$path = './system/';
		$this->zip->read_dir($path);
		$path = './assets/' . APPLICATION . '/';
		$this->zip->read_dir($path);
		$this->zip->read_dir($path);
		$path = './assets/themes/';
		$this->zip->read_dir($path);
		$path = './application/';
		$this->zip->read_dir($path);
		$this->zip->archive(APPLICATION. date('Y-m-d',time()).'.zip'); 
	}

}