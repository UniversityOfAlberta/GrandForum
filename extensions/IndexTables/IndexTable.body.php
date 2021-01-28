<?php

require_once("InactiveUsers.php");
require_once('PeopleWikiTab.php');
require_once('PeopleTableTab.php');

$indexTable = new IndexTable();

$wgHooks['OutputPageParserOutput'][] = 'IndexTable::externalRedirect';
$wgHooks['OutputPageParserOutput'][] = array($indexTable, 'generateTable');
$wgHooks['userCan'][] = array($indexTable, 'userCanExecute');

$wgHooks['SubLevelTabs'][] = 'IndexTable::createSubTabs';

class IndexTable {

    var $text = "";

    static function externalRedirect($out, $parseroutput){
        global $wgTitle, $wgServer;
        if($wgTitle->getNsText() == "File"){
            redirect($wgServer.wfLocalFile($wgTitle->getText())->getUrl() );
        }
        return true;
    }

    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgLang, $config, $wgTitle, $wgRoles, $wgAllRoles;
        $me = Person::newFromWgUser();
        $aliases = $config->getValue('roleAliases');
        if($config->getValue('projectsEnabled')){
            $project = Project::newFromHistoricName(str_replace("_", " ", $wgTitle->getNSText()));
            $selected = ((($project != null && $project->getType() != "Administrative") || $wgTitle->getText() == "Projects") && 
                         !($me->isMemberOf($project) || ($project != null && $me->isMemberOf($project->getParent())))) ? "selected" : "";
            $projectTab = TabUtils::createSubTab("Projects", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects", "$selected");
            if(Project::areThereDeletedProjects()){
                $projectTab['dropdown'][] = TabUtils::createSubTab("Current", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:Projects", $selected);
                $projectTab['dropdown'][] = TabUtils::createSubTab("Completed", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:CompletedProjects", $selected);
            }
            $tabs['Main']['subtabs'][] = $projectTab;
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
    if($me->isLoggedIn()){
        if($wgLang->getCode() == 'en'){
                $peopleSubTab = TabUtils::createSubTab("People");
        }
        else if($wgLang->getCode() == 'fr'){
            $peopleSubTab = TabUtils::createSubTab("Gens");
        }
        $roles = array_values($wgAllRoles);
        sort($roles);
        foreach($roles as $role){
            if(($role != HQP || $me->isLoggedIn()) && !isset($aliases[$role]) && count(Person::getAllPeople($role, true))){
                if($role == "Manager" || $role == "Expert" || $me->isRoleAtLeast(STAFF)){
                 $selected = ($lastRole == NI || $wgTitle->getText() == "ALL {$role}" || ($wgTitle->getNSText() == $role && !($me->isRole($role) && $wgTitle->getText() == $me->getName()))) ? "selected" : "";
                 $peopleSubTab['dropdown'][] = TabUtils::createSubTab($role, "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_{$role}", "$selected");
                }
            }
        }
        $tabs['Main']['subtabs'][] = $peopleSubTab;
    }
    if(isExtensionEnabled('Products')){
            $selected = ($wgTitle->getText() == "Products" || 
                         $wgTitle->getText() == "Multimedia" ||
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
            $tabs['Main']['subtabs'][] = $productsSubTab;
        }

        $selected = ($wgTitle->getText() == "ALL Stories" && str_replace('_',' ',$wgTitle->getNSText()) == $config->getValue('networkName')) ? "selected" : "";
        if($wgLang->getCode() == "en"){
                $storiesSubTab = TabUtils::createSubTab("All Cases", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Stories", "$selected");
        }
        else if($wgLang->getCode() == "fr"){
            $storiesSubTab = TabUtils::createSubTab("Tous Les Cas", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_Stories", "$selected");
        }
        if($wgUser->isLoggedIn() && $me->isRoleAtLeast(NI)){
            $tabs['Main']['subtabs'][] = $storiesSubTab;
        }
        $themesColl = new Collection(Theme::getAllThemes());
        $themeAcronyms = $themesColl->pluck('getAcronym()');
        $themeNames = $themesColl->pluck('getName()');
        $themes = array();
        foreach($themeAcronyms as $id => $acronym){
            $themes[] = $themeAcronyms[$id].' - '.$themeNames[$id];
        }
        
        if(Project::areThereAdminProjects()){
            $project = Project::newFromHistoricName($wgTitle->getNSText());
            $selected = ((($project != null && $project->getType() == 'Administrative') || $wgTitle->getText() == "AdminProjects")) ? "selected" : "";
            $tabs['Main']['subtabs'][] = TabUtils::createSubTab(Inflect::pluralize($config->getValue('adminProjects')), 
                                                                "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:AdminProjects", 
                                                                "$selected");
        }
        
        if(count($themes) > 0){
            $selected = ($wgTitle->getNSText() == $config->getValue('networkName') && 
                         ($wgTitle->getText() == Inflect::pluralize($config->getValue('projectThemes')) || 
                         array_search($wgTitle->getText(), $themes) !== false)) ? "selected" : "";
            
            $tabs['Main']['subtabs'][] = TabUtils::createSubTab(Inflect::pluralize($config->getValue('projectThemes')), 
                                                                "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:".Inflect::pluralize($config->getValue('projectThemes')), 
                                                                "$selected");
        }
        
        /*if(Wiki::newFromTitle("{$config->getValue('networkName')}_Conferences")->exists()){
            $selected = ($wgTitle->getNSText() == "Conference" || $wgTitle->getText() == "{$config->getValue('networkName')} Conferences") ? "selected" : "";
            $tabs['Main']['subtabs'][] = TabUtils::createSubTab("Conferences", "$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}_Conferences", "$selected");
        }*/


        return true;
    }

    function userCanExecute(&$title, &$user, $action, &$result){
        global $wgOut, $wgServer, $wgScriptPath, $config;
        if($title->getNSText() == "{$config->getValue('networkName')}"){
            $me = Person::newFromUser($user);
            $text = $title->getText();
            switch ($title->getText()) {
                case 'ALL '.HQP:
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

    function generateTable($out, $parseroutput){
        global $wgTitle, $wgOut, $wgUser, $config, $wgRoles, $wgAllRoles, $wgLang;
        $me = Person::newFromWgUser();
        if($wgTitle != null && str_replace("_", " ", $wgTitle->getNsText()) == "{$config->getValue('networkName')}" && !$wgOut->isDisabled()){
            $result = true;
            $this->userCanExecute($wgTitle, $wgUser, "read", $result);
            if(!$result || !$wgUser->isLoggedIn()){
                throw new PermissionsError('read');
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
                case 'Multimedia':
                    $wgOut->setPageTitle("Multimedia");
                    $this->generateMaterialsTable();
                    TabUtils::clearActions();
                    break;
                case 'Forms':
                    if($me->isRoleAtLeast(MANAGER)){
                        $wgOut->setPageTitle("Forms");
                        $this->generateFormsTable();
                                TabUtils::clearActions();
                    }
                    break;
                case 'Projects':
                    $wgOut->setPageTitle("Current Projects");
                    $this->generateProjectsTable('Active', 'Research');
                                TabUtils::clearActions();
                    break;
                case 'CompletedProjects':
                    $wgOut->setPageTitle("Completed Projects");
                    $this->generateProjectsTable('Ended', 'Research');
                                TabUtils::clearActions();
                    break;
                case 'AdminProjects':
                    $wgOut->setPageTitle(Inflect::pluralize($config->getValue('adminProjects')));
                    $this->generateAdminTable();
                                TabUtils::clearActions();
                    break;
                case Inflect::pluralize($config->getValue('projectThemes')):
                    $wgOut->setPageTitle(Inflect::pluralize($config->getValue('projectThemes')));
                    $this->generateThemesTable();
                                TabUtils::clearActions();
                    break;
                case 'ALL Stories':
                    $wgOut->setPageTitle("All Cases or Experiences");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Tous Les Cas");
                    }
                    if($me->isRoleAtLeast(NI)){
                        $this->generateUserStoriesTable();
                        TabUtils::clearActions();
                    }
                    break;
               case 'ALL Clinical':
                    $wgOut->setPageTitle("Clinical Guidelines");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Lignes directrices cliniques");
                    }
                    $this->generatePersonTable("Clinical");
                    break;

                case 'ALL Presentations':
                    $wgOut->setPageTitle("Presentations file");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Fichier de présentations");
                    }
                    $this->generatePersonTable("Presentations"); 
                    break;
                case 'ALL Surveys':
                    $wgOut->setPageTitle("Survey Instruments");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Fichier de présentations");
                    }
                    $this->generatePersonTable("Surveys"); 
                    break;
                case 'ALL Curricula':
                    $wgOut->setPageTitle("Curricula");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Fichier de présentations");
                    }
                    $this->generatePersonTable("Curricula"); 
                    break;
                case 'ALL Tools':
                    $wgOut->setPageTitle("Tools & Tips");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Outils et conseils");
                    }
                    $this->generatePersonTable("Tools");
                    break;
                case 'ALL Canadian':
                    $wgOut->setPageTitle("Canadian Resources");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Ressources Canadiennes");
                    }
                    $this->generatePersonTable("Canadian");
                    break;
                case 'ALL Nursing':
                    $wgOut->setPageTitle("Advanced Nursing Practice Resources");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Ressources de pratique infirmière avancée");
                    }
                    $this->generatePersonTable("Nursing");
                    break;
                case 'ALL Formulaires en français':
                    $wgOut->setPageTitle("Formulaires en français");
                    $this->generatePersonTable("Formulaires_en_français");
                    break;
                case 'ALL Organizations':
                    $wgOut->setPageTitle("Organizations");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Organizations");
                    }
                    $this->generatePersonTable("Organizations");
                    break;
                case 'ALL Articles':
                    $wgOut->setPageTitle("Articles");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Des Articles");
                    }
                    $this->generatePersonTable("Articles");
                    break;
                case 'ALL Patients':
                    $wgOut->setPageTitle("Patient Resources");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Les Ressources des Patients");
                    }
                    $this->generatePersonTable("Patients");
                    break;                    
                case 'ALL Resources':
                                $wgOut->setPageTitle("Resources");
                    if($wgLang->getCode() == 'fr'){
                        $wgOut->setPageTitle("Ressources");
                    }
                    $this->generatePersonTable("Resources");
                    break;
                default:
                    foreach($wgAllRoles as $role){
                        if(($role != HQP || $me->isLoggedIn()) && $wgTitle->getText() == "ALL {$role}"){
                            if($role == "Manager" || $role == "Expert" || $me->isRoleAtLeast(STAFF)){
                            $wgOut->setPageTitle($config->getValue('roleDefs', $role));
                                        $this->generatePersonTable($role);
                                            TabUtils::clearActions();
                            }
                        }
                    }
                    break;
            }
            $wgOut->addHTML($this->text);
            $wgOut->output();
            $wgOut->disable();
        }
        return true;
    }

    /**
     * Generates the Table for the projects
     * Consists of the following columns
     * Acronym | Name 
     */
    private function generateProjectsTable($status, $type="Research"){
        global $wgScriptPath, $wgServer, $wgOut, $wgUser, $config;
        $me = Person::newFromId($wgUser->getId());
        $themesHeader = "";
        $idHeader = "";
        if($type != "Administrative"){
            $themesHeader = "<th>{$config->getValue('projectThemes')}</th>";
        }
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th>Project Id</th>";
        }
        $data = Project::getAllProjectsEver();
        $this->text .= "
            <table class='indexTable' style='display:none;' frame='box' rules='all'>
            <thead>
            <tr><th>Acronym</th><th>Name</th>{$themesHeader}{$idHeader}</tr></thead><tbody>";
        foreach($data as $proj){
            if($proj->getStatus() == $status && ($proj->getType() == $type || $type == 'all')){
                $this->text .= "
                    <tr>
                    <td align='left' style='white-space: nowrap;'><a href='{$proj->getUrl()}'>{$proj->getName()}</a></td>
                    <td align='left'>{$proj->getFullName()}</td>";
                if($type != "Administrative"){
                    $this->text .= "<td align='center'>{$proj->getChallenge()->getAcronym()}</td>";
                }
                if($idHeader){
                    $this->text .= "<td>{$proj->getId()}</td>\n";
                }
                $this->text .= "</tr>\n";
            }
        }
        $this->text .= "</tbody></table>";
        $this->text .= "<script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});</script>";
        return true;
    }

    /**
     * Generates the Table for the themes
     * Consists of the following columns
     * Theme | Name 
     */
    private function generateThemesTable(){
        global $wgScriptPath, $wgServer, $config;
        $this->text .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                        <thead><tr><th>{$config->getValue('projectThemes')}</th><th>Name</th><th>Leaders</th><th>Coordinators</th></tr></thead><tbody>";
        $themes = Theme::getAllThemes(PROJECT_PHASE);
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
            $this->text .= <<<EOF
<tr>
<td align='left'>
<a href='{$theme->getUrl()}'>{$theme->getAcronym()}</a>
</td><td align='left'>
{$theme->getName()}
</td><td>{$leadersString}</td><td>{$coordsString}</td></tr>
EOF;
        }
        $this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});</script>";

        return true;
    }

    /**
     * Generates the Table of Admin Projects
     */
    private function generateAdminTable(){
        global $wgScriptPath, $wgServer, $config;
        $me = Person::newFromWgUser();
        $activityPlans = "";
        if($config->getValue('networkName') == 'AGE-WELL' && ($me->isProjectLeader() || $me->isRoleAtLeast(STAFF))){
            $activityPlans = "<th>Activity Plans</th>";
        }
        $this->text .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                        <thead><tr><th>{$config->getValue('adminProjects')}</th><th>Name</th><th>Leaders</th>{$activityPlans}</tr></thead><tbody>";
        $adminProjects = Project::getAllProjects();
        foreach($adminProjects as $project){
            if($project->getType() == 'Administrative'){
                $leaders = array();
                foreach($project->getLeaders() as $lead){
                    $leaders[] = "<a href='{$lead->getUrl()}'>{$lead->getNameForForms()}</a>";
                }
                $leaderString = implode(", ", $leaders);
                $this->text .= "<tr>";
                $this->text .= "<td><a href='$wgServer$wgScriptPath/index.php/{$project->getName()}:Information'>{$project->getName()}<a></td>";
                $this->text .= "<td>{$project->getFullName()}</td>";
                $this->text .= "<td>{$leaderString}</td>";
                if($config->getValue('networkName') == 'AGE-WELL' && ($me->isProjectLeader() || $me->isRoleAtLeast(STAFF))){
                    $this->text .= "<td>";
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
                    if($me->leadershipOf($project) || $me->isRoleAtLeast(STAFF)){
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
                    $this->text .= implode(", ", $projs);
                    $this->text .= "</td>";
                }
                $this->text .= "</tr>";
            }
        }
        $this->text .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});</script>";

        return true;
    }

    /**
     * Generates the Table for the Network Investigators, Collaborating
     * Researchers, or Highly-Qualified People, depending on parameter
     * #table.
     * Consists of the following columns
     * User Page | Projects | Twitter
     */
    private function generatePersonTable($table){
        global $wgAllRoles;
        $me = Person::newFromWgUser();
        if(($table == "Presentations" || $table == "Surveys" || $table == "Curricula") && 
           (!$me->isRoleAtLeast(STAFF) && !$me->isSubRole('Academic Faculty'))){
            permissionError();
        }
        $tabbedPage = new TabbedPage("people");
        $visibility = true;
        if(in_array($table,$wgAllRoles)){
            $tabbedPage->addTab(new PeopleTableTab($table, $visibility));
            if($me->isRole($table) || $me->isRoleAtLeast(ADMIN)){
                $tabbedPage->addTab(new PeopleWikiTab($table, $visibility));
            }
        }
        else{
            $tabbedPage->addTab(new PeopleWikiTab($table, $visibility));
        }
        $tabbedPage->showPage();
        return true;
    }

    function generateMaterialsTable(){
        global $wgServer, $wgScriptPath;
        $this->text = "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                       <thead><tr><th>Date</th><th style='min-width:300px;'>Title</th><th>Type</th><th>People</th><th>Projects</th></tr></thead><tbody>";
        $materials = Material::getAllMaterials();
        foreach($materials as $material){
            $this->text .= "<tr><td>{$material->getDate()}</td><td><a href='{$material->getUrl()}'>{$material->getTitle()}</a></td><td>{$material->getHumanReadableType()}</td>";
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
            $this->text .= "<td>".implode(", ", $personLinks)."</td>";
            $this->text .= "<td>".implode(", ", $projs)."</td>";
            $this->text .= "</tr>";
        }
        $this->text .= "</tbody></table>";
        $this->text .= "<script type='text/javascript'>
            $(document).ready(function(){
                $('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});
                $('.indexTable').dataTable().fnSort([[0,'desc']]);
            });
        </script>";
        return true;
    }

    function generateFormsTable(){
        global $wgServer, $wgScriptPath;
        $this->text = "<table class='indexTable' style='display:none;' frame='box' rules='all'>
<thead><tr><th>Date</th><th style='min-width:300px;'>Title</th><th>Person</th><th>University</th><th>Project</th></tr></thead><tbody>";
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
            $this->text .= "<tr><td>{$form->getDate()}</td><td><a href='$wgServer$wgScriptPath/index.php/Form:{$form->getId()}'>{$form->getTitle()}</a></td><td>{$personName}</td><td>{$university}</td><td>{$projectName}</td>";
            
            $this->text .= "</tr>";
        }
        $this->text .= "</tbody></table>";
        $this->text .= "<script type='text/javascript'>
            $(document).ready(function(){
                $('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth': false});
                $('.indexTable').dataTable().fnSort([[0,'desc']]);
            });
        </script>";
        return true;
    }

    private function generateUserStoriesTable(){
        global $wgUser,$wgOut, $config;
        if(!$wgUser->isLoggedIn()){
            permissionError();
        }
        $me = Person::newFromWgUser();
        $this->text .= "Below are all the current cases in {$config->getValue('networkName')}.  To search for a case in particular, use the search box below.  You can search by title, author or date submitted.<br /><br />";

        $this->text .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Title</th>";
        if($me->isRoleAtLeast(MANAGER)){
           $this->text .= "<th style='white-space:nowrap;'>Submitted By</th>";
        }
            $this->text .="<th style='white-space:nowrap;'>Date Submitted</th>
                        </tr></thead><tbody>";

        $stories = Story::getAllUserStories();
        foreach($stories as $story){
            if($story->getApproved()){
                $this->text .= "<tr><td align='left'><a href='".$story->getUrl()."'>".str_replace(">","&gt",str_replace("<","&lt;",$story->getTitle()))."</a></td>";
                if($me->isRoleAtLeast(MANAGER)){
                     $this->text .= "<td align='right'><a href='".$story->getUser()->getUrl()."'>".$story->getUser()->getNameForForms()."</a></td>";
                }
                $this->text .= "<td style='white-space: nowrap;'>".$story->getDateSubmitted()."</td></tr>";
            }
        }
        $this->text .= "</table></tbody><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength':100});</script>";
        return true;
    }

}

?>
