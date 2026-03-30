<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/shop/dispatch_tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/shop/dispatch_tabs', TEMPLATE_INCLUDEPATH));?>
<div class="rightlist">
 
<div class="panel panel-info">
    <div class="panel-heading">筛选</div>
    <div class="panel-body">
        <form action="./index.php" method="get" class="form-horizontal" role="form" id="form1">
            <input type="hidden" name="c" value="site" />
            <input type="hidden" name="a" value="entry" />
            <input type="hidden" name="m" value="sz_yi" />
            <input type="hidden" name="do" value="plugin" />
            <input type="hidden" name="p" value="store" />
            <input type="hidden" name="method" value="withdraw" />
            <input type="hidden" name="op" value="display" />

            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">会员信息</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                    <input type="text" class="form-control" name="name" value="<?php  echo $_GPC['name'];?>" placeholder='可搜索商户名/姓名/手机号'/> 
                </div>      F
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">提现时间</label>
                <div class="col-sm-2">
                    <label class='radio-inline'>
                        <input type='radio' value='0' name='searchtime' <?php  if($_GPC['searchtime']=='0') { ?>checked<?php  } ?>>不搜索
                    </label>
                    <label class='radio-inline'>
                        <input type='radio' value='1' name='searchtime' <?php  if($_GPC['searchtime']=='1') { ?>checked<?php  } ?>>搜索
                    </label>
                </div>
                <div class="col-sm-7 col-lg-8 col-xs-12">
                    <?php  echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d H:i', $starttime),'endtime'=>date('Y-m-d  H:i', $endtime)),true);?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">状态</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                    <select name='status' class='form-control'>
                         <option value='' <?php  if($_GPC['status']=='') { ?>selected<?php  } ?>></option>
                         <option value='0' <?php  if($_GPC['status']=='0') { ?>selected<?php  } ?>>申请中</option>
                         <option value='1' <?php  if($_GPC['status']=='1') { ?>selected<?php  } ?>>成功</option>
                         <option value='2' <?php  if($_GPC['status']=='2') { ?>selected<?php  } ?>>失败</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label"></label>
                <div class="col-sm-7 col-lg-9 col-xs-12">
                    <button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
                    <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                </div>
            </div>
        </form>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">总数：<?php  echo $total;?></div>
    <div class="panel-body ">
        <table class="table table-hover">
            <thead class="navbar-inner">
                <tr>
                    <th style='width:24%;'>提现单号</th>
                    <th style='width:18%;'>商户名</th>
                    <th style='width:12%;'>提现金额</th>
                    <th style='width:18%;'>提现时间</th>
                    <th style='width:14%;'>状态</th>
                    <th style='width:14%;'>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php  if(is_array($list)) { foreach($list as $row) { ?>
                <tr>
                    <td><?php  echo $row['withdraw_no'];?></td>
                    <td><?php  echo $row['name'];?></td>
                    <td><?php  echo $row['money'];?></td>
                    <td><?php  echo $row['create_time'];?></td>
                    <td>
                        <?php  if($row['status'] == 0) { ?>
                        <span class='label label-default'>申请中</span>
                        <?php  } else if($row['status'] == 1) { ?>
                        <span class='label label-success'>成功</span>
                        <?php  } else if($row['status'] == 2) { ?>
                        <span class='label label-warning'>失败</span>
                        <?php  } ?>
                    </td>
                    <td>
                        <?php if(cv('member.member.view')) { ?>
                        <a class='btn btn-default' href="<?php  echo $this->createWebUrl('member',array('op'=>'detail','id' => $row['member_id']));?>">用户信息</a>
                        <?php  } ?>
                        <?php  if($row['status']==0) { ?>
                        <?php if(cv('cashier.withdraw.withdraw')) { ?>
                        <a class='btn btn-default' onclick="return confirm('确认微信钱包提现?')" href="<?php  echo $this->createPluginWebUrl('verify/withdraw',array('op'=>'pay','paytype'=>'wechat','id' => $row['id']));?>">微信提现</a>
                        <a class='btn btn-default' onclick="return confirm('确认手动提现完成?')" href="<?php  echo $this->createPluginWebUrl('verify/withdraw',array('op'=>'pay','paytype'=>'manual','id' => $row['id']));?>">手动提现</a>
                        <a class='btn btn-default' onclick="return confirm('确认拒绝提现申请?')" href="<?php  echo $this->createPluginWebUrl('verify/withdraw',array('op'=>'pay','paytype'=>'refuse','id' => $row['id']));?>">拒绝</a>
                        <?php  } ?>
                        <?php  } ?>
                    </td>
                </tr>
                <?php  } } ?>
            </tbody>
        </table>
           <?php  echo $pager;?>
    </div>
</div>
</div>
 
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>
