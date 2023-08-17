<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ServiceRoles'] = 'ServiceRoles'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ServiceRoles'] = $dir . 'ServiceRoles.i18n.php';
$wgSpecialPageGroups['ServiceRoles'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'ServiceRoles::createSubTabs';

class ServiceRoles extends SpecialPage {
    
    function ServiceRoles(){
        parent::__construct("ServiceRoles", null, true);
    }
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return ($me->isRole(DEAN) || $me->isRole(DEANEA) || $me->isRole(VDEAN) || $me->isRoleAtLeast(STAFF));
    }
    
    function execute($par){
        global $wgOut;
        $wgOut->addHTML("<table id='serviceRoles' class='wikitable'>
                            <thead>
                                <tr>
                                    <th>Person</th>
                                    <th>Dept</th>
                                    <th>Role</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                </tr>
                            </thead>
                            <tbody>");
        foreach(Person::getAllServiceRoles() as $service){
            $person = Person::newFromId($service['user_id']);
            $wgOut->addHTML("<tr>
                                <td><a href='{$person->getUrl()}'>{$person->getReversedName()}</a></td>
                                <td>{$service['dept']}</td>
                                <td>{$service['role']}</td>
                                <td>{$service['start']}</td>
                                <td>{$service['end']}</td>
                            </tr>");
        }
        $wgOut->addHTML("    </tbody>
                        </table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#serviceRoles').DataTable({
                'order': [[ 3, 'desc' ], [4, 'desc']],
                'autoWidth': false,
                'iDisplayLength': -1
            });
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ServiceRoles") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Service Roles", "$wgServer$wgScriptPath/index.php/Special:ServiceRoles", $selected);
        }
        return true;
    }
}

?>
