<?php
global $_W, $_GPC;

$DBUtil = new DBUtil();
//******需要修改的地方 开始******
//模板前缀
$h_name='user';
//操作名称
$h_title=urlencode('用户库');
//表名
$h_tb=$DBUtil::wys_tongcheng_user;
//排序
$order=' id desc';
//页面显示条数
$pagesize=10;

$op=!empty($_GPC['op'])?$_GPC['op']:'list';
$page = max(1, $_GPC['page']);
//******需要修改的地方 结束******

//表单提交
if(checksubmit()){
//******需要修改的地方******
$data=array(
	'uniacid'=>$_W['uniacid'],
	'tname'=>$_GPC['tname'],
	'send_money'=>trim($_GPC['send_money']),
	'warn_words'=>trim($_GPC['warn_words']),
	'is_audit'=>trim($_GPC['is_audit']),
	'img'=>trim($_GPC['img']),

	'show_index'=>trim($_GPC['show_index']),
	'pxh'=>trim($_GPC['pxh']),
	'manager_openid'=>trim($_GPC['manager_openid']),
	'enable'=>trim($_GPC['enable']),
	'crtime'=>time()
	);

if($op=='add'){	
	$res=$DBUtil->save($h_tb,$data);
//echo var_dump($data);exit;
	if($res){message('新增成功', $this->createWebUrl($h_name.'_action'), 'success');
}else{
	essage('新增失败', $this->createWebUrl($h_name.'_action'), 'error');
}
return;
}else if($op=='edit'){
	$id=$_GPC['id'];
	$res=$DBUtil->update($h_tb,$data,array('id'=>$id));

	if($res){message('修改成功', $this->createWebUrl($h_name.'_action'), 'success');
}else{
	message('修改失败', $this->createWebUrl($h_name.'_action'), 'error');
}
return;
}else if($op=='list'){
 
}

}
//列表批量删除
if(checksubmit('delete_selectd')){
$res = $DBUtil->delete($h_tb,array('id'=>$_GPC['ids']));
if($res){message('删除'.count($_GPC['ids']).'条成功!',referer(), 'success');
}else{message('删除失败!',referer(), 'error');}
return;	
}


