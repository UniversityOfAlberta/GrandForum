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
          .filter(function(d) { return d.children && d.parent; });

      var nodes = treemap.nodes(root)
          .filter(function(d) { return !d.children; });

      var cell = svg.selectAll("g.cell")
          .data(nodes)
        .enter().append("svg:g")
          .attr("class", "cell")
          .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
          .on("click", function(d) { return zoom(node == d.parent ? root : d.parent); });

      cell.append("svg:rect")
          .attr("width", function(d) { return d.dx - 1; })
          .attr("height", function(d) { return d.dy - 1; })
          .style("fill", function(d) { return (!d.color) ? d.parent.color : d.color; });

      cell.append("svg:text")
          .attr("x", function(d) { return d.dx / 2; })
          .attr("y", function(d) { return d.dy / 2; })
          .attr("dy", ".35em")
          .attr("text-anchor", "middle")
          .text(function(d) { return d.name; })
          .style("cursor", "default")
          .style("opacity", function(d) { d.w = this.getComputedTextLength(); if(type == "size" && d.size == 0){return 0;} return d.dx > d.w ? 1 : 0; });
          
      var catCell = svg.selectAll("g.catCell")
          .data(categories)
        .enter().append("svg:g")
          .attr("class", "catCell")
          .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
          .on("click", function(d) { return zoom(node == d.parent ? d : root); });
      
      catCell.append("svg:rect")
          .attr("width", function(d) { return d.dx - 1; })
          .attr("height", function(d) { return d.dy - 1; })
          .style("fill", function(d) { return d.color; })
          .style("fill-opacity", 0.9)
          .style("stroke", "#000000")
          .style("stroke-width", "2px");

      catCell.append("svg:text")
          .attr("x", function(d) { return d.dx / 2; })
          .attr("y", function(d) { return d.dy / 2; })
          .attr("dy", ".35em")
          .attr("text-anchor", "middle")
          .text(function(d) { return d.name; })
          .style("cursor", "default")
          .style("font-weight", "bold")
          .style("stroke", function(d){ return d.color; })
          .style("opacity", function(d) { d.w = this.getComputedTextLength(); return d.dx > d.w ? 1 : 0; });

      catCell.append("svg:text")
          .attr("x", function(d) { return d.dx / 2; })
          .attr("y", function(d) { return d.dy / 2; })
          .attr("dy", ".35em")
          .attr("text-anchor", "middle")
          .text(function(d) { return d.name; })
          .style("cursor", "default")
          .style("font-weight", "bold")
          .style("opacity", function(d) { d.w = this.getComputedTextLength(); return d.dx > d.w ? 1 : 0; });

      d3.selectAll("#" + id + "options input").on("change", function() {
        type = this.value;
        treemap.value(this.value == "size" ? size : count).nodes(root);
        zoom(node);
      });
    });

    function size(d) {
      return d.size;
    }

    function count(d) {
      return 1;
    }

    function zoom(d) {
      var opacity = 0.1;
      var textpos = 20;
      
      var kx = w / d.dx, ky = h / d.dy;
      x.domain([d.x, d.x + d.dx]);
      y.domain([d.y, d.y + d.dy]);
      
      if(!d.parent){
        opacity = 0.9;
        textpos = 0;
      }

      var t = svg.selectAll("g").transition()
          .duration(d3.event.altKey ? 7500 : 750)
          .attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });

      t.select("rect")
          .attr("width", function(d) { return kx * d.dx - 1; })
          .attr("height", function(d) { return ky * d.dy - 1; })

      t.select("g.catCell rect")
        .style("fill-opacity", opacity);

      t.select("g.cell text")
          .attr("x", function(d) { return kx * d.dx / 2; })
          .attr("y", function(d) { return ky * d.dy / 2; })
          .style("opacity", function(d) { return kx * d.dx > d.w ? 1 : 0; });
          
      t.selectAll("g.catCell text")
          .attr("x", function(d) { return kx * d.dx / 2; })
          .attr("y", function(d) { return (!textpos) ? kx * d.dy / 2 : kx * 2; })
          .style("opacity", function(d) { return kx * d.dx > d.w ? 1 : 0; });

      node = d;
      d3.event.stopPropagation();
    }
}    
})( jQuery );
