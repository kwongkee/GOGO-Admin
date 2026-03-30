<?php

namespace app\admin\controller;


use think\Db;
use think\Request;

/**
 * 申报主体转换
 * Class SubjectConversion
 * @package app\admin\controller
 */
class SubjectConversion extends Auth
{
    public function save(Request $request)
    {
        $data = $request->post();
        // if (empty($data['ebent_no']) || empty($data['ebent_name']) || empty($data['ebp_ent_no']) || empty($data['ebp_ent_name']) || empty($data['internet_domain_name'])) {
        //     return json(['code' => 1, 'message' => '请填写完整']);
        // }
        Db::name('customs_declare_body')->insert([
            'batch_num'            => $data['batch_num'],
            'decl_ent_no'          => $data['decl_ent_no'],
            'decl_ent_name'        => $data['decl_ent_name'],
            'ebent_no'             => $data['ebent_no'],
            'ebent_name'           => $data['ebent_name'],
            'ebp_ent_no'           => $data['ebp_ent_no'],
            'ebp_ent_name'         => $data['ebp_ent_name'],
            'internet_domain_name' => $data['internet_domain_name'],
            'edi'                  => $data['edi'],
            'http_key'             => '12345678',
            'created_time'         => time(),
        ]);
        return json(['code' => 0, 'message' => '添加成功']);
    }
}