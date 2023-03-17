dateTimeHelpers = {
    formatDate: function(date, inputFormat, outputFormat){
        inputFormat = typeof inputFormat !== 'undefined' ? inputFormat : 'yyyy-MM-dd';
        outputFormat = typeof outputFormat !== 'undefined' ? outputFormat : 'd MMMM, yyyy';
        var d = new Date();
        d.fromFormattedString(date, inputFormat);
        return d.toFormattedString(outputFormat);
    }
}

function formatDate(date, inputFormat, outputFormat){
    return dateTimeHelpers.formatDate(date, inputFormat, outputFormat);
}

function number_format(n, c, t){
    var c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = ".", 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

function parseUrl(url) {
    var parser = document.createElement('a'),
        searchObject = {},
        queries, split, i;
    // Let the browser do the work
    parser.href = url;
    // Convert query string to object
    queries = parser.search.replace(/^\?/, '').split('&');
    for( i = 0; i < queries.length; i++ ) {
        split = queries[i].split('=');
        searchObject[split[0]] = split[1];
    }
    return {
        protocol: parser.protocol,
        host: parser.host,
        hostname: parser.hostname,
        port: parser.port,
        pathname: parser.pathname,
        search: parser.search,
        searchObject: searchObject,
        hash: parser.hash
    };
}

function nl2br(str) {
    return str.replace(/\n/g, "<br />");
}

function striphtml(str) {
    return str.replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function abbr(str, nChars){
    var abbr = $("<span></span>");
    if(str.length > nChars){
        $(abbr).html(str.substr(0, nChars) + '...');
        $(abbr).attr('title', str);
    }
    else{
        $(abbr).html(str);
    }
    $(abbr).wrap('div');
    return $(abbr).parent().html();
}

function showLanguage(language, textEn, textFr, delimiter){
    if(delimiter == undefined){
        delimiter = ' / ';
    }
    if(language == "English" || language == "en"){
        return textEn;
    }
    else if(language == "French" || language == "fr"){
        return textFr;
    }
    else if(language == "Bilingual" || language == "bi"){
        if(textEn != "" && textFr != ""){
            return textEn + delimiter + textFr;
        }
        else if(textEn != ""){
            return textEn;
        }
        else if(textFr != ""){
            return textFr;
        }
    }
    return "";
}

function subview(subviewName){
    return "<div data-subview='" + subviewName + "'></div>";
}

function HTML(){}

HTML.Element = function(tag, type, options){
    var el = document.createElement(tag);
    if(type != ''){
        el.setAttribute('type', type);
    }
    for(oId in options){
        if(oId != 'options'){
            var option = options[oId];
            el.setAttribute(oId, option);
        }
    }
    return el;
}

HTML.Name = function(attr){
    return attr.split(".").join("_")
               .split(" ").join("_")
               .split("/").join("_");
}

HTML.Value = function(model, attr){
    if(model.model != undefined){
        model = model.model;
    }
    if(attr.indexOf('.') != -1){
        var elems = attr.split(".");
        var last = _.last(elems);
        data = model.get(elems[0]);
        for (var i = 1; i < elems.length; ++i) {
            if (data[elems[i]] == undefined) {
                return '';
            } else {
                data = data[elems[i]];
            }
        }
        return data;
    }
    else{
        return model.get(attr);
    }
}

HTML.Hidden = function(view, attr, options){
    var el = HTML.Element("input", "hidden", options);
    el.setAttribute('type', 'hidden');
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('value', HTML.Value(view, attr));
    view.events['change input[name=' + HTML.Name(attr) + '][type=hidden]'] = function(e){
        if(attr.indexOf('.') != -1){
            var elems = attr.split(".");
            var recurse = function(data, depth) {
                if (depth < elems.length) {
                    if((data == undefined || data == '') && (!_.isArray(data[elems[depth]]) || !_.isObject(data[elems[depth]]))) {
                        data = {};
                        data[elems[depth]] = {};
                    }
                    data[elems[depth]] = recurse(data[elems[depth]], depth+1);
                    return data;
                } else {
                    return $(e.target).val();
                }
            }
            
            var data = view.model.get(elems[0]);
            data = recurse(data, 1);
            view.model.set(elems[0], _.clone(data));
            view.model.trigger('change', view.model);
            view.model.trigger('change:' + elems[0], view.model);
        }
        else{
            view.model.set(attr, $(e.target).val());
        }
    };
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + '][type=hidden]');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + '][type=hidden]', view.events['change input[name=' + HTML.Name(attr) + '][type=hidden]']);
    return el.outerHTML;
}

