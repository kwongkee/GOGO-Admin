<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
	<li<?php  if($do == 'index') { ?> class="active"<?php  } ?>><a href="<?php  echo url('profile/deskmenu/index');?>">工作台</a></li>
</ul>
<?php  if($do == 'index') { ?>
<div class="clearfix">
	<div class="clearfix menu">
		<?php  if(empty($_W['isfounder'])) { ?>
		<div class="alert alert-danger">您没有操作权限.请联系公众号管理员</div>
   		<?php  } else { ?>
		<?php  if(is_array($permission)) { foreach($permission as $row) { ?>
		<h5 class="page-header"><?php  echo $row['title'];?><?php  if(!$row['system']) { ?>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:" style="display: none;" title="编辑" class="editmain" data-id="<?php  echo $row['id'];?>" data-title="<?php  echo $row['title'];?>" data-op="editmain"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:" style="display: none;" class="delete" title="删除" data-id="<?php  echo $row['id'];?>" data-type="main"><i class="fa fa-times"></i></a><?php  } ?></h5>
		<div class="clearfix deskmenu">
			<?php  if(is_array($row['items'])) { foreach($row['items'] as $row1) { ?>
			<?php  if($row1['system'] == 0) { ?>
			<div class="cover" z-index="1"></div>
			<a href="javascript:" z-index="2" title="编辑" class="edit" data-toggle="modal" data-permission="<?php  echo $row1['permission'];?>" data-type="edit" data-id="<?php  echo $row1['id'];?>" data-title="<?php  echo $row1['title'];?>" data-displayorder="<?php  echo $row1['displayorder'];?>" data-icon="<?php  echo $row1['icon'];?>" data-url="<?php  echo $row1['url'];?>" data-maintitle="<?php  echo $row['title'];?>" data-op="edit" data-system="<?php  echo $row1['system'];?>"><i class="fa fa-edit"></i>编辑</a>
			<a href="javascript:" z-index="2" title="删除" class="delete del" data-id="<?php  echo $row1['id'];?>"><i class="fa fa-times"></i>删除</a>
			<?php  } ?>
			<a href="<?php  if($row1['type'] == 'url') { ?><?php  echo $row1['url'];?><?php  } else { ?>javascript:;<?php  } ?>" class="tile img-rounded<?php  if($row1['type'] == 'modal') { ?> modal-trade-<?php  echo $row1['url'];?><?php  } ?> menu" data-type="<?php  echo $row1['url'];?>">
				<i class="<?php  echo $row1['icon'];?>"></i>
				<span><?php  echo $row1['title'];?></span>
			</a>
			<?php  } } ?>
			<a href="javascript:" data-pid="<?php  echo $row['id'];?>" data-maintitle="<?php  echo $row['title'];?>" data-op="add" class="tile img-rounded add" >
				<i class="glyphicon glyphicon-plus"></i>
				<span>添加子菜单</span>
			</a>
		</div>
		<?php  } } ?>
		<h5 class="page-header"></h5>
		<div class="clearfix">
			<a href="javascript:" class="tile img-rounded addmain" data-op="addmain">
				<i class="glyphicon glyphicon-plus"></i>
				<span>添加主菜单</span>
			</a>
		</div>
		<?php  } ?>
	</div>
</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content"style="width: 700px">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"></h4>
			</div>
			<div class="modal-body">
				<form method="post" class="form-horizontal form" enctype="multipart/form-data" >
					<div class="form-group" id="main_title">
						<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
						<p class="text-danger" id="warning"></p>
						<label class="col-xs-12 col-sm-3 col-md-2 control-label">主菜单名</label>
						<div class="col-sm-9 col-xs-12">
							<input type="text" name="main_title" class="form-control" value="" />
						</div>
					</div>
					<div class="form-group" id="title">
						<label class="col-xs-12 col-sm-3 col-md-2 control-label">子菜单名</label>
						<div class="col-sm-9 col-xs-12">
							<input type="text" name="title" class="form-control" value="" />
						</div>
					</div>
					<div class="form-group" id="displayorder">
						<label class="col-xs-12 col-sm-3 col-md-2 control-label">排序</label>
						<div class="col-sm-9 col-xs-12">
							<input type="text" name="displayorder" class="form-control" value="" />
						</div>
					</div>
					<div class="form-group" id="icon">
						<label class="col-xs-12 col-sm-3 col-md-2 control-label">图标</label>
						<div class="col-sm-9 col-xs-12">
							<div class="input-group">
								<input type="text" name="icon" class="form-control" value="" />
								<span class="input-group-addon"><i class="fa fa-link"></i></span>
								<span class="input-group-btn">
									<button class="btn btn-default showIconDialog" type="button">图标</button>
								</span>
							</div>
						</div>
					</div>
					<div class="form-group" id="url">
						<label class="col-xs-12 col-sm-3 col-md-2 control-label">链接</label>
						<div class="col-sm-9 col-xs-12">
							<?php  echo tpl_form_module_link('url')?>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="id" value="">
				<input type="hidden" name="op" value="">
				<input type="hidden" name="pid" value="">
				<input type="submit" class="btn btn-primary submit">
			</div>
		</div>
	</div>
</div>
<script>
	require(['trade', 'bootstrap'], function(trade){
		$('.menu').click(function() {return false;});
		$('.menu').hover(function() {
			var top = $(this).position().top;
			var left = $(this).position().left;
			var height = parseInt($(this).css('height'));
			$('.cover').hide();
			$(this).prev().prev().prev().show();
			$(this).prev().prev().prev().css({'top' : top+height-16, 'left' : left+5.1});
			$('.edit').hide();
			$('.del').hide();
			$('.editmain').hide();
			$('.delete').hide();
			$(this).prev().show();
			$(this).prev().prev().show();
			$(this).prev().css({'top' : top+height-16, 'left': left+65});
			$(this).prev().prev().css({'top' : top+height-16, 'left' : left+15});
		});
		$('.page-header').hover(function() {
			$('.cover').hide();
			$(this).children().show();
			$('.edit').hide();
			$('.del').hide();
		});
		$('.edit').click(function() {
			$('#myModal').modal('show');
			$('#warning').html('');
			$('#myModalLabel').html('编辑菜单');
			$('[name="main_title"]').attr('disabled', true);
			$('#title, #icon, #displayorder, #url, #main_title').show();
			var main_title = $(this).data('maintitle');
			$('[name="main_title"]').val(main_title);
			var title = $(this).data('title');
			$('[name="title"]').val(title);
			var permission = $(this).data('permission');
			$('[name="permission"]').val(permission);
			var displayorder = $(this).data('displayorder');
			$('[name="displayorder"]').val(displayorder);
			var icon = $(this).data('icon');
			$('[name="icon"]').val(icon);
			var url = $(this).data('url');
			$('[name="url"]').val(url);
			var id = $(this).data('id');
			$('[name="id"]').val(id);
			var op = $(this).data('op');
			$('[name="op"]').val(op);
			var system = $(this).data('system');
			if (system) {
				$('#main_title, #icon, #url').hide();
				$('[name="permission"]').val('');
			}
			return false;
		});
		$('.editmain').click(function () {
			$('#myModal').modal('show');
			$('#main_title').show();
			$('#warning').html('');
			$('#myModalLabel').html('编辑主菜单');
			$('[name="main_title"]').attr('disabled', false);
			$('#title, #icon, #displayorder, #url').hide();
			var title = $(this).data('title');
			$('[name="main_title"]').val(title);
			var op = $(this).data('op');
			$('[name="op"]').val(op);
			var id = $(this).data('id');
			$('[name="id"]').val(id);
		});
		$('.add').click(function () {
			$('#myModal').modal('show');
			$('#warning').html('');
			$('#myModalLabel').html('添加子菜单');
			$('[name="main_title"]').attr('disabled', true);
			$('#title, #icon, #displayorder, #url, #main_title').show();
			$('[name="title"], [name="displayorder"], [name="icon"], [name="url"], [name="permission"]').val('');
			var main_title = $(this).data('maintitle');
			$('[name="main_title"]').val(main_title);
			var op = $(this).data('op');
			$('[name="op"]').val(op);
			var pid = $(this).data('pid');
			$('[name="pid"]').val(pid);
		});
		$('.addmain').click(function () {
			$('#myModal').modal('show');
			$('#main_title').show();
			$('#warning').html('');
			$('#myModalLabel').html('添加主菜单');
			$('[name="main_title"]').attr('disabled', false);
			$('[name="main_title"]').val('');
			$('#title, #icon, #displayorder, #url').hide();
			var op = $(this).data('op');
			$('[name="op"]').val(op);
		});
		$('.submit').click(function() {
			var title = $('[name="title"]').val();
			var main_title = $('[name="main_title"]').val();
			var displayorder = $('[name="displayorder"]').val();
			var icon = $('[name="icon"]').val();
			var url = $('[name="url"]').val();
			var id = $('[name="id"]').val();
			var op = $('[name="op"]').val();
			var pid = $('[name="pid"]').val();
			var permission = $('[name="permission"]').val();

			if (op == 'add') {
				if (title == '' || url == '') {
					$('#warning').html('&nbsp;&nbsp;&nbsp;&nbsp;子菜单名或链接不能为空');
					return false;
				}
			}
			if (op == 'addmain') {
				if (main_title == '') {
					$('#warning').html('&nbsp;&nbsp;&nbsp;&nbsp;主菜单名不能为空');
					return false;
				}
			}
			if (op == 'editmain') {
				if (main_title == '') {
					$('#warning').html('&nbsp;&nbsp;&nbsp;&nbsp;主菜单名不能为空');
					return false;
				}
				title = main_title;
			}
			if (op == 'edit') {
				if (title == '' || url == '') {
					$('#warning').html('&nbsp;&nbsp;&nbsp;&nbsp;子菜单名或链接不能为空');
					return false;
				}
			}
			$.post("<?php  echo url('profile/deskmenu')?>", {'permission' : permission, 'id' : id, 'title' : title, 'displayorder' : displayorder, 'icon' : icon, 'url' : url, 'op' : op, 'pid' : pid, 'main_title' : main_title}, function (data) {
				var data = $.parseJSON(data);
				location.reload();
			});
			return false;
		});
		$('.delete').click(function() {
			var type = $(this).data('type');
			if (type == 'main') {
				if (!confirm('确认删除主菜单？删除主菜单后主菜单下的所有子菜单也将被删除.')) {
					return false;
				}
			} else {
				if (!confirm('确认删除此菜单？')) {
					return false;
				}
			}
			$.post("<?php  echo url('profile/deskmenu', array('op' => 'delete'))?>", {'id' : $(this).data('id'), 'type' : type}, function(data) {
				var data = $.parseJSON(data);
				if (data.errno === 0) {
					alert('删除失败');
				} else {
					location.reload();
				}
			});
		});
	});
	require(['util'], function(u){
		$('.input-group').on('click', '.showIconDialog', function(){
			var icon = $(this).parent().prev().prev();
			u.iconBrowser(function(ico){
				icon.val(ico);
			});
		});
	});
</script>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>