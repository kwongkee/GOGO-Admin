<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
	<li <?php  if($do == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo url('activity/coupon/display', array());?>">卡券列表</a></li>
	<li <?php  if($do == 'post' && !$couponid) { ?>class="active"<?php  } ?>><a href="javascript:;" data-toggle="modal" data-target="#cardtype-modal">添加卡券</a></li>
	<?php  if($do == 'post' && $couponid) { ?><li class="active"><a href="<?php  echo url('activity/coupon/post', array('id' => $couponid))?>">编辑卡券</a></li><?php  } ?>
</ul>
<div class="modal fade" id="cardtype-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">选择你要创建的卡券类型</h4>
			</div>
			<div class="modal-body clearfix form-horizontal">
				<div class="form-group marbot0">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
					<div class="col-sm-9 col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="type" value="<?php echo COUPON_TYPE_DISCOUNT;?>"/> 折扣券
						</label>
						<div class="help-block">可为用户提供消费折扣</div>
					</div>
				</div>
				<div class="form-group marbot0">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
					<div class="col-sm-9 col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="type" value="<?php echo COUPON_TYPE_CASH;?>"/> 代金券
						</label>
						<div class="help-block">可为用户提供抵扣现金服务。可设置成为“满*元，减*元”</div>
					</div>
				</div>
				<?php  if(COUPON_TYPE == WECHAT_COUPON) { ?>
				<div class="form-group marbot0">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
					<div class="col-sm-9 col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="type" value="<?php echo COUPON_TYPE_GIFT;?>"/> 礼品券
						</label>
						<div class="help-block">可为用户提供消费送赠品服务</div>
					</div>
				</div>
				<div class="form-group marbot0">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
					<div class="col-sm-9 col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="type" value="<?php echo COUPON_TYPE_GROUPON;?>"/> 团购券
						</label>
						<div class="help-block">可为用户提供团购套餐服务</div>
					</div>
				</div>
				<div class="form-group marbot0">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
					<div class="col-sm-9 col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="type" value="<?php echo COUPON_TYPE_GENERAL;?>"/> 优惠券
						</label>
						<div class="help-block">即“通用券”，建议当以上四种无法满足需求时采用</div>
					</div>
				</div>
				<?php  } ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
				<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="location.href='<?php  echo url('activity/coupon/post')?>&type=' + $('#cardtype-modal input[type=radio]:checked').val()">确定</button>
			</div>
		</div>
	</div>
</div>
<?php  if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY || $_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) { ?>
<div class="panel panel-default">
	<div class="panel-heading">
		是否使用系统卡券：
		<input type="checkbox" name="flag" value="0" <?php  if(intval($uni_setting['coupon_type']) == SYSTEM_COUPON) { ?> checked="checked" <?php  } ?>/>
	</div>
</div>
<?php  } ?>
<div class="main">
	<div class="panel panel-info">
	<div class="panel-heading">筛选</div>
	<div class="panel-body">
		<form action="./index.php" method="get" class="form-horizontal" role="form">
		<input type="hidden" name="c" value="activity" />
		<input type="hidden" name="a" value="coupon" />
		<input type="hidden" name="type" value="<?php  echo $_GPC['type'];?>" />
			<div class="form-group">
				<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">卡券类型</label>
				<div class="col-sm-7 col-lg-9 col-xs-12 btn-group">
					<a href="<?php  echo filter_url('type:');?>" class="btn <?php  if($_GPC['type'] == '') { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">不限</a>
					<a href="<?php  echo filter_url('type:'.COUPON_TYPE_DISCOUNT);?>" class="btn <?php  if($_GPC['type'] == COUPON_TYPE_DISCOUNT) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">折扣券</a>
					<a href="<?php  echo filter_url('type:'.COUPON_TYPE_CASH);?>" class="btn <?php  if($_GPC['type'] == COUPON_TYPE_CASH) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">代金券</a>
					<?php  if(COUPON_TYPE == WECHAT_COUPON) { ?>
					<a href="<?php  echo filter_url('type:'.COUPON_TYPE_GIFT);?>" class="btn <?php  if($_GPC['type'] == COUPON_TYPE_GIFT) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">礼品券</a>
					<a href="<?php  echo filter_url('type:'.COUPON_TYPE_GROUPON);?>" class="btn <?php  if($_GPC['type'] == COUPON_TYPE_GROUPON) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">团购券</a>
					<a href="<?php  echo filter_url('type:'.COUPON_TYPE_GENERAL);?>" class="btn <?php  if($_GPC['type'] == COUPON_TYPE_GENERAL) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">优惠券</a>
					<?php  } ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">审核状态</label>
				<div class="col-sm-7 col-lg-9 col-xs-12">
					<div class="btn-group">
						<a href="<?php  echo filter_url('status:');?>" class="btn <?php  if($_GPC['status'] == '') { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">不限</a>
						<a href="<?php  echo filter_url('status:1');?>" class="btn <?php  if($_GPC['status'] == 1) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">审核中</a>
						<a href="<?php  echo filter_url('status:2');?>" class="btn <?php  if($_GPC['status'] == 2) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">未通过</a>
						<a href="<?php  echo filter_url('status:3');?>" class="btn <?php  if($_GPC['status'] == 3) { ?>btn-primary<?php  } else { ?>btn-default<?php  } ?>">已通过</a>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">可用会员组</label>
				<div class="col-sm-7 col-lg-9 col-xs-12">
					<select name="groupid" class="form-control">
						<?php  if(is_array($groups)) { foreach($groups as $row) { ?>
							<option <?php  if($_GPC['groupid'] == $row['id']) { ?>selected<?php  } ?> value="<?php  echo $row['id'];?>"><?php  echo $row['name'];?></option>
						<?php  } } ?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">可用门店</label>
				<div class="col-sm-7 col-lg-9 col-xs-12">
					<select name="storeid" class="form-control">
						<option value="0">不限</option>
						<?php  if(is_array($location_store)) { foreach($location_store as $row) { ?>
							<option <?php  if($_GPC['storeid'] == $row['id']) { ?>selected<?php  } ?> value="<?php  echo $row['id'];?>"><?php  echo $row['business_name'];?></option>
						<?php  } } ?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">可用模块</label>
				<div class="col-sm-7 col-lg-9 col-xs-12">
					<select name="moduleid" class="form-control">
						<option value="0">不限</option>
						<?php  if(is_array($module)) { foreach($module as $row) { ?>
							<?php  if(empty($row['issystem'])) { ?>
							<option <?php  if($_GPC['moduleid'] == $row['name']) { ?>selected<?php  } ?> value="<?php  echo $row['name'];?>"><?php  echo $row['title'];?></option>
							<?php  } ?>
						<?php  } } ?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">券标题</label>
				<div class="col-sm-7 col-lg-9 col-xs-12">
					<input class="form-control" name="title" placeholder="券标题" type="text" value="<?php  echo $_GPC['title'];?>">
				</div>
				<div class="pull-right col-xs-12 col-sm-3 col-lg-2">
					<button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="quantity-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">修改库存</h4>
			</div>
			<div class="modal-body clearfix form-horizontal">
				<input type="text" class="form-control" name="quantity" />
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
				<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="util.ajaxshow('<?php  echo url('activity/coupon/modifystock')?>&id=&quantity=' + $('input[name=quantity]').val() + '&id='+window.couponid)">确定</button>
			</div>
		</div>
	</div>
