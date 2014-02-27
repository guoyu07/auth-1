<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
 
class User_Autologin extends CI_Model {
	private $table_name = 'users_autologin';
	private $users_table_name = 'users';
	function __construct() {
		parent :: __construct();
		$ci = & get_instance();
		$this->table_name = $ci->config->item('db_table_prefix', 'user_auth') . $this->table_name;
		$this->users_table_name = $ci->config->item('db_table_prefix', 'user_auth') . $this->users_table_name;
	}
	function get($user_id, $key) {
		$this->db->select($this->users_table_name . '.id');
		$this->db->select($this->users_table_name . '.username');
		$this->db->from($this->users_table_name);
		$this->db->join($this->table_name, $this->table_name . '.user_id = ' . $this->users_table_name . '.id');
		$this->db->where($this->table_name . '.user_id', $user_id);
		$this->db->where($this->table_name . '.key_id', $key);
		$query = $this->db->get();
		if ($query->num_rows() == 1){
			return $query->row();
		}
		return NULL;
	}
	function set($user_id, $key) {
		return $this->db->insert($this->table_name, array (
			'user_id' => $user_id,
			'key_id' => $key,
			'user_agent' => substr($this->input->user_agent(), 0, 149),
			'last_ip' => $this->input->ip_address(),
		));
	}
	function delete($user_id, $key) {
		$this->db->where('user_id', $user_id);
		$this->db->where('key_id', $key);
		$this->db->delete($this->table_name);
	}
	function clear($user_id) {
		$this->db->where('user_id', $user_id);
		$this->db->delete($this->table_name);
	}

	function purge($user_id) {
		$this->db->where('user_id', $user_id);
		$this->db->where('user_agent', substr($this->input->user_agent(), 0, 149));
		$this->db->where('last_ip', $this->input->ip_address());
		$this->db->delete($this->table_name);
	}
} 