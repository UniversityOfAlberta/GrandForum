<?php

class Chord extends Visualisation {
    
    static $a = 0;
    var $url = "";
    var $width = "500";
    var $height = "500";
    
    function Chord($url){
        $this->url = $url;
        self::Visualisation();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath;
        $wgOut->addScript('<style rel="stylesheet" type="text/css">
        .chordChart {
            font: 10px sans-serif;
        }
        
        .chord path {
  fill-opacity: .67;
  stroke: #000;
  stroke-width: .5px;
}</style>');
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualisations/Chord/js/d3.min.js" type="text/javascript" charset="utf-8"></script>');
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $string = "<div style='height:".($this->height*1.1)."px;width:".($this->width*1.1)."px;float:left;' class='chordChart' id='vis{$this->index}'>
                   </div>
                   <div style='margin-top:100px;margin-left:25px;' id='visOptions{$this->index}'></div>";
        $string .= <<<EOF
<script type='text/javascript'>
    var params = Array();
  function onLoad{$this->index}(){
    var spin = spinner("vis{$this->index}", 40, 75, 12, 10, '#888');
    $.get('{$this->url}' + params.join(''), function(data){
        spin();
        var chord = d3.layout.chord()
            .padding(.05)
            .sortSubgroups(d3.descending)
            .matrix(data.matrix);

        var width = {$this->width},
            height = {$this->height},
            innerRadius = Math.min(width, height) * .25,
            outerRadius = innerRadius * 1.1;

        var fill = d3.scale.ordinal()
            .domain(d3.range(data.colors.length))
            .range(data.colors);

        var svg = d3.select("#vis{$this->index}").append("svg")
            .attr("width", width)
            .attr("height", height)
          .append("g")
            .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

        svg.append("g").selectAll("path")
            .data(chord.groups)
          .enter().append("path")
            .style("fill", function(d) { return fill(d.index); })
            .style("stroke", function(d) { return fill(d.index); })
            .attr("d", d3.svg.arc().innerRadius(innerRadius).outerRadius(outerRadius))
            .on("mouseover", fade(.1))
            .on("mouseout", fade(1));
            
        svg.append("g").selectAll("path")
            .data(chord.groups)
          .enter().append("svg:text")
              .each(function(d) { d.angle = (d.startAngle + d.endAngle) / 2; })
              .attr("dy", ".35em")
              .attr("text-anchor", function(d) { return d.angle > Math.PI ? "end" : null; })
              .attr("transform", function(d) {
                return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")"
                    + "translate(" + (innerRadius + 20) + ")"
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
        
        if($("#visOptions{$this->index}").html().trim() == ''){
            $("#visOptions{$this->index}").append("<h3>Options</h3><table>");
            for(oId in data.options){
                var option = data.options[oId];
                $("#visOptions{$this->index}").append("<tr><td><input type='checkbox' name='" + option.param + "' checked /></td><td valign='top'><b>" + option.name + "</b></td></tr>");
                $("#visOptions{$this->index} input[name=" + option.param + "]").change(function(){
                    if(!$(this).is(':checked')){
                        params.push('&' + $(this).attr('name'));
                    }
                    else{
                        var index = params.indexOf('&' + $(this).attr('name'));
                        params[index] = null;
                        delete params[index];
                    }
                    $("#vis{$this->index}").empty();
                    onLoad{$this->index}();
                });
            }
            $("#visOptions{$this->index}").append("</table>");
        }

        // Returns an event handler for fading a given chord group.
        function fade(opacity) {
          return function(g, i) {
            svg.selectAll(".chord path")
                .filter(function(d) { return d.source.index != i && d.target.index != i; })
              .transition()
                .style("opacity", opacity);
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
