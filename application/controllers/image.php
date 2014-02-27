<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Image extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('image_moo');
	}
	function setimage($d) {
		return str_ireplace(base_url() . 'attachment/', FCPATH . 'attachment/', $d);

	}
	function get() {
		$source_image = base64_decode(($this->input->get_post('url')));
		$source_image= trim($source_image);
		//$source_image='http://www.baidu.com/img/bdlogo.gif';
		$w=$this->input->get_post('width')?$this->input->get_post('width'):100;
		$h=$this->input->get_post('height')?$this->input->get_post('height'):100;
		$r=$this->input->get_post('r')?$this->input->get_post('r'):1;
		@file_get_contents($source_image)?'':($source_image=(build_url('images').APPLICATION.'.jpg'));
		$this->image_moo->load($source_image)->resize_crop($w, $h)->round($r)->save_dynamic();
		print $this->image_moo->display_errors();
	}

}