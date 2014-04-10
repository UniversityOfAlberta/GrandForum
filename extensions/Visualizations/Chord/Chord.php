<?php

require_once("SpecialChord.php");

class Chord extends Visualization {
    
    static $a = 0;
    var $url = "";
    var $width = "500";
    var $height = "500";
    
    function Chord($url){
        $this->url = $url;
        self::Visualization();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath, $visualizations;
        $wgOut->addScript('<style rel="stylesheet" type="text/css">

}</style>');
        if(strstr($wgOut->getScript(), 'raphael') === false){
            $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/raphael.js" type="text/javascript" charset="utf-8"></script>');
            $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Doughnut/doughnut/spinner.js" type="text/javascript" charset="utf-8"></script>');
        }
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='position:absolute;' id='visSpinner{$this->index}'></div>
                   <div style='height:".($this->height)."px;width:".($this->width)."px;display:inline-block;' class='chordChart' id='vis{$this->index}'>
                   </div>
                   <div style='display:inline-block;vertical-align:top;'>
                       <div style='margin-top:25px;margin-left:25px;' id='visOptions{$this->index}'></div>
                       <div style='margin-top:25px;margin-left:25px;' id='visSort{$this->index}'></div>
                       <div style='margin-top:25px;margin-left:25px;' id='visLegend{$this->index}'></div>
                   </div>";
        $string .= <<<EOF
<script type='text/javascript'>
    var params = Array();
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
  
  var lastChordRequest = null;
  
  function onLoad{$this->index}(){
    $("#visSpinner{$this->index}").empty();
    var spin = spinner("visSpinner{$this->index}", 10, 20, 12, 3, '#888');
    lastChordRequest = $.get('{$this->url}' + params.join(''), function(data){
        var svg;
        var chord;
        spin();
        $("#visSpinner{$this->index}").empty();
        $("#vis{$this->index}").empty();
        $("#visLegend{$this->index}").empty();
        
        var colors = Array();
        var showLegend = true;
        for(lId in data.colorHashs){
            var label = data.colorHashs[lId];
            if(label == data.labels[lId]){
                showLegend = false;
            }
            colors.push("#" + intToRGB(hashCode(label)));
        }
        if(showLegend){
            $("#visLegend{$this->index}").append("<h3>Legend</h3><table><tr><td valign='top'>").css('white-space','nowrap');
            var lastLabel = '';
            var i = 0;
            for(lId in data.colorHashs){
                var label = data.colorHashs[lId];
                var color = intToRGB(hashCode(label));
                if(lastLabel != label){
                    if(i % 20 == 0){
                        $("#visLegend{$this->index} table tr").append("<td valign='top'>").css('white-space','nowrap');
                    }
                    $("#visLegend{$this->index} table tr td").last().append("<div class='" + color + "' style='font-size:10px;line-height:10px;'><div class='" + color + "' style='display:inline-block;width:15px;height:10px;background:#" + color + ";border:1px solid #888;'></div>" + label + "</div>");
                    $("#visLegend{$this->index} table tr td > div > div." + color).parent().mouseover(function(){
                        var ids = Array();
                        var classColor = $(this).children(0).attr('class');
                        $.each($("path.outer"), function(index, val){
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
                        $.each($("path.outer"), function(index, val){
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

        var width = {$this->width},
            height = {$this->height},
            innerRadius = Math.min(width, height) * .25,
            outerRadius = innerRadius * 1.1;

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
            .attr("class", function(d) { return "outer _" + fill(d.index).replace('#', ''); })
            .attr("d", d3.svg.arc().innerRadius(innerRadius).outerRadius(outerRadius))
            .on("mouseover", fade(.1))
            .on("mouseout", fade(1));
            
        svg.append("g").selectAll("path")
            .data(chord.groups)
          .enter().append("svg:text")
              .each(function(d) { d.angle = (d.startAngle + d.endAngle) / 2; })
              .style("font-family", "sans-serif")
              .style("font-size", "10px")
              .attr("dy", ".35em")
              .attr("text-anchor", function(d) { return d.angle > Math.PI ? "end" : null; })
              .attr("transform", function(d) {
                return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")"
                    + "translate(" + (innerRadius + 25) + ")"
                    + (d.angle > Math.PI ? "rotate(180)" : "");
              })
              .text(function(d) { return data.labels[d.index]; });

        var ticks = svg.append("g").selectAll("g")
            .data(chord.groups)
          .enter().append("g")
            .attr("transform", function(d) {
              return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")"
                  + "translate(" + outerRadius + ",0)";
            });

        ticks.append("line")
            .attr("x1", 1)
            .attr("y1", 0)
            .attr("x2", 5)
            .attr("y2", 0)
            .style("stroke", "#000");

        ticks.append("text")
            .attr("x", 8)
            .attr("dy", ".35em")
            .attr("transform", function(d) { return d.angle > Math.PI ? "rotate(180)translate(-16)" : null; })
            .style("text-anchor", function(d) { return d.angle > Math.PI ? "end" : null; })
            .text(function(d) { return d.label; });

        svg.append("g")
            .attr("class", "chord")
          .selectAll("path")
            .data(chord.chords)
          .enter().append("path")
            .style("fill-opacity", 0.67)
            .style("stroke-width", '0.2px')
            .style("stroke", "#000")
            .attr("d", d3.svg.chord().radius(innerRadius))
            .style("fill", function(d) { return fill(d.target.index); })
            .style("opacity", 1);

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
        
        if($("#visOptions{$this->index}").html().trim() == '' && typeof data.filterOptions != 'undefined'){
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
                    params.push('&' + $(this).val());
                }
                else{
                    var index = params.indexOf('&' + $(this).val());
                    params[index] = null;
                    delete params[index];
                }
                lastChordRequest.abort();
                onLoad{$this->index}();
            });
            
            if(data.dateOptions != undefined){
                $("#visOptions{$this->index}").append("<tr><td><b>Date Range:</b><br /><div style='margin-top:5px;margin-left:1px;width:200px;' id='visDateSlider{$this->index}'></div><div id='visDateSliderLabels{$this->index}' class='steps'></td></tr>");
                var dateOptions = data.dateOptions;
                $("#visDateSlider{$this->index}").slider({
                    min: dateOptions[0].date,
                    max: dateOptions[dateOptions.length-1].date,
                    step: 1,
                    range: "max",
                    slide: function( event, ui ) {
                        for(pId in params){
                            var param = params[pId];
                            if(param.indexOf('&date=') !== -1){
                                params[pId] = null;
                                delete params[pId];
                            }
                        }
                        params.push('&date=' + ui.value);
                        
                        lastChordRequest.abort();
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
        
        if($("#visSort{$this->index}").html().trim() == '' && typeof data.sortOptions != 'undefined'){
            $("#visSort{$this->index}").append("<h3>Sorting Options</h3><table>");
            for(oId in data.sortOptions){
                var option = data.sortOptions[oId];
                $("#visSort{$this->index} table").append("<tr><td><input type='radio' value='" + option.value + "' name='visSort{$this->index}' " + option.checked + " /></td><td valign='top'><b>" + option.name + "</b></td></tr>");
            }
            $("#visSort{$this->index} input").change(function(){
                $.each($("#visSort{$this->index} input"), function(i, val){
                    if($(val).is(':checked')){
                        params.push('&sortBy=' + $(val).val());
                    }
                    else{
                        var index = params.indexOf('&sortBy=' + $(val).val());
                        params[index] = null;
                        delete params[index];
                    }
                });
                lastChordRequest.abort();
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
       });
    }
            
    $(document).ready(function(){
        if($('#vis{$this->index}:visible').length > 0){
            onLoad{$this->index}();
        }
    });

</script>
EOF;
        return $string;
    }
}


?>
