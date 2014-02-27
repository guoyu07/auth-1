<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}

class Fieldmodel extends MY_Model {
	public function __construct() {
		parent :: __construct();
		$this->load->config('field', TRUE);
		$this->field = $this->config->item('field');
		$this->load->dbforge();
	}
	function getextendtable($field = false) {
		$fields = $this->field['extend'];
		if ($field) {
			return isset ($fields[$field]) ? isset ($fields[$field]) : NULL;
		}
		return $fields;

	}
	function getextend($modelid, $siteid, $tables) {
		$field = $this->field['extendfield'];
		$extend = $this->field['extend'];
		if (!$this->db->table_exists($tables)) {
			$ext['id'] = $extend['id'];
			$ext['links'] = $extend['links'];
			$ext['islink'] = $extend['islink'];
			$ext['groupids_view'] = $extend['groupids_view'];
			$this->dbforge->add_field($ext);
			$this->dbforge->add_key(array (
				'id'
			));
			$this->dbforge->create_table($tables);
		}
		if ($this->db->field_exists('links', $tables)) {
			$extend['links']['name'] = 'links';
			$this->dbforge->modify_column($tables, array (
				'links' => $extend['links']
			));
		} else {
			$this->dbforge->add_column($tables, array (
				'links' => $extend['links']
			));
		}
		if ($this->db->field_exists('islink', $tables)) {
			$extend['islink']['name'] = 'islink';
			$this->dbforge->modify_column($tables, array (
				'islink' => $extend['islink']
			));
		} else {
			$this->dbforge->add_column($tables, array (
				'islink' => $extend['islink']
			));
		}
		if ($this->db->field_exists('groupids_view', $tables)) {
			$extend['groupids_view']['name'] = 'groupids_view';
			$this->dbforge->modify_column($tables, array (
				'groupids_view' => $extend['groupids_view']
			));
		} else {
			$this->dbforge->add_column($tables, array (
				'groupids_view' => $extend['groupids_view']
			));
		}
		foreach ($field as $key => $value) {
			$value['modelid'] = $where['modelid'] = $modelid;
			$value['siteid'] = $where['siteid'] = $siteid;
			$where['field'] = $value['field'];
			$query = $this->db->get_where('model_field', $where);
			if ($query->num_rows() == 0) {
				$value['fieldid']=$this->guid();
				$value ['datetime'] =$value ['updatetime']=time();
				$this->db->insert('model_field', $value);
			} else {
				$query = $query->row_array();
				$value ['updatetime']=time();
				$this->db->update('model_field', $value, "fieldid = '" . $query['fieldid']."'");
			}
			if ($this->db->field_exists($value['field'], $tables)) {
				$extend[$value['field']]['name'] = $value['field'];
				$this->dbforge->modify_column($tables, array (
					$value['field'] => $extend[$value['field']]
				));
			} else {
				$this->dbforge->add_column($tables, array (
					$value['field'] => $extend[$value['field']]
				));
			}
		}
	}
	function getbasic($modelid, $siteid, $tables) {
		$field = $this->field['basicfield'];
		$basic = $this->field['basic'];
		
		if (!$this->db->table_exists($tables)) {
			$db['id'] = $basic['id'];
			$db['columnid'] = $basic['columnid'];
			$db['datetime'] = $basic['datetime'];
			$db['updatetime'] = $basic['updatetime'];
			$db['username'] = $basic['username'];
			$db['uid'] = $basic['uid'];
			$db['upusername'] = $basic['upusername'];
			$db['upuid'] = $basic['upuid'];
			$db['pageview'] = $basic['pageview'];
			$db['status'] = $basic['status'];
			$this->dbforge->add_field($db);
			$this->dbforge->add_key(array (
				'id'
			));
			$this->dbforge->create_table($tables);
		}else{
			foreach ($basic as $key => $value){
				if ($this->db->field_exists($key, $tables)) {
					$basic[$key]['name'] = $key;
					$this->dbforge->modify_column($tables, array (
						$key => $value
					));
				} else {
					$this->dbforge->add_column($tables, array (
						$key => $value
					));
				}
			} 
		}
		if ($this->db->field_exists('columnid', $tables)) {
			$basic['columnid']['name'] = 'columnid';
			$this->dbforge->modify_column($tables, array (
				'columnid' => $basic['columnid']
			));
		} else {
			$this->dbforge->add_column($tables, array (
				'columnid' => $basic['columnid']
			));
		}
		foreach ($field as $key => $value) {
			$value['modelid'] = $where['modelid'] = $modelid;
			$value['siteid'] = $where['siteid'] = $siteid;
			$where['field'] = $value['field'];
			$query = $this->db->get_where('model_field', $where);
			if ($query->num_rows() == 0) {
				$value['fieldid']=$this->guid();
				$value ['datetime'] =$value ['updatetime']=time();
				$this->db->insert('model_field', $value);
			} else {
				$query = $query->row_array();
				$value ['updatetime']=time();
				$this->db->update('model_field', $value, "fieldid = '" . $query['fieldid']."'");
			}
			if ($this->db->field_exists($value['field'], $tables)) {
				$basic[$value['field']]['name'] = $value['field'];
				$this->dbforge->modify_column($tables, array (
					$value['field'] => $basic[$value['field']]
				));
			} else {
				$this->dbforge->add_column($tables, array (
					$value['field'] => $basic[$value['field']]
				));
			}
		}

	}
}