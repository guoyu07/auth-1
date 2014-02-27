<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
 
/*
|--------------------------------------------------------------------------
| 安全设置
|
| 'phpass_hash_portable' = 密码倾销和出口到另一台服务器。如果设置为FALSE，那么你将无法使用该数据库在另一台服务器上。
| 'phpass_hash_strength' = 密码哈希的强度
|--------------------------------------------------------------------------
*/
$config['phpass_hash_portable'] = TRUE;
$config['phpass_hash_strength'] = 8;

/*
|--------------------------------------------------------------------------
| 注册设置
|
| 'allow_registration' = 注册或不启用
| 'captcha_registration' = 注册使用CAPTCHA
| 'email_activation' = 要求用户激活自己的帐户使用电子邮件注册后。
| 'email_activation_expire' = 用户谁没有激活他们的帐户前的时间从数据库中删除。默认为48小时 (60*60*24*2).
| 'email_account_details' = 发送电子邮件帐户的详细资料登记后（仅当'email_activation'FALSE）。
| 'use_username' = 用户名需要与否。
|
| 'username_min_length' = 最小长度的用户的用户名。
| 'username_max_length' = 用户的用户名的最大长度。
| 'password_min_length' = 用户的密码的最小长度。
| 'password_max_length' = 用户的密码的最大长度。
|--------------------------------------------------------------------------
*/
$config['allow_registration'] = TRUE;
$config['captcha_registration'] = TRUE;
$config['email_activation'] = FALSE;
$config['email_activation_expire'] = 60*60*24*2;
$config['email_account_details'] = FALSE;
$config['use_username'] = TRUE;

$config['username_min_length'] = 4;
$config['username_max_length'] = 20;
$config['password_min_length'] = 4;
$config['password_max_length'] = 20;

/*
|--------------------------------------------------------------------------
|登录设置
|
| 'login_by_username' = 可以使用用户名登录。
| 'login_by_email' = 使用电子邮件，登录。
| 你必须设置至少一个以上2设置为TRUE。
| login_by_username'很有意义，只有当'use_username'为TRUE。
|
| 'login_record_ip' = 保存在用户登录数据库用户的IP地址。
| 'login_record_time' = 存在用户登录数据库的当前时间。
|
| 'login_count_attempts' = 失败登录尝试计数.
| 'login_max_attempts' = 将显示数前失败登录尝试验证码.
| 'login_attempt_expire' = 时间到生活的每一个尝试登录。默认为24小时 (60*60*24).
|--------------------------------------------------------------------------
*/
$config['login_by_username'] = TRUE;
$config['login_by_email'] = TRUE;
$config['login_record_ip'] = TRUE;
$config['login_record_time'] = TRUE;
$config['login_count_attempts'] = TRUE;
$config['login_max_attempts'] = 3;
$config['login_attempt_expire'] = 60*60*24;

/*
|--------------------------------------------------------------------------
| 自动登录设置
|
| 'autologin_cookie_name' = 自动登录cookie名称。
| 'autologin_cookie_life' = 自动登录cookie的生命之前过期。默认为2个月(60*60*24*31*2).
|--------------------------------------------------------------------------
*/
$config['autologin_cookie_name'] = 'autologinss';
$config['autologin_cookie_life'] = 60*60*24*31*2;

/*
|--------------------------------------------------------------------------
| 忘记密码设置
|
| 'forgot_password_expire' = 前的时间忘了密码钥匙变得无效。默认为15分钟 (60*15).
|--------------------------------------------------------------------------
*/
$config['forgot_password_expire'] = 60*15;

/*
|--------------------------------------------------------------------------
| Captcha
|
| You can set captcha that created by Auth library in here.
| 'captcha_path' = Directory where the catpcha will be created.
| 'captcha_fonts_path' = Font in this directory will be used when creating captcha.
| 'captcha_font_size' = Font size when writing text to captcha. Leave blank for random font size.
| 'captcha_grid' = Show grid in created captcha.
| 'captcha_expire' = Life time of created captcha before expired, default is 3 minutes (180 seconds).
| 'captcha_case_sensitive' = Captcha case sensitive or not.
|--------------------------------------------------------------------------
*/
$config['captcha_path'] = 'captcha/';
$config['captcha_fonts_path'] = $config['captcha_path'].'fonts';
$config['captcha_width'] = 320;
$config['captcha_height'] = 95;
$config['captcha_font_size'] = 32;
$config['captcha_grid'] = TRUE;
$config['captcha_expire'] = 180;
$config['captcha_case_sensitive'] = FALSE;

/*
|--------------------------------------------------------------------------
| reCAPTCHA
|
| 'use_recaptcha' = Use reCAPTCHA instead of common captcha
| You can get reCAPTCHA keys by registering at http://recaptcha.net
|--------------------------------------------------------------------------
*/
$config['use_recaptcha'] = FALSE;
$config['recaptcha_public_key'] = '';
$config['recaptcha_private_key'] = '';

/*
|--------------------------------------------------------------------------
| 数据库设置
|
| 'db_table_prefix' = 表前缀，将前置库所使用的每一个表名
| 
|--------------------------------------------------------------------------
*/
$config['user_auth_table_prefix'] = '';
$config['user_auth_users_table'] = 'users';
$config['user_auth_user_profile_table'] = 'users_profile';
$config['user_auth_user_temp_table'] = 'users_temp';
$config['user_auth_user_autologin'] = 'users_autologin';
$config['user_auth_roles_table'] = 'users_roles';
$config['user_auth_permissions_table'] = 'users_permissions';
$config['user_auth_login_attempts_table'] = 'users_login_attempts';

$config['user_auth_users_competence_table'] = 'users_competence';
$config['user_auth_users_permission_table'] = 'users_permission';
$config['user_auth_users_column_table'] = 'users_column';
$config['user_auth_users_stsyemlog_table'] = 'users_stsyemlog';
$config['user_auth_users_menus_table'] = 'users_menus';


$config['user_auth_deny_uri'] = 'auth/deny';
$config['user_auth_login_uri'] = 'auth/login';
$config['user_auth_banned_uri'] = 'auth/banned';
$config['user_auth_activate_uri'] = 'auth/activate';
$config['user_auth_reset_password_uri'] = 'auth/reset_password';
$config['user_auth_send_again_uri'] = 'auth/send_again';
$config['user_auth_forgot_password_uri'] = 'auth/forgot_password';
$config['user_auth_change_password_uri'] = 'auth/change_password';
$config['user_auth_change_email_uri'] = 'auth/change_email';
$config['user_auth_reset_email_uri'] = 'auth/reset_email';
$config['user_auth_unregister_uri'] = 'auth/unregister'; 






























 