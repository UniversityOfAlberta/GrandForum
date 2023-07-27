<?php

$wgHooks['UserLoginForm'][] = 'disableLoginForm';
$wgHooks['UserLoginComplete'][] = 'redirectTo';
$wgHooks['UserLogout'][] = 'logoutMessage';

function disableLoginForm($template){
    global $wgOut;
    $wgOut->setPageTitle("Main Page");
    $title = Title::newFromText("Main Page");
    $mainPage = Article::newFromID($title->getArticleID());
    //$wgOut->addWikiText($mainPage->getContent());
    $wgOut->output();
    exit;
    return true;
}

function redirectTo($user, $html){
    global $wgServer, $wgScriptPath;
    $returnto = @urldecode($_GET['returnto']);
    redirect("$wgServer$wgScriptPath/index.php/$returnto");
}

function logoutMessage(&$user){
    global $wgMessage, $config;
    $wgMessage->addSuccess("<en>You are now logged out of {$config->getValue('siteName')}</en><fr>Vous êtes maintenant déconnecté de {$config->getValue('siteName')}</fr>");
    return true;
}


?>
