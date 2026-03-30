<?php
// Ä£¿éLTDÌá¹©
namespace app\api\model;

class Member extends BaseModel
{
	protected $tableName = 'sz_yi_member';

	public function has($para)
	{
		$count = $this->where($para + array('pwd!=""'))->count();
		return (bool) $count;
	}

	public function where($where, $parse = NULL)
	{
		if (isset($where['pwd'])) {
			$where['pwd'] = md5($where['pwd']);
		}

		return parent::where($where, $parse);
	}
}

?>
