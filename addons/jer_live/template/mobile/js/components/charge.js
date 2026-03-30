var charge = Vue.extend({
    template: '#charge_template',
    data: function () {
        return {
            liveData: {},
            lid: 0,
            live_charge_price: 0,
            charge_content_status: true,
            charge_qr_status: false,
            charge_qr_img: ''
        }
    },
    route: {
        activate: function (transition) {
            var self = this;
            self.lid = transition.to.query.lid;

            //加载直播信息
            self.$http.get(window.sysinfo.index_ajax_url, {op: 'live_info', lid: self.lid}).then(function(response) {
                self.liveData = bottom_tool.info = response.data;
                bottom_tool.scenes = parseInt(bottom_tool.info.scenes);

                bottom_tool.is_reward = parseInt(window.sysinfo.is_reward);
                bottom_tool.wx_qrcode = window.sysinfo.wx_qrcode;
                bottom_tool.is_sub = 3;

                if(parseInt(bottom_tool.is_reward) != 0 && bottom_tool.is_reward !== ''){
                    ++bottom_tool.is_sub;
                }

                if(bottom_tool.wx_qrcode !== ''){
                    ++bottom_tool.is_sub;
                }

                if(bottom_tool.scenes == 2){
                    ++bottom_tool.is_sub;
                }

                self.live_charge_price = self.liveData.live_charge_price / 100;
            });

            bottom_tool.lid = self.lid;
            transition.next();
        },
        data: function () {
            bottom_tool.bottom_tool_status = true;
        }
    },
    methods: {
        pay: function(fee) {
            var self = this;
            var lid = self.lid;

            self.$http.get(window.sysinfo.index_ajax_url, {
                op: "pay",
                type: "charge",
                fee: fee,
                lid: lid
            }).then(function(response) {
                var code_url = response.data.code_url;
                self.charge_qr_status = true;
                self.charge_content_status = false;
                self.charge_qr_img = "http://paysdk.weixin.qq.com/example/qrcode.php?data=" + code_url
            })
        },
        jsapi_pay: function(fee) {
            var self = this;
            var lid = self.lid;

            self.$http.get(window.sysinfo.index_ajax_url, {
                op: "jsapi_pay",
                type: "charge",
                fee: fee,
                lid: lid
            }).then(function(response) {
                self.charge_content_status = false;
                WeixinJSBridge.invoke("getBrandWCPayRequest", response.data,
                    function(res) {
                        if (res.err_msg == "get_brand_wcpay_request:ok") {
                            tip.$emit("position");
                            tip.content = "支付成功~";
                            tip.tip_status = true;
                            tip.jump_url = '/message?lid=' + lid;
                            tip.$emit("body_stop", true)
                        } else {
                            tip.$emit("position");
                            tip.content = "支付失败，请联系管理员检查。";
                            tip.tip_status = true;
                            tip.$emit("body_stop", true)
                        }
                    })
            })
        }
    }
});
