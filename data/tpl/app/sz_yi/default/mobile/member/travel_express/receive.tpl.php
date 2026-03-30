<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title>预报清单</title>
	<link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/hui.css" />
	<link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/travel_express/css/style.css" />
	<style>
        .hui-header h1{
            padding: 0px 38px 0px 0;
        }
        .hui-form-items-title {
            width: 35%;
        }
        .pic-remove{font-size: 10px;background: rgb(237, 45, 34);top: 0px;padding: 1px 3px;border-radius: 0;}
    </style>
</head>
<body>
<header class="hui-header">
    <div id="hui-back"></div>
    <h1>预报清单</h1>
</header>

<div class="hui-wrap">
    <div class="wrap-top">
        <div class="hui-footer-icons hui-icons-news top-icon" style="margin: 4px 10px 0 0;"></div>
        <div class="text-title" style="width: 45%;display: inline-block;">
            <p>请选择包裹类别</p>
            <p class="title-en text-space">Please select package category</p>
        </div>
        <button type="button" class="hui-button hui-button-large" style="width: 40%;display: inline-block;float: right;margin: 0;background-color: #1b7ab6;color: white;" onclick="hui.shwoBlackMasker();">查看拒收物品</button>
    </div>
    <form style="padding:0 10px;" class="hui-form" id="form1">
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>分类</p><p class="sizes">Category</p></div>
            <div class="hui-form-select">
                <select name="cates" id="selectCates">
                    <?php  if(is_array($cates)) { foreach($cates as $v) { ?>
                        <option value="<?php  echo $v['id'];?>"><?php  echo $v['name'];?></option>
                    <?php  } } ?>
                </select>
            </div>
        </div>
        <!-- <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>类别</p><p class="sizes">Category</p></div>
            <div class="hui-form-radios" id="childCates">
                
            </div>
        </div> -->
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>类别</p><p class="sizes">Category</p></div>
            <div class="hui-form-select">
                <select name="cates_c" id="childCates">
                </select>
            </div>
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>品牌(中文)</p><p class="sizes">Brand(Chinese)</p></div>
            <div class="hui-form-select">
                <select name="brand_cn" id="brand_cn">
                    <option value="0">其他（Others）</option>
                </select>
                <input type="text" class="hui-input" placeholder="" id="brand_cn_other" name="brand_cn_other" style="width: 85% !important;display: none;margin-top: 10px;" />
            </div>
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>品牌(英文)</p><p class="sizes">Brand (English)</p></div>
            <div class="hui-form-select" style="width: 100%;">
                <select name="brand_en" id="brand_en" disabled="true">
                    <option value="0">其他（Others）</option>
                </select>
                <input type="text" class="hui-input" placeholder="" id="brand_en_other" name="brand_en_other" style="width: 85% !important;display: none;margin-top: 10px;" />
            </div>
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>单号</p><p class="sizes">Product name</p></div>
            <div style="width: 100%;">
                <input type="text" class="hui-input" placeholder="" name="item_no" checkType="string" checkData="2,30" checkMsg="单号为2-30个字符" style="width: 85% !important;" />
                <span id="item_no_text" style="display: none;" class="font-red">购物单据上的订单号</span>
            </div> 
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>品名</p><p class="sizes">Product name</p></div>
            <div style="width: 100%;">
                <input type="text" class="hui-input" placeholder="" name="good_name" checkType="string" checkData="2,30" checkMsg="品名为2-30个字符" style="width: 85% !important;" />
                <span id="good_name_text" style="display: none;" class="font-red">服装类、鞋类、包类、手表、皮带等，要在加上“男士、女士、儿童”，如：男士衬衫、女士运动鞋、女士手提包、儿童上衣、男士手表等。套装类的，写“面霜套装、积木玩具套装、女士内衣套装”等</span>
            </div> 
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>物品系列/型号</p><p class="sizes">Item series/model</p></div>
            
            <div style="width: 100%;">
                <input type="text" class="hui-input" placeholder="" name="model" style="width: 85% !important;" />
                <span id="model_text" style="display: none;" class="font-red">奶粉写“婴幼儿”“成人”，拉杆箱（申报型号），运动鞋（申报男/女士，码数，型号），电子表（型号/款号，类型：石英/机械），厨房用具中的勺子，碗等等（申报材质），电子阅读器（申报型号，容量）、枕头（材质）。套装商品，要具体列明套装里面每个商品名称和规格，如护肤品套装写：“高光水200ml、面霜50ml、晚霜50ml”；防蚊液套装写：“防蚊液200ml*4”</span>
            </div>
            <!-- <div class="hui-form-select wrap-select">
                <select name="year">
                    <option value="杂货Groceries">牛奶粉Milk powder</option>
                    <option value="单品Single product">羊奶粉Goat milk powder</option>
                </select>
            </div> -->
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>材质/段数</p><p class="sizes" style="line-height: 14px;">Material / number of segments</p></div>
            
            <div style="width: 100%;">
                <input type="text" class="hui-input" placeholder="" name="material" style="width: 85% !important;" />
                <span id="material_text" style="display: none;" class="font-red">奶粉写“1段、2段、3段等”“全段”，拉杆箱（申报型号），运动鞋（申报男/女士，码数，型号），电子表（型号/款号，类型：石英/机械），厨房用具中的勺子，碗等等（申报材质），电子阅读器（申报型号，容量）、枕头（材质）</span>
            </div>
            <!-- <div class="hui-form-select wrap-select">
                <select name="year">
                    <option value="杂货Groceries">牛奶粉Milk powder</option>
                    <option value="单品Single product">羊奶粉Goat milk powder</option>
                </select>
            </div> -->
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>规格</p><p class="sizes">Specification</p></div>
            
            <div class="hui-form-select  wrap-select">
                <!-- <select name="year" style="margin-bottom: 10px;">
                    <option value="2010">900</option>
                    <option value="2011">1000</option>
                    <option value="2012">1.8</option>
                </select> -->
                
                <div style="width: 100%;">
                    <input type="number" class="hui-input" name="specs" style="width: 85% !important;margin-bottom: 10px;" />
                    <span id="specs_text" style="display: none;" class="font-red">手链/项链（多少克）、奶粉、食品、保健品、化妆品、洗护类用品必须填写。 如：200、150、60</span>
                </div>
            </div>
        </div>    
        <div class="hui-form-items">    
            <div class="hui-form-items-title"><p><span class="font-red">*</span>容量单位</p><p class="sizes">Capacity unit</p></div>
            <div class="hui-form-select  wrap-select">
                <select name="specs2" style="margin-bottom: 10px;">
                	<option value="克">克</option>
                    <option value="千克">千克</option>
                	<option value="升">升</option>
                    <option value="毫升">毫升</option>
                    <option value="台">台</option>
                    <option value="只">只</option>
                    <option value="张">张</option>
                    <option value="件">件</option>
                    <option value="支">支</option>
                    <option value="枝">枝</option>
                    <option value="根">根</option>
                    <option value="条">条</option>
                    <option value="把">把</option>
                    <option value="块">块</option>
                    <option value="卷">卷</option>
                    <option value="副">副</option>
                    <option value="片">片</option>
                    <option value="双">双</option>
                    <option value="对">对</option>
                    <option value="斤">斤</option>
                    <option value="码">码</option>
                    <option value="寸">寸</option>
                    <option value="粒">粒</option>
                    <option value="磅">磅</option>
                    <option value="无">无</option>
                </select>
            </div>
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>包装单位</p><p class="sizes">Packing unit</p></div>
            <div class="hui-form-select  wrap-select">
                <select name="specs3">
                    <option value="包">包</option>
                    <option value="支">支</option>
                    <option value="瓶">瓶</option>
                    <option value="罐">罐</option>
                    <option value="袋">袋</option>
                    <option value="套">套</option>
                    <option value="组">组</option>
                    <option value="箱">箱</option>
                    <option value="桶">桶</option>
                    <option value="盒">盒</option>
                    <option value="个">个</option>
                    <option value="无">无</option>
                </select>
            </div>
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>数量</p><p class="sizes">Quantity</p></div>
            <div style="padding:0 10px;">
                <div class="hui-number-box" min="1" max="100">
                    <div class="reduce">-</div>
                    <input type="number" name="num" value="1" />
                    <div class="add">+</div>
                </div>
            </div>
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>重量</p><p class="sizes">Weight</p></div>
            <input type="number" class="hui-input" placeholder="请填写商品总重量" name="weight" checkType="string" checkData="1,5" checkMsg="请填写商品总重量" style="width: 48% !important;" /><span style="height: 40px; line-height: 40px; margin-left: 5px;">KG</span>
        </div>
        <div class="hui-form-items">
            <div class="hui-form-items-title"><p><span class="font-red">*</span>上传商品图片</p><p class="sizes" style="line-height: 14px;">Upload product image</p></div>
            <div class="hui-wrap" id="imglists">
                <div id="hui-img-cuter-select" style="float: left;margin-bottom: 10px;">
                    <input id="uploadimg" type="file" name="file" multiple="multiple" accept="image/*" style="width: 52px;height: 52px;visibility: hidden;" />
                    <div id="hui-img-cuter-t1" onclick="uploads();" style="position: absolute;top: 0;left: 0;right: 0;">+</div>
                    <input type="hidden" name="imgfile[]" />
                </div>
                <div style="float: right;"><p><span class="font-red">*</span>请上传不少于两张的商品正面包装、商品规格的图片。</p>
                    <p class="title-en">Please upload at least two pictures of the front packaging and specifications of the product.</p>
                </div>
            </div>
        </div>
        <div style="padding:15px 8px; width: 30%; margin: 20px auto 64px;">
            <button type="button" class="hui-button hui-primary hui-fr blue" id="submitBtn" style="margin: 0;"><img src="../addons/sz_yi/static/travel_express/images/icon_06.png" alt="" class="button-pic">保存</button>
        </div>
    </form>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/travel_express_footer', TEMPLATE_INCLUDEPATH)) : (include template('common/travel_express_footer', TEMPLATE_INCLUDEPATH));?>
