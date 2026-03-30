<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>付款确认</title>
<style>
    *{font-size:18px;}
    .container{padding:10px;box-sizing:border-box;}
    .container .sure{background: #1790FF;width: fit-content;color: #fff;padding: 2px 25px;box-sizing: border-box;border-radius: 5px;text-align: center;margin: 0 auto;}
    .container .back{background: #666;width: fit-content;color: #fff;padding: 2px 25px;box-sizing: border-box;border-radius: 5px;text-align: center;margin: 0 auto;}
</style>
<div class="container">
    <p>本人同意：</p>
    <p>1. 收款人委托第三方对本人的身份予以实名认证。</p>
    
    <p>2. 本人通过第三方实名认证即代表本人确认本次应付款之信息包括应付款事项与应付款金额。</p>
    
    <p>3. 收款人可对本人于应付款期限内不予足额支付应付金额向本人主张一切法律权利。</p>
    
    <p>4. 就收款人上述权利的主张放弃本人的抗辩权利，并承诺承担由此引致的收款人直接或间接的经济和社会损失。</p>
    
    <div style="display:flex;align-items:center;">
        <div class="back" onclick="javascript:history.back(-1);">返回</div>
        <div class="sure">确认</div>
    </div>
</div>
<script language="javascript">
    $('.sure').click(function(){
       $.ajax({
            url: 'https://decl.gogo198.cn/api/auth_verify',
            method: 'post',
            data: {
                'mobile': "<?php  echo $info['payer_tel'];?>",
                'idcard': "<?php  echo $info['idcard'];?>",
                'realname': "<?php  echo $info['payer_name'];?>",
                'reg_type':2,
                'is_attestation':1,
                'collection_id':"<?php  echo $info['id'];?>"
            },
            dataType: 'JSON',
            success: function (rres) {
                $.ajax({
                    url: 'https://decl.gogo198.cn/api/record_person',
                    method: 'post',
                    data: {
                        'id':"<?php  echo $info['id'];?>",
                        'form': rres,
                    },
                    dataType: 'JSON',
                    success: function (rres2) {
                        window.location.href=rres2.url;
                    }
                });
            }
       });
    });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>