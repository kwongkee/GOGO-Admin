/**
 * Created by baobaobao on 2016/3/31.
 */


//显示详细信息导航栏
var content = ['行程介绍','产品特色','费用说明','须知'];
function contentShow() {
    var aLi = $('.g-moreInfo li');
    var aA = $('.g-moreInfo a');
    var p = $('.u-content p');
    for(var i=0 ,len=aLi.length;i<len;i++){
        aLi[i].index = i;
        aA.on('click',function (e) {
            e.preventDefault();
        });
        aLi[i].onclick = function () {
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            p.text(content[this.index]);
        }
    }

}