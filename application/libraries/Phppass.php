<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Phppass {
	function __construct() {
	}
	function HashPassword($password=''){
		return base64_encode($password);
	}
	function CheckPassword($password,$old_pass){
		return $old_pass == base64_encode($password);
	}
}


