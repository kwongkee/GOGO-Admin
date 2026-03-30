<?php
	namespace app\home\controller;
	use think\Controller;
	class Appointment extends Controller
	{
		//预约设置
		public function set()
		{
			return $this->fetch('appoin/setlist');
		}
		
		public function postSet() {
			
			$data = [
				'times'=>input('times'),
				'checkType'=>input('checkType'),
				'limit'=>input('limit'),
				'workingStart'=>input('workingStart'),
				'workingEnd'=>input('workingEnd'),
				'holidayStart'=>input('holidayStart'),
				'holidayEnd'=>input('holidayEnd'),
			];
			echo json_encode($data);
		}
		
		public function lists()
		{
			echo '预约列表';
		}		
	}
?>