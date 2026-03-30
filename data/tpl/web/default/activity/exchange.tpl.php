<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php  if($type == 'coupon') { ?>
<ul class="nav nav-tabs">
	<li <?php  if($do == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo url('activity/exchange/display', array('type' => 'coupon'));?>">管理卡券兑换</a></li>
	<li <?php  if($do == 'post' && !$id) { ?>class="active"<?php  } ?>><a href="<?php  echo url('activity/exchange/post', array('type' => 'coupon'))?>" id="add-card">添加卡券兑换</a></li>
	<?php  if($do == 'post' && $id) { ?><li class="active"><a href="<?php  echo url('activity/exchange/coupon', array('id' => $id))?>">编辑卡券兑换</a></li><?php  } ?>
</ul>
<?php  if($do == 'display') { ?>
<div class="panel panel-default">
	<div class="panel-heading">
		是否启用兑换中心：
		<input type="checkbox" name="flag" value="1" <?php  if(intval($uni_setting['exchange_enable']) == 1) { ?> checked="checked" <?php  } ?>/>
	</div>
</div>
<div class="main">
	<div class="panel panel-info">
		<div class="panel-heading">筛选</div>
		<div class="panel-body">
			<form action="./index.php" method="get" class="form-horizontal" role="form">
				<input type="hidden" name="c" value="activity" />
				<input type="hidden" name="a" value="exchange" />
				<input type="hidden" name="do" value="coupon" />
				<input type="hidden" name="op" value="display" />
				<div class="form-group">
					<label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">卡券名称</label>
					<div class="col-sm-7 col-lg-8 col-xs-12">
						<input class="form-control" name="title" type="text" value="<?php  echo $_GPC['title'];?>">
					</div>
					<div class="pull-right col-lg-2">
						<input type="submit" name="submit" class="btn btn-default" value="搜索">
						<input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php  if(empty($list)) { ?>
	<div class="alert alert-info">
		您当前没有兑换活动
	</div>
	<?php  } else { ?>
	<div class="panel panel-default">
		<div class="table-responsive panel-body">
			<table class="table table-hover">
				<thead class="navbar-inner">
				<tr>
					<th style="width:150px;">卡券名称</th>
					<th style="width:200px;">兑换条件</th>
					<th style="width:200px;">兑换次数</th>
					<th style="width:200px;">每人限领</th>
					<th style="width:200px;">兑换时间</th>
					<th style="width:150px;">兑换状态</th>
					<th style="width:200px;">操作</th>
				</tr>
				</thead>
				<tbody>
				<?php  if(is_array($list)) { foreach($list as $item) { ?>
				<tr>
					<td><?php  echo $item['coupon']['title'];?></td>
					<td>
						<?php  if($item['credittype'] == 'credit1') { ?>
						积分兑换
						<?php  } else if($item['credittype'] == 'credit2') { ?>
						余额兑换
						<?php  } ?>
					</td>
					<td><?php  echo $item['num'];?></td>
					<td><?php  echo $item['pretotal'];?></td>
					<td><?php  echo $item['starttime'];?> - <?php  echo $item['endtime'];?></td>
					<td>
						<input class="js-flag" type="checkbox" data-id="<?php  echo $item['id'];?>" <?php  if($item['status']) { ?>checked<?php  } ?>/>
						<script>
							require(['bootstrap.switch'],function($){
								$('.js-flag:checkbox').bootstrapSwitch({onText: '启用', offText: '关闭'});
								$('.js-flag:checkbox').on('switchChange.bootstrapSwitch', function(event, state) {
									var id = $(this).data('id');
									var ban = state ? 1 : 0;
									$.getJSON("<?php  echo url('activity/exchange/change_status')?>", {id:id, status:ban}, function(data) {
										var data = eval(data.message);
									});
								});
							});
						</script>
					</td>
					<td>
						<a href="<?php  echo url('activity/exchange/post', array('id' => $item['id'], 'type' => 'coupon'))?>">查看详情</a>
						<a href="<?php  echo url('activity/exchange/delete', array('id' => $item['id'], 'type' => 'coupon'))?>" onclick="return confirm('确定删除卡券兑换吗？');return false;">删除</a>
					</td>
				</tr>
				<?php  } } ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php  } ?>
	<?php  echo $pager;?>
</div>
<script type="text/javascript">
	$(function(){
		require(['bootstrap.switch'], function() {
			angular.bootstrap(document, ['app']);
			$(":checkbox[name='flag']").bootstrapSwitch();
			$(':checkbox').on('switchChange.bootstrapSwitch', function(e, state){
				$this = $(this);
				var status = this.checked ? 1 : 0;
				$.post("<?php  echo url('activity/exchange/exchange_enable');?>", {'status' : status}, function(resp){
					resp = $.parseJSON(resp);
					if (resp.message.errno < 0) {
						util.message(resp.message.message, location.href, 'error');
					} else {
						util.message(resp.message.message, location.href, 'success');
					}
				});
			});
		});
		$('.modules').click(function(){
			return false;
		});
	});
</script>
<?php  } else if($do == 'post') { ?>
<form action="<?php  echo url('activity/exchange/post', array('type' => 'coupon'))?>" method="post" class="form-horizontal">
	<div class="panel panel-default">
		<div class="panel-heading">筛选</div>
		<div class="panel-body">
			<div class="clearfix">
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">* </span> 选择卡券</label>
					<div class="col-sm-8 col-xs-12">
						<?php  if(!$id) { ?>
						<button type="button" class="btn btn-default" data-toggle="modal" data-target="#myModal" id="add_coupon">添加卡券</button>
						<?php  } ?>
						<img src="<?php  echo $data['coupon']['logo_url'];?>" id="coupon_image" style="height: 100px;width: 240px;<?php  if(!$id) { ?>display: none;<?php  } ?>" data-id="<?php  echo $data['coupon']['id'];?>">
						<span class="help-block" id="coupon_title"><?php  echo $data['coupon']['title'];?></span>
						<input type="hidden" name="coupon">
					</div>
					<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
						<div class="modal-dialog" style="width: 450px;">
							<div class="modal-content">
								<div class="modal-header">
									<h4 class="modal-title">选择卡券</h4>
								</div>
								<div class="modal-body">
									<?php  if(empty($coupons)) { ?>
									您现在没有卡券，请先<a href="<?php  echo url('activity/coupon/display', array('flag' => 'exchange'))?>">添加卡券</a>。
									<?php  } else { ?>
									<table class="table">
										<thead style="height: 20px;">
										<th style="width: 60px">缩略图</th>
										<th style="width: 120px">卡券名称</th>
										<th style="width: 100px">类型</th>
										<th style="width: 80px">选择</th>
										</thead>
										<tbody>
										<?php  $types = activity_coupon_type_label();?>
										<?php  if(is_array($coupons)) { foreach($coupons as $coupon) { ?>
										<tr style="height: 20px;">
											<?php  if(COUPON_TYPE == WECHAT_COUPON) { ?>
											<td><img src="<?php  echo $coupon['logo_url']?>" style="height: 10px;"></td>
											<td><?php  echo $coupon['title'];?></td>
											<td>
												<?php  echo $types[$coupon['type']]['0'];?>
											</td>
											<?php  } else { ?>
											<td><img src="<?php  echo $coupon['logo_url']?>" style="height: 30px;"></td>
											<td><?php  echo $coupon['title'];?></td>
											<td><?php  if($coupon['type'] == 2) { ?>代金券<?php  } else { ?>折扣券<?php  } ?></td>
											<?php  } ?>
											<td>
												<button type="button" class="btn btn-default coupon_check" data-src="<?php  echo $coupon['logo_url'];?>" data-cid="<?php  echo $coupon['id'];?>" data-title="<?php  echo $coupon['title'];?>" <?php  if($coupon['date_info']['time_type'] == 1) { ?>data-start="<?php  echo $coupon['date_info']['time_limit_start'];?>" data-end="<?php  echo $coupon['date_info']['time_limit_end'];?>"<?php  } ?> data-limit="<?php  echo $coupon['get_limit'];?>" data-type="<?php  echo $coupon['date_info']['time_type'];?>" data-date_limit="<?php  echo $coupon['date_info']['limit'];?>">选择</button>
											</td>
										</tr>
										<?php  } } ?>
										</tbody>
									</table>
									<?php  } ?>
								</div>
							</div><!-- /.modal-content -->
						</div><!-- /.modal-dialog -->
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">兑换状态</label>
					<div class="col-sm-9 col-xs-12">
						<?php  if(!$id) { ?>
						<label class="radio-inline"><input id="a" type="radio" name="status" value="1" <?php  if($data['status'] == 1 || $data['status'] == '') { ?>checked<?php  } ?>/>开启</label>
						<label class="radio-inline"><input id="b" type="radio" name="status" value="0" <?php  if($data['status'] == 0 && $data['status'] != '') { ?>checked<?php  } ?>/>关闭</label>
						<span class="help-block">此设置项设置是否开启兑换</span>
						<?php  } else { ?>
						<label class="radio-inline"><?php  if($data['status'] == 1) { ?>开启<?php  } else { ?>关闭<?php  } ?></label>
						<?php  } ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">积分类型</label>
					<div class="col-sm-9 col-xs-12">
						<?php  if(!$id) { ?>
						<select name="credittype" class="form-control">
							<option value="credit1">积分</option>
							<option value="credit2">余额</option>
						</select>
						<span class="help-block">此设置项设置当前卡券兑换需要消耗的积分类型,如:金币、积分、贡献等。</span>
						<?php  } else { ?>
						<label class="radio-inline"><?php  if($data['credittype'] == 'credit1') { ?>积分<?php  } else { ?>余额<?php  } ?></label>
						<?php  } ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">积分数量</label>
					<div class="col-sm-9 col-xs-12">
						<?php  if(!$id) { ?>
						<input type="text" name="credit" value="<?php  echo $data['credit'];?>" class="form-control"/>
						<span class="help-block">此设置项设置当前卡券兑换需要消耗的积分数量。注：所需积分（余额）必须为正整数；</span>
						<?php  } else { ?>
						<label class="radio-inline"><?php  echo $data['credit'];?></label>
						<?php  } ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">使用期限</label>
					<div class="col-sm-9 col-xs-12">
						<?php  if(!$id) { ?>
						<?php  echo tpl_form_field_daterange('date', array('start' => date('Y-m-d', $data['starttime']), 'end' => date('Y-m-d', $data['endtime'])))?>
						<?php  } else { ?>
						<label class="radio-inline"><?php  echo date('Y-m-d', $data['starttime']);?> - <?php  echo date('Y-m-d', $data['endtime']);?></label>
						<?php  } ?>
						<span style="display: none;" class="help-block"><span class="text-danger">注</span>:卡券有效期为<span id="start"></span> - <span id="end"></span></span>
					</div>
				</div>
				<input type="hidden" name="coupon_limit">
				<input type="hidden" name="coupon_start">
				<input type="hidden" name="coupon_end">
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger"> </span>每人可领限制</label>
					<div class="col-sm-9 col-xs-12">
						<?php  if(!$id) { ?>
						<input type="text" name="pretotal" value="<?php  echo $data['pretotal'];?>" class="form-control"/>
						<span class="help-block" style="display: none;"><span class="text-danger">注</span>：卡券每人领券限制为<span id="limit"></span></span>
						<span class="help-block">此设置项设置每个用户可领取此折扣券数量, 默认为1。</span>
						<?php  } else { ?>
						<label class="radio-inline"><?php  echo $data['pretotal'];?></label>
						<?php  } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-1 col-xs-2 col-lg-10">
			<input type="hidden" name="token" value="<?php  echo $_W['token'];?>"/>
			<input type="hidden" name="id" value="<?php  echo $_GPC['id'];?>"/>
			<?php  if(!$id) { ?>
			<input type="submit" id="exchange" name="submit" value="提交" class="btn btn-primary"/>
			<?php  } else { ?>
			<a href="<?php  echo url('activity/exchange/coupon')?>" class="btn btn-primary">确定</a>
			<?php  } ?>
		</div>
	</div>
</form>
<script>
	require(['bootstrap'], function($) {
		$('#coupon_image').hover(function() {
			var coupon_info = $(this);
			$.post('<?php  echo url('activity/exchange/coupon_info')?>', {'id' : $(this).data('id')}, function(data) {
				var data = $.parseJSON(data);
				if (data.message.errno != 0) {
					coupon_info.popover({
						'html' : true,
						'placement' : 'right',
						'trigger':'manual',
						'content':'卡券不存在'
					});
					coupon_info.popover('show');
				} else {
					var data = data.message.message;
					var types = new Array();
					types = ['','折扣券','代金券','团购券','礼品券','优惠券'];
					var display = data.is_display == 1 ? '上架' : '下架';
					var content = '标题：'+ data.title+'<br/>';
					content += '状态：'+display+'<br/>';
					content += '类型：'+types[data.type]+'<br/>';
					if (data.extra_instruction != '') {
						content += '说明：'+data.extra_instruction+'<br/>';
					}
					coupon_info.popover({
						'html' : true,
						'placement' : 'right',
						'trigger':'manual',
						'content':content
					});
					coupon_info.popover('show');
				}
			});
		}, function(){
			$(this).popover('hide');
		});
	});
	$('.coupon_check').click(function() {
		$('#coupon_image').attr('src', $(this).data('src'));
		$('#coupon_title').html($(this).data('title'));
		$('#add_coupon').hide();
		$('[name="coupon"]').val($(this).data('cid'));
		$('#coupon_image').data('id', $(this).data('cid'));
		var type = $(this).data('type');
		if (type  != 2) {
			$('[name="coupon_start"]').val($(this).data('start'));
			$('#start').html($(this).data('start'));
			$('[name="coupon_end"]').val($(this).data('end'));
			$('#end').html($(this).data('end'));
		} else {
			$('#end').html("领取后"+$(this).data('date_limit')+"天");
		}
		$('#start').parent().show();
		$('#end').parent().show();
		$('[name="coupon_limit"]').val($(this).data('limit'));
		$('#limit').html($(this).data('limit'));
		$('#limit').parent().show();
		$('#coupon_image').show();
		$('#myModal').modal('hide');
	});
	$('#exchange').click(function(){
				var coupon = $('[name="coupon"]').val();
				var pretotal = $('[name="pretotal"]').val();
				var total = $('[name="total"]').val();
				var limit = $('[name="coupon_limit"]').val();
				var credit = $('[name="credit"]').val();
				if (credit != parseInt(credit) && credit != '') {
					util.message('所需积分数量请填写正整数');
					return false;
				}
				limit = parseInt(limit);
				pretotal = parseInt(pretotal);
				if (pretotal > limit && limit != 0) {
					console.dir(pretotal);
					console.dir(limit);
					util.message('领取限制大于卡券领取限制，请重新填写');
					return false;
				}
				if (coupon == '') {
					util.message('请选择折扣券', '', 'info');
					return false;
				}
			}
	);
</script>
<?php  } ?>
<?php  } ?>
<?php  if($type == 'goods') { ?>
<ul class="nav nav-tabs">
	<li <?php  if($do == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo url('activity/exchange/display', array('type' => 'goods'));?>">管理实物兑换</a></li>
	<li <?php  if($do == 'post' && !$id) { ?>class="active"<?php  } ?>><a href="<?php  echo url('activity/exchange/post', array('type' => 'goods'));?>">添加实物兑换</a></li>
	<?php  if($do == 'post' && $id) { ?><li class="active"><a href="<?php  echo url('activity/exchange/post', array('id' => $id, 'type' => 'goods'));?>">编辑实物兑换</a></li><?php  } ?>
	<li <?php  if($do == 'deliver') { ?>class="active"<?php  } ?>><a href="<?php  echo url('activity/exchange/deliver', array('type' => 'goods'));?>">发货记录</a></li>
	<?php  if($do == 'receiver' && $id) { ?><li class="active"><a href="<?php  echo url('activity/exchange/receiver', array('id' => $id, 'type' => 'goods'));?>">编辑收货人信息</a></li><?php  } ?>
</ul>
<?php  if($do == 'post') { ?>
<style>
	.text-danger{color:red;}
</style>
<div class="main">
	<form action="" method="post" class="form-horizontal form" enctype="multipart/form-data" id="form1">
		<div class="panel panel-default">
			<div class="panel-heading">
				兑换真实物品
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">*</span> 兑换名称</label>
					<div class="col-sm-9 col-xs-12">
						<input type="text" name="title" class="form-control" value="<?php  echo $item['title'];?>" />
						<span class="help-block">此设置项为当前礼品兑换设置一个名称。</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">*</span> 兑换状态</label>
					<div class="col-sm-9 col-xs-12">
						<label class="radio-inline"><input name="status" type="radio" value="1" class="" <?php  if(!$id) { ?>checked<?php  } else { ?><?php  if($item['status']) { ?>checked<?php  } ?><?php  } ?>>开启</label>
						<label class="radio-inline"><input name="status" type="radio" value="0" class="" <?php  if($id && !$item['status']) { ?>checked<?php  } ?>>关闭</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">*</span> 兑换内容</label>
					<div class="col-sm-9 col-xs-12">
						<input type="text" name="extra[title]" class="form-control" value="<?php  echo $item['extra']['title'];?>" />
						<span class="help-block">此设置项设置当前礼品兑换的礼品名称。</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">*</span> 积分类型</label>
					<div class="col-sm-9 col-xs-12">
						<select name="credittype" class="form-control">
							<?php  if(is_array($creditnames)) { foreach($creditnames as $key => $credit) { ?>
							<option value="<?php  echo $key;?>" <?php  if($key == $item['credittype']) { ?>selected<?php  } ?>><?php  echo $credit;?></option>
							<?php  } } ?>
						</select>
						<span class="help-block">此设置项设置当前礼品兑换需要消耗的积分类型,如:金币、积分、贡献等。注：所需积分（余额）必须为正整数；</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">积分数量</label>
					<div class="col-sm-9 col-xs-12">
						<input type="text" name="credit" class="form-control" value="<?php  echo $item['credit'];?>" />
						<span class="help-block">此设置项设置当前礼品兑换需要消耗的积分数量。</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">使用期限</label>
					<div class="col-sm-9 col-xs-12">
						<?php  echo tpl_form_field_daterange('datelimit', array('start' => date('Y-m-d', $item['starttime']),'end' => date('Y-m-d', $item['endtime'])), '')?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">*</span> 每人最大兑换次数</label>
					<div class="col-sm-9 col-xs-12">
						<input type="text" name="pretotal" class="form-control" value="<?php  echo $item['pretotal'];?>" />
						<span class="help-block">此设置项设置每个用户最大兑换次数。</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">*</span> 兑换总数</label>
					<div class="col-sm-9 col-xs-12">
						<input type="text" name="total" class="form-control" value="<?php  echo $item['total'];?>" />
						<span class="help-block">此设置项设置兑换总量。</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">*</span> 封面</label>
					<div class="col-sm-9 col-xs-12">
						<?php  echo tpl_form_field_image('thumb', $item['thumb'])?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label"><span class="text-danger">*</span> 说明</label>
					<div class="col-sm-9 col-xs-12">
						<?php  echo tpl_ueditor('description', $item['description'])?>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group col-sm-12">
			<input name="id" type="hidden" value="<?php  echo $item['id'];?>">
			<input name="submit" type="submit" value="提交" class="btn btn-primary col-lg-1">
			<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
		</div>
	</form>
</div>
<?php  } else if($do == 'display') { ?>
<div class="panel panel-default">
	<div class="panel-heading">
		是否启用兑换中心：
		<input type="checkbox" name="flag" value="1" <?php  if(intval($uni_setting['exchange_enable'])==1) { ?> checked="checked" <?php  } ?>/>
	</div>
</div>
<div class="main">
	<div class="panel panel-info">
		<div class="panel-heading">筛选</div>
		<div class="panel-body">
			<form action="" method="get" class="form-horizontal" role="form">
				<input type="hidden" name="c" value="activity" />
				<input type="hidden" name="a" value="exchange" />
				<input type="hidden" name="do" value="display" />
				<input type="hidden" name="type" value="goods" />
				<div class="form-group">
					<label class="col-xs-12 col-sm-2 col-md-2 col-lg-1 control-label">兑换名称</label>
					<div class="col-sm-7 col-lg-9 col-xs-12">
						<input class="form-control" name="keyword" id="" type="text" value="<?php  echo $_GPC['keyword'];?>">
					</div>
					<div class="pull-right col-xs-12 col-sm-3 col-lg-2">
						<button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="table-responsive panel-body">
			<table class="table table-hover">
				<thead>
				<tr>
					<th style="width:50px">图标</th>
					<th style="width:100px;">标题</th>
					<th style="width:80px;">领取条件</th>
					<th style="width:90px;">可兑换次数</th>
					<th style="width:80px;">已兑换</th>
					<th style="width:80px;">总量</th>
					<th style="width:150px;">有效时间</th>
					<th style="width:150px;">兑换状态</th>
					<th style="text-align:right; width:120px;">操作</th>
				</tr>
				</thead>
				<tbody>
				<?php  if(is_array($list)) { foreach($list as $item) { ?>
				<tr>
					<td><img width="40" src="<?php  echo $item['thumb'];?>"></td>
					<td><?php  echo $item['title'];?></td>
					<td><?php  echo $item['credit'];?> <?php  echo $creditnames[$item['credittype']];?></td>
					<td><?php  echo $item['pretotal'];?> 次</td>
					<td><?php  echo $item['num'];?> 个</td>
					<td><?php  echo $item['total'];?> 个</td>
					<td><?php  echo date('Y-m-d', $item['starttime'])?> - <?php  echo date('Y-m-d', $item['endtime'])?></td>
					<td>
						<input class="js-flag" type="checkbox" data-id="<?php  echo $item['id'];?>" <?php  if($item['status']) { ?>checked<?php  } ?>/>
						<script>
							require(['bootstrap.switch'],function($){
								$('.js-flag:checkbox').bootstrapSwitch({onText: '启用', offText: '关闭'});
								$('.js-flag:checkbox').on('switchChange.bootstrapSwitch', function(event, state) {
									var id = $(this).data('id');
									var ban = state ? 1 : 0;
									$.getJSON("<?php  echo url('activity/exchange/change_status')?>", {id:id, status:ban}, function(data) {
										var data = eval(data.message);
									});
								});
							});
						</script>
					</td>
					<td style="text-align:right;">
						<a href="<?php  echo url('activity/exchange/post', array('id' => $item['id'], 'type' => 'goods'))?>" title="编辑">编辑</a>&nbsp;-&nbsp;
						<a href="<?php  echo url('activity/exchange/del', array('id' => $item['id'], 'type' => 'goods'))?>" onclick="return confirm('此操作不可恢复，确认删除？');return false;" title="删除">删除</a>
						<a href="<?php  echo url('activity/exchange/record', array('exid' => $item['id'], 'type' => 'goods'))?>" title="兑换记录">兑换记录</a>
					</td>
				</tr>
				<?php  } } ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php  echo $pager;?>
</div>
<script type="text/javascript">
	$(function(){
		require(['wapeditor', 'bootstrap.switch'], function() {
			angular.bootstrap(document, ['app']);
			$(":checkbox[name='flag']").bootstrapSwitch();
			$(':checkbox').on('switchChange.bootstrapSwitch', function(e, state){
				$this = $(this);
				var status = this.checked ? 1 : 0;
				$.post("<?php  echo url('activity/exchange/exchange_enable');?>", {'status' : status}, function(resp){
					resp = $.parseJSON(resp);
					util.message(resp.message.message, location.href, 'success');
					if (resp.message.errno < 0) {
						util.message(resp.message.message, location.href, 'error');
					} else {
						util.message(resp.message.message, location.href, 'success');
					}
				});
			});
		});
		$('.modules').click(function(){
			return false;
		});
	});
</script>
<?php  } else if($do == 'deliver') { ?>
<div class="main">
	<div class="panel panel-info">
		<div class="panel-heading">筛选</div>
		<div class="panel-body">
			<form action="./index.php" method="get" class="form-horizontal" role="form">
				<input type="hidden" name="c" value="activity">
				<input type="hidden" name="a" value="exchange">
				<input type="hidden" name="do" value="deliver">
				<input type="hidden" name="type" value="goods">
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">兑换标题</label>
					<div class="col-sm-6 col-lg-8 col-xs-12">
						<select class="form-control" name="exid">
							<?php  if(is_array($exchanges)) { foreach($exchanges as $exchange) { ?>
							<option value="<?php  echo $exchange['id'];?>" <?php  if($_GPC['exid'] == $exchange['id']) { ?>selected<?php  } ?>><?php  echo $exchange['title'];?></option>
							<?php  } } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">用户UID</label>
					<div class="col-sm-6 col-lg-8 col-xs-12">
						<input class="form-control" name="uid" id="" type="text" value="<?php  echo $_GPC['uid'];?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">兑奖日期</label>
					<div class="col-sm-6 col-lg-8 col-xs-12">
						<?php  echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d', $starttime),'endtime'=>date('Y-m-d', $endtime)));?>
					</div>
					<div class="pull-right col-xs-12 col-sm-3 col-lg-2">
						<button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="table-responsive panel-body">
			<table class="table table-hover">
				<thead class="navbar-inner">
				<tr>
					<th style="width:60px;">用户ID</th>
					<th style="width:80px;">标题</th>
					<th style="width:100px;">兑换物品</th>
					<th style="width:100px;">收件人</th>
					<th style="width:100px;">收件人电话</th>
					<th style="width:100px;">收件人邮编</th>
					<th style="width:150px;">收件地址</th>
					<th style="width:80px;">状态</th>
					<th style="text-align:center;width:80px;">操作</th>
				</tr>
				</thead>
				<tbody>
				<?php  if(is_array($list)) { foreach($list as $item) { ?>
				<tr>
					<td><?php  echo $item['uid'];?></td>
					<td><?php  echo $item['title'];?></td>
					<td><?php  echo $item['extra']['title'];?></td>
					<td><?php  echo $item['name'];?></td>
					<td><?php  echo $item['mobile'];?></td>
					<td><?php  echo $item['zipcode'];?></td>
					<td><?php  echo $item['province'];?> <?php  echo $item['city'];?> <?php  echo $item['district'];?> <?php  echo $item['address'];?></td>
					<td>
						<?php  if($item['status'] == 0) { ?>
						<span class="label label-danger">待发货</span>
						<?php  } else if($item['status'] == 1) { ?>
						<span class="label label-warning">已发货</span>
						<?php  } else if($item['status'] == 2) { ?>
						<span class="label label-success">已收货</span>
						<?php  } else if($item['status'] == -1) { ?>
						<span class="label label-default">已关闭</span>
						<?php  } ?>
					</td>
					<td style="text-align:center;">
						<a href="<?php  echo url('activity/exchange/receiver',array('id'=>$item['tid'], 'type' => 'goods'));?>" title="编辑">编辑</a>
						<!--  <a onclick="return confirm('确定要删除当前物品吗？');" href="<?php  echo url('activity/exchange/shipping',array('op'=>'delete','id'=>$item['id']));?>" title="删除">删除</a>-->
					</td>
				</tr>
				<?php  } } ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php  echo $pager;?>
</div>
<?php  } else if($do == 'receiver') { ?>
<div class="main">
	<form action="" method="post" class="form-horizontal form">
		<div class="panel panel-default">
			<div class="panel-heading">
				收货人信息
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">收货人姓名</label>
					<div class="col-sm-9">
						<input type="text" name="realname" class="form-control" value="<?php  echo $shipping['name'];?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">收货人电话</label>
					<div class="col-sm-9">
						<input type="text" name="mobile" class="form-control" value="<?php  echo $shipping['mobile'];?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 col-lg-2 control-label">邮寄地址</label>
					<div class="col-sm-9">
						<?php  echo tpl_fans_form('reside', array('province' => $shipping['province'], 'city' => $shipping['city'], 'district' => $shipping['district']));?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">收货人邮编</label>
					<div class="col-sm-9">
						<input type="text" name="zipcode" class="form-control" value="<?php  echo $shipping['zipcode'];?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">收件地址</label>
					<div class="col-sm-9">
						<input type="text" name="address" class="form-control" value="<?php  echo $shipping['address'];?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-12 col-sm-3 col-md-2 control-label">状态</label>
					<div class="col-sm-9">
						<label class="radio-inline"><input type="radio" value="0" <?php  if($shipping['status'] == 0) { ?>checked<?php  } ?> name="status">待发货</label>
						<label class="radio-inline"><input type="radio" value="1" <?php  if($shipping['status'] == 1) { ?>checked<?php  } ?> name="status">已发货</label>
						<label class="radio-inline"><input type="radio" value="2" <?php  if($shipping['status'] == 2) { ?>checked<?php  } ?> name="status">已收货</label>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-12">
				<input name="id" type="hidden" value="<?php  echo $id;?>">
				<input name="submit" type="submit" value="保存" class="btn btn-primary">
				<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
			</div>
		</div>
	</form>
</div>
<?php  } ?>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>