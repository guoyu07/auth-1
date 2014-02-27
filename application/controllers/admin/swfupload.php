<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Swfupload extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library ( 'json' );
	}
	public function index() {
		$data ['$data'] = '';
		if (isset ( $_GET ['clos'] )) {
			$data ['SuccessClose'] = 'parent.SCF();';
		}
		if (isset ( $_GET ['mfk'] )) {
			if (isset ( $_GET ['mr'] )) {
				$fk = $_GET ['mfk'] . '(file,' . $_GET ['mr'] . ');';
			} else {
				$fk = $_GET ['mfk'] . '(file);';
			}
			$data ['ParentUploadSuccess'] = 'parent.' . $fk;
		}
		if (isset ( $_GET ['watermark'] )) {
			$data ['watermark'] = ',"watermark":"1"';
		} else {
			$data ['watermark'] = '';
		}
		$data['swfuploadurl']=site_url('swfupload/upload');
		$this->load->view ( 'admin/swfupload', $data );
	}
	function upload() {
		$fname = date ( 'Y/m/d/' );
		$file_forder = 'attachment/' . $fname;
		$config ['upload_path'] = FCPATH . $file_forder;
		$config ['allowed_types'] = 'jpg|jpeg|gif|png|bmp|jpe';
		$config ['max_size'] = '2048'; //允许上传大小
		$config ['file_name'] = $this->auth->guid ();
		$this->load->library ( 'upload', $config );
		$field_name = 'Filedata';
		if ($this->upload->do_upload ( $field_name )) {
			$data = $this->upload->data ();
			if (isset ( $_POST ['watermark'] )) {
				$this->watermark ( $fname . $data ['file_name'] );
			}
			$updata ['filename'] = $fname . $data ['file_name'];
			$updata ['pid'] = $config ['file_name'];
			$updata ['datastatus'] = 'success';
			exit ( $this->json->encode ( $updata ) );
			exit ( 'FILEID:' . $fname . $data ['file_name'] );
		} else {
			echo $this->upload->display_errors ();
			exit ( 0 );
		}
	}
	function watermark($f, $t = 'Overlay') {
		$this->load->library ( 'image_lib' );
		$cf ['source_image'] = FCPATH . $f;
		$cf ['wm_type'] = 'overlay';
		$cf ['wm_vrt_alignment'] = 'bottom';
		$cf ['wm_hor_alignment'] = 'right';
		$cf ['wm_hor_offset'] = '10px';
		$cf ['wm_vrt_offset'] = '5px';
		$cf ['wm_overlay_path'] = FCPATH . './skin/images/watermark.png';
		$this->image_lib->initialize ( $cf );
		return $this->image_lib->watermark ();
	
	}
	function thumbnail() {
		if (isset ( $_GET ['file'] )) {
			exit ( $_GET ['file'] );
		}
	
	}
	function uploadjson() { 
		$file_forder = 'attachment/' . date ( 'Y/m/d/' ); //文件目录  
		$config ['upload_path'] = FCPATH . $file_forder; //文件保存路径  这儿我用的是实际路径
		$config ['allowed_types'] = 'jpg|jpeg|gif|png|bmp|jpe'; //允许上传格式
		$config ['max_size'] = '2048'; //允许上传大小
		$config ['file_name'] = $this->auth->guid (); //存放的文件名 
		$this->load->library ( 'upload', $config );
		$field_name = 'imgFile'; //上传表单字段名 
		if ($this->upload->do_upload ( $field_name )) {
			$data = $this->upload->data ();
			if (isset ( $_GET ['watermark'] )) {
				$this->watermark ( $file_forder . $data ['file_name'] );
			}
			
			exit ( $this->json->encode ( array ('error' => 0, 'url' => base_url () . $file_forder . $data ['file_name'] ) ) );
		} else {
			print_r ( $this->upload );
			exit ( $this->json->encode ( array ('error' => 1, 'message' => $this->upload->display_errors () . $config ['upload_path'] ) ) );
			exit ( $this->json->encode ( array ('error' => 1, 'message' => $config ['upload_path'] ) ) );
		}
	}

}
