<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>感谢使用</title>
<style>
    body,.layui-footer{background:<?php  echo $website['color'];?>;}
    .layui-btn{background:<?php  echo $website['color'];?>;color:<?php  echo $website['color_word'];?>;border:1px solid <?php  echo $website['color_word'];?>;}
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}
    .laytable-cell-1-0-2{height:auto;min-height:auto;}
    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    .layui-layer-hui .layui-layer-content{color:#fff;}
    .disf{display:flex;align-items: center;}
    .user_tel,.user_email{display:none;}
    .show{display:block;}
    .layui-input-block{line-height:38px;}
    
    .box_content{justify-content:center;border-radius:8px;margin-bottom:30px;box-sizing:border-box;}
    .box_content{background:<?php  echo $website['color'];?>;color:<?php  echo $website['color_word'];?>;border:1px solid <?php  echo $website['color_word'];?>;font-size:25px;text-align:center;padding:30px;width:100%;}
    .box_content p{color:<?php  echo $website['color_word'];?>;}
    .box_content img{width:40px;margin-right:5px;}
</style>
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-2);">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="box_content">
            <p class="f18">感谢使用GOGO，如需管理或跟踪和使用询价，请扫描以下公众号或小程序登录查看。</p>
            <p style="margin:15px 0;"><img src="https://shop.gogo198.cn/app/img/qrcode_for_gogo.jpg" alt="公众号" style="width:200px;height:200px;"/></p>
            <p><img src="" alt="小程序" style="width:200px;height:200px;"/></p>
        </div>
    </div>
</div>