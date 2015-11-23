<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['HQPRegisterTable'] = 'HQPRegisterTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['HQPRegisterTable'] = $dir . 'HQPRegisterTable.i18n.php';
$wgSpecialPageGroups['HQPRegisterTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'HQPRegisterTable::createSubTabs';

function runHQPRegisterTable($par) {
    HQPRegisterTable::execute($par);
}

class HQPRegisterTable extends SpecialPage{

    function HQPRegisterTable() {
        SpecialPage::__construct("HQPRegisterTable", null, false, 'runHQPRegisterTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HQPAC));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        HQPRegisterTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $candidates = Person::getAllCandidates(HQP);
        $wgOut->addHTML("<table id='hqpRegisterTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th width='1%'>First&nbsp;Name</th>
                    <th width='1%'>Last&nbsp;Name</th>
                    <th width='1%'>Email</th>
                    <th>Registration Date</th>
                    <th>University</th>
                    <th>Level</th>
                </tr>
            </thead>
            <tbody>");
        foreach($candidates as $candidate){
            $wgOut->addHTML("<tr>");
            $candidate->getName();
            $wgOut->addHTML("<td align='right'>{$candidate->getFirstName()}</td>");
            $wgOut->addHTML("<td>{$candidate->getLastName()}</td>");
            $wgOut->addHTML("<td><a href='mailto:{$candidate->getEmail()}'>{$candidate->getEmail()}</a></td>");
            $wgOut->addHTML("<td>".time2date($candidate->getRegistration(), 'Y-m-d')."</td>");
            $wgOut->addHTML("<td>{$candidate->getUni()}</td>");
            $wgOut->addHTML("<td>{$candidate->getPosition()}</td>");
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</tbody></table>");
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('#hqpRegisterTable').dataTable({'iDisplayLength': 100});
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "HQPRegisterTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("HQP Registration Table", "$wgServer$wgScriptPath/index.php/Special:HQPRegisterTable", $selected);
        }
        return true;
    }

}

?>
