<?php
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
$curlpost = new Curl;//实例化
define('IN_IA',TRUE);
define('IN_MOBILE',TRUE);
global $_W;
global $_GPC;

	$filed = 'a.uniacid,a.ordersn,a.create_time,b.OthSeq,b.PlatDate,b.upOrderId';
	$datas = strtotime(date('Y-m-d'));
	$dates = date('Ymd');
	$find = array(':pay_status' => 1,':pay_type' =>'FAgro',':status'=>'已结算');//':pay_statusd' => 2,
	//OR a.pay_status = :pay_statusd
	$OrdData = pdo_fetchall("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.pay_status = :pay_status AND a.pay_type = :pay_type AND b.status = :status ORDER BY a.pay_time desc",$find);
//	echo '<pre>';
//	print_r($OrdData);
//	die;
?>

<!DOCTYPE html>
<html lang="zh">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="content-language" content="zh-CN" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<title>测试文件</title>
	<link rel="stylesheet" type="text/css" href="layui/css/layui.css"/>
	<script type="text/javascript" src="layui/layui.js"></script>
	<style type="text/css">
		.hides{
			display: none;
		}
	</style>
</head>
<body>
	<div style="text-align: center;width: 50%;margin: 6px auto;">
		<h2 class="layui-btn">测试文件</h2>
	</div>
	<form class="layui-form" name="form">
		
		<input type="hidden" name="uniacid" id="uniacid" value="<?php echo $OrdData[0]['uniacid'];?>" />
		<label class="layui-form-item" style="margin-bottom: 15px;">
			<label for="" class="layui-form-label">请选择类型</label>
			<div class="layui-input-inline" style="margin-bottom: 15px;">
				<select name="Token" lay-filter="aihao">
					<option value="" disabled="disabled">请选择</option>
					<!--<option value="FeeDeduction">扣费</option>-->
					<option value="Query">查询</option>
					<option value="WriteOff">单笔扣费冲销</option>
					<option value="Reconciliation">单笔扣费对账文件获取</option>
					<!--<option value="Surrender">用户解约</option>-->
				</select>
			</div>
		</label>
		
		<div id="FeeDeduction" class="hides">
			<div class="layui-form-item">
				<label for="" class="layui-form-label">手机号码</label>
				<div class="layui-input-inline">
					<input type="text" class="layui-input" name="Phone" maxlength="11" value="" placeholder="请输入注册手机号码" />
				</div>
			</div>
		
			<div class="layui-form-item">
				<label for="" class="layui-form-label">银行卡号</label>
				<div class="layui-input-inline">
					<input type="text" class="layui-input" name="CardNo" maxlength="16" placeholder="请输入银行卡号"/>
				</div>
			</div>
			
			<div class="layui-form-item">
				<div class="layui-form-label">订单编号</div>
				<div class="layui-input-inline">
					<input type="text" class="layui-input" name="OrderSn" placeholder="请输入订单编号"/>
				</div>
			</div>
		</div>
		
		
		<!-- 查询订单 -->
		<div id="Query">
			<div class="layui-form-item">
				<label for="" class="layui-form-label">发起方流水</label>
				<div class="layui-input-inline">
					<!--<input type="text" class="layui-input" name="OldSeq" value="" placeholder="请输入发起方流水" />-->
					<select name="OldSeq" lay-filter="aihao">
						<!--disabled="disabled"-->
						<option value="0" disabled="disabled">请选择</option>
						<?php foreach($OrdData as $key=>$val){?>
						<option value="<?php echo $val['OthSeq'];?>"><?php echo $val['OthSeq'];?></option>
						<?php }?>
					</select>
				</div>
			</div>
			
			<div class="layui-form-item">
				<label for="" class="layui-form-label">发起方日期</label>
				<div class="layui-input-inline">
					<!--<input type="text" class="layui-input" readonly name="OldDate" value="<?php echo $OrdData[0]['PlatDate'];?>" />-->
					<input type="text" class="layui-input" id="times" name="OldDate" placeholder="请输选择发起方日期"/>
				</div>
			</div>
		</div>
		
		<!-- 单笔冲销  -->
		<div id="WriteOff" class="hides">
			<div class="layui-form-item">
				<label for="" class="layui-form-label">原银行日期</label>
				<div class="layui-input-inline">
					<!--<input type="text" class="layui-input" id="times2" name="OldDate" placeholder="请输选择发起方日期"/>-->
					<input type="text" class="layui-input" name="PlatDate" value="<?php echo $OrdData[0]['PlatDate']?>"/>
				</div>
			</div>
			<div class="layui-form-item">
				<label for="" class="layui-form-label">原银行流水</label>
				<div class="layui-input-inline">
					<!--<input type="text" class="layui-input" name="upOrderId" placeholder="请输入原银行流水"/>-->
					<select name="upOrderId" lay-filter="aihao">
						<option value="" disabled="disabled">请选择</option>
						<?php foreach($OrdData as $key=>$val){?>
						<option value="<?php echo $val['upOrderId'];?>"><?php echo $val['upOrderId'];?></option>
						<?php }?>
					</select>
				</div>
			</div>
		</div>
		
		<!-- 对账单获取 -->
		<div id="Reconciliation" class="hides">
		
			<div class="layui-form-item">
				<label for="" class="layui-form-label">交易日期</label>
				<div class="layui-input-inline">
					<input type="text" class="layui-input" id="times1" name="OldDates" placeholder="请输选择发起方日期"/>
				</div>
			</div>
		</div>
	</form>
	
	<div class="layui-form-item">
		<div class="layui-input-block">
			<button class="layui-btn" onclick="goPost()">提 交</button>
		</div>
	</div>
	
	<div style="width: 100%;">
		<div style="width: 100%;text-align: center;"><h2 class="layui-btn">结果通知</h2></div>
		<table class="layui-table">
			<colgroup>
				<col width="150" />
				<col width="150" />
				<col width="150" />
				<col width="150" />
				<col width="150" />
			</colgroup>
			<thead>
				<tr>
					<th>交易码</th>
					<th>银行日期</th>
					<th>流水号</th>
					<th>状态</th>
					<th>消息</th>
				</tr>
			</thead>
			<tbody>
			<tr>
				<td id="SvcName"></td>
				<td id="PlatDate"></td>
				<td id="OthSeq"></td>
				<td id="Result" ></td>
				<td id="Message" ></td>
			</tr>
			</tbody>
		</table>
	</div>

