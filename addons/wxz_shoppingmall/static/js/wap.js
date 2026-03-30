<!--打字机效果-->
    var charIndexa = -1;
    var stringLengtha = 0;
    var inputTexta;
	
    function writeContenta(e){
    if(e){
    	inputTexta = document.getElementById('contentToWritea').innerHTML;
    }
        if(charIndexa==-1){
            charIndexa = 0;
            stringLengtha = inputTexta.length;
        }
        var initStringa = document.getElementById('myContenta').innerHTML;
		initStringa = initStringa.replace(/<SPAN.*$/gi,"");
        
        var theChara = inputTexta.charAt(charIndexa);
       	var nextFourCharsa = inputTexta.substr(charIndexa,4);
       	if(nextFourCharsa=='<BR>' || nextFourCharsa=='<br>'){
       		theChara  = '<BR>';
       		charIndexa+=3;
       	}
        initStringa = initStringa + theChara + "<SPAN id='blink'>_</SPAN>";
        document.getElementById('myContenta').innerHTML = initStringa;
        charIndexa = charIndexa/1 +1;
if(charIndexa%2==1){
             document.getElementById('blink').style.display='none';
        }else{
             document.getElementById('blink').style.display='inline';
        }
                
        if(charIndexa<=stringLengtha){
            setTimeout('writeContenta(false)',150);
        }else{
        	blinkSpana();
        }  
    }
    var currentStylea = 'inline';
    function blinkSpana(){
    	if(currentStylea=='inline'){
    	currentStylea='none';
    	}else{
    	currentStylea='inline';
    	}
    	document.getElementById('blink').style.display = currentStylea;
    	setTimeout('blinkSpana()',500);
    }
<!--打字机效果 end -->