<?php
class Apilibrary {
	function __construct() {
		$this->ci = & get_instance ();
	}
	function getpost($name) {
		return ! empty ( $_GET [$name] ) ? $_GET [$name] : (! empty ( $_POST [$name] ) ? $_POST [$name] : '');
	}
	function set_output($data) {
		$format = $this->ci->input->get_post('format');
		$haystack = array ('json', 'jsonp', 'xml', 'javascript', 'iframe','html' );
		$ation = in_array ( $format, $haystack ) ? $format : 'json';
		$data['param']=$this->ci->input->get_post('param');
		(isset($data['referer'])||$this->ci->input->get_post('referer'))?($data['referer']=$this->ci->input->get_post('referer')):'';
		(isset($data['refresh'])||$this->ci->input->get_post('refresh'))?($data['refresh']=$this->ci->input->get_post('refresh')):'';
		echo $this->$ation ( $data );
		exit ();
	}
	function jsonp($data) {
		$callback = ! empty ( $_GET ['callback'] ) ? $_GET ['callback'] : (! empty ( $_POST ['callback'] ) ? $_POST ['callback'] : 'jsonpcallback');
		$str = $this->json_encode ( $data );
		header ( 'Content-type: application/json' );
		return $callback . '(' . $str . ');';
	}
	function json($data) {
		$str = $this->json_encode ( $data );
		header ( 'Content-type: application/json' );
		return $str;
	}
	function json_encode($var) {
		if (function_exists ( 'json_encode' )) {
			return json_encode ( $var );
		} else {
			$this->ci->load->library ( 'json' );
			return $this->ci->json->encodeUnsafe ( $var );
		}
	}
	function javascript($data) {
		$callback = $this->getpost ( 'callback' );
		$callback=$callback?$callback:'jsonpcallback';
		$str = $this->json_encode ( $data );
		//header ( 'Content-type: application/x-javascript' );
		return '<script>' . $callback . '(' . $str . ');</script>';
	}
	function xml($data) {
		//header ( 'Content-type: text/xml' );
		header ( "Content-type: application/xml; charset=utf-8" );
		$this->ci->load->library ( 'atoxml' );
		echo $this->ci->atoxml->toXml ( $data );
	}
	function iframe($data) {
		$callback = $this->getpost ( 'callback' );
		$callback='parent.'.($callback?$callback:'jsonpcallback');
		$str = $this->json_encode ( $data );
		echo '<script>' . $callback . '(' . $str . ');</script>';
	}
	/**
	 * JSON响应
	 * @access private
	 * @param mixed $msg
	 * @return string
	 */
	public function response($msg,$error=0,$error_description='',$status=200) {
		$msg['error'] = $error;
		$msg['error_description'] = $error_description;
		@set_status_header($status);
		$this->set_output( $msg ); 
	}

}
