<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>自定义收款</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;font-size:15px;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; overflow: auto;white-space: nowrap;}
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:39px; width:100%; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#1E9FFF !important; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .disf{display:flex;}
    /**合同文件**/
    .info_main .images {/**display:inline-block;float:left;**/ width:fit-content;max-width:90%;height:38px;white-space:nowrap;overflow-x:scroll; }
    .info_main .images .img { display:inline-block;/**float:left;**/ position:relative;width:30px;height:30px;border:1px solid #e9e9e9;margin-right:5px;margin-top:5px;}
    .info_main .images .img img { position:absolute;top:0; width:100%;height:100%;}
    .info_main .images .img .minus { position:absolute;color:red;width:8px;height:12px;top:-18px;right:-1px;}
    .info_main .plus { /**float:left;display:inline-block;**/ width:30px;height:30px;border:1px solid #e9e9e9; color:#dedede;; font-size:18px;line-height:30px;text-align:center;margin-top:2px;}
    .info_main .plus i { left:7px;top:7px;}

    .bigautocomplete-layout{background:#fff;position:absolute;height: 200px;overflow: scroll;border:1px solid #000;}
    .bigautocomplete-layout table tr td div{height: 30px;line-height: 30px;}
    .back{height: 44px;width:96%;margin:14px auto 0;background: #aaa;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
    .express_voucher_disp,.trade_rate_block,.bill_box_disp{display:block;}
    .express_voucher_nodisp,.trade_rate_hide,.bill_box_nodisp{display:none;}

    .fa-check-circle-o{color: rgb(0, 204, 153);}
    .voucher_type_show{display:block;}
    .voucher_type_hide{display:none;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />

<link href="../addons/sz_yi/template/mobile/default/static/js/star-rating.css" media="all" rel="stylesheet" type="text/css"/>
<script src="../addons/sz_yi/template/mobile/default/static/js/star-rating.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/ajaxfileupload.js" type="text/javascript"></script>

<script src="../addons/sz_yi/static/js/jquery.bigautocomplete.js"></script>

<link rel="stylesheet" href="../addons/sz_yi/static/css/layui.css">
<div id="container">
    <div class="page_topbar">
        <div class="title">付款登记修改</div>
    </div>
    <div class="info_main">
        <!--付款状态-->
        <div class="line">
            <div class="title">付款状态</div>
            <div class="info"><div class="inner">
                <select name="money_status" id="money_status" readonly="readonly" onfocus="this.defaultIndex=this.selectedIndex;"
onchange="this.selectedIndex=this.defaultIndex;">
                    <option value="">请选择付款状态</option>
                    <option value="4" <?php echo $data['money_status']=='4'?'selected':''?>>已收票，应付款</option>
                    <option value="5" <?php echo $data['money_status']=='5'?'selected':''?>>已收票，未付款</option>
                    <option value="6" <?php echo $data['money_status']=='6'?'selected':''?>>未收票，预付款</option>
                </select>
            </div></div>
        </div>
        <!--开票状态-->
        <div class="line bill_box <?php echo $data['money_status']=='4'?'bill_box_disp':'bill_box_nodisp'?>">
            <div class="title">开票状态</div>
            <div class="info"><div class="inner">
                <select name="bill_status" id="bill_status" readonly="readonly" onfocus="this.defaultIndex=this.selectedIndex;"
onchange="this.selectedIndex=this.defaultIndex;">
                    <option value="">请选择开票状态</option>
                    <option value="1" <?php echo $data['bill_status']=='1'?'selected':''?>>早前开票</option>
                    <option value="2" <?php echo $data['bill_status']=='2'?'selected':''?>>本月开票</option>
                </select>
            </div></div>
        </div>
        <!--早前开票-->
        <div class="line bill_list" style="display:none;height:fit-content;max-height:400px;overflow:scroll;">
        </div>
        <!--选择凭证-->
        <div class="line voucher_list" style="height:fit-content;">
        </div>
        <input type="text" name="voucher_id" id="voucher_id" value="" style="display:none;"/>
        
        <!--凭证信息-->
        <?php  if((($data['money_status']==4 && $data['bill_status']==2) || ($data['money_status']==4 && $data['bill_status']==1) || ($data['money_status']==5))) { ?>
        <div class="voucher_info">
            <!--凭证类别-->
            <div class="line">
                <div class="title">凭证类别</div>
                <div class='info'>
                    <div class='inner' style="height:fit-content;">
                        <span class="voucher_type_radio" data-val="1"><i class="fa <?php echo $data['voucher_type']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>购买发票(抵扣/发票联)</span>
                        <span class="voucher_type_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>付款凭证</span>
                        <span class="voucher_type_radio" data-val="3" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type']==3?'fa-check-circle-o':'fa-circle-o';?>"></i>形式发票</span>
                        <input type="text" name="voucher_type" id="voucher_type" value="<?php  echo $data['voucher_type'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--购买发票(抵扣/发票联)-->
            <div class="line voucher_type1 <?php echo $data['voucher_type']==1?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type1_radio" data-val="1"><i class="fa <?php echo $data['voucher_type1']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>增值税专用发票</span>
                        <span class="voucher_type1_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type1']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>增值税普通发票</span>
                        <span class="voucher_type1_radio" data-val="3" style="margin-left:5px;margin-right: 10px;"><i class="fa <?php echo $data['voucher_type1']==3?'fa-check-circle-o':'fa-circle-o';?>"></i>增值税电子普通发票</span>
                        <input type="text" name="voucher_type1" id="voucher_type1" value="<?php  echo $data['voucher_type1'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--增值税普通发票-->
            <div class="line voucher_type1_2 <?php echo $data['voucher_type1']==2?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type1_2_radio" data-val="1"><i class="fa <?php echo $data['voucher_type1_2']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>增值税普通发票(折叠票)</span>
                        <span class="voucher_type1_2_radio" data-val="2" style="margin-left:5px;margin-right: 10px;"><i class="fa <?php echo $data['voucher_type1_2']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>增值税普通发票(卷式)</span>
                        <input type="text" name="voucher_type1_2" id="voucher_type1_2" value="<?php  echo $data['voucher_type1_2'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--付款凭证-->
            <div class="line voucher_type2 <?php echo $data['voucher_type']==2?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type2_radio" data-val="1"><i class="fa <?php echo $data['voucher_type2']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>行政收据</span>
                        <span class="voucher_type2_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type2']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>企业收据</span>
                        <input type="text" name="voucher_type2" id="voucher_type2" value="<?php  echo $data['voucher_type2'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--行政收据-->
            <div class="line voucher_type2_1 <?php echo $data['voucher_type2']==1?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type2_1_radio" data-val="1"><i class="fa <?php echo $data['voucher_type2_1']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>财政票据</span>
                        <span class="voucher_type2_1_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type2_1']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>税收票证</span>
                        <span class="voucher_type2_1_radio" data-val="3" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type2_1']==3?'fa-check-circle-o':'fa-circle-o';?>"></i>海关票据</span>
                        <input type="text" name="voucher_type2_1" id="voucher_type2_1" value="<?php  echo $data['voucher_type2_1'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--财政票据-->
            <div class="line voucher_type3 <?php echo $data['voucher_type2_1']==1?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type3_radio" data-val="1"><i class="fa <?php echo $data['voucher_type3']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>非税收入票据</span>
                        <span class="voucher_type3_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type3']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>其他财政票据</span>
                        <input type="text" name="voucher_type3" id="voucher_type3" value="<?php  echo $data['voucher_type3'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--非税收入票据-->
            <div class="line voucher_type3_1 <?php echo $data['voucher_type3']==1?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type3_1_radio" data-val="1"><i class="fa <?php echo $data['voucher_type3_1']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>统一通用票据</span>
                        <span class="voucher_type3_1_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type3_1']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>部门专用票据</span>
                        <input type="text" name="voucher_type3_1" id="voucher_type3_1" value="<?php  echo $data['voucher_type3_1'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--统一通用票据-->
            <div class="line voucher_type4_1 <?php echo $data['voucher_type3_1']==1?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type4_1_radio" data-val="1"><i class="fa <?php echo $data['voucher_type4_1']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>非税收入统一票据</span>
                        <span class="voucher_type4_1_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type4_1']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>非税收入一般缴款书</span>
                        <input type="text" name="voucher_type4_1" id="voucher_type4_1" value="<?php  echo $data['voucher_type4_1'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--部门专用票据-->
            <div class="line voucher_type4_2 <?php echo $data['voucher_type3_1']==2?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type4_2_radio" data-val="1"><i class="fa <?php echo $data['voucher_type4_2']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>定额票据</span>
                        <span class="voucher_type4_2_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type4_2']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>非定额票据</span>
                        <input type="text" name="voucher_type4_2" id="voucher_type4_2" value="<?php  echo $data['voucher_type4_2'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--其他财政票据-->
            <div class="line voucher_type3_2 <?php echo $data['voucher_type3']==2?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type3_2_radio" data-val="1"><i class="fa <?php echo $data['voucher_type3_2']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>社会团体会费收据</span>
                        <span class="voucher_type3_2_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type3_2']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>医疗票据</span>
                        <span class="voucher_type3_2_radio" data-val="3" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type3_2']==3?'fa-check-circle-o':'fa-circle-o';?>"></i>捐赠收据</span>
                        <input type="text" name="voucher_type3_2" id="voucher_type3_2" value="<?php  echo $data['voucher_type3_2'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--税收票证-->
            <div class="line voucher_type5 <?php echo $data['voucher_type2_1']==2?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type5_radio" data-val="1"><i class="fa <?php echo $data['voucher_type5']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>税收缴款书</span>
                        <span class="voucher_type5_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type5']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>税收收入退还书</span>
                        <span class="voucher_type5_radio" data-val="3" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type5']==3?'fa-check-circle-o':'fa-circle-o';?>"></i>税收完税证明</span>
                        <span class="voucher_type5_radio" data-val="4" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type5']==4?'fa-check-circle-o':'fa-circle-o';?>"></i>出口货物劳务专用税收票证</span>
                        <span class="voucher_type5_radio" data-val="5" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type5']==5?'fa-check-circle-o':'fa-circle-o';?>"></i>印花税专用税收票证</span>
                        <input type="text" name="voucher_type5" id="voucher_type5" value="<?php  echo $data['voucher_type5'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--出口货物劳务专用税收票证-->
            <div class="line voucher_type5_1 <?php echo $data['voucher_type5']==4?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type5_1_radio" data-val="1"><i class="fa <?php echo $data['voucher_type5_1']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>税收缴款书(出口货物劳务专用)</span>
                        <span class="voucher_type5_1_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type5_1']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>出口货物完税分割单</span>
                        <input type="text" name="voucher_type5_1" id="voucher_type5_1" value="<?php  echo $data['voucher_type5_1'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--海关票据-->
            <div class="line voucher_type6 <?php echo $data['voucher_type2_1']==3?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type6_radio" data-val="1"><i class="fa <?php echo $data['voucher_type6']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>海关税款专用缴款书</span>
                        <span class="voucher_type6_radio" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['voucher_type6']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>海关货物滞报金专用票据</span>
                        <input type="text" name="voucher_type6" id="voucher_type6" value="<?php  echo $data['voucher_type6'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--企业收据-->
            <div class="line voucher_type2_2 <?php echo $data['voucher_type2']==2?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type2_2_radio" data-val="1"><i class="fa <?php echo $data['voucher_type2_2']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>工资结算单</span>
                        <span class="voucher_type2_2_radio" data-val="2"><i class="fa <?php echo $data['voucher_type2_2']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>费用报销单</span>
                        <span class="voucher_type2_2_radio" data-val="3"><i class="fa <?php echo $data['voucher_type2_2']==3?'fa-check-circle-o':'fa-circle-o';?>"></i>借款收据</span>
                        <span class="voucher_type2_2_radio" data-val="4"><i class="fa <?php echo $data['voucher_type2_2']==4?'fa-check-circle-o':'fa-circle-o';?>"></i>领款收据</span>
                        <input type="text" name="voucher_type2_2" id="voucher_type2_2" value="<?php  echo $data['voucher_type2_2'];?>" style="display: none;">
                    </div>
                </div>
            </div>
            <!--凭证编号-->
            <div class="line"><div class="title">凭证编号</div><div class='info'><div class='inner'>
                <div class="disf" style="align-items: center;">
                    <input type="text" id='voucher' placeholder="请输入凭证编号"  value="<?php  echo $data['voucher'];?>"  style="width:80%;"/>
                    <img id="camera_call" src="../addons/sz_yi/static/images/camera.png" alt="" style="width:25px;margin-left:5px;">
                </div>
            </div></div></div>
            <!--凭证日期-->
            <div class="line">
                <div class="title">凭证日期</div>
                <div class="info"><div class="inner">
                    <input type="text" id="voucher_date" placeholder="点击选择日期" readonly value="<?php  echo $data['voucher_date'];?>"/>
                </div></div>
            </div>
            <!--内容摘要-->
            <div class="line">
                <div class="title">内容摘要</div>
                <div class="info"><div class="inner">
                        <span class="content" data-val="1"><i class="fa <?php echo $data['content']==1?'fa-check-circle-o':'fa-circle-o';?>"></i>选择</span>
                        <span class="content" data-val="2" style="margin-left:5px;"><i class="fa <?php echo $data['content']==2?'fa-check-circle-o':'fa-circle-o';?>"></i>录入</span>
                        <input type="text" name="content" id="content" value="<?php  echo $data['content'];?>" style="display: none;">
                </div></div>
            </div>
            <!--内容选择-->
            <div class="line tax_classify_sel <?php echo $data['content']==1?'voucher_type_show':'voucher_type_hide';?>">
                <!--<div class="title">分类选择</div>-->
                <!--<div class="info"><div class="inner">-->
                    <!--<input type="text" name="tax_classify_sel" id="tax_classify_sel" placeholder="请选择税收分类" class="layui-input" value="<?php  echo $data['tax_classify_sel'];?>">-->
                <!--</div></div>-->
                <div class="disf">
                    <div class="title">分类选择</div>
                    <form class="layui-form" lay-filter="component-form-element1" style="width:40%;">
                        <select name="tax_classify_sel" id="tax_classify_sel" lay-search>
                            <option value="">请选择税收分类</option>
                            <?php  if(is_array($tax_classify)) { foreach($tax_classify as $v) { ?>
                            <option  value="<?php  echo $v['id'];?>" <?php  if($v['id']==$data['tax_classify_sel']) { ?>selected<?php  } ?>><?php  echo $v['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </form>
                </div>
            </div>
            <!--内容录入-->
            <div class="line tax_classify_inp <?php echo $data['content']==2?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title">分类录入</div>
                <div class="info"><div class="inner">
                    <input type="text" id='tax_classify_inp' placeholder="请输入税收分类名称"  value="<?php  echo $data['tax_classify_inp'];?>"/>
                </div></div>
            </div>
            <!--凭证金额-->
            <div class="line"><div class="title">凭证金额</div><div class='info'><div class='inner'>
                <div class="disf" style="align-items: center;">
                    <select name="currency" id="currency" style="width:48%;margin-right:5px;">
                        <option value="">请选择付款币种</option>
                        <?php  if(is_array($currency)) { foreach($currency as $v) { ?>
                        <option value="<?php  echo $v['code_value'];?>" <?php echo $v['code_value']==$data['currency']?'selected':''?>><?php  echo $v['code_name'];?></option>
                        <?php  } } ?>
                    </select>
                    <input type="number" id='trade_price' placeholder="请输入凭证金额"  style="width:50%;" value="<?php  echo $data['trade_price'];?>" onkeyup="value=value.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1')"/>
                </div>
            </div></div></div>
        </div>
        <?php  } ?>
        
        <!--付款信息-->
        <?php  if((($data['money_status']==4 && $data['bill_status']==1) || ($data['money_status']==4 && $data['bill_status']==2) || ($data['money_status']==6))) { ?>
        <div class="collect_info">
            <!--付款金额-->
            <div class="line"><div class="title">付款金额</div><div class='info'><div class='inner'>
                <div class="disf" style="align-items: center;">
                    <select name="currency2" id="currency2" style="width:32%;margin-right:5px;">
                        <option value="">请选择付款币种</option>
                        <?php  if(is_array($currency)) { foreach($currency as $v) { ?>
                        <option value="<?php  echo $v['code_value'];?>" <?php echo $v['code_value']==$data['currency2']?'selected':''?>><?php  echo $v['code_name'];?></option>
                        <?php  } } ?>
                    </select>
                    <input type="number" id='trade_price2' placeholder="付款金额"  style="width:32%;" value="<?php  echo $data['trade_price2'];?>" onkeyup="value=value.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1')"/>
                    <input type="number" id='trade_rate' placeholder="交易汇率"  class="<?php echo $data['currency2']!=142?'trade_rate_block':'trade_rate_hide'?>"  style="width:32%;" value="<?php  echo $data['trade_rate'];?>"/>
                </div>
            </div></div></div>
            <!--付款方式-->
            <div class="line">
                <div class="title">付款方式</div>
                <div class="inner">
                    <span class="collect_type" data-val="1"><i class="fa <?php echo $data['collect_type']==1?'fa-check-circle-o':'fa-circle-o';?>"></i> 现金付款</span>&nbsp;&nbsp;
                    <span class="collect_type" data-val="2"><i class="fa <?php echo $data['collect_type']==2?'fa-check-circle-o':'fa-circle-o';?>"></i> 转账付款</span>
                    <input type="text" name="collect_type" id="collect_type" value="<?php  echo $data['collect_type'];?>" style="display: none;">
                </div>
            </div>
            <!--转账付款-->
            <div class="line transfer_collect <?php echo $data['collect_type']==2?'voucher_type_show':'voucher_type_hide';?>"><div class="title" style="background:#fff;z-index:99;">转账付款</div><div class="info"><div class="inner" style="z-index:98;">
                <span class="transfer_collect_type" data-val="1"><i class="fa <?php echo $data['transfer_collect_type']==1?'fa-check-circle-o':'fa-circle-o';?>"></i> 企业账户</span>&nbsp;&nbsp;
                <span class="transfer_collect_type" data-val="2"><i class="fa <?php echo $data['transfer_collect_type']==2?'fa-check-circle-o':'fa-circle-o';?>"></i> 个人账户</span>
                <span class="transfer_collect_type" data-val="3"><i class="fa <?php echo $data['transfer_collect_type']==3?'fa-check-circle-o':'fa-circle-o';?>"></i> 支付账户</span>
                <input type="text" name="transfer_collect_type" id="transfer_collect_type" value="<?php  echo $data['transfer_collect_type'];?>" style="display: none;">
            </div></div></div>
            <div class="line collect_account <?php echo $data['collect_type']==2?'voucher_type_show':'voucher_type_hide';?>">
                <div class="title">选择账户</div><div class='info'><div class='inner'>
                <select name="collect_account" id="collect_account">
                    <?php  if(is_array($data['account_info'])) { foreach($data['account_info'] as $k => $val) { ?>
                    <option value="<?php  echo $val['id'];?>" <?php echo $data['collect_account']==$val['id']?'selected':''?>><?php  echo $val['bank_account'];?>-<?php  echo $val['bank_name'];?>-<?php  echo $val['name'];?></option>
                    <?php  } } ?>
                </select>
            </div></div>
            </div>
            <!--<div class="account_receipt" style="display:none;">-->
                <!--<div class="line"><div class="title">付款回单</div><div class='info'><div class='inner'>-->
                    <!--<div class="pic img_info" data-ogid='0' data-max='99'>-->
                        <!--<div class="images">-->
                            <!--if !empty($data['account_receipt'])-->
                                <!--loop $data['account_receipt'] $v2-->
                                <!--<div data-img="<?php  echo $v2;?>" class="img">-->
                                    <!--<a href="https://shop.gogo198.cn/attachment/$v2"><img src="https://shop.gogo198.cn/attachment/$v2" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" ></a>-->
                                    <!--<div class="minus minus_del">-->
                                    <!--<i class="fa fa-minus-circle"></i>-->
                                    <!--</div>-->
                                <!--</div>-->
                                <!--/loop-->
                            <!--/if-->
                        <!--</div>-->
    <!---->
                        <!--<div class="plus" style="position:relative;" ><i class="fa fa-plus" style="position:absolute;"></i>-->
                            <!--<input type="file" name='imgFile0' id='imgFile0'  style="position:absolute;width:30px;height:30px;-webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;" />-->
                        <!--</div>-->
                    <!--</div>-->
                <!--</div></div></div>-->
            <!--</div>-->
        </div>
        <?php  } ?>
        
        <!--随附凭证-->
        <div class="attach_info" style="display:block;">
            <!--随附凭证-->
            <!--<div class="line"><div class="title">随附凭证</div><div class='info'><div class='inner'><input type="number" id='attach_cert' placeholder="单位：（张）"  value="<?php  echo $data['attach_cert'];?>"/></div></div></div>-->
            <!--凭证类型-->
            <div class="line">
                <div class="title">凭证类型</div>
                <div class="info"><div class="inner">
                    <select name="submit_method" id="submit_method">
                        <option value="">请选择凭证类型</option>
                        <option value="1" <?php echo $data['submit_method']==1?'selected':'';?>>纸质文件</option>
                        <option value="2" <?php echo $data['submit_method']==2?'selected':'';?>>电子文件</option>
                    </select>
                </div></div>
            </div>
            <!--电邮提交时，显示上传凭证-->
            <?php $data['submit_method']==2?'express_voucher_disp':'';?>  <?php $data['submit_method']==1?'express_voucher_nodisp':'';?>
            <div class="express_voucher">
                <div class="line"><div class="title">原始凭证</div><div class='info'><div class='inner'>
                    <div class="pic img_info" data-ogid='2' data-max='99' style="height:38px;overflow-x: scroll;display: flex;align-items: center;">
                        <div class="images">
                            <?php  if(!empty($data['express_voucher'])) { ?>
                            <?php  if(is_array($data['express_voucher'])) { foreach($data['express_voucher'] as $v2) { ?>
                            <div data-img="<?php  echo $v2;?>" class="img">
                                <a href="https://shop.gogo198.cn/attachment/<?php  echo $v2;?>"><img src="https://shop.gogo198.cn/attachment/<?php  echo $v2;?>" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" ></a>
                                <div class="minus minus_del">
                                <i class="fa fa-minus-circle"></i>
                                </div>
                            </div>
                            <?php  } } ?>
                            <?php  } ?>
                        </div>
    
                        <div class="plus" style="position:relative;" ><i class="fa fa-plus" style="position:absolute;"></i>
                            <input type="file" name='imgFile2' id='imgFile2'  style="position:absolute;width:30px;height:30px;-webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;" />
                        </div>
                    </div>
                </div></div></div>
            </div>
        </div>
        <!--自定义备注-->
        <div class="line" style="height:70px;line-height: 0">
            <div class="title">备注信息</div>
            <div class="info"><div class="inner">
                <textarea name="diy_remark" id="diy_remark" style="width:290px;" cols="40" rows="4" placeholder="请输入此凭证备注信息"><?php  echo $data['diy_remark'];?></textarea>
            </div></div>
        </div>
        <?php  if($data['status']==3) { ?>
            <!--有待复核未记账,修改补充-->
            <div class="line"><div class="title">拒绝原因</div><div class='info'><div class='inner' style="color:#ff5555;">
                <?php  echo $data['remark'];?>
            </div></div></div>
            <div class="line"><div class="title">补充文件</div><div class='info'><div class='inner'>
                <div class="pic img_info" data-ogid='1' data-max='99'>
                    <div class="images">
                        <!--<div data-img="" class="img">-->
                        <!--<img src="">-->
                        <!--<div class="minus minus_del">-->
                        <!--<i class="fa fa-minus-circle"></i>-->
                        <!--</div>-->
                        <!--</div>-->
                    </div>

                    <div class="plus" style="position:relative;" ><i class="fa fa-plus" style="position:absolute;"></i>
                        <input type="file" name='imgFile1' id='imgFile1'  style="position:absolute;width:30px;height:30px;-webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;" />
                    </div>
                </div>
            </div></div></div>
        <?php  } ?>
    </div>
    <div class="info_sub" style="margin-bottom:10px;text-align:center;width:100%;">提交更改</div>
    <div class="button back" onclick="javascript:history.back(-1);">返回</div>
</div>

<script id="tpl_img" type="text/html">
    <div class='img' data-img='<%filename%>'>
        <img src='<%url%>'  onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" />
        <div class='minus'><i class='fa fa-minus-circle'></i></div>
    </div>
</script>
<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script language="javascript">
    $(function(){
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload;

            form.render(null, 'component-form-element1');
        });
        // $("#tax_classify_sel").bigAutocomplete({data:<?php  echo $tax_classify;?>});
    });

    require(['tpl', 'core'], function(tpl, core) {
        //时间
        var currYear = (new Date()).getFullYear();
        var opt = {};
        opt.date = {preset: 'date'};
        opt.datetime = {preset: 'date'};
        opt.time = {preset: 'time'};
        opt.default = {
            // theme: 'android-ics light',
            // display: 'modal',
            // mode: 'scroller',
            // dateFormat:'yyyy-mm-dd',
            // lang: 'zh',
            // startYear: currYear,
            // endYear: currYear+1
            theme: 'android-ics light', //皮肤样式
            display: 'modal', //显示方式
            mode: 'scroller', //日期选择模式
            dateFormat: 'yyyy-mm-dd',
            lang: 'zh',
            showNow: true,
            nowText: "今天",
            startYear: currYear, //开始年份
            endYear: currYear //结束年份
        };
        $("#voucher_date").scroller('destroy').scroller($.extend(opt['datetime'], opt['default']));

        //选择凭证日期时判断当月有无提交税费申报
        $('#voucher_date').change(function(){
            let val = $(this).val();
            let money_status = $('#money_status').val();
            let bill_status = $('#bill_status').val();
            //若选择了本月开票，则无法选择上月或下月
            if(money_status==1 && bill_status==2){
                var myDate = new Date();
                let year = myDate.getFullYear();
                let month = myDate.getMonth()+1;

                let now = year+'-'+(month+"").padStart('2','0');

                let select_date = val.split('-');
                var select_date2 = select_date[0]+'-'+select_date[1];

                if(now<select_date2){
                    $(this).val("");
                    alert('不能大于当前月份！');return;
                }
            }

            //提交“对账单”后，无法登记与汇总“对账月”产生的“凭证”。
            $.ajax({
                url: "<?php  echo $this->createMobileUrl('account/register');?>",
                type: 'POST',
                dataType: 'json',
                data: {'op': 'pay_reg', 'add_reg': 4, 'date': select_date2},
                success: function (json) {
                    if(json.status==1){
                        $('#voucher_date').val("");
                        alert(json.result.msg);return;
                    }
                    // var data = json.result.data;
                }
            });
        });

        //凭证类别
        $('.voucher_type_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type').val(val);
            if(val==1){
                $('.voucher_type1').show();
                $('.voucher_type1_2').hide();$('.voucher_type2').hide();$('.voucher_type2_1').hide();$('.voucher_type2_2').hide();
                $('.voucher_type3').hide();$('.voucher_type3_1').hide();$('.voucher_type4_1').hide();$('.voucher_type4_2').hide();
                $('.voucher_type3_2').hide();$('.voucher_type5').hide();$('.voucher_type5_1').hide();$('.voucher_type6').hide();

            }else if(val==2){
                $('.voucher_type2').show();
                $('.voucher_type1').hide();$('.voucher_type1_2').hide();$('.voucher_type2_1').hide();$('.voucher_type2_2').hide();
                $('.voucher_type3').hide();$('.voucher_type3_1').hide();$('.voucher_type4_1').hide();$('.voucher_type4_2').hide();
                $('.voucher_type3_2').hide();$('.voucher_type5').hide();$('.voucher_type5_1').hide();$('.voucher_type6').hide();
            }else{
                $('.voucher_type1').hide();$('.voucher_type1_2').hide();$('.voucher_type2').hide();$('.voucher_type2_1').hide();
                $('.voucher_type2_2').hide();
                $('.voucher_type3').hide();$('.voucher_type3_1').hide();$('.voucher_type4_1').hide();$('.voucher_type4_2').hide();
                $('.voucher_type3_2').hide();$('.voucher_type5').hide();$('.voucher_type5_1').hide();$('.voucher_type6').hide();
            }
        });

        //购买发票(抵扣/发票联)
        $('.voucher_type1_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type1_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type1').val(val);
            if(val==1){
                $('.voucher_type1_2').hide();
            }else if(val==2){
                $('.voucher_type1_2').show();
            }else{
                $('.voucher_type1_2').hide();
            }
        });

        //增值税普通发票
        $('.voucher_type1_2_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type1_2_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type1_2').val(val);
        });

        //付款凭证
        $('.voucher_type2_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type2_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type2').val(val);
            if(val==1){
                $('.voucher_type2_1').show();
                $('.voucher_type2_2').hide();
            }else{
                $('.voucher_type2_1').hide();$('.voucher_type3').hide();$('.voucher_type3_1').hide();$('.voucher_type4_1').hide();
                $('.voucher_type4_2').hide();$('.voucher_type3_2').hide();$('.voucher_type5').hide();$('.voucher_type5_1').hide();
                $('.voucher_type2_2').show();$('.voucher_type6').hide();
            }
        });

        //行政收据
        $('.voucher_type2_1_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type2_1_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type2_1').val(val);
            if(val==1){
                $('.voucher_type3').show();
                $('.voucher_type2_2').hide();$('.voucher_type5').hide();$('.voucher_type5_1').hide();$('.voucher_type6').hide();
            }else if(val==2){
                $('.voucher_type5').show();
                $('.voucher_type3').hide();$('.voucher_type2_2').hide();$('.voucher_type5_1').hide();$('.voucher_type6').hide();
                $('.voucher_type3_1').hide();$('.voucher_type3_2').hide();$('.voucher_type4_1').hide();$('.voucher_type4_2').hide();
            }else if(val==3){
                $('.voucher_type6').show();
                $('.voucher_type3').hide();$('.voucher_type5_1').hide();$('.voucher_type2_2').hide();$('.voucher_type5').hide();
                $('.voucher_type3_1').hide();$('.voucher_type3_2').hide();$('.voucher_type4_1').hide();$('.voucher_type4_2').hide();
            }
        });

        //财政票据
        $('.voucher_type3_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type3_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type3').val(val);
            if(val==1){
                $('.voucher_type3_1').show();
                $('.voucher_type3_2').hide();
            }else if(val==2){
                $('.voucher_type3_2').show();
                $('.voucher_type3_1').hide();$('.voucher_type4_1').hide();$('.voucher_type4_2').hide();
            }
        });

        //非税收入票据
        $('.voucher_type3_1_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type3_1_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type3_1').val(val);
            if(val==1){
                $('.voucher_type4_1').show();
                $('.voucher_type4_2').hide();
            }else if(val==2){
                $('.voucher_type4_2').show();
                $('.voucher_type4_1').hide();
            }
        });

        //统一通用票据
        $('.voucher_type4_1_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type4_1_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type4_1').val(val);
        });

        //部门专用票据
        $('.voucher_type4_2_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type4_2_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type4_2').val(val);
        });

        //其他财政票据
        $('.voucher_type3_2_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type3_2_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type3_2').val(val);
        });

        //企业收据
        $('.voucher_type2_2_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type2_2_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type2_2').val(val);
        });

        //税收票证
        $('.voucher_type5_radio').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type5_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type5').val(val);
            if(val==4){
                $('.voucher_type5_1').show();
                $('.voucher_type3_1').hide();$('.voucher_type4_1').hide();$('.voucher_type4_2').hide();$('.voucher_type3_2').hide();
            }else{
                $('.voucher_type5_1').hide();
                $('.voucher_type3_1').hide();$('.voucher_type4_1').hide();$('.voucher_type4_2').hide();$('.voucher_type3_2').hide();
            }
        });

        //出口货物劳务专用税收票证
        $('.voucher_type5_1_radio').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type5_1_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type5_1').val(val);
        });

        //海关票据
        $('.voucher_type6_radio').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.voucher_type6_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#voucher_type6').val(val);
        });


        //内容摘要
        $('.content').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.content').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            if(val==1){
                $('.tax_classify_sel').show();
                $('.tax_classify_inp').hide();
            }else{
                $('.tax_classify_inp').show();
                $('.tax_classify_sel').hide();
            }
            $('#content').val(val);
        });

        //付款方式
        $('.collect_type').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.collect_type').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#collect_type').val(val);
            if(val==1){
                $('.transfer_collect').hide();
                $('.collect_account').hide();
                $('.account_receipt').show();
            }else if(val==2){
                $('.transfer_collect').show();
            }
        });

        //xx账户收款
        $('.transfer_collect_type').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.transfer_collect_type').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#transfer_collect_type').val(val);
            if(val==1){
                //企业账户收款
                $('.collect_account').show();
                $('.account_receipt').show();
                $('.account_receipt').find('.title').text('付款回单');
                $('.collect_account').find('.title').text('企业账号');
            }else if(val==2){
                //个人账户收款
                $('.collect_account').show();
                $('.account_receipt').show();
                $('.account_receipt').find('.title').text('付款回单');
                $('.collect_account').find('.title').text('个人账号');
            }else{
                //支付账户收款
                $('.collect_account').show();
                $('.account_receipt').show();
                $('.account_receipt').find('.title').text('付款截图');
                $('.collect_account').find('.title').text('支付账号');
            }

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/register');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'collect_reg','type':val},
                success:function(json) {
                    if (json.status == 1) {
                        if(json.result.data){
                            let html = '<option value="">请选择账户</option>';
                            let dat = json.result.data;

                            for(var i2=0;i2<dat.length;i2++){
                                html += '<option value="'+dat[i2].id+'">'+dat[i2].bank_account+'-'+dat[i2].bank_name+'-'+dat[i2].name+'</option>';
                            }
                            $('#collect_account').html(html);

                        }else{
                            if(confirm('系统监测到您还没有配置该类账户，是否现在立刻前往配置！')){
                                window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=basic_set';
                            }
                        }
                    }
                }
            });
        });

        //收款截图+入账回单文件
        $('.minus_del').click(function() {
            $(this).parent().remove();
            core.json('util/uploader', {op: 'remove', file: $(this).parent().data('img')}, function(rjson) {
                if (rjson.status == 1) {

                }
                $('.plus').show();
            }, false, true);
        });

        $('.plus input').change(function() {
            core.loading('正在上传');

            var comment =$(this).closest('.img_info');
            var ogid = comment.data('ogid');
            var max = comment.data('max');

            $.ajaxFileUpload({
                url: core.getUrl('util/uploader'),
                data: {file: "imgFile" + ogid,'op':'uploadFile'},
                secureuri: false,
                fileElementId: 'imgFile' + ogid,
                dataType: 'json',
                success: function(res, status) {
                    core.removeLoading();
                    if(res.status==0){
                        alert(res.message);return;
                    }
                    var obj = $(tpl('tpl_img', res));
                    $('.images',comment).append(obj);

                    $('.minus',comment).click(function() {
                        let t = $(this);
                        let del_img = $(this).parent()[0].dataset.img;
                        // $(obj).data('img')
                        core.json('util/uploader', {op: 'remove', file: del_img}, function(rjson) {
                            if (rjson.status == 1) {
                                // $(obj).remove();
                                t.parent().remove();
                            }
                            $('.plus',comment).show();
                        }, false, true);
                    });

                    if ($('.img',comment).length >= max) {
                        $('.plus',comment).hide();

                    }
                }, error: function(data, status, e) {
                    core.removeLoading();
                    core.tip.show('上传失败!');
                }
            });
        });

        //监控提交方式
        $("#submit_method").change(function() {
            var selected = $(this).children('option:selected').val();
            // console.log(selected);
            var voucher_type = $('#voucher_type').val();
            var voucher_type1 = $('#voucher_type1').val();
            // if( $('#voucher_type').isEmpty()){
            //     $(this).find("option:first").prop("selected","selected");
            //     alert('请选择凭证类别!');
            //     return;
            // }

            if(voucher_type==1) {
                if ($('#voucher_type1').isEmpty()) {
                    $(this).find("option:first").prop("selected","selected");
                    alert('请依次选择销售发票!');
                    return;
                }
            }
            if(!$('#voucher_type').isEmpty()) {
                if (selected == 1) {
                    //快递提交
                    // $('.express_voucher').hide();
                    if (voucher_type != 1) {
                        $(this).find("option:first").prop("selected", "selected");
                        alert('快递提交时只能选择增值税专用发票和增值税普通发票!');
                        return;
                    } else if (voucher_type == 1) {
                        if (voucher_type1 == 3) {
                            $(this).find("option:first").prop("selected", "selected");
                            alert('快递提交时只能选择增值税专用发票和增值税普通发票!');
                            return;
                        }
                    }
                } else if (selected == 2) {
                    //选择电邮提交时，不能选择增值税专用发票和增值税普通发票
                    // $('.express_voucher').show();
                    if (voucher_type == 1 && (voucher_type1 == 1 || voucher_type1 == 2)) {
                        $(this).find("option:first").prop("selected", "selected");
                        alert('电邮提交时不能选择增值税专用发票和增值税普通发票!');
                        return;
                    }
                }
            }
        });

        //凭证金额和币种填写后要与付款金额和币种一致
        $('#currency').change(function(){
            var selected = $(this).children('option:selected').val();
            if($('#money_status').val()!=5) {
                $('#currency2').children('option[value="' + selected + '"]').attr('selected', true).siblings().removeAttr('selected');
                if (selected == 142) {
                    $('#trade_rate').hide();
                } else {
                    $('#trade_rate').show();
                }
            }
        });
        $('#currency2').change(function(){
            var selected = $(this).children('option:selected').val();
            if(selected!=142){
                $('#trade_rate').show();
            }else{
                $('#trade_rate').hide();
            }
        });
        $('#trade_price').on('input',function(){
            if($('#money_status').val()!=5) {
                $('#trade_price2').val($(this).val());
            }
        });
    });

    window.inputs='';
    window.selects='';
    window.img_str='';
    $(function(){
        $('.info_sub').click(function(){
            let now_info = getNowInfo();
            if(now_info[0]==window.inputs && now_info[1]==window.selects && now_info[2]==window.img_str){
                alert('数据没有修改，请修改后再提交修改。');return;
            }
            let money_status = $('#money_status').val();
            let bill_status = $('#bill_status').val();
            //凭证信息
            let voucher_id = $('#voucher_id').val();
            let voucher_type = $('#voucher_type').val();
            let voucher_type1 = $('#voucher_type1').val();
            let voucher_type1_2 = $('#voucher_type1_2').val();
            let voucher_type2 = $('#voucher_type2').val();
            let voucher_type2_1 = $('#voucher_type2_1').val();
            let voucher_type3 = $('#voucher_type3').val();
            let voucher_type3_1 = $('#voucher_type3_1').val();
            let voucher_type4_1 = $('#voucher_type4_1').val();
            let voucher_type4_2 = $('#voucher_type4_2').val();
            let voucher_type3_2 = $('#voucher_type3_2').val();
            let voucher_type5 = $('#voucher_type5').val();
            let voucher_type5_1 = $('#voucher_type5_1').val();
            let voucher_type6 = $('#voucher_type6').val();
            let voucher_type2_2 = $('#voucher_type2_2').val();
            let voucher = $('#voucher').val();
            let voucher_date = $('#voucher_date').val();
            let content = $('#content').val();//内容摘要
            let tax_classify_sel = $('#tax_classify_sel').val();
            let tax_classify_inp = $('#tax_classify_inp').val();
            //收款信息
            let currency = $('#currency').val();
            let currency2 = $('#currency2').val();
            let trade_price = $('#trade_price').val();
            let trade_price2 = $('#trade_price2').val();
            let trade_rate = $('#trade_rate').val();
            let collect_type = $('#collect_type').val();
            let transfer_collect_type = $('#transfer_collect_type').val();
            let collect_account = $('#collect_account').val();//账号
            // var account_receipt = [];//入账回单、付款截图
            // $('.img_info[data-ogid=0]').find('.img').each(function(){
            //     account_receipt.push($(this).data('img'));
            // });
            
            
            var other_file = [];//补充文件
            $('.img_info[data-ogid=1]').find('.img').each(function(){
                other_file.push($(this).data('img'));
            });
            var diy_remark = $('#diy_remark').val();

            //随附凭证信息
            // let attach_cert = $('#attach_cert').val();
            let submit_method = $('#submit_method').val();
            var express_voucher = [];//上传凭证
            $('.img_info[data-ogid=2]').find('.img').each(function(){
                express_voucher.push($(this).data('img'));
            });
            
            
            //凭证信息
            if(money_status==5 || (money_status==4 && bill_status==2)) {
                if( $('#voucher_type').isEmpty()){
                    alert('请选择凭证类别!');
                    return;
                }
                if(voucher_type==1){
                    if( $('#voucher_type1').isEmpty()){
                        alert('请依次选择购买发票（抵扣/发票联）!');
                        return;
                    }
    
                    if(voucher_type1==2){
                        if( $('#voucher_type1_2').isEmpty()){
                            alert('请依次选择增值税普通发票!');
                            return;
                        }
                    }
                }else if(voucher_type==2){
                    if( $('#voucher_type2').isEmpty()){
                        alert('请依次选择付款凭证!');
                        return;
                    }
                    if(voucher_type2==1){
                        if( $('#voucher_type2_1').isEmpty()){
                            alert('请依次选择行政收据!');
                            return;
                        }
    
                        if(voucher_type2_1==1){
                            //财政票据
                            if( $('#voucher_type2_1').isEmpty()){
                                alert('请依次选择财政票据!');
                                return;
                            }
                            if(voucher_type3==1){
                                if($('#voucher_type3_1').isEmpty()){
                                    alert('请依次选择非税收入票据!');
                                    return;
                                }
                                if(voucher_type3_1==1){
                                    if($('#voucher_type4_1').isEmpty()){
                                        alert('请依次选择统一通用票据!');
                                        return;
                                    }
                                }else if(voucher_type3_1==2){
                                    if($('#voucher_type4_2').isEmpty()){
                                        alert('请依次选择部门专用票据!');
                                        return;
                                    }
                                }
                            }else if(voucher_type3==2){
                                if($('#voucher_type3_2').isEmpty()){
                                    alert('请依次选择其他财政票据!');
                                    return;
                                }
                            }
                        }else if(voucher_type2_1==2){
                            //税收票证
                            if($('#voucher_type5').isEmpty()){
                                alert('请依次选择税收票证!');
                                return;
                            }
                            if(voucher_type5==4){
                                if($('#voucher_type5_1').isEmpty()){
                                    alert('请依次选择出口货物劳务专用税收票证!');
                                    return;
                                }
                            }
                        }else if(voucher_type2_1==3){
                            //海关票据
                            if($('#voucher_type6').isEmpty()){
                                alert('请依次选择海关票据!');
                                return;
                            }
    
                        }
    
                    }else if(voucher_type2==2){
                        if( $('#voucher_type2_2').isEmpty()){
                            alert('请依次选择企业收据!');
                            return;
                        }
                    }
                }
    
                if( $('#voucher').isEmpty()){
                    alert('请输入凭证编号!');
                    return;
                }
                if( $('#voucher_date').isEmpty()){
                    alert('请选择日期!');
                    return;
                }
    
                if( $('#content').isEmpty()){
                    alert('请选择内容摘要!');
                    return;
                }
                if(content==1){
                    if( $('#tax_classify_sel').isEmpty()){
                        alert('请选择税收分类!');
                        return;
                    }
                }else if(content==2){
                    if( $('#tax_classify_inp').isEmpty()){
                        alert('请输入税收分类名称!');
                        return;
                    }
                }
                if( $('#currency').isEmpty()){
                    alert('请选择凭证金额币种!');
                    return;
                }
                if(money_status==5) {
                    if (parseFloat(trade_price) < 0 || $('#trade_price').isEmpty()) {
                        alert('请输入正确的凭证金额!');
                        return;
                    }
                }
            }

            if(money_status==4 || money_status==6){
                if(parseFloat(trade_price2)<0 || $('#trade_price2').isEmpty()){
                    alert('请输入正确的付款金额!');
                    return;
                }
            }

            //付款信息
            if((money_status==4 && bill_status==1) || (money_status==4 && bill_status==2) || money_status==6){
                if( $('#currency2').isEmpty()){
                    alert('请选择付款金额币种!');
                    return;
                }
                if(currency2!=142){
                    if($('#trade_rate').isEmpty()){
                        alert('请输入交易汇率!');return;
                    }
                }
                if( $('#collect_type').isEmpty()){
                    alert('请选择付款方式!');
                    return;
                }
                if(collect_type==2){
                    if($('#transfer_collect_type').isEmpty()){
                        alert('请选择转账付款方式!');
                        return;
                    }
                    if($('#collect_account').isEmpty()){
                        alert('请选择账户!');
                        return;
                    }
                    // if( account_receipt.length=='' || account_receipt.length==0){
                    //     if(transfer_collect_type==1 || transfer_collect_type==2){
                    //         alert('请上传入账回单!');
                    //     }else if(transfer_collect_type==3){
                    //         alert('请上传付款截图!');
                    //     }
                    //     return;
                    // }
                }
            }
            
            //随附凭证信息
            // if( $('#attach_cert').isEmpty()){
            //     alert('请输入随附凭证张数!');
            //     return;
            // }
            if($('#submit_method').isEmpty()){
                alert('请选择凭证类型!');
                return;
            }
            if(voucher_type==1 && submit_method==2 && (voucher_type1==1 || voucher_type1==2)){
                // $('.express_voucher').show();
                $('#submit_method').find("option:first").prop("selected","selected");
                alert('电子文件不能选择增值税专用发票和增值税普通发票!');
                return;
            }
            if(money_status!=6){
                if(submit_method==1){
                    // $('.express_voucher').hide();
                    if(voucher_type!=1){
                        $('#submit_method').find("option:first").prop("selected","selected");
                        alert('纸质文件只能选择增值税专用发票和增值税普通发票!');
                        return;
                    }else if(voucher_type==1){
                        if(voucher_type1==3){
                            $('#submit_method').find("option:first").prop("selected","selected");
                            alert('纸质文件只能选择增值税专用发票和增值税普通发票!');
                            return;
                        }
                    }
                }
            }

            if( express_voucher.length=='' || express_voucher.length==0){
                alert('请上传请上传与该笔收/付款交易关联的原始凭证，如银行回单、收付款截图、证明文件、清单文件等!');
            }


            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/register');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'reg_edit','type':<?php  echo $type;?>,'id':<?php  echo $id;?>,'status':<?php  echo $data['status'];?>,'voucher_type':voucher_type,'voucher_type1':voucher_type1,'voucher_type1_2':voucher_type1_2,'voucher_type2':voucher_type2,'voucher_type2_1':voucher_type2_1,'voucher_type3':voucher_type3,'voucher_type3_1':voucher_type3_1,'voucher_type4_1':voucher_type4_1,'voucher_type4_2':voucher_type4_2,'voucher_type3_2':voucher_type3_2,'voucher_type5':voucher_type5,'voucher_type5_1':voucher_type5_1,'voucher_type6':voucher_type6,'voucher_type2_2':voucher_type2_2,'voucher':voucher,'voucher_date':voucher_date,'content':content,'tax_classify_sel':tax_classify_sel,'tax_classify_inp':tax_classify_inp,'currency':currency,'currency2':currency2,'money_status':money_status,'bill_status':bill_status,'trade_price':trade_price,'trade_price2':trade_price2,'trade_rate':trade_rate,'collect_type':collect_type,'transfer_collect_type':transfer_collect_type,'collect_account':collect_account,other_file:other_file,'submit_method':submit_method,'express_voucher':express_voucher,'diy_remark':diy_remark},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result.msg);
                    }else{
                        alert('修改成功！');
                        setTimeout(function(){
                            window.history.back(-1);
                            // window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_manage';
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        initFileds();
    });

    //记录原始内容
    function initFileds(){
        var inputs = document.getElementsByTagName("input");
        for (var i=0;i<inputs.length;i++) {
            if(inputs[i].value!=''){
                window.inputs += inputs[i].value+',';
            }
        }

        var selects = document.getElementsByTagName("select");
        for (var i=0;i<selects.length;i++) {
            if(selects[i].value!=''){
                window.selects += selects[i].value+',';
            }
        }
        var img = $('.img_info').find('.images').find('.img');
        for(var i=0;i<img.length;i++){
            window.img_str += $('.img_info').find('.images').find('.img').eq(i).attr('data-img')+',';
        }
    }

    function getNowInfo(){
        var re_input = '';
        var re_select = '';
        var re_img = '';
        var inputs = document.getElementsByTagName("input");
        for (var i=0;i<inputs.length;i++) {
            if(inputs[i].value!=''){
                re_input += inputs[i].value+',';
            }
        }

        var selects = document.getElementsByTagName("select");
        for (var i=0;i<selects.length;i++) {
            if(selects[i].value!=''){
                re_select += selects[i].value+',';
            }
        }
        var img = $('.img_info').find('.images').find('.img');
        for(var i=0;i<img.length;i++){
            re_img += $('.img_info').find('.images').find('.img').eq(i).attr('data-img')+',';
        }
        return new Array(re_input,re_select,re_img);
    }

    function sel(id){
        //查找该凭证登记信息
        $.ajax({
            url: "<?php  echo $this->createMobileUrl('account/register');?>",
            type: 'POST',
            dataType: 'json',
            data: {'op': 'pay_reg', 'add_reg': 3, 'typ': 1,'id':id},
            success: function (json) {
                var data = json.result.data;
                var html = '<table class="table table-striped" style="margin:5px 0;width:100%;text-align:center;">\n' +
                            '    <tr>\n' +
                            '        <td colspan="3">['+data['voucher']+']凭证信息</td>\n' +
                            '    </tr>\n' +
                            '    <tr>\n' +
                            '        <td>凭证类别</td>\n' +
                            '        <td>凭证日期</td>\n' +
                            '        <td>税收分类</td>\n' +
                            '    </tr>\n' +
                            '    <tr>\n' +
                            '        <td>'+data['voucher_type_name']+'</td>\n' +
                            '        <td>'+data['voucher_date']+'</td>\n' +
                            '        <td>'+data['tax_classify_name']+'</td>\n' +
                            '    </tr>\n' +
                            '</table>';
                //当选择凭证时默认填写凭证类别
                $('#voucher_type').val(data['voucher_type']);
                $('#voucher_type1').val(data['voucher_type1']);
                
                $('#voucher_id').val(data['id']);//凭证ID
                $('.voucher_list').html(html);
            },error:function(json){
                alert('数据出错！');
            }
        });
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>

<script>
    require(['https://res.wx.qq.com/open/js/jweixin-1.2.0.js'],function(wx){
        window.shareData = <?php  echo json_encode($_W['shopshare'])?>;

        jssdkconfig = <?php  echo json_encode($_W['account']['jssdkconfig']);?> || { jsApiList:[] };

        jssdkconfig.debug = false;
        jssdkconfig.jsApiList = ['checkJsApi','onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','showOptionMenu','scanQRCode'];
        wx.config(jssdkconfig);

        $('#camera_call').click(function(){
            wx.scanQRCode({
                desc: 'scanQRCode desc',
                needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
                success: function (res) {
                    if(res.resultStr=='scan resultStr is here'){
                        alert('请使用手机打开扫描！');return;
                    }
                    var result = res.resultStr.split(',');
                    // 回调
                    $('#voucher').val(result[1]);
                },
                error: function(res){
                    if(res.errMsg.indexOf('function_not_exist') > 0){
                        alert('版本过低请升级')
                    }
                }
            });
        });
    });
</script>