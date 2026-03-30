<?php

namespace app\admin\model;

use think\Model;

abstract class MerchantModel extends Model
{


    /**
     * @param null $data
     * @return mixed
     */
    abstract public function createUser($data = null);
}
