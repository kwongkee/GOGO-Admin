<?php
/*
在线人数统计组件

需要用户定时去请求统计链接，频率不能少于cookie有效期
实际上针对该模块，每个页面都有定时请求的ajax，所以不需要考虑该问题了

工作原理：

1、set一个有效期cookie ，并且刷新统计log中的cookie有效期
2、如果log中cookie不存在，那么add
3、同时计算对应pid的log中所有的cookie的有效期是否超时，如果超时，那么剔除超时的数据，重新统计数量。

以上三步在同一个请求中完成

日志文件的格式按照   value:expire|value:expire存储（不采用）
或者按照 序列化后的二维数组存储（采用）
*/
class OnlineComponent {


	public $pid; // 针对某个页面的pid描述符进行统计
	public $uid; // uid用户唯一描述符
	public $file;
	public $path;
	public $fhandle; // 打开文件读写的句柄
	public $filesize;

	/*
	初始化组件id
	*/
	public function __construct($pid,$uid) {
		global $_W;
		$this->pid = $pid;
		$this->uid = $uid;
		// 日志文件路径
		$this->path = MODULE_ROOT.'/config/'.$_W['uniacid'];
		// 创建目录
		if(!is_dir($this->path)) {
			mkdir($this->path);
		}
		$this->file = $this->path.'/'.$pid.'.log';
		// 文件长度
		$this->filesize = filesize($this->file);
		// 创建文件
		$this->fhandle = fopen($this->file,'w+');
	}

	/*
	请求
	*/
	public function request() {

		// 播种cookie  60s有效期，如果超出60s自动下线
		$expire = time() + 60;
		// 实际上这里cookie没有起到作用，但是还是种上吧
		setcookie('sunshine_online_'.$this->pid,$this->uid,$expire);
		// 读取数据
		$onlineArr = $this->readLog();
		// 重置当前用户的超时时间记录
		$onlineArr[$this->uid] = $expire;
		// 保存
		$r = $this->writeLog(serialize($onlineArr));

		// 返回当前统计到的人数
		return count($onlineArr);
	}

	/*
	获取pid_log文件中的数据
	*/
	public function readLog() {
		// 全部读取
		$content = fread($this->fhandle,$this->filesize || 1000);
		// 如果没有读取到数据，返回空数组
		if(!$content) {
			return array();
		}
		// 解析
		$arr = unserialize($content);
		// 删除所有超时数据
		foreach($arr as $k=>$v) {
			if($v['expire'] < time()) {
				unset($arr[$k]);
			}
		}

		return $arr;
	}

	/*
	重写数据
	*/
	public function writeLog($content) {
		return fwrite($this->fhandle, $content);
	}

	/*
	释放文件句柄
	*/
	public function __destruct() {
		fclose($this->fhandle);
	}

}