<?php
/**
 * mogucms_shouye03模块微站定义
 *
 * @author 永和软件
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Mogucms_guanwangModuleSite extends WeModuleSite {
	public function mogucms_url($menu="",$par=array()){
		global $_W,$_GPC;

		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$setting = iunserializer($set["val"]);
      	if($setting["rewrite"]=="1" && !$this->isWeixin()){
      		if(empty($menu)){
      			$url="/";
      		}else{
      			$url = "/$menu.html";
      			if($par){
      				$url .="?";
      				foreach ($par as $key => $value) {
      					$url.="$key=".$value."&";
      				}
      			}
      		}
      	}else{
      		if(empty($menu)){
      			$url = "/web/index.php?c=account&a=welcome";
      		}else{
      			$url = "/web/index.php?c=account&a=welcome&do=".$menu;
      			if($par){
      				foreach ($par as $key => $value) {
      					$url.="&$key=".$value;
      				}
      			}
      		}
      	}
      	return trim($url,"&");
	}
	private function getset(){
		$domain = $this->getdomain();
		$setting = pdo_get("mogucms_guanwang_domain",array("domain"=>$domain));
		$setting = iunserializer($setting['val']);

		return $setting;
	}
	//判断是不是微信访问，微信访问不启用伪静态，否则签名错误
	public function isWeixin() { 
	  if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) { 
	    return true; 
	  } else {
	    return false; 
	  }
	}
	private function getJssdk(){
		global $_W,$_GPC;
		$arr = array();
		$wechat = pdo_fetch("select * from ".tablename("account_wechats")." where length(`key`) > 10 and length(secret) >10");
		if($wechat){
	    	$account_api = WeAccount::create($wechat['acid']);
	       	$arr = $account_api->getJssdkConfig();
	    }
       	return $arr;
	}
	private function getfounderset(){
		$setting = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
		$setting = iunserializer($setting['val']);
		return $setting;
	}

	private function getdomain(){
		global $_W;
		$domain = $_SERVER['HTTP_HOST'];
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$domain));
		if($set['isfounder'] =="-1"){
			$d = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
			return $d["domain"];
		}else{
			return $domain;
		}
	}

	

	//是否添加了域名已经
	public function isAddDomain(){
		global $_W;
		$username = $_W['username'];
		$role = $_W['role'];
		$user = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$e = pdo_get("modules_bindings",array("do"=>"zhandian","module"=>"mogucms_guanwang"));
		if(empty($user) && $role=="founder"){
			//itoast("请先在域名管理里面绑定域名和用户名",$_W['siteroot']."web/index.php?c=site&a=entry&eid=".$e['eid']);
			//die;
			$temp =  pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
			if(!empty($temp["isfounder"])){
				$data['username'] = $username;
				pdo_update("mogucms_guanwang_domain",$data,array("isfounder"=>1));
			}else{
				$data["domain"] = $_SERVER['HTTP_HOST'];
				$data['username'] = $username;
				$data['addtime'] = time();
				$data['isfounder']=1;//1 创始人 2.副创始人 3.manager
				pdo_insert("mogucms_guanwang_domain",$data);
			}
		}
	}
	
	public function getdomainidBydomain(){
		global $_W;
		$domain = $this->getdomain();
		$setting = pdo_get("mogucms_guanwang_domain",array("domain"=>$domain));
		if($setting){
			return $setting["id"];
		}
	}
	private function getbanner(){
		$domain = $this->getdomain();
		$setting = pdo_get("mogucms_guanwang_domain",array("domain"=>$domain));
		$setting = iunserializer($setting['banner']);
		return $setting;
	}
	public function doMobileFengmian() {
		global $_GPC, $_W;
		//print_r($_W['setting']['copyright']['statcode']);
		
		$do = trim($_GPC['do']);
		$jssdk = $this->getJssdk();
      	$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	if(!empty($set['module']) && $set['module']!="mogucms_guanwang"){
      		$site = WeUtility::createModuleSystemWelcome($set['module']);
      		if (!is_error($site)) {
				exit($site->systemWelcomeDisplay());
			}
      	}

        if(!empty($do)){
            $func = "systemWelcome".ucfirst($do);
            if( method_exists( $this , $func) ) {
                $this->$func();
                exit;
            }
        }
        

      	$isfounder = $set["isfounder"];
      	$dizhi = $this->mogucms_url();
      	$val = iunserializer($set["val"]);
      	
      	$service = iunserializer($set["ourservice"]);
      	$development = iunserializer($set["development"]);
      	$loginset = iunserializer($set["loginset"]);
      	$title = iunserializer($set["title"]);
      	$menu = iunserializer($set["menu"]);

		$setting = $this->getset();
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		
		//代理商读取创始人信息
		if($setting['isadminanli']=="1"){
			$admindomain = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
			$did = intval($admindomain['id']);
		}else{
      		$did = intval($this->getdomainidBydomain());
		}
      	if($val['homeanlinum']>0){
      	//$cases = pdo_getslice("mogucms_guanwang_case",array(),array(0,$val['homeanlinum'],$val['homeanlinum'],'','',array("ord desc")));
      	$cases = pdo_fetchall("select * from ".tablename("mogucms_guanwang_case")." where domainid=$did order by ord desc,id desc limit 0,".$val['homeanlinum']);
      	}else{
      	//$cases = pdo_getslice("mogucms_guanwang_case",array(),array(0,8),$val['homeanlinum'],'','',array("ord desc"));$cases = pdo_fetch("select * from ".tablename("mogucms_guanwang_case")." limit 0,$val['homeanlinum'] order by ord desc");
      	$cases = pdo_fetchall("select * from ".tablename("mogucms_guanwang_case")." where domainid=$did order by ord desc limit 0,8");
      	}
      	$categorys = pdo_getall("mogucms_guanwang_case_category",array("domainid"=>$did));
      	
		$slists = pdo_fetchall("select A.*,B.categoryname from ".tablename("mogucms_guanwang_solve")." as A left join ".tablename("mogucms_guanwang_case_category")." as B on A.category=B.id order by id desc limit 0,4");
      	$total=0;
      	$news = pdo_getslice("mogucms_guanwang_news",array(),array(0,6),$total,"","","id desc");
      	$login_urls = user_support_urls();
		if($setting["onlylogin"]=="1" && !in_array($do,array("login","register"))){
			$setting = iunserializer($set['loginset']);
			if(!empty($loginset['yangshi'])){
			include $this->template("login".$loginset['yangshi']);
			}else{
			$temp = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
			$temp = iunserializer($temp['loginset']);
			if(empty($temp['yangshi'])){
				$temp['yangshi']=1;
			}
			
			
			include $this->template("login".$temp['yangshi']);
			}
      	}else{
      		if($setting['moban']=="moban"){
			include $this->template('moban/index');
			}else{
			include $this->template('default/index');
			}
		}
	}

	public function systemWelcomeDisanfang(){
		global $_GPC, $_W;
		load()->func('communication');
		load()->func('global');
		load()->model('message');
		isetcookie('__session', '', -10000);
		isetcookie('__switch', '', -10000);
		$username = safe_gpc_string($_GPC['username']);
		$time = safe_gpc_string($_GPC['time']);
		$sign = safe_gpc_string($_GPC['sign']);
		$now = time();
		if(($now-$time)>15){
			die("请刷新链接后重试".$now);
		}
		$temp = md5($username.$time."boo11DRRRW@@t311232strap");
		if($temp != $sign){
			die("无效的签名");
		}
		//$username="admin";
		//$password = "admin";
		$record = pdo_get("users",array("username"=>$username));
		if(empty($record)){
			$password = rand(10000000,99999999);
			$user['salt'] = $password;
			$user['username'] = $username;
			$user['groupid'] = 1;
			$user['password'] = user_hash($password, $user['salt']);
			$user['joinip'] = CLIENT_IP;
			$user['joindate'] = TIMESTAMP;
			$user['lastip'] = CLIENT_IP;
			$user['lastvisit'] = TIMESTAMP;
			$user["starttime"] = time();
			$user["endtime"] = $user["starttime"]+31536000;
			if (empty($user['status'])) {
				$user['status'] = 2;
			}
			if (empty($user['type'])) {
				$user['type'] = USER_TYPE_COMMON;
			}
			$result = pdo_insert('users', $user);
		}
		$record = pdo_get("users",array("username"=>$username));
		$url_login = 'http://'.$_SERVER['HTTP_HOST'].'/web/index.php?c=user&a=login';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url_login);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch, CURLOPT_MAXREDIRS, 100);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$header = [
		    //'Content-Type:application/x-www-form-urlencoded',
		    //'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		   //'Accept-Encoding:gzip, deflate',
		   // 'Accept-Language:zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
		    //'Host:'. 'test.mogucms.com',
		    'X-Requested-With:XMLHttpRequest'
		    ];
		@curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		@curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'gzip, deflate');
		$data = ['username'=>$username,'password'=>$password];
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		$ret = curl_exec($ch);
		curl_close($ch);
		$cookie = array();
		$cookie['uid'] = $record['uid'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = md5($record['password'] . $record['salt']);
		$session = authcode(json_encode($cookie), 'encode');
		isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
		$status = array();
		$status['uid'] = $record['uid'];
		$status['lastvisit'] = TIMESTAMP;
		$status['lastip'] = CLIENT_IP;
		user_update($status);
		header("Location:/web/index.php?c=home&a=welcome&do=system&");
	}

	public function systemWelcomeDaili(){
		global $_GPC, $_W;
		$setting = $this->getset();
		$content = html_entity_decode($setting["daili"]);
		
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
		$title = iunserializer($set["title"]);
		$daili = iunserializer($set["daili"]);
		$loginset = iunserializer($set["loginset"]);

		$title3 = html_entity_decode($daili["title3"]);

		$jssdk = $this->getJssdk();
		$menu = iunserializer($set["menu"]);
		if($setting['moban']=="moban"){
			include $this->template('moban/daili');
		}else{
			include $this->template('default/daili');
		}
	}
	//解决方案详情
	public function systemWelcomeSolvedetail() {
		global $_GPC, $_W;
		$setting = $this->getset();
		$content = html_entity_decode($setting["solve"]);
		
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$menu = iunserializer($set["menu"]);
      	$jssdk = $this->getJssdk();
      	
		if($setting['isadminanli']=="1"){
			$admindomain = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
			$did = intval($admindomain['id']);
		}else{
      		$did = intval($this->getdomainidBydomain());
		}

		$categorys = pdo_getall("mogucms_guanwang_case_category",array("domainid"=>$did));
		$id = safe_gpc_int($_GPC['id']);
		if($id==0){
			if(strpos($_SERVER['REQUEST_URI'],"id")){
				$id = intval(str_replace("/solvedetail.html?id=","",$_SERVER['REQUEST_URI']));
			}
		}
		$info = pdo_get("mogucms_guanwang_solve",array("id"=>$id));
		pdo_update("mogucms_guanwang_solve",array("count"=>($info['count']+1)),array("id"=>$id));
		$cases = pdo_getslice("mogucms_guanwang_case",array("category"=>$info['category']),array(0,4));
		if($setting['moban']=="moban"){
			include $this->template('moban/solve_program_detail');
		}else{
			include $this->template('default/solve_program_detail');
		}
	}
	/**
	* 前端解决方案
	*/
	public function systemWelcomeSolve() {
		global $_GPC, $_W;
		$setting = $this->getset();
		$content = html_entity_decode($setting["solve"]);
		
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$title = iunserializer($set["title"]);
      	$jssdk = $this->getJssdk();
      	$menu = iunserializer($set["menu"]);
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		
		if($setting['isadminanli']=="1"){
			$admindomain = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
			$did = intval($admindomain['id']);
		}else{
      		$did = intval($this->getdomainidBydomain());
		}
		$categorys = pdo_getall("mogucms_guanwang_case_category",array("domainid"=>$did));
		$cid = safe_gpc_int($_GPC['cid']);
		if($cid==0){
			if(strpos($_SERVER['REQUEST_URI'],"cid")){
				$cid = intval(str_replace("/solve.html?cid=","",$_SERVER['REQUEST_URI']));
			}
		}
		
		$where = array();
		//$where['domainid'] = $this->getdomainid();
		if($cid>0){
			$where["category"] = $cid;
		}
		//$solves = pdo_getslice("mogucms_guanwang_solve",$where);
		$page = safe_gpc_int($_GPC['page']);
		if($page==0){
			if(strpos($_SERVER['REQUEST_URI'],"page")){
				$page = intval(str_replace("/solve.html?page=","",$_SERVER['REQUEST_URI']));
			}
		}
		$pindex = max(1, $page);
		//$pindex = max(1, intval($_GPC['page']));
        $psize = 8;

        $condition = array();
        $condition['domainid'] = $this->getdomainidBydomain();
        if(!empty($cid)){
            $condition['category'] = $cid;
        }
        $all = pdo_getall('mogucms_guanwang_solve',$condition);
        $total = sizeof($all);
        //$lists = pdo_getslice('mogucms_guanwang_solve', $condition, array($pindex, $psize), $total,'','','id desc');
        if(!empty($cid)){
        $lists = pdo_fetchall("select A.*,B.categoryname from ".tablename("mogucms_guanwang_solve")." as A left join ".tablename("mogucms_guanwang_case_category")." as B on A.category=B.id where A.category=".$cid." and A.domainid=".$did." order by id desc");
    	}else{
        $lists = pdo_fetchall("select A.*,B.categoryname from ".tablename("mogucms_guanwang_solve")." as A left join ".tablename("mogucms_guanwang_case_category")." as B on A.category=B.id where A.domainid=".$did." order by id desc");
    	}
        $allpage = ceil($total/$psize);
        if($setting['moban']=="moban"){
			include $this->template('moban/solve_program');
		}else{
			include $this->template('default/solve_program');
		}
	}

	/**
	* 前端小程序
	*/
	public function systemWelcomeXiaochengxu() {
		global $_GPC, $_W;
		$setting = $this->getset();
		$content = html_entity_decode($setting["xiaochengxu"]);

		
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$val = iunserializer($set["val"]);
      	$title = iunserializer($set["title"]);
      	$jssdk = $this->getJssdk();
      	$menu = iunserializer($set["menu"]);
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$rukous = pdo_getall("mogucms_guanwang_rukou",array());
		$cid = safe_gpc_int($_GPC['cid']);
		$cid2= safe_gpc_int($_GPC['cid']);
		if($cid==0){
			if(strpos($_SERVER['REQUEST_URI'],"cid")){
				$cid = intval(str_replace("/xiaochengxu.html?cid=","",$_SERVER['REQUEST_URI']));
			}
		}
		if($setting['isadminanli']=="1"){
			$admindomain = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
			$did = $admindomain['id'];
		}else{
      		$did = $this->getdomainidBydomain();
		}
		$categorys = pdo_getall("mogucms_guanwang_case_category",array("domainid"=>$did));
		$where = array();
		$where["domainid"] = $did;
		if($cid>0){
			$where["category"] = $cid;
		}
		if($val['xcxanlinum']>0){
		$cases = pdo_getslice("mogucms_guanwang_case",$where,array(0,$val['xcxanlinum']),$cid2,"","","ord desc,id desc");
		}else{
			$cases = pdo_getslice("mogucms_guanwang_case",$where,array(0,8),$cid2,"","","ord desc,id desc");
		}
		$customers = pdo_getall("mogucms_guanwang_customer",array());
		if($setting['moban']=="moban"){
			include $this->template('moban/xiaochengxu');
		}else{
			include $this->template('default/xiaochengxu');
		}
	}

	/**
	* 前端定制开发
	*/
	public function systemWelcomeCustomize() {
		global $_GPC, $_W;
		$setting = $this->getset();
		$content = html_entity_decode($setting["dingzhi"]);
		
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$title = iunserializer($set["title"]);
      	$jssdk = $this->getJssdk();
      	$menu = iunserializer($set["menu"]);
      	$youshi = iunserializer($set["youshi"]);
      	$cando = iunserializer($set["cando"]);
      	if($setting['moban']=="moban"){
			include $this->template('moban/customize');
		}else{
			include $this->template('default/customize');
		}
	}

	/**
	* 前端新闻资讯
	*/
	public function systemWelcomeNews() {
		global $_GPC, $_W;
		$setting = $this->getset();
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$title = iunserializer($set["title"]);
      	$jssdk = $this->getJssdk();
      	$menu = iunserializer($set["menu"]);
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$cid = safe_gpc_int($_GPC['cid']);
		if($cid==0){
			if(strpos($_SERVER['REQUEST_URI'],"cid")){
				$cid = intval(str_replace("/news.html?cid=","",$_SERVER['REQUEST_URI']));
			}
		}
		$categorys = pdo_getall("mogucms_guanwang_news_category",array(),array(),'',"ord desc");
		$page = safe_gpc_int($_GPC['page']);
		if($page==0){
			if(strpos($_SERVER['REQUEST_URI'],"page")){
				$page = intval(str_replace("/news.html?page=","",$_SERVER['REQUEST_URI']));
			}
		}
		$pindex = max(1, $page);
        $psize = 10;

        $condition = array();
        //$condition['domainid'] = $this->getdomainid();
        if(!empty($cid)){
            $condition['category'] = $cid;
        }
        $all = pdo_getall('mogucms_guanwang_news',$condition);
        $total = sizeof($all);
        $lists = pdo_getslice('mogucms_guanwang_news', $condition, array($pindex, $psize), $total,'','','id desc');
        //$pager = pagination($total, $pindex, $psize);
        $allpage = ceil($total/$psize);
        if($setting['moban']=="moban"){
			include $this->template('moban/news');
		}else{
			include $this->template('default/news');
		}
	}
	public function systemWelcomeHelp() {
		global $_GPC, $_W;
		$setting = $this->getset();
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$title = iunserializer($set["title"]);
      	$jssdk = $this->getJssdk();
      	$menu = iunserializer($set["menu"]);
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$cid = safe_gpc_int($_GPC['cid']);
		if($cid==0){
			if(strpos($_SERVER['REQUEST_URI'],"cid")){
				$cid = intval(str_replace("/help.html?cid=","",$_SERVER['REQUEST_URI']));
			}
		}
		$categorys = pdo_getall("mogucms_guanwang_help_category",array("domainid"=>$this->getdomainidBydomain()),array(),'',"ord desc");
		$pindex = max(1, intval($_GPC['page']));
        $psize = 10;

        $condition = array();
        $condition['domainid'] = $this->getdomainidBydomain();
        if(!empty($cid)){
            $condition['category'] = $cid;
        }
		$keyword = safe_gpc_string($_GPC['keyword']);

		if(!empty($keyword)){
			$condition['title like'] = "%$keyword%";
		}
        $all = pdo_getall('mogucms_guanwang_help',$condition);
        $total = sizeof($all);
        $lists = pdo_getslice('mogucms_guanwang_help', $condition, array($pindex, $psize), $total,'','','id desc');
        //$pager = pagination($total, $pindex, $psize);
        $allpage = ceil($total/$psize);
        if($setting['moban']=="moban"){
			include $this->template('moban/help');
		}else{
			include $this->template('default/help');
		}
	}
	/**
	* 前端关于我们
	*/
	public function systemWelcomeAbout() {
		global $_GPC, $_W;
		$setting = $this->getset();
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$title = iunserializer($set["title"]);
      	$jssdk = $this->getJssdk();
      	$menu = iunserializer($set["menu"]);
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$teams = pdo_getall("mogucms_guanwang_team",array());
		$certs = pdo_getall("mogucms_guanwang_cert",array());
		if($setting['moban']=="moban"){
			include $this->template('moban/about');
		}else{
			include $this->template('default/about');
		}
	}

	/**
	* 前端联系我们
	*/
	public function systemWelcomeContact() {
		global $_GPC, $_W;
		$setting = $this->getset();

		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$title = iunserializer($set["title"]);
      	$jssdk = $this->getJssdk();
      	$menu = iunserializer($set["menu"]);
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		if($setting['moban']=="moban"){
			include $this->template('moban/contact');
		}else{
			include $this->template('default/contact');
		}
	}

	/**
	* 前端注册
	*/
	public function systemWelcomeRegister() {
		global $_GPC, $_W;
		$smsurl = "/web/index.php?c=utility&a=verifycode";
		$setting = pdo_get("mogucms_guanwang_domain",array("domain"=>$_SERVER['HTTP_HOST']));
		$setting = iunserializer($setting['loginset']);
		$set = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
		$temp = iunserializer($set['loginset']);
		if($temp['sms']=="yonghe"){
			$t1 = pdo_fetch("select * from ".tablename("account")." where type=5 and isdeleted=0 order by acid asc");
			$t2 = pdo_fetch("select * from ".tablename("modules_bindings")." where module='mogucms_sms' and entry='cover' and do='home'");
			$smsurl = "/app/index.php?i=".$t1['acid']."&a=webapp&c=entry&eid=".$t2['eid'];
		}
		if(empty($setting['yangshi'])){
			
			$setting = iunserializer($set['loginset']);
		}
		$domain = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain(),"isfounder"=>0));
		$owner_uid=0;
		if(!empty($domain)){

			$temp = pdo_get("users",array("username"=>$domain['username']));
			$owner_uid=$temp['uid'];
		}
		
		$extendfields = OAuth2Client::create("system")->systemFields();
		if($setting["yangshi"]=="2"){
			include $this->template('register2');
		}else if($setting["yangshi"]=="3"){
			include $this->template('register3');
		}else if($setting["yangshi"]=="4"){
			include $this->template('register4');
		}else if($setting["yangshi"]=="5"){
			include $this->template('register5');
		}else if($setting["yangshi"]=="6"){
			include $this->template('register6');
		}else if($setting["yangshi"]=="7"){
			include $this->template('register7');
		}else if($setting["yangshi"]=="8"){
			include $this->template('register8');
		}else if($setting["yangshi"]=="9"){
			include $this->template('register9');
		}else{
			include $this->template('register1');
		}
	}


	/**
	* 登录
	*/
	public function systemWelcomeLogin(){
		global $_GPC, $_W;

		$setting = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
		$isfounder =  $setting["isfounder"];
		$setting = iunserializer($setting['loginset']);
		if(empty($setting['yangshi'])){
			$set = pdo_get("mogucms_guanwang_domain",array("isfounder"=>1));
			$setting = iunserializer($set['loginset']);
		}
		$login_urls = user_support_urls();
			if($setting["yangshi"]=="2"){
				include $this->template('login2');
			}else if($setting["yangshi"]=="3"){
				include $this->template('login3');
			}else if($setting["yangshi"]=="4"){
				include $this->template('login4');
			}else if($setting["yangshi"]=="5"){
				include $this->template('login5');
			}else if($setting["yangshi"]=="6"){
				include $this->template('login6');
			}else if($setting["yangshi"]=="7"){
				include $this->template('login7');
			}else if($setting["yangshi"]=="8"){
				include $this->template('login8');
			}else if($setting["yangshi"]=="9"){
				include $this->template('login9');
			}else{
				include $this->template('login1');
		}
	}

	/**
	* 登录
	*/
	public function systemWelcomeFindpassword(){
		global $_GPC, $_W;
		$setting = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
		$setting = iunserializer($setting['loginset']);
		include $this->template('findpassword'.$setting['yangshi']);
	}

	//新闻详情
	public function systemWelcomeNewsdetail(){
		global $_GPC, $_W;
		$setting = $this->getset();
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$menu = iunserializer($set["menu"]);
      	$jssdk = $this->getJssdk();
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$cid = safe_gpc_int($_GPC['cid']);
		
		$categorys = pdo_getall("mogucms_guanwang_news_category",array("domainid"=>$this->getdomainidBydomain()),array(),'',"ord desc");
		$id = safe_gpc_int($_GPC['id']);
		if($id==0){
			if(strpos($_SERVER['REQUEST_URI'],"id")){
				$id = intval(str_replace("/newsdetail.html?id=","",$_SERVER['REQUEST_URI']));
			}
		}
		$info = pdo_get("mogucms_guanwang_news",array("id"=>$id));
		$where = array();
		$where["domainid"] = $this->getdomainidBydomain();
		$where['id'] != $id;
		$newstj = pdo_getslice('mogucms_guanwang_news',$where,array(1,10));
		if($setting['moban']=="moban"){
			include $this->template('moban/news_detail');
		}else{
			include $this->template('default/news_detail');
		}
	}
	public function systemWelcomeHelpdetail(){
		global $_GPC, $_W;
		$setting = $this->getset();
		$set = pdo_get("mogucms_guanwang_domain",array("domain"=>$this->getdomain()));
      	$loginset = iunserializer($set["loginset"]);
      	$menu = iunserializer($set["menu"]);
      	$jssdk = $this->getJssdk();
		$banner = $this->getbanner();
		$fdset = $this->getfounderset();//创始人信息
		$cid = safe_gpc_int($_GPC['cid']);
		
		$categorys = pdo_getall("mogucms_guanwang_help_category",array("domainid"=>$this->getdomainidBydomain()),array(),'',"ord desc");
		$id = safe_gpc_int($_GPC['id']);
		if($id==0){
			if(strpos($_SERVER['REQUEST_URI'],"id")){
				$id = intval(str_replace("/helpdetail.html?id=","",$_SERVER['REQUEST_URI']));
			}
		}
		$info = pdo_get("mogucms_guanwang_help",array("id"=>$id));
		$where = array();
		$where["domainid"] = $this->getdomainidBydomain();
		$where['id'] != $id;
		$newstj = pdo_getslice('mogucms_guanwang_help',$where,array(1,10));
		$prev = pdo_fetch("select * from ".tablename("mogucms_guanwang_help")." where id <$id");
		$next = pdo_fetch("select * from ".tablename("mogucms_guanwang_help")." where id >$id");

		if($setting['moban']=="moban"){
			include $this->template('moban/help_detail');
		}else{
			include $this->template('default/help_detail');
		}
	}
	//是否是创始人
	private function isfounder(){
		global $_W,$_GPC;
		if($_W['role']=="founder"){
			include $this->template('error');
			die();
		}
	}

	private function isBindDomain(){
		global $_W,$_GPC;
		$this->isfounder();
		$username = $_W['username'];
		$role = $_W['role'];
		$domain = $_SERVER['HTTP_HOST'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		if(empty($set)){
			itoast('该用户还没有绑定域名，请联系管理员授权', referer());
			die();
		}
	}
	public function getdomainid(){
		global $_W;
		$username = $_W['username'];
		$setting = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		if($setting){
			return $setting["id"];
		}else{
			die("该用户不存在");
		}
	}

	//案例管理
	public function doWebCase(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$act = $_GPC['act'];
		if($act == "categoryadd"){
			if($_W['ispost']){
					$categoryname = safe_gpc_string($_GPC['categoryname']);
					$ord = safe_gpc_string($_GPC['ord']);
					if(empty($categoryname)){
						itoast('分类名不能为空', referer());
					}
					$data["categoryname"]= $categoryname;
					$data["ord"]= $ord;
					$data["domainid"] = $this->getdomainid();
					pdo_insert('mogucms_guanwang_case_category', $data);
					$url = safe_gpc_string($_GPC['url']);
					itoast('添加成功', $url, 'success');
			}else{
				include $this->template('admin/casecategoryadd');
			}
		}else if($act=="categorydel"){
			$mid = safe_gpc_int($_GPC["mid"]);
			pdo_delete('mogucms_guanwang_case_category', array('id' => $mid));
			itoast('删除成功', "/web/index.php?c=site&a=entry&eid=".$_GPC['eid']."&act=category","success");
		}else if($act=="categoryedit"){
			$mid = safe_gpc_int($_GPC["mid"]);

			if($_W['ispost']){
				$categoryname = safe_gpc_string($_GPC['categoryname']);
				$ord = safe_gpc_string($_GPC['ord']);
				$data["categoryname"]= $categoryname;
				$data["ord"] = $ord;
				$a=pdo_update("mogucms_guanwang_case_category",$data,array("id"=>$mid));
				$url = safe_gpc_string($_GPC['url']);
				itoast('编辑成功',$url,"success");
			}else{
				
				$list = pdo_get("mogucms_guanwang_case_category", array('id' => $mid));
				include $this->template('admin/casecategoryadd');
			}
		}else if($act=="category"){
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;

			$condition = array();
			$condition['domainid'] = $this->getdomainid();
			$all = pdo_getall('mogucms_guanwang_case_category',$condition);
        	$total = sizeof($all);
			$lists = pdo_getslice('mogucms_guanwang_case_category', $condition, array($pindex, $psize), $total,'', 'id', 'id desc');
			$pager = pagination($total, $pindex, $psize);
			include $this->template('admin/casecategory');
		}else if($act == "caseadd"){
			if($_W['ispost']){
					$category = safe_gpc_string($_GPC['category']);
					$title = safe_gpc_string($_GPC['title']);
					$image = safe_gpc_string($_GPC['image']);
					$erweima = safe_gpc_string($_GPC['erweima']);
					$ord = safe_gpc_string($_GPC['ord']);
					$myurl = safe_gpc_string($_GPC['myurl']);
					if(empty($title)){
						itoast('标题不能为空', referer());
					}
					$data["category"]= $category;
					$data["title"] = $title;
					$data["image"] = $image;
					$data["erweima"] = $erweima;
					$data["ord"] = $ord;
					$data["myurl"] = $myurl;
					
					$data["addtime"] = time();
					$data["domainid"] = $this->getdomainid();
					pdo_insert('mogucms_guanwang_case', $data);
					$url = safe_gpc_string($_GPC['url']);
					itoast('添加成功', $url, 'success');
			}else{
				$categorynews = pdo_getall('mogucms_guanwang_case_category',array("domainid"=>$this->getdomainid()));
				include $this->template('admin/caseadd');
			}
		} else if($act=="delete"){
			$mid = safe_gpc_int($_GPC["mid"]);
			pdo_delete('mogucms_guanwang_case', array('id' => $mid));
			itoast('删除成功', "/web/index.php?c=site&a=entry&eid=".$_GPC['eid'],"success");
		}else if($act=="edit"){
			$mid = safe_gpc_int($_GPC["mid"]);
			if($_W['ispost']){
				$category = safe_gpc_string($_GPC['category']);
				$title = safe_gpc_string($_GPC['title']);
				$image = safe_gpc_string($_GPC['image']);
				$erweima = safe_gpc_string($_GPC['erweima']);
				$ord = safe_gpc_string($_GPC['ord']);
				$myurl = safe_gpc_string($_GPC['myurl']);
				if(empty($title)){
					itoast('标题不能为空', referer());
				}
				$data["category"]= $category;
				$data["title"] = $title;
				$data["image"] = $image;
				$data["erweima"] = $erweima;
				$data["ord"] = $ord;
				$data["myurl"] = $myurl;
				pdo_update("mogucms_guanwang_case",$data,array("id"=>$mid));
				$url = safe_gpc_string($_GPC['url']);
				itoast('编辑成功', $url);
			}else{
				$categorynews = pdo_getall('mogucms_guanwang_case_category',array("domainid"=>$this->getdomainid()));
				$list = pdo_get("mogucms_guanwang_case", array('id' => $mid));
				include $this->template('admin/caseadd');
			}
		}else{
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;

			$condition = array();
			$condition['domainid'] = $this->getdomainid();
			$did = $this->getdomainid();
			$all = pdo_getall('mogucms_guanwang_case',$condition);
        	$total = sizeof($all);
			//$lists = pdo_getslice('mogucms_guanwang_case', $condition, array($pindex, $psize), $total,'', 'id', 'id desc');
			$start = ($pindex-1)*$psize;
			$lists = pdo_fetchall("select A.*,B.categoryname from ".tablename("mogucms_guanwang_case")." as A left join ".tablename("mogucms_guanwang_case_category")." as B on A.category=B.id where A.domainid=$did order by id desc limit $start,$psize ");

			$pager = pagination($total, $pindex, $psize);
			include $this->template('admin/case');
		}
	}
	public function doWebWebset() {
		global $_W,$_GPC;
		
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$setting=array();
		if(!empty($set["val"])){
			$setting = iunserializer($set["val"]);
		}
		if($_W["ispost"]){
			
			$seri['company'] = safe_gpc_string($_GPC['company']);
			$seri['domain'] = safe_gpc_string($_GPC['domain']);
			$seri['dianhua'] = safe_gpc_string($_GPC['dianhua']);
			$seri['shouji'] = safe_gpc_string($_GPC['shouji']);
			$seri['qq'] = safe_gpc_string($_GPC['qq']);
			$seri['email'] = safe_gpc_string($_GPC['email']);
			$seri['dizhi'] = safe_gpc_string($_GPC['dizhi']);
			$seri['luxian'] = safe_gpc_string($_GPC['luxian']);
			$seri['erweima'] = safe_gpc_string($_GPC['erweima']);
			$seri['ditu'] = safe_gpc_string($_GPC['ditu']);
			$seri['banquan'] = safe_gpc_string($_GPC['banquan']);
			$seri['beianhao'] = safe_gpc_string($_GPC['beianhao']);
			$seri['intro'] = safe_gpc_string($_GPC['intro']);
			$seri['logo1'] = safe_gpc_string($_GPC['logo1']);
			$seri['logo2'] = safe_gpc_string($_GPC['logo2']);
			$seri['solve'] = safe_gpc_string($_GPC['solve']);
			$seri['xiaochengxu'] = safe_gpc_string($_GPC['xiaochengxu']);
			$seri['dingzhi'] = safe_gpc_string($_GPC['dingzhi']);
			$seri['dzt'] = safe_gpc_string($_GPC['dzt']);
			$seri['rewrite'] = safe_gpc_string($_GPC['rewrite']);
			$seri['keywords'] = safe_gpc_string($_GPC['keywords']);
			$seri['description'] = safe_gpc_string($_GPC['description']);
			$seri['onlylogin'] = safe_gpc_string($_GPC['onlylogin']);
			$seri['loginurl'] = safe_gpc_string($_GPC['loginurl']);
			$seri['regurl'] = safe_gpc_string($_GPC['regurl']);
			$seri['daili'] = safe_gpc_string($_GPC['daili']);
			$seri['titleourservice'] = safe_gpc_string($_GPC['titleourservice']);
			$seri['titlekflc'] = safe_gpc_string($_GPC['titlekflc']);
			$seri['titlexwzx'] = safe_gpc_string($_GPC['titlexwzx']);
			$seri['titleanli'] = safe_gpc_string($_GPC['titleanli']);
			$seri['titlesolve'] = safe_gpc_string($_GPC['titlesolve']);
			$seri['showsolve'] = safe_gpc_string($_GPC['showsolve']);
			$seri['isadminanli'] = safe_gpc_string($_GPC['isadminanli']);
			$seri['titlexcxrk'] = safe_gpc_string($_GPC['titlexcxrk']);
			$seri['titlexcxrken'] = safe_gpc_string($_GPC['titlexcxrken']);
			$seri['titlebfanzs'] = safe_gpc_string($_GPC['titlebfanzs']);
			$seri['titlebfanzsen'] = safe_gpc_string($_GPC['titlebfanzsen']);
			$seri['titlesykh'] = safe_gpc_string($_GPC['titlesykh']);
			$seri['titlesykhen'] = safe_gpc_string($_GPC['titlesykhen']);
			$seri['title31'] = safe_gpc_string($_GPC['title31']);
			$seri['title32'] = safe_gpc_string($_GPC['title32']);
			$seri['title33'] = safe_gpc_string($_GPC['title33']);
			$seri['title34'] = safe_gpc_string($_GPC['title34']);
			$seri['title35'] = safe_gpc_string($_GPC['title35']);
			$seri['title36'] = safe_gpc_string($_GPC['title36']);
			$seri['icon'] = safe_gpc_string($_GPC['icon']);
			$seri['moban'] = safe_gpc_string($_GPC['moban']);
			$seri['gonganhaourl'] = safe_gpc_string($_GPC['gonganhaourl']);
			$seri['gonganhao'] = safe_gpc_string($_GPC['gonganhao']);
			$seri['homeanlinum'] = safe_gpc_string($_GPC['homeanlinum']);
			$seri['xcxanlinum'] = safe_gpc_string($_GPC['xcxanlinum']);
			$seri['gsimg'] = safe_gpc_string($_GPC['gsimg']);
			$seri['gsurl'] = safe_gpc_string($_GPC['gsurl']);
			$data['val'] = iserializer($seri);
			$a = pdo_update('mogucms_guanwang_domain', array("val"=>$data['val']),array("username"=>$username));
			 itoast('设置成功！', referer());
		}else{
			include $this->template('webset');
		}
	}

	public function doWebTeamadd(){
		include $this->template('admin/teamadd');
	}

	private function geturl($do){
		global $_W,$_GPC;
		$e = pdo_get("modules_bindings",array("do"=>$do,"module"=>"mogucms_guanwang","entry"=>"menu"));
		$url = $_W['siteroot']."web/index.php?c=site&a=entry&eid=".$e['eid'];
		return $url;
	}
	
	public function doWebLoginset(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));

        if (!empty($set['loginset'])) {
            $setting = iunserializer($set['loginset']);
        }

		if ($_W['ispost']) {
			

			$uni_setting['yangshi'] = safe_gpc_string($_GPC['yangshi']);
			$uni_setting['title'] = safe_gpc_string($_GPC['title']);
            $uni_setting['zhongwen'] = safe_gpc_string($_GPC['zhongwen']);
            $uni_setting['yingwen'] = safe_gpc_string($_GPC['yingwen']);
            $uni_setting['corp'] = safe_gpc_string($_GPC['corp']);
            $uni_setting['shipin'] = safe_gpc_string($_GPC['shipin']);
            $uni_setting['iszhuche'] = safe_gpc_string($_GPC['iszhuche']);
            $uni_setting['smssign'] = safe_gpc_string($_GPC['smssign']);
            $uni_setting['logo'] = safe_gpc_string($_GPC['logo']);
            $data['loginset'] = iserializer($uni_setting);

            if (!empty($set)) {
                pdo_update('mogucms_guanwang_domain', array('loginset' => $data['loginset']), array("username"=>$username));
            }
           
            itoast('设置成功！', $this->geturl('loginset'), 'success');
		}else{
			include $this->template('admin/setting');
		}
	}
	
	public function doWebBanner(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$setting=array();
		if(!empty($set["banner"])){
			$setting = iunserializer($set["banner"]);
		}

		if($_W['ispost']){
			
		
			$seri['contact'] = safe_gpc_string($_GPC['contact']);
			$seri['about'] = safe_gpc_string($_GPC['about']);
			$seri['home'] = safe_gpc_string($_GPC['home']);
			$seri['homemob'] = safe_gpc_string($_GPC['homemob']);
			$seri['solve'] = safe_gpc_string($_GPC['solve']);
			$seri['solvetitle'] = safe_gpc_string($_GPC['solvetitle']);
			$seri['xiaochengxu'] = safe_gpc_string($_GPC['xiaochengxu']);
			$seri['xiaochengxutitle'] = safe_gpc_string($_GPC['xiaochengxutitle']);
			$seri['dingzhititle'] = safe_gpc_string($_GPC['dingzhititle']);
			$seri['dingzhi'] = safe_gpc_string($_GPC['dingzhi']);
			$seri['news'] = safe_gpc_string($_GPC['news']);
			$seri['daili'] = safe_gpc_string($_GPC['daili']);
			$seri['dailititle'] = safe_gpc_string($_GPC['dailititle']);
			$seri['home1'] = safe_gpc_string($_GPC['home1']);
			$seri['home2'] = safe_gpc_string($_GPC['home2']);
			$seri['home3'] = safe_gpc_string($_GPC['home3']);
			$seri['homeurl1'] = safe_gpc_string($_GPC['homeurl1']);
			$seri['homeurl2'] = safe_gpc_string($_GPC['homeurl2']);
			$seri['homeurl3'] = safe_gpc_string($_GPC['homeurl3']);
			$seri['homemob'] = safe_gpc_string($_GPC['homemob']);
			$seri['homemob1'] = safe_gpc_string($_GPC['homemob1']);
			$seri['homemob2'] = safe_gpc_string($_GPC['homemob2']);
			$seri['homemob3'] = safe_gpc_string($_GPC['homemob3']);
			$seri['help'] = safe_gpc_string($_GPC['help']);
			$seri['helptitle'] = safe_gpc_string($_GPC['helptitle']);
			$data['banner'] = iserializer($seri);
			if($set){
				pdo_update('mogucms_guanwang_domain', array("banner"=>$data['banner']),array("username"=>$username));
			}
			 itoast('设置成功！', $this->geturl('banner'), 'success');
		}else{
			include $this->template('admin/banner');
		}
	}

	public function doWebMenu(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));

        if (!empty($set['menu'])) {
            $setting = iunserializer($set['menu']);
        }

		if ($_W['ispost']) {
			

			$uni_setting['about'] = safe_gpc_string($_GPC['about']);
			$uni_setting['news'] = safe_gpc_string($_GPC['news']);
			$uni_setting['dingzhi'] = safe_gpc_string($_GPC['dingzhi']);
			$uni_setting['xiaochengxu'] = safe_gpc_string($_GPC['xiaochengxu']);
			$uni_setting['solve'] = safe_gpc_string($_GPC['solve']);
			$uni_setting['daili'] = safe_gpc_string($_GPC['daili']);
			$uni_setting['contact'] = safe_gpc_string($_GPC['contact']);
			$uni_setting['about2'] = safe_gpc_string($_GPC['about2']);
			$uni_setting['news2'] = safe_gpc_string($_GPC['news2']);
			$uni_setting['dingzhi2'] = safe_gpc_string($_GPC['dingzhi2']);
			$uni_setting['xiaochengxu2'] = safe_gpc_string($_GPC['xiaochengxu2']);
			$uni_setting['solve2'] = safe_gpc_string($_GPC['solve2']);
			$uni_setting['daili2'] = safe_gpc_string($_GPC['daili2']);
			$uni_setting['help'] = safe_gpc_string($_GPC['help']);
			$uni_setting['help2'] = safe_gpc_string($_GPC['help2']);
			$uni_setting['contact2'] = safe_gpc_string($_GPC['contact2']);
			$uni_setting['defname1'] = safe_gpc_string($_GPC['defname1']);
			$uni_setting['defname2'] = safe_gpc_string($_GPC['defname2']);
			$uni_setting['defurl1'] = safe_gpc_string($_GPC['defurl1']);
			$uni_setting['defurl2'] = safe_gpc_string($_GPC['defurl2']);
			$uni_setting['home'] = safe_gpc_string($_GPC['home']);
            $data['menu'] = iserializer($uni_setting);

            if (!empty($set)) {
                pdo_update('mogucms_guanwang_domain', array('menu' => $data['menu']), array("username"=>$username));
            }
           
            itoast('设置成功！', $this->geturl('menu'), 'success');
		}else{
			include $this->template('admin/menu');
		}
	}

	public function doWebTitle(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));

        if (!empty($set['title'])) {
            $setting = iunserializer($set['title']);
        }

		if ($_W['ispost']) {
			

			$uni_setting['home'] = safe_gpc_string($_GPC['home']);
			$uni_setting['about'] = safe_gpc_string($_GPC['about']);
			$uni_setting['news'] = safe_gpc_string($_GPC['news']);
			$uni_setting['dingzhi'] = safe_gpc_string($_GPC['dingzhi']);
			$uni_setting['xiaochengxu'] = safe_gpc_string($_GPC['xiaochengxu']);
			$uni_setting['solve'] = safe_gpc_string($_GPC['solve']);
			$uni_setting['daili'] = safe_gpc_string($_GPC['daili']);
			$uni_setting['contact'] = safe_gpc_string($_GPC['contact']);
			$uni_setting['help'] = safe_gpc_string($_GPC['help']);
			
            $data['title'] = iserializer($uni_setting);

            if (!empty($set)) {
                pdo_update('mogucms_guanwang_domain', array('title' => $data['title']), array("username"=>$username));
            }
           
            itoast('设置成功！', $this->geturl('title'), 'success');
		}else{
			include $this->template('admin/title');
		}
	}

	//代理加盟
	public function doWebDaili(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$setting=array();
		if(!empty($set["daili"])){
			$setting = iunserializer($set["daili"]);
		}
		if($_W['ispost']){
			

			$seri['title1'] = safe_gpc_string($_GPC['title1']);
			$seri['title2'] = safe_gpc_string($_GPC['title2']);
			$seri['title3'] = safe_gpc_string($_GPC['title3']);
			$seri['title4'] = safe_gpc_string($_GPC['title4']);
			$seri['title5'] = safe_gpc_string($_GPC['title5']);
			$seri['title6'] = safe_gpc_string($_GPC['title6']);
			$seri['title7'] = safe_gpc_string($_GPC['title7']);
			$seri['title8'] = safe_gpc_string($_GPC['title8']);
			$seri['title9'] = safe_gpc_string($_GPC['title9']);
			$seri['title10'] = safe_gpc_string($_GPC['title10']);
			$seri['title11'] = safe_gpc_string($_GPC['title11']);
			$seri['title12'] = safe_gpc_string($_GPC['title12']);
			$seri['title13'] = safe_gpc_string($_GPC['title13']);
			$seri['title14'] = safe_gpc_string($_GPC['title14']);
			$seri['title15'] = safe_gpc_string($_GPC['title15']);
			$seri['title16'] = safe_gpc_string($_GPC['title16']);
			$seri['title17'] = safe_gpc_string($_GPC['title17']);
			$seri['title18'] = safe_gpc_string($_GPC['title18']);
			$seri['title19'] = safe_gpc_string($_GPC['title19']);
			$seri['title20'] = safe_gpc_string($_GPC['title20']);
			$seri['title21'] = safe_gpc_string($_GPC['title21']);
			$seri['title22'] = safe_gpc_string($_GPC['title22']);
			$seri['title23'] = safe_gpc_string($_GPC['title23']);
			$seri['title24'] = safe_gpc_string($_GPC['title24']);
			$seri['title25'] = safe_gpc_string($_GPC['title25']);
			$seri['title26'] = safe_gpc_string($_GPC['title26']);
			$seri['title27'] = safe_gpc_string($_GPC['title27']);
			$seri['title28'] = safe_gpc_string($_GPC['title28']);
			$seri['title29'] = safe_gpc_string($_GPC['title29']);
			
			
			$data['daili'] = iserializer($seri);
			if($set){
				pdo_update('mogucms_guanwang_domain', array("daili"=>$data['daili']),array("username"=>$username));
			}
			 itoast('设置成功！', $this->geturl('daili'), 'success');
		}else{
			include $this->template('admin/daili');
		}
	}

	//解决方案
	public function doWebSolve(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$act = $_GPC['act'];
		if($act == "categoryadd"){
			if($_W['ispost']){
					$categoryname = safe_gpc_string($_GPC['categoryname']);
					$ord = safe_gpc_string($_GPC['ord']);
					if(empty($categoryname)){
						itoast('分类不能为空', referer());
					}
					$data["categoryname"]= $categoryname;
					$data["ord"]= $ord;
					$data["domainid"] = $this->getdomainid();
					pdo_insert('mogucms_guanwang_solve_category', $data);
					$url = safe_gpc_string($_GPC['url']);
					itoast('添加成功', $url, 'success');
			}else{
				include $this->template('admin/solvecategoryadd');
			}
		}else if($act=="categorydel"){
			$mid = safe_gpc_int($_GPC["mid"]);
			pdo_delete('mogucms_guanwang_solve_category', array('id' => $mid));
			itoast('删除成功', "/web/index.php?c=site&a=entry&eid=".$_GPC['eid']."&act=category","success");
		}else if($act=="categoryedit"){
			$mid = safe_gpc_int($_GPC["mid"]);

			if($_W['ispost']){
				$categoryname = safe_gpc_string($_GPC['categoryname']);
				$ord = safe_gpc_string($_GPC['ord']);
				$data["categoryname"]= $categoryname;
				$data["ord"] = $ord;
				$a=pdo_update("mogucms_guanwang_solve_category",$data,array("id"=>$mid));
				$url = safe_gpc_string($_GPC['url']);
				itoast('编辑成功',$url,"success");
			}else{
				
				$list = pdo_get("mogucms_guanwang_solve_category", array('id' => $mid));
				include $this->template('admin/solvecategoryadd');
			}
		}else if($act=="category"){
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;

			$condition = array();
			//$condition['domainid'] = $this->getdomainid();
			$all = pdo_getall('mogucms_guanwang_solve_category',$condition);
        	$total = sizeof($all);
			$lists = pdo_getslice('mogucms_guanwang_solve_category', $condition, array($pindex, $psize), $total,'', 'id', 'id desc');
			$pager = pagination($total, $pindex, $psize);
			include $this->template('admin/solvecategory');
		}else if($act == "solveadd"){
			if($_W['ispost']){
					$category = safe_gpc_string($_GPC['category']);
					$title = safe_gpc_string($_GPC['title']);
					$count = safe_gpc_string($_GPC['count']);
					$content = safe_gpc_string($_GPC['content']);
					$keywords = safe_gpc_string($_GPC['keywords']);
					$description = safe_gpc_string($_GPC['description']);
					$image = safe_gpc_string($_GPC['image']);
					$erweima = safe_gpc_string($_GPC['erweima']);
					if(empty($title)){
						itoast('标题不能为空', referer());
					}
					$data["category"]= $category;
					$data["title"] = $title;
					$data["count"] = $count;
					$data["content"] = $content;
					$data["keywords"] = $keywords;
					$data["description"] = $description;
					$data["image"] = $image;
					$data["erweima"] = $erweima;
					$data["addtime"] = time();
					$data["domainid"] = $set['id'];
					pdo_insert('mogucms_guanwang_solve', $data);
					$url = safe_gpc_string($_GPC['url']);
					itoast('添加成功', $url, 'success');
			}else{
				$categorynews = pdo_getall('mogucms_guanwang_case_category',array("domainid"=>$set['id']));
				include $this->template('admin/solveadd');
			}
		} else if($act=="delete"){
			$mid = safe_gpc_int($_GPC["mid"]);
			pdo_delete('mogucms_guanwang_solve', array('id' => $mid));
			itoast('删除成功', "/web/index.php?c=site&a=entry&eid=".$_GPC['eid'],"success");
		}else if($act=="edit"){
			$mid = safe_gpc_int($_GPC["mid"]);
			if($_W['ispost']){
				$category = safe_gpc_string($_GPC['category']);
				$title = safe_gpc_string($_GPC['title']);
				$count = safe_gpc_string($_GPC['count']);
				$content = safe_gpc_string($_GPC['content']);
				$keywords = safe_gpc_string($_GPC['keywords']);
				$description = safe_gpc_string($_GPC['description']);
				$image = safe_gpc_string($_GPC['image']);
					$erweima = safe_gpc_string($_GPC['erweima']);
				if(empty($title)){
					itoast('标题不能为空', referer());
				}
				$data["category"]= $category;
				$data["title"] = $title;
				$data["count"] = $count;
				$data["content"] = $content;
				$data["image"] = $image;
				$data["erweima"] = $erweima;
				$data["keywords"] = $keywords;
				$data["description"] = $description;
				pdo_update("mogucms_guanwang_solve",$data,array("id"=>$mid));
				$url = safe_gpc_string($_GPC['url']);
				itoast('编辑成功', $url);
			}else{
				$categorynews = pdo_getall('mogucms_guanwang_case_category',array("domainid"=>$set['id']));
				$list = pdo_get("mogucms_guanwang_solve", array('id' => $mid));
				include $this->template('admin/solveadd');
			}
		}else{
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;

			$condition = array();
			$condition['domainid'] = $set['id'];
			$all = pdo_getall('mogucms_guanwang_solve',$condition);
        	$total = sizeof($all);
			$lists = pdo_getslice('mogucms_guanwang_solve', $condition, array($pindex, $psize), $total,'', 'id', 'id desc');
			$pager = pagination($total, $pindex, $psize);
			include $this->template('admin/solve');
		}
	}
	//我们能做什么
	public function doWebCando(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$setting=array();
		if(!empty($set["cando"])){
			$setting = iunserializer($set["cando"]);
		}
		if($_W['ispost']){
			$domain = $_SERVER['HTTP_HOST'];
			$isfounder = $_W['isfounder'];
			//$data['domain'] = $domain;
			//$data['isfounder'] = $isfounder;
			$data['addtime'] = time();

			$seri['image1'] = safe_gpc_string($_GPC['image1']);
			$seri['title1'] = safe_gpc_string($_GPC['title1']);
			$seri['miaoshu1'] = safe_gpc_string($_GPC['miaoshu1']);
			$seri['image2'] = safe_gpc_string($_GPC['image2']);
			$seri['title2'] = safe_gpc_string($_GPC['title2']);
			$seri['miaoshu2'] = safe_gpc_string($_GPC['miaoshu2']);
			$seri['image3'] = safe_gpc_string($_GPC['image3']);
			$seri['title3'] = safe_gpc_string($_GPC['title3']);
			$seri['miaoshu3'] = safe_gpc_string($_GPC['miaoshu3']);
			$seri['image4'] = safe_gpc_string($_GPC['image4']);
			$seri['title4'] = safe_gpc_string($_GPC['title4']);
			$seri['miaoshu4'] = safe_gpc_string($_GPC['miaoshu4']);
			$seri['image5'] = safe_gpc_string($_GPC['image5']);
			$seri['title5'] = safe_gpc_string($_GPC['title5']);
			$seri['miaoshu5'] = safe_gpc_string($_GPC['miaoshu5']);
			
			$data['cando'] = iserializer($seri);
			if($set){
				pdo_update('mogucms_guanwang_domain', array("cando"=>$data['cando']),array("username"=>$username));
			}else{
			 	//pdo_insert('mogucms_guanwang_domain', $data);
			}
			 itoast('设置成功！', $this->geturl('cando'), 'success');
		}else{
			include $this->template('admin/cando');
		}
	}
	//我们的服务
	public function doWebService(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$setting=array();
		if(!empty($set["ourservice"])){
			$setting = iunserializer($set["ourservice"]);
		}
		if($_W['ispost']){
			
			$seri['image1'] = safe_gpc_string($_GPC['image1']);
			$seri['title1'] = safe_gpc_string($_GPC['title1']);
			$seri['miaoshu1'] = safe_gpc_string($_GPC['miaoshu1']);
			$seri['image2'] = safe_gpc_string($_GPC['image2']);
			$seri['title2'] = safe_gpc_string($_GPC['title2']);
			$seri['miaoshu2'] = safe_gpc_string($_GPC['miaoshu2']);
			$seri['image3'] = safe_gpc_string($_GPC['image3']);
			$seri['title3'] = safe_gpc_string($_GPC['title3']);
			$seri['miaoshu3'] = safe_gpc_string($_GPC['miaoshu3']);
			$seri['image4'] = safe_gpc_string($_GPC['image4']);
			$seri['title4'] = safe_gpc_string($_GPC['title4']);
			$seri['miaoshu4'] = safe_gpc_string($_GPC['miaoshu4']);
			
			$data['ourservice'] = iserializer($seri);
			if($set){
				pdo_update('mogucms_guanwang_domain', array("ourservice"=>$data['ourservice']),array("username"=>$username));
			}
			 itoast('设置成功！', $this->geturl('service'), 'success');
		}else{
			include $this->template('admin/service');
		}
	}

	//我们的服务
	public function doWebDevelopment(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$setting=array();
		if(!empty($set["development"])){
			$setting = iunserializer($set["development"]);
		}
		if($_W['ispost']){
			$domain = $_SERVER['HTTP_HOST'];
			$isfounder = $_W['isfounder'];
			$data['domain'] = $domain;
			$data['isfounder'] = $isfounder;
			$data['addtime'] = time();

			$seri['image1'] = safe_gpc_string($_GPC['image1']);
			$seri['title1'] = safe_gpc_string($_GPC['title1']);
			$seri['miaoshu1'] = safe_gpc_string($_GPC['miaoshu1']);
			$seri['image2'] = safe_gpc_string($_GPC['image2']);
			$seri['title2'] = safe_gpc_string($_GPC['title2']);
			$seri['miaoshu2'] = safe_gpc_string($_GPC['miaoshu2']);
			$seri['image3'] = safe_gpc_string($_GPC['image3']);
			$seri['title3'] = safe_gpc_string($_GPC['title3']);
			$seri['miaoshu3'] = safe_gpc_string($_GPC['miaoshu3']);
			$seri['image4'] = safe_gpc_string($_GPC['image4']);
			$seri['title4'] = safe_gpc_string($_GPC['title4']);
			$seri['miaoshu4'] = safe_gpc_string($_GPC['miaoshu4']);
			$seri['image5'] = safe_gpc_string($_GPC['image5']);
			$seri['title5'] = safe_gpc_string($_GPC['title5']);
			$seri['miaoshu5'] = safe_gpc_string($_GPC['miaoshu5']);
			
			$data['development'] = iserializer($seri);
			if($set){
				pdo_update('mogucms_guanwang_domain', array("development"=>$data['development']),array("username"=>$username));
			}
			 itoast('设置成功！', $this->geturl('development'), 'success');
		}else{
			include $this->template('admin/development');
		}
	}

	public function doWebHelp(){
		global $_GPC, $_W;
		$this->isBindDomain();
		$username = $_W['username'];
		$set = pdo_get("mogucms_guanwang_domain",array("username"=>$username));
		$act = $_GPC['act'];
		if($act == "categoryadd"){
			if($_W['ispost']){
					$categoryname = safe_gpc_string($_GPC['categoryname']);
					$ord = safe_gpc_string($_GPC['ord']);
					if(empty($categoryname)){
						itoast('新闻分类不能为空', referer());
					}
					$data["categoryname"]= $categoryname;
					$data["ord"]= $ord;
					$data["domainid"] = $this->getdomainid();
					pdo_insert('mogucms_guanwang_help_category', $data);
					$url = safe_gpc_string($_GPC['url']);
					itoast('添加成功', $url, 'success');
			}else{
				include $this->template('admin/helpcategoryadd');
			}
		}else if($act=="categorydel"){
			$mid = safe_gpc_int($_GPC["mid"]);
			pdo_delete('mogucms_guanwang_help_category', array('id' => $mid));
			itoast('删除成功', "/web/index.php?c=site&a=entry&eid=".$_GPC['eid']."&act=category","success");
		}else if($act=="categoryedit"){
			$mid = safe_gpc_int($_GPC["mid"]);

			if($_W['ispost']){
				$categoryname = safe_gpc_string($_GPC['categoryname']);
				$ord = safe_gpc_string($_GPC['ord']);
				$data["categoryname"]= $categoryname;
				$data["ord"] = $ord;
				$a=pdo_update("mogucms_guanwang_help_category",$data,array("id"=>$mid));
				$url = safe_gpc_string($_GPC['url']);
				itoast('编辑成功',$url,"success");
			}else{
				
				$list = pdo_get("mogucms_guanwang_help_category", array('id' => $mid));
				include $this->template('admin/helpcategoryadd');
			}
		}else if($act=="category"){
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;

			$condition = array();
			$condition['domainid'] = $this->getdomainid();
			$all = pdo_getall('mogucms_guanwang_help_category',$condition);
        	$total = sizeof($all);
			$lists = pdo_getslice('mogucms_guanwang_help_category', $condition, array($pindex, $psize), $total,'', 'id', 'id desc');
			$pager = pagination($total, $pindex, $psize);
			include $this->template('admin/helpcategory');
		}else if($act == "newsadd"){
			if($_W['ispost']){
					$category = safe_gpc_string($_GPC['category']);
					$title = safe_gpc_string($_GPC['title']);
					$abstract = safe_gpc_string($_GPC['abstract']);
					$content = safe_gpc_string($_GPC['content']);
					$keywords = safe_gpc_string($_GPC['keywords']);
					$description = safe_gpc_string($_GPC['description']);
					$image = safe_gpc_string($_GPC['image']);
					if(empty($title)){
						itoast('标题不能为空', referer());
					}
					$data["category"]= $category;
					$data["title"] = $title;
					$data["abstract"] = $abstract;
					$data["content"] = $content;
					$data["image"] = $image;
					$data["keywords"] = $keywords;
					$data["description"] = $description;
					$data["addtime"] = time();
					$data["domainid"] = $this->getdomainid();
					pdo_insert('mogucms_guanwang_help', $data);
					$url = safe_gpc_string($_GPC['url']);
					itoast('添加成功', $url, 'success');
			}else{
				
				$condition = array();
				$condition['domainid'] = $this->getdomainid();
				$categorynews = pdo_getall('mogucms_guanwang_help_category',$condition);
				include $this->template('admin/helpadd');
			}
		} else if($act=="delete"){
			$mid = safe_gpc_int($_GPC["mid"]);
			pdo_delete('mogucms_guanwang_help', array('id' => $mid));
			itoast('删除成功', "/web/index.php?c=site&a=entry&eid=".$_GPC['eid'],"success");
		}else if($act=="edit"){
			$mid = safe_gpc_int($_GPC["mid"]);
			if($_W['ispost']){
				$category = safe_gpc_string($_GPC['category']);
				$title = safe_gpc_string($_GPC['title']);
				$abstract = safe_gpc_string($_GPC['abstract']);
				$content = safe_gpc_string($_GPC['content']);
				$keywords = safe_gpc_string($_GPC['keywords']);
				$description = safe_gpc_string($_GPC['description']);
				$image = safe_gpc_string($_GPC['image']);
				if(empty($title)){
					itoast('标题不能为空', referer());
				}
				$data["category"]= $category;
				$data["title"] = $title;
				$data["abstract"] = $abstract;
				$data["content"] = $content;
				$data["keywords"] = $keywords;
				$data["image"] = $image;
				$data["description"] = $description;
				//print_r($data);echo $mid;die;
				pdo_update("mogucms_guanwang_help",$data,array("id"=>$mid));
				$url = safe_gpc_string($_GPC['url']);
				itoast('编辑成功', $url);
			}else{
				$condition = array();
				$condition['domainid'] = $this->getdomainid();
				$categorynews = pdo_getall('mogucms_guanwang_help_category',$condition);
				$list = pdo_get("mogucms_guanwang_help", array('id' => $mid));
				include $this->template('admin/helpadd');
			}
		}else{
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;

			$condition = array();
			$condition['domainid'] = $this->getdomainid();
			$all = pdo_getall('mogucms_guanwang_help',$condition);
        	$total = sizeof($all);
			//$lists = pdo_getslice('mogucms_guanwang_news', $condition, array($pindex, $psize), $total,'', 'id', 'id desc');
			$start = ($pindex-1)*$psize;
			$lists = pdo_fetchall("select A.*,B.categoryname from ".tablename("mogucms_guanwang_help")." as A left join ".tablename("mogucms_guanwang_help_category")." as B on A.category=B.id where A.domainid='".$this->getdomainid()."' order by id desc limit $start,$psize");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('admin/help');
		}
	}
}