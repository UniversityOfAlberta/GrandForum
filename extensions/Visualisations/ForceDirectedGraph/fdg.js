function createFDG(width, height, id, url){

    var radius = 7;

    var color = d3.scale.category20();

    var svg = d3.select("#" + id).append("svg")
        .attr("width", width)
        .attr("height", height);

    d3.json(url, function(error, graph) {
        // Set up Legend
        $("#" + id).append("<div class='legend' style='position:absolute;display:inline;'><h3>Legend</h3></div>");
        graph.groups.forEach(function(g, i){
            console.log(color(i));
            $("#" + id + " .legend").append("<span style='display:inline-block;width:" + 10 + "px;height:" + 10 + "px;background:" + color(i) + ";'></span> " + g + "<br />");
        });
    
        isLabeled = false;
        graph.nodes.forEach(function(n, i){
            if(n.name != ''){
                isLabeled = true;
            }
            n.x = (Math.random() * width/2) + (width/2 - width/4);
            n.y = (Math.random() * height/2) + (height/2 - height/4);
        });
        
        labelAnchors = Array();
        labelAnchorLinks = Array();
    
        graph.nodes.forEach(function(n){
            labelAnchors.push({
                node : n,
                x : n.x,
                y : n.y
            });
            labelAnchors.push({
                node : n,
                x : n.x,
                y : n.y
            });
        });
        
        for(var i = 0; i < graph.nodes.length; i++) {
			labelAnchorLinks.push({
				source : i * 2,
				target : i * 2 + 1,
				weight : 1
			});
		};
		var force = d3.layout.force()
            .charge(-3000)
            .linkDistance(Math.sqrt(width*height)/Math.sqrt(graph.nodes.length)*2)
            .gravity(0.5)
            .size([width, height])
            .nodes(graph.nodes)
            .links(graph.links)
            .linkStrength(function(x) {
			    return x.value;
		    })
            .start();
        if(isLabeled) {
            var force2 = d3.layout.force()
                .charge(-100)
                .linkDistance(0)
                .linkStrength(8)
                .gravity(0)
                .size([width, height])
                .nodes(labelAnchors)
                .links(labelAnchorLinks)
                .start();
        }
        var link = svg.selectAll("line.link")
            .data(graph.links.reverse())
            .enter()
            .append("svg:line")
            .attr("class", "link")
            .style("stroke", function(d){
                if(d.source.index == 0 || d.target.index == 0) return "#888"; else return "#CCC"; 
            });
        
        var node = svg.selectAll("g.node")
            .data(force.nodes())
            .enter()
            .append("svg:g")
            .attr("class", "node");
        
		node.append("svg:circle")
		    .attr("r", function(d){ if(d.index == 0) return radius*2; else return radius; })
		    .style("fill", function(d){
		        return color(d.group); 
		    })
		    .style("stroke", "#FFF")
		    .style("stroke-width", 3);
		    
		node.call(force.drag);
        if(isLabeled){
            var anchorLink = svg.selectAll(".anchorLink").data(labelAnchorLinks);        
            var anchorNode = svg.selectAll(".anchorNode").data(force2.nodes()).enter().append("svg:g").attr("class", "anchorNode");
            anchorNode.append("svg:circle").attr("r", 0).style("fill", "#FFF");
            anchorNode.append("svg:text").text(function(d, i) {
                return i % 2 == 0 ? "" : d.node.name;
            }).style("fill", "#555").style("font-family", "Arial").style("font-size", 12);
        }
        var updateLink = function() {
            this.attr("x1", function(d) {
                return Math.min(width-radius, Math.max(radius, d.source.x));
            }).attr("y1", function(d) {
                return Math.min(height-radius, Math.max(radius, d.source.y));
            }).attr("x2", function(d) {
                return Math.min(width-radius, Math.max(radius, d.target.x));
            }).attr("y2", function(d) {
                return Math.min(height-radius, Math.max(radius, d.target.y));
            });
        }    
        
        var updateNode = function() {
            this.attr("transform", function(d) {
                return "translate(" + Math.min(width-radius, Math.max(radius, d.x)) + "," + Math.min(height-radius, Math.max(radius, d.y)) + ")";
            });
        }
        
        

        force.on("tick", function() {
            
            if(isLabeled){
                force2.start();
            }
            node.call(updateNode);
            if(isLabeled){
                anchorNode.each(function(d, i) {
                    if(i % 2 == 0) {
                        d.x = d.node.x;
                        d.y = d.node.y;
                    } else {
                        var b = this.childNodes[1].getBBox();

                        var diffX = d.x - d.node.x;
                        var diffY = d.y - d.node.y;
                        
                        var dist = Math.sqrt(diffX * diffX + diffY * diffY);

                        var shiftX = b.width * (diffX - dist) / (dist * 2);
                        shiftX = Math.max(-b.width, Math.min(0, shiftX));
                        var shiftY = 5;
                        this.childNodes[1].setAttribute("transform", "translate(" + shiftX + "," + shiftY + ")");
                    }
                });
            }
            if(isLabeled){
                anchorNode.call(updateNode);
            }
            link.call(updateLink);
            if(isLabeled){
                anchorLink.call(updateLink);
            }
        });
    });
}
