<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Lists extends CI_Controller {
	var $ucdata = array ();
	function __construct() {
		parent :: __construct();
		$this->load->model('contentmodel');
	}
	public function index($catid = NULL,$p=0) {
		return $this->contentmodel->setcontent($catid,$p);
	}
	 
}