HTML.TextBox = function(view, attr, options){
    var el = HTML.Element("input", "text", options);
    el.setAttribute('type', 'text');
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('value', HTML.Value(view, attr));
    view.events['change input[name=' + HTML.Name(attr) + '][type=text]'] = function(e){
        if(attr.indexOf('.') != -1){
            var elems = attr.split(".");
            var recurse = function(data, depth) {
                if (depth < elems.length) {
                    if((data == undefined || data == '') && (!_.isArray(data[elems[depth]]) || !_.isObject(data[elems[depth]]))) {
                        data = {};
                        data[elems[depth]] = {};
                    }
                    data[elems[depth]] = recurse(data[elems[depth]], depth+1);
                    return data;
                } else {
                    return $(e.target).val();
                }
            }
            
            var data = view.model.get(elems[0]);
            data = recurse(data, 1);
            view.model.set(elems[0], _.clone(data));
            view.model.trigger('change', view.model);
            view.model.trigger('change:' + elems[0], view.model);
        }
        else{
            view.model.set(attr, $(e.target).val());
        }
    };
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + '][type=text]');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + '][type=text]', view.events['change input[name=' + HTML.Name(attr) + '][type=text]']);
    return el.outerHTML;
}

HTML.TextArea = function(view, attr, options, model){
    if(model == undefined){ model = view.model; }
    var el = HTML.Element("textarea", "text", options);
    var id = _.uniqueId(model.cid);
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('id', id);
    el.innerHTML = HTML.Value(model, attr);
    view.events['change textarea#' + id + '[name=' + HTML.Name(attr) + ']'] = function(e){
        if(attr.indexOf('.') != -1){
            var elems = attr.split(".");
            var recurse = function(data, depth) {
                if (depth < elems.length) {
                    if((data == undefined || data == '') && (!_.isArray(data[elems[depth]]) || !_.isObject(data[elems[depth]]))) {
                        data = {};
                        data[elems[depth]] = {};
                    }
                    data[elems[depth]] = recurse(data[elems[depth]], depth+1);
                    return data;
                } else {
                    return $(e.target).val();
                }
            }
            
            var data = model.get(elems[0]);
            data = recurse(data, 1);
            model.set(elems[0], _.clone(data));
            model.trigger('change', model);
            model.trigger('change:' + elems[0], model);
        }
        else{
            model.set(attr, $(e.target).val());
        }
    };
    view.undelegate('change', 'textarea#' + id + '[name=' + HTML.Name(attr) + ']');
    view.delegate('change', 'textarea#' + id + '[name=' + HTML.Name(attr) + ']', view.events['change textarea#' + id + '[name=' + HTML.Name(attr) + ']']);
    return el.outerHTML;
}

HTML.CheckBox = function(view, attr, options){
    var el = HTML.Element("input", "checkbox", options);
    el.setAttribute('name', HTML.Name(attr));
    if(HTML.Value(view, attr) == options.value){
        $(el).attr('checked', 'checked');
    }
    view.events['change input[name=' + HTML.Name(attr) + '][type=checkbox]'] = function(e){
        if(attr.indexOf('.') != -1){
            var index = attr.indexOf('.');
            var data = view.model.get(attr.substr(0, index));
            if($(e.currentTarget).is(":checked")){
                data[attr.substr(index+1)] = $(e.target).val();
            }
            else{
                data[attr.substr(index+1)] = options.default;
            }
            view.model.set(attr.substr(0, index), _.clone(data));
            view.model.trigger("change");
        }
        else{
            if($(e.currentTarget).is(":checked")){
                view.model.set(attr, $(e.target).val());
            }
            else{
                view.model.set(attr, options.default);
            }
        }
    };
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + '][type=checkbox]');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + '][type=checkbox]', view.events['change input[name=' + HTML.Name(attr) + '][type=checkbox]']);
    return el.outerHTML;
}

