<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?> 
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('tabs', TEMPLATE_INCLUDEPATH)) : (include template('tabs', TEMPLATE_INCLUDEPATH));?> 
<?php  if($operation == 'post') { ?>
<div class="main rightlist">
	<form <?php if( ce('exhelper.senduser' ,$item) ) { ?>action="" method="post" <?php  } ?> class="form-horizontal form" enctype="multipart/form-data">
		<div class="panel panel-default">
			<div class="panel-heading">快递单发件人信息</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span> 发件人</label>
					<div class="col-sm-9 col-xs-12">
						<?php if( ce('exhelper.senduser' ,$item) ) { ?>
							<input type="text" name="sendername" class="form-control" value="<?php  echo $item['sendername'];?>" />
							<span class="help-block">如小张，xx商城</span> <?php  } else { ?>
							<div class='form-control-static'><?php  echo $item['sendername'];?></div>
						<?php  } ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span> 联系电话</label>
					<div class="col-sm-9 col-xs-12">
						<?php if( ce('exhelper.senduser' ,$item) ) { ?>
							<input type="text" name="sendertel" class="form-control" value="<?php  echo $item['sendertel'];?>" /> 
						<?php  } else { ?>
							<div class='form-control-static'><?php  echo $item['sendertel'];?></div>
						<?php  } ?>
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"> 发件地邮编</label>
					<div class="col-sm-9 col-xs-12">
						<?php if( ce('exhelper.senduser' ,$item) ) { ?>
							<input type="text" name="sendercode" class="form-control" value="<?php  echo $item['sendercode'];?>" /> 
						<?php  } else { ?>
							<div class='form-control-static'><?php  echo $item['sendercode'];?></div>
						<?php  } ?>
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span> 发件地址</label>
					<div class="col-sm-9 col-xs-12">
						<?php if( ce('exhelper.senduser' ,$item) ) { ?>
							<input type="text" name="senderaddress" class="form-control" value="<?php  echo $item['senderaddress'];?>" /> 
						<?php  } else { ?>
							<div class='form-control-static'><?php  echo $item['senderaddress'];?></div>
						<?php  } ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"> 发件城市</label>
					<div class="col-sm-9 col-xs-12">
						<?php if( ce('exhelper.senduser' ,$item) ) { ?>
							<input type="text" name="sendercity" class="form-control" value="<?php  echo $item['sendercity'];?>" /> 
						<?php  } else { ?>
							<div class='form-control-static'><?php  echo $item['sendercity'];?></div>
						<?php  } ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">发件人签名</label>
					<div class="col-sm-9 col-xs-12">
						<?php if( ce('exhelper.senduser' ,$item) ) { ?>
							<input type="text" name="sendersign" class="form-control" value="<?php  echo $item['sendersign'];?>" />
							<span class="help-block">如小张，小王</span> 
						<?php  } else { ?>
							<div class='form-control-static'><?php  echo $item['sendersign'];?></div>
						<?php  } ?>
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">是否为默认模板</label>
					<div class="col-sm-9 col-xs-12">
						<?php if( ce('exhelper.senduser' ,$item) ) { ?>
							<label class="radio-inline">
								<input type="radio" name='isdefault' value="1" <?php  if($item[ 'isdefault']==1) { ?>checked<?php  } ?> /> 是
							</label>
							<label class="radio-inline">
								<input type="radio" name='isdefault' value="0" <?php  if($item[ 'isdefault']==0) { ?>checked<?php  } ?> /> 否
							</label>
						<?php  } else { ?>
							<div class='form-control-static'><?php  if($item['isdefault']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
						<?php  } ?>

					</div>
				</div>
				<div class='panel-body'>
					<div class="form-group"></div>
					<div class="form-group">
						<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
						<div class="col-sm-9 col-xs-12">
							<?php if( ce('exhelper.senduser' ,$item) ) { ?>
								<input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1" onclick="return formcheck()" />
								<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" /> 
							<?php  } ?>
							<input type="button" name="back" onclick='history.back()'  <?php if(cv('exhelper.senduser.add|exhelper.senduser.edit')) { ?>style='margin-left:10px;' <?php  } ?> value="返回列表" class="btn btn-default col-lg-1" />
						</div>
					</div>

				</div>
			</div>

	</form>
	</div>
	<script language='javascript'>
		require(['util'], function (u) {
		        $('#cp').each(function () {
		            u.clip(this, $(this).text());
		        });
		    })
		    $('form').submit(function () {
		       if($(':input[name=sendername]').isEmpty()){
		           Tip.focus($(':input[name=sendername]'),'请填写发件人!');
		           return false;
		       }
		        if($(':input[name=sendertel]').isEmpty()){
		           Tip.focus($(':input[name=sendertel]'),'请填写联系电话!');
		           return false;
		       }
		        if($(':input[name=senderaddress]').isEmpty()){
		           Tip.focus($(':input[name=senderaddress]'),'请填写发件地址!');
		           return false;
		       }
		        return true;
		    });
	</script>
	<?php  } else if($operation == 'display') { ?>
	<div class="rightlist">
	<form action="" method="post">
		<div class="panel panel-default">
			<div class="panel-body table-responsive">
				<table class="table table-hover">
					<thead class="navbar-inner">
						<tr>
							<th style="width:30px;">ID</th>
							<th>发件人</th>
							<th>发件人电话</th>
							<th>发件人签名</th>
							<th>发件地邮编</th>
							<th>发件地址</th>
							<th>发件城市</th>
							<th>是否默认</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php  if(is_array($list)) { foreach($list as $row) { ?>
						<tr>
							<td><?php  echo $row['id'];?></td>
							<td><?php  echo $row['sendername'];?></td>
							<td><?php  echo $row['sendertel'];?></td>
							<td><?php  echo $row['sendersign'];?></td>
							<td><?php  echo $row['sendercode'];?></td>
							<td><?php  echo $row['senderaddress'];?></td>
							<td><?php  echo $row['sendercity'];?></td>
							<td>
								<?php  if($row['isdefault']==1) { ?>
								<span class='label label-success'><i class='fa fa-check'></i></span> <?php  } ?>
							</td>
							<td style="text-align:left;">

								<?php if(cv('exhelper.senduser.view|exhelper.senduser.edit')) { ?>
									<a href="<?php  echo $this->createPluginWebUrl('exhelper/senduser', array('op' => 'post', 'id' => $row['id']))?>" class="btn btn-default btn-sm" title="<?php if(cv('exhelper.temps.edit')) { ?>修改<?php  } else { ?>查看<?php  } ?>"><i class="fa fa-edit"></i></a>
								<?php  } ?> 
								<?php if(cv('exhelper.senduser.delete')) { ?>
									<a href="<?php  echo $this->createPluginWebUrl('exhelper/senduser', array('op' => 'delete', 'id' => $row['id']))?>" class="btn btn-default btn-sm" onclick="return confirm('确认删除此模板?')"title="删除"><i class="fa fa-times"></i></a>
								<?php  } ?> 
								<?php if(cv('exhelper.senduser.setdefault')) { ?> 
									<?php  if(empty($row['isdefault'])) { ?>
										<a href="<?php  echo $this->createPluginWebUrl('exhelper/senduser', array('op' => 'setdefault', 'id' => $row['id']))?>" class="btn btn-default btn-sm" onclick="return confirm('确认设置默认?')" title="设置默认"><i class="fa fa-check"></i></a> 
									<?php  } ?> 
								<?php  } ?>
							</td>
						</tr>
						<?php  } } ?>
						<tr>
							<td colspan='8'>
								<?php if(cv('exhelper.senduser.add')) { ?>
									<a class='btn btn-default' href="<?php  echo $this->createPluginWebUrl('exhelper/senduser',array('op'=>'post'))?>"><i class='fa fa-plus'></i> 添加快递单信息模板</a>
									<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" /> 
								<?php  } ?>
							</td>
						</tr>
					</tbody>
				</table>
				<?php  echo $pager;?>
			</div>
		</div>
	</form>
	</div>
	<script>
		require(['bootstrap'], function ($) {
		        $('.btn').hover(function () {
		            $(this).tooltip('show');
		        }, function () {
		            $(this).tooltip('hide');
		        });
		    });
	</script>

	<?php  } ?> 
	</div>
	<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>