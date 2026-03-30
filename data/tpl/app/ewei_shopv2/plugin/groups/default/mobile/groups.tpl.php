<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<?php  $this->followBar()?>
<link rel="stylesheet" href="../addons/ewei_shopv2/plugin/groups/template/mobile/default/css/style.css" />
<div class='fui-page creditshop-index-page'>
	<?php  if(is_h5app()) { ?>
	<div class="fui-header">
		<div class="fui-header-left">
			<a class="back"></a>
		</div>
		<div class="title"><?php  echo m('plugin')->getName('groups')?></div>
		<div class="fui-header-right"></div>
	</div>
	<?php  } ?>
	<div class='fui-content navbar'>

		<div class='fui-swipe' data-transition="500" data-gap="1">
		    <div class='fui-swipe-wrapper'>
			<?php  if(is_array($advs)) { foreach($advs as $adv) { ?>
				<a class='fui-swipe-item' href="<?php  if(!empty($adv['link'])) { ?><?php  echo $adv['link'];?><?php  } else { ?>javascript:;<?php  } ?>">
					<img src="<?php  echo tomedia($adv['thumb'])?>" alt="<?php  echo $adv['advname'];?>" onerror="this.src='../addons/ewei_shopv2/static/images/nopic100.jpg'"/>
				</a>
			<?php  } } ?>
		    </div>
		    <div class='fui-swipe-page'></div>
		</div>
		<?php  if(count($category)>0) { ?>
		<div class="lynn_index_menu row">
			<?php  if(is_array($category)) { foreach($category as $cate) { ?>
			<a href="<?php  echo mobileUrl('groups/category', array('category'=>$cate['id']))?>">
				<img src="<?php  echo $cate['thumb'];?>" alt="<?php  echo $cate['name'];?>" onerror="this.src='../addons/ewei_shopv2/static/images/nopic100.jpg'">
				<p><?php  echo $cate['name'];?></p>
			</a>
			<?php  } } ?>
		</div>
		<?php  } ?>
		<div class="lynn_item"></div>
		<div class="lynn_index_rec">
			<p><i></i><span>正在热卖</span></p>
			<span>H O T S A L E</span>
		</div>
		<?php  if(count($recgoods)>0) { ?>
		<ul class="lynn_index_list_ul row">
			<?php  if(is_array($recgoods)) { foreach($recgoods as $item) { ?>
			<li class="lynn_index_list_li fui-list goods-list">
				<a href="<?php  echo mobileUrl('groups/goods', array('id'=>$item['id']))?>" class="external lynn_index_list_a fui-list-media">
					<img src="<?php  echo tomedia($item['thumb'])?>" alt="<?php  echo $item['title'];?>" onerror="this.src='../addons/ewei_shopv2/static/images/nopic100.jpg'">
				</a>
				<div class="lynn_index_list_info fui-list-inner" onclick="window.location.href='<?php  echo mobileUrl('groups/goods', array('id'=>$item['id']))?>';">
					<h3><?php  echo $item['title'];?></h3>
					<p><?php  echo $item['description'];?></p>
					<del>原价：¥<?php  echo $item['price'];?></del>
					<div class="lynn_index_list_team">
						<span class="lynn_index_list_team_left">
							<strong><i class="icon icon-group"></i></strong><?php  echo $item['groupnum'];?>人团 ¥<em><?php  echo $item['groupsprice'];?></em>/<?php  echo $item['goodsnum'];?><?php  if($item['units']) { ?><?php  echo $item['units'];?><?php  } else { ?>件<?php  } ?>
						</span>
						<a href="<?php  echo mobileUrl('groups/goods', array('id'=>$item['id']))?>" class="external lynn_index_list_team_a">去拼团 ></a>
					</div>
				</div>
			</li>
			<?php  } } ?>
		</ul>
		<?php  } else { ?>
		<div class="fui-message fui-message-popup in">
			<div class="icon ">
				<i class="icon icon-information"></i>
			</div>
			<div class="content">暂无任何活动</div>
		</div>
		<?php  } ?>

	</div>
</div>
<?php  $this->footerMenus()?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>