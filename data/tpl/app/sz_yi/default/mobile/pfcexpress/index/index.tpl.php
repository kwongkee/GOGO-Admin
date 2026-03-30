<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>pfc内容对比</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <table class="layui-table">
                <thead>
                    <th style="background:#1790FF;color:#fff;">标题</th>
                    <th style="background:#1790FF;color:#fff;">操作</th>
                </thead>
                <tbody>
                    <?php  if(is_array($change_ids)) { foreach($change_ids as $val) { ?>
                        <tr>
                            <td><?php  echo $val['title'];?></td>
                            <td><a class="layui-btn layui-btn-xs layui-btn-primary" href="./index.php?i=3&c=entry&do=pfcexpress&p=index&m=sz_yi&op=detail&ids=<?php  echo $val['ids'];?>">查看</a></td>
                        </tr>
                    <?php  } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>