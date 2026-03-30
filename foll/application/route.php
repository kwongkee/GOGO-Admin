<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

// Route::rule('login/','mobile/login/index');
Route::get('mobile/', 'mobile/index/index'); // 定义GET请求路由规则
Route::get('mobile/login', 'mobile/login/index');
Route::post('mobile/verif_login', 'mobile/login/verif_login');
Route::get('mobile/sendmsg', 'mobile/index/send');
Route::get('mobile/parking_info', 'mobile/index/parking_info');
Route::get('mobile/wechats', 'mobile/wechats/index');
//后台首页
Route::get('home/home', 'home/home/home');
Route::get('home/getUrls', 'home/home/getUrls');

//钱包列表
Route::get('wallet/wallet', 'home/wallet/index');
Route::get('wallet/edit', 'home/wallet/edit'); //钱包编辑
Route::get('wallet/edit', 'home/wallet/edit'); //钱包编辑
Route::get('wallet/password', 'home/wallet/password'); //密码修改

Route::get('wallet/add', 'home/wallet/add'); //测试添加数据

Route::get('order/orderlist', 'home/order/orderlist'); //订单管理AdvertingManagementEdit  订单列表
Route::post('order/orderlist', 'home/order/orderlist'); //订单管理  订单列表
Route::get('order/export', 'home/order/export'); //导出数据  显示页面
Route::get('order/getExecl', 'home/order/getExecl'); //导出数据
Route::get('order/sendEmail', 'home/order/sendEmail'); //发送电子邮件；

//预约管理
Route::get('set/appoin', 'home/appointment/set'); //预约设置
Route::post('set/postset', 'home/appointment/postSet'); //提交设置；
Route::get('list/lists', 'home/appointment/lists'); //预约列表

//服务管理
Route::get('shelves/service', 'home/service/shelves'); //服务上架
Route::post('shelves/service', 'home/service/shelves'); //服务上架
Route::get('shelves/list', 'home/service/lists'); //商品列表

//登录
Route::get('home/login', 'home/home/loginIn');
//登录确认
Route::get('home/logincheck', 'home/home/loginIncheck');
//退出登录
Route::get('home/loginout', 'home/home/loginOut');

// Route::get("mobile/srarch","mobile/index/searchRes");

//订单对账列表链接
Route::get('order/index', 'order/order/index'); //账单表
Route::get('order/order', 'order/order/order'); //账单表
Route::post('order/orderLists', 'order/order/orderList'); //账单列表
Route::get('order/orderLists', 'order/order/orderList'); //账单列表
Route::get('order/login', 'order/order/login'); //登录
Route::get('order/logout', 'order/order/logout'); //退出登录
Route::post('order/isLogin', 'order/order/isLogin');  //登录请求；

//api
Route::get('api/parking_backup','api/ParkingBackup/index');
Route::get('api/payking','api/Test/index'); // 测试路由  使用解码数据  2019-10-31

Route::post('api/agro/acceptAgroInfo', 'api/Agro/acceptAgroInfo'); //接受农商拍卡接口
Route::post('api/acceptOrderData', 'api/AcceptOrderData/acceptOrderData'); //接受设备订单数据
Route::post('api/acceptParkingData', 'api/AcceptParkingData/acceptParkingData'); //接受车位实时数据
Route::post('api/acceptLeavingTime', 'api/Leaving/acceptLeavingTime'); //离场时间
Route::post('api/acceptLeavingTime2', 'api/Leaving2/acceptLeavingTime2'); //离场时间
Route::post('api/pullParkingCardBindStatusApi', 'api/RequestDevApi/pullParkingCardBindStatusApi'); //发送绑卡信息
Route::post('api/pullOnlinePayStatusApi', 'api/RequestDevApi/pullOnlinePayStatusApi'); //发送支付信息
Route::post('api/GetParkingInfo/getParkCode', 'api/GetParkingInfo/getParkCode'); //返回平台编号
Route::any('api/accpetReceiptInfo', 'api/AcceptGzeportCallBack/accpetReceiptInfo'); //跨境备案
Route::any('api/orderExcepHandling', 'api/AcceptExcepInfo/orderExcepHandling');
Route::post('api/lights_up', 'api/RequestDevApi/lights_up'); //预付费付费通知亮灯
Route::get('api/wx_access_token', 'api/WxAccessToken/updateAccessToken'); //批量获取accesstoken更新
Route::post('api/wechat/template', 'api/Wechat/wechatTemplate'); //发送微信模板消息
Route::post('api/Violations/order', 'api/Violations/order'); //接收违法订单
Route::get('api/wechat/sendVioOrederTempl', 'api/Wechat/sendVioOrederTempl'); //绑定车牌后查找发送违规订单补缴通知
Route::post('api/sendemail/index', 'api/SendEmail/index'); //外部请求发送邮件api
Route::any('api/centralize/getLine', 'api/CentralizeInfo/getLine'); //每周定时获取线路-pfc
Route::any('api/centralize/getinfo', 'api/CentralizeInfo/getinfo'); //每周定时获取官网集运服务信息-pfc
Route::any('api/centralize/contrast', 'api/CentralizeInfo/contrast'); //每周定时判断皇家内页内容距上次爬到的是否不一样-pfc

/**api_v2版本**/
Route::post('api_v2/CouponPayCallBack', 'api_v2/CouponPayCallBack/index');
Route::post('api_v2/coupon/getCoupon','api_v2/Coupon/getCoupon');//获取所有优惠卷
Route::post('api_v2/coupon/receive','api_v2/Coupon/receive');//领取优惠卷
Route::post('api_v2/Coupon/getReceiveList','api_v2/Coupon/getReceiveList');//获取已领取优惠卷
Route::post('api_v2/Coupon/deductibleDiscountAmount','api_v2/Coupon/deductibleDiscountAmount');//计算优惠金额
Route::post('api_v2/Coupon/saveUseCoupon','api_v2/Coupon/saveUseCoupon');//插入已使用优惠卷
//Route::post("api/index","api/index/index");
//Route::post("api/test2","api/index/test2");
//Route::post("api/test1","api/index/test");

//总后台开始
Route::get('admin/index', 'admin/index/index');
Route::get('admin/dashboard', 'admin/index/dashboard');
Route::get('admin/businessAdminCharacterList', 'admin/BusinessAdminRole/businessAdminCharacterList');
Route::get('admin/businessAdminCharacterAdd', 'admin/BusinessAdminRole/businessAdminCharacterAdd');
Route::post('admin/businessAdminCharacterSave', 'admin/BusinessAdminRole/businessAdminCharacterSave');
Route::get('admin/businessAdminCharacterDel', 'admin/BusinessAdminRole/businessAdminCharacterDel');
Route::get('admin/businessAdminCharacterUpdate', 'admin/BusinessAdminRole/businessAdminCharacterUpdate');
Route::post('admin/businessAdminCharacterUpdate', 'admin/BusinessAdminRole/businessAdminCharacterUpdate');
Route::get('admin/BusinessAdminUserIndex', 'admin/BusinessAdminUser/BusinessAdminUserIndex');
Route::post('admin/BusinessAdminUserAdd', 'admin/BusinessAdminUser/BusinessAdminUserAdd');
Route::get('admin/BusinessAdminUserDelete', 'admin/BusinessAdminUser/BusinessAdminUserDelete');
Route::get('admin/BusinessAdminUserStatusUpdate', 'admin/BusinessAdminUser/BusinessAdminUserStatusUpdate');
Route::get('admin/BusinessAdminUserEdit', 'admin/BusinessAdminUser/BusinessAdminUserEdit');
Route::post('admin/BusinessAdminUserEdit', 'admin/BusinessAdminUser/BusinessAdminUserEdit');
Route::get('admin/adminAccountAdd', 'admin/AdminAccount/index');
Route::post('admin/AdminCharacterSave', 'admin/Admincharacterlist/AdminCharacterSave');
Route::get('admin/AdminCharacterEdit', 'admin/Admincharacterlist/AdminCharacterEdit');
Route::post('admin/AdminCharacterEdit', 'admin/Admincharacterlist/AdminCharacterEdit');
Route::get('admin/AdminCharacterDel', 'admin/Admincharacterlist/AdminCharacterDel');
Route::get('admin/goods_reg_index', 'admin/CustomDeclaration/goodsRegIndex'); //商品备案列表
Route::get('admin/check_goods_dec', 'admin/CustomDeclaration/checkGoodsDec'); //商品备案审核通过
Route::get('admin/goods_details', 'admin/CustomDeclaration/goodsDetails'); //商品备案商品详情
Route::get('admin/goods_detail_edit', 'admin/CustomDeclaration/goodsDetailEdit'); //商品备案商品详情
Route::post('admin/goods_detail_edit', 'admin/CustomDeclaration/goodsDetailEdit'); //商品备案商品详情
Route::get('admin/goods/elist_code','admin/CustomsSystemController/elistCodeManage');//正面清单代码管理
Route::get('admin/goods/code_file_upload','admin/CustomsSystemController/codeFileUpload');//相关基础代码文件上传view
Route::post('admin/goods/code_file_upload','admin/CustomsSystemController/uplCodeFile');//处理相关基础代码文件上传
Route::post('admin/goods/listcode_file_upload','admin/CustomsSystemController/uplListCodeFIle');//清单文件上传处理
Route::get('admin/goods/upload_goods_image','admin/CustomsSystemController/uplGoodsPictureFile');//商品图片上传
Route::post('admin/goods/upload_goods_image','admin/CustomsSystemController/uplGoodsPictureFile');//商品图片上传
Route::get('admin/goods/list','admin/CustomsSystemController/goodsTotalList');//总商品列表
Route::get('admin/goods/layuiAjaxGetGoodsInfo','admin/CustomsSystemController/layuiAjaxGetGoodsInfo');//layui异步获取商品列表
Route::post('admin/goods/add','admin/CustomsSystemController/loadGoods');//添加库存商品
Route::get('admin/goods/edit','admin/CustomsSystemController/editGoodsInfo');//编辑库存商品信息
Route::post('admin/goods/edit','admin/CustomsSystemController/editGoodsInfo');//编辑库存商品信息
Route::get('admin/goods/delstock','admin/CustomsSystemController/delStockGoods');//删除库存
Route::get('admin/goods/exportTotalGoods','admin/CustomsSystemController/exportTotalGoods');
Route::post('admin/goods/updateTotalGoodsFormImportExcel','admin/CustomsSystemController/updateTotalGoodsFormImportExcel');//商品导入更新
Route::get('admin/goods/goodsOtherPlatformLinkList','admin/CustomsSystemController/goodsOtherPlatformLinkList');//列出抓取的其他电商平台链接
Route::post('admin/customssystem/account_auth','admin/CustomsSystemController/account_auth');
Route::post('admin/goods/saveGoodsLink','admin/CustomsSystemController/saveGoodsLink');//保存确认选择的第三方平台商品链接
Route::get('admin/goods/shelf_index','admin/CustomsSystemController/goodsShelf');//商品上架
Route::get('admin/billrate/check_list','admin/InspectionManagement/billCheckRate');//提单查验率列表
Route::get('admin/billrate/grandtotal','admin/InspectionManagement/billGrandTotal');//商户提单查验率累积
Route::get('admin/billrate/set_warningvalue','admin/InspectionManagement/billCheckWarningValueList');//查验峰值设置
Route::post('admin/billrate/set_warningvalue','admin/InspectionManagement/setBillCheckWarningValue');//查验峰值设置
Route::get('admin/billuser/action','admin/InspectionManagement/disableUser');//超查验预警值值锁定用户
Route::get('admin/uploadBillCheckFileCondition','admin/InspectionManagement/uploadBillCheckFileCondition');//控制上传查验文件
Route::post('admin/uploadBillCheckFileCondition','admin/InspectionManagement/uploadBillCheckFileCondition');//控制上传查验文件
Route::get('admin/OrderDeliveryManage/manifestList','admin/OrderDeliveryManage/manifestList');//快递单查询失败提单列表
Route::get('admin/OrderDeliveryManage/expressOrderQueryFailList','admin/OrderDeliveryManage/expressOrderQueryFailList');//快递单查询失败提订单列表

Route::any('admin/extrustDecl/del_withdraw_info','admin/Entrustdecl/del_withdraw_info');//删除国内结算
Route::get('admin/goods/intelligentNuclearPrice','admin/CustomsSystemController/intelligentNuclearPrice');//商品智能核价
Route::post('admin/goods/recoIntelligentNuclearPrice','admin/CustomsSystemController/recoIntelligentNuclearPrice');//保存商品智能核价
Route::get('admin/goods/artificialPrice','admin/CustomsSystemController/artificialPrice');//人工核价
Route::get('admin/goods/goodsNuclearPriceHandle','admin/CustomsSystemController/goodsNuclearPriceHandle');//价格通过
Route::get('admin/goods/goodsNuclearPriceFailHandle','admin/CustomsSystemController/goodsNuclearPriceFailHandle');//价格拒绝
Route::post('admin/goods/uploadPriceFailGoodsFile','admin/CustomsSystemController/uploadPriceFailGoodsFile');//批量处理商品失败核价
Route::get('admin/order/orderDeclChangeIndex','admin/CustomsSystemController/orderDeclChangeIndex');//订单申报变更
Route::get('admin/order/getOrderList','admin/CustomsSystemController/getOrderList');//获取待审核变更订单列表
Route::post('admin/order/orderDeclChangeHandle','admin/CustomsSystemController/orderDeclChangeHandle');//订单申报变更
Route::get('admin/ExpressDeliveryCompany','admin/ExpressDeliveryCompany/index');//快递公司编码管理
Route::post('admin/ExpressDeliveryCompany/upload','admin/ExpressDeliveryCompany/upload');//上传快递公司编码
Route::get('admin/add_printdevice','admin/AddPrintDevice/index');//添加打印设备
Route::post('admin/add_printdevice/saveConfig','admin/AddPrintDevice/saveConfig');//保存打印配置
Route::get('admin/assign_device_express','admin/assignDeviceExpress/index');//分配设备和快递公司
Route::post('admin/assign_device_express/saveConfig','admin/assignDeviceExpress/saveConfig');//保存分配设备和快递公司配置
Route::get('admin/HscodeTariffSchedule/index','admin/HscodeTariffSchedule/index');//海关商品编码进出口税则管理
Route::get('admin/HscodeTariffSchedule/getList','admin/HscodeTariffSchedule/getList');//获取海关商品编码进出口税则管理
Route::get('admin/HscodeTariffSchedule/updateRate','admin/HscodeTariffSchedule/updateRate');//更新费率
Route::get('admin/HscodeTariffSchedule/importExcelFile','admin/HscodeTariffSchedule/importExcelFile');//上传海关商品编码表
Route::post('admin/HscodeTariffSchedule/saveUploadExcelFile','admin/HscodeTariffSchedule/saveUploadExcelFile');//保存上传海关商品编码表
Route::get('admin/HscodeTariffSchedule/getCodeRate','admin/HscodeTariffSchedule/getCodeRate');//获取hscode费率
Route::get('admin/GoodsImportType/index','admin/GoodsImportType/index');//商品进口类型建议
Route::get('admin/GoodsImportType/getGoodsList','admin/GoodsImportType/getGoodsList');//获取商品进口类型列表
Route::get('admin/HsCodeController/specialCategoryManage','admin/HsCodeController/specialCategoryManage/');//hscode特殊类别管理
Route::get('admin/HsCodeController/getSpecialCategory','admin/HsCodeController/getSpecialCategory/');//ajax获取hscode特殊类别管理
Route::post('admin/HsCodeController/addSpecialCategory','admin/HsCodeController/addSpecialCategory');//add hscode特殊类别
Route::post('admin/HsCodeController/editSpecialCategory','admin/HsCodeController/editSpecialCategory');//edit hscode特殊类别
Route::post('admin/HsCodeController/delSpecialCategory','admin/HsCodeController/delSpecialCategory');//删除hscode特殊类别
Route::get('admin/GoodsImportType/goodsFileUpload','admin/GoodsImportType/goodsFileUpload');//上传申报检测申报方式
Route::post('admin/GoodsImportType/saveUploadGoodsFile','admin/GoodsImportType/saveUploadGoodsFile');//保存上传检测文件
Route::get('admin/HsCodeController/hscodeRisk','admin/HsCodeController/hscodeRisk');//hscode自定义风险类型范围
Route::get('admin/HsCodeController/getHscodeRiskList','admin/HsCodeController/getHscodeRiskList');
Route::get('admin/HsCodeController/addHsCodeRisk','admin/HsCodeController/addHsCodeRisk');
Route::post('admin/HsCodeController/addHsCodeRisk','admin/HsCodeController/addHsCodeRisk');
Route::get('admin/HsCodeController/delCustomizeHsCodeRisk','admin/HsCodeController/delCustomizeHsCodeRisk');
Route::post('admin/HsCodeController/upCustomizeHsCodeRisk','admin/HsCodeController/upCustomizeHsCodeRisk');
Route::get('admin/customsSystem/phoneAddressattributionWhitelist','admin/CustomsSystemController/phoneAddressattributionWhitelist');//电话地址归属地白名单管理
Route::get('admin/WaybillDeclaredsEnterprisesManagement/index','admin/WaybillDeclaredsEnterprisesManagement/index');//物流申报企业管理
Route::get('admin/WaybillDeclaredsEnterprisesManagement/select','admin/WaybillDeclaredsEnterprisesManagement/select');//物流申报企业管理
Route::post('admin/WaybillDeclaredsEnterprisesManagement/create','admin/WaybillDeclaredsEnterprisesManagement/create');//添加物流申报企业
Route::post('admin/WaybillDeclaredsEnterprisesManagement/delete','admin/WaybillDeclaredsEnterprisesManagement/delete');//删除物流申报企业
Route::get('admin/WaybillDeclaredsEnterprisesManagement/add_license','admin/WaybillDeclaredsEnterprisesManagement/add_license');//添加物流申报企业的车牌
Route::get('admin/CommonAddressManage/addrList','admin/CommonAddressManage/addrList');//常用地址管理
Route::get('admin/CommonAddressManage/getAddrList','admin/CommonAddressManage/getAddrList');//ajax异步获取列表
Route::post('admin/CommonAddressManage/create','admin/CommonAddressManage/create');//创建
Route::get('admin/CommonAddressManage/delete','admin/CommonAddressManage/delete');//删除
Route::post('admin/SubjectConversion/save','admin/SubjectConversion/save');//添加申报主体转换
Route::get('admin/UserOrderHistoryExport/index','admin/UserOrderHistoryExport/index');//用户申报订单历史资料导出
Route::get('admin/UserOrderHistoryExport/getCity','admin/UserOrderHistoryExport/getCity');//获取城市
Route::get('admin/UserOrderHistoryExport/getArea','admin/UserOrderHistoryExport/getArea');//获取区
Route::post('admin/UserOrderHistoryExport/getDataList','admin/UserOrderHistoryExport/getDataList');//获取数据列表
Route::post('admin/UserOrderHistoryExport/export','admin/UserOrderHistoryExport/export');//导出数据列表
Route::get('admin/billladingordermerge/newbilllading','admin/BillLadingOrderMerge/newBillLading');//新增新合并提单信息
Route::get('admin/billladingordermerge/getBatchListByBillNum','admin/BillLadingOrderMerge/getBatchListByBillNum');//获取批次列表
Route::post('admin/billladingordermerge/merge','admin/BillLadingOrderMerge/merge');//合并操作

