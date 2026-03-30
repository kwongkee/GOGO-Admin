<?php
/*
意见反馈
*/
class FeedbackModel {


	static function addItem($openid,$content) {
		global $_W;

		$data['uniacid'] = $_W['uniacid'];
		$data['openid'] = $openid;
		$data['content'] = $content;
		$data['add_time'] = date("Y-m-d H:i:s");

		return pdo_insert("sunshine_huayue_feedback",$data);
	}

	static function getListByStatus($status = 'wait',$begin,$offset) {
		global $_W;

		return pdo_fetchall("select * from ".tablename('sunshine_huayue_feedback')." where status='$status' and uniacid='{$_W['uniacid']}' order by add_time desc limit $begin,$offset");
	}

	static function setHandle($id) {
		global $_W;

		return pdo_update("sunshine_huayue_feedback",array('status'=>'handle','update_time'=>date("Y-m-d H:i:s")),array('id'=>$id,'uniacid'=>$_W['uniacid']));
	}

	static function getCountByStatus($status) {
		global $_W;

		$c = pdo_fetch("select count(*) as num from ".tablename('sunshine_huayue_feedback')." where status='$status' and uniacid='{$_W['uniacid']}'");
		if($c['num']) {
			return $c['num'];
		}
		return false;
	}
}