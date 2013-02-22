(function( $ ){

    $.fn.graph = function(data) {
        var model = data;
        var self = this;

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
           					//label : "<input type='text' class='stealth' value='' style='position:absolute;' />", 
           					label: '',
           					location:0.5,
           					id:"label"
           				  } ],
           				["Arrow", {
               				location:1.0, width:40,
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
        
        this.initGraph = function(){
            $(self).append("<div id='nodeMenu' class='graphMenu' style='display:none;position:absolute;z-index:6000'><ul><li><a href='#' name='addDesc'>Add Description</a></li></ul></div><div id='descMenu' class='graphMenu' style='display:none;position:absolute;z-index:6000'><ul><li><a href='#' name='editDesc'>Edit</a></li><li><a href='#' name='deleteDesc'>Delete</a></li></ul></div>");
            
            var id = 0;
            model.events.forEach(function(val, i){
                if(val.event == 'screen'){
                    var x = (8 + 25*(id%4));
                    var y = (200*Math.ceil((id+1)/4) - 100);
                    if(Math.ceil((id+1)/4) % 2 == 0){
                        x = 100-x-9;
                    }
                    
                    $(self).append("<div id='window" + id + "' name='" + i + "' style='background:#ffffff;width:100px;height:50px;border:1px solid #aaa;padding:3px;position:absolute;left:" + x + "%;top:" + y + "px;z-index:5000;-webkit-border-radius:5px;-moz-border-radius: 5px;border-radius:5px;cursor:pointer;' class='window'><a href='../index.php?action=getRecordedImage&id=" + val.img + "' style='height:100%;display:block;' rel='story'><img src='../index.php?action=getRecordedImage&id=" + val.img + "' style='position:absolute;top:0;bottom:0;margin:auto;max-width:100px;max-height:50px;' /></a></div>");
                    id++;
                }
            });
            
            // chrome fix.
            document.onselectstart = function () { return false; };

            jsPlumb.DefaultDragOptions = { cursor: 'pointer', zIndex:2000};
            jsPlumb.setRenderMode(jsPlumb.SVG);
            jsPlumb.bind("click", function(connection){
                var sourceId = connection.sourceId;
                var targetId = connection.targetId;
            });

            jsPlumb.draggable(jsPlumb.getSelector(".window"), {containment:"parent"});
            
            $.each($($(".window"), $(self)), function(index, val){
                if(index > 0){
                    var con = jsPlumb.connect({
	                        source:"window" + (index-1),
	                        target:"window" + index,
	                        }, connector);
	                $("<a rel='story' name1='" + $("#window" + (index-1)).attr('name') + "' name2='" + $("#window" + (index)).attr('name') + "' href='" + $("#window" + (index) + " a").attr('href') + "'></a>").insertBefore($(this));
                }
                var el = $($('.graphLabel'), $(self)).last();
                el.css('cursor', 'pointer');
                el.css('margin-left', '0');
                var transition = '';
                if(model.events[$("#window" + index).attr('name')].transition != ''){
                    if(model.events[$("#window" + index).attr('name')].transition != undefined){
                        transition = model.events[$("#window" + index).attr('name')].transition;
                    }
                    var beforeWidth = $(el).width();
                    el.html(transition.substring(0,16));
                    var afterWidth = $(el).width();
                    $(el).css('margin-left', parseInt($(el).css('margin-left')) - parseInt((afterWidth-beforeWidth)/2));
                }
                
                // Transition Colorbox
                el.click(function(){
                    var editContent = $("<textarea id='nodeTransition' style='height:190px;width:100%;margin:0;'>" + transition + "</textarea><button id='saveTrans'>Save</button><button id='cancelTrans'>Cancel</button>");
                    $.colorbox({open:true,
                                width:'500px',
                                height:'300px',
                                html:editContent,
                                onComplete:function(){$("textarea#nodeTransition").focus()}
                               });
                    $("#saveTrans").click(function(){
                        transition = $("#colorbox textarea").val();
                        model.events[$("#window" + index).attr('name')].transition = transition;
                        var beforeWidth = $(el).width();
                        el.html(transition.substring(0,16));
                        var afterWidth = $(el).width();
                        $.colorbox.close();
                        jsPlumb.repaintEverything();
                        $(el).css('margin-left', parseInt($(el).css('margin-left')) - parseInt((afterWidth-beforeWidth)/2));
                        self.save();
                    });
                    $("#cancelTrans").click(function(){
                        $.colorbox.close();
                    });
                });
            });

            $.each($($('.window'), $(self)), function(index, value){
                $(value).bind("contextmenu",function(e){
                    var that = this;
                    $("#nodeMenu").fadeIn(100);
                    $("#nodeMenu > ul").menu();
                    $("#nodeMenu > ul a[name=addDesc]").click(function(){
                        self.addDesc(that, model.events[$(that).attr('name')].descriptions);
                    });
                    $("#nodeMenu").css('left', $(this).position().left + $(this).width()).css('top', $(this).position().top);
                    e.stopPropagation();
                    return false;
                });
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

            // Screenshot Colorbox
            function next(){
                var img = $(".cboxPhoto");
                if(img.length > 0){
                    var desiredWidth = $("#cboxLoadedContent").width()*0.2;
                    var desiredHeight = img.height()*0.2;
                    img.removeAttr('height')
                                   .css('position', 'absolute')
                                   .css('left', ($("#cboxLoadedContent").width()/2 - img.width()/2) + 'px')
                                   .css('border-style', 'solid')
                                   .css('border-width', '0')
                                   .css('border-color', 'rgba(0,0,0,0.1)');
                    img.animate({width:desiredWidth + 'px',
                                 top: (($("#cboxLoadedContent").height()/2) - 5 - desiredHeight/2) + 'px',
                                 left: 0,
                                 'border-width': '3px'
                                }, 400, $.colorbox.next);
                }
                else{
                    var that = $("#rightImg img");
                    $(that).parent().stop();
                    var desiredWidth = $("#cboxLoadedContent table").width()*1;
                    var img = $(that).clone();
                    var currentWidth = $(that).width();
                    img.css('position','absolute')
                       .css('right',0)
                       .css('z-index', '1000')
                       .css('top', $(that).parent().height()/2 - $(that).height()/2)
                       .width(currentWidth + 'px');
                    $(that).parent().append(img);
                    img.animate({'width':desiredWidth + 'px', top:0, 'border-width':0}, 400, $.colorbox.next);
                }
            }
            
            function prev(){
                var img = $(".cboxPhoto");
                if(img.length > 0){
                    var desiredWidth = $("#cboxLoadedContent").width()*0.2;
                    var desiredHeight = img.height()*0.2;
                    img.removeAttr('height')
                                   .css('position', 'absolute')
                                   .css('right', ($("#cboxLoadedContent").width()/2 - img.width()/2) + 'px')
                                   .css('border-style', 'solid')
                                   .css('border-width', '0')
                                   .css('border-color', 'rgba(0,0,0,0.1)');
                    img.animate({width:desiredWidth + 'px',
                                 top: (($("#cboxLoadedContent").height()/2) - 5 - desiredHeight/2) + 'px',
                                 right: 0,
                                 'border-width': '3px'
                                }, 400, $.colorbox.prev);
                }
                else{
                    var that = $("#leftImg img");
                    $(that).parent().stop();
                    var desiredWidth = $("#cboxLoadedContent table").width()*1;
                    var img = $(that).clone();
                    var currentWidth = $(that).width();
                    img.css('position','absolute')
                       .css('left',0)
                       .css('z-index', '1000')
                       .css('top', $(that).parent().height()/2 - $(that).height()/2)
                       .width(currentWidth + 'px');
                    $(that).parent().append(img);
                    
                    img.animate({'width':desiredWidth + 'px', top:0, 'border-width':0}, 400, $.colorbox.prev);
                }
            }
            
            function getMaxWidth(img){
                var imgWidth = $(img).width();
                var imgHeight = $(img).height();
                return ((imgWidth*$("#cboxLoadedContent").height())/imgHeight) - 6;
            }
            
            $($("a[rel=story]"), $(self)).colorbox({
                 photo:true,
                 transition:'none',
                 speed:'0',
                 maxWidth:'85%',
                 maxHeight:'85%',
                 scrolling:false,
                 resize:false,
                 onOpen:function(){
                    $("#cboxPrevious").unbind('click');
                    $("#cboxNext").unbind('click');
                    $("#cboxPrevious").click(prev);
                    $("#cboxNext").click(next);
                 },
                 onComplete:function(){
                    $(".cboxPhoto")[0].onclick = undefined;
                    $(".cboxPhoto").click(next);
                    $.colorbox.resize({width:'85%', height:'85%'});
                    
                    if($(this).attr('name1') != undefined && $(this).attr('name2') != undefined){
                        // Transition
                        var href1 = $("div[name=" + $(this).attr('name1') + "] a").attr('href');
                        var href2 = $("div[name=" + $(this).attr('name2') + "] a").attr('href');
                        $("#cboxLoadedContent").empty();
                        var img1 = $("<img class='screenThumb' style='border-width:3px;border-style: solid;border-color: rgba(0,0,0,0.1);' src='" + href1 + "' />");
                        var img2 = $("<img class='screenThumb' style='border-width:3px;border-style: solid;border-color: rgba(0,0,0,0.1);' src='" + href2 + "' />");
                        var transition = model.events[$(this).attr('name2')].transition;
                        img1.width('100%')
                            .css('cursor', 'pointer')
                            .css('z-index', 100)
                            .css('position', 'relative')
                            .click(prev);
                        img2.width('100%')
                            .css('cursor', 'pointer')
                            .css('z-index', 100)
                            .css('position', 'relative')
                            .click(next);
                        $("#cboxLoadedContent").html("<table cellspacing='0' cellpadding='0' height='100%' width='100%' style='border-collapse:separate;'><tr><td id='leftImg' valign='middle' align='left' width='20%' style='padding-right:6px;'></td><td valign='middle' align='center'><div style='position:relative;z-index:100;color:#888888;padding:30px;'>" + transition + "<div style='position:absolute;height:30px;margin-top:-15px;top:50%;left:0;background:#000000;opacity:0.1;right:30px;'><img src='../extensions/ScreenCapture/arrow.png' style='position:absolute;right:-30px;margin-top:-15px;'></div></div></td><td id='rightImg' valign='middle' align='right' width='20%' style='padding-right:6px;'></td></tr></table>");
                        
                        $("#cboxLoadedContent #leftImg").append(img1);
                        $("#cboxLoadedContent #rightImg").append(img2);

                        if(img1.width() > getMaxWidth(img1)){
                            $("#cboxLoadedContent #leftImg").width(getMaxWidth(img1));
                        }
                        if(img2.width() > getMaxWidth(img2)){
                            $("#cboxLoadedContent #rightImg").width(getMaxWidth(img2));
                        }
                        
                        $(".screenThumb").mouseover(function(e){
                            $(this).parent().stop();
                            console.log(getMaxWidth($(this)));
                            var desiredWidth = Math.min(getMaxWidth($(this)), $("#cboxLoadedContent table").width()*0.4);
                            $(this).parent().animate({'width':(desiredWidth) + 'px'}, 250);
                        });
                        $(".screenThumb").mouseout(function(e){
                            $(this).parent().stop();
                            var desiredWidth = Math.min(getMaxWidth($(this)), $("#cboxLoadedContent table").width()*0.2);
                            $(this).parent().animate({'width':desiredWidth + 'px'}, 250);
                        });
                    }
                    else{
                        // Screenshot
                        var descs = "";
                        model.events[$(this).parent().attr('name')].descriptions.forEach(function(desc, i){
                             descs += "<p>" + desc + "</p><hr />";
                        });
                        var content = "<div class='sideOverlay' style='position:absolute;top:0;right:-175px;bottom:28px;width:225px;font-size:10px;padding:3px;background:#222;color:#fff;opacity:0.1;overflow-y:auto;'><div style='font-weight:bold;font-size:1.5em;'>Descriptions</div><hr />" + descs + "</div>";
                        $("#cboxLoadedContent").append(content);
                        $(".sideOverlay").mouseover(function(e){
                            $(this).stop();
                            $(this).animate({'opacity':0.85,
                                             'right':0
                                            }, 250);
                        });
                        $(".sideOverlay").mouseout(function(e){
                            $(this).stop();
                            $(this).animate({'opacity':0.1,
                                             'right':'-175px'
                                            }, 250);
                        });
                    }
                    
                 }
            });
                
            $(self).parent().height(Math.max((Math.ceil($($(".window"), $(self)).length/4))*200 + 100, $(self).parent().parent().height()));
            
            $.each($($(".window"), $(self)), function(index, val){
                model.events[$(val).attr('name')].descriptions.forEach(function(desc, i){
                    self.addDesc($(val), model.events[$(val).attr('name')].descriptions, i);
                });
            });
            
            $(document).ready(function(){
                jsPlumb.repaintEverything();
            });
            $(window).resize(function(){
                jsPlumb.repaintEverything();
            });
        }
        
        this.save = function(){
            $.post('../index.php?action=setRecordedStory', {'story': JSON.stringify(model)}, function(){});
        }

        this.deleteDesc = function(desc){
            var cons = jsPlumb.getConnections({target:[desc]});
            $(cons[0].endpoints[0].canvas).fadeOut();
            $(cons[0].endpoints[1].canvas).fadeOut();
            $(cons[0].canvas).fadeOut(function(){
                jsPlumb.detachAllConnections(desc);
            });
            
            desc.fadeOut(function(){
                
            });
        }

        this.addDesc = function(parent, descriptions, i){
            var description;
            if(i == undefined){
                description = "";
                i = -1;
                descriptions.forEach(function(value, index){
                    i = index;
                });
                i++;
                descriptions[i] = description;
                self.save();
            }
            else{
                description = descriptions[i];
            }
            if(description == undefined){
                return;
            }
            var desc = $("<div class='window' style='min-height:15px;min-width:15px;white-space:nowrap;background:rgba(255,255,255,0.8);border:1px solid #aaa;padding:3px;position:absolute;z-index:5000;-webkit-border-radius:5px;-moz-border-radius: 5px;border-radius:5px;cursor:pointer;opacity:0.0001'>" + description.substring(0,10) + "</div>");
            
            $(self).append(desc);
            self.positionNode(parent, desc);
            
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
            
            // Description Colorbox
            function showEdit(){
                var editContent = $("<textarea id='nodeDescription' style='height:190px;width:100%;margin:0;'>" + description + "</textarea><button id='saveDesc'>Save</button><button id='cancelDesc'>Cancel</button>");
                $.colorbox({open:true,
                            width:'500px',
                            height:'300px',
                            html:editContent,
                            onComplete:function(){$("textarea#nodeDescription").focus()}
                           });
                $("#saveDesc").click(function(){
                    description = $("#colorbox textarea").val();
                    descriptions[i] = description;
                    desc.html(description.substring(0,10));
                    $.colorbox.close();
                    jsPlumb.repaintEverything();
                    self.save();
                });
                $("#cancelDesc").click(function(){
                    $.colorbox.close();
                });
            }
            
            $(desc).bind("contextmenu",function(e){
                var that = this;
                $("#descMenu").fadeIn(100);
                $("#descMenu > ul").menu();
                $("#descMenu > ul a[name=deleteDesc]").click(function(){
                    self.deleteDesc(desc);
                    delete descriptions[i];
                    self.save();
                });
                
                $("#descMenu > ul a[name=editDesc]").click(showEdit);
                $("#descMenu").css('left', $(this).position().left + $(this).width()).css('top', $(this).position().top);
                e.stopPropagation();
                return false;
            });
            $(desc).click(showEdit);
            jsPlumb.repaintEverything();
        }

        this.positionNode = function(parent, node){

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
            
            graphWidth = $(self).width();
            graphHeight = $(self).height();
            
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
                $.each($($('.window, .graphLabel'), $(self)), function(index, val){
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
        
        this.initGraph();
    }
})( jQuery );
