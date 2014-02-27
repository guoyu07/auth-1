<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class MY_Router extends CI_Router {
	public function __construct() {
		parent :: __construct();
		if ((isset ($_GET['app']) && $_GET['app'] == 'mobile') || ($this->is_mobile())) {
			$this->directory = 'mobile/';
		}
	}
	
	function is_mobile() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$mobile_agents = Array (
			"240x320",
			"acer",
			"acoon",
			"acs-",
			"abacho",
			"ahong",
			"airness",
			"alcatel",
			"amoi",
			"android",
			"anywhereyougo.com",
			"applewebkit/525",
			"applewebkit/532",
			"asus",
			"audio",
			"au-mic",
			"avantogo",
			"becker",
			"benq",
			"bilbo",
			"bird",
			"blackberry",
			"blazer",
			"bleu",
			"cdm-",
			"compal",
			"coolpad",
			"danger",
			"dbtel",
			"dopod",
			"elaine",
			"eric",
			"etouch",
			"fly ",
			"fly_",
			"fly-",
			"go.web",
			"goodaccess",
			"gradiente",
			"grundig",
			"haier",
			"hedy",
			"hitachi",
			"htc",
			"huawei",
			"hutchison",
			"inno",
			"ipad",
			"ipaq",
			"ipod",
			"jbrowser",
			"kddi",
			"kgt",
			"kwc",
			"lenovo",
			"lg ",
			"lg2",
			"lg3",
			"lg4",
			"lg5",
			"lg7",
			"lg8",
			"lg9",
			"lg-",
			"lge-",
			"lge9",
			"longcos",
			"maemo",
			"mercator",
			"meridian",
			"micromax",
			"midp",
			"mini",
			"mitsu",
			"mmm",
			"mmp",
			"mobi",
			"mot-",
			"moto",
			"nec-",
			"netfront",
			"newgen",
			"nexian",
			"nf-browser",
			"nintendo",
			"nitro",
			"nokia",
			"nook",
			"novarra",
			"obigo",
			"palm",
			"panasonic",
			"pantech",
			"philips",
			"phone",
			"pg-",
			"playstation",
			"pocket",
			"pt-",
			"qc-",
			"qtek",
			"rover",
			"sagem",
			"sama",
			"samu",
			"sanyo",
			"samsung",
			"sch-",
			"scooter",
			"sec-",
			"sendo",
			"sgh-",
			"sharp",
			"siemens",
			"sie-",
			"softbank",
			"sony",
			"spice",
			"sprint",
			"spv",
			"symbian",
			"tablet",
			"talkabout",
			"tcl-",
			"teleca",
			"telit",
			"tianyu",
			"tim-",
			"toshiba",
			"tsm",
			"up.browser",
			"utec",
			"utstar",
			"verykool",
			"virgin",
			"vk-",
			"voda",
			"voxtel",
			"vx",
			"wap",
			"wellco",
			"wig browser",
			"wii",
			"windows ce",
			"wireless",
			"xda",
			"xde",
			"zte"
		);
		$is_mobile = false;
		foreach ($mobile_agents as $device) {
			if (stristr($user_agent, $device)) {
				$is_mobile = true;
				break;
			}
		}
		return $is_mobile;
	}
	function set_directory($dir) {
		$this->directory =  $dir . '/';
	}
	function _validate_request($segments) {
		if (count($segments) == 0) {
			return $segments;
		} 
		
		if($this->fetch_directory()==$segments[0].'/'){
			$this->set_directory('');
		}
		
		if (file_exists(APPPATH . 'controllers/' . $this->fetch_directory() . $segments[0] . '.php')) {
			return $segments;
		}
		if (is_dir(APPPATH . 'controllers/' . $this->fetch_directory() . $segments[0])) {
			$temp = array (
				'dir' => '',
				'number' => 0,
				'path' => ''
			);
			$temp['number'] = count($segments) - 1;

			for ($i = 0; $i <= $temp['number']; $i++) {
				$temp['path'] .= $segments[$i] . '/';

				if (is_dir(APPPATH . 'controllers/' . $temp['path'])) {
					$temp['dir'][] = str_replace(array (
						'/',
						'.'
					), '', $segments[$i]);
				}
			}
			$this->set_directory(implode('/', $temp['dir']));
			$segments = array_diff($segments, $temp['dir']);
			$segments = array_values($segments);
			unset ($temp);

			if (count($segments) > 0) {
				if (!file_exists(APPPATH . 'controllers/' . $this->fetch_directory() . $segments[0] . '.php')) {
					if (!empty ($this->routes['404_override'])) {
						$x = explode('/', $this->routes['404_override']);
						$this->set_directory('');
						$this->set_class($x[0]);
						$this->set_method(isset ($x[1]) ? $x[1] : 'index');
						return $x;
					} else {
						show_404($this->fetch_directory() . $segments[0]);
					}
				}
			} else {
				if (strpos($this->default_controller, '/') !== FALSE) {
					$x = explode('/', $this->default_controller);
					$this->set_class($x[0]);
					$this->set_method($x[1]);
				} else {
					$this->set_class($this->default_controller);
					$this->set_method('index');
				}
				if (!file_exists(APPPATH . 'controllers/' . $this->fetch_directory() . $this->default_controller . '.php')) {
					$this->directory = '';
					return array ();
				}

			}
			return $segments;
		}
		if (!empty ($this->routes['404_override'])) {
			$x = explode('/', $this->routes['404_override']);
			$this->set_class($x[0]);
			$this->set_method(isset ($x[1]) ? $x[1] : 'index');
			return $x;
		}
		show_404($segments[0]);
	}
}