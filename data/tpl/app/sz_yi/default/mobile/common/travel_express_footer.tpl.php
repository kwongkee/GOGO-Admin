<?php defined('IN_IA') or exit('Access Denied');?><div id="hui-footer">
    <a href="<?php  echo $this->createMobileUrl("member/travel_express")?>" id="nav-home">
        <div class="hui-footer-icons hui-icons-home" style="position: relative; top: 8px; left: 26px;"></div>
        <div class="hui-footer-text">
        	<p>首页</p>
        	<p class="title-en">Home</p>
        </div>
    </a>
    <a href="<?php  echo $this->createMobileUrl("member/travel_express_user")?>" id="nav-my">
        <div class="hui-footer-icons hui-icons-my" style="position: relative; top: 6px; left: 30px;"></div>
        <div class="hui-footer-text">
	        <p>我的</p>
	        <p class="title-en">Mine</p>
    	</div>
    </a>
    <a href="https://im.7x24cc.com/phone_webChat.html?accountId=N000000014488&chatId=f6600c02-b23f-429f-baeb-c6209dbf6219&nickName=<?php  echo $_W['fans']['nickname'];?>" id="nav-call">
        <div class="hui-footer-icons hui-icons-msg" style="position: relative; top: 8px; left: 17px;"></div>
        <div class="hui-footer-text">
        	<p>在线客服</p>
        	<p class="title-en">online</p>
        </div>
    </a>
</div>