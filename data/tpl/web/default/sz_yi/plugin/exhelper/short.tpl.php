<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('tabs', TEMPLATE_INCLUDEPATH)) : (include template('tabs', TEMPLATE_INCLUDEPATH));?>

<div class="main rightlist">
    <div class="panel panel-info">
        <div class="panel-heading">筛选</div>
        <div class="panel-body">
            <form action="./index.php" method="get" class="form-horizontal" role="form">
                <input type="hidden" name="c" value="site" />
                <input type="hidden" name="a" value="entry" />
                <input type="hidden" name="m" value="sz_yi" />
                <input type="hidden" name="do" value="plugin" />
                <input type="hidden" name="p"  value="exhelper" />
                <input type="hidden" name="method"  value="short" />
                <input type="hidden" name="op" value="display" />
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">关键字</label>
                    <div class="col-xs-12 col-sm-8 col-lg-9">
                        <input class="form-control" name="keyword" id="" type="text" value="<?php  echo $_GPC['keyword'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">状态</label>
                    <div class="col-xs-12 col-sm-8 col-lg-9">
                        <select name="status" class='form-control'>
                            <option value="1" <?php  if($_GPC['status'] != '0') { ?> selected<?php  } ?>>上架</option>
                            <option value="0" <?php  if($_GPC['status'] == '0') { ?> selected<?php  } ?>>下架</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">分类</label>
                    <div class="col-sm-8 col-xs-12">
                        <?php  if(intval($shopset['catlevel'])==3) { ?>
				        	<?php  echo tpl_form_field_category_level3('category', $parent, $children, $params[':pcate'], $params[':ccate'], $params[':tcate'])?>
				        <?php  } else { ?>
				        	<?php  echo tpl_form_field_category_level2('category', $parent, $children, $params[':pcate'], $params[':ccate'])?>
				        <?php  } ?>
                    </div>
                </div>
                   <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">简称状态</label>
                    <div class="col-xs-12 col-sm-8 col-lg-9">
                        <select name="shortstatus" class='form-control'>
                            <option value="1" <?php  if($_GPC['shortstatus'] == '1') { ?> selected<?php  } ?>>已填写</option>
                            <option value="0" <?php  if($_GPC['shortstatus'] == '0') { ?> selected<?php  } ?>>未填写</option>
                        </select>
                    </div>
                </div>
                 <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">分类</label>
                    <div class="col-sm-8 col-xs-12">
                        <button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
 
    <form action="" method="post">
    <div class="panel panel-default">
        <div class="panel-body table-responsive">
            <table class="table table-hover">
                <thead class="navbar-inner">
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th style='width:350px;'>商品</th>
                        <th>商品简称</th>
                    </tr>
                </thead>
                <tbody>
                    <?php  if(is_array($list)) { foreach($list as $item) { ?>
                    <tr>
                        <td><?php  echo $item['id'];?></td>
                        <td title="<?php  echo $item['title'];?>">
                               <?php  if(!empty($category[$item['pcate']])) { ?>
                            <span class="text-danger">[<?php  echo $category[$item['pcate']]['name'];?>]</span>
                            <?php  } ?>
                            <?php  if(!empty($category[$item['ccate']])) { ?>
                            <span class="text-info">[<?php  echo $category[$item['ccate']]['name'];?>]</span>
                            <?php  } ?>
                            <?php  if(!empty($category[$item['tcate']]) && intval($shopset['catlevel'])==3) { ?>
                            <span class="text-info">[<?php  echo $category[$item['tcate']]['name'];?>]</span>
                            <?php  } ?>
                            <br/><img src='<?php  echo tomedia($item['thumb'])?>' style='width:30px;height:30px;padding:1px;border:1px solid #ccc'/> <?php  echo $item['title'];?></td>
                          <td>
                              <?php if(cv('exhelper.short.save')) { ?>
                              		<input type="text" class="form-control" name="shorttitle[<?php  echo $item['id'];?>]" value="<?php  echo $item['shorttitle'];?>">
                              <?php  } else { ?>
                              		<?php  echo $item['shorttitle'];?>
                              <?php  } ?>
                          </td>
 
                    </tr>
                    <?php  } } ?>
                  <tr>
                    <td colspan='3'>
                           <?php if(cv('exhelper.short.save')) { ?>
	                           	<input name="submit" type="submit" class="btn btn-primary" value="批量修改商品简称">
	                           	<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                           <?php  } ?>
                    </td>
                </tr>
                
                </tbody>
            </table>
            <?php  echo $pager;?>
        </div>
    </div>
        </form>
</div>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>
