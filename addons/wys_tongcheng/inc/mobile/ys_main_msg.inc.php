<?php
global $_W, $_GPC;
$DBUtil = new DBUtil();
$op=$_GPC['op'];

if($op=='det'){
$msg_sql='uniacid=:uniacid and id=:id';
$msg_data=array(':uniacid'=>$_W['uniacid'],':id'=>$_GPC['did']);
$det=$DBUtil->getOne('wys_tongcheng_msg',$msg_sql,$msg_data);
$imglist=$DBUtil->getMany($DBUtil::wys_tongcheng_msg_imgs,' uniacid=:uniacid and oncode=:oncode',array(':uniacid'=>$_W['uniacid'],':oncode'=>$det['oncode']));
include $this->template('ys_det_msg');
return;
}else if($op=='audit'){
//审核
	$status=$_GPC['status'];
	if($status=='1'){
	$res = $DBUtil->update('wys_tongcheng_msg',array('audit_status'=>1),array('id'=>$_GPC['did']));
	}else if($status=='-1'){
	$res = $DBUtil->update('wys_tongcheng_msg',array('audit_status'=>-1,'audit_desc'=>$_GPC['audit_desc']),array('id'=>$_GPC['did']));	
	}else if($status=='-11'){
	//用户拉黑
	$DBUtil->update('wys_tongcheng_user',array('is_black'=>1),array('u_openid'=>$_GPC['openid']));
	$res = $DBUtil->update('wys_tongcheng_msg',array('audit_status'=>-1,'audit_desc'=>$_GPC['audit_desc']),array('id'=>$_GPC['did']));

	}

	//echo $status.'|'.$_GPC['did'];

if($res){
	message('操作成功,返回列表!',$this->createMobileUrl('ys_main_msg'), 'success');
}else{
	message('操作失败,返回列表!',$this->createMobileUrl('ys_main_msg'), 'error');
}

return;
}




$page = max(1, $_GPC['page']);
$pagesize=10;
//消息待审核
$msg_sql='uniacid=:uniacid and audit_status=1';
$msg_data=array(':uniacid'=>$_W['uniacid']);
$list=$DBUtil->getMany('wys_tongcheng_msg',$msg_sql,$msg_data,'crtime desc',$page, $pagesize);

$total=$DBUtil->getCount('wys_tongcheng_msg',$msg_sql, $msg_data);
$result['pager']=pagination($total, $page, $pagesize);
//$list=$DBUtil->getMany($h_tb,$where,$param,$order,$page, $pagesize);

include $this->template('ys_msg');

?>