<?php defined('IN_IA') or exit('Access Denied');?><link rel="stylesheet" type="text/css" href="../addons/sz_yi/template/pc/default/static/css/footer.css">
<style>
    .cover-page-foot{
        position:relative;
        box-sizing: border-box;
        padding: 50px 0;
    }
    .foot-box{
        position: absolute;
        right:180px;
        bottom:-4px;
    }
     .foot-box img{width:100%;}
</style>
<div class="cover-page-foot fl wfs">
    <p class="subnav">
        <?php  if($this->yzShopSet['fmenu_name']) { ?>
            <?php  if(is_array($this->yzShopSet['fmenu_name'])) { foreach($this->yzShopSet['fmenu_name'] as $k => $v) { ?>
            <a target="_blank" href="<?php  echo $this->yzShopSet['fmenu_url'][$k]?>"><?php  echo $v;?></a>
            <?php  } } ?>
        <?php  } else { ?>
            <a target="_blank" href="<?php  echo $this->createMobileUrl('shop/index')?>"><?php  if($_W['uniacid'] == 18) { ?>Home<?php  } else { ?>首页<?php  } ?></a>
            <a target="_blank" href="<?php  echo $this->createMobileUrl('shop/list', array('order' => 'sales', 'by' => 'desc'))?>"><?php  if($_W['uniacid'] == 18) { ?>Products<?php  } else { ?>全部商品<?php  } ?></a>
            <?php  if($_GPC['i'] == 3) { ?>
            <a target="_blank" href="<?php  echo $this->createMobileUrl('shop/notice')?>"><?php  if($_W['uniacid'] == 18) { ?>Announcement<?php  } else { ?>店铺公告<?php  } ?></a>
            <?php  if($this->footer['commission']) { ?>
            <a target="_blank" href="<?php  echo $this->footer['commission']['url']?>"><?php  echo $this->footer['commission']['text']?></a>
            <?php  } ?>
            <?php  } ?>
            <a target="_blank" href="<?php  echo $this->createMobileUrl('order')?>"><?php  if($_W['uniacid'] == 18) { ?>Member Centre<?php  } else { ?>会员中心<?php  } ?></a>
        <?php  } ?>
    </p>
    <p class="copyright">
        <p style="text-align: center;">
            <span style="color: rgb(119, 119, 119); font-family: " helvetica="" font-size:="" text-align:="" background-color:=""><a style="box-sizing: border-box; background:none; color: rgb(119, 119, 119); text-decoration-line: none; font-size:14px;" href="http://www.gogo198.net/" target="__blank">佛山市钜銘商務資訊服務有限公司</a></span>
        </p>
        <?php  if($_W['uniacid']==18 || $_GPC['i']==18) { ?>
            <p style="text-align: center;" class="fot_18">
                <a href="http://beian.miit.gov.cn/" target="_blank" style="box-sizing: border-box; background:none; color: rgb(119, 119, 119); text-decoration-line: none; font-family:'';border-right:1px solid #ccc;padding-right:5px;" helvetica="" font-size:="16" text-align:="" white-space:="">&nbsp;粤ICP备09003656号-7</a><a href="https://www.beian.gov.cn/portal/registerSystemInfo?recordcode=44060502000493" target="_blank" style="box-sizing: border-box; background:none; color: rgb(119, 119, 119); text-decoration-line: none; font-family:'';padding-left:5px; " helvetica="" font-size:="16" text-align:="" white-space:="">京公网安备44060502000493号</a>
                <br>
                <span style="color: rgb(119, 119, 119); font-family: " helvetica="" font-size:="" text-align:="" background-color:=""></span><span style="color: rgb(119, 119, 119); font-family: " helvetica="" font-size:="" text-align:="" background-color:="">Copyright 2003 - 2021 購購網 版权所有</span>
            </p>
        <?php  } ?>
        
        <?php  if($_W['uniacid']==3 || $_GPC['i']==3) { ?>
            <p style="text-align: center;" class="fot_3">
                <a href="http://beian.miit.gov.cn/" target="_blank" style="box-sizing: border-box; background:none; color: rgb(119, 119, 119); text-decoration-line: none; font-family:'';border-right:1px solid #ccc;padding-right:5px;" helvetica="" font-size:="16" text-align:="" white-space:="">&nbsp;粤ICP备09003656号-11</a><a href="https://www.beian.gov.cn/portal/registerSystemInfo?recordcode=44060502000492" target="_blank" style="box-sizing: border-box; background:none; color: rgb(119, 119, 119); text-decoration-line: none; font-family:'';padding-left:5px; " helvetica="" font-size:="16" text-align:="" white-space:="">京公网安备44060502000492号</a>
                <br>
                <span style="color: rgb(119, 119, 119); font-family: " helvetica="" font-size:="" text-align:="" background-color:=""></span><span style="color: rgb(119, 119, 119); font-family: " helvetica="" font-size:="" text-align:="" background-color:="">Copyright 2003 - 2021 購購網 版权所有</span>
            </p>
        <?php  } ?>
        <!--php echo htmlspecialchars_decode($this->yzShopSet['pccopyright'])-->
    </p>
    <div class="foot-box">
        <div class="bot-title" style="font-size:14px;">
        訪問我們的其他Gogo網站<br />Visit our other websites
        </div>
        <div class="bot-logo" style="width: 480px; display: flex;align-items:center;justify-content:space-evenly; padding: 0px;">
            <a href="http://www.gogo198.cn" target="_blank" style="float: left;margin: 10px 0 !important;width: 25%;height: 100% !important;text-align: left;">
                <img src="./pic/gogologo1.png"/>
                <p style="color: #1b7ab6;padding-left: 11px;font-size:12px;">
                    CROSS SHOPPING
                </p>
            </a> 
            <a href="http://www.gogo198.com" target="_blank" style="float: left;margin: 10px 0 !important;width: 25%;height: 100% !important;text-align: left;">
                <img src="./pic/gogologo2.png"/>
                <p style="color: #c6000b;padding-left: 21px;font-size:12px;">
                    GLOBAL SALES
                </p>
            </a> 
            <a href="http://www.gogo198.net/a/brand/brand_shop.html" target="_blank" style="float: left;margin: 10px 0 !important;width: 25%;height: 100% !important;text-align: left;">
                <img src="./pic/gogologo3.png"/>
                <p style="color: #de991e;padding-left: 15px;font-size:12px;">
                    TRAVEL EXPRESS
                </p>
            </a>
        </div>    
    </div>
    
    <?php  if($_GPC['i'] == 3) { ?>
    <!--<p><img src="/attachment/company/company.png" width="600" height="150" alt="公司简介"></p>-->
    <?php  } ?>
    
