<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;


class Package extends Auth
{
    public  $req;
    public function __construct(Request $request)
    {
        $this->req = $request;
    }

    //包裹集运配置
    public function config(Request $request)
    {
        if ( request()->isPost() )
        {
            $data = $request->post();
            Db::startTrans();
            try{
                Db::name('package_config')->where(array('id'=>1))->update($data);
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
                return json(['code'=>-1,'message'=>$e->getMessage()]);
            }
            return json(['code'=>0,'message'=>'保存成功']);
        }
        else
        {
            $infos = DB::name('package_config')->where(array('id'=>1))->find();
            return view("package/config",[
                'title' => '包裹集运配置',
                'infos' => $infos
            ]);
        }
    }

    // 获取所有的商户
    private function getDeclUser() {
        return Db::name('decl_user')->field('id,company_name')->where(array('user_status'=>0))->select();
    }

    // 获取商户信息
    public function getMerchat() {
        $uniacid = $this->req->post('uniacid');
        $data = Db::name('sz_yi_perm_user')->where('uniacid',$uniacid)->field('uid,username')->select();
        if(!empty($data)) {
            return json_encode(['code'=>1,'msg'=>'Success','data'=>$data]);
        }

        return json_encode(['code'=>0,'msg'=>'Error','data'=>'没有数据，请配置']);
    }

    //包裹列表
    public function datalist()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();
            $decl_user_id= input('decl_user_id');
            if($decl_user_id)
            {
                $map['up.decl_user_id'] = $decl_user_id;
            }

            $search = input('search');
            if($search)
            {
                $map['up.ordersn'] = ['like','%'.$search.'%'];
            }

            $map['up.is_shou'] = 0;

