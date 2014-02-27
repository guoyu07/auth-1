<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
$route['default_controller'] = "welcome";
$route['404_override'] = 'auth/error_404';

