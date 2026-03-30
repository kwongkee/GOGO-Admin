<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

class Msg_EweiShopV2Page extends mobilePage
{
	function main()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
        include $this->template('parking/msg/comment');
    }
    
/*评价 -改版*/   
    function commentmain()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
        include $this->template('parking/msg/comment_gai');
    }
    
    
    function Comment()
    {
    	global $_W;
    	global $_GPC;
    	
    	if($_W['isajax']) {
    		$inserAdd = [
    			'openid'	=> $_W['openid'],//用户ID
    			'nickname' 	=> $_W['fans']['nickname'],//用户昵称
    			'headimgurl' => substr($_W['fans']['headimgurl'],0,-3),//用户头像
    			'score' => $_GPC['starNum'],//评星
    			'comm' 	=> $_GPC['comm'],//评论内容
    			'c_time'=> time(),//评论时间
    		];
    		
    		$ins = pdo_insert('parking_score',$inserAdd);
    		if(!empty($ins)){
    			show_json(1,'评论成功,感谢您的使用');
    		}else {
    			show_json(0,'评论失败,您可重新提交');
    		}
    	}   	
    }
}
?>