<?php

$wgHooks['UserLoginForm'][] = 'disableLoginForm';
$wgHooks['UserLoginComplete'][] = 'redirectTo';

function disableLoginForm($template){
    global $wgOut;
    $wgOut->setPageTitle("Main Page");
    $title = Title::newFromText("Main Page");
    $mainPage = Article::newFromID($title->getArticleID());
    //$wgOut->addWikiText($mainPage->getContent());
    $wgOut->output();
    close();
}

function redirectTo($user, $html){
    global $wgServer, $wgScriptPath;
    $returnto = @urldecode($_GET['returnto']);
    redirect("$wgServer$wgScriptPath/index.php/$returnto");
}

?>
