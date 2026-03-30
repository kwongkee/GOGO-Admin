var message_add = Vue.extend({
    template: '#message_add_template',
    data: function () {
        return {
            file_list: [],
            view_list: [],
            xhr_list: [],
            file_input: {},
            lid: 0,
            content: '',
            video_img: '../addons/jer_live/template/mobile/img/video.png',
            index: 0
        }
    },
    route: {
        activate: function (transition) {
            var self = this;
            self.lid = transition.to.query.lid;

            if(!bottom_tool.info.name){
                self.$http.get(window.sysinfo.index_ajax_url, {op: 'live_info', lid: self.lid}).then(function(response) {
                    bottom_tool.info = response.data;
                });
            }
            self.file_list = [];
            self.view_list = [];
            transition.next();
        },
        data: function (transition) {
            this.lid = transition.to.query.lid;
            this.i = transition.to.query.i;
            bottom_tool.bottom_tool_status = true;
            bottom_tool.scenes = 1;
        }
    },
    methods: {
        fileUpload_img_choose: function () {
            this.file_input = document.getElementById("fileUpload_img_file");
            this.file_input.click();
            this.file_input.addEventListener('change', this.fileUpload_img_choose_do, false);
        },
        fileUpload_video_choose: function () {
            for(var i = 0; i < this.file_list.length; i++){
                if(this.file_list[i]['type']){
                    tip.$emit('position');
                    tip.content = '视频目前只能上传一个';
                    tip.tip_status = true;
                    tip.$emit('body_stop', true);
                    return;
                }
            }
            this.file_input = document.getElementById("fileUpload_video_file");
            this.file_input.click();
            this.file_input.addEventListener('change', this.fileUpload_video_choose_do, false);
        },
        fileUpload_video_choose_do: function (e) {
            var ne = e || window.event;
            var files = ne.target.files;
            var self = this;
            // 没有文件则退出
            if (files.length === 0) return;
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = self.fileUpload_video_callback;
            xhr.upload.onprogress = self.updateProgress;
            xhr.open('POST', window.sysinfo.index_ajax_url + '&op=upload_video');
            self.view_list.push({url: self.video_img, progress: 0});
            var form = new FormData();
            form.append('video_file', files[0]);
            xhr.send(form);

        },
        updateProgress: function (e) {
            var self = this;
            if (e.lengthComputable) {
                var percentComplete = e.loaded / e.total;
                self.view_list[self.index]['progress'] = percentComplete * 100;
            }
        },
        fileUpload_video_callback: function (e) {
            var ec = e.currentTarget;
            var self = this;
            if(ec.readyState == 4) {
                if(ec.status == 200) {
                    var data = eval('(' + ec.responseText + ')');
                    this.file_list.push({url: data.filename, type: 1});
                    ++self.index;
                }
            }
        },
        /**
         * 图片上传预览
         * @param e
         */
        fileUpload_img_choose_do: function (e) {
            var ne = e || window.event;
            var files = ne.target.files;
            var self = this;
            // 没有图片则退出
            if (files.length === 0) return;

            lrz(files[0]).then(function (rst) {
                // 处理成功会执行
                var xhr = new XMLHttpRequest();
                // 上传回调函数
                xhr.onreadystatechange = self.fileUpload_img_callback;
                xhr.onprogress = self.updateProgress;
                xhr.upload.onprogress = self.updateProgress;
                xhr.open('POST', window.sysinfo.siteroot + 'app/index.php?i=' + window.sysinfo.uniacid + '&c=utility&a=file&do=upload&type=image');

                self.view_list.push({url: rst.base64, progress: 0});
                // 添加参数和触发上传
                xhr.send(rst.formData);
            }).catch(function (err) {
                // 处理失败会执行
            }).always(function () {
                // 不管是成功失败，都会执行
            });
        },
        /**
         * 图片上传回调函数
         * @param e
         */
        fileUpload_img_callback: function(e){
            var ec = e.currentTarget;
            if(ec.readyState == 4) {
                if(ec.status == 200) {
                    var data = eval('(' + ec.responseText + ')');
                    this.file_list.push({url: data.filename, type: 0});
                    this.index = this.index + 1;
                }
            }
        },
        /**
         * 删除图片
         * @param i
         */
        rm_item: function (i) {
            this.view_list.splice(i, 1);
            this.file_list.splice(i, 1);
            --this.index;
        },
        message_send: function () {
            var self = this;
            var content = self.content;
            var lid = self.lid;
            var file_list = self.file_list;
            self.$http.get(window.sysinfo.index_ajax_url, {op: 'message_send', content: content, lid: lid, file_list: file_list}).then(function(response) {
                self.content = '';
                tip.$emit('position');
                tip.content = response.data.msg;
                tip.tip_status = true;
                tip.$emit('body_stop', true);
                tip.jump_url = '/message?i=' + this.i + '&lid=' + this.lid;
            });
        }
    }
});
