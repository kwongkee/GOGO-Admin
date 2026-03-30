<?php

// 获取经纬度
function getLatLng($address='',$city='') {
    $result = array();
    $ak = 'W3LqS3T6cYPAKBo4GgAQBuFdg6nWtjsT';//您的百度地图ak，可以去百度开发者中心去免费申请
    $url ="http://api.map.baidu.com/geocoder/v2/?callback=renderOption&output=json&address=".$address."&city=".$city."&ak=".$ak;
    $data = file_get_contents($url);
    $data = str_replace('renderOption&&renderOption(', '', $data);
    $data = str_replace(')', '', $data);
    $data = json_decode($data,true);
    if (!empty($data) && $data['status'] == 0) {
        $result['lat'] = $data['result']['location']['lat'];
        $result['lng'] = $data['result']['location']['lng'];
        return $result;//返回经纬度结果
    }else{
        return null;
    }
}


function myNearShop(){
    $slat = session('lat');
    $slng = session('lng');
    $sql =  "SELECT *, ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($slat*PI()/180-lat*PI()/180)/2),2)+COS($slat*PI()/180)*COS(lat*PI()/180)*POW(SIN(($slng*PI()/180-lng*PI()/180)/2),2)))) AS juli  
    FROM sline_mall where is_check = 1 HAVING juli <= 5";
    $shop =  M('mall')->query($sql);
    //$this->assign('shop',$shop);
    assign('shop',$shop);
}

echo '<pre>';
$get = getLatLng('广东省佛山市南海区南海广场','佛山南海');
print_r($get);

die;


// 定位
/*function location()
{
    $lng = I('post.lng');
    $lat = I('post.lat');
    if (!$lng || !$lat) {
        $this->ajaxReturn(0);
    }
    $url = 'http://api.map.baidu.com/geocoder/v2/?ak=C03899adf85230a417ad037197e5bd88&callback=renderReverse&location=' . $lat . ',' . $lng . '&output=json&pois=1';
    $content = file_get_contents($url);
    //dump($content); die();
    preg_match('/"district":"(.*?)",/msi', $content, $matches);
    $city = $matches[1];
    if ($city) {
        $city_id = M('mall_zone')->where('kindname = "' . $city . '"')->getField('pid as id');
        if ($city_id) {
            cookie('lng',$lng);
            cookie('lat',$lat);
            cookie('city',$city);
            cookie('city_id', $city_id);
            $this->ajaxReturn(1);
        } else {
            $this->ajaxReturn(0);
        }
    }
}*/

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

</body>
<script src="http://mat1.gtimg.com/libs/jquery2/2.2.0/jquery.js"></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=W3LqS3T6cYPAKBo4GgAQBuFdg6nWtjsT"></script>
<script>
    var geolocation = new BMap.Geolocation();
    geolocation.getCurrentPosition(function(r) {
        if (this.getStatus() == BMAP_STATUS_SUCCESS) {
            var url = "/";
            var lng = r.point.lng;
            var lat = r.point.lat;
            $.post(url, {lng: lng, lat: lat},function(data){
                if (data == 1) {
                    return false;
                }else{
                    lyaer.msg('定位失败');
                }
            });

        }else {
            lyaer.msg('failed' + this.getStatus());
        }
    }, {enableHighAccuracy: true})
</script>
</html>
