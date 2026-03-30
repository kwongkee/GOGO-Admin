<?php
// Ä£¿éLTDÌá¹©
namespace admin\api\controller\statistics;

class Sale extends \admin\api\YZ
{
	private $member_model;

	public function __construct()
	{
		parent::__construct();
		$this->member_model = new \admin\api\model\member();
	}

	public function index()
	{
		global $_W;
		$_obf_DQ8mJSQeBRknOwgeHA8FMiERBgwPNRE_ = strtotime(date('Y-m-d', time()));
		$sale['all'] = $this->getSaleData('sum(price)', array(':uniacid' => $_W['uniacid']));
		$sale['month'] = $this->getSaleData('sum(price)', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime('-1 month', $_obf_DQ8mJSQeBRknOwgeHA8FMiERBgwPNRE_), ':endtime' => time()));
		$count['yesterday_order'] = $this->getSaleData('count(*)', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime('-1 day', $_obf_DQ8mJSQeBRknOwgeHA8FMiERBgwPNRE_), ':endtime' => $_obf_DQ8mJSQeBRknOwgeHA8FMiERBgwPNRE_));
		$count['new_member'] = $this->member_model->getCount(array('uniacid' => $_W['uniacid'], 'createtime' => $_obf_DQ8mJSQeBRknOwgeHA8FMiERBgwPNRE_));
		$count['week_order'] = $this->getSaleData('count(*)', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime('-1 week', $_obf_DQ8mJSQeBRknOwgeHA8FMiERBgwPNRE_), ':endtime' => time()));
		$_obf_DQkvDxsTGlwuHCgSLSUYFRIpPAw0JhE_ = array('sale' => $sale, 'count' => $count);
		$this->returnSuccess($_obf_DQkvDxsTGlwuHCgSLSUYFRIpPAw0JhE_);
	}

	private function getSaleData($countfield, $map = array())
	{
		$condition = '1';

		if (isset($map[':uniacid'])) {
			$condition .= ' AND uniacid=:uniacid';
		}

		if (isset($map[':starttime'])) {
			$condition .= ' AND createtime >=:starttime';
		}

		if (isset($map[':endtime'])) {
			$condition .= ' AND createtime <=:endtime';
		}

		return pdo_fetchcolumn('SELECT ifnull(' . $countfield . ',0) as cnt FROM ' . tablename('sz_yi_order') . ' WHERE ' . $condition . ' AND status>=1 ', $map);
	}
}

?>
