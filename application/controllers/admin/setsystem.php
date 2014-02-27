<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Setsystem extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library('apilibrary');
		$this->load->helper('file');
	}
	function index() {
		$database['hostname']=$this->db->hostname;
		$database['username']=$this->db->username;
		$database['password']=$this->db->password;
		$database['database']=$this->db->database;
		$database['dbprefix']=$this->db->dbprefix;
		$database['dbdriver']=$this->db->dbdriver;
		$database['hostname']=$this->db->hostname;
		$data['datalist']=$this->config->config;
		$data['database']=$database;
		$data['user_auth']=$this->config->item('user_auth');
		$this->load->view('admin/setsystem/setsystem',$data);
	}
	function dopost(){
		$datalist=$this->config->config;
		foreach ( $datalist as $k => $v ) {
       		$p=$this->input->post($k);
       		if($p){
       			$data[$k]=$p;
       		}else{
       			if(!$v){
       				$v=0;
       			}
       			$data[$k]=$v;
       		}
		}
		$fs=$this->load->view('admin/setsystem/config',$data,true);
		$fs='<?php '.$fs;
		$this->user_auth->redirect ('admin/setsystem','操作完成' );
		return '';
		/***
		if(write_file(FCPATH.APPLICATION.'/config/config.php', $fs)){
			$this->user_auth->Messager ( '操作完成', 'setsystem' );
		}else{
			$this->user_auth->Messager ( '数据写入失败', 'setsystem' );
		}
		****/
		
	}
	 
	 
}