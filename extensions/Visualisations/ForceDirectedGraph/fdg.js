var fdgForces = Array();

function stopFDG(id){
    var force = fdgForces[id];
    var force2 = fdgForces[id + "2"];
    if(force != undefined){
        force.stop();
    }
    if(force2 != undefined){
        force2.stop();
    }
}

function createFDG(width, height, id, url){

    var radius = 6;

    var color = function(c){
        var colors = ["#1f77b4", "#aec7e8", "#e377c2", "#d62728", "#ff7f0e", "#98df8a", "#7f7f7f", "#c7c7c7"];
        return colors[c % colors.length];
    };
    
    var edgeColor = d3.scale.category10();

    var svg = d3.select("#" + id).append("svg")
        .attr("width", width)
        .attr("height", height);

    $.get(url, function(graph){
        stopFDG(id);
        // Set up Legend
        $("#" + id).append("<div class='legend' style='position:absolute;display:inline;'><h3>Nodes</h3></div>");
        graph.groups.forEach(function(g, i){
            var c = color(i);
            $("#" + id + " .legend").append("<span style='display:inline-block;width:" + 10 + "px;height:" + 10 + "px;background:" + c + ";'></span> " + g + "<br />");
        });
        
        $("#" + id).append("<div class='edgelegend' style='position:absolute;display:inline;'><h3>Edges</h3></div>");
        $("#" + id + " .edgelegend").css('margin-top', $("#" + id + " .legend").height());
        graph.edgeGroups.forEach(function(g, i){
            var c = edgeColor(i);
            $("#" + id + " .edgelegend").append("<span style='display:inline-block;width:" + 10 + "px;height:" + 10 + "px;background:" + c + ";'></span> " + g + "<br />");
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
            .charge(function(){
                return -(Math.sqrt(graph.links.length)/graph.nodes.length)*Math.min(width, height)*2;
            })
            .linkDistance(Math.max(radius*3, (graph.links.length/graph.nodes.length)*10))
            .gravity(0.25)
            .size([width, height])
            .nodes(graph.nodes)
            .links(graph.links)
            .linkStrength(0.5)
            .theta(0.99999)
            .start();
        fdgForces[id] = force;
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
            fdgForces[id + "2"] = force2;
        }
        
        var link = svg.selectAll("line.link")
            .data(graph.links.reverse())
            .enter()
            .append("svg:line")
            .attr("class", "link")
            .style("stroke", function(d){
                return edgeColor(d.group);
            })
            .style("stroke-width", function(d){
                return d.value*2;
            });

        var node = svg.selectAll("circle")
            .data(force.nodes())
            .enter()
	        .append("svg:circle")
	        .attr("r", function(d){ if(d.index == 0) return radius*2; else return radius; })
	        .style("fill", function(d){
	            return color(d.group);
	        })
	        .style("stroke", "#FFF")
	        .style("stroke-width", 2);
	        
	    node.call(force.drag);
        if(isLabeled){
            anchorLink = svg.selectAll(".anchorLink").data(labelAnchorLinks);        
            anchorNode = svg.selectAll(".anchorNode").data(force2.nodes()).enter().append("svg:g").attr("class", "anchorNode");
            anchorNode.append("svg:circle").attr("r", 0).style("fill", "#FFF");
            anchorNode.append("svg:text").text(function(d, i) {
                return i % 2 == 0 ? "" : d.node.name;
            })
            .style("fill", "#333")
            .style("font-family", "Arial")
            .style("font-size", 12)
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
            if(isLabeled && force2 != undefined){
                force2.start();
            }
            node.call(updateNode);
            if(isLabeled && force2 != undefined){
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
            if(isLabeled && force2 != undefined){
                anchorNode.call(updateNode);
            }
            link.call(updateLink);
            if(isLabeled && force2 != undefined){
                anchorLink.call(updateLink);
            }
        });
    });
}
