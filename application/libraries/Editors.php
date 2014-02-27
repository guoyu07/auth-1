<?php
class Editors {
	var $id = '';
	var $name = '';
	var $value = '';
	var $textarea = '';
	var $height = '300px';
	var $minWidth = '474px';
	var $parameters = array ();
	function __construct($params = array()) {
		$this->initialize ( $params );
	}
	
	public function initialize($params = array()) {
		if (count ( $params ) > 0) {
			foreach ( $params as $key => $val ) {
				$this->parameters [$key] = $val;
			}
		}
	}
	function createLinkstring() {
		$arg = "";
		foreach ( $this->parameters as $key => $value ) {
			if($key!='value'){
				$arg .= $key . '="' . $value . '" ';
			}
       		
		}
		return $arg;
	}
	public function kindeditor() { 
		$sd = '<textarea ' . $this->createLinkstring () . ' style="height:300px;"> ' . $this->parameters ['value'] . '</textarea>';
		$sd.=$this->kindeditorjs();
		$sd .= '<script>';
		$sd .= 'KindEditor.ready(function(K) {';
		$sd .= 'editor = K.create(\'#' . $this->parameters ['id'] . '\',{\'uploadJson\' : \'' . site_url ( 'swfupload/uploadjson' ) . '?watermark=1\',\'width\':\'97%\',\'minWidth\':\'' . $this->minWidth . '\',\'height\':\'350px\',\'resizeType\':\'1\',urlType : \'absolute\'});';
		$sd .= '});';
		$sd .= '</script>';
		//$sd .= '<script charset="utf-8" src="'.base_build_url('javascript').'plugins/editor/kindeditor/test.js"></script>';
		return $sd;
	}
	public function kindeditorjs() {
		$sd = '<script charset="utf-8" src="'.base_build_url('javascript').'plugins/editor/kindeditor/kindeditor.js"></script>';
		$sd .= '<script charset="utf-8" src="'.base_build_url('javascript').'plugins/editor/kindeditor/lang/zh_CN.js"></script>';
		$sd .= '<link rel="stylesheet" href="'.base_build_url('javascript').'plugins/editor/kindeditor/themes/default/default.css"type="text/css" media="all">';
		$sd .= '<script>';
		$sd .= 'var editor;';
		$sd .= 'var editconfig={\'uploadJson\' : \'' . site_url ( 'swfupload/uploadjson' ) . '?watermark=1\',\'width\':\'97%\',\'minWidth\':\'' . $this->minWidth . '\',\'minHeight\':\'350px\',\'resizeType\':\'1\',urlType : \'absolute\'};';
		$sd .= '</script>';
		return $sd;
	} 
	public function getedit($params = array(), $vs = 'kindeditor') {
		if (count ( $params ) > 0) {
			$this->initialize ( $params );
		}
		$sd = $this->$vs ();
		return $sd;
	} 

}