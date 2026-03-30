<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
    <?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/shop/dispatch_tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/shop/dispatch_tabs', TEMPLATE_INCLUDEPATH));?>
    <div class="rightlist">
        <?php  if($operation == 'post') { ?>
        <script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/cascade_street.js"></script>
        <div class="main">
            <form <?php if( ce('verify.store' ,$item) ) { ?>action="" method="post"<?php  } ?> class="form-horizontal form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php  echo $item['id'];?>" />
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    核销门店设置
                </div>
                <div class='panel-body'>
                    <?php  if(p('supplier')) { ?>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">绑定供应商</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <select name="supplier_uid" class="form-control">
                                <?php  if(is_array($supplier)) { foreach($supplier as $row) { ?>
                                <option value="0">无</option>
                                <option value="<?php  echo $row['uid'];?>" <?php  if($row['uid'] == $item['supplier_uid']) { ?>selected="true"<?php  } ?>><?php  echo $row['username'];?></option>
                                <?php  } } ?>
                            </select>
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  echo $item['username'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <?php  } ?>
                    <?php  if($id && p('cashier')) { ?>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">绑定收银台</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <select name="cashierid" class="form-control">
                                <?php  if(is_array($cashier)) { foreach($cashier as $row) { ?>
                                    <option value="<?php  echo $row['id'];?>" <?php  if($row['id'] == $item['cashierid']) { ?>selected="true"<?php  } ?>><?php  echo $row['name'];?></option>
                                <?php  } } ?>
                            </select>
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  echo $item['storename'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <?php  } ?>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span> 门店名称</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <input type="text" name="storename" class="form-control" value="<?php  echo $item['storename'];?>" />
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  echo $item['storename'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">门店图片</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <?php  echo tpl_form_field_image('thumb', $item['thumb'])?>
                            <span class="help-block">建议尺寸: 100*100，或正方型图片 </span>
                            <?php  } else { ?>
                            <?php  if(!empty($item['thumb'])) { ?>
                            <a href='<?php  echo tomedia($item['thumb'])?>' target='_blank'>
                            <img src="<?php  echo tomedia($item['thumb'])?>" style='width:100px;border:1px solid #ccc;padding:1px' />
                            </a>
                            <?php  } ?>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span>门店分类</label>
                        <div class="col-sm-8 col-xs-12">

                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <?php  echo tpl_form_field_category_level2('category', $parent, $children, $item['pcate'], $item['ccate'])?>
                            <?php  } else { ?>
                            <div class='form-control-static'>
                                <?php  echo pdo_fetchcolumn('select name from '.tablename('sz_yi_store_category').' where id=:id limit 1',array(':id'=>$item['pcate']))?> -
                                <?php  echo pdo_fetchcolumn('select name from '.tablename('sz_yi_store_category').' where id=:id limit 1',array(':id'=>$item['ccate']))?>
                            </div>
                            <?php  } ?>

                        </div>
                    </div>
                    <!--<div class="form-group">-->
                        <!--<label class="col-xs-12 col-sm-3 col-md-2 control-label">门店地址</label>-->
                        <!--<div class="col-sm-9 col-xs-12">-->
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <!--<input type="text" name="address" class="form-control" value="<?php  echo $item['address'];?>" />-->
                            <?php  } else { ?>
                            <!--<div class='form-control-static'><?php  echo $item['address'];?></div>-->
                            <?php  } ?>
                        <!--</div>-->
                    <!--</div>-->
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">门店地址</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('shop.refundaddress' ,$item) ) { ?>
                            <p class="form-control-static ad2" id="e_address">
                                <select id="sel-provance" name="province" onChange="selectCity();" class="select form-control" style="width:130px;display:inline;">
                                    <option value="" selected="true">省/直辖市</option>
                                </select>
                                <select id="sel-city" name="city" onChange="selectcounty(0)" class="select form-control" style="width:135px;display:inline;">
                                    <option value="" selected="true">请选择</option>
                                </select>
                                <select id="sel-area" name="area" onChange="selectstreet(0)"class="select form-control" style="width:130px;display:inline;">
                                    <option value="" selected="true">请选择</option>
                                </select>
                                <select id="sel-street" name="street" class="select form-control" style="width:130px;display:inline;">
                                    <option value="" selected="true">请选择</option>
                                </select>
                                <input type="text" name="address" id="address" class="form-control" style="width:300px;display:inline;" value="<?php  echo $item['address']?>">
                            </p>
                            <?php  } ?>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style="color:red">*</span>绑定店长微信</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <input type="hidden" class="form-control" id="member_id" name="member_id" value="<?php  echo $item['member_id'];?>" />
                            <div class='input-group' style='border:none;'>
                                <input type="text" class="form-control" id="member" value="<?php  if(!empty($member)) { ?><?php  echo $member['nickname'];?><?php  } ?>" readonly />
                                <div class="input-group-btn">
                                    <button type="button" onclick="$('#modal-members').modal()" class="btn btn-default" >选择会员</button>
                                </div>
                            </div>
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  echo $item['wechat'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style="color:red">*</span>平台提成</label>
                        <div class="col-sm-6 col-xs-9">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <input type="text" name="balance" class="form-control" value="<?php  echo $item['balance'];?>" />%
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  echo $item['balance'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style="color:red">*</span>人均消费金额</label>
                        <div class="col-sm-6 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <input type="text" name="singleprice" class="form-control" value="<?php  echo $item['singleprice'];?>" /> %
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  echo $item['singleprice'];?></div>
                            <?php  } ?>
                            <span class="help-block">若无需要可不填写！</span>
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">门店电话</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <input type="text" name="tel" class="form-control" value="<?php  echo $item['tel'];?>" />
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  echo $item['tel'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">门店位置</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <?php  echo tpl_form_field_coordinate('map',array('lng'=>$item['lng'],'lat'=>$item['lat']))?>
                            <?php  } else { ?>
                            <div class='form-control-static'>lng=<?php  echo $item['lng'];?>,lat=<?php  echo $item['lat'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">门店信息</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <input type="text" name="info" class="form-control" placeholder="此处填写店铺的信息：营业时间，特殊规定等。" value="<?php  echo $item['info'];?>" />
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  echo $item['info'];?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否支持自提</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <label class='radio-inline'>
                                <input type='radio' name='myself_support' value=1' <?php  if($item['myself_support']==1) { ?>checked<?php  } ?> /> 支持
                            </label>
                            <label class='radio-inline'>
                                <input type='radio' name='myself_support' value=0' <?php  if($item['myself_support']==0) { ?>checked<?php  } ?> /> 不支持
                            </label>
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  if($item['myself_support']==1) { ?>支持<?php  } else { ?>不支持<?php  } ?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">状态</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <label class='radio-inline'>
                                <input type='radio' name='status' value=1' <?php  if($item['status']==1) { ?>checked<?php  } ?> /> 启用
                            </label>
                            <label class='radio-inline'>
                                <input type='radio' name='status' value=0' <?php  if($item['status']==0) { ?>checked<?php  } ?> /> 禁用
                            </label>
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  if($item['status']==1) { ?>启用<?php  } else { ?>禁用<?php  } ?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <?php  if(!$id && p('cashier')) { ?>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否同时创建收银台</label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <label class='radio-inline'>
                                <input type='radio' name='iswithcashier' value='0' <?php  if($item['iswithcashier']==0) { ?>checked<?php  } ?> /> 否
                            </label>
                            <label class='radio-inline'>
                                <input type='radio' name='iswithcashier' value='1' <?php  if($item['iswithcashier']==1) { ?>checked<?php  } ?> /> 是
                            </label>
                            <?php  } else { ?>
                            <div class='form-control-static'><?php  if($item['iswithcashier']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
                            <?php  } ?>
                        </div>
                    </div>
                    <?php  } ?>
                    <div class="form-group"></div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <?php if( ce('verify.store' ,$item) ) { ?>
                            <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1"  />
                            <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                            <?php  } ?>
                            <input type="button" name="back" onclick='history.back()' <?php if(cv('verify.store.add|verify.store.edit')) { ?>style='margin-left:10px;'<?php  } ?> value="返回列表" class="btn btn-default" />
                        </div>
                    </div>
                    <div id="modal-members"  class="modal fade" tabindex="-1">
                        <div class="modal-dialog" style='width: 920px;'>
                            <div class="modal-content">
                                <div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button><h3>选择会员</h3></div>
                                <div class="modal-body" >
                                    <div class="row">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="keyword" value="" id="search-member" placeholder="请输入会员名" />
                                            <span class='input-group-btn'><button type="button" class="btn btn-default" onclick="search_member();">搜索</button></span>
                                        </div>
                                    </div>
                                    <div id="module-members" style="padding-top:5px;"></div>
                                </div>
                                <div class="modal-footer"><a href="#" class="btn btn-default" data-dismiss="modal" aria-hidden="true">关闭</a></div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            </form>
        </div>
        <script language='javascript'>
            $(function(){
                cascdeInit("<?php  echo $item['province']?>","<?php  echo $item['city']?>","<?php  echo $item['area']?>","<?php  echo $item['street']?>");
            });

            $('form').submit(function(){
        if($(':input[name=storename]').isEmpty()){
            Tip.focus($(':input[name=storename]'),'请输入门店名称!');
            return false;
        }
                return true;
            })
            function search_member() {
                if ($.trim($('#search-member').val()) == '') {
                    Tip.focus('#search-member', '请输入关键词');
                    return;
                }
                $("#module-members").html("正在搜索....")
                $.get('<?php  echo $this->createPluginWebUrl('verify/store/getmembers')?>', {
        keyword: $.trim($('#search-member').val())
    }, function (dat) {
        $('#module-members').html(dat);
                });
            }
            function select_member(o) {
                $("#member_id").val(o.id);
                $("#member").val(o.nickname);
                $("#modal-members .close").click();
            }
        </script>
        <?php  } else if($operation == 'display') { ?>
        <script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/cascade.js"></script>
        <div class="panel panel-info">
            <div class="panel-body">
                <form action="" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">关键字</label>
                        <div class="col-xs-12 col-sm-8 col-lg-9">
                            <input class="form-control" name="keyword" id="" type="text" value="<?php  echo $_GPC['keyword'];?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">门店地址</label>
                        <div class="col-sm-9 col-xs-12">

                                <select id="sel-provance" name="province" onChange="selectCity();" class="select form-control" style="width:130px;display:inline;">
                                    <option value="" selected="true">省/直辖市</option>
                                </select>
                                <select id="sel-city" name="city" onChange="selectcounty(0)" class="select form-control" style="width:135px;display:inline;">
                                    <option value="" selected="true">请选择</option>
                                </select>
                                <select id="sel-area" name="area" onChange="selectstreet(0)"class="select form-control" style="width:130px;display:inline;">
                                    <option value="" selected="true">请选择</option>
                                </select>
                                <select id="sel-street" name="street" class="select form-control" style="width:130px;display:inline;">
                                    <option value="" selected="true">请选择</option>
                                </select>



                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">分类</label>
                        <div class="col-sm-8 col-xs-12">

                            <?php  echo tpl_form_field_category_level2('category', $parent, $children, $params[':pcate'], $params[':ccate'])?>

                        </div>

                        <div class="col-xs-12 col-sm-2 col-lg-2">
                            <button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
                        </div>

                    </div>

                    <div class="form-group">
                    </div>
                </form>
            </div>
        </div>
        <script language='javascript'>
            $(function(){
                cascdeInit("<?php  echo $_GPC['province']?>","<?php  echo $_GPC['city']?>","<?php  echo $_GPC['area']?>","<?php  echo $_GPC['street']?>");
            });
        </script>
        <form action="" method="post" onsubmit="return formcheck(this)">
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    核销门店设置
                </div>
                <div class='panel-body'>

                    <table class="table">
                        <thead>
                        <tr>
                            <th>门店名称</th>
                            <th>门店地址</th>
                            <th>门店电话</th>
                            <th>核销员数量</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php  if(is_array($list)) { foreach($list as $row) { ?>
                        <tr>
                            <td><?php  echo $row['storename'];?></td>
                            <td><?php  echo $row['address'];?></td>
                            <td><?php  echo $row['tel'];?></td>
                            <td><?php  echo $row['salercount'];?></td>
                            <td>
                                <?php  if($row['status']==1) { ?>
                                <span class='label label-success'>启用</span>
                                <?php  } else { ?>
                                <span class='label label-danger'>禁用</span>
                                <?php  } ?>
                            </td>
                            <td>
                                <?php if(cv('verify.store.edit|verify.store.view')) { ?><a class="btn btn-default"  href="<?php  echo $this->createPluginWebUrl('verify/stock', array('id' => $row['id']))?>">库存 <span class="caret"></span></a><?php  } ?>
                                <?php if(cv('verify.store.edit|verify.store.view')) { ?><a class='btn btn-default' href="<?php  echo $this->createPluginWebUrl('verify/store', array('op' => 'post', 'id' => $row['id']))?>"><i class='fa fa-edit'></i></a><?php  } ?>
                                <?php if(cv('verify.store.delete')) { ?><a class='btn btn-default'  href="<?php  echo $this->createPluginWebUrl('verify/store', array('op' => 'delete', 'id' => $row['id']))?>" onclick="return confirm('确认删除此门店吗？');return false;"><i class='fa fa-remove'></i></a><?php  } ?>
                            </td>

                        </tr>
                        <?php  } } ?>

                        </tbody>
                    </table>

                </div>
                <?php if(cv('verify.store.add')) { ?>
                <div class='panel-footer'>
                    <a class='btn btn-primary' href="<?php  echo $this->createPluginWebUrl('verify/store', array('op' => 'post'))?>"><i class="fa fa-plus"></i> 添加新门店</a>
                </div>
                <?php  } ?>
            </div>
        </form>
        <?php  } ?>
    </div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>