//出口风控管理 2022-04-02
Route::any('admin/exportRisk/config','admin/exportRisk/config');//数源配置
Route::any('admin/exportRisk/config_save','admin/exportRisk/config_save');//数源配置
Route::any('admin/exportRisk/adjust','admin/exportRisk/adjust');//总值调整管理-第次调整幅度
Route::any('admin/exportRisk/price_limit','admin/exportRisk/price_limit');//总值调整管理-商品单价限值
Route::any('admin/exportRisk/store_sales','admin/exportRisk/store_sales');//总值调整管理-店铺销量配置
Route::any('admin/exportRisk/import_info','admin/exportRisk/import_info');//归类通数据导入信息列表
Route::any('admin/exportRisk/hscode_info','admin/exportRisk/hscode_info');//归类通数据信息详情
Route::any('admin/exportRisk/import_excel','admin/exportRisk/import_excel');//归类通数据导入信息程序

// 运单管理 2021-09-14
Route::any('admin/waybill/numberassign','admin/Waybill/numberassign');//出口-运单号码分配主页
Route::any('admin/waybill/numberassign_list','admin/Waybill/numberassign_list');//出口-商家运单号码列表
Route::any('admin/waybill/number_add','admin/Waybill/number_add');//出口-添加商家运单号码

Route::any('admin/waybill/numberassign2','admin/Waybill/numberassign2');//进口-运单号码分配主页
Route::any('admin/waybill/numberassign_list2','admin/Waybill/numberassign_list2');//进口-商家运单号码列表
Route::any('admin/waybill/numberassign_add2','admin/Waybill/numberassign_add2');//进口-分配商家运单号
Route::any('admin/waybill/get_this_exp_way_num','admin/Waybill/getThisExpWayNum');//进口-查看该快递公司起始和末端运单号
Route::any('admin/waybill/numberassign_insert','admin/Waybill/numberassign_insert');//进口-导入快递公司运单号码
Route::any('admin/waybill/number_upload','admin/Waybill/number_upload');//进口-上传运单excel

// 申报主体 2019-10-29
Route::any('main/index', 'admin/Mainbody/index');
// 添加主体
Route::get('main/add', 'admin/Mainbody/add');
Route::get('main/edit', 'admin/Mainbody/edit');
Route::post('main/doadd', 'admin/Mainbody/doadd');
Route::post('main/doedit', 'admin/Mainbody/doedit');
Route::post('main/del', 'admin/Mainbody/del');



//  支付渠道配置 2020-03-09
Route::get('admin/paychannels/paylist','admin/Paychannels/paylist');
Route::get('admin/paychannels/payedit','admin/Paychannels/payedit');
Route::get('admin/paychannels/payadd','admin/Paychannels/payadd');
Route::post('admin/paychannels/paydoadd','admin/Paychannels/payDoadd');
Route::post('admin/paychannels/paydel','admin/Paychannels/paydel');


Route::get('admin/paychannels/merlist','admin/Paychannels/merlist');
Route::get('admin/paychannels/meredit','admin/Paychannels/meredit');
Route::post('admin/paychannels/merdoadd','admin/Paychannels/merDoadd');
Route::post('admin/paychannels/merdel','admin/Paychannels/merdel');



// 支付通道分配
Route::get('paychannel/index','admin/Paychannel/index');
Route::get('paychannel/add','admin/Paychannel/add');
Route::get('paychannel/edit','admin/Paychannel/edit');
Route::post('paychannel/doadd','admin/Paychannel/doadd');
Route::post('paychannel/doedit','admin/Paychannel/doedit');
Route::post('paychannel/del','admin/Paychannel/del');
// 获取支付渠道对应的商户号列表；单选；
Route::post('paychannel/getMer', 'admin/Paychannel/getMer');

/**海关申报系统平台管理route开始2018-11-26**/

/**
 * 添加二次对账用户
 * 2019-05-10
 */
Route::get('admin/merchant/add','admin/CustomsSystemController/add');
Route::post('admin/merchant/doAdd','admin/CustomsSystemController/doAdd');
Route::get('admin/merchant/edit','admin/CustomsSystemController/edit');
Route::post('admin/merchant/doEdit','admin/CustomsSystemController/doEdit');
Route::get('admin/merchant/show','admin/CustomsSystemController/show');
Route::post('admin/merchant/isopen','admin/CustomsSystemController/isopen');
Route::post('admin/merchant/doDel','admin/CustomsSystemController/doDel');// 删除数据

//权限菜单
Route::get('admin/merchant/menu','admin/CustomsSystemController/menu');
Route::post('admin/merchant/menu','admin/CustomsSystemController/menu');

//菜单管理
Route::get('admin/customssystem/menu_list', 'admin/CustomsSystemController/declMenuManage'); //申报系统菜单管理
Route::get('admin/customssystem/fetchmenulist', 'admin/CustomsSystemController/fetchMenuList'); //获取菜单列表
Route::get('admin/customssystem/declMenuCreate', 'admin/CustomsSystemController/declMenuCreate'); //添加菜单
Route::post('admin/customssystem/declMenuCreate', 'admin/CustomsSystemController/declMenuCreate'); //添加菜单
Route::get('admin/customssystem/declMenuRep', 'admin/CustomsSystemController/declMenuRep'); //编辑
Route::post('admin/customssystem/declMenuRep', 'admin/CustomsSystemController/declMenuRep'); //编辑菜单
Route::get('admin/customssystem/declMenuDel', 'admin/CustomsSystemController/declMenuDel'); //删除菜单
//角色管理
Route::get('admin/customssystem/declRoleInfo', 'admin/CustomsSystemController/declRoleInfo'); //角色管理
Route::get('admin/customssystem/declRoleAdd', 'admin/CustomsSystemController/declRoleAdd'); //添加角色
Route::post('admin/customssystem/declRoleAdd', 'admin/CustomsSystemController/declRoleAdd'); //添加角色
Route::any('admin/customssystem/getMenuList', 'admin/CustomsSystemController/getMenuList'); //获取菜单列表
Route::get('admin/customssystem/roleStatus', 'admin/CustomsSystemController/roleStatus'); //更新角色状态
Route::get('admin/customssystem/roleDel', 'admin/CustomsSystemController/roleDel'); //删除角色
Route::get('admin/customssystem/roleEdit', 'admin/CustomsSystemController/roleEdit'); //角色编辑
Route::post('admin/customssystem/roleEdit', 'admin/CustomsSystemController/roleEdit'); //角色编辑
//商家注册管理
Route::get('admin/customssystem/declUserManage', 'admin/CustomsSystemController/declUserManage'); //商户注册管理列表
Route::get('admin/customssystem/getDeclMerchant', 'admin/CustomsSystemController/getDeclMerchant'); //ajax商户注册管理列表
Route::any('admin/customssystem/settlementRate', 'admin/CustomsSystemController/settlementRate'); //国内结算费率
Route::any('admin/customssystem/setOffshoreRate', 'admin/CustomsSystemController/setOffshoreRate'); //离岸手续费
Route::any('admin/customssystem/setAccount', 'admin/CustomsSystemController/setAccount'); //配置商户的会计
Route::any('admin/customssystem/setFreight', 'admin/CustomsSystemController/setFreight'); //配置商户的拖车信息
Route::any('admin/customssystem/setSysApp', 'admin/CustomsSystemController/setSysApp'); //配置商户的系统应用信息
Route::any('admin/customssystem/setUser', 'admin/CustomsSystemController/setUser'); //配置商户的用户配置
Route::any('admin/customssystem/sysuser_list', 'admin/CustomsSystemController/sysuser_list'); //商户的用户配置
Route::any('admin/customssystem/sysuser_save', 'admin/CustomsSystemController/sysuser_save'); //用户配置添加
Route::any('admin/customssystem/add_merchant', 'admin/CustomsSystemController/add_merchant'); //添加商户（openid在填写手机号时获取）
Route::any('admin/customssystem/can_see_app', 'admin/CustomsSystemController/can_see_app'); //公众号应用（设置每个人都可见得应用）

Route::get('admin/customssystem/declUserVerif', 'admin/CustomsSystemController/declUserVerif'); //商户注册通过审核
Route::post('admin/customssystem/declUserVerif', 'admin/CustomsSystemController/declUserVerif'); //商户注册通过审核
Route::get('admin/customssystem/changeUserStatus', 'admin/CustomsSystemController/changeUserStatus'); //商户账户状态
Route::get('admin/customssystem/changeVerifIdcardFrequency','admin/CustomsSystemController/changeVerifIdcardFrequency');//变更验证次数
Route::get('admin/customssystem/charge_user_role','admin/CustomsSystemController/changeUserRole');//更改用户所属角色
Route::get('admin/customssystem/addOrderCustomsDiscount','admin/CustomsSystemController/addOrderCustomsDiscount');//添加订单申报折扣
Route::get('admin/MerchantServiceProvision/index','admin/MerchantServiceProvision/index');//商户(服务商)服务功能
Route::post('admin/MerchantServiceProvision/create','admin/MerchantServiceProvision/create');//新增商户(服务商)服务功能
Route::post('admin/MerchantServiceProvision/update','admin/MerchantServiceProvision/update');//更新商户(服务商)服务功能
Route::get('admin/MerchantServiceProvision/delete','admin/MerchantServiceProvision/delete');//删除商户(服务商)服务功能

// 额外费用配置  2020-03-11
Route::get('additional/index','admin/Additional/index');//额外费用
Route::get('additional/add','admin/Additional/add');//额外费用
Route::post('additional/doadd','admin/Additional/doadd');// 额外费用  新增；
Route::post('additional/del','admin/Additional/del');// 额外费用  删除；
Route::get('additional/getadd','admin/Additional/getadd');// 额外费用 获取新增表单
Route::get('additional/edit','admin/Additional/edit');// 额外费用 获取新增表单


// 分配更新公众号  2019-12-06
Route::post('admin/customssystem/distribution','admin/CustomsSystemController/distribution');

/**海关申报系统route结束**/

/*电子订单申报*/
 //2018-12-11
Route::get('admin/elec/bollist', 'admin/Electronicorder/bollist');
Route::get('admin/elec/eleclist', 'admin/Electronicorder/eleclist');
Route::get('admin/elec/get_this_batch_order', 'admin/Electronicorder/getThisBatchOrder');
Route::any('admin/elec/print_elec', 'admin/Electronicorder/printElec');
Route::get('admin/elec/declare', 'admin/Electronicorder/declares');
Route::post('admin/elec/returns', 'admin/Electronicorder/Returns'); // 退回操作
Route::get('admin/elec/export_excel', 'admin/Electronicorder/exproWaybillElist'); //导出清单
Route::get('admin/elec/schedules', 'admin/Electronicorder/schedules'); // 进度查询
Route::any('admin/elec/schedulesQuery', 'admin/Electronicorder/schedulesQuery'); // 进度查询
Route::get('admin/elec/expro_err', 'admin/Electronicorder/exproOrderDeErrMsg'); //导出错误申报
Route::get('admin/elec/expro_purch', 'admin/Electronicorder/exproOrderPurch'); //导出购买风险  2019-11-25
Route::get('admin/tax/export', 'admin/Electronicorder/taxExport'); //导出计算的税费
Route::get('admin/elec/test', 'admin/Electronicorder/test'); // 进度查询

//账户管理，2021.08.13
Route::any('admin/elec/mainland_account', 'admin/Electronicorder/mainlandAccount'); // 国内收款账户管理
Route::any('admin/elec/mainland_account_edit', 'admin/Electronicorder/mainlandAccountEdit');//审核
Route::any('admin/elec/offshore_account', 'admin/Electronicorder/offshoreAccount'); // 离岸收款账户管理
Route::any('admin/elec/offshore_account_edit', 'admin/Electronicorder/offshoreAccountEdit'); // 离岸收款账户审批
Route::any('admin/elec/offshore_account_document', 'admin/Electronicorder/offshoreAccountDocument'); // 离岸账户文件管理
Route::any('admin/elec/offshore_account_document_edit', 'admin/Electronicorder/offshoreAccountDocumentEdit'); // 离岸账户文件审批
Route::any('admin/elec/offshore_account_document_manage', 'admin/Electronicorder/offshoreAccountDocumentManage'); // 离岸账户文件管理

//Route::any('admin/elec/mainland_set_account', 'admin/Electronicorder/mainlandSetAccount'); // 国内收款账户管理
//Route::any('admin/elec/offshore_set_account', 'admin/Electronicorder/offshoreSetAccount'); // 离岸收款账户管理

//买卖管理
Route::any('admin/elec/diligence_approve', 'admin/Electronicorder/diligenceApprove'); // 买卖尽职审批
Route::any('admin/elec/buysell_approve', 'admin/Electronicorder/buysellApprove'); // 买卖配置审批
Route::any('admin/elec/buysell_approve_edit', 'admin/Electronicorder/buysellApproveEdit'); // 买卖配置审批

//商品管理
Route::any('admin/elec/tax_set', 'admin/Electronicorder/taxSet'); // 税号配置
Route::any('admin/elec/three_set', 'admin/Electronicorder/threeSet'); // 三涉配置

//交易管理
Route::any('admin/elec/shoporder_approve', 'admin/Electronicorder/shoporderApprove'); // 平台订单关联审批
Route::any('admin/elec/shoporder_approve_edit', 'admin/Electronicorder/shoporderApproveEdit'); // 平台订单关联审批审核
Route::any('admin/elec/document_approve', 'admin/Electronicorder/documentApprove'); // 单证主体关联审批
Route::any('admin/elec/document_approve_edit', 'admin/Electronicorder/documentApproveEdit'); // 单证主体关联审批审核

