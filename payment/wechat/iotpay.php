<?php
$url = $_GET['url'];
$url = base64_decode($url);
$locaUrl = json_decode($url, true)['callback_url'];
//  var_dump($locaUrl);
// exit();
$html = "document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
        WeixinJSBridge.invoke('getBrandWCPayRequest',".$url.", function(res) {
            if(res.err_msg == 'get_brand_wcpay_request:ok') {
                location.href='".$locaUrl."'
            } else {
                alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
                history.go(-1);
            }
        });
    }, false);";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>微信支付</title>
</head>
<body>
</body>
<script type="text/javascript">
    <?php
        echo $html;
    ?>
</script>
</html>
