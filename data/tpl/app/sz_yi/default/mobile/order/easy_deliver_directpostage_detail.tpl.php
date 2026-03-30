<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH));?>
<body>
<style>
    table{
        border-color: #1b7ab6;
    }
    .layui-table th{
        text-align: center;
        background-color: #1b7ab6;
        border-right:1px solid #fff;
        color: #fff;
    }
    .zbox-btn{
        margin: 0;
    }
    .w{
        background-image: url("http://shop.gogo198.cn/addons/sz_yi/static/images/%E8%A7%92%E6%A0%87-%E5%B7%A6%E4%B8%8A%E8%A7%92.png");
        background-repeat:no-repeat;
        background-size:19%;
    }
</style>
<link rel="stylesheet" href="../addons/sz_yi/static/css/toast.css">
<link rel="stylesheet" href="../addons/sz_yi/static/css/centermenu.css">
<div style="text-align:center;margin:50px 0; font:normal 14px/24px 'MicroSoft YaHei';">
</div>

<header>
    <div class="logo"><img src="../addons/sz_yi/static//images/logo.png" alt=""></div>
    <div class="plane"><img src="../addons/sz_yi/static//images/plane.png" alt=""></div>
</header>
<div class="warp">
    <div class="content">
        <div class="warp-con">
            <p class="details-num">直邮编号：<?php  echo $osn['ordersn'];?></p>
            <input type="hidden" name="oid" id="oid" value="<?php  echo $order['ordersn'];?>">
            <div class="layui-form">
                <table class="layui-table">
                    <colgroup>
                        <col width="18%">
                        <col width="15%">
                        <col width="18%">
                        <col width="15%">
                        <col width="20%">
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>项目</th>
                        <th>应付</th>
                        <th>商户优惠</th>
                        <th>实付</th>
                        <th>备注</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php  if(is_array($orderDetail)) { foreach($orderDetail as $item) { ?>
                    <tr>
                        <td><?php  echo $item['fee_type'];?></td>
                        <td class="t-deepred"><?php  echo $item['fee_price'];?></td>
                        <td class="t-deepred"><?php  echo $item['fee_price']-$item['fee_price1'];?></td>
                        <td class="t-deepred"><?php  echo $item['fee_price1'];?></td>
                        <td></td>
                    </tr>
                    <?php  } } ?>
                    <tr style="text-align: center;">
                        <td colspan="5" onclick="sel_coupon();">
                            <span style="float: left;">使用优惠卷</span>
                            <span class="t-deepred iscouponname" style="float: right;color: #ccc;">></span>
                        </td>
                    </tr>
                    <tr style="text-align: center;">
                        <td colspan="5">应付总额：<span class="t-deepred"><?php  echo $order['total_price'];?></span> - 优惠总额：<span class="t-deepred youhui"><?php  echo ($order['total_price']-$order['total_price1']);?></span> = 实付总额：<span class="t-deepred inpay"><?php  echo $order['total_price1'];?></span></td>
                    </tr>

                    </tbody>
                </table>
            </div>
            <div class="shop-title">
                <div class="shop-title" style="border-bottom: 0;">
                    <div class="check-box">
                        <label for="checkbox1"></label>
                        <input type="checkbox" id="checkbox1" />
                    </div>
                    <div class="shop-name">本人已阅读《直邮易服务协议》，知悉及同意协议相关条款和约定。</div>
                </div>
                <div class="shop-title" style="border-bottom: 0;">
                    <div class="check-box">
                        <label for="checkbox1"></label>
                        <input type="checkbox" id="checkbox1" />
                    </div>
                    <div class="shop-name">本人已阅读《直邮易服务协议》，知悉及同意协议相关条款和约定。</div>
                </div>
                <div class="shop-title" style="border-bottom: 0;">
                    <div class="check-box">
                        <label for="checkbox1"></label>
                        <input type="checkbox" id="checkbox1" />
                    </div>
                    <div class="shop-name">本人已阅读《直邮易服务协议》，知悉及同意协议相关条款和约定。</div>
                </div>
            </div>
            <div class="price-pay dis">
                <ul>
                    <li>
                        <div class="check-box">
                            <label for="checkbox1"></label>
                            <input type="checkbox" id="checkbox1" name="all"/>
                        </div>
                    </li>
                    <li style="width: 36%; margin-right: 5px;"><img src="../addons/sz_yi/static//images/but.jpg" alt="" style="width: 100%;"></li>
                    <li style="color: #1b7ab6; margin-top: 5px;">
                        <p>恭喜你！本次直邮仅需<span class="t-deepred inpay"><?php  echo $order['total_price1'];?></span></p>
                        <p>好抱歉！本次直邮还需<span class="t-deepred inpay"><?php  echo $order['total_price1'];?></span></p>
                    </li>
                </ul>
                <div class="price-text5" style="float: right;">
                    <input  type="hidden" name="isCoupon" id="isCoupon" value="">
                    <button id="alertBtn" type="button" class="zbox-btn zbox-btn-blue zbox-btn-outlined" onclick="pay();return false;">
                        <img src="../addons/sz_yi/static//images/icon_06.png" alt="">支付
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH));?>
<script src="../addons/sz_yi/static/js/toast.js"></script>
<script src="../addons/sz_yi/static/js/centermenu.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/framework7.bundle.min.js"></script>