            $list = Db::name('smallwechat_user_package')
            ->alias("up")
            ->join('decl_user du', 'up.decl_user_id = du.id')
            ->where($map)
            ->limit($limit)
            ->order($order)
            ->field('up.*,du.company_name')
            ->select();

            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                if($v['ordersn_type']==1)
                {
                    $list[$k]['ordersn_type'] = '正式';
                }
                else {
                    $list[$k]['ordersn_type'] = '临时';
                }
            }

            $total = Db::name('smallwechat_user_package')
            ->alias("up")
            ->join('decl_user du', 'up.decl_user_id = du.id')
            ->where($map)
            ->count();

            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }
        else {
            return view("package/datalist",[
                'declusers' => $this->getDeclUser(),
                'title' => '包裹列表',
            ]);
        }
    }

    //揽收列表
    public function package_collect()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('smallwechat_user_collect')->order($order)->limit($limit)->select();

            foreach ($list as $k => $v) {

                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                $list[$k]['form_buss'] = Db::name('decl_user')->where(array('id'=>$v['form_user_id']))->value('company_name');
                if($v['to_user_id'])
                {
                    $list[$k]['to_buss'] = Db::name('decl_user')->where(array('id'=>$v['to_user_id']))->value('company_name');
                }
                else {
                    $list[$k]['to_buss'] = '未设置';
                }

                switch ($v['status']) {
                    case '-1':
                        $list[$k]['manage'] = '<button type="button" onclick="data_edit('."'查看详情','".Url('admin/Package/packageCollectDetail',['id'=>$v['id']])."','".$v['id']."'".')" class="btn btn-primary btn-xs">查看详情</button>';
                        $list[$k]['status'] = '<button type="button" class="btn btn-danger btn-xs">已拒绝</button>';
                        break;
                    case '0':
                        $list[$k]['manage'] = '<button type="button" onclick="data_edit('."'审核','".Url('admin/Package/packageCollectDo',['id'=>$v['id']])."','".$v['id']."'".')" class="btn btn-primary btn-xs">审核</button>';
                        $list[$k]['status'] = '<button type="button" class="btn btn-warning btn-xs">待审核(交货)</button>';
                        break;
                    case '1':
                        $list[$k]['manage'] = '<button type="button" onclick="data_edit('."'查看详情','".Url('admin/Package/packageCollectDetail',['id'=>$v['id']])."','".$v['id']."'".')" class="btn btn-primary btn-xs">查看详情</button>';
                        $list[$k]['status'] = '<button type="button" class="btn btn-primary btn-xs">待揽收</button>';
                        break;
                    case '2':
                        $list[$k]['manage'] = '<button type="button" onclick="data_edit('."'查看详情','".Url('admin/Package/packageCollectDetail',['id'=>$v['id']])."','".$v['id']."'".')" class="btn btn-primary btn-xs">查看详情</button>';
                        $list[$k]['status'] = '<button type="button" class="btn btn-primary btn-xs">揽收完成</button>';
                        break;
                    case '3':
                        $list[$k]['manage'] = '<button type="button" onclick="data_edit('."'审核','".Url('admin/Package/packageCollectDoShou',['id'=>$v['id']])."','".$v['id']."'".')" class="btn btn-primary btn-xs">审核</button>';
                        $list[$k]['status'] = '<button type="button" class="btn btn-warning btn-xs">待审核(收货)</button>';
                        break;
                }

                if($v['type']==1)
                {
                    $list[$k]['type'] = '系统发起';
                }
                else {
                    $list[$k]['type'] = '手动发起';
                }
                //$list[$k]['manage'].= '<a href="javascript:;" title="删除" onclick="del(this,'."'".$v['id']."'".')" style="padding-left: 5px;"><i class="glyphicon glyphicon-trash manage"></i></a>';
            }
            $total = Db::name('package_collect')->count();

            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }
        else {

            return view("package/package_collect",[
                'title' => '发起揽收',
            ]);
        }
    }

    //揽收审核
    public function collectdo()
    {
        $id = input('id');
        if ( request()->isPost() || request()->isAjax())
        {
            $data['id'] = input('collect_id');
            $data['form_user_id'] = input('form_user_id');
            $data['to_user_id'] = input('to_user_id');
            $data['status'] = input('status');

            if( Db::name('smallwechat_user_collect')->update($data) )
            {

                //通知收货人和交货人
                //微信通知交货人
                // $this->send_wechat_notice(json_encode([
                //   'call'=>'send_pre_commit_notice',
                //   'msg' =>'你好,您有订单需要揽收！',
                //   'name'=>' Cklein222',
                //   'time'=>date('Y-m-d H:i:s',time()),
                //   'openid'=>'ov3-btxV-ENpabbyd0c7grgz5RP4',
                //   'remark'=>'系统通知',
                //   'uniacid'=>3,
                //   'appid' => 'wx6d1af256d76896ba',
                //   'pagepath' => ''
                // ]));
                //微信通知收货人
                // $this->send_wechat_notice(json_encode([
                //   'call'=>'send_pre_commit_notice',
                //   'msg' =>'你好,您有订单需要揽收！',
                //   'name'=>' Cklein222',
                //   'time'=>date('Y-m-d H:i:s',time()),
                //   'openid'=>'ov3-btxV-ENpabbyd0c7grgz5RP4',
                //   'remark'=>'系统通知',
                //   'uniacid'=>3,
                //   'appid' => 'wx6d1af256d76896ba',
                //   'pagepath' => ''
                // ]));
                $result['status'] = 1;
                $result['message'] = '审核成功';
            }
            else {
                $result['status'] = 0;
                $result['message'] = '审核失败';
            }

            return json($result);

        }else {
            $collectdata = Db::name('smallwechat_user_collect')->where(array('id'=>$id))->find();
            $collectdata['order_goods'] = Db::name('smallwechat_user_package')->where(array('id'=>['in',$collectdata['package_ids']]))->select();
            foreach ($collectdata['order_goods'] as $k => $v) {
                $collectdata['order_goods'][$k]['orderdata'] = Db::name('smallwechat_user_package_goods')
                ->alias("pg")
                ->join('sz_yi_goods g', 'pg.goods_id = g.id')
                ->field('pg.*,g.title')
                ->where(array('pg.package_id'=>$v['id'],'pg.is_delete'=>0))
                ->select();
            }

            $formuser = Db::name('decl_user')->where(array('id'=>$collectdata['form_user_id']))->find();
            $touser = Db::name('decl_user')->field('id,user_name,company_name,user_tel')->where(array('user_status'=>0,'parent_id'=>0,'id'=>['not in',$formuser['id']]))->select();
            return view("package/package_do",[
                'title' => '揽收审核',
                'collectdata' => $collectdata,
                'formuser' => $formuser,
                'touser' => $touser
            ]);
        }
    }

    //揽收审核
    public function collectdoshou()
    {
        $id = input('id');
        if ( request()->isPost() || request()->isAjax())
        {
            $data['id'] = input('collect_id');
            $data['status'] = input('status');

            if( Db::name('smallwechat_user_collect')->update($data) )
            {
                //创建新数据
                $collectData = Db::name('smallwechat_user_collect')->where(array('id'=>$data['id']))->find();
                $package_ids = explode(",",$collectData['package_ids']);
                $touserData = Db::name('smallwechat_user')->where(array('decl_user_id'=>$collectData['to_user_id']))->find();
                //print_r($package_ids)
                foreach ($package_ids as $k => $v) {
                    $packageData = Db::name('smallwechat_user_package')->where(array('id'=>$v))->find();
                    $map = array();
                    $map['openid'] = $touserData['openid'];
                    $map['decl_user_id'] = $touserData['decl_user_id'];
                    $map['ordersn'] = $packageData['ordersn'];
                    $map['ordersn_type'] = $packageData['ordersn_type'];
                    $map['create_time'] = time();
                    $map['goods_num'] = $packageData['goods_num'];

                    $new_package_id = Db::name('smallwechat_user_package')->insertGetId($map);
                    Db::name('smallwechat_user_package')->update(array('id'=>$v,'is_shou'=>1));

                    $goods = Db::name('smallwechat_user_package_goods')->field('id,goods_id')->where(array('package_id'=>$v))->select();

                    foreach ($goods as $kk => $vv) {
                        $maps = array();
                        $maps['package_id'] = $new_package_id;
                        $maps['goods_id'] = $vv['goods_id'];
                        //print_r($maps);
                        Db::name('smallwechat_user_package_goods')->insert($maps);
                    }
                }

                //通知收货人和交货人
                //微信通知交货人
                // $this->send_wechat_notice(json_encode([
                //   'call'=>'send_pre_commit_notice',
                //   'msg' =>'你好,您有订单需要揽收！',
                //   'name'=>' Cklein222',
                //   'time'=>date('Y-m-d H:i:s',time()),
                //   'openid'=>'ov3-btxV-ENpabbyd0c7grgz5RP4',
                //   'remark'=>'系统通知',
                //   'uniacid'=>3,
                //   'appid' => 'wx6d1af256d76896ba',
                //   'pagepath' => ''
                // ]));
                //微信通知收货人
                // $this->send_wechat_notice(json_encode([
                //   'call'=>'send_pre_commit_notice',
                //   'msg' =>'你好,您有订单需要揽收！',
                //   'name'=>' Cklein222',
                //   'time'=>date('Y-m-d H:i:s',time()),
                //   'openid'=>'ov3-btxV-ENpabbyd0c7grgz5RP4',
                //   'remark'=>'系统通知',
                //   'uniacid'=>3,
                //   'appid' => 'wx6d1af256d76896ba',
                //   'pagepath' => ''
                // ]));
                $result['status'] = 1;
                $result['message'] = '审核成功';
            }
            else {
                $result['status'] = 0;
                $result['message'] = '审核失败';
            }

            return json($result);

        }else {
            $collectdata = Db::name('smallwechat_user_collect')->where(array('id'=>$id))->find();
            $collectdata['order_goods'] = Db::name('smallwechat_user_package')->where(array('id'=>['in',$collectdata['package_ids']]))->select();
            foreach ($collectdata['order_goods'] as $k => $v) {
                $collectdata['order_goods'][$k]['orderdata'] = Db::name('smallwechat_user_package_goods')
                ->alias("pg")
                ->join('sz_yi_goods g', 'pg.goods_id = g.id')
                ->field('pg.*,g.title')
                ->where(array('pg.package_id'=>$v['id'],'pg.is_delete'=>0))
                ->select();
            }

            $formuser = Db::name('decl_user')->where(array('id'=>$collectdata['form_user_id']))->find();

            $collectdata = Db::name('smallwechat_user_collect')->where(array('id'=>$id))->find();
            $collectdata['order_goods'] = Db::name('smallwechat_user_package')->where(array('id'=>['in',$collectdata['package_ids']]))->select();
            foreach ($collectdata['order_goods'] as $k => $v) {
                $collectdata['order_goods'][$k]['orderdata'] = Db::name('smallwechat_user_package_goods')
                ->alias("pg")
                ->join('sz_yi_goods g', 'pg.goods_id = g.id')
                ->field('pg.*,g.title')
                ->where(array('pg.package_id'=>$v['id'],'pg.is_delete'=>0))
                ->select();
            }

            $formuser = Db::name('decl_user')->where(array('id'=>$collectdata['form_user_id']))->find();
            $touser = Db::name('decl_user')->where(array('id'=>$collectdata['to_user_id']))->find();

            return view("package/package_doshou",[
                'title' => '揽收审核',
                'collectdata' => $collectdata,
                'formuser' => $formuser,
                'touser' => $touser
            ]);
        }
    }

    public function package_collect_detail()
    {
        $id = input('id');
        $collectdata = Db::name('smallwechat_user_collect')->where(array('id'=>$id))->find();
        $collectdata['order_goods'] = Db::name('smallwechat_user_package')->where(array('id'=>['in',$collectdata['package_ids']]))->select();
        foreach ($collectdata['order_goods'] as $k => $v) {
            $collectdata['order_goods'][$k]['orderdata'] = Db::name('smallwechat_user_package_goods')
            ->alias("pg")
            ->join('sz_yi_goods g', 'pg.goods_id = g.id')
            ->field('pg.*,g.title')
            ->where(array('pg.package_id'=>$v['id'],'pg.is_delete'=>0))
            ->select();
        }

        $formuser = Db::name('decl_user')->where(array('id'=>$collectdata['form_user_id']))->find();
        $touser = Db::name('decl_user')->where(array('id'=>$collectdata['to_user_id']))->find();

        return view("package/package_collect_detail",[
            'title' => '揽收详情',
            'collectdata' => $collectdata,
            'formuser' => $formuser,
            'touser' => $touser,
            'pack_id' => $id
        ]);
    }


    //发送微信通知
    public function send_wechat_notice($data) {
        $url = 'http://shop.gogo198.cn/api/sendwechattemplatenotice.php';
        $client = new \GuzzleHttp\Client();
        try {
            //正常请求
            $promise = $client->request('post', $url, ["headers" => ['Content-Type' => 'application/json'],'body'=>$data]);
        } catch (GuzzleHttpExceptionClientException $exception) {
            //捕获异常 输出错误
            return $this->error($exception->getMessage());
        }
    }


    //交收管理
    public function package_collect_manage()
    {
        return view("package/package_collect_manage",[
            'title' => '交收管理',
        ]);
    }

    //揽收人员配置
    public function package_collect_people()
    {
        if ( request()->isPost() || request()->isAjax())
        {
          $limit = $this->req->get('limit');
          $page = $this->req->get('page');
          $page = ( $page - 1 )*$limit;

          $list = Db::name('package_collect_people')->select();
          foreach ($list as $k => $v) {
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
          }
          $total = Db::name('package_collect_people')->count();

          return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);

        }else {
          return view("package/package_collect_people",[
              'title' => '揽收人员配置',
          ]);
        }
    }

    //变更清单
    public function package_sort()
    {
        return view("package/package_sort",[
            'title' => '变更清单',
        ]);
    }

    //包裹打包
    public function go_pack()
    {
        return view("package/go_pack",[
            'title' => '包裹打包',
        ]);
    }

    //包裹清单
    public function package_list()
    {
        return view("package/package_list",[
            'title' => '包裹清单',
        ]);
    }
}

?>
