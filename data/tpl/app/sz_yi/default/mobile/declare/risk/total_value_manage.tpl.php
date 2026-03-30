<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}
    .laytable-cell-1-0-2{height:auto;min-height:auto;}

    .layui-form-radio i {top: 0;width: 16px;height: 16px;line-height: 16px;border: 1px solid #d2d2d2;font-size: 12px;border-radius: 2px;background-color: #fff;color: #fff !important;}
    .layui-form-radioed i {position: relative;width: 18px;height: 18px;border-style: solid;background-color: #5FB878;color: #5FB878 !important;}
    /* 使用伪类画选中的对号 */
    .layui-form-radioed i::after, .layui-form-radioed i::before {content: "";position: absolute;top: 8px;left: 5px;display: block;width: 12px;height: 2px;border-radius: 4px;background-color: #fff;-webkit-transform: rotate(-45deg);transform: rotate(-45deg);}
    .layui-form-radioed i::before {position: absolute;top: 10px;left: 2px;width: 7px;transform: rotate(-135deg);}

    .disf{display:flex !important;align-items: center;justify-content: flex-start;}
    .layui-input{width:150px;}
    .upOrDown{display:none;width:25px;margin-left:5px;}
    .rang input.layui-input.layui-unselect {width:36px;padding-right:0;font-size:20px;}
    .rang .layui-select-title .layui-edge{display: none;}
    .adj{margin-left:10px !important;}
    .adj input.layui-input, .adj .layui-unselect {width:90px;}
    .layui-input-block{margin-left:90px;}
    .amp1,.amp2{width:50px;}
    /*.ratio .layui-select-title .layui-input,.money .layui-select-title .layui-input{font-size:20px;}*/
    /**进度条**/
    .layui-progress{display:none;position: fixed;background: #ffffff;border-radius: 5px;z-index:9999999;transform:translate(-50%,-50%);top: 50%;left:50%;width:80%;}
</style>
<div class="mask"  style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="layui-progress" lay-showpercent="true" lay-filter="demo" >
    <p style="color:#fff;text-align:center;margin-top:10px;">系统执行中，请稍后...</p>
    <div class="layui-progress-bar layui-bg-red" lay-percent="0%"></div>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body" style="padding:20px 0 0;">
                    <form class="layui-form" action="" method="post" lay-filter="component-form-element">
                        <!--<div class="layui-card-body">-->
                            <div class="layui-form-item">
                                <div class="layui-inline" style="margin-bottom:5px;width:100%;">
                                    <label class="layui-form-label" style="padding:9px;width:60px;">获取数据</label>
                                    <div class="layui-input-block disf">
                                        <select name="adjust_method" id="adjust_method" lay-verify="required" lay-filter="adjust_method">
                                            <?php  if(is_array($method)) { foreach($method as $key => $val) { ?>
                                            <option value="<?php  echo $val;?>"><?php  echo $val;?></option>
                                            <?php  } } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="layui-inline" style="margin-bottom:5px;width:100%;">
                                    <label class="layui-form-label" style="padding:9px;width:60px;">清单总值</label>
                                    <div class="layui-input-block disf">
                                        <?php  echo $currency;?>&nbsp;<span class="money_txt"><?php  echo $totalMoney;?></span>元
                                    </div>
                                </div>
                                <div class="layui-inline" style="margin-bottom:5px;width:100%;">
                                    <label class="layui-form-label" style="padding:9px;width:60px;">拟调总值</label>
                                    <div class="layui-input-block disf">
                                        <?php  echo $currency;?>&nbsp;<input type="number" lay-verify="required" class="layui-input" name="diy_price" placeholder="自定义金额">
                                    </div>
                                </div>
                                <div class="layui-inline" style="margin-bottom:5px;">
                                    <label class="layui-form-label" style="padding:9px;width:60px;">调整幅度</label>
                                    <div class="disf" style="align-items: center;width:60%;">
                                        <img src="" class="upOrDown" alt="">
                                        <div class="layui-input-inline adj" style="margin-bottom:0;margin-left:0;margin-right:0;">
                                            <select name="amp" id="amp" lay-verify="required" lay-filter="amp">
                                                <option value="1" selected>按比率</option>
                                                <option value="2">按金额</option>
                                            </select>
                                            <!--<input type="radio" lay-verify="required" lay-filter="amp" name="amp" value="1" title="按比率">-->
                                            <!--<input type="radio" lay-verify="required" lay-filter="amp" name="amp" value="2" title="按金额">-->
                                        </div>&nbsp;
                                        <!--<div class="layui-input-inline" style="display:block;">-->
                                            <div class="ratio rang" style="display:block;">
                                                <!--<label class="layui-form-label" style="padding:9px;width:60px;"></label>-->
                                                <div class="layui-input-inline disf" style="margin-left:0;margin-bottom:0;">
                                                    <select name="prop" id="prop" lay-filter="prop">

                                                    </select>
                                                    &nbsp;
                                                    <input class="layui-input amp1" type="number" name="amp1" value="">&nbsp;%
                                                </div>
                                            </div>
                                        <!--</div>-->
                                        <!--<div class="layui-input-inline money rang" style="display:none;">-->
                                            <div class="money rang" style="display:none;">
                                                <!--<label class="layui-form-label" style="padding:9px;width:60px;"></label>-->
                                                <div class="layui-input-inline disf" style="margin-left:0;margin-bottom:0;">
                                                    <select name="prop" id="prop" lay-filter="prop">

                                                    </select>&nbsp;
                                                    <div><?php  echo $currency;?></div>&nbsp;<input class="layui-input amp2" type="number" name="amp2" value="">&nbsp;元
                                                </div>
                                            </div>
                                        <!--</div>-->
                                    </div>
                                </div>
                            </div>

                            <div class="layui-layout-admin">
                                <div class="layui-input-block" style="height: 36px;margin-left:0;">
                                    <div class="layui-footer" style="position:absolute;background:#fff;left: 0;">
                                        <button class="layui-btn layui-btn-normal btn1" type="button" lay-submit lay-filter="component-form-element">立即提交</button>
                                        <button class="layui-btn layui-btn-normal btn2" type="button" style="margin-left:0;background:#8c8a8a;color:#fff;display:none;">立即提交</button>
                                    </div>
                                </div>
                            </div>
                        <!--</div>-->
                    </form>
                </div>

                <div class="layui-card-body" style="padding:0;">
                    <table class="layui-hide" id="mainTable"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    window.canSure=0;

    //显示进度条
    function load(){
        var n = 0;
        timer = setInterval(function(){//按照时间随机生成一个小于95的进度，具体数值可以自己调整
            n = n + Math.random()*10|0;
            if(n>95){
                n = 95;
                clearInterval(timer);
            }
            $('.mask').show();
            $('.layui-progress').show();
            $('.layui-progress').find('.layui-progress-bar').css('width',n+'%').text(n+'%');
        }, 50+Math.random()*1000);

        return timer;
    }

    //隐藏进度条
    function hide_load(timer){
        clearInterval(timer);
        $('.layui-progress').find('.layui-progress-bar').css('width','100%').text('100%');
        setTimeout(function(){
            $('.mask').hide();
            $('.layui-progress').find('.layui-progress-bar').css('width','0%').text('0%');
            $('.layui-progress').hide();
        },500);
    }

    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload
                , table = layui.table;
            var timer;//定义一个计时器

            form.render(null, 'component-form-element');

            form.on('select(prop)',function(data){
               let diy_price = $('input[name="diy_price"]').val();
               let totalMoney = parseFloat("<?php  echo $totalMoney;?>");
               let amp = $('select[name="amp"] option:selected').val();
               var className = '';
               if(amp==1){
                   className='ratio';
               }else{
                   className='money';
               }
               let val = data.value;
               if(diy_price>totalMoney){
                   if(val=='-'){
                       $('.'+className).find('select[name="prop"]').find('option[value="+"]').attr('selected','selected');
                       var select = 'dd[lay-value="+"]';
                       $('.'+className).find('select[name="prop"]').siblings("div.layui-form-select").find('dl').find(select).click();
                       form.render(null, 'component-form-element');
                       layer.msg('拟调总值大于清单总值，无法调降！');
                       return false;
                   }
               }
               else if(diy_price<totalMoney){
                   if(val=='+'){
                       $('.'+className).find('select[name="prop"]').find('option[value="-"]').attr('selected','selected');
                       var select = 'dd[lay-value="-"]';
                       $('.'+className).find('select[name="prop"]').siblings("div.layui-form-select").find('dl').find(select).click();
                       form.render(null, 'component-form-element');
                       layer.msg('拟调总值小于清单总值，无法调升！');
                       return false;
                   }
               }
            });

            form.on('select(adjust_method)',function(data){
                let val = data.value;
                //根据清单数据进行调值
                let dataList = {'op':'getTotalMoney','batch_num':"<?php  echo $batch_num;?>",'method':val};
                $.post("<?php  echo $this->createMobileUrl('declare/risk');?>",dataList,function(res){
                    res = JSON.parse(res);
                    if(res.status==1){
                        if(res.result.totalMoney==0 || res.result.totalMoney=='0.00'){
                            layer.alert('温馨提示：清单总值暂时为0，请先补缺后再调整总值。');
                            $('.btn1').hide();
                            $('.btn2').show();
                        }else{
                            $('.btn1').show();
                            $('.btn2').hide();
                        }
                        $('.money_txt').text(res.result.totalMoney);
                    }
                });
            });

            //监控拟调总值
            $('input[name="diy_price"]').bind('input propertychange',function(){
                let val = $(this).val();

                //判断是否已选择调整幅度
                let amp = $('select[name="amp"] option:selected').val();
                let totalMoney = parseFloat("<?php  echo $totalMoney;?>");
                if(typeof(amp)!='undefined' || amp!='undefined'){
                    if(amp==1){
                        //按比率
                        if(val==totalMoney){
                            layer.msg('请重新填写自定义金额');return false;
                        }else{
                            let prop = (val/totalMoney).toFixed(2);
                            $('input[name="amp1"]').val(prop);
                        }
                    }else{
                        //按金额
                        if(val==totalMoney){
                            layer.msg('请重新填写自定义金额');return false;
                        }else{
                            // let prop = (val/totalMoney).toFixed(2);
                            // $('input[name="amp2"]').val(prop);
                        }
                    }
                }
                    // <option value="+">+</option>
                    // <option value="-">-</option>
                if(val>totalMoney){
                    if(amp==1){
                        $('.upOrDown').attr('src','https://shop.gogo198.cn/attachment/images/up.png?v=2312').show();
                        // var select = 'dd[lay-value="+"]';
                        // $('.ratio').find('select[name="prop"]').siblings("div.layui-form-select").find('dl').find(select).click();
                        // find('option[value="+"]').attr('selected','selected')
                        $('.ratio').find('select[name="prop"]').empty().append('<option value="+" selected>+</option>');
                    }else if(amp==2){
                        $('.upOrDown').attr('src','https://shop.gogo198.cn/attachment/images/up.png?v=2312').show();

                        $('.money').find('select[name="prop"]').empty().append('<option value="+" selected>+</option>');
                    }
                }else if(totalMoney>val){
                    if(amp==1){
                        $('.upOrDown').attr('src','https://shop.gogo198.cn/attachment/images/down.png?v=2312').show();

                        $('.ratio').find('select[name="prop"]').empty().append('<option value="-" selected>-</option>');
                    }else if(amp==2){
                        $('.upOrDown').attr('src','https://shop.gogo198.cn/attachment/images/down.png?v=2312').show();

                        $('.money').find('select[name="prop"]').empty().append('<option value="-" selected>-</option>');
                    }
                }
                form.render(null, 'component-form-element');
            });

            form.on('select(amp)',function(data){
                let val = data.value;

                let diy_price = $('input[name="diy_price"]').val();
                if(diy_price=='' || diy_price=='undefined' || typeof(diy_price)=='undefined'){
                    $(this).attr('selected',false);
                    form.render(null, 'component-form-element');
                    layer.msg('请先填写拟调总值');return false;
                }
                diy_price = parseFloat(diy_price);
                let totalMoney = parseFloat("<?php  echo $totalMoney;?>");

                if(val==1){
                    //按比率
                    $('.ratio').show();
                    $('.money').hide();
                    if(val==totalMoney){
                        layer.msg('请重新填写自定义金额');return false;
                    }else{
                        let prop = (diy_price/totalMoney).toFixed(2);
                        $('input[name="amp1"]').val(prop);
                    }
                }else if(val==2){
                    //按金额
                    $('.ratio').hide();
                    $('.money').show();

                    if(val==totalMoney){
                        layer.msg('请重新填写自定义金额');return false;
                    }else{
                        // let prop = (diy_price/totalMoney).toFixed(2);
                        // $('input[name="amp2"]').val(prop);
                    }
                }

                if(diy_price>totalMoney){
                    if(val==1){
                        $('.upOrDown').attr('src','https://shop.gogo198.cn/attachment/images/up.png?v=2312').show();
                        // $('.ratio').find('select[name="prop"]').find('option[value="+"]').attr('selected','selected');
                        $('.ratio').find('select[name="prop"]').empty().append('<option value="＋" selected>+</option>');
                    }else if(val==2){
                        $('.upOrDown').attr('src','https://shop.gogo198.cn/attachment/images/up.png?v=2312').show();
                        // $('.money').find('select[name="prop"]').find('option[value="+"]').attr('selected','selected');
                        $('.money').find('select[name="prop"]').empty().append('<option value="＋" selected>+</option>');
                    }
                }else if(totalMoney>diy_price){
                    if(val==1){
                        $('.upOrDown').attr('src','https://shop.gogo198.cn/attachment/images/down.png?v=2312').show();
                        // $('.ratio').find('select[name="prop"]').find('option[value="-"]').attr('selected','selected');
                        $('.ratio').find('select[name="prop"]').empty().append('<option value="－" selected>-</option>');
                    }else if(val==2){
                        $('.upOrDown').attr('src','https://shop.gogo198.cn/attachment/images/down.png?v=2312').show();
                        // $('.money').find('select[name="prop"]').find('option[value="-"]').attr('selected','selected');
                        $('.money').find('select[name="prop"]').empty().append('<option value="－" selected>-</option>');
                    }
                }
                form.render(null, 'component-form-element');
            });

            //总值调控
            form.on('submit(component-form-element)', function(data){
                if(data['field'].amp==1){
                    if(data['field'].amp1==''){
                        layer.msg('请填写比率');
                        return false;
                    }
                }else{
                    if(data['field'].amp2==''){
                        layer.msg('请填写金额');
                        return false;
                    }
                }
                // layer.load(); //上传loading
                timer = load();
                $.ajax({
                    url: "<?php  echo $this->createMobileUrl('declare/risk');?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {'op': 'adjust_range', 'data': data['field'],'batch_num':"<?php  echo $batch_num;?>"},
                    success:function (json) {
                        hide_load(timer);

                        // layer.closeAll('loading'); //关闭loading
                        layer.alert(json.result.msg);

                        if (json.status == -1)
                        {
                            window.canSure=0;
                        }else if(json.status==1){
                            //总值调整完成
                            window.canSure=1;
                            // window.location.reload();
                        }
                    },
                    error:function(){
                        $('.mask').hide();
                        $('.layui-progress').hide();
                        $('.layui-progress').find('.layui-progress-bar').css('width','0%').text('0%');
                        // layer.closeAll();
                    }
                });
            });

            table.render({
                elem: '#mainTable'
                ,url: "<?php  echo $this->createMobileUrl('declare/risk');?>&op=total_value_manage&pa=1&batch_num=<?php  echo $batch_num;?>"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'logisticsNo', title: '运单编号'}
                    ,{align:'center', width:100, title: '订单概览', templet: function(d){
                            return [
                                '<a onclick="openWindow('+ "'"+d.logisticsNo+"订单概览'" +',' + "'"+ d.logisticsNo +"'" +','+"'"+d.pre_batch_num+"'"+ ','+1+');" class="layui-btn layui-btn-normal layui-btn-xs">订单概览</a>',
                            ].join('');
                        }}
                    ,{align:'center', width:100,  title: '分类清单', templet: function(d){
                            return [
                                '<a onclick="openWindow('+ "'"+d.logisticsNo+"分类清单'" +',' + "'"+ d.logisticsNo +"'" +','+"'"+d.pre_batch_num+"'"+ ','+2+');" class="layui-btn layui-btn-normal layui-btn-xs">分类清单</a>',
                            ].join('');
                        }}
                    ,{align:'center', width:100, title: '清单操作', fixed:'right', templet: function(d){
                            return [
                                // '<a onclick="openWindow('+ "'"+d.logisticsNo+"总值再调'" +',' + "'"+ d.logisticsNo +"'" +','+"'"+d.pre_batch_num+"'"+ ','+3+');" class="layui-btn layui-btn-normal layui-btn-xs">总值再调</a>',
                                '<a  class="layui-btn layui-btn-xs layui-btn-normal" onclick="openWindow('+"'"+ d.logisticsNo +"清单确认'"+','+"'"+ d.logisticsNo2 +"'" +','+"'"+d.pre_batch_num+"'"+','+4+');">清单确认</a>',
                            ].join('');
                        }}
                ]]
                ,page: false
                ,done : function(res, curr, count) {
                    // $('.layui-table').rowspan(3);
                }
            });

            var $ = layui.$, active = {
                reload: function(){
                    //执行重载
                    table.reload('mainTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        ,where: {
                            keywords: $("#keywords").val()
                        }
                    });
                }
            };
        });
    });

    function openWindow(title,logisticsNo,batch_num,typ)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //订单概览
            url="<?php  echo $this->createMobileUrl('declare/risk');?>&op=order_list&logisticsNo="+logisticsNo+"&batch_num="+batch_num+"&typ=2";
        }else if(typ==2){
            //分类清单
            url="<?php  echo $this->createMobileUrl('declare/risk');?>&op=category_list&logisticsNo="+logisticsNo+"&batch_num="+batch_num;
        }else if(typ==3){
            //总值再调
            url="<?php  echo $this->createMobileUrl('declare/risk');?>&op=total_value_manage&batch_num="+batch_num;
        } else if(typ==4){
            //清单确认
            if(window.canSure==0){
                $.ajax({
                    url: "<?php  echo $this->createMobileUrl('declare/risk');?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {'op': 'adjust_sured', 'batch_num': "<?php  echo $batch_num;?>"},
                    success: function (json) {
                        if(json.result.data==1){
                            //已清单确认
                            list_queren(logisticsNo,batch_num);
                        }else{
                            layer.msg('请先调整总值或重新调整总值后再确认！');
                            return false;
                        }
                    }
                });
            }else if(window.canSure==1){
                //点击清单确认
                list_queren(logisticsNo,batch_num);
            }
            return false;
        }
        var index = layer.open({
            type: 2,
            title: title,
            content: url
        });
        layer.full(index);
    }

    //点击清单确认
    function list_queren(logisticsNo,batch_num){
        var timer = load();
        $.ajax({
            url: "<?php  echo $this->createMobileUrl('declare/risk');?>",
            type: 'POST',
            dataType: 'json',
            data: {'op': 'adjust_sure', 'batch_num': "<?php  echo $batch_num;?>"},
            success: function (json) {
                if(json.status==1){
                    hide_load(timer);
                }
                layer.confirm('确认已完成，请选择清单确认后操作',{
                    btn:['预先申报','导出清单']
                }, function(index,layero){
                    //按钮【按钮一】的回调
                    $.ajax({
                        url: "<?php  echo $this->createMobileUrl('declare/risk');?>",
                        type: 'POST',
                        dataType: 'json',
                        data: {'op': 'pre_declare', 'batch_num': "<?php  echo $batch_num;?>"},
                        success: function (json) {
                            if(json.status==1){
                                // parent.parent.location.href="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=display";
                                //跳去申报风控
                                parent.parent.location.href="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=declare_risk";
                            }
                        }
                    });
                    return false;
                }, function(index){
                    //导出清单
                    window.location.href="<?php  echo $this->createMobileUrl('declare/risk');?>&op=export_list&logisticsNo="+logisticsNo+"&batch_num="+batch_num;
                });
            }
        });
    }

    jQuery.fn.rowspan = function (colIdx) {
        return this.each(function () {
            var that;
            $('tr', this).each(function (row) {
                $('td:eq(' + colIdx + ')', this).filter(':visible').each(function (col) {
                    console.log($(this).html());
                    // if (that != null && $(this).html() == $(that).html()) {

                        var rowspan = $(that).attr("rowspan");
                        if (rowspan == undefined) {
                            $(that).attr("rowspan", 1);
                            rowspan = $(that).attr("rowspan");
                        }
                        rowspan = Number(rowspan) + 1;
                        $(that).attr("rowspan", rowspan);
                        $(this).hide();
                    // } else {
                    //     that = this;
                    // }
                });
            });
        });
    };

</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>