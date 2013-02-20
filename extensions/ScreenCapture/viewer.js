// chrome fix.
document.onselectstart = function () { return false; };

jsPlumb.DefaultDragOptions = { cursor: 'pointer', zIndex:2000};
jsPlumb.setRenderMode(jsPlumb.CANVAS);
jsPlumb.bind("click", function(connection){
    var sourceId = connection.sourceId;
    var targetId = connection.targetId;
});

jsPlumb.draggable(jsPlumb.getSelector(".window"), {containment:"parent"});

var connector = {				
	connector:"Straight",
   	anchor: "AutoDefault",
   	endpoint:"Blank",
   	detachable:false,
   	paintStyle:{ 
		lineWidth:6,
		strokeStyle:"#8C529D",
	},
	endpointStyle:{ fillStyle:"#8C529D" },			   
   	overlays : [ ["Label", {
   	                cssClass:"graphLabel",
   					label : "<input type='text' class='stealth' value='' style='position:absolute;' />", 
   					location:0.5,
   					id:"label",
   					events:{
						"click":function(label, evt) {
							//alert("clicked on label for connection " + label.component.id);
   						}
   					}
   				  } ],
   				["Arrow", {
	   				location:1.0, width:40,
	   				events:{
	   					"click":function(arrow, evt) {
	   						//alert("clicked on arrow for connection " + arrow.component.id);
	   					}
	   				}
				}]
	]
};

var connector2 = {				
	connector:"Straight",
	detachable:false,
   	anchor: "Continuous",
   	endpointStyle:{ radius:3, fillStyle:"#0769AD" },
   	paintStyle:{ 
		lineWidth:2,
		strokeStyle:"#0769AD",
	}
};

function deleteDesc(desc){
    var cons = jsPlumb.getConnections({target:[desc]});
    $(cons[0].endpoints[0].canvas).fadeOut();
    $(cons[0].endpoints[1].canvas).fadeOut();
    $(cons[0].canvas).fadeOut(function(){
        jsPlumb.detachAllConnections(desc);
    });
    
    desc.fadeOut(function(){
        desc.remove();
    });
}

