<?php

namespace app\admin\model;

use think\Model;

class OtherGoodsDataModel extends Model
{
    protected $table = 'ims_other_goods_data';
    protected $connection = [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        // 数据库名
        'database'    => 'goods',
        // 数据库用户名
        'username'    => 'gogo198',
        // 数据库密码
        'password'    => 'Gogo@198',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库调试模式
        'debug'       => false,
    ];
}
