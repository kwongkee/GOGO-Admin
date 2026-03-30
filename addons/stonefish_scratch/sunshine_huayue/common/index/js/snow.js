//圣诞节日关怀特效JS
(function(){
    var NUMBER_OF_LEAVES=100;
    var container=document.getElementById("snowContainer");
    var showAbs=document.getElementById("showAbs");
    var flagTime=0,flagSet=true;
    var gamma,beta,alpha,xgamma,xbeta;

    function init()
    {     
        for(var i=0;i<NUMBER_OF_LEAVES;i++)
        {
            container.appendChild(createALeaf());
        }
        (new Orientation()).init();
    }
    function randomInteger(low,high)
    {
        return low+Math.floor(Math.random()*(high-low));
    }
    function randomFloat(low,high)
    {
        return low+Math.random()*(high-low);
    }
    function pixelValue(value)
    {
        return value+"px";
    }
    function durationValue(value)
    {
        return value+"s";
    }
    function createALeaf()
    {
        var leafDiv=document.createElement("div");
        var image=document.createElement("img");
        image.src="../addons/sunshine_huayue/common/index/img/snow.png";
        leafDiv.style.top=pixelValue(randomInteger(0,10)-40);
        leafDiv.style.left=pixelValue(randomInteger(0,500));
        var spinAnimationName=(Math.random()<0.5)?"clockwiseSpin":"counterclockwiseSpinAndFlip";
        leafDiv.style.webkitAnimationName="fade, drop";
        image.width=randomInteger(2,10)+7;
        var fadeAndDropDuration=durationValue(randomFloat(6,8));
//        var spinDuration=durationValue(randomFloat(4,8));
        leafDiv.style.webkitAnimationDuration=fadeAndDropDuration+","+fadeAndDropDuration;
        var leafDelay=durationValue(randomFloat(0,5));
        leafDiv.style.webkitAnimationDelay=leafDelay+", "+leafDelay;
        leafDiv.appendChild(image);
        return leafDiv;
    }

    function Orientation(selector) {
    
    }
    Orientation.prototype.init = function(){
        window.addEventListener('deviceorientation', this.orientationListener, false);
    }
    Orientation.prototype.orientationListener = function(evt) {
        if(!flagSet) return false;
        gamma = evt.gamma;
        beta = evt.beta;
        alpha = evt.alpha;
        flagSet = false;
    }

    setInterval(function(){
      if (xgamma != gamma || xbeta != beta) {
        if(beta>0){
         container.style.webkitTransform="rotate("+-gamma+"deg)";
        }
        xgamma = gamma;
        xbeta = beta;
      }
      flagSet=true;
    },400);
    
    init();
})()




