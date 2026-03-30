<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
$uniacid = $_W['uniacid'];
$notice_manage = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';//ov3-bt8keSKg_8z9Wwi-zG1hRhwg  ov3-bt5vIxepEjWc51zRQNQbFSaQ

//公用模块
if($op=='voucher_info'){
    //查看凭证信息
    $id = intval($_GPC['id']);
    $data = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$id]);
    if($data['type']==1){
        //收款登记
        if($data['voucher_type']==1 && $data['voucher_type1']==1){
            $data['voucher_type_name'] = '增值税专用发票';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==1){
            $data['voucher_type_name'] = '增值税普通发票(折叠票)';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==2){
            $data['voucher_type_name'] = '增值税普通发票（卷式）';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==3){
            $data['voucher_type_name'] = '增值税电子普通发票';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1){
            $data['voucher_type_name'] = '支票进账单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2){
            $data['voucher_type_name'] = '托收收账单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3){
            $data['voucher_type_name'] = '多余款通知单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==1){
            $data['voucher_type_name'] = '收据存根联';
        }elseif($data['voucher_type']==3){
            $data['voucher_type_name'] = '形式发票';
        }

        //收款方式
        if($data['collect_type']==1){
            $data['collect_type']='现金收款';
        }elseif($data['collect_type']==2){
            $data['collect_type']='转账收款';
        }
    }elseif($data['type']==2){
        //付款登记
        if($data['voucher_type']==1 && $data['voucher_type1']==1){
            $data['voucher_type_name'] = '增值税专用发票';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==1){
            $data['voucher_type_name'] = '增值税普通发票(折叠票)';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==2){
            $data['voucher_type_name'] = '增值税普通发票（卷式）';
        }elseif($data['voucher_type']==1 && $data['voucher_type1']==3){
            $data['voucher_type_name'] = '增值税电子普通发票';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==1 && $data['voucher_type4_1']==1){
            $data['voucher_type_name'] = '非税收入统一票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==1 && $data['voucher_type4_1']==2){
            $data['voucher_type_name'] = '非税收入一般缴款书';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==2 && $data['voucher_type4_2']==1){
            $data['voucher_type_name'] = '定额票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==2 && $data['voucher_type4_2']==2){
            $data['voucher_type_name'] = '非定额票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==1){
            $data['voucher_type_name'] = '社会团体会费收据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==2){
            $data['voucher_type_name'] = '医疗票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==3){
            $data['voucher_type_name'] = '捐赠收据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==1){
            $data['voucher_type_name'] = '税收缴款书';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==2){
            $data['voucher_type_name'] = '税收收入退还书';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==3){
            $data['voucher_type_name'] = '税收完税证明';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==4 && $data['voucher_type5_1']==1){
            $data['voucher_type_name'] = '税收缴款书(出口货物劳务专用)';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==4 && $data['voucher_type5_1']==2){
            $data['voucher_type_name'] = '出口货物完税分割单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==5){
            $data['voucher_type_name'] = '印花税专用税收票证';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3 && $data['voucher_type6']==1){
            $data['voucher_type_name'] = '海关税款专用缴款书';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3 && $data['voucher_type6']==2){
            $data['voucher_type_name'] = '海关货物滞报金专用票据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==1){
            $data['voucher_type_name'] = '工资结算单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==2){
            $data['voucher_type_name'] = '费用报销单';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==3){
            $data['voucher_type_name'] = '借款收据';
        }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==4){
            $data['voucher_type_name'] = '领款收据';
        }elseif($data['voucher_type']==3){
            $data['voucher_type_name'] = '形式发票';
        }

        //收款方式
        if($data['collect_type']==1){
            $data['collect_type']='现金付款';
        }elseif($data['collect_type']==2){
            $data['collect_type']='转账付款';
        }
    }

    //分类名称
    if($data['content']==1){
        $data['tax_classify_inp'] = $data['tax_classify_sel'];
    }

    //币种
    $data['currency'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:id',[':id'=>$data['currency']]);
    $data['currency2'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:id',[':id'=>$data['currency2']]);
    $data['currency3'] = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:id',[':id'=>$data['currency3']]);

    //收/付款状态
    if($data['type']==1){
        $data['type_name2'] = '收款状态';
        if($data['money_status']==1){
            $data['money_status']='已开票，应收款';
        }elseif($data['money_status']==2){
            $data['money_status']='已开票，未收款';
        }elseif($data['money_status']==3){
            $data['money_status']='未开票，预收款';
        }
    }elseif($data['type']==2){
        $data['type_name2'] = '付款状态';
        if($data['money_status']==4){
            $data['money_status']='已收票，应付款';
        }elseif($data['money_status']==5){
            $data['money_status']='已收票，未付款';
        }elseif($data['money_status']==6){
            $data['money_status']='未收票，预付款';
        }
    }

    //账户信息
    $data['collect_account'] = pdo_fetch('select * from '.tablename('customs_accounting_account').' where id=:id',[':id'=>$data['collect_account']]);
    if($data['collect_account']['type']==1){
        $data['collect_account']['type'] = '企业账户';
    }elseif($data['collect_account']['type']==2){
        $data['collect_account']['type'] = '个人账户';
    }elseif($data['collect_account']['type']==3){
        $data['collect_account']['type'] = '支付账户';
    }

    //文件
    $data['account_receipt'] = json_decode($data['account_receipt'],true);

    //凭证文件
    $data['express_voucher'] = json_decode($data['express_voucher'],true);

    //补充文件
    $data['other_file'] = json_decode($data['other_file'],true);

    //凭证类型
    if($data['type']==1){
        $data['type_name'] = '收款登记';
    }elseif($data['type']==2){
        $data['type_name'] = '付款登记';
    }elseif($data['type']==3){
        $data['type_name'] = '账单登记';
        $data['bill_account'] = pdo_fetch('select * from '.tablename('customs_accounting_account').' where id=:id',[':id'=>$data['bill_account_id']]);
        if($data['bill_account']['type']==1){
            $data['bill_account']['type'] = '企业账户';
        }elseif($data['bill_account']['type']==2){
            $data['bill_account']['type'] = '个人账户';
        }elseif($data['bill_account']['type']==3){
            $data['bill_account']['type'] = '支付账户';
        }
        //账单文件
        $data['bill_file'] = json_decode($data['bill_file'],true);
    }

    //身份1管理员，2记账端，3企业端
    if($data['openid']==$openid){
        $identify=3;
    }elseif($openid=='ov3-bt5vIxepEjWc51zRQNQbFSaQ' || $openid=='ov3-bt8keSKg_8z9Wwi-zG1hRhwg'){
        $identify=1;
    }else{
        $identify=2;
    }
    //查找该凭证下的评论
    $voucher_comment = pdo_fetchall('select * from '.tablename('customs_accounting_comment').' where voucher_id=:id and pid=0 order by id desc ',[':id'=>$data['id']]);
    foreach($voucher_comment as $k=>$v){
        if($v['identify']==1){
            $voucher_comment[$k]['identify']='管理端';
        }elseif($v['identify']==2){
            $voucher_comment[$k]['identify']='记账端';
        }elseif($v['identify']==3){
            $voucher_comment[$k]['identify']='企业端';
        }
        $voucher_comment[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        $voucher_comment[$k]['file'] = json_decode($v['file'],true);
        
        //查找该评论下所有回复
        $voucher_comment[$k]['allReplay'] = pdo_fetchall('select * from '.tablename('customs_accounting_comment').' where pid=:id order by id desc',[':id'=>$v['id']]);
        foreach($voucher_comment[$k]['allReplay'] as $k2=>$v2){
            if($v2['identify']==1){
                $voucher_comment[$k]['allReplay'][$k2]['identify']='管理端';
            }elseif($v2['identify']==2){
                $voucher_comment[$k]['allReplay'][$k2]['identify']='记账端';
            }elseif($v2['identify']==3){
                $voucher_comment[$k]['allReplay'][$k2]['identify']='企业端';
            }
            $voucher_comment[$k]['allReplay'][$k2]['createtime'] = date('Y-m-d H:i',$v2['createtime']);
            $voucher_comment[$k]['allReplay'][$k2]['file'] =  json_decode($v2['file'],true);
        }
    }

    //人工汇总
    if(($data['type']==1 || $data['type']==2) && $data['method']==1){
        //收款和付款操作
        $data['voucher_type'] = explode(',',$data['voucher_type']);
        $data['attach_cert'] = explode(',',$data['attach_cert']);
        $data['voucher_unit'] = explode(',',$data['voucher_unit']);
    }

    include $this->template('account/voucher_info');
}elseif($op=='voucher_comment'){
    //通知
    $user = pdo_fetch('select a.reg_number,a.openid as qiye,c.openid as jizhang from '.tablename('customs_special_record').' a left join '.tablename('decl_user').' b on a.openid=b.openid left join '.tablename('mc_mapping_fans').' c on c.fanid=b.accounting_id where a.id=:id',[':id'=>intval($_GPC['id'])]);
    if($_GPC['identify']==1){
        //管理员通知记账和企业
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' => '你好，管理员对凭证['.$user['reg_number'].']进行评论，请点击查看。',
            'keyword1' => '查看评论',
            'keyword2' => '查看评论',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_info&id='.intval($_GPC['id']),
            'openid' => $user['qiye'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);  
        $post2 = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' => '你好，管理员对凭证['.$user['reg_number'].']进行评论，请点击查看。',
            'keyword1' => '查看评论',
            'keyword2' => '查看评论',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_info&id='.intval($_GPC['id']),
            'openid' => $user['jizhang'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);  
    }elseif($_GPC['identify']==2){
        //记账通知管理和企业
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' => '你好，记账端对凭证['.$user['reg_number'].']进行评论，请点击查看。',
            'keyword1' => '查看评论',
            'keyword2' => '查看评论',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_info&id='.intval($_GPC['id']),
            'openid' => $notice_manage,
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);  
        $post2 = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' => '你好，记账端对凭证['.$user['reg_number'].']进行评论，请点击查看。',
            'keyword1' => '查看评论',
            'keyword2' => '查看评论',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_info&id='.intval($_GPC['id']),
            'openid' => $user['qiye'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);  
    }elseif($_GPC['identify']==3){
        //企业通知管理和记账
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' => '你好，企业端对凭证['.$user['reg_number'].']进行评论，请点击查看。',
            'keyword1' => '查看评论',
            'keyword2' => '查看评论',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_info&id='.intval($_GPC['id']),
            'openid' => $notice_manage,
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);  
        $post2 = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' => '你好，企业端对凭证['.$user['reg_number'].']进行评论，请点击查看。',
            'keyword1' => '查看评论',
            'keyword2' => '查看评论',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_info&id='.intval($_GPC['id']),
            'openid' => $user['jizhang'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);  
    }

    //文件回复
    $reply_type = intval($_GPC['reply_type']);//0文字回复，1小回复，2大回复

    if(empty($reply_type)){
        //纯文字回复
        if(intval($_GPC['pid'])>0){
            $data = [
                'voucher_id'=>$_GPC['id'],
                'identify'=>$_GPC['identify'],
                'content'=>trim($_GPC['msg']),
                'createtime'=>time(),
                'pid'=>intval($_GPC['pid'])
            ];

            $res = pdo_insert('customs_accounting_comment',$data);
            if($res){
                show_json(1,['msg'=>'回复成功']);
            }
        }else{
            $data = [
                'voucher_id'=>$_GPC['id'],
                'identify'=>$_GPC['identify'],
                'content'=>trim($_GPC['msg']),
                'createtime'=>time(),
            ];
            $res = pdo_insert('customs_accounting_comment',$data);
            if($res){
                show_json(1,['msg'=>'评论成功']);
            }
        }
    }else{
        //文件回复
        if($reply_type==1){
            //小回复
            $data = [
                'voucher_id'=>$_GPC['id'],
                'identify'=>$_GPC['identify'],
                'file'=>json_encode($_GPC['file'],true),
                'pid'=>intval($_GPC['pid']),
                'createtime'=>time(),
            ];
            $res = pdo_insert('customs_accounting_comment',$data);
            if($res){
                show_json(1,['msg'=>'评论成功']);
            }
        }elseif($reply_type==2){
            //大回复
            $data = [
                'voucher_id'=>$_GPC['id'],
                'identify'=>$_GPC['identify'],
                'file'=>json_encode($_GPC['file'],true),
                'createtime'=>time(),
            ];

            $res = pdo_insert('customs_accounting_comment',$data);
            if($res){
                show_json(1,['msg'=>'回复成功']);
            }
        }
    }

}elseif($op=='taxes_info'){
    //税费确认
    $taxation_id = intval($_GPC['id']);
    $is_accounting = intval($_GPC['is_accounting']);

    if($_W['isajax']){
        $tax_id = intval($_GPC['tax_id']);
        $typ = intval($_GPC['typ']);
        $manager = intval($_GPC['manager']);
        if($typ==1){
            //当月税费无误
            $tax_info = pdo_fetch('select * from '.tablename('customs_accounting_taxation_declare').' where id=:id',[':id'=>$tax_id]);
            if($manager==1){
                //登记端发送消息给管理端和记账端

                //查找该税费记录商户的会计师
                $accounting = pdo_fetch('select b.openid,a.user_name from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$openid]);
                //修改为确认税费申报状态
                $res = pdo_update('customs_accounting_taxation_declare',['status'=>1],['id'=>$tax_id,'openid'=>$openid]);
                if($res){
                    //查找该对账月份批次号
                    $induce_info = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where id=:id',[':id'=>$tax_info['induce_id']]);
                    $induce_info['batch_ids'] = explode(',',$induce_info['batch_ids']);
                    foreach($induce_info['batch_ids'] as $k=>$v){
                        if(!empty($v)){
                            $voucher_batch = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$v]);
                            $voucher_batch['ids'] = explode(',',$voucher_batch['ids']);
                            foreach($voucher_batch['ids'] as $k2=>$v2){
                                if(!empty($v2)){
                                    $is_dzed = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and (status=8 or status=11) and `type`!=3',[':id'=>$v2]);
                                    if($is_dzed['id']>0){
                                        pdo_update('customs_special_record',['status'=>10],['id'=>$v2]);
                                    }
                                }
                            }
                        }
                    }

                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' => '你好，［'.$accounting['user_name'].'］已经确认'.str_replace('-','年',$tax_info['decl_date']).'月的税费申报，本次税费申报已完成，辛苦您！',
                        'keyword1' => '税费申报审核',
                        'keyword2' => '确认',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=taxes_info&is_accounting=1&id='.$tax_info['id'],
                        'openid' => $accounting['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

                    $post2 = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' => '你好，［'.$accounting['user_name'].'］已经确认［记账端］提交的'.str_replace('-','年',$tax_info['decl_date']).'月的税费申报。',
                        'keyword1' => '税费申报审核',
                        'keyword2' => '确认',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '',
                        'url' => '',
                        'openid' => $notice_manage,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

                    show_json(1,['msg'=>'确认对账成功！']);
                }
            }elseif($manager==2){
                //管理端发送消息给登记端
                $accounting = pdo_fetch('select b.openid,a.user_name from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$tax_info['openid']]);
                pdo_update('customs_accounting_taxation_declare',['manage_status'=>1],['id'=>$tax_id]);
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' => '['.$accounting['user_name'].']你好，你有一份'.str_replace('-','年',$tax_info['decl_date']).'月的税费已申请申报，请确认本月的税费申报',
                    'keyword1' => '税费申报审核',
                    'keyword2' => '请确认本月度的税费申报',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=taxes_info&id='.$tax_info['id'],
                    'openid' => $tax_info['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                show_json(1,['msg'=>'确认税费无误成功，已通知登记端!']);
            }
        }elseif($typ==2){
            //对账有误，反馈
            $tax_info = pdo_fetch('select * from '.tablename('customs_accounting_taxation_declare').' where id=:id',[':id'=>$tax_id]);
            if($manager==1){
                //登记端发送消息给管理端和记账端

                //查找该凭证商户的会计师
                $accounting = pdo_fetch('select b.openid,a.user_name from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$openid]);

                //修改为确认对账状态
                $res = pdo_update('customs_accounting_taxation_declare',['status'=>2],['id'=>$tax_id,'openid'=>$openid]);
                if($res){
                    //查找该对账月份批次号
                    $induce_info = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where id=:id',[':id'=>$tax_info['induce_id']]);
                    $induce_info['batch_ids'] = explode(',',$induce_info['batch_ids']);
                    foreach($induce_info['batch_ids'] as $k=>$v){
                        if(!empty($v)){
                            $voucher_batch = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$v]);
                            $voucher_batch['ids'] = explode(',',$voucher_batch['ids']);
                            foreach($voucher_batch['ids'] as $k2=>$v2){
                                if(!empty($v2)){
                                    $is_dzed = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and (status=8 or status=11) and `type`!=3',[':id'=>$v2]);
                                    if($is_dzed['id']>0){
                                        pdo_update('customs_special_record',['status'=>11],['id'=>$v2]);
                                    }
                                }
                            }
                        }
                    }
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' => '你好，［'.$accounting['user_name'].'］反馈你提交的'.str_replace('-','年',$tax_info['decl_date']).'月的税费申报有误需纠正，请与其联系处理。',
                        'keyword1' => '税费申报审核',
                        'keyword2' => '有误需纠',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=taxes_info&id='.$tax_info['id'],
                        'openid' => $notice_manage,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

                    show_json(1,['msg'=>'确认对账有误，已通知管理员！']);
                }
            }elseif($manager==2){
                //管理端发送消息给记账端
                $accounting = pdo_fetch('select b.openid,a.user_name,a.id from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$tax_info['openid']]);
                pdo_update('customs_accounting_taxation_declare',['manage_status'=>2],['id'=>$tax_id]);
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' => '你好，你提交的'.str_replace('-','年',$tax_info['decl_date']).'月的税费申报有误需纠，请与管理员联系处理',
                    'keyword1' => '税费申报审核',
                    'keyword2' => '有误需纠',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=tax_declare_edit&id='.$tax_info['id'],
                    'openid' => $accounting['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                show_json(1,['msg'=>'确认税费申报有误，已通知记账端！']);

            }
        }
    }else{
        if(empty($taxation_id)){
            exit('参数错误');
        }
        $tax_info = pdo_fetch('select a.*,b.user_name,d.name from '.tablename("customs_accounting_taxation_declare").' a left join '.tablename('decl_user').' b on b.openid=a.openid left join '.tablename('enterprise_members').' c on c.openid=b.openid left join '.tablename('enterprise_basicinfo').' d on d.member_id=c.id where a.id=:id',[':id'=>$taxation_id]);
        $is_show_queren = 0;
        if($openid==$tax_info['openid']){
            $is_show_queren = 1;//登记端
        }

        //营业收入
        $income = [];
        $tax_info['buss_type'] = explode(',',$tax_info['buss_type']);
        $tax_info['buss_project'] = explode(',',$tax_info['buss_project']);
        $tax_info['currency'] = explode(',',$tax_info['currency']);
        $tax_info['income_price'] = explode(',',$tax_info['income_price']);
        foreach($tax_info['buss_type'] as $k=>$v){
            if(!empty($v)){
                $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$tax_info['currency'][$k]]);
                $currency = explode('(',explode(')',$currency)[0])[1];
                $income[$k] = ['name'=>$v,'project'=>$tax_info['buss_project'][$k],'currency'=>$currency,'price'=>number_format($tax_info['income_price'][$k],2)];
            }
        }

        //营运支出
        $tax_info['expend_cate'] = explode(',',$tax_info['expend_cate']);
        $tax_info['expend_name'] = explode(',',$tax_info['expend_name']);
        $tax_info['expend_currency'] = explode(',',$tax_info['expend_currency']);
        $tax_info['expend_price'] = explode(',',$tax_info['expend_price']);
        $cost = [];
        foreach($tax_info['expend_cate'] as $k => $v){
            if(!empty($v)){
                if($v==1){
                    $name = '成本';
                }else if($v==2){
                    $name = '费用';
                }else if($v==3){
                    $name = '损失';
                }
                $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$tax_info['expend_currency'][$k]]);
                $currency = explode('(',explode(')',$currency)[0])[1];
                $cost[$k] = ['name'=>$name,'project'=>$tax_info['expend_name'][$k],'currency'=>$currency,'price'=>number_format($tax_info['expend_price'][$k],2)];
            }
        }

        //税金
        $tax_info['taxes_id'] = explode(',',$tax_info['taxes_id']);
        $tax_info['taxes_cate_info'] = explode(',',$tax_info['taxes_cate_info']);
        $tax_info['taxes_currency'] = explode(',',$tax_info['taxes_currency']);
        $tax_info['taxes_price'] = explode(',',$tax_info['taxes_price']);
        $taxes = [];
        foreach($tax_info['taxes_id'] as $k => $v){
            if(!empty($v)){
                $taxes_name = pdo_fetchcolumn('select name from '.tablename('customs_accounting_tax_category').' where id=:id',[':id'=>$v]);
                $project = pdo_fetchcolumn('select name from '.tablename('customs_accounting_tax_category').' where id=:id',[':id'=>$tax_info['taxes_cate_info'][$k]]);
                $currency = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code_value',[':code_value'=>$tax_info['taxes_currency'][$k]]);
                $currency = explode('(',explode(')',$currency)[0])[1];
                $taxes[$k] = ['name'=>$taxes_name,'project'=>$project,'currency'=>$currency,'price'=>number_format($tax_info['taxes_price'][$k],2)];
            }
        }

        //本期利润
        $current_profit = explode(',',$tax_info['current_profit']);
        $profit = [];
        foreach($current_profit as $k=>$v){
            $profit[$k]['currency'] = explode('-',$v)[0];
            $profit[$k]['money'] = explode('-',$v)[1];
        }
    }
    include $this->template('account/taxes_info');
}elseif($op=='selectMonth'){
    $opera = intval($_GPC['opera']);//记录要跳转的地方

    include $this->template('account/enterprise/select_month');
}

