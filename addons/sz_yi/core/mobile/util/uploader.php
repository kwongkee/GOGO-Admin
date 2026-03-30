<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
load()->func('file');
$op = (!empty($_GPC['op']) ? $_GPC['op'] : 'upload');
//var_dump($_FILES);
if ($op == 'upload') {
	$field = $_GPC['file'];

	if (!empty($_FILES[$field]['name'])) {
		if ($_FILES[$field]['error'] != 0) {
			$result['message'] = '图片上传失败，请重试！';
			exit(json_encode($result));
		}

		$path = '/images/sz_yi/' . $_W['uniacid'];

		if (!is_dir(ATTACHMENT_ROOT . $path)) {
			mkdirs(ATTACHMENT_ROOT . $path);
		}

		$_W['uploadset'] = array();
		$_W['uploadset']['image']['folder'] = $path;
		$_W['uploadset']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadset']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$file = file_upload($_FILES[$field], 'image');

		if (is_error($file)) {
			$result['message'] = $file['message'];
			exit(json_encode($result));
		}

		if (function_exists('file_remote_upload')) {
			$remote = file_remote_upload($file['path']);

			if (is_error($remote)) {
				$result['message'] = $remote['message'];
				exit(json_encode($result));
			}
		}

		$result['status'] = 'success';
		$result['url'] = $file['url'];
		$result['error'] = 0;
		$result['filename'] = $file['path'];
		$result['url'] = save_media($_W['attachurl'] . $result['filename']);
		pdo_insert('core_attachment', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'filename' => $_FILES[$field]['name'], 'attachment' => $result['filename'], 'type' => 1, 'createtime' => TIMESTAMP));
		exit(json_encode($result));
		return 1;
	}

	$result['message'] = '请选择要上传的图片';
	exit(json_encode($result));
	return 1;
}else if($op == 'travel'){
	$field = 'file';
	if (!empty($_FILES[$field]['name'])) {
		if ($_FILES[$field]['error'] != 0) {
			$result['message'] = '图片上传失败，请重试！';
			exit(json_encode($result));
		}

		$path = '/images/sz_yi/' . $_W['uniacid'];

		if (!is_dir(ATTACHMENT_ROOT . $path)) {
			mkdirs(ATTACHMENT_ROOT . $path);
		}

		$_W['uploadset'] = array();
		$_W['uploadset']['image']['folder'] = $path;
		$_W['uploadset']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadset']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$file = file_upload($_FILES[$field], 'image');

		if (is_error($file)) {
			$result['message'] = $file['message'];
			exit(json_encode($result));
		}

		if (function_exists('file_remote_upload')) {
			$remote = file_remote_upload($file['path']);

			if (is_error($remote)) {
				$result['message'] = $remote['message'];
				exit(json_encode($result));
			}
		}

		$result['status'] = 'success';
		$result['url'] = $file['url'];
		$result['error'] = 0;
		$result['filename'] = $file['path'];
		$result['url'] = save_media($_W['attachurl'] . $result['filename']);
		pdo_insert('core_attachment', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'filename' => $_FILES[$field]['name'], 'attachment' => $result['filename'], 'type' => 1, 'createtime' => TIMESTAMP));
		exit(json_encode($result));
		return 1;
	}

	$result['message'] = '请选择要上传的图片';
	exit(json_encode($result));
	return 1;
}else if($op == 'uploadFile'){
	load()->func('file');
	$field = $_GPC['file'];
	$file_suffix = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
	$filetype = ['jpg', 'jpeg', 'png','pdf','doc','docx','excel','xls','xlsx'];

	if(!in_array($file_suffix,$filetype)){
		$result['status'] = 0;
		$result['message'] = '文件上传失败，不支持此类型文件！';
		exit(json_encode($result));
	}

	if (!empty($_FILES[$field]['name'])) {
		if ($_FILES[$field]['error'] != 0) {
			$result['status'] = 0;
			$result['message'] = '文件上传失败，请重试！';
			exit(json_encode($result));
		}

		$path = '/images/sz_yi/' . $_W['uniacid'];

		if (!is_dir(ATTACHMENT_ROOT . $path)) {
			mkdirs(ATTACHMENT_ROOT . $path);
		}

		$_W['uploadset'] = array();
		$_W['uploadset']['image']['folder'] = $path;
		$_W['uploadset']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadset']['image']['limit'] = $_W['config']['upload']['image']['limit'];

		$file = file_upload_xin($_FILES[$field], 'image');

		if (is_error($file)) {
			$result['message'] = $file['message'];
			exit(json_encode($result));
		}

		if (function_exists('file_remote_upload')) {
			$remote = file_remote_upload($file['path']);

			if (is_error($remote)) {
				$result['message'] = $remote['message'];
				exit(json_encode($result));
			}
		}

		$result['status'] = 'success';
		$result['url'] = $file['url'];
		$result['error'] = 0;
		$result['filename'] = $file['path'];
		$result['url'] = save_media($_W['attachurl'] . $result['filename']);
		pdo_insert('core_attachment', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'filename' => $_FILES[$field]['name'], 'attachment' => $result['filename'], 'type' => 1, 'createtime' => TIMESTAMP));
		exit(json_encode($result));
		return 1;
	}

	$result['message'] = '请选择要上传的文件';
	exit(json_encode($result));
	return 1;
}else if($op == 'uploadDeclFile'){
	load()->func('file');
	$field = 'file';
	$file_suffix = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
	$filetype = ['jpg', 'jpeg', 'png','pdf','doc','docx','excel','xls','xlsx'];

	if(!in_array($file_suffix,$filetype)){
		$result['status'] = 0;
		$result['message'] = '文件上传失败，不支持此类型文件！';
		exit(json_encode($result));
	}

	if (!empty($_FILES[$field]['name'])) {
		if ($_FILES[$field]['error'] != 0) {
			$result['status'] = 0;
			$result['message'] = '文件上传失败，请重试！';
			exit(json_encode($result));
		}

		$path = '/images/sz_yi/' . $_W['uniacid'];

		if (!is_dir(ATTACHMENT_ROOT . $path)) {
			mkdirs(ATTACHMENT_ROOT . $path);
		}

		$_W['uploadset'] = array();
		$_W['uploadset']['image']['folder'] = $path;
		$_W['uploadset']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadset']['image']['limit'] = $_W['config']['upload']['image']['limit'];

		$file = file_upload_xin($_FILES[$field], 'image');

		if (is_error($file)) {
			$result['message'] = $file['message'];
			exit(json_encode($result));
		}

		if (function_exists('file_remote_upload')) {
			$remote = file_remote_upload($file['path']);

			if (is_error($remote)) {
				$result['message'] = $remote['message'];
				exit(json_encode($result));
			}
		}

		$result['status'] = 'success';
		$result['url'] = $file['url'];
		$result['error'] = 0;
		$result['filename'] = $file['path'];
		$result['url'] = save_media($_W['attachurl'] . $result['filename']);
		pdo_insert('core_attachment', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'filename' => $_FILES[$field]['name'], 'attachment' => $result['filename'], 'type' => 1, 'createtime' => TIMESTAMP));
		exit(json_encode($result));
		return 1;
	}

	$result['message'] = '请选择要上传的文件';
	exit(json_encode($result));
	return 1;
}else if($op == 'uploadDeclGoodsFile'){
	load()->func('file');
	$field = 'file';
	$file_suffix = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
	$filetype = ['xls','xlsx'];

	if(!in_array($file_suffix,$filetype)){
		$result['status'] = 0;
		$result['message'] = '文件上传失败，只支持excel文件！';
		exit(json_encode($result));
	}

	if (!empty($_FILES[$field]['name'])) {
		if ($_FILES[$field]['error'] != 0) {
			$result['status'] = 0;
			$result['message'] = '文件上传失败，请重试！';
			exit(json_encode($result));
		}

		$path = '/images/sz_yi/' . $_W['uniacid'];

		if (!is_dir(ATTACHMENT_ROOT . $path)) {
			mkdirs(ATTACHMENT_ROOT . $path);
		}

		$_W['uploadset'] = array();
		$_W['uploadset']['image']['folder'] = $path;
		$_W['uploadset']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadset']['image']['limit'] = $_W['config']['upload']['image']['limit'];

		$file = file_upload_xin($_FILES[$field], 'image');

		if (is_error($file)) {
			$result['message'] = $file['message'];
			exit(json_encode($result));
		}

		if (function_exists('file_remote_upload')) {
			$remote = file_remote_upload($file['path']);

			if (is_error($remote)) {
				$result['message'] = $remote['message'];
				exit(json_encode($result));
			}
		}

		$result['status'] = 'success';
		$result['url'] = $file['url'];
		$result['error'] = 0;
		$result['filename'] = $file['path'];
		$result['url'] = save_media($_W['attachurl'] . $result['filename']);

		//循环excel文件
		//文件的扩展名
		$ext = strtolower(pathinfo($result['url'], PATHINFO_EXTENSION));
		require IA_ROOT.'/addons/sz_yi/core/inc/phpexcel/PHPExcel/IOFactory.php';

		if ($ext == 'xlsx') {
			$objReader = \PHPExcel_IOFactory::createReader('Excel2007');
			$objPHPExcel = $objReader->load(IA_ROOT.'/attachment/'.$file['path'], 'utf-8');
		} elseif ($ext == 'xls') {
			$objReader = \PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load(IA_ROOT.'/attachment/'.$file['path'], 'utf-8');
		}

		$sheet = $objPHPExcel->getSheet(0);
		$highestRow = $sheet->getHighestRow(); // 取得总行数
		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
		$ar = array();
		$i = 0;
		$importRows = 0;
		$openid=$_W['openid'];
		$declare_id = intval($_GPC['declare_id']);
		if($declare_id>0){
			$pre_batch_num = pdo_fetchcolumn('select pre_batch_num from '.tablename('customs_pre_declare').' where id=:id and openid=:openid',[':id'=>$declare_id,':openid'=>$openid]);//预报编号
			$result['pre_batch_num']=$pre_batch_num;
			//获取当前申报下的商品总数，作为货号
			$itemNo = pdo_fetchcolumn('select itemNo from '.tablename('customs_goods_pre_log').' where pre_batch_num=:batch_num and openid=:openid order by id desc limit 1',[':batch_num'=>$pre_batch_num,':openid'=>$openid]);
			$importRows = intval(substr($itemNo,6,7));
		}else{
			$pre_batch_num = 'YB'.date('YmdHis',time()).mt_rand(11,99);//预报编号
			$result['pre_batch_num']=$pre_batch_num;
		}

		//从第二行开始
		pdo_begin();
		$rows=0;
		$error_msg='';
		try{
			for ($j = 3; $j <= $highestRow; $j++) {
				$importRows++;
				$rows=$j;
				//必须
				$ar['itemNo'] = (string)$objPHPExcel->getActiveSheet()->getCell("A$j")->getValue();//企业商品货号
				if(empty($ar['itemNo'])){
					$ar['itemNo'] = 'GP'.date('md',time()).str_pad($importRows,7,'0',STR_PAD_LEFT);//货号
				}
				$ar['itemName'] = (string)$objPHPExcel->getActiveSheet()->getCell("B$j")->getValue();//企业商品名称
				if(empty($ar['itemName'])){
					$error_msg='商品名称不能为空';
					throw new Exception('001');
				}
				$ar['gcode'] = (string)$objPHPExcel->getActiveSheet()->getCell("C$j")->getValue();//商品编码
				if(!empty($ar['gcode'])){
				    $hscode = pdo_fetch('select basic_info,hscode,id from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$ar['gcode']]);
				    if(empty($hscode['id'])){
                        $error_msg='请输入正确的商品编码';
					    throw new Exception('001');				        
				    }else{
				        $basic_info = json_decode($hscode['basic_info'],true);
                        if($basic_info[11]=='作废'){
                            $error_msg='商品编码已作废，请更换';
					        throw new Exception('001');	
                        }  
				    }
				}
				$ar['currency'] = (string)$objPHPExcel->getActiveSheet()->getCell("D$j")->getValue();//币制
				if(empty($ar['currency'])){
					$ar['currency'] = 142;//币种
				}
				$ar['qty'] = (string)$objPHPExcel->getActiveSheet()->getCell("E$j")->getValue();//件数，申报数量
				if(empty($ar['qty'])){
					$error_msg='申报数量不能为空';
					throw new Exception('001');
				}
				$ar['qty1'] = (string)$objPHPExcel->getActiveSheet()->getCell("F$j")->getValue();//件数，法定数量
				if(empty($ar['qty1'])){
					$error_msg='法定数量不能为空';
					throw new Exception('001');
				}
				$ar['gmodel'] = (string)$objPHPExcel->getActiveSheet()->getCell("G$j")->getValue();//规格型号
				$ar['price'] = (string)$objPHPExcel->getActiveSheet()->getCell("H$j")->getValue();//FOB单价
				$ar['totalPrice'] = (string)$objPHPExcel->getActiveSheet()->getCell("I$j")->getValue();//FOB总价
				$ar['charge'] = (string)$objPHPExcel->getActiveSheet()->getCell("J$j")->getValue();//收款金额
				if(!empty($ar['price'])){
					if(empty($ar['totalPrice'])){
						$ar['totalPrice'] = sprintf('%.2f',$ar['price']*$ar['qty']);
						$ar['charge'] = $ar['totalPrice'];
					}
					$ar['gmodel'] = $ar['itemName'];
				}
				$ar['chargeDate'] = (string)$objPHPExcel->getActiveSheet()->getCell("K$j")->getValue();//到账时间
				$ar['logisticsNo'] = (string)$objPHPExcel->getActiveSheet()->getCell("L$j")->getValue();//物流运单号
				if(empty($ar['logisticsNo'])){
					$error_msg='物流运单号不能为空';
					throw new Exception('001');
				}
				$ar['freight'] = (string)$objPHPExcel->getActiveSheet()->getCell("M$j")->getValue();//运费
				if(empty($ar['freight'])) {
					$ar['freight'] = 0;//运费
				}
				$ar['insuredFee'] = (string)$objPHPExcel->getActiveSheet()->getCell("N$j")->getValue();//保费
				if(empty($ar['insuredFee'])) {
					$ar['insuredFee'] = 0;//保价
				}
				$ar['barCode'] = (string)$objPHPExcel->getActiveSheet()->getCell("O$j")->getValue();//条形码
				if(empty($ar['barCode'])){
					$ar['barCode']='无';
				}
				$ar['grossWeight'] = (string)$objPHPExcel->getActiveSheet()->getCell("P$j")->getValue();//毛重
				if(empty($ar['grossWeight'])){
					$error_msg='毛重不能为空';
					throw new Exception('001');
				}
				$ar['netWeight'] = (string)$objPHPExcel->getActiveSheet()->getCell("Q$j")->getValue();//净重
				if(empty($ar['netWeight'])){
					$ar['netWeight'] = $ar['grossWeight']*0.9;//毛重*90%=净重
				}
				$ar['packNo'] = (string)$objPHPExcel->getActiveSheet()->getCell("R$j")->getValue();//件数，包裹数
				if(empty($ar['packNo'])){
					$error_msg='件数不能为空';
					throw new Exception('001');
				}
				$ar['goodsInfo'] = (string)$objPHPExcel->getActiveSheet()->getCell("S$j")->getValue();//主要货物信息
				if(empty($ar['goodsInfo'])){
					$ar['goodsInfo']=$ar['itemName'];
				}
				$ar['unit'] = (string)$objPHPExcel->getActiveSheet()->getCell("T$j")->getValue();//申报计量单位
				$ar['unit1'] = (string)$objPHPExcel->getActiveSheet()->getCell("U$j")->getValue();//法定计量单位

				//运算项
				if(empty($ar['price'])) {
					$ar['price'] = 0;//单价
				}
				if(empty($ar['totalPrice'])) {
					$ar['totalPrice'] = 0;//总价
				}
				if(empty($ar['charge'])) {
					$ar['charge'] = 0;//收款金额
				}
				$ar['pre_batch_num']=$pre_batch_num;
				$ar['openid']=$openid;
				pdo_insert('customs_goods_pre_log',$ar);

				if(!empty($ar['price'])){
					$insert_id = pdo_insertid();
					//如果当前price列不为空，则不需要补缺,插入补缺表
					pdo_insert('customs_goods_pre_fill_log',[
						'pre_batch_num'=>$ar['pre_batch_num'],
						'openid'=>$openid,
						'type'=>1,
						'itemNo'=>$ar['itemNo'],
						'itemName'=>$ar['itemName'],
						'gcode'=>$ar['gcode'],
						'currency'=>$ar['currency'],
						'qty'=>$ar['qty'],
						'qty1'=>$ar['qty1'],
						'gmodel'=>$ar['gmodel'],
						'price'=>$ar['price'],
						'totalPrice'=>$ar['totalPrice'],
						'charge'=>$ar['charge'],
						'chargeDate'=>$ar['chargeDate'],
						'logisticsNo'=>$ar['logisticsNo'],
						'freight'=>$ar['freight'],
						'insuredFee'=>$ar['insuredFee'],
						'barCode'=>$ar['barCode'],
						'grossWeight'=>$ar['grossWeight'],
						'netWeight'=>$ar['netWeight'],
						'packNo'=>$ar['packNo'],
						'goodsInfo'=>$ar['goodsInfo'],
						'unit'=>$ar['unit'],
						'unit1'=>$ar['unit1'],
						'good_id'=>$insert_id,//原始商品id
					]);
				}

				$i++;
			}
			pdo_commit();
		}catch (\Exception $e) {
			pdo_rollback();
			exit(json_encode(['status'=>"error",'msg'=>'第'.$rows.'行：'.$error_msg]));
		}

		//循环excel文件END
		pdo_insert('core_attachment', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'filename' => $_FILES[$field]['name'], 'attachment' => $result['filename'], 'type' => 1, 'createtime' => TIMESTAMP));
		exit(json_encode($result));
		return 1;
	}

	$result['message'] = '请选择要上传的文件';
	exit(json_encode($result));
	return 1;
}

if ($op == 'remove') {
	$file = $_GPC['file'];
	file_delete($file);
	show_json(1);
}

?>
