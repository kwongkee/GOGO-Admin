<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

if (!class_exists('ArticleModel')) {
	class ArticleModel extends PluginModel
	{
		public function getSys()
		{
			global $_W;
			global $_GPC;
			$article_sys = pdo_fetch('select * from' . tablename('sz_yi_article_sys') . 'where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
			return $article_sys;
		}

		public function doShare($article, $shareid, $myid)
		{
			global $_W;
			global $_GPC;
			$profile = m('member')->getMember($shareid);
			$myinfo = m('member')->getMember($myid);
			$shopset = m('common')->getSysset('shop');
			if (!empty($myid) && ($shareid != $myid) && !empty($profile['openid'])) {
				$_obf_DTUxGzIdHANcNggeNAYiKB85LhsFKSI_ = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_article_share') . ' WHERE aid=:aid and click_user=:click_user and uniacid=:uniacid ', array(':aid' => $article['id'], ':click_user' => $myid, ':uniacid' => $_W['uniacid']));

				if (empty($_obf_DTUxGzIdHANcNggeNAYiKB85LhsFKSI_)) {
					$share_click = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_article_share') . ' WHERE aid=:aid and click_user=:share_user and share_user=:click_user and uniacid=:uniacid ', array(':aid' => $article['id'], ':share_user' => $shareid, ':click_user' => $myid, ':uniacid' => $_W['uniacid']));

					if (empty($share_click)) {
						$_obf_DSY_Ox0oOycWETEMBgMKKSEPDxE9AwE_ = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_article_share') . ' WHERE aid=:aid and share_user=:share_user and uniacid=:uniacid ', array(':aid' => $article['id'], ':share_user' => $shareid, ':uniacid' => $_W['uniacid']));

						if ($_obf_DSY_Ox0oOycWETEMBgMKKSEPDxE9AwE_ < $article['article_rule_allnum']) {
							$day_start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
							$day_end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
							$_obf_DTBbFT8MJDYTQCINHhgdHCU3HhETDhE_ = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_article_share') . ' WHERE aid=:aid and share_user=:share_user and click_date>:day_start and click_date<:day_end and uniacid=:uniacid ', array(':aid' => $article['id'], ':share_user' => $shareid, ':day_start' => $day_start, ':day_end' => $day_end, ':uniacid' => $_W['uniacid']));

							if ($_obf_DTBbFT8MJDYTQCINHhgdHCU3HhETDhE_ < $article['article_rule_daynum']) {
								$total_money = ($article['article_rule_userd_money'] ? $article['article_rule_userd_money'] : 0);

								if (0 < $article['article_rule_money_total']) {
									$sql = 'select sum(add_money) from ' . tablename('sz_yi_article_share') . ' where uniacid = \'' . $_W['uniacid'] . '\' and aid=\'' . $article['id'] . '\' ';
									$total_money += pdo_fetchcolumn($sql);

									if ($article['article_rule_money_total'] <= $total_money) {
										$article['article_rule_money'] = 0;
									}
								}

								$insert = array('aid' => $article['id'], 'share_user' => $shareid, 'click_user' => $myid, 'click_date' => time(), 'add_credit' => $article['article_rule_credit'], 'add_money' => $article['article_rule_money'], 'uniacid' => $_W['uniacid']);
								pdo_insert('sz_yi_article_share', $insert);

								if (0 < $article['article_rule_credit']) {
									m('member')->setCredit($profile['openid'], 'credit1', $article['article_rule_credit'], array(0, $shopset['name'] . ' 文章营销奖励积分'));
								}

								if (0 < $article['article_rule_money']) {
									m('member')->setCredit($profile['openid'], 'credit2', $article['article_rule_money'], array(0, $shopset['name'] . ' 文章营销奖励余额'));
								}

								$article_sys = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_article_sys') . ' WHERE uniacid=:uniacid limit 1 ', array(':uniacid' => $_W['uniacid']));
								$detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member';
								$p = '';

								if (!empty($article['article_rule_credit'])) {
									$p .= $article['article_rule_credit'] . '个积分、';
								}

								if (!empty($article['article_rule_money'])) {
									$p .= $article['article_rule_money'] . '元余额';
								}

								$msg = array(
									'first'    => array('value' => '您的奖励已到帐！', 'color' => '#4a5077'),
									'keyword1' => array('title' => '任务名称', 'value' => '分享得奖励', 'color' => '#4a5077'),
									'keyword2' => array('title' => '通知类型', 'value' => '用户通过您的分享进入文章《' . $article['article_title'] . '》，系统奖励您' . $p . '。', 'color' => '#4a5077'),
									'remark'   => array('value' => '奖励已发放成功，请到会员中心查看。', 'color' => '#4a5077')
									);

								if (!empty($article_sys['article_message'])) {
									m('message')->sendTplNotice($profile['openid'], $article_sys['article_message'], $msg, $detailurl);
									return NULL;
								}

								m('message')->sendCustomNotice($profile['openid'], $msg, $detailurl);
							}
						}
					}
				}
			}
		}

		public function mid_replace($content)
		{
			global $_GPC;
			preg_match_all('/href\\=["|\'](.*?)["|\']/is', $content, $links);

			foreach ($links[1] as $key => $lnk) {
				$_obf_DQUWBDAnFTc_GSUQFy8hJCscKxAoDDI_ = $this->href_replace($lnk);
				$content = str_replace($links[0][$key], 'href="' . $_obf_DQUWBDAnFTc_GSUQFy8hJCscKxAoDDI_ . '"', $content);
			}

			return $content;
		}

		public function href_replace($lnk)
		{
			global $_GPC;
			$_obf_DQUWBDAnFTc_GSUQFy8hJCscKxAoDDI_ = $lnk;
			if (strexists($lnk, 'sz_yi') && !strexists($lnk, '&mid')) {
				if (strexists($lnk, '?')) {
					$_obf_DQUWBDAnFTc_GSUQFy8hJCscKxAoDDI_ = $lnk . '&mid=' . intval($_GPC['mid']);
				}
				else {
					$_obf_DQUWBDAnFTc_GSUQFy8hJCscKxAoDDI_ = $lnk . '?mid=' . intval($_GPC['mid']);
				}
			}

			return $_obf_DQUWBDAnFTc_GSUQFy8hJCscKxAoDDI_;
		}

		public function perms()
		{
			return array(
	'article' => array(
		'text'     => $this->getName(),
		'isplugin' => true,
		'child'    => array(
			'cate' => array('text' => '分类设置', 'addcate' => '添加分类-log', 'editcate' => '编辑分类-log', 'delcate' => '删除分类-log'),
			'page' => array('text' => '文章设置', 'add' => '添加文章-log', 'edit' => '修改文章-log', 'delete' => '删除文章-log', 'showdata' => '查看数据统计', 'otherset' => '其他设置', 'report' => '举报记录')
			)
		)
	);
		}
	}
}

?>
