<?php

namespace app\api_v3\controller;

use think\Db;

/**
 * 小程序接口
 * Class WechatManage
 * @package app\api_v3\controller
 */
class WechatManage
{


    //获取会员
    public function getuser() {

        $openid = input('openid');
        $keyword = input('keyword');
        if( $keyword )
        {
            $map['nickname|phonenumber'] = ['like','%'.$keyword.'%'];
        }
        $map['openid'] = ['neq',$openid];
        $field = 'id,nickname,avatarurl';
        $userList = Db::name('smallwechat_user')->field($field)->where($map)->select();
        $result['data'] = $userList;
        return json($result);
    }

    //获取会员权限
    public function getauth() {

        $user_id = input('user_id');
        $authList = Db::name('smallwechat_user_auth')->select();
        $userAuth = Db::name('smallwechat_user')->field('id,auth')->where(array('id'=>$user_id))->find();
        $result['authList'] = $authList;
        foreach ($authList as $k => $v) {
            $authList[$k]['checked'] = false;
        }
        $result['userAuth'] = $userAuth;
        return json($result);
    }

    //设置会员权限
    public function setuserauth()
    {
        $user_id = input('user_id');
        $auth = input('auth');
        if( Db::name('smallwechat_user')->update(array('id'=>$user_id, 'auth'=>$auth)) )
        {
            $result['status'] = 1;
            $result['msg'] = '修改成功';
        }else{
            $result['status'] = 0;
            $result['msg'] = '修改失败';
        }
        return json($result);
    }

    //获取项目列表
    public function getprojectlist()
    {
        $projectList = Db::name('smallwechat_bill_project')->select();
        $result['projectList'] = $projectList;
        return json($result);
    }

    //新增项目
    public function addproject()
    {
        $name = input('project_name');
        if( Db::name('smallwechat_bill_project')->insert(array('name'=>$name, 'create_time'=>time())) )
        {
            $result['status'] = 1;
            $result['msg'] = '添加成功';
        }
        else{
            $result['status'] = 0;
            $result['msg'] = '添加失败';
        }
        return json($result);
    }

    //获取项目数据
    public function getprojectdata()
    {
        $project_id = input('project_id');
        $data = Db::name('smallwechat_bill_project')->where(array('id'=>$project_id))->find();
        $datas = Db::name('smallwechat_bill_project_data')->where(array('project_id'=>$project_id))->select();
        $result['data'] = $data;
        $result['datas'] = $datas;
        return json($result);
    }

    //设置项目数据
    public function setprojectdata()
    {
        $dataList = input('dataList/a');
        $project_id = input('project_id');
        Db::name('smallwechat_bill_project_data')->where(array('project_id'=>$project_id))->delete();
        foreach ($dataList as $k => $v) {
            $map = array();
            $map['name'] = $v['name'];
            $map['project_id'] = $project_id;
            $map['create_time'] = time();
            Db::name('smallwechat_bill_project_data')->insert($map);
        }

        $result['msg'] = '提交成功';
        return json($result);
    }

    //删除项目
    public function delproject()
    {
        $project_id = input('project_id');
        Db::name('smallwechat_bill_project_data')->where(array('project_id'=>$project_id))->delete();
        Db::name('smallwechat_bill_project')->where(array('id'=>$project_id))->delete();
        $result['msg'] = '删除成功';
        return json($result);
    }


    
    

}
