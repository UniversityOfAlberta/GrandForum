<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MyMailingLists'] = 'MyMailingLists'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MyMailingLists'] = $dir . 'MyMailingLists.i18n.php';
$wgSpecialPageGroups['MyMailingLists'] = 'other-tools';

$wgHooks['TopLevelTabs'][] = 'MyMailingLists::createTab';

function runMyMailingLists($par) {
  MyMailingLists::execute($par);
}

class MyMailingLists extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("MyMailingLists", null, true, 'runMyMailingLists');
    }
    
    function userCanExecute($user){
	    $me = Person::newFromUser($user);
	    return ($me->isLoggedIn() && !$me->isCandidate());
	}

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $config;
        $this->getOutput()->setPageTitle("My Mailing Lists");
        $person = Person::newFromWgUser();
        if(isset($_POST['unsub'])){
            foreach($_POST['unsub'] as $unsub){
                $ret = MailingList::manuallyUnsubscribe($unsub, $person);
                if(!MailingList::isSubscribed($unsub, $person) && 
                    MailingList::hasUnsubbed($unsub, $person)){
                    $wgMessage->addSuccess("You have been unsubscribed from '$unsub'");
                }
            }
            redirect("$wgServer$wgScriptPath/index.php/Special:MyMailingLists");
            exit;
        }
        $publicLists = array();
        if($person->isRoleAtLeast(MANAGER)){
            $lists = MailingList::listLists();
        }
        else if($person->isRoleAtLeast(STAFF)){
            $lists = array_diff(MailingList::listLists(), array("support", strtolower($config->getValue('networkName'))."-support"));
        }
        else{
            $lists = MailingList::getPersonLists($person);
            
        }
        foreach(MailingList::getPublicLists() as $list){
            if(array_search($list, $lists) === false){
                $publicLists[] = $list;
            }
        }
        $wgOut->addHTML("<form method='POST'><table class='mailTable' frame='box' rules='all'><thead>
                            <tr><th>List Name</th><th># Threads</th><th><span class='tooltip' title='Unsubscribing will remove you from the selected list(s) and will prevent you from being added to that list in the future'>Unsubscribe?</span></th></tr>
                         </thead><tbody>");
        foreach($lists as $list){
            $threads = MailingList::getThreads($list);
            $wgOut->addHTML("<tr><td><span style='display:none;'>0</span><a href='mailto:$list@{$config->getValue('domain')}'>$list</a><a style='float:right;' href='$wgServer$wgScriptPath/index.php/Mail:$list'>View Archives</a></td><td align='right'>".count($threads)."</td><td align='center'><input type='checkbox' name='unsub[]' value='{$list}' /></td></tr>\n");
        }
        foreach($publicLists as $list){
            $wgOut->addHTML("<tr><td><a href='mailto:$list@{$config->getValue('domain')}'>$list</a></td><td align='right'></td><td align='center'></td></tr>\n");
        }
        $wgOut->addHTML("</tbody></table><br />
            <input type='submit' value='Submit' />
        </form>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('.mailTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});
        </script>");
    }
    
    static function createTab(&$tabs){
        global $config, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($wgUser->isLoggedIn() && !$me->isCandidate()){
            if($config->getValue('networkName') == "BD" && !$me->isRoleAtLeast(STAFF)){
                return true;
            }
            $selected = "";
            if($wgTitle->getNSText() == "Mail" || 
               ($wgTitle->getNSText() == "Special" && $wgTitle->getText() == "MyMailingLists")){
                $selected = "selected";
            }
            $tabs["MailingLists"] = TabUtils::createTab("My Mailing Lists", "$wgServer$wgScriptPath/index.php/Special:MyMailingLists", $selected);
        }
        return true;
    }
}
?>