function addDesc(parent){
    var description = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. In magna ligula, pretium nec pharetra id, accumsan varius enim. Praesent mollis, dui eu convallis faucibus, sapien est vehicula dui, vitae iaculis augue nisi eget elit. Sed ultrices mauris euismod neque luctus condimentum. In hendrerit eros at justo fringilla in interdum dui convallis. Praesent eget libero ac sem ornare hendrerit a quis tellus. Donec placerat, sem at suscipit iaculis, purus quam fringilla augue, nec porta eros risus ac sapien. Praesent sagittis, tellus eu semper dapibus, velit mauris elementum ipsum, at semper quam metus a purus. Suspendisse id elit vel quam consequat tempor id id lacus.\\n\\nQuisque ac turpis lacus. Phasellus nibh quam, vestibulum sit amet feugiat ac, aliquam nec dolor. Sed lectus arcu, venenatis in eleifend id, ornare non leo. Nunc pharetra, ante quis fringilla fringilla, quam ante consectetur nisi, a sodales lorem metus vel mauris. Nunc pellentesque sapien vulputate nibh scelerisque ultrices aliquet leo volutpat. Quisque molestie vulputate magna, et volutpat sapien accumsan ac. Donec rutrum hendrerit tellus, sit amet consectetur quam viverra id. Curabitur egestas massa at nibh dictum et lacinia nisl aliquam. Nullam cursus nunc vitae metus suscipit et volutpat neque lacinia. Integer lacinia molestie interdum. Integer et diam justo. In vitae rhoncus enim. In porttitor gravida sapien sollicitudin condimentum. Cras elit nisl, lobortis nec varius nec, pellentesque consectetur massa. Vestibulum commodo porttitor mauris quis tristique. Donec varius nunc sed lectus consequat tempus.\\n\\nProin justo urna, lobortis non tincidunt commodo, aliquet quis nunc. In et purus nisi, et varius nisi. Pellentesque ultrices diam nec tellus tempus sagittis. Ut mattis lorem vel mauris commodo faucibus. Nunc sed sapien et urna sodales ullamcorper. Nulla ullamcorper sagittis metus, ac elementum ante mattis at. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin eu arcu ante, pharetra vulputate arcu. Ut molestie rutrum malesuada.\\n\\nSuspendisse potenti. Aenean ultricies lacus a nulla adipiscing ac pellentesque magna tempus. Mauris cursus, dolor in interdum tempor, dolor ipsum adipiscing orci, id gravida justo felis quis risus. Aliquam hendrerit libero et velit feugiat in gravida orci pharetra. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Curabitur bibendum mollis justo, vel eleifend lorem placerat at. Duis eu libero eget urna venenatis ullamcorper. Maecenas vel quam et leo porttitor aliquet. Curabitur non nunc eget elit ultricies lacinia id facilisis urna. Suspendisse ac quam a lorem cursus imperdiet vel sit amet sem. Duis luctus posuere neque et faucibus. Nunc suscipit, arcu interdum molestie feugiat, magna dolor blandit est, quis semper arcu est a enim. Duis lobortis purus eget nibh ultrices ullamcorper. Ut sit amet porta mauris. Pellentesque imperdiet nulla non est tempus vulputate.";
    var desc = $("<div class='window' style='white-space:nowrap;background:#ffffff;border:1px solid #aaa;padding:3px;position:absolute;z-index:5000;-webkit-border-radius:5px;-moz-border-radius: 5px;border-radius:5px;cursor:pointer;opacity:0.0001'>" + description.substring(0,10) + "</div>");
    desc.colorbox({width:'500px',height:'300px',html:description.replace(/\\n/g, '<br />')});
    
    $("#graph").append(desc);
    positionNode(parent, desc);
    
    jsPlumb.draggable(desc, {containment:"parent"});
    var con = jsPlumb.connect({
		        source: parent,
		        target:desc,
		        }, connector2);
    $(con.canvas).css('display', 'none');
    $(con.endpoints[0].canvas).css('display', 'none');
    $(con.endpoints[1].canvas).css('display', 'none');
    desc.animate({opacity: 1}, 100);
    $(con.canvas).fadeIn();
    $(con.endpoints[0].canvas).fadeIn();
    $(con.endpoints[1].canvas).fadeIn();
    $(desc).bind("contextmenu",function(e){
        var that = this;
        $("#descMenu").fadeIn(100);
        $("#descMenu > ul").menu();
        $("#descMenu > ul a[name=deleteDesc]").click(function(){
            deleteDesc(desc);
        });
        $("#descMenu > ul a[name=editDesc]").click(function(){
            var content = $("<textarea style='height:190px;width:100%;margin:0;'>" + description + "</textarea><button id='saveDesc'>Save</button><button id='cancelDesc'>Cancel</button>");
            $.colorbox({open:true,width:'500px',height:'300px',html:content});
            $("#saveDesc").click(function(){
                description = $("#colorbox textarea").val();
                desc.html(description.substring(0,10));
                desc.colorbox({width:'500px',height:'300px',html:description.replace(/\\n/g, '<br />')});
                $.colorbox.close();
            });
            $("#cancelDesc").click(function(){
                $.colorbox.close();
            });
        });
        $("#descMenu").css('left', $(this).position().left + $(this).width()).css('top', $(this).position().top);
        e.stopPropagation();
        return false;
    });
}

