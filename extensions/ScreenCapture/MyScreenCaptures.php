<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MyScreenCaptures'] = 'MyScreenCaptures';
$wgExtensionMessagesFiles['MyScreenCaptures'] = $dir . 'MyScreenCaptures.i18n.php';
$wgSpecialPageGroups['MyScreenCaptures'] = 'grand-tools';

function runMyScreenCaptures($par) {
	MyScreenCaptures::run($par);
}

class MyScreenCaptures extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('MyScreenCaptures');
		SpecialPage::SpecialPage("MyScreenCaptures", HQP.'+', true, 'runMyScreenCaptures');
	}
	
	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $recordings = $me->getRecordings();
	    $recording = $recordings[count($recordings)-1];
	    
	    $wgOut->addHTML("<div style='position:relative;'><div id='graph' style='position:absolute;top:0;bottom:0;left:0;right:0;padding:0'>");
	    $i = 0;
	    
	    // Template-like html bits
	    $wgOut->addHTML(<<<EOF
	        <div id="nodeMenu" class='graphMenu' style='display:none;position:absolute;z-index:6000'>
	            <ul>
                    <li><a href='#' name='addDesc'>Add Description</a></li>
                </ul>
            </div>
            <div id="descMenu" class='graphMenu' style='display:none;position:absolute;z-index:6000'>
                <ul>
                    <li><a href='#' name='editDesc'>Edit</a></li>
                    <li><a href='#' name='deleteDesc'>Delete</a></li>
                </ul>
            </div>
EOF
);
	    
	    foreach($recording as $screen){
	        $x = (8 + 25*($i%4));
	        $y = (200*ceil(($i+1)/4) - 100);
	        if(ceil(($i+1)/4) % 2 == 0){
	            $x = 100-$x-9;
	        }
	        
	        $wgOut->addHTML("<style>
	            .graphLabel {
	                font-size:10px;
	                border:1px solid #aaa; 
	                padding:5px;
	                padding:5px;
	                z-index:4000;
	                background: rgba(255,255,255,0.85);
	            }
	        </style>");
	        $wgOut->addHTML("<div id='window$i' style='background:#ffffff;width:100px;height:50px;border:1px solid #aaa;padding:3px;position:absolute;left:{$x}%;top:{$y}px;z-index:5000;-webkit-border-radius:5px;-moz-border-radius: 5px;border-radius:5px;cursor:pointer;' class='window'>
	                            <a href='../index.php?action=getRecordedImage&id={$screen->img}' style='height:100%;display:block;' title='{$screen->url}<br />{$screen->date}' rel='story'>
	                                <img src='../index.php?action=getRecordedImage&id={$screen->img}' style='position:absolute;top:0;bottom:0;margin:auto;max-width:100px;max-height:50px;' />
	                            </a>
	                        </div>");
	        $i++;
	    }
	    $wgOut->addHTML("</div></div>");
	    
	    $wgOut->addHTML(<<<EOF
	    <script type='text/javascript'>
	    
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
		   					label : "HELLO WORLD", 
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
		    var desc = $("<div class='window' style='background:#ffffff;border:1px solid #aaa;padding:3px;position:absolute;z-index:5000;-webkit-border-radius:5px;-moz-border-radius: 5px;border-radius:5px;cursor:pointer;'>" + description.substring(0,10) + "</div>");
		    desc.fadeIn();
		    desc.colorbox({title:'Description',width:'500px',height:'300px',html:description.replace(/\\n/g, '<br />')});
            $("#graph").append(desc);
            jsPlumb.draggable(desc, {containment:"parent"});
            var con = jsPlumb.connect({
				        source: parent,
				        target:desc,
				        }, connector2);
	        $(con.canvas).css('display', 'none');
	        $(con.endpoints[0].canvas).css('display', 'none');
		    $(con.endpoints[1].canvas).css('display', 'none');
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
	                $.colorbox({open:true,title:'Edit Description',width:'500px',height:'300px',html:content});
	                $("#saveDesc").click(function(){
	                    description = $("#colorbox textarea").val();
	                    desc.html(description.substring(0,10));
	                    desc.colorbox({title:'Description',width:'500px',height:'300px',html:description.replace(/\\n/g, '<br />')});
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
        
        $.each($("#graph .window"), function(index, val){
            if(index > 0){
                var con = jsPlumb.connect({
				        source:"window" + (index-1),
				        target:"window" + index,
				        }, connector);
	        }
	    });
	    
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
        </script>
EOF
);
	}
	
}
?>
