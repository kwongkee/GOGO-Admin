<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("header", TEMPLATE_INCLUDEPATH)) : (include template("header", TEMPLATE_INCLUDEPATH));?>
<body>
<header>
    <div class="logo"><img src="../addons/sz_yi/static/images/logo.png" alt=""></div>
    <div class="plane"><img src="../addons/sz_yi/static/images/plane.png" alt=""></div>
</header>
<div class="warp">
    <div style="background: white;">
        <div class="details-img">

            <img src="../attachment/<?php  echo $goods['thumb'];?>" onerror="this.src='../addons/sz_yi/static/images/det_01.jpg'">
        </div>
        <div class="details-title"><?php  echo $goods['title'];?></div>
        <div class="details-c">
            <span class="details-price">¥<?php  echo $goods['marketprice'];?></span>
            <span class="details-stock">库存：<?php  echo $goods['total'];?> 销量:<?php  echo $goods['sales'];?> 浏览:<?php  echo $goods['viewcount'];?></span>
        </div>
        <div class="details-c" id="chose" style="padding:4px 0;">
            <div class="row laisi" style="border-bottom: 1px solid #ccc;">
                <span style="color: #c6000b; padding-left:0px; padding-right: -1px;">*</span>
                <label for="name">买家姓名：</label>
                <input type="text" value="<?php  echo $userIdCard['realname'];?>" id="name" class="chose_input">
            </div>
            <div class="row laisi1" style="border-bottom: 1px solid #ccc;">
                <span style="color: #c6000b; padding-left: 0px; padding-right: -1px;">*</span>
                <label for="idcard">身份证号：</label>
                <input type="text" value="<?php  echo $userIdCard['id_card'];?>" id="idcard" class="chose_input">
            </div>
            <div class="row shuliang">

                <ul style="list-style-type: none;">
                    <li style="width: 50%;"><span style="color: #c6000b; padding-left: 0; padding-right: 5px;">*</span>购买数量：
                    </li>
                    <li style="width: 50%; text-align: right;">
                        <input type="hidden" name="id" value="<?php  echo $goods['id'];?>" id="gid">
                        <input type="hidden" name="diyformid" value="<?php  echo $goods['diyformid'];?>" id="diyformid">
                        <button style="background: #fff;border: 1px solid #ccc;width: 30px;height: 30px;" onclick="o.jian(this);return false;">-</button>
                        <strong style="margin: 0 10px;" id="num">1</strong>
                        <button style="background: #fff;border: 1px solid #ccc;width: 30px;height: 30px;" onclick="o.jia(this);return false;">+</button>
                        <!-- 单价：
                        <em>198元</em> 总计：
                        <span>0元</span> -->
                    </li>
                </ul>
            </div>
           <!-- <a href="">-->
<!--                <span class="details-num">请选择商品规格及数量</span>-->
<!--                <span class="details-row"><i class="fa fa-chevron-right" aria-hidden="true"></i></span>-->
            <!--</a>-->
        </div>
    </div>
    <div style="background: white; margin-top: 10px;">
        <div class="details-wrap">

            <div class="tab">
                <ul class="tab-hd">
                    <li class="active">图文详情</li>
                    <li>产品参数</li>
                    <li style="border-right: 0;">用户评价</li>
                </ul>

                <ul class="tab-bd">
                    <li class="thisclass">
<!--                        <p><img src="https://shop.gogo198.cn/attachment//images/goodetail/head.jpg" alt=""></p>-->
                        <?php  echo $goods['content'];?>
<!--                        <p><img src="https://shop.gogo198.cn/attachment//images/goodetail/footer.jpg" alt=""></p>-->
                    </li>
                    <li>
                        <?php  if(is_array($goodsParam)) { foreach($goodsParam as $item) { ?>
                        <p class="parameter"><?php  echo $item['title'];?>:<?php  echo $item['value'];?> </p>
                        <?php  } } ?>
                    </li>
                    <li>
                        <div id="goodsAccess">
                            <?php  if(is_array($goodsAccess)) { foreach($goodsAccess as $item) { ?>
                            <div class="evaluate">
                                <div class="details-name">
                                <span class="details-pic"><img src="<?php  echo $item['headimgurl'];?>" alt=""
                                                               width="20px"></span>
                                    <span class="det-name"><?php  echo $item['nickname'];?></span>
                                    <span class="details-date"><?php  echo date('Y-m-d H:i:s',$item['createtime'])?></span>
                                </div>
                                <div class="details-warp"><?php  echo $item['content'];?></div>
                            </div>
                            <?php  } } ?>
                        </div>

                        <div style="text-align: center;" onclick="o.getGoodsAccess();"><p>查看更多</p></div>

                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="bottom" style="margin-bottom: 100px;">
    <p>版权所有 © Gogo|購購網 粤ICP备09003656号 </p>
    <p>中国海关注册编码：44289609SL 跨境电商平台备案：C011000000332982</p>
