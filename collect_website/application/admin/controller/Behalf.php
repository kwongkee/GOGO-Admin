<?php
namespace app\admin\controller;

//use think\Controller;
use app\admin\controller;
use think\Db;
use think\Request;
use PHPExcel;
use PHPExcel_IOFactory;

class Behalf extends  Auth
{
    //物流管理
    public function logistics_manage(Request $request)
    {
        $dat = input();
        if(isset($dat['pa'])){
            $count = 0;
            $rows = [];

            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            if ($request->isAjax()) {
                $count = Db::name('behalf_express_company')->count();
                $rows = DB::name('behalf_express_company')
                    ->order($order)
                    ->limit($limit)
                    ->select();
                foreach($rows as $k=>$v){
                    $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
                return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
            }
        }else{
            return view('',compact(''));
        }
    }

    public function add_logistics(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        if($request->isAjax()){
            if($id>0){
                Db::name('behalf_express_company')->where(['id'=>$id])->update([
                    'enterprise_name'=>trim($dat['enterprise_name']),
                    'abbreviation_name'=>trim($dat['abbreviation_name']),
                    'mobile'=>trim($dat['mobile']),
                    'express_no'=>trim($dat['express_no']),
                    'express_format'=>trim($dat['express_format'][0]),
                    'logo'=>$dat['logo'][0]
                ]);
            }else{
                $id = Db::name('behalf_express_company')->insertGetId([
                    'enterprise_name'=>trim($dat['enterprise_name']),
                    'abbreviation_name'=>trim($dat['abbreviation_name']),
                    'mobile'=>trim($dat['mobile']),
                    'express_no'=>trim($dat['express_no']),
                    'express_format'=>trim($dat['express_format'][0]),
                    'logo'=>$dat['logo'][0],
                    'createtime'=>time()
                ]);
            }

            if(!empty($dat['expNums']) && !empty($dat['expNumd'])){
                $this->createExpressNumber($dat['express_no'],intval($dat['expNums']),intval($dat['expNumd']),$id);
            }

            return json(['code'=>0,'msg'=>'操作成功']);
        }else{
            $data = ['enterprise_name'=>'','abbreviation_name'=>'','logo'=>'','mobile'=>'','express_no'=>'','express_format'=>''];
            if($id>0){
                $data = Db::name('behalf_express_company')->where(['id'=>$id])->find();
            }
            return view('',compact('id','data'));
        }
    }

    public function createExpressNumber($express_no,$start,$end,$express_id){
        for($i=$start;$i<=$end;$i++){
            $number = $express_no.str_pad($i, 4, '0', STR_PAD_LEFT);
            //判断表中有无已有同样运单号
            $isHaveId = Db::name('behalf_express_number')->where(['number'=>$number])->find();
            if(empty($isHaveId['id'])){
                Db::name('behalf_express_number')->insert([
                    'express_id'=>$express_id,
                    'number'=>$number,
                    'status'=>0,
                ]);
            }else{
                continue;
            }
        }
    }

    public function expnumber_list(Request $request){
        $inp = input();
        $id = intval($inp['id']);
        if($request->isPost() || $request->isAjax()){

        }else {
            $status = ['未使用','已使用'];

            $count =Db::name('behalf_express_number')->where('express_id',$id)->count();
            $user = Db::name('behalf_express_number')->where('express_id',$id)->order('id', 'desc')->paginate(10, $count, ['query' => ['s' => 'admin/behalf/expnumber_list&id='.$id], 'var_page' => 'page', 'type' => 'Layui', 'newstyle' => true]);

            $page = $user->render();
            $data = $user->toArray()['data'];

            $this->assign('data',$data);
            $this->assign('page',$page);
            $this->assign('status',$status);
            return view();
        }
    }

    public function del_logistics(Request $request){
        $dat = input();

        $res = Db::name('behalf_express_company')->where(['id'=>$dat['id']])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #订单管理
    public function order_manage(Request $request){
        $dat = input();
        if(isset($dat['pa'])){
            $count = 0;
            $rows = [];

            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            if ($request->isAjax()) {
                $count = Db::name('behalf_order')->count();
                $rows = DB::name('behalf_order')
                    ->order($order)
                    ->limit($limit)
                    ->select();
                $status = [0=>'未确认',1=>'已确认'];
                foreach($rows as $k=>$v){
                    $rows[$k]['buyername'] = Db::name('sz_yi_member')->where('id',$v['user_id'])->field(['realname'])->find()['realname'];
                    $rows[$k]['sellername'] = Db::name('decl_user')->where('id',$v['merch_id'])->field(['user_name'])->find()['user_name'];
                    $rows[$k]['status_name'] = $status[$v['status']];
                    $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                }
                return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
            }
        }else{
            return view('',compact(''));
        }
    }

    public function order_detail(Request $request){
        $dat = input();
        $order = Db::name('behalf_order')->where(['id'=>$dat['id']])->find();
        $goods = Db::name('behalf_goods')->where(['id'=>$order['good_id']])->find();
        $unit = Db::name('unit')->where(['code_value'=>$goods['unit']])->find()['code_name'];
        $info = Db::name('sz_yi_member')->where(['openid'=>$order['openid']])->find();
        $address = Db::name('sz_yi_member_address')->where(['openid'=>$order['openid']])->find();
        return view('',compact('order','goods','unit','address','info'));
    }

    #输出运单
    public function make_express(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        #pdf信息
        $order = Db::name('behalf_order')->where(['id'=>$dat['id']])->find();
        if(empty($order['express_id'])){
            dd('卖家还未选择物流公司，还未能操作！');
        }
        $goods = Db::name('behalf_goods')->where(['id'=>$order['good_id']])->find();
        $unit = Db::name('unit')->where(['code_value'=>$goods['unit']])->find()['code_name'];
        $buyer = Db::name('sz_yi_member')->where(['openid'=>$order['openid']])->find();
        $address = Db::name('sz_yi_member_address')->where(['openid'=>$order['openid']])->find();
        $express = Db::name('behalf_express_company')->where(['id'=>$order['express_id']])->find();
        $seller = Db::name('decl_user')->where(['id'=>$order['merch_id']])->find();
        #pdf信息end

        header("Content-type: text/html; charset=utf-8");
        // 图片合成
        $bg=$_SERVER['DOCUMENT_ROOT'].'/'.$express['express_format'];#运单格式
        $bar_code='';#条形码
        //保存海报图路径
        $path = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/behalf/express/';
        if(!file_exists($path)) {
            mkdir($path,0777,true);
        }
        // 保存路径
        $savePath = $path.'orderExpress_'.$order['id'].'.png';
        $bg = imagecreatefrompng($bg);// 提前准备好的海报图  必须是PNG格式
//        $qrcodes = imagecreatefrompng(public_path($bar_code)); //二维码
#判断png或jpg
//        $judge_format = explode('.',$info['paper_card'][0])[1];
//        if($judge_format=='jpg' || $judge_format=='jpeg'){
//            $paper_code = imagecreatefromjpeg(public_path($info['paper_card'][0])); //纸质图片
//        }elseif($judge_format=='png'){
//            $paper_code = imagecreatefrompng(public_path($info['paper_card'][0])); //纸质图片
//        }

        $font = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/font/msyh.ttf';
        $font_color1 = imagecolorallocate($bg, 255, 255, 255);//白
        $font_color2 = imagecolorallocate($bg, 206, 0, 2);//红
        $font_color3 = imagecolorallocate($bg, 0, 0, 0);//黑
        $font_color4 = imagecolorallocate($bg, 106, 106, 106);//灰

        //超过45个字节时替换用“...”
//        if(strlen($info['digital_card']['final_address'])>100){
//            $info['digital_card']['final_address'] = $this->cutSubstr($info['digital_card']['final_address']);
//        }

        //卖家名称
        imagettftext($bg, 8, 0, 60, 68, $font_color3, $font, $seller['user_name']);
        //卖家电话
        imagettftext($bg, 8, 0, 220, 68, $font_color3, $font, $seller['user_tel']);
        //卖家发货地址
        imagettftext($bg, 8, 0, 16, 105, $font_color3, $font, $seller['address']);
        //收件人名称
        imagettftext($bg, 10, 0, 68, 131, $font_color3, $font, $address['realname']);
        //收件人电话
        imagettftext($bg, 10, 0, 160, 131, $font_color3, $font, $address['mobile']);
        //收件人地址
        imagettftext($bg, 10, 0, 16, 175, $font_color3, $font, $address['address']);
        //价值
        imagettftext($bg, 10, 0, 175, 294, $font_color3, $font, $order['totalprice']);
        //品名数量
        imagettftext($bg, 10, 0, 323, 294, $font_color3, $font, $order['buy_num']);
        //收件人名称
        imagettftext($bg, 10, 0, 68, 384, $font_color3, $font, $address['realname']);
        //收件人电话
        imagettftext($bg, 10, 0, 215, 384, $font_color3, $font, $address['mobile']);
        //收件人地址
        imagettftext($bg, 10, 0, 15, 422, $font_color3, $font, $address['address']);
        imagepng($bg, $savePath); //合并图片
        $imgSrc = preg_replace('/\/www\/wwwroot\/gogo\/collect_website\/public/','',$savePath).'?v='.time();
        #模拟运单
        if(!empty($order['expresssn'])){
            $express_no = $order['expresssn'];
        }else{
            $express_no = Db::name('behalf_express_number')->where(['express_id'=>$order['express_id'],'status'=>0])->find()['number'];
        }
        return view('',compact('order','goods','unit','address','info','express','imgSrc','express_no'));
    }

    private function cutSubstr($str,$len=15){
        header("Content-type: text/html; charset=utf-8");
        if (strlen($str)>$len) {
            $str=mb_substr($str,0,$len,'utf-8').'...';
        }

        return $str;
    }

    public function update_order_express(Request $request){
        $dat = input();
        if(request()->isAjax() || request()->isPost()){
            $id = intval($dat['id']);
            $express_no = trim($dat['express_no']);

            $res = Db::name('behalf_order')->where(['id'=>$id])->update(['expresssn'=>$express_no]);
            if($res){
                Db::name('behalf_express_number')->where(['number'=>$express_no])->update(['status'=>1]);
                return json(['code'=>0,'msg'=>'生成成功']);
            }
        }
    }
    
    public function make_ticket(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        #小票信息(1毫米 约等于 3.78像素)
        $order = Db::name('behalf_order')->where(['id'=>$dat['id']])->find();
        if(empty($order['express_id'])){
            dd('卖家还未选择物流公司，还未能操作！');
        }
        $goods = Db::name('behalf_goods')->where(['id'=>$order['good_id']])->find();
        $unit = Db::name('unit')->where(['code_value'=>$goods['unit']])->find()['code_name'];
        $buyer = Db::name('sz_yi_member')->where(['openid'=>$order['openid']])->find();
        $address = Db::name('sz_yi_member_address')->where(['openid'=>$order['openid']])->find();
        $express = Db::name('behalf_express_company')->where(['id'=>$order['express_id']])->find();
        $seller = Db::name('decl_user')->where(['id'=>$order['merch_id']])->find();
        $seller['behalf_info'] = json_decode($seller['behalf_info'],true);
        #小票信息end
        
        header("Content-type: text/html; charset=utf-8");
        // header ('Content-Type: image/png');
        //保存海报图路径
        $path = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/behalf/ticket/';
        if(!file_exists($path)) {
            mkdir($path,0777,true);
        }
        // 保存路径
        $savePath = $path.'orderTicket_'.$order['id'].'.png';
        $merch_logo = $seller['behalf_info']['sbrand'];
        // 创建图像
        $height = 600; //图像高度
        $width = 280; //图像宽度
        
        $im = imagecreatetruecolor($width,$height); //创建一个真彩色的图像
        $font = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/font/msyh.ttf';
        
        $white = imagecolorallocate($im,255,255,255);//白色
        $black = imagecolorallocate($im,0,0,0);//黑色
        #将背景设置为白色
        imagefill($im,0,0,$white);
        #卖家名称
        imagettftext($im, 15, 0, 100, 40, $black, $font, $seller['behalf_info']['abbreviation']);
        #卖家标识摆放点
        $x = imagesx($im); //二维码开始位置的x坐标
        $y = imagesy($im); //二维码开始位置的x坐标
        $arr = getimagesize($merch_logo);
        //判断png或jpg
        $judge_format = explode('.',$merch_logo)[3];
        $logo = '';
        if($judge_format=='jpg' || $judge_format=='jpeg'){
            $logo = imagecreatefromjpeg($merch_logo);
        }elseif($judge_format=='png'){
            $logo = imagecreatefrompng($merch_logo);
        }
        imagecopyresampled($im, $logo, 65, 60, 0, 0, 150, 100, $arr[0], $arr[1]);
        #虚线
        imagedashedline($im,10,180,270,180,$black);
        #卖家信息
        imagettftext($im, 10, 0, 10, 200, $black, $font, '卖家名称：'.$seller['user_name']);
        imagettftext($im, 10, 0, 10, 220, $black, $font, '卖家电话：'.$seller['user_tel']);
        imagettftext($im, 10, 0, 10, 240, $black, $font, $seller['address']);
        #虚线
        imagedashedline($im,10,250,270,250,$black);
        #货品信息
        imagettftext($im, 11, 0, 10, 270, $black, $font, '货品名称');
        imagettftext($im, 11, 0, 110, 270, $black, $font, '单价');
        imagettftext($im, 11, 0, 170, 270, $black, $font, '数量');
        imagettftext($im, 11, 0, 230, 270, $black, $font, '小计');
        imagettftext($im, 11, 0, 10, 290, $black, $font, $goods['name']);
        imagettftext($im, 11, 0, 100, 290, $black, $font, $goods['price']);
        imagettftext($im, 11, 0, 160, 290, $black, $font, $order['buy_num']);
        imagettftext($im, 11, 0, 210, 290, $black, $font, $order['totalprice']);
        #虚线
        imagedashedline($im,10,300,270,300,$black);
        #订单信息
        imagettftext($im, 10, 0, 10, 320, $black, $font, '订单编号：'.$order['ordersn']);
        imagettftext($im, 10, 0, 10, 340, $black, $font, '交易编号：'.$order['paysn']);
        imagettftext($im, 10, 0, 10, 360, $black, $font, '运单编号：'.$order['expresssn']);
        imagettftext($im, 10, 0, 10, 380, $black, $font, '订单总金额：￥'.$order['totalprice']);
        imagettftext($im, 10, 0, 10, 400, $black, $font, '下单时间：'.date('Y-m-d H:i:s',$order['createtime']));
        #虚线
        imagedashedline($im,10,410,270,410,$black);
        imagettftext($im, 10, 0, 10, 430, $black, $font, '订购热线：'.$seller['user_tel']);
        imagettftext($im, 10, 0, 10, 450, $black, $font, $seller['address']);
        // imageline($im,0,0,$width,$height,$white);//在图上画一条白色的直线
        // imagestring($im,4,80,150,"php",$white);//在图上显示白色的“php”文字

        header("cotent-type:image/png"); //输出图像的MIME类型
        
        imagepng($im,$savePath); //输出一个png图像数据
        
        $imgSrc = preg_replace('/\/www\/wwwroot\/gogo\/collect_website\/public/','',$savePath).'?v='.time();
        
//        echo '<div style="background:#999;width:600px;height:800px;"><img src="https://admin.gogo198.cn/collect_website/public/'.$imgSrc.'"></div>';die;
        imagedestroy($im);//清空内存

        echo '<img src="https://admin.gogo198.cn/collect_website/public/'.$imgSrc.'"><p style="margin-left:80px;color:#ff2222;margin-top:15px;">右键下载</p>';exit;
    }

    #清单管理
    public function inventory_manage(Request $request){
        $dat = input();

        if(isset($dat['pa'])){
            $merchid = isset($dat['merchid'])?intval($dat['merchid']):'';
            $expid = isset($dat['exp_id'])?intval($dat['exp_id']):'';
            $keyword = isset($dat['search'])?trim($dat['search']):'';
            $startdate = isset($dat['startDate'])?trim($dat['startDate']):'';
            $enddate = isset($dat['endDate'])?trim($dat['endDate']):'';
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $where = [];
            $where2 = '';
            if($merchid){
                $where = array_merge($where,['merch_id'=>$merchid]);
            }
            if($expid){
                $where = array_merge($where,['express_id'=>$expid]);
            }
            if($keyword){
                $where = array_merge($where,['ordersn'=>$keyword]);
            }
            if(!empty($startdate) && !empty($enddate)){
                $startdate = strtotime($startdate);
                $enddate = strtotime($enddate);
                $where2 = ' (createtime >= '.$startdate.' and createtime <= '.$enddate.') ';
            }
            $count = Db::name('behalf_order')->where($where)->where($where2)->count();
            $rows = DB::name('behalf_order')
                ->where($where)
                ->where($where2)
                ->order($order)
                ->limit($limit)
                ->select();
            $status = [0=>'未确认',1=>'已确认'];
            foreach($rows as $k=>$v){
                $rows[$k]['enterprise_name'] = Db::name('behalf_express_company')->where('id',$v['express_id'])->field(['enterprise_name'])->find()['enterprise_name'];
                $rows[$k]['buyername'] = Db::name('sz_yi_member')->where('id',$v['user_id'])->field(['realname'])->find()['realname'];
                $rows[$k]['sellername'] = Db::name('decl_user')->where('id',$v['merch_id'])->field(['user_name'])->find()['user_name'];
                $rows[$k]['status_name'] = $status[$v['status']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            $merch = Db::name('decl_user')->order('id','desc')->select();
            $express = Db::name('behalf_express_company')->order('id','desc')->select();
            return view('',compact('merch','express'));
        }
    }

    public function out_inventory(Request $request){
        $dat = input();

//        $rows = [["欧洲各国外贸网站","https://www.dragon-guide.net/guobie/Europe.htm"],["美洲各国外贸网站","https://www.dragon-guide.net/guobie/Americas.htm"],["亚洲各国外贸网站","https://www.dragon-guide.net/guobie/Asia.htm"],["大洋洲外贸网站","https://www.dragon-guide.net/guobie/Ocean.htm"],["非洲各国外贸网站","https://www.dragon-guide.net/guobie/Africa.htm"],["欧洲各国B2B","https://www.dragon-guide.net/hangye/ouzhou-B2B.htm"],["美洲各国B2B","https://www.dragon-guide.net/hangye/meizhou-B2B.htm"],["亚洲各国B2B","https://www.dragon-guide.net/hangye/yazhou-B2B.htm"],["大洋洲各国B2B","https://www.dragon-guide.net/hangye/dayangzhou-B2B.htm"],["非洲各国B2B","https://www.dragon-guide.net/hangye/feizhou-B2B.htm"],["欧洲各国黄页","https://www.dragon-guide.net/hangye/ouzhou-yellowpages.htm"],["美洲各国黄页","https://www.dragon-guide.net/hangye/meizhou-yellowpages.htm"],["亚洲各国黄页","https://www.dragon-guide.net/hangye/yazhou-yellowpages.htm"],["大洋洲各国黄页","https://www.dragon-guide.net/hangye/dayangzhou-yellowpages.htm"],["非洲各国黄页","https://www.dragon-guide.net/hangye/feizhou-yellowpages.htm"],["欧洲各国搜索引擎","https://www.dragon-guide.net/hangye/ouzhou-search.htm"],["美洲各国搜索引擎","https://www.dragon-guide.net/hangye/meizhou-search.htm"],["亚洲各国搜索引擎","https://www.dragon-guide.net/hangye/yazhou-search.htm"],["大洋洲搜索引擎","https://www.dragon-guide.net/hangye/dayangzhou-search.htm"],["非洲各国搜索引擎","https://www.dragon-guide.net/hangye/feizhou-search.htm"],["国际（空运）海运费查询","https://www.dragon-guide.net/yunfeichaxun.htm"],["全球海关网站及海关数据","https://www.dragon-guide.net/hangye/Customs.htm"],["中国驻外使馆经济商务参赞处","https://www.dragon-guide.net/hangye/canzanzhu.htm"],["全球外贸B2B","https://www.dragon-guide.net/hangye/zuihaowaimao.htm"],["全球进出口查询","https://www.dragon-guide.net/hangye/quanqiujinchukou.html"],["美国海关数据","http://www.ic.gc.ca/sc_mrkti/tdst/tdo/tdo.php"],["加拿大进口商数据","http://www.ic.gc.ca/cgi-bin/sc_mrkti/cid/cid_e.cgi"],["印度进出口数据分析","http://commerce.nic.in/ftpa/default.asp"],["韩国海关数据统计","http://english.customs.go.kr/kcsweb/user.tdf?a=user.itemimportexport.ItemImportExportApp&c=1001&mc=ENGLISH_INFORMATION_TRADE_040"],["日本海关统计数据","http://www.customs.go.jp/toukei/info/index_e.htm"],["中国海关统计数据","http://www.customs.gov.cn/tabid/400/default.aspx"],["百万买家数据大奉送","https://www.dragon-mall.net/category.php?id=136"],["全球贸易情报数据","https://www.dragon-mall.net/category.php?id=136"],["最新海关数据采购商","https://www.dragon-mall.net/category.php?id=136"],["最新海关数据","https://www.dragon-mall.net/category.php?id=136"],["俄罗斯海关","http://www.customs.ru"],["法国海关网","http://www.douane.gouv.fr"],["德国海关官方","http://www.zoll.de"],["英国税务与海关总署","http://www.hmrc.gov.uk"],["爱尔兰海关网","http://www.revenue.ie/services/customs.htm"],["意大利海关总署","http://ec.europa.eu/ecip/national_customs_websites/national_it_en.htm"],["匈牙利海关与财政警察署","http://vam.gov.hu"],["芬兰海关委员会","http://www.tulli.fi/fi"],["捷克海关总署","http://www.cs.mfcr.cz/cmsgrc"],["克罗地亚海关","http://www.carina.hr"],["保加利亚海关总署","http://www.customs.bg"],["白俄罗斯海关","http://www.customs.gov.by"],["挪威海关","http://www.toll.no"],["荷兰海关","http://www.belastingdienst.nl"],["马耳他海关","http://mfin.gov.mt"],["卢森堡海关","http://www.do.etat.lu"],["立陶宛海关","http://www.cust.lt/en/index"],["拉脱维亚海关","http://www.vid.gov.lv"],["哈萨克斯坦海关","http://www.customs.kz/exec/index"],["匈牙利海关","http://vam.gov.hu/welcome.do"],["捷克海关","http://www.cs.mfcr.cz"],["丹麦海关","http://www.skat.dk"],["芬兰海关","http://www.tulli.fi/fi"],["罗马尼亚海关","http://www.customs.ro"],["安道尔海关","http://www.duana.ad"],["亚美尼亚国家海关委员会","http://www.customs.am"],["阿塞拜疆海关","http://www.az-customs.net"],["爱沙尼亚海关","http://www.emta.ee"],["波兰海关","http://www.mf.gov.pl"],["乌克兰海关","http://www.customs.gov.ua"],["马其顿海关","http://www.customs.gov.mk/DesktopDefault.aspx"],["瑞士海关","http://www.ezv.admin.ch"],["瑞典海关","http://www.tullverket.se"],["西班牙海关","http://www.aeat.es"],["斯洛文尼亚海关","http://carina.gov.si/angl/index.htm"],["斯洛伐克海关","http://www.colnasprava.sk"],["塞黑海关","http://www.fcs.yu"],["冰岛海关署","http://www.tollur.is"],["最新全球海关数据","http://www.dragon-mall.net/goods.php?id=51"],["美国海关与边境保护局","http://www.cbp.gov"],["加拿大边境服务署","http://www.cbsa-asfc.gc.ca"],["巴西联邦税务总局","http://www.receita.fazenda.gov.br"],["阿根廷国家海关总署","http://www.afip.gov.ar"],["巴哈马海关","http://www.batelnet.bs/customs.htm"],["墨西哥海关总署","http://www.sat.gob.mx/nuevo.html"],["智利海关总署","http://www.aduana.cl"],["秘鲁国家海关总署","http://www.aduanet.gob.pe"],["委内瑞拉税务与海关总署","http://www.seniat.gov.ve"],["巴巴多斯海关总署","http://barbados.gov.bb/customs/frameset.htm"],["乌拉圭海关","http://www.aduanas.gub.uy"],["亚太经合组织关税数据","http://www.apectariff.org"],["韩国海关","http://www.customs.go.kr"],["日本海关关税局","http://www.customs.go.jp"],["马来西亚皇家海关","http://www.customs.gov.my"],["新加坡海关","http://www.customs.gov.sg"],["中国香港海关","http://www.customs.gov.hk"],["中国台湾海关","http://www.customs.gov.tw"],["印度中央消费税与关税","http://www.cbec.gov.in"],["泰国海关部","http://www.customs.go.th"],["阿联酋海关","http://www.dxbcustoms.gov.ae"],["约旦海关","http://www.customs.gov.jo"],["以色列海关与附加值税部","http://www.mof.gov.il/customs/eng/mainpage.htm"],["也门海关","http://www.customs.gov.ye"],["马尔代夫海关","http://www.customs.gov.mv"],["斯里兰卡海关部","http://www.customs.gov.lk"],["缅甸财政与税收部海关局","http://www.myanmar.com/Ministry/finance"],["文莱皇家关税与消费税部","http://www.customs.gov.bn"],["土耳其海关","http://www.gumruk.gov.tr"],["巴基斯坦中央税收委员会","http://www.cbr.gov.pk"],["黎巴嫩海关局","http://www.customs.gov.lb"],["柬埔寨海关与税收部","http://www.camnet.com.kh/customs"],["孟加拉海关","http://www.nbr-bd.org"],["巴林海关","http://www.bahraincustoms.gov.bh"],["不丹财政部税收与海关局","http://www.mof.gov.bt/drc/customs.html"],["尼泊尔海关部","http://www.customs.gov.np"],["中国海关总署","http://www.customs.gov.cn"],["广东分署","http://guangdong_sub.customs.gov.cn"],["上海海关","http://shanghai.customs.gov.cn"],["最新全球海关数据","http://www.dragon-mall.net/goods.php?id=51"],["埃及海关","http://www.customs.gov.eg"],["苏丹海关","http://www.customs.gov.sd"],["摩洛哥海关","http://www.douane.gov.ma"],["突尼斯海关","http://www.douane.gov.tn"],["阿尔及利亚海关","http://www.douane.gov.dz"],["埃塞俄比亚税务部","http://www.mor.gov.et/ecaweb"],["肯尼亚税务部","http://www.kra.go.ke"],["马拉维税务局","http://www.sdnp.org.mw/mra/customs_asycuda.html"],["纳米比亚财政部","http://www.op.gov.na/Decade_peace/mof.htm"],["卢旺达税务局","http://www.rra.gov.rw"],["斯威士兰财政部","http://www.gov.sz/home.asp?pid=69"],["赞比亚税务局海关处","http://www.zra.org.zm/customs/custintro.htm"],["安哥拉财政部","http://www.minfin.gv.ao/menus/1menuaduan.htm"],["加纳海关","http://www.cepsghana.org"],["民主刚果财政部","http://www.minfinrdc.cd"],["澳大利亚海关编码数据库","http://www.customs.gov.au/"],["新西兰海关编码数据库","http://www.med.govt.nz/buslt/trade-rem/"],["斐济贸易与投资局","http://www.ftib.org.fj/"],["最新全球海关数据","http://www.dragon-mall.net/goods.php?id=51"]];

        $merchid = isset($dat['merchid'])?intval($dat['merchid']):'';
        $expid = isset($dat['exp_id'])?intval($dat['exp_id']):'';
        $keyword = isset($dat['search'])?trim($dat['search']):'';
        $startdate = isset($dat['startDate'])?trim($dat['startDate']):'';
        $enddate = isset($dat['endDate'])?trim($dat['endDate']):'';
        $where = [];
        $where2 = '';

        if($merchid){
            $where = array_merge($where,['merch_id'=>$merchid]);
        }
        if($expid){
            $where = array_merge($where,['express_id'=>$expid]);
        }
        if($keyword){
            $where = array_merge($where,['ordersn'=>$keyword]);
        }
        if(!empty($startdate) && !empty($enddate)){
            $startdate = strtotime($startdate);
            $enddate = strtotime($enddate);
            $where2 = ' (createtime >= '.$startdate.' and createtime <= '.$enddate.') ';
        }
        $count = Db::name('behalf_order')->where($where)->where($where2)->count();
        $rows = DB::name('behalf_order')
            ->where($where)
            ->where($where2)
            ->select();
//        $status = [0=>'未确认',1=>'已确认'];
        foreach($rows as $k=>$v){
            #物流公司
            $rows[$k]['enterprise_info'] = Db::name('behalf_express_company')->where('id',$v['express_id'])->find();
            #买家信息
            $rows[$k]['buyer_info'] = Db::name('sz_yi_member')->where('id',$v['user_id'])->find();
            $rows[$k]['buyerAddr_info'] = Db::name('sz_yi_member_address')->where('openid',$v['openid'])->find();
            #卖家信息
            $rows[$k]['seller_info'] = Db::name('decl_user')->where('id',$v['merch_id'])->find();
            $rows[$k]['seller_info']['behalf_info'] = json_decode($rows[$k]['seller_info']['behalf_info'],true);
//            $rows[$k]['status_name'] = $status[$v['status']];
            $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }

        #提示
        if(!isset($dat['ppa'])){
            if(!empty($rows)){
                return json(['code'=>0,'msg'=>'现在导出']);
            }else{
                return json(['code'=>-1,'msg'=>'数据为空，导出失败！']);
            }
        }

        #输出excel表格
        $fileName = '清单数据['.date('Y-m-d H:i:s').'].xls';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes';
        $dir2 = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpoffice/phpexcel/Classes/PHPExcel';
        require_once($dir."/PHPExcel.php");
        require_once($dir2."/IOFactory.php");
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
//        $PHPSheet->setTitle('分运单');
//        $PHPSheet->setCellValue('A1', '名称');
//        $PHPSheet->setCellValue('B1', '链接');
        $PHPSheet->setTitle('分运单'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '进口分运单号');
        $PHPSheet->setCellValue('B1', '整单件数');
        $PHPSheet->setCellValue('C1', '报关类型');
        $PHPSheet->setCellValue('D1', '发件人');
        $PHPSheet->setCellValue('E1', '英文发件人');
        $PHPSheet->setCellValue('F1', '发件人国别');
        $PHPSheet->setCellValue('G1', '发件人城市');
        $PHPSheet->setCellValue('H1', '英文发件人城市');
        $PHPSheet->setCellValue('I1', '英文经停城市');
        $PHPSheet->setCellValue('J1', '发件人地址');
        $PHPSheet->setCellValue('K1', '英文发件人地址');
        $PHPSheet->setCellValue('L1', '发件人电话');
        $PHPSheet->setCellValue('M1', '收件人');
        $PHPSheet->setCellValue('N1', '收件人证件类型');
        $PHPSheet->setCellValue('O1', '收件人证件号码');
        $PHPSheet->setCellValue('P1', '收件人城市');
        $PHPSheet->setCellValue('Q1', '收件人地址');
        $PHPSheet->setCellValue('R1', '收件人电话');
        $PHPSheet->setCellValue('S1', '贸易国别');
        $PHPSheet->setCellValue('T1', '监管方式');
        $PHPSheet->setCellValue('U1', '成交方式');
        $PHPSheet->setCellValue('V1', '包装种类');
        $PHPSheet->setCellValue('W1', '是否含木质包装');
        $PHPSheet->setCellValue('X1', '是否旧物');
        $PHPSheet->setCellValue('Y1', '是否低温运输');
        $PHPSheet->setCellValue('Z1', '运费');
        $PHPSheet->setCellValue('AA1', '运费币制');
        $PHPSheet->setCellValue('AB1', '保费');
        $PHPSheet->setCellValue('AC1', '保费币制');
        $PHPSheet->setCellValue('AD1', '杂费');
        $PHPSheet->setCellValue('AE1', '杂费币制');
        $PHPSheet->setCellValue('AF1', '收件人信用代码');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $n = 2;
        //{"abbreviation":"\u4e2d\u56fd\u5546\u5bb6","sbrand":"https:\/\/shop.gogo198.cn\/attachment\/images\/3\/2023\/04\/QQnh1EZI17772i19k129EHPNfcuesZ.jpg","address_country":"142","type":"1","area_code":"0757"}
        if (!empty($rows)) {
            foreach ($rows as $value) {
//                $PHPSheet->setCellValue('A'.$n,"\t" .$value[0]."\t")
//                    ->setCellValue('B'.$n,"\t" .$value[1]."\t");
                $PHPSheet->setCellValue('A'.$n,"\t" .$value['expresssn']."\t")
                    ->setCellValue('B'.$n,"\t" .''."\t")
                    ->setCellValue('C'.$n,'2')
                    ->setCellValue('D'.$n,"\t" .$value['seller_info']['user_name']."\t")
                    ->setCellValue('E'.$n,'')
                    ->setCellValue('F'.$n,$value['seller_info']['behalf_info']['address_country'])
                    ->setCellValue('G'.$n,'')
                    ->setCellValue('H'.$n,'')
                    ->setCellValue('I'.$n,'')
                    ->setCellValue('J'.$n,$value['seller_info']['address'])
                    ->setCellValue('K'.$n,'')
                    ->setCellValue('L'.$n,$value['seller_info']['behalf_info']['area_code'].'-'.$value['seller_info']['user_tel'])
                    ->setCellValue('M'.$n,$value['buyer_info']['realname'])
                    ->setCellValue('N'.$n,'1')
                    ->setCellValue('O'.$n,"'".$value['buyer_info']['id_card']."'")
                    ->setCellValue('P'.$n,'')
                    ->setCellValue('Q'.$n,$value['buyerAddr_info']['address'])
                    ->setCellValue('R'.$n,$value['buyer_info']['mobile'])
                    ->setCellValue('S'.$n,'')
                    ->setCellValue('T'.$n,'')
                    ->setCellValue('U'.$n,'')
                    ->setCellValue('V'.$n,'2')
                    ->setCellValue('W'.$n,'')
                    ->setCellValue('X'.$n,'')
                    ->setCellValue('Y'.$n,'')
                    ->setCellValue('Z'.$n,'')
                    ->setCellValue('AA'.$n,'')
                    ->setCellValue('AB'.$n,'')
                    ->setCellValue('AC'.$n,'')
                    ->setCellValue('AD'.$n,'')
                    ->setCellValue('AE'.$n,'')
                    ->setCellValue('AF'.$n,'');
                $n +=1;
            }
        }

        #第二工作表
        $sheet2 = $Excel->createSheet();//创建第二个sheet
        $PHPSheet2 = $Excel->setactivesheetindex(1);//区别于第一个sheet，
        $PHPSheet2->setTitle('商品信息');
        $PHPSheet2->setCellValue('A1', '分运单号');
        $PHPSheet2->setCellValue('B1', '商品编码'."\t".'行邮税码');
        $PHPSheet2->setCellValue('C1', '商品名称');
        $PHPSheet2->setCellValue('D1', '商品英文名称');
        $PHPSheet2->setCellValue('E1', '规格型号');
        $PHPSheet2->setCellValue('F1', '产销国/地区');
        $PHPSheet2->setCellValue('G1', '产销城市');
        $PHPSheet2->setCellValue('H1', '申报数量');
        $PHPSheet2->setCellValue('I1', '申报计量单位');
        $PHPSheet2->setCellValue('J1', '毛重');
        $PHPSheet2->setCellValue('K1', '净重');
        $PHPSheet2->setCellValue('L1', '成交金额');
        $PHPSheet2->setCellValue('M1', '成交币制');
        $PHPSheet2->setCellValue('N1', '生产厂家');
        $PHPSheet2->setCellValue('O1', '第一数量');
        $PHPSheet2->setCellValue('P1', '第一单位');
        $PHPSheet2->setCellValue('Q1', '第二数量');
        $PHPSheet2->setCellValue('R1', '第二单位');
        $PHPSheet2->setCellValue('S1', '包装数量');
        $n2=2;
        if (!empty($rows)) {
            foreach ($rows as $value) {
                $goods = Db::name('behalf_goods')->where(['id'=>$value['good_id']])->find();
                $PHPSheet2->setCellValue('A'.$n2,"\t" .$value['expresssn']."\t")
                    ->setCellValue('B'.$n2,"\t" .''."\t")
                    ->setCellValue('C'.$n2,$goods['name'])
                    ->setCellValue('D'.$n2,"\t" .''."\t")
                    ->setCellValue('E'.$n2,$goods['option'])
                    ->setCellValue('F'.$n2,$value['seller_info']['behalf_info']['address_country'])
                    ->setCellValue('G'.$n2,'')
                    ->setCellValue('H'.$n2,$value['buy_num'])
                    ->setCellValue('I'.$n2,'')
                    ->setCellValue('J'.$n2,'')
                    ->setCellValue('K'.$n2,'')
                    ->setCellValue('L'.$n2,$value['totalprice'])
                    ->setCellValue('M'.$n2,'142')
                    ->setCellValue('N'.$n2,'')
                    ->setCellValue('O'.$n2,'')
                    ->setCellValue('P'.$n2,'')
                    ->setCellValue('Q'.$n2,'')
                    ->setCellValue('R'.$n2,'')
                    ->setCellValue('S'.$n2,$value['buy_num']);
                $n2 +=1;
            }
        }


        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        return $ExcelWrite->save('php://output');
    }
}