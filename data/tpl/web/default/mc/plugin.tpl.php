<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<div class="module">
	<ul class="thumbnails">
	<?php  if(is_array($plugins)) { foreach($plugins as $row) { ?>
	<li style="width:450px;" class="list-unstyled">
		<div class="thumbnail">
			<div class="module-pic">
				<div class="module-detail">
					<h5 class="module-title"><?php  echo $row['title'];?></h5>
					<p class="module-brief"><?php  echo $row['description'];?></p>
				</div>
			</div>
			<div class="module-button">
				<?php  if(!empty($account_plugins[$row['name']])) { ?>
					<a id="enabled_<?php  echo $row['mid'];?>_0" href="<?php  echo url('mc/plugin/enable', array('name' => $row['name'], 'enabled' => 0))?>" onclick="return ajaxopen(this.href)" class="btn btn-primary module-button-switch">禁用</a>
				<?php  } else { ?>
					<a id="enabled_<?php  echo $row['mid'];?>_1" href="<?php  echo url('mc/plugin/enable', array('name' => $row['name'], 'enabled' => 1))?>" onclick="return ajaxopen(this.href);" class="btn btn-danger module-button-switch">启用</a>
				<?php  } ?>
			</div>
		</div>
	</li>
	<?php  } } ?>
	</ul>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>