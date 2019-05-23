(function($){
 $.fn.timeline = function(options) {
 
    var id = $(this).attr('id');
    
    $.get(options.url, function(json){
        
        _.each(json.items, function(i, index){
            if(i.content != undefined && i.content.length > 25){
                i.content = i.content.substr(0, 25) + "...";
            }
            if(i.start != undefined){
                if(i.start.indexOf("0000") != -1){
                    delete json.items[index];
                }
                i.start = i.start.replace(/-00/g, "-01");
            }
            if(i.end != undefined){
                if(i.end.indexOf("0000") != -1){
                    delete json.items[index];
                }
                i.end = i.end.replace(/-00/g, "-01");
            }
        });
        
        json.items = _.filter(json.items, function(i){
            return (i != undefined && i.start != i.end);  
        });
        
        _.each(json.groups, function(g){
            $("#" + id + "Filter").append("<input type='checkbox' name='filter' value='" + g.id + "' checked='checked' /><span class='" + g.className + "'>" + g.content + "</span><br />");
        });
        
        var items = new vis.DataSet(json.items);
        var groups = new vis.DataSet(json.groups);
        
        var today = new Date();
        var lastYear = new Date();
        var nextYear = new Date();
        lastYear.setFullYear(lastYear.getFullYear()-1);
        nextYear.setFullYear(nextYear.getFullYear()+1);
        
        // create visualization
        var container = document.getElementById(id);
        var opts = {
            width: options.width,
            maxHeight: options.height,
            orientation: 'top',
            selectable: true,
            editable: false,
            showCurrentTime: true,
            margin: {item: 4, axis: 4},
            zoomMax: 315360000000,
            zoomMin: 26280000000,
            max: nextYear
        };
        
        this.alternateBackground = function(){
            $("div.vlabel", this).each(function(i, e){
                if(i % 2 == 1){
                    $(e).css("background", "rgba(0,0,0,0.05)");
                }
            });
              
            $("div.foreground div.group", this).each(function(i, e){
                if(i % 2 == 1){
                    $(e).css("background", "rgba(0,0,0,0.05)");
                }
            });
        }

        var timeline = new vis.Timeline(container);
        timeline.setOptions(opts);
        timeline.setGroups(groups);
        timeline.setItems(items);
        
        timeline.fit();
        
        timeline.on('select', function(properties){
            var id = _.first(properties.items);
            var item = items['_data'][id];
            
            var start = new Date(item.start);
            var end = new Date(item.end);
            
            var content = item.description.text;
            if(item.start != undefined){
                if(content != ""){
                    content += "<hr />";
                }
                content += "<p>";
                content += start.toDateString();
                if(item.end != undefined){
                    content += " &#8658; " + end.toDateString();
                }
                content += "</p>";
            }
            
            $(".selected", this).qtip({
                show: {
                    event: 'click',
                    solo: true
                },
                hide: false,
                style: {
                    classes: 'qtip-dark qtip-shadow qtip-rounded'
                },
                content: {
                    title: item.description.title,
                    text: content,
                    button: true
                },
                position: {
                    target: 'mouse', // Use the mouse position as the position origin
                    my: 'bottom center',
                    at: 'top center',
                    adjust: {
                    // Don't adjust continuously the mouse, just use initial position
                        mouse: false
                    }
                }
            });
        }.bind(this));
        
        this.alternateBackground();
        
        // Handle Filter Changes
        $("#" + id + "Filter > input").change(function(e){
            var values = new Array();
            $("#" + id + "Filter > input:checked").each(function(i, v){
                values.push($(v).val());
            });
            var filteredGroups = new vis.DataSet(_.filter(json.groups, function(g){
                return _.contains(values, g.id);
            }));
            timeline.setGroups(filteredGroups);
            this.alternateBackground();
            timeline.fit();
        }.bind(this));

    }.bind(this));
}
})( jQuery );
