<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>账号配置</title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:15px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#1E9FFF !important; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .back{height: 44px;margin: 14px 5px;background: #1E9FFF;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
</style>

<div id="container">
    <div class="info_main">
        <div class="line">
            <div class="title">账号类别</div>
            <div class='info'>
                <div class='inner'>
                    <?php  if($account['type']==1) { ?>
                    企业账户
                
                    <?php  } else if($account['type']==2) { ?>
                    个人账户
                    
                    <?php  } else { ?>
                    支付账户
                    <?php  } ?>
                </div>
            </div>
        </div>
        
            <div class="line bank_account"><div class="title">银行账号</div><div class='info'><div class='inner'><input type="text" id='bank_account' placeholder="请输入账号"  value="<?php  echo $account['bank_account'];?>" /></div></div></div>
            <?php  if(($account['type']==1 || $account['type']==2)) { ?>
            <div class="line bank_name12"><div class="title">银行名称</div><div class='info'><div class='inner'>
                <?php  echo $account['bank_name'];?>
            </div></div></div>
            <?php  } else { ?>
            <div class="line bank_name3"><div class="title">支付公司</div><div class='info'><div class='inner'><input type="text" id='bank_name2' placeholder="请输入支付公司"  value="<?php  echo $account['bank_name'];?>" /></div></div></div>
            <?php  } ?>
            <div class="line name"><div class="title">账号名称</div><div class='info'><div class='inner'><input type="text" id='name' placeholder="请输入账号名称"  value="<?php  echo $account['name'];?>" /></div></div></div>
        
    </div>
    <div class="button back" onclick="javascript:history.back(-1);">返回</div>
    <!--<div class="info_sub">提交</div>-->
</div>

<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        //账号类别
        $('.account_type_radio').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.account_type_radio').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#account_type').val(val);
            if(val==1 || val==2){
                $('#baml').show();
                $('.bank_account').find('.title').text('银行账号');
                $('.bank_name3').hide();$('.bank_name12').show();
            }else if(val==3){
                $('#baml').show();
                $('.bank_account').find('.title').text('支付账号');
                $('.bank_name12').hide();$('.bank_name3').show();
            }
        });
        
        $('.info_sub').click(function() {
            let account_type = $('#account_type').val();
            if( $('#account_type').isEmpty()){
                core.tip.show('请选择账号类别!');
                return;
            }
            let bank_account = $('#bank_account').val();
            if( $('#bank_account').isEmpty()){
                core.tip.show('请输入银行账号/支付账号!');
                return;
            }
            let bank_name = $('#bank_name').val();
            if(account_type==1 || account_type==2){
                if( $('#bank_name').isEmpty()){
                    core.tip.show('请选择银行!');
                    return;
                }
            }else{
                if( $('#bank_name2').isEmpty()){
                    core.tip.show('请输入支付公司!');
                    return;
                }    
            }
            let bank_name2 = $('#bank_name2').val();
            
            let name = $('#name').val();
            if( $('#name').isEmpty()){
                core.tip.show('请输入账号名称!');
                return;
            }
            core.json('account/register',{'op':'add_account','account_type':account_type,'bank_account':bank_account,'bank_name':bank_name,'bank_name2':bank_name2,'name':name},function(json){
                if(json.status==-1){
                    core.tip.show(json.result.msg);
                }else{
                    core.tip.show('添加成功！');
                    $('#bank_account').val('');
                    setTimeout(function(){
                        window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=basic_set';
                    },1500)
                }
            });
        });
    });
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>