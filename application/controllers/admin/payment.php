<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

class Payment extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'apilibrary' );
		$this->load->library ( 'formhandler' );
		$this->form = $this->formhandler;
		$this->_initialize ();
	}
	function _initialize($name = 'payment', $id = 'id') {
		$this->loadmodel->initialize ( array ('tablename' => $name, 'tableid' => $id ) );
	}
	
	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid=0) {
		$data ['uid'] = '';
		$p_config ['base_url'] = 'payment/index';
		$data = $this->loadmodel->getpage ( $p_config,$pid, 10, '', '*', 'id', 'ASC' );
		$pay_plugin=$this->_get_pay_plugin(array(),0,0);
		$data['pay_plugin']=$pay_plugin['datalist'];
		$this->load->view ( 'admin/payment/payment', $data );
	}
	/**
	 * 更多
	 */
	function payall($pid=0){
		$p_config ['base_url'] = 'payment/payall';
		$p_config ['pagination'] = 'systempagination';
		$data=$this->_get_pay_plugin($p_config,$pid);
		$this->load->view ( 'admin/payment/payall', $data );
	}
	
	function add(){
		
	}
	function test($pid=0){
		$payment=$this->loadmodel->get_by_id($pid);
		$this->_initialize ('pay_plugin');
		$pay_plugin=$this->loadmodel->get_by_id($payment['plugin_id']);
		$lib = $pay_plugin['interface'] . 'pay';
		$this->load->library ( $lib );
		$order['status']=2;
		$order['pay_status']=0;
		$order['payable_amount']='300';
		$order['order_no']=time();
		$order['body']='300';
		$order['subject']='测试账号';
		$order['create_time']='测试账号';
		$url = $this->$lib->build_form ( $order );
		echo $url; 
		
	}
	/**
	 * 添加
	 */
	 function alladd($id=null){
	 	$this->_initialize ('pay_plugin');
	 	$pay=$this->loadmodel->get_by_id($id);
	 	$data['datalist']['pay_plugin']=$data['datalist']['pay']=$pay;
	 	$this->load->library ( 'editors' );
		$data ['editors'] = $this->editors->getedit ( array ('value' => $pay ['description'], 'name' => 'note', 'id' => 'note' ) );
	 	$this->load->view ( 'admin/payment/alladd',$data );
	 }
	 /**
	  * 提交
	  */
	 function dopost(){
	 	$pay_id=$this->input->get_post('pay_id');
	 	$payment=$this->_get_pay_payment($pay_id);
	 	$paymentdb['status']=1;
	 	$paymentdb['poundage']=0;
	 	$paymentdb['poundage_type']=2;
	 	$paymentdb['type']=1;
	 	$paymentdb['name']=$this->input->get_post('name');
	 	$paymentdb['order']=$this->input->get_post('order');
	 	$paymentdb['description']=$this->input->get_post('description');
	 	$paymentdb['note']=$this->input->get_post('note');
	 	$paymentdb['config ']='array("key"=>"'.$this->input->get_post('key').'","secret"=>"'.$this->input->get_post('secret').'")';
	 	$json ['msg'] = '操作成功';
	 	$this->_initialize ('payment','plugin_id');
	 	if($payment){
	 		$this->loadmodel->update($paymentdb,$pay_id);
	 		$this->user_auth->redirect ('admin/payment',$json ['msg']);
	 	}else{
	 		$paymentdb['plugin_id']=$pay_id;
	 		$this->loadmodel->add($paymentdb);
	 		$this->user_auth->redirect ('admin/payment',$json ['msg']);
	 	}
	 	$this->load->view ( 'admin/payment/alladd');
	 }
	function _get_pay_payment($pid='',$fields='plugin_id'){
		$this->_initialize ('payment',$fields);
		return $this->loadmodel->get_by_id($pid);
		
	} 
	function _get_pay_plugin($config=array(),$pid=0,$row_count=10){
		$this->_initialize ('pay_plugin');
		$data = $this->loadmodel->getpage ( $config,$pid, $row_count, '', '*', 'id', 'ASC' );
		$datalist=$data['datalist'];
		$nd=array();
		foreach ( $datalist as $k => $v ) {
       		$nd[$v['id']]=$v;
		} 
		$data['datalist']=$nd;
		return $data;
	}
	 
} 