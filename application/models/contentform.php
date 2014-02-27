<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Contentform extends MY_Model {
	public function __construct() {
		parent :: __construct();
		$this->load->library('editors');
		$this->load->library('formhandler');
		
		$this->addfields = array (
			'text' => '单行文本',
			'textarea' => '多行文本',
			'typeid' => '分类',
			'style' => '颜色',
			'editor' => '编辑器',
			'box' => '多选项',
			'radio' => '单选项',
			'select' => '下拉框',
			'image' => '图片',
			'images' => '多图片',
			'number' => '数字', 
			'datetime' => '日期和时间', 
			'groupid' => '会员组',   
			'map' => '地图字段',
			'islink' => '链接',
			'downfiles' => '文件',
			'model' => '模型',
			'pages'=>'分页',
			'template'=>'模板',
			'url'=>'url',
			'column'=>'栏目'
		);
		$this->fields = $this->addfields;
		//不允许删除的字段，这些字段讲不会在字段添加处显示
		$this->not_allow_fields = array (
			'catid',
			'typeid',
			'title',
			'keyword',
			'posid',
			'template',
			'username'
		);
		//允许添加但必须唯一的字段
		$this->unique_fields = array (
			'pages',
			'readpoint',
			'author',
			'copyfrom',
			'template',
			'islink'
		);
		//禁止被禁用的字段列表
		$this->forbid_fields = array (
			'catid',
			'title',
			'updatetime',
			'inputtime',
			'url',
			'listorder',
			'status',
			'template',
			'username'
		);
		//禁止被删除的字段列表
		$this->forbid_delete = array (
			'catid',
			'typeid',
			'title',
			'thumb',
			'keywords',
			'updatetime',
			'inputtime',
			'posids',
			'url',
			'listorder',
			'status',
			'template',
			'username',
			'template'
		); 
		$this->datatypes = array (
			'CHAR' => 'CHAR',
			'VARCHAR' => 'VARCHAR',
			'TINYTEXT' => 'TINYTEXT',
			'TEXT' => 'TEXT',
			'BLOB' => 'BLOB',
			'MEDIUMTEXT' => 'MEDIUMTEXT',
			'MEDIUMBLOB' => 'MEDIUMBLOB',
			'LONGTEXT' => 'LONGTEXT',
			'LONGBLOB' => 'LONGBLOB',
			'ENUM' => 'ENUM',
			'SET' => 'SET',
			'TINYINT' => 'TINYINT',
			'SMALLINT' => 'SMALLINT',
			'MEDIUMINT' => 'MEDIUMINT',
			'INT' => 'INT',
			'BIGINT' => 'BIGINT',
			'FLOAT' => 'FLOAT',
			'DOUBLE' => 'DOUBLE',
			'DECIMAL' => 'DECIMAL',
			'DATE' => 'DATE',
			'DATETIME' => '日期和时间的组合',
			'TIMESTAMP' => '时间戳',
			'TIME' => '时间HH:MM:SS', 
			'YEAR' => '2 位或 4 位格式的年'
		);
	}
	function initialize($data) {
		foreach ($data as $key => $value) {
			$this-> $key = $value;
		}
	}
	function getinc($name) {
		if (isset ($this-> $name)) {
			return $this-> $name;
		}
	}
	function setinc($name, $data) {
		if (isset ($this-> $name)) {
			$this-> $name = $data;
		}
	}
	function gefield($data) {
		$formtype = $data['formtype'];
		$datatypes = $data['datatypes'];
		$fed['comment'] = $data['comment'];
		$fed['constraint'] = $data['constraint'];
		switch ($datatypes) {
			case 'TEXT' :
				$fed['type'] = 'TEXT';
				break; 
			default :
				$fed['type'] =$datatypes;
				if(strtolower($data['defaultvalues'])=='null'){
					$fed['null'] =true;
				}else{
					$fed['default'] = $data['defaultvalues'];
				}
				break;
				}
		return $fed;

	} 
	function gethidden() {
		return array (
			'status',
			'paginationtype',
			'maxcharperpage',
			'pages',
			'readpoint'
		);

	}
	function getcontent($data = array ()) {
		$this->data = $data;
		if (isset ($data['id'])) {
			$this->id = $data['id'];
		}
		$gethidden = $this->gethidden();
		$info = array ();
		$this->content_url = $data['url'];
		foreach ($this->fields as $field => $v) {
			$func = $v['formtype'];
			$value = isset ($data[$v['field']]) ? htmlspecialchars($data[$v['field']], ENT_QUOTES) : '';
			if ($func == 'pages' && isset ($data['maxcharperpage'])) {
				$value = $data['paginationtype'] . '|' . $data['maxcharperpage'];
			}
			if (in_array($v['field'], $gethidden)) {
				continue;
			}
			$form = $this-> $func ($v['field'], $value, $v);
			if ($form !== false) {
				if ($v['iscore'] == 1) {
					if ($v['isbase']) {
						$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
						$v['tips'] = $v['tips'] ? '<font class="vtip" title="' . $v['tips'] . '" style="color:#00F">?</font>' : '';
						$info['base'][$field] = array (
							'name' => $v['name'],
							'tips' => $v['tips'],
							'form' => $form,
							'star' => $star,
							'isomnipotent' => $v['isomnipotent'],
							'formtype' => $v['formtype'],
							'field' => $v['field']
						);
					} else {
						$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
						$info['senior'][$field] = array (
							'name' => $v['name'],
							'tips' => $v['tips'],
							'form' => $form,
							'star' => $star,
							'isomnipotent' => $v['isomnipotent'],
							'formtype' => $v['formtype'],
							'field' => $v['field']
						);
					}
				}

			}
		}
		return $info;
	}
	function text($field, $value, $fieldinfo) {
		if (!$fieldinfo['isadd']) {
			return $value;
		}
		return '<span class="rc_i_wp"><b><input type="text" name="' . $field . '" id="' . $field . '" value="' . $value . '" class="rc_ins w500 required"></b></span>';
	}
	function style($field, $value, $fieldinfo) {
		if (!$fieldinfo['isadd']) {
			return $value;
		}
		$str = '<input type="text" name="' . $field . '" value="' . $value . '" class="color">';

		return $str;
	}
	function image($field, $value, $fieldinfo) {
		$img = $value ? ('<img src="' . getimage($value) . '" onload="javascript:DrawImage(this,150,80);">') : '';
		if (!$fieldinfo['isadd']) {
			return $img;
		}
		$str = '<input type="hidden" name="' . $field . '" id="m_' . $field . '_' . $fieldinfo['fieldid'] . '" value="' . $value . '"  > <input type="button"  id="imagebutton" class="submit w80 imagebutton"  value="上 传" lang="' . $field . '_' . $fieldinfo['fieldid'] . '">';
		$str .= ' <div id="' . $field . '_' . $fieldinfo['fieldid'] . '"> ';
		$str .= $value ? ('<span class="picture"  id="image_' . ($field . '_' . $fieldinfo['fieldid']) . '">' . $img . '<a href="#" onclick="remove_image(this,\'' . ($field . '_' . $fieldinfo['fieldid']) . '\')" lang="' . ($field . '_' . $fieldinfo['fieldid']) . '"><img src="' . (base_build_url('styles/')) . 'images/error.gif"/></a></span>') : '';
		$str .= '</div >  ';
		$str .= $this->editors->kindeditorjs();
		return $str;
	}
	function textarea($field, $value, $fieldinfo) {
		if (!$fieldinfo['isadd']) {
			return $value;
		}
		return '<textarea style="height:100px" name="' . $field . '" id="' . $field . '" class="w500">' . $value . '</textarea>';
	}
	function editor($field, $value, $fieldinfo) {
		if (!$fieldinfo['isadd']) {
			return $value;
		}
		return $this->editors->getedit(array (
			'value' => getimage($value),
			'parameters' => array (
				'name' => $field,
				'id' => $field
			),
			'name' => $field,
			'id' => $field
		));

	}
	function url($field, $value, $fieldinfo) {
		if (!$fieldinfo['isadd']) {
			return $value;
		}
		return '<span class="rc_i_wp"><b><input type="text" name="' . $field . '" id="' . $field . '" value="' . $value . '" class="rc_ins w500 required"></b></span>';
	}
	function islink($field, $value, $fieldinfo) {
		$activatedRadio[0] = array (
			'name' => '关闭',
			'value' => '0',
			'extra' => 'onclick="activated(0)"'
		);
		$activatedRadio[1] = array (
			'name' => '开启',
			'value' => '1',
			'extra' => 'onclick="activated(1)"'
		);
		if (!$fieldinfo['isadd']) {
			return $value;
		}
		return $this->formhandler->Radio($field, $activatedRadio, '', $value);
	}
	function groupid($field, $value, $fieldinfo) {
		if(!$fieldinfo['isadd']){
			return $value;
		} 
		$this->initialize (array('tablename'=>$this->config->item('user_auth_users_competence_table', 'user_auth'),'tableid'=> 'id' ));
		$templates = $this->getdata (array());
		$fields[0]=array('value'=>0,'name'=>'全部','extra'=>'');
		foreach($templates as $k => $v){
			$v['value']=$v['id'];
			$v['name']=$v['subject'];
			$v['extra']=$v['id'];
			$fields[]=$v;
		}
		return $this->formhandler->Select($field, $fields, '', $value);
	}
	function template($field, $value, $fieldinfo) {
		if(!$fieldinfo['isadd']){
			return $value;
		}
		return $this->gettemplate ();
	}
		function gettemplate($name = NULL, $default = NULL, $type = NULL) {
		$this->initialize ( array ('tablename' => 'templates') );
		$where = '';
		if ($type) {
			$where ['type'] = $type;
		}
		$templates = $this->getdata (array());
		$data [] = array ('value' => '0', 'name' => '无模板', 'extra' => '' );
		foreach ( $templates as $k => $v ) {
			$s ['value'] = $v ['id'];
			$s ['name'] = $v ['name'];
			$s ['extra'] = '';
			$data [] = $s;
		}
		return $this->formhandler->Select ( $name, $data, array ('class' => '' ), $default );
	
	}
	function datetime($field, $value, $fieldinfo) {
		$time='';
		if($value){
			$time=date("Y-m-d H:i:s",$value);
		}
		if(!$fieldinfo['isadd']){
			return $time;
		} 
		$str='<span class="rc_i_wp"><b><input type="text" name="'.$field.'" id="'.$field.'" value="'.$time.'" class="Wdate rc_ins w500 required" onFocus="WdatePicker({startDate:\'%y-%M-01 00:00:00\',dateFmt:\'yyyy-MM-dd HH:mm:ss\',alwaysUseStartDate:true})"></b></span>';
		return $str;
	}
	function downfiles($field, $value, $fieldinfo) {
		if(!$fieldinfo['isadd']){
			return $value;
		}
		$str='<input type="hidden" name="' . $field . '" id="m_' . $field . '" value="' . $value . '"  > <input type="button" class="downfiles" value="上 传" lang="'.$field.'">';
		$str.=' <div id="' . $field . '"> <span class="pictures_files">'.($value?'<img src="'.base_build_url('images/filesicon').substr($value,-3).'.gif">':'').'</span></div >  '. $this->editors->kindeditorjs(); 
		return $str;
	
	}
	function radio($field, $value, $fieldinfo) {
		$values = explode('||', $fieldinfo['setting']);
		$data=array();
		foreach ($values as $option) {
			$optionArray = explode('==', $option);
			$s['name'] = $optionArray[0];
			$s['value'] = count($optionArray) == 1 ? $optionArray[0] : $optionArray[1];
			$s['extra']='';
			$data[$s['value']]=$s;
		}
		if (!$fieldinfo['isadd']) {
			 return isset($data[$value]['name'])?$data[$value]['name']:$value;
		}
		return $this->formhandler->Radio($field, $data, '', $value);
	}
}