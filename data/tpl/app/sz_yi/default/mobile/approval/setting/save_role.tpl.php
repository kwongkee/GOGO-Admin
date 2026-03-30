<?php defined('IN_IA') or exit('Access Denied');?><script src="../addons/sz_yi/static/warehouse/static_file/xm-select.js"></script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>

<style>
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
</style>
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-1);">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            <?php  echo $title;?>
        </div>
        <div class="layui-card-body" style="padding: 0px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="id" value="<?php  echo $data['id'];?>" style="display: none;">
                <input type="number" name="edit" value="1" style="display: none;">

                <div class="layui-form-item">
                    <label class="layui-form-label">角色名称</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="name" lay-verify="required" placeholder="请输入角色名称" autocomplete="off" class="layui-input" value="<?php  echo $info['name'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">关联父系</label>
                    <div class="layui-input-block layui-select-fscon">
                        <select name="pid" >
                            <option value="">请关联父系</option>
                            <?php  if(is_array($p_role)) { foreach($p_role as $k => $v) { ?>
                            <option value="<?php  echo $v['id'];?>" <?php  if($info['pid']==$v['id']) { ?>selected<?php  } ?>><?php  echo $v['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">功能列表</label>
                    <div class="layui-col-xs12" style="padding: 0 15px;box-sizing: border-box;">
                        <table class="layui-table">
                            <thead>
                                <tr>
                                    <th>名称</th>
                                    <th>权限</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php  if(is_array($app)) { foreach($app as $k => $v) { ?>
                                <tr>
                                    <td>
                                        <?php  echo $v['name'];?>
                                        <input type="text" name="authId[]" value="<?php  echo $v['id'];?>" style="display:none;">
                                    </td>
                                    <td>
                                        <select name="authType[]" lay-verify="required">
                                            <option value="">请选择功能权限</option>
                                            <option value="1" <?php  if($info['authList'][$k]['authType']==1) { ?>selected<?php  } ?>>可读可写</option>
                                            <option value="2" <?php  if($info['authList'][$k]['authType']==2) { ?>selected<?php  } ?>>可读禁写</option>
                                            <option value="3" <?php  if($info['authList'][$k]['authType']==3) { ?>selected<?php  } ?>>禁读禁写</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php  } } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo1">立即提交</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    layui.use(['layer', 'form', 'table', 'upload', 'laydate','jquery'], function () {
        var layer = layui.layer
            , form = layui.form
            , element = layui.element
            , laydate = layui.laydate
            , upload = layui.upload
            , table = layui.table
            , $ = layui.jquery;

        form.render(null, 'component-form-demo1');

        // 设备列表
        // var authList = xmSelect.render({
        //     el: '#authList',
        //     paging: true,
        //     pageSize: 5,
        //     filterable: true,
        //     prop: {
        //         name: 'name',
        //         value: 'id',
        //     },
        //     autoRow: true,
        //     data: $app,
        //     name: 'authList',
        //     layVerify: 'required',
        //     layVerType: 'msg',
        //     initValue: [$info['authList']]
        // });

        /* 监听提交 */
        form.on('submit(component-form-demo1)', function(data){
            $.ajax({
                url:"./index.php?i=3&c=entry&do=approval&p=index&m=sz_yi&op=save_role",
                method:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.msg,{time:3000}, function () {
                        if(res.code == 0)
                        {
                            window.history.back(-1);
                        }
                    });
                },
                error:function (data) {
                    layer.msg('系统错误',{time:3000});
                }
            });
            return false;
        });
    });
</script>