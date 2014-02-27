<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Swfupload extends CI_Controller {
	private $file_forder = '';
	function __construct() {
		parent :: __construct();
		$this->load->library('user_auth');
		$this->config->set_item('attachment', 'attachment');
		$this->file_forder =ASSETS. '/' . APPLICATION . '/' . $this->config->item('attachment') . '/' .  date('Y/m/d/');
	}

	public function index() {
		$data['$data'] = '';
		if (isset ($_GET['clos'])) {
			$data['SuccessClose'] = 'parent.SCF();';
		}
		if (isset ($_GET['mfk'])) {
			if (isset ($_GET['mr'])) {
				$fk = $_GET['mfk'] . '(file,' . $_GET['mr'] . ');';
			} else {
				$fk = $_GET['mfk'] . '(file);';
			}
			$data['ParentUploadSuccess'] = 'parent.' . $fk;
		}
		if (isset ($_GET['watermark'])) {
			$data['watermark'] = ',"watermark":"1"';
		} else {
			$data['watermark'] = '';
		}
		$data['swfuploadurl'] = site_url('swfupload/upload');
		$this->auth->systemview('swfupload', $data);
	}
	function upload() {
		$fname = date('Y/m/d/');
		$file_forder = 'attachment/' . $fname;
		$config['upload_path'] = FCPATH . $file_forder;
		$config['allowed_types'] = 'jpg|jpeg|gif|png|bmp|jpe';
		$config['max_size'] = '2048'; //允许上传大小
		$config['file_name'] = $this->auth->guid();
		$this->load->library('upload', $config);
		$field_name = 'Filedata';
		if ($this->upload->do_upload($field_name)) {
			$data = $this->upload->data();
			$updata['filename'] = $fname . $data['file_name'];
			$updata['pid'] = $config['file_name'];
			$updata['datastatus'] = 'success';
			exit ($this->auth->json_encode($updata));
			exit ('FILEID:' . $fname . $data['file_name']);
		} else {
			$updata['errorcode'] = $this->upload->display_errors();
			exit ($this->auth->json_encode($updata));
		}
	}
	function watermark($f, $t = 'Overlay') {
		$this->load->library('image_lib');
		$cf['source_image'] = FCPATH . $f;
		$cf['wm_type'] = 'overlay';
		$cf['wm_vrt_alignment'] = 'bottom';
		$cf['wm_hor_alignment'] = 'right';
		$cf['wm_hor_offset'] = '10px';
		$cf['wm_vrt_offset'] = '5px';
		$cf['wm_overlay_path'] = FCPATH . './skin/images/watermark.png';
		$this->image_lib->initialize($cf);
		return $this->image_lib->watermark();

	}
	function thumbnail() {
		if (isset ($_GET['file'])) {
			exit ($_GET['file']);
		}

	}
	function uploadjson() {
		$file_forder = $this->file_forder;
		$config['upload_path'] = FCPATH . $file_forder;
		$config['allowed_types'] = '*';
		$config['max_size'] = '500000000';
		$config['file_name'] = $this->user_auth->guid();
		$this->load->library('upload', $config);
		$field_name = 'imgFile';
		if ($this->upload->do_upload($field_name)) {
			$data = $this->upload->data();
			$data['error'] = 0;
			$data['url'] = base_url() . $file_forder . $data['file_name'];
			exit ($this->user_auth->json_encode($data));
		} else {
			exit ($this->user_auth->json_encode(array (
				'error' => 1,
				'message' => $this->upload->display_errors() . $config['upload_path']
			)));
		}
	}
	function filemanager() {
		$this->load->library('oauth');
		$settings = $this->loadmodel->settings();
		$sns_url = $settings['sns_url']['value'];
		//$sns_url='http://127.0.0.1:9002/';
		$params['apiUrlHttp'] = $sns_url . 'api/photos/';
		$params['apiUrlHttps'] = $sns_url . 'api/photos/';
		$this->oauth->initialize($params);
		$data = $result = array ();
		$id = $this->input->get_post('id');
		$path = $this->input->get_post('path');
		if ($path) {
			$param['action'] = 'album';
			$param['id'] = $id;
			$r = $this->oauth->api('', $param, 'GET');
			foreach ($r as $k => $v) {
				$v['is_dir'] = false;
				$v['has_file'] = false;
				$v['filename'] = $sns_url . 'api/photos?action=uri&id=' . $v['id'];
				$v['filename'] = $sns_url . 'photos/view/' . $v['id'];
				$v['id'] = $v['id'];
				$v['filesize'] = 0;
				$v['title'] = $v['FTitle'];
				$v['is_photo'] = 1;
				$result[] = $v;
			}
		} else {
			$r = $this->oauth->api('', '');
			foreach ($r as $k => $v) {
				$v['is_dir'] = true;
				$v['has_file'] = true;
				$v['filename'] = $v['FTitle'];
				$v['id'] = $v['id'];
				$v['filesize'] = 0;
				$result[] = $v;
			}
		}
		//相对于根目录的上一级目录
		$data['moveup_dir_path'] = '';
		//相对于根目录的当前目录
		$data['current_dir_path'] = $path;
		//当前目录的URL
		$data['current_url'] = '';
		//文件数
		$data['total_count'] = count($result);
		$data['file_list'] = $result;
		exit ($this->user_auth->json_encode($data));
	}
	function remote_download() {
		$data = $this->input->get_post('datas');
		$template = preg_replace("/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/ies", "remote_download('\\1')", $data);
		echo($template);
	}
	

}