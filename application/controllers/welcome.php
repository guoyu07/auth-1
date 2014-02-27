<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Welcome extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->helper('url');
		$this->load->helper('common');
		$this->load->model('contentmodel');
	}
	public function index() {
		$settings = $this->loadmodel->getsettings();
		$this->contentmodel->setcontent($settings['defaultpage']['value']);
	}
}