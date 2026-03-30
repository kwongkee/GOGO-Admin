<?php

namespace app\declares\logic;
use think\Model;
use think\Cache;
use think\Db;
class DataCount{
    
    public static $subCount = 0;//总提交数
    
    public static $batchCount = 0;//批次总提交数
    
    public static $errSub = 0;//错误提交数
    
    public static $batchErrorSub = 0;//批次错误提交数
    
    public static $payCount = 0;//支付成功数
    
    public static $batchPayCount = 0;//批次支付成功数
    
    public static $elecCount = 0;//申报成功数
    
    public static $batchElecCount = 0;//批次申报成功数
    
    public static $errPay = 0;//错误支付数
    
    public static $batchErrPay = 0;//批次失败支付数
    
    public static $errElec = 0;//申报失败数
    
    public static $batchErrELec = 0;//批次申报失败数
    
    public static $batchNum = null;
    
    public static $data = null;
    
    /**
     * @param $uid
     */
    protected static function fetchBatchInfo ( $uid )
    {
        self::$data = Db::name('foll_elec_count')->where(['uid' => $uid, 'batch_num' => self::$batchNum])->find();
        if ( !self::$data ) {
            self::$data = ['id' => 0, 'uid' => $uid, 'batch_num' => null, 'total_count' => null, 'batchCount' => null, 'errSub' => null, 'batchErrorSub' => null, 'payCount' => null, 'batchPayCount' => null, 'elecCount' => null, 'batchElecCount' => null, 'errPay' => null, 'batchErrPay' => null, 'errElec' => null, 'batchErrELec' => null,];
        }
    }
    
    /**
     * 总提交数
     * @param $uid
     */
    protected static function subCount ( $uid )
    {
        $result = Db::name('foll_elec_count')->where('uid', $uid)->sum('total_count');
        
        if ( is_null($result) ) {
            $result = 0;
        }
        Cache::set('subCount:' . self::$batchNum, $result, 21600);
        return $result;
    }
    
    /**批次总提交数
     * @param $uid
     */
    protected static function batchCount ( $uid )
    {
        is_null(self::$data) && self::fetchBatchInfo($uid);
        if ( is_null(self::$data['batchCount']) ) {
            self::$data['batchCount'] = 0;
        }
        Cache::set('batchCount:' . self::$batchNum, self::$data['batchCount'], 21600);
        return self::$data['batchCount'];
    }
    
    /**错误提交数
     * @param $uid
     */
    protected static function errSub ( $uid )
    {
        $result = Db::name('foll_elec_count')->where('uid', $uid)->sum('errSub');
        if ( is_null($result) ) {
            $result = 0;
        }
        Cache::set('errSub:' . self::$batchNum, $result, 21600);
        return $result;
    }
    
    /**
     * 批次错误提交数
     * @param $uid
     */
    protected static function batchErrorSub ( $uid )
    {
        is_null(self::$data) && self::fetchBatchInfo($uid);
        if ( is_null(self::$data['batchErrorSub']) ) {
            self::$data['batchErrorSub'] = 0;
        }
        Cache::set('batchErrorSub:' . self::$batchNum, self::$data['batchErrorSub'], 21600);
        return self::$data['batchErrorSub'];
    }
    
    /**
     * 支付成功数
     * @param $uid
     */
    protected static function payCount ( $uid )
    {
        
        $result = Db::name('foll_elec_count')->where('uid', $uid)->sum('payCount');
        if ( is_null($result) ) {
            $result = 0;
        }
        Cache::set('payCount:' . self::$batchNum, $result, 21600);
        return $result;
    }
    
    /**批次支付成功数
     * @param $uid
     */
    protected static function batchPayCount ( $uid )
    {
        is_null(self::$data) && self::fetchBatchInfo($uid);
        if ( is_null(self::$data['batchPayCount']) ) {
            self::$data['batchPayCount'] = 0;
        }
        Cache::set('batchPayCount:' . self::$batchNum, self::$data['batchPayCount'], 21600);
        return self::$data['batchPayCount'];
    }
    
    /**申报成功数
     * @param $uid
     */
    protected static function elecCount ( $uid )
    {
        $result = Db::name('foll_elec_count')->where('uid', $uid)->sum('elecCount');
        if ( is_null($result) ) {
            $result = 0;
        }
        Cache::set('elecCount:' . self::$batchNum, $result, 21600);
        return $result;
    }
    