</div>

<div class="menus">
    <ul>
        <li>
            <a href="javascript:void(0);" onclick="o.setFavorite(this);return false;">
            	<p style="margin-top: 6px;">
                    <i class="fa <?php echo (empty($favorite)||$favorite['deleted']==1)?'fa-star-o':'fa-star'?>" <?php echo $favorite['deleted']=='0'?"style='color:#f90'":""?> aria-hidden="true"></i>
                </p>
            	<p>收藏</p>
            </a>
        </li>
        <li>
        	<a href="">
            	<p style="margin-top: 6px;"><i class="fa fa-comments-o" aria-hidden="true"></i></p>
                <p><a href=https://im.7x24cc.com/phone_webChat.html?accountId=N000000014488&chatId=f6600c02-b23f-429f-baeb-c6209dbf6219&nickName=<?php  echo $_W['fans']['nickname'];?>">客服</a></p>
            </a>
        </li>
        <li><a href="javascript:void(0);" onclick="o.addCart(this);return false;">加入购物车</a></li>
        <li><a href="javascript:void(0);" onclick="o.orderNow(this);return false;">立即订购</a></li>
    </ul>
</div>


<!--<div class="taobao">
    <div class="col-lg-3"></div>
    <div class="col-lg-6">
        <div class="row poward">
            <div class="imitation"><img src="../addons/sz_yi/static/images/det_01.jpg" class="img-responsive"
                                        style="width: 94px; height: 94px; padding-top: 3px; padding-left: 3px;"></div>

            <div class="col-xs-6 chose_b">
                <div class="row concent_a"><span>￥<?php  echo $goods['marketprice'];?></span></div>
                <div class="row concent_b"><span>库存<?php  echo $goods['total'];?>件</span></div>
                <div class="row concent_c"><span>请选择规格</span></div>
            </div>

            <div class="col-xs-6 chose_noll">
                <div class="row concent_a"><span>￥<?php  echo $goods['marketprice'];?></span></div>
                <div class="row concent_b"><span>库存<?php  echo $goods['total'];?>件</span></div>
                <div class="row concent_c"><span>请选择规格</span></div>
            </div>

            <div class="col-xs-2 chose_c"><img src="../addons/sz_yi/static/images/chose-01-01.png"
                                               class="img-responsive" style="width: 30px;" id="close1"></div>
        </div>

        <div class="row laisi" style="border-bottom: 1px solid #ccc;">
            <span style="color: #c6000b; padding-left: 5px; padding-right: 5px;">*</span>
            <label for="">买家姓名：</label>
            <input type="text" value="<?php  echo $userIdCard['realname'];?>" class="chose_input">
        </div>
        <div class="row laisi1" style="border-bottom: 1px solid #ccc;">
            <span style="color: #c6000b; padding-left: 5px; padding-right: 5px;">*</span>
            <label for="">身份证号：</label>
            <input type="text" value="<?php  echo $userIdCard['id_card'];?>" class="chose_input">
        </div>
        <div class="row shuliang">

            <ul style="list-style-type: none;">
                <li style="width: 50%;"><span style="color: #c6000b; padding-left: 0; padding-right: 5px;">*</span>购买数量：
                </li>
                <li style="width: 50%; text-align: right;">
                    <button style="background: #fff;border: 1px solid #ccc;width: 30px;height: 30px;" onclick="o.jian(this);return false;">-</button>
                    <strong style="margin: 0 10px;" id="num">1</strong>
                    <button style="background: #fff;border: 1px solid #ccc;width: 30px;height: 30px;" onclick="o.jia(this);return false;">+</button>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-lg-3"></div>
