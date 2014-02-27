<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Login_attempts extends CI_Model {
	private $table_name = 'users_login_attempts';
	function __construct() {
		parent :: __construct();
		$ci = & get_instance();
		$this->table_name = $ci->config->item('db_table_prefix', 'user_auth') . $this->table_name;
	}
	function get_attempts_num($ip_address, $login) {
		$this->db->select('1', FALSE);
		$this->db->where('ip_address', $ip_address);
		if (strlen($login) > 0) {
			$this->db->or_where('login', $login);
		}
		$qres = $this->db->get($this->table_name);
		return $qres->num_rows();
	}
	function increase_attempt($ip_address, $login) {
		$db['ip_address']=$ip_address;
		$db['login']=$login;
		$this->db->insert($this->table_name, $db);
	}

	function clear_attempts($ip_address, $login, $expire_period = 86400) {
		$wdb['ip_address']=$ip_address;
		$wdb['login']=$login;
		$this->db->where($wdb);
		$this->db->or_where('UNIX_TIMESTAMP(time) <', time() - $expire_period);
		$this->db->delete($this->table_name);
	}
}