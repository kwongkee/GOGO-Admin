<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>银行账号信息</title>
    <link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />
    <!--<link rel="stylesheet" href="./static/hadmin/layui/css/layui.css">-->
    <style type="text/css">
        .btn{width:300px;line-height:40px}
        body{overflow-y: auto;}
        .page{width: 100%;margin: 0;padding:0;}
        #printId{width: 100%;background: white;font-size: 18px;}
        /*.table thead td{width:33.33%;}*/
    </style>
</head>

<body class="body" style="font-family:宋体;">
<div class="page root">
    <div class="page_box">
        <div id="printId" class="table-responsive">
            
           <table class="table table-striped basicinfo-table table-bordered">

               <tr>
                   <th>账号名称</th>
                   <?php  if($bankMode==1) { ?>
                   <td><?php  echo $bank['name'];?></td>
                   <?php  } else { ?>
                   <td><?php  echo $bank['account_name'];?></td>
                   <?php  } ?>
               </tr>
               <tr>
                   <th>银行名称</th>
                   <td><?php  echo $bank['bank_name'];?></td>
               </tr>
               <tr>
                   <th>银行账号</th>
                   <?php  if($bankMode==1) { ?>
                   <td><?php  echo $bank['bank_account'];?></td>
                   <?php  } else { ?>
                   <td><?php  echo $bank['account'];?></td>
                   <?php  } ?>
               </tr>
           </table>
        </div>
    </div>
</div>


<!--<div style="text-align:center;">-->
<!--     <a href="" download="下载图.png" id="download"></a>-->
<!--    <button type="button" style="width: 10em;" class="btn" onclick="createPDF2()">生成</button>-->
<!--</div>-->

<script src="https://shop.gogo198.cn/foll/public/static/doc/jquery.min.js"></script>
<script src="https://shop.gogo198.cn/foll/public/static/doc/html2canvas.min.js"></script>
<!--<script src="https://shop.gogo198.cn/foll/public/static/doc/jspdf.min.js"></script>-->

<script>
    function createPDF2(){
         html2canvas($(".body")).then(function(canvas) {
            var imgUri = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");
            $("#download").attr("href",imgUri);
            console.log(imgUri);
            document.getElementById("download").click();
        });
    }
    // 生成pdf
    function createPDF() {
        html2canvas(document.querySelector("#printId"), {
            allowTaint: !0,
            scale: 2
        }).then((function(canvas) {

            var contentWidth = canvas.width;
            var contentHeight = canvas.height;

            //一页pdf显示html页面生成的canvas高度;
            var pageHeight = contentWidth / 595.28 * 841.89;
            //未生成pdf的html页面高度
            var leftHeight = contentHeight;
            //pdf页面偏移
            var position = 0;
            //a4纸的尺寸[595.28,841.89]，html页面生成的canvas在pdf中图片的宽高
            var imgWidth = 555.28;
            var imgHeight = 555.28/contentWidth * contentHeight;

            var pageData = canvas.toDataURL('image/jpeg', 1.0);

            var pdf = new jsPDF('', 'pt', 'a4');
            //有两个高度需要区分，一个是html页面的实际高度，和生成pdf的页面高度(841.89)
            //当内容未超过pdf一页显示的范围，无需分页
            if (leftHeight < pageHeight) {
                pdf.addImage(pageData, 'JPEG', 30, 0, imgWidth, imgHeight );
            } else {
                while(leftHeight > 0) {
                    pdf.addImage(pageData, 'JPEG', 30, position, imgWidth, imgHeight)
                    leftHeight -= pageHeight;
                    position -= 841.89;
                    //避免添加空白页
                    if(leftHeight > 0) {
                        pdf.addPage();
                    }
                }
            }

            var pdfName = "通关无纸化出口放行通知书.pdf";
            // 将pdf输入为base格式的字符串
            var buffer = pdf.output("datauristring");
            // 将base64格式的字符串转换为file文件
            var myfile = dataURLtoFile(buffer, pdfName);
            var formdata = new FormData();
            formdata.append('file', myfile);
            formdata.append('folder', 'payer_pay');
            formdata.append('type', 'pi_invoice');
            pdf.save(pdfName);
           
        }))
    }

    //将base64转换为文件对象
    function dataURLtoFile(dataurl, filename) {
        var arr = dataurl.split(',');
        var mime = arr[0].match(/:(.*?);/)[1];
        var bstr = atob(arr[1]);
        var n = bstr.length;
        var u8arr = new Uint8Array(n);
        while(n--){
            u8arr[n] = bstr.charCodeAt(n);
        }
        //转换成file对象
        return new File([u8arr], filename, {type:mime});
        //转换成成blob对象
        //return new Blob([u8arr],{type:mime});
    }
</script>

</body>
</html>