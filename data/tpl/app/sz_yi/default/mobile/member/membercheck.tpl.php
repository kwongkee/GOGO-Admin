<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<link rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css">
<style>
    .list-group-item{
        border:1px solid #FFFFFF;
    }
    .list-group{
        margin-bottom:-1px;
    }
    ul{
        border: 1px solid #ddd;
    }
    .btns{
        float: right;
        margin-top: -24px;
    }
    .alert-success{
        display: none;
    }
    .alert-danger{
        display: none;
    }
</style>
<title>会员审核</title>
<div>
    <div class="alert alert-success" role="alert"></div>
    <div class="alert alert-danger" role="alert"></div>
    <?php  if(empty($list)) { ?>
    <div class="jumbotron">
        <h1>暂无信息</h1>
        <p>...</p>
        <p>
            <a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a>
        </p>
    </div>
    <?php  } else { ?>
    <?php  if(is_array($list)) { foreach($list as $row) { ?>
    <ul class="list-group">
        <li class="list-group-item">姓名：<?php  echo $row['realname'];?></li>
        <li class="list-group-item">身份证号：<?php  echo $row['id_card'];?></li>
        <li class="list-group-item">手机：<?php  echo $row['mobile'];?></li>
        <li class="list-group-item">地址：<?php  echo $row['province'];?><?php  echo $row['city'];?><?php  echo $row['address'];?></li>
        <li class="list-group-item">
            <div class="btns">
                <button type="button" class="btn btn-primary" onclick="check(<?php  echo $row['uid'];?>,'yes')">通过</button>
                <button type="button" class="btn btn-danger" onclick="check(<?php  echo $row['uid'];?>,'no')">拒绝</button>
            </div>
        </li>

    </ul>
    <?php  } } ?>
    <?php  } ?>
    <?php  echo $pager;?>
</div>
<script>
    function check(id,status){
        $.ajax({
            url:"<?php  echo $this->createMobileUrl('member/membercheck')?>",
            type:'POST',
            dataType:"json",
            data:{
                'id': id,
                'status': status
            },
            success:function(res){
                console.dir(res);
                if (res.status==1){
                    $('.alert-success').html(res.result);
                    $('.alert-success').css('display','block');
                    setTimeout(function () {
                        window.location.reload();
                    },1200);
                }else{
                    $('.alert-danger').html(res.result);
                    $('.alert-danger').css('display','block');
                    setTimeout(function () {
                        $('.alert-danger').css('display','none');
                    },1200);
                }
            },error:function (xhr) {
                $('.alert-danger').html('服务异常');
                $('.alert-danger').css('display','block');
                setTimeout(function () {
                    $('.alert-danger').css('display','none');
                },1200);
            }
        });
        return false;
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
