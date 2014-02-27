<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Auth extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');
		$this->load->library('user_auth');
	}
	function index() {
		 $this->login();
	}

	function login() {
		if (!empty ($_GET['callback'])) {
			$this->session->set_userdata(array ('callback' => $_GET['callback']));
		}
		$callback = $this->session->userdata('callback');
		$url = $callback ? $callback : 'admin' . '?token=' . md5(time());
		if ($this->user_auth->is_logged_in()) { 
			$this->user_auth->redirect($url);
		}
		elseif ($this->user_auth->is_logged_in(FALSE)) { 
			$this->user_auth->redirect('user_auth/send_again');
		} else {
			$data['login_by_username'] = ($this->config->item('login_by_username', 'user_auth') AND $this->config->item('use_username', 'user_auth'));
			$data['login_by_email'] = $this->config->item('login_by_email', 'user_auth');
			$val=$this->form_validation;
			$val->set_rules('username', '账号', 'trim|required|xss_clean');
			$val->set_rules('password', '密码', 'trim|required|xss_clean');
			$val->set_rules('remember', '记住登录', 'integer');
			if ($this->config->item('login_count_attempts', 'user_auth') AND ($login = $this->input->post('username'))) {
				$login = $this->security->xss_clean($login);
			} else {
				$login = '';
			}
			$data['show_captcha'] = FALSE;
			$data['use_recaptcha'] = $this->config->item('use_recaptcha', 'user_auth');
			if ($this->user_auth->is_max_login_attempts_exceeded($login)) {
				$data['show_captcha'] = TRUE;
				$val->set_rules('imgcode', '验证码', 'trim|xss_clean|required|callback_check_captcha');
			}
			$data['errors'] = array ();
			
			if ($val->run()) {
				if ($this->user_auth->login($val->set_value('username'), $val->set_value('password'), $val->set_value('remember'), $data['login_by_username'], $data['login_by_email'])) { 
					$this->user_auth->redirect($url);
				} else {
					$errors = $this->user_auth->get_error_message();
					if (isset ($errors['banned'])) { 
						$this->_show_message($this->lang->line('auth_message_banned') . ' ' . $errors['banned']);

					}
					elseif (isset ($errors['not_activated'])) { 
						$this->user_auth->redirect('admin/user_auth/send_again');
					} else { 
						$error = null;
						foreach ($errors as $k => $v){
							$error =$error. $this->lang->line($v);
							$data['errors'][$k] = $this->lang->line($v);
						}
						$errors['msg']=$error;
						$this->user_auth->redirect('admin/auth', $errors);
							
					}
				}
			}
			$this->load->view('admin/user_auth/login', $data);
		}
	}
 
	function logout() {
		$this->user_auth->logout();
		$this->_show_message($this->lang->line('auth_message_logged_out'));
	}
 
	function register() {
		$val=$this->form_validation;
		if ($this->user_auth->is_logged_in()) { 
			redirect('');
		}
		elseif ($this->user_auth->is_logged_in(FALSE)) {  
			redirect('auth/send_again');
		}elseif (!$this->config->item('allow_registration', 'user_auth')) {  
			$this->_show_message($this->lang->line('auth_message_registration_disabled'));

		} else {
			$use_username = $this->config->item('use_username', 'user_auth');
			if ($use_username) {
				$this->form_validation->set_rules('username', '用户名', 'trim|required|xss_clean|min_length[' . $this->config->item('username_min_length', 'user_auth') . ']|max_length[' . $this->config->item('username_max_length', 'user_auth') . ']|alpha_dash');
			}
			$val->set_rules('email', '邮箱', 'trim|required|xss_clean|valid_email');
			$val->set_rules('password', '密码', 'trim|required|xss_clean|min_length[' . $this->config->item('password_min_length', 'user_auth') . ']|max_length[' . $this->config->item('password_max_length', 'user_auth') . ']|alpha_dash');
			$val->set_rules('confirm_password', '确认密码', 'trim|required|xss_clean|matches[password]');

			$captcha_registration = $this->config->item('captcha_registration', 'user_auth');
			$use_recaptcha = $this->config->item('use_recaptcha', 'user_auth');
			if ($captcha_registration) {
				$data['show_captcha'] = TRUE;
				$val->set_rules('imgcode', '验证码', 'trim|xss_clean|required|callback_check_captcha');
			}
			$data['errors'] = array ();
			$email_activation = $this->config->item('email_activation', 'user_auth');
			if ($val->run()) { // 验证ok
				if (!is_null($data = $this->user_auth->create_user($use_username ? $val->set_value('username') : '', $val->set_value('email'), $val->set_value('password'), $email_activation))) { // 成功
					$data['site_name'] = $this->config->item('website_name', 'user_auth');
					if ($email_activation) { // “激活”发送电子邮件
						$data['activation_period'] = $this->config->item('email_activation_expire', 'user_auth') / 3600;
						$this->_send_email('activate', $data['email'], $data);
						unset ($data['password']); // 清除密码
						$this->_show_message($this->lang->line('auth_message_registration_completed_1'));
					} else {
						if ($this->config->item('email_account_details', 'user_auth')) { //发送“欢迎”电子邮件
							$this->_send_email('welcome', $data['email'], $data);
						}
						unset ($data['password']); //清除密码
						$this->_show_message($this->lang->line('auth_message_registration_completed_2') . ' ' . anchor('/auth/login/', 'Login'));
					}
				} else {
					$errors = $this->user_auth->get_error_message();
					foreach ($errors as $k => $v)
						$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$data['use_username'] = $use_username;
			$data['captcha_registration'] = $captcha_registration;
			$data['use_recaptcha'] = $use_recaptcha;
			$this->load->view('admin/user_auth/register', $data);
		}
	}

	/**
	 * Send activation email again, to the same or new email address
	 *
	 * @return void
	 */
	function send_again() {
		if (!$this->user_auth->is_logged_in(FALSE)) { // not logged in or activated
			redirect('/auth/login/');

		} else {
			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

			$data['errors'] = array ();

			if ($this->form_validation->run()) { // validation ok
				if (!is_null($data = $this->user_auth->change_email($this->form_validation->set_value('email')))) { // success

					$data['site_name'] = $this->config->item('website_name', 'user_auth');
					$data['activation_period'] = $this->config->item('email_activation_expire', 'user_auth') / 3600;

					$this->_send_email('activate', $data['email'], $data);

					$this->_show_message(sprintf($this->lang->line('auth_message_activation_email_sent'), $data['email']));

				} else {
					$errors = $this->user_auth->get_error_message();
					foreach ($errors as $k => $v)
						$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/send_again_form', $data);
		}
	}

	/**
	 * Activate user account.
	 * User is verified by user_id and authentication code in the URL.
	 * Can be called by clicking on link in mail.
	 *
	 * @return void
	 */
	function activate() {
		$user_id = $this->uri->segment(3);
		$new_email_key = $this->uri->segment(4);

		// Activate user
		if ($this->user_auth->activate_user($user_id, $new_email_key)) { // success
			$this->user_auth->logout();
			$this->_show_message($this->lang->line('auth_message_activation_completed') . ' ' . anchor('/auth/login/', 'Login'));

		} else { // fail
			$this->_show_message($this->lang->line('auth_message_activation_failed'));
		}
	}

 
	function forgot_password() {
		if ($this->user_auth->is_logged_in()) {
			redirect('');
		}
		elseif ($this->user_auth->is_logged_in(FALSE)) { 
			redirect('auth/send_again');
		} else {
			$val=$this->form_validation;
			$val->set_rules('login', '邮箱或账号', 'trim|required|xss_clean');
			$val->set_rules('imgcode', '验证码', 'trim|xss_clean|required|callback_check_captcha');
			$data['errors'] = array ();
			if ($val->run()) { 
				if (!is_null($data = $this->user_auth->forgot_password($val->set_value('login')))) {
					$data['site_name'] = $this->config->item('website_name', 'user_auth');
					$this->_send_email('forgot_password', $data['email'], $data);
					$this->_show_message($this->lang->line('auth_message_new_password_sent'));
				} else {
					$errors = $this->user_auth->get_error_message();
					foreach ($errors as $k => $v){
						$data['errors'][$k] = $this->lang->line($v);
					}
				}
			}
			$this->load->view('user_auth/forgot_password_form', $data);
		}
	}

 
	function reset_password($user_id=null,$new_pass_key=null) {
		$val=$this->form_validation;
		$val->set_rules('new_password', '新密码', 'trim|required|xss_clean|min_length[' . $this->config->item('password_min_length', 'user_auth') . ']|max_length[' . $this->config->item('password_max_length', 'user_auth') . ']|alpha_dash');
		$val->set_rules('confirm_new_password', '确认新密码', 'trim|required|xss_clean|matches[new_password]');
		$data['errors'] = array ();
		if ($val->run()) {
			$data = $this->user_auth->reset_password($user_id, $new_pass_key, $val->set_value('new_password'));
			if (!is_null($data)) { 
				$this->_send_email('reset_password', $data['email'], $data);
				$this->_show_message($this->lang->line('auth_message_new_password_activated') . ' ' . anchor('auth/login', '登录'));
			} else { 
				$this->_show_message($this->lang->line('auth_message_new_password_failed'));
			}
		} else {
			if ($this->config->item('email_activation', 'user_auth')) {
				$this->user_auth->activate_user($user_id, $new_pass_key, FALSE);
			}
			if (!$this->user_auth->can_reset_password($user_id, $new_pass_key)) {
				$this->_show_message($this->lang->line('auth_message_new_password_failed'));
			}
		}
		$this->load->view('user_auth/reset_password_form', $data);
	}

	/**
	 * Change user password
	 *
	 * @return void
	 */
	function change_password() {
		if (!$this->user_auth->is_logged_in()) { // not logged in or not activated
			redirect('/auth/login/');

		} else {
			$this->form_validation->set_rules('old_password', 'Old Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('new_password', 'New Password', 'trim|required|xss_clean|min_length[' . $this->config->item('password_min_length', 'user_auth') . ']|max_length[' . $this->config->item('password_max_length', 'user_auth') . ']|alpha_dash');
			$this->form_validation->set_rules('confirm_new_password', 'Confirm new Password', 'trim|required|xss_clean|matches[new_password]');

			$data['errors'] = array ();

			if ($this->form_validation->run()) { // validation ok
				if ($this->user_auth->change_password($this->form_validation->set_value('old_password'), $this->form_validation->set_value('new_password'))) { // success
					$this->_show_message($this->lang->line('auth_message_password_changed'));

				} else { // fail
					$errors = $this->user_auth->get_error_message();
					foreach ($errors as $k => $v)
						$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/change_password_form', $data);
		}
	}

	/**
	 * Change user email
	 *
	 * @return void
	 */
	function change_email() {
		if (!$this->user_auth->is_logged_in()) { // not logged in or not activated
			redirect('/auth/login/');

		} else {
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

			$data['errors'] = array ();

			if ($this->form_validation->run()) { // validation ok
				if (!is_null($data = $this->user_auth->set_new_email($this->form_validation->set_value('email'), $this->form_validation->set_value('password')))) { // success

					$data['site_name'] = $this->config->item('website_name', 'user_auth');

					// Send email with new email address and its activation link
					$this->_send_email('change_email', $data['new_email'], $data);

					$this->_show_message(sprintf($this->lang->line('auth_message_new_email_sent'), $data['new_email']));

				} else {
					$errors = $this->user_auth->get_error_message();
					foreach ($errors as $k => $v)
						$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/change_email_form', $data);
		}
	}

	/**
	 * Replace user email with a new one.
	 * User is verified by user_id and authentication code in the URL.
	 * Can be called by clicking on link in mail.
	 *
	 * @return void
	 */
	function reset_email() {
		$user_id = $this->uri->segment(3);
		$new_email_key = $this->uri->segment(4);

		// Reset email
		if ($this->user_auth->activate_new_email($user_id, $new_email_key)) { // success
			$this->user_auth->logout();
			$this->_show_message($this->lang->line('auth_message_new_email_activated') . ' ' . anchor('/auth/login/', 'Login'));

		} else { // fail
			$this->_show_message($this->lang->line('auth_message_new_email_failed'));
		}
	}

	/**
	 * Delete user from the site (only when user is logged in)
	 *
	 * @return void
	 */
	function unregister() {
		if (!$this->user_auth->is_logged_in()) { // not logged in or not activated
			redirect('/auth/login/');

		} else {
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
			$data['errors'] = array ();
			if ($this->form_validation->run()) { // validation ok
				if ($this->user_auth->delete_user($this->form_validation->set_value('password'))) { // success
					$this->_show_message($this->lang->line('auth_message_unregistered'));

				} else { // fail
					$errors = $this->user_auth->get_error_message();
					foreach ($errors as $k => $v)
						$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$this->load->view('auth/unregister_form', $data);
		}
	}
	function _show_message($message) {
		$this->session->set_flashdata('message', $message);
		redirect('auth');
	}

	function _send_email($type, $email, & $data) {
		$this->load->library('email');
		$this->load->config('email', TRUE);
		$comfig=$this->config->item('email');
		$data['website_name']=$this->config->item('website_name');
		$data['website_email']=$data['webmaster_email']=$comfig['smtp_user'];
		$this->email->from($this->config->item('smtp_user', 'email'),$data['website_name']);
		$this->email->to($email);
		$this->email->subject($this->lang->line('auth_subject_' . $type));
		$this->email->message($this->load->view('user_auth/email/' . $type . '-html', $data, TRUE));
		$this->email->set_alt_message($this->load->view('user_auth/email/' . $type . '-txt', $data, TRUE));
		return $this->email->send();
	}
	function check_captcha($v) {  
		$this->load->library ( 'authcheckcode' );
		if ($this->authcheckcode->check ( $v )) {
			return TRUE;
		} else {
			$this->form_validation->set_message('check_captcha', '验证码错误');
			return FALSE;
		}
	}
} 