Route::get('admin/elec/yzerror', 'admin/Electronicorder/yzerror'); // 验证失败
Route::get('admin/elec/risk', 'admin/Electronicorder/riskshow');    // 购买风控      2019-11-20
Route::get('admin/elec/getRisk', 'admin/Electronicorder/getRiskd'); // 查看购买风控   2019-11-20
Route::post('admin/elec/okRisk', 'admin/Electronicorder/okRisk');   // 确认购买风控   2019-11-20
Route::get('admin/elec/dsb', 'admin/Electronicorder/dsb'); // 待申报
Route::get('admin/elec/ysb', 'admin/Electronicorder/ysb'); // 已申报
Route::get('admin/elec/editordersender','admin/Electronicorder/editOrderSender');//修改申报发货人信息
Route::post('admin/elec/saveeditordersender','admin/Electronicorder/saveEditOrderSender');//保存修改发货人信息
// 风控查看
Route::get('admin/elec/Risk','admin/Electronicorder/Risk');
Route::get('admin/elec/idCardVerifFail','admin/Electronicorder/idCardVerifFail');//身份验证失败列表
Route::get('admin/elec/idCardVerifConfirm','admin/Electronicorder/idCardVerifConfirm');//身份风控确认

//2021-10-28
Route::get('admin/elec/device','admin/Electronicorder/device');//设备管理列表
Route::any('admin/elec/addDevice','admin/Electronicorder/addDevice');//添加/编辑设备
Route::any('admin/elec/delDevice','admin/Electronicorder/delDevice');//删除设备
Route::get('admin/elec/express','admin/Electronicorder/express');//快递公司管理列表
Route::any('admin/elec/addExpress','admin/Electronicorder/addExpress');//添加/编辑快递公司
Route::any('admin/elec/delExpress','admin/Electronicorder/delExpress');//删除快递公司

// 获取产品列表
Route::get('admin/elec/getrisk','admin/Electronicorder/getrisk');
// 风控配置
Route::get('admin/risk/config','admin/Risk/Configs');
Route::get('admin/risk/edit','admin/Risk/Edit');
Route::post('admin/risk/store','admin/Risk/Store');
//买家信息防盗（买家验核）
Route::get('admin/MemberIdentityVerif/verif_list','admin/MemberIdentityVerif/verif_list');//管理列表
Route::post('admin/MemberIdentityVerif/loadUserInfoByExcel','admin/MemberIdentityVerif/loadUserInfoByExcel');//导入清单
Route::get('admin/MemberIdentityVerif/exportUserVerifInfo','admin/MemberIdentityVerif/exportUserVerifInfo');//导入清单
/*2019-03-19  财务预警配置
 * */
Route::get('admin/fina/config','admin/Finas/index');// 配置主页
Route::get('admin/fina/edit','admin/Finas/edit');   // 编辑预警配置
Route::post('admin/fina/store','admin/Finas/store');// 保存预警配置
Route::get('admin/fina/warninglist','admin/Finas/lists');//财务预警列表
/*电子订单申报结束*/

/* 会计管理开始 */
Route::get('admin/account/tax','admin/Account/tax');
Route::post('admin/account/tax_upload','admin/Account/tax_upload');
Route::get('admin/account/subject','admin/Account/subject');
Route::post('admin/account/subject_upload','admin/Account/subject_upload');
Route::any('admin/account/member','admin/Account/member');
Route::any('admin/account/set_member_to_account','admin/Account/set_member_to_account');
/* 会计管理结束 */

/**
 * 获取支付汇总账单
 */
Route::any('admin/fina/paycheck','admin/Finas/payCheck');

/*
 *  余额不足导致验证失败的数据列表
 *  2019-04-01
 */
Route::get('admin/Aliver/ali', 'admin/Aliver/ali');// 阿里验证失败列表
Route::get('admin/Aliver/helver', 'admin/Aliver/helver');// 支付企业验证失败列表
Route::post('admin/Aliver/store','admin/Aliver/store');// 处理重新提交验证；
Route::get('admin/Aliver/verlist', 'admin/Aliver/verlist');// 已验证列表
// end 结束

//物联网系统
Route::get('admin/iot/user', 'admin/IotSystemController/residentUser'); //常驻用户管理
Route::get('admin/iot/checkuser', 'admin/IotSystemController/checkUser'); //审核用户
Route::get('admin/iot/deluser', 'admin/IotSystemController/delUser'); //删除用户信息
Route::get('admin/iot/apisetting', 'admin/IotSystemController/devApiSetting'); //设备接口配置信息
Route::get('admin/iot/addapisetting', 'admin/IotSystemController/addApiSetting'); //add设备接口配置信息
Route::post('admin/iot/addapisetting', 'admin/IotSystemController/addApiSetting'); //save设备接口配置信息
Route::get('admin/iot/delapisetting', 'admin/IotSystemController/delApiSetting'); //del设备接口配置信息
Route::get('admin/iot/editapisetting', 'admin/IotSystemController/editApiSetting'); //edit设备接口配置信息
Route::post('admin/iot/editapisetting', 'admin/IotSystemController/editApiSetting'); //save edit设备接口配置信息
Route::get('admin/iot/devicelist', 'admin/IotSystemController/deviceManage'); //设备管理
Route::get('admin/iot/deviceadd', 'admin/IotSystemController/addDevice'); //添加设备页面
Route::post('admin/iot/deviceadd', 'admin/IotSystemController/addDevice'); //保存设备数据
Route::get('admin/iot/device_edit', 'admin/IotSystemController/editDevice'); //编辑设备页面
Route::post('admin/iot/device_edit', 'admin/IotSystemController/editDevice'); //保存编辑设备数据
Route::get('admin/iot/device_del', 'admin/IotSystemController/delDevice'); //删除设备数据
Route::get('admin/iot/visitlog', 'admin/IotSystemController/visitLog'); //访客日志
Route::get('admin/iot/devclass', 'admin/IotSystemController/devClass'); //设备分类
Route::get('admin/iot/add_devclass', 'admin/IotSystemController/addDevClass'); //添加设备分类
Route::post('admin/iot/add_devclass', 'admin/IotSystemController/addDevClass'); //添加设备分类
Route::get('admin/iot/modifyDevClass', 'admin/IotSystemController/modifyDevClass'); //更改分类信息
Route::post('admin/iot/modifyDevClass', 'admin/IotSystemController/modifyDevClass'); //更改分类信息
Route::get('admin/iot/delDevClass', 'admin/IotSystemController/delDevClass'); //删除设备分类信息
Route::get('admin/iot/paysetting', 'admin/IotSystemController/paySetting'); //访客邀请支付费用设置
Route::post('admin/iot/paysetting', 'admin/IotSystemController/paySetting'); //访客邀请支付费用设置
//总后台结束

/*
 * 停车平台对账		开始
 */
//对账管理
Route::get('admin/ftpFileUrl', 'admin/AddDataFile/ftpFileUrl');
Route::get('upload/test', 'admin/Uploadfile/test');
Route::post('upload/test', 'admin/Uploadfile/test');
//定时执行下载对账文件任务; 2018-08-06
Route::get('upload/file', 'admin/Uploadfile/index');
Route::post('upload/file', 'admin/Uploadfile/index');
//解析对账文件 2018-08-07
Route::get('upload/analysis', 'admin/Uploadfile/Analysis');
Route::post('upload/analysis', 'admin/Uploadfile/Analysis');
//获取平台订单与上游订单对账    2018-08-31
Route::get('upload/Reconciliation', 'admin/Uploadfile/Reconciliation');
Route::post('upload/Reconciliation', 'admin/Uploadfile/Reconciliation');

//银企对账  2018-07-31
Route::get('admin/reconci', 'admin/Reconciliations/index');
Route::post('admin/reconci', 'admin/Reconciliations/index');
/*
 * 停车平台对账		结束
 */
/*
 * 总后台月卡审核
 */
Route::get('admin/monthly_index', 'admin/MonthlyCard/monthlyIndex');
Route::get('admin/update_month_check', 'admin/MonthlyCard/isMonthCheck'); //更新审核状态

//广告管理
Route::get('admin/adminAdverting', 'admin/adminAdverting/index');
Route::get('admin/AdminAdvertingAdd', 'admin/adminAdverting/AdminAdvertingAdd');
Route::get('admin/AdminAdvertingEdit', 'admin/adminAdverting/AdminAdvertingEdit');
Route::get('admin/AdminAdvertingDel', 'admin/adminAdverting/AdminAdvertingDel');
Route::get('admin/videoAdd', 'admin/adminAdverting/videoAdd');
Route::post('admin/adminAdverting', 'admin/adminAdverting/index');
Route::post('admin/AdminAdvertingSaves', 'admin/adminAdverting/AdminAdvertingSaves');
Route::post('admin/AdminAdvertingCheck', 'admin/adminAdverting/AdminAdvertingCheck');
Route::post('admin/AdminAdvertingGetMod', 'admin/adminAdverting/AdminAdvertingGetMod');
Route::post('admin/AdminAdvertingEdits', 'admin/adminAdverting/AdminAdvertingEdits');
Route::post('admin/change_status', 'admin/adminAdverting/change_status');
Route::post('admin/video_upload', 'admin/adminAdverting/video_upload');
Route::get('admin/AdminVideo', 'admin/AdminVideo/index');
Route::get('admin/AdvertingManagement', 'admin/AdvertingManagement/index');
Route::get('admin/AdvertingManagementAdd', 'admin/AdvertingManagement/add');
Route::get('admin/AdvertingInvoice', 'admin/AdvertingManagement/invoice');
Route::post('admin/AdvertingInvoiceStatus', 'admin/AdvertingManagement/invoiceStatus');
Route::post('admin/make_invoice', 'admin/AdvertingManagement/make_invoice'); //开发票
Route::post('admin/AdvertingManagementEdit', 'admin/AdvertingManagement/upload_edit'); //广告编辑
Route::post('admin/AdvertingManagementSave', 'admin/AdvertingManagement/upload_save'); //广告上传
Route::get('admin/AdvertingManagementEdit', 'admin/AdvertingManagement/edit');
Route::post('admin/AdvertingManagementDel', 'admin/AdvertingManagement/del');
Route::get('admin/AdvertingMaterial', 'admin/AdvertingManagement/material');
Route::post('admin/AdvertingMaterialSave', 'admin/AdvertingManagement/material_save');
Route::post('admin/AdminVideoUploads', 'admin/AdminVideo/video');

//后台对账管理
Route::get('admin/adminApiFortune', 'admin/AddDataFile/apiFortune'); //丰端祥接口
Route::get('admin/FortuneAccounts', 'admin/AddDataFile/FortuneAccounts'); //丰端祥接口生成对账文件
Route::get('admin/SenseAccounts', 'admin/AddDataFile/SenseAccounts'); //银联无感接口生成对账文件
Route::get('admin/BankAccounts', 'admin/AddDataFile/BankAccounts'); //农商银行接口生成对账文件
Route::get('admin/PaymentAccounts', 'admin/AddDataFile/PaymentAccounts'); //农商银行接口生成对账文件
Route::get('admin/adminApiSense', 'admin/AddDataFile/apiSense'); //银联无感接口
Route::get('admin/adminApiBank', 'admin/AddDataFile/apiBank'); //农商银行
Route::get('admin/adminApiPayment', 'admin/AddDataFile/apiPayment'); //聚合支付
Route::get('admin/get_file_info', 'admin/AddDataFile/get_file_info'); //丰瑞祥接口请求生成文件
Route::get('admin/adminsendEmail', 'admin/AddDataFile/sendEmail'); //测试邮箱发送

/**
 * 后台身份验证  2019-04-28
 */
Route::get('admin/Vertify/ali','admin/Vertify/ali');
Route::post('admin/Vertify/DoAli','admin/Vertify/DoAli');



//把对账用链接商户
Route::get('admin/businessLink', 'admin/AddDataFile/businessLink');
//把对账接口链接发给超级管理员
Route::get('admin/apiLink', 'admin/AddDataFile/apiLink');
//商户对账
Route::get('admin/business', 'admin/AddDataFile/business');
Route::post('admin/business', 'admin/AddDataFile/business');
//核对对账，把单边账改成平账
Route::get('admin/ReviseAccounts', 'admin/AddDataFile/ReviseAccounts');

//平台账户管理 菜单列表
Route::get('admin/businessAdminMenuList', 'admin/BusinessAdminMenuList/menuList');
Route::get('admin/businessAdminMenuEdit', 'admin/BusinessAdminMenuList/menuEdit');
Route::post('admin/businessAdminMenuEdit', 'admin/BusinessAdminMenuList/menuEdit');
Route::get('admin/businessAdminMenuAdd', 'admin/BusinessAdminMenuList/menuAdd');
Route::post('admin/businessAdminMenuAdd', 'admin/BusinessAdminMenuList/menuAdd');
Route::get('admin/businessAdminMenuDelete', 'admin/BusinessAdminMenuList/menuDelete');

Route::get('admin/login', 'admin/login/index');
Route::get('admin/sendcode', 'admin/login/sendCode');
Route::post('admin/loginUserVerif', 'admin/login/loginUserVerif');
Route::get('admin/wxqrlogin', 'admin/login/wxqrlogin');//微信扫码登录
Route::get('admin/wxlogin', 'admin/login/wxlogin');//微信授权登录
Route::get('admin/info', 'admin/index/info');
Route::get('admin/order', 'admin/order/index');
Route::get('admin/order/read', 'admin/order/read');
Route::get('admin/adver', 'admin/Advertising/index');
Route::post('admin/adver/save', 'admin/Advertising/save');
Route::get('admin/advertisingStatistics', 'admin/Advertising/advertisingStatistics');
Route::post('admin/AdminAccount/add', 'admin/AdminAccount/add');
Route::get('admin/AdminAccount/delete', 'admin/AdminAccount/delete');
Route::get('admin/AdminAccount/update', 'admin/AdminAccount/update');
Route::get('admin/AdminAccount/edit', 'admin/AdminAccount/edit');
Route::post('admin/AdminAccount/edit', 'admin/AdminAccount/edit');

//泊位管理后台

