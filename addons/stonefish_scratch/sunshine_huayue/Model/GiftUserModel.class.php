<?php

/*
用户礼物对照表
*/

class GiftUserModel {

	static function pushItem($openid,$gift_id,$gift_num) {
		global $_W;

		$r= pdo_fetch("select * from ".tablename('sunshine_huayue_gift_user')." where uniacid='{$_W['uniacid']}' and openid = '$openid' and gift_id='$gift_id'");
		if($r) {
			$r = pdo_query("update ".tablename('sunshine_huayue_gift_user')." set gift_num = gift_num+$gift_num  where uniacid='{$_W['uniacid']}' and openid = '$openid' and gift_id='$gift_id'");
			if(!$r) {
				return array('res'=>'101','msg'=>'更新表失败，更新gift_num失败');
			}
		}else {

			$data = array();
			$data['uniacid'] = $_W['uniacid'];
			$data['openid'] = $openid;
			$data['gift_id'] = $gift_id;
			$data['gift_num'] = $gift_num;
			$data['add_time'] = date("Y-m-d H:i:s");

			$r = pdo_insert("sunshine_huayue_gift_user",$data);
			if(!$r) {
				return array('res'=>'101','msg'=>'插入表失败');	
			}
		}
		
		return true;
	}

	static function getListByOpenid($openid) {
		global $_W;

		return pdo_fetchall("select * from ".tablename('sunshine_huayue_gift_user')." where uniacid='{$_W['uniacid']}' and openid='$openid' and gift_num>0 order by add_time desc");

	}

	static function getInfo($id,$openid) {
		global $_W;
		$r= pdo_fetch("select * from ".tablename('sunshine_huayue_gift_user')." where uniacid='{$_W['uniacid']}' and openid = '$openid' and id='$id'");
		return $r;
	}

	static function presentItem($openid,$gift_id) {
		global $_W;

		$r= pdo_fetch("select * from ".tablename('sunshine_huayue_gift_user')." where uniacid='{$_W['uniacid']}' and openid = '$openid' and gift_id='$gift_id' for update ");
		if($r) {
			$r = pdo_query("update ".tablename('sunshine_huayue_gift_user')." set gift_num = gift_num-1 where uniacid='{$_W['uniacid']}' and openid = '$openid' and gift_id='$gift_id'");
			if(!$r) {
				return array('res'=>'101','msg'=>'更新表失败，更新gift_num失败');
			}
			return true;
		}else {
			return array('res'=>'101','msg'=>'锁定记录失败');
		}
	}
}