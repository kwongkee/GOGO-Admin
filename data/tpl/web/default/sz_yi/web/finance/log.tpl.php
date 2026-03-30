<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/finance/tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/finance/tabs', TEMPLATE_INCLUDEPATH));?>
 
<div class="rightlist">
<div class="panel panel-info">
    <div class="panel-heading">筛选</div>
    <div class="panel-body">
        <form action="./index.php" method="get" class="form-horizontal" role="form" id="form1">
            <input type="hidden" name="c" value="site" />
            <input type="hidden" name="a" value="entry" />
            <input type="hidden" name="m" value="sz_yi" />
            <input type="hidden" name="do" value="finance" />
            <input type="hidden" name="p" value="log" />
            <input type="hidden" name="type" value="<?php  echo $_GPC['type'];?>" />
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">会员信息</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                    <input type="text" class="form-control"  name="realname" value="<?php  echo $_GPC['realname'];?>" placeholder='可搜索会员昵称/姓名/手机号/绑定手机号'/> 
                </div>
            </div>
             
             <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">充值单号</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                    <input type="text" class="form-control"  name="logno" value="<?php  echo $_GPC['logno'];?>" placeholder='可搜索充值单号'/> 
                </div>
            </div>
 
             <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">会员等级</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                       <select name='level' class='form-control'>
                        <option value=''></option>
                        <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
                        <option value='<?php  echo $level['id'];?>' <?php  if($_GPC['level']==$level['id']) { ?>selected<?php  } ?>><?php  echo $level['levelname'];?></option>
                        <?php  } } ?>
                    </select>
                </div>
            </div>
             <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">会员分组</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                       <select name='groupid' class='form-control'>
                        <option value=''></option>
                        <?php  if(is_array($groups)) { foreach($groups as $group) { ?>
                        <option value='<?php  echo $group['id'];?>' <?php  if($_GPC['groupid']==$level['id']) { ?>selected<?php  } ?>><?php  echo $group['groupname'];?></option>
                        <?php  } } ?>
                    </select>
                </div>
        
            </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">
                        <?php  if($_GPC['type']==1) { ?>提现时间<?php  } else { ?>充值时间<?php  } ?></label>
                      <div class="col-sm-2">
                            <label class='radio-inline'>
                                <input type='radio' value='0' name='searchtime' <?php  if($_GPC['searchtime']=='0') { ?>checked<?php  } ?>>不搜索
                            </label>
                             <label class='radio-inline'>
                                <input type='radio' value='1' name='searchtime' <?php  if($_GPC['searchtime']=='1') { ?>checked<?php  } ?>>搜索
                            </label>
                     </div>
                    <div class="col-sm-7 col-lg-7 col-xs-12">
                        <?php  echo tpl_form_field_daterange('time', array('starttime'=>date('Y-m-d H:i', $starttime),'endtime'=>date('Y-m-d  H:i', $endtime)),true);?>
                    </div>
                         
                </div>
            <?php  if($_GPC['type']==0) { ?>
               <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">充值方式</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                       <select name='rechargetype' class='form-control'>
                         <option value='' <?php  if($_GPC['rechargetype']=='') { ?>selected<?php  } ?>></option>
                         <option value='wechat' <?php  if($_GPC['rechargetype']=='wechat') { ?>selected<?php  } ?>>微信</option>
                         <option value='alipay' <?php  if($_GPC['rechargetype']=='alipay') { ?>selected<?php  } ?>>支付宝</option>
                         <option value='system' <?php  if($_GPC['rechargetype']=='system') { ?>selected<?php  } ?>>后台</option>
                         <option value='system1' <?php  if($_GPC['rechargetype']=='system1') { ?>selected<?php  } ?>>后台扣款</option>
                    </select>
                </div>
               </div>
                <?php  } ?>
                <?php  if(p('love')) { ?>
               <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">充值内容</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                       <select name='paymethod' class='form-control'>
                         <option value='' <?php  if($_GPC['paymethod']=='') { ?>selected<?php  } ?>></option>
                         <option value='0' <?php  if($_GPC['paymethod']=='0') { ?>selected<?php  } ?>>余额</option>
                         <option value='1' <?php  if($_GPC['paymethod']=='1') { ?>selected<?php  } ?>>积分</option>
                    </select>
                </div>
               </div>
                <?php  } ?>
                 <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">状态</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                       <select name='status' class='form-control'>
                         <option value='' <?php  if($_GPC['status']=='') { ?>selected<?php  } ?>></option>
                         <option value='1' <?php  if($_GPC['status']=='1') { ?>selected<?php  } ?>><?php  if($_GPC['type']==0) { ?>充值成功<?php  } else { ?>完成<?php  } ?></option>
                         <option value='0' <?php  if($_GPC['status']=='0') { ?>selected<?php  } ?>><?php  if($_GPC['type']==0) { ?>未充值<?php  } else { ?>申请中<?php  } ?></option>
                         <?php  if($_GPC['type']==1) { ?><option value='-1' <?php  if($_GPC['status']=='-1') { ?>selected<?php  } ?>>失败</option><?php  } ?>
                         
                    </select>
                </div>
               </div>
              <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label"></label>
                    <div class="col-sm-7 col-lg-9 col-xs-12">
                       <button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
                         <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                          <?php if(cv('finance.recharge.export|finance.withdraw.export')) { ?>
                        <button type="submit" name="export" value="1" class="btn btn-primary">导出 Excel</button>
                        <?php  } ?>
                    </div>
                </div>
            <div class="form-group">
            </div>
        </form>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        总数：<?php  echo $total;?>
        <!--xj 170918 s-->
        <?php  if($_GET['type']==1) { ?>
        &nbsp;&nbsp;
        提现金额：<?php  if($withdrawInfo['tot']) { ?><?php  echo $withdrawInfo['tot'];?><?php  } else { ?>0<?php  } ?>
        &nbsp;&nbsp;
        提现手续费：<?php  if($withdrawInfo['poundage']) { ?><?php  echo $withdrawInfo['poundage'];?><?php  } else { ?>0<?php  } ?>
        <?php  } ?>
        <!--xj 170918 e-->

    </div>
    <div class="panel-body ">
        <table class="table table-hover">
            <thead class="navbar-inner">
                <tr>
                    <th style='width:20%;'><?php  if($_GPC['type']==0) { ?>充值单号<?php  } else { ?>提现单号<?php  } ?></th>
					<th style='width:8%'>提现方式</th> <!-- peng 20170227-->
                    <th style='width:8%;'>粉丝</th>
                    <th style='width:18%;'>会员信息<br/>微信号</th>
                    <th style='width:8%;' class='hidden-xs'>等级/分组</th>
                    <th style='width:12%;'><?php  if($_GPC['type']==1) { ?>提现时间<?php  } else { ?>充值时间<?php  } ?></th>
                    <?php  if($_GPC['type']==0) { ?><th style='width:12%;'>充值方式</th><?php  } ?>
                    <th style='width:12%;'><?php  if($_GPC['type']==1) { ?>提现<?php  } else { ?>充值<?php  } ?>状态<?php  if($_GPC['type']==1) { ?>&&手续费<?php  } ?></th>
                    <th style='width:12%;'>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php  if(is_array($list)) { foreach($list as $row) { ?>
                <tr>
                     <td><?php  if(!empty($row['logno'])) { ?>
                                <?php  if(strlen($row['logno'])<=22) { ?>
                                <?php  echo $row['logno'];?>
                                <?php  } else { ?>
                                recharge<?php  echo $row['id'];?>
                                <?php  } ?>
                         <?php  } else { ?>
                         recharge<?php  echo $row['id'];?>
                         <?php  } ?></td>
					<td><?php  if($row['pay_mode'] == 2) { ?>微信提现<?php  } ?><?php  if($row['pay_mode'] == 4) { ?>支付宝提现<?php  } ?></td> <!-- peng 20170227-->
                    <td><img src='<?php  echo $row['avatar'];?>' style='width:30px;height:30px;padding1px;border:1px solid #ccc' /><br/> <?php  echo $row['nickname'];?></td>
					<!-- lotodo 20170401 <td><?php  if($row['pay_mode'] == 2) { ?><?php  echo $row['realname'];?><br/><?php  echo $row['mobile'];?><br/><?php  echo $row['weixin'];?><?php  } ?><?php  if($row['pay_mode'] ==4) { ?><?php  echo $row['diymemberdata']['diyzhifubaozhanghao'];?><br/><?php  echo $row['diymemberdata']['diyzhanghaoxingming'];?><?php  } ?></td> -->
                    <td><?php  if($row['pay_mode'] == 2) { ?><?php  echo $row['realname'];?><br/><?php  echo $row['mobile'];?><br/><?php  echo $row['weixin'];?><?php  } ?><?php  if($row['pay_mode'] ==4) { ?><?php  echo $row['alipay'];?><br/><?php  echo $row['alipayname'];?><?php  } ?></td>
                    <td><?php  echo $row['realname'];?><br/><?php  echo $row['mobile'];?><br/><?php  echo $row['weixin'];?></td>
                    <td class='hidden-xs'><?php  if(empty($row['levelname'])) { ?>普通会员<?php  } else { ?><?php  echo $row['levelname'];?><?php  } ?><br/><?php  if(empty($row['groupname'])) { ?>无分组<?php  } else { ?><?php  echo $row['groupname'];?><?php  } ?></td>

                    <td><?php  echo date('Y-m-d',$row['createtime'])?><br/><?php  echo date('H:i',$row['createtime'])?></td>
                                            
    <?php  if($_GPC['type']==0) { ?>
    <td> 
      <?php  if($row['aging']) { ?>
         分期共<?php  echo $row['aging']['num'];?><br/>
         已充值<?php echo $row['aging']['qtotal'] * $row['aging']['phase'] > $row['aging']['num'] ? $row['aging']['num'] : number_format($row['aging']['qtotal'] * $row['aging']['phase'],2)?><?php echo $row['aging']['paymethod'] == 0 ? " 元" : " 积分"?>
      <?php  } else { ?>
         <?php  echo $row['money'];?>
      <?php  } ?>
         <br/>
        <?php  if($row['rechargetype']=='alipay') { ?>
        <span class='label label-warning'>支付宝</span>
        <?php  } else if($row['rechargetype']=='wechat') { ?>
        <span class='label label-success'>微信</span>
         <?php  } else if($row['rechargetype']=='system') { ?>
         <?php  if($row['money']>0) { ?>
        <span class='label label-primary'>后台</span>
        <?php  } else { ?>
        <span class='label label-default'>后台扣款</span>
        <?php  } ?>
        
        <?php  } ?>
    </td>
    <?php  } ?>
                    
                    <td>
                        <?php  if($row['aging']) { ?>
                           <?php  echo $row['money'];?>(第<?php  echo $row['aging']['phase'];?>期)<br/>
                           共<?php  echo $row['aging']['qnum'];?>期
                        <?php  } else { ?>
                           <?php  echo $row['money'];?>
                           <?php  if($_GPC['type']==1) { ?>/手续费：<?php  if($row['poundage'] > 0) { ?><?php  echo $row['poundage'];?><?php  } else { ?>0<?php  } ?><?php  } ?>
                        <?php  } ?>
                        <br/>
                        <?php  if($row['status']==0) { ?>
                        <span class='label label-default'><?php  if($row['type']==1) { ?>申请中<?php  } else { ?>未充值<?php  } ?></span>
                        <?php  } else if($row['status']==1) { ?>
                        <span class='label label-success'><?php  if($row['type']==1) { ?>成功<?php  } else { ?>充值成功<?php  } ?></span>
                        <?php  } else if($row['status']==-1) { ?>
                        <span class='label label-warning'><?php  if($row['type']==-1) { ?>失败<?php  } ?></span>
                          <?php  } else if($row['status']==3) { ?>
                        <span class='label label-danger'><?php  if($row['type']==0) { ?>充值退款<?php  } ?></span>
                        <?php  } ?>
                    </td> 
                    
                    <td>
                        <?php if(cv('member.member.view')) { ?>
                        <a class='btn btn-default' href="<?php  echo $this->createWebUrl('member',array('op'=>'detail','id' => $row['mid']));?>" style="margin-bottom: 2px">用户信息</a>	
                        <?php  } ?>
                        <?php  if($row['type']==0 && $row['status']==1) { ?>
                              <?php  if($row['rechargetype']=='alipay' || $row['rechargetype']=='wechat') { ?>
                                 <?php if(cv('finance.recharge.refund')) { ?>
                                          <a class='btn btn-danger' onclick="return confirm('确认退款到微信钱包?')" href="<?php  echo $this->createWebUrl('finance/log',array('op'=>'pay','paytype'=>'refund','id' => $row['id']));?>">退款</a><br>
                                 <?php  } ?>
                              <?php  } ?>
                        <?php  } ?>
                        <?php  if($row['type']==1 && $row['status']==0) { ?>
                        <?php if(cv('finance.withdraw.withdraw')) { ?>
                         <?php  if($set['pay']['weixin']==1) { ?>
                        <a class='btn btn-default' style="margin-bottom: 2px" onclick="return confirm('确认微信钱包提现?')" href="<?php  echo $this->createWebUrl('finance/log',array('op'=>'pay','paytype'=>'wechat','id' => $row['id']));?>" >微信提现</a>
                          <?php  } ?> 
                        <?php  if($set['pay']['alipay_withdrawals']==1) { ?>
                         <a class='btn btn-default' style="margin-bottom: 2px" onclick="return confirm('确认支付宝提现?')" href="<?php  echo $this->createWebUrl('finance/log',array('op'=>'pay','paytype'=>'alipay','id' => $row['id']));?>"  target= "_blank">支付宝提现</a>
                         <?php  } ?>		

                        <a class='btn btn-default' style="margin-bottom: 2px" onclick="return confirm('确认手动提现完成?')" href="<?php  echo $this->createWebUrl('finance/log',array('op'=>'pay','paytype'=>'manual','id' => $row['id']));?>">手动提现</a>		
                        <a class='btn btn-default' style="margin-bottom: 2px" onclick="return confirm('确认拒绝提现申请?')" href="<?php  echo $this->createWebUrl('finance/log',array('op'=>'pay','paytype'=>'refuse','id' => $row['id']));?>">拒绝</a>		
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
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>
