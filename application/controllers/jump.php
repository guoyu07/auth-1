<?php
if (!defined('BASEPATH')){
	exit ('No direct script access allowed');
}
class Jump extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->helper('url');
		$this->load->helper('common');
	}
	function index() {
		$url=$this->input->get_post('url');
		redirect(base64url_decode($url));
	}
	function download(){
		$url=$this->input->get_post('url');
		redirect(base64url_decode($url));
	}
}