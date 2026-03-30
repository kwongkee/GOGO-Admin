<?php
	
/*if (empty($license)) {
    return false;
}
#匹配民用车牌和使馆车牌
# 判断标准
# 1，第一位为汉字省份缩写
# 2，第二位为大写字母城市编码
# 3，后面是5位仅含字母和数字的组合
{
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
}

#匹配特种车牌(挂,警,学,领,港,澳)
#参考 https://wenku.baidu.com/view/4573909a964bcf84b9d57bc5.html
{
    $regular = '/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{4}[挂警学领港澳]{1}$/u';
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
}

#匹配武警车牌
#参考 https://wenku.baidu.com/view/7fe0b333aaea998fcc220e48.html
{
    $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]?[0-9a-zA-Z]{5}$/ui';
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
}

#匹配军牌
#参考 http://auto.sina.com.cn/service/2013-05-03/18111149551.shtml
{
    $regular = "/[A-Z]{2}[0-9]{5}$/";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
}
#匹配新能源车辆6位车牌
#参考 https://baike.baidu.com/item/%E6%96%B0%E8%83%BD%E6%BA%90%E6%B1%BD%E8%BD%A6%E4%B8%93%E7%94%A8%E5%8F%B7%E7%89%8C
{
    #小型新能源车
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[DF]{1}[0-9a-zA-Z]{5}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
    #大型新能源车
    $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{5}[DF]{1}$/u";
    preg_match($regular, $license, $match);
    if (isset($match[0])) {
        return true;
    }
}
return false;*/

//$carNo = '粤BD12345';
$car = [
	'粤BD12345','粤E198P5','粤X33K65','粤BF12345'
];
foreach($car as $val) {
	var_dump(isLicensePlate($val));
	if(isLicensePlate($val)) {
		echo $val.' success<br>';
	} else{
		echo $val.' error<br>';
	} 
}



function isLicensePlate($str) {
	$regular = "/^(([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z](([0-9]{5}[DF])|([DF]([A-HJ-NP-Z0-9])[0-9]{4})))|([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z][A-HJ-NP-Z0-9]{4}[A-HJ-NP-Z0-9挂学警港澳使领]))$/u";
    if (preg_match($regular,$str)) {
        return true;
    } else {
    	return false;
    }    
//	return /^(([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z](([0-9]{5}[DF])|([DF]([A-HJ-NP-Z0-9])[0-9]{4})))|([京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领][A-Z][A-HJ-NP-Z0-9]{4}[A-HJ-NP-Z0-9挂学警港澳使领]))$/.test($str);
}

?>