function createFDG(id, url){

    var network;
    $.get(url, function(data){
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
              size: 12,
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
          physics: {
            stabilization: false,
            barnesHut: {
              gravitationalConstant: -80000,
              springConstant: 0.001,
              springLength: 200,
            },
          },
          interaction: {
            tooltipDelay: 200
          },
        };

        // Note: data is coming from ./datasources/WorldCup2014.js
        network = new vis.Network(container, data, options);
    });
}
