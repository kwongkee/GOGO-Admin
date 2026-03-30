<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;

class PostalTaxNumber extends Auth
{
    public function index()
    {
        return view();
    }

    public function getpostal_list(Request $request)
    {
        if ($request->isAJAX()) {
            $list = Db::name('postal_tax_number')->select();
            foreach ($list as &$item) {
                
            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        } else {
            return json(['code' => -1, 'msg' => '未知错误']);
        }
    }

    public function postal_add(Request $request)
    {
        if( $request->isAJAX() )
        {
            $data = $request->post();
            Db::startTrans();
            try {
                Db::name('postal_tax_number')->insert($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '添加失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '添加成功']);
        }else{
            $this->assign('pid', $request->get('pid'));
            return view();
        }
    }

    public function postal_edit(Request $request)
    {
        if( $request->isAJAX() )
        {
            $data = $request->post();
            $id = $data['id'];
            unset($data['id']);
            Db::startTrans();
            try {
                Db::name('postal_tax_number')->where('id',$id)->update($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '更新失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '更新成功']);
        }else{
            $this->assign('data', Db::name('postal_tax_number')->where('id',$request->get('id'))->find());
            return view();
        }
    }

    public function postal_del(Request $request)
    {
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '错误！']);
        }
        
        if (Db::name('postal_tax_number')->where(['pid' => $request->get('id')])->find()) {
            return json(['code' => -1, 'msg' => '存在子级内容,不允许删除!']);
        }
        Db::name('postal_tax_number')->where('id',$request->get('id'))->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }

    public function refuseManage()
    {
        return view();
    }

    public function getrefuse_list(Request $request)
    {
        if ($request->isAJAX()) {
            $list = Db::name('postal_refuse_list')->select();
            foreach ($list as &$item) {
                $item['type'] = $item['type'] == 1 ? '品牌' : '物品';
                $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        } else {
            return json(['code' => -1, 'msg' => '未知错误']);
        }
    }

    public function refuse_add(Request $request)
    {
        if( $request->isAJAX() )
        {
            $data = $request->post();
            $data['create_time'] = time();
            Db::startTrans();
            try {
                Db::name('postal_refuse_list')->insert($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '添加失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '添加成功']);
        }else{
            return view();
        }
    }

    public function refuse_edit(Request $request)
    {
        if( $request->isAJAX() )
        {
            $data = $request->post();
            $id = $data['id'];
            unset($data['id']);
            Db::startTrans();
            try {
                Db::name('postal_refuse_list')->where('id',$id)->update($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '更新失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '更新成功']);
        }else{
            $this->assign('data', Db::name('postal_refuse_list')->where('id',$request->get('id'))->find());
            return view();
        }
    }

    public function refuse_del(Request $request)
    {
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '错误！']);
        }
        
        Db::name('postal_refuse_list')->where('id',$request->get('id'))->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }

    public function categoryManage()
    {
        return view();
    }

    public function getcatelist(Request $request)
    {
        if ($request->isAJAX()) {
            $list = Db::name('postal_category')->select();
            foreach ($list as &$item) {
                $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
                switch ($item['type']) {
                    case '1':
                        $item['type'] = '同类';
                        break;
                    case '2':
                        $item['type'] = '配比';
                        break;
                    case '3':
                        $item['type'] = '单品';
                        break;
                }
            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        } else {
            return json(['code' => -1, 'msg' => '未知错误']);
        }
    }

    public function cate_add(Request $request)
    {
        if( $request->isAJAX() )
        {
            $data = $request->post();
            $data['create_time'] = time();
            Db::startTrans();
            try {
                Db::name('postal_category')->insert($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '添加失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '添加成功']);
        }else{
            return view();
        }
    }

    public function cate_edit(Request $request)
    {
        if( $request->isAJAX() )
        {
            $data = $request->post();
            $id = $data['id'];
            unset($data['id']);
            Db::startTrans();
            try {
                Db::name('postal_category')->where('id',$id)->update($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '更新失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '更新成功']);
        }else{
            $this->assign('data', Db::name('postal_category')->where('id',$request->get('id'))->find());
            return view();
        }
    }

    public function cate_del(Request $request)
    {
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '错误！']);
        }
        
        Db::name('postal_category')->where('id',$request->get('id'))->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }

    
    public function subcate_add(Request $request)
    {
        if( $request->isAJAX() )
        {
            $data = $request->post();
            $data['numbers'] = json_encode($data['numbersData']);
            unset($data['numbersData']);
            $data['create_time'] = time();
            Db::startTrans();
            try {
                Db::name('postal_category_role')->insert($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '添加失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '添加成功']);
        }else{
            $numbers = Db::name('postal_tax_number')->field('id,pid,code_name,code_value,max_num,max_weight')->order('code_value asc')->select();
            $this->assign('cate_info', Db::name('postal_category')->where('id',$request->get('cate_id'))->find());
            $this->assign('numbers', json_encode($numbers,true));
            // $arr = $this->getTree(0);
            // $this->v($arr);
            return view();
        }
    }

    public function subcate(Request $request)
    {
        $this->assign('cate_id', $request->get('cate_id'));
        return view();
    }

    public function getsubcatelist(Request $request)
    {
        if ($request->isAJAX()) {
            $list = Db::name('postal_category_role')->where('cate_id',$request->get('cate_id'))->select();
            foreach ($list as &$item) {
                $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            }
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        } else {
            return json(['code' => -1, 'msg' => '未知错误']);
        }
    }

    public function subcate_del(Request $request)
    {
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '错误！']);
        }
        
        Db::name('postal_category_role')->where('id',$request->get('id'))->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }

    public function subcate_edit(Request $request)
    {
        if( $request->isAJAX() )
        {
            $data = $request->post();
            $data['numbers'] = json_encode($data['numbersData']);
            unset($data['numbersData']);
            $id = $data['id'];
            unset($data['id']);
            $data['create_time'] = time();
            Db::startTrans();
            try {
                Db::name('postal_category_role')->where('id',$id)->update($data);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '编辑失败'.$e->getMessage()]);
            }
            return json(['code' => 0, 'msg' => '编辑成功']);
        }else{
            $numbers = Db::name('postal_tax_number')->field('id,code_name,code_value,max_num,max_weight')->order('code_value asc')->select();
            $data = Db::name('postal_category_role')->where('id',$request->get('id'))->find();
            $this->assign('numbers', json_encode($numbers,true));
            $this->assign('data', $data);
            return view();
        }
    }



    
    public function getTree($pid=0,$level=1)
    {
        $field = 'id,pid,code_name,code_value,max_num,max_weight';
        $navList = Db::name('postal_tax_number')->field($field)->select();
        $childs = $this->getChild($navList,$pid,$level);
        //array_multisort(array_column($childs,'sort'),SORT_NUMERIC,$childs);
        foreach($childs as $key => $navItem)
        {

            $resChild = $this->getTree($navItem['id'],$level+1);
            if(null != $resChild)
            {
                $childs[$key]['child'] = $resChild;
            }

        }
        return $childs;
    }

    public function getChild(&$arr,$id,$lev)
    {
        $child = [];
        foreach($arr as $k => $value)
        {

            if($value['pid'] == $id)
            {
                $child[] = $value;
            }
        }
        return $child;
    }

    public function v($data)
    {
        echo '<pre>';
        var_dump($data);exit;
    }



}