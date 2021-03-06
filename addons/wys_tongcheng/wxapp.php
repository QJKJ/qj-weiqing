﻿<?php

defined('IN_IA') or exit('Access Denied');
require_once dirname(__FILE__) . '/core/db.class.php';
require_once dirname(__FILE__) . '/core/function_common.class.php';
class Wys_tongchengModuleWxapp extends WeModuleWxapp
{
	public function doPageTx_info_add()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$setting = $this->module['config'];
		$tx_sh_isopen = $setting['tx_sh_isopen'];
		if ($tx_sh_isopen == '1') {
			$tx_sh_isopen = 0;
		} else {
			if ($tx_sh_isopen == '0') {
				$tx_sh_isopen = 1;
			} else {
				$tx_sh_isopen = 0;
			}
		}
		$user_det = $DBUtil->getOne('wys_tongcheng_user', 'u_openid=:u_openid', array(':u_openid' => $_GPC['u_openid']));
		$oncode = time() . $myfun->randombylength(8);
		$data_add = array('uniacid' => $_W['uniacid'], 'oncode' => $oncode, 'u_openid' => $_GPC['u_openid'], 'u_unionid' => $_GPC['u_unionid'], 'u_nickname' => $_GPC['u_nickname'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'u_phone' => $user_det['u_phone'], 'accout' => $_GPC['account'], 'money' => $_GPC['money'], 'money_sj' => $_GPC['money_sj'], 'money_sxf' => $_GPC['money_sxf'], 'money_sxfl' => $_GPC['money_sxfl'], 'formID' => $_GPC['formID'], 'crtime' => time(), 'status_tx' => '');
		$data_add['enable'] = $tx_sh_isopen;
		if (floatval($_GPC['money']) > floatval($_GPC['account'])) {
			$rpdata['tx_status'] = false;
			$rpdata['tx_status_str'] = 1;
		} else {
			$rpdata['tx_status'] = true;
			$DBUtil->save('wys_tongcheng_user_account_tx', $data_add);
			$account = floatval($user_det['account']) - floatval($_GPC['money']);
			$DBUtil->update('wys_tongcheng_user', array('account' => $account), array('id' => $user_det['id']));
			if ($tx_sh_isopen == '1') {
				$apiData = $myfun->api_transfers($_GPC['u_openid'], $_GPC['u_nickname'], $_GPC['money_sj'], $oncode);
				$rpdata['apidata'] = $apiData;
			}
			$rpdata['tx_sh_isopen'] = $tx_sh_isopen;
		}
		$errno = 0;
		$message = 'rp_textCC';
		return $this->result($errno, $message, $rpdata);
	}
	public function doPageGet_msg_refresh_status()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$status_det = $DBUtil->getOne('wys_tongcheng_status', 'uniacid=:uniacid and stype=:stype', array(':uniacid' => $_W['uniacid'], ':stype' => 'msg'));
		if (empty($status_det)) {
			$refresh_status = 0;
		} else {
			if ($status_det['status'] == '1') {
				$refresh_status = 1;
			} else {
				$refresh_status = 0;
			}
		}
		$errno = 0;
		$message = 'rp_textCC';
		$data = array('refresh_status' => $refresh_status);
		return $this->result($errno, $message, $data);
	}
	public function add_msg_status()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$status_det = $DBUtil->getOne('wys_tongcheng_status', 'uniacid=:uniacid and stype=:stype', array(':uniacid' => $_W['uniacid'], ':stype' => 'msg'));
		if (empty($status_det)) {
			$add_status = array('uniacid' => $_W['uniacid'], 'stype' => 'msg', 'status' => '1', 'crtime' => time(), 'modtime' => time());
			$DBUtil->save('wys_tongcheng_status', $add_status);
		} else {
			$add_status = array('uniacid' => $_W['uniacid'], 'stype' => 'msg', 'status' => '1', 'modtime' => time());
			$DBUtil->update('wys_tongcheng_status', $add_status, array('id' => $status_det['id']));
		}
	}
	public function doPageCheck_smrzbyuser()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$setting = $this->module['config'];
		if ($setting['smrz_isopen'] == '0') {
			$data['smrz_isopen'] = $setting['smrz_isopen'];
		} else {
			$openid = $_GPC['openId'];
			if ($openid == '') {
				$openid = $_W['openid'];
			}
			$param = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
			$smrz_det = $DBUtil->getOne('wys_tongcheng_smrz', 'uniacid=:uniacid and openid=:openid', $param);
			if (empty($smrz_det)) {
				$data = array('smrz_state' => false, 'smrz_txt' => '没有实名资料', 'audit_status' => '');
			} else {
				if ($smrz_det['audit_status'] == '1') {
					$data = array('smrz_state' => true, 'smrz_txt' => '实名已审核通过!', 'audit_status' => '1');
				} else {
					if ($smrz_det['audit_status'] == '0') {
						$data = array('smrz_state' => true, 'smrz_txt' => '实名已提交未审核!', 'audit_status' => '0');
					} else {
						if ($smrz_det['audit_status'] == '2') {
							$data = array('smrz_state' => false, 'smrz_txt' => '实名审核驳回!', 'audit_status' => '2');
						}
					}
				}
			}
			$data['smrz_isopen'] = $setting['smrz_isopen'];
		}
		$errno = 0;
		$message = 'OK';
		return $this->result($errno, $message, $data);
	}
	public function doPageCheck_smrzopen()
	{
		$setting = $this->module['config'];
		$errno = 0;
		$message = 'OK';
		$data = array('smrz_isopen' => $setting['smrz_isopen']);
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_smrz()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$openid = $_GPC['openId'];
		if ($openid == '') {
			$openid = $_W['openid'];
		}
		$param = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
		$smrz_det = $DBUtil->getOne('wys_tongcheng_smrz', 'uniacid=:uniacid and openid=:openid', $param);
		$errno = 0;
		$message = 'OK';
		return $this->result($errno, $message, $smrz_det);
	}
	public function doPageSmrz_submit()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$openid = $_GPC['openId'];
		if ($openid == '') {
			$openid = $_W['openid'];
		}
		$param = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
		$smrz_det = $DBUtil->getOne('wys_tongcheng_smrz', 'uniacid=:uniacid and openid=:openid', $param);
		if (empty($smrz_det)) {
			$add_data = array('uniacid' => $_W['uniacid'], 'openid' => $_GPC['openId'], 'p_name' => $_GPC['p_name'], 'p_sfid' => $_GPC['p_sfid'], 'p_phone' => $_GPC['p_phone'], 'formID' => $_GPC['formId'], 'audit_status' => '0', 'crtime' => time());
			$res = $DBUtil->save('wys_tongcheng_smrz', $add_data);
			$dotype = 'add';
		} else {
			$add_data = array('p_name' => $_GPC['p_name'], 'p_sfid' => $_GPC['p_sfid'], 'p_phone' => $_GPC['p_phone'], 'formID' => $_GPC['formId'], 'audit_status' => '0', 'crtime' => time());
			$res = $DBUtil->update('wys_tongcheng_smrz', $add_data, array('uniacid' => $_W['uniacid'], 'openid' => $_GPC['openId']));
			$dotype = 'edit';
		}
		$data = array('dostatus' => $res, 'dotype' => $dotype);
		$errno = 0;
		$message = 'OK';
		return $this->result($errno, $message, $data);
	}
	public function doPageSmrz_submit_imgs()
	{
		global $_GPC, $_W;
		$errno = 0;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$opneid = $_GPC['openId'];
		$img_type = $_GPC['imgtype'];
		$h_msg_det = $DBUtil->getOne('wys_tongcheng_smrz', 'uniacid=:uniacid and openid=:openid', array(':uniacid' => $_W['uniacid'], ':openid' => $opneid));
		$date_info = getdate();
		$year = $date_info['year'];
		$mon = $date_info['mon'];
		$day = $date_info['mday'];
		$logo_path = 'images/wys_tongcheng/uniacid_' . $_W['uniacid'] . '/smrz' . sprintf("%s/%s/%s/%s/", $logo_path, $year, $mon, $day);
		$uplogo_path = ATTACHMENT_ROOT . $logo_path;
		if (!is_dir($uplogo_path)) {
			$res = mkdir($uplogo_path, 0777, true);
		}
		$img_file_name = time() . $myfun->randombylength(8) . '.' . $myfun->fileext($_FILES['file']['name']);
		if (move_uploaded_file($_FILES['file']['tmp_name'], $uplogo_path . $img_file_name)) {
			$restult = true;
		} else {
			$restult = false;
		}
		$webimgurl = tomedia($logo_path . $img_file_name);
		$message = 'img upload suc';
		$data = array('oncode' => $oncode, 'isyun' => $webimgurl);
		if ($img_type == 'img1') {
			$update_data = array('img1' => $webimgurl);
		} else {
			if ($img_type == 'img2') {
				$update_data = array('img2' => $webimgurl);
			} else {
				if ($img_type == 'img3') {
					$update_data = array('img3' => $webimgurl);
				}
			}
		}
		$DBUtil->update('wys_tongcheng_smrz', $update_data, array('uniacid' => $_W['uniacid'], 'openid' => $opneid));
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_wxphone()
	{
		require_once dirname(__FILE__) . '/inc/function/wx_aes/wxBizDataCrypt.php';
		global $_GPC, $_W;
		$ekey = $_GPC['skey'];
		$edate = $_GPC['edate'];
		$eiv = $_GPC['eiv'];
		$pc = new WXBizDataCrypt($_W['account']['key'], $ekey);
		$errCode = $pc->decryptData($edate, $eiv, $rdata);
		if ($errCode == 0) {
			$data = array('gstage' => 1, 'rdata' => $rdata);
		} else {
			$data = array('gstage' => 0, 'rdata' => $errCode);
		}
		$errno = 0;
		$message = 'OK';
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_copyright()
	{
		$setting = $this->module['config'];
		$errno = 0;
		$message = 'OK';
		$data = array('pay_isopen' => $setting['pay_isopen'], 'account_isopen' => $setting['account_isopen'], 'kefu_isopen' => $setting['kefu_isopen'], 'integral_isopen' => $setting['integral_isopen'], 'copy_right_title' => $setting['copy_right_title'], 'copy_right_phone' => $setting['copy_right_phone'], 'banner_sale_title' => $setting['banner_sale_title']);
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_pmd()
	{
		$setting = $this->module['config'];
		$errno = 0;
		$message = 'OK';
		$run_pmd_text = $setting['run_pmd_text'];
		$strs = explode('#', $run_pmd_text);
		$msg_list = array();
		foreach ($strs as $key => $str) {
			array_push($msg_list, array('title' => $str, 'fontsize' => $this->txt_length_fontsize($str)));
		}
		if (count($strs) == 1) {
			array_push($msg_list, array('title' => $run_pmd_text, 'fontsize' => $this->txt_length_fontsize($run_pmd_text)));
		}
		$data = array('run_pmd_isopen' => $setting['run_pmd_isopen'], 'run_pmd_text' => $setting['run_pmd_text'], 'pmd_list' => $msg_list);
		return $this->result($errno, $message, $data);
	}
	function txt_length_fontsize($text)
	{
		$len = 0;
		if (mb_strlen($text) <= 20) {
			$len = 30;
		}
		if (mb_strlen($text) > 20) {
			$len = 24;
		}
		if (mb_strlen($text) > 25) {
			$len = 22;
		}
		if (mb_strlen($text) >= 30) {
			$len = 18;
		}
		return $len;
	}
	public function doPageGet_userinfo()
	{
		global $_GPC, $_W;
		$myfun = new MyFun();
		$data = $myfun->getUserinfo_openid($_W['account']['key'], $_W['account']['secret'], $_GPC['code']);
		$errno = 0;
		$message = function_exists('file_get_contents');
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_mtype_info()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$data = $DBUtil->getOne('wys_tongcheng_mtype', 'id=:id', array(':id' => $_GPC['bid']));
		if (!empty($data['rd_tw_imglist']) && $data['rd_tw_imglist'] != '' && $data['rd_tw_imglist'] != null && $data['rd_tw_imglist'] != 'null') {
			$data['rd_tw_imglist'] = json_encode(explode(',', $data['rd_tw_imglist']));
		}
		$data['crtime'] = $myfun->friendlyDate($data['crtime'], 'mohu_ot');
		$data['img'] = '';
		$errno = 0;
		$message = 'load doPageGet_mtype_info';
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_mtype_list_byid()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$m_typeid = $_GPC['typeid'];
		$mtype = $DBUtil->getOne('wys_tongcheng_mtype', 'id=:id', array(':id' => $m_typeid));
		if ($mtype['is_parent_open'] == '1') {
			$psql = 'uniacid=:uniacid and enable=:enable and attr=:attr and type=:type';
			$psql_data = array(':uniacid' => $_W['uniacid'], ':enable' => '1', ':attr' => $m_typeid, ':type' => 0);
			$rpdata = $DBUtil->getMany('wys_tongcheng_mtype', $psql, $psql_data, $order);
		} else {
			$rpdata = array();
		}
		$errno = 0;
		$message = 'load parent';
		return $this->result($errno, $message, $rpdata);
	}
	public function doPageGet_Mtype_List()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$setting = $this->module['config'];
		$indexmtype_isopen = $setting['indexmtype_isopen'];
		$h_tb = $DBUtil::wys_tongcheng_mtype;
		$referenceType = $_GPC['referenceType'];
		$order = ' pxh asc';
		$messageObj = array('referenceType' => $referenceType);
		if ($referenceType == 'index') {
			if (!empty($indexmtype_isopen) && $indexmtype_isopen == 1) {
				$messageObj['indexmtype_isopen'] = 1;
				$where = 'uniacid=:uniacid and show_index=:show_index and enable=:enable and attr=:attr';
				$param = array(':uniacid' => $_W['uniacid'], ':show_index' => 1, ':enable' => '1', ':attr' => 0);
				$mpage = 8;
				$mtype_allcnt = $DBUtil->getCount($h_tb, $where, $param);
				$mtype_fy_allpage = ceil($mtype_allcnt / $mpage);
				$messageObj['mtype_fy_allpage'] = $mtype_fy_allpage;
				$messageObj['mtype_fy_isdots'] = $mtype_fy_allpage > 1 ? 1 : 0;
				$page_arr = array();
				for ($i = 0; $i < $mtype_fy_allpage; $i++) {
					$list = $DBUtil->getMany($h_tb, $where, $param, $order, $i + 1, $mpage);
					foreach ($list as $key => &$value) {
						$value['img'] = $_W['attachurl'] . $value['img'];
						if ($value['is_parent_open'] == '1') {
							$psql = 'uniacid=:uniacid and enable=:enable and attr=:attr';
							$psql_data = array(':uniacid' => $_W['uniacid'], ':enable' => '1', ':attr' => $value['id']);
							$value['parent_types'] = $DBUtil->getMany($h_tb, $psql, $psql_data, $order);
						}
					}
					$add_arr = array('page_index' => $i, 'page_data' => $list);
					array_push($page_arr, $add_arr);
				}
				$data = $page_arr;
			} else {
				$messageObj['indexmtype_isopen'] = 0;
				$where = 'uniacid=:uniacid and show_index=:show_index and enable=:enable and attr=:attr';
				$param = array(':uniacid' => $_W['uniacid'], ':show_index' => 1, ':enable' => '1', ':attr' => 0);
				$list = $DBUtil->getMany($h_tb, $where, $param, $order);
				$errno = 0;
				$data = $list;
				foreach ($data as $key => &$value) {
					$value['img'] = $_W['attachurl'] . $value['img'];
					if ($value['is_parent_open'] == '1') {
						$psql = 'uniacid=:uniacid and enable=:enable and attr=:attr';
						$psql_data = array(':uniacid' => $_W['uniacid'], ':enable' => '1', ':attr' => $value['id']);
						$value['parent_types'] = $DBUtil->getMany($h_tb, $psql, $psql_data, $order);
					}
				}
			}
		} else {
			if ($referenceType == 'sendChooseType') {
				$where = 'uniacid=:uniacid and enable=:enable and attr=:attr and type=:type';
				$param = array(':uniacid' => $_W['uniacid'], ':enable' => '1', ':attr' => 0, ':type' => 0);
				$list = $DBUtil->getMany($h_tb, $where, $param, $order);
				$errno = 0;
				$data = $list;
				foreach ($data as $key => &$value) {
					$value['img'] = $_W['attachurl'] . $value['img'];
					if ($value['is_parent_open'] == '1') {
						$psql = 'uniacid=:uniacid and enable=:enable and attr=:attr and type=:type';
						$psql_data = array(':uniacid' => $_W['uniacid'], ':enable' => '1', ':attr' => $value['id'], ':type' => 0);
						$value['parent_types'] = $DBUtil->getMany($h_tb, $psql, $psql_data, $order);
					}
				}
			}
		}
		$no_openid_shang = $DBUtil->getMany('wys_tongcheng_payorder', 'ordertype=:ordertype and openid_y=:openid_y', array(':openid_y' => '', ':ordertype' => 'shang'));
		foreach ($no_openid_shang as $key => $nopen_shang) {
			$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'id=:id', array(':id' => $nopen_shang['dt_id']));
			if (!empty($msg_det)) {
				$DBUtil->update('wys_tongcheng_payorder', array('openid_y' => $msg_det['u_openid']), array('id' => $nopen_shang['id']));
			}
		}
		$up_order_data = array(':ordertype' => 'shang', ':user_money' => 0);
		$up_order_list = $DBUtil->getMany('wys_tongcheng_payorder', 'ordertype=:ordertype and user_money=:user_money', $up_order_data);
		foreach ($up_order_list as $key => $it) {
			$user_money = floatval($it['total_money']) - floatval($it['system_money']);
			$str_rmk = str_replace($user_money . "元", '', $it['orderrmk']);
			$up_date = array('user_money' => $user_money, 'orderrmk' => $str_rmk);
			$DBUtil->update('wys_tongcheng_payorder', $up_date, array('id' => $it['id']));
		}
		return $this->result($errno, $messageObj, $data);
	}
	public function doPageBanner_sale()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$oncode = time() . $myfun->randombylength(8);
		$userinfo = $DBUtil->getOne('wys_tongcheng_user', 'u_openid=:u_openid', array(':u_openid' => $_GPC['openid']));
		$add_data = array('uniacid' => $_W['uniacid'], 'oncode' => $oncode, 'openid' => $_GPC['openid'], 'unionid' => $_GPC['unionid'], 'bn_id' => $_GPC['bn_id'], 'bn_btypename' => $_GPC['bn_btypename'], 'bn_daymoney' => $_GPC['bn_daymoney'], 'bn_days' => $_GPC['bn_days'], 'bn_total_money' => $_GPC['bn_total_money'], 'm_id' => $_GPC['m_id'], 'bn_pxh' => $_GPC['bn_pxh'], 'paystate' => '0', 'formId' => $_GPC['formId'], 'crtime' => time(), 'audit_status' => 0, 'lasttime' => $myfun->doDate('minute', '+', 60), 'u_nickname' => $userinfo['u_nickname'], 'u_city' => $userinfo['u_city'], 'u_avatarurl' => $userinfo['u_avatarurl'], 'u_phone' => $userinfo['u_phone'], 'u_address' => $userinfo['u_address']);
		$res = $DBUtil->save('wys_tongcheng_salebanner', $add_data);
		$rpdata = array('oncode' => $oncode, 'res' => $res, 'ordermoney' => $_GPC['bn_total_money']);
		$errno = 0;
		$message = 'save suc';
		return $this->result($errno, $message, $rpdata);
	}
	public function doPageBanner_imgs()
	{
		global $_GPC, $_W;
		$errno = 0;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$oncode = $_GPC['oncode'];
		$h_msg_det = $DBUtil->getOne('wys_tongcheng_salebanner', 'uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $oncode));
		$date_info = getdate();
		$year = $date_info['year'];
		$mon = $date_info['mon'];
		$day = $date_info['mday'];
		$logo_path = 'images/wys_tongcheng/uniacid_' . $_W['uniacid'] . sprintf("%s/%s/%s/%s/", $logo_path, $year, $mon, $day);
		$uplogo_path = ATTACHMENT_ROOT . $logo_path;
		if (!is_dir($uplogo_path)) {
			$res = mkdir($uplogo_path, 0777, true);
		}
		$img_file_name = time() . $myfun->randombylength(8) . '.' . $myfun->fileext($_FILES['file']['name']);
		if (move_uploaded_file($_FILES['file']['tmp_name'], $uplogo_path . $img_file_name)) {
			$restult = true;
		} else {
			$restult = false;
		}
		$webimgurl = tomedia($logo_path . $img_file_name);
		$message = 'img upload suc';
		$data = array('oncode' => $oncode, 'isyun' => $webimgurl);
		$img_mod = array('uniacid' => $_W['uniacid'], 'oncode' => $oncode, 'imgpath' => $webimgurl, 'crtime' => time());
		$update_data = array('img' => $webimgurl);
		$DBUtil->update('wys_tongcheng_salebanner', $update_data, array('oncode' => $oncode));
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_banner_byuser()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$h_msg = $DBUtil::wys_tongcheng_salebanner;
		$page = max(1, $_GPC['page']);
		$setting = $this->module['config'];
		if (!empty($setting['page_size'])) {
			if ($setting['page_size'] == 0) {
				$pagesize = $setting['page_size'];
			} else {
				$pagesize = 10;
			}
		} else {
			$pagesize = 10;
		}
		$where = 'uniacid=:uniacid and openid=:openid';
		$params = array(':openid' => $_GPC['openid'], ':uniacid' => $_W['uniacid']);
		$order = ' audit_status asc,paystate desc,crtime desc';
		$rpdata = $DBUtil->getMany($h_msg, $where, $params, $order, $page, $pagesize);
		foreach ($rpdata as $key => &$value) {
			$value['crtime'] = $myfun->friendlyDate($value['crtime'], 'full');
			$value['dopaystatus'] = intval($value['lasttime']) > time();
			$value['lasttime'] = $myfun->friendlyDate($value['lasttime'], 'full');
			if ($value['audit_status'] == '1') {
				$value['banner_lasttime'] = $myfun->friendlyDate($value['banner_lasttime'], 'full');
			}
		}
		$errno = 0;
		$message = 'load suc';
		return $this->result($errno, $message, $rpdata);
	}
	public function doPageDel_banner()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$MyFun = new MyFun();
		$id = $_GPC['bnid'];
		$sale_banner = $DBUtil->getOne('wys_tongcheng_salebanner', ' uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $id));
		if ($sale_banner['audit_status'] == '1') {
			$update_banner = array('sale_status' => 0, 'sale_openid' => '', 'days' => 0, 'lastdate' => $MyFun->doDate('day', '+', 30), 'mid' => '', 'msg_type' => 0);
			$DBUtil->update('wys_tongcheng_banner', $update_banner, array('id' => $sale_banner['bn_id']));
			$DBUtil->update('wys_tongcheng_msg', array('banner_sale' => 0), array('id' => $sale_banner['m_id']));
		}
		$res = $DBUtil->delete('wys_tongcheng_salebanner', array('uniacid' => $_W['uniacid'], 'id' => $id));
		$errno = 0;
		$message = 'load suc';
		return $this->result($errno, $message, $res);
	}
	public function doPageMsg_top()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$mid = $_GPC['mid'];
		$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'id=:id', array(':id' => $mid));
		$update_data = array('payStatus' => '0', 'is_placed_top' => $_GPC['istop'], 'placed_top_days' => $_GPC['topdays'], 'placed_top_money' => $_GPC['money'], 'placed_top_duedate' => $myfun->doDate('day', '+', intval($_GPC['topdays']), time()), 'total_money' => floatval($msg_det['default_money']) + floatval($_GPC['money']));
		$res = $DBUtil->update('wys_tongcheng_msg', $update_data, array('id' => $mid));
		$errno = 0;
		$message = 'load suc';
		return $this->result($errno, $message, $res);
	}
	public function doPageMsg_send()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$setting = $this->module['config'];
		$oncode = time() . $myfun->randombylength(8);
		$h_msg = $DBUtil::wys_tongcheng_msg;
		$h_mtype = $DBUtil::wys_tongcheng_mtype;
		$user = $DBUtil->getOne($DBUtil::wys_tongcheng_user, 'u_openid=:u_openid', array(':u_openid' => $_GPC['u_openid']));
		if (empty($user) || $user['is_black'] != '1') {
			$h_mtype_det = $DBUtil->getOne($h_mtype, 'uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $_GPC['tid']));
			if ($_GPC['parent_tid'] != '0') {
				$h_mtype_det_parent = $DBUtil->getOne($h_mtype, 'uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $_GPC['parent_tid']));
				$default_fatie_money = $h_mtype_det_parent['send_money'];
			} else {
				$h_mtype_det_parent['tname'] = '';
				$default_fatie_money = $h_mtype_det['send_money'];
			}
			$mod_data = array('uniacid' => $_W['uniacid'], 'oncode' => $oncode, 'tid' => $_GPC['tid'], 'tname' => $h_mtype_det['tname'], 'parent_tid' => $_GPC['parent_tid'], 'parent_tname' => $h_mtype_det_parent['tname'], 'content' => $myfun->textEncode($_GPC['content']), 'loc_address' => $_GPC['gpsaddress'], 'loc_lon' => $_GPC['longitude'], 'loc_lat' => $_GPC['latitude'], 'loc_nation_code' => $_GPC['loc_nation_code'], 'loc_nation' => $_GPC['loc_nation'], 'loc_province' => $_GPC['loc_province'], 'loc_city_code' => $_GPC['loc_city_code'], 'loc_city' => $_GPC['loc_city'], 'loc_district' => $_GPC['loc_district'], 'u_nickname' => $myfun->textEncode($_GPC['u_nickname']), 'u_city' => $_GPC['u_city'], 'u_openid' => $_GPC['u_openid'], 'u_unionid' => $_GPC['u_unionid'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'u_phone' => $_GPC['phone'], 'crtime' => time(), 'formID' => $_GPC['formID'], 'imgs_list' => '', 'audit_status' => '0', 'default_money' => $default_fatie_money, 'is_placed_top' => $_GPC['istop'], 'placed_top_days' => $_GPC['topdyas'], 'placed_top_money' => $_GPC['money'], 'placed_top_duedate' => '', 'transaction_id' => '', 'pay_channel' => '', 'payStatus' => '');
			$ordermoney = floatval($mod_data['default_money']) + floatval($mod_data['placed_top_money']);
			$mod_data['total_money'] = $ordermoney;
			if (intval($_GPC['topdyas']) != 0) {
				$mod_data['placed_top_duedate'] = $myfun->doDate('day', '+', intval($_GPC['topdyas']));
			}
			$res = $DBUtil->save($h_msg, $mod_data);
			if ($ordermoney > 0) {
				$mod_data['payStatus'] = '0';
				$DBUtil->update($h_msg, $mod_data, array('oncode' => $oncode));
			} else {
				$mod_data['audit_status'] = $h_mtype_det['is_audit'];
				$DBUtil->update($h_msg, $mod_data, array('oncode' => $oncode));
				if ($mod_data['audit_status'] == '1') {
					$this->add_msg_status();
				} else {
					$status_det = $DBUtil->getOne('wys_tongcheng_status', 'uniacid=:uniacid and stype=:stype', array(':uniacid' => $_W['uniacid'], ':stype' => 'msg'));
					if (empty($status_det)) {
						$add_status = array('uniacid' => $_W['uniacid'], 'stype' => 'msg', 'status' => '0', 'crtime' => time(), 'modtime' => time());
						$DBUtil->save('wys_tongcheng_status', $add_status);
					}
				}
			}
			$data = array('oncode' => $oncode, 'ordermoney' => $ordermoney, 'rp_status' => '1', 'bbb' => $user['is_black']);
			if (empty($user)) {
				$integral_zs = $setting['integral_zs'];
				if (empty($integral_zs) || $integral_zs == '') {
					$integral_zs = 0;
				}
				$user = array('uniacid' => $_W['uniacid'], 'u_nickname' => $myfun->textEncode($_GPC['u_nickname']), 'u_city' => $_GPC['u_city'], 'u_openid' => $_GPC['u_openid'], 'u_unionid' => $_GPC['u_unionid'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'u_phone' => $_GPC['phone'], 'u_address' => $_GPC['gpsaddress'], 'u_lon' => $_GPC['longitude'], 'u_lat' => $_GPC['latitude'], 'crtime' => time(), 'integral' => floatval($integral_zs));
				if (!empty($user['u_openid'])) {
					$DBUtil->save($DBUtil::wys_tongcheng_user, $user);
				}
			} else {
				$user['u_address'] = $_GPC['gpsaddress'];
				$user['u_lon'] = $_GPC['longitude'];
				$user['u_lat'] = $_GPC['latitude'];
				$user['u_phone'] = $_GPC['phone'];
				$DBUtil->update($DBUtil::wys_tongcheng_user, $user, array('u_openid' => $_GPC['u_openid']));
			}
		} else {
			$data = array('rp_status' => '0', 'bk_lianxi' => $setting['bk_lianxi']);
		}
		$errno = 0;
		$message = 'save suc';
		return $this->result($errno, $message, $data);
	}
	public function doPageMsg_send_imgs()
	{
		global $_GPC, $_W;
		$errno = 0;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$oncode = $_GPC['oncode'];
		$h_msg_imgs = $DBUtil::wys_tongcheng_msg_imgs;
		$h_msg = $DBUtil::wys_tongcheng_msg;
		$h_msg_det = $DBUtil->getOne($h_msg, 'uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $oncode));
		$date_info = getdate();
		$year = $date_info['year'];
		$mon = $date_info['mon'];
		$day = $date_info['mday'];
		$logo_path = 'images/wys_tongcheng/uniacid_' . $_W['uniacid'] . sprintf("%s/%s/%s/%s/", $logo_path, $year, $mon, $day);
		$uplogo_path = ATTACHMENT_ROOT . $logo_path;
		if (!is_dir($uplogo_path)) {
			$res = mkdir($uplogo_path, 0777, true);
		}
		$img_file_name = time() . $myfun->randombylength(8) . '.' . $myfun->fileext($_FILES['file']['name']);
		if (move_uploaded_file($_FILES['file']['tmp_name'], $uplogo_path . $img_file_name)) {
			$restult = true;
		} else {
			$restult = false;
		}
		$webimgurl = tomedia($logo_path . $img_file_name);
		$message = 'img upload suc';
		$data = array('oncode' => $oncode, 'isyun' => $webimgurl);
		$img_mod = array('uniacid' => $_W['uniacid'], 'oncode' => $oncode, 'imgpath' => $webimgurl, 'crtime' => time());
		$h_msg_det['imgs_list'] = $h_msg_det['imgs_list'] . $webimgurl . ',';
		$DBUtil->update($h_msg, $h_msg_det, array('oncode' => $oncode));
		$res = $DBUtil->save($h_msg_imgs, $img_mod);
		return $this->result($errno, $message, $data);
	}
	public function doPageTop_xianzhi()
	{
		global $_GPC, $_W;
		$setting = $this->module['config'];
		$DBUtil = new DBUtil();
		$h_msg = $DBUtil::wys_tongcheng_msg;
		if ($setting['top_type'] == '-1') {
			$data['top_type'] = 0;
			$data['top_status'] = false;
		} else {
			$data['top_type'] = 1;
			if ($setting['top_type'] == '0') {
				$where = ' uniacid=:uniacid and is_placed_top=:is_placed_top';
				$param = array(':uniacid' => $_W['uniacid'], ':is_placed_top' => 1);
				$toplist = $DBUtil->getMany($DBUtil::wys_tongcheng_msg, $where, $param);
				foreach ($toplist as $key => $value) {
					if ($value['is_placed_top'] == '1' && intval($value['placed_top_duedate']) < time()) {
						$DBUtil->update($h_msg, array('is_placed_top' => '0'), array('id' => $value['id']));
					}
				}
				$have_top_cnt = $DBUtil->getCount($DBUtil::wys_tongcheng_msg, $where, $param);
				$top_cnt = $setting['top_cnt'] == '' ? '0' : $setting['top_cnt'];
				$data['top_status'] = $have_top_cnt >= intval($top_cnt);
				$data['top_xzcnt'] = intval($top_cnt);
				$data['top_havecnt'] = intval($have_top_cnt);
			} else {
				if ($setting['top_type'] == '1') {
					$where = ' uniacid=:uniacid and is_placed_top=:is_placed_top and tid=:tid';
					$param = array(':uniacid' => $_W['uniacid'], ':is_placed_top' => 1, ':tid' => $_GPC['tid']);
					$toplist = $DBUtil->getMany($DBUtil::wys_tongcheng_msg, $where, $param);
					foreach ($toplist as $key => $value) {
						if ($value['is_placed_top'] == '1' && intval($value['placed_top_duedate']) < time()) {
							$DBUtil->update($h_msg, array('is_placed_top' => '0'), array('id' => $value['id']));
						}
					}
					$have_top_cnt = $DBUtil->getCount($DBUtil::wys_tongcheng_msg, $where, $param);
					$top_cnt = $setting['top_cnt'] == '' ? '0' : $setting['top_cnt'];
					$data['top_status'] = $have_top_cnt >= intval($top_cnt);
					$data['top_xzcnt'] = intval($top_cnt);
					$data['top_havecnt'] = intval($have_top_cnt);
				}
			}
		}
		$errno = 0;
		$message = 'rp_text';
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_user_info()
	{
		global $_GPC, $_W;
		$setting = $this->module['config'];
		$DBUtil = new DBUtil();
		$data = $DBUtil->getOne($DBUtil::wys_tongcheng_user, 'u_openid=:u_openid', array(':u_openid' => $_GPC['u_openid']));
		$h_msg = $DBUtil::wys_tongcheng_msg;
		$data['is_top'] = $setting['is_top'];
		$topguizhe = $setting['topguizhe'];
		$array = explode(';', $topguizhe);
		$topgui_zhe = array();
		foreach ($array as $key => $item) {
			$item_ar = explode(':', $item);
			array_push($topgui_zhe, array('days' => $item_ar[0], 'money' => $item_ar[1]));
		}
		$data['topguizhe'] = $topgui_zhe;
		$phone_isopen = $setting['phone_isopen'];
		$data['phone_isopen'] = $phone_isopen == '1' ? true : false;
		if (empty($setting['imgs_cnt'])) {
			$data['imgs_cnt'] = 6;
		} else {
			$data['imgs_cnt'] = $setting['imgs_cnt'];
		}
		$data['loc_type'] = $setting['loc_type'];
		$data['loc_text'] = $setting['loc_text'];
		$data['qq_map_key'] = $setting['qq_map_key'];
		if ($setting['top_type'] == '-1') {
			$data['top_type'] = 0;
			$data['top_status'] = false;
		} else {
			$data['top_type'] = 1;
			if ($setting['top_type'] == '0') {
				$where = ' uniacid=:uniacid and is_placed_top=:is_placed_top';
				$param = array(':uniacid' => $_W['uniacid'], ':is_placed_top' => 1);
				$toplist = $DBUtil->getMany($DBUtil::wys_tongcheng_msg, $where, $param);
				foreach ($toplist as $key => &$value) {
					if ($value['is_placed_top'] == '1' && intval($value['placed_top_duedate']) < time()) {
						$DBUtil->update($h_msg, array('is_placed_top' => '0'), array('id' => $value['id']));
					}
				}
				$have_top_cnt = $DBUtil->getCount($DBUtil::wys_tongcheng_msg, $where, $param);
				$top_cnt = $setting['top_cnt'] == '' ? '0' : $setting['top_cnt'];
				$data['top_status'] = $have_top_cnt >= intval($top_cnt);
				$data['top_xzcnt'] = intval($top_cnt);
				$data['top_havecnt'] = intval($have_top_cnt);
			} else {
				if ($setting['top_type'] == '1') {
					$where = ' uniacid=:uniacid and is_placed_top=:is_placed_top and tid=:tid';
					$param = array(':uniacid' => $_W['uniacid'], ':is_placed_top' => 1, ':tid' => $_GPC['tid']);
					$toplist = $DBUtil->getMany($DBUtil::wys_tongcheng_msg, $where, $param);
					foreach ($toplist as $key => &$value) {
						if ($value['is_placed_top'] == '1' && intval($value['placed_top_duedate']) < time()) {
							$DBUtil->update($h_msg, array('is_placed_top' => '0'), array('id' => $value['id']));
						}
					}
					$have_top_cnt = $DBUtil->getCount($DBUtil::wys_tongcheng_msg, $where, $param);
					$top_cnt = $setting['top_cnt'] == '' ? '0' : $setting['top_cnt'];
					$data['top_status'] = $have_top_cnt >= intval($top_cnt);
					$data['top_xzcnt'] = intval($top_cnt);
					$data['top_havecnt'] = intval($have_top_cnt);
				}
			}
		}
		$data['smrz_isopen'] = $setting['smrz_isopen'];
		$data['xieyi_isopen'] = $setting['xieyi_isopen'];
		if ($_GPC['is_no'] != '1') {
			if ($data['xieyi_isopen'] == '1') {
				$data['xieyi_rmk'] = $setting['xieyi_rmk'];
			}
		}
		$errno = 0;
		$message = 'rp_text';
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_msg_refresh_crtime_money()
	{
		global $_GPC, $_W;
		$setting = $this->module['config'];
		$refresh_money = $setting['refresh_money'];
		if (empty($refresh_money) || $refresh_money == '') {
			$refresh_money = 0;
		} else {
			$refresh_money = floatval($refresh_money);
		}
		$errno = 0;
		$message = 'rp_textCC';
		$data = array('money' => $refresh_money);
		return $this->result($errno, $message, $data);
	}
	public function doPageSaveOrUpdate_user_info()
	{
		global $_GPC, $_W;
		$errno = 0;
		$DBUtil = new DBUtil();
		$user = $DBUtil->getOne($DBUtil::wys_tongcheng_user, 'u_openid=:u_openid', array(':u_openid' => $_GPC['u_openid']));
		$p_data = array('uniacid' => $_W['uniacid'], 'u_nickname' => $_GPC['u_nickname'], 'u_address' => $_GPC['u_address'], 'u_city' => $_GPC['u_city'], 'u_openid' => $_GPC['u_openid'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'enable' => '1', 'u_phone' => $_GPC['u_phone'], 'u_rmk' => $_GPC['u_rmk'], 'u_sex' => $_GPC['u_sex'], 'crtime' => time());
		if (empty($user)) {
			if (!empty($p_data['u_openid'])) {
				$DBUtil->save($DBUtil::wys_tongcheng_user, $p_data);
				$dotype = 0;
			}
		} else {
			$DBUtil->update($DBUtil::wys_tongcheng_user, $p_data, array('u_openid' => $_GPC['u_openid']));
			$dotype = 1;
		}
		$message = 'rp_text';
		$data = $p_data;
		return $this->result($errno, $message, $data);
	}
	public function doPageDel_msg()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$id = $_GPC['mid'];
		$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'id=:id', array('id' => $id));
		$imglist = $DBUtil->getMany('wys_tongcheng_msg_imgs', 'uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $msg_det['oncode']));
		foreach ($imglist as $key => $imgs) {
			$temp_mg1 = str_replace($_W['attachurl'], '', $imgs['imgpath']);
			if (file_exists(ATTACHMENT_ROOT . $temp_mg1)) {
				unlink(ATTACHMENT_ROOT . $temp_mg1);
			}
			$DBUtil->delete('wys_tongcheng_msg_imgs', array('id' => $imgs['id']));
		}
		$DBUtil->delete('wys_tongcheng_comments', array('mid' => $id));
		$DBUtil->delete('wys_tongcheng_salebanner', array('openid' => $msg_det['u_openid']));
		$res = $DBUtil->delete('wys_tongcheng_msg', array('id' => $id));
		$errno = 0;
		$message = 'rp_text' . $_GPC['mid'];
		$data = array('delstatus' => $res);
		return $this->result($errno, $message, $data);
	}
	public function doPageMsg_refresh_time()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$id = $_GPC['mid'];
		$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'id=:id', array('id' => $id));
		$r_time = time();
		$msg_det['crtime'] = $r_time;
		$res = $DBUtil->update('wys_tongcheng_msg', $msg_det, array('id' => $id));
		$errno = 0;
		$message = 'rp_text' . $_GPC['mid'];
		$data = array('ref_status' => $res, 'refresh_time' => $myfun->friendlyDate($r_time, 'mohu_ot'));
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_msg_list()
	{
		include_once MODULE_ROOT . '/inc/params_init.php';
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$h_msg = $DBUtil::wys_tongcheng_msg;
		$page = max(1, $_GPC['page']);
		$sql_text = $_GPC['sql_text'];
		$setting = $this->module['config'];
		$is_top = $setting['is_top'];
		$lookcnt_isopen = $setting['lookcnt_isopen'];
		$indexlxta_isopen = $setting['indexlxta_isopen'];
		$scomments_isopen = $setting['scomments_isopen'];
		$goods_isopen = $setting['goods_isopen'];
		$shang_isopen = $setting['shang_isopen'];
		$list_imgs_isopen = $setting['list_imgs_isopen'];
		$list_comments_isopen = $setting['list_comments_isopen'];
		$mtypshow_isopen = $setting['mtypshow_isopen'];
		if (!empty($setting['page_size'])) {
			if ($setting['page_size'] == 0) {
				$pagesize = $setting['page_size'];
			} else {
				$pagesize = 10;
			}
		} else {
			$pagesize = 10;
		}
		$where = 'uniacid=:uniacid and audit_status=:audit_status';
		$m_type = $_GPC['m_type'];
		if ($m_type == '0') {
			$order = ' is_placed_top desc,crtime desc';
			if (!empty($sql_text) && $sql_text != '') {
				$where .= " and content like :sqltext";
				$params = array(':audit_status' => 1, ':uniacid' => $_W['uniacid'], ':sqltext' => '%' . $sql_text . '%');
			} else {
				$params = array(':audit_status' => 1, ':uniacid' => $_W['uniacid']);
			}
			$rpdata = $DBUtil->getMany($h_msg, $where, $params, $order, $page, $pagesize);
			foreach ($rpdata as $key => &$value) {
				$value['mtypshow_isopen'] = $mtypshow_isopen;
				$value['goods_isopen'] = $goods_isopen;
			}
		} else {
			if ($m_type == '1') {
				$lon = $_GPC['lon'];
				$lat = $_GPC['lat'];
				if (!empty($sql_text) && $sql_text != '') {
					$where .= " and content like :sqltext";
					$params = array(':audit_status' => 1, ':uniacid' => $_W['uniacid'], ':sqltext' => '%' . $sql_text . '%');
				} else {
					$params = array(':audit_status' => 1, ':uniacid' => $_W['uniacid']);
				}
				$order = ' is_placed_top desc,SQRT((' . $lon . '-`loc_lon`)*(' . $lon . '-`loc_lon`)+(' . $lat . '-`loc_lat`)*(' . $lat . '-`loc_lat`))  asc';
				$rpdata = $DBUtil->getMany($h_msg, $where, $params, $order, $page, $pagesize);
				foreach ($rpdata as $key => &$value) {
					$value['mtypshow_isopen'] = $mtypshow_isopen;
				}
			} else {
				if ($m_type == '2') {
					$where .= ' and is_placed_top=:is_placed_top';
					if (!empty($sql_text) && $sql_text != '') {
						$where .= " and content like :sqltext";
						$params = array(':audit_status' => 1, ':uniacid' => $_W['uniacid'], ':is_placed_top' => '1', ':sqltext' => '%' . $sql_text . '%');
					} else {
						$params = array(':audit_status' => 1, ':uniacid' => $_W['uniacid'], ':is_placed_top' => '1');
					}
					$order = ' crtime desc';
					$rpdata = $DBUtil->getMany($h_msg, $where, $params, $order, $page, $pagesize);
					foreach ($rpdata as $key => &$value) {
						$value['mtypshow_isopen'] = $mtypshow_isopen;
					}
				} else {
					if ($m_type == '4') {
						$m_parent_typeid = $_GPC['m_parent_typeid'];
						$m_typeid = $_GPC['m_typeid'];
						$where .= ' and tid=:tid';
						$params = array(':audit_status' => 1, ':uniacid' => $_W['uniacid'], ':tid' => $m_typeid);
						$order = ' is_placed_top desc,crtime desc';
						if (!empty($m_parent_typeid) && $m_parent_typeid != '0') {
							$where .= ' and parent_tid=:parent_tid';
							$params[':parent_tid'] = $m_parent_typeid;
							$rpdata = $DBUtil->getMany($h_msg, $where, $params, $order, $page, $pagesize);
						} else {
							$rpdata = $DBUtil->getMany($h_msg, $where, $params, $order, $page, $pagesize);
						}
						foreach ($rpdata as $key => &$value) {
							$value['mtypshow_isopen'] = $mtypshow_isopen;
						}
					} else {
						if ($m_type == '5') {
							$where = 'uniacid=:uniacid and u_openid=:u_openid';
							$params = array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']);
							$order = ' crtime desc';
							$rpdata = $DBUtil->getMany($h_msg, $where, $params, $order, $page, $pagesize);
							foreach ($rpdata as $key => &$value) {
								$value['is_top'] = $is_top;
								$value['mtypshow_isopen'] = $mtypshow_isopen;
							}
						} else {
							if ($m_type == '6') {
								$where = 'uniacid=:uniacid and u_openid=:u_openid and audit_status=:audit_status';
								$params = array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['byuser'], ':audit_status' => 1);
								$order = ' crtime desc';
								$rpdata = $DBUtil->getMany($h_msg, $where, $params, $order, $page, $pagesize);
								foreach ($rpdata as $key => &$value) {
									$value['mtypshow_isopen'] = $mtypshow_isopen;
								}
							}
						}
					}
				}
			}
		}
		$list_addlookcnt_isopen = $setting['list_addlookcnt_isopen'];
		$list_addlookcnt_num_min = $setting['lookcnt_nummin'] == '' ? 0 : $setting['lookcnt_nummin'];
		$list_addlookcnt_num_max = $setting['lookcnt_nummax'] == '' ? 0 : $setting['lookcnt_nummax'];
		foreach ($rpdata as $key => &$value) {
			if ($list_addlookcnt_isopen == '1') {
				$add_lookcnt = intval(rand(intval($list_addlookcnt_num_min), intval($list_addlookcnt_num_max)));
				$value['look_cnt'] += $add_lookcnt;
				$DBUtil->update('wys_tongcheng_msg', array('look_cnt' => $value['look_cnt']), array('id' => $value['id']));
			}
			$value['goods_isopen'] = $goods_isopen;
			$value['shang_isopen'] = $shang_isopen;
			if ($list_imgs_isopen == '1') {
				if (substr($value['imgs_list'], strlen($value['imgs_list']) - 1, 1) == ',') {
					$imgsstr = substr($value['imgs_list'], 0, strlen($value['imgs_list']) - 1);
				} else {
					$imgsstr = $value['imgs_list'];
				}
				$value['imgs_list'] = '';
				$value['imgs_lists'] = explode(',', $imgsstr);
			}
			$value['content'] = $myfun->textDecode($value['content']);
			$value['u_nickname'] = $myfun->textDecode($value['u_nickname']);
			$value['crtime'] = $myfun->friendlyDate($value['crtime'], 'mohu_ot');
			$value['isgoods'] = $this->getIsGoods($value['id'], $value['uniacid'], $_GPC['openid']);
			if (($m_type == '1' || $m_type == '4') && $_GPC['lat'] != '') {
				$value['loc_juli'] = '(' . $myfun->getDistance(floatval($_GPC['lon']), floatval($_GPC['lat']), floatval($value['loc_lon']), floatval($value['loc_lat']), 2, 2) . 'km)';
			}
			$value['lookcnt_isopen'] = $lookcnt_isopen;
			$value['indexlxta_isopen'] = $indexlxta_isopen;
			$value['scomments_isopen'] = $scomments_isopen;
			if ($value['scomments_isopen'] == 1 && $value['comments_isopen'] == 1) {
				if ($list_comments_isopen == '1') {
					$value['comments'] = $this->getComments($value['id'], $_W['uniacid']);
				}
			}
			if ($value['is_placed_top'] == '1' && intval($value['placed_top_duedate']) < time()) {
				$update_msg = array('total_money' => floatval($value['total_money']) - floatval($value['placed_top_money']), 'is_placed_top' => '0', 'placed_top_days' => '', 'placed_top_money' => '', 'placed_top_duedate' => '');
				$DBUtil->update($h_msg, $update_msg, array('id' => $value['id']));
			}
			if (!empty($value['placed_top_duedate'])) {
				$value['placed_top_duedate'] = $myfun->friendlyDate($value['placed_top_duedate'], 'full');
			}
		}
		$status_det = $DBUtil->getOne('wys_tongcheng_status', 'uniacid=:uniacid and stype=:stype', array(':uniacid' => $_W['uniacid'], ':stype' => 'msg'));
		if (!empty($status_det)) {
			$add_status = array('status' => '0', 'modtime' => time());
			$DBUtil->update('wys_tongcheng_status', $add_status, array('id' => $status_det['id']));
		}
		$errno = 0;
		$message = 'load suc';
		return $this->result($errno, $message, $rpdata);
	}
	public function doPageGet_msg_one()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$h_msg = $DBUtil::wys_tongcheng_msg;
		$setting = $this->module['config'];
		$where = 'uniacid=:uniacid and id=:id';
		$params = array(':uniacid' => $_W['uniacid'], ':id' => $_GPC['mid']);
		$value = $DBUtil->getOne($h_msg, $where, $params);
		$value['look_cnt'] = $value['look_cnt'] + 1;
		$DBUtil->update($h_msg, $value, array('id' => $_GPC['mid']));
		if (substr($value['imgs_list'], strlen($value['imgs_list']) - 1, 1) == ',') {
			$imgsstr = substr($value['imgs_list'], 0, strlen($value['imgs_list']) - 1);
		} else {
			$imgsstr = $value['imgs_list'];
		}
		$value['imgs_list'] = '';
		$value['imgs_lists'] = explode(',', $imgsstr);
		$value['content'] = $myfun->textDecode($value['content']);
		$value['u_nickname'] = $myfun->textDecode($value['u_nickname']);
		$value['crtime'] = $myfun->friendlyDate($value['crtime'], 'ym_time');
		$value['isgoods'] = $this->getIsGoods($value['id'], $value['uniacid'], $_GPC['openid']);
		$value['loc_juli'] = $myfun->getDistance(floatval($_GPC['lon']), floatval($_GPC['lat']), floatval($value['loc_lon']), floatval($value['loc_lat']), 2, 2) . 'km';
		$value['lookcnt_isopen'] = $setting['lookcnt_isopen'];
		$value['goods_isopen'] = $setting['goods_isopen'];
		$value['scomments_isopen'] = $setting['scomments_isopen'];
		$value['shang_isopen'] = $setting['shang_isopen'];
		if ($value['scomments_isopen'] == 1 && $value['comments_isopen'] == 1) {
			$value['comments'] = $this->getComments($value['id'], $_W['uniacid']);
		}
		if (!empty($value['placed_top_duedate'])) {
			$value['placed_top_duedate'] = $myfun->friendlyDate($value['placed_top_duedate'], 'full');
		}
		if ($value['goods_isopen'] == '1') {
			$value['good_list'] = $DBUtil->getMany('wys_tongcheng_goods', 'mid=:mid', array(':mid' => $value['id']), 'crtime desc');
		}
		$errno = 0;
		$message = 'load suc';
		return $this->result($errno, $message, $value);
	}
	function getIsGoods($mid, $uniacid, $openid)
	{
		$DBUtil = new DBUtil();
		$h_goods = $DBUtil::wys_tongcheng_goods;
		$ishaveWhere = 'uniacid=:uniacid and mid=:mid and u_openid=:u_openid';
		$ishaveParam = array(':uniacid' => $uniacid, ':mid' => $mid, ':u_openid' => $openid);
		$ishave = $DBUtil->getCount($h_goods, $ishaveWhere, $ishaveParam);
		return $ishave;
	}
	function getComments($mid, $unacid)
	{
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$h_comments = $DBUtil::wys_tongcheng_comments;
		$where = 'uniacid=:uniacid and pl_ctype=:pl_ctype and mid=:mid';
		$params = array(':uniacid' => $unacid, ':pl_ctype' => 'main', ':mid' => $mid);
		$order = ' id desc';
		$rpdata = $DBUtil->getMany($h_comments, $where, $params, $order);
		foreach ($rpdata as $key => &$cmts) {
			$cmts['mcontent'] = $myfun->textDecode($cmts['mcontent']);
			$cmts['u_nickname'] = $myfun->textDecode($cmts['u_nickname']);
			$cmts['rp_nickname'] = $myfun->textDecode($cmts['rp_nickname']);
			$cmts['pt_comments'] = $this->getPT_Comments($cmts['id'], $unacid);
		}
		return $rpdata;
	}
	function getPT_Comments($attr, $unacid)
	{
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$h_comments = $DBUtil::wys_tongcheng_comments;
		$where = 'pl_ctype=:pl_ctype and attr=:attr';
		$params = array('pl_ctype' => 'ptmain', ':attr' => $attr);
		$order = ' id asc';
		$rpdata = $DBUtil->getMany($h_comments, $where, $params, $order);
		foreach ($rpdata as $key => &$cmts) {
			$cmts['mcontent'] = $myfun->textDecode($cmts['mcontent']);
			$cmts['u_nickname'] = $myfun->textDecode($cmts['u_nickname']);
			$cmts['rp_nickname'] = $myfun->textDecode($cmts['rp_nickname']);
		}
		return $rpdata;
	}
	public function doPageSend_comments()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$setting = $this->module['config'];
		$h_msg = $DBUtil::wys_tongcheng_msg;
		$h_comments = $DBUtil::wys_tongcheng_comments;
		$h_msg_det = $DBUtil->getOne($h_msg, 'uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $_GPC['pl_mid']));
		$cmtid = $_GPC['attr'];
		$attrtp = $_GPC['attrtp'];
		$rp_formid = '';
		if ($cmtid > 0 && $attrtp != '') {
			$plinfo = $DBUtil->getOne($h_comments, 'uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $_GPC['plid']));
			if ($attrtp == 'u') {
				$h_msg_det['u_nickname'] = $plinfo['u_nickname'];
				$h_msg_det['u_city'] = $plinfo['u_city'];
				$h_msg_det['u_openid'] = $plinfo['u_openid'];
				$h_msg_det['u_unionid'] = $plinfo['u_unionid'];
				$h_msg_det['u_avatarurl'] = $plinfo['u_avatarurl'];
			}
			if ($attrtp == 'rp') {
				$h_msg_det['u_nickname'] = $plinfo['rp_nickname'];
				$h_msg_det['u_city'] = $plinfo['rp_city'];
				$h_msg_det['u_openid'] = $plinfo['rp_openid'];
				$h_msg_det['u_unionid'] = $plinfo['rp_unionid'];
				$h_msg_det['u_avatarurl'] = $plinfo['rp_avatarurl'];
			}
			$rp_formid = $plinfo['formID'];
		} else {
			$rp_formid = $h_msg_det['formId'];
		}
		$mod_data = array('uniacid' => $_W['uniacid'], 'oncode' => $h_msg_det['oncode'], 'mid' => $_GPC['pl_mid'], 'attr' => $_GPC['attr'], 'tid' => $h_msg_det['tid'], 'tname' => $h_msg_det['tname'], 'pl_ctype' => $_GPC['pl_ctype'], 'mcontent' => $_GPC['pl_content'], 'rp_nickname' => $h_msg_det['u_nickname'], 'rp_city' => $h_msg_det['u_city'], 'rp_openid' => $h_msg_det['u_openid'], 'rp_unionid' => $h_msg_det['u_unionid'], 'm_content' => $h_msg_det['content'], 'u_nickname' => $_GPC['u_nickname'], 'u_city' => $_GPC['u_city'], 'u_openid' => $_GPC['u_openid'], 'u_unionid' => '', 'u_avatarurl' => $_GPC['u_avatarurl'], 'formID' => $_GPC['formID'], 'crtime' => time());
		$DBUtil->save('wys_tongcheng_comments', $mod_data);
		$h_msg_detupdate['comments_cnt'] = $h_msg_det['comments_cnt'] + 1;
		$DBUtil->update($h_msg, $h_msg_detupdate, array('id' => $_GPC['pl_mid']));
		$openid_rp = $mod_data['rp_openid'];
		if ($setting['sendcmmtsrt_isopen'] == '1') {
			$DBUtil->update('wys_tongcheng_msg', array('crtime' => time()), array('id' => $mod_data['mid']));
		}
		if (!empty($setting['commentsTmpl_isopen']) && $setting['commentsTmpl_isopen'] == '1') {
			$tplid = $setting['commentsTmpl_templ'];
			$formid = $rp_formid;
			$openid = $openid_rp;
			$data_arr = array('keyword1' => array('value' => $mod_data['u_nickname'] . '(评论了)'), 'keyword2' => array('value' => date("Y-m-d H:i:s")), 'keyword3' => array('value' => $mod_data['mcontent']), 'keyword4' => array('value' => $mod_data['m_content']));
			$page = 'wys_tongcheng/pages/msg_info/msg_info?mid=' . $mod_data['mid'];
			$templ_res = $myfun->send_template_fun($openid, $tplid, $formid, $page, $data_arr, '');
		}
		$errno = 0;
		$message = $openid_rp;
		$data = $this->getComments($_GPC['pl_mid'], $_W['uniacid']);
		return $this->result($errno, $message, $data);
	}
	public function doPageSend_goods()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$h_msg = $DBUtil::wys_tongcheng_msg;
		$h_goods = $DBUtil::wys_tongcheng_goods;
		$h_msg_det = $DBUtil->getOne($h_msg, 'uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $_GPC['mid']));
		$ishaveWhere = 'uniacid=:uniacid and mid=:mid and u_openid=:u_openid';
		$ishaveParam = array(':uniacid' => $_W['uniacid'], ':mid' => $_GPC['mid'], ':u_openid' => $_GPC['openId']);
		$ishave = $DBUtil->getCount($h_goods, $ishaveWhere, $ishaveParam);
		if ($ishave > 0) {
			$DBUtil->delete($h_goods, array('uniacid' => $_W['uniacid'], 'mid' => $_GPC['mid'], 'u_openid' => $_GPC['openId']));
			$allgoods = $h_msg_det['goods_cnt'] - 1;
			$h_msg_det['goods_cnt'] = $allgoods;
			$DBUtil->update($h_msg, $h_msg_det, array('id' => $_GPC['mid']));
			$isgoods = 0;
		} else {
			$mod_data = array('uniacid' => $_W['uniacid'], 'oncode' => $h_msg_det['oncode'], 'mid' => $_GPC['mid'], 'tid' => $h_msg_det['tid'], 'tname' => $h_msg_det['tname'], 'u_nickname' => $myfun->textEncode($_GPC['nickName']), 'u_city' => $_GPC['city'], 'u_openid' => $_GPC['openId'], 'u_unionid' => '', 'u_avatarurl' => $_GPC['avatarUrl'], 'crtime' => time());
			$res = $DBUtil->save($h_goods, $mod_data);
			$allgoods = $h_msg_det['goods_cnt'] + 1;
			$h_msg_det['goods_cnt'] = $allgoods;
			$DBUtil->update($h_msg, $h_msg_det, array('id' => $_GPC['mid']));
			$isgoods = 1;
		}
		$del_data = array(':u_openid' => '', ':u_nickname' => '%rdgztest%', ':mid1' => '', ':mid2' => 'undefined');
		$del_list = $DBUtil->getMany($h_goods, '(u_openid=:u_openid or u_nickname like :u_nickname or (mid=:mid1 or mid=:mid2))', $del_data);
		foreach ($del_list as $key => $del_detit) {
			if ($del_detit['mid'] != '' && $del_detit['mid'] != 'null' && $del_detit['mid'] != 'undefined' && $del_detit['mid'] != null) {
				$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'id=:id', array(':id' => $del_detit['mid']));
				$rmcnt = intval($msg_det['goods_cnt']) - 1;
				$DBUtil->update('wys_tongcheng_msg', array('goods_cnt' => $rmcnt), array('id' => $del_detit['mid']));
			}
			$DBUtil->delete($h_goods, array('id' => $del_detit['id']));
		}
		$errno = 0;
		$message = 'rp_text|' . count($del_list);
		$data = array('allgoods' => $allgoods, 'isgoods' => $isgoods);
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_banners()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$MyFun = new MyFun();
		$h_banner = $DBUtil::wys_tongcheng_banner;
		$where = 'uniacid=:uniacid and btype=:btype and enable=:enable and lastdate>=:lastdate and audit_status=:audit_status';
		$params = array(':uniacid' => $_W['uniacid'], ':btype' => $_GPC['btype'], ':enable' => '1', ':audit_status' => 1, ':lastdate' => time());
		$order = ' pxh asc';
		$rpdata = $DBUtil->getMany($h_banner, $where, $params, $order);
		foreach ($rpdata as $key => &$value) {
			$value['img'] = $_W['attachurl'] . $value['img'];
			if ($value['is_sale'] == '1' && intval($value['lastdate']) < time()) {
				$sale_banner = $DBUtil->getOne('wys_tongcheng_salebanner', ' uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $id));
				$update_banner = array('sale_status' => 0, 'sale_openid' => '', 'days' => 0, 'lastdate' => $MyFun->doDate('day', '+', 90), 'mid' => '', 'msg_type' => 0, 'sale_img' => '');
				$DBUtil->update('wys_tongcheng_banner', $update_banner, array('id' => $sale_banner['bn_id']));
				if (!empty($sale_banner['m_id'])) {
					$DBUtil->update('wys_tongcheng_msg', array('banner_sale' => 0), array('id' => $sale_banner['m_id']));
				}
				$res = $DBUtil->update('wys_tongcheng_salebanner', array('audit_status' => '0'), array('id' => $id));
			} else {
				if ($value['is_sale'] == '0' && intval($value['lastdate']) < time()) {
					$lastdate = $MyFun->doDate('day', '+', 90);
					$DBUtil->update('wys_tongcheng_banner', array('lastdate' => $lastdate), array('id' => $value['id']));
				}
			}
		}
		$errno = 0;
		$message = 'rp_text';
		$data = $rpdata;
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_banners_sale()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$page = max(1, $_GPC['page']);
		$pagesize = 10;
		$h_banner = $DBUtil::wys_tongcheng_banner;
		$where = 'uniacid=:uniacid and is_sale=:is_sale and enable=:enable and sale_status=:sale_status';
		$params = array(':uniacid' => $_W['uniacid'], ':enable' => '1', ':is_sale' => 1, ':sale_status' => '0');
		$order = ' btype asc,pxh asc';
		$rpdata = $DBUtil->getMany($h_banner, $where, $params, $order, $page, $pagesize);
		foreach ($rpdata as $key => &$value) {
			$value['img'] = $_W['attachurl'] . $value['img'];
		}
		$errno = 0;
		$message = 'rp_text';
		$data = $rpdata;
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_banner_info()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$h_banner = $DBUtil::wys_tongcheng_banner;
		$where = 'uniacid=:uniacid and id=:id';
		$params = array(':uniacid' => $_W['uniacid'], ':id' => $_GPC['bid']);
		$rpdata = $DBUtil->getOne($h_banner, $where, $params);
		$rpdata['img'] = $_W['attachurl'] . $rpdata['img'];
		$rpdata['crtime'] = $myfun->friendlyDate($rpdata['crtime'], 'mohu_ot');
		$errno = 0;
		$message = 'rp_text';
		$data = $rpdata;
		return $this->result($errno, $message, $data);
	}
	public function doPageSend_jubao()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$msg_mod = $DBUtil->getOne($DBUtil::wys_tongcheng_msg, 'id=:id', array(':id' => $_GPC['mid']));
		$data_p = array('uniacid' => $_W['uniacid'], 'mid' => $_GPC['mid'], 'msg_content' => $msg_mod['content'], 'jb_conent' => $_GPC['content'], 'u_city' => $_GPC['u_city'], 'u_openid' => $_GPC['u_openid'], 'u_unionid' => $_GPC['u_unionid'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'u_nickname' => $_GPC['u_nickname'], 'crtime' => time(), 'cl_state' => '0', 'formID' => $_GPC['formID']);
		$DBUtil->save($DBUtil::wys_tongcheng_jubao, $data_p);
		$errno = 0;
		$message = 'rp_text';
		$data = array();
		return $this->result($errno, $message, $data);
	}
	public function doPageSend_templ()
	{
		global $_GPC, $_W;
		$myfun = new MyFun();
		$setting = $this->module['config'];
		$openid = $_GPC['openid'];
		$tplid = $setting['audit_templ'];
		$page = 'wys_tongcheng/pages/audit_info/msg_info?mid=82';
		$formid = $_GPC['formid'];
		$data_arr = array('keyword1' => array('value' => '119991', "color" => 'red'), 'keyword2' => array('value' => '222-aaddd'), 'keyword3' => array('value' => '123123'));
		$res = $myfun->send_template_fun($openid, $tplid, $formid, $page, $data_arr, '');
		$errno = 0;
		$message = 'send_ok';
		$data = array('res' => $res);
		return $this->result($errno, $message, $data);
	}
	public function doPagePay_old()
	{
		global $_GPC, $_W;
		$myfun = new MyFun();
		$errno = 0;
		$message = 'rp_text' . $_GPC['sum'];
		$nonceStr = $myfun->randombylength_num(32);
		$notify_url = $_W['siteroot'] . 'payment/wechat/native.php';
		$out_trade_no = 'dA' . $myfun->randombylength_num(30);
		$spbill_create_ip = $_SERVER['SERVER_ADDR'];
		$total_fee = intval($_GPC['sum'] * 100);
		$trade_type = 'JSAPI';
		$appid = $_W['account']['key'];
		$mch_id = $_GPC['mc_id'];
		$key = $_GPC['mc_key'];
		$wechatAppPay = new wechatAppPay($appid, $mch_id, $notify_url, $key);
		$params['body'] = '商品描述';
		$params['out_trade_no'] = $out_trade_no;
		$params['total_fee'] = $total_fee;
		$params['trade_type'] = $trade_type;
		$params['openid'] = $_GPC['openid'];
		$params['nonce_str'] = $nonceStr;
		$result = $wechatAppPay->unifiedOrder($params);
		$data = array('appid' => $_W['account']['key'], 'timeStamp' => '1500596496', 'nonceStr' => $nonceStr, 'package' => 'prepay_id=' . $result['prepay_id'], '$out_trade_no' => $out_trade_no, 'prepay_id' => $result['prepay_id']);
		$sgin = $this->mksing($data['appid'], $data['nonceStr'], $data['package'], $data['timeStamp'], $_GPC['mc_key']);
		$data['paySign'] = $sgin;
		return $this->result($errno, $message, $data);
	}
	function mksing($appid, $nonceStr, $package, $timeStamp, $key)
	{
		return md5('appId=' . $appid . '&nonceStr=' . $nonceStr . '&package=' . $package . '&signType=MD5&timeStamp=' . $timeStamp . '&key=' . $key);
	}
	private function createNoncestr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	public function doPagePay()
	{
		global $_GPC, $_W;
		$openid = $_GPC['openid'];
		if (empty($openid) || $openid == '') {
			if (empty($_W['openid'])) {
				$openid = $_W['openid'];
			}
		}
		$_W['openid'] = $openid;
		$order = array('tid' => $_GPC['oncode'] . '|' . $_GPC['ptype'] . '|' . date('YmdHis'), 'user' => $openid, 'fee' => floatval($_GPC['sum']), 'title' => $_W['account']['name'] . '支付');
		$pay_params = $this->pay($order);
		if (is_error($pay_params)) {
			return $this->result(1, $pay_params, $_W['openid']);
		}
		return $this->result(0, '', $pay_params);
	}
	public function doPageCheck_user_have()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$setting = $this->module['config'];
		$user_det = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['u_openid']));
		if (empty($user_det)) {
			$integral_zs = $setting['integral_zs'];
			if (empty($integral_zs) || $integral_zs == '') {
				$integral_zs = 0;
			}
			$p_inte = floatval($integral_zs);
			$user = array('uniacid' => $_W['uniacid'], 'u_nickname' => $myfun->textEncode($_GPC['u_nickname']), 'u_city' => $_GPC['u_city'], 'u_openid' => $_GPC['u_openid'], 'u_unionid' => $_GPC['u_unionid'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'crtime' => time(), 'account' => 0, 'integral' => $p_inte);
			if (!empty($user['u_openid'])) {
				$DBUtil->save('wys_tongcheng_user', $user);
			}
			$user_have = true;
		} else {
			$user_have = false;
		}
		$errno = 0;
		$message = 'rp_text';
		$data = array('user_have' => $user_have);
		return $this->result($errno, $message, $data);
	}
	public function doPagePay_taocan_list()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$errno = 0;
		$message = 'rp_text';
		$data = $DBUtil->getMany('wys_tongcheng_paytaocan', 'uniacid=:uniacid and enable=:enable', array(':uniacid' => $_W['uniacid'], ':enable' => '1'), 'lasttime asc');
		foreach ($data as $key => &$value) {
			$value['lasttime'] = date('Y年m月d日', $value['lasttime']);
		}
		return $this->result($errno, $message, $data);
	}
	public function payResult($params)
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$setting = $this->module['config'];
		if ($params['result'] == 'success' && $params['from'] == 'notify') {
			$arrys = explode('|', $params['tid']);
			$oncode = current($arrys);
			$paytype = next($arrys);
			if ($paytype == 'pay') {
				$arrys_oncode = explode(',', $oncode);
				$shang_mid = current($arrys_oncode);
				$shang_random_code = next($arrys_oncode);
				$pay_log = $DBUtil->getOne('core_paylog', 'uniacid=:uniacid and tid=:tid', array(':uniacid' => $_W['uniacid'], ':tid' => $params['tid']));
				$taocan_det = $DBUtil->getOne('wys_tongcheng_paytaocan', 'id=:id', array(':id' => $shang_mid));
				$user_det = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $pay_log['openid']));
				$p_acc = $taocan_det['pay_money'] + floatval($user_det['account']);
				$p_inte = $taocan_det['song_integral'] + floatval($user_det['integral']);
				$up_account = array('account' => $p_acc, 'integral' => $p_inte);
				$DBUtil->update('wys_tongcheng_user', $up_account, array('u_openid' => $pay_log['openid']));
				$order_data = array('uniacid' => $_W['uniacid'], 'oncode' => $taocan_det['id'], 'openid' => $pay_log['openid'], 'unionid' => '', 'ordertype' => 'pay', 'dt_id' => $taocan_det['id'], 'orderrmk' => '用户充值:', 'total_money' => $pay_log['fee'], 'transaction_id' => $params['tag']['transaction_id'], 'u_name' => $user_det['u_nickname'], 'u_phone' => $user_det['u_phone'], 'paystate' => 1, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
				$DBUtil->save('wys_tongcheng_payorder', $order_data);
			} else {
				if ($paytype == 'msg') {
					$arrys_oncode = explode(',', $oncode);
					$shang_mid = current($arrys_oncode);
					$shang_random_code = next($arrys_oncode);
					$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $shang_mid));
					$h_mtype_det = $DBUtil->getOne('wys_tongcheng_mtype', 'uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $msg_det['tid']));
					$audit_status = $msg_det['audit_status'];
					if ($audit_status != '1') {
						$audit_status = $h_mtype_det['is_audit'];
						$this->add_msg_status();
					}
					$res = $DBUtil->update('wys_tongcheng_msg', array('audit_status' => $audit_status, 'payStatus' => 1, 'transaction_id' => $params['tag']['transaction_id']), array('oncode' => $shang_mid));
					$order_data = array('uniacid' => $_W['uniacid'], 'oncode' => $msg_det['oncode'], 'openid' => $msg_det['u_openid'], 'unionid' => $msg_det['u_unionid'], 'ordertype' => 'msg', 'dt_id' => $msg_det['id'], 'orderrmk' => '消息或置顶付费', 'total_money' => $msg_det['total_money'], 'transaction_id' => $params['tag']['transaction_id'], 'u_name' => $msg_det['u_nickname'], 'u_phone' => $msg_det['u_phone'], 'paystate' => 1, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
					$DBUtil->save('wys_tongcheng_payorder', $order_data);
				} else {
					if ($paytype == 'msg_refresh_crtime') {
						$arrys_oncode = explode(',', $oncode);
						$shang_mid = current($arrys_oncode);
						$shang_random_code = next($arrys_oncode);
						$pay_log = $DBUtil->getOne('core_paylog', 'uniacid=:uniacid and tid=:tid', array(':uniacid' => $_W['uniacid'], ':tid' => $params['tid']));
						$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $shang_mid));
						$order_data = array('uniacid' => $_W['uniacid'], 'oncode' => $msg_det['oncode'], 'openid' => $msg_det['u_openid'], 'unionid' => $msg_det['u_unionid'], 'ordertype' => 'msg_refresh_crtime', 'dt_id' => $msg_det['id'], 'orderrmk' => '消息刷新日期', 'total_money' => $pay_log['fee'], 'transaction_id' => $params['tag']['transaction_id'], 'u_name' => $msg_det['u_nickname'], 'u_phone' => $msg_det['u_phone'], 'paystate' => 1, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
						$DBUtil->save('wys_tongcheng_payorder', $order_data);
					} else {
						if ($paytype == 'banner') {
							$arrys_oncode = explode(',', $oncode);
							$shang_mid = current($arrys_oncode);
							$shang_random_code = next($arrys_oncode);
							$banner_is_audit = $setting['banner_audit'];
							$res = $DBUtil->update('wys_tongcheng_salebanner', array('paystate' => 1, 'paycrtime' => time(), 'transaction_id' => $params['tag']['transaction_id']), array('oncode' => $shang_mid));
							$sale_banner = $DBUtil->getOne('wys_tongcheng_salebanner', ' uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $shang_mid));
							$DBUtil->update('wys_tongcheng_banner', array('sale_status' => 1), array('id' => $sale_banner['bn_id']));
							$order_data = array('uniacid' => $_W['uniacid'], 'oncode' => $sale_banner['oncode'], 'openid' => $sale_banner['openid'], 'unionid' => $sale_banner['unionid'], 'ordertype' => 'banner', 'dt_id' => $sale_banner['m_id'], 'orderrmk' => '幻灯广告位出租消息', 'total_money' => $sale_banner['bn_total_money'], 'transaction_id' => $params['tag']['transaction_id'], 'u_name' => $sale_banner['u_nickname'], 'u_phone' => $sale_banner['u_phone'], 'paystate' => 1, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
							$DBUtil->save('wys_tongcheng_payorder', $order_data);
						} else {
							if ($paytype == 'shang') {
								$arrys_oncode = explode(',', $oncode);
								$shang_mid = current($arrys_oncode);
								$shang_random_code = next($arrys_oncode);
								$pay_log = $DBUtil->getOne('core_paylog', 'uniacid=:uniacid and tid=:tid', array(':uniacid' => $_W['uniacid'], ':tid' => $params['tid']));
								$banner_is_audit = $setting['banner_audit'];
								$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'id=:id', array(':id' => $shang_mid));
								$up_shangdet = intval($msg_det['shang_cnt']) + 1;
								$DBUtil->update('wys_tongcheng_msg', array('shang_cnt' => $up_shangdet), array('id' => $shang_mid));
								$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $msg_det['u_openid']));
								$shang_kcl = $setting['shang_kcl'];
								if (empty($shang_kcl) || $shang_kcl == '') {
									$shang_kcl = 0;
								}
								$shang_kcl = floatval($shang_kcl);
								$fee_user_l = (100 - $shang_kcl) / 100;
								$fee_system_l = 1 - $fee_user_l;
								$fee_touser = floatval($pay_log['fee']) * $fee_user_l;
								$fee_system = floatval($pay_log['fee']) * $fee_system_l;
								$fee_touser = floor($fee_touser * 100) / 100;
								$fee_system = floor($fee_system * 100) / 100;
								$pay_money = $pay_user['account'] + $fee_touser;
								$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $msg_det['u_openid']));
								$user_det = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $pay_log['openid']));
								$order_data = array('uniacid' => $_W['uniacid'], 'oncode' => $msg_det['oncode'], 'openid_y' => $msg_det['u_openid'], 'openid' => $pay_log['openid'], 'unionid' => $sale_banner['uniontid'], 'ordertype' => 'shang', 'dt_id' => $msg_det['id'], 'orderrmk' => '打赏给:' . $pay_user['u_nickname'], 'user_money' => $fee_touser, 'system_rmk' => '平台扣除' . $fee_system . '元', 'system_money' => $fee_system, 'total_money' => $pay_log['fee'], 'transaction_id' => $params['tag']['transaction_id'], 'u_name' => $user_det['u_nickname'], 'u_phone' => $user_det['u_phone'], 'paystate' => 1, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
								$DBUtil->save('wys_tongcheng_payorder', $order_data);
							} else {
								if ($paytype == 'store_ruzhu') {
									$arrys_oncode = explode(',', $oncode);
									$shang_mid = current($arrys_oncode);
									$shang_random_code = next($arrys_oncode);
									$res = $DBUtil->update('wys_tongcheng_store', array('paystatus' => 1, 'pay_time' => time(), 'transaction_id' => $params['tag']['transaction_id']), array('oncode' => $shang_mid));
									$store_det = $DBUtil->getOne('wys_tongcheng_store', 'oncode=:oncode', array(':oncode' => $shang_mid));
									$use_det = $DBUtil->getOne('wys_tongcheng_user', 'u_openid=:u_openid', array(':u_openid' => $store_det['openid']));
									$order_data = array('uniacid' => $_W['uniacid'], 'oncode' => $store_det['oncode'], 'openid' => $store_det['openid'], 'unionid' => $store_det['unionid'], 'ordertype' => 'store_ruzhu', 'dt_id' => $store_det['id'], 'orderrmk' => '店铺入驻', 'total_money' => $store_det['ruzzhu_money'], 'transaction_id' => $params['tag']['transaction_id'], 'u_name' => $use_det['u_nickname'], 'u_phone' => $use_det['u_phone'], 'paystate' => 1, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
									$DBUtil->save('wys_tongcheng_payorder', $order_data);
								} else {
									if ($paytype == 'store_order') {
										$arrys_oncode = explode(',', $oncode);
										$shang_mid = current($arrys_oncode);
										$shang_random_code = next($arrys_oncode);
										$res = $DBUtil->update('wys_tongcheng_store_order', array('status' => 1, 'time_pay' => time()), array('oncode' => $shang_mid));
										$store_det = $DBUtil->getOne('wys_tongcheng_store_order', 'oncode=:oncode', array(':oncode' => $shang_mid));
										$use_det = $DBUtil->getOne('wys_tongcheng_user', 'u_openid=:u_openid', array(':u_openid' => $store_det['openid']));
										$order_data = array('uniacid' => $_W['uniacid'], 'oncode' => $store_det['oncode'], 'openid' => $store_det['openid'], 'unionid' => $store_det['unionid'], 'ordertype' => $paytype, 'dt_id' => $store_det['id'], 'orderrmk' => '店铺订单>', 'total_money' => $store_det['total_money'], 'transaction_id' => $params['tag']['transaction_id'], 'u_name' => $use_det['u_nickname'], 'u_phone' => $use_det['u_phone'], 'paystate' => 1, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
										$DBUtil->save('wys_tongcheng_payorder', $order_data);
										$use_det_u = $DBUtil->getOne('wys_tongcheng_user', 'u_openid=:u_openid', array(':u_openid' => $store_det['store_openid']));
										$pay_money = floatval($use_det_u['account']) + floatval($store_det['total_money']);
										$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $store_det['store_openid']));
										$order_infos = $DBUtil->getMany('wys_tongcheng_store_order_info', 'oncode=:oncode', array(':oncode' => $store_det['oncode']));
										foreach ($order_infos as $key_i => $info) {
											$goods_det = $DBUtil->getOne('wys_tongcheng_store_goods', 'id=:id', array(':id' => $info['goods_id']));
											$g_cnt_sale = intval($goods_det['g_cnt_sale']) + intval($info['cnt_buy']);
											$DBUtil->update('wys_tongcheng_store_goods', array('g_cnt_sale' => $g_cnt_sale), array('id' => $info['goods_id']));
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	public function doPageUpdate_pay_auditstatus()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$MyFun = new MyFun();
		$pay_type = $_GPC['ptype'];
		$pay_channel = $_GPC['pay_channel'];
		$setting = $this->module['config'];
		if ($pay_type == 'pay') {
			$dt_id = $_GPC['oncode'];
			$rnum = $_GPC['rnum'];
			$p_order = $DBUtil->getOne('wys_tongcheng_payorder', 'ordertype=:ordertype and random_code=:random_code', array(':ordertype' => $pay_type, ':random_code' => $rnum));
			if (empty($p_order)) {
				$taocan_det = $DBUtil->getOne('wys_tongcheng_paytaocan', 'id=:id', array(':id' => $dt_id));
				$user_det = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
				$p_acc = $taocan_det['pay_money'] + floatval($user_det['account']);
				$p_inte = $taocan_det['song_integral'] + floatval($user_det['integral']);
				$up_account = array('account' => $p_acc, 'integral' => $p_inte);
				$DBUtil->update('wys_tongcheng_user', $up_account, array('u_openid' => $_GPC['openid']));
				$order_data = array('uniacid' => $_W['uniacid'], 'oncode' => $taocan_det['id'], 'openid' => $_GPC['openid'], 'unionid' => '', 'ordertype' => 'pay', 'dt_id' => $taocan_det['id'], 'orderrmk' => '用户充值:', 'total_money' => $_GPC['fee'], 'transaction_id' => $params['tag']['transaction_id'], 'u_name' => $user_det['u_nickname'], 'u_phone' => $user_det['u_phone'], 'paystate' => 2, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
				$DBUtil->save('wys_tongcheng_payorder', $order_data);
			} else {
				$rp_pay_status = 'OK';
				if ($p_order['openid'] == '' || $p_order['openid'] == 'null' || $p_order['openid'] == null) {
					$p_order['openid'] = $_GPC['openid'];
				}
				if ($p_order['total_money'] == '' || $p_order['total_money'] == 'null' || $p_order['total_money'] == null) {
					$p_order['total_money'] = $_GPC['fee'];
				}
				if ($p_order['u_avatarurl'] == '' || $p_order['u_avatarurl'] == 'null' || $p_order['u_avatarurl'] == null) {
					$p_order['u_avatarurl'] = $_GPC['u_avatarurl'];
				}
				if ($p_order['u_name'] == '' || $p_order['u_name'] == 'null' || $p_order['u_name'] == null) {
					$p_order['u_name'] = $_GPC['u_nickname'];
				}
				$p_order['crtime'] = time();
				$DBUtil->update('wys_tongcheng_payorder', $p_order, array('id' => $p_order['id']));
			}
		} else {
			if ($pay_type == 'msg') {
				$dt_id = $_GPC['oncode'];
				$rnum = $_GPC['rnum'];
				$p_order = $DBUtil->getOne('wys_tongcheng_payorder', 'ordertype=:ordertype and random_code=:random_code', array(':ordertype' => $pay_type, ':random_code' => $rnum));
				if (empty($p_order)) {
					$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $_GPC['oncode']));
					$h_mtype_det = $DBUtil->getOne('wys_tongcheng_mtype', 'uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $msg_det['tid']));
					if ($msg_det['payStatus'] == '0') {
						$audit_status = $msg_det['audit_status'];
						if ($audit_status != '1') {
							$audit_status = $h_mtype_det['is_audit'];
							$this->add_msg_status();
						}
						$res = $DBUtil->update('wys_tongcheng_msg', array('audit_status' => $audit_status, 'payStatus' => 1), array('oncode' => $_GPC['oncode']));
						$rp_pay_status = 'faile';
						$order_data = array('pay_channel' => $pay_channel, 'uniacid' => $_W['uniacid'], 'oncode' => $msg_det['oncode'], 'openid' => $msg_det['u_openid'], 'unionid' => $msg_det['u_unionid'], 'ordertype' => 'msg', 'dt_id' => $msg_det['id'], 'orderrmk' => '消息或置顶付费', 'total_money' => $msg_det['total_money'], 'u_name' => $msg_det['u_nickname'], 'u_phone' => $msg_det['u_phone'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'transaction_id' => '', 'paystate' => 2, 'crtime' => time(), 'paycrtime' => time());
						$DBUtil->save('wys_tongcheng_payorder', $order_data);
					} else {
						$rp_pay_status = 'OK';
					}
				} else {
					$rp_pay_status = 'OK';
					$p_order['pay_channel'] = $pay_channel;
					if ($p_order['openid'] == '' || $p_order['openid'] == 'null' || $p_order['openid'] == null) {
						$p_order['openid'] = $_GPC['openid'];
					}
					if ($p_order['total_money'] == '' || $p_order['total_money'] == 'null' || $p_order['total_money'] == null) {
						$p_order['total_money'] = $_GPC['fee'];
					}
					if ($p_order['u_avatarurl'] == '' || $p_order['u_avatarurl'] == 'null' || $p_order['u_avatarurl'] == null) {
						$p_order['u_avatarurl'] = $_GPC['u_avatarurl'];
					}
					if ($p_order['u_name'] == '' || $p_order['u_name'] == 'null' || $p_order['u_name'] == null) {
						$p_order['u_name'] = $_GPC['u_nickname'];
					}
					$p_order['crtime'] = time();
					$DBUtil->update('wys_tongcheng_payorder', $p_order, array('id' => $p_order['id']));
				}
				if ($pay_channel == 'wx') {
				} else {
					if ($pay_channel == 'account') {
						$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
						$pay_money = $pay_user['account'] - floatval($_GPC['fee']);
						$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $_GPC['openid']));
					} else {
						if ($pay_channel == 'integral') {
							$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
							$pay_money = $pay_user['integral'] - floatval($_GPC['fee']) * floatval($setting['integral_pay_bl']);
							$DBUtil->update('wys_tongcheng_user', array('integral' => $pay_money), array('u_openid' => $_GPC['openid']));
						}
					}
				}
			} else {
				if ($pay_type == 'msg_refresh_crtime') {
					$dt_id = $_GPC['oncode'];
					$rnum = $_GPC['rnum'];
					$p_order = $DBUtil->getOne('wys_tongcheng_payorder', 'ordertype=:ordertype and random_code=:random_code', array(':ordertype' => $pay_type, ':random_code' => $rnum));
					if (empty($p_order)) {
						$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $_GPC['oncode']));
						$order_data = array('pay_channel' => $pay_channel, 'uniacid' => $_W['uniacid'], 'oncode' => $msg_det['oncode'], 'openid' => $msg_det['u_openid'], 'unionid' => $msg_det['u_unionid'], 'ordertype' => 'msg_refresh_crtime', 'dt_id' => $msg_det['id'], 'orderrmk' => '消息刷新日期', 'total_money' => $_GPC['fee'], 'u_name' => $msg_det['u_nickname'], 'u_phone' => $msg_det['u_phone'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'transaction_id' => '', 'paystate' => 2, 'crtime' => time(), 'paycrtime' => time());
						$DBUtil->save('wys_tongcheng_payorder', $order_data);
						$rp_pay_status = 'faile';
					} else {
						$rp_pay_status = 'OK';
						$p_order['pay_channel'] = $pay_channel;
						if ($p_order['openid'] == '' || $p_order['openid'] == 'null' || $p_order['openid'] == null) {
							$p_order['openid'] = $_GPC['openid'];
						}
						if ($p_order['total_money'] == '' || $p_order['total_money'] == 'null' || $p_order['total_money'] == null) {
							$p_order['total_money'] = $_GPC['fee'];
						}
						if ($p_order['u_avatarurl'] == '' || $p_order['u_avatarurl'] == 'null' || $p_order['u_avatarurl'] == null) {
							$p_order['u_avatarurl'] = $_GPC['u_avatarurl'];
						}
						if ($p_order['u_name'] == '' || $p_order['u_name'] == 'null' || $p_order['u_name'] == null) {
							$p_order['u_name'] = $_GPC['u_nickname'];
						}
						$p_order['crtime'] = time();
						$DBUtil->update('wys_tongcheng_payorder', $p_order, array('id' => $p_order['id']));
					}
					if ($pay_channel == 'wx') {
					} else {
						if ($pay_channel == 'account') {
							$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
							$pay_money = $pay_user['account'] - floatval($_GPC['fee']);
							$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $_GPC['openid']));
						} else {
							if ($pay_channel == 'integral') {
								$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
								$pay_money = $pay_user['integral'] - floatval($_GPC['fee']) * floatval($setting['integral_pay_bl']);
								$DBUtil->update('wys_tongcheng_user', array('integral' => $pay_money), array('u_openid' => $_GPC['openid']));
							}
						}
					}
				} else {
					if ($pay_type == 'banner') {
						$dt_id = $_GPC['oncode'];
						$rnum = $_GPC['rnum'];
						$p_order = $DBUtil->getOne('wys_tongcheng_payorder', 'ordertype=:ordertype and random_code=:random_code', array(':ordertype' => $pay_type, ':random_code' => $rnum));
						if (empty($p_order)) {
							$sale_banner = $DBUtil->getOne('wys_tongcheng_salebanner', ' uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $_GPC['oncode']));
							if ($sale_banner['paystate'] == '0') {
								$DBUtil->update('wys_tongcheng_banner', array('sale_status' => 1), array('id' => $sale_banner['bn_id']));
								$res = $DBUtil->update('wys_tongcheng_salebanner', array('paystate' => 1, 'paycrtime' => time()), array('oncode' => $_GPC['oncode']));
								$rp_pay_status = 'faile';
								$order_data = array('pay_channel' => $pay_channel, 'uniacid' => $_W['uniacid'], 'oncode' => $sale_banner['oncode'], 'openid' => $sale_banner['openid'], 'unionid' => $sale_banner['unionid'], 'ordertype' => 'banner', 'dt_id' => $sale_banner['m_id'], 'orderrmk' => '幻灯广告位出租消息', 'total_money' => $sale_banner['bn_total_money'], 'transaction_id' => '', 'u_name' => $sale_banner['u_nickname'], 'u_phone' => $sale_banner['u_phone'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'paystate' => 2, 'crtime' => time(), 'paycrtime' => time());
								$DBUtil->save('wys_tongcheng_payorder', $order_data);
							} else {
								$rp_pay_status = 'OK';
							}
						} else {
							$rp_pay_status = 'OK';
							$p_order['pay_channel'] = $pay_channel;
							if ($p_order['openid'] == '' || $p_order['openid'] == 'null' || $p_order['openid'] == null) {
								$p_order['openid'] = $_GPC['openid'];
							}
							if ($p_order['total_money'] == '' || $p_order['total_money'] == 'null' || $p_order['total_money'] == null) {
								$p_order['total_money'] = $_GPC['fee'];
							}
							if ($p_order['u_avatarurl'] == '' || $p_order['u_avatarurl'] == 'null' || $p_order['u_avatarurl'] == null) {
								$p_order['u_avatarurl'] = $_GPC['u_avatarurl'];
							}
							if ($p_order['u_name'] == '' || $p_order['u_name'] == 'null' || $p_order['u_name'] == null) {
								$p_order['u_name'] = $_GPC['u_nickname'];
							}
							$p_order['crtime'] = time();
							$DBUtil->update('wys_tongcheng_payorder', $p_order, array('id' => $p_order['id']));
						}
						if ($pay_channel == 'wx') {
						} else {
							if ($pay_channel == 'account') {
								$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
								$pay_money = $pay_user['account'] - floatval($_GPC['fee']);
								$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $_GPC['openid']));
							} else {
								if ($pay_channel == 'integral') {
									$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
									$pay_money = $pay_user['integral'] - floatval($_GPC['fee']) * floatval($setting['integral_pay_bl']);
									$DBUtil->update('wys_tongcheng_user', array('integral' => $pay_money), array('u_openid' => $_GPC['openid']));
								}
							}
						}
					} else {
						if ($pay_type == 'store_ruzhu') {
							$dt_id = $_GPC['oncode'];
							$rnum = $_GPC['rnum'];
							$p_order = $DBUtil->getOne('wys_tongcheng_payorder', 'ordertype=:ordertype and random_code=:random_code', array(':ordertype' => $pay_type, ':random_code' => $rnum));
							if (empty($p_order)) {
								$store_det = $DBUtil->getOne('wys_tongcheng_store', ' uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $_GPC['oncode']));
								if ($store_det['paystatus'] == '0') {
									$DBUtil->update('wys_tongcheng_store', array('paystatus' => 1, 'pay_time' => time()), array('oncode' => $_GPC['oncode']));
									$rp_pay_status = 'faile';
									$use_det = $DBUtil->getOne('wys_tongcheng_user', 'u_openid=:u_openid', array(':u_openid' => $store_det['openid']));
									$order_data = array('pay_channel' => $pay_channel, 'uniacid' => $_W['uniacid'], 'oncode' => $store_det['oncode'], 'openid' => $store_det['openid'], 'unionid' => $store_det['unionid'], 'ordertype' => 'store_ruzhu', 'dt_id' => $store_det['id'], 'orderrmk' => '店铺入驻', 'total_money' => $store_det['ruzzhu_money'], 'transaction_id' => '', 'u_name' => $use_det['u_nickname'], 'u_phone' => $use_det['u_phone'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'paystate' => 2, 'crtime' => time(), 'paycrtime' => time());
									$DBUtil->save('wys_tongcheng_payorder', $order_data);
								} else {
									$rp_pay_status = 'OK';
								}
							} else {
								$rp_pay_status = 'OK';
								$p_order['pay_channel'] = $pay_channel;
								if ($p_order['openid'] == '' || $p_order['openid'] == 'null' || $p_order['openid'] == null) {
									$p_order['openid'] = $_GPC['openid'];
								}
								if ($p_order['total_money'] == '' || $p_order['total_money'] == 'null' || $p_order['total_money'] == null) {
									$p_order['total_money'] = $_GPC['fee'];
								}
								if ($p_order['u_avatarurl'] == '' || $p_order['u_avatarurl'] == 'null' || $p_order['u_avatarurl'] == null) {
									$p_order['u_avatarurl'] = $_GPC['u_avatarurl'];
								}
								if ($p_order['u_name'] == '' || $p_order['u_name'] == 'null' || $p_order['u_name'] == null) {
									$p_order['u_name'] = $_GPC['u_nickname'];
								}
								$p_order['crtime'] = time();
								$DBUtil->update('wys_tongcheng_payorder', $p_order, array('id' => $p_order['id']));
							}
							if ($pay_channel == 'wx') {
							} else {
								if ($pay_channel == 'account') {
									$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
									$pay_money = $pay_user['account'] - floatval($_GPC['fee']);
									$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $_GPC['openid']));
								} else {
									if ($pay_channel == 'integral') {
										$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
										$pay_money = $pay_user['integral'] - floatval($_GPC['fee']) * floatval($setting['integral_pay_bl']);
										$DBUtil->update('wys_tongcheng_user', array('integral' => $pay_money), array('u_openid' => $_GPC['openid']));
									}
								}
							}
						} else {
							if ($pay_type == 'store_order') {
								$dt_id = $_GPC['oncode'];
								$rnum = $_GPC['rnum'];
								$p_order = $DBUtil->getOne('wys_tongcheng_payorder', 'ordertype=:ordertype and random_code=:random_code', array(':ordertype' => $pay_type, ':random_code' => $rnum));
								$store_det = $DBUtil->getOne('wys_tongcheng_store_order', 'uniacid=:uniacid and oncode=:oncode', array(':uniacid' => $_W['uniacid'], ':oncode' => $_GPC['oncode']));
								$use_det = $DBUtil->getOne('wys_tongcheng_user', 'u_openid=:u_openid', array(':u_openid' => $store_det['openid']));
								if (empty($p_order)) {
									$order_data = array('pay_channel' => $pay_channel, 'uniacid' => $_W['uniacid'], 'oncode' => $store_det['oncode'], 'openid' => $store_det['openid'], 'unionid' => $store_det['unionid'], 'ordertype' => $pay_type, 'dt_id' => $store_det['id'], 'orderrmk' => '店铺订单', 'total_money' => $store_det['total_money'], 'transaction_id' => '', 'u_name' => $use_det['u_nickname'], 'u_phone' => $use_det['u_phone'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'paystate' => 2, 'crtime' => time(), 'paycrtime' => time());
									$DBUtil->save('wys_tongcheng_payorder', $order_data);
								}
								if ($store_det['status'] == '0') {
									$DBUtil->update('wys_tongcheng_store_order', array('status' => 1, 'time_pay' => time()), array('oncode' => $_GPC['oncode']));
									$use_det_u = $DBUtil->getOne('wys_tongcheng_user', 'u_openid=:u_openid', array(':u_openid' => $store_det['store_openid']));
									$pay_money = floatval($use_det_u['account']) + floatval($store_det['total_money']);
									$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $store_det['store_openid']));
									$order_infos = $DBUtil->getMany('wys_tongcheng_store_order_info', 'oncode=:oncode', array(':oncode' => $store_det['oncode']));
									foreach ($order_infos as $key_i => $info) {
										$goods_det = $DBUtil->getOne('wys_tongcheng_store_goods', 'id=:id', array(':id' => $info['goods_id']));
										$g_cnt_sale = intval($goods_det['g_cnt_sale']) + intval($info['cnt_buy']);
										$DBUtil->update('wys_tongcheng_store_goods', array('g_cnt_sale' => $g_cnt_sale), array('id' => $info['goods_id']));
									}
									$rp_pay_status = 'faile';
								} else {
									$rp_pay_status = 'OK';
									$p_order['pay_channel'] = $pay_channel;
									if ($p_order['openid'] == '' || $p_order['openid'] == 'null' || $p_order['openid'] == null) {
										$p_order['openid'] = $_GPC['openid'];
									}
									if ($p_order['total_money'] == '' || $p_order['total_money'] == 'null' || $p_order['total_money'] == null) {
										$p_order['total_money'] = $_GPC['fee'];
									}
									if ($p_order['u_avatarurl'] == '' || $p_order['u_avatarurl'] == 'null' || $p_order['u_avatarurl'] == null) {
										$p_order['u_avatarurl'] = $_GPC['u_avatarurl'];
									}
									if ($p_order['u_name'] == '' || $p_order['u_name'] == 'null' || $p_order['u_name'] == null) {
										$p_order['u_name'] = $_GPC['u_nickname'];
									}
									$p_order['crtime'] = time();
									$DBUtil->update('wys_tongcheng_payorder', $p_order, array('id' => $p_order['id']));
								}
								if ($pay_channel == 'wx') {
								} else {
									if ($pay_channel == 'account') {
										$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
										$pay_money = $pay_user['account'] - floatval($_GPC['fee']);
										$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $_GPC['openid']));
									} else {
										if ($pay_channel == 'integral') {
											$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
											$pay_money = $pay_user['integral'] - floatval($_GPC['fee']) * floatval($setting['integral_pay_bl']);
											$DBUtil->update('wys_tongcheng_user', array('integral' => $pay_money), array('u_openid' => $_GPC['openid']));
										}
									}
								}
							} else {
								if ($pay_type == 'shang') {
									$dt_id = $_GPC['oncode'];
									$rnum = $_GPC['rnum'];
									$p_order = $DBUtil->getOne('wys_tongcheng_payorder', 'ordertype=:ordertype and random_code=:random_code', array(':ordertype' => $pay_type, ':random_code' => $rnum));
									$fee = $_GPC['fee'];
									if (empty($p_order)) {
										$msg_det = $DBUtil->getOne('wys_tongcheng_msg', 'id=:id', array(':id' => $dt_id));
										$up_shangdet = intval($msg_det['shang_cnt']) + 1;
										$DBUtil->update('wys_tongcheng_msg', array('shang_cnt' => $up_shangdet), array('id' => $dt_id));
										$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $msg_det['u_openid']));
										$shang_kcl = $setting['shang_kcl'];
										if (empty($shang_kcl) || $shang_kcl == '') {
											$shang_kcl = 0;
										}
										$shang_kcl = floatval($shang_kcl);
										$fee_user_l = (100 - $shang_kcl) / 100;
										$fee_system_l = 1 - $fee_user_l;
										$fee_touser = floatval($fee) * $fee_user_l;
										$fee_system = floatval($fee) * $fee_system_l;
										$fee_touser = floor($fee_touser * 100) / 100;
										$fee_system = floor($fee_system * 100) / 100;
										$pay_money = $pay_user['account'] + $fee_touser;
										$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $msg_det['u_openid']));
										$user_det = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
										$order_data = array('pay_channel' => $pay_channel, 'uniacid' => $_W['uniacid'], 'oncode' => $msg_det['oncode'], 'openid' => $_GPC['openid'], 'openid_y' => $msg_det['u_openid'], 'unionid' => $msg_det['u_unionid'], 'ordertype' => 'shang', 'dt_id' => $msg_det['id'], 'orderrmk' => '打赏给:' . $pay_user['u_nickname'], 'system_rmk' => '平台扣除' . $fee_system . '元', 'user_money' => $fee_touser, 'system_money' => $fee_system, 'total_money' => $fee, 'transaction_id' => '', 'u_name' => $_GPC['u_nickname'], 'u_phone' => $user_det['u_phone'], 'u_avatarurl' => $_GPC['u_avatarurl'], 'paystate' => 2, 'crtime' => time(), 'paycrtime' => time(), 'random_code' => $shang_random_code);
										$DBUtil->save('wys_tongcheng_payorder', $order_data);
										$rp_pay_status = 'faile';
									} else {
										$p_order['pay_channel'] = $pay_channel;
										$rp_pay_status = 'OK';
										if ($p_order['openid'] == '' || $p_order['openid'] == 'null' || $p_order['openid'] == null) {
											$p_order['openid'] = $_GPC['openid'];
										}
										if ($p_order['total_money'] == '' || $p_order['total_money'] == 'null' || $p_order['total_money'] == null) {
											$p_order['total_money'] = $_GPC['fee'];
										}
										if ($p_order['u_avatarurl'] == '' || $p_order['u_avatarurl'] == 'null' || $p_order['u_avatarurl'] == null) {
											$p_order['u_avatarurl'] = $_GPC['u_avatarurl'];
										}
										if ($p_order['u_name'] == '' || $p_order['u_name'] == 'null' || $p_order['u_name'] == null) {
											$p_order['u_name'] = $_GPC['u_nickname'];
										}
										$p_order['crtime'] = time();
										$DBUtil->update('wys_tongcheng_payorder', $p_order, array('id' => $p_order['id']));
									}
									if ($pay_channel == 'wx') {
									} else {
										if ($pay_channel == 'account') {
											$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
											$pay_money = $pay_user['account'] - floatval($_GPC['fee']);
											$DBUtil->update('wys_tongcheng_user', array('account' => $pay_money), array('u_openid' => $_GPC['openid']));
										} else {
											if ($pay_channel == 'integral') {
												$pay_user = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openid']));
												$pay_money = $pay_user['integral'] - floatval($_GPC['fee']) * floatval($setting['integral_pay_bl']);
												$DBUtil->update('wys_tongcheng_user', array('integral' => $pay_money), array('u_openid' => $_GPC['openid']));
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$rpdata = array('pay_type' => $pay_type, 'rp_pay_status' => $rp_pay_status);
		return $this->result(0, '修改状态', $rpdata);
	}
	public function doPageCheck_user_account()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$setting = $this->module['config'];
		$user_det = $DBUtil->getOne('wys_tongcheng_user', 'uniacid=:uniacid and u_openid=:u_openid', array(':uniacid' => $_W['uniacid'], ':u_openid' => $_GPC['openId']));
		if (!empty($user_det)) {
			$data = array('have_user' => true);
			$ck_money = $_GPC['ck_money'];
			$ck_type = $_GPC['ck_type'];
			if ($ck_type == 'account') {
				$integral_pay_bl = $setting['integral_pay_bl'];
				if (floatval($user_det['integral']) >= floatval($ck_money) * floatval($integral_pay_bl)) {
					$data['ck_status'] = true;
					$data['pay_channel'] = 'integral';
					$data['pay_money'] = floatval($ck_money) * floatval($integral_pay_bl);
				} else {
					if (floatval($user_det['account']) >= floatval($ck_money)) {
						$data['ck_status'] = true;
						$data['pay_channel'] = 'account';
						$data['pay_money'] = floatval($ck_money);
					} else {
						$data['ck_status'] = false;
					}
				}
			} else {
				if ($ck_type == 'account_only') {
					$data['ck_status'] = floatval($user_det['account']) >= floatval($ck_money);
				} else {
					if ($ck_type == 'integral_only') {
						$data['ck_status'] = floatval($user_det['integral']) >= floatval($ck_money);
					} else {
						if ($ck_type == 'all') {
							$data['account'] = $user_det['account'];
							$data['integral'] = $user_det['integral'];
							$money_sxfl = $setting['money_sxfl'];
							if ($money_sxfl == '') {
								$money_sxfl = 0.06;
							}
							$data['money_sxfl'] = $money_sxfl;
						}
					}
				}
			}
		} else {
			$data = array('have_user' => false);
		}
		$data['account'] = $user_det['account'];
		$data['integral'] = $user_det['integral'];
		$errno = 0;
		$message = 'rp_textCC';
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_setting_open()
	{
		global $_GPC, $_W;
		$setting = $this->module['config'];
		$errno = 0;
		$message = 'rp_textCC';
		$tx_isopen = $setting['tx_isopen'];
		if (empty($tx_isopen)) {
			$tx_isopen = 0;
		}
		$data = array('pay_isopen' => $setting['pay_isopen'], 'shang_isopen' => $setting['shang_isopen'], 'tx_isopen' => $tx_isopen);
		return $this->result($errno, $message, $data);
	}
	public function doPageGet_order_list()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$setting = $this->module['config'];
		$integral_pay_bl = $setting['integral_pay_bl'];
		$ordertype = $_GPC['ordertype'];
		$openid = $_GPC['openid'];
		$page = max(1, $_GPC['page']);
		$pagesize = 20;
		if (empty($openid)) {
			$openid = $_W['openid'];
		}
		$sql = 'uniacid=:uniacid';
		$sql_data = array(':uniacid' => $_W['uniacid']);
		if ($ordertype == 'msg') {
			$sql .= ' and openid=:openid';
			$sql_data[':openid'] = $openid;
			$sql .= ' and (ordertype=:ordertype_1 or ordertype=:ordertype_2)';
			$sql_data[':ordertype_1'] = 'msg';
			$sql_data[':ordertype_2'] = 'msg_refresh_crtime';
		} else {
			if ($ordertype == 'banner') {
				$sql .= ' and openid=:openid';
				$sql_data[':openid'] = $openid;
				$sql .= ' and ordertype=:ordertype';
				$sql_data[':ordertype'] = 'banner';
			} else {
				if ($ordertype == 'pay') {
					$sql .= ' and openid=:openid';
					$sql_data[':openid'] = $openid;
					$sql .= ' and (ordertype=:ordertype_1 or ordertype=:ordertype_2)';
					$sql_data[':ordertype_1'] = 'pay';
					$sql_data[':ordertype_2'] = 'back_pay';
				} else {
					if ($ordertype == 'shang_rem') {
						$sql .= ' and openid=:openid';
						$sql_data[':openid'] = $openid;
						$sql .= ' and ordertype=:ordertype';
						$sql_data[':ordertype'] = 'shang';
					} else {
						if ($ordertype == 'shang_add') {
							$sql .= ' and openid_y=:openid_y';
							$sql_data[':openid_y'] = $openid;
							$sql .= ' and ordertype=:ordertype';
							$sql_data[':ordertype'] = 'shang';
						} else {
							if ($ordertype == 'account_tx') {
								$sql .= ' and openid=:openid';
								$sql_data[':openid'] = $openid;
								$sql .= ' and ordertype=:ordertype';
								$sql_data[':ordertype'] = 'account_tx';
							}
						}
					}
				}
			}
		}
		$order_list = $DBUtil->getMany('wys_tongcheng_payorder', $sql, $sql_data, 'paycrtime desc', $page, $pagesize);
		foreach ($order_list as $key => &$it) {
			$it['paycrtime'] = $myfun->friendlyDate($it['paycrtime'], 'ym_time');
			if ($it['pay_channel'] == 'integral') {
				$it['total_money'] = round(floatval($it['total_money']) * floatval($integral_pay_bl));
			}
			$it['orderrmk'] = $myfun->textDecode($it['orderrmk']);
			if ($ordertype == 'shang_rem') {
				$it['shang_type'] = 'to';
			} else {
				if ($ordertype == 'shang_add') {
					$it['shang_type'] = 'go';
				}
			}
		}
		$errno = 0;
		$message = 'rp_textCC';
		return $this->result($errno, $message, $order_list);
	}
	public function doPageGet_account_tx_list()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$myfun = new MyFun();
		$openid = $_GPC['openid'];
		$page = max(1, $_GPC['page']);
		$pagesize = 20;
		if (empty($openid)) {
			$openid = $_W['openid'];
		}
		$sql = 'uniacid=:uniacid and u_openid=:u_openid';
		$sql_data = array(':uniacid' => $_W['uniacid'], ':u_openid' => $openid);
		$order_list = $DBUtil->getMany('wys_tongcheng_user_account_tx', $sql, $sql_data, 'enable asc,crtime desc', $page, $pagesize);
		$errno = 0;
		$message = 'rp_textCC';
		return $this->result($errno, $message, $order_list);
	}
	public function doPageTest1()
	{
		global $_GPC, $_W;
		$DBUtil = new DBUtil();
		$errno = 0;
		$message = 'rp_textCC';
		$data = array();
		return $this->result($errno, $message, $data);
	}
	public function doPageMytest()
	{
		global $_GPC, $_W;
		$errno = 0;
		$message = 'rp_text' . $_W['uniacid'];
		$data = array('messate' => $_GPC['ssm'], 'cnt' => 12);
		return $this->result($errno, $message, $data);
	}
	public function getList_imsg_one($imgs)
	{
		$imgs = explode(',', $imgs);
		foreach ($imgs as $key => $value) {
			if (!empty($value)) {
				return $value;
			}
		}
	}
	public function getList_imsg_list($imgs_str)
	{
		$imgs = explode(',', $imgs_str);
		$img_upurlList = array();
		foreach ($imgs as $key => $it) {
			if ($it != '') {
				array_push($img_upurlList, $it);
			}
		}
		return $img_upurlList;
	}
	public function getScore_m($score)
	{
		$s_str = "";
		for ($i = 1; $i <= intval($score); $i++) {
			$s_str .= '1';
		}
		return $s_str;
	}
	public function getScore_p($score)
	{
		$s_str = "";
		for ($i = 1; $i <= 5 - intval($score); $i++) {
			$s_str .= '1';
		}
		return $s_str;
	}
}