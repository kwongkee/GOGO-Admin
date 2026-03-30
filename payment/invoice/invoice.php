<?php
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');

date_default_timezone_set('Asia/Shanghai');

if(!empty($_POST) && isset($_POST['token'])) {
	//file_put_contents('./get_postdata.log', print_r($_POST,TRUE),FILE_APPEND);
	
	$User_infos = pdo_get('invoice_config',array('uniacid'=>$_POST['uniacid']),array('config'));
	
	if(!$User_infos){
		echo "no user_infodatas";
		return FALSE;
	}
	//否则有数据就去配置中的数据；
	$strArr = unserialize($User_infos['config']);
	
	$sendArrs = $_POST?$_POST:'';
	$sendArrs['config'] = $strArr['xb'];//发票公司新配置；
	
	switch($sendArrs['token'])
	{		  
		case 'SendinVeli':
			SendinVeli($sendArrs);//请求执行发票订单生成
			break;
		case 'QueryEli':
			QueryEli($sendArrs);//发票订单查询
			break;
		case 'UpConfig':
			UpConfig($sendArrs);//更新配置
			break;
		case 'getFunc':
			getFunc();//获取WDSL的方法
			break;
	}

}else {
	echo 'no Datas';
}

//发票订单生成
function SendinVeli($sendArrs)
{
	$sendArr = [
		'userName'=>$sendArrs['config']['userName'],//用户名
	    'passWord'=>$sendArrs['config']['passWord'],//密码
	    
	    'FPQQLSH'=>$sendArrs['FPQQLSH'],//发票流水号		s
	    'DDH'=>$sendArrs['DDH'],//订单编号			s
	    'uniacid'=>$sendArrs['uniacid'],//公众号ID
	    'openid'=>$sendArrs['openid'],//公众号ID
	    
	    'XMMC'=>$sendArrs['XMMC'],//项目名称	s
		'DW'=>$sendArrs['DW'],//单位  个		n
		'XMSL'=>$sendArrs['XMSL'],//项目数量	s
		'XMDJ'=>$sendArrs['XMDJ'],//项目单价	s		
		'GGXH'=>$sendArrs['GGXH'],//规格型号  填写车位编码	n
	    'invoice_type'=>$sendArrs['invoice_type']?$sendArrs['invoice_type']:'',//商城：mall,商店：shop
	    /**
		 * 配置信息
		 */
	    'HSBZ'=>$sendArrs['config']['HSBZ'],//含税标志		s
		'YHZCBS'=>$sendArrs['config']['YHZCBS'],//优惠政策：0不使用，1使用	n
		'SL'=>$sendArrs['config']['SL'],//税率		s
	    'SPBM'=>$sendArrs['config']['SPBM'],//商品分类编码	s  3070500000000000000（居民日常服务）		1070101010200000000（车用汽油）
		 /**
		 * 销货方信息
		 */
	    'KP_NSRSBH'=>$sendArrs['config']['NSRSBH'],//开票方纳税人识别号	s
	    'KP_NSRMC'=>$sendArrs['config']['NSRMC'],//开票方名称		s
	    'DKBZ'=>$sendArrs['config']['DKBZ'],//代开标志:1、自开(0),2、代开(1)			s
	    'XHF_NSRSBH'=>$sendArrs['config']['NSRSBH'],//销货方识别号		440002999999441		s
	    'XHF_MC'=>$sendArrs['config']['NSRMC'],//销货方名称								s
	    'XHF_DZ'=>$sendArrs['config']['XHF_DZ'],//销货方地址		s
	    'XHF_DH'=>$sendArrs['config']['XHF_DH'],//销货方电话				s
	    'XHF_YHZH'=>$sendArrs['config']['XHF_YHZH'],//销货方银行账号		s
	    'KPR'=>$sendArrs['config']['KPR'],//开票员
	    'SKR'=>$sendArrs['config']['SKR'],//收款人
	    'FHR'=>$sendArrs['config']['FHR'],//复核人
	    
		/**
		 * 购货方信息
		 */
		'GHF_NSRSBH'=>$sendArrs['GHF_NSRSBH'],//购货方识别号	可空	91440604MA4W42D54L（找不到）n
		'GHF_DZ'	=>$sendArrs['GHF_DZ'],//购货方地址		n
		'GHF_GDDH'	=>$sendArrs['GHF_GDDH'],//购货方固定电话		n
		'GHF_SJ'	=>$sendArrs['GHF_SJ'],//购货方手机 用于接收发票短信链接；	n
		'GHFQYLX'	=>$sendArrs['GHFQYLX'],//购货方企业类型：01企业，02机关事业单位03：个人,04:其他；
		'GHF_YHZH'	=>$sendArrs['GHF_YHZH'],//		n
	    'GHF_MC'	=>$sendArrs['GHF_MC'],//购货方名称		s
	    'GHF_EMAIL'	=>$sendArrs['GHF_EMAIL'],//购货方名称		s
	    'oids'		=>$sendArrs['oids'],
	];

	$inv = new Inoveli($sendArr);
	
	$res = $inv->inVeli();
	
	if($res->out->returnCode == '0000' && $res->out->returnMsg == '成功')
	{
		
		$methodParams = array(
			'FPQQLSH' =>$res->out->FPQQLSH?$res->out->FPQQLSH:'',//发票流水号
			'DDH'=>$res->out->DDH?$res->out->DDH:'',//订单号
			'KP_NSRSBH'=>$sendArr['KP_NSRSBH']?$sendArr['KP_NSRSBH']:'',//开票方纳税人识别号
			'XHF_NSRSBH'=>$sendArr['KP_NSRSBH']?$sendArr['KP_NSRSBH']:'',//销货方识别号
		);
		
			//$pdfurl = '';
		
			//$Query = $inv->QueryEli($methodParams);//查询订单
			
			//$pdfurl = $Query->out->PDF_URL;
			
			$pdfurl = $res->out->PDF_URL;
			
			//try{
				//sleep(1);
				
				if($pdfurl != '') {//如果pdfurl存在就显示链接；
				
					$upData = [
						'FP_HM'	=>$res->out->FP_HM,//发票号码
						'FP_DM'	=>$res->out->FP_DM,//发票代码
						'JYM'	=>$res->out->JYM,//发票检验码
						'PDF_URL'=>$res->out->PDF_URL,//发票下载地址
						'state'	=>'1',
					];
					/**
					 * 发送邮箱数据组装；
					 */
					/*$SendEmail = [
						'XHF_NSRSBH' 	=>$sendArr['XHF_NSRSBH'],//销货方识别号
						'GHF_EMAIL'		=>$sendArr['GHF_EMAIL'],//购货方邮箱
						'FP_HM'			=>$res->out->FP_HM,//发票号码
						'FP_DM'			=>$res->out->FP_DM,//发票代码
					];*/
					
					$result = pdo_update('invoices_ord', $upData, array('FPQQLSH' => $res->out->FPQQLSH));
					
						//发送电子邮箱
						//$inv->autoSendByEmail($SendEmail);
						
					$msg = [
						'statu'=> 'success',
						'pdfurl'=> $pdfurl,
					];
					echo json_encode($msg);
					
					
				} else {
					
					$msg = [
						'statu' => 'error',
						'pdfurl'=> $pdfurl,
						'msg'   => '开票失败'
					];
					echo json_encode($msg);
				}

			/*}catch(SOAPFault $e) {
				
				$res = [
					'statu'=> $e,
					'pdfurl'=> '',
				];
				echo json_encode($res);
			}*/
		
	} else {

		$msg = [
			'statu' => 'error',
			'pdfurl'=> '',
		];
		echo json_encode($msg);
	}
}


