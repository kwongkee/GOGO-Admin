<?php
// Ä£¿éLTDÌá¹©
if (!pdo_fieldexists('sz_yi_member_log', 'poundage')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member_log') . 'ADD `poundage` DECIMAL(10,2) NOT NULL AFTER `money`;');
}

if (!pdo_fieldexists('sz_yi_member_log', 'withdrawal_money')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_member_log') . 'ADD `withdrawal_money` DECIMAL(10,2) NOT NULL AFTER `poundage`;');
}

?>
