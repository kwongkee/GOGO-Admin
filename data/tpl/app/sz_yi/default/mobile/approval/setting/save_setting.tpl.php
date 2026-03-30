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
                    <label class="layui-form-label">事项名称</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="name" lay-verify="required" readonly autocomplete="off" class="layui-input" value="<?php  echo $info['name'];?>">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">发起角色</label>
                    <div class="layui-input-block layui-select-fscon">
                        <select name="role_id" id="role_id" lay-verify="required" lay-search>
                            <option value="">请选择可发起角色</option>
                            <?php  if(is_array($role)) { foreach($role as $k => $v) { ?>
                            <option value="<?php  echo $v['id'];?>" <?php  if($info['role_id']==$v['id']) { ?>selected<?php  } ?>><?php  echo $v['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">审批方式</label>
                    <div class="layui-input-block layui-select-fscon">
                        <select name="approval_type" id="approval_type" lay-verify="required" lay-search>
                            <option value="">请选择审批方式</option>
                            <option value="1" <?php  if($info['approval_type']==1) { ?>selected<?php  } ?>>串联审批</option>
                            <option value="2" <?php  if($info['approval_type']==2) { ?>selected<?php  } ?>>并联审批</option>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">审批角色</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div id="role_ids" class="xm-select-demo"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">抄送角色</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div id="copyInfoFor_roleIds" class="xm-select-demo"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">事项详情</label>
                    <div class="layui-input-block layui-select-fscon">
<!--                        <div class="layui-col-xs12">-->
<!--                            <div class="layui-col-xs4">-->
<!--                                <input type="text" class="layui-input" name="label_name" placeholder="事项名称">-->
<!--                            </div>-->
<!--                            <div class="layui-col-xs5">-->
<!--                                <input type="text" class="layui-input" name="label_val" placeholder="事项详情">-->
<!--                            </div>-->
<!--                            <div class="layui-col-xs3">-->
<!--                                <div class="layui-btn" onclick="add_diyForm()">添加</div>-->
<!--                            </div>-->
<!--                        </div>-->
                        <select name="diyformid" id="diyformid" lay-verify="required" lay-filter="diyformid" lay-search>
                            <option value="">请选择预设表单</option>
                            <?php  if(is_array($diyForm)) { foreach($diyForm as $k => $v) { ?>
                                <option value="<?php  echo $v['id'];?>" <?php  if($info['diyformid']==$v['id']) { ?>selected<?php  } ?>><?php  echo $v['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>
<!--                <table class="layui-table" style="display:none;">-->
<!--                    <thead>-->
<!--                        <tr>-->
<!--                            <th>事项名称</th>-->
<!--                            <th>事项详情</th>-->
<!--                            <th>操作</th>-->
<!--                        </tr>-->
<!--                    </thead>-->
<!--                    <tbody>-->
<!--                    if !empty($info['diyFormData'])-->
<!--                        loop $info['diyFormData'] $k $v-->
<!--                            <tr>-->
<!--                                <td><input type="text" class="layui-input" name="form_name[]" placeholder="事项名称" value="$v['label_name']"></td>-->
<!--                                <td><input type="text" class="layui-input" name="form_val[]" placeholder="事项详情" value="$v['label_val']"></td>-->
<!--                                <td><a onclick="del_diyForm(this);" class="layui-btn layui-btn-normal layui-btn-xs">删除</a></td>-->
<!--                            </tr>-->
<!--                        /loop-->
<!--                    /if-->
<!--                    </tbody>-->
<!--                </table>-->
                    <div class="layui-form-item">
                        <label class="layui-form-label">数据关联</label>
                        <div class="layui-input-block layui-select-fscon">
                            <select name="authid" id="authid">
                                <option value="">请选择数据关联方式</option>
                                <option value="1" <?php  if($info['authid']==1) { ?>selected<?php  } ?>>应用</option>
                                <option value="2" <?php  if($info['authid']==2) { ?>selected<?php  } ?>>系统</option>
                                <option value="3" <?php  if($info['authid']==3) { ?>selected<?php  } ?>>接口</option>
                            </select>
                        </div>
                    </div>
<!--                <div class="layui-form-item">-->
<!--                    <label class="layui-form-label">应用列表</label>-->
<!--                    <div class="layui-input-block layui-select-fscon">-->
<!--                        <div id="event_ids" class="xm-select-demo"></div>-->
<!--                    </div>-->
<!--                </div>-->

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

        // 抄送角色
        var copyInfoFor_roleIds = xmSelect.render({
            el: '#copyInfoFor_roleIds',
            paging: true,
            pageSize: 5,
            filterable: true,
            prop: {
                name: 'name',
                value: 'id',
            },
            autoRow: true,
            data: <?php  echo $roles;?>,
            name: 'copyInfoFor_roleIds',
            layVerify: 'required',
            layVerType: 'msg',
            initValue: [<?php  echo $info['copyInfoFor_roleIds'];?>]
        });
        // 审批角色
        var role_ids = xmSelect.render({
            el: '#role_ids',
            paging: true,
            pageSize: 5,
            filterable: true,
            prop: {
                name: 'name',
                value: 'id',
            },
            autoRow: true,
            data: <?php  echo $roles;?>,
            name: 'role_ids',
            layVerify: 'required',
            layVerType: 'msg',
            initValue: [<?php  echo $info['role_ids'];?>]
        });

        // 应用列表
        // var authList = xmSelect.render({
        //     el: '#event_ids',
        //     paging: true,
        //     pageSize: 5,
        //     filterable: true,
        //     prop: {
        //         name: 'name',
        //         value: 'id',
        //     },
        //     autoRow: true,
        //     data: $app,
        //     name: 'event_ids',
        //     layVerify: 'required',
        //     layVerType: 'msg',
        //     initValue: [$info['authList']]
        // });

        /* 监听提交 */
        form.on('submit(component-form-demo1)', function(data){
            $.ajax({
                url:"./index.php?i=3&c=entry&do=approval&p=index&m=sz_yi&op=save_setting",
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

    function add_diyForm(){
        var layer = layui.layer
            , $ = layui.jquery;

        let label_name = $('input[name="label_name"]').val();
        // let label_val = $('input[name="label_val"]').val();
        // || label_val==''
        if(label_name==''){
            layer.msg('自主配置表单内容不能为空!');return false;
        }
        let html = '<tr>' +
                '<td><input type="text" class="layui-input" name="form_name[]" placeholder="事项名称" value="'+label_name+'"></td>'+
                '<td><input type="text" class="layui-input" name="form_val[]" placeholder="事项详情" value="'+label_val+'"></td>'+
                '<td><a onclick="del_diyForm(this);" class="layui-btn layui-btn-normal layui-btn-xs">删除</a></td>'+
            '</tr>';

        $('.layui-table tbody').append(html);
    }

    function del_diyForm(t){
        $(t).parent().parent().remove();
    }
</script>