function getFunc()
{
	
	$inv = new Inoveli();
	
	$res = $inv->getFunc();
	echo '<pre>';
	print_r($res);
}






//更新发票配置
function UpConfig($postArr)
{
	// 序列化数据配置
	$update=[
		//测试使用账户
		/*'xb' =>[
			'userName'	=> '14410101',//账号
			'passWord' 	=>'JWVFkqs7IP+++xBb1I9a/qr6/L/qYxyw==',//秘钥
			'NSRSBH' 	=> '440002999999441',//纳税人识别号
			'NSRMC' 	=>'佛山市成柏电子科技有限公司',//秘钥
			'XHF_DZ'	=>'佛山市顺德区伦教街道办事处常教社区居民委员会尚成路1号唯美嘉园6座103号铺',//销货方地址
			'XHF_DH'	=>'0757-22223287',//销货方电话
			'XHF_YHZH'	=>'广东顺德农村商业银行股份有限公司大良信合支行  801101000927634235',//销货方银行账号
			
			//'GHFQYLX'=>'03',//购货方企业类型：01企业，02机关事业单位03：个人,04:其他；
			
			'KPR'	=>'梁月华',//开票员
		    'SKR'	=>'梁月华',//收款人
		    'FHR'	=>'植银弟',//复核人
		    'DKBZ'	=>'0',//代开标志:1、自开(0),2、代开(1)
		    'HSBZ'	=>'1',//含税标志		s	配置部分
		    'YHZCBS'=>'0',//优惠政策：0不使用，1使用
			'SL'	=>'0.10',//税率		s	配置部分		
		    'SPBM'	=>'3040502020200000000',//'3070500000000000000',//商品分类编码	s
					
		],*/
		
		//正式使用账户
		'xb' =>[
			'userName'	=> '14410101',//账号
			'passWord' 	=>'JWVFkqs7IP+++xBb1I9a/qr6/L/qYxyw==',//秘钥
			'NSRSBH' 	=> '91440606MA4WX4KK55',//纳税人识别号
			'NSRMC' 	=>'佛山市成柏电子科技有限公司',//秘钥
			'XHF_DZ'	=>'佛山市顺德区伦教街道办事处常教社区居民委员会尚成路1号唯美嘉园6座103号铺',//销货方地址
			'XHF_DH'	=>'0757-22223287',//销货方电话
			'XHF_YHZH'	=>'广东顺德农村商业银行股份有限公司大良信合支行  801101000927634235',//销货方银行账号
			
			//'GHFQYLX'=>'03',//购货方企业类型：01企业，02机关事业单位03：个人,04:其他；
			
			'KPR'	=>'梁月华',//开票员
		    'SKR'	=>'梁月华',//收款人
		    'FHR'	=>'植银弟',//复核人
		    'DKBZ'	=>'0',//代开标志:1、自开(0),2、代开(1)
		    'HSBZ'	=>'1',//含税标志		s	配置部分
		    'YHZCBS'=>'0',//优惠政策：0不使用，1使用
			'SL'	=>'0.09',//税率		s	配置部分
		    'SPBM'	=>'3040502020200000000',//'3070500000000000000',//商品分类编码	s
			
		],
	];
	// 序列化。
	$str = serialize($update);
	$datas = [
		'config' =>$str,
	];
	//查询表中是否有数据，无数据就插入新的，有就更新原来的数据；
	$configs = pdo_get('invoice_config',array('uniacid'=>$postArr['uniacid']),array('id'));
	
	if($configs) {//如果有数据就更新
		$res = pdo_update('invoice_config', $datas, array('uniacid' =>$postArr['uniacid']));
		if($res) {
			echo 'UpdateSuccess';
		}else {
			echo 'UpdateError';
		}
	}else{
		$datas['uniacid'] = $postArr['uniacid'];
		$result = pdo_insert('invoice_config', $datas);
		if (!empty($result)) {
			$uid = pdo_insertid();
			echo 'InsertSuccess';
		}
	}
}


