<?php
    $wgHooks['BeforeDisplayNoArticleText'][] = 'agreement';

    function agreement(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn() && $me->isCandidate() && $wgTitle->isSpecialPage()){
            redirect($wgServer.$wgScriptPath.'/index.php/Special:CAPSCompleteRegister');
        }
        return true;
    }

?>
