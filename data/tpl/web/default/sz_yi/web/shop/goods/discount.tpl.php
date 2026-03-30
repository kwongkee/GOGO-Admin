<?php defined('IN_IA') or exit('Access Denied');?>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员等级浏览权限</label>
    <div class="col-sm-9 col-xs-12 chks">
           <?php if( ce('shop.goods' ,$item) ) { ?>
       <label class="checkbox-inline">
           <input type="checkbox" class='chkall' name="showlevels" value="" <?php  if($item['showlevels']=='') { ?>checked="true"<?php  } ?>  /> 全部会员等级
       </label>
       <label class="checkbox-inline">
           <input type="checkbox" class='chksingle' name="showlevels[]" value="0" <?php  if($item['showlevels']!='' && is_array($item['showlevels']) && in_array('0', $item['showlevels'])) { ?> checked="true"<?php  } ?>  />  <?php echo empty($shop['levelname'])?'普通等级':$shop['levelname']?>
       </label>
       <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
          <label class="checkbox-inline">
           <input type="checkbox" class='chksingle' name="showlevels[]" value="<?php  echo $level['id'];?>" <?php  if($item['showlevels']!='' && is_array($item['showlevels'])  && in_array($level['id'], $item['showlevels'])) { ?>checked="true"<?php  } ?>  /> <?php  echo $level['levelname'];?>
          </label>
       <?php  } } ?>
       <?php  } else { ?>
       <div class='form-control-static'>
           <?php  if($item['showlevels']=='') { ?>
              全部会员等级
           <?php  } else { ?>
           <?php  if($item['showlevels']!='' && is_array($item['showlevels']) && in_array('0', $item['showlevels'])) { ?>
              <?php echo empty($shop['levelname'])?'普通等级':$shop['levelname']?>; 
           <?php  } ?>
           <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
                   <?php  if($item['showlevels']!='' && is_array($item['showlevels'])  && in_array($level['id'], $item['showlevels'])) { ?>
                      <?php  echo $level['levelname'];?>; 
                   <?php  } ?>
            <?php  } } ?>
       <?php  } ?>
       </div>
       
       <?php  } ?>
    </div>
</div>   
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员等级购买权限</label>
    <div class="col-sm-9 col-xs-12 chks" >
              <?php if( ce('shop.goods' ,$item) ) { ?>
              
       <label class="checkbox-inline">
           <input type="checkbox" class='chkall' name="buylevels" value="" <?php  if($item['buylevels']=='' ) { ?>checked="true"<?php  } ?>  /> 全部会员等级
       </label>
       <label class="checkbox-inline">
           <input type="checkbox" class='chksingle'  name="buylevels[]" value="0" <?php  if($item['buylevels']!='' && is_array($item['buylevels'])  && in_array('0', $item['buylevels'])) { ?>checked="true"<?php  } ?>  />  <?php echo empty($shop['levelname'])?'普通等级':$shop['levelname']?>
       </label>
       <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
          <label class="checkbox-inline">
           <input type="checkbox" class='chksingle'  name="buylevels[]" value="<?php  echo $level['id'];?>" <?php  if($item['buylevels']!='' && is_array($item['buylevels']) && in_array($level['id'], $item['buylevels']) ) { ?>checked="true"<?php  } ?>  /> <?php  echo $level['levelname'];?>
          </label>
       <?php  } } ?>
            <?php  } else { ?>
       <div class='form-control-static'>
           <?php  if($item['buylevels']=='') { ?>
              全部会员等级
           <?php  } else { ?>
           <?php  if($item['buylevels']!='' && is_array($item['buylevels']) && in_array('0', $item['buylevels'])) { ?>
              <?php echo empty($shop['levelname'])?'普通等级':$shop['levelname']?>; 
           <?php  } ?>
           <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
                   <?php  if($item['buylevels']!='' && is_array($item['buylevels'])  && in_array($level['id'], $item['buylevels'])) { ?>
                      <?php  echo $level['levelname'];?>; 
                   <?php  } ?>
            <?php  } } ?>
       <?php  } ?>
       </div>
       
       <?php  } ?>
       
       
    </div>