</div>-->
<script>
    $(function () {
        $(".tab-hd").children().hover(function () {
            $(this).addClass("active").siblings().removeClass("active");
            $(".tab-bd").children().eq($(".tab-hd").children().index(this)).show().siblings().hide();
        });
        // $("#chose").click(function () {
        //     $(".taobao").slideDown();
        //     $("#body").css("background-color", "#ffffff");
        //     $("#body").css("opacity", "0.5");
        // });
        // $("#close1").click(function () {
        //     $(".taobao").hide();
        //     $("#body").css("background-color", "#ffffff");
        //     $("#body").css("opacity", "1");
        // });
        $("#btns").click(function () {
            $(".rr").hide();
        });
        $("#btns2").click(function () {
            $(".zcxs").hide();
        });
        $("#btnp1").click(function () {
            $(".chose_b").hide();
            $(".chose_noll").show();
        });
    });
    const o = {
        data:{
            page:1,
            isBtn:false,
            isCart:false,
        },
        jia:function(obj){
            var num =parseInt($('#num').html());
            $('#num').html((num+1));
        },
        jian:function(obj){
            var num =parseInt($('#num').html())-1;
            $('#num').html((num<=1?1:num));
        },
        getGoodsAccess:function () {
            $.ajax({
                url:"<?php  echo $this->createPluginMobileUrl('directmailorder/detail',['id'=>$goods['id'],'a'=>'getGoodsAccess'])?>",
                type:"GET",
                data:{page:(this.data.page+1)},
                dataType:"json",
                success:function (ret) {
                    if (ret.result!=undefined){
                        o.setGoodsAccess(ret.result);
                    }
                }
            });
            return false;
        },
        setGoodsAccess:function (result) {
            var h='';
            var n=result.length;
            for (var i=0;i<n;i++){
                h+='  <div class="evaluate">\n' +
                    '<div class="details-name">\n' +
                    '<span class="details-pic"><img src="'+result[i].headimgurl+'" alt="" width="20px"></span>\n' +
                    '<span class="det-name">'+result[i].nickname+'</span>\n' +
                    '<span class="details-date">'+result[i].createtime+'</span>\n' +
                    '</div>\n' +
                    '<div class="details-warp">'+result[i].content+'</div>\n' +
                    '</div>';
            }
            $('#goodsAccess').append(h);
        },
        addCart:function (obj) {
            var name= $('#name').val();
            var idcard = $('#idcard').val();
            var num=$('#num').html();
            var id=$('#gid').val();
            var diyformid=$('#diyformid').val();
            if (name==""||idcard==""){
                return jqueryAlert({
                    'content' : '请填写身份信息'
                });
            }
            if (this.data.isBtn){
                return false;
            }
            this.data.isBtn=true;
            $.ajax({
                url:"<?php  echo $this->createPluginMobileUrl('directmailorder/addcart')?>",
                type:"POST",
                dataType:"json",
                data:{diyformdata:{diyformid:diyformid,diydata:{diymaijiaxingming:name,diyshenfenzhenghao:idcard}},total:num,id:id},
                success:function (ret) {
                    o.data.isBtn=false;
                    if (ret.status==1){
                        o.data.isCart=true;
                    }
                    return jqueryAlert({
                        'content':ret.result.message
                    });
                },error:function (xhr) {
                    o.data.isBtn=false;
                    return jqueryAlert({
                        'content':'添加失败'
                    });
                }
            });
            return false;
        },
        orderNow:function (obj) {
            if (this.data.isCart){
                window.location.href="<?php  echo $this->createMobileUrl('shop/easy_deliver_cart')?>";
                return true;
            }
            var name= $('#name').val();
            var idcard = $('#idcard').val();
            var num=$('#num').html();
            var id=$('#gid').val();
            var diyformid=$('#diyformid').val();
            if (name==""||idcard==""){
                return jqueryAlert({
                    'content' : '请填写身份信息'
                });
            }
            if (this.data.isBtn){
                return false;
            }
            this.data.isBtn=true;
            $.ajax({
                url:"<?php  echo $this->createPluginMobileUrl('directmailorder/addcart')?>",
                type:"POST",
                dataType:"json",
                data:{diyformdata:{diyformid:diyformid,diydata:{diymaijiaxingming:name,diyshenfenzhenghao:idcard}},total:num,id:id},
                success:function (ret) {
                    o.data.isBtn=false;
                    if (ret.status==1){
                        window.location.href="<?php  echo $this->createMobileUrl('shop/easy_deliver_cart')?>";
                    }else{
                        return jqueryAlert({
                            'content':ret.result.message
                        });
                    }

                },error:function (xhr) {
                    o.data.isBtn=false;
                    return jqueryAlert({
                        'content':'添加失败'
                    });
                }
            });
            return false;
        },
        setFavorite:function (obj) {
            $.ajax({
                url:"<?php  echo $this->createPluginMobileUrl('directmailorder/detail',['id'=>$goods['id'],'a'=>'setFavorite'])?>",
                type:"GET",
                dataType:"json",
                success:function (ret) {
                    if (ret.result.isfavorite){
                        $(obj).html('<p style="margin-top: 6px;">\n' +
                            '<i class="fa fa-star" style="color:#f90" aria-hidden="true"></i>\n' +
                            '</p>\n' +
                            '<p>收藏</p>');
                    }else{
                        $(obj).html('<p style="margin-top: 6px;">\n' +
                            '<i class="fa fa-star-o"  aria-hidden="true"></i>\n' +
                            '</p>\n' +
                            '<p>收藏</p>');
                    }
                }
            })
        }
    }

</script>
</body>
</html>