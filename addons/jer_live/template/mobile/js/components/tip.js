var tip = new Vue({
    el: "#tip",
    data: {
        content: "",
        tip_status: false,
        jump_url: ""
    },
    methods: {
        close_tip: function() {
            var self = this;
            this.tip_status = false;
            this.$emit("body_stop", false);
            if (this.jump_url) {
                router.go({
                    path: this.jump_url
                })
            }
        }
    },
    events: {
        position: function() {
            position("tip_message", "tip_mask", 100)
        },
        body_stop: function(status) {
            if (status) {
                document.addEventListener("touchmove", body_stop)
            } else {
                document.body.style.overflow = "auto";
                document.removeEventListener("touchmove", body_stop)
            }
        }
    }
});