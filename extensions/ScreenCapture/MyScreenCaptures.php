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
	    if(!isset($_GET['id'])){
	        self::showTable();
	    }
        else{
	        $id = $_GET['id'];
	        $found = false;
	        foreach($recordings as $recording){
	            if($id == $recording->id){
	                $found = true;
	                break;
	            }
	        }
	        if($found){
	            $wgOut->addHTML("<script type='text/javascript'>
	                $(document).ready(function(){
	                    $.get('$wgServer$wgScriptPath/index.php?action=getRecordedStory&id={$id}', function(response){
	                        $('#graph').graph(response);
	                    });
	                });
	            </script>");
	            $wgOut->addHTML("<div>
	                <a style='cursor:pointer;' id='graphInstructionsLink'>Instructions</a>
	                <div id='graphInstructions' style='display:none;z-index:5000;font-size:11px;'>
	                    <p><b>Adding/Editing Descriptions</b><br />
	                        To add a description to a screenshot, right click the screenshot node, and click 'Add Description'.  This will create a new description node attached to the screenshot.  To edit this description, either click the description node, or right click and click 'Edit'.  This will bring up a dialogue box where you can enter in a description for that screenshot.  Once you are satisfied with the description, press the 'Save' button, and the graph will be updated and saved to the server.
	                    </p>
	                    <p><b>Deleting Descriptions</b><br />
	                        To delete a description, right click the description node, and click 'Delete'.  This will delete the node and the graph will be saved to the server.
	                    </p>
	                    <p><b>Editing Transition Text</b><br />
	                        Transition text can be edited by clicking the small box on the purple edge between screenshot nodes.  This will bring up a dialogue box where you can enter in a description for the transition between screenshots.  Once you are satisfied with the description, press the 'Save' button, and the graph will be updated and saved to the server.
	                    </p>
	                    <p><b>Viewing Screenshots</b><br />
	                        At any point while editing the graph, you can view a larger version of the screenshots by clicking on the screenshot nodes.  Here you can see the screenshot in the center of the window, and on the right edge of the window the screenshot descriptions can be viewed by hovering the mouse over the right edge of the window.<br />
	                        To view the next screenshot, you can click the current screenshot, or click the 'right' arrow at the bottom of the window (keyboard navigation with the arrow keys also works).  This will first show the transition between the two screenshots, and clicking on either of the two screenshots will show that one in full window.  To view the previous screenshot, click the 'left' arrow.<br />
	                        To leave the viewing mode, click outside of the window.
	                    </p>
	                </div>
	            </div>");
	            $wgOut->addHTML("<div style='position:relative;'><div id='graph' style='position:absolute;top:0;bottom:0;left:0;right:0;padding:0'>");
	            $i = 0;

                $wgOut->addHTML("<style>
                    .graphLabel {
                        font-size:10px;
                        line-height:12px;
                        border:1px solid #aaa; 
                        padding:5px;
                        z-index:4000;
                        background: rgba(255,255,255,0.85);
                        height: 13px;
                        margin-left:-2px;
                    }
                    
                    .graphLabel:hover {
                        border-color:#666666 !important;
                    }
                    
                    .window:hover {
                        border-color:#666666 !important;
                    }
                    
                    .ui-dialog {
                        z-index: 5000 !important;
                    }
                </style>");
	            
	            $wgOut->addHTML("</div></div>");
	            
	            $wgOut->addHTML(<<<EOF
	            <script type='text/javascript' src='$wgServer$wgScriptPath/extensions/ScreenCapture/viewer.js'></script>
                <script type='text/javascript'>
                    $("#graphInstructionsLink").click(function(){
                        $("#graphInstructions").dialog({title: "Instructions", width:600,height:400});
                    });
                </script>
EOF
                );
            }
            else{
                self::showTable();
            }
        }
	}
	
	function showTable(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $recordings = $me->getRecordings();
	    $wgOut->addHTML("<table class='indexTable' frame='box' rules='all'>
<thead><tr><th>Date</th><th>Graph Editor</th><th># Screenshots</th></tr></thead><tbody>");
	    foreach($recordings as $recording){
	        $nScreens = 0;
	        foreach($recording->events as $evt){
	            if($evt->event == 'screen'){
	                $nScreens++;
	            }
	        }
	        $wgOut->addHTML("<tr bgcolor='#F2F2F2'><td>".$recording->created."</td><td><a href='$wgServer$wgScriptPath/index.php/Special:MyScreenCaptures?id={$recording->id}'>Graph Editor</a></td><td align='right'>{$nScreens}</td></tr>");
	    }
	    $wgOut->addHTML("</tbody></table>");
	    $wgOut->addHTML("<script type='text/javascript'>
	        $('.indexTable').dataTable({'aaSorting': [ [0,'desc']]});
	        $('.dataTables_wrapper').width('650px');
	    </script>");
	}
	
}
?>
