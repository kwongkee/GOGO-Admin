<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<div class='fui-page  fui-page-current'>
    <div class="fui-header">
		<div class="fui-header-left">
			<a class="back" onclick='location.back()'></a>
		</div>
		<div class="title">会员中心</div>
		<div class="fui-header-right"></div>
	</div>

	<div class='fui-content member-page navbar'>
		<div class="headinfo" >
			<a class="setbtn" href="<?php  echo mobileUrl('member/info')?>" data-nocache='true'><i class="icon icon-settings"></i></a>
			<div class="child">
				<div class="title"><?php  echo $_W['shopset']['trade']['moneytext'];?></div>
				<div class="num"><?php  echo number_format($member['credit2'],2)?></div>
				<?php  if(empty($_W['shopset']['trade']['closerecharge'])) { ?><a href="<?php  echo mobileUrl('member/recharge')?>"><div class="btn">充值</div></a><?php  } ?>
			</div>
			<div class="child userinfo">
				<a href="<?php  echo mobileUrl('member/info/face')?>" data-nocache="true" style="color: white;">
					<div class="face"><img src="<?php  echo $member['avatar'];?>" /></div>
					<div class="name"><?php  echo $member['nickname'];?></div>
				</a>
				<div class="level" <?php  if(!empty($_W['shopset']['shop']['levelurl'])) { ?>onclick='location.href="<?php  echo $_W['shopset']['shop']['levelurl'];?>"'<?php  } ?>>
				    <?php  if(empty($level['id'])) { ?>
				    [<?php  if(empty($_W['shopset']['shop']['levelname'])) { ?>普通会员<?php  } else { ?><?php  echo $_W['shopset']['shop']['levelname'];?><?php  } ?>]
				    <?php  } else { ?>
				    [<?php  echo $level['levelname'];?>]
				    <?php  } ?>
				    <?php  if(!empty($_W['shopset']['shop']['levelurl'])) { ?><i class='icon icon-question1' style='font-size:0.65rem'></i><?php  } ?>
				</div>
			</div>
			<div class="child">
				<div class="title"><?php  echo $_W['shopset']['trade']['credittext'];?></div>
				<div class="num"><?php  echo number_format($member['credit1'],0)?></div>
				<?php  if($open_creditshop) { ?><a href="<?php  echo mobileUrl('creditshop')?>" class="external"><div class="btn">兑换</div></a><?php  } ?>
			</div>
		</div>

		<?php  if($needbind) { ?>
			<div class="fui-cell-group fui-cell-click external">
				<a class="fui-cell"  href="<?php  echo mobileUrl('member/bind')?>">
					<div class="fui-cell-icon"><i class="icon icon-mobile"></i></div>
					<div class="fui-cell-text"><p class="text text-danger">绑定手机号</p></div>
					<div class="fui-cell-remark"></div>
				</a>
				<div class="fui-cell-tip">如果您用手机号注册过会员或您想通过微信外购物请绑定您的手机号码</div>
			</div>
		<?php  } ?>

		<?php  if(!empty($roleuser)) { ?>
			<div class="fui-cell-group fui-cell-click external">
				<a class="fui-cell"  href="<?php  echo mobileUrl('mmanage')?>" data-nocache="true">
					<div class="fui-cell-icon"><i class="icon icon-mobilephone"></i></div>
					<div class="fui-cell-text"><?php  echo m('plugin')->getName('mmanage')?></div>
					<div class="fui-cell-remark"></div>
				</a>
				<div class="fui-cell-tip">当前用户已绑定操作员，您可以通过手机管理商城</div>
			</div>
		<?php  } ?>


	<div class="fui-cell-group fui-cell-click">
			<a class="fui-cell external" href="<?php  echo mobileUrl('order')?>">
				<div class="fui-cell-icon"><i class="icon icon-list"></i></div>
				<div class="fui-cell-text">我的订单</div>
				<div class="fui-cell-remark" style="font-size: 0.5rem;">查看全部订单</div>
			</a>
			<div class="fui-icon-group selecter">
			    <a class="fui-icon-col external" href="<?php  echo mobileUrl('order',array('status'=>0))?>">
					<?php  if($statics['order_0']>0) { ?><div class="badge"><?php  echo $statics['order_0'];?></div><?php  } ?>
					<div class="icon icon-green radius"><i class="icon icon-card"></i></div>
					<div class="text">待付款</div>
				</a>
				<a class="fui-icon-col external" href="<?php  echo mobileUrl('order',array('status'=>1))?>">
					<?php  if($statics['order_1']>0) { ?><div class="badge"><?php  echo $statics['order_1'];?></div><?php  } ?>
					<div class="icon icon-orange radius"><i class="icon icon-box"></i></div>
					<div class="text">待发货</div>
				</a>
				<a class="fui-icon-col external" href="<?php  echo mobileUrl('order',array('status'=>2))?>">
					<?php  if($statics['order_2']>0) { ?><div class="badge"><?php  echo $statics['order_2'];?></div><?php  } ?>
					<div class="icon icon-blue radius"><i class="icon icon-deliver"></i></div>
					<div class="text">待收货</div>
				</a>
				<a class="fui-icon-col external" href="<?php  echo mobileUrl('order',array('status'=>4))?>">
					<?php  if($statics['order_4']>0) { ?><div class="badge"><?php  echo $statics['order_4'];?></div><?php  } ?>
					<div class="icon icon-pink radius"><i class="icon icon-electrical"></i></div>
					<div class="text">退换货</div>
				</a>
			</div>
		</div>

	<?php  if($newstore_plugin) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell external" href="<?php  echo mobileUrl('newstore/norder')?>">
			<div class="fui-cell-icon"><i class="icon icon-list"></i></div>
			<div class="fui-cell-text">我的预约</div>
			<div class="fui-cell-remark" style="font-size: 0.5rem;">查看全部预约</div>
		</a>
		<div class="fui-icon-group selecter">
			<a class="fui-icon-col external" href="<?php  echo mobileUrl('newstore/norder',array('status'=>0))?>">
				<?php  if($statics['norder_0']>0) { ?><div class="badge"><?php  echo $statics['norder_0'];?></div><?php  } ?>
				<div class="icon icon-green radius"><i class="icon icon-pay"></i></div>
				<div class="text">待付款</div>
			</a>
			<a class="fui-icon-col external" href="<?php  echo mobileUrl('newstore/norder',array('status'=>1))?>">
				<?php  if($statics['norder_1']>0) { ?><div class="badge"><?php  echo $statics['norder_1'];?></div><?php  } ?>
				<div class="icon icon-orange radius"><i class="icon icon-like"></i></div>
				<div class="text">待使用</div>
			</a>
			<a class="fui-icon-col external" href="<?php  echo mobileUrl('newstore/norder',array('status'=>2))?>">
				<?php  if($statics['norder_3']>0) { ?><div class="badge"><?php  echo $statics['norder_3'];?></div><?php  } ?>
				<div class="icon icon-blue radius"><i class="icon icon-discover"></i></div>
				<div class="text">已完成</div>
			</a>
			<a class="fui-icon-col external" href="<?php  echo mobileUrl('newstore/norder',array('status'=>4))?>">
				<?php  if($statics['norder_4']>0) { ?><div class="badge"><?php  echo $statics['norder_4'];?></div><?php  } ?>
				<div class="icon icon-pink radius"><i class="icon icon-remind"></i></div>
				<div class="text">取消预约</div>
			</a>
		</div>
	</div>
	<?php  } ?>

	<?php  if(p('task')) { ?>
	<?php  $open = pdo_get('ewei_shop_task_default',array('uniacid'=>$_W['uniacid']),array('open'));?>
	<?php  if($open['open']) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell"  href="<?php  echo mobileUrl('task')?>">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p>任务中心</p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<?php  } ?>
	<?php  } ?>
	<?php  if($hasThreen) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell"  href="<?php  echo mobileUrl('threen')?>">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p><?php  echo $plugin_threen_set['texts']['threen'];?></p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<?php  } ?>
	<?php  if($haslive) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell" href="<?php  echo mobileUrl('live')?>">
			<div class="fui-cell-icon"><i class="icon icon-video"></i></div>
			<div class="fui-cell-text"><p><?php  echo $live_set['pluginname'];?></p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<?php  } ?>
	<?php  if($hassign) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell" href="<?php  echo mobileUrl('sign')?>">
			<div class="fui-cell-icon"><i class="icon icon-goods1"></i></div>
			<div class="fui-cell-text"><p><?php  echo $hassign;?></p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<?php  } ?>

	<?php  if($hasglobonus) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell"  href="<?php  echo mobileUrl('globonus')?>">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p><?php  echo $plugin_globonus_set['texts']['center'];?></p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<?php  } ?>

	<?php  if($hasabonus) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell"  href="<?php  echo mobileUrl('abonus')?>">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p><?php  echo $plugin_abonus_set['texts']['center'];?></p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<?php  } ?>


	<?php  if($hasauthor) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell"  href="<?php  echo mobileUrl('author')?>">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p><?php  echo $plugin_author_set['texts']['center'];?></p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<?php  } ?>

	<?php  if(!empty($showcard)) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell" href="javascript:;" onclick="addCard('<?php  echo $card['card_id'];?>')">
			<div class="fui-cell-icon"><i class="icon icon-same"></i></div>
			<div class="fui-cell-text"><p><?php  echo $cardtag;?></p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<?php  } ?>
	<div class="fui-cell-group fui-cell-click" id="putong">
		<a class="fui-cell" href="javascript:;">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p>普通会员</p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<div style="display: none;" id="pu">
		<div class="fui-cell-group fui-cell-click">
			<a class="fui-cell" href="<?php  echo mobileUrl('member/cart');?>">
				<div class="fui-cell-icon"><i class="icon icon-cart"></i></div>
				<div class="fui-cell-text"><p>我的购物车</p></div>
				<div class="fui-cell-remark"><?php  if($statics['cart']>0) { ?><span class='badge'><?php  echo $statics['cart'];?></span><?php  } ?></div>
			</a>
			<?php  if($hascouponcenter) { ?>
			<a class="fui-cell" href="<?php  echo mobileUrl('sale/coupon')?>">
				<div class="fui-cell-icon"><i class="icon icon-same"></i></div>
				<div class="fui-cell-text"><p>领取优惠券</p></div>
				<div class="fui-cell-remark"></div>
			</a>
			<?php  } ?>
			<a class="fui-cell"  href="<?php  echo mobileUrl('sale/coupon/my')?>">
				<div class="fui-cell-icon"><i class="icon icon-card"></i></div>
				<div class="fui-cell-text"><p>我的优惠券</p></div>
				<div class="fui-cell-remark"><?php  if($statics['coupon']>0) { ?><span  <?php  if($statics['newcoupon']>0) { ?>style="background: #fe5455;color:#fff"<?php  } ?> class='badge'>  <?php  if($statics['newcoupon']>0) { ?>new<?php  } else { ?><?php  echo $statics['coupon'];?><?php  } ?></span><?php  } ?></div>
			</a>
			<a class="fui-cell" href="<?php  echo mobileUrl('member/favorite');?>">
				<div class="fui-cell-icon"><i class="icon icon-like"></i></div>
				<div class="fui-cell-text"><p>我的关注</p></div>
				<div class="fui-cell-remark"><?php  if($statics['favorite']>0) { ?><span class='badge'><?php  echo $statics['favorite'];?></span><?php  } ?></div>
			</a>
			<a class="fui-cell" href="<?php  echo mobileUrl('member/history');?>">
				<div class="fui-cell-icon"><i class="icon icon-footprint"></i></div>
				<div class="fui-cell-text"><p>我的足迹</p></div>
				<div class="fui-cell-remark"></div>
			</a>
			<?php  if($hasLineUp) { ?>
				<a class="fui-cell"  href="<?php  echo mobileUrl('lineup')?>">
					<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
					<div class="fui-cell-text"><p>排队返现列表</p></div>
					<div class="fui-cell-remark"></div>
				</a>
			<?php  } ?>
				<a class="fui-cell" href="<?php  echo mobileUrl('verifygoods')?>">
					<div class="fui-cell-icon"><i class="icon icon-same"></i></div>
					<div class="fui-cell-text"><p>核销商品信息</p></div>
					<div class="fui-cell-remark"></div>
				</a>
			<?php  if(!empty( $_W['shopset']['rank']['status'] ) || !empty( $_W['shopset']['rank']['order_status'] ) ) { ?>

			<?php  if(!empty( $_W['shopset']['rank']['status'] ) ) { ?>
			<a class="fui-cell" href="<?php  echo mobileUrl('member/rank');?>">
				<div class="fui-cell-icon"><i class="icon icon-rank"></i></div>
				<div class="fui-cell-text"><p><?php  echo $_W['shopset']['trade']['credittext'];?>排行</p></div>
				<div class="fui-cell-remark"></div>
			</a>
			<?php  } ?>
			<?php  if(!empty( $_W['shopset']['rank']['order_status'] ) ) { ?>
			<a class="fui-cell" href="<?php  echo mobileUrl('member/rank/order_rank');?>">
				<div class="fui-cell-icon"><i class="icon icon-money"></i></div>
				<div class="fui-cell-text"><p>消费排行</p></div>
				<div class="fui-cell-remark"></div>
			</a>
			<?php  } ?>

			<?php  } ?>
			<a class="fui-cell" href="<?php  echo mobileUrl('member/address')?>">
				<div class="fui-cell-icon"><i class="icon icon-address"></i></div>
				<div class="fui-cell-text"><p>收货地址管理</p></div>
				<div class="fui-cell-remark"></div>
			</a>
			<?php  if($hasqa) { ?>
				<a class="fui-cell" href="<?php  echo mobileUrl('qa')?>">
					<div class="fui-cell-icon"><i class="icon icon-help"></i></div>
					<div class="fui-cell-text"><p>帮助中心</p></div>
					<div class="fui-cell-remark"></div>
				</a>
			<?php  } ?>
			<a class="fui-cell" href="<?php  echo mobileUrl('member/log')?>">
				<div class="fui-cell-icon"><i class="icon icon-list"></i></div>
				<div class="fui-cell-text"><p>
					<?php  if($_W['shopset']['trade']['withdraw']==1) { ?><?php  echo $_W['shopset']['trade']['moneytext'];?>明细<?php  } else { ?>充值记录<?php  } ?>
				</p></div>
				<div class="fui-cell-remark"></div>
			</a>
			
			<div class="fui-cell" id="orderall">
				<div class="fui-cell-icon"><i class="icon icon-list"></i></div>
				<div class="fui-cell-text"><p>订单中心</p></div>
				<div class="fui-cell-remark"></div>
			</div>
			
			<div id="order" style="display: none;">
				<!-- 已付订单  -->
				<div class="fui-cell" id="yifuOrder">
					<div class="fui-cell-icon"><i class="icon"></i></div>
					<div class="fui-cell-text"><p>已付订单</p></div>
					<div class="fui-cell-remark"></div>
				</div>
				
				<div id="yifuOrder1" style="display: none;">
					<a class="fui-cell" href="<?php  echo mobileUrl('member/address')?>">
						<div class="fui-cell-icon"><i class="icon icon"></i></div>
						<div class="fui-cell-text"><p>停车订单</p></div>
						<!--<div class="fui-cell-remark"></div>-->
					</a>

					<a class="fui-cell" href="<?php  echo mobileUrl('member/address')?>">
						<div class="fui-cell-icon"><i class="icon icon"></i></div>
						<div class="fui-cell-text"><p>电商订单</p></div>
						<!--<div class="fui-cell-remark"></div>-->
					</a>
				</div>
				
				<!-- 未付订单  -->
				<div class="fui-cell" id="weifuOrder">
					<div class="fui-cell-icon"><i class="icon"></i></div>
					<div class="fui-cell-text"><p>未付订单</p></div>
					<div class="fui-cell-remark"></div>
				</div>
				
				<div id="weifuOrder1" style="display: none;">
					<a class="fui-cell" href="<?php  echo mobileUrl('member/address')?>">
						<div class="fui-cell-icon"><i class="icon"></i></div>
						<div class="fui-cell-text"><p>停车订单</p></div>
						<!--<div class="fui-cell-remark"></div>-->
					</a>
					
					<a class="fui-cell" href="<?php  echo mobileUrl('member/address')?>">
						<div class="fui-cell-icon"><i class="icon icon"></i></div>
						<div class="fui-cell-text"><p>电商订单</p></div>
						<!--<div class="fui-cell-remark"></div>-->
					</a>
				</div>
			</div>
			<script type="text/javascript">
				//点击订单中心，显示里面所有的数据；
				$('#orderall').click(function(){
					var order = $('#order').css('display');
					if(order == 'block'){
						$('#order').hide()
					}else{
						$('#order').show();
					}
				});
				
				//点击已支付订单，显示里面所有的数据；
				$('#yifuOrder').click(function(){
					$(this).css('background-color','skyblue');
					var order = $('#yifuOrder1').css('display');
					if(order == 'block'){
						$('#yifuOrder1').hide()
						$(this).css('background-color','white');
					}else{
						$('#yifuOrder1').show();						
						$(this).css('background-color','skyblue');
					}
				});
				
				//点击未支付订单，显示里面所有的数据；
				$('#weifuOrder').click(function(){
					$(this).css('background-color','skyblue');
					var order = $('#weifuOrder1').css('display');
					if(order == 'block'){
						$('#weifuOrder1').hide()
						$(this).css('background-color','white');
					}else{
						$('#weifuOrder1').show();
						$(this).css('background-color','skyblue');
					}
				});
				
			</script>
			
			<!-- 授权中心  -->
			
			<div class="fui-cell" id="shouquan">
				<div class="fui-cell-icon"><i class="icon icon-list"></i></div>
				<div class="fui-cell-text"><p>授权中心</p></div>
				<div class="fui-cell-remark"></div>
			</div>
			<div id="shouquan1" style="display: none;">
				<a class="fui-cell" href="<?php  echo mobileUrl('parking/auth')?>">
					<div class="fui-cell-icon"><i class="icon icon"></i></div>
					<div class="fui-cell-text"><p>我要授权</p></div>
					<!--<div class="fui-cell-remark"></div>-->
				</a>

				<a class="fui-cell" href="<?php  echo mobileUrl('parking/auth/Surrender')?>">
					<div class="fui-cell-icon"><i class="icon icon"></i></div>
					<div class="fui-cell-text"><p>我要解约</p></div>
					<!--<div class="fui-cell-remark"></div>-->
				</a>
			</div>
			<script type="text/javascript">
				//点击已支付订单，显示里面所有的数据；
				$('#shouquan').click(function(){
					$(this).css('background-color','skyblue');
					
					var order = $('#shouquan1').css('display');
					if(order == 'block'){
						$('#shouquan1').hide()
						$(this).css('background-color','white');
					}else{
						$('#shouquan1').show();						
						$(this).css('background-color','skyblue');
					}
				});
			</script>
			
			
			<!--<a class="fui-cell" href="<?php  echo mobileUrl('member/notice');?>" data-nocache="true">
				<div class="fui-cell-icon"><i class="icon icon-notice"></i></div>
				<div class="fui-cell-text"><p>消息提醒设置</p></div>
				<div class="fui-cell-remark"></div>
			</a>-->
		</div>
	</div>
	<div class="fui-cell-group fui-cell-click" id="renzhen">
		<a class="fui-cell" href="javascript:;">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p>认证会员</p></div>
			<div class="fui-cell-remark"></div>
		</a>
	</div>
	<div class="fui-cell-group fui-cell-click" style="display:none;" id="ren">
		<a class="fui-cell"  href="<?php  echo mobileUrl('parking/verified');?>">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p>实名认证</p></div>
			<div class="fui-cell-remark"></div>
		</a>
		<a class="fui-cell external" href="<?php  echo mobileUrl('parking/vehiclecard')?>" data-nocache="true">
			<div class="fui-cell-icon"><i class="icon icon-countdown"></i></div>
			<div class="fui-cell-text"><p>车牌关联</p></div>
			<div class="fui-cell-remark"></div>
		</a>
		<a class="fui-cell external" href="javascript:;" data-nocache="true">
			<div class="fui-cell-icon"><i class="icon icon-countdown"></i></div>
			<div class="fui-cell-text"><p>月卡管理</p></div>
			<div class="fui-cell-remark"></div>
		</a>
		
		<!-- 发票管理   "<?php  echo mobileUrl('parking/invoicelist');?>"   2018-01-18-->
		<div class="fui-cell" id="invoice">
			<div class="fui-cell-icon"><i class="icon icon-profile"></i></div>
			<div class="fui-cell-text"><p>发票管理</p></div>
			<div class="fui-cell-remark"></div>
		</div>
		
		<div id="invoice1" style="display: none;">
			
			<a class="fui-cell" href="<?php  echo mobileUrl('parking/invoicelist/headers')?>">
				<div class="fui-cell-icon"><i class="icon icon"></i></div>
				<div class="fui-cell-text"><p>抬头管理</p></div>
				<!--<div class="fui-cell-remark"></div>-->
			</a>

			
			<div class="fui-cell" id="park_invoice">
				<div class="fui-cell-icon"><i class="icon icon"></i></div>
				<div class="fui-cell-text"><p>发票开具</p></div>
				<div class="fui-cell-remark"></div>
			</div>
			<div id="park_invoice1" style="display: none;">
				<a class="fui-cell" href="<?php  echo mobileUrl('parking/invoicelist/park')?>">
					<div class="fui-cell-icon"><i class="icon icon"></i></div>
					<div class="fui-cell-text"><p>开具</p></div>
				</a>
				<a class="fui-cell" href="<?php  echo mobileUrl('parking/invoicelist/history')?>">
					<div class="fui-cell-icon"><i class="icon icon"></i></div>
					<div class="fui-cell-text"><p>查询</p></div>
				</a>
			</div>
			
			
		</div>
		<script type="text/javascript">
			$('#invoice').click(function(){
				$(this).css('background-color','skyblue');
				
				var order = $('#invoice1').css('display');
				if(order == 'block'){
					$('#invoice1').hide()
					$(this).css('background-color','white');
				}else{
					$('#invoice1').show();						
					$(this).css('background-color','skyblue');
				}
			});
			
			$('#park_invoice').click(function(){
				$(this).css('background-color','skyblue');
				
				var order = $('#park_invoice1').css('display');
				if(order == 'block'){
					$('#park_invoice1').hide()
					$(this).css('background-color','white');
				}else{
					$('#park_invoice1').show();						
					$(this).css('background-color','skyblue');
				}
			});
			
		</script>
		
		
		
		<a class="fui-cell external" href="<?php  echo mobileUrl('member/fullback');?>" data-nocache="true">
			<div class="fui-cell-icon"><i class="icon icon-countdown"></i></div>
			<div class="fui-cell-text"><p>返利钱包</p></div>
			<div class="fui-cell-remark"></div>
		</a>
		<a class="fui-cell external" href="javascript:;" data-nocache="true">
			<div class="fui-cell-icon"><i class="icon icon-countdown"></i></div>
			<div class="fui-cell-text"><p>车位共享</p></div>
			<div class="fui-cell-remark"></div>
		</a>
		<a class="fui-cell external" href="javascript:;" data-nocache="true">
			<div class="fui-cell-icon"><i class="icon icon-countdown"></i></div>
			<div class="fui-cell-text"><p>商务合作</p></div>
			<div class="fui-cell-remark"></div>
		</a>
		<?php  if($_W['shopset']['trade']['withdraw']==1) { ?>
		<a class="fui-cell" href="<?php  echo mobileUrl('member/withdraw')?>">
			<div class="fui-cell-icon"><i class="icon icon-money"></i></div>
			<div class="fui-cell-text"><p><?php  echo $_W['shopset']['trade']['moneytext'];?>提现</p></div>
			<div class="fui-cell-remark"></div>
		</a>
		<?php  } ?>
	</div>
	<?php  if(!is_weixin()) { ?>
	<div class="fui-cell-group fui-cell-click">
		<a class="fui-cell external" href="<?php  if(!empty($member['mobileverify'])) { ?><?php  echo mobileUrl('member/changepwd')?><?php  } else { ?><?php  echo mobileUrl('member/bind')?><?php  } ?>">
			<div class="fui-cell-text" style="text-align: center;color:red;"><p>修改密码</p></div>
		</a>
		<a class="fui-cell external btn-logout">
			<div class="fui-cell-text" style="text-align: center;color:red;"><p>退出登录</p></div>
		</a>
	</div>

	<div class="pop-apply-hidden" style="display: none">
		<div class="verify-pop pop">
			<div class="close"><i class="icon icon-roundclose"></i></div>
			<div class="qrcode">
				<div class="inner">
					<div class="title"><?php  echo $set['applytitle'];?></div>
					<div class="text"><?php  echo $set['applycontent'];?></div>
				</div>
				<div class="inner-btn" style="padding: 0.5rem">
					<div class="btn btn-warning" style="width: 100%; margin: 0">我已阅读</div>
				</div>
			</div>
		</div>
	</div>

	<?php  } ?>
		<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_copyright', TEMPLATE_INCLUDEPATH)) : (include template('_copyright', TEMPLATE_INCLUDEPATH));?>
	</div>
	<script language='javascript'>
		require(['biz/member/index'], function (modal) {
			modal.init();
		});
	</script>
</div>

<script  language='javascript'>
	function addCard(card_id) {

		var data = {'openid': '<?php  echo $_W["openid"]?>', 'card_id': card_id};
		$.ajax({
			url: "<?php  echo mobileUrl('sale/coupon/getsignature')?>",
			data: data,
			cache: false
		}).done(function (result) {
			var data = jQuery.parseJSON(result);
			if (data.status == 1) {
				wx.addCard({
					cardList: [
						{
							cardId: card_id,
							cardExt: data.result.cardExt
						}
					],
					success: function (res) {

						//alert(JSON.stringify(res))
						//alert('已添加卡券：' + JSON.stringify(res.cardList));
					},
					cancel: function (res) {
						//alert(JSON.stringify(res))
					}
				});
			} else {
				alert("微信接口繁忙,请稍后再试!");
				alert(data.result.message);
			}
		});
	}
$('#putong').bind("click",function () {
    $("#pu").css("display",'block');
    });
$("#renzhen").bind("click",function () {
	$("#ren").css("display",'block');
})
</script>

<?php  $this->footerMenus()?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>