class Inoveli
{
    //const HOST = 'http://www.aisinogz.com:19876/AisinoFp-test/eliWebService.ws?wsdl'; //测试地址
	const HOST = 'http://www.aisinogz.com:19876/AisinoFp_fscbdz/eliWebService.ws?wsdl'; // 生产地址
    private $uniacids;//公众号系统ID
	private $userName;//账号
	private $passWord;//秘钥
	private $FPQQLSH;//订单流水号
	private $DDH;//订单编号
	private $XMSL;//项目数量
	private $XMDJ;//项目单价
	private $XMJE;//项目金额
	private $HSBZ;//函数标志
	private $YHZCBS;//优惠政策  0：不使用，1使用
	private $ZZSTSGL;//增值税特殊管理
	private $SL;//税率
	private $se;//税额  //税额=实收金额/(1十税率)x税率  	不含税金额=实收金额/(1十税率)  计算公式
	private $SE;//税额
	private $KPHJJE;//价税合计金额
	private $XMMC;//项目名称
	private $DW;//单位
	private $invoice_type;//开票类型，发票类型；自定义
	/**
	 * 开票方信息
	 */
	private $KP_NSRSBH;//开票纳税人识别号
	private $KP_NSRMC;//开票纳税人名称
	private $DKBZ;//代开标志
	private $XHF_NSRSBH;//销货方纳税人识别号
	private $XHF_MC;//销货方名称
	private $XHF_DZ;//销货方地址
	private $XHF_DH;//销货方电话
	private $XHF_YHZH;//销货方银行账号
	private $KPR;//开票员
    private	$SKR;//收款人
    private	$FHR;//复核人
    private $uniacid;//公众号ID
    
