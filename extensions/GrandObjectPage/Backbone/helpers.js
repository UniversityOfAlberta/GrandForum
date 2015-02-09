dateTimeHelpers = {
    formatDate: function(date, inputFormat, outputFormat){
        inputFormat = typeof inputFormat !== 'undefined' ? inputFormat : 'yyyy-MM-dd';
        outputFormat = typeof outputFormat !== 'undefined' ? outputFormat : 'd MMMM, yyyy';
        var d = new Date();
        d.fromFormattedString(date, inputFormat);
        return d.toFormattedString(outputFormat);
    }
}

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
        var option = options[oId];
        $(el).attr(oId, option);
    }
    return el;
}

HTML.Name = function(attr){
    if(attr.indexOf('.') != -1){
        return attr.replace('.', '_');
    }
    else{
        return attr;
    }
}

HTML.Value = function(view, attr){
    if(attr.indexOf('.') != -1){
        var index = attr.indexOf('.');
        var data = view.model.get(attr.substr(0, index));
        return data[attr.substr(index+1)];
    }
    else{
        return view.model.get(attr);
    }
}

HTML.TextBox = function(view, attr, options){
    var el = HTML.Element("<input type='text' />", options);
    $(el).attr('name', HTML.Name(attr));
    $(el).attr('value', HTML.Value(view, attr));
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
    $(el).wrap('div');
    return $(el).parent().html();
}

HTML.Radio = function(view, attr, options){
    var el = HTML.Element("<span>");
    _.each(options.options, function(opt){
        var checked = "";
        if(HTML.Value(view, attr) == opt){
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
    _.each(options.options, function(opt){
        var selected = "";
        if(val.split(":")[0] == opt){
            selected = "selected='selected'";
        }
        $(el).append("<option " + selected + ">" + opt + "</option>");
    });
    var events = view.events;
    view.events['change select[name=' + HTML.Name(attr) + ']'] = function(e){
        view.model.set(attr, $(e.target).val());
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
    view.events['change input[name=' + HTML.Name(attr) + ']'] = evt;
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
        var newItems = Array();
        for(cId in current){
            var c = current[cId];
            var tuple = {};
            tuple[subName] = c;
            newItems.push(tuple);
        }
        var field = attr.substr(0, index);
        eval("view.model.set({" + field + ": newItems}, {silent:true});");
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
            newItems.push(tuple);
        }
        var field = attr.substr(0, index);
        eval("view.model.set({" + field + ": newItems}, {silent:true});");
    };
    view.delegateEvents(events);
    return el;
}
