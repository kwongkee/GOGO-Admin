var message = Vue.extend({
    template: '#message_template',
    data: function () {
        return {
            liveData: {},
            message_item: [],
            comment_item: [],
            comment_item_load_status: true,
            comment_add_input: '',
            comment_add_more_status: true,
            comment_add_loading_status: false,
            message_item_ids: [],
            message_loading_status: true,
            ban_status: false,
            page: 1,
            lid: 0,
            is_empty: false,
            empty: {
                status: false,
                title: '',
                desc: ''
            },
            auto_fun: {},
            liveVideo: {},
            liveTab: {}
        }
    },
    methods: {
        scroll: function () {
            var self = this;
            if(getScrollTop() + getWindowHeight() == getScrollHeight()){
                self.message_loading_status = true;
                if(self.liveData.live_type == 1){
                    var op = 'comment_item';
                }else{
                    var op = 'message_item';
                }
                self.$http.get(window.sysinfo.index_ajax_url, {op: op, lid: self.lid, page: self.page}).then(function(response) {
                    if(response.data[0]){
                        for(var i = 0; i < response.data.length; i++){
                            if(self.liveData.live_type == 1){
                                self.comment_item.push(response.data[i]);
                            }else{
                                self.message_item.push(response.data[i]);
                            }
                        }
                        self.message_loading_status = false;
                        self.page++;
                    }else {
                        window.removeEventListener('scroll', this.scroll, false);
                    }
                    self.message_loading_status = false;
                });
            }
        },
        auto_load: function () {
            var self = this;
            self.message_item_ids = obj_arr_tran(self.message_item, 'id');
            self.$http.get(window.sysinfo.index_ajax_url, {op: 'message_item', lid: self.lid, page: 1}).then(function(response) {
                if(response.data[0]){
                    for(var i = 0; i < response.data.length; i++){
                        if(self.message_item_ids.indexOf(response.data[i]['id']) < 0){
                            self.message_item.unshift(response.data[i]);
                        }
                    }
                }
            });
        },
        live_video_play: function () {
            var self = this;
            //是否开启视频直播
            if(self.liveData.live_type == 1){
                //视频直播源
                if(self.liveVideo.live_video_type === 0){
                    //乐视云
                    self.liveVideo.lstv_url = window.sysinfo.MODULE_URL + "iframe/letv.php?type=" + self.liveVideo.live_video_letv_play_type + "&activityid=" + self.liveVideo.letv_activityid + "&uu=" + self.liveVideo.letv_uu + "&vu=" + self.liveVideo.letv_vu;

                }else if(self.liveVideo.live_video_type === 1){
                    //暴风云
                    var b = new Base64();
                    var json = [];

                    if(self.liveVideo.live_video_bfy_play_type == 2){
                        //直播
                        json['vk'] = 'servicetype=2&uid=' + self.liveVideo.bfy_live_userid + '&fid=' + self.liveVideo.bfy_live_fid;
                        json['isautosize'] = 1;
                        var jmi = b.encode(json['vk']);
                        if(json['tk']){
                            jmi += '?tk='+json['tk'] + "&ifhtml5=true";
                        }else{
                            jmi += "?ifhtml5=true";
                        }

                        self.$http.get('http://livequery.baofengcloud.com/'+jmi).then(function(response) {
                            if(response.data.status == 0){
                                var ajson = response.data;
                                var comcdnflag = ajson.usecomcdnflag;
                                self.liveVideo.url = ajson.gcids[0]['urllist'][0];
                                self.liveVideo.mmime = 'application/x-mpegURL';
                                if (comcdnflag==1){
                                    self.liveVideo.url = ajson.comcdnurl2;
                                }else{
                                    self.liveVideo.url = self.liveVideo.url.replace(':8081',':8080').replace('?key=','.m3u8?key=');
                                }
                            }else{
                                self.liveData.live_type = 0;
                                self.empty.status = true;
                                self.empty.title = '视频直播已关闭';
                                self.empty.desc = '';
                            }
                        });
                    }else{
                        json['vk'] = 'servicetype=1&uid=' + self.liveVideo.bfy_vod_userid + '&fid=' + self.liveVideo.bfy_vod_fid;
                        json['isautosize'] = 1;
                        var jmi = b.encode(json['vk']);
                        if(json['tk']){
                            jmi += '?tk='+json['tk'] + "&ifhtml5=true";
                        }else{
                            jmi += "?ifhtml5=true";
                        }
                        self.$http.get('http://cdnquery.baofengcloud.com/'+jmi).then(function(response) {
                            var ajson = response.data;
                            if(ajson.covurlflag == 0 && ajson.defcovurl.length>0){
                                self.liveVideo.poster = ajson.defcovurl;
                            }else if(ajson.covurlflag == 1 && ajson.usrcovurl.length>0){
                                self.liveVideo.poster = ajson.usrcovurl;
                            }else{
                                self.liveVideo.poster = "http://filepry.baofengcloud.com/" + ajson.gcids[0]['gcid'] + '.cov.0.jpg';
                            }
                            self.liveVideo.url = ajson.gcids[0]['urllist'][0];
                            var comcdnflag = ajson.gcids[0]['usecomcdnflag'];

                            var USER_AGENT = navigator.userAgent;
                            var IS_IPHONE = (/iPhone/i).test(USER_AGENT);
                            var IS_IPAD = (/iPad/i).test(USER_AGENT);
                            var IS_IPOD = (/iPod/i).test(USER_AGENT);
                            var IS_IOS = IS_IPHONE || IS_IPAD || IS_IPOD;
                            var IS_WINDOWSWECHAT = (/WindowsWechat|(OS X (\d+)_)/).test(USER_AGENT);

                            if(IS_IOS){
                                self.liveVideo.mtype = 'm3u8';
                                self.liveVideo.mmime = 'application/x-mpegURL';
                                if (comcdnflag == 1){
                                    self.liveVideo.url = ajson.gcids[0]['comcdnurl2'];
                                }else{
                                    self.liveVideo.url = self.liveVideo.url.replace(/\:443/g,':8088');
                                }
                                self.liveVideo.url = self.liveVideo.url.replace(/\.mp4/g,'.m3u8');

                            }else if(IS_WINDOWSWECHAT){
                                self.liveVideo.mtype = 'mp4';
                                self.liveVideo.mmime = 'video/mp4';
                                if (comcdnflag==1){
                                    self.liveVideo.url = ajson.gcids[0]['comcdnurl2'];
                                }
                            }else{
                                self.liveVideo.mtype = 'm3u8';
                                self.liveVideo.mmime = 'application/x-mpegURL';
                                if (comcdnflag == 1){
                                    self.liveVideo.url = ajson.gcids[0]['comcdnurl2'];
                                }else{
                                    self.liveVideo.url = self.liveVideo.url.replace(/\:443/g,':8088');
                                }
                                self.liveVideo.url = self.liveVideo.url.replace(/\.mp4/g,'.m3u8');
                            }
                        });
                    }
                }else{
                    self.liveVideo.mmime = 'application/x-mpegURL';
                    self.liveVideo.url = self.liveVideo.m3u8_url;
                }
                self.liveVideo.poster = bottom_tool.info['thumb'];
            }
        }
    },
    route: {
        canActivate: function (transition) {
            //滚动条置顶
            window.scrollTo(0, 0);
            transition.next();
        },
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

                if(parseInt(bottom_tool.is_reward) != 0 && bottom_tool.is_reward !== '' && bottom_tool.scenes == 0){
                    ++bottom_tool.is_sub;
                }

                if(bottom_tool.wx_qrcode !== ''){
                    ++bottom_tool.is_sub;
                }

                if(bottom_tool.scenes == 2){
                    ++bottom_tool.is_sub;
                }

                self.empty.desc = self.liveData.description;

                $_share['title'] = bottom_tool.info['live_share_title'] || bottom_tool.info['name'];
                $_share['desc'] = bottom_tool.info['live_share_desc'] || bottom_tool.info['description'];
                $_share['imgUrl'] = window.sysinfo.attachurl_local + (bottom_tool.info['live_share_img'] || bottom_tool.info['thumb']);

                if(self.liveData.live_charge){
                    if(self.liveData.live_charge_status == 0){
                        this.$route.router.go({ path: '/charge', query: { lid: self.lid }});
                    }
                }

                self.liveVideo = self.liveData.live_video;
                self.liveTab = self.liveData.live_tab;
                if(self.liveData.live_type == 1){
                    //视频直播
                    self.live_video_play();
                    this.message_loading_status = false;
                    //加载评论
                    self.$http.get(window.sysinfo.index_ajax_url, {op: 'comment_item', lid: self.lid, page: self.page}).then(function(response) {
                        if(response.data[0]){
                            self.comment_item = response.data;
                            self.page++;
                        }else{
                            self.comment_item = {};
                        }
                    });
                }else{
                    this.message_loading_status = true;
                    self.empty.status = true;
                    self.empty.title = '暂无信息';
                    //加载图文直播信息
                    self.$http.get(window.sysinfo.index_ajax_url, {op: 'message_item', lid: self.lid, page: self.page}).then(function(response) {
                        if(response.data[0]){
                            self.message_item = response.data;
                            self.page++;
                            self.empty.status = false;
                        }else {
                            self.empty.status = true;
                        }
                        self.message_loading_status = false;
                    });
                }
                window.addEventListener('scroll', self.scroll, false);
            });

            //禁言列表
            self.$http.get(window.sysinfo.index_ajax_url, {op: 'ban_status', lid: self.lid}).then(function(response) {
                self.ban_status = response.data.status;
            });

            //增加直播查看数
            self.$http.get(window.sysinfo.index_ajax_url, {op: 'live_add_view', lid: self.lid}).then(function(response) {});
            sharedata['link'] = window.sysinfo.share_link + '&message_lid=' + self.lid + '#!/message?lid=' + self.lid;
            bottom_tool.lid = self.lid;
            transition.next();
        },
        data: function () {
            if(window.sysinfo.autoload_status){
                var autoload_time = window.sysinfo.autoload_time * 1000 || 10000;
                this.auto_fun = setInterval(this.auto_load, autoload_time);
            }
            bottom_tool.bottom_tool_status = true;
        },
        deactivate: function (transition) {
            this.message_item = [];
            this.page = 1;
            window.removeEventListener('scroll', this.scroll, false);
            this.comment_item_load_status = true;
            clearTimeout(this.auto_fun);
            sharedata['link'] = window.sysinfo.share_link;
            transition.next();
        }
    },
    components: {
        message_item: {
            template: '#message_item_template',
            props: ['message_item', 'ban_status', 'lid'],
            methods: {
                zan_status_toggle: function (index) {
                    var current_item = this.message_item[index];
                    var self = this;
                    var mid = current_item.id;
                    var lid = current_item.lid;
                    var new_status = !current_item.zan_status;
                    self.$http.get(window.sysinfo.index_ajax_url, {op: 'zan_change', mid: mid, lid:lid, status: new_status}).then(function(response) {
                        if(response.data.status){
                            if(new_status){
                                current_item.zan_count++;
                            }else{
                                current_item.zan_count--;
                                current_item.zan_count = Math.max.apply(null, [0, current_item.zan_count]);
                            }
                            current_item.zan_status = new_status;
                        }else{
                            if(response.data.msg){
                                tip.$emit('position');
                                tip.content = response.data.msg;
                                tip.tip_status = true;
                                tip.$emit('body_stop', true);
                            }
                        }
                    });
                },
                comm_status_toggle: function (index) {
                    var current_item = this.message_item[index];
                    var self = this;
                    var mid = current_item.id;
                    if(!current_item.comm_init){
                        current_item.comment_loading_status = true;
                        self.$http.get(window.sysinfo.index_ajax_url, {op: 'comment_list', mid: mid}).then(function(response) {
                            current_item.comment_list = response.data.list;
                            current_item.comment_loading_status = false;
                            current_item.comment_more_status = response.data.comment_more_status;
                        });
                        current_item.comm_init = true;
                    }
                    current_item.comment_status = !current_item.comment_status;
                },
                comment_loading :function (index) {
                    var current_item = this.message_item[index];
                    var self = this;
                    var mid = current_item.id;
                    current_item.comment_page++;
                    current_item.comment_loading_status = true;
                    self.$http.get(window.sysinfo.index_ajax_url, {op: 'comment_list', mid: mid, page: current_item.comment_page}).then(function(response) {
                        current_item.comment_loading_status = false;
                        for(var i = 0; i < response.data.list.length; i++){
                            current_item.comment_list.push(response.data.list[i]);
                        }
                        current_item.comment_more_status = response.data.comment_more_status;
                    });
                },
                comment_add :function (index) {
                    var current_item = this.message_item[index];
                    var self = this;
                    var lid = this.lid;
                    var mid = current_item.id;
                    var con = current_item.comment_con_input;
                    Vue.http.options.emulateJSON = true;
                    self.$http.post(window.sysinfo.index_ajax_url, {op: 'comment_add', lid: lid, mid: mid, con: con}).then(function(response) {
                        current_item.comment_con_input = '';
                        tip.$emit('position');
                        tip.content = response.data.msg;
                        tip.tip_status = true;
                        tip.$emit('body_stop', true);
                    });
                }
            }
        },
        comment_item: {
            template: '#comment_item_template',
            props: ['comment_item', 'ban_status', 'lid', 'comment_add_input', 'liveTab', 'liveData'],
            data: function () {
                return {
                    tab_css: {
                        tit: 'tit',
                        con: 'con',
                        act: 'act'
                    },
                    tab: [true, false, false, false]
                }
            },
            methods: {
                tab_change :function (index) {
                    var self = this;
                    for(var i = 0; i < self.tab.length; i++){
                        self.tab.$set(i, false);
                    }
                    self.tab.$set(index, true);

                },
                zan_status_toggle: function () {
                    var self = this;
                    var lid = self.lid;
                    var mid = 0;
                    var new_status = !self.$parent.liveData.zan_status;
                    self.$http.get(window.sysinfo.index_ajax_url, {op: 'zan_change', lid:lid, mid: mid, status: new_status}).then(function(response) {
                        if(response.data.status){
                            if(new_status){
                                self.$parent.liveData.zan_count++;
                            }else{
                                self.$parent.liveData.zan_count--;
                                self.$parent.liveData.zan_count = Math.max.apply(null, [0, self.$parent.liveData.zan_count]);
                            }
                            self.$parent.liveData.zan_status = new_status;
                        }else{
                            if(response.data.msg){
                                tip.$emit('position');
                                tip.content = response.data.msg;
                                tip.tip_status = true;
                                tip.$emit('body_stop', true);
                            }
                        }
                    });
                },
                comment_add :function () {
                    var self = this;
                    var lid = self.lid;
                    var con = self.comment_add_input;
                    Vue.http.options.emulateJSON = true;
                    self.$http.post(window.sysinfo.index_ajax_url, {op: 'comment_add', lid: lid, con: con}).then(function(response) {
                        self.comment_add_input = '';
                        if(response.data.comment_add_status == 1){
                            self.comment_item.unshift(response.data.comment_data);
                        }else{
                            tip.$emit('position');
                            tip.content = response.data.msg;
                            tip.tip_status = true;
                            tip.$emit('body_stop', true);
                        }
                    });
                }
            }
        }
    }
});