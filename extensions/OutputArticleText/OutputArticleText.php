<?php

$wgHooks['BeforePageDisplay'][] = 'outputArticleText';

function outputArticleText( &$out, &$sk ) {
    global $wgTitle;
	if(isset($_GET['outputArticleText'])){
        echo "<h1>{$wgTitle->getText()}</h1>";
	    echo $out->getHTML();
	    exit;
	    $out->disable();
	}
	return false;
}

?>
