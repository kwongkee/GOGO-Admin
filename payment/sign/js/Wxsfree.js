$("#Tel").blur(function() {
	var a = $("#Tel").val();
	if ($.trim($("#Tel").val()).length == 0) {
		$("#Tel").val("").focus().val(a);
		$("#Teltext").html("请输入会员注册手机号码");
		$("#Teltext").css("display", "block")
	} else {
		if (isPhoneNo($.trim($("#Tel").val())) == false) {
			$("#Tel").val("").focus().val(a);
			$("#Teltext").html("会员注册手机号码不正确");
			$("#Teltext").css("display", "block")
		} else {
			$("#Teltext").css("display", "none")
		}
	}
});
$("#Tel").keydown(function() {
	$("#Tel").maxLength(11)
});
$("#Phone").blur(function() {
	var a = $("#Phone").val();
	if ($.trim($("#Phone").val()).length == 0) {
		$("#Phone").val("").focus().val(a);
		$("#Phonetext").html("请输入持卡人银行卡预留手机号");
		$("#Phonetext").css("display", "block")
	} else {
		if (isPhoneNo($.trim($("#Phone").val())) == false) {
			$("#Phone").val("").focus().val(a);
			$("#Phonetext").html("您的输入的手机号码格式不正确，请重新输入！");
			$("#Phonetext").css("display", "block")
		} else {
			$("#Phonetext").css("display", "none")
		}
	}
});
$("#Phone").keydown(function() {
	$("#Phone").maxLength(11)
});
$("#UserName").blur(function() {
	var a = $("#UserName").val();
	if ($.trim($("#UserName").val()).length == 0) {
		$("#UserName").val("").focus().val(a);
		$("#UserNametext").html("请输入持卡人姓名");
		$("#UserNametext").css("display", "block")
	} else {
		if (isChinaName($.trim($("#UserName").val())) == false) {
			$("#UserName").val("").focus().val(a);
			$("#UserNametext").html("持卡人姓名不正确");
			$("#UserNametext").css("display", "block")
		} else {
			$("#UserNametext").css("display", "none")
		}
	}
});
$("#CardNo").blur(function() {
	var d = document.getElementById("carType");
	var b = d.selectedIndex;
	var a = d.options[b].value;
	var c = $("#CardNo").val();
	if ($.trim($("#CardNo").val()).length == 0) {
		$("#CardNo").val("").focus().val(c);
		$("#CardNotext").html("请输入带有银联标识的信用卡号");
		$("#CardNotext").css("display", "block")
	} else {
		if (a == "Ucard") {
			if (isCardNo($.trim($("#CardNo").val())) == false) {
				$("#CardNo").val("").focus().val(c);
				$("#CardNotext").html("您的银联信用卡号输入不正确");
				$("#CardNotext").css("display", "block")
			} else {
				$("#CardNotext").css("display", "none")
			}
		} else {
			if (a == "DCard") {
				if (!jjCard($.trim($("#CardNo").val()))) {
					$("#CardNo").val("").focus().val(c);
					$("#CardNotext").html("您输入的顺德农商借记卡号错误请重新输入！");
					$("#CardNotext").css("display", "block")
				} else {
					$("#CardNotext").css("display", "none")
				}
			}
		}
	}
});
$("#CardNo").keyup(function() {
	var d = document.getElementById("carType");
	var b = d.selectedIndex;
	var a = d.options[b].value;
	var c = $("#CardNo").val();
	var e = c.substring(0, 1);
	console.dir(e);
	if (a == "Ucard") {
		$("#CardNo").maxLength(16);
		if (e != 6) {
			$("#CardNotext").html("请输入以6开头的银联信用卡号");
			$("#CardNotext").css("display", "block");
			$("#CardNo").val("").focus().val("")
		} else {
			$("#CardNotext").css("display", "none")
		}
	} else {
		if (a == "DCard") {
			switch (parseInt(e)) {
			case 6:
				$("#CardNotext").css("display", "none");
				$("#CardNo").maxLength(16);
				break;
			default:
				$("#CardNotext").html("请输入以6开头的顺德农商借记卡");
				$("#CardNotext").css("display", "block");
				$("#CardNo").val("").focus().val("")
			}
		}
	}
});
$("#CertNo").blur(function() {
	var b = $("#CertNo").val();
	if ($.trim($("#CertNo").val()).length == 0) {
		$("#CertNo").val("").focus().val(b);
		$("#CertNotext").html("请输入持卡人证件号");
		$("#CertNotext").css("display", "block")
	} else {
		if (!checkCertNo(b)) {
			$("#CertNo").val("").focus().val(b);
			$("#CertNotext").html("您输入的证件号不正确！请重新输入...");
			$("#CertNotext").css("display", "block")
		} else {
			$("#CertNotext").css("display", "none")
		}
	}
});
$("#CertNo").keyup(function() {
	var t = $('#CertNo').val();
	if ($.trim($('#CertNo').val()).length == 0) {
		$('#CertNo').val('').focus().val(t);
		$('#CertNotext').html('请输入持卡人证件号');
		$('#CertNotext').css('display', 'block')
	} else if ($.trim($('#CertNo').val()).length == 18) {
		if (!checkCertNo(t)) {
			$('#CertNo').val('').focus().val(t);
			$('#CertNotext').html('您输入的证件号不正确！请重新输入...');
			$('#CertNotext').css('display', 'block')
		} else {
			$('#CertNotext').css('display', 'none')
		}
	}
});
$("#CarNo").keydown(function() {
	var a = document.getElementById("CarColor");
	var b = a.selectedIndex;
	var c = a.options[b].value;
	if (c == "green") {
		$("#CarNo").maxLength(6)
	} else {
		$("#CarNo").maxLength(5)
	}
});
$("#CarNo").blur(function() {
	var a = document.getElementById("id_type");
	var b = a.selectedIndex;
	var h = a.options[b].text;
	var c = h + $("#CarNo").val();
	var i = $("#CarNo").val();
	if ($.trim($("#CarNo").val()).length == 0) {
		$("#CarNo").val("").focus().val(i);
		$("#CarNotext").html("请输入车牌号");
		$("#CarNotext").css("display", "block")
	} else {
		var e = document.getElementById("CarColor");
		var g = e.selectedIndex;
		var f = e.options[g].value;
		if (f == "green") {
			$("#CarNo").maxLength(6);
			var d = isVehicleNumber2(c);
			if (!d) {
				$("#CarNo").val("").focus().val(i);
				$("#CarNotext").html("您的车牌输入错误，请重新输入。。。");
				$("#CarNotext").css("display", "block")
			} else {
				$("#CarNotext").css("display", "none")
			}
		} else {
			$("#CarNo").maxLength(5);
			var d = isVehicleNumber(c);
			if (!d) {
				$("#CarNo").val("").focus().val(i);
				$("#CarNotext").html("您的车牌输入错误，请重新输入。。。");
				$("#CarNotext").css("display", "block")
			} else {
				$("#CarNotext").css("display", "none")
			}
		}
	}
});
$("#CarNo").focus(function() {
	var c = document.getElementById("id_type");
	var b = c.selectedIndex;
	var a = c.options[b].text;
	if (a == "请选择车牌") {
		alert("请选择车牌")
	}
});