<script src="../addons/sz_yi/static/js/jquery.js" type="text/javascript" charset="utf-8"></script>
<script src="../addons/sz_yi/static/travel_express/js/hui.js" type="text/javascript" charset="utf-8"></script>
<script src="../addons/sz_yi/static/travel_express/js/hui-form.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript" src="../addons/sz_yi/static/travel_express/plug-ins/phoneswipe/photoswipe.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="../addons/sz_yi/static/travel_express/plug-ins/phoneswipe/photoswipe-ui-default.min.js" charset="UTF-8"></script>
<link rel="stylesheet" href="../addons/sz_yi/static/travel_express/plug-ins/phoneswipe/photoswipe.css" type="text/css" />
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container" style="overflow:visible;">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="关闭"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div> 
            </div>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>
<style>
    .table-list{width: 100%;}
    #hui-black-mask{background: white;}
    #hui-black-mask-content{
        left: 5%;
        top: 0;
        transform: unset;
        width: 90%;
        overflow-y: scroll;
        height: 100%;
        right: 5%;
    }
    #hui-black-close{color: black;}
</style>
<div id="hui-black-mask" style="display: none;">
	<div id="hui-black-mask-content">
		<div id="hui-black-action">
			<div id="hui-black-close"></div>
		</div>
		<table class="table-list">
            <tr style="background:#f7f7f7;">
                <td class="table-list-top">类别</td>
                <td class="table-list-top">名称(中文)</td>
                <td class="table-list-top table-list-right">名称(英文)</td>
              </tr>
            <?php  if(is_array($refuse_list)) { foreach($refuse_list as $v) { ?>
            <tr>
                <td><?php  if($v['type']==1) { ?>品牌<?php  } else { ?>物品<?php  } ?></td>
                <td><?php  echo $v['name'];?></td>
                <td class="table-list-right"><?php  echo $v['name_en'];?></td>
            </tr>
            <?php  } } ?>
        </table>
	</div>