HTML.MultiCheckBox = function(view, attr, options){
    var el = HTML.Element("span", "", []);
    var val = HTML.Value(view, attr);
    _.each(options.options, function(opt){
        var checked = "";
        if(val == opt || (typeof opt == 'object' && (val == opt.value || val.indexOf(opt.value) != -1)) || val.indexOf(opt) != -1){
            checked = "checked='checked'";
        }
        if(typeof opt == 'object'){
            $(el).append("<div><input type='checkbox' style='vertical-align:middle;' name='" + HTML.Name(attr) + "' value='" + opt.value + "'" + checked + " /><span style='vertical-align:middle;'>" + opt.option + "</span></div>");
        }
        else{

            $(el).append("<div><input type='checkbox' style='vertical-align:middle;' name='" + HTML.Name(attr) + "' value='" + opt + "'" + checked + " /><span style='vertical-align:middle;'>" + opt + "</span></div>");
        }
    });
    view.events['change input[name=' + HTML.Name(attr) + '][type=checkbox]'] = function(e){
        if(attr.indexOf('.') != -1){
            var values = view.$('input[name=' + HTML.Name(attr) + '][type=checkbox]:checked');
            var index = attr.indexOf('.');
            var data = view.model.get(attr.substr(0, index));
            data[attr.substr(index+1)] = new Array();
            $(values).each(function(i, e){
                data[attr.substr(index+1)].push($(e).val());
            });
            view.model.set(attr.substr(0, index), _.clone(data));
            view.model.trigger("change");
        }
        else{
            var values = view.$('input[name=' + HTML.Name(attr) + '][type=checkbox]:checked');
            var data = new Array();
            $(values).each(function(i, e){
                data.push($(e).val());
            });
            view.model.set(attr, data);
        }
    };
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + '][type=checkbox]');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + '][type=checkbox]', view.events['change input[name=' + HTML.Name(attr) + '][type=checkbox]']);
    return el.outerHTML;
}

HTML.Radio = function(view, attr, options){
    var el = HTML.Element("span", "", []);
    var val = HTML.Value(view, attr);
    _.each(options.options, function(opt){
        var checked = "";
        if(val == opt || (typeof opt == 'object' && val == opt.value)){
            checked = "checked='checked'";
        }
        if(typeof opt == 'object'){
            $(el).append("<input type='radio' style='vertical-align:middle;' name='" + HTML.Name(attr) + "' value='" + opt.value + "'" + checked + " /><span style='vertical-align:middle;'>" + opt.option + "</span><br />");
        }
        else{
            $(el).append("<input type='radio' style='vertical-align:middle;' name='" + HTML.Name(attr) + "' value='" + opt + "'" + checked + " /><span style='vertical-align:middle;'>" + opt + "</span><br />");
        }
    });
    view.events['change input[name=' + HTML.Name(attr) + '][type=radio]'] = function(e){
        if(attr.indexOf('.') != -1){
            var index = attr.indexOf('.');
            var data = view.model.get(attr.substr(0, index));
            data[attr.substr(index+1)] = $(e.target).val();
            view.model.set(attr.substr(0, index), _.clone(data));
        }
        else{
            view.model.set(attr, $(e.target).val());
        }
    };
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + '][type=radio]');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + '][type=radio]', view.events['change input[name=' + HTML.Name(attr) + '][type=radio]']);
    return el.outerHTML;
}

