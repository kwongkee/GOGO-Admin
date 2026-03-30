<?php

if(!pdo_fieldexists('yun_fkz_record', 'flag')) {
	pdo_query("ALTER TABLE ".tablename('yun_fkz_record')." ADD COLUMN `flag` tinyint(2) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('yun_fkz_record', 'tixian')) {
	pdo_query("ALTER TABLE ".tablename('yun_fkz_record')." ADD COLUMN `tixian` tinyint(3) NOT NULL DEFAULT '0';");
}

if(!pdo_fieldexists('yun_fkz_record', 'tixian_time')) {
	pdo_query("ALTER TABLE ".tablename('yun_fkz_record')." ADD COLUMN `tixian_time` int(11) DEFAULT NULL;");
}






