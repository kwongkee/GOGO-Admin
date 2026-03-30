<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/member/tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/member/tabs', TEMPLATE_INCLUDEPATH));?>
<div class="rightlist">
<?php  if($operation == 'post') { ?>
<!-- 新增加右侧顶部三级菜单 -->
<div class="right-titpos">
	<ul class="add-snav">
		<li class="active"><a href="#">会员管理</a></li>
		<li><a href="#">等级设置</a></li>
	</ul>
</div>
<!-- 新增加右侧顶部三级菜单结束 -->


<div class="main">

    <form <?php if( ce('member.level' ,$level) ) { ?>action="" method="post"<?php  } ?> class="form-horizontal form" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php  echo $level['id'];?>" />
        <div class='panel panel-default'>
            <div class='panel-body'>

                 <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">等级权重</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('member.level' ,$level) ) { ?>
                        <input type="text" name="level" class="form-control" value="<?php  echo $level['level'];?>" />
                        <span class='help-block'>等级权重，数字越大越高级</span>
                        <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $level['level'];?></div>
                        <?php  } ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span> 等级名称</label>
                    <div class="col-sm-9 col-xs-12">
                       <?php if( ce('member.level' ,$level) ) { ?>
                        <input type="text" name="levelname" class="form-control" value="<?php  echo $level['levelname'];?>" />
                           <?php  } else { ?>
                        <div class='form-control-static'><?php  echo $level['levelname'];?></div>
                        <?php  } ?>
                    </div>
                </div>

                  <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">升级条件</label>
                    <div class="col-sm-9 col-xs-12">
                        <?php if( ce('member.level' ,$level) ) { ?>
                            <div class='input-group'>
    							<?php  if(empty($shopset['leveltype'])) { ?>
    								  <span class='input-group-addon'>完成订单金额满</span>
    								   <input type="text" name="ordermoney" class="form-control" value="<?php  echo $level['ordermoney'];?>" />
    								   <span class='input-group-addon'>元</span>
    							<?php  } ?>
    							<?php  if($shopset['leveltype']==1) { ?> 
        							<span class='input-group-addon'>完成订单数量满</span>
                                    <input type="text" name="ordercount" class="form-control" value="<?php  echo $level['ordercount'];?>" />
                                    <span class='input-group-addon'>个</span>
    							<?php  } ?>
                                <?php  if($shopset['leveltype']==2) { ?>   
                                    <div class="col-sm-12">
                                       <input type='hidden' class='form-control' id='goodsid' name='goods_id' value="<?php  echo $level['goodsid'];?>" />
                                        <div class='input-group'>
                                            <div class='input-group-addon'  style='border:none;background:#fff;'><label class="radio-inline" style='margin-top:-3px;'> 购买指定商品</label></div>
                                            <input type='text' class='form-control' id='goods' value="<?php  if(!empty($level['goodsid'])) { ?>[<?php  echo $goods['id'];?>]<?php  echo $goods['title'];?><?php  } ?>" readonly />
                                            <div class="input-group-btn">
                                                <button type="button" onclick="$('#modal-goods').modal()" class="btn btn-default" >选择商品</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php  } ?>
                            </div>
    						<span class='help-block'>会员升级条件，不填写默认为不自动升级, 设置<a href="<?php  echo $this->createWebUrl('sysset',array('op'=>'member'))?>">【会员升级依据】</a> </span>
                         <?php  } else { ?>
                            <div class='form-control-static'>
						        <?php  if(empty($shopset['leveltype'])) { ?>
                                    <?php  if($level['ordermoney']>0) { ?>
                                      完成订单金额满 <?php  echo $level['ordermoney'];?>元
                                    <?php  } else { ?>
                                      不自动升级
                                    <?php  } ?>
                                <?php  } ?>
                                <?php  if($shopset['leveltype']==1) { ?>
                                    <?php  if($level['ordercount']>0) { ?>
                                       完成订单数量满 <?php  echo $level['ordercount'];?>个
                                    <?php  } else { ?>
                                        不自动升级
                                    <?php  } ?>
                                <?php  } ?>
                           </div>
                        <?php  } ?>
                    </div>
                </div>


                 <div class="form-group">
                     <label class="col-xs-12 col-sm-3 col-md-2 control-label">折扣</label>
                    <div class="col-sm-9 col-xs-12">
                             <?php if( ce('member.level' ,$level) ) { ?>
                        <input type="text" name="discount" class="form-control" value="<?php  echo $level['discount'];?>" />
                        <span class='help-block'>请输入0.1~10之间的数字,值为空代表不设置折扣</span>
                        <?php  } else { ?>
                        <div class='form-control-static'>
                           <?php  if(empty($level['discount'])) { ?>
                           不打折
                           <?php  } else { ?>
                           <?php  echo $level['discount'];?>折
                           <?php  } ?>
                           </div>
                        <?php  } ?>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">订单金额倍数</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="text" name="multiple" class="form-control" value="<?php  echo $level['multiple'];?>" />
                        <span class='help-block'>请输入1~100之间的数字！</span>
                    </div>
                </div>

                
                <div class="form-group"></div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                           <?php if( ce('member.level' ,$level) ) { ?>
                            <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1"  />
                            <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                        <?php  } ?>
                       <input type="button" name="back" onclick='history.back()' <?php if(cv('member.level.add|member.level.edit')) { ?>style='margin-left:10px;'<?php  } ?> value="返回列表" class="btn btn-default" />
                    </div>
                </div>
                
            </div>
        </div>
      
    </form>