HTML.DatePicker = function(view, attr, options){
    options.style = (options.style != undefined) ? 'width:72px;' + options.style : 'width:72px;';
    var el = HTML.Element("input", "datepicker", options);
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('value', HTML.Value(view, attr));
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
        if(attr.indexOf('.') != -1){
            var elems = attr.split(".");
            var recurse = function(data, depth) {
                if (depth < elems.length) {
                    if((data == undefined || data == '') && (!_.isArray(data[elems[depth]]) || !_.isObject(data[elems[depth]]))) {
                        data = {};
                        data[elems[depth]] = {};
                    }
                    data[elems[depth]] = recurse(data[elems[depth]], depth+1);
                    return data;
                } else {
                    return $(e.target).val();
                }
            }
            
            var data = view.model.get(elems[0]);
            data = recurse(data, 1);
            view.model.set(elems[0], _.clone(data));
            view.model.trigger('change', view.model);
            view.model.trigger('change:' + elems[0], view.model);
        }
        else{
            view.model.set(attr, $(e.target).val());
        }
    };
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + ']');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + ']', view.events['change input[name=' + HTML.Name(attr) + ']']);
    $(el).wrap('div');
    _.defer(function(){
        view.$('input[name=' + HTML.Name(attr) + ']').on("input change", function() {
            if($(this).val() != "" && $(this).val().match(/^\d{4}-\d{2}-\d{2}/) == null){
                $(this).css("background-color", "#feb8b8");
            }
            else{
                $(this).css("background-color", "");
            }
        }).trigger("change");
    });
    return $(el).parent().html();
}

HTML.Select = function(view, attr, options){
    var el = HTML.Element("select", "", options);
    el.setAttribute('name', HTML.Name(attr));
    var val = HTML.Value(view, attr);
    var foundSelected = false;
    _.each(options.options, function(opt){
        var selected = "";
        if(val == null){
            val = "";
        }
        if(val.split(":")[0] == opt || val == opt || 
           (typeof opt == 'object' && val.split(":")[0] == opt.value) || (typeof opt == 'object' && val == opt.value)){
            selected = "selected='selected'";
            foundSelected = true;
        }
        if(typeof opt == 'object'){
            $(el).append("<option " + selected + " value='" + opt.value + "'>" + opt.option + "</option>");
        }
        else{
            $(el).append("<option " + selected + ">" + opt + "</option>");
        }
    });

    if(!foundSelected){
        $(el).append("<option selected>" + val.split(":")[0] + "</option>");
    }

    view.events['change select[name=' + HTML.Name(attr) + ']'] = function(e){
        if(attr.indexOf('.') != -1){
            var elems = attr.split(".");
            var recurse = function(data, depth) {
                if (depth < elems.length) {
                    if((data == undefined || data == '') && (!_.isArray(data[elems[depth]]) || !_.isObject(data[elems[depth]]))) {
                        data = {};
                        data[elems[depth]] = {};
                    }
                    data[elems[depth]] = recurse(data[elems[depth]], depth+1);
                    return data;
                } else {
                    return $(e.target).val();
                }
            }
            
            var data = view.model.get(elems[0]);
            data = recurse(data, 1);
            view.model.set(elems[0], _.clone(data));
            view.model.trigger('change', view.model);
            view.model.trigger('change:' + elems[0], view.model);
        }
        else{
            view.model.set(attr, $(e.target).val());
        }
    };
    view.undelegate('change', 'select[name=' + HTML.Name(attr) + ']');
    view.delegate('change', 'select[name=' + HTML.Name(attr) + ']', view.events['change select[name=' + HTML.Name(attr) + ']']);
    $(el).wrap('div');
    return $(el).parent().html();
}

HTML.File = function(view, attr, options){
    var el = HTML.Element("input", "file", options);
    el.setAttribute('type', 'file');
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('value', HTML.Value(view, attr));
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
        var file = e.target.files[0];
        var reader = new FileReader();
        reader.addEventListener("load", function() {
            var fileObj = {
                filename: file.name,
                type: file.type,
                size: file.size,
                data: reader.result
            };
            fileObj.filename = file.name;
            if(attr.indexOf('.') != -1){
                var index = attr.indexOf('.');
                var data = view.model.get(attr.substr(0, index));
                data[attr.substr(index+1)] = fileObj;
                view.model.set(attr.substr(0, index), _.clone(data));
            }
            else{
                view.model.set(attr, fileObj);
            }
        }.bind(this));
        reader.readAsDataURL(file);
    };
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + ']');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + ']', view.events['change input[name=' + HTML.Name(attr) + ']']);
    return el.outerHTML;
}

