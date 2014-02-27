<?php
if (!defined('BASEPATH')) {
	exit ('No direct script access allowed');
}
class Apimodel extends MY_Model {
	public function __construct() {
		parent :: __construct();

	}
	function initialize($data) {
		foreach ($data as $key => $value) {
			$this-> $key = $value;
		}

	}
	function where($where) {
		$this->db->where($where);
	}
	/**
	 * 获取
	 * @param int $id
	 */
	function get() {
		$query = $this->db->get($this->tablename);
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return NULL;
	}

	/**
	 * 获取
	 * @param int $id
	 */
	function get_one() {
		$query = $this->db->get($this->tablename);
		if ($query->num_rows() > 0) {
			$content = $query->row_array();
			$v1=$v2=array();
			foreach ($content as $key => $value) {
				$v1[$key]=$this->getimage($value);
			}
			$this->db->where('id', $content['id']);
			$content_data = $this->db->get($this->tablename . '_data')->row_array();
			foreach ($content_data as $key => $value) {
				$v2[$key]=$this->getimage($value);
			}
			return array_merge($v1, $v2);
		}
		return NULL;
	}

}