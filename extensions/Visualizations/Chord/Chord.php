<?php

class Chord extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $width = "500";
    var $height = "500";
    var $options = true;
    var $fn = "";
    
    function __construct($url){
        $this->url = $url;
        parent::__construct();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath, $visualizations;
        $wgOut->addScript('<style rel="stylesheet" type="text/css">

}</style>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/raphael.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='height:".($this->height)."px;width:".($this->height)."px;display:inline-block;' class='chordChart' id='vis{$this->index}'>
                   </div>";
        if($this->options){
            $string .= "
                   <div style='display:inline-block;vertical-align:top;'>
                    <div style='margin-top:25px;margin-left:25px;' id='visOptions{$this->index}'></div>
                    <div style='margin-top:25px;margin-left:25px;' id='visSort{$this->index}'></div>
                    <div style='margin-top:25px;margin-left:25px;' id='visLegend{$this->index}'></div>
                   </div>";
        }
        $string .= <<<EOF
<script type='text/javascript'>
    var params{$this->index} = Array();
    function hashCode(str) { // java String#hashCode
        var hash = 0;
        if(str == null){
            return 0;
        }
        for (var i = 0; i < str.length; i++) {
           hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        return hash;
    }

    function intToRGB(i){
        return ("00" + ((i>>16)&0xFF).toString(16)).slice(-2) + 
               ("00" + ((i>>8)&0xFF).toString(16)).slice(-2) + 
               ("00" + (i&0xFF).toString(16)).slice(-2);
    }
  
  var lastChordRequest{$this->index} = null;
  
  var render{$this->index} = function(){};
  
  function onLoad{$this->index}(){
    lastChordRequest{$this->index} = $.get('{$this->url}' + params{$this->index}.join(''), function(data){
        
        var width = {$this->width},
            height = {$this->height};
        
        render{$this->index} = function(width, height){
            var svg;
            var chord;
            $("#vis{$this->index}").empty();
            $("#visLegend{$this->index}").empty();
            
            var colors = Array();
            var showLegend = true;
            
            if(data.colors != undefined && data.colors.length == data.labels.length){
                colors = data.colors;
                for(lId in data.colorHashs){
                    var label = data.colorHashs[lId];
                    if(label == data.labels[lId]){
                        showLegend = false;
                    }
                }
            }
            else{
                for(lId in data.colorHashs){
                    var label = data.colorHashs[lId];
                    if(label == data.labels[lId]){
                        showLegend = false;
                    }
                    colors.push("#" + intToRGB(hashCode(label)));
                }
            }
            if(showLegend){
                $("#visLegend{$this->index}").append("<h3>Legend</h3><table><tr><td valign='top'>").css('white-space','nowrap');
                var lastLabel = '';
                var i = 0;
                for(lId in data.colorHashs){
                    var label = data.colorHashs[lId];
                    var color = colors[lId].replace("#", "");
                    if(lastLabel != label){
                        if(i % 20 == 0){
                            $("#visLegend{$this->index} table tr").append("<td valign='top'>").css('white-space','nowrap');
                        }
                        $("#visLegend{$this->index} table tr td").last().append("<div class='" + color + "' style='font-size:10px;line-height:10px;'><div class='" + color + "' style='display:inline-block;width:15px;height:10px;background:#" + color + ";border:1px solid #888;'></div>" + label + "</div>");
                        $("#visLegend{$this->index} table tr td > div > div." + color).parent().mouseover(function(){
                            var ids = Array();
                            var classColor = $(this).children(0).attr('class');
                            $.each($("#vis{$this->index} path.outer"), function(index, val){
                                if($(val).attr('class').indexOf(classColor) != -1){
                                    ids.push(index);
                                }
                            });
                            $("#visLegend{$this->index} table tr td > div").not("." + classColor).stop();
                            $("#visLegend{$this->index} table tr td > div").not("." + classColor).animate({opacity: 0.5}, 250);
                            svg.select("path._" + classColor).data(chord.groups).on("mouseover")(undefined, ids);
                        });
                        $("#visLegend{$this->index} table tr td > div > div." + color).parent().mouseout(function(){
                            var ids = Array();
                            var classColor = $(this).children(0).attr('class');
                            $.each($("#vis{$this->index} path.outer"), function(index, val){
                                if($(val).attr('class').indexOf(classColor) != -1){
                                    ids.push(index);
                                }
                            });
                            var classColor = $(this).children(0).attr('class');
                            $("#visLegend{$this->index} table tr td div").not("." + classColor).stop();
                            $("#visLegend{$this->index} table tr td div").not("." + classColor).animate({opacity: 1}, 250);
                            svg.select("path._" + classColor).data(chord.groups).on("mouseout")(undefined, ids);
                        });
                        i++;
                    }
                    lastLabel = label;
                }
            }
            
            var padding = Math.max(0.01, Math.min(0.05, 1/data.labels.length));
            
            chord = d3.layout.chord()
                .padding(padding)
                .sortSubgroups(d3.descending)
                .matrix(data.matrix);
            
            var innerRadius = Math.min(width, height) * .44,
                outerRadius = Math.min(width, height) * .49;

            var fill = d3.scale.ordinal()
                .domain(d3.range(colors.length))
                .range(colors);

            svg = d3.select("#vis{$this->index}").append("svg")
                .attr("width", width)
                .attr("height", height)
              .append("g")
                .attr("transform", "translate(" + ((width / 2)) + "," + ((height / 2)) + ")");

            svg.append("g").selectAll("path")
                .data(chord.groups)
                .enter().append("path")
                .style("fill", function(d) { return fill(d.index); })
                .style("stroke", function(d) { return fill(d.index); })
                .style("cursor", "pointer")
                .attr("class", function(d) { return "outer _" + fill(d.index).replace('#', ''); })
                .attr("d", d3.svg.arc().innerRadius(innerRadius).outerRadius(outerRadius))
                .attr("title", function(d) { return data.labels[d.index]; })
                .on("click", function(d){ {$this->fn} })
                .on("mouseover", fade(.3))
                .on("mouseout", fade(1));
            
            svg.append("g")
                .attr("class", "chord")
              .selectAll("path")
                .data(chord.chords)
              .enter().append("path")
                .style("fill-opacity", 0.67)
                .style("stroke-width", '1px')
                .style("stroke", "#000")
                .style("stroke-opacity", 0.2)
                .attr("d", d3.svg.chord().radius(innerRadius))
                .style("fill", function(d) { return fill(d.target.index); })
                .style("opacity", 1)
                .attr("title", function(d){ if(data.chordLabels == undefined) {return ""; } return data.chordLabels[d.source.index][d.target.index]; })
                .on("mouseover", fade2(.3))
                .on("mouseout", fade2(1));
                
            $("#vis{$this->index} path.outer, #vis{$this->index} .chord path").qtip({
                position: {
                    target: 'mouse', // Track the mouse as the positioning target
                    adjust: { x: 15, y: 10 } // Offset it slightly from under the mouse
                },
                style: {
                    classes: 'qtip-tipsy'
                }
            });

            // Returns an array of tick angles and labels, given a group.
            function groupTicks(d) {
              var k = (d.endAngle - d.startAngle) / d.value;
              return d3.range(0, d.value, 5).map(function(v, i) {
                return {
                  angle: v * k + d.startAngle,
                  label: i % 1 ? null : v / 1
                };
              });
            }
            
            if($("#visOptions{$this->index}").length > 0 && 
               $("#visOptions{$this->index}").html().trim() == '' && typeof data.filterOptions != 'undefined'){
                $("#visOptions{$this->index}").append("<h3>Filter Options</h3><table>");
                for(oId in data.filterOptions){
                    var option = data.filterOptions[oId];
                    $("#visOptions{$this->index} table").append("<tr><td><input type='checkbox' value='" + option.param + "' " + option.checked + " /></td><td valign='top'><b>" + option.name + "</b></td></tr>");
                    if(option.inverted){
                        $("#visOptions{$this->index} input[value=" + option.param + "]").addClass('inverted');
                    }
                }
                $("#visOptions{$this->index} input").change(function(){
                    if((!$(this).hasClass('inverted') && !$(this).is(':checked')) || ($(this).hasClass('inverted') && $(this).is(':checked'))) {
                        params{$this->index}.push('&' + $(this).val());
                    }
                    else{
                        var index = params{$this->index}.indexOf('&' + $(this).val());
                        params{$this->index}[index] = null;
                        delete params{$this->index}[index];
                    }
                    lastChordRequest{$this->index}.abort();
                    onLoad{$this->index}();
                });
                
                if(data.dateOptions != undefined){
                    $("#visOptions{$this->index}").append("<tr><td><b>Date Range:</b><br /><div style='margin-top:5px;margin-left:1px;width:200px;' id='visDateSlider{$this->index}'></div><div id='visDateSliderLabels{$this->index}' class='steps'></td></tr>");
                    var dateOptions = data.dateOptions;
                    $("#visDateSlider{$this->index}").slider({
                        min: parseInt(dateOptions[0].date),
                        max: parseInt(dateOptions[dateOptions.length-1].date),
                        step: 1,
                        range: "max",
                        slide: function( event, ui ) {
                            for(pId in params{$this->index}){
                                var param = params{$this->index}[pId];
                                if(param.indexOf('&date=') !== -1){
                                    params{$this->index}[pId] = null;
                                    delete params{$this->index}[pId];
                                }
                            }
                            params{$this->index}.push('&date=' + ui.value);
                            
                            lastChordRequest{$this->index}.abort();
                            onLoad{$this->index}();
                        }
                    });
                    for(dId in dateOptions){
                        var dOption = dateOptions[dId];
                        if(dOption.checked == 'checked'){
                            $("#visDateSlider{$this->index}").slider("option", "value", dOption.date);
                        }
                        var perc = Math.floor((dId/Math.max(1, (dateOptions.length-1)))*100);
                        $("#visDateSliderLabels{$this->index}").append("<span style='left: " + perc + "%;' class='tick'>|<br />" + dOption.date + "</span>");
                    }
                }
            }
            
            if($("#visSort{$this->index}").length > 0 && 
               $("#visSort{$this->index}").html().trim() == '' && typeof data.sortOptions != 'undefined'){
                $("#visSort{$this->index}").append("<h3>Sorting Options</h3><table>");
                for(oId in data.sortOptions){
                    var option = data.sortOptions[oId];
                    $("#visSort{$this->index} table").append("<tr><td><input type='radio' value='" + option.value + "' name='visSort{$this->index}' " + option.checked + " /></td><td valign='top'><b>" + option.name + "</b></td></tr>");
                }
                $("#visSort{$this->index} input").change(function(){
                    $.each($("#visSort{$this->index} input"), function(i, val){
                        if($(val).is(':checked')){
                            params{$this->index}.push('&sortBy=' + $(val).val());
                        }
                        else{
                            var index = params{$this->index}.indexOf('&sortBy=' + $(val).val());
                            params{$this->index}[index] = null;
                            delete params{$this->index}[index];
                        }
                    });
                    lastChordRequest{$this->index}.abort();
                    onLoad{$this->index}();
                });
            }

            // Returns an event handler for fading a given chord group.
            function fade(opacity) {
              return function(g, i) {
                if(i instanceof Array){
                    svg.selectAll(".chord path")
                        .filter(function(d) { return i.indexOf(d.source.index) == -1 && i.indexOf(d.target.index) == -1; })
                        .transition()
                        .style("opacity", opacity);
                }
                else{
                    svg.selectAll(".chord path")
                        .filter(function(d) { return d.source.index != i && d.target.index != i; })
                        .transition()
                        .style("opacity", opacity);
                }
              };
            }
            
            function fade2(opacity) {
              return function(g, i) {
                var path = this;
                svg.selectAll(".chord path")
                    .filter(function(d) { return (path != this); })
                    .transition()
                    .style("opacity", opacity);
              };
            }
        }
        
        // Actually render it
        if($('#vis{$this->index}').is(':visible')){
            render{$this->index}(width, height);
        }
        $('#vis{$this->index}').show();
       });
    }
    
    $(document).ready(function(){
        onLoad{$this->index}();
    });

</script>
EOF;
        return $string;
    }
}


?>