HTML.MiscAutoComplete = function(view, attr, options){
    var el = HTML.Element("input", "text", options);
    el.setAttribute('name', HTML.Name(attr) + '_misc');
    el.setAttribute('value', HTML.Value(view, attr).replace("Misc: ", "").replace("Misc", ""));
    $(el).wrap('div');
    
    var evt = function(e){
        _.defer(function(){
            if(attr.indexOf('.') != -1){
                var index = attr.indexOf('.');
                var data = view.model.get(attr.substr(0, index));
                data[attr.substr(index+1)] = "Misc: " + $(e.target).val();
                view.model.set(attr.substr(0, index), _.clone(data), {silent: true});
            }
            else{                
                view.model.set(attr, "Misc: " + $(e.target).val(), {silent: true});
            }
        });
    };
    var events = view.events;
    events['change input[name=' + HTML.Name(attr) + '_misc]'] = evt;
    _.defer(function(){
        view.$('input[name=' + HTML.Name(attr) + '_misc]').autocomplete({
            source: options.misc,
            select: evt
        });
    });
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + '_misc]');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + '_misc]', view.events['change input[name=' + HTML.Name(attr) + '_misc]']);
    return $(el).parent().html();
}

HTML.TagIt = function(view, attr, options){
    options.name = HTML.Name(attr);
    var tagit = new TagIt(options);
    var tagitView = new TagItView({model: tagit});
    var el = tagitView.render();
    $("input", el).attr('id', 'tagit_' + attr);
    
    var index = attr.indexOf('.');
    var subName = attr.substr(index+1);
    var items = view.model.get(attr.substr(0, index));
    for(id in items){
        tagitView.tagit("createTag", items[id][subName]);
    }
    $(el).attr('name', HTML.Name(attr));
    $(el).attr('value', HTML.Value(view, attr));
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
        var current = tagitView.tagit("assignedTags");
        var newItems = new Array();
        for(cId in current){
            var c = current[cId];
            newItems.push(c);
        }
        var field = attr.substr(0, index);
        if(attr.indexOf('.') != -1){
            view.model.set(attr.substr(0, index), _.clone(newItems), {silent: true});
        }
        else{                
            view.model.set(attr, _.clone(newItems), {silent: true});
        }
    };
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + ']');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + ']', view.events['change input[name=' + HTML.Name(attr) + ']']);
    return el;
}

HTML.Switcheroo = function(view, attr, options){
    var switcheroo = new Switcheroo(options);
    var switcherooView = new SwitcherooView({model: switcheroo});
    var el = switcherooView.render();
    
    var index = attr.indexOf('.');
    var subName = attr.substr(index+1);

    $(el).attr('name', HTML.Name(attr));
    view.events['change input[name=' + options.name + ']'] = function(e){
        var current = switcherooView.switcheroo().getValue();
        var newItems = Array();
        var index = attr.indexOf('.');
        var subName = attr.substr(index+1);
        for(cId in current){
            var c = current[cId];
            var tuple = {};
            tuple[subName] = c;
            if(options.objs != undefined && options.objs[c] != undefined){
                newItems.push(options.objs[c]);
            }
            else{
                newItems.push(tuple);
            }
        }
        var field = attr.substr(0, index);
        eval("view.model.set({" + field + ": newItems}, {silent:true});");
    };
    view.undelegate('change', 'input[name=' + options.name + ']');
    view.delegate('change', 'input[name=' + options.name + ']', view.events['change input[name=' + options.name + ']']);
    return el;
}

HTML.ProjectSelector = function(view, attr, options){
    if(options == undefined){ options = {} };
    var id = _.uniqueId("project_selector_");
    var el = HTML.Element("div", "", options);
    el.setAttribute('id', id);
    $(el).append("<span class='throbber'></span>");
    $(el).wrap('div');
    if(view.projectSelectorView != undefined){
        // Teardown the old view to prevent double firing of events
        view.projectSelectorView.stopListening();
        view.projectSelectorView.undelegateEvents();
    }
    view.projectSelectorView = new ProjectSelectorView(_.extend(options, {model: view.model, el: el}));
    _.defer(function(){
        view.projectSelectorView.$el = $("#" + id);
        view.projectSelectorView.delegateEvents();
    });
    return $(el).parent().html();
}
