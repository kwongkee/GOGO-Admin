var live = Vue.extend({
    template: '#live_template',
    data: function () {
        return {
            live_item: [],
            live_loading_status: true,
            page: 1,
            init: true
        }
    },
    methods: {
        scroll: function () {
            var self = this;
            self.live_loading_status = true;
            if(getScrollTop() + getWindowHeight() == getScrollHeight()){
                self.$http.get(window.sysinfo.index_ajax_url, {op: 'live_item', page: self.page}).then(function(response) {
                    if(response.data[0]){
                        for(var i = 0; i < response.data.length; i++){
                            self.live_item.push(response.data[i]);
                        }
                        self.page++;
                    }else {
                        window.removeEventListener('scroll', this.scroll, false);
                    }
                    self.live_loading_status = false;
                });
            }
        }
    },
    route: {
        canReuse: false,
        activate: function (transition) {
            var self = this;
            if(self.init && self.page === 1){
                self.live_item = [];
            }else{
                self.live_loading_status = false;
            }

            $_share['title'] = window.sysinfo.live_list_share_title;
            $_share['desc'] = window.sysinfo.live_list_share_desc;
            $_share['imgUrl'] = window.sysinfo.attachurl_local + window.sysinfo.live_list_share_img;
            transition.next();
        },
        data: function (transition) {
            var self = this;
            if(self.init){
                self.$http.get(window.sysinfo.index_ajax_url, {op: 'live_item', page: self.page}).then(function(response) {
                    self.live_item = response.data;
                    self.live_loading_status = false;
                    self.page++;
                    window.addEventListener('scroll', self.scroll, false);
                });

                self.init = false;
            }

            bottom_tool.bottom_tool_status = false;
            bottom_tool.info_status = false;
            bottom_tool.reward_status = false;
            transition.next();
        },
        deactivate: function (transition) {
            window.removeEventListener('scroll', this.scroll, false);
            this.page = 1;
            transition.next();
        }
    },
    components: {
        live_item: {
            template: '#live_item_template',
            props: ['live_item'],
            methods: {
                /**
                 * 切换message模块
                 * @param lid
                 */
                to_message: function (lid) {
                    this.$route.router.go({ path: '/message', query: { lid: lid }});
                }
            }
        }
    }
});