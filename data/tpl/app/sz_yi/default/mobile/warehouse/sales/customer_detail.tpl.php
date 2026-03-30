<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<style>
    .banner img {
        width: 100%;
    }

    div {
        overflow: visible;
    }

    .info_box_top {
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }

    .info_box {
        margin-top: 10px;
        float: left;
        width: 100%;
    }

    .info_box .layui-col-xs12 {
        background: #fff;
    }

    .info_search_box .order_head {
        padding: 10px;
        box-sizing: border-box;
        background: #1790FF;
        color: #fff;
    }

    .info_search_box .disf {
        padding: 10px;
        box-sizing: border-box;
        color: #666;
    }

    .info_box .title {
        color: #666;
        padding: 10px 10px;
        box-sizing: border-box;
    }

    .info_box .val {
        padding: 10px 10px;
        box-sizing: border-box;
    }

    .info_box .line {
        width: 100%;
        height: 1px;
        background: #eee;
    }

    .line {
        width: 100%;
        height: 1px;
        background: #eee;
    }

    .info_box .info_box_bottom {
        border-top: 1px solid #eee;
        padding: 10px;
        box-sizing: border-box;
    }

    .info_box .info_box_bottom .btn_desc {
        color: #999;
    }

    .info_box .info_box_bottom .layui-col-xs4 {
        text-align: right;
    }

    .info_box .title_bar {
        text-align: center;
        padding: 10px 0;
        box-sizing: border-box;
        border-bottom: 1px solid #eee;
        color: #fff;
        background: #1790FF;
    }

    /**layui框架**/
    .layui-icon-ok:before {
        content: "√"
    }

    .layui-form-checkbox {
        width: 18px;
        height: 18px;
        line-height: 18px;
        padding-right: 18px;
        margin-right: 2px;
    }

    .layui-form-checkbox i {
        width: 18px;
        height: 18px;
        border-left: 1px solid #d2d2d2;
    }

    .layui-btn-normal {
        background: #1790FF;
    }

    .layui-layer-hui .layui-layer-content {
        color: #fff;
    }

    .g_info{border-bottom:2px solid #1790FF;}
    .g_info:last-child{border-bottom:0;}
    .layui-tab{margin-bottom:0;}
    .layui-tab-brief>.layui-tab-title .layui-this{color:#1790FF;}
    .layui-tab-brief>.layui-tab-title .layui-this:after{border-bottom:2px solid #1790FF;}
    .layui-tab-title{background:#fff;}

    .layui-table,.layui-table thead th{text-align: center;}
    .layui-table .spin_input{width:33.33%;}
</style>

<div class="layui-col-xs12 inspection_box">
    <form class="layui-form" lay-filter="component-form-element">
        <div class="upload_info info_box">
            <input type="text" name="id" value="<?php  echo $user['id'];?>" style="display: none;">
            <div class="layui-col-xs12 g_info">
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">客户昵称</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $user['name'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">真实名称</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $user['realname'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">客户邮箱</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $user['email'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">联系电话</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $user['mobile'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">余额</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $user['balance'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">积分</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $user['points'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">注册时间</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo date('Y-m-d H:i',$user['createtime']);?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<script>
    //一句话即可完成js工作
    hui.numberBox();

    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'element'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , upload = layui.upload
                , table = layui.table;

            form.render(null,'component-form-element');

            form.on('submit(component-form-element1)',function(data){
                data.field['pa']=1;
                $.ajax({
                    url: "./index.php?i=3&c=entry&p=sales&do=warehouse&m=sz_yi&op=goods_detail",
                    method: 'post',
                    data: data.field,
                    dataType: 'JSON',
                    success: function (res) {
                        layer.msg(res.result.msg, {time: 3000}, function () {
                            if (res.status == 0) {
                                parent.location.reload();
                                // window.location.href = "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=warehouse_goods_up_down"
                            }
                        });
                    },
                    error: function (data) {
                        layer.msg("系统错误", {time: 3000});
                    }
                });
                return false;
            });
        })
    })
</script>