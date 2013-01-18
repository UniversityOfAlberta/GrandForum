dateTimeHelpers = {
    formatDate: function(date, inputFormat, outputFormat){
        inputFormat = typeof inputFormat !== 'undefined' ? inputFormat : 'yyyy-MM-dd';
        outputFormat = typeof outputFormat !== 'undefined' ? outputFormat : 'd MMMM, yyyy';
        var d = new Date();
        d.fromFormattedString(date, inputFormat);
        return d.toFormattedString(outputFormat);
    }
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
    return $(el).parent().html();
}

HTML.TagIt = function(view, attr, options){
    var el = HTML.Element("<input type='text' />", options);
    $(el).attr('name', HTML.Name(attr));
    $(el).attr('value', HTML.Value(view, attr));
    var events = view.events;
    view.events['change input[name=' + HTML.Name(attr) + ']'] = function(e){
        view.model.set(attr, $(e.target).val());
    };
    view.delegateEvents(events);
    $(el).wrap('div');
    return $(el).parent().html();
}