</div>
<div class="alert alert-info">
	<?php  if(COUPON_TYPE == SYSTEM_COUPON) { ?>您现在使用的是系统卡券，强烈建议您开通微信卡券功能 <br /><?php  } ?>
	如果您希望在会员在线上消费时，可以使用优惠券来减免金额，请确保 <a href="<?php  echo url('profile/payment')?>" target="_blank">支付参数</a> 中的卡券开关为："使用系统卡券"
</div>
<div class="panel panel-default">
	<div class="table-responsive panel-body">
		<table class="table table-hover">
			<thead class="navbar-inner">
				<tr>
					<th width="80px">卡券类型</th>
					<th width="120px">卡券名称</th>
					<th width="150px">卡券有效期</th>
					<th width="70px">状态</th>
					<th width="100px">库存</th>
					<th width="50px">限领</th>
					<th width="80px">上架状态</th>
					<th style="width:450px; text-align:right;">操作</th>
				</tr>
			</thead>
			<tbody>
				<?php  if(is_array($list)) { foreach($list as $item) { ?>
				<tr>
					<td><?php  echo $item['type']['0'];?></td>
					<td><?php  echo $item['title'];?></td>
					<td>
						<?php  echo $item['date_info'];?>
					</td>
					<td>
						<?php  if($item['status'] == '1') { ?>
						<span class="label label-info">审核中</span>
						<?php  } else if($item['status'] == '2') { ?>
						<span class="label label-danger">未通过</span>
						<?php  } else if($item['status'] == '3') { ?>
						<span class="label label-success">已通过</span>
						<?php  } else if($item['status'] == '4') { ?>
						<span class="label label-default">卡券被商户删除</span>
						<?php  } else if($item['status'] == '5') { ?>
						<span class="label label-warning">已在公众平台投放</span>
						<?php  } ?>
					</td>
					<td><?php  echo $item['quantity'];?></td>
					<td><?php  echo $item['get_limit'];?></td>
					<td>
						<?php  if($item['is_display'] == 1) { ?>
							<span class="label label-success">上架中</span>
						<?php  } else { ?>
							<span class="label label-danger">已下架</span>
						<?php  } ?>
					</td>
					<td style="text-align:right;">
						<?php  if(COUPON_TYPE == WECHAT_COUPON) { ?><a href="javascript:;" onclick="util.ajaxshow('<?php  echo url('activity/coupon/selfconsume', array('id' => $item['id']))?>')" class="btn <?php  if($item['is_selfconsume'] == 1) { ?>btn-danger<?php  } else { ?>btn-success<?php  } ?> btn-sm toggle-selfconsume" title="自助核销"><?php  if($item['is_selfconsume'] == 1) { ?>关闭自助核销<?php  } else { ?>开启自助核销<?php  } ?></a>
						<a href="#put-coupon" data-toggle="modal" data-cid=<?php  echo $item['id'];?> class="btn btn-default btn-sm js-publish">投放</a>
						<?php  } ?>
						<a href="javascript:;" class="btn btn-default btn-sm" onclick="window.couponid = '<?php  echo $item['id'];?>';$('input[name=quantity]').val('');" data-toggle="modal" data-target="#quantity-modal">修改库存</a>
						<a href="javascript:;" onclick="util.ajaxshow('<?php  echo url('activity/coupon/toggle', array('id' => $item['id']))?>')" class="btn btn-default btn-sm toggle-display" title="上架/下架"><?php  if($item['is_display'] == 1) { ?>下架<?php  } else { ?>上架<?php  } ?></a>
						<a href="<?php  echo url('activity/coupon/detail', array('id' => $item['id']))?>" class="btn btn-success btn-sm" title="查看详情">查看详情</a>
						<a href="<?php  echo url('activity/consume/display', array('couponid' => $item['id'], 'status' => '3'))?>"  class="btn btn-default btn-sm" title="领取记录">领取记录</a>
						<a href="<?php  echo url('activity/coupon/del', array('id' => $item['id']))?>"  class="btn btn-default btn-sm" title="删除" onclick="if(!confirm('删除后将不可恢复，确定删除吗?')) return false;">删除</a>
					</td>
				</tr>
				<?php  } } ?>
			</tbody>
		</table>
		</div>
	</div>
	<?php  if(COUPON_TYPE == WECHAT_COUPON) { ?>
	<a class="btn btn-success js-sync pull-left" onclick="util.ajaxshow('<?php  echo url('activity/coupon/sync', array('type' => 1))?>')">更新全部卡券最新状态</a>
	<?php  } ?>
	<div class="pull-right"><?php  echo $pager;?></div>