Route::get('index/member', 'index/Member/index'); //会员ui
Route::get('index/member_list', 'index/Member/member_list'); //会员列表
Route::get('index/userDeteil', 'index/Member/userDeteil'); //会员详情
Route::get('index/exproUser', 'index/Member/exproUser'); //导出用户信息
//Route::get("index/result","index/index/refultMoney");
Route::get('index/index', 'index/Index/index');
Route::get('index/login', 'index/Login/index');
Route::get('index/sendCode', 'index/Login/sendCode');
Route::post('index/verifLogin', 'index/Login/verifLogin');
Route::get('login/logout', 'index/Login/logout');
Route::get('index/role_manage', 'index/RoleManage/index');
Route::get('index/role_add', 'index/RoleManage/roleAdd');
Route::post('index/role_add', 'index/RoleManage/roleAdd');
Route::get('index/role_delete', 'index/RoleManage/roleDelete');
Route::get('index/user_manage', 'index/UserManage/index');
Route::get('index/user_create', 'index/UserManage/userCreate');
Route::post('index/user_create', 'index/UserManage/userCreate');
Route::get('index/charge_list', 'index/Charge/index');
Route::get('index/charge_add', 'index/Charge/addCharge');
Route::post('index/charge_save', 'index/Charge/saveCharge');
Route::post('index/charge_edit', 'index/Charge/editCharge');
Route::get('index/charge_edit', 'index/Charge/editCharge');
Route::get('index/charge_del', 'index/Charge/deleteCharge');
Route::post('index/sendChargeEMAIL', 'index/Charge/sendChargeEMAIL');
Route::get('index/park_add', 'index/ParkManage/parkAdd');
Route::post('index/sendParkNumberMail', 'index/ParkManage/sendParkNumberMail');
Route::post('index/park_save', 'index/ParkManage/parkSave');
Route::get('index/park_index', 'index/ParkManage/parkIndex');
Route::post('index/park_index', 'index/ParkManage/parkIndex');
Route::get('index/postion', 'index/ParkManage/postion');
Route::get('index/park_del', 'index/ParkManage/parkDel');
Route::get('index/Holiday_index', 'index/Holiday/Holiday_index');
Route::post('index/Holiday_add', 'index/Holiday/Holiday_add');
Route::get('index/order_index', 'index/Order/index'); //订单管理列表
Route::get('index/search_order', 'index/Order/SearchOrder'); //查找订单
Route::get('index/send_auth_code', 'index/Order/SendAuthCode'); //发送订单查看授权代码
Route::get('index/order_userinfo', 'index/Order/OrderUserInfo'); //订单用户信息
Route::get('index/order_user_authpay', 'index/Order/OrderUserAuthPay'); //查看用户授权信息
Route::get('index/order_expro', 'index/Order/exproOrder'); //导出订单信息
Route::post('order/delOrder', 'index/Order/delOrder'); //删除订单
Route::get('order/modifyOrder', 'index/Order/modifyOrder'); //修改订单数据ui
Route::post('order/modifyOrder', 'index/Order/modifyOrder'); //保存修改订单数据
Route::post('month_card/add', 'index/FullDayCard/addMonthCard'); //发行添加
Route::get('month_card/add', 'index/FullDayCard/addMonthCard'); //发行添加
Route::get('month_card/index', 'index/FullDayCard/monthIndex'); //发行列表
Route::get('month_card/del', 'index/FullDayCard/monthDel'); //发行删除
Route::get('month_card/add_cer', 'index/FullDayCard/addUploadCer'); //需要上传的资料ui
Route::post('month_card/add_cer', 'index/FullDayCard/addUploadCer'); //save添加需要上传的资料
Route::get('month_card/cer_del', 'index/FullDayCard/delCer');
Route::get('month_card/month_applyaccept', 'index/FullDayCard/monthApplyAccept'); //申请受理
Route::post('month_card/month_applycheck', 'index/FullDayCard/monthApplyCheck'); //通过拒绝受理
Route::get('month_card/month_review', 'index/FullDayCard/monthReview'); //受理审核
Route::get('month_card/month_status', 'index/FullDayCard/monthStatus'); //更新月卡状态monthStatus
Route::get('month_card/month_reviewdetail', 'index/FullDayCard/monthReviewDetail'); //月卡审核详情
Route::get('month_card/is_review', 'index/FullDayCard/isMonthReview'); //审核通过或失败
Route::get('month_card/getaddr', 'index/FullDayCard/getAddr'); //获取地址
Route::get('month_card/user_month_applylist', 'index/FullDayCard/userMonthApplyList'); //月卡申请管理
Route::post('month_card/add_aplpub', 'index/FullDayCard/addAplPub'); //月卡申请前置公告
Route::get('month_card/add_aplpub', 'index/FullDayCard/addAplPub'); //月卡申请前置公告
Route::get('month_card/upload_winresl', 'index/FullDayCard/uploadWinResl'); //上传中签结果
Route::post('month_card/upload_winresl', 'index/FullDayCard/uploadWinResl'); //上传中签结果
Route::get('month_card/win_list', 'index/FullDayCard/win_list'); //中签结果列表
Route::get('month_card/win_detail', 'index/FullDayCard/win_detail'); //中签结果列表
Route::get('month_card/pay_out_manage', 'index/FullDayCard/payOutManage'); //月卡逾期缴费管理
Route::get('month_card/pay_out_manage_info', 'index/FullDayCard/payOutManageInfo'); //月卡逾期缴费详情管理
Route::post('month_card/pay_outdelaytime', 'index/FullDayCard/payOutDelayTime'); //延期月卡付费时间
Route::get('month_card/subte_list', 'index/FullDayCard/subteList'); //替补列表
Route::get('month_card/update_alternateStu', 'index/FullDayCard/updateAlternateStu'); //替换候补
Route::get('month_card/exprotUserApply', 'index/FullDayCard/exprotUserApply'); //导出用户申请月卡资料
Route::get('month_card/month_cancellist', 'index/FullDayCard/monthCancelList'); //月卡注销管理
Route::post('month_card/month_cancelhandle', 'index/FullDayCard/monthCancelHandle'); //月卡注销更新
Route::get('month_card/month_pay', 'index/FullDayCard/monthPay'); //月卡支付管理
Route::get('month_card/exproPayInfo', 'index/FullDayCard/exproPayInfo'); //月卡支付导出
Route::get('index/user/del','index/Member/delUser');//删除用户
//后台卡券管理
Route::get('admincardvoucher/check', 'admin/AdminCardvoucher/AdminCardvoucherCheck');
Route::get('admincardvoucher/passorreject', 'admin/AdminCardvoucher/CardvoucherPassOrReject');
Route::post('admincardvoucher/passorreject', 'admin/AdminCardvoucher/CardvoucherPassOrReject');

//卡券管理
Route::get('cardvoucher/add', 'index/cardvoucher/CardvoucherAdd');
Route::post('cardvoucher/add', 'index/cardvoucher/CardvoucherAdd');
Route::post('cardvoucher/save', 'index/cardvoucher/CardvoucherSave');
Route::get('cardvoucher/check', 'index/cardvoucher/CardvoucherCheck');
Route::get('cardcouhcer/manage', 'index/cardvoucher/CardvoucherManage'); //卡卷ui
Route::get('cardcouhcer/get_coupon_list', 'index/cardvoucher/get_coupon_list'); //获取卡卷列表
Route::post('cardvoucher/pay_coupon', 'index/cardvoucher/payCoupon'); //发行支付2018-07-03
//商户后台广告管理
Route::get('advertising/index', 'index/advertising/index');
Route::post('advertising/index', 'index/advertising/index');
Route::get('advertising/add', 'index/advertising/add');
Route::get('advertising/invoice_list', 'index/advertising/invoice_list'); //发票列表
Route::get('advertising/invoice', 'index/advertising/invoice'); //发票申请
Route::post('advertising/invoice_apply', 'index/advertising/invoice_apply'); //发票申请
Route::post('advertising/save', 'index/advertising/save');
Route::post('advertising/selectData', 'index/advertising/selectData');
Route::post('advertising/is_payurl', 'index/advertising/is_payurl');
Route::post('advertising/add_order', 'index/advertising/add_order');

//2019-01-27 海关商户费率管理
Route::get('merchant/feelist', 'admin/Merchant/feelist'); // 费率列表
Route::get('merchant/feeedits', 'admin/Merchant/edits'); // 费率编辑
Route::post('merchant/feed', 'admin/Merchant/feeds'); // 确认编辑
// 清算管理
Route::get('Financed/Days', 'admin/Financed/Days');      // 每日对账
Route::any('Financed/getDays', 'admin/Financed/getDays'); // 获取每日对账数据
Route::get('Financed/Dayexports', 'admin/Financed/Dayexports'); // 导出每日对账数据，批次导出

Route::get('Financed/Months', 'admin/Financed/Months');      // 每月对账
Route::any('Financed/getMonths', 'admin/Financed/getMonths'); // 获取对账数据
Route::get('Financed/Monthsexports', 'admin/Financed/Monthsexports'); // 数据导出

/**
 *  2020-03-30
 *  获取周期结算，日、月对账；后台；
 */
Route::any('Financed/getSetts', 'admin/Financed/getSetts'); // 获取每日周期结算对账数据
Route::any('Financed/getMonthSetts', 'admin/Financed/getMonthSetts'); // 获取每日周期结算对账数据
Route::any('Financed/getAdditional', 'admin/Financed/getAdditional'); // 获取每日额外费用对账数据



/**
 *   2020-03-24
 *   周期结算功能
 */
Route::get('Settlement/index','admin/Settlement/index');
Route::get('Settlement/add','admin/Settlement/add');
Route::get('Settlement/getadd','admin/Settlement/getadd');
Route::get('Settlement/getBill','admin/Settlement/getBill');
Route::get('Settlement/getBatch','admin/Settlement/getBatch');
Route::get('Settlement/setinfo','admin/Settlement/setinfo');
Route::post('Settlement/doadd','admin/Settlement/doadd');
Route::post('Settlement/del','admin/Settlement/del');

// 队列任务；
Route::get('settlement/test','index/Settlement/queueTest');

/**
 * 清单日对账；
 * date：2020-01-16
 */
// 日对账
Route::get('Elist/Days','admin/Financed/ElistgetDays');
Route::any('Elist/getDays','admin/Financed/Elistget');
// 月对账
Route::get('Elist/Months','admin/Financed/ElistMonths');
Route::any('Elist/Monthsget','admin/Financed/ElistMonget');

/**
 * 运单，日对账，月对账获取；
 */
// 日对账
Route::get('Logis/Days','admin/Financed/LogisgetDays');
Route::any('Logis/getDays','admin/Financed/Logisget');
// 月对账
Route::get('Logis/Months','admin/Financed/LogisMonths');
Route::any('Logis/Monthsget','admin/Financed/LogisMonget');



Route::get('Financed/Faileds', 'admin/Financed/Faileds');    // 清算管理
Route::any('Financed/Liqus', 'admin/Financed/Liqus');        // 确认清算
Route::any('Financed/SeeLiqus', 'admin/Financed/SeeLiqus');  // 查看清算
Route::any('Financed/Remind', 'admin/Financed/Remind');      // 提醒清算

Route::get('Financed/Mistakens', 'admin/Financed/Mistakens'); // 有误待查
Route::any('Financed/Medits', 'admin/Financed/Medits'); // 获取数据
Route::post('Financed/setMedits', 'admin/Financed/setMedits'); // 设置数据

// 月总账单
Route::get('Financed/Monthly','admin/Financed/Monthly');// 获取月总账单列表
Route::any('Financed/getMonthlys','admin/Financed/getMonthlys');// 显示月总订单信息


/**
 * 二次对账数据
 */
Route::get('merchants/Days', 'admin/Merchants/Days');      // 每日对账
Route::any('merchants/getDays', 'admin/Merchants/getDays'); // 获取每日对账数据
Route::get('merchants/Dayexports', 'admin/Merchants/Dayexports'); // 导出每日对账数据，批次导出

Route::get('merchants/Mistakens', 'admin/Merchants/Mistakens'); // 有误待查
Route::any('merchants/Medits', 'admin/Merchants/Medits'); // 获取数据
Route::post('merchants/setMedits', 'admin/Merchants/setMedits'); // 设置数据


//总后台 试运营管理  2018-06-19
Route::get('Opera/reviewed', 'admin/Opera/reviewed'); //需审核列表
Route::post('Opera/check', 'admin/Opera/check');		//审核
Route::get('Opera/lists', 'admin/Opera/lists');		//运营列表

//试运营设置 2018-06-15
Route::get('trialopera/index', 'index/TrialOpera/index');
//设置运营
Route::post('trialopera/index', 'index/TrialOpera/index');
Route::post('trialopera/save', 'index/TrialOpera/save'); //保存设置
Route::post('trialopera/checkTitle', 'index/TrialOpera/checkTitle'); //查询该阶段名称是否存
//运营表
Route::get('trialopera/lists', 'index/TrialOpera/lists');
Route::post('trialopera/lists', 'index/TrialOpera/lists');
//编辑运营
Route::get('trialopera/editList', 'index/TrialOpera/editList');
Route::post('trialopera/edit', 'index/TrialOpera/edit'); //编辑
Route::post('trialopera/dels', 'index/TrialOpera/dels'); //删除
Route::get('index/trailorder', 'index/Order/trailOrderHandle'); //试运营订单处理
Route::get('trial_order/send_code', 'index/Order/sendCode'); //发送验证码
Route::post('trial_order/delTrailOrder', 'index/Order/delTrailOrder'); //删除订单
Route::post('trial_order/changeParkingStartTime', 'index/Order/changeParkingStartTime'); //变更停车订单入场时间
//跨境电商后台 2018-05-17
Route::get('business/index', 'declares/Business/index');
Route::get('account/login', 'declares/Account/login'); //登录
Route::get('account/logout', 'declares/Account/logout'); //退出
Route::post('account/logout', 'declares/Account/logout'); //退出
Route::get('account/sendCode', 'declares/Account/sendCode'); //发送验证码
Route::post('account/LoginChecked', 'declares/Account/LoginChecked'); //检查登录
//生成跨境电商对账文件
//生成对账文件  并发送电子邮箱   2018-07-17   -  2018-07-30  2020-01-02暂停发送
Route::get('account/Generatingbill', 'declares/Account/Generatingbill');
Route::post('account/Generatingbill', 'declares/Account/Generatingbill');

Route::get('home/index', 'declares/Home/index'); //首页
Route::get('home/welcome', 'declares/Home/welcome'); //首页
Route::get('admins/index', 'declares/Admins/index'); //管理员列表
Route::get('admins/add', 'declares/Admins/add'); //管理员列表
Route::post('admins/add', 'declares/Admins/add'); //管理员添加，编辑
Route::post('admins/save', 'declares/Admins/save'); //管理员保存
Route::post('admins/delete', 'declares/Admins/delete'); //管理员删除
Route::get('menu/index', 'declares/Menu/index'); //菜单列表
Route::post('menu/save', 'declares/Menu/save'); //保存菜单
Route::get('roles/index', 'declares/Roles/index'); //角色列表
Route::get('roles/add', 'declares/Roles/add'); //角色列表
Route::post('roles/add', 'declares/Roles/add'); //角色列表
Route::post('roles/save', 'declares/Roles/save'); //角色列表
Route::post('roles/deletes', 'declares/Roles/deletes'); //角色列表
Route::get('record/index', 'declares/Record/index'); //企业备案
Route::post('record/save', 'declares/Record/save'); //企业备案
//2018-07-16
Route::get('record/platformsave', 'declares/Record/platformsave'); //电商平台保存
Route::post('record/platformsave', 'declares/Record/platformsave'); //电商平台保存

Route::get('record/subjects', 'declares/Record/subjects'); //主体信息
Route::post('record/subjectsSave', 'declares/Record/subjectsSave'); //主体信息保存
Route::get('record/messages', 'declares/Record/messages'); //报文信息
Route::post('record/messagesSave', 'declares/Record/messagesSave'); //报文信息保存
Route::get('record/customs', 'declares/Record/customs'); //报关信息
Route::post('record/customsSave', 'declares/Record/customsSave'); //报关信息保存
Route::get('record/inspection', 'declares/Record/inspection'); //检验检疫
Route::post('record/inspectionSave', 'declares/Record/inspectionSave'); //检验检疫保存
Route::get('record/payConfig', 'declares/Record/payConfig'); //支付配置
Route::post('record/payConfigSave', 'declares/Record/payConfigSave'); //支付配置保存
Route::get('payment/test', 'declares/Payment/test'); //测试地址

Route::get('admins/Charge', 'declares/Admins/Charge'); //管理平台收费标准设置  2018-07-03
Route::post('admins/Chargesave', 'declares/Admins/Chargesave'); //管理平台收费标准设置  2018-07-03

/*
 * 人民币收银台回调地址
 */
Route::post('notify/returnUrl', 'declares/Notify/returnUrl'); //异步回调
Route::get('notify/returnUrl', 'declares/Notify/returnUrl'); //异步回调
Route::post('notify/noticeUrl', 'declares/Notify/noticeUrl'); //通知回调
Route::get('notify/noticeUrl', 'declares/Notify/noticeUrl'); //通知回调

/*
 * 帮付宝  报关文件提交  回调地址
 */
Route::get('notify/submitNotify', 'declares/Notify/submitNotify'); //通知回调
Route::post('notify/submitNotify', 'declares/Notify/submitNotify'); //通知回调

Route::post('payment/native', 'declares/Payment/nativeCashierOrder'); //人民币支付
Route::get('payment/native', 'declares/Payment/nativeCashierOrder'); //人民币支付
Route::post('payment/nativepay', 'declares/Payment/nativepay'); //人民币支付 ===
Route::get('payment/nativepay', 'declares/Payment/nativepay'); //人民币支付 ===
Route::post('payment/nativePost', 'declares/Payment/nativePost'); //人民币支付 ===
Route::get('payment/payDetail', 'declares/Payment/payDetail'); //支付订单明细
Route::post('payment/payDetail', 'declares/Payment/payDetail'); //支付订单明细

Route::get('payment/payDetailt', 'declares/Payment/payDetailt'); //支付订单明细
/*
 * 支付明细回调地址
 */
Route::get('notify/DetailNotify', 'declares/Notify/DetailNotify'); //支付订单明细  后台回调
Route::post('notify/DetailNotify', 'declares/Notify/DetailNotify'); //支付订单明细 后台回调
Route::get('notify/fileNotifyUrl', 'declares/Notify/fileNotifyUrl'); //支付订单明细   前台回调
Route::post('notify/fileNotifyUrl', 'declares/Notify/fileNotifyUrl'); //支付订单明细 前台回调

/*
 * 帮付宝
 * 2018-06-05
 */