	/**
	 * 购货方信息
	 */
	private $GHF_NSRSBH;//购货方识别号
	private $GHF_DZ;//购货方地址
	private $GHF_GDDH;//
	private	$GHF_SJ;//购货方手机 用于接收发票短信链接；
	private $GHF_EMAIL;//购货方邮箱；
	private	$GHFQYLX;//购货方企业类型：01企业，02机关事业单位03：个人,04:其他；
	private $GHF_YHZH;//购货方银行账号;
    private $GHF_MC;//购货方名称
    
    private $openid;//用户区别；
    
    private $GGXH;//规格型号  填写车位编码
    private $SPBM;//商品分类编码
	private $oids;// 开票停车订单自增ID foll_order 表的
	/**
	 * 构造函数变量赋值
	 */
	function __construct($sendArr)
	{
		$this->oids		= $sendArr['oids'];
        $this->uniacids = $sendArr['uniacid'];
		$this->userName = $sendArr['userName'];//账号
		$this->openid = $sendArr['openid'];//账号
		$this->passWord = $sendArr['passWord'];//秘钥
		$this->FPQQLSH = $sendArr['FPQQLSH'];//订单流水号
		$this->DDH = $sendArr['DDH'];//订单编号
		$this->XMMC = $sendArr['XMMC'];//项目数量
		$this->DW = $sendArr['DW'];//项目单价
		$this->XMSL = $sendArr['XMSL'];//项目数量
		$this->XMDJ = $sendArr['XMDJ'];//项目单价
		$this->XMJE = ($this->XMDJ*$this->XMSL);//计算项目金额   项目金额
		$this->HSBZ = $sendArr['HSBZ'];//函数标志
		$this->YHZCBS = $sendArr['YHZCBS'];//优惠政策  0：不使用，1使用
		$this->ZZSTSGL = $sendArr['YHZCBS']=='1'?'免税':'';//增值税特殊管理
		$this->SL = $sendArr['SL'];//税率
		$this->se = ($this->HSBZ==1?($this->XMJE/(1+$this->SL)*$this->SL):($this->XMJE/(1+$this->SL)));//计算税额
		$this->SE = sprintf('%0.2f',$this->se);//税额
		$this->KPHJJE = sprintf('%0.2f',$this->XMJE);//开票金额
		$this->invoice_type = $sendArr['invoice_type'];//商城：mall,商店：shop  针对商城订单的发票
		
		$this->KP_NSRSBH = $sendArr['KP_NSRSBH'];//开票方纳税人识别号
		$this->KP_NSRMC = $sendArr['KP_NSRMC'];//开票纳税人名称
		$this->DKBZ = $sendArr['DKBZ'];//代开标志
		$this->XHF_NSRSBH = $sendArr['XHF_NSRSBH'];//销货方纳税人识别号
		$this->XHF_MC = $sendArr['XHF_MC'];//销货方名称
		$this->XHF_DZ = $sendArr['XHF_DZ'];//销货方地址
		$this->XHF_DH = $sendArr['XHF_DH'];//销货方电话
		$this->XHF_YHZH = $sendArr['XHF_YHZH'];//销货方银行账号
		$this->KPR = $sendArr['KPR'];//开票员
		$this->SKR = $sendArr['SKR'];//收款人
		$this->FHR = $sendArr['FHR'];//复核人
		$this->uniacid =$sendArr['uniacid'];//公众号ID
		
		$this->GHFQYLX = $sendArr['GHFQYLX'];//购货方企业类型：01企业，02机关事业单位03：个人,04:其他；
		$this->GGXH = $sendArr['GGXH'];//规格型号  填写车位编码
		$this->SPBM = $sendArr['SPBM'];//商品分类编码
		$this->GHF_EMAIL = $sendArr['GHF_EMAIL'];//购货方电子邮箱
		
		if($sendArr['GHFQYLX'] == '03') {
			$this->GHF_SJ = $sendArr['GHF_SJ'];//购货方手机 用于接收发票短信链接
			$this->GHF_MC = '个人('.$sendArr['GHF_MC'].')';//购货方名称
			
		}else {
			$this->GHF_NSRSBH = $sendArr['GHF_NSRSBH'];//购货方识别号
			$this->GHF_DZ = $sendArr['GHF_DZ'];//购货方地址		1
			$this->GHF_GDDH = $sendArr['GHF_GDDH'];//购货方电话		1
			$this->GHF_SJ = $sendArr['GHF_SJ'];//购货方手机 用于接收发票短信链接
			$this->GHF_YHZH = $sendArr['GHF_YHZH'];//购货方银行账号;
			$this->GHF_MC = $sendArr['GHF_MC'];//购货方名称
		}
	}
	
