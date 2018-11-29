<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>

<div class="page-heading"><h2>系统授权 </h2></div>





<form action="" method="post" class="form-horizontal form-validate" enctype="multipart/form-data" >



    <div class="form-group">

        <label class="col-sm-2 control-label">域名</label>

        <div class="col-sm-9 col-xs-12">

            <input type="text" name="domain" class="form-control" value="<?php  echo $domain;?>" readonly/>

            <span class="help-block">服务器域名</span>

        </div>

    </div>

    <div class="form-group">

        <label class="col-sm-2 control-label">站点IP</label>

        <div class="col-sm-9 col-xs-12">

            <input type="text" name="ip" class="form-control" value="<?php  echo $ip;?>" readonly/>

            <span class="help-block">站点IP</span>

        </div>

    </div>

    <div class="form-group">

        <label class="col-sm-2 control-label">站点ID</label>

        <div class="col-sm-9 col-xs-12">

            <input type="text" name="id" class="form-control" value="<?php  echo $id;?>" readonly/>

            <span class="help-block">站点ID,绑定您的服务器</span>

        </div>

    </div>



    <div class="form-group">

        <label class="col-sm-2 control-label">授权码</label>

        <div class="col-sm-9 col-xs-12">

            <input type="text" name="code" class="form-control" value="<?php  echo $auth['code'];?>" data-rule-required='true' data-msg-required='请填写授权码' />

            <span class="help-block">请联系客服将IP及站点ID提交给客服, 索取授权码，保护好您的授权码，避免泄漏</span>

        </div>

    </div>

    <div class="form-group">

        <label class="col-sm-2 control-label">授权状态</label>

        <div class="col-sm-9 col-xs-12">

            <div class='form-control-static'>

                <?php  if(!empty($result['status'])) { ?>

                <span class='label label-primary'>已授权</span>

                <?php  } else if($status==0) { ?>

                <span class='label label-danger'>未授权</span>

                <?php  } ?>

            </div>

        </div>

    </div>
    <?php  if(!empty($result['status'])) { ?>
    <div class="form-group">
        <label class="col-sm-2 control-label">更新服务到期时间</label>
        <div class="col-sm-9 col-xs-12">
            <div class='form-control-static'>
                <span class='label label-warning'><?php  echo $domain_time;?></span>
            </div>
        </div>
    </div>
    <?php  } ?>
    <?php  if($_W['isfounder']) { ?>
	
	
	<div class="form-group">
       <label class="col-sm-2 control-label">官方网站</label>
        <div class="col-sm-9 col-xs-12">
		    <div class='form-control-static'><a href='http://item.taobao.com/item.htm?id=562179241737' target='_blank'>立即购买授权</a></div>
        </div>
    </div>

    <div class="form-group">

        <label class="col-sm-2 control-label">官方客服</label>

        <div class="col-sm-9 col-xs-12">
		
		    <div class='form-control-static'><a target=blank href=tencent://message/?uin=961999507&Site=人人商城&Menu=yes><img border="0" SRC=http://wpa.qq.com/pa?p=1:961999507:10 alt="点击这里给我发消息"></a>

            <?php  if(empty($result['status'])) { ?>

            <span class='help-block'>如果您正在使用非正版授权，请您尊重我们的劳动成果，谢谢您~</span>

            <span class='help-block'>盗版有风险，请谨慎使用!</span>

            <?php  } else { ?>

            <?php  } ?>

        </div>

    </div>

    <?php  } ?>

    <div class="form-group">

        <label class="col-sm-2 control-label"></label>

        <div class="col-sm-9 col-xs-12">

            <div class='form-control-static'>



                <input type="submit"  value="验证授权" class="btn btn-primary" />

                <?php  if(!empty($result['status'])) { ?>

                <input type="button" style="margin-left:10px;" onclick="location.href='<?php  echo webUrl('system/auth/upgrade')?>'" value="系统升级" class="btn btn-success" />

                <?php  } ?>



            </div>

        </div>

    </div>

</form>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>