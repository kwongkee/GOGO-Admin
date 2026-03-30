<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('member/center', TEMPLATE_INCLUDEPATH)) : (include template('member/center', TEMPLATE_INCLUDEPATH));?>
<title>收货地址管理</title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    a {text-decoration:none;}

    .address_addnav {height:44px; width:94%; padding:0 3%; border-bottom:1px solid #f0f0f0; border-top:1px solid #f0f0f0; margin-top:14px; line-height:42px; color:#666; background:#fff;box-sizing: content-box;}
    .address_list {height:50px; padding:0 10px;  border-bottom:1px solid #f0f0f0; border-top:1px solid #f0f0f0; margin-top:14px; background:#fff;}
    .address_list .ico {height:50px; width:30px;   float:left; color:#999;margin-right:-30px; z-index:2}
    .address_list .ico i { font-size:24px;margin-top:15px;margin-left:10px;z-index:2;position: relative;}
    .address_list .info {height:50px; width:94%; float:left;position: relative;}
    .address_list .info .inner { margin-left:40px;margin-right:50px;}
    .address_list .info .inner .addr {height:20px; width:100%; color:#999; line-height:26px; font-size:14px; overflow:hidden;}
    .address_list .info .inner .user {height:30px; width:100%; color:#666; line-height:30px; font-size:16px; overflow:hidden;}
    .address_list .info .inner .user span {color:#444; font-size:16px;}
    .address_list .btn { width:45px; float:right;margin-left:-45px;z-index:2;position: relative;padding: 0;box-sizing: content-box;}
    .address_list .btn .edit,.address_list .btn .remove {height:50px; float:right; color:#999; font-size:18px;/*margin-top:5px;*/}
    .address_list .btn .edit { margin-right:10px;}
    
.address_addnav {height:40px;  border-bottom:1px solid #f0f0f0; border-top:1px solid #f0f0f0; margin-top:14px; line-height:40px; color:#666; }
.address_main {height:auto;width:94%; padding:0px 3%; border-bottom:1px solid #f0f0f0; border-top:1px solid #f0f0f0; margin-top:14px; background:#fff;}
.address_main .line {height:44px; width:100%; border-bottom:1px solid #f0f0f0; line-height:44px;}

.address_main .line input {float:left; height:44px; width:100%; padding:0px; margin:0px; border:0px; outline:none; font-size:14px; color:#666;padding-left:5px;}
.address_main .line select  { border:none;height:42px;width:100%;color:#666;font-size:14px;}
.address_sub1 {height:44px; margin:14px 10px; background:#ff4f4f; border-radius:4px; text-align:center; font-size:14px; line-height:44px; color:#fff;}
.address_sub2 {height:44px; margin:14px 10px; background:#ddd; border-radius:4px; text-align:center; font-size:14px; line-height:44px; color:#666; border:1px solid #d4d4d4;}
</style>
<?php  if($trade['is_street'] == 1) { ?>
<script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/cascade_street.js"></script>
<?php  } else { ?>
<script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/cascade.js"></script>
<?php  } ?>
<div id='container' class="rightlist"></div></div>

<script id='address_list' type='text/html'>
    <div class="page_topbar">
        <!--<a href="javascript:;" class="back" onclick="history.back()"><i class="fa fa-angle-left"></i></a>-->
        <div class="title"><?php  if($_W['uniacid'] == 18) { ?>Delivery address management<?php  } else { ?>收货地址管理<?php  } ?></div>
    </div>
    
   <%each list as value%>
   <div class="address_list" 
        data-addressid="<%value.id%>">
        <div class="ico" ><i class="fa <%if value.isdefault=='1'%>fa-check-circle-o<%else%>fa-circle-o<%/if%>" <%if value.isdefault=='1'%>style="color:#0c9"<%/if%>></i></div>
        <div class="info">
            <div class='inner'>
               <div class="addr"><%value.address%></div>
               <div class="user"><span><%value.realname%>  <%value.mobile%></span></div>
            </div>
        </div>
        <div class='btn'>
            <div class="remove" ><i class="fa fa-remove" style="margin-top:13px"></i></div>
            <div class="edit"><i class="fa fa-pencil" style="margin-top:14px"></i></div>
        </div>
    </div>
  <%/each%>
  <a href="javascript:;" id='new_address'><div class="address_addnav"><i class="fa fa-plus-circle" style="color:#999; margin:10px 10px 0 8px; font-size:18px;"></i><?php  if($_W['uniacid'] == 18) { ?>New shipping address<?php  } else { ?>新增收货地址<?php  } ?></div></a>
</script>
<script id='address_data' type='text/html'>
 
      <div class="page_topbar">
        <!--<a href="javascript:;" class="back" id="editback"><i class="fa fa-angle-left"></i></a>-->
        <div class="title"> <%if !address.id%><?php  if($_W['uniacid'] == 18) { ?>Add shipping address<?php  } else { ?>添加收货地址<?php  } ?><%else%><?php  if($_W['uniacid'] == 18) { ?>Edit shipping address<?php  } else { ?>编辑收货地址<?php  } ?><%/if%></div>
    </div>
    
    <div class="address_main" >
        <input type='hidden' id='addressid' value="<%address.id%>"/>
        <div class="line"><input type="text" placeholder="<?php  if($_W['uniacid'] == 18) { ?>Recipient<?php  } else { ?>收件人<?php  } ?>" id="realname" value="<?php  if(address.realname) { ?><%address.realname%><?php  } ?>" /></div>
        <div class="line"><input type="text" placeholder="<?php  if($_W['uniacid'] == 18) { ?>contact number<?php  } else { ?>联系电话<?php  } ?>"  id="mobile" value="<?php  if(address.mobile) { ?><%address.mobile%><?php  } ?>"/></div>
        
        <div class="line"><select id="sel-provance" onchange="selectCity();" class="select"><option value="" selected="true"><?php  if($_W['uniacid'] == 18) { ?>Province<?php  } else { ?>所在省份<?php  } ?></option></select></div>
         <div class="line"><select id="sel-city" onchange="selectcounty()" class="select"><option value="" selected="true"><?php  if($_W['uniacid'] == 18) { ?>City<?php  } else { ?>所在城市<?php  } ?></option></select></div>
        <div class="line"><select id="sel-area" <?php  if($trade['is_street'] == 1) { ?>onchange="selectstreet()"<?php  } ?> class="select"><option value="" selected="true"><?php  if($_W['uniacid'] == 18) { ?>Area<?php  } else { ?>所在地区<?php  } ?></option></select></div>
        <?php  if($trade['is_street'] == 1) { ?>
        <div class="line"><select id="sel-street" class="select"><option value="" selected="true"><?php  if($_W['uniacid'] == 18) { ?>Street<?php  } else { ?>所在街道<?php  } ?></option></select></div>
        <?php  } ?>
        
        <div class="line"><input type="text" placeholder="<?php  if($_W['uniacid'] == 18) { ?>Detailed address (excluding provinces and cities)
<?php  } else { ?>详细地址(不包含省份城市)<?php  } ?>"  id="address" value="<?php  if(address.address) { ?><%address.address%><?php  } ?>"/></div>
<!--        <div class="line"><input type="text" placeholder="<?php  if($_W['uniacid'] == 18) { ?>Postal code<?php  } else { ?>邮政编码<?php  } ?>"  id="zipcode" value="<?php  if(address.zipcode) { ?><%address.zipcode%><?php  } ?>"/></div>-->
    </div>
    <div class="address_sub1"><?php  if($_W['uniacid'] == 18) { ?>Confirm<?php  } else { ?>确认<?php  } ?></div>
    <div class="address_sub2"><?php  if($_W['uniacid'] == 18) { ?>Cancel<?php  } else { ?>取消<?php  } ?></div>

</script>

<script language="javascript">
    
    require(['tpl', 'core'], function(tpl, core) {
        
        function bindEditEvents(){
       
            $('.address_sub1').click(function(){
                
                if($(this).attr('saving')=='1'){
                        return;
                }
                if($('#realname').isEmpty()){
                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the recipient<?php  } else { ?>请输入收件人<?php  } ?>!');
                    return;
                }
                if(!$('#mobile').isMobile()){
                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the correct contact number<?php  } else { ?>请输入正确的联系电话<?php  } ?>!');
                    return;
                }
	       if($('#sel-provance').val()=='<?php  if($_W['uniacid'] == 18) { ?>Please select province<?php  } else { ?>请选择省份<?php  } ?>'){
                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please select province<?php  } else { ?>请选择省份<?php  } ?>!');
                    return;
                }
	       if($('#sel-city').val()=='<?php  if($_W['uniacid'] == 18) { ?>Please select city<?php  } else { ?>请选择城市<?php  } ?>'){
                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please select city<?php  } else { ?>请选择城市<?php  } ?>!');
                    return;
                }
	       if($('#sel-area').val()=='<?php  if($_W['uniacid'] == 18) { ?>please select the region<?php  } else { ?>请选择地区<?php  } ?>'){
                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>please select the region<?php  } else { ?>请选择地区<?php  } ?>!');
                    return;
                }
                if($('#address').isEmpty()){
                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Please enter the delivery address<?php  } else { ?>请输入收货地址<?php  } ?>!');
                    return;
                } 
               
                $('.address_sub1').html('<?php  if($_W['uniacid'] == 18) { ?>Processing<?php  } else { ?>正在处理<?php  } ?>...').attr('saving',1);
                if ($('#sel-street').val()) {
                    var street = $('#sel-street').val();
                } else {
                    var street = '';
                }
                core.json('shop/address',{
                    op:'submit',
                    id:$('#addressid').val(),
                    addressdata: {
                        realname: $('#realname').val(),
                        mobile: $('#mobile').val(),
                        address: $('#address').val(),
                        province: $('#sel-provance').val(),
                        city: $('#sel-city').val(),
                        area: $('#sel-area').val(),
                        street:street
                                //,zipcode: $('#zipcode').val()
                    }
                 },function(json){
                     if(json.status==1){
                         core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Saved successfully<?php  } else { ?>保存成功<?php  } ?>!');
                         list();
                     }
                     else{
                         $('.address_sub1').html('<?php  if($_W['uniacid'] == 18) { ?>Confirm<?php  } else { ?>确认<?php  } ?>').removeAttr('saving');
                         core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Save failed<?php  } else { ?>保存失败<?php  } ?>');
                     }
                },true,true);
            });
 
            $('.address_sub2,#editback').click(function(){
                list();
            });
        }
        function list(){
            core.json('shop/address',{},function(json){
                 $('#container').html(  tpl('address_list',json.result) );

                 $('.edit').click(function(){
                  
                    var id =$(this).closest('.address_list').data('addressid');
                    core.json('shop/address',{op:'get',id:id},function(json){
                        $('#container').html(  tpl('address_data',json.result) );
                        var address = json.result.address;
                        <?php  if($trade['is_street'] == 1) { ?>
                        cascdeInit(address.province,address.city,address.area,address.street);
                        <?php  } else { ?>
                        cascdeInit(address.province,address.city,address.area);
                        <?php  } ?>
                        bindEditEvents();
                     },true);
                     
                 })
        
                 $('.ico').click(function(){
                     var id =$(this).closest('.address_list').data('addressid');
                 
                      $('.ico i').removeClass('fa-check-circle-o').addClass('fa-circle-o').css('color','#999');
                          $('.address_list[data-addressid='+id +'] .ico i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color','#0c9');
                         core.json('shop/address',{op:'setdefault',id:id},function(json){
                          if(json.status==1){
                              core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Set the default address successfully<?php  } else { ?>设置默认地址成功<?php  } ?>');
                          }
                          else{
                              core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>Failed to set default address<?php  } else { ?>设置默认地址失败<?php  } ?>');
                          }
                         },true,true);
                 });
            
                 $('.remove').click(function(){
                     var id =$(this).closest('.address_list').data('addressid');
                      core.tip.confirm('<?php  if($_W['uniacid'] == 18) { ?>Confirm to delete this address<?php  } else { ?>确认要删除此地址<?php  } ?>?',function(){

                             var aobj = $('.address_list[data-addressid='+id +']');
                             aobj.fadeOut(500,function(){ 
                                       aobj.remove();
                             });
                              core.json('shop/address',{op:'remove',id:id},function(json){
                                if(json.status==1){
                                    if(json.result && json.result.defaultid){
                                        $('.ico i').removeClass('fa-check-circle-o').addClass('fa-circle-o').css('color','#999');
                                        $('.address_list[data-addressid='+json.result.defaultid +'] .ico i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color','#0c9');
                                    }
                                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>successfully deleted<?php  } else { ?>删除成功<?php  } ?>');
                                }
                                else{
                                    core.tip.show('<?php  if($_W['uniacid'] == 18) { ?>failed to delete<?php  } else { ?>删除失败<?php  } ?>');
                                }
                             },true,true);
                     }) 
                 });
               
                 $('#new_address').click(function(){
                      core.json('shop/address',{op:'new'},function(json){
                          var result = json.result;
                          $('#container').html(  tpl('address_data',result) );
                          <?php  if($trade['is_street'] == '1') { ?>
                          cascdeInit(result.address.province,result.address.city,result.address.area,result.address.street);
                          <?php  } else { ?>
                          cascdeInit(result.address.province,result.address.city,result.address.area);
                          <?php  } ?>
                          <?php  if($trade['shareaddress']=='1' && $trade['is_street'] != '1' && is_weixin()) { ?>
                          var shareAddress = <?php  echo json_encode($shareAddress)?>;
                          WeixinJSBridge.invoke('editAddress',shareAddress,function(res){
                              if(res.err_msg=='edit_address:ok'){
                                  $("#realname").val( res.userName  );
                                  $('#mobile').val( res.telNumber );
                                  $('#address').val( res.addressDetailInfo );
                                  cascdeInit(res.proviceFirstStageName,res.addressCitySecondStageName,res.addressCountiesThirdStageName);
                                }
                          });
                          <?php  } ?>
                            bindEditEvents();
                     },true);
                  });
            },true);
        }
        
        list();
        
    })
</script>
<?php  $show_footer = true?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
