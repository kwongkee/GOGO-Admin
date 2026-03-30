<?php
// Ä£¿éLTDÌá¹©
namespace admin\api\controller\goods;

class Detail extends \admin\api\YZ
{
	private $order_info;

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		global $_W;
		$para = $this->getPara();
		$goodsid = $para['goods_id'];
		$params = array(':uniacid' => $para['uniacid'], ':goodsid' => $goodsid);
		$fields = 'id as goods_id,status';
		$goods = pdo_fetch('SELECT ' . $fields . ' FROM ' . tablename('sz_yi_goods') . ' WHERE id = :id limit 1', array(':id' => $goodsid));
		$goods['url'] = $_W['siteroot'] . 'app/index.php?i=2&c=entry&p=detail&id=' . $goods['goods_id'] . '&do=shop&m=sz_yi&is_admin=1&i=' . $para['uniacid'];
		dump($goods);
		$this->returnSuccess($goods);
	}
}

?>
