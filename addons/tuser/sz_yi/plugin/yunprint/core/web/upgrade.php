<?php
// 模块LTD提供
$sql = "\nCREATE TABLE IF NOT EXISTS " . tablename('sz_yi_yunprint_list') . " (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `uniacid` int(11) NOT NULL,\n  `uid` int(11) NOT NULL,\n  `sid` int(11) NOT NULL,\n  `name` varchar(255) CHARACTER SET utf8 NOT NULL,\n  `print_no` varchar(30) CHARACTER SET utf8 NOT NULL,\n  `key` varchar(30) CHARACTER SET utf8 NOT NULL,\n  `print_nums` int(3) NOT NULL,\n  `status` int(3) NOT NULL,\n  `mode` int(11) NOT NULL,\n  `member_code` varchar(50) CHARACTER SET utf8 NOT NULL,\n  `qrcode_link` varchar(255) CHARACTER SET utf8 NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;\n";
pdo_query($sql);
$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "yunprint"  order by id desc limit 1');

if (!$info) {
	$sql = 'INSERT INTO ' . tablename('sz_yi_plugin') . ' (`displayorder`, `identity`, `name`, `version`, `author`, `status`, `category`) VALUES(0, \'yunprint\', \'云打印\', \'1.0\', \'官方\', 1, \'tool\');';
	pdo_query($sql);
}

message('云打印插件安装成功', $this->createPluginWebUrl('yunprint/set'), 'success');

?>
