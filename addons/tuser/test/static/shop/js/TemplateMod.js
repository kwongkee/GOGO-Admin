;(function (factory) {
    if (typeof define === "function" && define.amd) {
        // AMD模式
        define(["PRselector"], factory);
    } else {
        // 全局模式
        if(!window.TemplateMod){
            window.TemplateMod = factory(PRselector);
        }
    }
}(function($) {
    var TM = {  //模版引擎
        dataPool: null,
        createItem: function (template, data, context) {
            var html, outer, dom;
            context = context || {};
            TM.dataPool = {
                data: data,
                context: context
            };
            TM.dataTree = {};
            html = TM.createHTML(template, data, context);
            outer = $('<div></div>').html(html);
            outer.find('[tm-datacode]').each(function () {
                this.tmData = TM.dataTree[$(this).attr('tm-datacode')];
                $(this).removeAttr('tm-datacode');
                $(this).attr('tm-hasData', 'true');
            });
            dom = outer.children();
            TM.dataPool = null;
            TM.dataTree = null;
            return dom;
        },
        createDom: function (template, data, context) {
            var html = TM.createHTML(template, data, context),
                outer = $('<div></div>').html(html),
                doms = outer.children(),
                r = TM.getRandomCode();
            TM.dataTree[r] = data;
            doms.attr('tm-datacode', r);
            return outer.html();
        },
        getRandomCode: function () {
            var r;
            do {
                r = (0 | Math.random() * 100000) + '' + new Date().getTime();
            } while (TM.dataTree[r]);
            TM.dataTree[r] = true;
            return r;
        },
        createHTML: function (template, data, context) {
            var groups = TM.getTemplateKeys(template),
                key, vars, w, content, value, d, i = 0, j, flag, html = '', oldData;
            oldData = TM.dataPool.data;
            TM.dataPool.data = data;
            for (; i < groups.length; i++) {
                if (groups[i].isFn) {
                    content = groups[i].content.match(/(^|;)\s?[\w\.]+\s??/g);
                    key = content[0].replace(/[\;\s]/g, '').split('.');
                    flag = true;
                    d = data;
                    for (j = 0; j < key.length; j++) {
                        if (typeof d[key[j]] === 'undefined') {
                            flag = false;
                            break;
                        }
                        d = d[key[j]];
                    }
                    if (flag) {
                        if (content.length > 1) {
                            content.pop();
                            w = groups[i].content.match(/\w+\(.+\)(\s??\.\s??\w+)?/)[0];
                            vars = TM.getVars(content);
                            value = (function () {
                                var TMval;
                                eval(vars + ' TMval = ' + 'TM.' + w + ';');
                                return TMval
                            })();
                        } else {
                            value = d;
                        }
                    } else {
                        value = '';
                    }
                    html += value;
                } else {
                    html += groups[i].content;
                }
            }
            TM.dataPool.data = oldData;
            return html;
        },
        getTemplateKeys: function (template) {//将模板划分为有逻辑的片段和纯文字的片段
            template = unescape(template);
            var data = [], k, key, d = data, n, tree = [], j, str = '', innerStr = '', outputer = [];
            while (template && (template.indexOf('{{') !== -1 || template.indexOf('}}') !== -1)) {
                if (template.indexOf('{{') == 0) {
                    n = [];
                    if (tree.length == 1) {
                        str += '{{';
                    } else if (tree.length > 1) {
                        innerStr += '{{';
                    }
                    tree.push(d.length);
                    d.push(n);
                    d = n;
                    template = template.substring(2);
                }
                if (template.indexOf('}}') == 0) {
                    tree.pop();
                    if (tree.length == 1) {
                        str += escape(innerStr) + '}}';
                        innerStr = '';
                    } else if (tree.length > 1) {
                        innerStr += '}}';
                    }
                    d = data;
                    for (j = 0; j < tree.length; j++) {
                        d = d[tree[j]];
                    }
                    template = template.substring(2);
                }
                k = Math.min(template.indexOf('{{'), template.indexOf('}}'));
                if (k < 0) {
                    k = Math.max(template.indexOf('{{'), template.indexOf('}}'));
                }
                key = template.substring(0, k);
                d.push(key);
                if (tree.length == 1) {
                    str += key;
                } else if (tree.length > 1) {
                    innerStr += key
                } else if (tree.length < 1) {
                    if (str) {
                        outputer.push({
                            content: str,
                            isFn: true
                        });
                        str = '';
                    }
                    outputer.push({
                        content: key,
                        isFn: false
                    });
                }
                template = template.substring(k);
            }
            outputer.push({
                content: template,
                isFn: false
            });
            return outputer;
        },
        getVars: function (content) {
            var i = 0, vars = "var ", ctt, start = true;
            for (; i < content.length; i++) {
                ctt = content[i].split('.')[0].replace(/\;\s/g, '');
                if (!start) {
                    vars += ',';
                } else {
                    start = false;
                }
                vars += ctt + '=data.' + ctt;
            }
            vars += ';';
            return vars;
        },
        repeat: function (template, max, min, data) {
            var i, j, html, content = '', tempData;
            if (typeof max == 'object' && Object.prototype.toString.call(max) === '[object Array]') {
                data = max;
                max = data.length;
                min = 0;
            } else if (typeof min == 'undefined') {
                min = 0;
            } else if (typeof min == 'object' && Object.prototype.toString.call(min) === '[object Array]') {
                data = min;
                min = 0;
            }
            for (i = min; i < max; i++) {
                tempData = {};
                if (data) {
                    tempData.repeatItem = data[i];
                    tempData.dataParent = data;
                    tempData.dataIndex = i;
                }
                tempData.repeatIndex = i;
                html = TM.createDom(template, tempData, TM.dataPool.context);
                content += html;
            }
            return content;
        },
        find: function (context, value, key) {
            var i, data;
            if (key === 'id') {
                return context[value];
            } else {
                for (i in context) {
                    if (context[i][key] == value) {
                        data = context[i];
                        break;
                    }
                }
                return data;
            }
        },
        judge: function (bool, template1, template2) {
            template2 = template2 || '';
            return !!bool ? TM.createHTML(template1, TM.dataPool.data, TM.dataPool.context) : TM.createHTML(template2, TM.dataPool.data, TM.dataPool.context);
        },
        val: function (value) {
            return TM.createHTML(value, TM.dataPool.data, TM.dataPool.context);
        }
    };
    return TM;
}));