Route::get('helpay/realnames', 'declares/Helpay/realnames'); //实名认证窗口
Route::post('helpay/RealName', 'declares/Helpay/RealName'); //实名认证  后台
Route::get('helpay/RealName', 'declares/Helpay/RealName'); //实名认证  后台
//报关文件提交 测试地址
Route::get('helpay/Ordersubmit', 'declares/Helpay/Ordersubmit');
Route::post('helpay/Ordersubmit', 'declares/Helpay/Ordersubmit');
//报关文件提交
Route::get('helpay/Ordersubmits', 'declares/Helpay/Ordersubmits');
Route::post('helpay/Ordersubmits', 'declares/Helpay/Ordersubmits');
//测试调试地址
Route::get('helpay/test', 'declares/Helpay/test');
//导出身份证验证数据  2018-08-13
Route::get('helpay/export', 'declares/Helpay/export');
Route::post('helpay/export', 'declares/Helpay/export');
//监控订单提交数  2018-08-13
Route::get('helpay/MonitorOrder', 'declares/Helpay/MonitorOrder');
Route::post('helpay/MonitorOrder', 'declares/Helpay/MonitorOrder');

//监控身份验证  2018-08-13
Route::get('helpay/MonitorRealName', 'declares/Helpay/MonitorRealName');
Route::post('helpay/MonitorRealName', 'declares/Helpay/MonitorRealName');

//监控身份验证  2018-08-13
Route::get('helpay/Import', 'declares/Helpay/ImportRealName');
Route::post('helpay/Import', 'declares/Helpay/ImportRealName');

/*
 * 帮付宝对账文件列表
 */
Route::get('Reconcil/Auth', 'declares/Reconcil/Auth');
Route::post('Reconcil/Auth', 'declares/Reconcil/Auth');
Route::post('Reconcil/Authuploads', 'declares/Reconcil/Authuploads'); //下载实名认证对账 2018-07-05
Route::get('Reconcil/Authuploads', 'declares/Reconcil/Authuploads'); //下载实名认证对账 2018-07-05
Route::post('Reconcil/getAuth', 'declares/Reconcil/getAuth');		//获取商户对账记录   2018-07-10
Route::get('Reconcil/getAuth', 'declares/Reconcil/getAuth');		//获取商户对账记录   2018-07-10
Route::post('Reconcil/getAuths', 'declares/Reconcil/getAuths');		//获取商户对账记录   2018-07-11
Route::get('Reconcil/getAuths', 'declares/Reconcil/getAuths');		//获取商户对账记录   2018-07-11
Route::post('Reconcil/Autherror', 'declares/Reconcil/Autherror');	//确认有误订单   实名认证！2018-07-09
Route::get('Reconcil/Autherror', 'declares/Reconcil/Autherror');	//确认有误订单   实名认证！2018-07-09
Route::get('Reconcil/Orders', 'declares/Reconcil/Orders');
//查看支付账单
Route::get('Reconcil/getOrder', 'declares/Reconcil/getOrder'); //查看支付账单 2018-07-16
Route::post('Reconcil/getOrder', 'declares/Reconcil/getOrder'); //查看支付账单 2018-07-16
//获取支付文件对账
Route::get('Reconcil/getOrders', 'declares/Reconcil/getOrders'); //查看支付账单 2018-07-16
Route::post('Reconcil/getOrders', 'declares/Reconcil/getOrders'); //查看支付账单 2018-07-16
//确认无误账单
Route::get('Reconcil/Orderuploads', 'declares/Reconcil/Orderuploads'); //查看支付账单 2018-07-16
Route::post('Reconcil/Orderuploads', 'declares/Reconcil/Orderuploads'); //查看支付账单 2018-07-16
//有误账单
Route::get('Reconcil/Ordererror', 'declares/Reconcil/Ordererror'); //查看支付账单 2018-07-16
Route::post('Reconcil/Ordererror', 'declares/Reconcil/Ordererror'); //查看支付账单 2018-07-16

//2018-07-14  备案商品
Route::get('Reconcil/Statistic', 'declares/Reconcil/Statistic'); //显示对账
Route::get('Reconcil/getStatistic', 'declares/Reconcil/getStatistic'); //查看对账
Route::post('Reconcil/getStatistic', 'declares/Reconcil/getStatistic'); //查看对账
//获取对账文件  2018-07-14
Route::get('Reconcil/getStatistics', 'declares/Reconcil/getStatistics'); //对账
Route::post('Reconcil/getStatistics', 'declares/Reconcil/getStatistics'); //对账
//确认对账
Route::get('Reconcil/Statisuploads', 'declares/Reconcil/Statisuploads'); //确认按钮
Route::post('Reconcil/Statisuploads', 'declares/Reconcil/Statisuploads'); //确认按钮
//有误对账 2018-07-16
Route::get('Reconcil/Statiserror', 'declares/Reconcil/Statiserror'); //有误按钮   备案订单
Route::post('Reconcil/Statiserror', 'declares/Reconcil/Statiserror'); //有误按钮   备案订单
//生成对账文件  2018-07-16  测试
Route::get('Reconcil/Generatingbill', 'declares/Reconcil/Generatingbill');
Route::post('Reconcil/Generatingbill', 'declares/Reconcil/Generatingbill');
/*
 * 跨境电商系统   平台对账
 */
//已经确认 2018-07-17
Route::get('Platform/index', 'declares/Platform/index');
Route::post('Platform/index', 'declares/Platform/index');
//查看对账信息  2018-07-17 15:23
Route::get('Platform/Seeinfo', 'declares/Platform/Seeinfo');
Route::post('Platform/Seeinfo', 'declares/Platform/Seeinfo');
//有误待核
Route::get('Platform/Mistaken', 'declares/Platform/Mistaken');
Route::post('Platform/Mistaken', 'declares/Platform/Mistaken');
//查看有误对账单 2018-07-18 10：30
Route::get('Platform/Mistakensee', 'declares/Platform/Mistakensee');
Route::post('Platform/Mistakensee', 'declares/Platform/Mistakensee');
//修改有误订单
Route::get('Platform/edit', 'declares/Platform/edit');
Route::post('Platform/edit', 'declares/Platform/edit');
//保存修改有误订单  2018-07-18 14：18
Route::get('Platform/editSave', 'declares/Platform/editSave');
Route::post('Platform/editSave', 'declares/Platform/editSave');
//管理员下载有误订单    2018-07-18 16：29
Route::get('Platform/upload', 'declares/Platform/upload');
Route::post('Platform/upload', 'declares/Platform/upload');
//已全部核查   2018-07-18 17:46
Route::get('Platform/queryOk', 'declares/Platform/queryOk');
Route::post('Platform/queryOk', 'declares/Platform/queryOk');
//平台应付  2018-07-20
Route::get('Platform/Payable', 'declares/Platform/Payable');
Route::post('Platform/Payable', 'declares/Platform/Payable');
//平台应付  2018-07-20
Route::get('Platform/Payables', 'declares/Platform/Payables');
Route::post('Platform/Payables', 'declares/Platform/Payables');
/*
 * 用户数据信息统计
 * 2018-07-24
 */
Route::get('Userinfo/index', 'declares/Userinfo/index');
Route::post('Userinfo/index', 'declares/Userinfo/index');

Route::get('Reconcil/Statistics', 'declares/Reconcil/Statistics'); //备案商品统计
Route::post('Reconcil/Statistics', 'declares/Reconcil/Statistics'); //备案商品统计
Route::get('Reconcil/Confirm', 'declares/Reconcil/Confirm'); //备案商品统计
Route::post('Reconcil/Confirms', 'declares/Reconcil/Confirms'); //确认发送邮箱 备案商品统计
Route::post('Reconcil/Mistaken', 'declares/Reconcil/Mistaken'); //商户点击  ‘有误’ 2018-06-27
Route::get('Reconcil/piciInfo', 'declares/Reconcil/piciInfo'); //批次详细  2018-06-27
Route::get('Reconcil/Confirma', 'declares/Reconcil/Confirma'); //备案商品统计   下载显示邮箱页面
Route::post('Reconcil/batch', 'declares/Reconcil/batch');	//批次导出  2018-06-29 确认导出
//电子订单   2018-07-19
Route::get('Reconcil/Copy', 'declares/Reconcil/Copy');		//订单结算  企业应付
//查看已对账 电子订单   2018-07-19
Route::get('Reconcil/getCopy', 'declares/Reconcil/getCopy');
Route::post('Reconcil/getCopy', 'declares/Reconcil/getCopy');
//获取对账 电子订单   2018-07-19
Route::get('Reconcil/getCopys', 'declares/Reconcil/getCopys');
Route::post('Reconcil/getCopys', 'declares/Reconcil/getCopys');
//获取对账  确认对账无误    2018-07-19
Route::get('Reconcil/Copyuploads', 'declares/Reconcil/Copyuploads');
Route::post('Reconcil/Copyuploads', 'declares/Reconcil/Copyuploads');
//获取对账  有误订单按钮    2018-07-19
Route::get('Reconcil/Copyerror', 'declares/Reconcil/Copyerror');
Route::post('Reconcil/Copyerror', 'declares/Reconcil/Copyerror');

Route::get('Reconcil/Checkdetail', 'declares/Reconcil/Checkdetail');	//订单详细  2018-07-01
Route::get('Reconcil/Checkbtn', 'declares/Reconcil/Checkbtn');	//确认订单按钮   2018-07-02
Route::post('Reconcil/Checkok', 'declares/Reconcil/Checkok');	//确认下载   2018-07-02
Route::get('Reconcil/Errors', 'declares/Reconcil/Errors');			//修改统计数据   2018-07-03

Route::get('Gzeport/gzeport_list', 'declares/Gzeport/gzeport_list'); //商品备案列表
Route::get('Gzeport/fillMessage', 'declares/Gzeport/fillMessage'); //商品备案信息填写
Route::get('Gzeport/getAllGoodsInfo', 'declares/Gzeport/getAllGoodsInfo'); //商品备案信息列表
Route::post('Gzeport/getPostData', 'declares/Gzeport/getPostData'); //商品备案提交
Route::get('Gzeport/goodsReg', 'declares/Gzeport/goodsReg'); //提交商品提交海关备案
Route::get('Gzeport/generateExcelAndDown', 'declares/Gzeport/generateExcelAndDown'); //下载商品备案信息
Route::get('electronicorder/order_index', 'declares/ElectronicOrder/orderIndex'); //电子订单列表
Route::post('electronicorder/upload', 'declares/ElectronicOrder/uploadOrderData'); //电子订单列表
Route::get('electronicorder/getOrderAllInfo', 'declares/ElectronicOrder/getOrderAllInfo'); //电子订单详细
Route::any('electronicorder/pushDeclarationOrder', 'declares/ElectronicOrder/pushDeclarationOrder'); //订单申报
Route::any('Gzeport/goods_pictrue_upload', 'declares/Gzeport/goodsPictureUpload'); //订单申报
Route::post('Gzeport/goods_picu_ploads', 'declares/Uploads/goodsPictureUpload'); // 图片上传  外部链接使用
Route::post('Gzeport/goods_picu_ploads/new', 'declares/Uploads/goodsPictureUpload2'); // 图片上传  外部链接使用2
Route::post('ordercustoms/sendElcOrder', 'declares/OrderCustoms/sendElcOrder'); //电子订单海关申报
Route::get('electronicorder/get_list', 'declares/ElectronicOrder/getList');
Route::get('electronicorder/export_way_excel', 'declares/ElectronicOrder/exportWayExcel'); //导出物流报文
Route::get('goodsregsuce/index', 'declares/GoodsRegSuce/index'); //上传备案成功的商品ui
Route::post('goodsregsuce/saveGoodsRegSuce', 'declares/GoodsRegSuce/saveGoodsRegSuce'); //上传备案成功的商品
Route::get('datamonitor/index', 'declares/DataMonitor/index'); //数据监控
Route::get('datamonitor/getCount', 'declares/DataMonitor/getCount'); //数据监控实时
Route::get('datamonitor/expro_errorsub', 'declares/DataMonitor/exproErrorSub'); //导出错误提交订单
/*
 * 监管
 */

Route::get('superadmin/home', 'superadmin/Home/index');
Route::get('superadmin/pool_list', 'superadmin/Home/pool_list');
Route::get('superadmin/setlotteryround', 'superadmin/Home/setLotteryRound'); //设置抽签轮次
Route::post('superadmin/savelotteryround', 'superadmin/Home/saveLotteryRound'); //保存设置抽签轮次
Route::get('superadmin/round_menagem', 'superadmin/Home/roundMenagem');
Route::get('superadmin/lottery_menagem', 'superadmin/Home/lotteryMenagem'); //中签管理
Route::post('superadmin/upload', 'superadmin/Home/upload'); //上传抽签结果
Route::get('superadmin/lottery_detail', 'superadmin/Home/lotteryDetail'); //中签详情
Route::get('superadmin/say_menage', 'superadmin/Home/sayMenage'); //意见管理
Route::post('superadmin/say_menage', 'superadmin/Home/sayMenage'); //意见回复
Route::get('superadmin/confirm_win', 'superadmin/Home/confirmWin'); //确定中签
/*
 * 客户信息查询系统
 * 2018-07-25
 */
Route::get('usersinfo/login', 'usersinfo/Account/login');
Route::post('usersinfo/login', 'usersinfo/Account/login');
//发送验证码验证
Route::get('usersinfo/sendCode', 'usersinfo/Account/sendCode');
Route::post('usersinfo/sendCode', 'usersinfo/Account/sendCode');
//登录验证
Route::get('usersinfo/LoginChecked', 'usersinfo/Account/LoginChecked');
Route::post('usersinfo/LoginChecked', 'usersinfo/Account/LoginChecked');
//退出登录
Route::get('usersinfo/logout', 'usersinfo/Account/logout');
Route::post('usersinfo/logout', 'usersinfo/Account/logout');
//主页菜单
Route::get('usersinfo/home', 'usersinfo/Home/index');
Route::post('usersinfo/home', 'usersinfo/Home/index');
//欢迎菜单
Route::get('usersinfo/welcome', 'usersinfo/Home/welcome');
Route::post('usersinfo/welcome', 'usersinfo/Home/welcome');
//跨境电商用户数据
Route::get('usersinfo/crossborder', 'usersinfo/Crossborder/index');
Route::post('usersinfo/crossborder', 'usersinfo/Crossborder/index');
//实名数据2018-07-26
Route::get('usersinfo/realname', 'usersinfo/Crossborder/Realname');
Route::post('usersinfo/realname', 'usersinfo/Crossborder/Realname');
//订单详细数据 2018-07-26
Route::get('usersinfo/orderedit', 'usersinfo/Crossborder/orderEdit');
Route::post('usersinfo/orderedit', 'usersinfo/Crossborder/orderEdit');
//跨境电商用户数据下载
Route::get('usersinfo/cuploads', 'usersinfo/Crossborder/Uploads');
Route::post('usersinfo/cuploads', 'usersinfo/Crossborder/Uploads');

//停车平台用户数据--------
Route::get('usersinfo/parking', 'usersinfo/Parking/index');
Route::post('usersinfo/parking', 'usersinfo/Parking/index');
//停车平台实名认证信息查询  2018-07-27
Route::get('usersinfo/seeauth', 'usersinfo/Parking/Seeauth');
Route::post('usersinfo/seeauth', 'usersinfo/Parking/Seeauth');
//停车平台驾驶认证信息查询  2018-07-27
Route::get('usersinfo/driving', 'usersinfo/Parking/Driving');
Route::post('usersinfo/driving', 'usersinfo/Parking/Driving');
//停车平台行驶认证信息查询  2018-07-27  Orderdetail
Route::get('usersinfo/travel', 'usersinfo/Parking/Travel');
Route::post('usersinfo/travel', 'usersinfo/Parking/Travel');
//停车平台订单详细   2018-07-27
Route::get('usersinfo/orderdetail', 'usersinfo/Parking/Orderdetail');
Route::post('usersinfo/orderdetail', 'usersinfo/Parking/Orderdetail');
//停车平台用户数据下载  2018-07-27
Route::get('usersinfo/uploads', 'usersinfo/Parking/Uploads');
Route::post('usersinfo/uploads', 'usersinfo/Parking/Uploads');

//测试专用  2018-08-10
Route::get('test/index', 'declares/Test/index');
Route::post('test/index', 'declares/Test/index');
Route::get('test/PostData', 'declares/Test/PostData');
Route::post('test/PostData', 'declares/Test/PostData');

Route::get('tests/index', 'admin/Test/index');
Route::post('tests/index', 'admin/Test/index');

/*
 * 停车平台银企对账
 * 2018-09-08
 */
//测试地址
Route::get('mreconcil/test', 'admin/Mreconcil/test');
Route::post('mreconcil/test', 'admin/Mreconcil/test');
//测试地址

