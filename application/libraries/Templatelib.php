<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

function Template($h) {
	$c = get_instance();
}
function addquote($var) {
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

function system_site_url($uri) {
	$CI = & get_instance();
	return $CI->config->site_url('/admin/' . $uri);
}
function site_url_build($uri) {
	$CI = & get_instance();
	if (!preg_match('#^https?://#i', $uri)) {
		$uri = $CI->config->site_url($uri);
	}
	return $uri;
}
function stripvtags($expr, $statement) {
	$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
	$statement = str_replace("\\\"", "\"", $statement);
	return $expr . $statement;
}
function templates($s) {
	$t = new Templatelib();
	return $t->Template($s, FALSE);
}
function systemtemplate($s) {
	$t = new Templatelib();
	return $t->Template($s, FALSE);
}
function templatebuild($s) {
	$t = new Templatelib();
	$t->Template($s);
	$CI = & get_instance();
	$TemplateString = $CI->load->view($s, '', true);
	$t->TemplateString = $TemplateString;
	$t->Write();
	return $TemplateString;
}

class Templatelib {
	var $TemplateRootPath;
	var $TemplatePath;
	var $TemplateFolder;
	var $CompiledFolder = "";
	var $CompiledPath = "";
	var $TemplateFile = "";
	var $CompiledFile = "";
	var $TemplateString = "";
	var $TemplateExtension = '.html';
	var $CompiledExtension = '.php';

	/**
	 * Constructor
	 */
	public function __construct($fname = NULL, $write = TRUE, $initarray = NULL) {
		$this->ci = & get_instance();
		$this->TemplateRootPath = APPPATH . "templates/";
		$this->CompiledRootPath = APPPATH . "views/";
		$this->TemplateFolder =  "/";
		$this->CompiledFolder = "";
		$this->ci->load->helper('url');
		$this->lang = $this->ci->lang->language;
		$this->settings = & get_config();
		$this->initconfig();
		if ($initarray) {
			$this->initialize($initarray);
		}
		if ($fname) {
			return $this->Template($fname, $write);
		}
	}
	function initconfig() { 
		if(isset($this->ci->db->hostname)){
			$this->ci->loadmodel->initialize(array (
			'tableid' => 'id',
			'tablename' => 'settings'
			));
			$data = $this->ci->loadmodel->get_all()->result_array();
			foreach ($data as $k => $v) {
				$this->settings[$v['name']] = $v['value'];
			}
		}
		
	}
	function initialize($systemtemplate) {
		foreach ($systemtemplate as $key => $value) {
			$this-> $key = $value;
		}

	}
	function Template($filename, $write = TRUE) {
		$this->TemplateFile = $this->TemplateRootPath . $this->TemplateFolder . $filename . $this->TemplateExtension;
		$this->CompiledFile = $this->CompiledRootPath . $this->CompiledFolder . $filename . $this->CompiledExtension;
		
		if (!is_file($this->TemplateFile)) {
			$this->TemplateFile = ASSETS .'/'.APPLICATION.'/'.TEMPLATES. $this->TemplateFolder . $filename . $this->TemplateExtension;
		}
		if (!is_file($this->TemplateFile)) {
			die("模板文件'" . $this->TemplateFile . "'不存在，请检查目录");
		}
		if ($this->Load()) {
			$this->Compile($this->TemplateString);
			if ($write) {
				$this->Write();
			}
		} else {
			return false;
		}
		return $this->TemplateString;
	}
	function EvalTemplate($filename) {
		$this->TemplateFile = $this->TemplatePath . $filename . $this->TemplateExtension;
		$this->Load();
		$contents = str_replace('"', '\"', $this->TemplateString);
		return "return \"{$contents}\";";
	}
	function Load() {
		$fp = fopen($this->TemplateFile, 'rb');
		if ($fp) {
			if (!$this->TemplateString = @ fread($fp, filesize($this->TemplateFile))) {
				die("模板文件'" . $this->TemplateFile . "'不存在和内容错误，请检查文件");
			}
		}
		fclose($fp);
		return true;
	}
	function Compile($string) {
		$CONFIG = $this->settings;
		$CONFIG['attachment'] = 'attachment';
		$LANGLINE = $this->lang;
		$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(-\>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)?(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

		$nest = 5;
		$template = $string;

		if ($CONFIG['csrf_protection'] === TRUE) {
			$md5 = md5(time());
			$form = "\\1 \\2\n<input type=\"hidden\" name=\"";
			$form .= $this->ci->security->get_csrf_token_name();
			$form .= "\" value=\"{echo \$this->security->get_csrf_hash()}\"/>";
			$template = preg_replace("/(\<form.*? method=[\"\']?post[\"\']?)([^\>]*\>)/i", $form, $template);

		}
		
		if (isset ($CONFIG['ajax']) && $CONFIG['ajax'] == TRUE) {
			$form = "\\1  onclick=\"return onsubmitajax(this);\" type=\"button\"";
			$template = preg_replace("/(\<input.*? id=[\"\']?formsubmit[\"\']?)/i", $form, $template);

			$form = "\\1 \\2\n<input type=\"hidden\" name=\"doajax";
			$form .= "\" value=\"ajax\"/>";
			$template = preg_replace("/(\<form.*? method=[\"\']?post[\"\']?)([^\>]*\>)/i", $form, $template);

			$form = "\\1  onclick=\"return onsubmitajax(this);\" type=\"button\"";
			$template = preg_replace("/(\<input.*? id=[\"\']?formsubmit[\"\']?)/i", $form, $template);
			
			$form = "\\1 onclick=\"return onsubmitajax(this)\"";
			//$template = preg_replace ( "/(\<a.*? ajax=[\"\']?[\"\']?)/i",$form, $template );
		} else {
			$form = "\\1   type=\"submit\"";
			$template = preg_replace("/(\<input.*? id=[\"\']?formsubmit[\"\']?)/i", $form, $template);
		}
		//$template = preg_replace ( "/(\<img.*? src=[\"\']?[\"\']?)/i", '<img src="'.site_url(), $template );
		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
		$template = str_replace("{LF}", "<?=\"\\n\"?>", $template);
		$template = preg_replace("/\{$var_regexp\}/s", "<?=\\1?>", $template);
		$template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?>')", $template);
		$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template);
		$template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_\/]+)\}[\n\r\t]*/ies", "templates('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/ies", "templates('\\1')", $template);

		$template = preg_replace("/[\n\r\t]*\{buildtemplate\s+([a-z0-9_\/]+)\}[\n\r\t]*/ies", "templatebuild('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{buildtemplate\s+(.+?)\}[\n\r\t]*/ies", "templatebuild('\\1')", $template);

		$template = preg_replace("/[\n\r\t]*\{systemtemplate\s+(.+?)\}[\n\r\t]*/ies", "systemtemplate('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{modules\s+([a-z0-9_\/]+)\}[\n\r\t]*/is", "<?php include('" . APPPATH . "modules/data.modules.\\1.php')?>", $template);
		$template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? \\1 ?>','')", $template);
		$template = preg_replace("/[\n\r\t]*\{CONFIG\s+(.+?)\}[\n\r\t]*/ies", "\$CONFIG['\\1']", $template);
		$template = preg_replace("/[\n\r\t]*\{LANGLINE\s+(.+?)\}[\n\r\t]*/ies", "\$LANGLINE['\\1']", $template);
		$template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? echo \\1; ?>','')", $template);
		$template = preg_replace("/[\n\r\t]*\{elseif\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? } elseif(\\1) { ?>','')", $template);
		$template = preg_replace("/[\n\r\t]*\{else\}[\n\r\t]*/is", "<? } else { ?>", $template);
		$template = preg_replace("/[\n\r\t]*\{BASE_URL\}[\n\r\t]*/is", base_url(), $template);
		$template = preg_replace("/[\n\r\t]*\{SITE_URL\}[\n\r\t]*/is", site_url(), $template);
		$template = preg_replace("/[\n\r\t]*\{SYSTEM_URL\}[\n\r\t]*/is", site_url() . '/admin', $template);
		$template = preg_replace("/[\n\r\t]*\{IMAGES\}[\n\r\t]*/is", $this->build_url('images'), $template);
		$template = preg_replace("/[\n\r\t]*\{STYLES\}[\n\r\t]*/is", $this->build_url('styles'), $template);
		$template = preg_replace("/[\n\r\t]*\{JAVASCRIPT\}[\n\r\t]*/is", $this->build_url('javascript'), $template);
		$template = preg_replace("/[\n\r\t]*\{BASE_STYLES\}[\n\r\t]*/is", $this->base_urlpath('styles'), $template);
		$template = preg_replace("/[\n\r\t]*\{BASE_JAVASCRIPT\}[\n\r\t]*/is", $this->base_urlpath('javascript'), $template);
		$template = preg_replace("/[\n\r\t]*\{BASE_IMAGES\}[\n\r\t]*/is", $this->base_urlpath('images'), $template);
		
		$template = preg_replace("/[\n\r\t]*\{ATTACHMENT\}[\n\r\t]*/is", base_url() .'assets/'.APPLICATION.'/'. $CONFIG['attachment'] . '/', $template);
		$template = preg_replace("/[\n\r\t]*\{SYSTEMBUILDURL\s+(.+?)\}[\n\r\t]*/ies", "system_site_url('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{BUILDURL\s+(.+?)\}[\n\r\t]*/ies", "site_url_build('\\1')", $template);
		//$template = preg_replace("/[\n\r\t]*\{BUILDALIAS\s+(.+?)\}[\n\r\t]*/ies", "alias_url_build('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{BUILDDATE\}[\n\r\t]*/is", date("YmdHis", time()), $template);
		$template = preg_replace("/[\n\r\t]*\{getcategory\s+(.+?)\}[\n\r\t]*/ies", "self::getcategory_url_build('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{TAGS\s+(.+?)\}[\n\r\t]*/ies", "stripvtags(self::buildtags('\\1'),'')", $template);
		$template = preg_replace("/[\n\r\t]*\{MODULE\s+(.+?)\}[\n\r\t]*/ies", "stripvtags(self::build_modules('\\1'),'')", $template);
		
		
		for ($i = 0; $i < $nest; $i++) {
			$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\\3<? } } ?>')", $template);
			$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\\4<? } } ?>')", $template);
			$template = preg_replace("/[\n\r\t]*\{if\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/if\}[\n\r\t]*/ies", "stripvtags('<? if(\\1) { ?>','\\2<? } ?>')", $template);
			$template = preg_replace("/[\n\r\t]*\{while\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/while\}[\n\r\t]*/ies", "stripvtags('<? while(\\1) { ?>','\\2<? } ?>')", $template);
		}
		$template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
		$template = trim($template);
		//一行处理
		//$template = preg_replace ( '/\s(?=\s)/', '', $template );
		//$template = preg_replace ( '/[\n\r\t]/', ' ', $template );
		//换行
		$template = preg_replace("/ \?\>[\n\r]*\<\? /s", "", $template);

		$this->TemplateString = $template;

		if (!empty ($this->LinkFileType)) {
			$this->ModifyLinks();
		}
		return $this->TemplateString;

	} 
	function build_url($s='') {
		return build_url($s);
	}
	function base_urlpath($s='') {
		return base_build_url($s);
	}
	function build_modules($id){
		$str = $this->ci->loadmodel->get_module_by_id($id);
		//echo $this->Compile($str['data']);exit;
		return $str['code'].$this->Compile($str['data']);
		//return $str['code'].$str['data'];
	}
	function buildtags($data) {
		$str = explode('|', $data);
		$datas = array ();
		$return = null;
		foreach ($str as $value) {
			$s = explode(' = ', $value);
			if ($s[0] == 'action') {
				$action = $s[1];
			} else {
				if ($s[0] == 'return') {
					$return = $s[1];
				} else {
					$datas[$s[0]] = $s[1];
				}
			}
		}
		if (!isset ($action)) {
			return FALSE;
		}
		return $this->_action($action, $datas, $return);
	}
	function _action($action, $data, $return = NULL) {
		$return = isset ($return) ? $return : 'data';
		//$str = '$ci=get_instance ();';
		$model = isset ($data['model']) ? $data['model'] : 'loadmodel';
		//$str .= '$ci->load->model("' . $model . '","' . $model . '");';
		$str = '$' . $return . '=$ci->' . $model . '->' . $action . '(' . $this->arr_to_html($data) . ');';
		return '<?php ' . $str . ' ?>';
	}
	private static function arr_to_html($data) {
		if (is_array($data)) {
			$str = 'array(';
			$first = true;
			foreach ($data as $key => $val) {
				if (!$first) {
					$str .= ',';
				}
				$first = false;
				if (is_array($val)) {
					$str .= "'$key'=>" . self :: arr_to_html($val);
				} else {
					if (stripos($val, '$') === 0) {
						$str .= "'$key'=>$val";
					}
					elseif (stripos($val, '(') === 0) {
						$str .= "'$key'=>$val";
					}
					elseif (stripos($val, 'EV') === 0) {
						$str .= "'$key'=>$val";
					}
					elseif (stripos($val, '<?') === 0) {
						$str .= "'$key'=>$val";
					}
					elseif (stripos($val, 'array') === 0) {
						$str .= "'$key'=>" . ($val) . "";
					} else {
						$str .= "'$key'=>'" . ($val) . "'";
					}
				}
			}
			return $str . ')';
		}
		return false;
	}
	function getcategory_url_build($srt) {
		$su = explode('|', $srt); 
		$this->ci->loadmodel->initialize(array (
			'tableid' => 'catid',
			'tablename' => 'category'
		));
		$data = $this->ci->loadmodel->get_by_id($su[0]);
		$sut = isset ($su[1]) ? $su[1] : 'url';
		if (!$data) {
			return FALSE;
		}
		if ($sut == 'url') {
			if ($data['sethtml']) {
				//return $data ['purl'];
			}
			return $this->site_url($data['url']);
		}
		return $data[$sut];
	}
	function site_url($uri) {
		return $this->ci->config->site_url($uri);
	}
	function write() {
		$save_dir = dirname($this->CompiledFile);
		if (!is_dir($save_dir)) {
			$this->MakeDir($save_dir, 0777);
		}
		$fp = fopen($this->CompiledFile, 'wb');
		if (!$fp) {
			die('模板无法写入,请检查目录是否有可写');
		}
		$length = fwrite($fp, $this->TemplateString);
		fclose($fp);
		return $length;
	}

	function MakeDir($dir_name, $mode = 0777) {
		$dir_name = str_replace("\\", "/", $dir_name);
		$dir_name = preg_replace("#(/" . "/+)#", "/", $dir_name);
		if (is_dir($dir_name) !== false)
			Return true;
		$dir_name = explode("/", $dir_name);
		$dirs = '';
		foreach ($dir_name as $dir) {
			if (trim($dir) != '') {
				$dirs .= $dir . "/";
				if (is_dir($dirs) == false && @ mkdir($dirs, $mode) === false) {
					return false;
				} else {
					;
				}
			}
		}
		return true;
	}

	function RepairBracket($var) {
		return preg_replace("~\[([a-z0-9_\x7f-\xff]*?[a-z_\x7f-\xff]+[a-z0-9_\x7f-\xff]*?)\]~i", "[\"\\1\"]", $var);
	}

}