</body>
</html>
<script type="text/javascript" src="layui/jquery.js"></script>
<script type="text/javascript">
	layui.use(['form','layer','jquery','laydate'],function(){
		var form = layui.form;
		layer = layui.layer;
		var laydate = layui.laydate;
		$ = layui.jquery;
		
		laydate.render({
			elem:'#times'
			,max:'<?php echo date("Y-m-d",time())?>'
		});
		laydate.render({
			elem:'#times1'
			
		});
		laydate.render({
			elem:'#times2'
			,max:'<?php echo date("Y-m-d",time())?>'
		});
		
		form.on('select(aihao)',function(res) {
			
			$('#'+res.value).show().siblings('div').hide();
			console.dir(res.value);

		});
	});
	
	
	//提交
	function goPost() {
		
		console.dir($('form').serialize());
		$.post('mytest.php',$('form').serialize(),function(res){
			console.dir(res);
//			if(res.Message == '成功完成') {
				var SvcName = res.SvcName;
				var PlatDate = res.PlatDate;
				var Result = res.Result;
				var Message = res.Message;
				var SvcNames = '';
				switch(SvcName) {
					case '100020':
						SvcNames = '查询订单: '+ SvcName
					break;
					case '100019':
						SvcNames = '冲销订单: '+ SvcName
					break;
					case '100018':
						SvcNames = '对账查询: '+ SvcName
					break;
				}
				
				document.getElementById('SvcName').innerHTML = SvcNames;
				document.getElementById('PlatDate').innerHTML = PlatDate;
				document.getElementById('Result').innerHTML = Result;
				document.getElementById('Message').innerHTML = Message;
				document.getElementById('OthSeq').innerHTML = res.OthSeq;
				
//			} else {
//				layui.alert(res.Message,{icon:2});
//			}
			
		},'json');
	}
	
</script>

