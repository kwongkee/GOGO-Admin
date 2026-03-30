(function(window) {
    var util_ext = {};
    /**
     * val : video 值;
     * callback: 回调函数
     * base64options: base64(json($options))
     * options: {tabs: {'browser': 'active', 'upload': '', 'remote': ''}
	 **/
    util_ext.jer_video = function(val, callback, base64options, options) {
        var opts = {
            type :'video',
            direct : false,
            multiple : false,
            path : '',
            dest_dir : ''
        };
        if(val){
            opts.path = val;
        }

        opts = $.extend({}, opts, options);

        require(['jquery', '../../../addons/jer_live/static/js/videoUploader.js'], function($, videoUploader){
            videoUploader.show(function(images){
                if(images){
                    if($.isFunction(callback)){
                        callback(images);
                    }
                }
            }, opts);
        });
    };

    if (typeof define === "function" && define.amd) {
        define(['bootstrap'], function(){
            return util_ext;
        });
    } else {
        window.util_ext = util_ext;
    }

})(window);