</div>
<?php  if(COUPON_TYPE == WECHAT_COUPON) { ?>
<div class="modal fade" id="put-coupon">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="form-group">
					<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="active"><a href="#put-qrcode" aria-controls="qrcode" role="tab" data-toggle="tab">二维码</a></li>
					<li role="presentation"><a href="#put-coupon-record" aria-controls="put-coupon-record" role="tab" data-toggle="tab">领取记录</a></li>
				</ul>
				</div>
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active text-center" id="put-qrcode">
						<div class="alert alert-info text-left">
							该投放二维码有效期至：<span class="js-qrcode-expire"></span> <br />
							如果您要大量投放卡券，请使用卡券营销功能
						</div>
						<img src="" class="js-qrcode-src">
					</div>
					<div role="tabpanel" class="tab-pane" id="put-coupon-record">
						<div class="alert alert-info text-left">
							共有 <span class="js-qrcode-record-total"></span> 人领取
						</div>
						<table class="table table-hover">
							<thead class="table table-hover">
								<tr>
									<th style="width: 20%;"></th>
									<th>昵称</th>
									<th style="width: 30%;">领取时间</th>
								</tr>
							</thead>
							<tbody class="js-qrcode-record"></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click', '.js-publish', function() {
		cid = $(this).data('cid');
		$.post("<?php  echo url('activity/coupon/publish')?>", {'cid' : cid}, function(data) {
			var data = $.parseJSON(data);
			if (data.message.errno == '0') {
				var coupon = data.message.message.coupon;
				$('.js-qrcode-src').attr('src', coupon.url);
				$('.js-qrcode-expire').html(coupon.expire);
				$('.js-qrcode-record-total').html(data.message.message.total);
				var record = data.message.message.record;
				if (record) {
					$('.js-qrcode-record').html('');
					for (i in record) {
						$('.js-qrcode-record').append('<tr><td><img src="'+record[i].avatar+'" width="50" /></td><td><a href="#">'+record[i].nickname+'</a></td><td><a href="#">'+record[i].createtime+'</a></td></tr>');
					}
				}
			} else {
				$('#put-coupon').modal('hide');
				util.message(data.message.message);
			}
		});
	});
</script>
<?php  } ?>
<script>
	$(function(){
		require(['bootstrap.switch'], function() {
			angular.bootstrap(document, ['app']);
			$(":checkbox[name='flag']").bootstrapSwitch();
			$(':checkbox').on('switchChange.bootstrapSwitch', function(e, state){
				$this = $(this);
				var status = this.checked ? 1 : 2;
				$.post("<?php  echo url('activity/coupon/exchange_coupon_type');?>", {'status' : status}, function(resp){
					resp = $.parseJSON(resp);
					if (resp.message.errno < 0) {
						util.message(resp.message.message, location.href, 'error');
					} else {
						util.message(resp.message.message, location.href, 'success');
					}
				});
			});
		});
	});
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>