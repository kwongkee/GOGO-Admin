<?php
global $_W;
$sql = "
drop table if exists " . tablename('yun_fkz_adv') . " ;
drop table if exists " . tablename('yun_fkz_member') . " ;
drop table if exists " . tablename('yun_fkz_poster') . " ;
drop table if exists " . tablename('yun_fkz_qr') . " ;
drop table if exists " . tablename('yun_fkz_record') . " ;
drop table if exists " . tablename('yun_fkz_scene_id') . " ;
drop table if exists " . tablename('yun_fkz_setting') . " ;

";
pdo_query($sql);