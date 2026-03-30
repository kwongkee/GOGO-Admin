<?php

namespace app\declares\controller;

use think\Controller;
use think\Db;
use think\Request;

class Uploads extends Controller {
    
    /*
     * 批量处理上传图片
     */
    public function goodsPictureUpload(Request $request) {
        // 跨域问题
        header("Access-Control-Allow-Origin:*");
        // 返回数据
        
        if ($request->isPost()) {
            
            $buss_id = input('post.buss_id');
            if ($buss_id) {
                $uniacid = $buss_id;
            } else {
                return json(['code' => -1, 'msg' => '异常buss_id']);
            }
            
            $files = $request->file('file');
            if (!is_object($files)) {
                return json(['code' => -1, 'msg' => '数据异常']);
            }
            $basePath = '../../attachment/';
            $path     = 'images/total_image/' . date('Ym', time());
            $info     = $files->validate(['ext' => 'png,jpg'])->rule(function ($files) use ($path) {
                $name = explode('_', $files->getInfo('info')['name']);

                if (count($name) == 1) {
                    $name[0]=explode('.',$name[0]);

                    $content = '<p><img src="/images/goodetail/head.jpg" width="100%" style=""/></p><p><br/></p><p><br/></p>';
                    $content .= '<p><img src="' . $path . '/' . $name[0][0] . '.jpg" width="100%"/></p>';
                    $content .= '<p><img src="' . $path . '/' . $name[0][0] . '_01.jpg" width="100%" style=""/></p>';
                    $content .= '<p><img src="' . $path . '/' . $name[0][0] . '_02.jpg" width="100%" style=""/></p>';
                    $content .= '<p><img src="/images/goodetail/footer.jpg" width="100%" style=""/></p><p><br/></p>';
                    Db::name('sz_yi_goods')->where('goodssn', $name[0][0])->update([
                        'thumb'   => $path . '/' . $name[0][0] . '.jpg',
                        'content' => $content,
                    ]);
                }
                return $files->getInfo('info')['name'];
            })->move($basePath . $path);
            
            if ($info) {
                return json(['code' => 0, 'msg' => '上传成功']);
            } else {
                return json(['code' => -1, 'msg' => $files->getError()]);
            }
        }
    }
    
    /*
     * 批量处理上传图片
     */
    public function goodsPictureUpload2(Request $request) {
        // 跨域问题
        header("Access-Control-Allow-Origin:*");
        // 返回数据
        $files    = $request->file('file');
        $basePath = '../../attachment/';
        $path     = 'images/total_image/' . date('Ym',time());
        if (!is_dir($basePath.$path)) {
            mkdir($basePath.$path, 0777, true);
        }
        if (!is_object($files)) {
            return json(['code' => -1, 'msg' => '数据异常']);
        }
        $info = $files->validate(['ext' => 'png,jpg'])
            ->rule(function ($files) use ($path) {
                $name = explode('_', $files->getInfo('info')['name']);
                if (count($name) == 1) {
                    $name[0]=explode('.',$name[0]);
                    $content = '<p><img src="/images/goodetail/head.jpg" width="100%" style=""/></p><p><br/></p><p><br/></p>';
                    $content .= '<p><img src="' . $path . '/' . $name[0][0] . '.jpg" width="100%"/></p>';
                    $content .= '<p><img src="' . $path . '/' . $name[0][0] . '_01.jpg" width="100%" style=""/></p>';
                    $content .= '<p><img src="' . $path . '/' . $name[0][0] . '_02.jpg" width="100%" style=""/></p>';
                    $content .= '<p><img src="/images/goodetail/footer.jpg" width="100%" style=""/></p><p><br/></p>';
                    Db::name('sz_yi_goods')->where('goodssn', $name[0][0])->update([
                        'thumb'   => $path . '/' . $name[0][0] . '.jpg',
                        'content' => $content,
                    ]);
                }
                return $files->getInfo('info')['name'];
            })->move($basePath . $path);
        
        if ($info) {
            return json(['code' => 0, 'msg' => '上传成功']);
        } else {
            return json(['code' => -1, 'msg' => $files->getError()]);
        }
    }
}