</div>   
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员组浏览权限</label>
    <div class="col-sm-9 col-xs-12 chks" >
            <?php if( ce('shop.goods' ,$item) ) { ?>
       <label class="checkbox-inline">
           <input type="checkbox" class='chkall' name="showgroups" value="" <?php  if($item['showgroups']=='' ) { ?>checked="true"<?php  } ?>  /> 全部会员组
       </label>
       <label class="checkbox-inline">
           <input type="checkbox" class='chksingle'  name="showgroups[]" value="0" <?php  if($item['showgroups']!='' && is_array($item['showgroups']) && in_array('0', $item['showgroups'])) { ?>checked="true"<?php  } ?>  /> 无分组
       </label>
       <?php  if(is_array($groups)) { foreach($groups as $group) { ?>
          <label class="checkbox-inline">
           <input type="checkbox" class='chksingle'  name="showgroups[]" value="<?php  echo $group['id'];?>" <?php  if($item['showgroups']!=''  && in_array($group['id'], $item['showgroups']) && is_array($item['showgroups'])) { ?>checked="true"<?php  } ?>  /> <?php  echo $group['groupname'];?>
          </label>
       <?php  } } ?>
       
          <?php  } else { ?>
       <div class='form-control-static'>
           <?php  if($item['showgroups']=='') { ?>
              全部会员等级
           <?php  } else { ?>
           <?php  if($item['showgroups']!='' && is_array($item['showgroups']) && in_array('0', $item['showgroups'])) { ?>
              <?php echo empty($shop['levelname'])?'普通等级':$shop['levelname']?>; 
           <?php  } ?>
           <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
                   <?php  if($item['showgroups']!='' && is_array($item['showgroups'])  && in_array($level['id'], $item['showgroups'])) { ?>
                      <?php  echo $level['levelname'];?>; 
                   <?php  } ?>
            <?php  } } ?>
       <?php  } ?>
       </div>
       
       <?php  } ?>
       
    </div>
</div>   

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员组购买权限</label>
    <div class="col-sm-9 col-xs-12 chks" >
            <?php if( ce('shop.goods' ,$item) ) { ?>
       <label class="checkbox-inline">
           <input type="checkbox" class='chkall' name="buygroups" value="" <?php  if($item['buygroups']=='' ) { ?>checked="true"<?php  } ?>  /> 全部会员组
       </label>
       <label class="checkbox-inline">
           <input type="checkbox" class='chksingle'  name="buygroups[]" value="0" <?php  if($item['buygroups']!=''  && is_array($item['buygroups']) && in_array('0', $item['buygroups'])) { ?>checked="true"<?php  } ?>  />  无分组
       </label>
       <?php  if(is_array($groups)) { foreach($groups as $group) { ?>
          <label class="checkbox-inline">
           <input type="checkbox" class='chksingle'  name="buygroups[]" value="<?php  echo $group['id'];?>" <?php  if($item['buygroups']!='' &&  is_array($item['buygroups']) && in_array($group['id'], $item['buygroups']) ) { ?>checked="true"<?php  } ?>  /> <?php  echo $group['groupname'];?>
          </label>
       <?php  } } ?>
          <?php  } else { ?>
       <div class='form-control-static'>
           <?php  if($item['buygroups']=='') { ?>
              全部会员等级
           <?php  } else { ?>
           <?php  if($item['buygroups']!='' && is_array($item['buygroups']) && in_array('0', $item['buygroups'])) { ?>
              <?php echo empty($shop['levelname'])?'普通等级':$shop['levelname']?>; 
           <?php  } ?>
           <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
                   <?php  if($item['buygroups']!='' && is_array($item['buygroups'])  && in_array($level['id'], $item['buygroups'])) { ?>
                      <?php  echo $level['levelname'];?>; 
                   <?php  } ?>
            <?php  } } ?>
       <?php  } ?>
       </div>
       
       <?php  } ?>
       
    </div>