//1：只有商户才能使用登记端
if(!empty($openid)){
    $is_user = pdo_fetch('select a.id,b.user_status,b.account_auth from '.tablename('enterprise_members').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.openid=:openid and a.uniacid=:uni limit 1',array(':openid'=>$openid,':uni'=>$uniacid));

    if(empty($is_user)){
        //先去成为商户
        header('Location: '.$_W['siteroot'].'./app/index.php?i='.$_W['uniacid'].'&c=entry&do=enterprise&m=sz_yi&p=register');
    }elseif($is_user['user_status']==1 ){
        echo '<h3>商户已被禁用，请联系管理员！</h3>';exit;
    }elseif($is_user['user_status']==2){
        echo '<h3>商户正在审核，请耐心等待！</h3>';exit;
    }elseif(strstr($is_user['account_auth'],'1')=='' || empty($is_user['account_auth'])){
        echo '<h3>商户无此权限，请联系管理员！</h3>';exit;
    }
}

if($op=='display'){
    include $this->template('account/register');
}elseif($op=='basic_set'){
    //基本设置
    if($_W['isajax']){
        $account = pdo_fetchall('select * from '.tablename('customs_accounting_account').' where type=:type and openid=:openid order by id desc',[':type'=>intval($_GPC['type']),':openid'=>$openid]);
        show_json(1,['data'=>$account]);
    }else{
        $account = pdo_fetchall('select * from '.tablename('customs_accounting_account').' where type=1 and openid=:openid order by id desc',[':openid'=>$openid]);
        
        include $this->template('account/basic_set');
    }
}elseif($op=='add_account'){
    //添加账户
    if($_W['isajax']){
        $data['type'] = intval($_GPC['account_type']);
        $data['bank_account'] = trim($_GPC['bank_account']);
        if($data['type']==1 || $data['type']==2){
            $data['bank_name'] = trim($_GPC['bank_name']);
        }else{
            $data['bank_name'] = trim($_GPC['bank_name2']);   
        }
        $data['createtime'] = time();
        $data['name'] = trim($_GPC['name']);
        $data['openid'] = $openid;

        $res = pdo_insert('customs_accounting_account',$data);
        if($res){
            show_json(1, array('msg'=>'添加成功'));
        }
    }else{
        $bank = pdo_fetchall('select * from '.tablename('bank_list').' where 1 order by id asc');
        foreach($bank as $k=>$v){
            $arr2[] = ['title'=>$v['bank_name']];
        }
        $bank = json_encode($arr2,true);
        include $this->template('account/add_account');   
    }
}elseif($op=='edit_account'){
    $id = intval($_GPC['id']);
    $account = pdo_fetch('select * from '.tablename('customs_accounting_account').' where id=:id and openid=:openid limit 1',[':id'=>$id,':openid'=>$openid]);
    include $this->template('account/edit_account');
}elseif($op=='voucher_reg'){
    //凭证登记

    include $this->template('account/voucher_reg');
}elseif($op=='voucher_manage'){
    //凭证管理
    if($_W['isajax']) {
        $type = intval($_GPC['type']);
        $date = trim($_GPC['date']);
        $dateStart = strtotime($date.'-01 00:00:00');
        $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
        if($type==1){
            //已登记未汇总
            $list = pdo_fetchall('select * from '.tablename('customs_special_record').' where status=0 and openid=:openid and method=0 and ( createtime >= '.$dateStart.' and createtime <= '.$dateEnd.' ) order by createtime desc',[':openid'=>$openid]);
            foreach($list as $k=>$v){
                $list[$k]['createtime'] = date('Y年m月d日',$v['createtime']);
            }
            show_json(1, array('data'=>$list));
        }elseif($type==2){
            //已提交未确认
            $list = pdo_fetchall('select * from '.tablename('customs_accounting_register').' where status2=1 and openid=:openid and ( createtime >= '.$dateStart.' and createtime <= '.$dateEnd.' ) order by createtime desc',[':openid'=>$openid]);
            foreach($list as $k=>$v){
                $ids = explode(',',$v['ids']);
                foreach($ids as $kk=>$vv){
                    if(!empty($vv)){
                        $list[$k]['voucher_num'][$kk] = pdo_fetchcolumn('select reg_number from '.tablename('customs_special_record').' where id=:id',[':id'=>$vv]);
                    }
                }
                $list[$k]['createtime'] = date('Y年m月d日',$v['createtime']);
            }

            show_json(1, array('data'=>$list));
        }elseif($type==3){
            //已汇总未记账
            $list = pdo_fetchall('select * from '.tablename('customs_accounting_register').' where ( status>=1 and status<7) and openid=:openid and ( createtime >= '.$dateStart.' and createtime <= '.$dateEnd.' ) order by createtime desc',[':openid'=>$openid]);
            foreach($list as $k=>$v){
                $ids = explode(',',$v['ids']);
                $list[$k]['is_show'] = 0;
                foreach($ids as $kk=>$vv){
                    if(!empty($vv)){
                        $list[$k]['voucher_info'][$kk] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and (status=-2 or status=-1 or status=1 or status=2 or status=3 or status=5 or status=6 or status=7)',[':id'=>$vv]);
                        if($list[$k]['voucher_info'][$kk]['id']>0){
                            $list[$k]['is_show'] = 1;//已汇总、可记账无需复核、不可记账待复核
                        }
                    }
                }
                $list[$k]['createtime'] = date('Y年m月d日',$v['createtime']);
            }
            show_json(1, array('data'=>$list));
        }elseif($type==4){
            //已汇总已记账
            $list = pdo_fetchall('select * from '.tablename('customs_accounting_register').' where (status>=1 and status<7) and openid=:openid and ( createtime >= '.$dateStart.' and createtime <= '.$dateEnd.' ) order by createtime desc',[':openid'=>$openid]);
            foreach($list as $k=>$v){
                $ids = explode(',',$v['ids']);
                $list[$k]['is_show'] = 0;
                foreach($ids as $kk=>$vv){
                    if(!empty($vv)){
                        $list[$k]['voucher_info'][$kk] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and (status=4 or status=8 or status=9)',[':id'=>$vv]);
                        if($list[$k]['voucher_info'][$kk]['id']>0){
                            $list[$k]['is_show'] = 1;//有凭证信息已记账
                        }
                    }
                }
                $list[$k]['createtime'] = date('Y年m月d日',$v['createtime']);
            }
            show_json(1, array('data'=>$list));
        }elseif($type==5){
            //提交汇总

            //1、查询当月的凭证有无生成对账单，例如：2022-03就是查2022-02的账单
            $date = trim($_GPC['date']);
            $true_date = date('Y-m',strtotime($date." - 1 month"));
            $ishavelog = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where reconciliation_date=:dat and openid=:openid',[':dat'=>$true_date,':openid'=>$openid]);
            if($ishavelog['id']>0){
                show_json(-1,['msg'=>'当前月份已生成账单，不可继续添加汇总！']);
            }

            $ids = trim($_GPC['ids']);
            $batch_num = 'GP' . date('YmdH', time()) . str_pad(mt_rand(1, 999), 3, '0',
                        STR_PAD_LEFT);
            $data = [
                'openid'=>$openid,
                'batch_num'=>$batch_num,
                'ids'=>$ids,
                'status'=>7,
                'status2'=>'1',
                'submit_method'=>intval($_GPC['submit_method']),
                'createtime'=>time(),
            ];

            $res = pdo_insert('customs_accounting_register',$data);
            if($res){
                $id_arr = explode(',',$ids);
                foreach($id_arr as $k=>$v){
                    if(!empty($v)){
                        pdo_update('customs_special_record',['status'=>1],['openid'=>$openid,'id'=>$v]);
                    }
                }
                
                //2、填写物流信息后发送业务处理通知给管理员

                $user_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
                $post = json_encode([
                    'call'=>'sendNewInfoMsg',
                    'first' =>'商户('.$user_info['user_name'].')提交了凭证汇总(汇总批次号：'.$batch_num.')，请尽快确认！',
                    'keyword1' => $user_info['user_name'],
                    'keyword2' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=manage&date='.date('Y-m',time()),
                    'openid' => $notice_manage,
                    'temp_id' => 'zXF01McqJ_fW7hGAYvQR4hO7X1MI7fmKMzsn7gHScIc'
                ]);

                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                
                show_json(1, array('msg'=>'提交成功，请等待后台审核确认！'));
            }
        }elseif($type==6){
            //填写快递信息
            if(intval($_GPC['id'])){
                $data = [
                    'express_id'=>trim($_GPC['express_id']),
                    // 'express_time'=>strtotime($_GPC['express_time']),
                    'express_num'=>trim($_GPC['express_num']),
                    'status'=>1,
                    'status2'=>2
                ];
                $res = pdo_update('customs_accounting_register',$data,['id'=>intval($_GPC['id'])]);    
                if($res){
                    //2、通知会计师
                    $info = pdo_fetch('select a.user_name,d.openid from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' d on d.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$openid]);

                    $post = json_encode([
                        'call'=>'collectionNotice',
                        'first' =>'您有一件快递即将到达，请知悉！',
                        'keyword1' => $info['user_name'],
                        'keyword2' => $data['express_id'],
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'keyword4' => $data['express_num'],
                        'keyword5' => '凭证寄出',
                        'remark' => '',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_sign&date='.date('Y-m',time()),
                        'openid' => $info['openid'],
                        'temp_id' => 'VncuMA4q8QY-2qhz7j-H7_ZFgA84l324QifPgndtCGI'
                    ]);
        
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                    
                    //2、通知管理端
                    $post2 = json_encode([
                        'call'=>'collectionNotice',
                        'first' => '商户['.$info['user_name'].']已发出快递，请知悉！',
                        'keyword1' => $info['user_name'],
                        'keyword2' => $data['express_id'],
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'keyword4' => $data['express_num'],
                        'keyword5' => '凭证寄出',
                        'remark' => '',
                        'url' => '',
                        'temp_id' => 'VncuMA4q8QY-2qhz7j-H7_ZFgA84l324QifPgndtCGI',
                        'openid' => $notice_manage,
                    ],true);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                    
                    show_json(1,['msg'=>'填写物流信息成功，请等待签收审核！']);
                }
            }
        }elseif($type==7){
            //查看该汇总批次号有无填写过物流信息
            $id=intval($_GPC['id']);
            if(!empty($id)){
                $reg = pdo_fetch('select status from '.tablename('customs_accounting_register').' where openid=:openid and id=:id',[':openid'=>$openid,':id'=>$id]);
                if($reg['status']==8 || $reg['status']==0){
                    show_json(1);
                }elseif($reg['status']==7){
                    show_json(0);
                }
            }
        }elseif($type==8){
            //撤回快递
            $id=intval($_GPC['id']);
            if(!empty($id)){
                $res = pdo_update('customs_special_record',[
                    'status'=>-2,
                    ],['id'=>$id]);
                if($res){
                   //2、通知管理员
                    $merch_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
                    $voucher_info = pdo_fetch('select voucher from '.tablename('customs_special_record').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$openid]);
                    $post2 = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'商户['.$merch_info['user_name'].']对['.$voucher_info['voucher'].']凭证进行撤回快递操作，请知悉。',
                        'keyword1' => '凭证审核',
                        'keyword2' => '撤回快递',
                        'keyword3' => '撤回时间：'.date('Y-m-d H:i:s',time()),
                        'remark' => '',
                        'url' => '',
                        'openid' => $notice_manage,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                    
                   show_json(1,['msg'=>'撤回快递成功！']);   
                }
            }
        }elseif($type==9){
            //有待复核未记账
            $id=intval($_GPC['id']);
            
        }elseif($type==10){
            //不予记账
            $id = intval($_GPC['id']);
            if(!empty($id)){
                $res = pdo_update('customs_special_record',['status'=>-1],['id'=>$id,'openid'=>$openid]);
                $merch_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
                $voucher_info = pdo_fetch('select voucher from '.tablename('customs_special_record').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$openid]);
                //通知管理员
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户['.$merch_info['user_name'].']对凭证['.$voucher_info['voucher'].']不予记账，请知悉！',
                    'keyword1' => '凭证审核',
                    'keyword2' => '不予记账',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => '',
                    'openid' => $notice_manage,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                
                if($res){
                    show_json(1,['msg'=>'操作成功！']);
                }
            }
        }
    }else{
//        $list = pdo_fetchall('select * from '.tablename('customs_special_record').' where status=0 and openid=:openid order by createtime desc',[':openid'=>$openid]);
        $express = pdo_fetchall('select * from '.tablename('customs_express_company_code').' where 1');
        foreach($express as $k=>$v){
            $arr2[] = ['title'=>$v['name']];
        }
        $express = json_encode($arr2,true);
        $view = intval($_GPC['view'])>0?intval($_GPC['view']):2;
        include $this->template('account/voucher_manage');
    }
}elseif($op=='special_purpose_voucher'){
    //专用记账凭证
    $date = $_GPC['date'];

    $date2 = date('Y-m',strtotime($date." - 1 month"));
    $is_havelog = pdo_fetch('select id from '.tablename('customs_accounting_reconciliation').' where openid=:openid and reconciliation_date="'.$date2.'"',[':openid'=>$openid]);
    if($is_havelog['id']>0){
        echo '
            <html>
                <head>
                <title></title>
                </head>
            <body>
            <script>
            //js调用php
            alert("上月['.$date2.']已生成账单,本月['.$date.']不可再新增凭证！");
            setTimeout(function(){
                window.history.back();
            },500);
            </script>
            </body>
            </html>';
        exit;
    }
    include $this->template('account/special_purpose_voucher');
}elseif($op=='general_purpose_voucher'){
    //通用记账凭证
    echo '<h3>正在开发中...</h3>';exit;
    include $this->template('account/general_purpose_voucher');
}elseif($op=='collect_reg'){
    //收款登记
    if($_W['isajax']){
        $add_reg = intval($_GPC['add_reg']);
        if($add_reg==1){

            $data = [
                'openid'=>$openid,
                'type'=>1,
                'voucher_type'=>intval($_GPC['voucher_type']),
                'voucher_type1'=>intval($_GPC['voucher_type'])==1?intval($_GPC['voucher_type1']):'',
                'voucher_type1_2'=>intval($_GPC['voucher_type1'])==2?intval($_GPC['voucher_type1_2']):'',
                'voucher_type2'=>intval($_GPC['voucher_type'])==2?intval($_GPC['voucher_type2']):'',
                'voucher_type2_1'=>intval($_GPC['voucher_type2'])==1?intval($_GPC['voucher_type2_1']):'',
                'voucher_type2_2'=>intval($_GPC['voucher_type2'])==2?intval($_GPC['voucher_type2_2']):'',
                'voucher'=>trim($_GPC['voucher']),
                'voucher_date'=>trim($_GPC['voucher_date']),
                'content'=>intval($_GPC['content']),
                'tax_classify_sel'=>intval($_GPC['content'])==1?trim($_GPC['tax_classify_sel']):'',
                'tax_classify_inp'=>intval($_GPC['content'])==2?trim($_GPC['tax_classify_inp']):'',
                'currency'=>trim($_GPC['currency']),
                'currency2'=>trim($_GPC['currency2']),
                'money_status'=>intval($_GPC['money_status']),
                'bill_status'=>intval($_GPC['bill_status']),
                'trade_price'=>trim($_GPC['trade_price']),
                'trade_price2'=>trim($_GPC['trade_price2']),
                'trade_rate'=>trim($_GPC['trade_rate']),
//                'attach_cert'=>intval($_GPC['attach_cert']),
                'collect_type'=>intval($_GPC['collect_type']),
                'transfer_collect_type'=>intval($_GPC['transfer_collect_type']),
                'collect_account'=>intval($_GPC['collect_account']),//收款账号
//                'account_receipt'=>json_encode($_GPC['account_receipt']),//入账回单
                'submit_method'=>intval($_GPC['submit_method']),
                'express_voucher'=>json_encode($_GPC['express_voucher']),
                'diy_remark' => trim($_GPC['diy_remark']),
                'createtime'=>time(),
            ];
//            print_r(json_encode($_GPC['express_voucher']));die;
            if(intval($_GPC['voucher_id'])>0){
                //早前开票，则修改收款信息与、随附凭证信息
                $data = [
                    'currency'=>trim($_GPC['currency']),
                    'currency2'=>trim($_GPC['currency2']),
                    'money_status'=>intval($_GPC['money_status']),
                    'bill_status'=>intval($_GPC['bill_status']),
                    'trade_price'=>trim($_GPC['trade_price']),
                    'trade_price2'=>trim($_GPC['trade_price2']),
                    'trade_rate'=>trim($_GPC['trade_rate']),
//                    'attach_cert'=>intval($_GPC['attach_cert']),
                    'collect_type'=>intval($_GPC['collect_type']),
                    'transfer_collect_type'=>intval($_GPC['transfer_collect_type']),
                    'collect_account'=>intval($_GPC['collect_account']),//收款账号
//                    'account_receipt'=>json_encode($_GPC['account_receipt']),//入账回单
                    'submit_method'=>intval($_GPC['submit_method']),
                    'express_voucher'=>json_encode($_GPC['express_voucher']),
                ];
                $res = pdo_update('customs_special_record',$data,['id'=>intval($_GPC['voucher_id']),'openid'=>$openid]);
                if($res){
                    show_json(1, array('msg'=>'录入信息成功'));
                }
            }else{
                //判断凭证编号和金额是否已存在
                if(!empty($data['voucher']) && !empty($data['trade_price'])){
                    $ishavelog = pdo_fetch('select id from '.tablename('customs_special_record').' where voucher=:voucher and openid=:openid and trade_price=:trade_price',[':voucher'=>$data['voucher'],':openid'=>$data['openid'],':trade_price'=>$data['trade_price']]);
                    if($ishavelog['id']>0){
                        show_json(-1,['msg'=>'您好，该凭证已经登记，为免重复登记造成数据错乱，敬请核查后再登记。']);
                    }
                }
                $res = pdo_insert('customs_special_record',$data);
                $insert_id = pdo_insertid();
                if($insert_id>0){
                    //登记编号
                    $prefix = '';
                    if($data['money_status']==1){$prefix='YS';}elseif($data['money_status']==2){$prefix='WS';}elseif($data['money_status']==3){$prefix='YS';}
                    $reg_number = $prefix.date('Ymd',time()).str_pad($insert_id,2,'0',STR_PAD_LEFT);
                    //修改原始凭证名称
                    $file_head = $_SERVER['DOCUMENT_ROOT'].'/attachment/';
                    $new_express_voucher = [];
                    if(!empty($data['express_voucher'])){
                        $express_voucher = json_decode($data['express_voucher'],true);
                        foreach($express_voucher as $k=>$v){
                            if(file_exists($file_head.$v)){
                                $file_position = str_split($v,17)[0];
                                $ext = explode('.',$v)[1];
                                $new_name = $file_position.$reg_number.'_'.str_pad($k+1,2,'0',STR_PAD_LEFT).'.'.$ext;
//                print_r($file_head.$new_name);die;
                                if(rename($file_head.$v,$file_head.$new_name)){
                                    array_push($new_express_voucher,$new_name);
                                }
                            }
                        }
                    }

                    $res = pdo_update('customs_special_record',['reg_number'=>$reg_number,'express_voucher'=>json_encode($new_express_voucher)],['id'=>$insert_id]);
                    if($res){
                        show_json(1,['msg'=>'创建成功']);
                    }
                }
            }
        }elseif($add_reg==2){
            //查询“已开票，未收款”的已登记、已汇总、已记账、已对账
            $list = pdo_fetchall('select id,voucher,status from '.tablename('customs_special_record').' where openid=:openid and (status=0 or status=1 or status=4 or status=8) and `type`=1 and money_status=2 order by id desc',[':openid'=>$openid]);
            foreach($list as $k=>$v){
                if($v['status']==0){
                    $list[$k]['status']='已登记';
                }elseif($v['status']==1){
                    $list[$k]['status']='已汇总';
                }elseif($v['status']==4){
                    $list[$k]['status']='已记账';
                }elseif($v['status']==8){
                    $list[$k]['status']='已对账';
                }
            }
            show_json(1,['data'=>$list]);
        }elseif($add_reg==3){
            //查找该凭证已登记信息
            $id = intval($_GPC['id']);
            $data = pdo_fetch('select * from '.tablename('customs_special_record').' where openid=:openid and id=:id',[':openid'=>$openid,':id'=>$id]);
            if($data['voucher_type']==1 && $data['voucher_type1']==1){
                $data['voucher_type_name'] = '增值税专用发票';
            }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==1){
                $data['voucher_type_name'] = '增值税普通发票(折叠票)';
            }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==2){
                $data['voucher_type_name'] = '增值税普通发票（卷式）';
            }elseif($data['voucher_type']==1 && $data['voucher_type1']==3){
                $data['voucher_type_name'] = '增值税电子普通发票';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1){
                $data['voucher_type_name'] = '支票进账单';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2){
                $data['voucher_type_name'] = '托收收账单';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3){
                $data['voucher_type_name'] = '多余款通知单';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==1){
                $data['voucher_type_name'] = '收据存根联';
            }elseif($data['voucher_type']==3){
                $data['voucher_type_name'] = '形式发票';
            }
            
            if($data['content']==1){
                $data['tax_classify_name']=$data['tax_classify_sel'];
            }elseif($data['content']==2){
                $data['tax_classify_name']=$data['tax_classify_inp'];
            }
            show_json(1,['data'=>$data]);
        }elseif($add_reg==4){
            //检测选择凭证日期时判断当月有无生成账单
            $date = explode('-',trim($_GPC['date']));
            $date = $date[0].'-'.$date[1];
            $date2 = date('Y-m',strtotime($date." - 1 month"));
            $is_havelog = pdo_fetch('select id from '.tablename('customs_accounting_reconciliation').' where openid=:openid and reconciliation_date="'.$date2.'"',[':openid'=>$openid]);
            if($is_havelog['id']>0){
                show_json(1,['msg'=>'当月已生成账单，不可操作！']);
            }else{
                show_json(0);
            }
        }else{
            $type = intval($_GPC['type']);
            $account = pdo_fetchall('select * from '.tablename('customs_accounting_account').' where openid=:openid and type=:type order by id desc',[':openid'=>$openid,':type'=>$type]);

            show_json(1, array('data'=>$account));
        }
    }else{
        //税收分类
        $tax_classify = pdo_fetchall('select * from '.tablename('customs_account_tax_code').' where 1 order by id asc');

//        foreach($tax_classify as $k=>$v){
//            $arr2[] = ['title'=>$v['name']];
//        }
//        $tax_classify = json_encode($arr2,true);

        //币种
        $currency = pdo_fetchall('select * from '.tablename('currency').' where 1');

        include $this->template('account/collect_reg');
    }
}elseif($op=='pay_reg'){
    //付款登记
    if($_W['isajax']){
        $add_reg = intval($_GPC['add_reg']);
        if($add_reg==1){
            $data = [
                'openid'=>$openid,
                'type'=>2,
                'voucher_type'=>intval($_GPC['voucher_type']),
                'voucher_type1'=>intval($_GPC['voucher_type'])==1?intval($_GPC['voucher_type1']):'',
                'voucher_type1_2'=>intval($_GPC['voucher_type1'])==2?intval($_GPC['voucher_type1_2']):'',
                'voucher_type2'=>intval($_GPC['voucher_type'])==2?intval($_GPC['voucher_type2']):'',
                'voucher_type2_1'=>intval($_GPC['voucher_type2'])==1?intval($_GPC['voucher_type2_1']):'',
                'voucher_type3'=>intval($_GPC['voucher_type2_1'])==1?intval($_GPC['voucher_type3']):'',
                'voucher_type3_1'=>intval($_GPC['voucher_type3'])==1?intval($_GPC['voucher_type3_1']):'',
                'voucher_type4_1'=>intval($_GPC['voucher_type3_1'])==1?intval($_GPC['voucher_type4_1']):'',
                'voucher_type4_2'=>intval($_GPC['voucher_type3_1'])==2?intval($_GPC['voucher_type4_2']):'',
                'voucher_type3_2'=>intval($_GPC['voucher_type3'])==2?intval($_GPC['voucher_type3_2']):'',
                'voucher_type5'=>intval($_GPC['voucher_type2_1'])==2?intval($_GPC['voucher_type5']):'',
                'voucher_type5_1'=>intval($_GPC['voucher_type2'])==1?intval($_GPC['voucher_type5_1']):'',
                'voucher_type6'=>intval($_GPC['voucher_type2_1'])==3?intval($_GPC['voucher_type6']):'',
                'voucher_type2_2'=>intval($_GPC['voucher_type2'])==2?intval($_GPC['voucher_type2_2']):'',
                'voucher'=>trim($_GPC['voucher']),
                'voucher_date'=>trim($_GPC['voucher_date']),
                'content'=>intval($_GPC['content']),
                'tax_classify_sel'=>intval($_GPC['content'])==1?trim($_GPC['tax_classify_sel']):'',
                'tax_classify_inp'=>intval($_GPC['content'])==2?trim($_GPC['tax_classify_inp']):'',
                'currency'=>trim($_GPC['currency']),
                'currency2'=>trim($_GPC['currency2']),
                'money_status'=>intval($_GPC['money_status']),
                'bill_status'=>intval($_GPC['bill_status']),
                'trade_price'=>trim($_GPC['trade_price']),
                'trade_price2'=>trim($_GPC['trade_price2']),
                'trade_rate'=>trim($_GPC['trade_rate']),
//                'attach_cert'=>intval($_GPC['attach_cert']),
                'collect_type'=>intval($_GPC['collect_type']),
                'transfer_collect_type'=>intval($_GPC['transfer_collect_type']),
                'collect_account'=>intval($_GPC['collect_account']),//收款账号
//                'account_receipt'=>json_encode($_GPC['account_receipt']),
                'submit_method'=>intval($_GPC['submit_method']),
                'express_voucher'=>json_encode($_GPC['express_voucher']),
                'diy_remark' => trim($_GPC['diy_remark']),
                'createtime'=>time(),
            ];
            if(intval($_GPC['voucher_id'])>0){
                //早前开票，则修改收款信息与、随附凭证信息
                $data = [
                    'currency'=>trim($_GPC['currency']),
                    'currency2'=>trim($_GPC['currency2']),
                    'money_status'=>intval($_GPC['money_status']),
                    'bill_status'=>intval($_GPC['bill_status']),
                    'trade_price'=>trim($_GPC['trade_price']),
                    'trade_price2'=>trim($_GPC['trade_price2']),
                    'trade_rate'=>trim($_GPC['trade_rate']),
//                    'attach_cert'=>intval($_GPC['attach_cert']),
                    'collect_type'=>intval($_GPC['collect_type']),
                    'transfer_collect_type'=>intval($_GPC['transfer_collect_type']),
                    'collect_account'=>intval($_GPC['collect_account']),//收款账号
//                    'account_receipt'=>json_encode($_GPC['account_receipt']),
                    'submit_method'=>intval($_GPC['submit_method']),
                    'express_voucher'=>json_encode($_GPC['express_voucher']),
                ];
                $res = pdo_update('customs_special_record',$data,['id'=>intval($_GPC['voucher_id']),'openid'=>$openid]);
                if($res){
                    show_json(1, array('msg'=>'录入信息成功'));
                }
            }else{
                //判断凭证编号和金额是否已存在
                if(!empty($data['voucher']) && !empty($data['trade_price'])){
                    $ishavelog = pdo_fetch('select id from '.tablename('customs_special_record').' where voucher=:voucher and openid=:openid and trade_price=:trade_price',[':voucher'=>$data['voucher'],':openid'=>$data['openid'],':trade_price'=>$data['trade_price']]);
                    if($ishavelog['id']>0){
                        show_json(-1,['msg'=>'您好，该凭证已经登记，为免重复登记造成数据错乱，敬请核查后再登记。']);
                    }
                }
                $res = pdo_insert('customs_special_record',$data);
                $insert_id = pdo_insertid();
                if($insert_id>0){
                    $prefix = '';
                    if($data['money_status']==4){$prefix='YF';}elseif($data['money_status']==5){$prefix='WF';}elseif($data['money_status']==6){$prefix='YF';}
                    $reg_number = $prefix.date('Ymd',time()).str_pad($insert_id,2,'0',STR_PAD_LEFT);
                    //修改原始凭证名称
                    $file_head = $_SERVER['DOCUMENT_ROOT'].'/attachment/';
                    $new_express_voucher = [];
                    if(!empty($data['express_voucher'])){
                        $express_voucher = json_decode($data['express_voucher'],true);
                        foreach($express_voucher as $k=>$v){
                            if(file_exists($file_head.$v)){
                                $file_position = str_split($v,17)[0];
                                $ext = explode('.',$v)[1];
                                $new_name = $file_position.$reg_number.'_'.str_pad($k+1,2,'0',STR_PAD_LEFT).'.'.$ext;
//                print_r($file_head.$new_name);die;
                                if(rename($file_head.$v,$file_head.$new_name)){
                                    array_push($new_express_voucher,$new_name);
                                }
                            }
                        }
                    }
                    $res = pdo_update('customs_special_record',['reg_number'=>$reg_number,'express_voucher'=>json_encode($new_express_voucher)],['id'=>$insert_id]);
                    if($res){
                        show_json(1,['msg'=>'创建成功']);
                    }
                }
            }
        }elseif($add_reg==2){
            //查询“已开票，未付款”的已登记、已汇总、已记账、已对账
            $list = pdo_fetchall('select id,voucher,status from '.tablename('customs_special_record').' where openid=:openid and (status=0 or status=1 or status=4 or status=8) and `type`=2 and money_status=5 order by id desc',[':openid'=>$openid]);
            foreach($list as $k=>$v){
                if($v['status']==0){
                    $list[$k]['status']='已登记';
                }elseif($v['status']==1){
                    $list[$k]['status']='已汇总';
                }elseif($v['status']==4){
                    $list[$k]['status']='已记账';
                }elseif($v['status']==8){
                    $list[$k]['status']='已对账';
                }
            }
            show_json(1,['data'=>$list]);
        }elseif($add_reg==3){
            //查找该凭证已登记信息
            $id = intval($_GPC['id']);
            $data = pdo_fetch('select * from '.tablename('customs_special_record').' where openid=:openid and id=:id',[':openid'=>$openid,':id'=>$id]);
            if($data['voucher_type']==1 && $data['voucher_type1']==1){
                $data['voucher_type_name'] = '增值税专用发票';
            }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==1){
                $data['voucher_type_name'] = '增值税普通发票(折叠票)';
            }elseif($data['voucher_type']==1 && $data['voucher_type1']==2 && $data['voucher_type1_2']==2){
                $data['voucher_type_name'] = '增值税普通发票（卷式）';
            }elseif($data['voucher_type']==1 && $data['voucher_type1']==3){
                $data['voucher_type_name'] = '增值税电子普通发票';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==1 && $data['voucher_type4_1']==1){
                $data['voucher_type_name'] = '非税收入统一票据';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==1 && $data['voucher_type4_1']==2){
                $data['voucher_type_name'] = '非税收入一般缴款书';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==2 && $data['voucher_type4_2']==1){
                $data['voucher_type_name'] = '定额票据';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==1 && $data['voucher_type3_1']==2 && $data['voucher_type4_2']==2){
                $data['voucher_type_name'] = '非定额票据';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==1){
                $data['voucher_type_name'] = '社会团体会费收据';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==2){
                $data['voucher_type_name'] = '医疗票据';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==1 && $data['voucher_type3']==2 && $data['voucher_type3_2']==3){
                $data['voucher_type_name'] = '捐赠收据';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==1){
                $data['voucher_type_name'] = '税收缴款书';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==2){
                $data['voucher_type_name'] = '税收收入退还书';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==3){
                $data['voucher_type_name'] = '税收完税证明';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==4 && $data['voucher_type5_1']==1){
                $data['voucher_type_name'] = '税收缴款书(出口货物劳务专用)';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==4 && $data['voucher_type5_1']==2){
                $data['voucher_type_name'] = '出口货物完税分割单';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==2 && $data['voucher_type5']==5){
                $data['voucher_type_name'] = '印花税专用税收票证';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3 && $data['voucher_type6']==1){
                $data['voucher_type_name'] = '海关税款专用缴款书';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==1 && $data['voucher_type2_1']==3 && $data['voucher_type6']==2){
                $data['voucher_type_name'] = '海关货物滞报金专用票据';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==1){
                $data['voucher_type_name'] = '工资结算单';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==2){
                $data['voucher_type_name'] = '费用报销单';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==3){
                $data['voucher_type_name'] = '借款收据';
            }elseif($data['voucher_type']==2 && $data['voucher_type2']==2 && $data['voucher_type2_2']==4){
                $data['voucher_type_name'] = '领款收据';
            }elseif($data['voucher_type']==3){
                $data['voucher_type_name'] = '形式发票';
            }
            
            if($data['content']==1){
                $data['tax_classify_name']=$data['tax_classify_sel'];
            }elseif($data['content']==2){
                $data['tax_classify_name']=$data['tax_classify_inp'];
            }
            show_json(1,['data'=>$data]);
        }elseif($add_reg==4){
            //检测选择凭证日期时判断当月有无提交税费申报
            $date = explode('-',trim($_GPC['date']));
            $date = $date[0].'-'.$date[1];
            $date2 = date('Y-m',strtotime($date." - 1 month"));
            $is_havelog = pdo_fetch('select id from '.tablename('customs_accounting_reconciliation').' where openid=:openid and reconciliation_date="'.$date2.'"',[':openid'=>$openid]);
            if($is_havelog['id']>0){
                show_json(1,['msg'=>'当月已生成账单，不可操作！']);
            }else{
                show_json(0);
            }
        }else{
            $type = intval($_GPC['type']);
            $account = pdo_fetchall('select * from '.tablename('customs_accounting_account').' where openid=:openid and type=:type order by id desc',[':openid'=>$openid,':type'=>$type]);

            show_json(1, array('data'=>$account));
        }
    }else {
        //税收分类
        $tax_classify = pdo_fetchall('select * from ' . tablename('customs_account_tax_code') . ' where 1 order by id asc');
//        foreach($tax_classify as $k=>$v){
//            $arr2[] = ['title'=>$v['name']];
//        }
//        $tax_classify = json_encode($arr2,true);
        //币种
        $currency = pdo_fetchall('select * from ' . tablename('currency') . ' where 1');
        include $this->template('account/pay_reg');
    }
}elseif($op=='bill_reg'){
    //对账单登记
    if($_W['isajax']){
        //登记编号

        $data = [
            'openid' => $_W['openid'],
            'type' => 3,
            'bill_account_type' => intval($_GPC['bill_account_type']),
            'bill_account_id' => intval($_GPC['bill_account_id']),
            'bill_startDate' => trim($_GPC['bill_startDate']),
            'bill_endDate' => trim($_GPC['bill_endDate']),
            'currency3' => trim($_GPC['currency3']),
            'bill_price' => trim($_GPC['bill_price']),
            'bill_file' => json_encode($_GPC['bill_file'],true),
            'submit_method' => intval($_GPC['submit_method']),
            'diy_remark' => trim($_GPC['diy_remark']),
            'createtime' => time()
        ];
        pdo_insert('customs_special_record',$data);
        $insert_id = pdo_insertid();
        if($insert_id>0){
            $reg_number = 'DZ'.date('Ymd',time()).str_pad($insert_id,2,'0',STR_PAD_LEFT);
            //修改原始凭证名称
            $file_head = $_SERVER['DOCUMENT_ROOT'].'/attachment/';
            $new_express_voucher = [];
            if(!empty($data['bill_file'])){
                $express_voucher = json_decode($data['bill_file'],true);
                foreach($express_voucher as $k=>$v){
                    if(file_exists($file_head.$v)){
                        $file_position = str_split($v,17)[0];
                        $ext = explode('.',$v)[1];
                        $new_name = $file_position.$reg_number.'_'.str_pad($k+1,2,'0',STR_PAD_LEFT).'.'.$ext;
                        if(rename($file_head.$v,$file_head.$new_name)){
                            array_push($new_express_voucher,$new_name);
                        }
                    }
                }
            }
            $res = pdo_update('customs_special_record',['reg_number'=>$reg_number,'bill_file'=>json_encode($new_express_voucher)],['id'=>$insert_id]);
            if($res){
                show_json(1,['msg'=>'创建成功']);
            }
        }
    }else{
        //币种
        $currency = pdo_fetchall('select * from ' . tablename('currency') . ' where 1');
        include $this->template('account/bill_reg');
    }

}elseif($op=='bill_edit'){
    //对账单修改
    $type = intval($_GPC['type']);
    $id = intval($_GPC['id']);
    if(empty($id)){
        exit('参数错误');
    }
    if($_W['isajax']){
        $voucher_info = pdo_fetch('select reg_number from '.tablename('customs_special_record').' where id=:id and openid=:openid and `type`=:typ',[':id'=>$id,':openid'=>$openid,':typ'=>$type]);
        $data = [
            'bill_account_type' => intval($_GPC['bill_account_type']),
            'bill_account_id' => intval($_GPC['bill_account_id']),
            'bill_startDate' => trim($_GPC['bill_startDate']),
            'bill_endDate' => trim($_GPC['bill_endDate']),
            'currency3' => trim($_GPC['currency3']),
            'bill_price' => trim($_GPC['bill_price']),
            'bill_file' => json_encode($_GPC['bill_file'],true),
            'submit_method' => intval($_GPC['submit_method']),
            'updatetime'=>time()
        ];

        if(intval($_GPC['status'])==3){
            //有待复核未记账-复核补充
            $data = array_merge($data,[
                'status'=>5
            ]);

            //通知会计师,修改补充已完成
            $info = pdo_fetch('select a.voucher,c.openid,b.user_name,a.reg_number from '.tablename('customs_special_record').' a left join '.tablename('decl_user').' b on b.openid=a.openid left join '.tablename('mc_mapping_fans').' c on c.fanid=b.accounting_id where a.id=:id and a.openid=:openid',[':id'=>$id,':openid'=>$openid]);
            $post = json_encode([
                'call'=>'sendNewInfoMsg',
                'first' =>'您好，商户['.$info['user_name'].']的登记编号为'.$info['reg_number'].'已复核，请点击查看。',
                'keyword1' => $info['user_name'],
                'keyword2' => date('Y-m-d H:i:s',time()),
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_keep&view=2&date='.date('Y-m',time()),
                'openid' => $info['openid'],
                'temp_id' => '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

            //通知管理员
            $post2 = json_encode([
                'call'=>'sendNewInfoMsg',
                'first' =>'商户['.$info['user_name'].']的登记编号为'.$info['reg_number'].'已复核，请知悉。',
                'keyword1' => $info['user_name'],
                'keyword2' => date('Y-m-d H:i:s',time()),
                'remark' => '',
                'url' => '',
                'openid' => $notice_manage,
                'temp_id' => '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
        }

        //修改原始凭证名称
        $file_head = $_SERVER['DOCUMENT_ROOT'].'/attachment/';
        $new_express_voucher = [];
        $reg_number = $voucher_info['reg_number'];
        if(!empty($data['bill_file'])){
            $express_voucher = json_decode($data['bill_file'],true);
            foreach($express_voucher as $k=>$v){
                if(file_exists($file_head.$v)){
                    $file_position = str_split($v,17)[0];
                    $ext = explode('.',$v)[1];
                    $new_name = $file_position.$reg_number.'_'.str_pad($k+1,2,'0',STR_PAD_LEFT).'.'.$ext;
//                print_r($file_head.$new_name);die;
                    if(rename($file_head.$v,$file_head.$new_name)){
                        array_push($new_express_voucher,$new_name);
                    }
                }
            }
        }
        $data['bill_file'] = json_encode($new_express_voucher);
        $res = pdo_update('customs_special_record',$data,['id'=>$id,'type'=>$type,'openid'=>$openid]);
        if($res){
            show_json(1,['msg'=>'修改成功']);
        }
    }else{
        //币种
        $currency = pdo_fetchall('select * from ' . tablename('currency') . ' where 1');
        $data = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and openid=:openid and `type`=:typ',[':id'=>$id,':openid'=>$openid,':typ'=>$type]);
        $data['bill_file'] = json_decode($data['bill_file'],true);

        //获取银行账户
        $data['account_info'] = pdo_fetchall('select * from '.tablename('customs_accounting_account').' where openid=:openid and `type`=:typ order by id desc',[':openid'=>$data['openid'],':typ'=>$data['bill_account_type']]);
        include $this->template('account/bill_edit');
    }
}elseif($op=='reg_edit'){
    //登记修改
    $type = intval($_GPC['type']);
    $id = intval($_GPC['id']);
    if(empty($id)){
        exit('参数错误');
    }
    
    if($type==1){
        //收款登记修改
        if($_W['isajax']){
            //查询是否在审核中
            $is_exam = pdo_fetch('select status,reg_number from '.tablename('customs_special_record').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$openid]);
            if($is_exam['status']==5){
                show_json(-1,['msg'=>'凭证已在审核中，请耐心等待！']);
            }
            
            $data = [
                'voucher_type'=>intval($_GPC['voucher_type']),
                'voucher_type1'=>intval($_GPC['voucher_type'])==1?intval($_GPC['voucher_type1']):'0',
                'voucher_type1_2'=>intval($_GPC['voucher_type1'])==2?intval($_GPC['voucher_type1_2']):'0',
                'voucher_type2'=>intval($_GPC['voucher_type'])==2?intval($_GPC['voucher_type2']):'0',
                'voucher_type2_1'=>intval($_GPC['voucher_type2'])==1?intval($_GPC['voucher_type2_1']):'0',
                'voucher_type2_2'=>intval($_GPC['voucher_type2'])==2?intval($_GPC['voucher_type2_2']):'0',
                'voucher'=>trim($_GPC['voucher']),
                'voucher_date'=>trim($_GPC['voucher_date']),
                'content'=>intval($_GPC['content']),
                'tax_classify_sel'=>intval($_GPC['content'])==1?trim($_GPC['tax_classify_sel']):'',
                'tax_classify_inp'=>intval($_GPC['content'])==2?trim($_GPC['tax_classify_inp']):'',
                'currency'=>trim($_GPC['currency']),
                'currency2'=>trim($_GPC['currency2']),
                'money_status'=>intval($_GPC['money_status']),
                'bill_status'=>intval($_GPC['bill_status']),
                'trade_price'=>trim($_GPC['trade_price']),
                'trade_price2'=>trim($_GPC['trade_price2']),
                'trade_rate'=>trim($_GPC['trade_rate']),
                'collect_type'=>intval($_GPC['collect_type']),
                'transfer_collect_type'=>intval($_GPC['transfer_collect_type']),
                'collect_account'=>intval($_GPC['collect_account']),//收款账号
//                'attach_cert'=>intval($_GPC['attach_cert']),
                'submit_method'=>intval($_GPC['submit_method']),
                'express_voucher'=>json_encode($_GPC['express_voucher']),
                'other_file'=>json_encode($_GPC['other_file']),
                'diy_remark'=>trim($_GPC['diy_remark']),
                'updatetime'=>time()
            ];

            if(intval($_GPC['status'])==3){
                //有待复核未记账-复核补充
                $data = array_merge($data,[
                    'status'=>5
                ]);

                //通知会计师,修改补充已完成
                $info = pdo_fetch('select a.voucher,c.openid,b.user_name,a.reg_number from '.tablename('customs_special_record').' a left join '.tablename('decl_user').' b on b.openid=a.openid left join '.tablename('mc_mapping_fans').' c on c.fanid=b.accounting_id where a.id=:id and a.openid=:openid',[':id'=>$id,':openid'=>$openid]);
                $post = json_encode([
                    'call'=>'sendNewInfoMsg',
                    'first' =>'您好，商户['.$info['user_name'].']的登记编号['.$info['reg_number'].']已修改复核，请点击查看。',
                    'keyword1' => $info['user_name'],
                    'keyword2' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_keep&view=2&date='.date('Y-m',time()),
                    'openid' => $info['openid'],
                    'temp_id' => '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                
                //通知管理员
                $post2 = json_encode([
                    'call'=>'sendNewInfoMsg',
                    'first' =>'商户['.$info['user_name'].']的登记编号['.$info['reg_number'].']已修改复核，请知悉。',
                    'keyword1' => $info['user_name'],
                    'keyword2' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => '',
                    'openid' => $notice_manage,
                    'temp_id' => '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            }

            //修改原始凭证文件名称
            $file_head = $_SERVER['DOCUMENT_ROOT'].'/attachment/';
            $new_express_voucher = [];
            $reg_number = $is_exam['reg_number'];
            if(!empty($data['express_voucher'])){
                $express_voucher = json_decode($data['express_voucher'],true);
                foreach($express_voucher as $k=>$v){
                    if(file_exists($file_head.$v)){
                        $file_position = str_split($v,17)[0];
                        $ext = explode('.',$v)[1];
                        $new_name = $file_position.$reg_number.'_'.str_pad($k+1,2,'0',STR_PAD_LEFT).'.'.$ext;
                        if(rename($file_head.$v,$file_head.$new_name)){
                            array_push($new_express_voucher,$new_name);
                        }
                    }
                }
            }
            $data['express_voucher'] = json_encode($new_express_voucher);

            $res = pdo_update('customs_special_record',$data,['id'=>$id,'type'=>$type,'openid'=>$openid]);
            if($res){
                show_json(1, array('msg'=>'修改成功'));
            }
        }else{
            $data = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and openid=:openid and (status=0 or status=3)',[':id'=>$id,':openid'=>$openid]);
            if(empty($data)){
                exit('参数错误');
            }
            
            if(!empty($data['account_receipt'])){
                $data['account_receipt'] = json_decode($data['account_receipt'],true);
            }

            $data['express_voucher'] = json_decode($data['express_voucher'],true);

            //获取账号
            $data['account_info'] = pdo_fetchall('select * from '.tablename('customs_accounting_account').' where openid=:openid and `type`=:typ order by id desc',[':openid'=>$data['openid'],':typ'=>$data['transfer_collect_type']]);
        }
        //税收分类
        $tax_classify = pdo_fetchall('select * from ' . tablename('customs_account_tax_code') . ' where 1 order by id asc');
//        foreach($tax_classify as $k=>$v){
//            $arr2[] = ['title'=>$v['name']];
//        }
//        $tax_classify = json_encode($arr2,true);
        //币种
        $currency = pdo_fetchall('select * from ' . tablename('currency') . ' where 1');
        include $this->template('account/collreg_edit');
    }elseif($type==2){
        //付款登记修改
        if($_W['isajax']){
            //查询是否在审核中
            $is_exam = pdo_fetch('select status,reg_number from '.tablename('customs_special_record').' where id=:id and openid=:openid',[':id'=>$id,':openid'=>$openid]);
            if($is_exam['status']==5){
                show_json(-1,['msg'=>'凭证已在审核中，请耐心等待！']);
            }
            
            $data = [
                'voucher_type'=>intval($_GPC['voucher_type']),
                'voucher_type1'=>intval($_GPC['voucher_type'])==1?intval($_GPC['voucher_type1']):'0',
                'voucher_type1_2'=>intval($_GPC['voucher_type1'])==2?intval($_GPC['voucher_type1_2']):'0',
                'voucher_type2'=>intval($_GPC['voucher_type'])==2?intval($_GPC['voucher_type2']):'0',
                'voucher_type2_1'=>intval($_GPC['voucher_type2'])==1?intval($_GPC['voucher_type2_1']):'0',
                'voucher_type3'=>intval($_GPC['voucher_type2_1'])==1?intval($_GPC['voucher_type3']):'0',
                'voucher_type3_1'=>intval($_GPC['voucher_type3'])==1?intval($_GPC['voucher_type3_1']):'0',
                'voucher_type4_1'=>intval($_GPC['voucher_type3_1'])==1?intval($_GPC['voucher_type4_1']):'0',
                'voucher_type4_2'=>intval($_GPC['voucher_type3_1'])==2?intval($_GPC['voucher_type4_2']):'0',
                'voucher_type3_2'=>intval($_GPC['voucher_type3'])==2?intval($_GPC['voucher_type3_2']):'0',
                'voucher_type5'=>intval($_GPC['voucher_type2_1'])==2?intval($_GPC['voucher_type5']):'0',
                'voucher_type5_1'=>intval($_GPC['voucher_type2'])==1?intval($_GPC['voucher_type5_1']):'0',
                'voucher_type6'=>intval($_GPC['voucher_type2_1'])==3?intval($_GPC['voucher_type6']):'0',
                'voucher_type2_2'=>intval($_GPC['voucher_type2'])==2?intval($_GPC['voucher_type2_2']):'0',
                'voucher'=>trim($_GPC['voucher']),
                'voucher_date'=>trim($_GPC['voucher_date']),
                'content'=>intval($_GPC['content']),
                'tax_classify_sel'=>intval($_GPC['content'])==1?trim($_GPC['tax_classify_sel']):'',
                'tax_classify_inp'=>intval($_GPC['content'])==2?trim($_GPC['tax_classify_inp']):'',
                'currency'=>trim($_GPC['currency']),
                'currency2'=>trim($_GPC['currency2']),
                'money_status'=>intval($_GPC['money_status']),
                'bill_status'=>intval($_GPC['bill_status']),
                'trade_price'=>trim($_GPC['trade_price']),
                'trade_price2'=>trim($_GPC['trade_price2']),
                'trade_rate'=>trim($_GPC['trade_rate']),
                'collect_type'=>intval($_GPC['collect_type']),
                'transfer_collect_type'=>intval($_GPC['transfer_collect_type']),
                'collect_account'=>intval($_GPC['collect_account']),//收款账号
//                'attach_cert'=>intval($_GPC['attach_cert']),
                'submit_method'=>intval($_GPC['submit_method']),
                'express_voucher'=>json_encode($_GPC['express_voucher']),
                'other_file'=>json_encode($_GPC['other_file']),
                'diy_remark'=>trim($_GPC['diy_remark']),
                'updatetime'=>time()
            ];

            if(intval($_GPC['status'])==3){
                //有待复核未记账-复核补充
                $data = array_merge($data,[
                    'status'=>5
                ]);

                //通知会计师,修改补充已完成
                $info = pdo_fetch('select a.voucher,c.openid,b.user_name,a.reg_number from '.tablename('customs_special_record').' a left join '.tablename('decl_user').' b on b.openid=a.openid left join '.tablename('mc_mapping_fans').' c on c.fanid=b.accounting_id where a.id=:id and a.openid=:openid',[':id'=>$id,':openid'=>$openid]);
                $post = json_encode([
                    'call'=>'sendNewInfoMsg',
                    'first' =>'您好，商户['.$info['user_name'].']的登记编号为'.$info['reg_number'].'已复核，请点击查看。',
                    'keyword1' => $info['user_name'],
                    'keyword2' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_keep&view=2&date='.date('Y-m',time()),
                    'openid' => $info['openid'],
                    'temp_id' => '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                
                //通知管理员
                $post2 = json_encode([
                    'call'=>'sendNewInfoMsg',
                    'first' =>'商户['.$info['user_name'].']的登记编号为'.$info['reg_number'].'已复核，请知悉。',
                    'keyword1' => $info['user_name'],
                    'keyword2' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => '',
                    'openid' => $notice_manage,
                    'temp_id' => '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            }

            //修改原始凭证文件名称
            $file_head = $_SERVER['DOCUMENT_ROOT'].'/attachment/';
            $new_express_voucher = [];
            $reg_number = $is_exam['reg_number'];
            if(!empty($data['express_voucher'])){
                $express_voucher = json_decode($data['express_voucher'],true);
                foreach($express_voucher as $k=>$v){
                    if(file_exists($file_head.$v)){
                        $file_position = str_split($v,17)[0];
                        $ext = explode('.',$v)[1];
                        $new_name = $file_position.$reg_number.'_'.str_pad($k+1,2,'0',STR_PAD_LEFT).'.'.$ext;
                        if(rename($file_head.$v,$file_head.$new_name)){
                            array_push($new_express_voucher,$new_name);
                        }
                    }
                }
            }
            $data['express_voucher'] = json_encode($new_express_voucher);

            $res = pdo_update('customs_special_record',$data,['id'=>$id,'type'=>$type,'openid'=>$openid]);
            if($res){
                show_json(1, array('msg'=>'修改成功'));
            }
        }else{
            $data = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and openid=:openid and (status=0 or status=3)',[':id'=>$id,':openid'=>$openid]);
            if(empty($data)){
                exit('参数错误');
            }
            
            if(!empty($data['account_receipt'])){
                $data['account_receipt'] = json_decode($data['account_receipt'],true);
            }
            

            $data['express_voucher'] = json_decode($data['express_voucher'],true);
            //获取账号
            $data['account_info'] = pdo_fetchall('select * from '.tablename('customs_accounting_account').' where openid=:openid and `type`=:typ order by id desc',[':openid'=>$data['openid'],':typ'=>$data['transfer_collect_type']]);
        }
        //税收分类
        $tax_classify = pdo_fetchall('select * from ' . tablename('customs_account_tax_code') . ' where 1 order by id asc');
//        foreach($tax_classify as $k=>$v){
//            $arr2[] = ['title'=>$v['name']];
//        }
//        $tax_classify = json_encode($arr2,true);
        //币种
        $currency = pdo_fetchall('select * from ' . tablename('currency') . ' where 1');

        include $this->template('account/payreg_edit');
    }
}elseif($op=="withdraw"){
    //撤回申请
    $id = intval($_GPC['id']);
    if(!empty($id)){
        $data = pdo_fetch('select ids,batch_num,method from '.tablename('customs_accounting_register').' where id=:id and openid=:openid and status2=1',[':id'=>$id,':openid'=>$openid]);
        
        if($data['method']==1){
            $res = pdo_update('customs_accounting_register',['status'=>-4],['id'=>$id,'openid'=>$openid]);
        }else{
            $res = pdo_delete('customs_accounting_register',['id'=>$id,'openid'=>$openid,'status2'=>1]);
        }
        
        if($res){
            $data['ids'] = explode(',',$data['ids']);
            foreach($data['ids'] as $k=>$v){
                if(!empty($v)){
                    pdo_update('customs_special_record',['status'=>0],['id'=>$v,'openid'=>$openid]);
                }
            }
            
            //2、通知管理员
            $merch_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
            $post2 = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'商户['.$merch_info['user_name'].']对['.$data['batch_num'].']凭证批次号进行撤回提交操作，请知悉。',
                'keyword1' => '凭证批次审核',
                'keyword2' => '撤回提交',
                'keyword3' => '撤回时间：'.date('Y-m-d H:i:s',time()),
                'remark' => '',
                'url' => '',
                'openid' => $notice_manage,
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            
            show_json(1,['msg'=>'撤回申请成功！']);
        }
    }
}elseif($op=='reconciliation_info'){
    //对账信息
    $id = intval($_GPC['id']);
    if($_W['isajax']){
        $recon_id = intval($_GPC['recon_id']);
        $typ = intval($_GPC['typ']);
        $manager = intval($_GPC['manager']);
        if($typ==1){
            //对账无误
            if($manager==1){
                //登记端发送消息给管理端和记账端
                $recon_info = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where id=:id',[':id'=>$recon_id]);
                $date = $recon_info['reconciliation_date'];//当前对账单的对账月份
                $accounting = pdo_fetch('select b.openid,a.user_name,a.id from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$recon_info['openid']]);
                //登记端的所有批次号下的凭证状态为：对账无误
                $ids = explode(',',$recon_info['batch_ids']);
                foreach($ids as $k=>$v){
                    if(!empty($v)){
                        $batch_ids = pdo_fetch('select ids from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$v]);
                        $batch_ids['ids'] = explode(',',$batch_ids['ids']);
                        foreach($batch_ids['ids'] as $k2=>$v2){
                            if(!empty($v2)){
                                $record = pdo_fetch('select id from '.tablename('customs_special_record').' where id=:id and (status=4 or status=8 or status=9 or status=10 or status=11) and `type`!=3',[':id'=>$v2]);
                                if(!empty($record)){
                                    //登记端确认对账有误
                                    pdo_update('customs_special_record',['status'=>8],['id'=>$v2]);
                                }
                            }
                        }
                    }
                }
                //修改为对账无误状态
                $res = pdo_update('customs_accounting_reconciliation',['reconciliation_status2'=>1],['id'=>$recon_id]);
                if($res){
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' => '你好，［'.$accounting['user_name'].'］已经确认其提交的［'.$recon_info['reconciliation_date'].'］凭证记账对账，本次记账已完成，辛苦您！',
                        'keyword1' => '对账确认审核',
                        'keyword2' => '确认对账',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=reconciliation_detail&id='.$accounting['id'].'&date='.$date,
                        'openid' => $accounting['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                    
                    $post2 = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' => '你好，［'.$accounting['user_name'].'］已经确认其向［记账端］提交的［'.$recon_info['reconciliation_date'].'］凭证记账对账。',
                        'keyword1' => '对账确认审核',
                        'keyword2' => '确认对账',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=manage&op=reconciliation&date='.$date,
                        'openid' => $notice_manage,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
                    
                    show_json(1,['msg'=>'确认对账成功！']);
                }
            }elseif($manager==2){
                //管理端发送消息给登记端
                $recon_info = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where id=:id',[':id'=>$recon_id]);
                $decl_user = pdo_fetch('select openid,user_name,id from '.tablename('decl_user').' where openid=:openid',[':openid'=>$recon_info['openid']]);
                //修改为对账无误状态
                $res = pdo_update('customs_accounting_reconciliation',['reconciliation_manage_status'=>1],['id'=>$recon_id]);

                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' => '['.$decl_user['user_name'].']你好，你提交的［'.$recon_info['reconciliation_date'].'］凭证已完成记账，请确认本月的对账',
                    'keyword1' => '对账确认审核',
                    'keyword2' => '请确认本月的对账',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=reconciliation_info&id='.$recon_id,
                    'openid' => $decl_user['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                show_json(1,['msg'=>'确认对账无误成功，已通知登记端']);
            }
        }elseif($typ==2){
            //对账有误，反馈
            
            if($manager==1){
                //登记端发送消息给管理端和记账端
                $recon_info = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where id=:id',[':id'=>$recon_id]);
                $date = $recon_info['reconciliation_date'];//当前对账单的对账月份
                $accounting = pdo_fetch('select b.openid,a.user_name,a.id from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$recon_info['openid']]);
                //登记端的所有批次号下的凭证状态为：对账有误
                $ids = explode(',',$recon_info['batch_ids']);
                foreach($ids as $k=>$v){
                    if(!empty($v)){
                        $batch_ids = pdo_fetch('select ids from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$v]);
                        $batch_ids['ids'] = explode(',',$batch_ids['ids']);
                        foreach($batch_ids['ids'] as $k2=>$v2){
                            if(!empty($v2)){
                                $record = pdo_fetch('select id from '.tablename('customs_special_record').' where id=:id and (status=4 or status=8 or status=9 or status=10 or status=11) and `type`!=3',[':id'=>$v2]);
                                if(!empty($record)){
                                    //登记端确认对账有误
                                    pdo_update('customs_special_record',['status'=>9],['id'=>$v2]);
                                }
                            }
                        }
                    }
                }
                //修改为对账有误状态
                $res = pdo_update('customs_accounting_reconciliation',['reconciliation_status2'=>2],['id'=>$recon_id]);

                if($res){
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' => '你好，［'.$accounting['user_name'].'］反馈你提交的［'.$date.'］凭证对账有误需纠正，请与其联系处理。',
                        'keyword1' => '对账确认审核',
                        'keyword2' => '对账有误',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=reconciliation_info&id='.$recon_id,
                        'openid' => $notice_manage,
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                    
                    show_json(1,['msg'=>'确认对账有误，已通知管理员！']);
                }
            }elseif($manager==2){
                //管理端发送消息给记账端
                $recon_info = pdo_fetch('select * from '.tablename('customs_accounting_reconciliation').' where id=:id',[':id'=>$recon_id]);
                $accounting = pdo_fetch('select b.openid,a.user_name,a.id from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$recon_info['openid']]);
                //修改为对账有误状态
                $res = pdo_update('customs_accounting_reconciliation',['reconciliation_status'=>0,'reconciliation_manage_status'=>2],['id'=>$recon_id]);
                $date = date('Y-m',strtotime($recon_info['reconciliation_date']." + 1 month"));//当前对账月份的凭证时间（3月份提交的就是2月份的对账）

                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' => '你好，你提交的［'.$recon_info['reconciliation_date'].'］凭证对账有误需纠正，请与管理员联系处理。',
                    'keyword1' => '对账确认审核',
                    'keyword2' => '对账有误',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=watch_bill&uid='.$accounting['id'].'&id='.$recon_info['id'],
                    'openid' => $accounting['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                show_json(1,['msg'=>'确认对账有误，已通知记账端！']);
                
            }
        }
    }else{
        //查看当月对账信息
        $id = intval($_GPC['id']);
        $my_merchant = pdo_fetch('select a.*,b.user_name from '.tablename('customs_accounting_reconciliation').' a left join '.tablename('decl_user').' b on b.openid=a.openid where a.id=:id',[':id'=>$id]);
        $my_merchant['reconciliation_sys_list'] = json_decode($my_merchant['reconciliation_sys_list'],true);
        $my_merchant['reconciliation_list'] = json_decode($my_merchant['reconciliation_list'],true);
        if(!empty($my_merchant['reconciliation_list'])){
            foreach($my_merchant['reconciliation_list'] as $k2=>$v2){
                if(!empty($v2)){
                    $my_merchant['reconciliation_list'][$k2] = explode(',',$v2);
                }
            }
        }
        if($openid!=$my_merchant['openid']){
            $is_show_queren = 0;//管理员
        }else{
            $is_show_queren = 1;//登记端
        }
        include $this->template('account/reconciliation_info');
    }
}elseif($op=='reconciliation_lastMonth_info'){
    //上月对账信息
    $ids = explode(',',trim($_GPC['ids']));
    //上月
    $_lastMonth = date('Y年m月',strtotime("-1 month", strtotime(date('Y-m'))));

    if($_W['isajax']){
        $batch_id = intval($_GPC['batch_id']);
        $typ = intval($_GPC['typ']);

        if($typ==1){
            //正确无误

            //查找自己的会计师
            $accounting = pdo_fetch('select b.openid,a.user_name from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$openid]);
            $batch = pdo_fetch('select batch_num from '.tablename('customs_accounting_register').' where id=:id and openid=:openid',[':id'=>$batch_id,':openid'=>$openid]);
            //修改为确认对账状态
            $res = pdo_update('customs_accounting_register',['reconciliation_status2'=>1],['id'=>$batch_id,'openid'=>$openid]);
            if($res){
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，商户['.$accounting['user_name'].']对凭证批次号为['.$batch['batch_num'].']的对账结果为确认对账，请点击查看！',
                    'keyword1' => '对账确认审核',
                    'keyword2' => '确认对账',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '点击查看详情',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=reconciliation_info&id='.$batch_id,
                    'openid' => $accounting['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

                $post2 = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'商户['.$accounting['user_name'].']对凭证批次号为['.$batch['batch_num'].']的对账结果为确认对账，请知悉！',
                    'keyword1' => '对账确认审核',
                    'keyword2' => '确认对账',
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '',
                    'url' => '',
                    'openid' => $notice_manage,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

                show_json(1,['msg'=>'确认对账成功！']);
            }
        }
    }else{
        if(empty($ids)){
            $my_merchant = '';
        }else{
            foreach($ids as $k=>$v){
                $my_merchant[$k] = pdo_fetch('select id,openid,batch_num,ids,reconciliation_status,reconciliation_list,reconciliation_status2 from '.tablename('customs_accounting_register').' where id=:id and openid=:openid and status=1 and status2=2 order by id desc',[':id'=>$v,':openid'=>$openid]);
                if(!empty($my_merchant[$k]['reconciliation_list'])){
                    $my_merchant[$k]['reconciliation_list'] = json_decode($my_merchant[$k]['reconciliation_list'],true);
                }
                $ids_record = explode(',',$my_merchant[$k]['ids']);
                $my_merchant[$k]['is_show']=0;
                //step3:统计金额（已收、未收、预收、已付、未付、预付）
                $code_name = pdo_fetchall('select code_name from '.tablename('currency').' where 1');
                foreach($code_name as $kk=>$vv){
                    $code_name2 = explode('(',explode(')',$vv['code_name'])[0])[1];
                    $my_merchant[$k]['all_money']['col_1'][$code_name2]=0;
                    $my_merchant[$k]['all_money']['col_2'][$code_name2]=0;
                    $my_merchant[$k]['all_money']['col_3'][$code_name2]=0;
                    $my_merchant[$k]['all_money']['pay_1'][$code_name2]=0;
                    $my_merchant[$k]['all_money']['pay_2'][$code_name2]=0;
                    $my_merchant[$k]['all_money']['pay_3'][$code_name2]=0;
                }

                foreach($ids_record as $k3=>$v3){
                    if(!empty($v3)){
                        $my_merchant[$k]['voucher'][$k3] = pdo_fetch('select id,type,reg_number,voucher,currency,currency2,money_status,trade_price,trade_price2 from '.tablename('customs_special_record').' where id=:id and status=4',[':id'=>$v3]);
                        if(!empty($my_merchant[$k]['voucher'][$k3])){
                            //step3:统计金额（已收、未收、预收、已付、未付、预付）
                            $currency = $my_merchant[$k]['voucher'][$k3]['currency'];
                            $currency2 = $my_merchant[$k]['voucher'][$k3]['currency2'];
                            $money_status = $my_merchant[$k]['voucher'][$k3]['money_status'];
                            $trade_price = $my_merchant[$k]['voucher'][$k3]['trade_price'];
                            $trade_price2 = $my_merchant[$k]['voucher'][$k3]['trade_price2'];

                            switch($money_status){
                                case 1:
                                    //已收
                                    $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency2]);
                                    $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                    $my_merchant[$k]['all_money']['col_1'][$code_namee]+=$trade_price2;
                                    break;
                                case 2:
                                    //未收
                                    $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency]);
                                    $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                    $my_merchant[$k]['all_money']['col_2'][$code_namee]+=$trade_price;
                                    break;
                                case 3:
                                    //预收
                                    $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency2]);
                                    $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                    $my_merchant[$k]['all_money']['col_3'][$code_namee]+=$trade_price2;
                                    break;
                                case 4:
                                    //已付
                                    $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency2]);
                                    $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                    $my_merchant[$k]['all_money']['pay_1'][$code_namee]+=$trade_price2;
                                    break;
                                case 5:
                                    //未付
                                    $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency]);
                                    $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                    $my_merchant[$k]['all_money']['pay_2'][$code_namee]+=$trade_price;
                                    break;
                                case 6:
                                    //预付
                                    $code_namee = pdo_fetchcolumn('select code_name from '.tablename('currency').' where code_value=:code',[':code'=>$currency2]);
                                    $code_namee = explode('(',explode(')',$code_namee)[0])[1];
                                    $my_merchant[$k]['all_money']['pay_3'][$code_namee]+=$trade_price2;
                                    break;
                                default:
                                    break;
                            }

                            $my_merchant[$k]['is_show']=1;
                        }
                    }
                }

                foreach($code_name as $kk=>$vv){
                    $code_name2 = explode('(',explode(')',$vv['code_name'])[0])[1];
                    if($my_merchant[$k]['all_money']['col_1'][$code_name2]==0){
                        unset($my_merchant[$k]['all_money']['col_1'][$code_name2]);
                    }
                    if($my_merchant[$k]['all_money']['col_2'][$code_name2]==0){
                        unset($my_merchant[$k]['all_money']['col_2'][$code_name2]);
                    }
                    if($my_merchant[$k]['all_money']['col_3'][$code_name2]==0){
                        unset($my_merchant[$k]['all_money']['col_3'][$code_name2]);
                    }
                    if($my_merchant[$k]['all_money']['pay_1'][$code_name2]==0){
                        unset($my_merchant[$k]['all_money']['pay_1'][$code_name2]);
                    }
                    if($my_merchant[$k]['all_money']['pay_2'][$code_name2]==0){
                        unset($my_merchant[$k]['all_money']['pay_2'][$code_name2]);
                    }
                    if($my_merchant[$k]['all_money']['pay_3'][$code_name2]==0){
                        unset($my_merchant[$k]['all_money']['pay_3'][$code_name2]);
                    }
                }

                if($my_merchant[$k]['is_show']==0){
                    unset($my_merchant[$k]['all_money']);
                }
            }
        }
        include $this->template('account/reconciliation_lastMonth_info');
    }
}elseif($op=='person_reg'){
    //人员增减
    include $this->template('account/personal_reg');
}elseif($op=='person_operation'){
    //社保/个税增减
    $type = intval($_GPC['type']);
    if($_W['isajax']){
        $data = [
            'openid'=>$openid,
            'type'=>intval($_GPC['type']),
            'opera_type'=>intval($_GPC['opera_type']),
            'opera_date'=>trim($_GPC['opera_date']),
            'info_file'=>json_encode($_GPC['info_file'],true),
            'status'=>0,
            'createtime'=>time()
        ];
        $res = pdo_insert('customs_accounting_social_tax',$data);
        $insert_id = pdo_insertid();
        if($res){
            //通知管理端、记账端
            $decl_user = pdo_fetch('select a.user_name,b.openid from '.tablename('decl_user').' a left join '.tablename('mc_mapping_fans').' b on b.fanid=a.accounting_id where a.openid=:openid',[':openid'=>$openid]);
            $typeName = '';
            if($data['type']==1){
                $typeName = '社保';
            }elseif($data['type']==2){
                $typeName = '个税';
            }
            $operatypeName = '';
            if($data['opera_type']==1){
                $operatypeName = '增员';
            }elseif($data['opera_type']==2){
                $operatypeName = '减员';
            }
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好！［'.$decl_user['user_name'].'］提交了［'.$typeName.'］［'.str_replace('-','年',$data['opera_date']).'月］的［'.$operatypeName.'］申请，请尽快处理并予以确认。',
                'keyword1' => $typeName.$operatypeName.'申请',
                'keyword2' => '已提交，待确认',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=socialTax_info&id='.$insert_id,
                'openid' => $decl_user['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

            $post2 = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好！［'.$decl_user['user_name'].'］向其记账代理提交了［'.$typeName.'］［'.str_replace('-','年',$data['opera_date']).'月］的［'.$operatypeName.'］申请，现正待记账代理处理与确认。',
                'keyword1' => $typeName.$operatypeName.'申请',
                'keyword2' => '已提交，待确认',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=opera_manage&id='.$insert_id,
                'openid' => $notice_manage,
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);
            show_json(1,['msg'=>'创建'.$typeName.'增减成功，请等待审核！']);
        }
    }else{
        include $this->template('account/person_operation');
    }
}elseif($op=='opera_manage'){
    //增减管理
    $id = intval($_GPC['id']);
    $opera = intval($_GPC['opera']);
    $date = trim($_GPC['date'])==''?date('Y-m',time()):'';
    if($_W['isajax']){
        $type = intval($_GPC['type']);

        if($type==1 || $type==2){
            $condition = '';
            if($type==1){
                $condition .= ' and a.status=0';
            }elseif($type==2){
                $condition .= ' and (a.status=1 or a.status=2)';
            }
            if($id>0){
                //管理端查看
                $condition .= ' and a.id='.$id;
            }else{
                $condition .= ' and a.openid="'.$openid.'"';
            }
            $list = pdo_fetchall('select a.*,b.user_name from '.tablename('customs_accounting_social_tax').' a left join '.tablename('decl_user').' b on b.openid=a.openid where 1'.$condition.' and a.opera_date="'.$date.'" order by a.id desc');
            foreach($list as $k=>$v){
                $list[$k]['info_file'] = json_decode($v['info_file'],true);
                if($v['type']==1){
                    $list[$k]['typeName'] = '社保';
                }elseif($v['type']==2){
                    $list[$k]['typeName'] = '个税';
                }
                if($v['opera_type']==1){
                    $list[$k]['operatypeName'] = '增员';
                }elseif($v['opera_type']==2){
                    $list[$k]['operatypeName'] = '减员';
                }
                if($v['status']==1){
                    $list[$k]['statusName'] = '增减成功';
                }elseif($v['status']==2){
                    $list[$k]['statusName'] = '增减失败';
                }
            }
            show_json(1,['data'=>$list]);
        }
    }else{

        include $this->template('account/opera_manage');
    }
}elseif($op=='watch_voucher'){
    //对账管理-查看该批次下的凭证
    $id = intval($_GPC['id']);
    $list = pdo_fetch('select * from '.tablename('customs_accounting_register').' where id=:id',[':id'=>$id]);
    $ids = explode(',',$list['ids']);
    foreach($ids as $k3=>$v3){
        if(!empty($v3)){
            if(intval($_GPC['opera'])==1){
                $list['voucher'][$k3] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id',[':id'=>$v3]);
            }else{
                $list['voucher'][$k3] = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and (status=4 or status=8 or status=9)',[':id'=>$v3]);
            }
        }
    }
    include $this->template('account/watch_voucher');
}elseif($op=='summary_manage'){
    //凭证管理->[凭证汇总，管理汇总]
    include $this->template('account/summary_manage');
}elseif($op=='voucher_summary'){
    //系统-凭证汇总
    if($_W['isajax']) {
        $type = intval($_GPC['type']);
        if ($type == 1) {
            //已登记未汇总
            $list = pdo_fetchall('select * from ' . tablename('customs_special_record') . ' where status=0 and openid=:openid and method=0 order by createtime desc', [':openid' => $openid]);
            show_json(1, array('data' => $list));
        }
    }
    include $this->template('account/voucher_summary');
}elseif($op=='voucher_manage_select'){
    include $this->template('account/voucher_manage_select');
}elseif($op=='voucherSummary'){
    //人工-凭证汇总

    include $this->template('account/enterprise/voucher_summary');
}elseif($op=='manualSummaryList'){
    //人工汇总列表
    $date = trim($_GPC['date']);
    if($_W['isajax']) {
        $type = intval($_GPC['type']);
        $dateStart = strtotime($date.'-01 00:00:00');
        $dateEnd = strtotime(date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', strtotime($date))+1, 00)));
        if ($type == 1) {
            //已登记未汇总
            $list = pdo_fetchall('select * from ' . tablename('customs_special_record') . ' where (status=0 or status=3) and openid=:openid and method=1 and ( createtime >= '.$dateStart.' and createtime <= '.$dateEnd.' ) order by createtime desc', [':openid' => $openid]);
            show_json(1, array('data' => $list));
        }
    }else{
        //1、查询该用户所有凭证的创建日期
//        $list = pdo_fetchall('select createtime from ' . tablename('customs_special_record') . ' where status=0 and openid=:openid and method=1 order by createtime desc', [':openid' => $openid]);
//        $date = [];
//        foreach($list as $k=>$v){
//            array_push($date,date('Y-m',$v['createtime']));
//        }
//        $date = array_reverse(array_unique($date));
    }
    include $this->template('account/enterprise/manual_summary_list');
}elseif($op=='manualSummary'){
    //人工-凭证汇总
    if($_W['isajax']){
       $add_reg = intval($_GPC['add_reg']);
       if($add_reg==1){
           $edit_id = intval($_GPC['id']);
           $voucher_type=0;
           if(intval($_GPC['voucher_type'])==1){
               //收款
               $voucher_type=trim($_GPC['collect_voucher']);
           }else if(intval($_GPC['voucher_type'])==2){
               //付款
               $voucher_type=trim($_GPC['payment_voucher']);
           }

           if($edit_id>0){
               //编辑
               $data = [
                   'type'=>intval($_GPC['voucher_type']),
                   'voucher_type'=>$voucher_type,
                   'voucher_date'=>trim($_GPC['voucher_date']),
                   'attach_cert'=>trim($_GPC['voucher_number']),
                   'voucher_unit'=>trim($_GPC['voucher_unit']),
                   'voucher_form'=>intval($_GPC['voucher_form']),
                   'submit_method'=>intval($_GPC['submit_method']),
                   'express_voucher'=>intval($_GPC['voucher_form'])==2?json_encode($_GPC['express_voucher'],true):'',
                   'diy_remark'=>trim($_GPC['diy_remark']),
                   'status'=>1
               ];

               $res = pdo_update('customs_special_record',$data,['id'=>$edit_id,'openid'=>$openid]);
               if($res){
                   pdo_update('customs_accounting_register',['status'=>7],['ids'=>$edit_id]);
                   $batch = pdo_fetch('select batch_num,createtime from '.tablename('customs_accounting_register').' where ids=:id',[':id'=>$edit_id]);
                   $user_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
                   $post = json_encode([
                       'call'=>'sendNewInfoMsg',
                       'first' =>'商户['.$user_info['user_name'].']修改了人工汇总[汇总批次号：'.$batch['batch_num'].']，请尽快确认！',
                       'keyword1' => $user_info['user_name'],
                       'keyword2' => date('Y-m-d H:i:s',time()),
                       'remark' => '',
                       'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=manage&date='.date('Y-m',$batch['createtime']),
                       'openid' => $notice_manage,
                       'temp_id' => 'zXF01McqJ_fW7hGAYvQR4hO7X1MI7fmKMzsn7gHScIc'
                   ]);

                   ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                   show_json(1,['msg'=>'编辑成功,已重新提交！']);
               }
           }else{
               //创建

               $data = [
                   'openid'=>$_W['openid'],
                   'type'=>intval($_GPC['voucher_type']),
                   'voucher_type'=>$voucher_type,
                   'voucher_date'=>trim($_GPC['voucher_date']),
                   'attach_cert'=>trim($_GPC['voucher_number']),
                   'voucher_unit'=>trim($_GPC['voucher_unit']),
                   'voucher_form'=>intval($_GPC['voucher_form']),
                   'submit_method'=>intval($_GPC['submit_method']),
                   'express_voucher'=>intval($_GPC['voucher_form'])==2?json_encode($_GPC['express_voucher'],true):'',
                   'diy_remark'=>trim($_GPC['diy_remark']),
                   'createtime'=>time(),
                   'status'=>1,
                   'method'=>1
               ];

               $res = pdo_insert('customs_special_record',$data);
               if($res){
                   $insert_id = pdo_insertid();

                   if(intval($_GPC['voucher_type'])==1){$prefix='SK';}elseif(intval($_GPC['voucher_type'])==2){$prefix='FK';}elseif(intval($_GPC['voucher_type'])==3){$prefix='ZD';}
                   $reg_number = $prefix.date('Ymd',time()).str_pad($insert_id,2,'0',STR_PAD_LEFT);
                   $res = pdo_update('customs_special_record',['reg_number'=>$reg_number],['id'=>$insert_id]);

                   //新增汇总批次号
                   $batch_num = 'GP' . date('YmdH', time()) . str_pad(mt_rand(1, 999), 3, '0',
                           STR_PAD_LEFT);
                   pdo_insert('customs_accounting_register',['openid'=>$openid,'batch_num'=>$batch_num,'ids'=>$insert_id,'status'=>7,'status2'=>1,'submit_method'=>intval($_GPC['submit_method']),'createtime'=>time(),'method'=>1]);

                   $user_info = pdo_fetch('select user_name from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
                   $post = json_encode([
                       'call'=>'sendNewInfoMsg',
                       'first' =>'商户['.$user_info['user_name'].']提交了人工汇总[汇总批次号：'.$batch_num.']，请尽快确认！',
                       'keyword1' => $user_info['user_name'],
                       'keyword2' => date('Y-m-d H:i:s',time()),
                       'remark' => '',
                       'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=account&m=sz_yi&p=manage&date='.date('Y-m',time()),
                       'openid' => $notice_manage,
                       'temp_id' => 'zXF01McqJ_fW7hGAYvQR4hO7X1MI7fmKMzsn7gHScIc'
                   ]);

                   ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

                   if($res){
                       show_json(1, array('msg'=>'新增汇总成功，请等待后台审核确认！'));
                   }
               }
           }
       }elseif($add_reg==2){
           //查看当月有无生成账单
           $date = $_GPC['date'];
           $date2 = date('Y-m',strtotime($date." - 1 month"));;

           $is_havelog = pdo_fetch('select id from '.tablename('customs_accounting_reconciliation').' where openid=:openid and reconciliation_date="'.$date2.'"',[':openid'=>$openid]);
           if($is_havelog['id']>0){
               show_json(1,['msg'=>'上月['.$date2.']已生成账单,本月['.$date.']不可再新增汇总！']);
           }else{
               show_json(0);
           }
       }
    }else{
        $voucher_id = intval($_GPC['id']);
        $data=[];
        if(!empty($voucher_id)){
            $data = pdo_fetch('select * from '.tablename('customs_special_record').' where id=:id and openid=:openid',[':id'=>$voucher_id,':openid'=>$openid]);
            if($data['type']==1 || $data['type']==2){
                //收款和付款操作
                $data['voucher_type'] = explode(',',$data['voucher_type']);
                $data['attach_cert'] = explode(',',$data['attach_cert']);
                $data['voucher_unit'] = explode(',',$data['voucher_unit']);
            }
            $data['express_voucher'] = json_decode($data['express_voucher'],true);
        }

        include $this->template('account/enterprise/manual_summary');
    }
}
