<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Users extends CI_Model {
	private $table_name = 'users';
	private $profile_table_name = 'users_profile'; 
	private $roles_table = 'users_roles';
	function __construct() {
		parent :: __construct();
		$ci = & get_instance();
		$this->table_name = $ci->config->item('db_table_prefix', 'tank_auth') . $this->table_name;
		$this->profile_table_name = $ci->config->item('db_table_prefix', 'tank_auth') . $this->profile_table_name;
	}

	function get_all($offset = 0, $row_count = 0) {
		$users_table = $this->table_name;
		$roles_table = $this->roles_table;
		if ($offset >= 0 AND $row_count > 0) {
			$this->db->select("$users_table.*", FALSE);
			$this->db->select("$roles_table.name AS role_name", FALSE);
			$this->db->join($roles_table, "$roles_table.id = $users_table.role_id");
			$this->db->order_by("$users_table.id", "ASC");
			$query = $this->db->get($this->table_name, $row_count, $offset);
		} else {
			$query = $this->db->get($this->table_name);
		}
		return $query;
	}
	function get_by_id($user_id) {
		$this->db->where('id', $user_id);
		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1){
			return $query->row_array();
		}
		return NULL;
	}
	function get_user_by_id($user_id, $activated) {
		$this->db->where('id', $user_id);
		$this->db->where('activated', $activated ? 1 : 0);
		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1){
			return $query->row_array();
		}
		return NULL;
	}
	function get_by_field($user_id = null) {
		$this->db->where('id', $user_id);
		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1) {
			return $query->row_array();
		}
		$fieldArray = $query->list_fields();
		$fields = array ();
		foreach ($fieldArray as $fieldName) {
			$fields[$fieldName] = '';
		}
		return $fields;
	}
	function get_user_by_login($login) {
		$this->db->where('LOWER(username)=', strtolower($login));
		$this->db->or_where('LOWER(email)=', strtolower($login));
		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1){
			return $query->row();
		}
		return NULL;
	}
	function get_user_by_username($username) {
		$this->db->where('LOWER(username)=', strtolower($username));

		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1)
			return $query->row();
		return NULL;
	}

	function get_user_by_email($email) {
		$this->db->where('LOWER(email)=', strtolower($email));
		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1)
			return $query->row();
		return NULL;
	}


	function is_username_available($username) {
		$this->db->select('1', FALSE);
		$this->db->where('LOWER(username)=', strtolower($username));
		$query = $this->db->get($this->table_name);
		return $query->num_rows() == 0;
	}


	function is_email_available($email) {
		$this->db->select('1', FALSE);
		$this->db->where('LOWER(email)=', strtolower($email));
		$this->db->or_where('LOWER(new_email)=', strtolower($email));
		$query = $this->db->get($this->table_name);
		return $query->num_rows() == 0;
	}
	function create_user($data, $activated = TRUE) {
		$data['created'] = date('Y-m-d H:i:s');
		$data['activated'] = $activated ? 1 : 0;
		$data['id'] = $this->guid();
		if ($this->db->insert($this->table_name, $data)) {
			$user_id =$data['id'] ;
			if ($activated){
				$this->create_profile($user_id);
			}
			$ndb['id']=$data['id'] ;
			return $ndb;
		}
		return NULL;
	}
	function activate_user($user_id, $activation_key, $activate_by_email) {
		$this->db->select('1', FALSE);
		$this->db->where('id', $user_id);
		if ($activate_by_email) {
			$this->db->where('new_email_key', $activation_key);
		} else {
			$this->db->where('new_password_key', $activation_key);
		}
		$this->db->where('activated', 0);
		$query = $this->db->get($this->table_name);
		if ($query->num_rows() == 1) {
			$this->db->set('activated', 1);
			$this->db->set('new_email_key', NULL);
			$this->db->where('id', $user_id);
			$this->db->update($this->table_name);
			$this->create_profile($user_id);
			return TRUE;
		}
		return FALSE;
	}

	function purge_na($expire_period = 172800) {
		$this->db->where('activated', 0);
		$this->db->where('UNIX_TIMESTAMP(created) <', time() - $expire_period);
		$this->db->delete($this->table_name);
	}
	function delete_user($user_id) {
		$this->db->where('id', $user_id);
		$this->db->delete($this->table_name);
		if ($this->db->affected_rows() > 0) {
			$this->delete_profile($user_id);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 用户设置新的密码钥匙。
	 * 此键可以用来进行身份验证时，用户的密码重置。
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	function set_password_key($user_id, $new_pass_key) {
		$this->db->set('new_password_key', $new_pass_key);
		$this->db->set('new_password_requested', date('Y-m-d H:i:s'));
		$this->db->where('id', $user_id);

		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * 检查密码的密钥是有效的和用户进行身份验证。
	 * @param	int
	 * @param	string
	 * @param	int
	 * @return	void
	 */
	function can_reset_password($user_id, $new_pass_key, $expire_period = 900) {
		$this->db->select('1', FALSE);
		$this->db->where('id', $user_id);
		$this->db->where('new_password_key', $new_pass_key);
		$this->db->where('UNIX_TIMESTAMP(new_password_requested) >', time() - $expire_period);
		$query = $this->db->get($this->table_name);
		return $query->num_rows() == 1;
	}

	/**
	 * 更改用户密码，如果密码的密钥是有效的和用户进行身份验证。
	 * @param	int
	 * @param	string
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	function reset_password($user_id, $new_pass, $new_pass_key, $expire_period = 900) {
		$this->db->set('password', $new_pass);
		$this->db->set('new_password_key', NULL);
		$this->db->set('new_password_requested', NULL);
		$this->db->where('id', $user_id);
		$this->db->where('new_password_key', $new_pass_key);
		$this->db->where('UNIX_TIMESTAMP(new_password_requested) >=', time() - $expire_period);
		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * 更改用户密码
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	function change_password($user_id, $new_pass) {
		$this->db->set('password', $new_pass);
		$this->db->where('id', $user_id);
		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * 设置新的电子邮件用户（可能会被激活或不）。
	 * 它被激活之前，不能用于新的电子邮件登录或通知。
	 * @param	int
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function set_new_email($user_id, $new_email, $new_email_key, $activated) {
		$this->db->set($activated ? 'new_email' : 'email', $new_email);
		$this->db->set('new_email_key', $new_email_key);
		$this->db->where('id', $user_id);
		$this->db->where('activated', $activated ? 1 : 0);

		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * 激活新的电子邮件（用新的代替旧的电子邮件），如果激活密钥是有效的。
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	function activate_new_email($user_id, $new_email_key) {
		$this->db->set('email', 'new_email', FALSE);
		$this->db->set('new_email', NULL);
		$this->db->set('new_email_key', NULL);
		$this->db->where('id', $user_id);
		$this->db->where('new_email_key', $new_email_key);
		$this->db->update($this->table_name);
		return $this->db->affected_rows() > 0;
	}
	function update_login_info($user_id, $record_ip, $record_time) {
		$this->db->set('new_password_key', NULL);
		$this->db->set('new_password_requested', NULL);
		if ($record_ip)
			$this->db->set('last_ip', $this->input->ip_address());
		if ($record_time){
			$this->db->set('last_login', date('Y-m-d H:i:s'));
		}
		$this->db->where('id', $user_id);
		$this->db->update($this->table_name);
	}

	/**
	 * 禁止用户
	 * @param	int
	 * @param	string
	 * @return	void
	 */
	function ban_user($user_id, $reason = NULL) {
		$this->db->where('id', $user_id);
		$this->db->update($this->table_name, array (
			'banned' => 1,
			'ban_reason' => $reason,
			
		));
	}

	/**
	 * 取消禁止用户
	 * @param	int
	 * @return	void
	 */
	function unban_user($user_id) {
		$this->db->where('id', $user_id);
		$this->db->update($this->table_name, array (
			'banned' => 0,
			'ban_reason' => NULL,
			
		));
	}

	private function create_profile($user_id) {
		$this->db->set('id', $user_id);
		return $this->db->insert($this->profile_table_name);
	}

	/**
	 * 删除的用户配置文件
	 * @param	int
	 * @return	void
	 */
	private function delete_profile($user_id) {
		$this->db->where('id', $user_id);
		$this->db->delete($this->profile_table_name);
	}
	function update($data, $id) {
		$this->db->where('id', $id);
		if ($this->db->update($this->table_name, $data)) {
			return $id;
		}
		return '0';
	}
	function guid($h = '') {
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
		$hyphen = chr(45); // "-"
		//$uuid = chr(123) // "{"
		$uuid = substr($charid, 0, 8) . $hyphen .
		substr($charid, 8, 4) . $hyphen .
		substr($charid, 12, 4) . $hyphen .
		substr($charid, 16, 4) . $hyphen .
		substr($charid, 20, 12);
		//chr(125); // "}"
		return $uuid;
	}
}
 