<?php

require_once("InactiveUsers.php");
if($config->getValue("networkName") == "FES"){
    require_once("FESPeopleTable.php");
}
autoload_register('IndexTables');

$wgHooks['OutputPageParserOutput'][] = 'IndexTable::generateTable';
$wgHooks['userCan'][] = 'IndexTable::userCanExecute';
$wgHooks['SubLevelTabs'][] = 'IndexTable::createSubTabs';

class IndexTable {

    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $config, $wgTitle, $wgRoles, $wgAllRoles;
        $me = Person::newFromWgUser();
        if($config->getValue('guestLockdown') && !$me->isLoggedIn()){
            return true;
        }
        $aliases = $config->getValue('roleAliases');
        
        $themesColl = new Collection(Theme::getAllThemes());
        $themeAcronyms = $themesColl->pluck('getAcronym()');
        $themeNames = $themesColl->pluck('getName()');
        $themes = array();
        foreach($themeAcronyms as $id => $acronym){
            $themes[] = $themeAcronyms[$id].' - '.$themeNames[$id];
        }
        
        if(Project::areThereAdminProjects()){
            $project = Project::newFromHistoricName($wgTitle->getNSText());
            $selected = ((($project != null && $project->getType() == 'Administrative') || strstr($wgTitle->getText(), "AdminProjects") !== false)) ? "selected" : "";
            $adminTab = TabUtils::createSubTab(Inflect::pluralize($config->getValue('adminProjects')), 
                                                                "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:AdminProjects", 
                                                                "$selected");
            if(PROJECT_PHASE > 1){
                $phaseDates = $config->getValue('projectPhaseDates');
                for($phase = PROJECT_PHASE; $phase > 0; $phase--){
                    $rome = rome($phase);
                    $adminTab['dropdown'][] = TabUtils::createSubTab(substr($phaseDates[$phase], 0, 4)."-".substr($phaseDates[$phase+1], 0, 4), 
                                                                     "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:AdminProjects {$rome}", "$selected");
                }
            }
            $tabs['Main']['subtabs'][] = $adminTab;
        }
        
        if(Project::areThereInnovationHubs()){
            $proj = Project::newFromHistoricName($wgTitle->getNSText());
            $hubsSubTab = TabUtils::createSubTab("Innovation Hubs");
            $projects = Project::getAllProjects();
            foreach($projects as $project){
                if($project->getType() == "Innovation Hub"){
                    $selected = ($proj!= null && $proj->getId() == $project->getId()) ? "selected" : "";
                    $hubsSubTab['dropdown'][] = TabUtils::createSubTab($project->getName(), "{$project->getUrl()}", "$selected");
                }
            }
        }
        $tabs['Main']['subtabs'][] = $hubsSubTab;
        if(count($themes) > 0){
            $selected = (($wgTitle->getNSText() == $config->getValue('networkName') && 
                         (strstr($wgTitle->getText(), Inflect::pluralize($config->getValue('projectThemes'))) !== false)) ||
                         (array_search($wgTitle->getNSText(), $themeAcronyms) !== false)) ? "selected" : "";
            $themeTab = TabUtils::createSubTab(Inflect::pluralize($config->getValue('projectThemes')), 
                                                                  "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:".Inflect::pluralize($config->getValue('projectThemes')), 
                                                                  "$selected");
            if(PROJECT_PHASE > 1){
                for($phase = 1; $phase <= PROJECT_PHASE; $phase++){
                    $phaseNames = $config->getValue("projectPhaseNames");
                    $rome = rome($phase);
                    $themeTab['dropdown'][$phaseNames[$phase]] = TabUtils::createSubTab(Inflect::pluralize($phaseNames[$phase]), 
                                                                 "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:".Inflect::pluralize($config->getValue('projectThemes'))." {$rome}", "$selected");
                }
                ksort($themeTab['dropdown']);
            }
            $tabs['Main']['subtabs'][] = $themeTab;
        }
        
