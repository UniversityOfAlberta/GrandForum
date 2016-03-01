<?php
require_once('ProjectPage/ProjectVisualizationsTab.php');
autoload_register('GrandObjectPage/ProjectPage');

$projectPage = new ProjectPage();
$wgHooks['ArticleViewHeader'][] = array($projectPage, 'processPage');

$wgHooks['TopLevelTabs'][] = 'ProjectPage::createTab';
$wgHooks['SubLevelTabs'][] = 'ProjectPage::createSubTabs';

class ProjectPage {

    function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgTitle, $wgUser, $wgRoles, $wgServer, $wgScriptPath;
        
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
                    $wgOut->clearHTML();
                    $wgOut->permissionRequired('');
                    $wgOut->output();
                    exit;
                }
                return true;
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

            $isLead = ($isLead && (!FROZEN || $me->isRoleAtLeast(STAFF)) );
            $isMember = ($isMember && (!FROZEN || $me->isRoleAtLeast(STAFF)) );

            $edit = (isset($_POST['edit']) && $isLead);
            
            // Project Exists and it is the right Namespace
            if($project != null && $project->getName() != null){
                TabUtils::clearActions();
                $wgOut->clearHTML();
                $wgOut->setPageTitle($project->getFullName());
                
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
                $tabbedPage->addTab(new ProjectMainTab($project, $visibility));
                $tabbedPage->addTab(new ProjectDescriptionTab($project, $visibility));
                if(!$project->isSubProject() && $project->getPhase() > 1 && $project->getStatus() != 'Proposed'){
                    $tabbedPage->addTab(new ProjectSubprojectsTab($project, $visibility));
                }
                $tabbedPage->addTab(new ProjectMilestonesTab($project, $visibility));
                if($project->getStatus() != 'Proposed'){
                    $tabbedPage->addTab(new ProjectDashboardTab($project, $visibility));
                }
                $tabbedPage->addTab(new ProjectBudgetTab($project, $visibility));
                if($project->getStatus() != 'Proposed' && $project->getType() != 'Administrative'){
                    $tabbedPage->addTab(new ProjectVisualizationsTab($project, $visibility));
                }
                $tabbedPage->addTab(new ProjectWikiTab($project, $visibility));
                $tabbedPage->showPage();
                
                $wgOut->output();
                $wgOut->disable();
            }
        }
        return true;
    }
 
    function addPhaseTabs($me, $phase, $name, &$content_actions){
        foreach($me->getProjects() as $proj){
            if($proj->isSubProject() || $proj->getPhase() != $phase){
                continue;
            }
            if(str_replace("_Talk", "", $name) != $proj->getName()){
                $class = false;
            }
            else{
                $class = "selected";
            }
            $dropdown = null;
            $title = "{$proj->getName()}";
            if(count($proj->getSubProjects()) > 0){
                $dropdown = array('name' => $proj->getName(), 
                                  'title' => $title, 
                                  'width' => 125);
            }
            $action = array (
                 'class' => "$class {$proj->getName()}",
                 'text'  => $title,
                 'href'  => "{$proj->getUrl()}"
            );
            
            if($dropdown != null){
                $action['dropdown'] = $dropdown;
            }
            
            $content_actions[] = $action;
            foreach($proj->getSubProjects() as $subproj){
                if(str_replace("_Talk", "", $name) != $subproj->getName()){
                    $class = false;
                }
                else{
                    $class = "selected";
                }
                $title = $subproj->getName();
                $content_actions[] = array (
                     'class' => "$class {$proj->getName()}",
                     'text'  => $title,
                     'href'  => "{$subproj->getUrl()}"
                );
            }
        }
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
            $projects = $me->getProjects();
            
            if(!$wgUser->isLoggedIn() || count($projects) == 0 || $me->isRoleAtLeast(MANAGER)){
		        return true;
		    }

            foreach($projects as $key => $project){
                if($project->isSubProject()){
                    unset($projects[$key]);
                }
            }
            $projects = array_values($projects);
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
