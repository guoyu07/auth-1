<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Aliasmodel extends MY_Model {
	public function __construct() {
		parent :: __construct();
		$this->_initialize();
	}
	function _initialize($name = 'alias', $id = 'aid') {
		$this->initialize(array (
			'tablename' => $name,
			'tableid' => $id
		));
	}
	function add($data = array ()) {
		if(stripos($data['url'],"lists/index/")){
			return true;
		}
		if(stripos($data['url'],"ontent/index/")){
			return true;
		}
		$ck = $this->get_alias($data);
		if ($ck) {
			$this->db->where('alias', $data['alias']);
			$this->db->where('value', $data['value']);
			return $this->db->update($this->tablename, $data); 
		} else {
			$data['dateline'] = time();
			if ($this->db->insert($this->tablename, $data)) {
				$id = $this->db->insert_id();
				return $id;
			}
			return '0';
		}
	}
	function get_alias($data = array ()) {
		$this->db->where('alias', $data['alias']);
		$this->db->where('value', $data['value']);
		$query = $this->db->get($this->tablename);
		return $query->num_rows();
	}
}