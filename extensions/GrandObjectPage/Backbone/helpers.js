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
        var ret = data[attr.substr(index+1)];
        if(ret == undefined){
            ret = "";
        }
        return ret;
    }
    else{
        return view.model.get(attr);
    }
}

HTML.TextBox = function(view, attr, options){
    var id = _.uniqueId(HTML.Name(attr) + "_");
    var el = HTML.Element("input", "text", options);
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('id', id);
    el.setAttribute('value', HTML.Value(view, attr));
    view.events['change input#' + id] = function(e){
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
    view.undelegate('change', 'input#' + id);
    view.delegate('change', 'input#' + id, view.events['change input#' + id]);
    return el.outerHTML;
}

HTML.TextArea = function(view, attr, options){
    var id = _.uniqueId(HTML.Name(attr) + "_");
    var el = HTML.Element("textarea", "text", options);
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('id', id);
    el.innerHTML = HTML.Value(view, attr);
    view.events['change textarea#' + id] = function(e){
        view.model.set(attr, $(e.target).val());
    };
    view.undelegate('change', 'textarea#' + id);
    view.delegate('change', 'textarea#' + id, view.events['change textarea#' + id]);
    return el.outerHTML;
}

HTML.CheckBox = function(view, attr, options){
    var id = _.uniqueId(HTML.Name(attr) + "_");
    var el = HTML.Element("input", "checkbox", options);
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('id', id);
    if(HTML.Value(view, attr) == options.value){
        el.setAttribute('checked', 'checked');
    }
    view.events['change input#' + id] = function(e){
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
    view.undelegate('change', 'input#' + id);
    view.delegate('change', 'input#' + id, view.events['change input#' + id]);
    return el.outerHTML;
}

HTML.Radio = function(view, attr, options){
    var el = HTML.Element("span", "", []);
    var val = HTML.Value(view, attr);
    _.each(options.options, function(opt){
        var checked = "";
        if(val == opt){
            checked = "checked='checked'"
        }
        $(el).append("<p><input type='radio' name='" + HTML.Name(attr) + "' value='" + opt + "'" + checked + " />" + opt + "</p>");
    });
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
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + ']');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + ']', view.events['change input[name=' + HTML.Name(attr) + ']']);
    return el.outerHTML;
}

HTML.Pages = function(view, attr, options){
    var value = HTML.Value(view, attr);
    var values = value.split("-");
    
    var start = "";
    if(values[0] != undefined){
        start = values[0].trim();
    }
    
    var end = "";
    if(values[1] != undefined){
        end = values[1].trim();
    }
    var el1 = HTML.Element("input", "integer", options);
    el1.setAttribute('name', HTML.Name(attr) + '_start');
    el1.setAttribute('value', start);
    
    var el2 = HTML.Element("input", "integer", options);
    el2.setAttribute('name', HTML.Name(attr) + '_end');
    el2.setAttribute('value', end);
    
    var fn = function(e){
        if(attr.indexOf('.') != -1){
            var index = attr.indexOf('.');
            var data = view.model.get(attr.substr(0, index));
            var start = $('input[name=' + HTML.Name(attr) + '_start]').val();
            var end = $('input[name=' + HTML.Name(attr) + '_end]').val();
            if(end == ""){
                data[attr.substr(index+1)] = $('input[name=' + HTML.Name(attr) + '_start]').val();
            }
            else{
                data[attr.substr(index+1)] = $('input[name=' + HTML.Name(attr) + '_start]').val() + "-" + $('input[name=' + HTML.Name(attr) + '_end]').val();
            }
            view.model.set(attr.substr(0, index), _.clone(data));
        }
        else{
            if(end == ""){
                view.model.set(attr, $('input[name=' + HTML.Name(attr) + '_start]').val());
            }
            else{
                view.model.set(attr, $('input[name=' + HTML.Name(attr) + '_start]').val() + "-" + $('input[name=' + HTML.Name(attr) + '_end]').val());
            }
        }
    };
    view.events['change input[name=' + HTML.Name(attr) + '_start]'] = fn;
    view.events['change input[name=' + HTML.Name(attr) + '_end]'] = fn;
    
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + '_start]');
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + '_end]');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + '_start]', view.events['change input[name=' + HTML.Name(attr) + '_start]']);
    view.delegate('change', 'input[name=' + HTML.Name(attr) + '_end]', view.events['change input[name=' + HTML.Name(attr) + '_end]']);
    
    $(el1).wrap('div');
    $(el1).parent().append(" - ").append(el2);
    
    return $(el1).parent().html();
}