//页面转及初始化其它操作说明模版
if($op=='pay_account'){
$id=$_GPC['id'];
$sql_text=$_GPC['sql_text'];//搜索的信息
$user_det=$DBUtil->getOne('wys_tongcheng_user','id=:id',array(':id'=>$id));


$account=$_GPC['account']==''?'0':$_GPC['account'];
$integral=$_GPC['integral']==''?'0':$_GPC['integral'];


//增加充值记录
$order_data=array(
			'pay_channel'=>'back_pay',
			'uniacid'=>$_W['uniacid'],
			'oncode'=>$user_det['id'],
			'openid'=>$user_det['u_openid'],
			'unionid'=>$user_det['u_unionid'],
			'ordertype'=>'back_pay',
			'dt_id'=>$taocan_det['id'],
			'orderrmk'=>'后台冲值:帐户:'.$account.'元,积分:'.$integral,
			'total_money'=>$account,
			'system_money'=>$integral,
			'transaction_id'=>$params['tag']['transaction_id'],
			'u_name'=>$user_det['u_nickname'],
			'u_phone'=>$user_det['u_phone'],
			'paystate'=>3,
			'crtime'=>time(),
			'paycrtime'=>time()
			);

$DBUtil->save('wys_tongcheng_payorder',$order_data);


// echo $_GPC['uid'].'</br>';
// echo $_GPC['account'].'</br>';
if($integral!=''){
	$user_det['integral']=floatval($user_det['integral'])+floatval($integral);
}

if($account!=''){
	$user_det['account']=floatval($user_det['account'])+floatval($account);
}

$res = $DBUtil->update('wys_tongcheng_user',$user_det,array('id'=>$id));
if($res){
	message('充值成功!',referer(), 'success');
}else{
	message('充值失败!',referer(), 'error');
}
}else if($op=='del'){
$id=$_GPC['id'];	
$res = $DBUtil->delete($h_tb,array('uniacid'=>$_W['uniacid'], 'id'=>$id));
if($res){
	message('删除成功!',referer(), 'success');
}else{
	message('删除失败!',referer(), 'error');
}
}else if($op=='add'){
/*增加表单*/
//表单初始化开始
$det['enable']=1;
$det['show_index']=1;
$det['pxh']=0;
$det['send_money']=10;
//表单初始化结束

$pagediv='form';
include $this->template('web/'.$h_name.'_page');
return;	
}else if($op=='edit'){
$id=$_GPC['id'];
$where='uniacid=:uniacid and id=:id';
$param=array(':uniacid'=>$_W['uniacid'],':id'=>$_GPC['id']);
$det=$DBUtil->getOne($h_tb,$where,$param);
//$start_diqu = $DBUtil->getDiqus('uniacid=:uniacid AND attr=:attr', array(':uniacid'=>$_W['uniacid'], ':attr'=>0));
include $this->template('web/'.$h_name.'_page');
return;
}else if($op=='enable'){
//禁用
$id=$_GPC['id'];
$res=$DBUtil->update($h_tb,array('enable'=>$_GPC['enable']),array('id'=>$id));
if($res){message('修改成功', $this->createWebUrl($h_name.'_action'), 'success');
}else{
	message('修改失败', $this->createWebUrl($h_name.'_action'), 'error');
}
include $this->template('web/'.$h_name.'_page');
return;
}else if($op=='black'){
//禁用
$id=$_GPC['id'];
$res=$DBUtil->update($h_tb,array('is_black'=>$_GPC['enable']),array('id'=>$id));
if($res){message('修改成功', $this->createWebUrl($h_name.'_action'), 'success');
}else{
	message('修改失败', $this->createWebUrl($h_name.'_action'), 'error');
}
include $this->template('web/'.$h_name.'_page');
return;
}else if($op=='list'||$op==''){

$pagediv='list';
$list_page='list';
$l_t='list';
$where='uniacid=:uniacid';
$param=array(':uniacid'=>$_W['uniacid']);
$total=$DBUtil->getCount($h_tb,$where, $param);
//生成分页HTML
$result['pager']=pagination($total, $page, $pagesize);
$list=$DBUtil->getMany($h_tb,$where,$param,$order,$page, $pagesize);
foreach ($list as $key => &$value) {

	$value['account']=floor(floatval($value['account'])*100)/100;
	$value['cnt_send']=$DBUtil->getCount('wys_tongcheng_msg','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));
	$value['cnt_pl']=$DBUtil->getCount('wys_tongcheng_comments','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));
	$value['cnt_goods']=$DBUtil->getCount('wys_tongcheng_goods','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));

	$value['cnt_shang']=$DBUtil->getCount('wys_tongcheng_payorder','uniacid=:uniacid and openid=:openid and ordertype=:ordertype',array(':uniacid'=>$_W['uniacid'],':openid'=>$value['u_openid'],':ordertype'=>'shang'));

}

include $this->template('web/'.$h_name.'_page');
return;	
}else if($op=='list_back'){
//所有黑名单

$pagediv='list';
$list_page='list_back';
$l_t='list';
$where='uniacid=:uniacid and is_black=:is_black';
$param=array(':uniacid'=>$_W['uniacid'],':is_black'=>'1');
$total=$DBUtil->getCount($h_tb,$where, $param);
//生成分页HTML
$result['pager']=pagination($total, $page, $pagesize);
$list=$DBUtil->getMany($h_tb,$where,$param,$order,$page, $pagesize);
foreach ($list as $key => &$value) {

	$value['account']=floor(floatval($value['account'])*100)/100;
	$value['cnt_send']=$DBUtil->getCount('wys_tongcheng_msg','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));
	$value['cnt_pl']=$DBUtil->getCount('wys_tongcheng_comments','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));
	$value['cnt_goods']=$DBUtil->getCount('wys_tongcheng_goods','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));

	$value['cnt_shang']=$DBUtil->getCount('wys_tongcheng_payorder','uniacid=:uniacid and openid=:openid and ordertype=:ordertype',array(':uniacid'=>$_W['uniacid'],':openid'=>$value['u_openid'],':ordertype'=>'shang'));

}

include $this->template('web/'.$h_name.'_page');
return;	
}else if($op=='list_search'){
$l_t='list_search';
$list_page='list';
$pagediv='list';
$where='uniacid=:uniacid';
$param=array(':uniacid'=>$_W['uniacid']);
$sql_text=$_GPC['sql_text'];//搜索的信息
echo "sql_text:".$sql_text;
if(!empty($sql_text)){
$where.=' and (u_nickname like :cont or u_phone like :cont or u_openid like :cont)';
$param[':cont']='%'.$sql_text.'%';
}

$total=$DBUtil->getCount($h_tb,$where, $param);
//生成分页HTML
$result['pager']=pagination($total, $page, $pagesize);
$list=$DBUtil->getMany($h_tb,$where,$param,$order,$page, $pagesize);

foreach ($list as $key => &$value) {
$value['u_openid']=str_replace($sql_text,'<font color=\'red\' weight=\'bold\'>'.$sql_text.'</font>',$value['u_openid']);
$value['u_phone']=str_replace($sql_text,'<font color=\'red\'>'.$sql_text.'</font>',$value['u_phone']);
$value['u_nickname']=str_replace($sql_text,'<font color=\'red\'>'.$sql_text.'</font>',$value['u_nickname']);

$value['cnt_send']=$DBUtil->getCount('wys_tongcheng_msg','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));
$value['cnt_pl']=$DBUtil->getCount('wys_tongcheng_comments','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));
$value['cnt_goods']=$DBUtil->getCount('wys_tongcheng_goods','uniacid=:uniacid and u_openid=:u_openid',array(':uniacid'=>$_W['uniacid'],':u_openid'=>$value['u_openid']));

$value['cnt_shang']=$DBUtil->getCount('wys_tongcheng_payorder','uniacid=:uniacid and openid=:openid and ordertype=:ordertype',array(':uniacid'=>$_W['uniacid'],':openid'=>$value['u_openid'],':ordertype'=>'shang'));

}

include $this->template('web/'.$h_name.'_page');
return;	
}

?>