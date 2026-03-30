<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<style>
    div{overflow: visible;}
    .disf{display:flex;align-items:center;}
    .layui-fluid{background:#f8f8f8;padding-top: 10px;}
    .header{background:#fff;}
    .header .logo{width:100%;height:auto;}
    .white_bg{background:#fff;border-radius:5px;margin-bottom: 50px;}
    .menu_box{position:relative;}
    .menu_box .number_notice{text-align: center;position: absolute;top:27px;left:calc(50% - 48px);background:#ff2222;width:20px;height:20px;box-sizing: border-box;border-radius: 50%;color:#fff;font-size:12px;text-overflow: ellipsis;-webkit-line-clamp: 1;white-space: nowrap;overflow: hidden;padding-top:1px;box-sizing: border-box;}
    .menu_box1{border-right:2px solid #f8f8f8;border-bottom:2px solid #f8f8f8;}
    .menu_box2{border-bottom:2px solid #f8f8f8;}
    .menu_box3{border-right:2px solid #f8f8f8;}
    .menu_part1{padding: 25px 0 25px 5px;justify-content: center;}
    .menu_part1 img{width:45px;margin-right:5px;}
    .menu_part1 .menu_part1_text p:nth-of-type(1){color:#717171;}
    .menu_part1 .menu_part1_text p:nth-of-type(2){font-size:13px;color:#9a9a9a;margin-top:12px;}
    .white_bg .common_part{padding:10px;box-sizing: border-box;background:#1790FF;text-align: center;margin:10px 0;color:#fff;}
</style>
<!--LOGO-->
<div class="header"><img src="../addons/sz_yi/static/warehouse/warehouse_logo.png" alt="" class="logo" /></div>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="white_bg">
                <div class="common_part">包裹操作</div>
                <div class="layui-form-item" style="margin-bottom:0;">
                    <div class="layui-col-xs6 menu_box1">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=scan_warehousing">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_01.png" alt="">
                                <div class="menu_part1_text">
                                    <p>确认收货</p>
                                    <p>一键扫描 运抵通知</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="layui-col-xs6 menu_box2 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_inspection">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_02.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹验货</p>
                                    <p>一键扫描 立即验货</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['inspection_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['inspection_num'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="layui-form-item" style="margin-bottom:0px;">
                    <div class="layui-col-xs6 menu_box1 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_warehousing">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_03.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹入库</p>
                                    <p>一键扫描 快速入库</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['in_warehouse_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['in_warehouse_num'];?></div>
                        <?php  } ?>
                    </div>
                    <div class="layui-col-xs6 menu_box2 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_return">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_04.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹退运</p>
                                    <p>一键扫描 快速退运</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['return_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['return_num'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="layui-form-item" style="margin-bottom:0px;">
                    <div class="layui-col-xs6 menu_box1 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_abandon">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_05.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹弃货</p>
                                    <p>一键扫描 快速弃货</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['abandon_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['abandon_num'];?></div>
                        <?php  } ?>
                    </div>
                    <div class="layui-col-xs6 menu_box2 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_spinoff">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_06.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹分拆</p>
                                    <p>一键扫描 快速分拆</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['spinoff_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['spinoff_num'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="layui-form-item" style="margin-bottom:0px;">
                    <div class="layui-col-xs6 menu_box1 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_attachment">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_07.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹附加</p>
                                    <p>一键扫描 快速附加</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['attach_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['attach_num'];?></div>
                        <?php  } ?>
                    </div>
                    <div class="layui-col-xs6 menu_box2 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_rejection">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_08.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹剔除</p>
                                    <p>一键扫描 快速剔除</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['reject_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['reject_num'];?></div>
                        <?php  } ?>
                    </div>
                </div>
                <div class="layui-form-item" style="margin-bottom:0px;">
                    <div class="layui-col-xs6 menu_box1 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_merge">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_09.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹合并</p>
                                    <p>一键确定 快速合并</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['merge_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['merge_num'];?></div>
                        <?php  } ?>
                    </div>
                    <div class="layui-col-xs6 menu_box2 menu_box">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_exWarehouse">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_10.png" alt="">
                                <div class="menu_part1_text">
                                    <p>包裹出库</p>
                                    <p>一键扫描 快速出库</p>
                                </div>
                            </div>
                        </a>
                        <?php  if($data['exWarehouse_num']>0) { ?>
                        <div class="number_notice"><?php  echo $data['exWarehouse_num'];?></div>
                        <?php  } ?>
                    </div>
                </div>

                <!--包裹仓储设置-->
                <div class="common_part">仓储设置</div>
                <div class="layui-form-item" style="margin-bottom:0px;">
                    <div class="layui-col-xs6 menu_box1">
                        <a href="./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=setting">
                            <div class="menu_part1 disf">
                                <img src="../addons/sz_yi/static/warehouse/index_setting.png" alt="">
                                <div class="menu_part1_text">
                                    <p>仓储设置</p>
                                    <p>包裹仓储 一键设置</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_footer', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_footer', TEMPLATE_INCLUDEPATH));?>