</div>
<script type="text/javascript">
hui.formInit();
hui.blackMask();
hui.numberBox();
//默认获取第一个分类的子类目
getCatesChild(hui("#selectCates option:first-child").val());
hui('#selectCates').change(function(){  
    getCatesChild(hui(this).val())
    
});

hui("#childCates").change(function(){  
    getBrandData(hui(this).val())
});

hui("#brand_cn").change(function(){  
    var index = $("#brand_cn").get(0).selectedIndex;
    $("#brand_en").get(0).selectedIndex=index;
    if($(this).val()==0)
    {
        $("#brand_cn_other").show();
        $("#brand_en_other").show();
    }else{
        $("#brand_cn_other").hide();
        $("#brand_en_other").hide();
    }
});

hui('input').focusIn(function(){
    var name = $(this).attr("name");
    $("#"+name+"_text").show();
});

hui('input').focusOut(function(){
    var name = $(this).attr("name");
    $("#"+name+"_text").hide();
});

//获取品牌数据
function getBrandData(cate_id) {
    hui.ajax({
        url  : '<?php  echo $this->createMobileUrl("member/travel_express_receive")?>&op=get_brand&cate_id='+cate_id,
        beforeSend : function(){hui.loading();},
        complete   : function(){hui.closeLoading();},
        success : function(res){
            if(res.result.brand_data.length>0)
            {
                hui("#brand_cn").html('');
                var html = '';
                for (var i = 0; i < res.result.brand_data.length; i++) {
                    html += '<option value="'+res.result.brand_data[i].name+'">'+res.result.brand_data[i].name+'</option>';
                }
                html += '<option value="0">其他</option>';
                hui("#brand_cn").html(html);

                hui("#brand_en").html('');
                var html2 = '';
                for (var i = 0; i < res.result.brand_data.length; i++) {
                    html2 += '<option value="'+res.result.brand_data[i].name_en+'">'+res.result.brand_data[i].name_en+'</option>';
                }
                html2 += '<option value="0">其他</option>';
                hui("#brand_en").html(html2);

                $("#brand_cn_other").hide();
                $("#brand_en_other").hide();
            }else{
                hui("#brand_cn").html('');
                html = '<option value="0">其他</option>';
                hui("#brand_cn").html(html);

                hui("#brand_en").html('');
                html2 = '<option value="0">其他</option>';
                hui("#brand_en").html(html2);

                $("#brand_cn_other").show();
                $("#brand_en_other").show();
            }
        },
        'backType' : "JSON"
    });
}