</div>   
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
    <div class="col-sm-6 col-xs-6">
      <div class='alert alert-info'>
        只有当折扣大于0，小于10的情况下才能生效，否则按自身会员等级折扣计算<br/>（折扣填写0.1-10之间
      </div>
    </div>
</div>  

<div class='panel-heading'>
    等级折扣
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">折扣类型</label>
    <div class="col-sm-6 col-xs-6">
      <label class="radio-inline">
        <input type="radio" name="discounttype" value="1" <?php  if($discounttype == 1) { ?> checked="true" <?php  } ?> /> 会员等级
      </label>
      <?php  if(!empty($com_set['level'])) { ?>
        <label class="radio-inline">
          <input type="radio" name="discounttype" value="2" <?php  if($discounttype == 2) { ?> checked="true" <?php  } ?> /> 分销商等级
        </label>
      <?php  } ?>
    </div>
</div>   

    <div class="form-group">
      <label class="col-xs-12 col-sm-3 col-md-2 control-label">折扣方式</label>
      <div class="col-sm-6 col-xs-6">
        <div class='input-group'>
         <label class="radio-inline">
             <input type="radio" name="discountway" value="1" <?php  if($discountway == 1) { ?> checked="true" <?php  } ?> /> 折扣
           </label>
             <label class="radio-inline">
               <input type="radio" name="discountway" value="2" <?php  if($discountway == 2) { ?> checked="true" <?php  } ?> /> 固定金额
             </label>
        </div>
      </div> 
    </div> 
    <div id="ismember"  <?php  if($discounttype != 1) { ?> style="display:none" <?php  } ?>>
      <div class="form-group">
         <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员等级折扣</label>
         <div class="col-sm-6 col-xs-6">
             <div class='input-group'>
                <div class='input-group-addon'>默认等级</div>
                <input type='text' name='discounts[default]' class="form-control discounts" value="<?php  echo $discounts['default']?>" />
                <div class='input-group-addon waytxt'><?php  if($discountway == 1) { ?>折<?php  } else { ?>元<?php  } ?></div>
            </div>
         </div>
      </div>   
      <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
        <div class="form-group">
           <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
           <div class="col-sm-6 col-xs-6">
               <div class='input-group'>
                  <div class='input-group-addon'><?php  echo $level['levelname'];?></div>
                  <input type='text' name='discounts[level<?php  echo $level['id'];?>]' class="form-control discounts"  value="<?php  echo $discounts['level'.$level['id']]?>" />
                  <div class='input-group-addon waytxt'><?php  if($discountway == 1) { ?>折<?php  } else { ?>元<?php  } ?></div>
              </div>
           </div>
        </div>   
      <?php  } } ?>
    </div>

    <div id="isdistribution"  <?php  if($discounttype != 2) { ?> style="display:none" <?php  } ?>>
      <div class="form-group">
         <label class="col-xs-12 col-sm-3 col-md-2 control-label">分销商等级折扣</label>
         <div class="col-sm-6 col-xs-6">
             <div class='input-group'>
                <div class='input-group-addon'>默认等级</div>
                <input type='text' name='discounts2[default]' class="form-control discounts2" value="<?php  echo $discounts2['default']?>" />
                <div class='input-group-addon waytxt'><?php  if($discountway == 1) { ?>折<?php  } else { ?>元<?php  } ?></div>
            </div>
         </div>
      </div>   
      <?php  if(is_array($distributor_levels)) { foreach($distributor_levels as $level) { ?>
      <div class="form-group">
         <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
         <div class="col-sm-6 col-xs-6">
             <div class='input-group'>
                <div class='input-group-addon'><?php  echo $level['levelname'];?></div>
                <input type='text' name='discounts2[level<?php  echo $level['id'];?>]' class="form-control discounts2"  value="<?php  echo $discounts2['level'.$level['id']]?>" />
                <div class='input-group-addon waytxt'><?php  if($discountway == 1) { ?>折<?php  } else { ?>元<?php  } ?></div>
            </div>
         </div>
      </div>   
      <?php  } } ?>
    </div>
 

