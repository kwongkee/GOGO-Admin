<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>仓储订单</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-fluid{background:#f8f8f8;}
    .header{background:#fff;}
    .header .logo{width:100%;height:auto;}
    .white_bg{background:#fff;border-radius:5px;}
    .cen_notice{padding:10px 15px;box-sizing: border-box;border-bottom:1px solid rgb(229,229,229);font-size:13px;}
    .menu_box1{border-right:2px solid #f8f8f8;border-bottom:2px solid #f8f8f8;}
    .menu_box2{border-bottom:2px solid #f8f8f8;}
    .menu_box3{border-right:2px solid #f8f8f8;}
    .menu_part1{padding: 25px 0 25px 5px;justify-content: center;}
    .menu_part1 img{width:45px;margin-right:5px;}
    .menu_part1 .menu_part1_text p:nth-of-type(1){color:#717171;}
    .menu_part1 .menu_part1_text p:nth-of-type(2){font-size:13px;color:#9a9a9a;margin-top:12px;}

    .menu_part2_container{justify-content: space-between;}
    .menu_part2_container .layui-col-xs3{width:24%;}
    .menu_part2{text-align: center;background:#fff;padding:10px 0;box-sizing: border-box;border-radius:5px;}
    .menu_part2 img{width:35px;}
    .menu_part2 p{font-size:13px;}

    .line_part{width:100%;border-radius:5px;background:#fff;overflow: hidden;margin-bottom:10px;}
    .line_part .line_blue{background:#1790FF;width:5px;height:100px;max-height: 150px;min-height:100px;}
    .line_part .line_part_content{width:100%;padding:10px;box-sizing: border-box;}
    .line_part .line_part_content .line_title{font-weight: bold;max-width: 200px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;-webkit-line-clamp: 1;}
    .line_part .line_part_content .line_time{color:#a9a9a9;font-size:13px;}
    .line_part .line_part_content .line_price{font-size:13px;margin:10px 0;}
    .line_part .line_part_content .line_price .line_price_red{color:#ff2222;}
    .line_part .line_part_content .line_accept{font-size:13px;width:100%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;-webkit-line-clamp: 1;}

    .layui-layer-hui .layui-layer-content{color:#fff;}
    .eventInfo,.payerInfo{display:none;}

    div{overflow: visible;}
    .line{width:100%;height:1px;background:#eee;margin:5px 0;}
    .layui-card{float:left;padding:5px 0;box-sizing:border-box;}
    .up{background:#009688;}
    .layui-card-header{font-size:16px;font-weight:bold;}
    .layui-col-xs12 .title{font-size:15px;font-weight:bold;}
    .event{float:left;border: 1px solid #efefef;margin-bottom: 10px;padding: 10px;}
    .layui-table th{font-size:14px;font-weight:bold;color:#000;}
    .layui-table tr td,.layui-table th{text-align:center;}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15" style="padding-top:5px;">
        <form class="layui-form" lay-filter="component-form-element1" style="padding-left:0;padding-right:0;">
            <div class="layui-col-md12" style="background:#fff;">
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;display: flex;align-items: center;">
                    <div class="layui-col-xs3 title">
                        <img src="../addons/sz_yi/static/images/gogo_order_avatar.jpg" alt="" style="width:100px;">
                    </div>
                    <div class="layui-col-xs8 val" style="text-align: center;font-weight:bold;font-size:20px;">
                        仓储订单列表
                    </div>
                </div>

                <div class="layui-col-xs12">
                    <table class="layui-table" style="margin-top:0;">
                        <thead>
                        <tr style="background: rgb(189,215,238);">
                            <th style="width:12%;">序号</th>
                            <th>付款客户</th>
                            <th>生成日期</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php  if(is_array($list)) { foreach($list as $k => $v) { ?>
                        <tr>
                            <td><?php  echo $k+1;?></td>
                            <td><?php  echo $v['payer_name'];?></td>
                            <td><?php  echo $v['createtime'];?></td>
                            <td>
                                <div class="layui-btn layui-btn-xs" onclick="info(<?php  echo $v['id'];?>)">进入订单</div>
                            </td>
                        </tr>
                        <?php  } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    layui.use(['layer','jquery','element','laydate','form'], function() {
        var layer = layui.layer
            , $ = layui.jquery
            , element = layui.element
            , laydate = layui.laydate
            , form = layui.form;

    });

    function info(id){
        var layer = layui.layer
            , $ = layui.jquery;

        window.location.href='https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=warehouse&op=sureOrderList&m=sz_yi&id='+id;
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>