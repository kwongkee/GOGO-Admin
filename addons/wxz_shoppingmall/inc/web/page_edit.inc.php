<?php

global $_W, $_GPC;

$id = $_GPC['id'];
$info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_page') . " WHERE id={$id} AND isdel=0";
$info = pdo_fetch($info_sql);
if (!$info) {
    message('页面配置不存在', $this->createWebUrl('page_list'));
}

load()->web('tpl');
require_once WXZ_SHOPPINGMALL . '/source/Page.class.php';

switch ($info['type']) {
    case 3: case 9:
        $info['desc'] = json_decode($info['desc'], true);
        break;
}

if (checksubmit()) {
    switch ($info['type']) {
        case 3:
            foreach ($_GPC['titles'] as $k => $title) {
                $data[] = array(
                    'title' => $title,
                    'link' => $_GPC['links'][$k],
                );
            }
            $_GPC['desc'] = json_encode($data);
            break;
        case 9:
            foreach ($_GPC['titles'] as $i => $titles) {
                foreach ($titles as $pageNum => $title) {
                    if ($title) {
                        $data[$i][$pageNum]['title'] = $title;
                        $data[$i][$pageNum]['link'] = $_GPC['links'][$i][$pageNum];
                        $data[$i][$pageNum]['icon'] = $_GPC['icons'][$i][$pageNum];
                    }
                }
            }
            $_GPC['desc'] = json_encode($data);
            break;
    }

    //字段验证, 并获得正确的数据$dat
    $data = array(
        'title' => $_GPC['title'],
        'img' => $_GPC['img'],
        'link' => $_GPC['link'],
        'desc' => $_GPC['desc'],
        'update_at' => time(),
    );

    if (pdo_update('wxz_shoppingmall_page', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('page_list'));
    } else {
        message('更新失败', $this->createWebUrl('page_edit', array('id' => $id)));
    }
}

switch ($info['type']) {
    case 3:
        include $this->template('web/page_edit_index2_buttom_nav');
        break;
    case 5:case 6:case 7:case 8:
        include $this->template('web/page_edit_img');
        break;
    case 9:
        $pageConfig = Page::$index2PageNav;
        $pageNum = 10;
        if (count($info['desc']) <= 0) {
            $info['desc'][0][0] = array(
                'title' => '',
                'link' => '',
                'color' => '',
                'icon' => '',
            );
        }
        $nextPageNum = count($info['desc']) <= 1 ? 1 : count($info['desc']) + 1; //添加页数
        include $this->template('web/page_index2_nav');
        break;
    default:
        include $this->template('web/page_edit_title');
        break;
}
?>
