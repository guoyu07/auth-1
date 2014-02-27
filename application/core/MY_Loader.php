<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class MY_Loader extends CI_Loader {
	public function __construct() {
		parent :: __construct();
		$this->_ci_model_paths[1] = (BASEPATH);
		$this->ci = & get_instance();
		$this->ci->load = $this;
		if($this->ci->router->fetch_class()!='install'){
			if (!file_exists("install.lock")) {
				$this->ci->load->helper('url');
				redirect('install');
			}
		}
		$this->_ci_cached_vars['ci'] = $this->ci;
		$this->_ci_cached_vars['format'] = $this->ci->input->get_post('format');
		$this->ci->load->model('loadmodel');
		$this->ci->load->helper('common');
		
		/***
		 $lang = $ci->uri->segment(1); 
		 if ($lang=='en' || $lang=='ch'){
		 	$langs['ch']='chinese';
		 	$langs['en']='english';
			$ci->config->set_item('language',$langs[$lang]);
			$ci->config->set_item('post_lang', '_'.$lang);
		}
		$this->helper('language');
		***/
	}
	public function set_vars($name, $data) {
		$this->_ci_cached_vars[$name] = $data;
	}
	public function template($view, $vars = array (), $return = FALSE) {
		return $this->_ci_load(array (
			'_ci_view' => $view,
			'_ci_vars' => $this->_ci_object_to_array($vars),
			'_ci_return' => $return,
			'_ci_template' => 1
		));
	}
	protected function _ci_load($_ci_data) {
		foreach (array (
				'_ci_view',
				'_ci_vars',
				'_ci_path',
				'_ci_return'
			) as $_ci_val) {
			$$_ci_val = (!isset ($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
		}
		$t__ci_view = $_ci_view;
		$_ci_view = APPLICATION . '/' . $_ci_view;
		$file_exists = FALSE;
		if ($_ci_path != '') {
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		} else {
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = (!stripos($_ci_view, '.php')) ? $_ci_view . '.php' : $_ci_view;
			foreach ($this->_ci_view_paths as $view_file => $cascade) {
				$_ci_path = $view_file . $_ci_file;
			}
		}
		if (!file_exists($_ci_path)) {
			$this->GetTpl($t__ci_view,isset($_ci_data['_ci_template']));

		}
		if (!file_exists($_ci_path)) {
			//show_error ( '无法加载所需的文件: ' . $_ci_file );
		}
		$_ci_CI = & get_instance();
		foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var) {
			if (!isset ($this-> $_ci_key)) {
				$this-> $_ci_key = & $_ci_CI-> $_ci_key;
			}
		}
		if (is_array($_ci_vars)) {
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
		}
		extract($this->_ci_cached_vars);
		@ ob_start();

		if (( bool ) @ ini_get('short_open_tag') === FALSE and config_item('rewrite_short_tags') == TRUE) {
			echo eval ('?>' . preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
		} else {
			include ($_ci_path);
		}
		if (ENVIRONMENT == 'development') {
			$this->output->enable_profiler(TRUE);
		}
		log_message('debug', 'File loaded: ' . $_ci_path);
		if (isset ($_GET['mkdir'])) {
			$rsegments = $this->uri->rsegments;
			$directory = '';
			foreach ($rsegments as $k => $v) {
				$directory .= '/' . $v;
			}
			$directory = str_replace("/content/get/", "", $directory);
			//$directory = str_replace("/content/index/", "content/index/", $directory);
			$directory = str_replace("/welcome/", "", $directory);
			if (strstr($directory, '/')) {
				$this->MakeDir($directory);
			}
			$dir_name = explode("/", $directory);
			if (isset ($dir_name['1']) && ($dir_name['1'] == $this->router->routes['default_controller'])) {
				$directory = $this->router->method;
			}
			$directory .= $this->config->item('url_suffix');
			$buffer = ob_get_contents();
			@ ob_end_clean();
			if (!$fp = @ fopen(FCPATH . $directory, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {

			}
			flock($fp, LOCK_EX);
			fwrite($fp, $buffer);
			flock($fp, LOCK_UN);
			fclose($fp);
			echo $buffer;
		}
		if ($_ci_return === TRUE) {
			$buffer = ob_get_contents();
			@ ob_end_clean();
			return $buffer;
		}

		if (ob_get_level() > $this->_ci_ob_level + 1) {
			ob_end_flush();
		} else {
			$_ci_CI->output->append_output(ob_get_contents());
			@ ob_end_clean();
		}
	}
	function GetTpl($_ci_data, $sys = false) {
		$_ci_view = $_ci_data;
		$file_exists = FALSE;
		$_ci_path = '';
		$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
		$_ci_file = ($_ci_ext == '') ? $_ci_view . '.php' : $_ci_view;
		foreach ($this->_ci_view_paths as $view_file => $cascade) {
			if (file_exists($view_file . $_ci_file)) {
				$_ci_path = $view_file . $_ci_file;
				$file_exists = TRUE;
				break;
			}
			if (!$cascade) {
				break;
			}
		}
		if (!$file_exists && !file_exists($_ci_path)) {
			$this->library('templatelib');
			$t = new Templatelib();
			$t->CompiledFolder = APPLICATION . '/';
			$t->Template($_ci_view);
		}
		return $_ci_file;
	}
	function MakeDir($dir_name, $mode = 0777) {
		$dir_name = str_replace("\\", "/", $dir_name);
		$dir_name = preg_replace("#(/" . "/+)#", "/", $dir_name);
		/**
		 * 干掉最后/
		 */

		$dir_name = preg_replace("~/[^/]+?$~i", "", $dir_name);
		if (is_dir($dir_name) !== false) {
			return true;
		}
		$dir_name = explode("/", $dir_name);
		$dirs = '';
		foreach ($dir_name as $key => $dir) {
			if (trim($dir) != '') {
				$dirs .= $dir . "/";
				if (is_dir($dirs) == false && @ mkdir($dirs, $mode) === false) {
					return false;
				}
			}
		}
		return true;
	} 

}