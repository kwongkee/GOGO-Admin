<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_header', TEMPLATE_INCLUDEPATH)) : (include template('web/_header', TEMPLATE_INCLUDEPATH));?>
<div class="w1200 m0a">
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('tabs', TEMPLATE_INCLUDEPATH)) : (include template('tabs', TEMPLATE_INCLUDEPATH));?>
<div class="rightlist">
<form action="" method="post">
	<div class="panel panel-default">
		<div class="panel-heading"><i class="fa fa-cogs"></i> 打印设置</div>
		<div class="panel-body table-responsive">
			<div class="alert alert-info" style="width: 500px;">提示：请在连有打印的电脑上安装控件并进行打印。</div>
			<div class="input-group" style="width: 500px;">
				<div style="border-right:none" class="input-group-addon">本地打印机IP</div>
				<input type="text" value="<?php  if(!empty($printset['ip'])) { ?><?php  echo $printset['ip'];?><?php  } ?>" class="form-control" name="ip" placeholder="localhost">
				<div style="border-right:none; border-left: none;" class="input-group-addon"> 打印机端口</div>
				<input type="tel" value="<?php  if(!empty($printset['port'])) { ?><?php  echo $printset['port'];?><?php  } ?>" class="form-control" placeholder="8000" name="port" <?php if(cv('exhelper.printset.save')) { ?><?php  } else { ?> disabled=""<?php  } ?>>
			</div>
		</div>
		<div class="panel-footer">
			<?php if(cv('exhelper.printset.save')) { ?>
				<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" /> 
				<button class="btn btn-primary">保存设置</button>
			<?php  } ?>
		</div>
	</div>
</form>
</div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('web/_footer', TEMPLATE_INCLUDEPATH)) : (include template('web/_footer', TEMPLATE_INCLUDEPATH));?>