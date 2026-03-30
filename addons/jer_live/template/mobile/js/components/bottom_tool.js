var bottom_tool = new Vue({
    el: "#bottom_tool",
    data: {
        bottom_tool_status: false,
        info_status: false,
        qr_status: false,
        qr_img: "",
        reward_status: false,
        is_reward: 0,
        wx_qrcode: '',
        is_sub: 3,
        scenes: 9,
        lid: 0,
        info: {},
        zan_content_status: true,
        zan_qr_img: ""
    },
    methods: {
        to_live: function() {
            router.go({
                path: "/live"
            })
        },
        to_message_add: function(lid) {
            router.go({
                path: "/message_add",
                query: {
                    lid: lid
                }
            })
        },
        to_return: function() {
            history.go( - 1)
        },
        show_info: function() {
            this.reward_status = false;
            this.qr_status = false;
            this.zan_content_status = true;
            this.info_status = !this.info_status;
        },
        show_qr: function() {
            this.info_status = false;
            this.reward_status = false;
            this.qr_status = !this.qr_status;
            this.qr_img = window.sysinfo.wx_qrcode;
        },
        show_reward: function() {
            this.info_status = false;
            this.qr_status = false;
            this.zan_content_status = true;
            this.reward_status = !this.reward_status
        },
        refresh: function() {
            location.reload()
        },
        pay: function(fee) {
            var self = this;
            var lid = self.lid;
            self.$http.get(window.sysinfo.index_ajax_url, {
                op: "pay",
                type: "reward",
                fee: fee,
                lid: lid
            }).then(function(response) {
                var code_url = response.data.code_url;
                self.zan_content_status = false;
                self.zan_qr_img = "http://paysdk.weixin.qq.com/example/qrcode.php?data=" + code_url
            })
        },
        jsapi_pay: function(fee) {
            var self = this;
            var lid = self.lid;
            self.reward_status = false;
            self.zan_content_status = false;
            self.info_status = false;
            self.$http.get(window.sysinfo.index_ajax_url, {
                op: "jsapi_pay",
                type: "reward",
                fee: fee,
                lid: lid
            }).then(function(response) {
                self.zan_content_status = false;
                WeixinJSBridge.invoke("getBrandWCPayRequest", response.data,
                    function(res) {
                        if (res.err_msg == "get_brand_wcpay_request:ok") {
                            tip.$emit("position");
                            tip.content = "支付成功~";
                            tip.tip_status = true;
                            tip.$emit("body_stop", true)
                        } else {
                            tip.$emit("position");
                            tip.content = "支付失败，请联系管理员检查。";
                            tip.tip_status = true;
                            tip.$emit("body_stop", true)
                        }
                    });
            })
        }
    }
});