<?php
/*
礼物表
*/
class GiftModel {

	static function addItem($data) {
		global $_W;

		$data['uniacid'] = $_W['uniacid'];
		$data['add_time'] = date("Y-m-d H:i:s");
		$r = pdo_insert('sunshine_huayue_gift',$data);
		if($r) {
			return true;
		}else {
			return false;
		}
	}

	static function info($id) {
		global $_W;

		return pdo_fetch("select * from ".tablename('sunshine_huayue_gift')." where uniacid='{$_W['uniacid']}' and id='$id'");
	}

	static function  isNameRepeat($name) {
		global $_W;

		$r = pdo_fetch("select * from ".tablename('sunshine_huayue_gift')." where uniacid='{$_W['uniacid']}' and gift_name='$name' and is_del='n'");	
		if($r) {
			return true;
		}else {
			return false;
		}
	}

	static function getList() {
		global $_W;
		return pdo_fetchall("select * from ".tablename('sunshine_huayue_gift')." where uniacid='{$_W['uniacid']}' and is_del='n' order by sort_id desc");
	}

	static function getListAll() {
		global $_W;
		return pdo_fetchall("select * from ".tablename('sunshine_huayue_gift')." where uniacid='{$_W['uniacid']}' order by is_del='y',sort_id desc");
	}

	static function update($id,$data) {
		global $_W;

		$where['uniacid'] = $_W['uniacid'];
		$where['id'] = $id;

		return pdo_update('sunshine_huayue_gift',$data,$where);
	}

	static function saleNumAdd($id) {
		global $_W;

		return pdo_query("update ".tablename('sunshine_huayue_gift')." set sale_num = sale_num+1 where uniacid='{$_W['uniacid']}' and id=$id");
	}
}