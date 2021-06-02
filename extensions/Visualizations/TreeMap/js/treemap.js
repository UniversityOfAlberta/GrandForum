(function($){
 $.fn.treemap = function(options) { 

    var w = $(this).width(),
        h = $(this).height(),
        x = d3.scale.linear().range([0, w]),
        y = d3.scale.linear().range([0, h]),
        root,
        node,
        type = "size",
        id = $(this).attr("id");

    var treemap = d3.layout.treemap()
        .round(false)
        .size([w, h])
        .sticky(true)
        .value(function(d) { return d.size; });

    var svg = d3.select("#" + id)
        .attr("class", "chart")
        .style("width", w + "px")
        .style("height", h + "px")
      .append("svg:svg")
        .attr("width", w)
        .attr("height", h)
      .append("svg:g")
        .attr("transform", "translate(.5,.5)");

    d3.json(options.url, function(data) {
      node = root = data;

      var categories = treemap.nodes(root)
          .filter(function(d) { return d.children && d.parent; }).reverse();

      var nodes = treemap.nodes(root)
          .filter(function(d) { return !d.children; });

      var cell = svg.selectAll("g.cell")
          .data(nodes)
        .enter().append("svg:g")
          .attr("title", function(d){ var ret = (d.tooltip || d.name) + "<table>" + 
                                                      "<tr><td>" + options.sizeLabel + ":</td><td align=right>" + options.sizeUnit + addCommas(Math.round(d.value)) + "</td><td>(" + Math.round(d.value*100*10/d.parent.value)/10 + "%)</td></tr>"; 
                                      if(options.countLabel  != ""){
                                         ret += "<tr><td>" + options.countLabel + ":</td><td align=right>" + options.countUnit + addCommas(recursiveCount(d)) + "</td><td>(" + Math.round(recursiveCount(d)*100*10/recursiveCount(d.parent))/10 + "%)</td></tr>";
                                      }
                                      ret += "</table>";
                                      return ret; })
          .attr("depth", function(d) { return d.depth; })
          .attr("class", "cell")
          .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
          .on("click", function(d) { return zoom(node == d.parent ? root : d.parent); });

      cell.append("svg:rect")
          .attr("width", function(d) { return d.dx - 1; })
          .attr("height", function(d) { return d.dy - 1; })
          .style("fill", function(d) { return (!d.color) ? d.parent.color : d.color; });

      cell.append("svg:text")
          .attr("class", "name")
          .attr("x", function(d) { return d.dx / 2; })
          .attr("y", function(d) { return d.dy / 2; })
          .attr("dy", ".35em")
          .attr("text-anchor", "middle")
          .text(function(d) { return d.name; })
          .style("cursor", function(d){ return (d.url != undefined && d.url != "") ? "pointer" : "default"; })
          .style("font-weight", "bold")
          .style("fill", function(d){ if(d3.lab(d.parent.color).l < 25) { return "#FFF"; } return "#000"; })
          .style("opacity", function(d) { d.w = this.getComputedTextLength(); if(type == "size" && d.size == 0){ return 0; } return d.dx > d.w ? 1 : 0; })
          .on("click", function(d){ if(d.url != undefined && d.url != ""){ d3.event.stopPropagation(); window.location = d.url; } })
          .on("mouseover", function(d){
              if(d.url != undefined && d.url != ""){
                  d3.select(this)
                    .style("text-decoration", "underline");
              }
          })
          .on("mouseout", function(d){
                d3.select(this)
                  .style("text-decoration", "none");
          });
          
      var catCell = svg.selectAll("g.catCell")
          .data(categories)
        .enter().append("svg:g")
          .attr("title", function(d){ var ret = (d.tooltip || d.name) + "<table>" + 
                                                      "<tr><td>" + options.sizeLabel + ":</td><td align=right>" + options.sizeUnit + addCommas(Math.round(d.value)) + "</td><td>(" + Math.round(d.value*100*10/d.parent.value)/10 + "%)</td></tr>"; 
                                      if(options.countLabel  != ""){
                                         ret += "<tr><td>" + options.countLabel + ":</td><td align=right>" + options.countUnit + addCommas(recursiveCount(d)) + "</td><td>(" + Math.round(recursiveCount(d)*100*10/recursiveCount(d.parent))/10 + "%)</td></tr>";
                                      }
                                      ret += "</table>";
                                      return ret; })
          .attr("depth", function(d) { return d.depth; })
          .attr("class", "catCell")
          .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
          .style("opacity", 0.9)
          .on("click", function(d) { return zoom(node == d.parent ? d : root); });
      
      catCell.append("svg:rect")
          .attr("width", function(d) { return d.dx - 1; })
          .attr("height", function(d) { return d.dy - 1; })
          .style("fill", function(d) { return d.color; })
          .style("stroke", "#000000")
          .style("stroke-width", "2px");

      catCell.append("svg:text")
          .attr("class", "name")
          .attr("x", function(d) { return d.dx / 2; })
          .attr("y", function(d) { return d.dy / 2; })
          .attr("dy", ".35em")
          .attr("text-anchor", "middle")
          .text(function(d) { return d.name; })
          .style("cursor", function(d){ return (d.url != undefined && d.url != "") ? "pointer" : "default"; })
          .style("font-weight", "bold")
          .style("fill", function(d){ if(d3.lab(d.color).l < 25) { return "#FFF"; } return "#000"; })
          .style("opacity", function(d) { d.w = this.getComputedTextLength(); if(type == "size" && d.size == 0){ return 0; } return d.dx > d.w ? 1 : 0; })
          .on("click", function(d){ if(d.url != undefined && d.url != ""){ d3.event.stopPropagation(); window.location = d.url; } })
          .on("mouseover", function(d){
              if(d.url != undefined && d.url != ""){
                  d3.select(this)
                    .style("text-decoration", "underline");
              }
          })
          .on("mouseout", function(d){
                d3.select(this)
                  .style("text-decoration", "none");
          });
          
      catCell.append("svg:text")
          .attr("class", "longname")
          .attr("x", function(d) { return d.dx / 2; })
          .attr("y", function(d) { return d.dy / 2; })
          .attr("dy", "1.4em")
          .attr("text-anchor", "middle")
          .text(function(d) { return (d.name != d.longname) ? d.longname : ""; })
          .style("cursor", "default")
          .style("font-size", "0.90em")
          .style("fill", function(d){ if(d3.lab(d.color).l < 25) { return "#FFF"; } return "#000"; })
          .style("opacity", function(d) { d.w2 = this.getComputedTextLength(); if(type == "size" && d.size == 0){ return 0; } return d.dx > d.w2 ? 0.85 : 0; });

      d3.selectAll("#" + id + "options input").on("change", function() {
        type = this.value;
        treemap.value(this.value == "size" ? size : count).nodes(root);
        zoom(node);
      });
      
      $("g").qtip({'style': 'qtip-tipsy', 'position': {my: 'bottom center', at: 'top center'}});
    });

    function size(d) {
      return d.size;
    }
    
    function count(d) {
      return 1;
    }
    
    function recursiveCount(d){
        var count = 0;
        if(!d.children){
            return 1;
        }
        else {
            _.each(d.children, function(child){
                count += recursiveCount(child);
            });
        }
        return count;
    }
    
    function addCommas(nStr){
	    nStr += '';
	    var x = nStr.split('.');
	    var x1 = x[0];
	    var x2 = x.length > 1 ? '.' + x[1] : '';
	    var rgx = /(\d+)(\d{3})/;
	    while (rgx.test(x1)) {
		    x1 = x1.replace(rgx, '$1' + ',' + '$2');
	    }
	    return x1 + x2;
    }

    function zoom(d) {
      var depth = d.depth;
        
      svg.selectAll("g")
        .filter(function(d){
            return (d.depth > depth);
        })
        .style("display", "inline");
      
      var kx = w / d.dx, ky = h / d.dy;
      x.domain([d.x, d.x + d.dx]);
      y.domain([d.y, d.y + d.dy]);

      var t = svg.selectAll("g").transition()
          .duration(500)
          .attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });

      t.filter(function(d){
            return (d.depth > depth);
        })
        .style("opacity", function(d){ return (!d.children) ? 1 : 0.9; });

      t.filter(function(d){
            return (d.depth <= depth);
        })
        .style("opacity", 0)
        .each("end", function(){
            d3.select(this).style("display", "none");
        });

      t.select("rect")
          .attr("width", function(d) { return kx * d.dx - 1; })
          .attr("height", function(d) { return ky * d.dy - 1; })

      t.selectAll("text.name")
          .attr("x", function(d) { return kx * d.dx / 2; })
          .attr("y", function(d) { return ky * d.dy / 2; })
          .style("opacity", function(d) { if(type == "size" && d.size == 0){ return 0; } return kx * d.dx > d.w ? 1 : 0; });
          
      t.selectAll("text.longname")
          .attr("x", function(d) { return kx * d.dx / 2; })
          .attr("y", function(d) { return ky * d.dy / 2; })
          .style("opacity", function(d) { if(type == "size" && d.size == 0){ return 0; } return kx * d.dx > d.w2 ? 0.85 : 0; });

      node = d;
      d3.event.stopPropagation();
    }
}    
})( jQuery );
