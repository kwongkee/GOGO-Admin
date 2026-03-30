
//删除确认
function deleteWarning(msg) {
    if(msg==undefined){
        msg = "您确定要删除吗？";
    }
    return confirm(msg);
}
//检测金额输入框
function input_float_check(obj,max_int_num,max_float_num){
    if(isNaN(max_int_num) || max_int_num == ""){max_int_num = 0;}
    if(isNaN(max_float_num) || max_int_num == ""){max_float_num = 2;}
    var value = obj.value.replace(/[^\d\.]|^\./g,'');
    var p = value.indexOf('.');
    if(p != -1){
        var int_val = value.substring(0,p);
        if(max_int_num < int_val.length && max_int_num > 0){
            value = int_val.substring(0,max_int_num);
        }else{
            var float_val = value.substring(p+1).replace(/[\.]/g,'');
            if(max_float_num < float_val.length && max_float_num > 0){
                float_val = float_val.substring(0, max_float_num);
            }
            value = int_val+'.'+ float_val;
        }
    }else{
        if(max_int_num < value.length && max_int_num > 0){
            value = value.substring(0,max_int_num);
        }
    }
    obj.value = value;
}

//检测整数输入框
function input_int_check(obj,max_int_num){
    if(isNaN(max_int_num) || max_int_num == ""){max_int_num = 0;}
    var value = obj.value.replace(/[^\d]/g,'');
    if(max_int_num < value.length && max_int_num > 0){
        value = value.substring(0,max_int_num);
    }
    obj.value = value;
}
