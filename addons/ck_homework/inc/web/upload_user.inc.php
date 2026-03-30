<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
set_time_limit(0);
include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/IOFactory.php';

if ($_FILES["file"]["error"] > 0) {
    exit(json_encode(['code' => 1, 'message' => "错误：" . $_FILES["file"]["error"]]));
}

//获取学生
function get_student($sheet, $highestRow)
{
    $data = [];
    for ($row = 2; $row <= $highestRow; $row++) {
        if ($sheet->getCellByColumnAndRow(0, $row)->getValue() == "") {
            continue;
        }
        $data[] = [
            'number' => $sheet->getCellByColumnAndRow(0, $row)->getValue(),//编号
            'mobile' => $sheet->getCellByColumnAndRow(1, $row)->getValue(),//手机号
            'name' => $sheet->getCellByColumnAndRow(2, $row)->getValue(),//名称
            'sex' => $sheet->getCellByColumnAndRow(3, $row)->getValue(),//性别
            'birthday' => date('Y-m-d', strtotime($sheet->getCellByColumnAndRow(4, $row)->getValue())),//出生日期
            'address' => $sheet->getCellByColumnAndRow(5, $row)->getValue(),//地址
            'teacher' => $sheet->getCellByColumnAndRow(6, $row)->getValue(),//老师
            'jiazName' => $sheet->getCellByColumnAndRow(7, $row)->getValue(),//家长
            'jiazPhone' => $sheet->getCellByColumnAndRow(8, $row)->getValue(),//家长手机
        ];
    }
    return $data;
}



function get_banjicode_by_teachername($name)
{
    return pdo_fetchall("SELECT
	    a2.code,
	    a3.listorder,
	    a4.school_code 
    FROM
	    ims_onljob_user a1
	    LEFT JOIN ims_onljob_theclass a2 ON a1.uid = a2.uid
	    LEFT JOIN ims_onljob_class a3 ON a2.njid = a3.cid
	    LEFT JOIN ims_onljob_school a4 ON a2.school_id = a4.id 
    WHERE
	    a1.`name` = '{$name}'");
}


//保存学生信息
function save_student($data, $weid, $authkey)
{

    foreach ($data as $val) {
        $number = get_banjicode_by_teachername($val['teacher']);
        if (empty($number)){
            continue;
        }
        $salt = random(8);
        $password = md5($val['mobile'] . $salt . $authkey);
        $number[0]['listorder']= strlen($number[0]['listorder'])>=2?$number[0]['listorder']:'0'.$number[0]['listorder'];
        $n = $number[0]['school_code'].'-'.$number[0]['listorder'].$number[0]['code'].'-'.$val['number'];
        pdo_insert('onljob_user', [
            'uid' => 0,
            'weid' => $weid,
            'usernumber' => $n,
            'name' => $val['name'],
            'phone' => $val['mobile'],
            'type' => 0,
            'sex' => $val['sex'] == '男' ? 1 : 2,
            'address' => $val['address'],
            'birthday' => $val['birthday'],
            'password' => $password,
            'salt' => $salt
        ]);
        $hzuid = pdo_insertid();
        save_jiaz($val, $weid, $authkey,$hzuid);
    }
}

function save_jiaz($data, $weid, $authkey,$hzuid)
{
    $salt = random(8);
    $password = md5($data['jiazPhone'] . $salt . $authkey);
    $numberlent = strlen($hzuid);
    $canumb = 9 - $numberlent;
    $usernumber = random($canumb, true) . $hzuid;
    pdo_insert('onljob_user', [
            'uid' => 0,
            'weid' => $weid,
            'usernumber' => $usernumber,
            'name' => $data['jiazName'],
            'phone' => $data['jiazPhone'],
            'type' => 2,
            'sex' => 0,
            'address' => $data['address'],
            'birthday' => date('Y-m-d', time()),
            'password' => $password,
            'salt' => $salt,
            'hzuid'=>$hzuid
    ]);
}


//获取老师
function get_teacher($sheet, $highestRow)
{
    $data = [];
    for ($row = 2; $row <= $highestRow; $row++) {
        if ($sheet->getCellByColumnAndRow(0, $row)->getValue() == "") {
            continue;
        }
        $data[] = [
            'number' => $sheet->getCellByColumnAndRow(0, $row)->getValue(),//编号
            'mobile' => $sheet->getCellByColumnAndRow(1, $row)->getValue(),//手机号
            'name' => $sheet->getCellByColumnAndRow(2, $row)->getValue(),//名称
            'sex' => $sheet->getCellByColumnAndRow(3, $row)->getValue(),//性别
            'birthday' => date('Y-m-d', strtotime($sheet->getCellByColumnAndRow(4, $row)->getValue())),//出生日期
            'address' => $sheet->getCellByColumnAndRow(5, $row)->getValue(),//地址
        ];
    }
    return $data;
}

//保存老师信息
function save_teacher($data, $weid, $authkey)
{
    foreach ($data as $val) {
        $salt = random(8);
        $password = md5($val['mobile'] . $salt . $authkey);
        pdo_insert('onljob_user', [
            'uid' => 0,
            'weid' => $weid,
            'usernumber' => $val['number'],
            'name' => $val['name'],
            'phone' => $val['mobile'],
            'type' => 1,
            'sex' => $val['sex'] == '男' ? 1 : 2,
            'address' => $val['address'],
            'birthday' => $val['birthday'],
            'audit' => 1,
            'password' => $password,
            'salt' => $salt
        ]);
    }
}

$type = @end(explode('.', $_FILES['file']['name']));
$type = strtolower($type);
// $path = __DIR__ . '/excel/';
// $fileName = date('YmdHis', time()) . mt_rand(11, 999).$type;
// move_uploaded_file($_FILES['file']['tmp_name'], $path . $fileName);
if ($type == 'xls') {
    $inputFileType = 'Excel5';//这个是读 xls的
} else {
    $inputFileType = 'Excel2007';//这个是计xlsx的
}
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($_FILES['file']['tmp_name']);
$sheet = $objPHPExcel->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();//取得总列数
$highestColumnNum = PHPExcel_Cell::columnIndexFromString($highestColumn);

if ($_GPC['utype'] == 0) {
    $result = get_student($sheet, $highestRow);
    pdo_begin();
    try {
        save_student($result, $_W['uniacid'], $_W['config']['setting']['authkey']);
        pdo_commit();
    } catch (\Exception $e) {
        pdo_rollback();
        exit(json_encode(['status' => 1, 'message' => '保存失败']));
    }
} elseif ($_GPC['utype'] == 1) {
    $result = get_teacher($sheet, $highestRow);
    pdo_begin();
    try {
        save_teacher($result, $_W['uniacid'], $_W['config']['setting']['authkey']);
        pdo_commit();
    } catch (\Exception $e) {
        pdo_rollback();
        exit(json_encode(['status' => 1, 'message' => '保存失败']));
    }
}
exit(json_encode(['status' => 0, 'message' => '已保存']));

// var_dump($_FILES);