//获取子分类
function getCatesChild(pid)
{
    hui.ajax({
        url  : '<?php  echo $this->createMobileUrl("member/travel_express_receive")?>&op=get_child&pid='+pid,
        beforeSend : function(){hui.loading();},
        complete   : function(){hui.closeLoading();},
        success : function(res){
            if(res.result.child.length>0)
            {
                hui("#childCates").html('');
                var html = '';
                // for (var i = 0; i < res.result.child.length; i++) {
                //     html += '<div style="margin-left: 10px;"><input type="checkbox" value="'+res.result.child[i].id+'" name="cates_c[]" id="c'+res.result.child[i].id+'" /><label for="c'+res.result.child[i].id+'">'+res.result.child[i].name+'</label></div>';
                // }
                for (var i = 0; i < res.result.child.length; i++) {
                    html += '<option value="'+res.result.child[i].id+'">'+res.result.child[i].name+'</option>';
                }
                hui("#childCates").html(html);
                //获取第一个品牌数据
                getBrandData(hui("#childCates option:first-child").val());
            }
        },
        'backType' : "JSON"
    });
}

function uploads() {
    $("#uploadimg").click();
}

$("#uploadimg").change(function(){
        var filesList = $("#uploadimg")[0].files;
        if(filesList.length==0){                  
            return false;
        }else{
            if(filesList.length>9)
            {
                hui.toast('最多只能上传9张图片!');
            }else{
                //开始上传                
                for (var i = 0; i < filesList.length; i++) {
                    var datas = new FormData();
                    datas.append('file', filesList[i]);
                    $.ajax({
                        url  : '<?php  echo $this->createMobileUrl("util/uploader")?>&op=travel',
                        type : 'POST',
                        dataType: "json",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data : datas,
                        beforeSend : function(){hui.loading('上传图片中...');},
                        complete   : function(){hui.closeLoading();},
                        success : function(res){
                            if(res.error==0)
                            {
                                html = '<div id="hui-img-cuter-select" style="float: left;margin-bottom: 10px;"><div class="hui-number-point hui-icons hui-icons-remove pic-remove" onclick="removeimg(this);"></div><img height="100%" width="100%" class="imgs" onclick="seeimgs();" src="'+res.url+'"><input type="hidden" name="imgfile[]" value="'+res.url+'" /></div>';
                                $("#imglists").prepend(html);
                            }
                        },
                        error : function(e){
                            console.log(JSON.stringify(e));
                            hui.toast('系统错误!');
                        },
                        
                    });

                }
            }
            return false;
        }
    });

