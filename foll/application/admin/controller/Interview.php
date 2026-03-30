<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;


class Interview extends Auth
{
    public function joblist()
    {
        $list = Db::name('interview_job')->select();
        $this->assign('lists', $list);
        return view("interview/job/list");
    }

    public function jobadd(Request $request)
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $data['create_time'] = time();
            if( Db::name('interview_job')->insert($data) )
            {
                return json(['code'=>1,'msg'=>'新增成功']);
            }else{
                return json(['code'=>0,'msg'=>'新增失败']);
            }
        }else{
            return view("interview/job/add");
        }
    }

    public function jobedit(Request $request){
        if ( request()->isPost() || request()->isAjax()){
            $data = input();
            if( Db::name('interview_job')->update($data) )
            {
                return json(['code'=>1,'msg'=>'编辑成功']);
            }else{
                return json(['code'=>0,'msg'=>'编辑失败']);
            }
        }else{
            $id = input('id');
            $data = Db::name('interview_job')->where('id',$id)->find();
            $this->assign('data', $data);
            return view("interview/job/edit");
        }
    }
    public function jobdel()
    {
        $id = input('id');
        if( Db::name('interview_job')->where('id',$id)->delete() )
        {
            return json(['code'=>1,'msg'=>'删除成功']);
        }else{
            return json(['code'=>0,'msg'=>'删除失败']);
        }
    }
    public function qaqlist()
    {
        $list = Db::name('interview_question')->select();
        $this->assign('lists', $list);
        return view("interview/ques/list");
    }
    public function qaqadd(Request $request)
    {
        if (request()->isPost()){
            $data = input();
            if ($data["type"]==2) {
                $data['question_options'] = json_encode($data["question_options"]);
            }
            $data['create_time'] = time();
            if( Db::name('interview_question')->insert($data) )
            {
                return json(['code'=>1,'msg'=>'新增成功']);
            }else{
                return json(['code'=>0,'msg'=>'新增失败']);
            }
        }else{
            return view("interview/ques/add");
        }
    }
    public function qaqedit(Request $request){
        if (request()->isPost()){
            $data = input();
            if ($data["type"]==2) {
                $data['question_options'] = json_encode($data["question_options"]);
            }
            $result  =Db::name('interview_question')->update($data);
            if($result){
                return json(['code'=>1,'msg'=>'编辑成功']);
            }else{
                return json(['code'=>0,'msg'=>'编辑失败']);
            }
        }else{
            $id = input('id');
            $data = Db::name('interview_question')->where('id',$id)->find();
            $this->assign('data', $data);
            return view("interview/ques/edit");
        }
    }
    public function qaqdel()
    {
        $id = input('id');
        if( Db::name('interview_question')->where('id',$id)->delete() )
        {
            return json(['code'=>1,'msg'=>'删除成功']);
        }else{
            return json(['code'=>0,'msg'=>'删除失败']);
        }
    }
    public function formlist(){
        $list = Db::name('interview_form')->select();
        $qaqlist = Db::name('interview_question')->select();
        $joblist = Db::name('interview_job')->select();
        $this->assign('lists', $list);
        $this->assign('qaqlist', $qaqlist);
        $this->assign('joblist', $joblist);
        return view("interview/form/list");
    }
    public function formadd(){
        if (request()->isPost()){
            $data = input();
            if (empty($data["question_id"])) {
                return json(['code'=>0,'msg'=>'至少选择一个题目']);
            }
            $data['question_id'] =implode(",",$data["question_id"]);
            $data['create_time'] = time();
            $data['form_status'] = 1;
            if( Db::name('interview_form')->insert($data) )
            {
                return json(['code'=>1,'msg'=>'保存成功']);
            }else{
                return json(['code'=>0,'msg'=>'保存失败']);
            }
        }else{
            $joblist = Db::name('interview_job')->select();
            $queslist = Db::name('interview_question')->select();
            $this->assign('joblist', $joblist);
            $this->assign('queslist', $queslist);
            return view("interview/form/add");
        }
    }
    public function formedit(Request $request){
        if (request()->isPost()){
            $data = input();
            if (empty($data["question_id"])) {
                return json(['code'=>0,'msg'=>'至少选择一个题目']);
            }
            $data['question_id'] =implode(",",$data["question_id"]);
            $result  =Db::name('interview_form')->update($data);
            if($result){
                return json(['code'=>1,'msg'=>'编辑成功']);
            }else{
                return json(['code'=>0,'msg'=>'编辑失败']);
            }
        }else{
            $id = input('id');
            $data = Db::name('interview_form')->where('id',$id)->find();
            $joblist = Db::name('interview_job')->select();
            $queslist = Db::name('interview_question')->select();
            $data["question_id"] =explode(",",$data["question_id"]);
            $this->assign('joblist', $joblist);
            $this->assign('queslist', $queslist);
            $this->assign('data', $data);
            return view("interview/form/edit");
        }
    }
    public function formview(Request $request){
        return view("interview/form/view");
    }
    public function formdel(){}
    public function invitelist(){
        // $list = Db::name('interview_job')->select();
        // $this->assign('lists', $list);
        return view("interview/invite/list");
    }
}