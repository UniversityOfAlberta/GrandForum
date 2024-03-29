(function($){
 $.fn.cluster = function(options) { 

    var width = $(this).width();
    var id = $(this).attr("id");
        
    var scalingFactor = 0.5;

    var offset = 100*scalingFactor;

    var w = (width*2 - 200)*scalingFactor,
        h = (width*2 - 200)*scalingFactor,
        rx = (w / 2),
        ry = (h / 2),
        m0,
        rotate = 0;

    var cluster = d3.layout.cluster()
        .size([360, ry - 100])
        .sort(null);

    var diagonal = d3.svg.diagonal.radial()
        .projection(function(d) { return [d.y, d.x / 180 * Math.PI]; });

    var svg = d3.select("#" + id);

    var vis = svg.append("svg:svg")
        .attr("width", w + offset*2)
        .attr("height", w + offset*2)
      .append("svg:g")
        .attr("transform", "translate(" + (rx + offset) + "," + (ry + offset) + ")");

    d3.json(options.url, function(json) {
      var nodes = cluster.nodes(json);
      var links = cluster.links(nodes);
      
      var root = nodes[0];
      var rootChildren = root.children;

      var lastChild = rootChildren[0];
      _.each(rootChildren, function(child){
            links.shift();
            links.push({"source": lastChild, "target": child});
            lastChild = child;
      });
      links.push({"source": rootChildren[0], "target": lastChild});

      var link = vis.selectAll("path.link")
          .data(links)
        .enter().append("svg:path")
          .attr("class", "link")
          .style("fill", "none")
          .style("stroke", "#000")
          .style("stroke-width", 4*scalingFactor)
          .attr("d", diagonal);

      var node = vis.selectAll("g.node")
          .data(nodes)
        .enter().append("svg:g")
          .attr("class", "node normal")
          .attr("transform", function(d) { 
                if(d == root){
                    return "rotate(" + (180) + ")"; 
                }
                else{
                    return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; 
                }
          })
          .style("cursor", "pointer")
          .on("click", changePage);

      var normalNodes = vis.selectAll("g.normal");

      normalNodes.append("svg:circle")
          .attr("title", function(d){ return d.fullname; })
          .attr("class", "cluster-tooltip")
          .style("display", function(d) { return d == root ? "none" : "block"})
          .attr("r", function(d) { if(d.children == undefined){ return 15*scalingFactor; } else { return 15*scalingFactor; }})
          .style("stroke-width", 3*scalingFactor)
          .style("fill", function(d){ return d.color; })
          .style("stroke", "#000000");
        
        function createText(stroke){
            var text = node.append("svg:text");
            text.attr("title", function(d){ return d.fullname; })
                .attr("class", "cluster-tooltip")
                .attr("dx", function(d) {
                    if(d.children == undefined){
                        return d.x < 180 ? 20*scalingFactor : -20*scalingFactor; 
                    } else {
                        return 0;
                    }
                })
                .attr("y", function(d){
                    if(d.children == undefined){
                        return 0;
                    }
                    return "-" + (1 + d.name.split(" ").length) + "em";
                })
                .attr("dy", function(d) {
                    if(d.text != undefined && d.text == "below"){
                        return 35*scalingFactor + 25*scalingFactor;
                    }
                    else if(d == root){
                        return "0.25em";
                    } else if(d.children == undefined){
                       return ".31em";
                    } else {
                        return d.x < 180 ? "1.69em" : "-1em";
                    }
                })
                .attr("text-anchor", function(d) { 
                    if(d.children == undefined){
                        return d.x < 180 ? "start" : "end";
                    } else{
                       return "middle";
                    } 
                })
                .attr("transform", function(d) {
                    if(d.text != undefined && d.text == "below"){
                       return "rotate(" + -(d.x - 90) + ")";
                    }
                    return d.x < 180 ? null : "rotate(180)"; 
                })
                .style("fill",  function(d){ return d3.rgb(d.color); })
                .style("font-size", function(d) {
                    if(d.text != undefined && d.text == "below"){
                        return 50*scalingFactor + "px";
                    }
                    return d == root ? 50*scalingFactor + "px" : 25*scalingFactor + "px"
                })
                .style("font-weight", "bold")
                .text(function(d) { if(d.children == undefined) { return d.name; } return ""; });
                if(stroke){
                     text.style("stroke", function(d){ if(d3.lab(d.color).l > 75) { return "#000"; } return "#FFF"; })
                      .style("stroke-width", 5*scalingFactor);
                }
            text.selectAll("text")
                .data(function(d) { if(d.children == undefined) { return []; } return d.name.split(" "); })
                .enter()
                .append("tspan")
                .attr("class", "text")
                .text(d => d)
                .attr("x", 0)
                .attr("dy", '1em');
        }
        
        node.on("mouseover", function(d){
            d3.select(this)
              .selectAll("text")
              .style("fill", d3.rgb(d.color).darker(1));
              
            d3.select(this)
              .selectAll("circle.overlay")
              .style("opacity", 0.2);
              
            d3.select(this)
              .selectAll("circle:not(.overlay)")
              .style("fill", d3.rgb(d.color).darker(1));
        });
        
        node.on("mouseout", function(d){
            d3.select(this)
              .selectAll("text")
              .style("fill", d.color);
              
            d3.select(this)
              .selectAll("circle.overlay")
              .style("opacity", 0);
              
            d3.select(this)
              .selectAll("circle:not(.overlay)")
              .style("fill", d.color);
        });

        createText(true);
        createText(false);
        
        $(".cluster-tooltip").qtip({
            style: {
                classes: 'qtip-dark qtip-shadow'
            },
            position: {
                my: 'bottom left',
		        target: 'mouse'
	        }
        });
        
    });

        function changePage(d){
            window.location = d.url;
        }
    }    
})( jQuery );