</div>

<div id="modal-goods"  class="modal fade" tabindex="-1">
    <div class="modal-dialog" style='width: 920px;'>
        <div class="modal-content">
            <div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button><h3>选择商品</h3></div>
            <div class="modal-body" >
                <div class="row"> 
                    <div class="input-group"> 
                        <input type="text" class="form-control" name="keyword" value="" id="search-kwd-goods" placeholder="请输入商品名称" />
                        <span class='input-group-btn'><button type="button" class="btn btn-default" onclick="search_goods();">搜索</button></span>
                    </div>
                </div>
                <div id="module-menus-goods" style="padding-top:5px;"></div>
            </div>
            <div class="modal-footer"><a href="#" class="btn btn-default" data-dismiss="modal" aria-hidden="true">关闭</a></div>
        </div>
    </div>
</div>
<script language='javascript'>

    function search_goods() {
        if( $.trim($('#search-kwd-goods').val())==''){
             Tip.focus('#search-kwd-goods','请输入关键词');
             return;
        }
        $("#module-goods").html("正在搜索....")
        $.get('<?php  echo $this->createWebUrl('shop/query')?>', {
            keyword: $.trim($('#search-kwd-goods').val())
        }, function(dat){
            $('#module-menus-goods').html(dat);
        });
    }

    function select_good(o) {
        $("#goodsid").val(o.id);
        $("#goods").val( "[" + o.id + "]" + o.title);
        $("#modal-goods .close").click();
    }

    $('form').submit(function(){
        if($(':input[name=levelname]').isEmpty()){
            Tip.focus($(':input[name=levelname]'),'请输入等级名称!');
            return false;
        }
        return true;
    })
    </script>
<?php  } else if($operation == 'display') { ?>
<!-- 新增加右侧顶部三级菜单 -->
<div class="right-titpos">
	<ul class="add-snav">
		<li class="active"><a href="#">会员管理</a></li>
		<li><a href="#">会员等级</a></li>
	</ul>
</div>
<!-- 新增加右侧顶部三级菜单结束 -->
<form action="" method="post" onsubmit="return formcheck(this)">
     <div class='panel panel-default'>
                 <div class='panel-body'>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>等级权重</th>
                                <th>等级名称</th>
                                <th>升级条件</th>
                                <th>订单金额倍数</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php  if(is_array($list)) { foreach($list as $row) { ?>
                            <tr>
                                <td><?php  echo $row['level'];?></td>
                                <td><?php  echo $row['levelname'];?></td>
                                   <td>
                                       <?php  if(empty($shopset['leveltype'])) { ?>
        						            <?php  if($row['ordermoney']>0) { ?>
                                                完成订单金额满 <?php  echo $row['ordermoney'];?>元
                                          <?php  } else { ?>
                                                不自动升级
                                          <?php  } ?>
        						        <?php  } ?>

        			                    <?php  if($shopset['leveltype']==1) { ?>
                                             <?php  if($row['ordercount']>0) { ?>
                                                   完成订单数量满 <?php  echo $row['ordercount'];?>个
                                                <?php  } else { ?>
                                              不自动升级
                                              <?php  } ?>
                                         <?php  } ?>
                                  </td>

                                <td><?php  echo $row['multiple'];?> 倍</td>

                                <td>
                                    <?php if(cv('member.level.add|member.group.view')) { ?>
                                        <a class='btn btn-default' href="<?php  echo $this->createWebUrl('member/level', array('op' => 'post', 'id' => $row['id']))?>" title="<?php if(cv('member.level.edit')) { ?>修改<?php  } else { ?>查看<?php  } ?>"><i class='fa fa-edit'></i></a>
                                    <?php  } ?>

                                    <?php if(cv('member.level.delete')) { ?>
                                     <a class='btn btn-default'  href="<?php  echo $this->createWebUrl('member/level', array('op' => 'delete', 'id' => $row['id']))?>" onclick="return confirm('确认删除此等级吗？');return false;"><i class='fa fa-remove'></i></a></td>
                                    <?php  } ?>

                                </tr>
                            <?php  } } ?>
                         
                        </tbody>
                    </table>
                 </div>
         <?php if(cv('member.level.add')) { ?>
               <div class='panel-footer'>
                    <a class='btn btn-primary' href="<?php  echo $this->createWebUrl('member/level', array('op' => 'post'))?>"><i class="fa fa-plus"></i> 添加新等级</a>
               </div>
         <?php  } ?>
        </div>
       </form>

<?php  } ?>
</div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>
