<?php defined('IN_IA') or exit('Access Denied');?><?php  if(empty($item['statusvalue'])) { ?>
    <!--未付款-->
    <?php if(cv('order.op.pay')) { ?>
        <?php  if($item['paytypevalue']==3) { ?>
        <div>
            <input class='addressdata' type='hidden' value='<?php  echo json_encode($item[' addressdata'])?>' />
            <input class='itemid' type='hidden' value="<?php  echo $item['id'];?>"/>
            <a class="btn btn-primary btn-sm disbut" href="javascript:;" onclick="send(this)" data-toggle="modal"
               data-target="#modal-confirmsend">确认发货</a>
        </div>
        <?php  } else { ?>
        <?php  if(empty($perm_role)) { ?>
        <a class="btn btn-primary btn-sm disbut"
           href="<?php  echo $this->createWebUrl('order', array('op' => 'deal','to'=>'confirmpay','id' => $item['id']))?>"
           onclick="return confirm('确认此订单已付款吗？');return false;">确认付款</a>
        <?php  } else { ?>
        <a class="label label-default">等待付款</a>
        <?php  } ?>
        <?php  } ?>

    <?php  } ?>

<?php  } else if($item['statusvalue'] == 1) { ?>
        <!--已付款-->

    <?php  if(!empty($item['addressid']) ) { ?>
        <!--快递 发货-->
        <?php if(cv('order.op.send')) { ?>
            <div>
                <input class='addressdata' type='hidden' value='<?php  echo json_encode($item['addressdata'])?>' />
                <input class='itemid' type='hidden' value="<?php  echo $item['id'];?>"/>
                <a class="btn btn-primary btn-sm disbut" href="javascript:;" onclick="send(this)" data-toggle="modal"
                   data-target="#modal-confirmsend">确认发货</a>
            </div>
        <?php  } ?>
    <?php  } else { ?>
        <?php  if($item['isverify']==1) { ?>
        <!--核销 确认核销-->
            <?php if(cv('order.op.verify')) { ?>
            <div>
                <input class='addressdata' type='hidden' value='<?php  echo json_encode($item['addressdata'])?>' />
                <input class='itemid1' type='hidden' value="<?php  echo $item['id'];?>"/>
                <a class="btn btn-primary btn-sm disbut" href="javascript:;" onclick="send1(this)" data-toggle="modal"
                   data-target="#modal-confirmsend1">确认核销</a>
            </div>
            <?php  } ?>
        <?php  } else { ?>
            <!--自提 确认取货-->
            <?php if(cv('order.op.fetch')) { ?>
            <a class="btn btn-primary btn-sm disbut"
               href="<?php  echo $this->createWebUrl('order', array('op' => 'deal','to'=>'confirmsend1','id' => $item['id']))?>"
               onclick="return confirm('确认取货吗？');return false;">确认取货</a>
            <?php  } ?>
        <?php  } ?>

    <?php  } ?>
<?php  } else if($item['statusvalue'] == 2) { ?>
    <!--已发货-->
    <?php  if(!empty($item['addressid'])) { ?>
    <!--快递 取消发货-->
        <?php if(cv('order.op.sendcancel')) { ?>
        <a class="btn btn-danger btn-sm disbut" href="javascript:;"
           onclick="$('#modal-cancelsend').find(':input[name=id]').val('<?php  echo $item['id'];?>')" data-toggle="modal"
           data-target="#modal-cancelsend">取消发货</a>
        <?php  } ?>
        <?php if(cv('order.op.finish')) { ?>
        <?php  if(empty($perm_role)) { ?>
        <a class="btn btn-primary btn-sm disbut"
           href="<?php  echo $this->createWebUrl('order', array('op' => 'deal','to'=>'finish','id' => $item['id']))?>"
           onclick="return confirm('确认订单收货吗？');return false;">确认收货</a>
        <?php  } else { ?>
        <a class="btn btn-danger btn-sm disbut">等待收货</a>
        <?php  } ?>
        <?php  } ?>
    <?php  } ?>
<!-- 补发红包 -->
<?php  } else if($item['statusvalue'] == 3) { ?>
    <?php  if(!empty($item['redstatus'])) { ?>
    <a class="btn btn-danger btn-sm disbut"
       href="<?php  echo $this->createWebUrl('order', array('op' => 'deal','to'=>'redpack','id' => $item['id']))?>"
       onclick="return confirm('确认发送红包吗？');return false;">补发红包</a>
    <a><?php  echo $item['redprice'];?>元</a>
    <?php  } ?>
<?php  } ?>
