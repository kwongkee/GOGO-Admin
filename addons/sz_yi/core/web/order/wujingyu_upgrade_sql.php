<?php
// Ä£¿éLTDÌá¹©
if (!pdo_fieldexists('sz_yi_cashier_store', 'centercan')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_cashier_store') . ' ADD    `centercan` tinyint(1) DEFAULT \'1\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'getsupplier')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD    `getsupplier` tinyint(1) DEFAULT \'0\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'supplierids')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD    `supplierids` text(0) DEFAULT \'\';');
}

if (!pdo_fieldexists('sz_yi_coupon', 'suppliernames')) {
	pdo_fetchall('ALTER TABLE ' . tablename('sz_yi_coupon') . ' ADD    `suppliernames` text(0) DEFAULT \'\';');
}

?>
