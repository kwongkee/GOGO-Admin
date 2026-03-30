<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>新增口岸信息</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #1E9FFF;color:#fff; }
    .layui-card-body{padding:0;}
    .required{color:#ff2222;}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <form class="layui-form" lay-filter="component-form-element1">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">新增口岸信息</div>
                    <div class="layui-card-body">
                        <table class="layui-table">
                            <thead>
                                <tr>
                                    <th><span class="required">*</span>口岸代码</th>
                                    <th><span class="required">*</span>企业代码</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="area_code" lay-verify="required" class="layui-form-select" id="area_code" lay-search>
                                            <option value="">选择口岸代码</option>
                                            <?php  if(is_array($customs_codes)) { foreach($customs_codes as $val) { ?>
                                                <option value="<?php  echo $val['value_code'];?>"><?php  echo $val['AreaCode'];?></option>
                                            <?php  } } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" lay-verify="required" name="decl_code" id="decl_code" placeholder="企业代码" autocomplete="off" class="layui-input">
                                    </td>
                                </tr>
                            </tbody>
                            <thead>
                                <tr>
                                    <th><span class="required">*</span>企业名称</th>
                                    <th><span class="required">*</span>edi账号</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" lay-verify="required" name="decl_name" id="decl_name" placeholder="企业名称" autocomplete="off" class="layui-input">
                                    </td>
                                    <td>
                                        <input type="text" lay-verify="required" name="edi" id="edi" placeholder="edi账号" autocomplete="off" class="layui-input">
                                    </td>
                                </tr>
                            </tbody>
                            <thead>
                                <tr>
                                    <th><span class="required">*</span>数据交换账号</th>
                                    <th><span class="required">*</span>绑定卡号</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" lay-verify="required" name="dxp" id="dxp" placeholder="数据交换账号" autocomplete="off" class="layui-input">
                                    </td>
                                    <td>
                                        <input type="text" lay-verify="required" name="card_num" id="card_num" placeholder="绑定卡号" autocomplete="off" class="layui-input">
                                    </td>
                                </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <th><span class="required">*</span>httpkey</th>
                                <th>电子口岸备案号</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <input type="text" lay-verify="required" name="api_key" id="api_key" placeholder="httpkey" autocomplete="off" class="layui-input">
                                </td>
                                <td>
                                    <input type="text" name="electronic_port_code" id="electronic_port_code" placeholder="电子口岸备案号" autocomplete="off" class="layui-input">
                                </td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <th><span class="required">*</span>报文上传类型</th>
                                <th>vpn账号</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <select name="isFtp" id="isFtp">
                                        <option value="0">广州http</option>
                                        <option value="1">ftp</option>
                                        <option value="2">佛山http</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="vpn_account" id="vpn_account" placeholder="vpn账号" autocomplete="off" class="layui-input">
                                </td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <th>vpn密码</th>
                                <th>ftp账号</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <input type="text" name="vpn_passwd" id="vpn_passwd" placeholder="vpn密码" autocomplete="off" class="layui-input">
                                </td>
                                <td>
                                    <input type="text" name="ftp_account" id="ftp_account" placeholder="ftp账号" autocomplete="off" class="layui-input">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th>ftp密码</th>
                                <th>ftp地址</th>
                                <th>ftp端口</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <input type="text" name="ftp_password" id="ftp_password" placeholder="ftp密码" autocomplete="off" class="layui-input">
                                </td>
                                <td>
                                    <input type="text" name="ftp_host" id="ftp_host" placeholder="ftp地址" autocomplete="off" class="layui-input">
                                </td>
                                <td>
                                    <input type="text" name="ftp_port" id="ftp_port" placeholder="ftp端口" autocomplete="off" class="layui-input">
                                </td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <th colspan="2">私钥key文件</th>
                                <th colspan="2">公钥key文件</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="2">
                                    <input type="file" style="padding-top: 5px;" name="private_key_file" id="private_key" placeholder="私钥key文件" autocomplete="off" class="layui-input">
                                    <input type="hidden" name="private_key" id="ok_private_key">
                                </td>
                                <td colspan="2">
                                    <input type="file" style="padding-top: 5px;" name="public_key_file" id="public_key" placeholder="公钥key文件" autocomplete="off" class="layui-input">
                                    <input type="hidden" name="public_key" id="ok_public_key">
                                </td>
                            </tr>
                            </tbody>

                        </table>
                        <div class="layui-form-item" style="margin-top: 15px;text-align: center;">
                            <div>
                                <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    $(function(){
        layui.use(['index', 'form', 'table', 'upload', 'laydate'], function(){
            var  $ = layui.$
                ,layer = layui.layer
                , form = layui.form
                ,element = layui.element
                ,laydate = layui.laydate
                ,upload = layui.upload;

            form.render(null, 'component-form-element1');
            // element.render('breadcrumb', 'breadcrumb');

            upload.render({
                elem: '#private_key'
                ,url: "{{url('/electronport/upload')}}"
                ,accept: 'file'
                ,data: {
                    type: function(){
                        return 'private_key';
                    },
                    _token: function(){
                        return $("input[name='_token']").val();
                    },
                }
                ,done: function(res){
                    layer.msg(res.msg,{time:2000});
                    if(res.code == 0)
                    {
                        $("#ok_private_key").val(res.data);
                    }
                }
            });

            upload.render({
                elem: '#public_key'
                ,url: "{{url('/electronport/upload')}}"
                ,accept: 'file'
                ,data: {
                    type: function(){
                        return 'public_key';
                    },
                    _token: function(){
                        return $("input[name='_token']").val();
                    },
                }
                ,done: function(res){
                    layer.msg(res.msg,{time:2000});
                    if(res.code == 0)
                    {
                        $("#ok_public_key").val(res.data);
                    }
                }
            });

            form.on('submit(component-form-element)', function(data){
                //layer.msg(JSON.stringify(data.field));

                if( data.field['isFtp'] == '0' )
                {
                    if( data.field['private_key'] == '' || data.field['public_key'] == '' )
                    {
                        layer.msg('私钥key文件或公钥key文件不能为空',{time:2000});
                        return false;
                    }
                }

                $.ajax({
                    url:"{{url('/electronport/save')}}",
                    method:'POST',
                    data:data.field,
                    dataType:'JSON',
                    success:function(res){
                        layer.msg(res.msg,{time:2000}, function () {
                            if(res.code == 0)
                            {
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:2000});
                    }
                });
                return false;
            });
        });
    });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>