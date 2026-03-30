<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('tabs', TEMPLATE_INCLUDEPATH)) : (include template('tabs', TEMPLATE_INCLUDEPATH));?>
<div class="rightlist">
<?php  if($operation=='display') { ?>
        <form action="./index.php" method="get" class="form-horizontal" role="form" id="form1">
            <input type="hidden" name="c" value="site" />
            <input type="hidden" name="a" value="entry" />
            <input type="hidden" name="m" value="sz_yi" />
            <input type="hidden" name="do" value="plugin" />
            <input type="hidden" name="p" value="coupon" />
            <input type="hidden" name="method" value="coupon" />
            <input type="hidden" name="op" value="display" />
      
<div class="panel panel-info">
    <div class="panel-heading">筛选</div>
    <div class="panel-body">

            <div class="form-group">
              
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">优惠券名称</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                    <input type="text" class="form-control"  name="keyword" value="<?php  echo $_GPC['keyword'];?>" placeholder='可搜索优惠券名称'/> 
                </div>
            </div>
                  <div class="form-group">
                   <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">分类</label>
                    <div class="col-sm-9 col-xs-12">
      <select name='catid' class='form-control'>
        <option value=''></option>
        <?php  if(is_array($category)) { foreach($category as $k => $c) { ?>
          <option value='<?php  echo $k;?>' <?php  if($_GPC['catid']==$k) { ?>selected<?php  } ?>><?php  echo $c['name'];?></option>
                <?php  } } ?>
      </select>
                      
                    </div>
         </div>
              <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">类型</label>
              <div class="col-sm-8 col-lg-9 col-xs-12">
                    <select name='type' class='form-control'>
                        <option value=''></option>
                        <option value='0' <?php  if($_GPC['type']=='0') { ?>selected<?php  } ?>>购物</option>
                        <option value='1' <?php  if($_GPC['type']=='1') { ?>selected<?php  } ?>>充值</option>
                    </select>
                  </div>
               
                </div>
    <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">领取中心是否显示</label>
              <div class="col-sm-8 col-lg-9 col-xs-12">
                    <select name='gettype' class='form-control'>
                        <option value=''></option>
                        <option value='0' <?php  if($_GPC['gettype']=='0') { ?>selected<?php  } ?>>不显示</option>
                        <option value='1' <?php  if($_GPC['gettype']=='1') { ?>selected<?php  } ?>>显示</option>
                    </select>
                  </div>
               
                </div>
                  <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">创建时间</label>
                    <div class="col-sm-7 col-lg-9 col-xs-12">
                        <div class="col-sm-3">
                            <label class='radio-inline'>
                                <input type='radio' value='0' name='searchtime' <?php  if($_GPC['searchtime']=='0') { ?>checked<?php  } ?>>不搜索
                            </label> 
                             <label class='radio-inline'>
                                <input type='radio' value='1' name='searchtime' <?php  if($_GPC['searchtime']=='1') { ?>checked<?php  } ?>>搜索
                            </label>
                     </div>
                        <?php  echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d', $starttime),'endtime'=>date('Y-m-d ', $endtime)),true);?>
                    </div>
                </div>
 
<div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label"></label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                       <button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
      <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                    
                </div>
            </div>    
   
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">总数:<?php  echo $total;?> <small>排序数字越大越靠前</small></div>
    <div class="panel-body">
        <table class="table table-hover table-responsive">
            <thead class="navbar-inner" >
                <tr>
                     <th width="4%">ID</th>
                     <th width="6%">排序</th>
                     <th width="16%">优惠券名称</th>
                     <th width="16%">使用条件/优惠</th>
                     <th width="10%">已使用/已发出/剩余数量</th>
                     <th width="10%">领取中心</th>
                     <th width="10%">口令玩法人数/猜中人数</th>
                     <th width="10%">创建时间</th>
                     <th width="18%">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php  if(is_array($list)) { foreach($list as $row) { ?>
                <tr>
                      <td><?php  echo $row['id'];?></td>
            <td>
         <?php if(cv('coupon.coupon.edit')) { ?>
                           <input type="text" class="form-control" name="displayorder[<?php  echo $row['id'];?>]" value="<?php  echo $row['displayorder'];?>">
                        <?php  } else { ?>
                           <?php  echo $row['displayorder'];?>
                        <?php  } ?>
            </td>
                
        <td><?php  if($row['coupontype']==0) { ?>
          <label class='label label-success'>购物</label>
              <?php  } else { ?>
              <label class='label label-warning'>充值</label>
           <?php  } ?>
           <?php  if(!empty($row['catid'])) { ?>
           <label class='label label-primary'><?php  echo $category[$row['catid']]['name'];?></label>
           <?php  } ?>
           <br/><?php  echo $row['couponname'];?>
            </td>
            <td><?php  if($row['enough']>0) { ?>
              <label class="label label-danger">满<?php  echo $row['enough'];?>可用</label>
              <?php  } else { ?>
                <label class="label label-warning">不限</label>
              <?php  } ?>
           
              <br/><?php  if($row['backtype']==0) { ?>
              立减 <?php  echo $row['deduct'];?> 元
              <?php  } else if($row['backtype']==1) { ?>
              打 <?php  echo $row['discount'];?> 折
              <?php  } else if($row['backtype']==2) { ?>
              <?php  if($row['backmoney']>0) { ?>返 <?php  echo $row['backmoney'];?> 余额;<?php  } ?>
              <?php  if($row['backcredit']>0) { ?>返 <?php  echo $row['backcredit'];?> 积分;<?php  } ?>
              <?php  if($row['backredpack']>0) { ?>返 <?php  echo $row['backredpack'];?> 红包;<?php  } ?>
              <?php  } ?>
           </td>
           
                    <td>
                                            <?php if(cv('coupon.log.view')) { ?>
                                            <a href="<?php  echo $this->createPluginWebUrl('coupon/log',array('coupon'=>$row['id']))?>">
                                                 <?php  echo $row['usetotal'];?> / <?php  echo $row['gettotal'];?> / <?php  if($row['total']==-1) { ?>无限数量<?php  } else { ?><?php  echo $row['lasttotal']?><?php  } ?>
                                            </a>
                                            <?php  } else { ?>
                                             <?php  echo $row['usetotal'];?> / <?php  echo $row['gettotal'];?> / <?php  if($row['total']==-1) { ?>无限数量<?php  } else { ?><?php  echo $row['lasttotal']?><?php  } ?>
                                            <?php  } ?>
                                      
                     <td><?php  if($row['gettype']==0) { ?>
             <label class="label label-default">不显示</label>
             <?php  } else { ?>
             
             <?php  if($row['credit']>0 || $row['money']>0) { ?>
             <?php  if($row['credit']>0) { ?><label class='label label-primary'><?php  echo $row['credit'];?> 积分</label><br/><?php  } ?>
             <?php  if($row['money']>0) { ?><label class='label label-danger'><?php  echo $row['money'];?> 现金</label><br/><?php  } ?>
             <?php  } else { ?>
             <label class='label label-warning'>免费</label>
             <?php  } ?>
           <?php  } ?>
           </td>
           <td><?php  echo $row['pwdjoins'];?> / <?php  echo $row['pwdoks'];?></td>
          <td><?php  echo date('Y-m-d',$row['createtime'])?></td>
          <td style="position:relative">
            <a href="javascript:;" data-url="<?php  echo $this->createPluginMobileUrl('coupon/detail', array('id' => $row['id']))?>"  title="复制连接" class="btn btn-default btn-sm js-clip"><i class="fa fa-link"></i></a>
            
                         <?php if(cv('coupon.coupon.edit')) { ?> 
                              <a class='btn btn-default btn-sm' href="<?php  echo $this->createPluginWebUrl('coupon/coupon/post',array('id' => $row['id']));?>" title="编辑" ><i class='fa fa-edit'></i></a>
               
                        <?php  } ?>
                        <?php if(cv('coupon.coupon.delete')) { ?> 
                              <a class='btn btn-default  btn-sm' href="<?php  echo $this->createPluginWebUrl('coupon/coupon/delete',array('id' => $row['id']));?>" title="删除" onclick="return confirm('确定要删除该优惠券吗？');"><i class='fa fa-remove'></i></a>
               
                        <?php  } ?>
                        
                       <?php if(cv('coupon.coupon.send')) { ?> 
                              <a  class='btn btn-primary  btn-sm' href="<?php  echo $this->createPluginWebUrl('coupon/send',array('couponid' => $row['id']));?>" title="发放优惠券" ><i class='fa fa-send'></i></a>
              
                        <?php  } ?>
                             </ul>
                       </div>

               
                    </td>
                </tr>
                <?php  } } ?>
            </tbody>
        </table>
        <?php  echo $pager;?>
    </div>
    <div class='panel-footer'>
                               <?php if(cv('article.page.edit')) { ?>
                          <input name="submit" type="submit" class="btn btn-default" value="提交排序">
                        <?php  } ?>
        <?php if(cv('coupon.coupon.add')) { ?>                   
                  <a class='btn btn-primary' href="<?php  echo $this->createPluginWebUrl('coupon/coupon',array('op'=>'post'))?>"><i class='fa fa-plus'></i> 添加购物优惠券</a>
    <a class='btn btn-primary' href="<?php  echo $this->createPluginWebUrl('coupon/coupon',array('op'=>'post','type'=>1))?>"><i class='fa fa-plus'></i> 添加充值优惠券</a>
        <?php  } ?>
    </div>
</div>
           </form>
<?php  } else if($operation=='post') { ?>

<form <?php if( ce('coupon.coupon.edit' ,$item) ) { ?>action="" method='post'<?php  } ?> class='form-horizontal'>
    <input type="hidden" name="id" value="<?php  echo $item['id'];?>">
    <input type="hidden" name="op" value="detail">
    <input type="hidden" name="c" value="site" />
    <input type="hidden" name="a" value="entry" />
    <input type="hidden" name="m" value="sz_yi" />
    <input type="hidden" name="p" value="coupon" />
    <input type="hidden" name="method" value="coupon" />
    <input type="hidden" name="op" value="post" />
    <div class='panel panel-default'>
        <div class='panel-heading'>
            编辑<?php  if(empty($_GPC['type'])) { ?>购物<?php  } else { ?>充值<?php  } ?>优惠券
        </div>
    
   <div class='panel-body'>
        <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">排序</label>
               <div class="col-sm-5">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input type="text" name="displayorder" class="form-control" value="<?php  echo $item['displayorder'];?>"  />
     <span class='help-block'>数字越大越靠前</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $item['displayorder'];?></div>
                    <?php  } ?>
                </div>
        </div>
   
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span> 优惠券名称</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input type="text" name="couponname" class="form-control" value="<?php  echo $item['couponname'];?>"  />
                    <?php  } else { ?>
                    <input type="hidden" name="couponname" class="form-control" value="<?php  echo $item['couponname'];?>"  />
                    <div class='form-control-static'><?php  echo $item['couponname'];?></div>
                    <?php  } ?>
                </div>
        </div>
 
      
        <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">分类</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('coupon.coupon' ,$item) ) { ?>
            <select name='catid' class='form-control'>
              <option value=''></option>
              <?php  if(is_array($category)) { foreach($category as $k => $c) { ?>
                <option value='<?php  echo $k;?>' <?php  if($item['catid']==$k) { ?>selected<?php  } ?>><?php  echo $c['name'];?></option>
               <?php  } } ?>
            </select>
                        <?php  } else { ?>
                             <div class='form-control-static'><?php  if(empty($item['catid'])) { ?>暂时无分类<?php  } else { ?> <?php  echo $category[$item['catid']]['name'];?><?php  } ?></div>
                        <?php  } ?>
                    </div>
         </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">缩略图</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('coupon.coupon' ,$item) ) { ?>
                        <?php  echo tpl_form_field_image('thumb', $item['thumb'])?>
                        <?php  } else { ?>
                        <input type="hidden" name="thumb" value="<?php  echo $item['thumb'];?>"/>
                        <?php  if(!empty($item['thumb'])) { ?>
                        <a href='<?php  echo tomedia($item['thumb'])?>' target='_blank'>
                           <img src="<?php  echo tomedia($item['thumb'])?>" style='width:100px;border:1px solid #ccc;padding:1px' />
                        </a>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
         <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">使用条件</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input type="text" name="enough" class="form-control" value="<?php  echo $item['enough'];?>"  />
                    <span class='help-block' ><?php  if(empty($_GPC['type'])) { ?>消费<?php  } else { ?>充值<?php  } ?>满多少可用, 空或0 不限制</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  if($item['enough']>0) { ?>满 <?php  echo $item['enough'];?> 可用 <?php  } else { ?>不限制<?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">使用时间限制</label>
                
                    <?php if( ce('coupon.coupon.edit' ,$item) ) { ?>
                    <div class="col-sm-5">
                    <div class='input-group'>
                        <span class='input-group-addon'>
                             <label class="radio-inline" style='margin-top:-5px;' ><input type="radio" name="timelimit" value="0" <?php  if($item['timelimit']==0) { ?>checked<?php  } ?>>获得后</label>
                        </span>
                   
                     <input type='text' class='form-control' name='timedays' value="<?php  echo $item['timedays'];?>" />
                     <span class='input-group-addon'>天内有效(空为不限时间使用)</span>
                      </div>
                     </div>
                    
                     <div class="col-sm-3">
                    <div class='input-group'>
                        <span class='input-group-addon'>
                             <label class="radio-inline" style='margin-top:-5px;' ><input type="radio" name="timelimit" value="1" <?php  if($item['timelimit']==1) { ?>checked<?php  } ?>>日期</label>
                        </span>
                         <?php  echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d', $starttime),'endtime'=>date('Y-m-d', $endtime)));?>
                          <span class='input-group-addon'>内有效</span>
                      </div>
                     </div>
                       <?php  } else { ?>
                       <div class="col-sm-9 col-xs-12">
                      <div class='form-control-static'>
              <?php  if($item['timelimit']==0) { ?>
                          <?php  if(!empty($item['timedays'])) { ?>获得后 <?php  echo $item['timedays'];?> 天内有效<?php  } else { ?>不限时间<?php  } ?>
                          <?php  } else { ?>
                          <?php  echo date('Y-m-d',$starttime)?> - <?php  echo date('Y-m-d',$endtime)?>  范围内有效
                          <?php  } ?></div>
                      </div>
                    <?php  } ?>
              
            </div>
            <?php  if(empty($_GPC['type'])) { ?>
      <?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('coupon/consume', TEMPLATE_INCLUDEPATH)) : (include template('coupon/consume', TEMPLATE_INCLUDEPATH));?>
      <?php  } else { ?>
      <?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('coupon/recharge', TEMPLATE_INCLUDEPATH)) : (include template('coupon/recharge', TEMPLATE_INCLUDEPATH));?>
      <?php  } ?>
   <?php  if(empty($_GPC['type'])) { ?>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否支持收银台使用</label>
        <div class="col-sm-9 col-xs-12" >
          <?php if( ce('coupon.coupon' ,$item) ) { ?>
            <label class="radio-inline">
                <input type="radio" name="getcashier" value="0" <?php  if($item['getcashier'] == 0) { ?>checked="true"<?php  } ?>  onclick="$('.getcashierarea').hide()"/> 否
            </label>
            <label class="radio-inline">
                <input type="radio" name="getcashier" value="1" <?php  if($item['getcashier'] == 1) { ?>checked="true"<?php  } ?> onclick="$('.getcashierarea').show()" /> 是
            </label>
            <span class='help-block' style="color:red">注:选择"是"之后,则只支持收银台使用， 如不继续选择指定商户,则默认支持所有收银台使用; 如选择"否",则不支持收银台使用!</span>
          <?php  } else { ?> 
            <div class='form-control-static'>
               <?php  if($item['getcashier']==1) { ?>是<?php  } else { ?>否<?php  } ?>
            </div>
          <?php  } ?>
        </div>

    </div>

    <div class="form-group">
      <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-sm-7 getcashier getcashierarea" <?php  if($item['getcashier']!=1) { ?>style='display:none'<?php  } ?>>
          <div class='input-group'>
            <div id="cashier">
            <table class="table">
              <tbody id="param-itemscashier">
              <?php  if(is_array($item['cashiersids'])) { foreach($item['cashiersids'] as $k => $v) { ?>
              <tr>
                  <td>
                      <a href="javascript:;" class="fa fa-move" title="拖动调整此显示顺序" ><i class="fa fa-arrows"></i></a>&nbsp;
                      <a href="javascript:;" onclick="deleteParam(this)" style="margin-top:10px;"  title="删除"><i class='fa fa-times'></i></a>
                  </td>
                  <td colspan="2">
                      <input type="hidden" class="form-control" name="cashiersids[]" data-id="<?php  echo $v;?>" data-name="cashiersids"  value="<?php  echo $v;?>" style="width:200px;float:left"  />
                   
                      <input class="form-control" type="text" data-id="<?php  echo $v;?>" data-name="cashiersnames" name="cashiersnames[]"  value="<?php  echo $item['cashiersnames'][$k];?>" style="width:200px;float:left">
                      <span class="input-group-btn">
                          <button class="btn btn-default nav-link-cashier" type="button" data-id="<?php  echo $v;?>" >选择商户</button>
                      </span>
                  </td>
              </tr>
              <?php  } } ?>               
              </tbody>
              <tbody>
                  <tr>
                      <td colspan="3">
                          <a href="javascript:;" id='add-param_cashier' onclick="addParam('cashier')" style="margin-top:10px;" class="btn btn-primary"  title="添加商户"><i class='fa fa-plus'></i> 添加商户</a>
                      </td>
                  </tr>
              </tbody>
            </table> 
              </div>
          </div> 
        </div>　
    </div>  

  <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否指定核销门店</label>
        <div class="col-sm-9 col-xs-12" >
          <?php if( ce('coupon.coupon' ,$item) ) { ?>
            <label class="radio-inline">
                <input type="radio" name="getstore" value="0" <?php  if($item['getstore'] == 0) { ?>checked="true"<?php  } ?>  onclick="$('.getstorearea').hide()"/> 否
            </label>
            <label class="radio-inline">
                <input type="radio" name="getstore" value="1" <?php  if($item['getstore'] == 1) { ?>checked="true"<?php  } ?> onclick="$('.getstorearea').show()" /> 是
            </label>
            <span class='help-block' style="color:red">注:选择"是"之后, 如不继续选择指定门店,则默认支持所有门店使用!</span>
          <?php  } else { ?> 
            <div class='form-control-static'>
               <?php  if($item['getstore']==1) { ?>是<?php  } else { ?>否<?php  } ?>
            </div>
          <?php  } ?>
        </div>

    </div>

   <div class="form-group">
      <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-sm-7 getstore getstorearea" <?php  if($item['getstore']!=1) { ?>style='display:none'<?php  } ?>>
          <div class='input-group'>
            <div id="store">
            <table class="table">
              <tbody id="param-itemsstore">
              <?php  if(is_array($item['storeids'])) { foreach($item['storeids'] as $k => $v) { ?>
              <tr>
                  <td>
                      <a href="javascript:;" class="fa fa-move" title="拖动调整此显示顺序" ><i class="fa fa-arrows"></i></a>&nbsp;
                      <a href="javascript:;" onclick="deleteParam(this)" style="margin-top:10px;"  title="删除"><i class='fa fa-times'></i></a>
                  </td>
                  <td colspan="2">
                      <input type="hidden" class="form-control" name="storeids[]" data-id="<?php  echo $v;?>" data-name="storeids"  value="<?php  echo $v;?>" style="width:200px;float:left"  />
                   
                      <input class="form-control" type="text" data-id="<?php  echo $v;?>" data-name="storenames" name="storenames[]"  value="<?php  echo $item['storenames'][$k];?>" style="width:200px;float:left">
                      <span class="input-group-btn">
                          <button class="btn btn-default nav-link-store" type="button" data-id="<?php  echo $v;?>" >选择门店</button>
                      </span>
                  </td>
              </tr>
              <?php  } } ?>               
              </tbody>
              <tbody>
                  <tr>
                      <td colspan="3">
                          <a href="javascript:;" id='add-param_store' onclick="addParam('store')" style="margin-top:10px;" class="btn btn-primary"  title="添加门店"><i class='fa fa-plus'></i> 添加门店</a>
                      </td>
                  </tr>
              </tbody>
            </table> 
              </div>
          </div> 
        </div>　
    </div>      
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否指定供应商</label>
        <div class="col-sm-9 col-xs-12" >
          <?php if( ce('coupon.coupon' ,$item) ) { ?>
            <label class="radio-inline">
                <input type="radio" name="getsupplier" value="0" <?php  if($item['getsupplier'] == 0) { ?>checked="true"<?php  } ?>  onclick="$('.getsupplierarea').hide()"/> 否
            </label>
            <label class="radio-inline">
                <input type="radio" name="getsupplier" value="1" <?php  if($item['getsupplier'] == 1) { ?>checked="true"<?php  } ?> onclick="$('.getsupplierarea').show()" /> 是
            </label>
            <span class='help-block' style="color:red">注:选择"是"之后, 如不继续选择指定供应商,则默认支持所有供应商使用!</span>
          <?php  } else { ?> 
            <div class='form-control-static'>
               <?php  if($item['getsupplier']==1) { ?>是<?php  } else { ?>否<?php  } ?>
            </div>
          <?php  } ?>
        </div>

    </div>

   <div class="form-group">
      <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-sm-7 getsupplier getsupplierarea" <?php  if($item['getsupplier']!=1) { ?>style='display:none'<?php  } ?>>
          <div class='input-group'>
            <div id="supplier">
            <table class="table">
              <tbody id="param-itemssupplier">
              <?php  if(is_array($item['supplierids'])) { foreach($item['supplierids'] as $k => $v) { ?>
              <tr>
                  <td>
                      <a href="javascript:;" class="fa fa-move" title="拖动调整此显示顺序" ><i class="fa fa-arrows"></i></a>&nbsp;
                      <a href="javascript:;" onclick="deleteParam(this)" style="margin-top:10px;"  title="删除"><i class='fa fa-times'></i></a>
                  </td>
                  <td colspan="2">
                      <input type="hidden" class="form-control" name="supplierids[]" data-id="<?php  echo $v;?>" data-name="supplierids"  value="<?php  echo $v;?>" style="width:200px;float:left"  />
                   
                      <input class="form-control" type="text" data-id="<?php  echo $v;?>" data-name="suppliernames" name="suppliernames[]"  value="<?php  echo $item['suppliernames'][$k];?>" style="width:200px;float:left">
                      <span class="input-group-btn">
                          <button class="btn btn-default nav-link-supplier" type="button" data-id="<?php  echo $v;?>" >选择供应商</button>
                      </span>
                  </td>
              </tr>
              <?php  } } ?>               
              </tbody>
              <tbody>
                  <tr>
                      <td colspan="3">
                          <a href="javascript:;" id='add-param_supplier' onclick="addParam('supplier')" style="margin-top:10px;" class="btn btn-primary"  title="添加供应商"><i class='fa fa-plus'></i> 添加供应商</a>
                      </td>
                  </tr>
              </tbody>
            </table> 
              </div>
          </div> 
        </div>
        </div>　
        

    <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">适用范围</label>
                    <div class="col-sm-9 col-xs-12">
              <input type="hidden" name="coupontype" value="0"/>
                        <?php if( ce('coupon.coupon' ,$item) ) { ?>
                         <label class="radio-inline " ><input type="radio" name="usetype" onclick='showusetype(0)' value="0" <?php  if($item['usetype']==0) { ?>checked<?php  } ?>>全类适用</label>
                         <label class="radio-inline"><input type="radio" name="usetype" onclick='showusetype(1)' value="1" <?php  if($item['usetype']==1) { ?>checked<?php  } ?>>指定商品分类</label>
                         <label class="radio-inline "><input type="radio" name="usetype" onclick='showusetype(2)' value="2" <?php  if($item['usetype']==2) { ?>checked<?php  } ?>>指定商品</label>
                           <?php  } else { ?>
                          <div class='form-control-static'>
                  <?php  if($item['usetype']==0) { ?>
                  全类适用 
                  <?php  } else if($item['usetype']==1) { ?>
                  指定商品
                  <?php  } else { ?>
                  指定商品分类 
                  <?php  } ?>
                </div>
                        <?php  } ?>
                    </div>
    </div>

    <div class="form-group">
          <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
          
            <?php if( ce('coupon.coupon' ,$item) ) { ?>
              <div class="col-sm-2 usetype usetype0"  <?php  if($item['usetype']!=0) { ?>style='display:none'<?php  } ?>>
                <div class='input-group'>
                  <span class='help-block'>如选择此项,则支持商城所有商品使用!</span>
                </div>
              </div>
              <div class="col-sm-7 usetype usetype1"  <?php  if($item['usetype']!=1) { ?>style='display:none'<?php  } ?>>
                <div class='input-group'>
                      <div id="category" >
                        <table class="table">
                            <thead>
           
                            </thead>
                            <tbody id="param-itemscategory">
                            <?php  if(is_array($item['categoryids'])) { foreach($item['categoryids'] as $k => $v) { ?>
                            <tr>
                                <td>
                                    <a href="javascript:;" class="fa fa-move" title="拖动调整此显示顺序" ><i class="fa fa-arrows"></i></a>&nbsp;
                                    <a href="javascript:;" onclick="deleteParam(this)" style="margin-top:10px;"  title="删除"><i class='fa fa-times'></i></a>
                                </td>
                                <td  colspan="2">
                                          <input type="hidden" class="form-control" name="categoryids[]" data-id="<?php  echo $v;?>" data-name="categoryids"  value="<?php  echo $v;?>" style="width:200px;float:left"  />
                                       
                                          <input class="form-control" type="text" data-id="<?php  echo $v;?>" data-name="categorynames" name="categorynames[]"  value="<?php  echo $item['categorynames'][$k];?>" style="width:200px;float:left">
                                          <span class="input-group-btn">
                                              <button class="btn btn-default nav-link" type="button" data-id="<?php  echo $v;?>" >选择分类</button>
                                          </span>
                                       
                                    
                                </td>
                               
                            </tr>
                            <?php  } } ?>               
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="3">
                                        <a href="javascript:;" id='add-param_category' onclick="addParam('category')" style="margin-top:10px;" class="btn btn-primary"  title="添加分类"><i class='fa fa-plus'></i> 添加分类</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table> 
                    </div>
                </div> 
                </div> 
                    <div class="col-sm-7 usetype usetype2"  <?php  if($item['usetype']!=2) { ?>style='display:none'<?php  } ?>>
                      <div class='input-group'>
    
                            <div id="goods">
                              <table class="table">
                                   <tbody id="param-itemsgoods">
                                  <?php  if(is_array($item['goodsids'])) { foreach($item['goodsids'] as $k => $v) { ?>
                                   <tr>
                                      <td>
                                          <a href="javascript:;" class="fa fa-move" title="拖动调整此显示顺序" ><i class="fa fa-arrows"></i></a>&nbsp;
                                          <a href="javascript:;" onclick="deleteParam(this)" style="margin-top:10px;"  title="删除"><i class='fa fa-times'></i></a>
                                      </td>
                                      <td  colspan="2">
                                          <input type="hidden" class="form-control" name="goodsids[]" data-id="<?php  echo $v;?>" data-name="goodsids"  value="<?php  echo $v;?>" style="width:200px;float:left"  />
                                       
                                          <input class="form-control" type="text" data-id="<?php  echo $v;?>" data-name="goodsnames" name="goodsnames[]"  value="<?php  echo $item['goodsnames'][$k];?>" style="width:200px;float:left">
                                          <span class="input-group-btn">
                                              <button class="btn btn-default nav-link-goods" type="button" data-id="<?php  echo $v;?>" >选择商品</button>
                                          </span>
                                      </td>
                                  </tr>
                                  <?php  } } ?>               
                                  </tbody>
                                     
                                  <tbody>
                                      <tr>
                                          <td colspan="3">
                                              <a href="javascript:;" id='add-param_goods' onclick="addParam('goods')" style="margin-top:10px;" class="btn btn-primary"  title="添加商品"><i class='fa fa-plus'></i> 添加商品</a>
                                          </td>
                                      </tr>
                                  </tbody>
                                  
                              </table> 

                          </div>
                      </div>
                         
                    </div>　
                    <?php  } else { ?>
                      <div class='form-control-static'>
                        <?php  if($item['usetype']==0) { ?>
                        支持商城所有商品使用!
                        <?php  } else if($item['usetype']==1) { ?>
                        
                        <?php  } else if($item['usetype']==2) { ?>

                        <?php  } ?>
                      </div>
                  <?php  } ?>
</div>
<?php  } ?>

<div id="goods" style="display: none"></div>




<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">领券中心是否可获得</label>
    <div class="col-sm-9 col-xs-12" >
          <?php if( ce('coupon.coupon' ,$item) ) { ?>
        <label class="radio-inline">
            <input type="radio" name="gettype" value="0" <?php  if($item['gettype'] == 0) { ?>checked="true"<?php  } ?>  onclick="$('.gettype').hide()"/> 不可以
        </label>
              <label class="radio-inline">
            <input type="radio" name="gettype" value="1" <?php  if($item['gettype'] == 1) { ?>checked="true"<?php  } ?> onclick="$('.gettype').show()" /> 可以
        </label>
      <span class='help-block'>会员是否可以在领券中心直接领取或购买</span>
    
          <?php  } else { ?> <div class='form-control-static'>
             <?php  if($item['gettype']==1) { ?>可以<?php  } else { ?>不可以<?php  } ?>
          </div>
          <?php  } ?>
    </div>
</div>

       <div class="form-group gettype" <?php  if($item['gettype']!=1) { ?>style="display:none"<?php  } ?>>
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-6">
                              <?php if( ce('coupon.coupon' ,$item) ) { ?>
                            <div class="input-group">
        <span class="input-group-addon">每个限领</span>
        <input type='text' class='form-control' value="<?php  echo $item['getmax'];?>" name='getmax' style="width: 80px" />
                                <span class="input-group-addon">张 消耗</span>
                             <input style="width: 80px"  type='text' class='form-control' value="<?php  echo $item['credit'];?>" name='credit'/>
                             <span class="input-group-addon">积分 + 花费</span>
                                <input style="width: 80px"  type='text' class='form-control' value="<?php  echo $item['money'];?>" name='money'/>
                              <span class="input-group-addon">元&nbsp;&nbsp;
                                  <label class="checkbox-inline" style='margin-top:-8px;'>
                                    <input type="checkbox" name='usecredit2' value="1" <?php  if($item['usecredit2']==1) { ?>checked<?php  } ?> /> 优先使用余额支付
                                </label>
                              </span></div>
                              <span class="help-block">每人限领，空不限制，领取方式可任意组合，可以单独积分兑换，单独现金兑换，或者积分+现金形式兑换, 如果都为空，则可以免费领取</span>
                                                       <?php  } else { ?>
                             <div class='form-control-static'>消耗 <?php  echo $item['credit'];?> 积分 花费 <?php  echo $item['money'];?> 元现金</div>
                             <?php  } ?> 
                             </div>
      
                    </div>
    　                    
         <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">发放总数</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input type="text" name="total" class="form-control" value="<?php  echo $item['total'];?>"  />
                    <span class='help-block' >优惠券总数量，没有不能领取或发放,-1 为不限制张数</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  if($item['total']==-1) { ?>无限数量<?php  } else { ?>剩余 <?php  echo $item['total'];?> 张<?php  } ?></div>
                    <?php  } ?>
                </div>
   </div>
   
