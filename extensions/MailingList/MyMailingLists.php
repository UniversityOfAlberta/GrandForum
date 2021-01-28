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
        SpecialPage::__construct("MyMailingLists", HQP.'+', true, 'runMyMailingLists');
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $config;
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
        if($person->isRoleAtLeast(MANAGER)){
            $lists = MailingList::listLists();
        }
        else if($person->isRoleAtLeast(STAFF)){
            $lists = array_diff(MailingList::listLists(), array("support", strtolower($config->getValue('networkName'))."-support"));
        }
        else{
            $lists = MailingList::getPersonLists($person);
        }
        $wgOut->addHTML("<form method='POST'><table class='mailTable' frame='box' rules='all'><thead>
                            <tr><th>List Name</th><th># Threads</th><th><span class='tooltip' title='Unsubscribing will remove you from the selected list(s) and will prevent you from being added to that list in the future'>Unsubscribe?</span></th></tr>
                         </thead><tbody>");
        foreach($lists as $list){
            $threads = MailingList::getThreads($list);
            $wgOut->addHTML("<tr><td><a href='mailto:$list@{$config->getValue('domain')}'>$list</a><a style='float:right;' href='$wgServer$wgScriptPath/index.php/Mail:$list'>View Archives</a></td><td align='right'>".count($threads)."</td><td align='center'><input type='checkbox' name='unsub[]' value='{$list}' /></td></tr>\n");
        }
        $wgOut->addHTML("</tbody></table><br />
            <input type='submit' value='Submit' />
        </form>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('.mailTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});
        </script>");
    }
    
    static function createTab(&$tabs){
        global $wgUser, $wgTitle, $wgServer, $wgScriptPath, $wgLang;
        $me = Person::newFromWgUser();
        if($wgUser->isLoggedIn() && $me->isRoleAtLeast(HQP)){
            $selected = "";
            if($wgTitle->getNSText() == "Mail" || 
               ($wgTitle->getNSText() == "Special" && $wgTitle->getText() == "MyMailingLists")){
                $selected = "selected";
            }
                $title = "My Mailing Lists";
                if($wgLang->getCode() == "fr"){
                    $title ="Mes Listes de Diffusion";
                }
            $tabs["MailingLists"] = TabUtils::createTab($title, "$wgServer$wgScriptPath/index.php/Special:MyMailingLists", $selected);
        }
        return true;
    }
}
?>
