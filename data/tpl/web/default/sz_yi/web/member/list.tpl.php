<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/member/tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/member/tabs', TEMPLATE_INCLUDEPATH));?>
<div class="rightlist">
<?php  if($operation=='display') { ?>
<!-- 新增加右侧顶部三级菜单 -->
<div class="right-titpos">
	<ul class="add-snav">
		<li class="active"><a href="#">会员管理</a></li>
		<li><a href="#">全部会员</a></li>
	</ul>
</div>
<!-- 新增加右侧顶部三级菜单结束 -->
<div class="panel panel-info">
    <div class="panel-heading">筛选</div>
    <div class="panel-body">
        <form action="./index.php" method="get" class="form-horizontal" role="form" id="form1">
            <input type="hidden" name="c" value="site" />
            <input type="hidden" name="a" value="entry" />
            <input type="hidden" name="m" value="sz_yi" />
            <input type="hidden" name="p" value="list" id="form_p" />
            <input type="hidden" name="do" value="member" id="form_do" />
                 <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">ID</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                    <input type="text" class="form-control"  name="mid" value="<?php  echo $_GPC['mid'];?>"/> 
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">会员信息</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                    <input type="text" class="form-control"  name="realname" value="<?php  echo $_GPC['realname'];?>" placeholder="可搜索昵称/姓名/手机号"/> 
                </div>
            </div>
               <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">是否关注</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                       <select name='followed' class='form-control'>
                        <option value=''></option>
                        <option value='0' <?php  if($_GPC['followed']=='0') { ?>selected<?php  } ?>>未关注</option>
                        <option value='1' <?php  if($_GPC['followed']=='1') { ?>selected<?php  } ?>>已关注</option>
                        <option value='2' <?php  if($_GPC['followed']=='2') { ?>selected<?php  } ?>>取消关注</option>
                    </select>
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
                        <option value='<?php  echo $group['id'];?>' <?php  if($_GPC['groupid']==$group['id']) { ?>selected<?php  } ?>><?php  echo $group['groupname'];?></option>
                        <?php  } } ?>
                    </select>
                </div>
        
            </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">注册时间</label>
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
	      <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">黑名单</label>
                <div class="col-sm-8 col-lg-9 col-xs-12">
                       <select name='isblack' class='form-control'>
                        <option value=''></option>
                        <option value='0' <?php  if($_GPC['isblack']=='0') { ?>selected<?php  } ?>>否</option>
                        <option value='1' <?php  if($_GPC['isblack']=='1') { ?>selected<?php  } ?>>是</option>
                    </select>
                </div>
            </div>
              <div class="form-group">
                    <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label"></label>
                    <div class="col-sm-7 col-lg-9 col-xs-12">
                       <button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
                       <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                       <?php if(cv('member.member.export')) { ?>   
                        <button type="button" name="export" value="1" id="export" class="btn btn-primary">导出 Excel</button>
                        
                        <?php  } ?>
                       
                    </div>
               </div> 
          
            
            <div class="form-group">
            </div>
        </form>
    </div>
</div><div class="clearfix">