	/**
	 * 发票开票
	 */
	public function inVeli()
	{
		$methodParams = array(
			'in0'=>array(
		        'userName'=>$this->userName,//用户名
		        'passWord'=>$this->passWord,//密码
		        		        
		        'FPQQLSH'=>$this->FPQQLSH,//发票流水号
		        'DDH'=>$this->DDH,//订单编号
		        /**
				 * 销货方信息
				 */
		        'KP_NSRSBH'=>$this->KP_NSRSBH,//开票方纳税人识别号	变量
		        'KP_NSRMC'=>$this->KP_NSRMC,//开票方名称
		        'DKBZ'=>$this->DKBZ,//代开标志:1、自开(0),2、代开(1)
		        'XHF_NSRSBH'=>$this->XHF_NSRSBH,//销货方识别号
		        'XHF_MC'=>$this->XHF_MC,//销货方名称
		        'XHF_DZ'=>$this->XHF_DZ,//销货方地址
		        'XHF_DH'=>$this->XHF_DH,//
		        'XHF_YHZH'=>$this->XHF_YHZH,
				
				/**
				 * 购货方信息
				 */
				'GHF_NSRSBH'=>$this->GHF_NSRSBH,//购货方识别号
				'GHF_DZ'=>$this->GHF_DZ,
				'GHF_GDDH'=>$this->GHF_GDDH,
				'GHF_SJ'=>$this->GHF_SJ,//购货方手机 用于接收发票短信链接；
				'GHFQYLX'=>$this->GHFQYLX,//购货方企业类型：01企业，02机关事业单位03：个人,04:其他；
				'GHF_YHZH'=>$this->GHF_YHZH,
		        'GHF_MC'=>$this->GHF_MC,//购货方名称
		        'GHF_EMAIL'=>$this->GHF_EMAIL,//购货方邮箱
		        
		        'KPR'=>$this->KPR,//开票员
		        'SKR'=>$this->SKR,//收款人
		        'FHR'=>$this->FHR,//复核人
		        
		        'KPLX'=>'1',//1 正票、2 红票	默认
		        'CZDM'=>'10',//10 正票正常开具11 正票错票重开20 退货折让红票、21 错票重开红票、22 换票冲红（全冲红电子发票，开具纸质发票）
		        
		        'KPHJJE'=>$this->KPHJJE,//合计不含税金额
		        'BZ'=>'',
		        'details'=>array(
		        	array(
		                'XMMC'=>$this->XMMC,//项目名称
		                'DW'=>$this->DW,//单位  个
		                'GGXH'=>$this->GGXH,//规格型号  填写车位编码
		                'SPBM'=>$this->SPBM,//商品分类编码
		                'YHZCBS'=>$this->YHZCBS,//优惠政策标识
		                'XMSL'=>$this->XMSL,//项目数量		s
		                'XMDJ'=>$this->XMDJ,//项目单价		s
		                'HSBZ'=>$this->HSBZ,//含税标志			s
		                'XMJE'=>$this->KPHJJE,//项目金额		s
		                'SL'=>$this->SL,//税率			s
		                'SE'=>$this->SE//税额
		        	)
				)
		    )
		);
		
		
		$oggArr = [
            'uniacid' => $this->uniacids,//公众号IDdds
            'openid' => $this->openid,//用户ID
	        'FPQQLSH'=>$this->FPQQLSH,//发票流水号
	        'DDH'=>$this->DDH,//订单编号
	        'invoice_type'=>$this->invoice_type,
	        /**
			 * 销货方信息
			 */
	        'KP_NSRSBH'=>$this->KP_NSRSBH,//开票方纳税人识别号	变量
	        'KP_NSRMC'=>$this->KP_NSRMC,//开票方名称
	        'DKBZ'=>$this->DKBZ,//代开标志:1、自开(0),2、代开(1)
	        'XHF_NSRSBH'=>$this->XHF_NSRSBH,//销货方识别号
	        'XHF_MC'=>$this->XHF_MC,//销货方名称
	        'XHF_DZ'=>$this->XHF_DZ,//销货方地址
	        'XHF_DH'=>$this->XHF_DH,//0757-8632911	销货方电话
	        'XHF_YHZH'=>$this->XHF_YHZH,//销货方银行账号
			/**
			 * 购货方信息
			 */
			'GHF_NSRSBH'=>$this->GHF_NSRSBH,//购货方识别号
			'GHF_SJ'=>$this->GHF_SJ,//购货方手机 用于接收发票短信链接；
			'GHF_DZ'=>$this->GHF_DZ,//购货方手机 用于接收发票短信链接；
			'GHF_GDDH'=>$this->GHF_GDDH,//购货方手机 用于接收发票短信链接；
			'GHF_EMAIL'=>$this->GHF_EMAIL,//购货方邮箱
			'GHFQYLX'=>$this->GHFQYLX,//购货方企业类型：01企业，02机关事业单位03：个人,04:其他；
			'GHF_YHZH'=>$this->GHF_YHZH,//购货方银行账号
	        'GHF_MC'=>$this->GHF_MC,//购货方名称
	        'KPR'=>$this->KPR,//开票员
	        'SKR'=>$this->SKR,//收款人
	        'FHR'=>$this->FHR,//复核人
	        
	        'KPLX'=>'1',//1 正票、2 红票
	        'CZDM'=>'10',//10 正票正常开具11 正票错票重开20 退货折让红票、21 错票重开红票、22 换票冲红（全冲红电子发票，开具纸质发票）
	               
	        'YHZCBS'=>$this->YHZCBS,//优惠政策标识
	        'XMMC'=>$this->XMMC,//项目名称
            'DW'=>$this->DW,//单位  个
            'GGXH'=>$this->GGXH,//规格型号  填写车位编码
            'SPBM'=>$this->SPBM,//商品分类编码
            
            'XMSL'=>$this->XMSL,//项目数量		s
            'XMDJ'=>$this->XMDJ,//项目单价		s
            'HSBZ'=>$this->HSBZ,//含税标志			s
            'KPHJJE'=>$this->KPHJJE,//合计不含税金额
            'XMJE'=>$this->KPHJJE,//项目金额		s
            'SL'=>$this->SL,//税率			s
            'SE'=>$this->SE,//税额
			'oids'=>$this->oids,
            'create_date' => time(),
		];
		
		$old = pdo_insert('invoices_ord', $oggArr);
		
        file_put_contents('./oggArr'.date('Ym',time()).'.log', print_r($oggArr,TRUE),FILE_APPEND);
		
		try {
			//实例化对象
//			$client = new SoapClient("http://www.aisinogz.com:19876/AisinoFp-test/eliWebService.ws?wsdl");
            $client = new SoapClient(self::HOST);
			//数据请求开票
			$res = $client->invEli($methodParams);//传递数据
            //发送数据函数；
//          $functionName = 'invEli';
            //检测函数，传递参数；
//          $res = $client->__soapCall($functionName, array($methodParams,));
			//写入日志文件
			file_put_contents('./invEli'.date('Ym',time()).'.log', print_r($res,TRUE),FILE_APPEND);
			return $res;//返回数据信息
			
		}catch(SOAPFault $e){
			return $e;
		}
	}