function seeimgs() {
    var index = hui(this).index();
	previewImg(index);
}

function previewImg(index){
	var pswpElement = document.querySelectorAll('.pswp')[0];
	var items = [];
	//获取图片数据并填充近数组
	hui('.imgs').each(function(eimg){
		var imgObj = {src:eimg.getAttribute('src'), w:eimg.naturalWidth, h:eimg.naturalHeight};
		items.push(imgObj);
	});
	var options = {index: index};
	var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
	gallery.init();
}
function removeimg(obj) {
    obj = $(obj);
    hui.confirm('您确认要删除该图片吗？', ['取消','确定'], function(){
        obj.parent().remove();
    },function(){
    });
}

//表单元素数据收集演示
hui('#submitBtn').click(function(){

    var res = huiFormCheck('#form1');
    if(res){
        var data = hui.getFormData('#form1');
        if(data['brand_cn']=="0" && data['brand_cn_other']=="")
        {
            hui.toast('品牌(中文)不能为空!');
            return false;
        }
        if(data['brand_en']=="0" && data['brand_en_other']=="")
        {
            hui.toast('品牌(英文)不能为空!');
            return false;
        }
        if($(".imgs").length <= 1)
        {
            hui.toast('请上传至少两张商品图片！');
            return false;
        }
        
        hui.ajax({
                url  : '<?php  echo $this->createMobileUrl("member/travel_express_receive")?>&op=submit',
                type : 'POST',
                data : {brand_cn_other: data.brand_cn_other, brand_en_other: data.brand_en_other, good_name: data.good_name, model: data.model, material: data.material, specs: data.specs, specs2: data.specs2, specs3: data.specs3, num: data.num, weight: data.weight, imgfile: data.imgfile, cates: data.cates, cates_c: data.cates_c, brand_cn: data.brand_cn, brand_en: data.brand_en, item_no:data.item_no },
                beforeSend : function(){hui.loading();},
                complete   : function(){hui.closeLoading();},
                success : function(res){
                    hui.toast(res.result.msg);
                    if(res.status == 1)
                    {
                        setTimeout(function(){
                            window.location.href="<?php  echo $this->createMobileUrl('member/travel_express_list')?>";
                        }, 2000);
                    }
                    
                },
                error : function(e){
                    console.log(JSON.stringify(e));
                    hui.iconToast('系统错误', 'warn');
                },
                'backType' : "JSON"
        });
    }
    
});

</script>
</body>
</html>