<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class User_auth_event {
	var $ci;
	function __construct() {
		$this->ci = & get_instance();
	}
	function user_activated($user_id) {
		$this->ci->load->model('user_auth/user_profile', 'user_profile');
		$this->ci->user_profile->create_profile($user_id);
	}
	function user_logged_in($user_id) {
	}
	function user_logging_out($user_id) {
	}
	function user_changed_password($user_id, $new_password) {
	}
	function user_canceling_account($user_id) {
		$this->ci->load->model('user_auth/user_profile', 'user_profile');
		$this->ci->user_profile->delete_profile($user_id);
	}
 	function checked_uri_permissions($user_id, & $allowed) {
	}
 	function got_permission_value($user_id, $key) {
	}
 	function got_permissions_value($user_id, $key) {
	}
	function sending_account_email($data, & $content) {
		$this->ci->load->helper('url');
		$content = sprintf($this->ci->lang->line('auth_account_content'), $this->ci->config->item('website_name'), $data['username'], $data['email'], $data['password'], site_url($this->ci->config->item('user_auth_login_uri')), $this->ci->config->item('website_name'));
	}
	function sending_activation_email($data, & $content) {
		$content = sprintf($this->ci->lang->line('auth_activate_content'), $this->ci->config->item('website_name'), $data['activate_url'], $this->ci->config->item('email_activation_expire') / 60 / 60, $data['username'], $data['email'], $data['password'], $this->ci->config->item('website_name'));
	}
	function sending_forgot_password_email($data, & $content) {
		$content = sprintf($this->ci->lang->line('auth_forgot_password_content'), $this->ci->config->item('website_name'), $data['user_auth_reset_password_uri'], $data['password'], $data['key'], $this->ci->config->item('webmaster_email'), $this->ci->config->item('website_name'));
	}
}
?>