<div class="panel panel-default">
    <div class="panel-heading">总数：<?php  echo $total;?>   </div>
    <div class="panel-body">
        <table class="table table-hover" style="overflow:visible;">
            <thead class="navbar-inner">
                <tr>
                    <th style='width:8%;text-align: center;'>会员ID</th>
		  <?php  if($opencommission) { ?>
			<th style='width:8%;text-align: center;'>推荐人</th>	
		  <?php  } ?>

                    <th style='width:8%;text-align: center;'>粉丝</th>
                    <th style='width:12%;'>姓名<br/>手机号码</th>
                    <th style='width:8%;'>等级/分组</th>
                    <th style='width:10%;'>注册时间</th>
                    <th style='width:15%;'><?php  echo SZ_YI_INTEGRAL?>/余额</th>
                    <th style='width:15%;'>成交</th>
                    <th style='width:8%'>关注</th>
                    <th style='width:8%'>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php  if(is_array($list)) { foreach($list as $row) { ?>
                <tr>
                    <td style="text-align: center;">   <?php  echo $row['id'];?></td>
		  <?php  if($opencommission) { ?>
		    <td style="text-align: center;"  <?php  if(!empty($row['agentid'])) { ?>title='ID: <?php  echo $row['agentid'];?>'<?php  } ?>>
				<?php  if(empty($row['agentid'])) { ?>
				  <?php  if($row['isagent']==1) { ?>
				      <label class='label label-primary'>总店</label>
				      <?php  } else { ?>
				       <label class='label label-default'>暂无</label>
				      <?php  } ?>
				<?php  } else { ?>
				
                    	<?php  if(!empty($row['agentavatar'])) { ?>
                         <img src='<?php  echo $row['agentavatar'];?>' style='width:30px;height:30px;padding:1px;border:1px solid #ccc' /><br/>
                       <?php  } ?>
                       <?php  if(empty($row['agentnickname'])) { ?>未更新<?php  } else { ?><?php  echo $row['agentnickname'];?><?php  } ?>
					   <?php  } ?>
                        
                    </td>
		  <?php  } ?>
		  
                    <td style="text-align: center;">
                    	<?php  if(!empty($row['avatar'])) { ?>
                         <img src='<?php  echo $row['avatar'];?>' style='width:30px;height:30px;padding:1px;border:1px solid #ccc' /><br/>
                       <?php  } ?>
                       <?php  if(empty($row['nickname'])) { ?>未更新<?php  } else { ?><?php  echo $row['nickname'];?><?php  } ?>
                        
                    </td>
                    <td><?php  echo $row['realname'];?><br/><?php  echo $row['mobile'];?></td>
                    <td><?php  if(empty($row['levelname'])) { ?>普通会员<?php  } else { ?><?php  echo $row['levelname'];?><?php  } ?>
                        <br/><?php  if(empty($row['groupname'])) { ?>无分组<?php  } else { ?><?php  echo $row['groupname'];?><?php  } ?></td>
                    <td><?php  echo date('Y-m-d',$row['createtime'])?><br/><?php  echo date('H:i',$row['createtime'])?></td>
                    <td><label class="label label-primary"><?php  echo SZ_YI_INTEGRAL?>：<?php  echo $row['credit1'];?></label><br/><label class="label label-danger">余额：<?php  echo $row['credit2'];?></label></td>
                    <td><label class="label label-primary">订单：<?php  echo $row['ordercount'];?></label><br/>
                    <label class="label label-danger">金额：<?php  echo floatval($row['ordermoney'])?></label></td>
                    <td> 
						   <?php  if($row['isblack']==1) { ?>
                    <span class="label label-default" style='color:#fff;background:black'>黑名单</span>
					<?php  } else { ?>
						<?php  if(empty($row['followed'])) { ?>
                        <?php  if(empty($row['uid'])) { ?>
                        <label class='label label-default'>未关注</label>
                        <?php  } else { ?>
                        <label class='label label-warning'>取消关注</label>
                        <?php  } ?>
                        <?php  } else { ?>
                    <label class='label label-success'>已关注</label>    
                    <?php  } ?><?php  } ?>
					
					</td>
             
                            <td  style="overflow:visible;">
                        
                        <div class="btn-group btn-group-sm" >
                                <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:;">操作 <span class="caret"></span></a>
                                <ul class="dropdown-menu dropdown-menu-left" role="menu" style='z-index: 9999'>
                               
                        <?php if(cv('member.member.view|member.member.edit')) { ?><li><a href="<?php  echo $this->createWebUrl('member',array('op'=>'detail','id' => $row['id']));?>" title="会员详情"><i class='fa fa-edit'></i> 会员详情</a></li><?php  } ?>
                        <?php if(cv('order')) { ?><li><a  href="<?php  echo $this->createWebUrl('order', array('p'=>'list_member','op' => 'display','member'=>$row['nickname']))?>" title='会员订单'><i class='fa fa-list'></i> 会员订单</a></li><?php  } ?>
                        <?php if(cv('finance.recharge.credit1')) { ?><li><a href="<?php  echo $this->createWebUrl('finance/recharge', array('op'=>'credit1','id'=>$row['id']))?>" title='充值<?php  echo SZ_YI_INTEGRAL?>'><i class='fa fa-credit-card'></i> 充值<?php  echo SZ_YI_INTEGRAL?></a></li><?php  } ?>
                        <?php if(cv('finance.recharge.credit2')) { ?><li><a href="<?php  echo $this->createWebUrl('finance/recharge', array('op'=>'credit2','id'=>$row['id']))?>" title='充值余额'><i class='fa fa-money'></i> 充值余额 </a></li><?php  } ?>
                        <?php  if(p('yunbi')) { ?>
                        <?php  $yunbi_set = p('yunbi')->getSet()?>
                        <li><a href="<?php  echo $this->createWebUrl('finance/recharge', array('op'=>'virtual_currency','id'=>$row['id']))?>" title='充值<?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?>'><i class='fa fa-money'></i> 充值<?php  if($yunbi_set['yunbi_title'] ) { ?><?php  echo $yunbi_set['yunbi_title'];?><?php  } else { ?>云币<?php  } ?> </a></li>
                        <?php  } ?>
                        <?php  if(p('love')) { ?>
                        <li><a href="<?php  echo $this->createWebUrl('finance/aging_recharge', array('id'=>$row['id']))?>" title='分期充值'><i class='fa fa-money'></i> 分期充值 </a></li>
                        <?php  } ?>
		                  <?php if(cv('member.member.black')) { ?>
                            <?php  if($row['isblack']==1) { ?>
                            <li><a href="<?php  echo $this->createWebUrl('member/list',array('op'=>'setblack','id' => $row['id'],'black'=>0));?>" title='取消黑名单'><i class='fa fa-minus-square'></i> 取消黑名单</a></li>
                            <?php  } else { ?>
                            <li><a href="<?php  echo $this->createWebUrl('member/list',array('op'=>'setblack','id' => $row['id'],'black'=>1));?>" title='设置黑名单'><i class='fa fa-minus-circle'></i> 设置黑名单</a></li>
                            <?php  } ?>
                        <?php  } ?>
                        <?php if(cv('member.member.delete')) { ?><li><a  href="<?php  echo $this->createWebUrl('member',array('op'=>'delete','id' => $row['id']));?>" title='删除会员' onclick="return confirm('确定要删除该会员吗？');"><i class='fa fa-remove'></i> 删除会员</a></li><?php  } ?>
                                </ul>
                            </div>

               
                    </td>
                   
                    </td>
                </tr>
                <?php  } } ?>
            </tbody>
        </table>
           <?php  echo $pager;?>
    </div>
