<?php
class Formhandler {
	function __construct() {
	}
	function Radio($name, $options, $value = array(), $default = null, $extra = '') {
		$v = isset ( $value ['value'] ) ? $value ['value'] : 'value';
		$n = isset ( $value ['name'] ) ? $value ['name'] : 'name'; 
		$string = ''; 
		foreach ( $options as $key => $option ) {
			$option ['value'] = isset ( $option [$v] ) ? $option [$v] : $key;
			$checked = $checkeds='';
			if ($default !== null) {
				$checked = in_array ( $option [$v], ( array ) $default ) ? " CHECKED" : "";
				$checkeds = in_array ( $option [$v], ( array ) $default ) ? " hradiocheckeds" : "";
			} 
			$string .= "<div  class='hradio{$checkeds}' lang='radio{$name}' >{$option[$n]}<input name='{$name}' id='{$name}_{$option[$v]}' type='radio' value='{$option[$v]}'{$checked} class=radio {$option['extra']} style='display: none;'><label for='{$name}_{$option[$v]}'style='display: none;'>{$option[$n]}</label></div>";
 
		}
		return '<div id="radio'.$name.'">'.$string.'</div>';
	}
	function Checkboxs($name, $options, $default = null, $extra = NULL, $qs = '', $fs = '') {
		$string = '';
		foreach ( $options as $key => $option ) {
			$option ['value'] = isset ( $option ['value'] ) ? $option ['value'] : $key;
			$checked = '';
			if ($default !== null) {
				$checked = in_array ( $option ['value'], ( array ) $default ) ? " checked" : "";
			} 
			$str = "<label for='{$name}_{$option['value']}'class='themesbox $checked'><input name='{$name}' id='{$name}_{$option['value']}' type='checkbox'";
			$str .= "value='{$option['value']}'{$checked} class='checkbox' lang='{$option['name']}' {$option['extra']}> {$option['name']}</label>";
			$string .= '' . $str . '';
		}
		Return $qs . $string . $fs;
	}
	function Checkbox($name, $options, $default = null, $extra = NULL, $qs = '<ul class="checkbox">', $fs = '</ul>') {
		$string = '';
		foreach ( $options as $key => $option ) {
			$option ['value'] = isset ( $option ['value'] ) ? $option ['value'] : $key;
			$checked = '';
			if ($default !== null) {
				$checked = in_array ( $option ['value'], ( array ) $default ) ? " CHECKED" : "";
			
			}
			$str = "<label for='{$name}_{$option['value']}' ><input name='{$name}' id='{$name}_{$option['value']}' type='checkbox'";
			$str .= "value='{$option['value']}'{$checked} class='checkbox' lang='{$option['name']}' {$option['extra']}> {$option['name']}</label>";
			$string .= '<li>' . $str . '</li>';
		}
		Return $qs . $string . $fs;
	}
	function Select($name, $options, $value = array(), $default = null, $extra = null) {
		$v = isset ( $value ['value'] ) ? $value ['value'] : 'value';
		$n = isset ( $value ['name'] ) ? $value ['name'] : 'name';
		$class = isset ( $value ['class'] ) ? $value ['class'] : 'select';
		$extras = isset ( $value ['extra'] ) ? $value ['extra'] : 'extra';
		if ($default === 0) {
			settype ( $default, 'string' );
		}
		$size = 0;
		if (stristr ( $extra, 'multiple' ) !== false and stristr ( $extra, 'size' ) === false) {
			$size = ' size="' . count ( $options ) . '"';
		}
		
		$string = "<SELECT NAME=\"{$name}\" id=\"{$name}\"{$size} class=\"{$class}\" $extra>";
		$selected = '';
		foreach ( $options as $label => $option ) {
			$option [$v] = isset ( $option [$v] ) ? $option [$v] : $label;
			if ($default !== null) {
				$selected = in_array ( $option [$v], ( array ) $default ) ? " SELECTED" : "";
			} 
			$string .= "<option value='{$option[$v]}'{$selected} {$option[$extras]}>{$option[$n]}</option>";
		
		}
		$string .= "</SELECT>\r\n";
		return $string;
	}
}