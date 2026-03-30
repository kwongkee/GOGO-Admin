<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/link', TEMPLATE_INCLUDEPATH)) : (include template('web/link', TEMPLATE_INCLUDEPATH));?>
<style>
    .layui-form select{
        display: block;
        width: 80px;
        height:30px;
        border-radius: 3px;
    }
    hr{
    	margin: 41px 0 0 0;
    }
    .pagination{
        margin: 10px 0px;display: inline-block;
        padding-left: 0px;
        border-radius: 4px;
    }

    .pagination>li:first-child>a,.pagination>li:first-child>span
    {
        margin-left: 0px;border-top-left-radius: 4px;border-bottom-left-radius: 4px;
    }
    .pagination>li>a,.pagination>li>span
    {
        background-color: #FFF;border: 1px solid #DDD;color: inherit;float: left;line-height: 1.42857;
        margin-left: 1px;padding: 4px 10px;position: relative;text-decoration: none;
    }
    .pagination>.active>a,.pagination>.active>span,.pagination>.active>a:hover,.pagination>.active>span:hover,.pagination>.active>a:focus,.pagination>.active>span:focus
    {
        background-color: #1ab394;border-color: #1ab394;color: #fff;cursor: default;z-index: 2;
    }
    .pagination>li{display: inherit;text-align: -webkit-match-parent;float: left;width: auto;text-align: center;font-size: 14px;}
</style>
<!--<div class="layui-body">-->
<div style="padding: 15px;">
    <div class="laytitle"><span>注册审批</span></div>
    <hr class="layui-bg-blue">
    <div class="layui-form">
        <table class="layui-table">
            <!--<colgroup>-->
                <!--<col width="150">-->
                <!--<col width="150">-->
                <!--<col width="150">-->
                <!--<col width="150">-->
                <!--<col width="150">-->
                <!--<col width="150">-->
                <!--<col width="200">-->
                <!--<col>-->
            <!--</colgroup>-->
            <thead>
            <tr>
                <th>商户类别</th>
                <th>手机号码</th>
                <th>邮箱</th>
                <th>商户名称</th>
                <th>状态</th>
                <th>注册时间</th>
                <th>商户权限(用于操作应用)</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($users)) { foreach($users as $index => $item) { ?>
            <tr>
                <td><?php  echo $cusTypes[$item['cusTypes']];?><?php  echo $typeCom[$item['typeCom']];?></td>
                <td><?php  echo $item['phone'];?></td>
                <td><?php  echo $item['email'];?></td>
                <td><?php  echo $item['adminName'];?></td>
                <td><?php  echo $appStatus[$item['appStatus']];?></td>
                <td><?php  echo date('Y-m-d H:i',$item['c_time'])?></td>
                <td>
                    <select name="role" class="role" lay-verify="required">
                        <option value="" style="color: red;" selected>未分配权限</option>
                        <?php  if(is_array($permiss)) { foreach($permiss as $inx => $itm) { ?>
                            <option value="<?php  echo $itm['id'];?>" uids="<?php  echo $item['uid'];?>" <?php  if($item['roleid'] == $itm['id'] ) { ?> selected <?php  } ?>><?php  echo $itm['roleName'];?></option>
                        <?php  } } ?>
                    </select>
                </td>

                <td>
                    <div>
                        <button type="button" onclick="pezi(<?php  echo $item['uid'];?>,'<?php  echo $item["adminName"];?>');" class="layui-btn layui-btn-normal layui-btn-sm" style="margin-bottom: 0; float: left; margin-right: 10px;">配置公众号</button>

                        <?php  if($item['appStatus'] == 0 || $item['appStatus'] == 3 ) { ?>
                            <button type="button"  data-method="offset" data-type="auto" onclick="adpot(<?php  echo $item['uid'];?>,'yes');" class="layui-btn layui-btn-warm layui-btn-sm" >通过</button>
                        <?php  } ?>

                        <?php  if($item['appStatus'] == 1) { ?>
                            <button type="button" class="layui-btn layui-btn-danger layui-btn-sm" onclick="adpot(<?php  echo $item['uid'];?>,'no');" style="margin-bottom: 0; float: left; margin-right: 10px;">禁用</button>
                        <?php  } ?>
                    </div>
                </td>
            </tr>
            <?php  } } ?>

            </tbody>
        </table>

        <div  style="text-align: center;" id="pager">
            <?php  echo $pager;?>
        </div>

    </div>
</div>

<script>
    //     加载layui.js
    layui.use(['element','layer'],function(){
        var element = layui.element;
        $ = layui.jquery;
        layer = layui.layer;
    })

    // 通过，禁止按钮
    function adpot(id,type) {
        // 请求地址；
        var urls = "<?php  echo WebUrl('mer_chats',array('m' => 'mer_chats','do'=>'index', 'p'=>'adopt'))?>";
        $.ajax({
            url:urls,
            type:'POST',
            data:{
                uid:id,
                types:type
            },
            success:function(res) {
                var red = JSON.parse(res);
                if(red.code != 1) {
                    layer.msg(red.msg);
                    return false;
                }

                layer.msg(red.msg);
                setTimeout(function(){
                    layer.closeAll();
                    window.location.reload();
                },1200)
            }
        })
    }


    // 配置公众号
    function pezi(uid,uname) {
        layer.open({
            type: 2,
            title:'配置公众号',
            area:['450px','450px'],
            skin: 'layui-layer-lan',
            content: "<?php  echo WebUrl('mer_chats',array('m' => 'mer_chats','do'=>'index', 'p'=>'public'))?>&uid="+uid+'&uname='+uname //这里content是一个URL，如果你不想让iframe出现滚动条，你还可以content: ['http://sentsin.com', 'no']
        });
    }


    $('.role').change(function() {
        // 用户ID
        var uids = $(this).find('option:selected').attr('uids');
        // 权限ID
        var val  = $(this).val();

        if(uids== '' || val == '') {
            layer.msg('请选择操作权限！')
            return false;
        }

        var url  = "<?php  echo WebUrl('mer_chats',array('m'=>'mer_chats','do'=>'index','p'=>'chrole'))?>";
        $.ajax({
            url:url,
            type:'POST',
            data:{
                uid:uids,
                roleid:val,
            },
            success:function(res) {
                var red = JSON.parse(res);
                if(red.code !=1) {
                    layer.msg(red.msg);
                    return false;
                }
                layer.msg(red.msg)
            }
        })
    });


    // 更改商户权限
</script>
<!--</div>-->