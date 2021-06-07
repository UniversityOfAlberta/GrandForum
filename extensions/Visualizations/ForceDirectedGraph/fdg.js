function createFDG(id, url, callback){

    var network;
    $.get(url, function(response){
        nodesd = new vis.DataSet(response.nodes);
        edgesd = new vis.DataSet(response.edges);
        data = {
            nodes: nodesd,
            edges: edgesd
        };
        // create a network
        var container = document.getElementById(id);
        var options = {
          nodes: {
            shape: "dot",
            scaling: {
              min: 10,
              max: 30,
            },
            font: {
              size: 30,
              strokeWidth: 2,
              face: "Tahoma",
            },
          },
          edges: {
            width: 0.15,
            color: { inherit: "from" },
            smooth: {
              type: "continuous",
            },
          },
          autoResize: true,
          physics: {
            stabilization: {
              enabled: true,
              iterations: 250
            },
            barnesHut: {
              gravitationalConstant: -80000,
              springConstant: 0.001,
              springLength: 200,
            },
          },
          interaction: {
            tooltipDelay: 100
          },
        };

        network = new vis.Network(container, data, options);
        globalNetwork = network;
        
        updateNetworkEdges = function(groups){
            var toRemove = [];
            var toAdd = [];
            _.each(response.edges, function(edge){
                toRemove.push(edge.id);
            });
            _.each(response.edges, function(edge){
                var width = 0;
                var newEdge = Object.assign({}, edge);
                newEdge.width = 0
                _.each(edge.groups, function(width, group){
                    if(groups.indexOf(group) != -1){
                        newEdge.width += width;
                        newEdge.width = Math.min(5, newEdge.width);
                    }
                });
                if(newEdge.width > 0){
                    toAdd.push(newEdge);
                }
            });
            data.edges.remove(toRemove);
            data.edges.add(toAdd);
        }
        
        if(callback != undefined){
            callback();
        }
    });
}
