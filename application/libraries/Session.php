<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class CI_Session {

	var $flash_key = 'flash';
	
	 function __construct() {
		$this->ci = & get_instance();
		$this->ci->load->config('user_auth', TRUE);
		ini_set('session.gc_maxlifetime', 7200);
		$this->_sess_run();
	}
	function all_userdata() {
		return $_SESSION;
	}
	
	function regenerate_id() {
		$old_session_id = session_id();
		$old_session_data = $_SESSION;
		session_regenerate_id();
		$new_session_id = session_id(); 
		session_id($old_session_id);
		session_destroy(); 
		session_id($new_session_id);
		session_start(); 
		$_SESSION = $old_session_data;
 
		$_SESSION['regenerated'] = time(); 
		session_write_close();
	}
 
	function sess_destroy() {
		unset ($_SESSION);
		if (isset ($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time() - 42000, '/');
		}
		session_destroy();
	}
 
	function userdata($item) {
		if ($item == 'session_id') {
			return session_id();
		} else {
			return (!isset ($_SESSION[$item])) ? false : $_SESSION[$item];
		}
	}
 
	function set_userdata($newdata = array (), $newval = '') {
		if (is_string($newdata)) {
			$newdata = array (
				$newdata => $newval
			);
		}
		if (count($newdata) > 0) {
			foreach ($newdata as $key => $val) {
				$_SESSION[$key] = $val;
			}
			//session_set_cookie_params($this->session_id_ttl);;
			//session_cache_expire($this->session_id_ttl);
		}
		
	}
 
	function unset_userdata($newdata = array ()) {
		if (is_string($newdata)) {
			$newdata = array (
				$newdata => ''
			);
		}

		if (count($newdata) > 0) {
			foreach ($newdata as $key => $val) {
				unset ($_SESSION[$key]);
			}
		}
	}

	function _sess_run() {
		@ session_start();
		$session_id_ttl = $this->ci->config->item('login_attempt_expire', 'user_auth');
		if (is_numeric($session_id_ttl)) {
			if ($session_id_ttl > 0) {
				$this->session_id_ttl = $this->ci->config->item('login_attempt_expire', 'user_auth');
			} else {
				$this->session_id_ttl = (60 * 60 * 24 * 365 * 2);
			}
		}

		if ($this->_session_id_expired()) {
			$this->regenerate_id();
		}
		$this->_flashdata_sweep();
		$this->_flashdata_mark();
	}

	function _session_id_expired() {
		if (!isset ($_SESSION['regenerated'])) {
			$_SESSION['regenerated'] = time();
			return false;
		}
		$expiry_time = time() - $this->session_id_ttl;
		if ($_SESSION['regenerated'] <= $expiry_time) {
			return true;
		}
		return false;
	}
 
	function set_flashdata($newdata = array (), $newval = '') {
		if (is_string($newdata)) {
			$newdata = array (
				$newdata => $newval
			);
		}
		if (count($newdata) > 0) {
			$flashdata = array ();

			foreach ($newdata as $key => $val) {
				$flashdata[$this->flash_key . ':new:' . $key] = $val;
			}
			$this->set_userdata($flashdata);
		}
	} 
	function keep_flashdata($key) {
		$old_flash_key = $this->flash_key . ':old:' . $key;
		$value = $this->userdata($old_flash_key);

		$new_flash_key = $this->flash_key . ':new:' . $key;
		$this->set_userdata($new_flash_key, $value);
	}
 
	function flashdata($key) {
		$flash_key = $this->flash_key . ':old:' . $key;
		return $this->userdata($flash_key);
	}
 
	function _flashdata_mark() {
		foreach ($_SESSION as $name => $value) {
			$parts = explode(':new:', $name);
			if (is_array($parts) && count($parts) == 2) {
				$new_name = $this->flash_key . ':old:' . $parts[1];
				$this->set_userdata($new_name, $value);
				$this->unset_userdata($name);
			}
		}
	}

	/**
	* PRIVATE: Internal method - removes "flash" session marked as 'old'
	*/
	function _flashdata_sweep() {
		foreach ($_SESSION as $name => $value) {
			$parts = explode(':old:', $name);
			if (is_array($parts) && count($parts) == 2 && $parts[0] == $this->flash_key) {
				$this->unset_userdata($name);
			}
		}
	}
} 