Route::get('mreconcil/index', 'admin/Mreconcil/index');
Route::post('mreconcil/index', 'admin/Mreconcil/index');
//请求修改数据
Route::get('mreconcil/ajax', 'admin/Mreconcil/Ajax_update');
Route::post('mreconcil/ajax', 'admin/Mreconcil/Ajax_update');
//请求导出数据
Route::get('mreconcil/dowlon', 'admin/Mreconcil/Excels');
Route::post('mreconcil/dowlon', 'admin/Mreconcil/Excels');
/*
 * 一键平账
 */
Route::get('mreconcil/balance', 'admin/Mreconcil/OneBalance');
Route::post('mreconcil/balance', 'admin/Mreconcil/OneBalance');

/*
 * 商户订单对账
 */
Route::get('OrderReconcils/Analyaqs', 'index/OrderReconcil/Analyaqs');
Route::post('OrderReconcils/Analyaqs', 'index/OrderReconcil/Analyaqs');
Route::get('Reconcils/OkAnalyaqs', 'index/Reconcils/OkAnalyaqs');
Route::post('Reconcils/OkAnalyaqs', 'index/Reconcils/OkAnalyaqs');
//
Route::get('Reconcils/Analyaqs', 'index/Reconcils/Analyaqs');
Route::post('Reconcils/Analyaqs', 'index/Reconcils/Analyaqs');
Route::post('Reconcils/CheckAnalyaqs', 'index/Reconcils/CheckAnalyaqs'); //平账
//end  聚合支付
Route::get('OrderReconcils/Analywxs', 'index/OrderReconcil/Analywxs');
Route::post('OrderReconcils/Analywxs', 'index/OrderReconcil/Analywxs');
// end 微信免密支付
Route::get('OrderReconcils/AnalyUnions', 'index/OrderReconcil/AnalyUnions');
Route::post('OrderReconcils/AnalyUnions', 'index/OrderReconcil/AnalyUnions');
// end 银联免密
Route::get('OrderReconcils/AnalySdes', 'index/OrderReconcil/AnalySdes');
Route::post('OrderReconcils/AnalySdes', 'index/OrderReconcil/AnalySdes');
// end 农商行
//支付汇总start
Route::get('OrderReconcils/PaymentSummary', 'index/OrderReconcil/PaymentSummary');
Route::post('OrderReconcils/PaymentSummary', 'index/OrderReconcil/PaymentSummary');
//支付汇总end

//测试用  2018-10-10
Route::get('OrderReconcils/test', 'index/OrderReconcil/test');
Route::post('OrderReconcils/test', 'index/OrderReconcil/test');
//发送商户订单对账文件
Route::any('Reconcils/emailok', 'index/Reconcils/emailok');
Route::post('Reconcils/test', 'index/Reconcils/test');
Route::get('Reconcils/TestPahts', 'index/Reconcils/TestPahts');

//重新对账
Route::get('Reconcils/Reconciliations', 'index/Reconcils/Reconciliations');
Route::post('Reconcils/GoReconciliations', 'index/Reconcils/GoReconciliations');

// 2018-10-23  商户发票管理
Route::get('Invoice/index', 'index/Invoice/index');
Route::post('Invoice/index', 'index/Invoice/index');
// 发票订单详细信息
Route::get('Invoice/infos', 'index/Invoice/infos');
Route::post('Invoice/infos', 'index/Invoice/infos');
// 开票人信息
Route::get('Invoice/infoss', 'index/Invoice/infoss');
Route::post('Invoice/infoss', 'index/Invoice/infoss');
// 发票导出
Route::get('Invoice/espost', 'index/Invoice/espost');
Route::post('Invoice/espost', 'index/Invoice/espost');

// 聚合支付API START
Route::post('PaymentsApi/pay', 'payments/PaymentsApi/pay'); //支付请求入口
Route::get('PaymentsApi/pay', 'payments/PaymentsApi/pay');

Route::post('PaymentsApi/Refund', 'payments/PaymentsApi/Refund'); // 退款接口
Route::get('PaymentsApi/Refund', 'payments/PaymentsApi/Refund');

Route::get('notify/TgPay', 'payments/Notify/TgPay'); // 聚合支付回调
Route::post('notify/TgPay', 'payments/Notify/TgPay'); // 聚合支付回调

// 支付API END

// 新生支付
Route::get('newpay/index', 'payments/Newpay/index');
Route::post('newpay/index', 'payments/Newpay/index');
// 收银台 回调地址
Route::get('newpay/notify', 'payments/Newpay/Notify');
Route::post('newpay/notify', 'payments/Newpay/Notify');

// 商户收款回调
Route::get('notify/NewpayNotify', 'declares/Notify/NewpayNotify'); //通知回调
Route::post('notify/NewpayNotify', 'declares/Notify/NewpayNotify'); //通知回调

// 前端页面地址
Route::get('newpay/welcoom', 'payments/Newpay/welcoom');
Route::post('newpay/welcoom', 'payments/Newpay/welcoom');
// 新生支付END

//卡卷系统
Route::get('coupon/login', 'coupon/Login/index'); //登录
Route::get('coupon/signout', 'coupon/Login/signOut'); //登出
Route::get('coupon/send_login_code', 'coupon/Login/send_code'); //发送登录验证码
Route::post('coupon/check_login', 'coupon/Login/check_login'); //验证登录
Route::get('coupon/register', 'coupon/Register/index'); //注册
Route::post('coupon/reg', 'coupon/Register/reg_save'); //注册保存信息
Route::get('coupon/send_code', 'coupon/Register/send_code'); //发送验证码

Route::get('coupon/user/set_companyinfo', 'coupon/User/setCompanyInfo'); //配置用户信息ui
Route::post('coupon/user/set_companyinfo', 'coupon/User/setCompanyInfo'); //更新用户信息

Route::get('coupon/coupon_manage_list', 'coupon/Coupon/coupon_manage_list'); //优惠券管理
Route::get('coupon/coupon/create', 'coupon/Coupon/create'); //添加优惠券
Route::post('coupon/coupon/save', 'coupon/Coupon/save'); //保存添加
Route::post('coupon/coupon/upload_image', 'coupon/Coupon/upload_image'); //图片添加
Route::get('coupon/coupon_list', 'coupon/Coupon/coupon_list'); //卡卷列表数据
Route::get('coupon/coupon_detail', 'coupon/Coupon/coupon_detail'); //优惠券详情列表
Route::get('coupon/use_manage', 'coupon/Coupon/useManage'); //领取管理
Route::get('coupon/settleAccouManage', 'coupon/Coupon/settleAccouManage'); //结算管理
Route::get('coupon/close_rel', 'coupon/Coupon/closeRel'); //取消发布
Route::get('coupon/disable', 'coupon/Coupon/disable'); //更新优惠券状态
Route::get('coupon/delete_coupon', 'coupon/Coupon/deleteCoupon'); //删除发布
Route::get('coupon/confirm_push', 'coupon/Coupon/confirmPush'); //确认发布
Route::get('coupon/checkPayStatus', 'coupon/Coupon/checkPayStatus'); //检查支付状态
Route::get('coupon/coupon_verifsheet', 'coupon/Coupon/couponVerificationSheet'); //核销管理
Route::get('coupon/test', 'coupon/Test/index');
Route::post('coupon/test', 'coupon/Test/index');

//物联网手机端操作
Route::get('iot/wxcode', 'mobile/IotRegister/getWxCode'); //获取微信code
Route::get('iot/user/reg', 'mobile/IotRegister/index'); //常驻用户注册
Route::post('iot/user/reg', 'mobile/IotRegister/regStorage'); //用户注册保存
Route::any('iot/reg_success', 'mobile/IotRegister/regSuccessMsg'); //注册成功页面
Route::get('iot/login', 'mobile/IotLogin/index');//登录
Route::post('iot/login', 'mobile/IotLogin/login');//登录
Route::get('iot/sendcode', 'mobile/IotLogin/sendCode');//发送验证码
Route::get('iot/logout', 'mobile/IotMyInfo/logout');//登出
Route::get('iot', 'mobile/IotHome/index');//首页
Route::get('iot/my', 'mobile/IotMyInfo/index');//我的信息
Route::get('iot/open', 'mobile/IotHome/turnOn');//开关
Route::get('iot/dooron', 'mobile/TurnOn/turnOn');//门禁开关
Route::get('iot/invite', 'mobile/IotInvitedToVisit/index');//邀请访客
Route::get('iot/inviter', 'mobile/IotInvitedToVisit/invitedIndex');//来访邀请
Route::post('iot/invite', 'mobile/IotInvitedToVisit/invited');//邀请访客
Route::get('iot/paysuccess', 'mobile/IotInvitedToVisit/paySuccessShare');//支付成功页面
Route::get('iot/inviteverif', 'mobile/IotInvitedVerif/index');//访客验证
Route::post('iot/verface', 'mobile/IotInvitedVerif/faceVerif');//人脸识别验证
Route::post('iot/veridcard', 'mobile/IotInvitedVerif/idCardVerif');//身份证验证
Route::get('iot/visitlog', 'mobile/VisitLog/index');//访客日志
Route::get('iot/visitlog/askdata', 'mobile/VisitLog/askData');//流加载获取访客日志
Route::get('iot/visitdetail','mobile/VisitLog/visitorDetail');//访客邀请详情
Route::get('iot/permanentuser', 'mobile/VisitLog/permanentUserManage');//常驻人员管理
Route::get('iot/askpermanentuser', 'mobile/VisitLog/askGetPermanentList');//流式获取常驻人员数据
Route::post('iot/downshareimage', 'mobile/IotInvitedToVisit/downImage');//下载邀请二维码
Route::get('iot/resident', 'mobile/ResidentInvitation/index');//常驻邀请
Route::post('iot/storeinvit', 'mobile/ResidentInvitation/storeInvitInfo');//常驻邀请
Route::get('iot/devicecate', 'mobile/IotHome/deviceCate');//设备类别
Route::get('iot/devicelist', 'mobile/IotHome/deviceList');//设备列表
//Route::post('Payments/wxScode','payments/Payments/wxScode');// 微信公众号支付
//Route::post('Payments/Alipays','payments/Payments/Alipays');// 支付宝支付
Route::any('Payments/Notifys', 'payments/Payments/Notifys'); //物联网支付回调

Route::get('order/CheckOrderManagement','admin/CheckOrderManagement/index');//订单抽查情况
Route::get('order/orderCheckList','admin/CheckOrderManagement/orderList');//订单列表
Route::get('order/exportrawpayinfo','admin/CheckOrderManagement/exportRawPayInfo');//导出订单原始支付信息

Route::get('DownloadParking/index', 'index/DownloadParking/index'); //停车数据下载
Route::post('DownloadParking/sendcode', 'index/DownloadParking/sendcode'); //发送邮箱验证码
Route::post('DownloadParking/down', 'index/DownloadParking/down'); //下载文件

/**
 * 二次对账管理
 */
// 管理员管理
Route::get('agents/admin/list','admin/Agents/admin');
Route::get('agents/admin/edit','admin/Agents/aedit');
Route::post('agents/admin/doedit','admin/Agents/Doedit');
Route::post('agents/admin/del','admin/Agents/adel');


// 菜单管理
Route::get('agents/menu/list','admin/Agents/menu');
Route::get('agents/menu/add','admin/Agents/madd');
Route::post('agents/menu/edit','admin/Agents/medit');
Route::get('agents/menu/edit','admin/Agents/medits');


// 角色列
Route::get('agents/role/list','admin/Agents/role');
Route::get('agents/role/add','admin/Agents/radd');
Route::post('agents/role/save','admin/Agents/rsave');


// 配置商户
Route::get('agents/config/list','admin/Agents/config');
Route::get('agents/config/edit','admin/Agents/cedit');
Route::post('agents/config/save','admin/Agents/csave');


// 商户扣费项目
Route::get('agents/config/cost','admin/Agents/cost');
Route::any('agents/config/cost_add','admin/Agents/cost_add');
Route::any('agents/config/cost_edit','admin/Agents/cost_edit');
Route::post('agents/config/cost_del','admin/Agents/cost_del');

// 配置费率
Route::get('agents/fee/list','admin/Agents/fee');
Route::get('agents/fee/edit','admin/Agents/fedit');
Route::post('agents/fee/doedit','admin/Agents/fdedit');
Route::get('agents/fee/del','admin/Agents/fdel');


// 核查有误账单
Route::get('agents/bill/lists','admin/Agentsbill/lists');
// 详细账单列表
Route::get('agents/bill/order','admin/Agentsbill/up');
// 更新datas 账单列表
Route::post('agents/bill/update','admin/Agentsbill/update');
// 确认修改
Route::post('agents/bill/check','admin/Agentsbill/check');


// 二次对账管理 ******************************************


// 税费管理 ==============================================
// 税号列表
Route::get('admin/taxation/lists','admin/Taxation/lists');
// 降税优惠列表 2019-12-04
Route::get('admin/taxation/taxDislist','admin/Taxation/taxDislist');
// 添加降税优惠  更新的形式添加   2019-12-04
Route::post('admin/taxation/taxadd','admin/Taxation/taxAdd');
// 更新数据 添加降税优惠   2019-12-04
Route::post('admin/taxation/taxedit','admin/Taxation/taxEdit');
// 删除功能  添加降税优惠  2019-12-04
Route::post('admin/taxation/taxdels','admin/Taxation/taxDels');

// 导入税号
//Route::get('admin/taxation/imptax','admin/Taxation/imptaxs');
Route::post('admin/taxation/imptax','admin/Taxation/imps');
// 编辑税号
Route::get('admin/taxation/edit','admin/Taxation/edit');
// 保存编辑
Route::post('admin/taxation/edits','admin/Taxation/edits');
// 删除
Route::post('admin/taxation/del','admin/Taxation/del');


// 税费管理
Route::get('admin/taxation/show','admin/Taxation/show');
// 导入计算税费；
Route::get('admin/taxation/exportTax','admin/Taxation/exportTax');
// 获取提单编号
Route::post('admin/taxation/getbol','admin/Taxation/getbol');
// 审核清算
Route::post('admin/taxation/setTaxLiqui','admin/Taxation/setTaxLiqui');

// 税费管理 ==============================================





//商户总账号管理
Route::get('buss/account/list','admin/MerchantTotalAccountManage/index');//列表

//应用管理
Route::get('SystemAppManage/index','admin/SystemAppManage/index');//应用列表
Route::get('SystemAppManage/add','admin/SystemAppManage/add');//添加应用
Route::post('SystemAppManage/add','admin/SystemAppManage/add');//添加应用
Route::get('SystemAppManage/edit','admin/SystemAppManage/edit');//编辑应用
Route::post('SystemAppManage/edit','admin/SystemAppManage/edit');//保存编辑应用
Route::get('SystemAppManage/del','admin/SystemAppManage/del');//保存编辑应用
Route::post('buss/user/review','admin/MerchantTotalAccountManage/reviewMerchant');//商户审核
Route::post('buss/user/update','admin/MerchantTotalAccountManage/updateMerchantAppRule');//更新商户拥有应用权限


/**
 * 2019-08-06 查验风控
 */
Route::get('insp/index','admin/Inspection/index');
Route::post('insp/add','admin/Inspection/add');
Route::post('insp/del','admin/Inspection/del');
Route::get('insp/edit','admin/Inspection/Edit');
Route::post('insp/doedit','admin/Inspection/doEdit');

/**
 * 分类
 */
Route::get('insp/cate','admin/Inspection/cate');
Route::post('insp/docate','admin/Inspection/docate');
Route::get('insp/catelist','admin/Inspection/catelist');
Route::post('insp/catedel','admin/Inspection/catedel');
Route::get('insp/catedit','admin/Inspection/catedit');
Route::post('insp/docatedit','admin/Inspection/docatedit');

// 审核查验风控 2019-08-07
Route::get('insps/index','admin/Inspectionlist/index');
Route::get('insps/insplist','admin/Inspectionlist/insplist');
Route::post('insps/updates','admin/Inspectionlist/updates');
Route::post('insps/checkall','admin/Inspectionlist/checkAll');


// 跨境电商配置  2019-8-16
Route::get('admin/cross/logistics','admin/CrossBorder/logistics');  // 跨境物流配置
Route::get('admin/cross/logGetname','admin/CrossBorder/logGetname');  // 物流企业名称添加
Route::post('admin/cross/logDoname','admin/CrossBorder/logDoname');  // 物流企业名称添加