HTML.DatePicker = function(view, attr, options){
    options.style = (options.style != undefined) ? 'width:6em;' + options.style : 'width:6em;';
    var id = _.uniqueId(HTML.Name(attr) + "_");
    var el = HTML.Element("input", "datepicker", options);
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('id', id);
    el.setAttribute('value', HTML.Value(view, attr));
    view.events['change input#' + id] = function(e){
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
    view.undelegate('change', 'input#' + id);
    view.delegate('change', 'input#' + id, view.events['change input#' + id]);
    return el.outerHTML;
}

HTML.Select = function(view, attr, options){
    var id = _.uniqueId(HTML.Name(attr) + "_");
    var el = HTML.Element("select", "", options);
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('id', id);
    var val = HTML.Value(view, attr);
    var foundSelected = false;
    
    if(typeof options.sorted == 'undefined' || options.sorted != false){
        if(typeof options.options[0] == 'object'){
            options.options = _.sortBy(options.options, 'option');
        }
        else{
            options.options = _.sortBy(options.options);
        }
    }
    
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
    view.events['change select#' + id] = function(e){
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
    view.undelegate('change', 'select#' + id);
    view.delegate('change', 'select#' + id, view.events['change select#' + id]);
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
    el.setAttribute('name', HTML.Name(attr));
    el.setAttribute('value', HTML.Value(view, attr).replace("Misc: ", "")
                                                   .replace("Misc", "")
                                                   .replace("Other: ", "")
                                                   .replace("Other", ""));
    var prefix = (options.prefix != undefined) ? options.prefix : "Misc";
    $(el).wrap('div');
    
    var evt = function(e){
        _.defer(function(){
            if(attr.indexOf('.') != -1){
                var index = attr.indexOf('.');
                var data = view.model.get(attr.substr(0, index));
                data[attr.substr(index+1)] = prefix + ": " + $(e.target).val();
                view.model.set(attr.substr(0, index), _.clone(data), {silent: true});
            }
            else{                
                view.model.set(attr, prefix + ": " + $(e.target).val(), {silent: true});
            }
        });
    };
    view.events['change input[name=' + HTML.Name(attr) + ']'] = evt;
    view.undelegate('change', 'input[name=' + HTML.Name(attr) + ']');
    view.delegate('change', 'input[name=' + HTML.Name(attr) + ']', view.events['change input[name=' + HTML.Name(attr) + ']']);
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
    var subName = "";
    if(index != -1){
        subName = attr.substr(index+1);
        var items = view.model.get(attr.substr(0, index));
        for(id in items){
            tagitView.tagit("createTag", items[id][subName]);
        }
    }
    else {
        var items = view.model.get(attr);
        for(id in items){
            tagitView.tagit("createTag", items[id]);
        }
    }
    
    $(el).attr('name', HTML.Name(attr));
    $(el).attr('value', HTML.Value(view, attr));
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
        var current = tagitView.tagit("assignedTags");
        var newItems = new Array();
        for(cId in current){
            var c = current[cId];
            if(subName != ""){
                var tuple = {};
                tuple[subName] = c;
            }
            else{
                tuple = c;
            }
            if(options.objs != undefined && options.objs[c] != undefined){
                newItems.push(options.objs[c]);
            }
            else{
                newItems.push(tuple);
            }
        }
        var field = attr.substr(0, index);
        if(attr.indexOf('.') != -1){
            var index = attr.indexOf('.');
            var data = view.model.get(attr.substr(0, index));
            data[attr.substr(index+1)] = newItems;
            view.model.set(attr.substr(0, index), _.clone(newItems));
        }
        else{                
            view.model.set(attr, _.clone(newItems));
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
