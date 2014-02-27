<?php 
class Pcharts {
	public function __construct($props = array()) { 
		if (count ( $props ) > 0) {
			$this->initialize ( $props );
		}
		include("chart/pData.class.php"); 
 		include("chart/pChart.class.php"); 
		include("chart/pCache.class.php"); 
	}
	public function initialize($config = array()) {
		foreach ( $config as $key => $val ) {
			include("chart/'.$val.'.php"); 
		} 
	}

} 