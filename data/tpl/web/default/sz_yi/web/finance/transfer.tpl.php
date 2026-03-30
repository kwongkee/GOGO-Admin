<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/finance/tabs', TEMPLATE_INCLUDEPATH)) : (include template('web/finance/tabs', TEMPLATE_INCLUDEPATH));?>
 
 <div class="rightlist">
<div class="panel panel-default">
    <div class="panel-heading">总数：<?php  echo $total;?></div>
    <div class="panel-body ">
        <table class="table table-hover">
            <thead class="navbar-inner">
                <tr>
                    <th style='width:50px;'>编号</th>
                    <th style='width:120px;'>转让人</th>
                    <th style='width:120px;'>受让人</th>
                    <th style='width:120px;'  class='hidden-xs'>金额</th>
                    <th style='width:120px;' class='hidden-xs'>转让时间</th>
                    <th style='width:120px;'>状态</th>
                </tr>
            </thead>
            <tbody>
                <?php  if(is_array($list)) { foreach($list as $row) { ?>
                <tr>

                    <td><?php  echo $row['id'];?></td>
                    <td><?php  echo $row['tosell_realname'];?>(<?php  echo $row['tosell_id'];?>)</td>
                    <td><?php  echo $row['assigns_realname'];?>(<?php  echo $row['assigns_id'];?>)</td>
                    <td><?php  echo $row['money'];?></td>
                    <td><?php  echo $row['createtime'];?></td>
                    <td><?php  if($row['status']=='-1') { ?>失败<?php  } else if($row['status']=='0') { ?>进行中<?php  } else { ?>已到账<?php  } ?></td>

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
