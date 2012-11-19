<?php

$wgHooks['UserLoginForm'][] = 'disableLoginForm';
$wgHooks['UserLoginComplete'][] = 'redirectTo';

function disableLoginForm($template){
    global $wgOut;
    $wgOut->setPageTitle("Main Page");
    $title = Title::newFromText("Main Page");
    $mainPage = Article::newFromID($title->getArticleID());
    $wgOut->addWikiText($mainPage->getContent());
    $wgOut->output();
    exit;
    return true;
}

function redirectTo($user, $html){
    global $wgServer, $wgScriptPath;
    $returnto = urldecode($_GET['returnto']);
    header("Location: $wgServer$wgScriptPath/index.php/$returnto");
}

?>
