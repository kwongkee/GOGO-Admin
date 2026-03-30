<?php

namespace app\admin\logic;

use think\Model;

class User extends Model
{
    protected $table = 'ims_foll_user';
    public function userInfo($tel)
    {
//      $list=User::get(['tel'=>$tel,'role'=>0]);
        $list=User::get(['tel'=>$tel]);
        if(empty($list)){
            return false;
        }else{
            return $list->toArray();
        }
    }

}