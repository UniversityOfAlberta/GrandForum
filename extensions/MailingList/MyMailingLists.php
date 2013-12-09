<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MyMailingLists'] = 'MyMailingLists'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MyMailingLists'] = $dir . 'MyMailingLists.i18n.php';
$wgSpecialPageGroups['MyMailingLists'] = 'other-tools';

function runMyMailingLists($par) {
  MyMailingLists::run($par);
}

class MyMailingLists extends SpecialPage{

    function MyMailingLists() {
        wfLoadExtensionMessages('MyMailingLists');
        SpecialPage::SpecialPage("MyMailingLists", HQP.'+', true, 'runMyMailingLists');
    }

    function run($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
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
        $lists = MailingList::getPersonLists($person);
        /*if($person->isProjectLeader() || $person->isProjectCoLeader()){
            $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:MailingListRequest'>Subscribe/Unsubscribe Users</a><br /><br />");
        }*/
        $wgOut->addHTML("<form method='POST'><table class='mailTable' frame='box' rules='all'><thead>
                            <tr><th>List Name</th><th># Threads</th><th><span class='tooltip' title='Unsubscribing will remove you from the selected list(s) and will prevent you from being added to that list in the future'>Unsubscribe?</span></th></tr>
                         </thead><tbody>");
        foreach($lists as $list){
            $threads = MailingList::getThreads($list);
            $wgOut->addHTML("<tr><td><a href='mailto:$list@forum.grand-nce.ca'>$list</a><a style='float:right;' href='$wgServer$wgScriptPath/index.php/Mail:$list'>View Archives</a></td><td align='right'>".count($threads)."</td><td align='center'><input type='checkbox' name='unsub[]' value='{$list}' /></td></tr>\n");
        }
        $wgOut->addHTML("</tbody></table><br />
            <input type='submit' value='Submit' />
        </form>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('.mailTable').dataTable({'iDisplayLength': 100});
        </script>");
    }
    
    static function createTab() {
        global $wgServer, $wgScriptPath, $wgTitle;
        $selected = "";
        if($wgTitle->getNSText() == "Mail" || 
           ($wgTitle->getNSText() == "Special" && $wgTitle->getText() == "MyMailingLists")){
            $selected = "selected";
        }
        echo <<<EOM
<li class='top-nav-element $selected'><span class='top-nav-left'>&nbsp;</span>
<a class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:MyMailingLists' class='new'>My Mailing Lists</a>
<span class='top-nav-right'>&nbsp;</span></li>
EOM;
    }
}
?>
