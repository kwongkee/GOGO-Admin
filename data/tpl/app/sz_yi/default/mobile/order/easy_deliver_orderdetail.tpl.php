<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH));?>
<body>
<header>
    <div class="logo"><img src="../addons/sz_yi/static/images/logo.png" alt=""></div>
    <div class="plane"><img src="../addons/sz_yi/static/images/plane.png" alt=""></div>
</header>
<div class="warp">
<!--    <div class="title">-->
<!--        <a href="javascript:window.history.back(-1)"><img src="../addons/sz_yi/static/images/row.png" alt=""></a>订购详情-->
<!--    </div>-->
    <div class="content">
        <div class="warp-con">
            <p class="details-num">订购编号：<?php  echo $order['ordersn'];?></p>
            <div class="details-title">
                <span class="details-line">|</span>已购商品
            </div>
            <?php  if(is_array($goods)) { foreach($goods as $item) { ?>
            <div class="details-num">
                <p>商品名称：<?php  echo $item['title'];?></p>
                <p>规格数量：<?php  echo $item['value'];?>-购买数量：<?php  echo $item['total'];?></p>
                <p>商品总价：¥ <?php  echo sprintf("%.2f",$item['total']*$item['price'])?></p>
            </div>
            <?php  } } ?>
        </div>
    </div>
    <div class="content">
        <div class="warp-con">
            <div class="details-title">
                <span class="details-line">|</span>订购详情
            </div>
            <div class="details-num">
                <p>订购状态：已订购</p>
                <p>订购金额：¥ <?php  echo $order['price'];?> </p>
                <p><span style="padding-left: 28px;">其中：</span>在线支付 ¥ <?php  echo $order['price'];?> + 线下支付 ¥ <?php  echo $order['offline_pay_price'];?> </p>
                <p>订购时间：<?php  echo date('Y-m-d H:i:s',$order['createtime'])?></p>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="warp-con">
            <div class="details-title">
                <span class="details-line">|</span>状态跟踪
            </div>
            <div class="details-num">
                <p><?php  echo date('Y-m-d H:i:s',$order['logistics_time'])?>：<?php  echo $order['logistics_status'];?></p>
            </div>
        </div>
    </div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH));?>
</body>
</html>