function checkCertNo(g) {
	var c = {
		11: "北京",
		12: "天津",
		13: "河北",
		14: "山西",
		15: "内蒙古",
		21: "辽宁",
		22: "吉林",
		23: "黑龙 江",
		31: "上海",
		32: "江苏",
		33: "浙江",
		34: "安徽",
		35: "福建",
		36: "江西",
		37: "山东",
		41: "河南",
		42: "湖 北",
		43: "湖南",
		44: "广东",
		45: "广西",
		46: "海南",
		50: "重庆",
		51: "四川",
		52: "贵州",
		53: "云南",
		54: "西 藏",
		61: "陕西",
		62: "甘肃",
		63: "青海",
		64: "宁夏",
		65: "新疆",
		71: "台湾",
		81: "香港",
		82: "澳门",
		91: "国 外"
	};
	var a = 0;
	var m = g;
	var b = m.length;
	if (!/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(m)) {
		return false
	}
	if (c[parseInt(m.substr(0, 2))] == null) {
		return false
	}
	if (b == 15) {
		sBirthday = "19" + m.substr(6, 2) + "-" + Number(m.substr(8, 2)) + "-" + Number(m.substr(10, 2));
		var h = new Date(sBirthday.replace(/-/g, "/"));
		var n = h.getFullYear().toString() + "-" + (h.getMonth() + 1) + "-" + h.getDate();
		if (sBirthday != n) {
			return false
		}
		m = m.substring(0, 6) + "19" + m.substring(6, 15);
		m = m + GetVerifyBit(m)
	}
	var l = m.substring(6, 10);
	if (l < 1900 || l > 2078) {
		return false
	}
	m = m.replace(/x$/i, "a");
	sBirthday = m.substr(6, 4) + "-" + Number(m.substr(10, 2)) + "-" + Number(m.substr(12, 2));
	var h = new Date(sBirthday.replace(/-/g, "/"));
	if (sBirthday != (h.getFullYear() + "-" + (h.getMonth() + 1) + "-" + h.getDate())) {
		return false
	}
	for (var f = 17; f >= 0; f--) {
		a += (Math.pow(2, f) % 11) * parseInt(m.charAt(17 - f), 11)
	}
	if (a % 11 != 1) {
		return false
	}
	var j = new Array();
	j = new Array("11111119111111111", "12121219121212121");
	for (var e = 0; e < j.length; e++) {
		if (m.indexOf(j[e]) != -1) {
			return false
		}
	}
	return true
}
function isChinaName(a) {
	var b = /^[\u4E00-\u9FA5]{1,6}$/;
	return b.test(a)
}
function isCertNo(a) {
	var b = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
	return b.test(a)
}
function isCardNo(a) {
	var b = /^\d{16}$/g;
	return b.test(a)
}
function is_CardNo(a) {
	var b = /^\d{19}$/g;
	return b.test(a)
}
jQuery.fn.maxLength = function(a) {
	this.each(function() {
		var c = this.tagName.toLowerCase();
		var b = this.type ? this.type.toLowerCase() : null;
		if (c == "input" && b == "text" || b == "password") {
			this.maxLength = a
		} else {
			if (c == "textarea") {
				this.onkeypress = function(h) {
					var f = h || event;
					var g = f.keyCode;
					var d = document.selection ? document.selection.createRange().text.length > 0 : this.selectionStart != this.selectionEnd;
					return !(this.value.length >= a && (g > 50 || g == 32 || g == 0 || g == 13) && !f.ctrlKey && !f.altKey && !d)
				};
				this.onkeyup = function() {
					if (this.value.length > a) {
						this.value = this.value.substring(0, a)
					}
				}
			}
		}
	})
};

