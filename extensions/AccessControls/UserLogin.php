<?php

$wgHooks['UserLoginForm'][] = 'disableLoginForm';

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

?>
