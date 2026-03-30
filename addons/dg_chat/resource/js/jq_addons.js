(function(a) {
	a.fn.extend({
		minTipsBox: function(b) {
			b = a.extend({
				tipsContent: "",
				tipsTime: 1
			}, b);
			var c = 1E3 * parseFloat(b.tipsTime);
			0 < a(".min_tips_box").length ? a(".min_tips_box").show() : a('<div class="min_tips_box"><b class="bg"></b><span class="tips_content"></span></div>').appendTo("body");
			(function() {
				a(".min_tips_box .tips_content").html(b.tipsContent);
				var c = a(".min_tips_box .tips_content").width() / 2 + 10;
				a(".min_tips_box .tips_content").css("margin-left", "-" + c + "px")
			})();
			setTimeout(function() {
				a(".min_tips_box").hide()
			}, c)
		}
	})
})(jQuery);