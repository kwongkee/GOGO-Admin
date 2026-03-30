<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$shopset = m('common')->getSysset('shop');
$set = m('common')->getSysset(array('pay'));

if($op=='display'){
    if(empty($_W['openid'])){
        echo '<h3>openid不能为空！</h3>';exit;
    }
    $user = pdo_fetch('select * from '.tablename('website_user').' where openid=:openid',[':openid'=>$_W['openid']]);
    if(empty($user)){
        echo '<h1>你还未登录<a href="//www.gogo198.net/?s=index/customer_login" target="_blank" style="color:#1790ff;font-weight: 800;text-decoration: underline;">购购网</a>，请先登录成为购购网会员后再来~~</h1>';exit;
    }
    $dat = $_GPC;

    if ($_W['isajax']) {
        if($dat['pa']==1){
            #开始问问题，插入数据表
            $content = trim($dat['content']);
            if(empty($content)){
                echo json_encode(['code'=>-1,'msg'=>'请输入问题'],true);exit;
            }

            $ip = $_SERVER['REMOTE_ADDR'];

            #新版查询本地电脑大模型并携带问题ID和翻译后的语句======================START
            if(11>2) {
                $res = pdo_insert('train_qa_history', ['pid' => 0, 'cid' => 0, 'ip' => $ip, 'uid' => $user['id'], 'text' => $content, 'createtime' => time()]);
                $question_id = pdo_insertid();
//                $question_id = 182;
                #检测用户的母语和大模型翻译后回答
                $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/translate_word&question_id='.$question_id, json_encode(['question_id' => $question_id], true));
                if ($res == -1) {
                    #暂时不支持该语种查询
                    pdo_insert('train_qa_history', ['pid' => $question_id, 'cid' => 0, 'ip' => $ip, 'uid' => $user['id'], 'text' => '暂时不支持该语种查询', 'createtime' => time()]);
                    echo json_encode(['code' => 0, 'msg' => '暂时不支持该语种查询'], true);
                    exit;
                }
                else {
                    #发起智能客服对话（队列服务）
                    $options = array('http' => array('timeout' => 75000));
                    $context = stream_context_create($options);
                    $res = file_get_contents('https://decl.gogo198.cn/api/v2/intelligent_chat?question_id=' . $question_id, false, $context);
                    if ($res) {
                        #获取等待时间平均值
                        $qa_answer_info = pdo_fetchall('select answertime from ' . tablename('train_qa_history') . ' where pid<>0 and answertime>0');

                        $min_sec = '智能客服正在思考中';
                        if (!empty($qa_answer_info)) {
                            $answertime = 0;
                            foreach ($qa_answer_info as $k => $v) {
                                $answertime += $v['answertime'];
                            }

                            $answertime = sprintf('%.2f', $answertime / count($qa_answer_info));
                            // 计算分钟数
                            $minutes = floor($answertime / 60);
                            // 计算剩余的秒数
                            $remainingSeconds = $answertime % 60;
                            $min_sec .= '，需等待大约' . $minutes . '分' . $remainingSeconds . '秒';
                        }
//                $res = json_decode($res,true);
                        echo json_encode(['code' => 0, 'msg' => $min_sec], true);
                        exit;
                    }
                }
            }
            #新版查询本地电脑大模型并携带问题ID和翻译后的语句======================END

            #旧版查询本地电脑大模型==============================================START
            if(1>2){
                #历史对话记录
                $res = pdo_insert('train_qa_history',['pid'=>0,'uid'=>$user['id'],'ip'=>$ip,'cid'=>0,'text'=>$content,'createtime'=>time()]);
                $question_id = pdo_insertid();
//            $question_id = 34;

                $options = array('http' => array('timeout' => 75000));
                $context = stream_context_create($options);
                $res = file_get_contents('https://decl.gogo198.cn/api/v2/intelligent_chat?question_id='.$question_id, false, $context);

                #用户提交后进入队列，队列会一个个执行，并告诉用户需要等待的时间
                if($res){
                    #获取等待时间平均值
                    $qa_answer_info = pdo_fetchall('select answertime from '.tablename('train_qa_history').' where pid<>0 and answertime>0');

                    $min_sec = '智能客服正在思考中';
                    if(!empty($qa_answer_info)){
                        $answertime = 0;
                        foreach($qa_answer_info as $k=>$v){
                            $answertime += $v['answertime'];
                        }

                        $answertime = sprintf('%.2f',$answertime / count($qa_answer_info));
                        // 计算分钟数
                        $minutes = floor($answertime / 60);
                        // 计算剩余的秒数
                        $remainingSeconds = $answertime % 60;
                        $min_sec .= '，需等待大约'.$minutes.'分'.$remainingSeconds.'秒';
                    }
//                $res = json_decode($res,true);
                    echo json_encode(['code'=>0,'msg'=>$min_sec],true);exit;
                }
            }
            #旧版查询本地电脑大模型==============================================END
        }
        elseif($dat['pa']==2){
            #获取历史消息，以日为数组

            $list = pdo_fetchall('select id,createtime from '.tablename('train_qa_history').' where uid=:uid and cid=:cid order by id asc',[':uid'=>$user['id'],':cid'=>0]);
            $pid = 0;

            $chat_group = [];
            $who_send = 2;
            if(!empty($list)){
                #聊天记录以时间为数组
                $group = [];
                foreach($list as $k=>$v){
                    $time = date('Y-m-d',$v['createtime']);
                    if(empty($group)){
                        $group = array_merge($group,[$time]);
                    }else{
                        if(!in_array($time,$group)){
                            $group = array_merge($group,[$time]);
                        }
                    }
                }
                sort($group);
                #根据时间查找聊天记录
                foreach($group as $k=>$v){
                    $starttime = strtotime($v.' 00:00:00');
                    $endtime = strtotime($v.' 23:59:59');
                    $chat_group[$k]['time'] = date('Y年m月d日',$starttime);

                    $chat_group[$k]['info'] = pdo_fetchall('select * from '.tablename('train_qa_history').' where uid=:uid and cid=:cid and ( createtime>=:starttime and createtime<=:endtime ) order by id asc',[':uid'=>$user['id'],':cid'=>0,':starttime'=>$starttime,':endtime'=>$endtime]);
                }
                #整理数组
                foreach($chat_group as $k=>$v){
                    foreach($v['info'] as $kk=>$vv){
                        if(!empty($vv['pid'])){
                            #智能客服
                            $chat_group[$k]['info'][$kk]['kefu_name'] = '客服';
                        }
                        $chat_group[$k]['info'][$kk]['createtime'] = date('H:i',$vv['createtime']);
                    }
                }
            }
            echo json_encode(['code'=>0,'data'=>$chat_group,'pid'=>$pid],true);exit;
        }
    }
    else{
        include $this->template('customer/chat');
    }
}