        if($config->getValue('projectsEnabled') && Project::areThereNonAdminProjects()){
            $project = Project::newFromHistoricName(str_replace("_", " ", $wgTitle->getNSText()));
            $selected = ((($project != null && $project->getType() != "Administrative" && $project->getType() != "Innovation Hub") || $wgTitle->getText() == "Projects" || $wgTitle->getText() == "CompletedProjects" || $wgTitle->getText() == "ProposedProjects") && 
                         !($me->isMemberOf($project) || $me->isThemeLeaderOf($project) || $me->isThemeCoordinatorOf($project) || ($project != null && $me->isMemberOf($project->getParent())))) ? "selected" : "";
            $projectTab = TabUtils::createSubTab("Projects", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects", "$selected");
            $projectTab['dropdown'][] = TabUtils::createSubTab("Current", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects", $selected);
            if(Project::areThereDeletedProjects()){
                $projectTab['dropdown'][] = TabUtils::createSubTab("Completed", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:CompletedProjects", $selected);
            }
            if(Project::areThereProposedProjects() && $me->isRoleAtLeast(STAFF)){
                $projectTab['dropdown'][] = TabUtils::createSubTab("Proposed", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ProposedProjects", $selected);
            }
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
        
        $peopleSubTab = TabUtils::createSubTab("People");
        $roles = array_values($wgAllRoles);
        $roles[] = NI;
        sort($roles);
        $roles = array_filter(array_unique($roles));
        foreach($roles as $role){
            if(($role != HQP || $me->isLoggedIn()) && !isset($aliases[$role]) && count(Person::getAllPeople($role, true))){
                $selected = ($lastRole === NI || $wgTitle->getText() == "ALL {$role}" || ($wgTitle->getNSText() == $role && !($me->isRole($role) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
                $peopleSubTab['dropdown'][] = TabUtils::createSubTab(str_replace("Member", "Members", $role), "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_{$role}", "$selected");
            }
        }
        
        if($me->isRoleAtLeast(STAFF)){
            if(count(DBFunctions::select(array('mw_user'),
                                         array('user_id'),
                                         array('candidate' => 1,
                                               'deleted' => 0))) > 0){
                $selected = ($wgTitle->getText() == "ALL Candidates") ? "selected" : "";
                $peopleSubTab['dropdown'][] = TabUtils::createSubTab("Candidates", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Candidates", "$selected");
            }
            if(NI != null){
                $selected = ($wgTitle->getText() == "ALL Manager ".NI) ? "selected" : "";
                $tabs['Manager']['subtabs'][] = TabUtils::createSubTab(NI, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Manager_".NI, "$selected");
            }
        }
        
        if($config->getValue('projectsEnabled')){
            $tabs['Main']['subtabs'][] = $projectTab;
        }
        $tabs['Main']['subtabs'][] = $peopleSubTab;
        
        $selected = ($wgTitle->getText() == "Products" || 
                     $wgTitle->getText() == "Multimedia" ||
                     $wgTitle->getText() == "BibliographyPage" ||
                     $wgTitle->getNsText() == "Multimedia") ? "selected" : "";
        $productsSubTab = TabUtils::createSubTab(Inflect::pluralize($config->getValue("productsTerm")));
        $structure = Product::structure();
        $categories = array_keys($structure['categories']);
        foreach($categories as $category){
            if(Product::countByCategory($category) > 0){
                $productsSubTab['dropdown'][] = TabUtils::createSubTab(Inflect::pluralize($category), "$wgServer$wgScriptPath/index.php/Special:Products#/{$category}", "$selected");
            }
        }
        if(Material::countByCategory() > 0){
            $productsSubTab['dropdown'][] = TabUtils::createSubTab("Multimedia", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Multimedia", "$selected");
        }
        if(Bibliography::count() > 0){
            $productsSubTab['dropdown'][] = TabUtils::createSubTab("Bibliographies", "$wgServer$wgScriptPath/index.php/Special:BibliographyPage", "$selected");
        }
        $tabs['Main']['subtabs'][] = $productsSubTab;

        return true;
    }

    static function userCanExecute(&$title, &$user, $action, &$result){
        global $wgOut, $wgServer, $wgScriptPath, $config;
        if($config->getValue('guestLockdown') && !$user->isLoggedIn()){
            $result = false;
            return true;
        }
        if($title->getNSText() == "{$config->getValue('networkName')}"){
            $me = Person::newFromUser($user);
            $text = $title->getText();
            switch ($title->getText()) {
                case 'ALL '.HQP:
                    $result = ($me->isLoggedIn() || $config->getValue('hqpIsPublic'));
                    break;
                case 'Multimedia':
                    $result = $me->isLoggedIn();
                    break;
                case 'Forms':
                    $result = $me->isRoleAtLeast(MANAGER);
                    break;
            }
        }
        return true;
    }

    static function generateTable($out, $parseroutput){
        global $wgTitle, $wgOut, $wgUser, $config, $wgRoles, $wgAllRoles;
        $me = Person::newFromWgUser();
        if($wgTitle != null && str_replace("_", " ", $wgTitle->getNsText()) == "{$config->getValue('networkName')}" && !$wgOut->isDisabled()){
            $result = true;
            self::userCanExecute($wgTitle, $wgUser, "read", $result);
            if(!$result){
                $wgOut->loginToUse();
                $wgOut->output();
                $wgOut->disable();
                return true;
            }
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('.indexTable').css('display', 'table');
                    $('.dataTables_filter').css('float', 'none');
                    $('.dataTables_filter').css('text-align', 'left');
                    $('.dataTables_filter input').css('width', 250);
                });
            </script>");
            $phaseNames = $config->getValue("projectPhaseNames");
            switch ($wgTitle->getText()) {
                case 'Multimedia':
                    $wgOut->setPageTitle("Multimedia");
                    self::generateMaterialsTable();
                    break;
                case 'Forms':
                    if($me->isRoleAtLeast(MANAGER)){
                        $wgOut->setPageTitle("Forms");
                        self::generateFormsTable();
                    }
                    break;
                case 'Projects':
                    $wgOut->setPageTitle("Current Projects");
                    self::generateProjectsTable('Active', 'Research');
                    break;
                case 'CompletedProjects':
                    $wgOut->setPageTitle("Completed Projects");
                    self::generateProjectsTable('Ended', 'Research');
                    break;
                case 'ProposedProjects':
                    $wgOut->setPageTitle("Proposed Projects");
                    self::generateProjectsTable('Proposed', 'Research');
                    break;
                case 'AdminProjects':
                    $wgOut->setPageTitle(Inflect::pluralize($config->getValue('adminProjects')));
                    self::generateAdminTable();
                    break;
                case 'AdminProjects I':
                    $wgOut->setPageTitle(Inflect::pluralize($config->getValue('adminProjects')));
                    self::generateAdminTable(1);
                    break;
                case 'AdminProjects II':
                    $wgOut->setPageTitle(Inflect::pluralize($config->getValue('adminProjects')));
                    self::generateAdminTable(2);
                    break;
                case 'AdminProjects III':
                    $wgOut->setPageTitle(Inflect::pluralize($config->getValue('adminProjects')));
                    self::generateAdminTable(3);
                    break;
                case Inflect::pluralize($config->getValue('projectThemes')):
                case Inflect::pluralize($config->getValue('projectThemes'))." I":
                    // Phase 1
                    $wgOut->setPageTitle(Inflect::pluralize($phaseNames[1]));
                    self::generateThemesTable(1);
                    break;
                case Inflect::pluralize($config->getValue('projectThemes'))." II":
                    // Phase 2
                    $wgOut->setPageTitle(Inflect::pluralize($phaseNames[2]));
                    self::generateThemesTable(2);
                    break;
                case Inflect::pluralize($config->getValue('projectThemes'))." III":
                    // Phase 3 (unlikly to have more than that)
                    $wgOut->setPageTitle(Inflect::pluralize($phaseNames[3]));
                    self::generateThemesTable(3);
                    break;
                default:
                    foreach($wgAllRoles as $role){
                        if(($role != HQP || $me->isLoggedIn()) && $wgTitle->getText() == "ALL {$role}"){//Here we can get role
                            self::generatePersonTable($role);
                            break;
                        }
                    }
                    if($wgTitle->getText() == "ALL Manager ".NI && $me->isRoleAtLeast(STAFF)){
                        self::generateNITable();
                    }
                    if($wgTitle->getText() == "ALL ".NI){
                        $wgOut->setPageTitle("");
                        self::generatePersonTable(NI);
                    }
                    if($wgTitle->getText() == "ALL Candidates" && $me->isRoleAtLeast(STAFF)){
                        self::generatePersonTable("Candidate");
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
     * Generates the Table for the projects
     * Consists of the following columns
     * Identifier | Name 
     */
    private static function generateProjectsTable($status, $type="Research"){
        global $wgScriptPath, $wgServer, $wgOut, $wgUser, $config;
        $me = Person::newFromId($wgUser->getId());
        $themesHeader = "";
        $datesHeader = "";
        $idHeader = "";
        if($type != "Administrative"){
            $themesHeader = "<th>{$config->getValue('projectThemes')}</th>";
        }
        if($me->isLoggedIn()){
            $datesHeader = "<th style='white-space:nowrap;'>Start Date</th>
                            <th style='white-space:nowrap;'>End Date</th>";
        }
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space:nowrap;'>Project Id</th>";
        }
        $data = Project::getAllProjectsEver(($status != "Active"));
        $wgOut->addHTML("
            <table class='indexTable' style='display:none;' frame='box' rules='all'>
            <thead>
            <tr><th>Identifier</th><th>Name</th><th>Leaders</th>{$themesHeader}{$datesHeader}{$idHeader}</tr></thead><tbody>");
        foreach($data as $proj){
            if($proj->getStatus() == $status && ($proj->getType() == $type || $type == 'all')){
                $subProjects = array();
                if($status == "Active"){
                    // Only show sub-projects after the main when on the 'Current' tab
                    foreach($proj->getSubProjects() as $sub){
                        $subProjects[] = "<a href='{$sub->getUrl()}'>{$sub->getName()}</a>";
                    }
                }
                $subProjects = (count($subProjects) > 0) ? " (".implode(", ", $subProjects).")" : "";
                $wgOut->addHTML("
                    <tr>
                    <td align='left'><a href='{$proj->getUrl()}'>{$proj->getName()}</a> {$subProjects}</td>
                    <td align='left'>{$proj->getFullName()}</td>");
                $leaders = array();
                if(!$proj->isDeleted()){
                    foreach($proj->getAllPeople(PL) as $leader){
                        $leaders[] = "<a href='{$leader->getUrl()}'>{$leader->getNameForForms()}</a>";
                    }
                }
                else{
                    foreach($proj->getAllPeopleOn(PL, $proj->getEffectiveDate()) as $leader){
                        $leaders[] = "<a href='{$leader->getUrl()}'>{$leader->getNameForForms()}</a>";
                    }
                }
                $wgOut->addHTML("<td>".implode(", ", $leaders)."</td>");
                if($type != "Administrative"){
                    $challenges = $proj->getChallenges();
                    $text = array();
                    foreach($challenges as $challenge){
                        $text[] = ($challenge->getAcronym() != "") ? "<a href='{$challenge->getUrl()}'>{$challenge->getName()} ({$challenge->getAcronym()})</a>" : "";
                    }
                    $wgOut->addHTML("<td align='left'>".implode(", ", $text)."</td>");
                }
                if($datesHeader){
                    $wgOut->addHTML("<td align='center' style='white-space:nowrap;'>".substr($proj->getStartDate(), 0, 10)."</td>
                                     <td align='center' style='white-space:nowrap;'>".substr($proj->getEndDate(), 0, 10)."</td>");
                }
                if($idHeader){
                    $wgOut->addHTML("<td align='center'>{$proj->getId()}</td>\n");
                }
                $wgOut->addHTML("</tr>\n");
            }
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>$('.indexTable').dataTable({
                                                                            'iDisplayLength': 100, 
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

    /**
     * Generates the Table for the themes
     * Consists of the following columns
     * Theme | Name 
     */
    private static function generateThemesTable($phase=1){
        global $wgScriptPath, $wgServer, $config, $wgOut;
        $wgOut->addHTML(
"<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>Identifier</th><th>Name</th><th>Leaders</th><th>Coordinators</th></tr></thead><tbody>
");
        $themes = Theme::getAllThemes($phase);
        foreach($themes as $theme){
            $leaders = array();
            $coordinators = array();
            $leads = $theme->getLeaders();
            $coords = $theme->getCoordinators();
            foreach($leads as $lead){
                $leaders[] = "<a href='{$lead->getUrl()}'>{$lead->getNameForForms()}</a>";
            }
            foreach($coords as $coord){
                $coordinators[] = "<a href='{$coord->getUrl()}'>{$coord->getNameForForms()}</a>";
            }
            $leadersString = implode(", ", $leaders);
            $coordsString = implode(", ", $coordinators);
            $wgOut->addHTML("<tr>
                                <td align='left'>
                                    <a href='{$theme->getUrl()}'>{$theme->getAcronym()}</a>
                                </td><td align='left'>
                                    {$theme->getName()}
                                </td><td>{$leadersString}</td>
                                <td>{$coordsString}</td>
                            </tr>");
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>$('.indexTable').dataTable({
                                                                            'iDisplayLength': 100, 
                                                                            'autoWidth': false,
                                                                            'dom': 'Blfrtip',
                                                                            'buttons': [
                                                                                'excel', 'pdf'
                                                                            ]
                                                                         });</script>");
        return true;
    }

    /**
     * Generates the Table of Admin Projects
     */
    private static function generateAdminTable($phase=1){
        global $wgScriptPath, $wgServer, $config, $wgOut;
        $me = Person::newFromWgUser();
        $activityPlans = "";
        if($config->getValue('networkName') == 'AGE-WELL' && ($me->isRole(PL) || $me->isRoleAtLeast(STAFF))){
            $activityPlans = "<th>Activity Plans</th>";
        }
        $wgOut->addHTML("<table class='indexTable' style='display:none;' frame='box' rules='all'>
                            <thead><tr><th>{$config->getValue('adminProjects')}</th><th>Name</th><th>Leaders</th>{$activityPlans}</tr></thead><tbody>");
        $adminProjects = Project::getAllProjects();
        foreach($adminProjects as $project){
            if($project->getType() == 'Administrative' && $project->getPhase() == $phase){
                $leaders = array();
                foreach($project->getLeaders() as $lead){
                    $leaders[] = "<a href='{$lead->getUrl()}'>{$lead->getNameForForms()}</a>";
                }
                $leaderString = implode(", ", $leaders);
                $wgOut->addHTML("<tr>
                                    <td><a href='{$project->getUrl()}'>{$project->getName()}<a></td>
                                    <td>{$project->getFullName()}</td>
                                    <td>{$leaderString}</td>");
                if($config->getValue('networkName') == 'AGE-WELL' && ($me->isRole(PL) || $me->isRoleAtLeast(STAFF))){
                    $wgOut->addHTML("<td>");
                    $projs = array();
                    $projects = array();
                    foreach($me->leadership() as $p){
                        $projects[$p->getName()] = $p;
                    }
                    foreach($me->getThemeProjects() as $p){
                        $projects[$p->getName()] = $p;
                    }
                    foreach($projects as $proj){
                        if($proj->getType() != 'Administrative'){
                            $projs[] = "<a href='$wgServer$wgScriptPath/index.php/Special:Report?report=CCPlanning&project={$proj->getName()}&section={$project->getName()}'>{$proj->getName()}</a>";
                        }
                    }
                    if($me->isRole(PL, $project) || $me->isRoleAtLeast(STAFF)){
                        $report = "";
                        switch($project->getName()){
                            case "CC1 K-MOB":
                                $report = "CC1Leader";
                                break;
                            case "CC2 TECH-TRANS":
                                $report = "CC2Leader";
                                break;
                            case "CC3 T-WORK":
                                $report = "CC3Leader";
                                break;
                            case "CC4 TRAIN":
                                $report = "CC4Leader";
                                break;
                        }
                        $projs[] = "<a href='$wgServer$wgScriptPath/index.php/Special:Report?report={$report}&project={$project->getName()}'>Feedback</a>";
                    }
                    $wgOut->addHTML(implode(", ", $projs));
                    $wgOut->addHTML("</td>");
                }
                $wgOut->addHTML("</tr>");
            }
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>$('.indexTable').dataTable({
                                                                            'iDisplayLength': 100, 
                                                                            'autoWidth': false,
                                                                            'dom': 'Blfrtip',
                                                                            'buttons': [
                                                                                'excel', 'pdf'
                                                                            ]
                                                                         });</script>");
        return true;
    }

    /**
     * Generates the Table for the Network Investigators, Collaborating
     * Researchers, or Highly-Qualified People, depending on parameter
     * table.
     */
    private static function generatePersonTable($table){
        global $config, $wgOut;
        $me = Person::newFromWgUser();
        $tabbedPage = new TabbedPage("people");
        $visibility = true;
        header("HTTP/1.0: 200");
        $tabbedPage->addTab(new PeopleTableTab($table, $visibility, false));
        if($table != "Candidate"){
            $tabbedPage->addTab(new PeopleTableTab($table, $visibility, true));
            if($me->isRoleAtLeast(STAFF)){
                $phaseDates = $config->getValue('projectPhaseDates');
                for($y=date('Y', time() - 60*60*24*30*4); $y>=substr($phaseDates[1],0,4); $y--){
                    $tabbedPage->addTab(new PeopleTableTab($table, $visibility, $y));
                }
            }
            if($config->getValue('wikiEnabled') && ($me->isRole($table) || $me->isRoleAtLeast(ADMIN))){
                $tabbedPage->addTab(new PeopleWikiTab($table, $visibility));
            }
        }
        $tabbedPage->showPage();
        $wgOut->addHTML("<script type='text/javascript'>
            $('.custom-title').hide();
        </script>");
        foreach($tabbedPage->tabs as $key => $tab){
            if(@$_GET['tab'] == $tab->id || (@$_GET['tab'] == "" && $key == 0)){
                $wgOut->addHTML("<script type='text/javascript'>
                    {$tab->tabSelect()}
                </script>");
                break;
            }
        }
        return true;
    }
    
    /**
     * Generates the Table for the Network Investigators
     */
    private static function generateNITable(){
        global $config;
        $me = Person::newFromWgUser();
        $tabbedPage = new TabbedPage("people");
        $visibility = true;
        $tabbedPage->addTab(new NITableTab($visibility, false));
        $tabbedPage->showPage();
        return true;
    }

    static function generateMaterialsTable(){
        global $wgServer, $wgScriptPath, $wgOut;
        $wgOut->addHTML("<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>Date</th><th style='min-width:300px;'>Title</th><th>Type</th><th>People</th><th>Projects</th></tr></thead><tbody>");
        $materials = Material::getAllMaterials();
        foreach($materials as $material){
            $wgOut->addHTML("<tr><td>{$material->getDate()}</td><td><a href='{$material->getUrl()}'>{$material->getTitle()}</a></td><td>{$material->getHumanReadableType()}</td>");
            $projs = array();
            foreach($material->getProjects() as $project){
                $projs[] = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
            }
            $personLinks = array();
            foreach($material->getPeople() as $person){
                if($person->getType() != ""){
                    $personLinks[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
                }
                else{
                    $personLinks[] = "{$person->getName()}";
                }
            }
            $wgOut->addHTML("   <td>".implode(", ", $personLinks)."</td>
                                <td>".implode(", ", $projs)."</td>
                            </tr>");
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>$('.indexTable').dataTable({
                                                                            'iDisplayLength': 100, 
                                                                            'autoWidth': false,
                                                                            'dom': 'Blfrtip',
                                                                            'buttons': [
                                                                                'excel', 'pdf'
                                                                            ]
                                                                         });</script>");
        return true;
    }

    static function generateFormsTable(){
        global $wgServer, $wgScriptPath, $wgOut;
        $wgOut->addHTML("<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>Date</th><th style='min-width:300px;'>Title</th><th>Person</th><th>Institution</th><th>Project</th></tr></thead><tbody>");
        $forms = Form::getAllForms();
        foreach($forms as $form){
            $personName = "";
            $person = $form->getPerson();
            if($person != null && $person->getName() != ""){
                $personName = "<a href='{$person->getType()}'>{$person->getReversedName()}</a>";
            }

            $university = $form->getUniversity();

            $projectName = "";
            $project = $form->getProject();
            if($project != null && $project->getName() != ""){
                $projectName = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
            }
            $wgOut->addHTML("<tr><td>{$form->getDate()}</td><td><a href='$wgServer$wgScriptPath/index.php/Form:{$form->getId()}'>{$form->getTitle()}</a></td><td>{$personName}</td><td>{$university}</td><td>{$projectName}</td>");
            
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>$('.indexTable').dataTable({
                                                                            'iDisplayLength': 100, 
                                                                            'autoWidth': false,
                                                                            'dom': 'Blfrtip',
                                                                            'buttons': [
                                                                                'excel', 'pdf'
                                                                            ]
                                                                         });</script>");
        return true;
    }
}

?>
