<?php

if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

/**
 * Class ParkingLocation_EweiShopV2Page
 * 泊位定位地图查找
 */
class Imap_EweiShopV2Page extends mobilePage
{


    protected $api = 'http://14.116.253.146:8087/API/OpenApi.asmx?op=';
    protected $dom = null;

    public function __construct()
    {
        parent::__construct();
        load()->classs("curl");
    }

    /**
     * 地图ui
     */
    public function main()
    {
        global $_W;
        global $_GPC;
        include $this->template('parking/imap');
    }

    /**
     * 请求
     * @param $parm
     * @return mixed
     */
    protected function getAddrByDevApi($parm)
    {
        $curl = new Curl();
        $curl->setHeader("Content-type", "text/xml");
        $curl->post($parm['url'], $parm['data']);
        return $curl->response;
    }


    /**
     * 实例dom
     */
    protected function newDomObject()
    {
        $this->dom = new DOMDocument('1.0', 'utf-8');
    }

    /**xml解析获取数据
     * @param $xml
     * @param $nodeKey
     * @return string
     */
    protected function getNodeValByXml($xml, $nodeKey)
    {
        if (is_null($this->dom)) $this->newDomObject();
        $this->dom->loadXML($xml);
        $xmlResult = $this->dom->getElementsByTagName($nodeKey);
        return $xmlResult->item(0)->nodeValue;
    }


    /**
     * 根据两点间的经纬度计算距离
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return float
     */
    public function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6367000; //approximate radius of earth in meters
        $lat1 = ($lat1 * pi()) / 180;
        $lng1 = ($lng1 * pi()) / 180;
        $lat2 = ($lat2 * pi()) / 180;
        $lng2 = ($lng2 * pi()) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }


    /**
     * 获取所有城市信息
     */
    protected function GetAllCity()
    {
        $str = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><GetCityAll xmlns="http://14.116.253.146:8087/API/" /></soap:Body></soap:Envelope>';
        $cityResult = $this->getAddrByDevApi(['url' => $this->api . 'GetCityAll', 'data' => $str]);
        $city = $this->getNodeValByXml($cityResult, 'GetCityAllResult');
        return json_decode($city, true)['data'];
    }

    /**
     * 根据城市获取所有区域
     */
    public function GetAreaByCityId($cid)
    {
        $str = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><GetAreaByCityId xmlns="http://14.116.253.146:8087/API/"><cityId>' . $cid . '</cityId></GetAreaByCityId></soap:Body></soap:Envelope>';
        $areaResult = $this->getAddrByDevApi(['url' => $this->api . 'GetAreaByCityId', 'data' => $str]);
        $area = $this->getNodeValByXml($areaResult, 'GetAreaByCityIdResult');
        return json_decode($area, true)['data'];
    }

    /**
     * 根据区域获取所有街道
     */
    public function GetStreetByAreaId($aid)
    {
        $str = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><GetStreetByAreaId xmlns="http://14.116.253.146:8087/API/"><areaId>' . $aid . '</areaId></GetStreetByAreaId></soap:Body></soap:Envelope>';
        $streetResult = $this->getAddrByDevApi(['url' => $this->api . 'GetStreetByAreaId', 'data' => $str]);
        $street = $this->getNodeValByXml($streetResult, 'GetStreetByAreaIdResult');
        return json_decode($street, true)['data'];
    }

    /**
     * 根据街道获取所有路段
     */
    public function GetRoadByStreetId($sid)
    {
        $str = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><GetRoadByStreetId xmlns="http://14.116.253.146:8087/API/"><streetId>' . $sid . '</streetId></GetRoadByStreetId></soap:Body></soap:Envelope>';
        $roadResult = $this->getAddrByDevApi(['url' => $this->api . 'GetRoadByStreetId', 'data' => $str]);
        $road = $this->getNodeValByXml($roadResult, 'GetRoadByStreetIdResult');
        return json_decode($road, true)['data'];
    }

    /**
     * 路段停车实时信息及路段坐标信息（搜狗）
     */
    public function GetCoordinateAll($rid)
    {
        $str = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><GetCoordinateAll xmlns="http://14.116.253.146:8087/API/"><roadId>' . $rid . '</roadId></GetCoordinateAll></soap:Body></soap:Envelope>';
        $coorResult = $this->getAddrByDevApi(['url' => $this->api . 'GetCoordinateAll', 'data' => $str]);
        $coor = $this->getNodeValByXml($coorResult, 'GetCoordinateAllResult');
        return json_decode($coor, true)['data'];
    }


    /**
     * 地址选择泊位列表
     */
    public function BerthList()
    {
        global $_W;
        global $_GPC;
        //并行读取数据
        //$data['distance'] = $this->getDistance('23.032777', '113.133798', '23.032798', '113.134095');
        $signPackage = $_W['account']['jssdkconfig'];
        $data['city'] = $this->GetAllCity();
        $data['area'] = $this->GetAreaByCityId($data['city'][0]['CityId']);
        $data['street'] = $this->GetStreetByAreaId($data['area'][0]['AreaId']);
        $data['road'] = $this->GetRoadByStreetId($data['street'][0]['StreetId']);
        $data['coor'] = $this->GetCoordinateAll($data['road'][0]['RoadId']);
        $data['addr'] = $data['city'][0]['CityName'].$data['area'][0]['AreaName'];
        include $this->template('parking/berthlist');
    }


    /**
     * 获取街道列表
     */
    public function getStreet(){
        global $_GPC;
        $road= $this->GetStreetByAreaId($_GPC['key']);
//        $coor = $this->GetStreetByAreaId($road[0]['RoadId']);
        show_json(1,['list'=>$road]);
    }


    /**
     * 获取街道泊位信息
     */
    public function getRoad(){
        global $_GPC;
        $road = $this->GetRoadByStreetId($_GPC['key']);
        $coor = $this->GetCoordinateAll($road[0]['RoadId']);
        show_json(1,['list'=>$coor['roads']]);
    }

    /**
     * 地图形式
     * [imap description]
     * @return [view] [视图]
     */
    public function imap()
    {
        echo 123;
    }
}
