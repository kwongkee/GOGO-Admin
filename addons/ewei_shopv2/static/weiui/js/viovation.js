$('.check').bind('click', function(b) {
	var c = $(this);
	var d = c.attr('hrs');
	$.ajax({
		type: "post",
		url: "{$upOpenid}",
		async: true,
		data: {
			'ordersn': d
		},
		success: function(a) {
			var a = JSON.parse(a);
			if (a.code >= 1) {
				window.location.href = a.info
			} else {
				alert(a.msg)
			}
		}
	})
});
var provinces = new Array("京", "沪", "浙", "苏", "粤", "鲁", "晋", "冀", "豫", "川", "渝", "辽", "吉", "黑", "皖", "鄂", "津", "贵", "云", "桂", "琼", "青", "新", "藏", "蒙", "宁", "甘", "陕", "闽", "赣", "湘");
var keyNums = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P", "A", "S", "D", "F", "G", "H", "J", "K", "L", "确认", "Z", "X", "C", "V", "B", "N", "M", "删除");
var next = 0;

function showProvince() {
	$("#pro").html("");
	var a = "";
	for (var i = 0; i < provinces.length; i++) {
		a = a + addKeyProvince(i)
	}
	$("#pro").html("<ul class='clearfix ul_pro'>" + a + "<li class='li_close' onclick='closePro();'><span>关闭</span></li><li class='li_clean' onclick='cleanPro();'><span>清空</span></li></ul>")
}
function showKeybord() {
	$("#pro").html("");
	var a = "";
	for (var i = 0; i < keyNums.length; i++) {
		a = a + '<li class="ikey ikey' + i + ' ' + (i > 9 ? "li_zm" : "li_num") + ' ' + (i > 28 ? "li_w" : "") + '" ><span onclick="choosekey(this,' + i + ');">' + keyNums[i] + '</span></li>'
	}
	$("#pro").html("<ul class='clearfix ul_keybord'>" + a + "</ul>")
}
function addKeyProvince(a) {
	var b = '<li>';
	b += '<span onclick="chooseProvince(this);">' + provinces[a] + '</span>';
	b += '</li>';
	return b
}
function chooseProvince(a) {
	$(".input_pro span").text($(a).text());
	$(".input_pro").addClass("hasPro");
	$(".input_pp").find("span").text("");
	$(".ppHas").removeClass("ppHas");
	next = 0;
	showKeybord()
}
function choosekey(a, b) {
	if (b == 29) {
		var c = $(".ul_input").find('li');
		var d = '';
		$(c).each(function() {
			if ($(this).text() != '') {
				d += $.trim($(this).text())
			}
		});
		d = d.toUpperCase();
		var e = isVehicleNumber(d);
		if (!e) {
			alert('您输入的车牌号不正确');
			return false
		}
		$('#CarNo').val(d);
		$('#bk_form').submit();
		layer.closeAll()
	} else if (b == 37) {
		if ($(".ppHas").length == 0) {
			$(".hasPro").find("span").text("");
			$(".hasPro").removeClass("hasPro");
			showProvince();
			next = 0
		}
		$(".ppHas:last").find("span").text("");
		$(".ppHas:last").removeClass("ppHas");
		next = next - 1;
		if (next < 1) {
			next = 0
		}
	} else {
		if (next > 6) {
			return
		}
		for (var i = 0; i < $(".input_pp").length; i++) {
			if (next == 0 & b < 10 & $(".input_pp:eq(" + next + ")").hasClass("input_zim")) {
				layer.open({
					content: '车牌第二位为字母',
					skin: 'msg',
					time: 1
				});
				return
			}
			$(".input_pp:eq(" + next + ")").find("span").text($(a).text());
			$(".input_pp:eq(" + next + ")").addClass("ppHas");
			next = next + 1;
			if (next > 6) {
				next = 7
			}
			getpai();
			return
		}
	}
}
function isVehicleNumber(a) {
	var b = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}(([0-9]{5}[DF]$)|([DF][A-HJ-NP-Z0-9][0-9]{4}$))/;
	var c = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-HJ-NP-Z0-9]{4}[A-HJ-NP-Z0-9挂学警港澳]{1}$/;
	if (a.length == 7) {
		return c.test(a)
	} else if (a.length == 8) {
		return b.test(a)
	} else {
		return false
	}
}
function closePro() {
	layer.closeAll()
}
function cleanPro() {
	$(".ul_input").find("span").text("");
	$(".hasPro").removeClass("hasPro");
	$(".ppHas").removeClass("ppHas");
	next = 0
}
function trimStr(a) {
	return a.replace(/(^\s*)|(\s*$)/g, "")
}
function getpai() {
	var a = trimStr($(".car_input").text());
	$(".car_input").attr("data-pai", a)
}

window.onload = function() {
	$(".input_pro").click(function() {
		layer.open({
			type: 1,
			content: '<div id="pro"></div>',
			anim: 'up',
			shade: false,
			style: 'position:fixed; bottom:0; left:0; width: 100%; height: auto; padding:0; border:none;'
		});
		showProvince()
	}) $(".input_pp").click(function() {
		if ($(".input_pro").hasClass("hasPro")) {
			layer.open({
				type: 1,
				content: '<div id="pro"></div>',
				anim: 'up',
				shade: false,
				style: 'position:fixed; bottom:0; left:0; width: 100%; height: auto; padding:0; border:none;'
			});
			showKeybord()
		} else {
			$(".input_pro").click()
		}
	})
}