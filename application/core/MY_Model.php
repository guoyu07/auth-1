<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class MY_Model extends CI_Model {
	var $tablename = NULL;
	var $tableid = null;
	var $settings = null;
	public function __construct() {
		parent :: __construct();
		$this->load->helper('url');
	}
	function initialize($data) {
		foreach ($data as $key => $value) {
			$this-> $key = $value;
		}
	}
	function get($data = array ()) {

	}
	function get_category_id($catid) {
		$this->tablename = $this->config->item('user_auth_users_column_table', 'user_auth');
		$this->tableid = 'id';
		return $this->get_by_id($catid);
	}
	function truncate($data = array ()) {
		return 0;
	}
	function where($data = array (), $funs = 'num_rows') {
		$this->db->where($data);
		$query = $this->db->get($this->tablename);
		return $query-> $funs ();
	}

	function where_in($name = null, $data = array (), $select = '*') {
		$this->db->select($select);
		$this->db->where_in($name, $data);
		$query = $this->db->get($this->tablename);
		return $query->result_array();
	}
	function settings() {
		if ($this->settings) {
			return $this->settings;
		}
		$query = $this->db->get('settings');
		$settings = $query->result_array();
		foreach ($settings as $k => $v) {
			$result[$v['name']] = $v;
		}
		$this->settings = $result;
		return $result;
	}
	/** 
	 * @param int $offset
	 * @param int $row_count
	 * @param array|string $where
	 * @param string $select
	 * @param string $order
	 * @param string $by
	 * @param array $wherein
	 */
	function get_all($offset = 0, $row_count = 0, $where = NULL, $select = '*', $order = '', $by = 'DESC', $wherein = NULL, $like = null, $or_like = NULL) {
		$order = $order ? $order : $this->tableid;
		$show_table = $this->tablename;
		$this->db->select($select);
		if ($where) {
			$this->db->where($where);
		}
		if ($wherein) {
			$this->db->where_in($wherein['name'], $wherein['value']);
		}
		if ($like) {
			$this->db->like($like);
		}
		if ($or_like) {
			$this->db->or_like($or_like);
		}
		$this->db->order_by($order, $by);
		if ($offset >= 0 and $row_count > 0) {
			$query = $this->db->get($show_table, $row_count, $offset);
		} else {
			$query = $this->db->get($show_table);
		}
		//print_r($this->db->last_query());
		return $query;
	}

	function getpage($config, $offset = 0, $row_count = 20, $where = NULL, $select = '*', $order = '', $by = 'DESC', $wherein = NULL, $like = NULL, $or_like = NULL) {
		$pagination = isset ($config['pagination']) ? $config['pagination'] : 'pagination';
		$this->load->library($pagination);
		$this->pagination = $this-> $pagination;
		$datalist = $this->get_all($offset, $row_count, $where, $select, $order, $by, $wherein, $like, $or_like)->result_array();
		if ($row_count) {
			$config['total_rows'] = $this->get_all(0, 0, $where, $select, $order, $by, $wherein, $like, $or_like)->num_rows();
			$config['per_page'] = $row_count;
			$this->pagination->initialize($config);
			$data['pagination'] = $this->pagination->create_links();
		}
		$data['datalist'] = $datalist;
		return ($data);
	}

	function getdata($data = array ()) {
		$offset = isset ($data['offset']) ? $data['offset'] : 0;
		$row_count = isset ($data['row_count']) ? $data['row_count'] : 0;
		$where = isset ($data['where']) ? $data['where'] : NULL;
		$select = isset ($data['select']) ? $data['select'] : '*';
		$order = isset ($data['order']) ? $data['order'] : '';
		$by = isset ($data['by']) ? $data['by'] : 'DESC';
		$wherein = isset ($data['wherein']) ? $data['wherein'] : NULL;
		$or_like = isset ($data['or_like']) ? $data['or_like'] : NULL;
		return $this->get_all($offset, $row_count, $where, $select, $order, $by, $wherein, $or_like)->result_array();
	}
	function getjoindata($data = array ()) {
		$offset = isset ($data['offset']) ? $data['offset'] : 0;
		$row_count = isset ($data['row_count']) ? $data['row_count'] : 0;
		$where = isset ($data['where']) ? $data['where'] : NULL;
		$select = isset ($data['select']) ? $data['select'] : '*';
		$order = isset ($data['order']) ? $data['order'] : '';
		$by = isset ($data['by']) ? $data['by'] : 'DESC';
		$wherein = isset ($data['wherein']) ? $data['wherein'] : NULL;
		$or_like = isset ($data['or_like']) ? $data['or_like'] : NULL;
		return $this->getjoin($offset, $row_count, $where, $select, $order, $by, $wherein, $or_like)->result_array();
	}

	function join($data = array ()) {
		$funs = isset ($data['funs']) ? $data['funs'] : 'num_rows';
		//$data['select']=isset($data['select'])?$data['select']:'*';
		//$this->db->select($data['select']);
		$this->db->from($data['from'] . ' F');
		$this->db->join($data['join'] . ' J', ' J.' . $data['join_id'] . ' = F.' . $data['from_id'], 'left');
		$this->All_Options($data);
		$query = $this->db->get();
		return $query-> $funs ();
	}
	function All_Options($data = array ()) {
		if (isset ($data['select'])) {
			$select = isset ($data['select']) ? $data['select'] : '*';
			$this->db->select($select);
		}
		if (isset ($data['select_max'])) {
			$this->db->select_max($data['select_max']);
		}
		if (isset ($data['select_min'])) {
			$this->db->select_min($data['select_min']);
		}
		if (isset ($data['select_avg'])) {
			$this->db->select_avg($data['select_avg']);
		}
		if (isset ($data['select_sum'])) {
			$this->db->select_sum($data['select_sum']);
		}
		if (isset ($data['from'])) {
			//$this->db->from($data['from']);
		}
		if (isset ($data['where'])) {
			$this->db->where($data['where']);
		}
		if (isset ($data['or_where'])) {
			$this->db->or_where($data['or_where']);
		}
		if (isset ($data['where_in'])) {
			$this->db->where_in($data['where_in']['name'], $data['where_in']['value']);
		}
		if (isset ($data['or_where_in'])) {
			$this->db->or_where_in($data['or_where_in']['name'], $data['or_where_in']['value']);
		}
		if (isset ($data['where_not_in'])) {
			$this->db->where_not_in($data['where_not_in']['name'], $data['where_not_in']['value']);
		}
		if (isset ($data['or_where_not_in'])) {
			$this->db->or_where_not_in($data['or_where_not_in']['name'], $data['or_where_not_in']['value']);
		}
		if (isset ($data['like'])) {
			$this->db->like($data['like']);
		}
		if (isset ($data['or_like'])) {
			$this->db->or_like($data['or_like']);
		}
		if (isset ($data['or_not_like'])) {
			$this->db->or_not_like($data['or_not_like']);
		}
		if (isset ($data['group_by'])) {
			$this->db->group_by($data['group_by']);
		}
		if (isset ($data['distinct'])) {
			$this->db->distinct($data['distinct']);
		}
		if (isset ($data['having'])) {
			$this->db->having($data['having']);
		}
		if (isset ($data['or_having'])) {
			$this->db->or_having($data['or_having']);
		}
		if (isset ($data['order_by'])) {
			$this->db->order_by($data['order_by']);
		}
		if ((isset ($data['offset']) && (isset ($data['offset']) >= 0)) and isset ($data['row_count']) && (isset ($data['row_count']) > 0)) {
			$this->db->limit($data['row_count'], $data['offset']);
		}

	}
	function getjoin($offset = 0, $row_count = 0, $where = NULL, $select = '*', $order = '', $by = 'DESC', $wherein = NULL, $like = null, $or_like = NULL) {
		$order = $order ? $order : 'M.' . $this->tableid;
		$show_table = $this->tablename;
		$this->db->from($show_table . ' M');
		$this->db->join($show_table . '_data D', 'M.id = D.id', 'LEFT');
		$this->db->select($select);
		if ($where) {
			$this->db->where($where);
		}
		if ($wherein) {
			$this->db->where_in($wherein['name'], $wherein['value']);
		}
		if ($like) {
			$this->db->like($like);
		}
		if ($or_like) {
			$this->db->or_like($or_like);
		}
		$this->db->order_by($order, $by);
		if ($offset >= 0 and $row_count > 0) {
			$query = $this->db->get('', $row_count, $offset);
		} else {
			$query = $this->db->get('');
		}
		return $query;
	}

	/**
	 * 添加
	 * @param array $data
	 */
	function add($data) {
		if ($this->db->insert($this->tablename, $data)) {
			$id = $this->db->insert_id();
			return $id?$id:1;
		}
		return '0';
	}

	/**
	 * 获取
	 * @param int $id
	 */
	function get_by_id($id) {
		$this->db->where($this->tableid, $id);
		$query = $this->db->get($this->tablename);
		if ($query->num_rows() == 1) {
			return $query->row_array();
		}
		return NULL;
	}

	/**
	 * 获取
	 * @param int $id
	 */
	function get_by_field($id) {
		$this->db->where($this->tableid, $id);
		$query = $this->db->get($this->tablename);
		if ($query->num_rows() == 1) {
			return $query->row_array();
		}
		$fieldArray = $query->list_fields();
		$fields = array ();
		foreach ($fieldArray as $fieldName) {
			$fields[$fieldName] = '';
		}
		return $fields;
	}
	/**
	 *删除
	 * @param int $id
	 */
	function delete($id = null, $data = array ()) {
		if ($id) {
			$this->db->where($this->tableid, $id);
		}
		if ($this->db->delete($this->tablename, $data)) {
			return true;
		}
		return false;
	}

	/** 
	 * 更新
	 * @param array $data
	 * @param int $id
	 */
	function update($data, $id) {
		$this->db->where($this->tableid, $id);
		if ($this->db->update($this->tablename, $data)) {
			return $id;
		}
		return '0';
	}
	function genTree($items, $id = 'catid', $pid = 'parentid', $son = 'children') {
		$tree = array (); //格式化的树
		$tmpMap = array (); //临时扁平数据

		foreach ($items as $item) {
			$tmpMap[$item[$id]] = $item;
		}
		foreach ($items as $item) {
			if (isset ($tmpMap[$item[$pid]])) {
				$tmpMap[$item[$pid]][$son][] = & $tmpMap[$item[$id]];
			} else {
				$tree[] = & $tmpMap[$item[$id]];
			}
		}
		return $tree;
	}
	function guid($h = '') {
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
		$hyphen = chr(45); // "-"
		//$uuid = chr(123) // "{"
		$uuid = substr($charid, 0, 8) . $hyphen .
		substr($charid, 8, 4) . $hyphen .
		substr($charid, 12, 4) . $hyphen .
		substr($charid, 16, 4) . $hyphen .
		substr($charid, 20, 12);
		//chr(125); // "}"
		return $uuid;
	}
	function uuid($h=''){
		return $this->guid($h);
	}
}