	//订单查询
	public function QueryEli($queryArr)
	{
		$methodParams = array(
			'in0'=>array(
				'FPQQLSH' 	=>$queryArr['FPQQLSH'],//发票流水号
				'DDH'		=>$queryArr['DDH'],//订单号
				'KP_NSRSBH'	=>$queryArr['KP_NSRSBH'],//开票方纳税人识别号
				'XHF_NSRSBH'=>$queryArr['KP_NSRSBH'],//销货方识别号
			)
		);
		
		try {
			//实例化对象
			$client = new SoapClient(self::HOST);
			//数据请求开票
			$res = $client->queryEliData($methodParams);//传递数据
			//写入日志文件
			file_put_contents('./queryEliData.log', print_r($res,TRUE),FILE_APPEND);
			return $res;//返回数据信息
			
		}catch(SOAPFault $e){
			return $e;
		}

	}
	
	/**
	 * 发送电子邮箱
	 */
	public function autoSendByEmail($queryArr)
	{
		$methodParams = array(
			'in0'=>array(
				'XHF_NSRSBH' =>$queryArr['XHF_NSRSBH'],//销货方识别号
				'GHF_EMAIL'=>$queryArr['GHF_EMAIL'],//购货方邮箱
				'FP_HM'=>$queryArr['FP_HM'],//发票号码
				'FP_DM'=>$queryArr['FP_DM'],//发票代码
			)
		);
		
//		try {
			//实例化对象
			$client = new SoapClient(self::HOST);
			//数据请求开票
			$res = $client->autoSendByEmail($methodParams);//传递数据
			//写入日志文件
			file_put_contents('./autoSendByEmail.log', print_r($res,TRUE),FILE_APPEND);
			return $res;//返回数据信息
			
//		}catch(SOAPFault $e){
//			return $e;
//		}
	}
	
	/**
	 * 查询发票库存
	 */
	public function queryEliStocks($KPNSRSBH='440002999999441')
	{
		
		$methodParams = array(
			'KP_NSRSBH'=>$KPNSRSBH,//开票方纳税人识别号
		);
		
		try {
			//实例化对象
			$client = new SoapClient(self::HOST);
			//数据请求开票
			$res = $client->queryEliStock($methodParams);//传递数据
			return $res;//返回数据信息
			
		}catch(SOAPFault $e){
			return $e;
		}

	}
	
	/**
	 * 获取WSDL的服务方法
	 */
	public function getFunc()
	{
		try {
			//实例化对象
			$client = new SoapClient(self::HOST);
			//数据请求开票
			$res = $client->__getFunctions();//获取服务方法
			$res = $client->__getTypes();//获取参数类型；
			return $res;//返回数据信息
			
		}catch(SOAPFault $e){
			return $e;
		}
	}
}

?>