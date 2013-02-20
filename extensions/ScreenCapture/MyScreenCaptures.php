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
	    if(count($recordings) == 0){
	        return;
	    }
	    $recording = $recordings[count($recordings)-1];
	    
	    $wgOut->addHTML("<script type='text/javascript'>
	        $(document).ready(function(){
	            $.get('$wgServer$wgScriptPath/index.php?action=getRecordedStory&id={$recording->id}', function(response){
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
	
}
?>
