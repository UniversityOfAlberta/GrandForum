var frame;
var labels;
var txt;
var dimension;
var lastP;

var data = Array();
Raphael.fn.doughnut = function (name, cx, cy, data, stroke, clickable, fn, raphael) {
    var paper = this,
        rad = Math.PI / 180,
        legend = data.legend,
        levels = data.levels;
        
    // Creates a sector with the given position information
    function sector(cx, cy, startAngle, endAngle, params, level) {
        endAngle = Math.min(359.9999, endAngle);
        var width = (((dimension/2) - ((dimension/levels.length)/4) - 2)/levels.length);
        var holeWidth = (dimension/levels.length)/4;
        
        var x0 = cx + (width*(level+0) - level + holeWidth) * Math.cos(-startAngle * rad),
            x1 = cx + (width*(level+1) - level + holeWidth) * Math.cos(-startAngle * rad),
            x2 = cx + (width*(level+1) - level + holeWidth) * Math.cos(-endAngle * rad),
            x3 = cx + (width*(level+0) - level + holeWidth) * Math.cos(-endAngle * rad),
            y0 = cy + (width*(level+0) - level + holeWidth) * Math.sin(-startAngle * rad),
            y1 = cy + (width*(level+1) - level + holeWidth) * Math.sin(-startAngle * rad),
            y2 = cy + (width*(level+1) - level + holeWidth) * Math.sin(-endAngle * rad),
            y3 = cy + (width*(level+0) - level + holeWidth) * Math.sin(-endAngle * rad);
        return paper.path(["M", x0, y0,
                           "L", x1, y1,
                           "A", (width*(level+1) - level + holeWidth), (width*(level+1) - level + holeWidth), 0, + (endAngle - startAngle > 180), 0, x2, y2,
                           "L", x3, y3,
                           "A", (width*(level+0) - level + holeWidth), (width*(level+0) - level + holeWidth), 0, + (endAngle - startAngle > 180), 1, x0, y0, "z"]).attr(params);
    }
    
    // Highlights the selected arc
    function highlight(p, ms){
        var color = p.color;
        var bcolor = p.bcolor;
        var angle = p.angle;
        var label = labels[0].attr('text');
        if(color.b == p.colorOrig.b && bcolor.b == p.bcolorOrig.b){
            color.b += 0.25;
            color.b = Math.min(1, color.b);
            
            bcolor.b += 0.25;
            bcolor.b = Math.min(1, bcolor.b);
            p.attr('gradient', angle + "-" + bcolor + "-" + color);
        }
        if(clickable && label != "Others"){
            frame.attr('cursor', 'pointer');
            labels[0].attr('cursor', 'pointer');
            labels[1].attr('cursor', 'pointer');
            frame.unclick();
            labels[0].unclick();
            labels[1].unclick();
            frame.click(function(){fn(label)});
            labels[0].click(function(){fn(label)});
            labels[1].click(function(){fn(label)});
        }
        else{
            frame.attr('cursor', 'default');
            labels[0].attr('cursor', 'default');
            labels[1].attr('cursor', 'default');
        }
    }
    
    // Resets the previously selected arc's colour
    function unhighlight(p, ms){
        var color = Raphael.getRGB(p.colorOrig);
        color = Raphael.rgb2hsb(color.r, color.g, color.b);
        var bcolor = Raphael.getRGB(p.colorOrig);
        bcolor = Raphael.rgb2hsb(bcolor.r, bcolor.g, bcolor.b);
        bcolor.b = bcolor.b/2;
        var angle = p.angle;
        
        p.color = color;
        p.bcolor = bcolor;
        
        p.attr('gradient', angle + "-" + bcolor + "-" + color);
    }
    
    //places the mouse over pop up window
    function placePopUp(){
    	var width = (dimension / (levels.length * 2 + 1)) - 1;
        var x = cx + (width*(lastP.l + 1))*Math.cos(-(lastP.angle + (lastP.angleplus/2)) * rad);
        var y = cy + (width*(lastP.l + 1))*Math.sin(-(lastP.angle + (lastP.angleplus/2)) * rad);
        popup(x, y);
    }
    
    // Creates a popup tooltip to display information about the highlighted arc
    var frameInit = false;
    var lastLabel = "";
    var lastValue = "";
    function popup(x, y){
        if(lastP.label != lastLabel || lastP.value != lastValue){
            lastLabel = lastP.label;
            lastValue = lastP.value;
            labels[0].attr({text: lastP.label});
            var type = data.data_type_plural;
            if(lastP.value == 1){
                type = data.data_type_singular;
            }
            labels[1].attr({text: lastP.value + " " + type}).attr({fill: "#80B3FF"});
            var ppp = r.popup(x, y, labels, "right", 1);
            var anim = Raphael.animation({
                    path: ppp.path,
                    transform: ["t", ppp.dx, ppp.dy]
                }, ms, 'linear');
            lx = labels[0].transform()[0][1] + ppp.dx;
            ly = labels[0].transform()[0][2] + ppp.dy;
            frame.show().stop().animate(anim);
            labels.animate({transform: ["t", lx, ly]}, ms, 'linear');
        }
        frame.show();
        labels.show();
        if(!frameInit){
            frame.mouseover(function () {
                highlight(lastP, ms);
                placePopUp();
            }).mouseout(function () {
                unhighlight(lastP, ms);
                frame.hide();
                labels.hide();
            });
            labels.mouseover(function () {
                highlight(lastP, ms);
                placePopUp();
            }).mouseout(function () {
                unhighlight(lastP, ms);
                frame.hide();
                labels.hide();
            });
            frameInit = true;
        }
    }

    var angle = 0,
        total = 0,
        start = 0,
        ms = 120;
        
    // Adds the sector to the doughnut
    process = function (l, i) {
        var label = levels[l]['labels'][i]
        var value = levels[l]['values'][i];
        
        var angleplus = 360 * value/(total);
        var color = Raphael.getRGB(legend[l]['color']);
        color = Raphael.rgb2hsb(color.r, color.g, color.b);
        var bcolor = Raphael.getRGB(legend[l]['color']);
        bcolor = Raphael.rgb2hsb(bcolor.r, bcolor.g, bcolor.b);
        bcolor.b = bcolor.b/2;
        
        var colorOrig = Raphael.getRGB(legend[l]['color']);
        colorOrig = Raphael.rgb2hsb(colorOrig.r, colorOrig.g, colorOrig.b);
        var bcolorOrig = Raphael.getRGB(legend[l]['color']);
        bcolorOrig = Raphael.rgb2hsb(bcolorOrig.r, bcolorOrig.g, bcolorOrig.b);
        bcolorOrig.b = bcolorOrig.b/2;

        var p = sector(cx, 
                       cy, 
                       angle, 
                       angle + angleplus,
                       {gradient: angle + "-" + bcolor + "-" + color, stroke: d3.rgb(legend[l]['color']).darker(4), "stroke-width": 1},
                       l);
        p.color = color;
        p.bcolor = bcolor;
        p.angle = angle;
        
        p.l = l;
        p.angle = angle;
        p.angleplus = angleplus;
        p.label = label;
        p.value = value;
        
        p.colorOrig = colorOrig;
        p.bcolorOrig = bcolorOrig;
                       
        p.mouseover(function () {
            lastP = p;
            highlight(lastP, ms);
            placePopUp();   
        }).mouseout(function () {
            unhighlight(p, ms);
            labels.hide();
            frame.hide();
        });
        if(clickable){
            if(label != "Others"){
                p.attr("cursor", "pointer");
                p.click(function(){
                    fn(label);
                });
            }
        }
        
        angle += angleplus;
    };
    
    for(var l = 0; l < levels.length; l++){
        angle = 0;
        total = 0;
        for (var i = 0, ii = levels[l]['values'].length; i < ii; i++) {
            total += levels[l]['values'][i];
        }
        for (var i = 0; i < ii; i++) {
            process(l, i);
        }
        
    }
};

