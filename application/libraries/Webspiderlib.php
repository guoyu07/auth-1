<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Webspiderlib {
	function __construct() {
		$this->ci = & get_instance();
		$this->ci->load->library('phpquery');
	}
	function get_content($nodetent, $config, $page = 0) {
		$url=$nodetent['url'];
		set_time_limit(300);
		$page = intval($page) ? intval($page) : 0;
		$this->ci->phpquery->newDocumentFile($url);
		$html = $companies = pq("body");
		$data=array();
		if ($html) {
			if (empty($page)) {
				//获取标题
				if ($config['title_rule']) {
					$data['title'] = $this->replace_item(pq($config['title_rule'])->text(), $config['title_html_rule']);
				}else{
					$data['title'] = $nodetent['title'];
				}
				//获取作者
				if ($config['author_rule']) {
					$data['author'] = $this->replace_item(pq($config['author_rule'])->text(), $config['author_html_rule']);
				}
				//获取来源
				if ($config['comeform_rule']) {
					$data['comeform'] = $this->replace_item(pq($config['comeform_rule'])->text(), $config['comeform_html_rule']);
				}
				//获取时间
				if ($config['time_rule']) {
					$data['time'] = $this->replace_item(pq($config['time_rule'])->text(), $config['time_html_rule']);
				}
				if (empty($data['time'])) {
					$data['time'] = time();
				}
				//对自定义数据进行采集
				if ($config['customize_config']) {
					foreach ($config['customize_config'] as $k=>$v) {
						if (empty($v['rule'])){
							 continue;
						}
						$data[$v['en_name']] = $this->replace_item(pq($v['rule'])->text(), $v['html_rule']);
					}
				}
			}
			//print_r(pq('body')->html());
			//exit;//获取内容
			if ($config['content_rule']) {
				$data['content'] = $this->replace_item(pq($config['content_rule'])->html(), $config['content_html_rule']);
			}
		}
		return $data;
	}
	/**
	 * 过滤代码
	 * @param string $html  HTML代码
	 * @param array $config 过滤配置
	 */
	public  function replace_item($html, $config) {
		if (empty($config)) {
			return $html;
		}
		$config = explode("\n", $config);
		$patterns = $replace = array();
		$p = 0;
		foreach ($config as $k=>$v) {
			if (empty($v)){
				 continue;
			}
			$c = explode('[|]', $v);
			$patterns[$k] = '/'.str_replace('/', '\/', $c[0]).'/i';
			$replace[$k] = $c[1];
			$p = 1;
		}
		print_r($replace);
		return $p ? @preg_replace($patterns, $replace, $html) : false;
	}
	/**
	 * URL地址检查
	 * @param string $url      需要检查的URL
	 * @param string $baseurl  基本URL
	 * @param array $config    配置信息
	 */
	public  function url_check($url, $baseurl, $config) {
		$urlinfo = parse_url($baseurl);
		$baseurl = $urlinfo['scheme'].'://'.$urlinfo['host'].(substr($urlinfo['path'], -1, 1) === '/' ? substr($urlinfo['path'], 0, -1) : str_replace('\\', '/', dirname($urlinfo['path']))).'/';
		$explodes=explode("/",$urlinfo['path']);
		//print_r($explodes);exit;
		if (strpos($url, '://') === false) {
			if ($url[0] == '/') {
				$url = $urlinfo['scheme'].'://'.$urlinfo['host'].$url;
			}else if($url[0].$url[1] == './'){
				$url= str_replace("./","/",$url);
				//$url = $urlinfo['scheme'].'://'.$urlinfo['host'].'/'.$explodes['1'].$url;
				//$url = $config['page_base'].$url;
				$url = $config['page_base'].$url;
			}else if($url[0].$url[1].$url[2] == '../'){
				$url= str_replace("../","/",$url);
				$url = $urlinfo['scheme'].'://'.$urlinfo['host'].'/'.$url;
			}else {
				if ($config['page_base']) {
					$url = $config['page_base'].$url;
				} else {
					$url = $baseurl.$url;
				}
			}
		}
		return $url;
	}
	/**
	 * 获取文章网址
	 * @param string $url           采集地址
	 * @param array $config         配置
	 */
	public function get_url_lists($url, & $config) {
		$this->ci->phpquery->newDocumentFile($url);
		$companies = pq("a");
		$data = array ();
		foreach ($companies as $k=> $v) {
			$nurl = pq($v)->attr('href');
			if ($config['url_contain']) {
				if (strpos($nurl, $config['url_contain']) === false) {
					continue;
				}
			}
			if ($config['url_except']) {
				if (strpos($nurl, $config['url_except']) !== false) {
					continue;
				}
			}
			$url2 = $this->url_check($nurl, $url, $config);
			$data[$k]['url'] = $url2;
			$data[$k]['title'] = pq($v)->text();

		}
		return $data;
	}
	/**
	 * 得到需要采集的网页列表页
	 * @param array $config 配置参数
	 * @param integer $num  返回数
	 */
	public function url_list(& $config, $num = '') {
		$url = array ();
		switch ($config['sourcetype']) {
			case '1' : //序列化
				$num = ($num) ? $config['pagesize_end'] : $num;
				for ($i = $config['pagesize_start']; $i <= $num; $i = $i + $config['par_num']) {
					$url[$i] = str_replace('(*)', $i, $config['urlpage']);
				}
				break;
			case '2' : //多网址
				$url = explode("\r\n", $config['urlpage']);
				break;
			case '3' : //单一网址
			case '4' : //RSS
				$url[] = $config['urlpage'];
				break;
		}
		return $url;
	}
}