　    </div>  
    　
        <div class='panel-heading'>
            使用说明
        </div>
        <div class='panel-body'>
      
          <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否使用统一说明 </label>
                <div class="col-sm-9 col-xs-12">
                   <?php if( ce('coupon.coupon' ,$item) ) { ?>
           <label class="radio-inline" >
          <input type="radio" name="descnoset" value="0" <?php  if($item['descnoset'] == 0) { ?>checked="true"<?php  } ?> /> 使用
        </label>
         
                         <label class="radio-inline">
          <input type="radio" name="descnoset" value="1" <?php  if($item['descnoset'] == 1) { ?>checked="true"<?php  } ?> /> 不使用
        </label>
           <span class='help-block'>统一说明在<a href="<?php  echo $this->createPluginWebUrl('coupon/set')?>" target='_blank'>【基础设置】</a>中设置，如果使用统一说明，则在优惠券说明前面显示统一说明</span>
            <?php  } else { ?>
            
            <div class='form-control-static'>
              <?php  if($item['descnoset']==0) { ?>
              使用
              <?php  } else if($item['descnoset']==1) { ?>
             不使用
              <?php  } else { ?>
              <?php  } ?>
            </div>
            <?php  } ?>
                </div>
            </div>
      
            
      <div class="form-group">
  <label class="col-xs-12 col-sm-3 col-md-2 control-label">使用说明</label>
  <div class="col-sm-9 col-xs-12">
                  <?php if( ce('coupon.coupon' ,$item) ) { ?>
                            <?php  echo tpl_ueditor('desc',$item['desc'])?>
                            <?php  } else { ?>
                            <textarea id='desc' style='display:none'><?php  echo $item['desc'];?></textarea>
                            <a href='javascript:preview_html("#desc")' class="btn btn-default">查看内容</a>
                            <?php  } ?>
  </div>
    </div>
    </div>    
               <div class='panel-heading'>
            推送消息 (发放或用户从领券中心获得后的消息推送，如果标题为空就不推送消息)
        </div>
      <div class='panel-body'>
      
        
          <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">推送标题</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input type="text" name="resptitle" class="form-control" value="<?php  echo $item['resptitle'];?>"  />
      <span class="help-block">变量 [nickname] 会员昵称 [total] 优惠券张数</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $item['resptitle'];?></div>
                    <?php  } ?>
                </div>
            </div>
          <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">推送封面</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('coupon.coupon' ,$item) ) { ?>
                        <?php  echo tpl_form_field_image('respthumb', $item['respthumb'])?>
                        <?php  } else { ?>
                        <input type="hidden" name="respthumb" value="<?php  echo $item['respthumb'];?>"/>
                        <?php  if(!empty($item['thumb'])) { ?>
                        <a href='<?php  echo tomedia($item['respthumb'])?>' target='_blank'>
                           <img src="<?php  echo tomedia($item['respthumb'])?>" style='width:100px;border:1px solid #ccc;padding:1px' />
                        </a>
                        <?php  } ?>
                        <?php  } ?>
                    </div>
                </div>
        
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">推送说明</label>
                <div class="col-sm-9 col-xs-12">
                     <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <textarea name="respdesc" class='form-control'><?php  echo $item['respdesc'];?></textarea>
            <span class="help-block">变量 [nickname] 会员昵称 [total] 优惠券张数</span>
                       <?php  } else { ?>
                      <div class='form-control-static'><?php  echo $item['respdesc'];?></div>
                    <?php  } ?>
                </div>
            </div>
          <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">推送连接</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input type="text" name="respurl" class="form-control" value="<?php  echo $item['respurl'];?>"  />
          <span class='help-block'>消息推送点击的连接，为空默认为优惠券详情</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $item['respurl'];?></div>
                    <?php  } ?>
                </div>
            </div>  
      </div>
    
    
  <div class='panel-heading'>
            口令玩法 (用户发送关键词猜取优惠券)
        </div>
  <div class='panel-body'>
    
    <div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否开启口令玩法</label>
    <div class="col-sm-9 col-xs-12" >
          <?php if( ce('coupon.coupon' ,$item) ) { ?>
        <label class="radio-inline">
            <input type="radio" name="pwdopen" value="0" <?php  if($item['pwdopen'] == 0) { ?>checked="true"<?php  } ?> onclick="$('.couponkey').hide()"  /> 关闭
        </label>
              <label class="radio-inline">
            <input type="radio" name="pwdopen" value="1" <?php  if($item['pwdopen'] == 1) { ?>checked="true"<?php  } ?> onclick="$('.couponkey').show()"  /> 开启
        </label>
          <?php  } else { ?> 
   <div class='form-control-static'>
             <?php  if($item['pwdopen']==1) { ?>开启<?php  } else { ?>关闭<?php  } ?>
          </div>
          <?php  } ?>
    </div>