</div>
</div>
<?php  } else if($operation=='detail') { ?>
<!-- 新增加右侧顶部三级菜单 -->
<div class="right-titpos">
	<ul class="add-snav">
		<li class="active"><a href="#">会员管理</a></li>
		<li><a href="#">会员详情</a></li>
	</ul>
</div>
<!-- 新增加右侧顶部三级菜单结束 -->
<form <?php  if('member.member.edit') { ?>action="" method='post'<?php  } ?> class='form-horizontal'>
    <input type="hidden" name="id" value="<?php  echo $member['id'];?>">
    <input type="hidden" name="op" value="detail">
    <input type="hidden" name="c" value="site" />
    <input type="hidden" name="a" value="entry" />
    <input type="hidden" name="m" value="sz_yi" />
    <input type="hidden" name="do" value="member" />
    <div class='panel panel-default'>
        <div class='panel-body'>
             <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">粉丝</label>
                <div class="col-sm-9 col-xs-12">
                    <img src='<?php  echo $member['avatar'];?>' style='width:100px;height:100px;padding:1px;border:1px solid #ccc' />
                         <?php  echo $member['nickname'];?>
                </div>
            </div>
               <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">OPENID</label>
                <div class="col-sm-9 col-xs-12">
                    <div class="form-control-static"><?php  echo $member['openid'];?></div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员等级</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if(cv('member.member.edit')) { ?>
                      <select name='data[level]' class='form-control'>
                        <option value=''><?php echo empty($shop['levelname'])?'普通会员':$shop['levelname']?></option>
                        <?php  if(is_array($levels)) { foreach($levels as $level) { ?>
                        <option value='<?php  echo $level['id'];?>' <?php  if($member['level']==$level['id']) { ?>selected<?php  } ?>><?php  echo $level['levelname'];?></option>
                        <?php  } } ?>
                    </select>
                    <?php  } else { ?>
                    <div class='form-control-static'>
                        <?php  if(empty($member['level'])) { ?>
                        <?php echo empty($shop['levelname'])?'普通会员':$shop['levelname']?>
                        <?php  } else { ?>
                        <?php  echo pdo_fetchcolumn('select levelname from '.tablename('sz_yi_member_level').' where id=:id limit 1',array(':id'=>$member['level']))?>
                        <?php  } ?>
                    </div>
                    <?php  } ?>
                </div>
            </div>
              <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">会员分组</label>
                <div class="col-sm-9 col-xs-12">
                       <?php if(cv('member.member.edit')) { ?>
                      <select name='data[groupid]' class='form-control'>
                        <option value=''>无分组</option>
                        <?php  if(is_array($groups)) { foreach($groups as $group) { ?>
                        <option value='<?php  echo $group['id'];?>' <?php  if($member['groupid']==$group['id']) { ?>selected<?php  } ?>><?php  echo $group['groupname'];?></option>
                        <?php  } } ?>
                    </select>
                          <?php  } else { ?>
                    <div class='form-control-static'>
                        <?php  if(empty($member['groupid'])) { ?>
                        无分组
                        <?php  } else { ?>
                        <?php  echo pdo_fetchcolumn('select groupname from '.tablename('sz_yi_member_group').' where id=:id limit 1',array(':id'=>$member['groupid']))?>
                        <?php  } ?>
                    </div>
                    <?php  } ?>
                </div>
            </div>
             
        
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">真实姓名</label>
                <div class="col-sm-9 col-xs-12">
                      <?php if(cv('member.member.edit')) { ?>
                    <input type="text" name="data[realname]" class="form-control" value="<?php  echo $member['realname'];?>"  />
                    <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $member['realname'];?></div>
                    <?php  } ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">联系电话</label>
                <div class="col-sm-9 col-xs-12">
                        <?php if(cv('member.member.edit')) { ?>
                    <input type="text" name="data[membermobile]" class="form-control" value="<?php  echo $member['membermobile'];?>"  />
                      <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $member['membermobile'];?></div>
                    <?php  } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">绑定手机</label>
                <div class="col-sm-9 col-xs-12">
                        <?php if(cv('member.member.edit')) { ?>
                    <div class='form-control-static'><?php  echo $member['mobile'];?></div>
                      <?php  } else { ?>
                    <div class='form-control-static'><?php  echo $member['mobile'];?></div>
                    <?php  } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">微信号</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if(cv('member.member.edit')) { ?>
                          <input type="text" name="data[weixin]" class="form-control" value="<?php  echo $member['weixin'];?>"  />
                      <?php  } else { ?>
                         <div class='form-control-static'><?php  echo $member['weixin'];?></div>
                    <?php  } ?>
                </div>
            </div>
           <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label"><?php  echo SZ_YI_INTEGRAL?></label>
                <div class="col-sm-3">
                      <?php if(cv('finance.recharge.credit1')) { ?>
                     <div class='input-group'>
                        <div class=' input-group-addon'  style='width:200px;text-align: left;'><?php  echo $member['credit1'];?></div>
                      <div class='input-group-btn'>
                         <a class='btn btn-primary' href="<?php  echo $this->createWebUrl('finance/recharge', array('op'=>'credit1','id'=>$member['id']))?>">充值</a>
                          </div>
                      </div>
                      <?php  } else { ?>
                       <div class='form-control-static'><?php  echo $member['credit1'];?></div>
                      <?php  } ?>
          
                </div>
            </div>
              <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">余额</label>
                <div class="col-sm-3">  
                    <?php if(cv('finance.recharge.credit2')) { ?>
                    <div class='input-group'>
                        <div class=' input-group-addon' style='width:200px;text-align: left;'><?php  echo $member['credit2'];?></div>
                       
                        <div class='input-group-btn'><a class='btn btn-primary' href="<?php  echo $this->createWebUrl('finance/recharge', array('op'=>'credit2','id'=>$member['id']))?>">充值</a>
                            </div>
                   
                    </div>
                    <?php  } else { ?>
                      <div class='form-control-static'><?php  echo $member['credit2'];?></div>
                      <?php  } ?>
                </div>
            </div>
             <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">成交订单数</label>
                <div class="col-sm-9 col-xs-12">
                    <div class='form-control-static'><?php  echo $member['self_ordercount'];?></div>
                </div>
            </div>
               <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">成交金额</label>
                <div class="col-sm-9 col-xs-12">
                    <div class='form-control-static'><?php  echo $member['self_ordermoney'];?> 元</div>
                </div>
            </div>
               <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">注册时间</label>
                <div class="col-sm-9 col-xs-12">
                    <div class='form-control-static'><?php  echo date('Y-m-d H:i:s', $member['createtime']);?></div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">关注状态</label>
                <div class="col-sm-9 col-xs-12">
                    <div class='form-control-static'>
                        <?php  $followed = m('user')->followed($member['openid'])?>
                         <?php  if(!$followed) { ?>
                            <?php  if(empty($member['uid'])) { ?>
                            <label class='label label-default'>未关注</label>
                            <?php  } else { ?>
                            <label class='label label-warning'>取消关注</label>
                            <?php  } ?>
                            <?php  } else { ?>
                        <label class='label label-success'>已关注</label>    
                        <?php  } ?>
                        
                    </div>
                </div>
            </div>
        <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">黑名单</label>
                <div class="col-sm-9 col-xs-12">
                      <?php if(cv('member.member.edit')) { ?>
                    <label class="radio-inline"><input type="radio" name="data[isblack]" value="1" <?php  if($member['isblack']==1) { ?>checked<?php  } ?>>是</label>
                    <label class="radio-inline" ><input type="radio" name="data[isblack]" value="0" <?php  if($member['isblack']==0) { ?>checked<?php  } ?>>否</label>
                    <span class="help-block">设置黑名单后，此会员无法访问商城</span>
                    <?php  } else { ?>
                      <input type='hidden' name='data[isblack]' value='<?php  echo $member['isblack'];?>' />
                      <div class='form-control-static'><?php  if($member['isblack']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
                    <?php  } ?>
                    
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">备注</label>
                <div class="col-sm-9 col-xs-12">
                      <?php if(cv('member.member.edit')) { ?>
                    <textarea name="data[content]" class='form-control'><?php  echo $member['content'];?></textarea>
                      <?php  } else { ?>
                         <div class='form-control-static'><?php  echo $member['content'];?></div>
                    <?php  } ?>
                </div>
            </div>
        </div>

		
        <?php  if($diyform_flag == 1) { ?>
        <div class='panel-heading'>
            自定义表单信息
        </div>
        <div class='panel-body'>
            <!--<span>diyform</span>-->

            <?php  $datas = iunserializer($member['diymemberdata'])?>
            <?php  if(is_array($fields)) { foreach($fields as $key => $value) { ?>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label"><?php  echo $value['tp_name']?></label>
                <div class="col-sm-9 col-xs-12">
                    <div class="form-control-static">

                        <?php  if($value['data_type'] == 0 || $value['data_type'] == 1 || $value['data_type'] == 2 || $value['data_type'] == 6) { ?>
                        <?php  echo str_replace("\n","<br/>",$datas[$key])?>

                        <?php  } else if($value['data_type'] == 3) { ?>
                        <?php  if(!empty($datas[$key])) { ?>
                        <?php  if(is_array($datas[$key])) { foreach($datas[$key] as $k1 => $v1) { ?>
                        <?php  echo $v1?>
                        <?php  } } ?>
                        <?php  } ?>

                        <?php  } else if($value['data_type'] == 5) { ?>
                        <?php  if(!empty($datas[$key])) { ?>
                        <?php  if(is_array($datas[$key])) { foreach($datas[$key] as $k1 => $v1) { ?>
                        <a target="_blank" href="<?php  echo tomedia($v1)?>"><img style='width:100px;padding:1px;border:1px solid #ccc'  src="<?php  echo tomedia($v1)?>"></a>
                        <?php  } } ?>
                        <?php  } ?>

                        <?php  } else if($value['data_type'] == 7) { ?>
                        <?php  echo $datas[$key]?>

                        <?php  } else if($value['data_type'] == 8) { ?>
                        <?php  if(!empty($datas[$key])) { ?>
                        <?php  if(is_array($datas[$key])) { foreach($datas[$key] as $k1 => $v1) { ?>
                        <?php  echo $v1?>
                        <?php  } } ?>
                        <?php  } ?>

                        <?php  } else if($value['data_type'] == 9) { ?>
                         <?php echo $datas[$key]['province']!='请选择省份'?$datas[$key]['province']:''?>-<?php echo $datas[$key]['city']!='请选择城市'?$datas[$key]['city']:''?>
                        <?php  } ?>
                    </div>

                </div>
            </div>

            <?php  } } ?>
        </div>
        <?php  } ?>
	<?php  if($member['isagent']==1 && $member['status']==1) { ?>
        <?php  if($hasbonus && cv('bonus.agent.changeagent')) { ?>
        <?php  if($trade['is_street'] == 1) { ?>
        <script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/cascade_street.js"></script>
        <?php  } else { ?>
        <script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/cascade.js"></script>
        <?php  } ?>
        <div class='panel-heading'>
            设置代理商 <small>注意: 成为分销商后才可设置成为代理等级与代理地区</small>
        </div>
        <div class='panel-body'>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">代理商等级</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if(cv('bonus.agent.edit')) { ?>
                    <select name='bdata[bonuslevel]' class='form-control'>
                        <option value='0'><?php echo empty($plugin_bonus_set['levelname'])?'普通等级':$plugin_bonus_set['levelname']?></option>
                        <?php  if(is_array($bonuslevels)) { foreach($bonuslevels as $level) { ?>
                        <option value='<?php  echo $level['id'];?>' <?php  if($member['bonuslevel']==$level['id']) { ?>selected<?php  } ?>><?php  echo $level['levelname'];?></option>
                        <?php  } } ?>
                    </select>
                    <?php  } else { ?>
                    <input type="hidden" name="data[bonuslevel]" class="form-control" value="<?php  echo $member['bonuslevel'];?>"  />

                    <?php  if(empty($member['agentlevel'])) { ?>
                    <?php echo empty($plugin_bonus_set['levelname'])?'普通等级':$plugin_bonus_set['levelname']?>
                    <?php  } else { ?>
                    <?php  echo pdo_fetchcolumn('select levelname from '.tablename('sz_yi_bonus_level').' where id=:id limit 1',array(':id'=>$member['bonuslevel']))?>
                    <?php  } ?>
                    <?php  } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">区域代理</label>
                <div class="col-sm-4">
                    <?php if(cv('bonus.agent.edit')) { ?>
                    <select  class="form-control" id="bonus_area" name="bdata[bonus_area]">
                        <option value="0" <?php  if(empty($member['bonus_area'])) { ?>selected<?php  } ?>>不选择</option>
                        <option value="1" <?php  if($member['bonus_area']==1) { ?>selected<?php  } ?>>省级代理</option>
                        <option value="2" <?php  if($member['bonus_area']==2) { ?>selected<?php  } ?> >市级代理</option>
                        <option value="3" <?php  if($member['bonus_area']==3) { ?>selected<?php  } ?> >区级代理</option>
                        <?php  if($trade['is_street'] == 1) { ?>
                        <option value="4" <?php  if($member['bonus_area']==4) { ?>selected<?php  } ?> >街级代理</option>
                        <?php  } ?>
                    </select>
                    <?php  } else { ?>
                    <input type="hidden" name="data[bonus_area]" class="form-control" value="<?php  echo $member['bonus_area'];?>"  />
                <div class='form-control-static'><?php  if(empty($member['bonus_area'])) { ?>无<?php  } ?><?php  if($member['bonus_area']==1) { ?>省级代理<?php  } ?><?php  if($member['bonus_area']==2) { ?>市级代理<?php  } ?><?php  if($member['bonus_area']==3) { ?>区级代理<?php  } ?><?php  if($member['bonus_area']==4) { ?>街级代理<?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>
            <div class="form-group form-area" <?php  if(empty($member['bonus_area'])) { ?>style="display:none;"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">代理区域</label>
                <div class="col-sm-9 col-xs-12">
                    <?php if(cv('bonus.agent.edit')) { ?>
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                        <select class="form-control tpl-province" id="sel-provance" onchange="selectCity();" name="reside[province]">
                            <option value="" selected="true">所在省份</option>
                        </select>
                    </div>
          
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                        <select class="form-control tpl-city" id="sel-city" onchange="selectcounty()" name="reside[city]"><option value="" selected="true">所在城市</option></select>
                    </div>
            
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                        <select class="form-control tpl-district" id="sel-area" name="reside[district]"><option value="" selected="true">所在地区</option></select>
                    </div>

                        <?php  if($trade['is_street'] == 1) { ?>
                           <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                <select class="form-control tpl-street" id="sel-street" name="reside[street]"><option value="" selected="true">所在街道</option></select>
                            </div>
                        <?php  } ?>

                    <?php  } else { ?>
                    <input type="hidden" name="reside[province]" class="form-control" value="<?php  echo $member['bonus_province'];?>"  />
                    <div class='form-control-static'><?php  echo $member['bonus_province'];?></div>
                    <input type="hidden" name="reside[city]" class="form-control" value="<?php  echo $member['bonus_city'];?>"  />
                    <div class='form-control-static'><?php  echo $member['bonus_city'];?></div>
                    <input type="hidden" name="reside[district]" class="form-control" value="<?php  echo $member['bonus_area'];?>"  />
                    <div class='form-control-static'><?php  echo $member['bonus_area'];?></div>
                    <input type="hidden" name="reside[street]" class="form-control" value="<?php  echo $member['bonus_street'];?>"  />
                    <div class='form-control-static'><?php  echo $member['bonus_street'];?></div>
                    <?php  } ?>    
                </div>
            </div>
        </div>
        <div class="form-group form-area">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                <div class="col-sm-4">
                    <?php if(cv('bonus.agent.edit')) { ?>
                    <div class="input-group">
                        <div class="input-group-addon">区域代理</div>
                        <input type="text" name="bdata[bonus_area_commission]" class="form-control" value="<?php  echo $member['bonus_area_commission'];?>"  />
                        <div class="input-group-addon">%</div>
                    </div>
                    <span class='help-block'>如不设置，则为基础设置中默认比例值</span>
                    <?php  } else { ?>
                    <input type="hidden" name="bdata[bonus_area_commission]" class="form-control" value="<?php  echo $member['bonus_area_commission'];?>"  />
                    <div class='form-control-static'><?php  echo $member['bonus_area_commission'];?></div>
                    <?php  } ?>
                    
                </div>
            </div>
         <script type="text/javascript">
            $(document).ready(function(){
              $("#bonus_area").change(function(){
                changearea();
              });
            });
            function changearea(){
                var area_val = $("#bonus_area").val();
                if(area_val==0){
                    $(".form-area").hide();
                }else if(area_val==1){            
                    $(".form-area").show();
                    $(".form-province").show();
                    $(".tpl-city").hide();
                    $(".tpl-district").hide();
                    <?php  if($trade['is_street'] == 1) { ?>
                        $(".tpl-street").hide(); 
                    <?php  } ?>
                }else if(area_val==2){            
                    $(".form-area").show();
                    $(".form-province").show();
                    $(".tpl-city").show();
                    $(".tpl-district").hide();
                    <?php  if($trade['is_street'] == 1) { ?>
                        $(".tpl-street").hide(); 
                    <?php  } ?>
                }else if(area_val==3){            
                    $(".form-area").show();
                    $(".form-province").show();
                    $(".tpl-city").show();
                    $(".tpl-district").show();  
                    <?php  if($trade['is_street'] == 1) { ?>
                        $(".tpl-street").hide(); 
                    <?php  } ?>
                }

                <?php  if($trade['is_street'] == 1) { ?>
                    if(area_val==4){            
                        $(".form-area").show();
                        $(".form-province").show();
                        $(".tpl-city").show();
                        $(".tpl-district").show(); 
                        $(".tpl-street").show();  
                    }
                <?php  } ?>
        
            }
            changearea();
            cascdeInit("<?php  echo $member['bonus_province'];?>", "<?php  echo $member['bonus_city'];?>", "<?php  echo $member['bonus_district'];?>", "<?php  echo $member['bonus_street'];?>");
        </script>
        <?php  } ?>
	<?php  } else { ?>
        <?php  if($hascommission && cv('commission.agent.changeagent')) { ?>
        <div class='panel-heading'>
            设置分销商 <small>注意: 分销商设置后，无法再此进行修改，如果要修改，请联系系统管理员</small>
        </div>
           <div class='panel-body'>
<div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">上级分销商</label>
                    <div class="col-sm-4">
                       <input type="hidden" value="<?php  echo $member['agentid'];?>" id='agentid' name='adata[agentid]' class="form-control"  />
                        
                      <?php if(cv('commission.agent.edit')) { ?>
                        <div class='input-group'>
                            <input type="text" name="parentagent" maxlength="30" value="<?php  if(!empty($parentagent)) { ?><?php  echo $parentagent['nickname'];?>/<?php  echo $parentagent['realname'];?>/<?php  echo $parentagent['membermobile'];?><?php  } ?>" id="parentagent" class="form-control" readonly />
                            <div class='input-group-btn'>
                                <button class="btn btn-default" type="button" onclick="popwin = $('#modal-module-menus-notice').modal();">选择上级分销商</button>
                                <button class="btn btn-danger" type="button" onclick="$('#agentid').val('');$('#parentagent').val('');$('#parentagentavatar').hide()">清除选择</button>
                            </div> 
                        </div>
                        <span id="parentagentavatar" class='help-block' <?php  if(empty($parentagent)) { ?>style="display:none"<?php  } ?>><img  style="width:100px;height:100px;border:1px solid #ccc;padding:1px" src="<?php  echo $parentagent['avatar'];?>"/></span>
                         
                        <div id="modal-module-menus-notice"  class="modal fade" tabindex="-1">
                            <div class="modal-dialog" style='width: 920px;'>
                                <div class="modal-content">
                                    <div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button><h3>选择上级分销商</h3></div>
                                    <div class="modal-body" >
                                        <div class="row">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="keyword" value="" id="search-kwd-notice" placeholder="请输入分销商昵称/姓名/手机号" />
                                                <span class='input-group-btn'><button type="button" class="btn btn-default" onclick="search_members();">搜索</button></span>
                                            </div>
                                        </div>
                                        <div id="module-menus-notice" style="padding-top:5px;"></div>
                                    </div>
                                    <div class="modal-footer"><a href="#" class="btn btn-default" data-dismiss="modal" aria-hidden="true">关闭</a></div>
                                </div>

                            </div>
                        </div>
                        <span class="help-block">修改后， 只有关系链改变, 以往的订单佣金都不会改变,新的订单才按新关系计算佣金 ,请谨慎选择</span>
                        <?php  } else { ?>
                        <div class='form-control-static'>
                            <?php  if(!empty($parentagent)) { ?><img  style="width:100px;height:100px;border:1px solid #ccc;padding:1px" src="<?php  echo $parentagent['avatar'];?>"/><?php  } else { ?>无<?php  } ?>
                         </div>
                        <?php  } ?>
                        
                    </div>
                </div>
            
			     <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否固定上级</label>
                <div class="col-sm-9 col-xs-12">
                     <?php if(cv('commission.agent.check')) { ?>
                    <label class="radio-inline"><input type="radio" name="adata[fixagentid]" value="1" <?php  if($member['fixagentid']==1) { ?>checked<?php  } ?>>是</label>
                    <label class="radio-inline" ><input type="radio" name="adata[fixagentid]" value="0" <?php  if($member['fixagentid']==0) { ?>checked<?php  } ?>>否</label>
                    <span class="help-block">固定上级后，任何条件也无法改变其上级，如果不选择上级分销商，且固定上级，则上级永远为总店（是分销商）或无上线（非分销商）</span>
                    <?php  } else { ?>
                      <input type='hidden' name='adata[fixagentid]' value='<?php  echo $member['fixagentid'];?>' />
                      <div class='form-control-static'><?php  if($member['fixagentid']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
                    <?php  } ?>
                    
                </div>
            </div>
			   
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">分销商等级</label>
               <div class="col-sm-9 col-xs-12">
                         <?php if(cv('commission.agent.edit')) { ?>
                    <select name='adata[agentlevel]' class='form-control'>
                        <option value='0'><?php echo empty($plugin_com_set['levelname'])?'普通等级':$plugin_com_set['levelname']?></option>
                         <?php  if(is_array($agentlevels)) { foreach($agentlevels as $level) { ?>
                        <option value='<?php  echo $level['id'];?>' <?php  if($member['agentlevel']==$level['id']) { ?>selected<?php  } ?>><?php  echo $level['levelname'];?></option>
                        <?php  } } ?>
                    </select>
                         <?php  } else { ?>
                             <input type="hidden" name="adata[agentlevel]" class="form-control" value="<?php  echo $member['agentlevel'];?>"  />
                             
                              <?php  if(empty($member['agentlevel'])) { ?>
                            <?php echo empty($plugin_com_set['levelname'])?'普通等级':$plugin_com_set['levelname']?>
                                <?php  } else { ?>
                                <?php  echo pdo_fetchcolumn('select levelname from '.tablename('sz_yi_commission_level').' where id=:id limit 1',array(':id'=>$member['agentlevel']))?>
                                <?php  } ?>
                         <?php  } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">累计佣金</label>
                <div class="col-sm-9 col-xs-12">
                    <div class='form-control-static'> <?php  echo $member['commission_total'];?></div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">已打款佣金</label>
                <div class="col-sm-9 col-xs-12">
                    <div class='form-control-static'> <?php  echo $member['commission_pay'];?></div>
                </div>
            </div>
			   <?php  if($member['agenttime']!='1970-01-01 08:00') { ?>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">成为分销商时间</label>
                <div class="col-sm-9 col-xs-12">
                    <div class='form-control-static'><?php  echo $member['agenttime'];?></div> 
                </div>
            </div>
			   <?php  } ?>
           <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">分销商权限</label>
                <div class="col-sm-9 col-xs-12">
                     <?php if(cv('commission.agent.check')) { ?>
                    <label class="radio-inline"><input type="radio" name="adata[isagent]" value="1" <?php  if($member['isagent']==1) { ?>checked<?php  } ?>>是</label>
                    <label class="radio-inline" ><input type="radio" name="adata[isagent]" value="0" <?php  if($member['isagent']==0) { ?>checked<?php  } ?>>否</label>
                    <?php  } else { ?>
                      <input type='hidden' name='adata[isagent]' value='<?php  echo $member['isagent'];?>' />
                      <div class='form-control-static'><?php  if($member['isagent']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
                    <?php  } ?>
                    
                </div>
            </div>
       
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">审核通过</label>
                <div class="col-sm-9 col-xs-12">
                     <?php if(cv('commission.agent.check')) { ?>
                    <label class="radio-inline"><input type="radio" name="adata[status]" value="1" <?php  if($member['status']==1) { ?>checked<?php  } ?>>是</label>
                    <label class="radio-inline" ><input type="radio" name="adata[status]" value="0" <?php  if($member['status']==0) { ?>checked<?php  } ?>>否</label>
                    <input type='hidden' name='oldstatus' value="<?php  echo $member['status'];?>" />
                       <?php  } else { ?>
                      <input type='hidden' name='adata[status]' value='<?php  echo $member['status'];?>' />
                      <div class='form-control-static'><?php  if($member['status']==1) { ?>是<?php  } else { ?>否<?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>

             <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">强制不自动升级</label>
                <div class="col-sm-9 col-xs-12">
                      <?php if(cv('commission.agent.edit')) { ?>
                    <label class="radio-inline" ><input type="radio" name="adata[agentnotupgrade]" value="0" <?php  if($member['agentnotupgrade']==0) { ?>checked<?php  } ?>>允许自动升级</label>
                    <label class="radio-inline"><input type="radio" name="adata[agentnotupgrade]" value="1" <?php  if($member['agentnotupgrade']==1) { ?>checked<?php  } ?>>强制不自动升级</label>
                    <span class="help-block">如果强制不自动升级，满足任何条件，此分销商的级别也不会改变</span>
                    <?php  } else { ?>
                         <input type="hidden" name="adata[agentnotupgrade]" class="form-control" value="<?php  echo $member['agentnotupgrade'];?>"  />
                      <div class='form-control-static'><?php  if($member['agentnotupgrade']==1) { ?>强制不自动升级<?php  } else { ?>允许自动升级<?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>
        
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">自选商品</label>
                <div class="col-sm-9 col-xs-12">
                      <?php if(cv('commission.agent.edit')) { ?>
                    <label class="radio-inline" ><input type="radio" name="adata[agentselectgoods]" value="0" <?php  if($member['agentselectgoods']==0) { ?>checked<?php  } ?>>系统设置</label>
                    <label class="radio-inline"><input type="radio" name="adata[agentselectgoods]" value="1" <?php  if($member['agentselectgoods']==1) { ?>checked<?php  } ?>>强制禁止</label>
                    <label class="radio-inline"><input type="radio" name="adata[agentselectgoods]" value="2" <?php  if($member['agentselectgoods']==2) { ?>checked<?php  } ?>>强制开启</label>
                    <span class="help-block">系统设置： 跟随系统设置，系统关闭自选则为禁止，系统开启自选则为允许</span>
                    <span class="help-block">强制禁止： 无论系统自选商品是否关闭或开启，此分销商永不能自选商品</span>
                    <span class="help-block">强制允许： 无论系统自选商品是否关闭或开启，此分销商永可以自选商品</span>
                    <?php  } else { ?>
                      <input type="hidden" name="adata[agentselectgoods]" class="form-control" value="<?php  echo $member['agentselectgoods'];?>"  />
                      <div class='form-control-static'><?php  if($member['agentnotselectgoods']==1) { ?>
                          强制禁止 
                          <?php  } else if($member['agentselectgoods']==2) { ?>
                          强制允许
                          <?php  } else { ?>
                          <?php  if($plugin_com_set['select_goods']==1) { ?>系统允许<?php  } else { ?>系统禁止<?php  } ?>
                          <?php  } ?></div>
                    <?php  } ?>
                </div>
            </div>
        </div>
        <?php  } ?>
		        <?php  } ?>
        <div class='panel-body'>
          <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                <div class="col-sm-9 col-xs-12">
                    <?php if(cv('member.member.edit')) { ?>
                  <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1" />
	<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                  <?php  } ?>
                <input type="button" class="btn btn-default" name="submit" onclick="history.go(-1)" value="返回列表" <?php if(cv('member.member.edit')) { ?>style='margin-left:10px;'<?php  } ?> />
                </div>
            </div>
         </div>

    </div>   
	
</form>
<?php  } ?>
</div>
</div>
<script language='javascript'>
    
         function search_members() {
             if( $.trim($('#search-kwd-notice').val())==''){
                 Tip.focus('#search-kwd-notice','请输入关键词');
                 return;
             }
		$("#module-menus-notice").html("正在搜索....")
		$.get('<?php  echo $this->createPluginWebUrl('commission/agent')?>', {
			keyword: $.trim($('#search-kwd-notice').val()),'op':'query',selfid:"<?php  echo $id;?>"
		}, function(dat){
			$('#module-menus-notice').html(dat);
		});
	}
    $(function () {
        $('#export').click(function(){
            $('#form_p').val("exportMember");
            $('#form1').submit();   
            $('#form_p').val("list");
        });  
    });
	function select_member(o) {
		$("#agentid").val(o.id);
                  $("#parentagentavatar").show();
                  $("#parentagentavatar").find('img').attr('src',o.avatar);
		$("#parentagent").val( o.nickname+ "/" + o.realname + "/" + o.membermobile );
		$("#modal-module-menus-notice .close").click();
	}
        
    </script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>

