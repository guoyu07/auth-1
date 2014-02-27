<?php

class Authcode extends CI_Controller { 
	function __construct() {
		parent::__construct ();
		$this->load->library ( 'authcheckcode' );
	} 
	/**
	 * 显示图片
	 *
	 */
	function show() {
		$this->authcheckcode->show ();
	} 
	/**
	 * ajax验证
	 *
	 */
	function check() {
		if ($this->authcheckcode->check ( $this->uri->segment ( 3 ) )) {
			echo '1';
		} else {
			echo '验证码不正确，请重新输入';
		}
	}
}
