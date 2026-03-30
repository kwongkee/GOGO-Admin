<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title>收件地址</title>
	<link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/hui.css" />
	<link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/style.css" />
	<style>
        .hui-header h1{
            padding: 0px 38px 0px 0;
        }
        .hui-list-text {
            line-height: 25px;
            height: 100%;
            display: block;
        }
        .hui-list li{
            border-bottom: 1px solid #969696;
        }
        .hui-list-info{
            margin-top: 16px;
        }
        .hui-wrap{
			margin-bottom: 60px;
		}
		.hui-button {
		    margin: 0 1%;
		}
    </style>
</head>
<body>
<header class="hui-header">
    <div id="hui-back"></div>
    <h1>收件人列表</h1>
</header>

<div class="hui-wrap">
    <form style="padding:0 10px;" class="hui-form" id="form1">
        <div class="hui-list" style="background:#f7f7f7; margin-top:0;">
            <ul>
                <?php  if(is_array($list)) { foreach($list as $v) { ?>
                <li>
                    <a href="javascript:void(0);">
                        <div class="hui-list-text">
                            <p style="width: 100%;"><span><?php  echo $v['realname'];?></span><span><?php  echo $v['mobile'];?></span></p>
                            <p style="width: 100%;"><?php  echo $v['province'];?> <?php  echo $v['city'];?> <?php  echo $v['area'];?> <?php  echo $v['address'];?></p>
                        </div>
                        <div class="hui-list-info">
                            <span class="hui-icons hui-icons-edit font-blue iconfont" style="margin-right: 10px;" id="edit" editId="<?php  echo $v['id'];?>"></span>
                            <span class="hui-icons hui-icons-remove font-red iconfont" id="delete" removeId="<?php  echo $v['id'];?>"></span>
                        </div>
                    </a>
                </li> 
                <?php  } } ?>
            </ul>
        </div>
        <div style="padding:15px 8px; width: 40%; margin: 20px auto 64px;">
            <button type="button" class="hui-button hui-primary hui-fr red" id="submitBtn"><img src="../addons/sz_yi/static/travel_express/images/icon_07.png" alt="" class="button-pic">新增地址</button>
        </div>
    </form>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/travel_express_footer', TEMPLATE_INCLUDEPATH)) : (include template('common/travel_express_footer', TEMPLATE_INCLUDEPATH));?>

<script src="../addons/sz_yi/static/travel_express/js/hui.js" type="text/javascript" charset="utf-8"></script>
<script src="../addons/sz_yi/static/travel_express/js/hui-form.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    hui.formInit();
    //表单元素数据收集演示
    hui('#submitBtn').click(function(){
        window.location.href="<?php  echo $this->createMobileUrl("member/travel_express_address")?>&op=new";
    });

    hui('#delete').click(function(){
        var id = hui(this).attr('removeId');
        hui.confirm('您确认要删除该地址吗？', ['取消','确定'], function(){
            hui.ajax({
                url  : '<?php  echo $this->createMobileUrl("member/travel_express_address")?>&op=remove',
                type : 'POST',
                data : {id: id},
                beforeSend : function(){hui.loading();},
                complete   : function(){hui.closeLoading();},
                success : function(res){
                    hui.toast(res.result.message);
                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                },
                error : function(e){
                    console.log(JSON.stringify(e));
                    hui.iconToast('系统错误', 'warn');
                },
                'backType' : "JSON"
            });
        },function(){
            // console.log('取消后执行...');
        });
    });

    hui('#edit').click(function(){
        var id = hui(this).attr('editId');
        window.location.href="<?php  echo $this->createMobileUrl("member/travel_express_address")?>&op=get&id="+id;
    });

</script>
</body>
</html>