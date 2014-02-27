<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Welcome extends CI_Controller {
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
	}
	function index() {
		$data = '';
		$this->load->view('admin/welcome', $data);
	}
	function statistics() {
		$this->_initialize();
		$mresult=$this->loadmodel->getdata(array('select'=>'count(*) as counts'));
		
		$this->_initialize('templates','tid');
		$tresult=$this->loadmodel->getdata(array('select'=>'count(*) as counts'));
		
		$this->_initialize('users','uid');
		$uresult=$this->loadmodel->getdata(array('select'=>'count(*) as counts'));
		//print_R($uresult);exit;
		
		$this->_initialize('category','catid');
		$cresult=$this->loadmodel->getdata(array('select'=>'count(*) as counts'));
		
		$this->_initialize('users_roles','rid');
		$rresult=$this->loadmodel->getdata(array('select'=>'count(*) as counts'));
		
		
		$this->load->library('pcharts');
		$DataSet = new pData;
		 $DataSet->AddPoint(array(1,4,3,4,3,3,2,1,0,7,4,3,2,3,3,5,1,0,7),"Serie1");
		 $DataSet->AddPoint(array(1,4,2,6,2,3,0,1,5,1,2,4,5,2,1,0,6),"Serie2");
		 $DataSet->AddAllSeries();
		 $DataSet->SetAbsciseLabelSerie();
		$DataSet->SetSerieName("用户","Serie1");
		$DataSet->SetSerieName("栏目","Serie2");
		 
		$w = $this->input->get_post('w')?$this->input->get_post('w'):1000;
		$h = $this->input->get_post('h')?$this->input->get_post('h'):400;;
		$p = 5;
		 //echo SCALE_NORMAL;exit;
		 $Test = new pChart($w,$h);
		 //$Test = new pChart(700,230);
		 $Test->setFixedScale(-2,8);
		 $Test->setFontProperties(APPPATH . "fonts/simhei.ttf",8);
		 $Test->setGraphArea(50,30,$w-115,$h-30);
		 $Test->drawFilledRoundedRectangle(7,7,$w-7,$h-7,$p,240,240,240);
		 $Test->drawRoundedRectangle(5,5,$w-5,$h-5,5,230,230,230);
		 $Test->drawGraphArea(255,255,255,TRUE);
		 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
		 $Test->drawGrid(4,TRUE,230,230,230,50);
		
		 // Draw the 0 line
		 $Test->setFontProperties(APPPATH . "fonts/simhei.ttf",6);
		 $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
		
		 // Draw the cubic curve graph
		$Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
		
		$Test->setFontProperties(APPPATH . "fonts/simhei.ttf", 12);
 		$Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1"); 
 		$Test->writeValues($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie2"); 
 		
		 // Finish the graph
		 $Test->setFontProperties(APPPATH . "fonts/simhei.ttf",8);
		 $Test->drawLegend($w-100,30,$DataSet->GetDataDescription(),255,255,255);
		 $Test->setFontProperties(APPPATH . "fonts/simhei.ttf",10);
		 $Test->drawTitle(50,22,"数据统计",50,50,50,585);
		 
		 
		$Test->Stroke();
	}
	
}