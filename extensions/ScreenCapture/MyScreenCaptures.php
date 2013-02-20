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
	                height: 13px;
	                margin-left:-2px;
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
	    <script type='text/javascript' src='$wgServer$wgScriptPath/extensions/ScreenCapture/viewer.js'></script>
EOF
);
	}
	
}
?>
