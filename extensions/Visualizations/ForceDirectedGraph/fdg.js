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
    });
}
