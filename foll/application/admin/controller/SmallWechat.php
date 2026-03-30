<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;


class SmallWechat extends Auth
{

    //信息配置
    public function config(Request $request)
    {
        if ( request()->isPost() )
        {
            $data = $request->post();
            Db::startTrans();
            try{
                Db::name('smallwechat_config')->where(array('id'=>1))->update($data);
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
                return json(['code'=>-1,'message'=>$e->getMessage()]);
            }
            return json(['code'=>0,'message'=>'保存成功']);
        }
        else
        {
            $infos = DB::name('smallwechat_config')->where(array('id'=>1))->find();
            return view("smallwechat/config",[
                'title' => '小程序配置',
                'infos' => $infos
            ]);
        }
    }

    //用户列表
    public function userlist()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();

            $search = input('search');
            if($search)
            {
                $map['nickname|phonenumber'] = ['like','%'.$search.'%'];
            }

            $list = Db::name('smallwechat_user')->where($map)->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                $list[$k]['avatarurl'] = "<img src=".$v['avatarurl']." width='50' height='50' />";
                switch ($v['gender']) {
                    case '1':
                        $list[$k]['gender'] = "男";
                        break;
                    case '2':
                        $list[$k]['gender'] = "女";
                        break;
                    case '0':
                        $list[$k]['gender'] = "未知";
                        break;
                }
                if(!$v['phonenumber'])
                {
                    $list[$k]['phonenumber'] = '未绑定';
                }
                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                if($v['add_status']==0)
                {
                    $list[$k]['manage'] = '<button style="margin-right: 5px;" type="button" onclick="sendAddWxFriend('."'".$v['id']."'".')" class="btn btn-primary btn-xs">添加微信好友</button>';
                }
                else {
                    $list[$k]['manage'] = '<button style="margin-right: 5px;" type="button" class="btn btn-primary btn-xs">已添加</button>';
                }

                $list[$k]['manage'] .= '<button type="button" onclick="setAuth('."'权限配置','".Url('admin/smallwechat/setauth')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">权限配置</button>';
            }
            $total = Db::name('smallwechat_user')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else {
            return view("smallwechat/userlist",[
                'title' => '小程序用户列表',
            ]);
        }
    }


    //配置权限
    public function setauth()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $user_id = input('user_id');
            $auth = input('auth/a');
            $user = Db::name('smallwechat_user')->where(array('id'=>$user_id))->find();
            if(!$user)
            {
                return json(['status'=>0,'message'=>'暂无该用户']);
            }else {
                if( Db::name('smallwechat_user')->update(array('id'=>$user['id'],'auth'=>implode(',',$auth))) )
                {
                    return json(['status'=>1,'message'=>'修改成功']);
                }else {
                    return json(['status'=>0,'message'=>'修改失败']);
                }
            }
        }else {
            $user_id = input('user_id');
            $auth = Db::name('smallwechat_user_auth')->select();
            $user = Db::name('smallwechat_user')->field('id,auth')->where(array('id'=>$user_id))->find();
            return view("smallwechat/setauth",[
                'title' => '权限配置',
                'auth'  => $auth,
                'user_id' => $user_id,
                'user'  => $user
            ]);
        }
    }



    /**
     * 添加微信好友
     * @param  Request  $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sendAddWxFriend(Request $request)
    {
        $id=$request->get('id');
        if (!is_numeric($id)){
            return json(['code'=>1,'msg'=>'错误参数']);
        }
        Db::name('smallwechat_user')->where('id',$id)->update(['add_status'=>1]);
        return json(['code'=>0,'msg'=>'等待执行']);
    }


}

?>
