<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

class Stsyemlog extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->library('user_auth');
		$this->user_auth->check_uri_permissions ('admin');
		$this->load->library ( 'apilibrary' );
		$this->loadmodel->_initialize($this->config->item('user_auth_users_stsyemlog_table', 'user_auth'));
	}
	
	function truncate($ajax = NULL) {
		$this->db->truncate('users_stsyemlog'); 
		isset ( $_POST ['vurl'] ) ? ($json ['vurl'] = $_POST ['vurl']) : '';
		$json ['status'] = 'success';
		$json ['msg'] = '所有系统日志已清空';
		$json ['doajax'] = ($ajax == 'ajax') ? 1 : NULL;
		return $this->apilibrary->set_output ( $json );
	}
	/**
	 * 主页
	 * Enter description here ...
	 */
	function index($pid = 0) {
		$data ['uid'] = '';
		$p_config ['base_url'] = 'admin/stsyemlog/index';
		$data = $this->loadmodel->getpage ( $p_config, $pid, 10, '', '*', 'datetime', 'DESC' );
		$this->load->view ( 'admin/stsyemlog/welcome', $data );
	}
	function iframe() {
		extract($_GET);
		$data['subject'] = isset ($subject) ? $subject : '';
		$data['starttime'] = isset ($starttime) ? $starttime : '';
		$data['stoptime'] = isset ($stoptime) ? $stoptime : '';
		$data['username'] = isset ($username) ? $username : '';
		$data['uid'] = isset ($uid) ? $uid : '';
		$data['action'] = 'admin/stsyemlog';
		$this->load->view('admin/stsyemlog/search_iframe', $data);
	}
	function delete($id = null) {
		$column = $this->loadmodel->get_by_id ( $id );
		$json ['status'] = 'failure';
		if ($column) {
			if ($this->loadmodel->delete ( $id )) {
				$json ['msg'] = '删除成功';
				$json ['status'] = 'success';
			} else {
				$json ['msg'] = '删除失败';
			}
		} else {
			$json ['msg'] = '数据不存在';
		}
		isset ( $_POST ['vurl'] ) ? ($json ['vurl'] = $_POST ['vurl']) : '';
		return $this->apilibrary->set_output ( $json );
	}
	function detailed($id=null,$do = NULL){
		$detailed = $this->loadmodel->get_by_field ( $id );
		$data['detailed']=$detailed;
		$data['detailed']['location'] = iconv('gbk','utf-8',$this->convertip($detailed['ip']));
		$data ['ajax'] = $do;
		$this->load->view ( 'admin/stsyemlog/detailed', $data ); 
	}
		function convertip($ip) {
		$ip1num = 0;
		$ip2num = 0;
		$ipAddr1 = "";
		$ipAddr2 = "";
		$dat_path = './assets/themes/default/data/qqwry.dat';
		if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
			return 'IP Address Error';
		}
		if (!$fd = @ fopen($dat_path, 'rb')) {
			return 'IP date file not exists or access denied';
		}
		$ip = explode('.', $ip);
		$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];
		$DataBegin = fread($fd, 4);
		$DataEnd = fread($fd, 4);
		$ipbegin = implode('', unpack('L', $DataBegin));
		if ($ipbegin < 0)
			$ipbegin += pow(2, 32);
		$ipend = implode('', unpack('L', $DataEnd));
		if ($ipend < 0)
			$ipend += pow(2, 32);
		$ipAllNum = ($ipend - $ipbegin) / 7 + 1;
		$BeginNum = 0;
		$EndNum = $ipAllNum;
		while ($ip1num > $ipNum || $ip2num < $ipNum) {
			$Middle = intval(($EndNum + $BeginNum) / 2);
			fseek($fd, $ipbegin +7 * $Middle);
			$ipData1 = fread($fd, 4);
			if (strlen($ipData1) < 4) {
				fclose($fd);
				return 'System Error';
			}
			$ip1num = implode('', unpack('L', $ipData1));
			if ($ip1num < 0)
				$ip1num += pow(2, 32);

			if ($ip1num > $ipNum) {
				$EndNum = $Middle;
				continue;
			}
			$DataSeek = fread($fd, 3);
			if (strlen($DataSeek) < 3) {
				fclose($fd);
				return 'System Error';
			}
			$DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
			fseek($fd, $DataSeek);
			$ipData2 = fread($fd, 4);
			if (strlen($ipData2) < 4) {
				fclose($fd);
				return 'System Error';
			}
			$ip2num = implode('', unpack('L', $ipData2));
			if ($ip2num < 0)
				$ip2num += pow(2, 32);
			if ($ip2num < $ipNum) {
				if ($Middle == $BeginNum) {
					fclose($fd);
					return 'Unknown';
				}
				$BeginNum = $Middle;
			}
		}
		$ipFlag = fread($fd, 1);
		if ($ipFlag == chr(1)) {
			$ipSeek = fread($fd, 3);
			if (strlen($ipSeek) < 3) {
				fclose($fd);
				return 'System Error';
			}
			$ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
			fseek($fd, $ipSeek);
			$ipFlag = fread($fd, 1);
		}
		if ($ipFlag == chr(2)) {
			$AddrSeek = fread($fd, 3);
			if (strlen($AddrSeek) < 3) {
				fclose($fd);
				return 'System Error';
			}
			$ipFlag = fread($fd, 1);
			if ($ipFlag == chr(2)) {
				$AddrSeek2 = fread($fd, 3);
				if (strlen($AddrSeek2) < 3) {
					fclose($fd);
					return 'System Error';
				}
				$AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
				fseek($fd, $AddrSeek2);
			} else {
				fseek($fd, -1, SEEK_CUR);
			}
			while (($char = fread($fd, 1)) != chr(0))
				$ipAddr2 .= $char;
			$AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
			fseek($fd, $AddrSeek);
			while (($char = fread($fd, 1)) != chr(0))
				$ipAddr1 .= $char;
		} else {
			fseek($fd, -1, SEEK_CUR);
			while (($char = fread($fd, 1)) != chr(0))
				$ipAddr1 .= $char;
			$ipFlag = fread($fd, 1);
			if ($ipFlag == chr(2)) {
				$AddrSeek2 = fread($fd, 3);
				if (strlen($AddrSeek2) < 3) {
					fclose($fd);
					return 'System Error';
				}
				$AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
				fseek($fd, $AddrSeek2);
			} else {
				fseek($fd, -1, SEEK_CUR);
			}
			while (($char = fread($fd, 1)) != chr(0)) {
				$ipAddr2 .= $char;
			}
		}
		fclose($fd);
		if (preg_match('/http/i', $ipAddr2)) {
			$ipAddr2 = '';
		}
		$ipaddr = "$ipAddr1 $ipAddr2";
		$ipaddr = preg_replace('/CZ88.NET/is', '', $ipaddr);
		$ipaddr = preg_replace('/^s*/is', '', $ipaddr);
		$ipaddr = preg_replace('/s*$/is', '', $ipaddr);
		if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
			$ipaddr = 'Unknown';
		}
		return $ipaddr;
	}
	 

} 