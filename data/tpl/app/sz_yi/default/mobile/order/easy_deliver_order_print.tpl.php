<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH));?>
<body>
<link rel="stylesheet" href="../addons/sz_yi/static/css/waybill.css">
<header>
    <div class="logo"><img src="../addons/sz_yi/static/images/logo.png" alt=""></div>
    <div class="plane"><img src="../addons/sz_yi/static/images/plane.png" alt=""></div>
</header>
<div class="warp">
    <div class="content">
        <div class="waybill">
            <ul>
                <li>订购编号：</li>
                <li style="width: 75%;">
                <?php  if(is_array($allOrder)) { foreach($allOrder as $item) { ?>
                    <div class="row">
                        <input type="checkbox" name="order" value="<?php  echo $item['ordersn'];?>">
                        <span style="padding-left: 15px;"><?php  echo $item['ordersn'];?></span>
                        <!-- <span style="padding-left: 15px;"><?php  echo $item['pack_ordersn'];?></span> -->
                        <span style="float: right;"><?php  echo $item['clear_type'];?>通道</span>
                    </div>
                <?php  } } ?>
                </li>
            </ul>
        </div>
        <?php  if(empty($allOrder)) { ?>
        <div class="waybill-btn"><button disabled id="alertBtn" type="button" class="zbox-btn zbox-btn-blue zbox-btn-outlined "><img src="../addons/sz_yi/static/images/icon_06.png" alt="">打印</button></div>
        <?php  } else { ?>
        <div class="waybill-btn">
<!--            <button   onclick="btnPrint();" id="alertBtn" type="button" style="width: 35%;" class="zbox-btn zbox-btn-blue zbox-btn-outlined "><img src="../addons/sz_yi/static/images/icon_06.png" alt="">打印面单</button>-->
            <button   onclick="btnPrint();" id="alertBtn" type="button" style="margin-left: 31px;width:35%" class="zbox-btn zbox-btn-blue zbox-btn-outlined "><img src="../addons/sz_yi/static/images/icon_06.png" alt="">打印面单</button>
            <button onclick="printBill();"  id="alertBtn2" type="button" style="position: inherit;margin-right: 0px;margin-top: -48px;width:35%;" class="zbox-btn zbox-btn-blue zbox-btn-outlined"><img src="../addons/sz_yi/static/images/icon_06.png" alt="">生成小票</button>
        </div>
        <?php  } ?>
    </div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH));?>
</body>
<script type="text/javascript" src="../addons/sz_yi/static/js/framework7.bundle.min.js"></script>
<script>
    var App = new Framework7();
    var $$ = Dom7;
    const btnPrint=()=>{
        var orderlist = {};
        $$('input[type="checkbox"]:checked').each(function () {
            var v=$$(this).val();
            orderlist[v]=v;
        });
        App.request.post("<?php  echo $this->createMobileUrl('order/easy_deliver_order_print')?>",{orderlist:orderlist},(data)=>{
            data = JSON.parse(data);
            $.DialogByZ.Alert({Title: "", Content: data.result});
            // $$('#alertBtn').attr('disabled','disabled');
        });
    }
    const printBill=()=>{
        var orderlist = new Array();
        $$('input[type="checkbox"]:checked').each(function () {
            var v=$$(this).val();
            orderlist.push(v);
        });
        if (orderlist.length>1){
           return $.DialogByZ.Alert({Title: "", Content: "不能多选订单"});
        }
        window.location.href="<?php  echo $this->createPluginMobileUrl('directmailorder/generatesmallticket')?>&ordersn="+orderlist.join(',');
    }
</script>
</html>