// 物流线路
Route::get('admin/cross/logisticLine','admin/CrossBorder/logisticLine');  // 物流线路
Route::get('admin/cross/logisticsadd','admin/CrossBorder/logisticsadd');  // 跨境物流配置
Route::get('admin/cross/logisticsedit','admin/CrossBorder/logisticsedit');  // 编辑线路
Route::post('admin/cross/logDoadd','admin/CrossBorder/logDoadd');       // 线路添加
Route::post('admin/cross/logDel','admin/CrossBorder/logDel');           // 线路删除
Route::post('admin/cross/Del','admin/CrossBorder/Del');           // 删除物流企业

// 快递配置
Route::get('admin/express/index','admin/Express/index');
Route::get('admin/express/add','admin/Express/add');
Route::post('admin/express/Doadd','admin/Express/Doadd');
Route::post('admin/express/Del','admin/Express/Del');
// 快递区域配置
Route::get('admin/express/region','admin/Express/region');
Route::get('admin/express/regionadd','admin/Express/regionadd');
Route::get('admin/express/regionedit','admin/Express/regionedit');
Route::post('admin/express/regionDoadd','admin/Express/regionDoadd');
Route::post('admin/express/regionDel','admin/Express/regionDel');

// 运单配置
Route::get('admin/transport/index','admin/Transport/index');  // 新领运单
Route::get('admin/transport/add','admin/Transport/add');  // 新增新领运单
Route::post('admin/transport/Doadd','admin/Transport/Doadd');  // 新增新领运单
Route::post('admin/transport/Del','admin/Transport/Del');  // 删除运单
// 分配运单 2019-08-20
Route::get('admin/transport/distri','admin/Transport/distri');
Route::get('admin/transport/distriAdd','admin/Transport/distriAdd');
Route::post('admin/transport/distriDoadd','admin/Transport/distriDoadd');// 分配确认
Route::post('admin/transport/transDel','admin/Transport/transDel');// 删除分配；
// 获取商户
Route::post('admin/transport/getMerchat','admin/Transport/getMerchat');
Route::post('admin/transport/getTrans','admin/Transport/getTrans'); // 获取号码段

// 查询运单
Route::get('admin/transport/query','admin/Transport/query');


// 包材配置
Route::get('admin/packages/index','admin/Packages/index');// 首页
// 添加供应企业
Route::get('admin/packages/add','admin/Packages/add');
// 添加供应企业操作；
Route::post('admin/packages/doAdd','admin/Packages/doAdd');
Route::post('admin/packages/Del','admin/Packages/Del');

// 包材新增
Route::get('admin/packages/new','admin/Packages/newList');//  新购包材列表
Route::get('admin/packages/newAdd','admin/Packages/newAdd');  // 新增包材
Route::post('admin/packages/newDoadd','admin/Packages/newDoadd');// 新增包材操作；

// 分配包材
Route::get('admin/packages/distri','admin/Packages/distri');
Route::get('admin/packages/distriAdd','admin/Packages/distriAdd');// 分配页面
Route::post('admin/packages/distriDoadd','admin/Packages/distriDoadd');// 分配包材
Route::post('admin/packages/getTrans','admin/Packages/getTrans'); // 获取包材
Route::post('admin/packages/transDel','admin/Packages/transDel');// 删除配置

// 包材查询
Route::get('admin/packages/query','admin/Packages/query');


// 商户计费
Route::get('admin/charging/index','admin/Charging/index');// 列表
Route::get('admin/charging/add','admin/Charging/add');    // 添加
Route::get('admin/charging/edit','admin/Charging/edit');    // 编辑
Route::post('admin/charging/doAdd','admin/Charging/doAdd'); // 添加操作
Route::post('admin/charging/Del','admin/Charging/Del'); // 删除操作
Route::get('admin/charging/getServiceProviderService','admin/charging/getServiceProviderService');//获取商户(服务商)服务

// 查询物流企业
Route::post('admin/charging/getWaybill','admin/Charging/getWaybill'); // 查询物流路线；

Route::get('admin/shop/splitOrderManage','admin/ShopManage/splitOrderManage');//拼单拆单管理
Route::post('admin/shop/splitOrderManage','admin/ShopManage/splitOrderManage');//拼单拆单管理
Route::get('admin/shop/delSplitOrderConditions','admin/ShopManage/delSplitOrderConditions');//删除拼单拆单条件


/**
 * 邦付宝代付；
 * 2020-01-03
 */
Route::get('admin/customs/acclist','admin/Bedalf/acc'); // 账户列表
Route::post('admin/customs/add','admin/Bedalf/add');    // 添加账户列表
Route::post('admin/customs/del','admin/Bedalf/del');    // 添加账户列表
Route::post('admin/customs/doEdit','admin/Bedalf/doEdit');    // 添加账户列表
Route::get('admin/customs/edit','admin/Bedalf/edit');    // 添加账户列表
Route::get('admin/customs/oldlist','admin/Bedalf/old'); // 订单列表

/**
 * 小程序后端
 * 2020-03-23
 */

Route::any('admin/smallwechat/config', 'admin/SmallWechat/config'); //信息配置
Route::any('admin/smallwechat/userlist', 'admin/SmallWechat/userlist'); //用户列表
Route::any('admin/smallwechat/getuserdata', 'admin/SmallWechat/getuserdata'); //请求用户列表
Route::get('admin/smallwechat/sendAddWxFriend','admin/SmallWechat/sendAddWxFriend');//添加微信好友
Route::any('admin/smallwechat/setauth','admin/SmallWechat/setauth');//配置权限

/**
 * 小程序前端
 * 2020-03-23
 */
Route::any('api_v3/WechatAuth/mp_auth', 'api_v3/WechatAuth/mp_auth'); //用户授权登录
Route::get('api_v3/WechatAuth/get_logo', 'api_v3/WechatAuth/get_logo'); //获取logo
Route::get('api_v3/WechatAuth/get_user', 'api_v3/WechatAuth/get_user'); //获取用户信息
Route::any('api_v3/WechatAuth/bindmobilephone', 'api_v3/WechatAuth/bindmobilephone'); //绑定用户手机
Route::any('api_v3/WechatAuth/userqrcode', 'api_v3/WechatAuth/userqrcode'); //推广海报
Route::any('api_v3/WechatAuth/getindex', 'api_v3/WechatAuth/getindex'); //推广海报
Route::any('api_v3/WechatAuth/updatedata', 'api_v3/WechatAuth/updatedata'); //修改会员资料
Route::any('api_v3/WechatAuth/updatedecldata', 'api_v3/WechatAuth/updatedecldata'); //绑定商户账号
Route::any('api_v3/WechatAuth/getuserdata', 'api_v3/WechatAuth/getuserdata'); //获取用户资料

Route::any('api_v3/AccountCheck/check', 'api_v3/AccountCheck/check'); //获取用户资料
Route::any('api_v3/WarehouseAccountCheck/check', 'api_v3/WarehouseAccountCheck/check'); //获取仓储员工资料,2022-07-25

// 打包
Route::any('api_v3/WechatPackage/getlist', 'api_v3/WechatPackage/getlist'); //获取打包列表
Route::any('api_v3/WechatPackage/getordersn', 'api_v3/WechatPackage/getordersn'); //获取运单号
Route::any('api_v3/WechatPackage/getgoods', 'api_v3/WechatPackage/getgoods'); //获取商品
Route::any('api_v3/WechatPackage/dopackage', 'api_v3/WechatPackage/dopackage'); //开始打包

//揽收
Route::any('api_v3/WechatCollect/getlist', 'api_v3/WechatCollect/getlist'); //获取揽收列表
Route::any('api_v3/WechatCollect/docollect', 'api_v3/WechatCollect/docollect'); //发起揽收
Route::any('api_v3/WechatCollect/cancollect', 'api_v3/WechatCollect/cancollect'); //判断用户是否可以发起揽收
Route::any('api_v3/WechatCollect/gocollects', 'api_v3/WechatCollect/gocollects'); //提交收货

//分拣
Route::any('api_v3/WechatSelect/getdeletelist', 'api_v3/WechatSelect/getdeletelist'); //待打包商品列表
Route::any('api_v3/WechatSelect/getpackageorderlist', 'api_v3/WechatSelect/getpackageorderlist'); //获取可分拣运单
Route::any('api_v3/WechatSelect/getorderdata', 'api_v3/WechatSelect/getorderdata'); //获取运单商品
Route::any('api_v3/WechatSelect/doselect', 'api_v3/WechatSelect/doselect'); //开始分拣
Route::any('api_v3/WechatSelect/getorderformscan', 'api_v3/WechatSelect/getorderformscan'); //扫码获取运单
Route::any('api_v3/WechatSelect/addgoodsformwaitlist', 'api_v3/WechatSelect/addgoodsformwaitlist'); //添加商品到待打包列表

//停车对账
Route::any('api_v3/WechatCarBill/gettrueurl', 'api_v3/WechatCarBill/gettrueurl'); //获取对账地址
Route::any('api_v3/WechatCarBill/checkorder', 'api_v3/WechatCarBill/checkorder'); //对账操作
Route::any('api_v3/WechatCarBill/getmredata', 'api_v3/WechatCarBill/getmredata'); //获取银企对账
Route::any('api_v3/WechatCarBill/lastdayexcel', 'api_v3/WechatCarBill/lastdayexcel'); //导出昨日对账
Route::any('api_v3/WechatCarBill/onebalance', 'api_v3/WechatCarBill/onebalance'); //一键平账
Route::any('api_v3/WechatCarBill/getorderlist', 'api_v3/WechatCarBill/getorderlist'); //获取对账列表
Route::any('api_v3/WechatCarBill/checkorder_day', 'api_v3/WechatCarBill/checkorder_day'); //检测今天是否对账
Route::any('api_v3/WechatCarBill/getcustomsorderlist', 'api_v3/WechatCarBill/getcustomsorderlist'); //获取客户对账列表
Route::any('api_v3/WechatCarBill/seeexcelt', 'api_v3/WechatCarBill/seeexcelt'); //导出昨日对账


//对账管理
Route::any('api_v3/WechatUserBill/getuser', 'api_v3/WechatUserBill/getuser'); //获取会员信息
Route::any('api_v3/WechatUserBill/addbill', 'api_v3/WechatUserBill/addbill'); //添加对账单
Route::any('api_v3/WechatUserBill/getbilllist', 'api_v3/WechatUserBill/getbilllist'); //获取账单列表
Route::any('api_v3/WechatUserBill/sendbill', 'api_v3/WechatUserBill/sendbill'); //发送账单
Route::any('api_v3/WechatUserBill/getmybill', 'api_v3/WechatUserBill/getmybill'); //获取我的账单
Route::any('api_v3/WechatUserBill/getbilldetail', 'api_v3/WechatUserBill/getbilldetail'); //获取账单详情
Route::any('api_v3/WechatUserBill/ordercheck', 'api_v3/WechatUserBill/ordercheck'); //有误待查
Route::any('api_v3/WechatUserBill/restsend', 'api_v3/WechatUserBill/restsend'); //重新发送账单
Route::any('api_v3/WechatUserBill/uploadimages', 'api_v3/WechatUserBill/uploadimages'); //上传图片
Route::any('api_v3/WechatUserBill/okcheck', 'api_v3/WechatUserBill/okcheck'); //提交
Route::any('api_v3/WechatUserBill/recallbill', 'api_v3/WechatUserBill/recallbill'); //撤回账单
Route::any('api_v3/WechatUserBill/editbill', 'api_v3/WechatUserBill/editbill'); //修改账单
Route::any('api_v3/WechatUserBill/delbill', 'api_v3/WechatUserBill/delbill'); //删除账单
Route::any('api_v3/WechatUserBill/moneycheck', 'api_v3/WechatUserBill/moneycheck'); //到账核查
Route::any('api_v3/WechatUserBill/postinvoiced', 'api_v3/WechatUserBill/postinvoiced'); //提交电子专票
Route::any('api_v3/WechatUserBill/getinvoicedetail', 'api_v3/WechatUserBill/getinvoicedetail'); //获取电子专票
Route::any('api_v3/WechatUserBill/addnewinvoice', 'api_v3/WechatUserBill/addnewinvoice'); //新增开票
Route::any('api_v3/WechatUserBill/nookinvoice', 'api_v3/WechatUserBill/nookinvoice'); //拒绝开票
Route::any('api_v3/WechatUserBill/postinvoicez', 'api_v3/WechatUserBill/postinvoicez'); //提交电子专票
Route::any('api_v3/WechatUserBill/postinvoices', 'api_v3/WechatUserBill/postinvoices'); //提交电子专票



//管理员
Route::any('api_v3/WechatManage/getuser', 'api_v3/WechatManage/getuser'); //获取小程序会员
Route::any('api_v3/WechatManage/getauth', 'api_v3/WechatManage/getauth'); //获取会员权限
Route::any('api_v3/WechatManage/setuserauth', 'api_v3/WechatManage/setuserauth'); //设置会员权限
Route::any('api_v3/WechatManage/getprojectlist', 'api_v3/WechatManage/getprojectlist'); //获取项目列表
Route::any('api_v3/WechatManage/addproject', 'api_v3/WechatManage/addproject'); //新增项目
Route::any('api_v3/WechatManage/getprojectdata', 'api_v3/WechatManage/getprojectdata'); //获取项目数据
Route::any('api_v3/WechatManage/setprojectdata', 'api_v3/WechatManage/setprojectdata'); //设置项目数据
Route::any('api_v3/WechatManage/delproject', 'api_v3/WechatManage/delproject'); //删除项目




//自动账单
Route::any('api_v3/WechatAuto/index', 'api_v3/WechatAuto/index'); //自动账单接口

/**
 * 小程序服务端
 * 2020-03-23
 */

Route::any('api_v3/WechatServer/index', 'api_v3/WechatServer/index'); //消息推送

// 包裹集运

Route::any('admin/Package/packageCollectDo','admin/Package/collectdo'); //揽收审核(交货)
Route::any('admin/Package/packageCollectDoShou','admin/Package/collectdoshou'); //揽收审核(收货)
Route::any('admin/package/config','admin/Package/config'); //包裹集运配置
Route::any('admin/package/dataList','admin/Package/datalist'); //包裹列表
Route::any('admin/package/packageCollect','admin/Package/package_collect'); //发起揽收
Route::any('admin/package/packageCollectManage','admin/Package/package_collect_manage'); //交收管理
Route::any('admin/package/packageCollectPeople','admin/Package/package_collect_people'); //揽收人员配置
Route::any('admin/package/packageCollectPeopleCreate','admin/Package/package_collect_people_create'); //添加人员
Route::any('admin/package/packageSort','admin/Package/package_sort'); //变更清单
Route::any('admin/package/goPack','admin/Package/go_pack'); //包裹打包
Route::any('admin/package/packageList','admin/Package/package_list'); //包裹清单
Route::any('admin/package/getMerchat','admin/Package/getMerchat'); //获取商户信息
Route::any('admin/package/packageCollectAdd','admin/Package/package_collect_add'); //发起揽收
Route::any('admin/package/packageCollectDetail','admin/Package/package_collect_detail'); //揽收详情
Route::any('admin/Package/packageCollectCode','admin/Package/package_collect_code'); //揽收核销


//邮件发送
Route::any('admin/sendmail/datalist','admin/SendMail/datalist'); //数据列表
Route::any('admin/sendmail/gosend','admin/SendMail/gosend'); //邮件发送
Route::any('api/mailback/index','api/MailBack/index'); //邮件打开回执

//海关出口
Route::get('admin/export/getLading','admin/ExportDeclare/getLading');//获取提单
Route::get('admin/export/zxgetLading','admin/ExportDeclare/zxgetLading');
Route::get('admin/export/exportBatchApplyRecall','admin/Common/exportBatchApplyRecall');//出口申报批次申请撤回
Route::get('admin/export/downloadxml','admin/Common/downloadxml');
Route::get('admin/export/getExportBatchInfoList','admin/Common/getExportBatchInfoList');//获取申报类型批次信息
Route::get('admin/export/zxgetExportBatchInfoList','admin/Common/zxgetExportBatchInfoList');

