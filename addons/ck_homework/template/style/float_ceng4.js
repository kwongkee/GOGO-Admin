function showid(idname){
	
	var isIE = (document.all) ? true : false;
	var isIE6 = isIE && ([/MSIE (\d)\.0/i.exec(navigator.userAgent)][0][1] == 6);
	var newbox=document.getElementById(idname);
	newbox.style.zIndex="9999";
	newbox.style.display="block"
	newbox.style.position = !isIE6 ? "fixed" : "absolute";
	newbox.style.top =newbox.style.left = "50%";
	newbox.style.marginTop = - newbox.offsetHeight / 2 + "px";
	newbox.style.marginLeft = - newbox.offsetWidth / 2 + "px";  
	
	//var sel=document.getElementsByTagName("select");
//	for(var i=0;i<sel.length;i++){        
//		sel[i].style.visibility="hidden";
//	}
	
	function newbox_iestyle(){      
		newbox.style.marginTop = document.documentElement.scrollTop - newbox.offsetHeight / 2 + "px";
		newbox.style.marginLeft = document.documentElement.scrollLeft - newbox.offsetWidth / 2 + "px";
	}
	
	document.getElementById('close_'+idname).onclick=function(){newbox.style.display="none";for(var i=0;i<sel.length;i++){
	sel[i].style.visibility="visible";
	}}

}