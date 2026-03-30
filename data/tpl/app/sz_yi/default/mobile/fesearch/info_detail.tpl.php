<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title><?php  echo $info['title'];?></title>
<style>
    body{background:#f8f8f8;}
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}
    .laytable-cell-1-0-2{height:auto;min-height:auto;}
    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .center{text-align: center;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}

    .box{font-size: 15px;width: 100%;margin: 0 auto;box-shadow: 0px 0px 2px #a7a5a5;border-radius: 5px;box-sizing: border-box;padding:15px 20px;text-align:center;margin-top:15px;color:#fff;}
    .box1{background:#1790FF;}
    .box2{background:#f70e0e;}

    .layui-layer-hui .layui-layer-content{color:#fff;}
    .titles{font-size:18px;font-weight:600;padding:8px;box-sizing:border-box;}
    .descs{margin-bottom: 90px;}
    .descs h3{font-size:18px;background:#1790FF;color:#fff;padding:8px;box-sizing:border-box;}
    .descs .noms h3{display:none;}
    
    .intro h3{font-size:18px;background:#1790FF;color:#fff;padding:8px;box-sizing:border-box;}
    .intro .intro_con>ul .intro-item{float: left;width: 50%;line-height: 34px;font-size: 14px;}
    /*查询结果*/
    .intro_con .general-item-wrap .intro-item .title{font-weight:700;font-size:15px;}
    .descs .noms .des-item .title{font-size:17px;font-weight:600;margin:10px 0 5px 0;}
    .number_box{position:fixed;bottom:-3%;left:50%;transform: translate(-50%,-50%);}
    .number_box .get_number{font-size:18px;color:#fff;background:#ff2222;text-align:center;padding:10px 25px;border-radius:15px;margin:15px auto;width: 80%;opacity: 0.8;}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15" style="padding-top:5px;">
        <div class="layui-col-xs12">
            <!--轮播图-->
            <div class="layui-carousel" id="test1" lay-filter="test1">
                <div carousel-item="">
                    <?php  if(is_array($info['pic_list'])) { foreach($info['pic_list'] as $k => $v) { ?>
                        <img src="<?php  echo $v;?>" alt="" class="" style="width:100%;height:100%;">
                    <?php  } } ?>
                </div>
            </div>
            
            <div class="layui-col-xs12 titles">
                <?php  echo $info['title'];?>
            </div>
            <div class="layui-col-xs12 info">
                <div class="layui-col-xs3" style="color:#ff2222;font-weight:600;text-align:center;">
                    <?php  echo $info['price'];?>    
                </div>
                <div class="layui-col-xs3" style="text-align:center;">
                    <?php  echo $info['area'];?>    
                </div>
                <div class="layui-col-xs3" style="text-align:center;">
                    <?php  echo $info['staff'];?>    
                </div>
                <div class="layui-col-xs3" style="text-align:center;">
                    <?php  echo $info['decoration'];?>    
                </div>
            </div>
            <div class="layui-col-xs12 info" style="margin-bottom:8px;">
                <div class="layui-col-xs3" style="font-size:12px;color:#666;text-align:center;">
                    租金  
                </div>
                <div class="layui-col-xs3" style="font-size:12px;color:#666;text-align:center;">
                    建筑面积  
                </div>
                <div class="layui-col-xs3" style="font-size:12px;color:#666;text-align:center;">
                    推荐工位数 
                </div>
                <div class="layui-col-xs3" style="font-size:12px;color:#666;text-align:center;">
                    装修程度 
                </div>
            </div>
            <div class="layui-col-xs12" style="margin-bottom:8px;">
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3" style="text-align:center;">
                        楼&nbsp;&nbsp;盘&nbsp;:
                    </div>
                    <div class="layui-col-xs9">
                        <?php  echo $info['building_name'];?>
                    </div>
                </div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3" style="text-align:center;">
                        地&nbsp;&nbsp;址&nbsp;:
                    </div>
                    <div class="layui-col-xs9">
                        <?php  echo $info['address'];?>
                    </div>
                </div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3" style="text-align:center;">
                        销&nbsp;&nbsp;售&nbsp;:
                    </div>
                    <div class="layui-col-xs9">
                        <?php  echo $info['seller_name'];?>(<span style="color:#666;font-size:12px;"><?php  echo $info['wuye_name'];?></span>)
                    </div>
                </div>
            </div>
            <div class="layui-col-xs12 intro">
                <h3>概况</h3>
                <div class="intro_con" style="padding:8px;box-sizing:border-box;"><?php  echo $info['intro'];?></div>
            </div>
            <div class="layui-col-xs12 intro">
                <h3>配套</h3>
                <div class="" style="padding:8px;box-sizing:border-box;">
                    <?php  echo $info['mating'];?>
                </div>
            </div>
            <div class="layui-col-xs12 descs">
                <h3>描述</h3>
                <div class="noms" style="padding:8px;box-sizing:border-box;">
                    <?php  echo $info['descs'];?>
                </div>
            </div>
            <div class="layui-col-xs12 number_box">
                <!--<a href="<?php  echo $info['link'];?>">-->
                    <div class="get_number">
                        获取手机
                    </div>
                <!--</a>-->
            </div>
        </div>
    </div>
</div>

<script>
    layui.use(['layer','jquery','carousel','element'], function() {
        var layer = layui.layer
            , $ = layui.jquery
            , element = layui.element
            , carousel = layui.carousel;
            
            //輪播圖
        var lb = carousel.render({
            elem: '#test1'
            /* ,full:true*/
            ,width:'100%' //设置容器宽度
            ,height: '220px' //设置容器高度
            ,arrow: 'block'//始终显示箭头
            ,anim: 'default' //切换动画方式
            ,interval:'3000'//自动切换的时间间隔,不能低于800
            ,indicator:'inside'//指示器位置,如果设定了 anim:'updown'，该参数将无效
            /* ,arrow:'hover'*/
        });
        
        $('.get_number').click(function(){
            layer.open({
                type:2,
                title:'获取电话',
                area:['100%','100%'],
                content:"<?php  echo $info['link'];?>"
            });
        });
    });
</script>