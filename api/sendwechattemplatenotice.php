<?php



include "../framework/bootstrap.inc.php";
include "../framework/class/weixin.account.class.php";
// require  '../framework/class/loader.class.php';

function sendDefaultMsg($post) {
    $data = array(
        'first' => array(
            'value' => $post['first'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['keyword1'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['keyword2'],
            'color' => '#ff510'
        ),
        'keyword3' => array(
            'value' => $post['keyword3'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );
    if($post['appid'] != '')
    {
      $miniprogram = array(
        'appid' => $post['appid'],
        'pagepath' => $post['pagepath']
      );
    }
    else{
      $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>3));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        $post['temp_id'],
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

//商户提交-已寄快递通知管理员
function sendNewInfoMsg($post) {
    $data = array(
        'first' => array(
            'value' => $post['first'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['keyword1'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['keyword2'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );
    if($post['appid'] != '')
    {
        $miniprogram = array(
            'appid' => $post['appid'],
            'pagepath' => $post['pagepath']
        );
    }
    else{
        $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>3));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        $post['temp_id'],
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

// 通用通知发送
function send_common_msg($post)
{
    $msg = array(
        'keyword1' => array('value' => $post['title'], 'color' => '#73a68d'),
        'keyword2' => array('value' => $post['content'], 'color' => '#73a68d')
    );
    $url = $post['url'];

    $wx = pdo_get('account_wechats',array('uniacid'=>3));
    $account = new WeiXinAccount($wx);

    if (!$account) {
        return NULL;
    }

    $content = '';

    if (is_array($msg)) {
        foreach ($msg as $key => $value) {
            if (!empty($value['title'])) {
                $content .= $value['title'] . ':' . $value['value'] . "\n";
            }
            else {
                $content .= $value['value'] . "\n";

                if ($key == 0) {
                    $content .= "\n";
                }
            }
        }
    }
    else {
        $content = $msg;
    }

    if (!empty($url)) {
        $content .= '<a href=\'' . $url . '\'>点击查看详情</a>';
    }
    $res = $account->sendCustomNotice(array(
        'touser'  => $post['openid'],
        'msgtype' => 'text',
        'text'    => array('content' => urlencode($content))
    ));
    file_put_contents('./123.txt',json_encode($res));
    return $res;
}

// 发送申请通知
// 资料提交模板id：8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI
function sendApplyMsg($post)
{
    $data = array(
        'first' => array(
            'value' => $post['title'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['user_name'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['time'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );
    if($post['appid'] != '')
    {
      $miniprogram = array(
        'appid' => $post['appid'],
        'pagepath' => $post['pagepath']
      );
    }
    else{
      $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>3));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI',
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

// 发送审核通知
// 审核模板id：SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8
function sendCheckMsg($post) {
    $data = array(
        'first' => array(
            'value' => $post['title'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['projects'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['status_text'],
            'color' => '#ff510'
        ),
        'keyword3' => array(
            'value' => $post['time'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );
    if($post['appid'] != '')
    {
      $miniprogram = array(
        'appid' => $post['appid'],
        'pagepath' => $post['pagepath']
      );
    }
    else{
      $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>3));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8',
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

//发送预提通知
function send_pre_commit_notice($post){
    $data = array(
        'first' => array(
            'value' => $post['msg'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['name'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['time'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );
    if($post['appid'] != '')
    {
      $miniprogram = array(
        'appid' => $post['appid'],
        'pagepath' => $post['pagepath']
      );
    }
    else{
      $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>$post['uniacid']));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI',
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

function send_pre_commit_notice_d($post){
    $data = array(
        'first' => array(
            'value' => $post['first'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['dates'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['payType'],
            'color' => '#ff510'
        ),
        'keyword3' => array(
            'value' => $post['ordersn'],
            'color' => '#ff510'
        ),
        'keyword4' => array(
            'value' => $post['payMoney'],
            'color' => '#ff510'
        ),
        'keyword5' => array(
            'value' => $post['result'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );
    if($post['appid'] != '')
    {
      $miniprogram = array(
        'appid' => $post['appid'],
        'pagepath' => $post['pagepath']
      );
    }
    else{
      $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>$post['uniacid']));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        'f35Y6je6nrC1gxp-PJYp-agppwEy25WOqFR1oBD_wIo',
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

//发送文本信息给粉丝
function sendTextToFans($post)
{
    $custom = array(
        'msgtype' => 'text',
        'text' => array('content' => urlencode($post['msg'])),
        'touser' => $post['touser'],
    );
    $account = pdo_get('account_wechats',array('uniacid'=>$post['uniacid']));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendCustomNotice($custom);
    return json_encode($result);
}

//催款通知
function pressMoneyToMember($post){
    $data = array(
        'first' => array(
            'value' => $post['first'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['keyword1'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['keyword2'],
            'color' => '#ff510'
        ),
        'keyword3' => array(
            'value' => $post['keyword3'],
            'color' => '#ff510'
        ),
        'keyword4' => array(
            'value' => $post['keyword4'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );

    if($post['appid'] != '')
    {
        $miniprogram = array(
            'appid' => $post['appid'],
            'pagepath' => $post['pagepath']
        );
    }
    else{
        $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>3));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        $post['temp_id'],
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

//收款通知
function collectionNotice($post){
    $data = array(
        'first' => array(
            'value' => $post['first'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['keyword1'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['keyword2'],
            'color' => '#ff510'
        ),
        'keyword3' => array(
            'value' => $post['keyword3'],
            'color' => '#ff510'
        ),
        'keyword4' => array(
            'value' => $post['keyword4'],
            'color' => '#ff510'
        ),
        'keyword5' => array(
            'value' => $post['keyword5'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );

    if($post['appid'] != '')
    {
        $miniprogram = array(
            'appid' => $post['appid'],
            'pagepath' => $post['pagepath']
        );
    }
    else{
        $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>3));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        $post['temp_id'],
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

//确认收款通知\审核未予收款通知
function confirmCollectionNotice($post){
    $data = array(
        'first' => array(
            'value' => $post['first'],
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $post['keyword1'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => $post['keyword2'],
            'color' => '#ff510'
        ),
        'keyword3' => array(
            'value' => $post['keyword3'],
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => $post['remark'],
            'color' => '#ff510'
        ),
    );

    if($post['appid'] != '')
    {
        $miniprogram = array(
            'appid' => $post['appid'],
            'pagepath' => $post['pagepath']
        );
    }
    else{
        $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>3));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        $post['temp_id'],
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

//工单待处理通知
function workorderToMember($post){
    $data = array(
        'thing20' => array(
            'value' => $post['thing20'],
            'color' => '#ff510'
        ),
        'time48' => array(
            'value' => $post['time48'],
            'color' => '#ff510'
        ),
    );

    if($post['appid'] != '')
    {
        $miniprogram = array(
            'appid' => $post['appid'],
            'pagepath' => $post['pagepath']
        );
    }
    else{
        $miniprogram = '';
    }
    $account = pdo_get('account_wechats',array('uniacid'=>3));
    $wx = new WeiXinAccount($account);
    $result = $wx->sendTplNotice(
        $post['openid'],
        $post['temp_id'],
        $data,
        $post['url'],
        $miniprogram
    );
    return json_encode($result);
}

$post  = json_decode(file_get_contents('php://input'),true);
if (empty($post)){
    exit(json_encode(['code'=>-1,'message'=>'参数错误']));
}
exit($post['call']($post));
