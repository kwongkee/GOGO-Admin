<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template("parking/_header", TEMPLATE_INCLUDEPATH)) : (include template("parking/_header", TEMPLATE_INCLUDEPATH));?>
<fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
    <legend>企业信息填写</legend>
</fieldset>
<form class="layui-form layui-form-pane" action="">
    <div class="layui-form-item">
        <label class="layui-form-label">企业名称</label>
        <div class="layui-input-block">
            <input type="text" name="commpany_name" autocomplete="off" lay-verify="commpany_name" placeholder="请输入名称 必填" class="layui-input" value="<?php  echo $commpanyData['name'];?>">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">企业简称</label>
        <div class="layui-input-block">
            <input type="text" name="title" autocomplete="off" lay-verify="title" placeholder="请输入简称 必填" class="layui-input" value="<?php  echo $commpanyData['short_title'];?>">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">项目名称</label>
        <div class="layui-input-block">
            <input type="text" name="project_name" autocomplete="off" lay-verify="project_name" placeholder="请输入项目名 必填" class="layui-input" value="<?php  echo $commpanyData['project_name'];?>">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">公司座机号</label>
        <div class="layui-input-block">
            <input type="text" name="tel" autocomplete="off" lay-verify="tel" placeholder="请输入号码 必填" class="layui-input" value="<?php  echo $commpanyData['tel'];?>">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">车位数量</label>
        <div class="layui-input-block">
            <input type="number" name="num" autocomplete="off" placeholder="请输入 可选" class="layui-input" value="<?php  echo $commpanyData['num'];?>">
        </div>
    </div>
    <input type="hidden" name="id" value="<?php  echo $commpanyData['id'];?>">
    <div class="layui-form-item" style="margin-left:50%;">
        <button class="layui-btn" lay-submit="" lay-filter="demo2">提交</button>
    </div>
</form>
<script src="//res.layui.com/layui/dist/layui.js" charset="utf-8"></script>
<!-- 注意：如果你直接复制所有代码到本地，上述js路径需要改成你本地的 -->
<script>
    layui.use(['form', 'layedit', 'laydate'], function(){
        var form = layui.form
            ,layer = layui.layer;
        form.verify({
            title: function(value){
                if(value.length <= 0){
                    return '不能为空';
                }
            },
            tel:function (v) {
                if(v.length<=0){
                    return '不能为空';
                }
            },
            project_name:function (v) {
                if(v.length<=0){
                    return '不能为空';
                }
            },
            commpany_name:function (v) {
                if(v.length<=0){
                    return '不能为空';
                }
            }
        });
        //监听提交
        form.on('submit(demo2)', function(data){
            // var res=JSON.stringify(data.field);
            // console.dir(res);
            $.ajax({
                url:"<?php  echo webUrl('parking/commpany/saveCommpanyInformation')?>",
                type:"post",
                dataType:"text",
                data:data.field,
                success:function (e) {
                    console.log(e);
                    var e=JSON.parse(e);
                    if(e.status==1){
                        window.location.reload();
                    }else{
                        alert("填写失败");
                    }
                }
            });
            return false;
        });
    });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template("parking/_footer", TEMPLATE_INCLUDEPATH)) : (include template("parking/_footer", TEMPLATE_INCLUDEPATH));?>