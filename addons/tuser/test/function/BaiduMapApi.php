<?php

/**
 * 百度地图api
 */
class BaiduMapApi {

    private $ak = '345cade0d861b77d1441f57faa65b873';
    private $api_url = 'http://api.map.baidu.com/geoconv/v1/?';

    /**
     * 转换经纬度为百度坐标
     * @param type $coords 经纬度 114.21892734521,29.575429778924
     */
    public function change_to_baidu_coordinate($coords) {
        $param = array(
            'coords' => $coords,
            'from' => 1,
            'to' => 5,
            'ak' => $this->ak,
        );
        $request_url = $this->api_url . http_build_query($param);
        $responce = file_get_contents($request_url);
        $responce = json_decode($responce, true);
        if ($responce && $responce['status'] == 0) {
            return $responce['result'][0];
        } else {
            return false;
        }
    }

    public function geo_location($coords) {
        $request_url = "http://api.map.baidu.com/geocoder/v2/?ak=" . $this->ak . "&coordtype=wgs84ll&location={$coords}&output=json";
        $responce = file_get_contents($request_url);
        $responce = json_decode($responce, true);
        if ($responce && $responce['status'] == 0) {
            return $responce['result'];
        } else {
            return false;
        }
    }

}
?>
