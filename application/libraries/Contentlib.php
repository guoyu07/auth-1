<?php
class Contentlib {
	public function __construct() {
		$this->ci = & get_instance ();
		$this->loadmodel = $this->ci->loadmodel;
		$this->_initialize ();
	}
	function _initialize($name = 'users_column', $id = 'id') {
		$this->loadmodel->initialize ( array ('tablename' => $name, 'tableid' => $id ) );
	}
	function validation($columnid) {
		$json ['status'] = 'failure';
		if (! $columnid) {
			$json ['msg'] = '错误操作';
			return $json;
		}
		$category = $this->loadmodel->get_by_id ( $columnid );
		if (is_null ( $category )) {
			$json ['msg'] = '错误操作';
			return $json;
		}
		//print_r ( $category );
		if ($category ['type'] == 1) {
			$json ['msg'] = '错误操作';
			//return $json;
		}
		if (empty ( $category ['modelid'] )) {
			$json ['msg'] = '错误操作';
			return $json;
		}
		$modelid = $category ['modelid'];
		$this->_initialize ( 'model', 'modelid' );
		$mode = $this->loadmodel->get_by_id ( $modelid );
		$this->_initialize ( 'model_field', 'modelid' );
		$where ['modelid'] = $modelid;
		$modelfields = $this->loadmodel->getpage ( array (), 0, 0, $where, '*', 'listorder', 'ASC' );
		$fields = $modelfields ['datalist'];
		$this->ci->load->library ( 'form_validation' );
		$val = $this->ci->form_validation;
		foreach ( $fields as $k => $v ) {
			$val->set_rules ( $v ['field'], $v ['name'], $v ['pattern'] );
		}
		 
		if ($val->run () == FALSE) {
			$strarry = $val->_error_array;
			$str = '';
			foreach ( $strarry as $k => $v ) {
				$str .= $v;
			}
			$json ['msg'] = $str;
			return $json;
		}
		foreach ( $fields as $k => $v ) {
			if ($v ['issystem']) {
				$info ['base'] [$v ['field']] = $val->set_value ( $v ['field'] );
			} else {
				$info ['senior'] [$v ['field']] = $val->set_value ( $v ['field'] );
			}
		}
		
		$info ['base'] ['columnid'] = $columnid;
		//$info ['base'] ['status'] = 1;
		//$ucdata = $this->auth->ucdata;
		//$info ['base'] ['upuid'] = $ucdata ['uid'];
		//$info ['base'] ['upusername'] = $ucdata ['username'];
		$id = isset ( $_POST ['id'] ) ? $_POST ['id'] : (isset ( $_GET ['id'] ) ? $_GET ['id'] : '0');
		$this->_initialize ();
		$category = $this->loadmodel->get_by_id ( $columnid );
		$this->_initialize ( 'model', 'modelid' );
		$model = $this->loadmodel->get_by_id ( $category ['modelid'] );
		$this->_initialize ( $model ['tablename'], 'id' );
		$content = $this->loadmodel->get_by_id ( $id );
		if ($content) {
			$this->loadmodel->updata ( $info ['base'], $id );
		} else {
			//$info ['base'] ['uid'] = $ucdata ['uid'];
			//$info ['base'] ['username'] = $ucdata ['username'];
			$info ['base'] ['datetime'] =$info ['base'] ['updatetime'] = time();
			$id = $this->loadmodel->add ( $info ['base'] );
		}
		$this->_initialize ( $model ['tablename'] . '_data', 'id' );
		$content_data = $this->loadmodel->get_by_id ( $id );
		if ($content_data) {
			$this->loadmodel->update ( $info ['senior'], $id );
		} else {
			$info ['senior'] ['id'] = $id;
			$this->loadmodel->add ( $info ['senior'] );
		}
		$this->updataurl ( $model ['tablename'], $columnid, $id );
		$json ['status'] = 'success';
		$json ['msg'] = '操作成功';
		return $json;
	}
	function updataurl($tablename, $columnid, $id) {
		$this->_initialize ( $tablename, 'id' );
		$db=array ('url' => ( 'content/index/' . $columnid . '/' . $id ) );
		$this->loadmodel->update ($db,$id );
		//echo $this->loadmodel->db->last_query();
		//exit;
	}
	function site_url($uri = '') {
		return $this->ci->config->site_url ( $uri );
	}
}
?>