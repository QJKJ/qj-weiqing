<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<div class="new-keyword">
	<ol class="breadcrumb we7-breadcrumb">
		<a href="<?php  echo url('user/display');?>"><i class="wi wi-back-circle"></i> </a>
		<li><a href="<?php  echo url('user/display');?>">用户管理</a></li>
		<li>添加用户</li>
	</ol>
	<form action="" class="we7-form" method="post" id="js-user-create" ng-controller="UserCreate" ng-cloak>
		
		<div class="form-group">
			<label for="" class="control-label col-sm-2">用户名</label>
			<div class="form-controls col-sm-8">
				<input type="text" name="username" id="username" ng-style="{'width': '600px'}" class="form-control" ng-model="user.username" placeholder="" autocomplete="off">
				<div class="help-block">请输入用户名，用户名为 3 到 15 个字符组成，包括汉字，大小写字母（不区分大小写）</div>
			</div>
		</div>
		<div class="form-group">
			<label for="" class="control-label col-sm-2">密码</label>
			<div class="form-controls col-sm-8">
				<input type="text" value="" class="hidden"/>
				<input type="text" name="password" id="password" ng-style="{'width': '600px'}" class="form-control" ng-model="user.password" placeholder="" autocomplete="off" ng-focus="changeType($event)">
				<div class="help-block">请填写密码，最小长度为 8 个字符</div>
			</div>
		</div>
		<div class="form-group">
			<label for="" class="control-label col-sm-2">确认密码</label>
			<div class="form-controls col-sm-8">
				<input type="text" name="repassword" id="repassword" ng-style="{'width': '600px'}" class="form-control" ng-model="user.repassword" placeholder="" autocomplete="off" ng-focus="changeType($event)">
				<div class="help-block">重复输入密码，确认正确输入</div>
			</div>
		</div>
		<div class="form-group">
			<label for="" class="control-label col-sm-2">所属用户组</label>
			<div class="form-controls col-sm-8">
				<select name="groupid" class="we7-select" id="groupid">
					<option value="0">请选择所属用户组</option>
					<option ng-repeat="group in groups" ng-value="group.id" ng-bind="group.name"></option>
				</select>
				<span class="help-block"> 分配用户所属用户组后，该用户会自动拥有此用户组内的模块操作权限</span>
				<span class="help-block"><strong class="text-danger">设置用户组后，系统会根据对应用户组的服务期限对用户的服务开始时间和结束时间进行初始化</strong></span>
			</div>
		</div>
		
			<?php  if(permission_check_account_user('see_user_create_own_vice_founder')) { ?>
			<div class="form-group">
				<label for="" class="control-label col-sm-2">所属副创始人</label>
				<div class="form-controls col-sm-8">
					<input type="text" name="vice_founder_name" id="vice_founder" ng-style="{'width': '200px'}" class="form-control" ng-model="user.vice_founder_name" placeholder="" autocomplete="off">
					<div class="help-block">请输入副创始人姓名</div>
				</div>
			</div>
			<?php  } ?>
		
		<div class="form-group">
			<label for="" class="control-label col-sm-2">备注</label>
			<div class="form-controls col-sm-8">
				<textarea name="remark" rows="6" class="form-control" ng-style="{'width': '600px'}" ng-bind="user.remark" placeholder="方便注明此用户的身份"></textarea>
			</div>
		</div>
		<input type="submit" name="submit" id="" value="提交" class="btn btn-primary" ng-click="checkSubmit($event)" ng-style="{'padding': '6px 50px'}"/>
		<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
		<input type="hidden" name="do" value="<?php  echo $_GPC['do'];?>" />
	</form>
</div>
<script type="text/javascript">
	angular.module('userManageApp').value('config', {
		groups: <?php echo !empty($groups) ? json_encode($groups) : 'null'?>,
	});
	angular.bootstrap($('#js-user-create'), ['userManageApp']);
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>