// Sort the label/value pairs
function sortData(data){
    if(data.sort == 'desc' || data.sort == 'asc'){
        for(i = 0; i < data.levels.length; i++){
            vals = Array();
            for(j = 0; j < data.levels[i].labels.length; j++){
                vals[data.levels[i].labels[j]] = data.levels[i].values[j];
            }
            data.levels[i].labels.sort(function compare(a, b){
                return -(vals[a] - vals[b]);
            });
            data.levels[i].values.sort(function compare(a, b){
                return -(a - b);
            });
            if(data.sort == 'asc'){
                data.levels[i].values.reverse();
                data.levels[i].labels.reverse();
            }
        }
    }
}

// Filters the data so that any entries with index higher than data.limit
// Will be grouped together into an entry with the label "Others"
function filter(data){
    for(i = 0; i < data.levels.length; i++){
        var newValues = Array();
        var newLabels = Array();
        if(data.limit <= data.levels[i].labels.length){
            var otherTotal = 0;
            for(j = 0; j < data.levels[i].labels.length; j++){
                if(j >= data.limit){
                    otherTotal += data.levels[i].values[j];
                }
                else{
                    newValues.push(data.levels[i].values[j]);
                    newLabels.push(data.levels[i].labels[j]);
                }
            }
            if(otherTotal > 0){
                newValues.push(otherTotal);
                newLabels.push("Others");
            }
            
            data.levels[i].values = newValues;
            data.levels[i].labels = newLabels;
        }
    }
}

