<?php


namespace app\api\controller;

use think\Request;
use think\Controller;
use think\Loader;
class AgroOrderInfo extends Controller
{
    public function getAgroOrder(Request $request)
    {
        $logic = Loader::model('Agro','logic');
        return $logic->getAgroUserOrder($request->post());
    }
}