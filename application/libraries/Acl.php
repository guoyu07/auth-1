<?php
class Acl {
	public function __construct() {
		$this->ci = & get_instance();
		$this->competencetype = array (
			1 => array (
				'name' => '前台',
				'value' => '1',
				'extra' => '',
				'allow' => ''
			),
			2 => array (
				'name' => '后台',
				'value' => '2',
				'extra' => '',
				'allow' => 'admin'
			)
		);
		$this->allow['stsyemlog']['value'] = 'stsyemlog';

	}
	function getacl() {
		$action = $this->ci->router->directory . $this->ci->router->fetch_class() . '/' . $this->ci->router->fetch_method();
		$user = $this->ci->user_auth->ucdata;
		if (!$user) {
			return FALSE;
		}
		$this->ci->loadmodel->_initialize($this->ci->config->item('user_auth_users_competence_table','user_auth'));
		$roles = $this->ci->loadmodel->get_by_id($user['roleid']);
		if (!$roles) {
			return FALSE;
		}

		$acl = explode(',', $roles['acl']);
		if (!$acl) {
			return FALSE;
		}
		$this->ci->loadmodel->_initialize($this->ci->config->item('user_auth_users_permission_table','user_auth'));
		$menuurl = $url = $menus = array ();
		$permission = $this->ci->loadmodel->where_in('id', $acl);
		foreach ($permission as $k => $v) {
			$uri = ($this->competencetype[$v['pid']]['allow'] ? $this->competencetype[$v['pid']]['allow'] . '/' : '');
			$menuurl[] = $urls = $uri . $v['url'];
			$v['url'] = $this->ci->config->site_url($urls);
			$menus[$urls] = $v;
		}
		//$this->addlog($menus[$action], $user, $action);
		//return 1;
		if (in_array($action, $menuurl)) {
			$this->addlog($menus[$action], $user, $action);
			return $action;
		}
		return FALSE;

	}
	function ip_address() {
		if (!empty ($_SERVER["HTTP_CLIENT_IP"])) {
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif (!empty ($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif (!empty ($_SERVER["REMOTE_ADDR"])) {
			$cip = $_SERVER["REMOTE_ADDR"];
		} else {
			$cip = "无法获取！";
		}
		return $cip;
	}
	function addlog($data, $user, $action) {
		foreach ($this->allow as $k => $v) {
			if (strpos($action, $v['value'])) {
				return FALSE;
			}
		}
		$this->ci->loadmodel->_initialize($this->ci->config->item('user_auth_users_stsyemlog_table','user_auth'));
		$this->ci->load->library('user_agent');
		$db['subject'] = $data['subject'];
		$db['url'] = $data['url'];
		$db['uid'] = $user['id'];
		$db['username'] = $user['username'];
		$db['id'] = $this->ci->loadmodel->uuid();
		$db['ip'] = $this->ip_address();
		$db['agent_string'] = $this->ci->agent->agent_string();
		$db['browser'] = $this->ci->agent->browser();
		$db['browserversion'] = $this->ci->agent->version();
		$db['os'] = $this->ci->agent->platform();
		$db['robot'] = $this->ci->agent->robot();
		$db['referrer'] = $this->ci->agent->referrer();
		$this->ci->loadmodel->add($db);
	}

}
?>