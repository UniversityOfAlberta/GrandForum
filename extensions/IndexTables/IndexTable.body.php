<?php

$wgHooks['OutputPageParserOutput'][] = 'IndexTable::generateTable';
$wgHooks['userCan'][] = 'IndexTable::userCanExecute';
$wgHooks['SubLevelTabs'][] = 'IndexTable::createSubTabs';

class IndexTable {
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $config, $wgTitle, $wgRoles, $wgAllRoles;
        $me = Person::newFromWgUser();
        if(!$me->isRoleAtLeast(STAFF)){
            return true;
        }
        $lastRole = "";
        if($wgTitle->getNSText() == INACTIVE && !($me->isRole(INACTIVE) && $wgTitle->getText() == $me->getName())){
            $person = Person::newFromName($wgTitle->getText());
            if($person != null & $person->getName() != null && $person->isRole(INACTIVE)){
                $roles = $person->getRoles(true);
                $lastRole = "";
                for($i = count($roles) - 1; $i >= 0; $i--){
                    $role = $roles[$i];
                    if($role->getRole() != INACTIVE){
                        $lastRole = $role->getRole();
                        break;
                    }
                }
            }
        }
        
        $roles = array_values($wgAllRoles);
        if(count($roles) == 1){
              $role = $roles[0];
              $dbRole = DBFunctions::execSQL("SELECT role FROM `grand_roles` r, mw_user u WHERE r.user_id = u.user_id AND role = '$role' AND u.deleted = 0 LIMIT 1");
              if(($role != HQP || $me->isLoggedIn()) && count($dbRole) > 0){
                    $selected = ($lastRole == NI || $wgTitle->getText() == "ALL {$role}" || ($wgTitle->getNSText() == $role &&
                    !($me->isRole($role) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
                    $peopleSubTab = TabUtils::createSubTab('People', "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_{$role}", "$selected");
              }
        }
        elseif(count($roles)>1){
            sort($roles);
            $peopleSubTab = TabUtils::createSubTab('People', "", "");
            foreach($roles as $role){
                $dbRole = DBFunctions::execSQL("SELECT role FROM `grand_roles` r, mw_user u WHERE r.user_id = u.user_id AND role = '$role' AND u.deleted = 0 LIMIT 1");
                if(($role != HQP || $me->isLoggedIn()) && count($dbRole) > 0){
                    $selected = ($lastRole == NI || $wgTitle->getText() == "ALL {$role}" || ($wgTitle->getNSText() == $role &&
                    !($me->isRole($role) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
                    if($role == AR){
                        $roleTitle = "Faculty";
                    }
                    else{
                        $roleTitle = Inflect::pluralize($role);
                    }
                    $peopleSubTab['dropdown'][] = TabUtils::createSubTab($roleTitle, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_{$role}", "$selected");
                }
            }
        }
        
        $tabs['Main']['subtabs'][] = $peopleSubTab;
        /*
        $selected = ($wgTitle->getText() == "Products") ? "selected" : "";
        $productsSubTab = TabUtils::createSubTab(Inflect::pluralize($config->getValue("productsTerm")));
        $structure = Product::structure();
        $categories = array_keys($structure['categories']);
        foreach($categories as $category){
            if(Product::countByCategory($category) > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab(Inflect::pluralize($category), "$wgServer$wgScriptPath/index.php/Special:Products#/{$category}", "$selected");
            }
        }
        $selected = ($wgTitle->getText() == "ALL Grants" && str_replace('_',' ',$wgTitle->getNSText()) == $config->getValue('networkName')) ? "selected" : "";
        $grantSubTab = TabUtils::createSubTab("Grants", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Grants", "$selected");
        if($wgUser->isLoggedIn()){
            //$tabs['Main']['subtabs'][] = $grantSubTab;
        }
        $selected = ($wgTitle->getText() == "ALL Courses" && str_replace('_',' ',$wgTitle->getNSText()) == $config->getValue('networkName')) ? "selected" : "";
        $grantSubTab = TabUtils::createSubTab("Courses", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Courses", "$selected");
        if($wgUser->isLoggedIn()){
            //$tabs['Main']['subtabs'][] = $grantSubTab;
        }
        */
        return true;
    }

    function userCanExecute(&$title, &$user, $action, &$result){
        global $wgOut, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromUser($user);
        $result = $me->isRoleAtLeast(STAFF);
        return true;
    }

    function generateTable($out, $parseroutput){
        global $wgTitle, $wgOut, $wgUser, $config, $wgRoles, $wgAllRoles;
        $me = Person::newFromWgUser();
        if($wgTitle != null && str_replace("_", " ", $wgTitle->getNsText()) == "{$config->getValue('networkName')}" && !$wgOut->isDisabled()){
            $result = true;
            self::userCanExecute($wgTitle, $wgUser, "read", $result);
            if(!$result){
                permissionError();
            }
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('.indexTable').css('display', 'table');
                    $('.dataTables_filter').css('float', 'none');
                    $('.dataTables_filter').css('text-align', 'left');
                    $('.dataTables_filter input').css('width', 250);
                });
            </script>");
            switch ($wgTitle->getText()) {
                case 'ALL Courses':
                    $wgOut->setPageTitle("Courses");
                    self::generateCoursesTable();
                default:
                    foreach($wgAllRoles as $role){
                        if(($role != HQP || $me->isLoggedIn()) && $wgTitle->getText() == "ALL {$role}"){
                            $wgOut->setPageTitle($config->getValue('roleDefs', $role));
                            self::generatePersonTable($role);
                        }
                    }
                    break;
            }
            TabUtils::clearActions();
            $wgOut->output();
            $wgOut->disable();
        }
        return true;
    }

    /**
     * Generates the Table for the Network Investigators, Collaborating
     * Researchers, or Highly-Qualified People, depending on parameter
     * #table.
     * Consists of the following columns
     * User Page | Twitter
     */
    private function generatePersonTable($table){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
        $me = Person::newFromId($wgUser->getId());
        $data = Person::getAllPeople($table);
        $data = Person::filterFaculty($data, true);
        $idHeader = "";
        $idsHeader = "";
        $contactHeader = "";
        $subRoleHeader = "";
        $ldapHeader = "";
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space: nowrap;'>User Id</th>
                         <th style='white-space: nowrap;'>Employee Id</th>";
        }
        if($me->isRoleAtLeast(DEAN)){
            $idsHeader = "<th style='white-space: nowrap;'>OpenAlex</th>
                          <th style='white-space: nowrap;'>Google Scholar</th>
                          <th style='white-space: nowrap;'>Sciverse</th>
                          <th style='white-space: nowrap;'>ORCID</th>
                          <th style='white-space: nowrap;'>WoS</th>";
        }
        if($me->isLoggedIn()){
            $contactHeader = "<th style='white-space: nowrap;'>Email</th>";
        }
        if($table == HQP){
            $subRoleHeader = "<th style='white-space: nowrap;'>Sub Roles</th>";
        }
        $ldapHeader = "<th style='white-space: nowrap; '>LDAP</th>";
        $wgOut->addHTML("<table class='indexTable' style='display:none;' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th style='white-space: nowrap;'>Name</th>
                                    {$subRoleHeader}
                                    <th style='white-space: nowrap;'>Department</th>
                                    <th style='white-space: nowrap;'>Title</th>
                                    {$ldapHeader}
                                    {$contactHeader}
                                    {$idHeader}
                                    {$idsHeader}</tr>
                                </thead>
                                <tbody>");
        foreach($data as $person){
            $wgOut->addHTML("<tr>
                <td align='left' style='white-space: nowrap;'>
                <a href='{$person->getUrl()}'>{$person->getReversedName()}</a>
                </td>");
            if($subRoleHeader != ""){
                $subRoles = $person->getSubRoles();
                $wgOut->addHTML("<td style='white-space:nowrap;' align='left'>".implode("<br />", $subRoles)."</td>");
            }
            $university = $person->getUniversity();
            $wgOut->addHTML("<td align='left'>{$university['department']}</td>");
            $wgOut->addHTML("<td align='left'>{$university['position']}</td>");
            $wgOut->addHTML("<td align='left'>");
            if($person->getLdap() != ""){
                $wgOut->addHTML("<a href='{$person->getLdap()}' target='_blank'>LDAP</a>");
            }
            $wgOut->addHTML("</td>");
            if($contactHeader != ''){
                $wgOut->addHTML("<td align='left'><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>");
            }
            if($idHeader != ''){
                $wgOut->addHTML("<td>{$person->getId()}</td>");
                $wgOut->addHTML("<td>{$person->getEmployeeId()}</td>");
            }
            if($idsHeader != ''){
                $wgOut->addHTML("<td>{$person->getAlexId()}</td>");
                $wgOut->addHTML("<td>{$person->getGoogleScholar()}</td>");
                $wgOut->addHTML("<td>{$person->getSciverseId()}</td>");
                $wgOut->addHTML("<td>{$person->getOrcId()}</td>");
                $wgOut->addHTML("<td>{$person->getWOS()}</td>");
            }
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({
                                                                            'aLengthMenu': [[100,-1], [100,'All']], 
                                                                            'iDisplayLength': -1, 
                                                                            'autoWidth': false,
                                                                            'dom': 'Blfrtip',
                                                                            columnDefs: [
                                                                               {type: 'natural', targets: 0}
                                                                            ],
                                                                            'buttons': [
                                                                                'excel', 'pdf'
                                                                            ]
                                                                         });</script>");

        return true;
    }
        
    private function generateCoursesTable(){
        global $wgUser,$wgOut;
        if(!$wgUser->isLoggedIn()){
            permissionError();
        }
        $wgOut->addHTML("<table class='indexTable' style='display:none;' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Title</th>
                        <th style='white-space:nowrap;'>Number</th>
                        <th style='white-space:nowrap;'>Catalog Description</th>
                        </tr></thead><tbody>");

        $courses = Course::getAllCourses();
        foreach($courses as $course){
            $wgOut->addHTML("<tr><td>$course->subject</td>
                                <td>$course->catalog</td>
                                <td>$course->courseDescr</td></tr>");
        }
        $wgOut->addHTML("</table></tbody><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength':100});</script>");
        return true;
    }
}

?>
