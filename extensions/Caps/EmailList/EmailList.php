<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EmailList'] = 'EmailList'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EmailList'] = $dir . 'EmailList.i18n.php';
$wgSpecialPageGroups['EmailList'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'EmailList::createToolboxLinks';

class EmailList extends SpecialPage{

    function EmailList() {
        parent::__construct("EmailList", STAFF.'+', true);
    }

    function execute($par){
        global $wgOut;
        
        $people = Person::getAllCandidates();
        $wgOut->addHTML("<table id='people' rules='all' frame='box'>
            <thead>
                <tr>
                    <th>Registration Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Postal Code</th>
                    <th>Province</th>
                    <th>Language</th>
                    <th>Role</th>
                    <th>Certified</th>
                </tr>
            </thead>
            <tbody>");
        foreach($people as $person){
            $certified = (!$person->isCandidate()) ? "Yes" : "No";
            $wgOut->addHTML("<tr>
                    <td>".time2date($person->getRegistration(), "Y-m-d H:i:s")."</td>
                    <td>{$person->getNameForForms()}</td>
                    <td>{$person->getEmail()}</td>
                    <td>{$person->getPostalCode()}</td>
                    <td>{$person->getProvinceFromPostalCode()}</td>
                    <td>{$person->getUser()->getOption('language')}</td>
                    <td>{$person->getType()}</td>
                    <td>{$certified}</td>
                </tr>");
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $(document).ready(function(){
                $('#people').DataTable({
                    'aLengthMenu': [[-1], ['All']]
                });
            });
        </script>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgLang;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            $toolbox['People']['links'][3] = TabUtils::createToolboxLink("Email List", "$wgServer$wgScriptPath/index.php/Special:EmailList");
        }
        return true;
    }

}

?>
