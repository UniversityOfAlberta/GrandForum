<?php
    $wgHooks['BeforePageDisplay'][] = 'agreement';

    function agreement(&$out, &$skin){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
	$me = Person::newFromWgUser();
	if($me->isLoggedIn() && $me->isCandidate()){
	    $wgOut->clearHTML();
            if($wgTitle == null){
            	// Depending on when this function is called, the title may not be created yet, so make an empty one
            	$wgTitle = new Title();
            }
	    if(isset($_POST['agreement'])){
		if($_POST['agreement'] == 'I agree'){
		    DBFunctions::update('mw_user',
				        array('candidate'=>'0'),
					array('user_id'=>$me->getId()));
		    redirect("$wgServer$wgScriptPath/index.php");
		}
		elseif($_POST['agreement'] == 'I do not agree'){
		    $wgMessage->addWarning('You must agree with Confidentiality Agreement to continue to the forum.');
		}

	    }
    	    $wgOut->setPageTitle("Confidentiality Agreement");
    	    $wgOut->addHTML("<iframe style='height:350px; width:100%;' frameborder=0 src='$wgServer$wgScriptPath/extensions/Caps/agreement.html'></iframe><form method='POST' action='$wgServer$wgScriptPath/index.php'><input type='submit' name='agreement' value='I agree' />&nbsp;<input type='submit' name='agreement' value='I do not agree' /></form>");
    	    //$wgOut->output();
    	    $wgOut->disable();
    	    //exit;
	}
	return true;	
    }

?>