function positionNode(parent, node){

    var centroidCache = Array();

    function centroid(node1){
        var id = $(node1).attr('id');
        if(centroidCache[id] == undefined){
            x1 = $(node1).position().left + $(node1).width()/2;
            y1 = $(node1).position().top + $(node1).height()/2;
            centroidCache[id] = {x:x1,y:y1};
        }
        return centroidCache[id];
    }

    function distance(node1, node2){
        var c1, c2;
        c1 = centroid(node1);
        c2 = centroid(node2);
        return Math.sqrt(Math.pow(c2.x-c1.x, 2) + Math.pow(c2.y-c1.y, 2));
    }
    
    var desired = 75;
    var nIterations = 25;
    
    var pC = centroid(parent);
    var rand1 = (Math.random() * 2) - 1;
    var rand2 = (Math.random() * 2) - 1;
    
    node.css('left', pC.x - node.width()/2 + rand1*25)
        .css('top', pC.y - node.height()/2 + rand2*25);
    
    graphWidth = $("#graph").width();
    graphHeight = $("#graph").height();
    
    nodeWidth = node.width();
    nodeHeight = node.height();
    
    var k = Math.min(graphWidth, graphHeight);
    var id = node.attr('id');
    for(var i = 0; i < nIterations;i++){
        var C = Math.log( i + 1 ) * 100;
        centroidCache[id] = undefined;
        var c = centroid(node);
        var c1 = centroid(node);
        
        var pD = distance(parent, node);
        var f = Math.min(0.5, Math.pow(Math.min(Math.abs(desired-pD), desired), 2)/Math.pow(desired, 2));
        if((desired-pD) <= 0){
            c1.x -= (c.x-pC.x)*(f);
            c1.y -= (c.y-pC.y)*(f);
            c1.x = Math.min(graphWidth - nodeWidth/2, Math.max(0 + nodeWidth/2, c1.x));
            c1.y = Math.min(graphHeight - nodeHeight/2, Math.max(0 + nodeHeight/2, c1.y));
        }
        $.each($('#graph .window, #graph .graphLabel'), function(index, val){
            if($(val).attr('id') != id){
                var vD = distance(val, node);
                var mul = k * k / (vD*vD*C);
                var vC = centroid(val);

                c1.x += (c.x-vC.x) * mul;
                c1.y += (c.y-vC.y) * mul;
                c1.x = Math.min(graphWidth - nodeWidth/2, Math.max(0 + nodeWidth/2, c1.x));
                c1.y = Math.min(graphHeight - nodeHeight/2, Math.max(0 + nodeHeight/2, c1.y));
            }
        });
        
        node.css('left', c1.x - nodeWidth/2).css('top', c1.y - nodeHeight/2);
    }
}

$.each($("#graph .window"), function(index, val){
    if(index > 0){
        var con = jsPlumb.connect({
		        source:"window" + (index-1),
		        target:"window" + index,
		        }, connector);
    }
});

setInterval(function(){
    $.each($("#graph .graphLabel > input"), function(){
        var val = $(this).val();
        var oldVal = $(this).attr('data');
        if(val != oldVal){
            $(this).attr('data', val);
            var beforeWidth = $(this).width();
            var tmpSpan = $("<span style='white-space:nowrap;'>" + val + "</span>");
            $(this).parent().append(tmpSpan);
            var width = tmpSpan.width();
            tmpSpan.remove();
            $(this).parent().width(width + 4);
            $(this).width(width + 15);
            var afterWidth = $(this).width();
            $(this).parent().css('margin-left', parseInt($(this).parent().css('margin-left')) - parseInt((afterWidth-beforeWidth)/2));
        }
    });
}, 33);

$('#graph .window').bind("contextmenu",function(e){
    var that = this;
    $("#nodeMenu").fadeIn(100);
    $("#nodeMenu > ul").menu();
    $("#nodeMenu > ul a[name=addDesc]").click(function(){
        addDesc(that);
    });
    $("#nodeMenu").css('left', $(this).position().left + $(this).width()).css('top', $(this).position().top);
    e.stopPropagation();
    return false;
});

$(window).bind("contextmenu", function(e){
    $(".graphMenu a").unbind("click");
    $(".graphMenu").fadeOut(100);
});

$(window).click(function(e){
    if(e.button == 0){
        $(".graphMenu a").unbind("click");
        $(".graphMenu").fadeOut(100);
    }
});

$("#graph .window a").colorbox({photo:true,
                                maxWidth:'85%',
                                maxHeight:'85%'
                               });    
$("#graph").parent().height(Math.max((Math.ceil($("#graph .window").length/4))*200 + 100, $("#graph").parent().parent().height()));
$(document).ready(function(){
    jsPlumb.repaintEverything();
});
$(window).resize(function(){
    jsPlumb.repaintEverything();
});