<script>
    var App = new Framework7();
    var $$ = Dom7;
    var Amount ="<?php  echo $order['total_price1'];?>";
    var amount2= "<?php  echo ($order['total_price']-$order['total_price1'])?>";
    var isDis = 0;
    const pay=()=>{
      if(isCheckPact()){
         return;
      }
      App.request.post("<?php  echo $this->createMobileUrl('order/easy_deliver_directpostage_detail')?>",{
          oid:$$('#oid').val(),
          isCoupon:$$('#isCoupon').val()
      },(data)=>{
          data = JSON.parse(data);
          console.dir(data.status);
          if (data.status==1){
              $.DialogByZ.Alert({Title:"提示",Content: data.result});
          }else if(data.status==2){
              postcall(data.result.url,data.result);
          }else{
              window.location.href=data.result;
          }

      });
        return false;
    }

    const postcall=( url, params, target,menth='post')=>{
        var tempform = document.createElement("form");
        tempform.action = url;
        tempform.method = menth;
        tempform.style.display="none"
        if(target) {
            tempform.target = target;
        }
        for (var x in params) {
            var opt = document.createElement("input");
            opt.name = x;
            opt.value = params[x];
            tempform.appendChild(opt);
        }

        var opt = document.createElement("input");
        opt.type = "submit";
        tempform.appendChild(opt);
        document.body.appendChild(tempform);
        tempform.submit();
        document.body.removeChild(tempform);
    }

    const isCheckPact = ()=>{
        var c = false;
        $$('input[type="checkbox"]').each(function () {
            if ($$(this).attr('name')=='all'){
                return true;
            }
            if(!$$(this).prop('checked')){
                $.DialogByZ.Alert({Title:"提示",Content: "请同意协议"});
                c =true;
                return  false;
            }
        });
        return c;
    }
    $$('input[name="all"]').on('click',function () {
        $("input[type='checkbox']").prop("checked", function( i, val ) {
            return !val;
        });
    });

    const sel_coupon = ()=>{
        $('body').centermenu({
            duration:600,
            source:<?php  echo $coupon_str;?>,
            liWidth:300,
            liHeight:50,
            click:function (ret) {
                $('.cpt_selectCenterMenu').remove();
                $('.cpt-dw-mask').remove();
                $('body,html').css({height: 'auto', overflow: 'auto'});
                $(document.body).css({'border-right': 'none',});
                if (!ret.ele.context.lastChild.dataset.cid==""){
                    var coid =ret.ele.context.lastChild.dataset.cid;
                    if (coid==isDis){
                        isDis = 0;
                        $('.youhui').html(amount2);
                        $('.inpay').html(Amount);
                        $('.iscouponname').html('>');
                        $(ret.ele.context).removeClass('w');
                        return false;
                    }
                    isDis = coid;
                    $('.iscouponname').html(ret.ele.context.lastChild.innerText+'>');
                    $('#isCoupon').val(coid);
                    $(ret.ele.context).attr('class','w')
                    $.ajax({
                        url:"<?php  echo $this->createMobileUrl('order/easy_deliver_directpostage_detail')?>&a=coupon_deduction",
                        type:"POST",
                        dataType:"json",
                        data:{receive_id:coid,Amount:Amount},
                        success:function (ret) {
                            if (ret.status==1){
                                $.DialogByZ.Alert({Title:"提示",Content: "使用失败"});
                            }else{
                                $('.youhui').html(parseFloat(amount2)+ret.result.youhui);
                                $('.inpay').html(parseFloat(ret.result.total));
                            }
                        },error:function () {
                            $.DialogByZ.Alert({Title:"提示",Content: "使用失败"});
                        }
                    });
                }
            }
        });
        return false;
    }


</script>
</body>
</html>