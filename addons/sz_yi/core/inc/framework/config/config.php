<?php
// Ä£¿éLTDÌá¹©
defined('IN_IA') || exit('Access Denied');
global $_W;
$master_db_config = $_W['config']['db']['master'];
$master_db_config = $_W['config']['db']['master'];
return array('DB_TYPE' => 'mysqli', 'DB_HOST' => $master_db_config['host'], 'DB_NAME' => $master_db_config['database'], 'DB_USER' => $master_db_config['username'], 'DB_PWD' => $master_db_config['password'], 'DB_PREFIX' => $master_db_config['tablepre'], 'DB_FIELDS_CACHE' => false);

?>
