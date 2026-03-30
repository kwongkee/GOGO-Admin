<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>自定义收款</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;font-size:15px;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; overflow: auto;white-space: nowrap;}
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:39px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
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

    .bigautocomplete-layout{background:#fff;position:absolute;height: 200px;overflow: scroll;}
    .bigautocomplete-layout table tr td div{height: 30px;line-height: 30px;}
    button, input, optgroup, select, textarea{font:unset !important;color:black !important;line-height:1.5 !important;}
    .sel{box-sizing: border-box;padding: 4px 10px;font-size: 14px;color: #fff;background: #1E9FFF;text-align:center;width: fit-content;margin: 0 auto;}
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

<!--<script src="../addons/sz_yi/static/js/jquery.bigautocomplete.js"></script>-->
<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />
<link rel="stylesheet" href="../addons/sz_yi/static/css/layui.css">

<div id="container">
    <div class="page_topbar">
        <div class="title">付款登记</div>
    </div>
    <div class="info_main">
        <!--付款状态-->
        <div class="line">
            <div class="title">付款状态</div>
            <div class="info"><div class="inner">
                <select name="money_status" id="money_status">
                    <option value="">请选择付款状态</option>
                    <option value="4">已收票，应付款</option>
                    <option value="5">已收票，未付款</option>
                    <option value="6">未收票，预付款</option>
                </select>
            </div></div>
        </div>
        <!--开票状态-->
        <div class="line bill_box" style="display:none;">
            <div class="title">开票状态</div>
            <div class="info"><div class="inner">
                <select name="bill_status" id="bill_status">
                    <option value="">请选择开票状态</option>
                    <option value="1">早前开票</option>
                    <option value="2">本月开票</option>
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
        <div class="voucher_info" style="display:none;">
            <!--凭证类别-->
            <div class="line">
                <div class="title">凭证类别</div>
                <div class='info'>
                    <div class='inner' style="height:fit-content;">
                        <span class="voucher_type_radio" data-val="1"><i class="fa fa-circle-o"></i>购买发票(抵扣/发票联)</span>
                        <span class="voucher_type_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>付款凭证</span>
                        <span class="voucher_type_radio" data-val="3" style="margin-left:5px;"><i class="fa fa-circle-o"></i>形式发票</span>
                        <input type="text" name="voucher_type" id="voucher_type" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--购买发票(抵扣/发票联)-->
            <div class="line voucher_type1" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type1_radio" data-val="1"><i class="fa fa-circle-o"></i>增值税专用发票</span>
                        <span class="voucher_type1_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>增值税普通发票</span>
                        <span class="voucher_type1_radio" data-val="3" style="margin-left:5px;margin-right: 10px;"><i class="fa fa-circle-o"></i>增值税电子普通发票</span>
                        <input type="text" name="voucher_type1" id="voucher_type1" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--增值税普通发票-->
            <div class="line voucher_type1_2" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type1_2_radio" data-val="1"><i class="fa fa-circle-o"></i>增值税普通发票(折叠票)</span>
                        <span class="voucher_type1_2_radio" data-val="2" style="margin-left:5px;margin-right: 10px;"><i class="fa fa-circle-o"></i>增值税普通发票(卷式)</span>
                        <input type="text" name="voucher_type1_2" id="voucher_type1_2" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--付款凭证-->
            <div class="line voucher_type2" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type2_radio" data-val="1"><i class="fa fa-circle-o"></i>行政收据</span>
                        <span class="voucher_type2_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>企业收据</span>
                        <input type="text" name="voucher_type2" id="voucher_type2" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--行政收据-->
            <div class="line voucher_type2_1" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type2_1_radio" data-val="1"><i class="fa fa-circle-o"></i>财政票据</span>
                        <span class="voucher_type2_1_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>税收票证</span>
                        <span class="voucher_type2_1_radio" data-val="3" style="margin-left:5px;"><i class="fa fa-circle-o"></i>海关票据</span>
                        <input type="text" name="voucher_type2_1" id="voucher_type2_1" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--财政票据-->
            <div class="line voucher_type3" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type3_radio" data-val="1"><i class="fa fa-circle-o"></i>非税收入票据</span>
                        <span class="voucher_type3_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>其他财政票据</span>
                        <input type="text" name="voucher_type3" id="voucher_type3" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--非税收入票据-->
            <div class="line voucher_type3_1" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type3_1_radio" data-val="1"><i class="fa fa-circle-o"></i>统一通用票据</span>
                        <span class="voucher_type3_1_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>部门专用票据</span>
                        <input type="text" name="voucher_type3_1" id="voucher_type3_1" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--统一通用票据-->
            <div class="line voucher_type4_1" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type4_1_radio" data-val="1"><i class="fa fa-circle-o"></i>非税收入统一票据</span>
                        <span class="voucher_type4_1_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>非税收入一般缴款书</span>
                        <input type="text" name="voucher_type4_1" id="voucher_type4_1" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--部门专用票据-->
            <div class="line voucher_type4_2" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type4_2_radio" data-val="1"><i class="fa fa-circle-o"></i>定额票据</span>
                        <span class="voucher_type4_2_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>非定额票据</span>
                        <input type="text" name="voucher_type4_2" id="voucher_type4_2" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--其他财政票据-->
            <div class="line voucher_type3_2" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type3_2_radio" data-val="1"><i class="fa fa-circle-o"></i>社会团体会费收据</span>
                        <span class="voucher_type3_2_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>医疗票据</span>
                        <span class="voucher_type3_2_radio" data-val="3" style="margin-left:5px;"><i class="fa fa-circle-o"></i>捐赠收据</span>
                        <input type="text" name="voucher_type3_2" id="voucher_type3_2" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--税收票证-->
            <div class="line voucher_type5" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type5_radio" data-val="1"><i class="fa fa-circle-o"></i>税收缴款书</span>
                        <span class="voucher_type5_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>税收收入退还书</span>
                        <span class="voucher_type5_radio" data-val="3" style="margin-left:5px;"><i class="fa fa-circle-o"></i>税收完税证明</span>
                        <span class="voucher_type5_radio" data-val="4" style="margin-left:5px;"><i class="fa fa-circle-o"></i>出口货物劳务专用税收票证</span>
                        <span class="voucher_type5_radio" data-val="5" style="margin-left:5px;"><i class="fa fa-circle-o"></i>印花税专用税收票证</span>
                        <input type="text" name="voucher_type5" id="voucher_type5" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--出口货物劳务专用税收票证-->
            <div class="line voucher_type5_1" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type5_1_radio" data-val="1"><i class="fa fa-circle-o"></i>税收缴款书(出口货物劳务专用)</span>
                        <span class="voucher_type5_1_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>出口货物完税分割单</span>
                        <input type="text" name="voucher_type5_1" id="voucher_type5_1" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--海关票据-->
            <div class="line voucher_type6" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type6_radio" data-val="1"><i class="fa fa-circle-o"></i>海关税款专用缴款书</span>
                        <span class="voucher_type6_radio" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>海关货物滞报金专用票据</span>
                        <input type="text" name="voucher_type6" id="voucher_type6" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--企业收据-->
            <div class="line voucher_type2_2" style="display:none;">
                <div class="title"></div>
                <div class='info'>
                    <div class='inner'>
                        <span class="voucher_type2_2_radio" data-val="1"><i class="fa fa-circle-o"></i>工资结算单</span>
                        <span class="voucher_type2_2_radio" data-val="2"><i class="fa fa-circle-o"></i>费用报销单</span>
                        <span class="voucher_type2_2_radio" data-val="3"><i class="fa fa-circle-o"></i>借款收据</span>
                        <span class="voucher_type2_2_radio" data-val="4"><i class="fa fa-circle-o"></i>领款收据</span>
                        <input type="text" name="voucher_type2_2" id="voucher_type2_2" value="" style="display: none;">
                    </div>
                </div>
            </div>
            <!--凭证编号-->
            <div class="line"><div class="title">凭证编号</div><div class='info'><div class='inner'>
                <div class="disf" style="align-items: center;">
                    <input type="text" id='voucher' placeholder="请输入凭证编号"  value="" style="width:80%;"/>
                    <img id="camera_call" src="../addons/sz_yi/static/images/camera.png" alt="" style="width:25px;margin-left:5px;">
                </div>
            </div></div></div>
            <!--凭证日期-->
            <div class="line">
                <div class="title">凭证日期</div>
                <div class="info"><div class="inner">
                    <input type="text" id="voucher_date" placeholder="点击选择日期" readonly value=''/>
                </div></div>
            </div>
            <!--内容摘要-->
            <div class="line">
                <div class="title">内容摘要</div>
                <div class="info"><div class="inner">
                    <span class="content" data-val="1"><i class="fa fa-circle-o"></i>选择</span>
                    <span class="content" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>录入</span>
                    <input type="text" name="content" id="content" value="" style="display: none;">
                </div></div>
            </div>
            <!--内容选择-->
            <div class="line tax_classify_sel" style="display:none;">
                <!--<div class="title">分类选择</div>-->
                <!--<div class="info"><div class="inner">-->
                    <!--<input type="text" name="tax_classify_sel" id="tax_classify_sel" placeholder="请选择税收分类" class="layui-input">-->
                <!--</div></div>-->
                <div class="disf">
                    <div class="title">分类选择</div>
                    <form class="layui-form" lay-filter="component-form-element1" style="width:40%;">
                        <select name="tax_classify_sel" id="tax_classify_sel" lay-search>
                            <option value="">请选择税收分类</option>
                            <?php  if(is_array($tax_classify)) { foreach($tax_classify as $v) { ?>
                            <option  value="<?php  echo $v['id'];?>"><?php  echo $v['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </form>
                </div>
            </div>
            <!--内容录入-->
            <div class="line tax_classify_inp" style="display:none;">
                <div class="title">分类录入</div>
                <div class="info"><div class="inner">
                    <input type="text" id='tax_classify_inp' placeholder="请输入税收分类名称"  value=""/>
                </div></div>
            </div>
            <!--凭证金额-->
            <div class="line"><div class="title">凭证金额</div><div class='info'><div class='inner'>
                <div class="disf" style="align-items: center;">
                    <select name="currency" id="currency" style="width:48%;margin-right:5px;">
                        <option value="">请选择付款币种</option>
                        <?php  if(is_array($currency)) { foreach($currency as $v) { ?>
                        <option value="<?php  echo $v['code_value'];?>" <?php echo $v['code_value']==142?'selected':''?>><?php  echo $v['code_name'];?></option>
                        <?php  } } ?>
                    </select>
                    <input type="number" id='trade_price' placeholder="请输入凭证金额"  style="width:50%;" value="" onkeyup="value=value.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1')"/>
                </div>
            </div></div></div>
        </div>
        
        <!--收款信息-->
        <div class="collect_info" style="display:none;">
            <div class="line"><div class="title">付款金额</div><div class='info'><div class='inner'>
                <div class="disf" style="align-items: center;">
                    <select name="currency2" id="currency2" style="width:32%;margin-right:5px;">
                        <option value="">请选择付款币种</option>
                        <?php  if(is_array($currency)) { foreach($currency as $v) { ?>
                        <option value="<?php  echo $v['code_value'];?>" <?php echo $v['code_value']==142?'selected':''?>><?php  echo $v['code_name'];?></option>
                        <?php  } } ?>
                    </select>
                    <input type="number" id='trade_price2' placeholder="付款金额"  style="width:32%;" value="" onkeyup="value=value.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1')"/>
                    <input type="number" id='trade_rate' placeholder="交易汇率"  style="width:32%;display:none;" value=""/>
                </div>
            </div></div></div>
            <!--付款方式-->
            <div class="line">
                <div class="title">付款方式</div>
                <div class="inner">
                    <span class="collect_type" data-val="1"><i class="fa fa-circle-o"></i> 现金付款</span>&nbsp;&nbsp;
                    <span class="collect_type" data-val="2"><i class="fa fa-circle-o"></i> 转账付款</span>
                    <input type="text" name="collect_type" id="collect_type" value="" style="display: none;">
                </div>
            </div>
            <!--转账付款-->
            <div class="line transfer_collect" style="display: none;"><div class="title" style="background:#fff;z-index:99;">转账付款</div><div class="info"><div class="inner" style="z-index:98;">
                <span class="transfer_collect_type" data-val="1"><i class="fa fa-circle-o"></i> 企业账户</span>&nbsp;&nbsp;
                <span class="transfer_collect_type" data-val="2"><i class="fa fa-circle-o"></i> 个人账户</span>
                <span class="transfer_collect_type" data-val="3"><i class="fa fa-circle-o"></i> 支付账户</span>
                <input type="text" name="transfer_collect_type" id="transfer_collect_type" value="" style="display: none;">
            </div></div></div>
            <div class="line collect_account" style="display:none;">
                <div class="title">选择账户</div><div class='info'><div class='inner'>
                <select name="collect_account" id="collect_account">
                    <option value="">请选择账户</option>
                </select>
            </div></div>
            </div>
            <!--<div class="account_receipt" style="display:none;">-->
                <!--<div class="line"><div class="title">付款回单</div><div class='info'><div class='inner'>-->
                    <!--<div class="pic img_info" data-ogid='0' data-max='99'>-->
                        <!--<div class="images">-->
                        <!--</div>-->
    <!---->
                        <!--<div class="plus" style="position:relative;" ><i class="fa fa-plus" style="position:absolute;"></i>-->
                            <!--<input type="file" name='imgFile0' id='imgFile0'  style="position:absolute;width:30px;height:30px;-webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;" />-->
                        <!--</div>-->
                    <!--</div>-->
                <!--</div></div></div>-->
            <!--</div>-->
        </div>
        
        <!--随附凭证-->
        <div class="attach_info" style="display:none;">
            <!--随附凭证-->
            <!--<div class="line"><div class="title">随附凭证</div><div class='info'><div class='inner'><input type="number" id='attach_cert' placeholder="单位：（张）"  value=""/></div></div></div>-->
            <!--凭证类型-->
            <div class="line">
                <div class="title">凭证类型</div>
                <div class="info"><div class="inner">
                    <select name="submit_method" id="submit_method">
                        <option value="">请选择凭证类型</option>
                        <option value="1">纸质文件</option>
                        <option value="2">电子文件</option>
                    </select>
                </div></div>
            </div>
            <!--上传凭证-->
            <div class="express_voucher">
                <div class="line"><div class="title">原始凭证</div><div class='info'><div class='inner'>
                    <div class="pic img_info" data-ogid='1' data-max='99' style="height:38px;overflow-x: scroll;display: flex;align-items: center;">
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
            </div>
            <!--备注信息-->
            <div class="line" style="height:97px;line-height: 0;">
                <div class="title">备注信息</div>
                <div class="info"><div class="inner">
                    <textarea name="diy_remark" id="diy_remark" cols="35" rows="4" style="width:290px;" placeholder="请输入此凭证备注信息"></textarea>
                </div></div>
            </div>
        </div>
    </div>
    <div class="info_sub" style="margin-bottom:10px;">提交</div>
    <div class="button back" onclick="javascript:history.back(-1);" style="height: 44px;margin: 14px 2%;background: #aaa;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;width:96%;">返回</div>
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
                        if(json.result.data!=''){
                            let html = '<option value="">请选择账户</option>';
                            let dat = json.result.data;

                            for(var i2=0;i2<dat.length;i2++){
                                html += '<option value="'+dat[i2].id+'">'+dat[i2].bank_account+'-'+dat[i2].bank_name+'-'+dat[i2].name+'</option>';
                            }
                            $('#collect_account').html(html);

                        }else{
                            if(confirm('系统监测到您还没有配置该类账户，是否现在立刻前往配置！')){
                                window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=basic_set';
                            }else{
                                let html='<option value="">请选择账户</option>';
                                $('#collect_account').html(html);
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
                        alert('纸质文件只能选择增值税专用发票和增值税普通发票!');
                        return;
                    } else if (voucher_type == 1) {
                        if (voucher_type1 == 3) {
                            $(this).find("option:first").prop("selected", "selected");
                            alert('纸质文件只能选择增值税专用发票和增值税普通发票!');
                            return;
                        }
                    }
                } else if (selected == 2) {
                    //选择电邮提交时，不能选择增值税专用发票和增值税普通发票
                    // $('.express_voucher').show();
                    if (voucher_type == 1 && (voucher_type1 == 1 || voucher_type1 == 2)) {
                        $(this).find("option:first").prop("selected", "selected");
                        alert('电子文件不能选择增值税专用发票和增值税普通发票!');
                        return;
                    }
                }
            }
        });

        //付款状态
        $('#money_status').change(function(){
            var selected = $(this).children('option:selected').val();
            $('#bill_status').find('option:first').prop('selected','selected');
            if(selected==4){
                //已开票，应付款
                $('.bill_box').show();
                $('.bill_list').hide();
            }else if(selected==5){
                //已开票，未付款
                $('.bill_box').hide();
                $('.voucher_info').show();
                $('.attach_info').show();
                $('.collect_info').hide();
                $('.bill_list').hide();
            }else if(selected==6){
                //未开票，预付款
                $('.bill_box').hide();
                $('.voucher_info').hide();
                $('.attach_info').show();
                $('.collect_info').show();
                $('.bill_list').hide();
            }
        });

        //开票状态
        $('#bill_status').change(function() {
            var selected = $(this).children('option:selected').val();
            if(selected==1){
                //早前开票
                $('.voucher_info').hide();
                $('.attach_info').show();
                $('.collect_info').show();
                $('.bill_list').show();

                //查找”已开票，未收款”的“已登记、已汇总、已记账、已对账”
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/register');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'pay_reg','add_reg':2,'typ':1},
                    success:function(json) {
                        var data = json.result.data;

                        var html = '<table class="table table-striped" style="margin-bottom:0;width:100%;text-align:center;">\n' +
                            '       <tr>\n' +
                            '         <td colspan="3">“已开票，未付款”列表</td>\n' +
                            '       </tr>\n' +
                            '       <tr>\n' +
                            '         <td>凭证编号</td>\n' +
                            '         <td>状态</td>\n' +
                            '         <td>操作</td>\n' +
                            '       </tr>';
                        if(data==''){
                            html += '   <tr>\n' +
                                    '         <td colspan="3">暂无信息</td>\n' +
                                    '       </tr>\n';
                            html += '</table>';
                        }else{
                            for(var i=0;i<data.length;i++){
                                
                                if(data[i].id>0){
                                    html += '   <tr>\n' +
                                        '         <td>'+data[i]['voucher']+'</td>\n' +
                                        '         <td>'+data[i]['status']+'</td>\n' +
                                        '         <td><div class="sel" onclick="sel('+data[i]['id']+')">选择</div></td>\n' +
                                        '       </tr>';
                                }
                            }
                        }
                        html += '</table>';
                        $('.bill_list').html(html);
                    },error:function(json){
                        alert('数据出错！');
                    }
                });
            }else if(selected==2){
                //本月开票
                $('.voucher_info').show();
                $('.attach_info').show();
                $('.collect_info').show();
                $('.bill_list').hide();
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
    })
    $(function(){
        $('.info_sub').click(function(){
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
            //随附凭证信息
            // let attach_cert = $('#attach_cert').val();
            let submit_method = $('#submit_method').val();
            var express_voucher = [];//上传凭证
            $('.img_info[data-ogid=1]').find('.img').each(function(){
                express_voucher.push($(this).data('img'));
            });
            
            let diy_remark = $('#diy_remark').val();
            
            if( $('#money_status').isEmpty()){
                alert('请选择收款状态!');
                return;
            }
            
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
            // if(submit_method==2){
                //电邮提交
                if( express_voucher.length=='' || express_voucher.length==0){
                    alert('请上传请上传与该笔收/付款交易关联的原始凭证，如银行回单、收付款截图、证明文件、清单文件等!');
                }
            // }

            if(money_status==4 && bill_status==1){
                //选择早前凭证
                if($('#voucher_id').isEmpty()){
                    alert('请选择“已开票，未付款”列表中的凭证！');return;
                }
            }

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/register');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'pay_reg','add_reg':1,'voucher_id':voucher_id,'voucher_type':voucher_type,'voucher_type1':voucher_type1,'voucher_type1_2':voucher_type1_2,'voucher_type2':voucher_type2,'voucher_type2_1':voucher_type2_1,'voucher_type3':voucher_type3,'voucher_type3_1':voucher_type3_1,'voucher_type4_1':voucher_type4_1,'voucher_type4_2':voucher_type4_2,'voucher_type3_2':voucher_type3_2,'voucher_type5':voucher_type5,'voucher_type5_1':voucher_type5_1,'voucher_type6':voucher_type6,'voucher_type2_2':voucher_type2_2,'voucher':voucher,'voucher_date':voucher_date,'content':content,'tax_classify_sel':tax_classify_sel,'tax_classify_inp':tax_classify_inp,'currency':currency,'currency2':currency2,'money_status':money_status,'bill_status':bill_status,'trade_price':trade_price,'trade_price2':trade_price2,'trade_rate':trade_rate,'collect_type':collect_type,'transfer_collect_type':transfer_collect_type,'collect_account':collect_account,'submit_method':submit_method,'express_voucher':express_voucher,'diy_remark':diy_remark},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result.msg);
                    }else{
                        alert(json.result.msg);
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });
    })
    
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