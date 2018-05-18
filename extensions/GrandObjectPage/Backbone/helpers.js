dateTimeHelpers = {
    formatDate: function(date, inputFormat, outputFormat){
        inputFormat = typeof inputFormat !== 'undefined' ? inputFormat : 'yyyy-MM-dd';
        outputFormat = typeof outputFormat !== 'undefined' ? outputFormat : 'd MMMM, yyyy';
        var d = new Date();
        d.fromFormattedString(date, inputFormat);
        return d.toFormattedString(outputFormat);
    }
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

function subview(subviewName){
    return "<div data-subview='" + subviewName + "'></div>";
}

function HTML(){}

HTML.Element = function(html, options){
    var el = $(html);
    for(oId in options){
        if(oId != 'options'){
            var option = options[oId];
            $(el).attr(oId, option);
        }
    }
    return el;
}

HTML.Name = function(attr){
    return attr.split(".").join("_")
               .split(" ").join("_")
               .split("/").join("_");
}

HTML.Value = function(view, attr){
    if(attr.indexOf('.') != -1){
        var elems = attr.split(".");
        var last = _.last(elems);
        data = view.model.get(elems[0]);
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
        return view.model.get(attr);
    }
}

HTML.TextBox = function(view, attr, options){
    var el = HTML.Element("<input type='text' />", options);
    $(el).attr('type', 'text');
    $(el).attr('name', HTML.Name(attr));
    $(el).attr('value', HTML.Value(view, attr));
    var events = view.events;
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
    view.delegateEvents(events);
    $(el).wrap('div');
    return $(el).parent().html();
}

HTML.TextArea = function(view, attr, options){
    var el = HTML.Element("<textarea type='text'></textarea>", options);
    $(el).attr('name', HTML.Name(attr));
    $(el).text(HTML.Value(view, attr));
    var events = view.events;
    view.events['change textarea[name=' + HTML.Name(attr) + ']'] = function(e){
        view.model.set(attr, $(e.target).val());
    };
    view.delegateEvents(events);
    $(el).wrap('div');
    return $(el).parent().html();
}

HTML.CheckBox = function(view, attr, options){
    var el = HTML.Element("<input type='checkbox' />", options);
    $(el).attr('name', HTML.Name(attr));
    if(HTML.Value(view, attr) == options.value){
        $(el).attr('checked', 'checked');
    }
    var events = view.events;
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
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
    view.delegateEvents(events);
    return $(el)[0].outerHTML;
}

HTML.Radio = function(view, attr, options){
    var el = HTML.Element("<span>");
    var val = HTML.Value(view, attr);
    _.each(options.options, function(opt){
        var checked = "";
        if(val == opt){
            checked = "checked='checked'"
        }
        $(el).append("<p><input type='radio' name='" + HTML.Name(attr) + "' value='" + opt + "'" + checked + " />" + opt + "</p>");
    });
    var events = view.events;
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
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
    view.delegateEvents(events);
    $(el).wrap('div');
    return $(el).parent().html();
}

HTML.DatePicker = function(view, attr, options){
    var el = HTML.Element("<input type='datepicker' />", options);
    $(el).attr('name', HTML.Name(attr));

    $(el).attr('value', HTML.Value(view, attr));
    var events = view.events;
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
        view.model.set(attr, $(e.target).val());
    };
    view.delegateEvents(events);
    $(el).wrap('div');
    _.defer(function(){
        view.$('input[name=' + HTML.Name(attr) + ']').keydown(function() {
            return false;
        });
    });
    return $(el).parent().html();
}

HTML.Select = function(view, attr, options){
    var el = HTML.Element("<select />", options);
    $(el).attr('name', HTML.Name(attr));
    var val = HTML.Value(view, attr);
    var foundSelected = false;
    _.each(options.options, function(opt){
        var selected = "";
        if(val.split(":")[0] == opt || (typeof opt == 'object' && val.split(":")[0] == opt.value)){
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

    var events = view.events;
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
    view.delegateEvents(events);
    $(el).wrap('div');
    return $(el).parent().html();
}

HTML.File = function(view, attr, options){
    var el = HTML.Element("<input type='file' />", options);
    $(el).attr('type', 'file');
    $(el).attr('name', HTML.Name(attr));
    $(el).attr('value', HTML.Value(view, attr));
    var events = view.events;
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
        var file = e.target.files[0];
        var reader = new FileReader();
        reader.addEventListener("load", $.proxy(function() {
            var fileObj = {
                filename: file.name,
                type: file.type,
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
        }, this));
        reader.readAsDataURL(file);
    };
    view.delegateEvents(events);
    $(el).wrap('div');
    return $(el).parent().html();
}

HTML.MiscAutoComplete = function(view, attr, options){
    var el = HTML.Element("<input type='text' />", options);
    $(el).attr('name', HTML.Name(attr));
    $(el).attr('value', HTML.Value(view, attr).replace("Misc: ", "").replace("Misc", ""));
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
    events['change input[name=' + HTML.Name(attr) + ']'] = evt;
    _.defer(function(){
        view.$('input[name=' + HTML.Name(attr) + ']').autocomplete({
            source: options.misc,
            select: evt
        });
    });
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
    var events = view.events;
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
    view.delegateEvents(events);
    return el;
}

HTML.Switcheroo = function(view, attr, options){
    var switcheroo = new Switcheroo(options);
    var switcherooView = new SwitcherooView({model: switcheroo});
    var el = switcherooView.render();
    
    var index = attr.indexOf('.');
    var subName = attr.substr(index+1);

    $(el).attr('name', HTML.Name(attr));
    var events = view.events;
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
    view.delegateEvents(events);
    return el;
}

HTML.ProjectSelector = function(view, attr, options){
    if(options == undefined){ options = {} };
    var id = _.uniqueId("project_selector_");
    var el = HTML.Element("<div id='" + id + "'><span class='throbber'></span></div>", options);
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
