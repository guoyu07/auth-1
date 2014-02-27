<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Test extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');
		$this->load->library('security');
	}
	function index(){
		echo preg_replace("/^(.+?);.*$/", "\\1", 'cueb/images/tu1.jpg');
		echo site_url('admin/test/bdrt');
		
	}
	function bdrt(){
		$fn='toauth';
		$lib = $fn . '_lib';
		$this->load->library('oauth/' . $lib);
		$this-> $lib->start();
		
	}
	function indexdd() {
		$this->load->library('word');
		$data='';
		$html =$this->load->view('admin/test/word', $data,true);
		for ($i = 1; $i <= 3; $i++) {
			$this->word->start(); 
			$wordname =  $i . ".doc";
			echo $html;
			$this->word->save($wordname);
			ob_flush(); //每次执行前刷新缓存 
			flush();
		}
		
		//echo date('Y-m-d H:i:s', 1371390848);
	}
	function db($dbs=''){
		
		$this->output->enable_profiler(TRUE);
		$database=$this->db->database;
		$dbs2=$this->db->get($dbs)->result_array();
		$fields = $this->db->list_fields($dbs);
		 print_R($fields);
		//$this->db->reconnect();
		//$this->load->database='web';
		$db1=$this->load->database('web',TRUE);
		$dbs1=$db1->get($dbs)->result_array();
		
		$db2=$this->load->database('default',TRUE);
		foreach($dbs1 as $k => $v){
			foreach($fields as $ks => $vs){
				$ndb[$vs]=$v[$vs];
			}
			$db2->insert($dbs, $ndb);
		}
		
		
	}
}