<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title><?php  echo $this->set['texts']['center']?></title>
<style type="text/css">
    body {margin:0px; background:#eee; font-family:'微软雅黑'; }
    a {text-decoration:none;}
    .topbar {padding: 10px;
    background: #f15353 !important;}
.topbar .user_face {
    border: 2px solid #fff;
    height: 48px;
    width: 48px;
    background: #fff;
    margin: 6px auto;
    border-radius: 50px;
}
    .topbar .user_face img {height:100%; width:100%;}
    .topbar .user_info {
    height: 40px;

    width: 100%;
    text-align: center;
}
    .topbar .user_info .user_name {    height: 24px;
    width: 100%;
    font-size: 14px;
    line-height: 24px;
    color: #fff;}
       .topbar .user_info .user_name span {
    font-size: 14px;
    color: #FFE10F;
}
.topbar .user_info .user_date {
    height: 14px;
    width: 100%;
    font-size: 14px;
    line-height: 14px;
    color: #fff;
}

    .top {height:180px;padding:5px; background:#cc3431;}
    .top .top_1 {height:114px; width:100%;}
    .top .top_1 .text {height:114px; width:auto; float:left; color:#fff; line-height:50px; font-size:14px; color:#fff;}
    .top .top_1 .ico {height:40px; width:30px; background:url(../addons/sz_yi/plugin/commission/template/mobile/default/static/images/gold_ico2.png) 0px 10px no-repeat; margin-bottom:74px; float:right;}
    .top .top_2 {height:66px; width:100%; font-size:40px; line-height:66px; color:#fff;}
    .top .top_2 span {height:32px; color:#fff; width:auto; border:1px solid #fff; font-size:14px; line-height:32px; margin-top:17px; padding:0px 15px;  float:right; border-radius:5px;}
    .top .top_2 .disabled { color:#999;border:1px solid #999;}
    .menu {overflow:hidden; background:#fff;}
    .menu .nav { width:33%; float:left;padding-top:10px;padding-bottom:10px; text-align: center;}
    
    .menu .nav .title {height:24px; width:100%; text-align:center; font-size:14px; color:#666;}
    .menu .nav .con {height:20px; width:100%; text-align:center; font-size:12px; color:#999;}
    .menu .nav .con span {color:#f90;}
    .menu .nav1 {border-bottom:1px solid #f1f1f1; border-right:1px solid #f1f1f1;   }
    .menu .nav2 {border-bottom:1px solid #f1f1f1; }
</style>
<div id='container'></div>

<div class="topbar header">
        <div class="user_face"><img src="<?php  echo $member['avatar'];?>"></div>
        <div class="user_info">
            <div class="user_name"><?php  echo $member['nickname'];?><span>
                <?php  if($level) { ?>
                    [<?php  echo $level['levelname'];?>]
                <?php  } ?>
                <?php  if($member['bonus_area']) { ?>
                    <?php  if($member['bonus_area']==1) { ?>
                        [<?php  echo $this->set['texts']['agent_province']?>]
                    <?php  } ?>
                    <?php  if($member['bonus_area']==2) { ?>
                        [<?php  echo $this->set['texts']['agent_city']?>]
                    <?php  } ?>
                    <?php  if($member['bonus_area']==3) { ?>
                        [<?php  echo $this->set['texts']['agent_district']?>]
                    <?php  } ?>
                    <?php  if($member['bonus_area']==4) { ?>
                        [<?php  echo $this->set['texts']['agent_street']?>]
                    <?php  } ?>
                <?php  } ?>
                <?php  if($set['levelurl']!='') { ?><i class='fa fa-question-circle' <?php  if(!empty($set['levelurl'])) { ?>onclick='location.href="<?php  echo $set['levelurl'];?>"'<?php  } ?>></i></span><?php  } ?></div>
                <?php  if($member['agenttime']) { ?>
                <div class="user_date">加入时间：<?php  echo date("Y-m-d", $member['agenttime'])?></div>
                <?php  } ?>
    </div>
 </div>
<div class="comm-center">
    <ul>
        <?php $love_width = p('love') ? "" : "width: 50%";?>
        <li style="<?php  echo $love_width;?>"><a href="#"><span><?php  echo $member['commission_total'];?></span><br/><?php  echo $this->set['texts']['commission_total']?></a></li>
        <li style="<?php  echo $love_width;?>"><a href="#"><span><?php  echo $member['commission_ok'];?></span><br/><?php  echo $this->set['texts']['commission_ok']?></a></li>
        <?php  if(p('love')) { ?>
        <li><a class="recharge" <?php  if($commission_ok<=0 || $commission_ok< $set['withdraw'] || $commission_ok< $set['consume_withdraw']) { ?>href="javascript:;"<?php  } else { ?>href="<?php  echo $this->createPluginMobileUrl('commission/apply')?>"<?php  } ?> id='withdraw' ><p style="margin:0" <?php  if($commission_ok<=0 || $commission_ok< $set['withdraw']) { ?>class='disabled'<?php  } ?> ><?php  echo $this->set['texts']['withdraw']?></p style="margin:0"></a></li>
        <?php  } ?>
    </ul>
</div>

    <div class="menu">  
        <a href="<?php  echo $this->createPluginMobileUrl('bonus/withdraw')?>"><div class="nav nav1"><i class="fa fa-cny fa-3x" style="background:#BC7BFF;"></i><div class="title"><?php  echo $this->set['texts']['commission']?></div><div class="con"><span><?php  echo $member['commission_total'];?></span> 元</div></div></a>
        <?php  if($level) { ?>
        <a href="<?php  echo $this->createPluginMobileUrl('bonus/order')?>"><div class="nav nav1"><i class="fa fa-list fa-3x" style="background:#F981A7;"></i><div class="title"><?php  echo $this->set['texts']['order']?></div><div class="con"><span><?php  echo $member['ordercount0'];?></span> 个</div></div></a>
        <?php  } ?>
        <?php  if($member['bonus_area']) { ?>
        <a href="<?php  echo $this->createPluginMobileUrl('bonus/order_area')?>"><div class="nav nav1"><i class="fa fa-list fa-3x" style="background:#58d5d9;"></i><div class="title"><?php  echo $this->set['texts']['order_area']?></div><div class="con"><span><?php  echo $member['ordercount_area'];?></span> 个</div></div></a>
        <?php  } ?>
        <a href="<?php  echo $this->createPluginMobileUrl('bonus/log')?>"><div class="nav nav1"><i class="fa fa-random  fa-3x" style="background:#ffb13b;"></i><div class="title"><?php  echo $this->set['texts']['commission_detail']?></div><div class="con"><?php  echo $this->set['texts']['commission']?>明细</div></div></a>        
        <a href="<?php  echo $this->createPluginMobileUrl('bonus/customer')?>"><div class="nav nav1"><i class="fa fa-users  fa-3x" style="background:#ff3877;"></i><div class="title"><?php  echo $this->set['texts']['mycustomer']?></div><div class="con"><span><?php  echo $member['customercount'];?></span> 人</div></div></a> 
    </div>
    <script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
            $('#withdraw').click(function(){
                <?php  if($iswithdraw_msg) { ?>
                    core.tip.show('<?php  echo $iswithdraw_msg;?>');
                        return false; 
                <?php  } ?>
            });
    })
</script>
<?php  $show_footer=true;$footer_current ='bonus'?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