function create(holder, data, clickable, fn){
    (function (raphael) {
        $(function () {
            sortData(data);
            filter(data);
        
            var width = holder.width();
            var height = holder.height();
            if(typeof data.width != 'undefined' && data.width > 0){
                width = data.width;
                holder.width(width);
            }
            if(typeof data.height != 'undefined' && data.height > 0){
                height = data.height;
                holder.height(height);
            }
            
            var minDim = Math.min(width, height);
            holder.css('position', 'relative');
            holder.append("<div style='width:" + minDim + "px;height:" + minDim + "px;vertical-align:top;display:inline-block;position:absolute;top:0;left:0;z-index:101;' id='" + holder.attr('id') + "doughnut'></div>");
            
            dimension = minDim - 20;

            r = Raphael(holder.attr('id') + 'doughnut', width, height);
            
            r.doughnut(holder.attr('id') + 'doughnut', dimension/2 + 10, dimension/2 + 10, data, "#000", clickable, fn, raphael);
            
            labels = r.set();
            txt = {font: '12px Helvetica, Arial, sans-serif', fill: "#fff"};
            labels.push(r.text(60, 12, "").attr(txt));
            labels.push(r.text(60, 24, "0").attr(txt));
            labels.hide();
            frame = r.popup(100, 100, labels, "right").attr({fill: "#000", stroke: "#666", "stroke-width": 2, "fill-opacity": .7});
            frame.hide();
            
            holder.append("<div style='display:inline-block;position:absolute;right:0; z-index:100;' id='" + holder.attr('id') + "legend'></div>");
            var legendDiv = $("#" + holder.attr('id') + "legend");
            legendDiv.append("<center><b>Legend</b></center><br />");
            for(i = 0; i < data.legend.length; i++){
                legendDiv.append("<div style='border: 1px solid #000;vertical-align:middle;margin:5px;display:inline-block;width:28px;height:18px;background:" + data.legend[i].color + ";'></div>" + data.legend[i].name + "<br />");
            }
        });
    })(Raphael);
}

$.fn.doughnut = function(data, clickable, fn){
    var holder = this;
    var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    if(typeof data == 'string' && pattern.test(data)){
        // Data is a url, perform a GET request to retrieve a json file
        var width = this.width();
        var height = this.height();
        
        var minDim = Math.min(width, height);
        
        this.css('position', 'relative');
        this.append("<div class='spinner' style='padding-top:" + (minDim - (75+40)*2) + "px;text-align:center;vertical-align:top;display:inline-block;' id='" + this.attr('id') + "spinner'></div>");
        
        var id = $(this).attr('id');
        $.get(data, function(response){
            if(response[0].levels[0].values.length > 0){
                $("#" + id).empty();
                if(response[0].width == "100%"){
                    $("#" + id).width("100%");
                    response[0].width = Math.round($("#" + id).width());
                    response[0].height = Math.round(response[0].width*0.50);
                    create(holder, response[0], clickable, fn);
                    $("#" + id).width("100%");
                    var maxWidth = Math.round($("#" + id).width());
                    setInterval(function(){
                        if($("#" + id).is(":visible") && maxWidth != Math.round($("#" + id).width()) && 
                                                         response[0].width != Math.round($("#" + id).width())){
                            response[0].width = Math.round($("#" + id).width());
                            response[0].height = Math.round(response[0].width*0.50);
                            $("#" + id).empty();
                            create(holder, response[0], clickable, fn);
                            $("#" + id).width("100%");
                            maxWidth = Math.round($("#" + id).width());
                        }
                        $("#" + id).width("100%");
                    }, 100);
                }
                else{
                    create(holder, response[0], clickable, fn);
                }
            }
            else{
                $("#" + id).prev().remove();
                $("#" + id).next().remove();
                $("#" + id).remove();
            }
        });
    }
    else if(typeof data != 'string'){
        // Data is an object
        create(holder, data);
        var maxWidth = width;
        setInterval(function(){
            if($("#" + id).is(":visible") && maxWidth != $("#" + id).width()){
                maxWidth = $("#" + id).width();
                $("#" + id).empty();
                create(holder, data, clickable, fn);
            }
        }, 100);
    }
}

