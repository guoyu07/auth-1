<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Help extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
	}
	function templates($templates ='welcome') {
		$data = '';
		$this->load->view('admin/help/templates/' . $templates, $data);
	}
	function api($templates = 'welcome') {
		$data = '';
		$this->load->view('admin/help/api/' . $templates, $data);
	}
	function content($templates = 'welcome') {
		$data = '';
		$this->load->view('admin/help/content/' . $templates, $data);
	}

}