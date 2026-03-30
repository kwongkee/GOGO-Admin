<?php defined('IN_IA') or exit('Access Denied');?>
<!-- 全返开关 begin -->
<!-- From:LuckStar.D    Date:2016/04/27   Content:加入全返开关,  ims_sz_yi_goods 加入isreturn字段-->
<?php  if($isreturn) { ?>
<div class='panel-heading'>
   全返设置
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否订单全返</label>
    <div class="col-sm-6 col-xs-6">
        <?php if( ce('shop.goods' ,$item) ) { ?>
        <label class="radio-inline"><input type="radio" name="isreturn" value="1" <?php  if($item['isreturn'] == 1) { ?>checked="true"<?php  } ?>  /> 是</label>
        <label class="radio-inline"><input type="radio" name="isreturn" value="0" <?php  if($item['isreturn'] == 0) { ?>checked="true"<?php  } ?>  /> 否</label>
           <?php  } else { ?>
           <div class='form-control-static'><?php  if($item['isreturn']) { ?>是<?php  } else { ?>否<?php  } ?></div>
         <?php  } ?>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否排列全返</label>
    <div class="col-sm-6 col-xs-6">
        <?php if( ce('shop.goods' ,$item) ) { ?>
        <label class="radio-inline"><input type="radio" name="isreturnqueue" value="1" <?php  if($item['isreturnqueue'] == 1) { ?>checked="true"<?php  } ?>  /> 是</label>
        <label class="radio-inline"><input type="radio" name="isreturnqueue" value="0" <?php  if($item['isreturnqueue'] == 0) { ?>checked="true"<?php  } ?>  /> 否</label>
           <?php  } else { ?>
           <div class='form-control-static'><?php  if($item['isreturn']) { ?>是<?php  } else { ?>否<?php  } ?></div>
         <?php  } ?>
    </div>
</div>
<?php  } ?>

<!-- 全返开关 end -->
<?php  if($isyunbi) { ?>
<div class='panel-heading'>
   <?php  echo $yunbi_set['yunbi_title'];?>设置
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><p class="yunbi">是否返 <?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?> </p></label>
    <div class="col-sm-6 col-xs-6">
        <?php if( ce('shop.goods' ,$item) ) { ?>
        <label class="radio-inline"><input type="radio" name="isyunbi" value="1" <?php  if($item['isyunbi'] == 1) { ?>checked="true"<?php  } ?>  /> 是</label>
        <label class="radio-inline"><input type="radio" name="isyunbi" value="0" <?php  if($item['isyunbi'] == 0) { ?>checked="true"<?php  } ?>  /> 否</label>
           <?php  } else { ?>
           <div class='form-control-static'><?php  if($item['isyunbi']) { ?>是<?php  } else { ?>否<?php  } ?></div>
         <?php  } ?>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">消费获得<?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?>比例</label>
    <div class="col-sm-8">
        <div class="input-group">
            <input type="text" name="yunbi_consumption" class="form-control" value="<?php  echo $item['yunbi_consumption'];?>"  />
            <div class="input-group-addon">%</div>
        </div>
        <span class='help-block'>商品返还多少
        <b class="yunbi"> <?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?> </b>的百分比！优先级：单个商品设置大于订单统一设置 </span>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">消费上级获得<?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?>比例</label>
    <div class="col-sm-8">
        <div class="input-group">
            <input type="text" name="yunbi_commission" class="form-control" value="<?php  echo $item['yunbi_commission'];?>"  />
            <div class="input-group-addon">%</div>
        </div>
        <span class='help-block'>购买商品上级多少
        <b class="yunbi"> <?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?> </b>的百分比！0 或 为空时上级不获得<?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?> </span>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><?php  echo $yunbi_set['yunbi_title'];?>抵扣</label>
    <div class="col-sm-4">
        <div class='input-group'>
            <span class="input-group-addon">最多抵扣</span>
            <input type="text" name="yunbi_deduct"  value="<?php  echo $item['yunbi_deduct'];?>" class="form-control" />
            <span class="input-group-addon">元</span>
        </div>
       <span class="help-block">如果设置0，则不支持<?php  echo $yunbi_set['yunbi_title'];?>抵扣</span>
    </div>   
</div> 
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><p class="yunbi">是否强制使用<?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?> </p></label>
    <div class="col-sm-6 col-xs-6">
        <?php if( ce('shop.goods' ,$item) ) { ?>
        <label class="radio-inline"><input type="radio" name="isforceyunbi" value="1" <?php  if($item['isforceyunbi'] == 1) { ?>checked="true"<?php  } ?>  /> 是</label>
        <label class="radio-inline"><input type="radio" name="isforceyunbi" value="0" <?php  if($item['isforceyunbi'] == 0) { ?>checked="true"<?php  } ?>  /> 否</label>
           <?php  } else { ?>
           <div class='form-control-static'><?php  if($item['isforceyunbi']) { ?>是<?php  } else { ?>否<?php  } ?></div>
         <?php  } ?>
    </div>
</div>
<div class='panel-heading'>
   报单设置
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><p class="yunbi">是否使用报单</p></label>
    <div class="col-sm-6 col-xs-6">
        <label class="radio-inline"><input type="radio" name="isdeclaration" value="1" <?php  if($item['isdeclaration'] == 1) { ?>checked="true"<?php  } ?>  /> 是</label>
        <label class="radio-inline"><input type="radio" name="isdeclaration" value="0" <?php  if($item['isdeclaration'] == 0) { ?>checked="true"<?php  } ?>  /> 否</label>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">报单人获得<?php  echo $yunbi_set['yunbi_title'];?></label>
    <div class="col-sm-4">
        <div class='input-group'>
            <input type="text" name="virtual_declaration"  value="<?php  echo $item['virtual_declaration'];?>" class="form-control" />
            <span class="input-group-addon"><?php  echo $yunbi_set['yunbi_title'];?></span>
        </div>
       <span class="help-block">报单人可获得<?php  echo $yunbi_set['yunbi_title'];?>数量</span>
    </div>   
</div> 
<?php  } ?>