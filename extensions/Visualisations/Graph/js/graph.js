var graph = null;

$.fn.graph = function(d){
    if(graph == null){
        var containter = $(this);
        $(this).html("<div id='graphInfo' class='graph_info redraw'></div>" + 
                     "<div id='graphLegend' class='graph_legend redraw'></div>" + 
                     "<div id='graphOptions' class='graph_options redraw'></div>" + 
                     "<div id='graph' class='redraw'>" + 
                        "<div id='canvases' class='redraw'></div>" + 
                        "<div id='div_help_box' class='help_text_box'></div>" + 
                     "</div>");
        $(this).addClass("graphBorder");
        $(this).addClass("graphGradient");
        $("#graph").height($(this).height());
        var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
        if(typeof d == 'string' && pattern.test(d)){
            $.get(d, function(response){
                graph = new Graph();
                graph.init();
                graph.setData(response);
                graph.addNewNode(response.start_node, null, null);
            });
        }   
        else{
            graph = new Graph();
            graph.init();
            graph.setData(d);
            graph.addNewNode(d.start_node, null, null);
        }
    }
};

function Graph(){

    // Globals
    var thisGraph = this;
    var data = null;                                  //The data array for this graph 
    var graphWidth = 0;                               //Width of drawing area
    var graphHeight = 0;                              //Height of drawing area
    var mouse = {"x" : 0, "y" : 0};                   //Current mouse position
    var starterNodeId = null;                         //The id of the starting node
    var clickedNode = null;                           //The object which is currently selected
    var draggedNode = null;                           //The object which is currently being dragged
    var hoveredNode = false;                          //Id of the node being hovered
    var nodes = [];                                   //Array of current nodes being displayed
    var filtered = [];                                //Types which are currently filtered
    var connections = new Array();                    //2d array containing all the edges
    var edges = new Array();                          //An array containing all the edges
    var oldObj = null;                                //Stores the old object to verify there's only 1 object
    var oldBody = null;                               //Maximizing/Minimizing
    var frame = 0;                                    //Used for the repelNodesInit function

    this.init = function() {
        hoveredNode = null;
        if(document.getElementById("graph") != null){
            graphWidth = document.getElementById("graph").clientWidth;
            graphHeight = document.getElementById("graph").clientHeight;
        }
        
        var fadeTime = 500;
        
        //Fades in all objects which use the redraw class
        obj = $("div.redraw");
        if(obj != null){
            obj.css("display", "none");
            obj.fadeIn(fadeTime);
        }
        
        //Hyperlinks will cause a fadeOut on redraw objects
        //(Unless they have a noredraw class)
        $("a:not(a.noredraw)").click(function(event){
            if($(this).attr('href') != undefined && $(this).attr('href').indexOf('#') == -1){
                if(obj != null){
                    event.preventDefault();
                    linkLocation = this.href;
                    obj.fadeOut(fadeTime, redirectPage);
                }
                redirectPage();
            }
        });
        
        function redirectPage() {
            window.location = linkLocation;
        }

        // chrome fix.
        document.onselectstart = function () { return false; };
        
        jsPlumb.DefaultDragOptions = { cursor: 'pointer', zIndex:2000 };
        jsPlumb.setRenderMode(jsPlumb.CANVAS);
        jsPlumb.bind("click", function(connection){
            var sourceId = connection.sourceId;
            var targetId = connection.targetId;
        });
        
        //Handles all mouse movements
        //Resizing Nodes
        $(document).mousemove(function(e){
            var lastMouseX = mouse.x;
            var lastMouseY = mouse.y;
            mouse.x = e.pageX;
            mouse.y = e.pageY;
            if(draggedNode == null && document.getElementById("graph") != null){
                thisGraph.resizeNodes(mouse.x, mouse.y);
            }
        }); 
        
        var resizeTimeout;
        //Called when the browser window is resized,
        //Node positions are recomputed
        $(window).resize(function() {
            thisGraph.recomputeNodePositions();
        });
        
        //Called 30 fps
        //Used to repel nodes.
        window.setInterval(function(){
            if(document.getElementById("graph") != null){
                thisGraph.repelNodes();
            }
        }, 1000/30);
        
        thisGraph.recomputeNodePositions();
    };
    
    this.destroy = function(){
        for(id in nodes){
            var node = nodes[id];
            jsPlumb.removeAllEndpoints(id);
        }
        jsPlumb.unload();
        thisGraph = this;
        data = null;
        graphWidth = 0;
        graphHeight = 0;
        mouse = {"x" : 0, "y" : 0};
        starterNodeId = null;
        clickedNode = null;
        draggedNode = null;
        hoveredNode = false;
        nodes = [];
        filtered = [];
        connections = new Array();
        edges = new Array();
        oldObj = null;
        oldBody = null;
        frame = 0;
    }

    /*
     * Sets the global data variable containing full information about the graph
     */
    this.setData = function(json){
        data = json;
        var found = false;
        for(lid in data.legend){
            if(data.legend[lid].name == 'Other'){
                found = true;
            }
        }
        if(!found){
            data.legend["Other"] = {"name" : 'Other', "color" : "#888888"};
        }
        this.setUpLegend();
        this.setUpOptions();
    }

    this.setUpLegend = function(){
        $("#graphLegend").html("<span class='legend_head'>Legend</span>");
        for(lid in data.legend){
            var type = data.legend[lid];
            var checked = '';
            if(typeof filtered[type.name] == 'undefined' || filtered[type.name] == false){
                checked = 'checked';
            }
            $("#graphLegend").append("<input style='vertical-align:middle;' type='checkbox' name='graphFilter' value='" + lid + "' " + checked + "> ");
            $("#graphLegend").append("<div class='graph_legend_box' style='background:" + type.color + ";'></div>");
            $("#graphLegend").append(type.name + "<br />");
        }
        $("input[name=graphFilter]").change(function(event, ui){
            thisGraph.filter($(this).val(), $(this).attr('checked') == 'checked');
        });
    }

    this.filter = function(name, include){
        if(include == false){
            $.each($(".window[name=" + name + "]"), function(index, el){
                if(this.id != clickedNode.id){
                    $(this).fadeOut(500);
                    var cons = thisGraph.getConnections($(this).attr('id'));
                    for(cId in cons){
                        var con = cons[cId];
                        $(con.connection.canvas).fadeOut(500);
                    }
                }
            });
            filtered[name] = true;
        }
        else{
            $.each($(".window[name=" + name + "]:not(.window[id=" + clickedNode.id + "])"), function(index, el){
                var cons = thisGraph.getConnections($(this).attr('id'));
                if(typeof cons[clickedNode.id] != 'undefined'){
                    $(this).fadeIn(500);
                    var val = document.getElementById(this.id);
                    rand1 = Math.random();
                    rand2 = Math.random();
                    
                    var left = Math.floor(rand1*(graphWidth - val.clientWidth*2 - parseInt($(".graph_info").css('padding-left')) - 
                                          parseInt($(".graph_info").css('padding-right')) -
                                          parseInt($(".graph_info").css('margin-left')) -
                                          parseInt($(".graph_info").css('borderLeftWidth')) - 
                                          parseInt($(".graph_info").css('borderRightWidth'))) + 
                                        val.clientWidth);
                    var top = Math.floor(rand2*(graphHeight - val.clientHeight*2) + 
                                         val.clientHeight);
                
                    val.style.left = left + "px";
                    val.style.top = top + "px";
                    
                    nodes[this.id].width = this.clientWidth;
                    nodes[this.id].height = this.clientHeight;
                    
                    for(cId in cons){
                        var con = cons[cId];
                        if((filtered[$("#" + con.connection.targetId).attr("name")] != true) &&
                           (filtered[$("#" + con.connection.sourceId).attr("name")] != true)){
                            $(con.connection.canvas).fadeIn(500);
                        }
                    }
                }
            });
            filtered[name] = false;
        }
        frame = 0;
        thisGraph.repelNodesInit();
    }

    this.setUpOptions = function(){
        $("#graphOptions").html("<span class='legend_head'>Options</span>");
        $("#graphOptions").append("<a id='graphFull' href='#'>Full Screen</a><br />");
        $("#graphOptions").append("<input type='text' name='graphSearch' id='graphSearch' /><br /><input id='graphSearchButton' type='button' onClick='search()' value='Search' /><span id='searchResult'></span>");
        $("#graphFull").click(function(){
            thisGraph.fullScreenToggle();
        });
        $("#graphSearch").keypress(function(e){
            var key=e.keyCode || e.which;
            if (key==13){
                thisGraph.search();
            }
        });
        $("#graphSearchButton").click(function(){
            thisGraph.search();
        });
    }

    /*
     * Switches the graph from minimized to full screen, and vice-versa
     */
    this.fullScreenToggle = function(){
        if(oldBody == null){
            for(id in nodes){
                var node = nodes[id];
                jsPlumb.removeAllEndpoints(id);
            }
            jsPlumb.unload();
            var graph = $("#graph").parent().html();
            oldBody = $("body").detach();
            var body = document.createElement("body");
            $("html").append(body);
            $("body").html(graph);
            $("body").addClass('fullScreen');
            $("html").addClass('fullScreen');
            $("#graph").parent().removeClass("graphBorder");
            $("#graph").parent().height('100%');
            $("#graph").height('100%');
            $("._jsPlumb_connector").remove();
            connections = Array();
            nodes = Array();
            edges = [];
            
            var oldClicked = clickedNode.id;
            $("div.window").remove();
            clickedNode = null;
            draggedNode = null;
            hoveredNode = null;
            thisGraph.addNewNode(oldClicked.replace("n", "").replace("_l1", ""), null, null);
            thisGraph.setUpLegend();
            thisGraph.setUpOptions();
            $("#graphFull").text("Minimize");
        }
        else{
            windows = $("div.window"); //Windows which should be removed
            for(id in nodes){
                var node = nodes[id];
                jsPlumb.removeAllEndpoints(id);
            }
            jsPlumb.unload();
            connections = Array();
            nodes = Array();
            edges = [];
            var oldClicked = clickedNode.id;
            $("body").replaceWith(oldBody);
            $("._jsPlumb_connector").remove();
            
            $("div.window").remove();
            clickedNode = null;
            draggedNode = null;
            hoveredNode = null;

            thisGraph.addNewNode(oldClicked.replace("n", "").replace("_l1", ""), null, null);
            oldBody = null;
            $("body").removeClass('fullScreen');
            $("html").removeClass('fullScreen');
            $("#graph").parent().addClass("graphBorder");
            thisGraph.setUpLegend();
            thisGraph.setUpOptions();
            $("#graphFull").text("Full Screen");
        }
        thisGraph.recomputeNodePositions();
        frame = 0;
        thisGraph.repelNodesInit();
    }

    this.search = function(){
        var value = $("#graphSearch").val().toLowerCase().replace(/ /g, '').replace(/\./g, '').replace(/&nbsp;/g, '');
        for(id in data.nodes){
            var node = data.nodes[id];
            if(typeof node.name != 'undefined'){
                var name = node.name.toLowerCase().replace(/ /g, '').replace(/\./g, '').replace(/&nbsp;/g, '');
                if(name.match("^.*" + value + ".*$")){
                    thisGraph.addNewNode(id, null, null);
                    $("#searchResult").html("");
                    return;
                }
            }
        }
        $("#searchResult").text("No Results Found");
    }

    /*
     * Adds a new node with the specified
     * id: id of the node
     * parent: id of the parent
     * weight: the weight of the parent connection
     */
    this.addNewNode = function(id, parent, weight){
        this.addNode(id, parent, weight, data.nodes[id]);
    }

    /*
     * Adds a new node with the specified
     * id: id of the node
     * parent: id of the parent
     * weight: the weight of the parent connection
     * data: the database json object.
     */
    this.addNode = function(id, parent, weight, node){
        var alreadyExists = (typeof nodes["n" + id + "_l1"] != 'undefined');
        var divExists = ($("#n" + id + "_l1").length > 0);
        //Only create the node's html if it isn't already in there
        if(typeof node.name == 'undefined'){
            return;
        }
        if(!alreadyExists && !divExists){
            var name = "Other";
            if(typeof node.type == 'undefined'){
                node.type = name;
            }
            if(typeof data.legend[node.type] != 'undefined'){
                name = data.legend[node.type].name;
            }
            $("#graph").append(
            "<div name='" + name + "' class='window' id='n" + node.id + "_l1'>" + 
                "<span>" + node.name + "</span>" +
            "</div>");
            if(typeof data.legend[node.type] != 'undefined'){
                color = data.legend[node.type].color;
                $("#n" + node.id + "_l1").css("background", color);
            }
            jsPlumb.draggable(jsPlumb.getSelector("#n" + node.id + "_l1"));
        }
        var val = document.getElementById("n" + node.id + "_l1");
        if(!alreadyExists){
            rand1 = Math.random();
            rand2 = Math.random();
            
            //Randomly place the node
            if(weight == null){
                rand1 = 0.5;
                rand2 = 0.5;
            }
            
            if(typeof filtered[node.type] == 'undefined' || filtered[node.type] != true || parent == null){
                $(val).fadeIn(500);
            }
            else{
                $(val).fadeOut(0);
            }
            var left = Math.floor(rand1*(graphWidth - val.clientWidth*2 - parseInt($(".graph_info").css('padding-left')) - 
                                          parseInt($(".graph_info").css('padding-right')) -
                                          parseInt($(".graph_info").css('margin-left')) -
                                          parseInt($(".graph_info").css('borderLeftWidth')) - 
                                          parseInt($(".graph_info").css('borderRightWidth'))) + 
                                        val.clientWidth);
                var top = Math.floor(rand2*(graphHeight - val.clientHeight*2) + 
                                     val.clientHeight);
            
                val.style.left = left + "px";
                val.style.top = top + "px";
                nodes[val.id] = {"name" : node.name,
                                "desc" : node.description,
                                "media" : null,
                                "val" : val,
                                "left" : left, 
                                "top" : top, 
                                "width" : val.clientWidth,
                                "height" : val.clientHeight,
                                "fontSize" : val.style.fontSize,
                                "clicked" : false,
                                "vx" : 0,
                                "vy" : 0};
        }
        if(weight == null){
            //The root node
            //Adds all of the connected nodes from the root
            function processRootConnectedNodes(data2){
                windows = $("div.window:not(#n" + node.id + "_l1)"); //Windows which should be removed
                for(nodeId in data2){
                    var edge = data2[nodeId];
                    if(node.id == edge.a){
                        windows = windows.not("#n" + edge.b + "_l1");
                        thisGraph.addNewNode(edge.b, node.id, edge.weight);
                    }
                    else{
                        windows = windows.not("#n" + edge.a + "_l1");
                        thisGraph.addNewNode(edge.a, node.id, edge.weight);
                    }
                }
                //Remove nodes and connections which are not connected to the root
                $.each(windows, function(i, val){
                    var winId = $(val).attr("id");
                    var cons = thisGraph.getConnections(winId);
                    for(conId in cons){
                        thisGraph.removeConnection(winId, conId);
                    }
                    jsPlumb.detachAllConnections(winId);
                    delete nodes[winId];
                });
                windows.fadeOut(500, function(){
                    $(this).remove();
                });
            }
            processRootConnectedNodes(node.connections);
        }
        else{
            // Secondary nodes
            
            //Adds connections between already existing nodes, but does not add any new nodes
            function processSecondaryConnectedNodes(data2){
                for(nodeId in data2){
                    var edge = data2[nodeId];
                    var a = document.getElementById("n" + edge.a + "_l1");
                    var b = document.getElementById("n" + edge.b + "_l1");
                    if(a != null && b != null && edge.a != parent && edge.b != parent){
                        color = "#70C0FF";
                        if((typeof connections["n" + edge.a + "_l1"] == 'undefined' || connections["n" + edge.a + "_l1"]["n" + edge.b + "_l1"] == 'undefined') &&
                           (typeof connections["n" + edge.b + "_l1"] == 'undefined' || typeof connections["n" + edge.b + "_l1"]["n" + edge.a + "_l1"] == 'undefined')){
                            if(typeof edge.color != 'undefined'){
                                color = edge.color;
                            }
                        }
                        thisGraph.addConnection("n" + edge.a + "_l1", "n" + edge.b + "_l1", "", edge.weight, color);
                    }
                }
            }
            processSecondaryConnectedNodes(node.connections);
        }
        //Adding Connection to parent
        if(parent != null){
            if(typeof connections["n" + node.id + "_l1"] == 'undefined' || 
               typeof connections["n" + parent + "_l1"] == 'undefined' ||
               (typeof connections["n" + parent + "_l1"]["n" + node.id + "_l1"] == 'undefined' &&
                typeof connections["n" + node.id + "_l1"]["n" + parent + "_l1"] == 'undefined')){
                color = "#70C0FF";
                for(index in node.connections){
                    if(node.connections[index].a == node.id || node.connections[index].b == node.id){
                        if(typeof node.connections[index].color != 'undefined'){
                            color = node.connections[index].color;
                            break;
                        }
                    }
                }
                thisGraph.addConnection("n" + parent + "_l1", "n" + node.id + "_l1", "", weight, color);
            }
        }
        if(!alreadyExists && !divExists){
            thisGraph.initNodeEvents(node.id);
        }
        if(weight == null){
            thisGraph.clickNode("n" + node.id + "_l1");
        }
    }

    /*
     * Initializes all of the events related to 
     * node(windows)
     */
    this.initNodeEvents = function(id){
        val = document.getElementById("n" + id + "_l1");
        //Called when mouse hovers over a node
        $(val).mouseenter(function() {
            nid= "n" + id + "_l1";
            //Change the css of the node to hovered
            thisGraph.hoverNode(nid);
        });
        
        //Called when the mouse leaves a node
          //Unhovers the node, and closes any message box
          $(val).mouseleave(function() {
              nid= "n" + id + "_l1";
              thisGraph.unHoverNode(nid);
          });
        
        $(val).draggable({
            //Called when an object starts to drag
            start: function(event, ui) {
                if(typeof nodes[event.target.id] == 'undefined'){
                    // Return out of the function since the node most likeley non-existent
                    // or is being faded out.
                    return;
                }
                draggedNode = event.target;
            }
        });
        
        $(val).bind("dragstop", function(event, ui) {
            thisGraph.clickNode(event.target.id);
            draggedNode = null;
        });
        
        $(val).bind("click", function(event, ui) {
            thisGraph.clickNode(this.id);
        });
        
        $(val).draggable({ containment: "parent" });
    }

    /*
     * Adds a new connection, connection node1 and node2 with the given size, color and text
     */
    this.addConnection = function(node1, node2, text, size, color){
      if(typeof connections[node1] == 'undefined'){
        connections[node1] = new Array();
      }
      if(typeof connections[node2] == 'undefined'){
        connections[node2] = new Array();
      }
      if(typeof connections[node1][node2] == 'undefined' && typeof connections[node2][node1] == 'undefined'){
        function increase_saturation(hex, percent){
            var r = parseInt(hex.substr(1, 2), 16),
                g = parseInt(hex.substr(3, 2), 16),
                b = parseInt(hex.substr(5, 2), 16);
            var newR = r;
            var newG = g;
            var newB = b;
            var maxC = Math.max(Math.max(r,g),b);
            if(r < maxC){
                newR = r - (r*percent/100);
            }
            if(g < maxC){
                newG = g - (g*percent/100);
            }
            if(b < maxC){
                newB = b - (b*percent/100);
            }
            
            return '#' +
               ((0|(1<<8) + newR).toString(16)).substr(1) +
               ((0|(1<<8) + newG).toString(16)).substr(1) +
               ((0|(1<<8) + newB).toString(16)).substr(1);
        }
        var connection = jsPlumb.connect({
                    source: node1, 
                    target: node2, 
                    anchors:["AutoDefault", "AutoDefault"],
                    connector: ["Bezier", { curviness:75 } ],
                    endpoint:"Blank",
                    paintStyle:{ 
					    lineWidth:size,
					    strokeStyle: color,
					    outlineWidth:0.75,
					    outlineColor:"#000"
				    },
				    hoverPaintStyle:{ 
					    lineWidth:size,
					    strokeStyle: increase_saturation(color, 25),
					    outlineWidth:1,
					    outlineColor:"#000"
				    },
                    labelStyle : {fillStyle: color, color:"white",borderWidth:10 ,font:"16px helvetica"},
                    label : text });
        con = $(connection.canvas);
        con.css("display", "none");

        connections[node1][node2] = {"id" : connection.canvas.id,
                                     "connection" : connection,
                                     "size" : size
                                    };
        connections[node2][node1] = {"id" : connection.canvas.id,
                                     "connection" : connection,
                                     "size" : size
                                    };
        edges[node1 + node2] = {"a" : node1,
                                "b" : node2,
                                "connection" : connection,
                                "size" : size};
        delete edges[node2 + node1];
        return connection;
      }
      return null;
    }

    /*
     * Returns the jsPlumb connections for the given node
     */
    this.getConnections = function(node){
      return connections[node];
    }

    /*
     * Returns the number of connections for the given node
     */
    this.getNumberOfConnections = function(node){
      var counter = 0;
      for(conId in connections[node]){
        counter++;
      }
      return counter;
    }

    /*
     * Removes the connection between node1 and node2
     */
    this.removeConnection = function(node1, node2){
      if(typeof connections[node1] != 'undefined'){
        var newConnections = new Array();
        for(conId in connections[node1]){
          if(conId != node2){
            newConnections[conId] = connections[node1][conId];
          }
        }
        connections[node1] = newConnections;
      }
      if(typeof connections[node2] != 'undefined'){
        var newConnections = new Array();
        for(conId in connections[node2]){
          if(conId != node1){
            newConnections[conId] = connections[node2][conId];
          }
        }
        connections[node2] = newConnections;
      }
      delete edges[node1 + node2];
      delete edges[node2 + node1];
    }

    /*
     * Recomputes the node positions when the window is resized so no nodes get
     * stuck behind the explanation box
     */
    this.recomputeNodePositions = function(){
        var graph = document.getElementById("graph");
        frame = 25;
        if(graph == null){
            return;
        }
        $("#graph").width($("#graph").parent().width() - 
                          $(".graph_info").width() - 
                          parseInt($(".graph_info").css('padding-left')) - 
                          parseInt($(".graph_info").css('padding-right')) -
                          parseInt($(".graph_info").css('margin-left')) -
                          parseInt($(".graph_info").css('borderLeftWidth')) - 
                          parseInt($(".graph_info").css('borderRightWidth'))
                         );
        $("#graph").css('margin-left', parseInt($(".graph_info").width()) + 
                                        parseInt($(".graph_info").css('padding-left')) +
                                        parseInt($(".graph_info").css('padding-right')) +
                                        parseInt($(".graph_info").css('margin-left')) +
                                        parseInt($(".graph_info").css('borderLeftWidth')) + 
                                        parseInt($(".graph_info").css('borderRightWidth')));
        
        graphWidth = document.getElementById("graph").clientWidth;
        graphHeight = document.getElementById("graph").clientHeight;
        for(id in nodes){
            node = nodes[id];
            var left = node.val.offsetLeft;
            var top = node.val.offsetTop;
            var minWidth = Math.max(0, Math.min(left, graphWidth - 2 - node.val.clientWidth));
            var minHeight = Math.max(0, Math.min(top, graphHeight - 2 - node.val.clientHeight));
            if(minWidth != left || minHeight != top){
                node.left = minWidth;
                node.top = minHeight;
                
                node.val.style.left = minWidth + "px";
                node.val.style.top = minHeight + "px";
            }
        }
        
        // Move the cavases div around so that the edges are painted at the correct position
        document.getElementById("canvases").style.top = -$("#canvases").parent().offset().top + "px";
        document.getElementById("canvases").style.right = -$("#canvases").parent().offset().right + "px";
        document.getElementById("canvases").style.bottom = -$("#canvases").parent().offset().bottom + "px";
        document.getElementById("canvases").style.left = -$("#canvases").parent().offset().left + "px";
        jsPlumb.repaintEverything();
    }

    /*
     * Repels the nodes from each other.
     * If one node is overapping another, they will both move out of the way.
     */
    this.repelNodes = function(){
        var spacing = 1;
        var widths = [];
        var heights = [];
        var offsets = [];
        var redrawNodes = [];
        var ids = [];
        var draggedNodeId = $(draggedNode).attr("id");
        if(draggedNode != null){
            nodes[draggedNodeId].left = draggedNode.offsetLeft;
            nodes[draggedNodeId].top = draggedNode.offsetTop;
        }
        for(id in nodes){
            node = nodes[id];
            if(node.val.style.display == 'none'){
                continue;
            }
            var width = node.width+spacing;
            var height = node.height+spacing;
            
            var centerX = node.left + width/2;
            var centerY = node.top + height/2;
            
            for(id1 in nodes){
                if(typeof nodes[id1] != 'undefined'){
                    node1 = nodes[id1];
                    if(node1.val.style.display == 'none'){
                        continue;
                    }
                    if((id != id1 && typeof redrawNodes[id1] == 'undefined') && 
                         (draggedNode == null || (draggedNode != null && draggedNodeId != id1))
                        ){
                        var width1 = node1.width+spacing;
                        var height1 = node1.height+spacing;
                        
                        var centerX1 = node1.left + width1/2;
                        var centerY1 = node1.top + height1/2;
                        
                        var dX = centerX - centerX1;
                        var dY = centerY - centerY1;

                        //Only move the node if it is actually in the way of another
                        if(Math.abs(dX) <= (width + width1)/2 && Math.abs(dY) <= (height + height1)/2){
                            var signX = (dX == 0) ? 0 : (dX > 0) ? 1 : -1;
                            var signY = (dY == 0) ? 0 : (dY > 0) ? 1 : -1;
                            
                            var left = Math.round(centerX1 - width1/2 - Math.min(10, Math.abs((width + width1)/(dX + signX)))*signX);
                            var top = Math.round(centerY1 - height1/2 - Math.min(10, Math.abs((height + height1)/(dY + signY)))*signY);
                            node1.left = Math.max(0, Math.min(left, graphWidth - width1+spacing - 2));
                            node1.top = Math.max(0, Math.min(top, graphHeight - 3 - node1.height));
                            node1.val.style.left = node1.left + "px";
                            node1.val.style.top = node1.top + "px";
                            redrawNodes[id1] = true;
                            jsPlumb.repaint($(node1.val));
                        }
                    }
                }
            }
        }
    }

    /*
     * Repels the nodes from each other.
     * A physics based approach is here:
     *  - connected nodes will attract each other, while
     *    unconnected nodes will repel
     *  - used a modified version of this algorithm: http://stevehanov.ca/blog/index.php?id=65
     */
    this.repelNodesInit = function(){
        if(clickedNode == null){
            return;
        }
        var centers = [];
        var types = [];
        var k = Math.min(graphWidth, graphHeight);
        while(frame < 64){
            var C = Math.log( frame + 1 ) * 100;
            frame++;
            for(id in nodes){
                var node = nodes[id];
                var width = node.width;
                var height = node.height;
                
                var centerX = node.left + width/2;
                var centerY = node.top + height/2;
                centers[id] = {"x" : centerX,
                               "y" : centerY};
                types[id] = $(node.val).attr('name');
            }
            
            for(id in nodes){
                node = nodes[id];
                if(filtered[types[id]] == true && id != clickedNode.id){
                    continue;
                }
                node.vx = 0;
                node.vy = 0;
                
                // for each other node, calculate the repulsive force and adjust the velocity
                // in the direction of repulsion.
                for(uindex in nodes){
                    if ( id == uindex ) {
                        continue;
                    }
                    var u = nodes[uindex];
                    if(filtered[types[uindex]] == true && uindex != clickedNode.id){
                        continue;
                    }

                    // D is short hand for the difference vector between the positions
                    // of the two vertices
                    if(typeof centers[id] == 'undefined'){
                        // Graph has changed, get out of here!
                        continue;
                    }
                    var Dx = centers[id].x - centers[uindex].x;
                    var Dy = centers[id].y - centers[uindex].y;
                    var len = Math.pow( Dx*Dx+Dy*Dy, 0.5 ); // distance
                    if ( len == 0 ) continue;
                    var mul = k * k / (len*len*C);
                    node.vx += Dx * mul;
                    node.vy += Dy * mul;
                }
            }
            
            // calculate attractive forces
            for (eindex in edges){
                var e = edges[eindex];
                
                var node = nodes[e.a];
                var node1 = nodes[e.b];
                if((filtered[types[e.a]] == true && e.a != clickedNode.id) ||
                   (filtered[types[e.b]] == true && e.b != clickedNode.id)){
                    continue;
                }

                // each edge is an ordered pair of vertices .v and .u
                if(typeof centers[e.a] == 'undefined' || typeof centers[e.b] == 'undefined'){
                    // Graph has changed, get out of here!
                    continue;
                }
                var Dx = centers[e.a].x - centers[e.b].x;
                var Dy = centers[e.a].y - centers[e.b].y;
                var len = Math.pow( Dx * Dx + Dy * Dy, 0.5 ); // distance.
                if ( len == 0 ) continue;

                var mul = len * len/k/C;
                var Dxmul = Dx * mul;
                var Dymul = Dy * mul;
                // attract both nodes towards eachother.
                node.vx -= Dxmul;
                node.vy -= Dymul;
                node1.vx += Dxmul;
                node1.vy += Dymul;
            }
            
            // Here we go through each node and actually move it in the given direction.
            for(id in nodes){
                var v = nodes[id];
                var len = v.vx * v.vx + v.vy * v.vy;
                //var max = Math.min(10, 10/(frame*frame/500));
                var max = 10;
                if ( len > max*max ){
                    len = Math.pow( len, 0.5 );
                    v.vx *= max / len;
                    v.vy *= max / len;
                    
                    v.left = Math.min(graphWidth - v.width/2, Math.max(v.left + v.vx, 0));
                    v.top = Math.min(graphHeight - v.height/2, Math.max(v.top + v.vy, 0));
                }
            }
        }
        if(frame == 64){
            for(id in nodes){
                var v = nodes[id];
                v.val.style.left = v.left + "px";
                v.val.style.top = v.top + "px";
            }
            for(eId in edges){
                var edge = edges[eId];
                var typeA = $("#" + edge.a).attr('name');
                var typeB = $("#" + edge.b).attr('name');
                if(((typeof filtered[typeA] == 'undefined' || filtered[typeA] != true) || $("#" + edge.a).attr('id') == clickedNode.id) &&
                   ((typeof filtered[typeB] == 'undefined' || filtered[typeB] != true) || $("#" + edge.b).attr('id') == clickedNode.id)){
                    $(edge.connection.canvas).fadeIn(500);
                }
            }
            jsPlumb.repaintEverything();
            thisGraph.resizeNodes(mouse.x, mouse.y);
            frame++;
        }
    }

    /*
     * Resizes nodes based on the mouse distance to the node
     */
    this.resizeNodes = function(mouseX, mouseY){
        var minFont = 0.5;
        var maxFont = 1.0;
        var xDistFactor = -0.50;
        var yDistFactor = -0.75;
        var graphAbs = getElementAbsolutePos(document.getElementById('graph')); 
        mouseY = mouseY - graphAbs.y;
        mouseX = mouseX - graphAbs.x;
        for(id in nodes){
            if(typeof nodes[id] != 'undefined'){
                node = nodes[id];
                if(node.val.style.display == 'none'){
                    continue;
                }
                var size = null;
                if(node.clicked){
                    size = maxFont;
                }
                var width = node.width;
                var height = node.height;
                
                var centerX = Math.round(node.left) + width/2;
                var centerY = Math.round(node.top) + height/2;
                if(size == null){
                    var dX = centerX - mouseX;
                    var dY = centerY - mouseY;

                    var thresholdDistance = 10/(Math.sqrt(Math.sqrt(Math.pow(dX, 2)/Math.pow(width, xDistFactor) + 
                            Math.pow(dY, 2)/Math.pow(height, yDistFactor)))+1);
                    size = Math.min(maxFont, Math.max(minFont, thresholdDistance));
                }

                if(Math.round(node.fontSize*50) != Math.round(size*50)){
                    // Preliminary Check, if font hasn't changed hardly any, then don't continue
                    node.fontSize = size;
                    node.val.style.fontSize = size + "em";
                    node.width = node.val.clientWidth;
                    node.height = node.val.clientHeight;
                    
                    var newWidth = node.width;
                    var newHeight = node.height;
                    //Only redraw the node if the size has actually changed
                    if(width != newWidth || height != newHeight){
                        deltaWidth = newWidth - width;
                        deltaHeight = newHeight - height;
                        newX = Math.min(graphWidth - 2 - newWidth, Math.max(0, centerX - deltaWidth/2 - Math.floor(width/2)));
                        newY = Math.max(0, Math.min(graphHeight - 2 - newHeight, centerY -deltaHeight/2 - Math.floor(height/2)));
                        node.val.style.left = Math.floor(newX) + "px";
                        node.val.style.top = Math.floor(newY) + "px";
                        
                        node.top = node.val.offsetTop;
                        node.left = node.val.offsetLeft;
                        jsPlumb.repaint($(node.val));
                    }
                }
            }
        }
    }

    /*
     * Updates the information div with the info from the selected node
     */
    this.updateInfo = function(id){
        $(".graph_info").html('');
        $(".graph_info").append("<span class='info_head'>" + nodes[id].name.replace(/&nbsp;/g, ' ') + "</span>");
        $(".graph_info").append("<span>" + nodes[id].desc + "</span>");
    }

    /*
     * Changes the CSS so that when the mouse clicks a node
     * the previously clicked node is unclicked, and the new node
     * has a clicked style.
     */
    this.clickNode = function(id){
        if(draggedNode != null){
            return;
        }
        explanationUpdated = false;
        var lastClickedNode = clickedNode;
        if(typeof nodes[id] == 'undefined'){
            // Return out of the function since the node most likeley non-existent
            // or is being faded out.
            return;
        }
        var l1 = document.getElementById(id);
        
        $(l1).addClass("window_click");
        
        if(clickedNode != null && id != clickedNode.id){
            nodes[id].clicked = true;
            thisGraph.unClickNode();
        }
        else if(clickedNode == null){
            nodes[id].clicked = true;
            thisGraph.resizeNodes(-1, -1);
        }
        else{
            nodes[id].clicked = true;
            thisGraph.resizeNodes(-1, -1);
        }
        
        if(lastClickedNode != null && id != lastClickedNode.id && draggedNode == null){
            clickedNode = document.getElementById(id);
            thisGraph.addNewNode(id.replace("n", "").replace("_l1", ""), null, null);
        }

        clickedNode = document.getElementById(id);
        draggedNode = null;
        thisGraph.updateInfo(id);
        if(lastClickedNode == null || id != lastClickedNode.id){
            frame = 0;
            thisGraph.repelNodesInit();
        }
    }

    /*
     * Resets the style of the node to it's original form after a click.
     */
    this.unClickNode = function(){
        if(clickedNode != null){
            id = clickedNode.id;
            var l1 = clickedNode;
            
            $(l1).removeClass("window_click window_hover");
            if(typeof nodes[id] != 'undefined'){
                nodes[id].clicked = false;
            }
        }
        clickedNode = null;
        thisGraph.resizeNodes(-1, -1);
        jsPlumb.repaint($(l1));
    }

    /*
     * Changes the CSS so that when the mouse hovers over a node
     * there is a hover effect.
     */
    this.hoverNode = function(id){
        toolTipType="node";
        hoveredNode = id;
        var l1 = document.getElementById(id);
        
        if(clickedNode != null && id == clickedNode.id){
            $(l1).addClass("window_click_hover");
        }
        else{
            $(l1).addClass("window_hover");
        }
    }

    /*
     * Resets the CSS so that when the mouse unhovers a node
     * the node goes back to its original style.
     */
    this.unHoverNode = function(id){
        hoveredNode = null;
        var l1 = document.getElementById(id);
        
        if(clickedNode != null && id == clickedNode.id){
            $(l1).removeClass("window_click_hover");
        }
        else{
            $(l1).removeClass("window_hover");
        }
    }
}