Route::get('admin/export/orderExportLadingView','admin/ExportDeclare/orderExportLadingView');//出口电子订单提单列表
Route::get('admin/export/orderExportBatchView','admin/ExportDeclare/orderExportBatchView');//出口电子订单批次列表
Route::get('admin/export/paymentSlipExportLadingView','admin/ExportDeclare/paymentSlipExportLadingView');//出口支付单提单列表
Route::get('admin/export/paymentSlipExportBatchView','admin/ExportDeclare/paymentSlipExportBatchView');//出口支付单批次列表
Route::get('admin/export/logisticsWaybillExportLadingView','admin/ExportDeclare/logisticsWaybillExportLadingView');
Route::get('admin/export/zxlogisticsWaybillExportLadingView','admin/ExportDeclare/zxlogisticsWaybillExportLadingView');//出口物流运单
Route::get('admin/export/logisticsWaybillExportBatchView','admin/ExportDeclare/logisticsWaybillExportBatchView');//出口物流运单批次
Route::get('admin/export/logisticsWaybillExportBillnoView','admin/ExportDeclare/logisticsWaybillExportBillnoView');//出口物流运单号列表

Route::get('admin/export/getLogisticsNo','admin/ExportDeclare/getLogisticsNo');
Route::get('admin/export/zxlogisticsWaybillExportBatchView','admin/ExportDeclare/zxlogisticsWaybillExportBatchView');
Route::get('admin/export/inventoryExportLadingView','admin/ExportDeclare/inventoryExportLadingView');//出口清单提单
Route::get('admin/export/inventoryExportCancelLadingView','admin/ExportDeclare/inventoryExportCancelLadingView');//出口清单提单
Route::get('admin/export/inventoryExportBatchView','admin/ExportDeclare/inventoryExportBatchView');//出口清单提单批次
Route::get('admin/export/inventoryExportCancelBatchView','admin/ExportDeclare/inventoryExportCancelBatchView');//出口清单提单批次
Route::get('admin/export/inventoryTotalScoreExportLading','admin/ExportDeclare/inventoryTotalScoreExportLading');//出口清单总分单(总运单)
Route::get('admin/export/inventoryTotalScoreExportBatch','admin/ExportDeclare/inventoryTotalScoreExportBatch');//出口清单总分单(总运单)
Route::get('admin/export/logisticsDepartureListLading','admin/ExportDeclare/logisticsDepartureListLading');//出口物流离境单
Route::get('admin/export/logisticsDepartureListBatch','admin/ExportDeclare/logisticsDepartureListBatch');//出口物流离境单批次
Route::get('admin/export/collectApplyLading','admin/ExportDeclare/collectApplyLading');//出口汇总申请
Route::get('admin/export/collectApplyBatch','admin/ExportDeclare/collectApplyBatch');//出口汇总申请
Route::get('admin/export/addOrderBody','admin/ExportDeclare/addOrderBody');//新增订单出口申报主体
Route::post('admin/export/addOrderBody','admin/ExportDeclare/addOrderBody');//保存订单出口主体
Route::get('admin/export/newOrderMerge','admin/ExportDeclare/newOrderMerge');//新增订单合并
Route::get('admin/export/customsDeclarationList','admin/ExportDeclare/customsDeclarationList');//报关单
Route::post('admin/export/customsDeclarationList','admin/ExportDeclare/customsDeclarationList');//报关单
Route::get('admin/export/customsDeclarationBatchList','admin/ExportDeclare/customsDeclarationBatchList');//报关单批次
Route::get('admin/bohui/pictureUpload','admin/BoHuiPictureUpload/uploadView');//上传商品图片到博汇商城

//预录列表
Route::get('admin/prerecorded/detailedlist','admin/Prerecorded/detailedlist'); //清单列表
Route::get('admin/prerecorded/declarationlist','admin/Prerecorded/declarationlist'); //报关单列表
Route::get('admin/prerecorded/transferlist','admin/Prerecorded/transferlist'); //转关单列表
Route::get('admin/prerecorded/getlist','admin/Prerecorded/getlist'); //获取列表
Route::get('admin/prerecorded/getlogisticslist','admin/Prerecorded/getlogisticslist');

//还原申报,2021-12-16
Route::get('admin/reduction/orderlist','admin/Reduction/orderlist');//订单还原
Route::get('admin/reduction/declareorderlist','admin/Reduction/declareorderlist');//清单还原

//9610预申报,2022-03-25
Route::get('admin/predeclare/addlading','admin/Predeclare/addlading');//新增预提
Route::get('admin/predeclare/predeclarelist','admin/Predeclare/predeclarelist');//预申报列表
Route::any('admin/predeclare/getportinfo','admin/Predeclare/getportinfo');//修改预申报的口岸信息


Route::get('admin/WaybillNumebrExtract/ems','admin/WaybillNumebrExtract/Ems');//ems单号提取
Route::post('admin/WaybillNumebrExtract/ems','admin/WaybillNumebrExtract/Ems');//ems单号提取

//行邮税号表
Route::get('admin/PostalTaxNumber/index','admin/PostalTaxNumber/index'); //个人行邮税号表
Route::get('admin/PostalTaxNumber/getpostal_list','admin/PostalTaxNumber/getpostal_list'); //获取个人行邮税号表
Route::any('admin/PostalTaxNumber/postal_add','admin/PostalTaxNumber/postal_add'); 
Route::any('admin/PostalTaxNumber/postal_edit','admin/PostalTaxNumber/postal_edit'); 
Route::any('admin/PostalTaxNumber/postal_del','admin/PostalTaxNumber/postal_del'); 

Route::get('admin/PostalTaxNumber/refuseManage','admin/PostalTaxNumber/refuseManage'); //拒收品牌及物品
Route::get('admin/PostalTaxNumber/getrefuse_list','admin/PostalTaxNumber/getrefuse_list'); //获取个人行邮税号表
Route::any('admin/PostalTaxNumber/refuse_add','admin/PostalTaxNumber/refuse_add'); 
Route::any('admin/PostalTaxNumber/refuse_edit','admin/PostalTaxNumber/refuse_edit'); 
Route::any('admin/PostalTaxNumber/refuse_del','admin/PostalTaxNumber/refuse_del'); 

Route::get('admin/PostalTaxNumber/categoryManage','admin/PostalTaxNumber/categoryManage'); //CC物品归类
Route::get('admin/PostalTaxNumber/getcatelist','admin/PostalTaxNumber/getcatelist');
Route::any('admin/PostalTaxNumber/cate_add','admin/PostalTaxNumber/cate_add');
Route::any('admin/PostalTaxNumber/cate_edit','admin/PostalTaxNumber/cate_edit');
Route::any('admin/PostalTaxNumber/cate_del','admin/PostalTaxNumber/cate_del'); 

Route::any('admin/PostalTaxNumber/subcate_add','admin/PostalTaxNumber/subcate_add');
Route::get('admin/PostalTaxNumber/subcate','admin/PostalTaxNumber/subcate');
Route::any('admin/PostalTaxNumber/getsubcatelist','admin/PostalTaxNumber/getsubcatelist');
Route::any('admin/PostalTaxNumber/subcate_edit','admin/PostalTaxNumber/subcate_edit');
Route::any('admin/PostalTaxNumber/subcate_del','admin/PostalTaxNumber/subcate_del');

//自我邮
Route::any('admin/travel_express/cates','admin/TravelExpress/cates'); //类目配置
Route::any('admin/travel_express/get_list','admin/TravelExpress/get_list'); //获取列表
Route::any('admin/travel_express/cates_add','admin/TravelExpress/cates_add'); //类目添加
Route::any('admin/travel_express/cates_edit','admin/TravelExpress/cates_edit'); //类目编辑
Route::any('admin/travel_express/cates_del','admin/TravelExpress/cates_del'); //类目删除

Route::any('admin/travel_express/brand','admin/TravelExpress/brand'); //品牌列表
Route::any('admin/travel_express/getbrand','admin/TravelExpress/getbrand');

//自我邮-用户订单
Route::any('admin/travel_express/orders','admin/TravelExpress/orders'); //订单列表
Route::any('admin/travel_express/getOrderList','admin/TravelExpress/getOrderList');
Route::any('admin/travel_express/getOrderDetail','admin/TravelExpress/getOrderDetail');
Route::post('admin/travel_express/orderCheck','admin/TravelExpress/orderCheck');
// 面试测评
// 岗位配置
Route::get('admin/interview/joblist','admin/Interview/joblist');//岗位列表
Route::any('admin/interview/jobadd','admin/Interview/jobadd');//添加岗位
Route::any('admin/interview/jobedit','admin/Interview/jobedit');//编辑岗位
Route::any('admin/interview/jobdel','admin/Interview/jobdel');//编辑岗位
Route::post('admin/interview/jobsave','admin/Interview/jobsave');//保存岗位
// 题目配置
Route::get('admin/interview/queslist','admin/Interview/qaqlist');//题目列表
Route::any('admin/interview/quesadd','admin/Interview/qaqadd');//添加题目
Route::any('admin/interview/quesedit','admin/Interview/qaqedit');//编辑题目
Route::any('admin/interview/quesdel','admin/Interview/qaqdel');//编辑题目
Route::post('admin/interview/quessave','admin/Interview/qaqsave');//保存题目
// 评测管理
Route::get('admin/interview/formlist','admin/Interview/formlist');//评测列表
Route::any('admin/interview/formadd','admin/Interview/formadd');//添加评测
Route::any('admin/interview/formedit','admin/Interview/formedit');//编辑评测
Route::any('admin/interview/formdel','admin/Interview/formdel');//编辑评测
Route::post('admin/interview/formsave','admin/Interview/formsave');//保存评测
// 面试管理
Route::get('admin/interview/invitelist','admin/Interview/invitelist');//面试列表
Route::any('admin/interview/inviteadd','admin/Interview/inviteadd');//添加面试
Route::any('admin/interview/inviteview','admin/Interview/inviteview');//查看面试
Route::any('admin/interview/inviteedit','admin/Interview/inviteedit');//编辑面试
Route::post('admin/interview/invitesave','admin/Interview/invitesave');//保存面试

//供应商管理
Route::any('supplier/index/config_index','supplier/Index/config_index');//企业信息
Route::any('supplier/index/config_docking','supplier/Index/config_docking');//对接信息

//集运系统管理
//*集运配置
Route::any('centralize/managemenber/menu_list','centralize/Managemenber/menu_list');//权限配置
Route::any('centralize/managemenber/role_lists','centralize/Managemenber/role_lists');//角色配置
Route::any('centralize/managemenber/person_lists','centralize/Managemenber/person_lists');//用户配置
Route::any('centralize/managemenber/cargo_lists','centralize/Managemenber/cargo_lists');//货物属性配置
//*基础配置
Route::any('centralize/index/rotation','centralize/Index/rotation');//商城和集运首页轮播图
Route::any('centralize/index/rotation_save','centralize/Index/rotation_save');
Route::any('centralize/index/rotation_del','centralize/Index/rotation_del');
Route::any('centralize/index/notice','centralize/Index/notice');//集运首页公告
Route::any('centralize/index/notice_save','centralize/Index/notice_save');
Route::any('centralize/index/notice_del','centralize/Index/notice_del');
Route::any('centralize/index/embargo','centralize/Index/embargo');//禁运列表
Route::any('centralize/index/embargo','centralize/Index/embargo');//集运教学
Route::any('centralize/index/user_agreement','centralize/Index/user_agreement');//用户协议
Route::any('centralize/index/consolidation_agreement','centralize/Index/consolidation_agreement');//集运协议
Route::any('centralize/index/instruction_order','centralize/Index/instruction_order');//下单须知
Route::any('centralize/index/about','centralize/Index/about');//关于我们
Route::any('centralize/index/news','centralize/Index/news');//新闻列表
Route::any('centralize/index/news_save','centralize/Index/news_save');
Route::any('centralize/index/news_del','centralize/Index/news_del');
Route::any('centralize/index/news_type','centralize/Index/news_type');//最新动态分类
Route::any('centralize/index/news_type_save','centralize/Index/news_type_save');
Route::any('centralize/index/news_type_del','centralize/Index/news_type_del');
Route::any('centralize/index/warehouse','centralize/Index/warehouse');//仓库地址
Route::any('centralize/index/warehouse_save','centralize/Index/warehouse_save');
Route::any('centralize/index/warehouse_del','centralize/Index/warehouse_del');
Route::any('centralize/index/integral_rule','centralize/Index/integral_rule');//积分规则
Route::any('centralize/index/commission','centralize/Index/commission');//分销商佣金配置
Route::any('centralize/index/pay_param','centralize/Index/pay_param');//支付参数配置
Route::any('centralize/index/pick_up_point','centralize/Index/pick_up_point');//提货点配置
Route::any('centralize/index/pick_up_point_save','centralize/Index/pick_up_point_save');
Route::any('centralize/index/pick_up_point_del','centralize/Index/pick_up_point_del');
//*集运营销
Route::any('centralize/index/extra_value_line','centralize/Index/extra_value_line');//超值路线
Route::any('centralize/index/extra_value_line_save','centralize/Index/extra_value_line_save');
Route::any('centralize/index/extra_value_line_del','centralize/Index/extra_value_del');
Route::any('centralize/index/complaint','centralize/Index/complaint');//投诉建议
Route::any('centralize/index/complaint_info','centralize/Index/complaint_info');
Route::any('centralize/index/complaint_del','centralize/Index/complaint_del');
Route::any('centralize/index/coupon','centralize/Index/coupon');//优惠券
Route::any('centralize/index/coupon_save','centralize/Index/coupon_save');
Route::any('centralize/index/coupon_del','centralize/Index/coupon_del');
//Route::any('centralize/index/set_country','centralize/Index/set_country');
//Route::any('centralize/index/set_line','centralize/Index/set_line');
//*会员列表
Route::any('centralize/member/member_lists','centralize/Member/lists');//会员列表
Route::any('centralize/member/member_info','centralize/Member/member_info');
Route::any('centralize/member/member_del','centralize/Member/member_del');
Route::any('centralize/member/member_data_detail','centralize/Member/member_data_detail');
Route::any('centralize/member/member_grade_list','centralize/Member/member_grade_list');//会员等级列表
Route::any('centralize/member/member_grade_save','centralize/Member/member_grade_save');
Route::any('centralize/member/member_grade_del','centralize/Member/member_grade_del');
Route::any('centralize/member/member_grade_detail','centralize/Member/member_grade_detail');//会员等级购买明细
//*分销商列表
Route::any('centralize/distributor/lists','centralize/Distributor/lists');//分销商列表
Route::any('centralize/distributor/commission_detail','centralize/Distributor/commission_detail');
Route::any('centralize/distributor/child_detail','centralize/Distributor/child_detail');
Route::any('centralize/distributor/withdrawal_apply_list','centralize/Distributor/withdrawal_apply_list');
Route::any('centralize/distributor/withdraw_apply','centralize/Distributor/withdraw_apply');
//*集运订单
Route::any('centralize/centralizeorder/no_warehouse','centralize/Centralizeorder/no_warehouse');//未入库列表
Route::any('centralize/centralizeorder/no_warehouse_check','centralize/Centralizeorder/no_warehouse_check');
Route::any('centralize/centralizeorder/parcel_order_detail','centralize/Centralizeorder/parcel_order_detail');
Route::any('centralize/centralizeorder/in_warehouse','centralize/Centralizeorder/in_warehouse');
Route::any('centralize/centralizeorder/packing','centralize/Centralizeorder/packing');
Route::any('centralize/centralizeorder/packing_check','centralize/Centralizeorder/packing_check');
Route::any('centralize/centralizeorder/parcel_merge_order_detail','centralize/Centralizeorder/parcel_merge_order_detail');
Route::any('centralize/centralizeorder/all_order','centralize/Centralizeorder/all_order');//待付款、待发货、已发货、待评价（已完成）
Route::any('centralize/centralizeorder/deliver_goods','centralize/Centralizeorder/deliver_goods');//发货
Route::any('centralize/centralizeorder/return_goods_apply','centralize/Centralizeorder/return_goods_apply');//处理退换货申请
Route::any('centralize/centralizeorder/no_main_part','centralize/Centralizeorder/no_main_part');//无件住认领
Route::any('centralize/centralizeorder/no_main_part_save','centralize/Centralizeorder/no_main_part_save');
Route::any('centralize/centralizeorder/no_main_part_del','centralize/Centralizeorder/no_main_part_del');

/**
 * 集运系统配置用户功能
**/
Route::any('centralize/managemenber/menu_list','centralize/Managemenber/menu_list');//权限菜单列表
Route::any('centralize/managemenber/save_menu','centralize/Managemenber/save_menu');//保存权限菜单
Route::any('centralize/managemenber/del_menu','centralize/Managemenber/del_menu');//删除权限

Route::any('centralize/managemenber/role_lists','centralize/Managemenber/role_lists');//角色列表
Route::any('centralize/managemenber/save_role','centralize/Managemenber/save_role');//保存角色
Route::any('centralize/managemenber/del_role','centralize/Managemenber/del_role');//删除角色