</div>
<div style="display:none;">
	<?php  echo htmlspecialchars_decode($this->yzShopSet['diycode'])?>
</div>
<script id='tpl_show_message' type='text/html'><div class="sweet-alert" style="display:block;">
        <%if type=='error'%><div class="icon error animateErrorIcon" style="display: block;"><span class="x-mark animateXMark"><span class="line left"></span><span class="line right"></span></span></div><%/if%>
        <%if type=='warning'%><div class="icon warning pulseWarning" style="display: block;"><span class="body pulseWarningIns"></span><span class="dot pulseWarningIns"></span></div><%/if%>
        <%if type=='success'%><div class="icon success animate" style="display: block;"><span class="line tip animateSuccessTip"></span><span class="line long animateSuccessLong"></span><div class="placeholder"></div><div class="fix"></div></div><%/if%>
        <div class="info"><%message%><%if url%><br><span>如果您的浏览器没有自动跳转请点击此处</span><%/if%></div>
        
        <div class="sub" 
             <%if url%>
                    onclick="location.href='<%url%>'"
             <%else%>
                    <%if js%>
                        onclick="<%js%>"
                    <%else%>
                        onclick="history.back()"
                    <%/if%>
             <%/if%>
             >
        <%if type=='success'%><div class="green">确认</div><%/if%>
        <%if type=='warning'%><div class="grey">确认</div><%/if%>
        <%if type=='error'%><div class="red">确认</div><%/if%>
        </div>
</script>
</body>
</html>