    /**
     * 批次申报成功数
     * @param $uid
     */
    protected static function batchElecCount ( $uid )
    {
        is_null(self::$data) && self::fetchBatchInfo($uid);
        if ( is_null(self::$data['batchElecCount']) ) {
            self::$data['batchElecCount'] = 0;
        }
        Cache::set('batchElecCount:' . self::$batchNum, self::$data['batchElecCount'], 21600);
        return self::$data['batchElecCount'];
    }
    
    /**错误支付数
     * @param $uid
     */
    protected static function errPay ( $uid )
    {
        $result = Db::name('foll_elec_count')->where('uid', $uid)->sum('errPay');
        if ( is_null($result) ) {
            $result = 0;
        }
        Cache::set('errPay:' . self::$batchNum, $result, 21600);
        return $result;
    }
    
    /**
     * 批次失败支付数
     * @param $uid
     */
    protected static function batchErrPay ( $uid )
    {
        is_null(self::$data) && self::fetchBatchInfo($uid);
        if ( is_null(self::$data['batchErrPay']) ) {
            self::$data['batchErrPay'] = 0;
        }
        Cache::set('batchErrPay:' . self::$batchNum, self::$data['batchErrPay'], 21600);
        return self::$data['batchErrPay'];
    }
    
    /**申报失败数
     * @param $uid
     */
    protected static function errElec ( $uid )
    {
        $result = Db::name('foll_elec_count')->where('uid', $uid)->sum('errElec');
        if ( is_null($result) ) {
            $result = 0;
        }
        Cache::set('errElec:' . self::$batchNum, $result, 21600);
        return $result;
    }
    
    /**批次申报失败数
     * @param $uid
     */
    protected static function batchErrELec ( $uid )
    {
        is_null(self::$data) && self::fetchBatchInfo($uid);
        if ( is_null(self::$data['batchErrELec']) ) {
            self::$data['batchErrELec'] = 0;
        }
        Cache::set('batchErrELec:' . self::$batchNum, self::$data['batchErrELec'], 21600);
        return self::$data['batchErrELec'];
    }
    
    public function getCount($batchNum)
    {
        self::$batchNum=$batchNum;
        $uid = Session('admin.id');
        return [
            'subCount'      => Cache::has('subCount:'.$batchNum)?Cache::get('subCount:'.$batchNum):self::subCount($uid),
            'batchCount'    => Cache::has('batchCount:'.$batchNum)?Cache::get('batchCount:'.$batchNum):self::batchCount($uid),
            'errSub'        => Cache::has('errSub:'.$batchNum)?Cache::get('errSub:'.$batchNum):self::errSub($uid),
            'batchErrorSub' => Cache::has('batchErrorSub:'.$batchNum)?Cache::get('batchErrorSub:'.$batchNum):self::batchErrorSub($uid),
            'payCount'      => Cache::has('payCount:'.$batchNum)?Cache::get('payCount:'.$batchNum):self::payCount($uid),
            'batchPayCount' => Cache::has('batchPayCount:'.$batchNum)?Cache::get('batchPayCount:'.$batchNum):self::batchPayCount($uid),
            'elecCount'     => Cache::has('elecCount:'.$batchNum)?Cache::get('elecCount:'.$batchNum):self::elecCount($uid),
            'batchElecCount'=> Cache::has('batchElecCount:'.$batchNum)?Cache::get('batchElecCount:'.$batchNum):self::batchElecCount($uid),
            'errPay'        => Cache::has('errPay:'.$batchNum)?Cache::get('errPay:'.$batchNum):self::errPay($uid),
            'batchErrPay'   => Cache::has('batchErrPay:'.$batchNum)?Cache::get('batchErrPay:'.$batchNum):self::batchErrPay($uid),
            'errElec'       => Cache::has('errElec:'.$batchNum)?Cache::get('errElec:'.$batchNum):self::errElec($uid),
            'batchErrELec'  => Cache::has('batchErrELec:'.$batchNum)?Cache::get('batchErrELec:'.$batchNum):self::batchErrELec($uid)
        ];
    }
    
    
}