</div>
    
  <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">开始活动关键词</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input type="text" name="pwdkey" class="form-control" value="<?php  echo $item['pwdkey'];?>"  />
      <span class="help-block">从平台获取优惠券的回复关键词,如果设置关键词为空，则不使用口令玩法，如果更换关键词，则表示开启另一轮活动</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $item['pwdkey'];?></div>
                    <?php  } ?>
                </div>
            </div>
   <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">口令集</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                      <textarea name="pwdwords" class='form-control'><?php  echo $item['pwdwords'];?></textarea>
      <span class="help-block">可以多个口令, 用半角逗号隔开,口令不要与其他系统关键词重复</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $item['pwdwords'];?></div>
                    <?php  } ?>
                </div>
            </div>
    
       <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">每人猜测机会</label>
                <div class="col-sm-9 col-xs-12">
                     <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input name="pwdtimes" class='form-control' value='<?php  echo $item['pwdtimes'];?>'>
      <span class="help-block">每人机会，空或0为不限制 </span>
                       <?php  } else { ?>
                      <div class='form-control-static'><?php  if(empty($item['pwdtimes'])) { ?>不限制<?php  } else { ?><?php  echo $item['pwdtimes'];?>次<?php  } ?></div>
            
                    <?php  } ?>
                </div>
            </div>
       <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">提示语</label>
                <div class="col-sm-9 col-xs-12">
                     <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <textarea name="pwdask" class='form-control'><?php  echo $item['pwdask'];?></textarea>
      <span class="help-block">默认: 请输入优惠券口令: </span>
      <span class='help-block'>变量: [nickname] 会员昵称 [couponname] 优惠券名称 [times] 已猜测次数 [lasttimes] 剩余猜测次数</span>
                       <?php  } else { ?>
                      <div class='form-control-static'><?php  if(empty($item['pwdask'])) { ?>请输入优惠券口令:<?php  } else { ?><?php  echo $item['pwdask'];?><?php  } ?></div>
            
                    <?php  } ?>
                </div>
            </div>
  
     <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">猜中提示语</label>
                <div class="col-sm-9 col-xs-12">
                     <?php if( ce('coupon.coupon' ,$item) ) { ?>
                       <textarea name="pwdsuc" class='form-control'><?php  echo $item['pwdsuc'];?></textarea>
         <span class="help-block">默认: 恭喜你，猜中啦！优惠券已发到您账户了!</span>
       <span class='help-block'>变量: [nickname] 会员昵称 [couponname] 优惠券名称 [times] 已猜测次数 [lasttimes] 剩余猜测次数</span>
                       <?php  } else { ?>
                      <div class='form-control-static'><?php  if(empty($item['pwdsuc'])) { ?>恭喜你，猜中啦！优惠券已发到您账户了!<?php  } else { ?><?php  echo $item['pwdsuc'];?><?php  } ?></div>
            
                    <?php  } ?>
                </div>
            </div>
  
    <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">猜错提示语</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <textarea name="pwdfail" class='form-control'><?php  echo $item['pwdfail'];?></textarea>
      <span class='help-block'>默认: 很抱歉，您猜错啦，继续猜~</span>
      <span class='help-block'>变量: [nickname] 会员昵称 [couponname] 优惠券名称 [times] 已猜测次数 [lasttimes] 剩余猜测次数</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  if(empty($item['pwdfail'])) { ?>很抱歉，您猜错啦，继续猜~<?php  } else { ?><?php  echo $item['pwdfail'];?><?php  } ?></div>
      
                    <?php  } ?> 
                </div>
            </div>  
     <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">猜测次数超出限制提示</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                     <textarea name="pwdfull" class='form-control'><?php  echo $item['pwdfull'];?></textarea>
      <span class='help-block'>默认: 很抱歉，您已经没有机会啦~</span>
      <span class='help-block'>变量: [nickname] 会员昵称 [couponname] 优惠券名称 [times] 已猜测次数 [lasttimes] 剩余猜测次数</span>
                    <?php  } else { ?> 
                    <div class='form-control-static'><?php  if(empty($item['pwdfull'])) { ?>很抱歉，您已经没有机会啦~<?php  } else { ?><?php  echo $item['pwdfull'];?><?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>  
       <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">退出口令</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                    <input type="text" name="pwdexit" class="form-control" value="<?php  echo $item['pwdexit'];?>"  />
      <span class="help-block">如果设置有次数限制，用户继续猜了，可输入退出口令，默认为0</span>
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $item['pwdexit'];?></div>
                    <?php  } ?>
                </div>
            </div>
       <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">退出后提示</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                     <textarea name="pwdexitstr" class='form-control'><?php  echo $item['pwdexitstr'];?></textarea>
       <span class='help-block'>默认: 好的，等待您下次来玩!</span>
      <span class='help-block'>变量: [nickname] 会员昵称 [couponname] 优惠券名称 [times] 已猜测次数 [lasttimes] 剩余猜测次数</span>
      
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  if(empty($item['pwdexitstr'])) { ?>很好的，等待您下次来玩!<?php  } else { ?><?php  echo $item['pwdexitstr'];?><?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>
    
    <div class="form-group couponkey" <?php  if(empty($item['pwdopen'])) { ?>style="display:none"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">已获得提示</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if( ce('coupon.coupon' ,$item) ) { ?>
                     <textarea name="pwdown" class='form-control'><?php  echo $item['pwdown'];?></textarea>
      <span class='help-block'>默认: 您已经参加过啦,等待下次活动吧~</span>
      <span class='help-block'>变量: [nickname] 会员昵称 [couponname] 优惠券名称 [times] 已猜测次数 [lasttimes] 剩余猜测次数</span>
                    <?php  } else { ?> 
                    <div class='form-control-static'><?php  if(empty($item['pwdown'])) { ?>您已经参加过啦,等待下次活动吧~<?php  } else { ?><?php  echo $item['pwdown'];?><?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>   
      </div>

 <div class="form-group"></div>
            <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                         <?php if( ce('coupon.coupon' ,$item) ) { ?>
                            <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1"  />
                            <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                        <?php  } ?>
                       <input type="button" name="back" onclick='history.back()' <?php if( ce('coupon.coupon' ,$item) ) { ?>style='margin-left:10px;'<?php  } ?> value="返回列表" class="btn btn-default" />
                    </div>
            </div>

                  


<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/sysset/selectgoods', TEMPLATE_INCLUDEPATH)) : (include template('web/sysset/selectgoods', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/sysset/selectcategory', TEMPLATE_INCLUDEPATH)) : (include template('web/sysset/selectcategory', TEMPLATE_INCLUDEPATH));?>    
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/sysset/selectcashier', TEMPLATE_INCLUDEPATH)) : (include template('web/sysset/selectcashier', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/sysset/selectstore', TEMPLATE_INCLUDEPATH)) : (include template('web/sysset/selectstore', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/sysset/selectsupplier', TEMPLATE_INCLUDEPATH)) : (include template('web/sysset/selectsupplier', TEMPLATE_INCLUDEPATH));?>
<script type="text/javascript">
    $(function() {
        $("#chkoption").click(function() {
            var obj = $(this);
            if (obj.get(0).checked) {
                $("#tboption").show();
                $(".trp").hide();
            }
            else {
                $("#tboption").hide();
                $(".trp").show();
            }
        });
    })

    function addParam(type) {
        var url = "<?php  echo $this->createWebUrl('shop/tpl')?>&tpl="+type;
        $.ajax({
            "url": url,
            success: function(data) {
                $('#param-items'+type).append(data);
            }
        });
        return;
    }
    function deleteParam(o) {
        $(o).parent().parent().remove();
    }
    function saveadd(o) {
        $(o).parent().parent().remove();
    }
</script>

            
           


        </div>


    </div>   
   
</form>
<script language='javascript'>
    
    function showbacktype(type){
 
        $('.backtype').hide();
        $('.backtype' + type).show();
    }        

    function showusetype(type){
       
        $('.usetype').hide();
        $('.usetype' + type).show();
    }
  $(function(){
    
    $('form').submit(function(){
      
      if($(':input[name=couponname]').isEmpty()){
        Tip.focus($(':input[name=couponname]'),'请输入优惠券名称!');
        return false;
      }
      var backtype = $(':radio[name=backtype]:checked').val();
      if(backtype=='0'){
        if($(':input[name=deduct]').isEmpty()){
          Tip.focus($(':input[name=deduct]'),'请输入立减多少!');
          return false;
        }
      }else if(backtype=='1'){
        if($(':input[name=discount]').isEmpty()){
          Tip.focus($(':input[name=discount]'),'请输入折扣多少!');
          return false;
        }
      }else if(backtype=='2'){
        if($(':input[name=backcredit]').isEmpty() && $(':input[name=backmoney]').isEmpty() && $(':input[name=backredpack]').isEmpty()){
          Tip.focus($(':input[name=backcredit]'),'至少输入一种返利!');
          return false;
        }
      }
      return true;
    })
    
  })
</script>
  
<?php  } ?>
</div>
</div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>
