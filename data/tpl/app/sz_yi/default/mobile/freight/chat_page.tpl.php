<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>留言页</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-fluid{background:#f8f8f8;}
    .disf{display:flex;align-items:center;}
    .footer{position:fixed;bottom:0;left:0;width:100%;padding:10px 10px;box-sizing:border-box;border-top:1px solid #efefef;box-shadow:0px 0px 0px 2px #efefef;text-align:center;background:#fff;}
    
    /**留言区**/
    .chat_content{margin-bottom:10px;}
    .chat_content:last-child{margin-bottom:50px;}
    .chat_content .l1 .identify{font-size: 16px;font-weight: bold;}
    .chat_content .l2{font-size: 13px;margin-left: 10px;font-weight: bold;}
    .chat_content .reply{padding:5px 0 10px;box-sizing:border-box;}
    .chat_content{border-bottom:1px solid #a2a2a2;}
    .chat_content .content{border-bottom:1px solid #efefef;font-size:15px;color:#1f1f1f;padding:10px 5px;box-sizing:border-box;}
    .chat_content .content img{width:100%;}
    .chat_content .chat_content{margin-bottom:0;}
</style>
<!--百度富文本-->
<script type="text/javascript" src="../addons/sz_yi/static/ueditor/ueditor.config.js?v=<?php  echo time();?>"></script>
<script type="text/javascript" src="../addons/sz_yi/static//ueditor/ueditor.all.min.js"> </script>
<script type="text/javascript" src="../addons/sz_yi/static/ueditor/lang/zh-cn/zh-cn.js"></script>

<!--聊天-->
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-card">
            <div class="layui-card-header" style="font-weight:bold;font-size:16px;">留言区</div>
            <div class="layui-card-body" style="padding: 10px 0;">
                <?php  if(!empty($list)) { ?>
                    <?php  if(is_array($list)) { foreach($list as $v) { ?>
                    <div class="chat_content">
                        <div class="role disf" style="justify-content:space-between;">
                            <div class="l1">
                                <div class="identify"><?php  echo $v['identify_role'];?></div>
                            </div>
                            <div class="l2">
                                <?php  echo $v['createtime'];?>
                            </div>
                        </div>
                        <div class="content">
                            <?php  if($v['content_type']==1) { ?>
                                <?php  echo $v['content'];?>
                            <?php  } else if($v['content_type']==2) { ?>
                                <?php  echo html_entity_decode($v['content']);?>
                            <?php  } ?>
                        </div>
                        
                        <!--子评论-->
                        <?php  if(!empty($v['child_content'])) { ?>
                            <?php  if(is_array($v['child_content'])) { foreach($v['child_content'] as $vv) { ?>
                                <div class="chat_content" style="border-bottom:0;padding:10px;box-sizing:border-box;">
                                    <div class="role disf" style="justify-content:space-between;">
                                        <div class="l1">
                                            <div class="identify"><?php  echo $vv['identify_role'];?></div>
                                        </div>
                                        <div class="l2">
                                            <?php  echo $vv['createtime'];?>
                                        </div>
                                    </div>
                                    <div class="content">
                                        <?php  if($vv['content_type']==1) { ?>
                                            <?php  echo $vv['content'];?>
                                        <?php  } else if($vv['content_type']==2) { ?>
                                            <?php  echo html_entity_decode($vv['content']);?>
                                        <?php  } ?>
                                    </div>
                                </div>
                            <?php  } } ?>
                        <?php  } ?>
                        <div class="reply">
                            <div class="layui-btn layui-btn-md" onclick="show_chat(<?php  echo $v['id'];?>)" style="background:#ff2222;color:#fff;">回复</div>
                        </div>
                    </div>
                    <?php  } } ?>
                <?php  } else { ?>
                    <div style="text-align:center;">暂无留言</div>
                <?php  } ?>
                
                
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <div class="layui-btn layui-btn-md back">返回主页</div>
    <div class="layui-btn layui-btn-md layui-btn-warm start_chat">发表留言</div>
</div>

<div id="chat_box" style="display:none;">
    <form class="layui-form" action="" lay-filter="chat-demo">
        <input type="hidden" name="id" value="<?php  echo $id;?>">
        <input type="hidden" name="pid" value="0">
        <div class="layui-form-item">
        <div class="layui-tab layui-tab-card">
            <ul class="layui-tab-title">
                <li class="layui-this" data-i='1'>普通文本</li>
                <li data-i='2'>富文本</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <textarea class="layui-textarea" name="content" placeholder="开始聊天~~"></textarea>
                </div>
                <div class="layui-tab-item">
                    <script id="content" type="text/plain" style="width:100%;height:100px;">

                    </script>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <div class="layui-col-xs12" style="text-align: center;margin-top:10px;">
            <button class="layui-btn layui-btn-warm" lay-submit="" lay-filter="chat-demo1">立即提交</button>
        </div>
    </div>
    </form>
</div>

<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    layui.use(['layer','jquery','element','laydate','form'], function() {
        var layer = layui.layer
            , $ = layui.jquery
            , element = layui.element
            , laydate = layui.laydate
            , form = layui.form;
        
        var ue = UE.getEditor('content', {
            initialFrameHeight: 200,
            serverUrl: '../addons/sz_yi/static/ueditor/php/controller.php'
        });
        
        $('.back').click(function(){window.history.back(-1);});
        
        $('.start_chat').click(function(){
            $('input[name="pid"]').val('0');
            form.render(null,'chat-demo');
           layer.open({
               type:1,
               title:'发表留言',
               area:['90%','80%'],
               shadeClose:true,
               content:$('#chat_box')
           }); 
        });
        
        /* 监听提交 */
        form.on('submit(chat-demo1)', function (data) {
            data.field['content_type']=1;
            if($('.layui-tab-title').find('.layui-this').attr('data-i')==2){
                data.field['content_type']=2;
                data.field['content'] = data.field['editorValue'];
            }

            if(data.field['content']==''){
                layer.msg('请输入内容！');
                return false;
            }

            $.ajax({
                url: "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=freight&op=freight_chat&m=sz_yi&id=<?php  echo $id;?>",
                method: 'post',
                data: data.field,
                dataType: 'JSON',
                success: function (res) {
                    layer.msg(res.msg, {time: 2000}, function () {
                        if (res.code == 0) {
                            // var index = parent.layer.getFrameIndex(window.name);
                            // parent.layer.close(index);
                            window.location.reload();
                        }
                    });
                },
                error: function (data) {
                    layer.msg('系统错误', {time: 2000});
                }
            });
            return false;
        });
    });
    
    function show_chat(pid){
        var layer = layui.layer
            , $ = layui.jquery
            , form = layui.form;
            
        $('input[name="pid"]').val(pid);
        form.render(null,'chat-demo');
        
        layer.open({
           type:1,
           title:'发表留言',
           area:['90%','80%'],
           shadeClose:true,
           content:$('#chat_box')
       }); 
    }
</script>