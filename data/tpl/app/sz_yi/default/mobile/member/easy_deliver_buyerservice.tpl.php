<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/easy_delivery_header', TEMPLATE_INCLUDEPATH)) : (include template('common/easy_delivery_header', TEMPLATE_INCLUDEPATH));?>
<body>
<link rel="stylesheet" href="../addons/sz_yi/static/css/index.css">
<link rel="stylesheet" href="../addons/sz_yi/static/css/style.css">
<style>
    .index_icon a{
        color: #ffffff;
    }
</style>
<header>
    <div class="logo"><img src="../addons/sz_yi/static/images/logo.png" alt=""></div>
    <div class="plane"><img src="../addons/sz_yi/static/images/plane.png" alt=""></div>
</header>
<div class="warp">
    <?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/banner', TEMPLATE_INCLUDEPATH)) : (include template('common/banner', TEMPLATE_INCLUDEPATH));?>
    <div class="map">
        <div class="index_title">
            <h2><span>——</span>买家服务<span>——</span></h2>
        </div>
        <div class="index_icon">
            <ul>
                <li class="green">
                    <a href="<?php  echo $this->createMobileUrl('member/easydeliver_order_manage')?>">
                        <img src="../addons/sz_yi/static/images/订单管理.png" alt="">
                        <p>订单管理</p>
                    </a>
                </li>
                <li class="yellow">
                    <a href="<?php  echo $this->createMobileUrl('order/easy_deliver_centralizedpackage')?>&a=main">
                        <img src="../addons/sz_yi/static/images/订单管理.png" alt="">
                        <p>集中打包</p>
                    </a>
                </li>
            </ul>
            <ul>
                <li class="blue">
                    <a href="<?php echo $_W['siteroot'].'/app/index.php?i='.$_W['uniacid'].'&c=entry&p=list&do=index&m=go_coupon'?>">
                        <img src="../addons/sz_yi/static/images/优惠卷.png" alt="">
                        <p>卡卷管理</p>
                    </a>
                </li>
                <li class="red">
                    <a href="<?php  echo $this->createMobileUrl('member/center')?>">
                        <img src="../addons/sz_yi/static/images/index_05.png" alt="">
                        <p>个人中心</p>
                    </a>
                </li>

            </ul>
        </div>
    </div>

</div>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/easy_deliver_footer', TEMPLATE_INCLUDEPATH)) : (include template('common/easy_deliver_footer', TEMPLATE_INCLUDEPATH));?>
<script src="../addons/sz_yi/static/js/easySlider.js"></script>
<script>
    $(function() {
        $("#slider").easySlider( {
            slideSpeed: 500,
            autoSlide: true,
            paginationSpacing: "15px",
            paginationDiameter: "10px",
            paginationPositionFromBottom: "0px",
            slidesClass: ".slides",
            controlsClass: ".controls",
            paginationClass: ".pagination"
        });
    });


    function b(){
        t = parseInt(x.css('top'));
        y.css('top','19px');
        x.animate({top: t - 19 + 'px'},'slow');	//19为每个li的高度
        if(Math.abs(t) == h-19){ //19为每个li的高度
            y.animate({top:'0px'},'slow');
            z=x;
            x=y;
            y=z;
        }
        setTimeout(b,3000);//滚动间隔时间 现在是3秒
    }
    $(document).ready(function(){
        $('.swap').html($('.news_li').html());
        x = $('.news_li');
        y = $('.swap');
        h = $('.news_li li').length * 19; //19为每个li的高度
        setTimeout(b,3000);//滚动间隔时间 现在是3秒

    })
</script>
</body>
</html>