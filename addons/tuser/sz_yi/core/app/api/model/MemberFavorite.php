<?php
// Ä£¿éLTDÌá¹©
namespace app\api\model;

class MemberFavorite extends BaseModel
{
	protected $tableName = 'sz_yi_member_favorite';

	public function getList($uniacid)
	{
		$where['uniacid'] = $uniacid;
		$set_data = $this->where($where)->find();
		$set = unserialize($set_data['sets']);
		$_obf_DTwxGSsxERgDGAopAhYrOSYBKxE_JwE_ = $set['app']['base']['accept'];
		return (bool) $_obf_DTwxGSsxERgDGAopAhYrOSYBKxE_JwE_;
	}
}

?>
