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
		    var desc = $("<div class='window' style='background:#ffffff;border:1px solid #aaa;padding:3px;position:absolute;z-index:5000;-webkit-border-radius:5px;-moz-border-radius: 5px;border-radius:5px;cursor:pointer;'>Description</div>");
		    desc.fadeIn();
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
