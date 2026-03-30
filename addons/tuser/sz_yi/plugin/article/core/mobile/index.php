<?php
// 模块LTD提供
function getIP2()
{
	static $realip;

	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$realip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else {
			$realip = $_SERVER['REMOTE_ADDR'];
		}
	}
	else if (getenv('HTTP_X_FORWARDED_FOR')) {
		$realip = getenv('HTTP_X_FORWARDED_FOR');
	}
	else if (getenv('HTTP_CLIENT_IP')) {
		$realip = getenv('HTTP_CLIENT_IP');
	}
	else {
		$realip = getenv('REMOTE_ADDR');
	}

	return $realip;
}

function getCity($ip)
{
	$url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
	$ip = json_decode(file_get_contents($url));

	if ((string) $ip->code == '1') {
		return false;
	}

	$data = (array) $ip->data;
	return $data;
}

global $_W;
global $_GPC;
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
load()->func('tpl');
$aid = intval($_GPC['aid']);
$member_levels = m('member')->getLevels();
$distributor_levels = p('commission')->getLevels();
$condition = '';

if (is_weixin()) {
	$condition = ' and article_state_wx = 1 ';
}

if (!empty($aid)) {
	$article = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_article') . ' WHERE id=:aid and article_state=1 and uniacid=:uniacid ' . $condition . ' limit 1 ', array(':aid' => $aid, ':uniacid' => $_W['uniacid']));

	if (!empty($article)) {
		$category = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_article_category') . ' WHERE uniacid=:uniacid and id = \'' . $article['article_category'] . '\'', array(':uniacid' => $_W['uniacid']));
		$article['isread'] = false;

		if ($category['m_level'] == 0) {
			$article['isread'] = true;
		}
		else {
			if ($category['m_level'] == $member['level']) {
				$article['isread'] = true;
			}
		}

		if ($member['isagent'] == 1) {
			if ($category['d_level'] == 0) {
				$article['isread'] = true;
			}
			else {
				if ($category['d_level'] == $member['agentlevel']) {
					$article['isread'] = true;
				}
			}
		}

		foreach ($member_levels as $key => $value) {
			if ($category['m_level'] == $value['id']) {
				$article['m_message'] = '成为“' . $value['levelname'] . '”等级的会员';
			}
		}

		if ($category['d_level'] == 0) {
			$article['d_message'] = '成为分销商！';
		}
		else {
			foreach ($distributor_levels as $key => $value) {
				if ($category['d_level'] == $value['id']) {
					$article['d_message'] = '成为“' . $value['levelname'] . '”等级的分销商';
				}
			}
		}

		if (!$article['isread']) {
			$messages = '您没有阅读权限！' . $article['m_message'] . '或' . $article['d_message'];
			message($messages, $this->createPluginMobileUrl('article/article'), 'warning');
		}

		$article_sys = pdo_fetch('select * from' . tablename('sz_yi_article_sys') . 'where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
		$article_area = $article_sys['article_area'];

		if ($article_area) {
			$article_area = json_decode($article_area, true);
			if (is_array($article_area) && (0 < sizeof($article_area))) {
				$ip = getIP2();
				$city = getCity($ip);
				$in_area = 0;
				if (is_array($city) && sizeof($city)) {
					$province = $city['region'];
					$city = $city['city'];

					foreach ($article_area as $key => $area) {
						if (trim($area['province']) == trim($province)) {
							if (trim($area['city'])) {
								if (trim($area['city']) == trim($city)) {
									$in_area = 1;
									break;
								}
							}
							else {
								$in_area = 1;
								break;
							}
						}
					}
				}

				if (!$in_area) {
					message('对不起，您不在该文章允许阅读的地理区域内！', '', 'error');
					exit();
				}
			}
		}

		$article['article_content'] = $this->model->mid_replace(htmlspecialchars_decode($article['article_content']));
		$readnum = $article['article_readnum'] + $article['article_readnum_v'];
		$readnum = (100000 < $readnum ? '100000+' : $readnum);
		$likenum = $article['article_likenum'] + $article['article_likenum_v'];
		$likenum = (100000 < $likenum ? '100000+' : $likenum);

		if (empty($article['article_mp'])) {
			$mp = pdo_fetch('SELECT acid,uniacid,name FROM ' . tablename('account_wechats') . ' WHERE uniacid=:uniacid limit 1 ', array(':uniacid' => $_W['uniacid']));
			$article['article_mp'] = $mp['name'];
		}

		$shop = m('common')->getSysset(array('shop', 'share'));
		$openid = m('user')->getOpenid();

		if (!empty($openid)) {
			$state = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_article_log') . ' WHERE openid=:openid and aid=:aid and uniacid=:uniacid limit 1 ', array(':openid' => $openid, ':aid' => $article['id'], ':uniacid' => $_W['uniacid']));

			if (empty($state['id'])) {
				$insert = array('aid' => $aid, 'read' => 1, 'uniacid' => $_W['uniacid'], 'openid' => $openid);
				pdo_insert('sz_yi_article_log', $insert);
				$sid = pdo_insertid();
				pdo_update('sz_yi_article', array('article_readnum' => $article['article_readnum'] + 1), array('id' => $article['id']));
			}
			else {
				if ($state['read'] < 4) {
					pdo_update('sz_yi_article_log', array('read' => $state['read'] + 1), array('id' => $state['id']));
					pdo_update('sz_yi_article', array('article_readnum' => $article['article_readnum'] + 1), array('id' => $article['id']));
				}
			}
		}

		$article['product_advs'] = htmlspecialchars_decode($article['product_advs']);
		$advs = json_decode($article['product_advs'], true);

		foreach ($advs as $i => &$v) {
			$v['link'] = $this->model->href_replace($v['link']);
		}

		unset($v);
		$article['product_advs_link'] = $this->model->href_replace($article['product_advs_link']);
		$article['article_linkurl'] = $this->model->href_replace($article['article_linkurl']);

		if (!empty($advs)) {
			$advnum = count($advs);

			if ($article['product_advs_type'] == 1) {
				$advrand = 0;
			}
			else if ($article['product_advs_type'] == 2) {
				$advrand = rand(0, $advnum - 1);
			}
			else {
				if (($article['product_advs_type'] == 3) && (1 <= $advnum)) {
					$advrand = -1;
				}
			}
		}

		$myid = m('member')->getMid();
		$shareid = intval($_GPC['shareid']);
		echo $doShare = $this->model->doShare($article, $shareid, $myid);
		$_W['shopshare'] = array('title' => $article['article_title'], 'imgUrl' => $article['resp_img'], 'desc' => $article['resp_desc'], 'link' => $this->createPluginMobileUrl('article', array('aid' => $article['id'], 'directopenid' => 1, 'shareid' => $myid)));

		if (p('commission')) {
			$set = p('commission')->getSet();

			if (!empty($set['level'])) {
				$member = m('member')->getMember($openid);
				if (!empty($member) && ($member['status'] == 1) && ($member['isagent'] == 1)) {
					$_W['shopshare']['link'] = $this->createPluginMobileUrl('article', array('aid' => $article['id'], 'shareid' => $myid, 'mid' => $member['id']));
				}
				else if (!empty($_GPC['mid'])) {
					$_W['shopshare']['link'] = $this->createPluginMobileUrl('article', array('aid' => $article['id'], 'shareid' => $myid, 'mid' => $_GPC['mid']));
				}
				else {
					if (!empty($shareid)) {
						$_W['shopshare']['link'] = $this->createPluginMobileUrl('article', array('aid' => $article['id'], 'shareid' => $myid, 'mid' => $shareid));
					}
				}
			}
		}

		$total_money = ($article['article_rule_userd_money'] ? $article['article_rule_userd_money'] : 0);

		if (0 < $article['article_rule_money_total']) {
			$sql = 'select sum(add_money) from ' . tablename('sz_yi_article_share') . ' where uniacid = \'' . $_W['uniacid'] . '\' and aid=\'' . $article['id'] . '\' ';
			$total_money += pdo_fetchcolumn($sql);
		}
	}
	else {
		$url = $this->createPluginMobileUrl('article/article');
		exit('<script>top.window.location.href=\'' . $url . '\'</script>');
	}
}
else {
	exit('url参数错误！');
}

include $this->template('index');

?>