function doSubmit() {
	var c = document.getElementById("id_type");
	var d = c.selectedIndex;
	var e = c.options[d].text;
	var f = e + $("#CarNo").val();
	var g = document.getElementById("CarColor");
	var h = g.selectedIndex;
	var i = g.options[h].value;
	if (checkForm()) {
		$.ajax({
			type: 'POST',
			url: 'Wxsfree.php',
			async: false,
			dataType: 'text',
			data: {
				Tel: $("#Tel").val(),
				CardNo: $("#CardNo").val(),
				UserName: $("#UserName").val(),
				CertType: certType,
				CertNo: $("#CertNo").val(),
				Phone: $("#Phone").val(),
				CarNo: f,
				token: $("#token").val(),
				Color: i,
			},
			success: function(a) {
				var b = jQuery.parseJSON(a);
				if (b.msg == 'success') {} else {}
			}
		})
	} else {
		alert('请填写所有必填选项！')
	}
}
function myCheck() {
	for (var a = 0; a < document.form1.elements.length - 1; a++) {
		if (document.form1.elements[a].value == " ") {
			document.form1.elements[a].focus();
			return false
		}
	}
	return true
}
function checkForm() {
	var a = document.form1.getElementsByTagName("input");
	for (var b = 0; b < a.length; b++) {
		if (a[b].value == "") {
			return false
		}
	}
	return true
}
function isVehicleNumber(b) {
	var a = false;
	if (b.length == 7) {
		var c = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$/;
		a = c.test(b)
	}
	return a
}
function isVehicleNumber2(b) {
	var a = false;
	if (b.length == 8) {
		var c = /^(([一-龥][a-zA-Z]|[一-龥]{2}\d{2}|[一-龥]{2}[a-zA-Z])[-]?|([wW][Jj][一-龥]{1}[-]?)|([a-zA-Z]{2}))([A-Za-z0-9]{5}|[DdFf][A-HJ-NP-Za-hj-np-z0-9][0-9]{4}|[0-9]{5}[DdFf])$/;
		a = c.test(b)
	}
	return a
}
function loadAddress() {
	var a = "";
	for (var b in address) {
		a += '<option value="' + b + '">' + address[b] + "</option>"
	}
	$("#id_type").append(a)
};