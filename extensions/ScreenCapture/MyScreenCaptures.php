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
	            
	            $wgOut->addHTML("<div style='position:relative;'><div id='graph' style='position:absolute;top:0;bottom:0;left:0;right:0;padding:0'>");
	            $i = 0;

                $wgOut->addHTML("<style>
                    .graphLabel {
                        font-size:10px;
                        border:1px solid #aaa; 
                        padding:5px;
                        padding:5px;
                        z-index:4000;
                        background: rgba(255,255,255,0.85);
                        height: 13px;
                        margin-left:-2px;
                    }
                </style>");
	            
	            $wgOut->addHTML("</div></div>");
	            
	            $wgOut->addHTML(<<<EOF
	            <script type='text/javascript' src='$wgServer$wgScriptPath/extensions/ScreenCapture/viewer.js'></script>
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
	    $wgOut->addHTML("<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
<thead><tr bgcolor='#F2F2F2'><th>Date</th><th>Graph Editor</th><th># Screenshots</th></tr></thead><tbody>");
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
