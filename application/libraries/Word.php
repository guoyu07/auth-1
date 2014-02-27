<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Word {
	function __construct() {

	}
	function start() {
		ob_start();
		echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
			xmlns:w="urn:schemas-microsoft-com:office:word"
			xmlns="http://www.w3.org/TR/REC-html40">';
	}
	function save($path) {
		echo "</html>";
		$data = ob_get_contents();
		ob_end_clean();
		$this->wirtefile($path, $data);
	}
	function wirtefile($fn, $data) {
		$fp = fopen(FCPATH.$fn, "wb");
		fwrite($fp, $data);
		fclose($fp);
	}
}