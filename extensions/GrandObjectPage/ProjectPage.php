<?php

autoload_register('GrandObjectPage/ProjectPage');

UnknownAction::createAction('ProjectVisualizationsTab::getProjectTimelineData');
UnknownAction::createAction('ProjectVisualizationsTab::getProjectDoughnutData');
UnknownAction::createAction('ProjectVisualizationsTab::getProjectChordData');
UnknownAction::createAction('ProjectVisualizationsTab::getProjectWordleData');

$wgHooks['ArticleViewHeader'][] = 'ProjectPage::processPage';
$wgHooks['TopLevelTabs'][] = 'ProjectPage::createTab';
$wgHooks['SubLevelTabs'][] = 'ProjectPage::createSubTabs';

class ProjectPage {

    static function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgTitle, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromId($wgUser->getId());
        if(!$wgOut->isDisabled()){
            $name = ($article != null) ? str_replace("_Talk", "", $article->getTitle()->getNsText()) : "";
            $name = str_replace("_", " ", $name);
            $title = ($article != null) ? $article->getTitle()->getText() : "";

            $project = Project::newFromHistoricName($name);
            
            if($name == ""){
                $split = explode(":", $name);
                if(count($split) > 1){
                    $title = $split[1];
                }
                else{
                    $title = "";
                }
                $name = $split[0];
            }
            if($title != "Main"){
                if($wgTitle->getText() == "Mail Index"){
                    TabUtils::clearActions();
                }
                else if($project != null && 
                        $project->getType() != 'Administrative' &&
                        !$me->isMemberOf($project) && 
                        !$me->isRoleAtLeast(STAFF) && 
                        !$me->isThemeLeaderOf($project) && 
                        !$me->isThemeCoordinatorOf($project) &&
                        !$me->isRole("CF") && 
                        !($project->isSubProject() && ($me->isThemeLeaderOf($project->getParent()) || 
                                                       $me->isThemeCoordinatorOf($project->getParent())))){
                    TabUtils::clearActions();
                    permissionError();
                    exit;
                }
                return true;
            }

            // Project Exists and it is the right Namespace
            if($project != null && $project->getName() != null){
                if($config->getValue('guestLockdown') && !$wgUser->isLoggedIn()){
                    permissionError();
                }
                $isLead = false;
                if($project != null){
                    $isLead = $project->userCanEdit();
                }
                
                $isMember = ($project != null && ($me->isMemberOf($project) || $project->getType() == 'Administrative'));
                
                //Adding support for GET['edit']
                if(isset($_GET['edit'])){
                    $_POST['edit'] = true;
                    $_POST['submit'] = "Edit Main";
                }

                $edit = (isset($_POST['edit']) && $isLead);
                TabUtils::clearActions();
                $wgOut->clearHTML();
                $wgOut->setPageTitle("{$project->getFullName()} ({$project->getName()})");
                
                $visibility = array();
                if(!$project->isDeleted()){
                    $visibility['edit'] = $edit;
                    $visibility['isLead'] = $isLead;
                    $visibility['isMember'] = $isMember;
                }
                else{
                    $visibility['edit'] = false;
                    $visibility['isLead'] = $isLead;
                    $visibility['isMember'] = false;
                }
                
                $tabbedPage = new TabbedPage("project");
                $tabbedPage->singleHeader = false;
                $tabbedPage->addTab(new ProjectMainTab($project, $visibility));
                if($config->getValue('projectLongDescription')){
                    if($config->getValue('networkName') == "FES"){
                        $tabbedPage->addTab(new ProjectFESDescriptionTab($project, $visibility));
                    }
                    else{
                        $tabbedPage->addTab(new ProjectDescriptionTab($project, $visibility));
                    }
                }
                /*if(!$project->isSubProject() && $project->getPhase() > 1 && $project->getStatus() != 'Proposed'){
                    $tabbedPage->addTab(new ProjectSubprojectsTab($project, $visibility));
                }*/
                if($config->getValue('networkType') == "CFREF"){
                    $tabbedPage->addTab(new ProjectFESMilestonesTab($project, $visibility));
                }
                else if($config->getValue('networkName') != "CIC" && (strstr($project->getName(), "GIS-") === false)){
                    $tabbedPage->addTab(new ProjectMilestonesTab($project, $visibility));
                }
                if($project->getStatus() != 'Proposed'){
                    $tabbedPage->addTab(new ProjectDashboardTab($project, $visibility));
                }
                if($project->getType() != 'Administrative' && !$me->isSubRole('NOBUDGET') && $config->getValue('networkName') != "CIC" && (strstr($project->getName(), "GIS-") === false)){
                    $tabbedPage->addTab(new ProjectBudgetTab($project, $visibility));
                }
                if(strstr($project->getName(), "GIS-") !== false){
                    $tabbedPage->addTab(new ProjectKPI2Tab($project, $visibility));
                    $tabbedPage->addTab(new ProjectKPITab($project, $visibility));
                    //$tabbedPage->addTab(new ProjectKPISummaryTab($project, $visibility));
                }
                if($project->getStatus() != 'Proposed' && $project->getType() != 'Administrative'){
                    $tabbedPage->addTab(new ProjectVisualizationsTab($project, $visibility));
                }
                if($config->getValue('wikiEnabled')){
                    $tabbedPage->addTab(new ProjectWikiTab($project, $visibility));
                }
                if($config->getValue('networkName') == "GlycoNet" && (strstr($project->getName(), "GIS-") === false)){
                    $tabbedPage->addTab(new ProjectReportsTab($project, $visibility));
                }
                if($visibility['isLead'] && isExtensionEnabled('Reporting')){
                    $tabbedPage->addTab(new ProjectSummaryTab($project, $visibility));
                }
                if($config->getValue('networkType') == "CFREF"){
                    $tabbedPage->addTab(new ProjectFESProjectionsTab($project, $visibility));
                    if($config->getValue('networkName') == "FES"){
                        $tabbedPage->addTab(new ProjectEdiTab($project, $visibility));
                    }
                    $tabbedPage->addTab(new ProjectFESReportTab($project, $visibility));
                }
                $tabbedPage->showPage();
                
                if(!$edit){
                    $allProjects = array_values(Project::getAllProjects());
                    $prev = null;
                    $next = null;
                    foreach($allProjects as $key => $p){
                        if($p->getId() == $project->getId()){
                            $prev = (isset($allProjects[$key-1])) ? $allProjects[$key-1] : $allProjects[count($allProjects)-1];
                            $next = (isset($allProjects[$key+1])) ? $allProjects[$key+1] : $allProjects[0];
                        }
                    }
                    if($prev != null && $next != null){
                        $wgOut->addHTML("<a href='{$prev->getUrl()}' class='button' style='width:35px;'>Prev</a>&nbsp;<a href='{$next->getUrl()}' class='button' style='width:35px;'>Next</a>");
                    }
                }
                $wgOut->output();
                $wgOut->disable();
                exit;
            }
        }
        return true;
    }
    
    static function createTab(&$tabs){
        global $config;
        if($config->getValue('projectsEnabled')){
            $tabs["Projects"] = TabUtils::createTab("My Projects");
        }
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        if($config->getValue('projectsEnabled')){
            $me = Person::newFromWgUser();
            $myProjects = array();
            foreach($me->getProjects() as $proj){
                $myProjects[$proj->getName()] = $proj;
            }
            foreach($me->getThemeProjects() as $proj){
                $myProjects[$proj->getName()] = $proj;
            }
            //$projects = array_merge($projects, $me->getThemeProjects());
            if(!$wgUser->isLoggedIn() || count($myProjects) == 0 || $me->isRoleAtLeast(MANAGER)){
                return true;
            }

            foreach($myProjects as $key => $project){
                if($project->isSubProject() || $project->getStatus() != "Active"){
                    unset($myProjects[$key]);
                }
            }
            $projects = array_values($myProjects);
            usort($projects, function($a, $b){
                return strnatcmp($a->getName(), $b->getName());
            });
            foreach($projects as $project){
                $selected = (str_replace("_", " ", $wgTitle->getNSText()) == $project->getName()) ? "selected" : "";
                $subtab = TabUtils::createSubTab($project->getName(), $project->getUrl(), $selected);
                $subprojects = $project->getSubProjects();
                if(count($subprojects) > 0){
                    $subtab['dropdown'][] = TabUtils::createSubTab($project->getName(), $project->getUrl(), $selected);
                    foreach($project->getSubProjects() as $subProject){
                        $subtab['dropdown'][] = TabUtils::createSubTab($subProject->getName(), $subProject->getUrl(), $selected);
                    }
                }
                $tabs["Projects"]['subtabs'][] = $subtab;
            }
        }
        return true;
    }
}
?>
