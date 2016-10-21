<?php
    $wgHooks['BeforePageDisplay'][] = 'agreement';

    function agreement(&$out, &$skin){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn() && !$me->isConfidential()){
            $wgOut->clearHTML();
            if($wgTitle == null){
                // Depending on when this function is called, the title may not be created yet, so make an empty one
                $wgTitle = new Title();
            }
            if(isset($_POST['agreement'])){
                if($_POST['agreement'] == 'I agree'){
                    $collect_comments = (isset($_POST['collect_comments'])) ? '1' : '0';
                    $collect_demo     = (isset($_POST['collect_demo']))     ? '1' : '0';
                    DBFunctions::update('mw_user',
                                        array('collect_comments' => $collect_comments,
                                              'collect_demo' => $collect_demo,
                                              'confidential' => '1'),
                                        array('user_id'=>$me->getId()));
                    redirect("$wgServer$wgScriptPath/index.php");
                }
                else if($_POST['agreement'] == 'I do not agree'){
                    $wgMessage->addWarning('You must agree with Confidentiality Agreement to continue to the forum.');
                }
            }
            $wgOut->setPageTitle("Confidentiality Agreement");
            $agreement = file_get_contents("extensions/Caps/agreement.html");
            $wgOut->addHTML("$agreement<br />
                            <form method='POST' action='$wgServer$wgScriptPath/index.php'>
                                <input type='checkbox' name='collect_comments' value='Yes' checked /> I agree to participate in the collection of any comments, statements or questions that I post on the site<br />
                                <input type='checkbox' name='collect_demo' value='Yes' checked /> I agree to provide my postal code and professional demographics to understand the availability of abortion care across the country.<br /><br />
                                Do you agree to the Confidentiality Agreement?<br />
                                <input type='submit' name='agreement' value='I agree' />&nbsp;<input type='submit' name='agreement' value='I do not agree' />
                            </form>");
            $wgOut->disable();
        }
        return true;
    }

?>