<?php  if(p('return')) { ?>
  <div class='panel-heading'>
      等级返现
  </div>
  <div class="form-group">
      <label class="col-xs-12 col-sm-3 col-md-2 control-label">返现类型</label>
      <div class="col-sm-6 col-xs-6">
          <label class="radio-inline">
            <input type="radio" name="returntype" value="1" <?php  if($returntype == 1) { ?> checked="true" <?php  } ?> /> 会员等级
          </label>
          <?php  if(!empty($com_set['level'])) { ?>
            <label class="radio-inline">
              <input type="radio" name="returntype" value="2" <?php  if($returntype == 2) { ?> checked="true" <?php  } ?> /> 分销商等级
            </label>
          <?php  } ?>
      </div>
  </div> 
  <div id="ismember2"  <?php  if($returntype != 1) { ?> style="display:none" <?php  } ?>>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员等级返现</label>
        <div class="col-sm-6 col-xs-6">
            <div class='input-group'>
              <div class='input-group-addon'>默认等级-返现</div>
              <input type='text' name='returns[default]' class="form-control returns" value="<?php  echo $returns['default']?>" />
              <div class='input-group-addon'>元 </div>
           </div>
        </div>
    </div>   
    <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-sm-6 col-xs-6">
            <div class='input-group'>
              <div class='input-group-addon'><?php  echo $level['levelname'];?>-返现</div>
              <input type='text' name='returns[level<?php  echo $level['id'];?>]' class="form-control returns"  value="<?php  echo $returns['level'.$level['id']]?>" />
              <div class='input-group-addon'>元</div>
           </div>
        </div>
    </div>   
    <?php  } } ?>
  </div>
  <div id="isdistribution2"  <?php  if($returntype != 2) { ?> style="display:none" <?php  } ?>>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">分销商等级返现</label>
        <div class="col-sm-6 col-xs-6">
            <div class='input-group'>
               <div class='input-group-addon'>默认等级-返现</div>
               <input type='text' name='returns2[default]' class="form-control returns2" value="<?php  echo $returns2['default']?>" />
               <div class='input-group-addon'>元 </div>
           </div>
        </div>
    </div>   
    <?php  if(is_array($distributor_levels)) { foreach($distributor_levels as $level) { ?>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-sm-6 col-xs-6">
            <div class='input-group'>
              <div class='input-group-addon'><?php  echo $level['levelname'];?>-返现</div>
              <input type='text' name='returns2[level<?php  echo $level['id'];?>]' class="form-control returns2" value="<?php  echo $returns2['level'.$level['id']]?>" />
              <div class='input-group-addon'>元</div>

           </div>
        </div>
    </div>   
    <?php  } } ?>
  </div>
<?php  } ?>

<script language='javascript'>
    $('input[name="discounttype"]').click(function(){
      var discounttype = $('input:radio[name=discounttype]:checked').val();
      if(discounttype == 1){
        $('#isdistribution').hide();
        $('#ismember').show();
      }else{
        $('#ismember').hide();
        $('#isdistribution').show();
      }
    });
    $('input[name="returntype"]').click(function(){
      var returntype = $('input:radio[name=returntype]:checked').val();
      if(returntype == 1){
        $('#isdistribution2').hide();
        $('#ismember2').show();
      }else{
        $('#ismember2').hide();
        $('#isdistribution2').show();
      }
    });

    $('input[name="discountway"]').click(function(){
      var discountway = $('input:radio[name=discountway]:checked').val();
      if(discountway == 1){
        $('.waytxt').html('折');
      }else{
        $('.waytxt').html('元');
      }
    });
 

    $('.chkall').click(function(){
        var checked =$(this).get(0).checked;
        if(checked) {
            $(this).closest('div').find(':checkbox[class!="chkall"]').removeAttr('checked');
        }
    });
    $('.chksingle').click(function(){
         $(this).closest('div').find(':checkbox[class="chkall"]').removeAttr('checked');
    })
    
	</script>