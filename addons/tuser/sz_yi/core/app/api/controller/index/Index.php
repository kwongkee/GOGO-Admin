<?php
// Ä£¿éLTDÌá¹©
namespace app\api\controller\index;

class Index extends \app\api\YZ
{
	public function getGoodsList()
	{
		$goodsid = \app\api\Request::input('goodsid');
		$keywords = \app\api\Request::input('keywords', '');
		$args = array('page' => 1, 'pagesize' => 10, 'goodsid' => $goodsid, 'keywords' => $keywords, 'isrecommand' => 1, 'order' => 'displayorder desc,id desc', 'by' => '');
		$goods = m('goods')->getList($args);

		foreach ($goods as &$good) {
			$good = array_part('id,thumb,title,marketprice,type,groupnumber,productprice,productprice', $good);
		}

		$this->returnSuccess($goods);
	}
}

?>
