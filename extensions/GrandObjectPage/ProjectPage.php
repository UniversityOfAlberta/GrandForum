<?php
require_once('ProjectPage/ProjectVisualisationsTab.php');
autoload_register('GrandObjectPage/ProjectPage');

$projectPage = new ProjectPage();
$wgHooks['ArticleViewHeader'][] = array($projectPage, 'processPage');
$wgHooks['SkinTemplateTabs'][] = array($projectPage, 'showTabs');

class ProjectPage {

    function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgTitle, $wgUser, $wgRoles, $wgServer, $wgScriptPath;
        
        $me = Person::newFromId($wgUser->getId());
        if(!$wgOut->isDisabled()){
            $name = str_replace("_Talk", "", $article->getTitle()->getNsText());
            $title = $article->getTitle()->getText();
            $project = Project::newFromHistoricName($name);
            
            $wgOut->addScript("<script type='text/javascript'>
                function stripAlphaChars(id){
                    var str = $('#' + id).val();
                    var out = new String(str); 
                    out = out.replace(/[^0-9]/g, ''); 
                    if(out > 100){
                        out = 100;
                    }
                    else if(out < 0){
                        out = 0;
                    }
                    $('#' + id).attr('value', out);
                }
            </script>");
            
            
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
                else if($project != null && !$me->isMemberOf($project) && !$me->isRoleAtLeast(STAFF)){
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
                if($me->isRoleAtLeast(STAFF)){
                    $isLead = true;
                }
                if(!$isLead){
                    $isLead = $me->leadershipOf($project->getName());
                    $parent = $project->getParent();
                    if($parent != null){
                        $isLead = ($isLead || $me->leadershipOf($parent));
                    }
                }
            }
            
            $isMember = $me->isMemberOf($project);
            
            //Adding support for GET['edit']
            if(isset($_GET['edit'])){
                $_POST['edit'] = true;
                $_POST['submit'] = "Edit Main";
            }

            $isLead = ( $isLead && (!FROZEN || $me->isRoleAtLeast(STAFF)) );
            $isMember = ($isMember && (!FROZEN || $me->isRoleAtLeast(STAFF)) );
            $edit = (isset($_POST['edit']) && $isLead);
            
            // Project Exists and it is the right Namespace
            if($project != null && $project->getName() != null){
                TabUtils::clearActions();
                $wgOut->clearHTML();
                $wgOut->setPageTitle($project->getFullName()." (Phase ".$project->getPhase().")");
                
                $visibility = array();
                if(!$project->isDeleted()){
                    $visibility['edit'] = $edit;
                    $visibility['isLead'] = $isLead;
                    $visibility['isMember'] = $isMember;
                }
                else{
                    $visibility['edit'] = false;
                    $visibility['isLead'] = false;
                    $visibility['isMember'] = false;
                }
                
                $tabbedPage = new TabbedPage("project");
                $tabbedPage->addTab(new ProjectMainTab($project, $visibility));
                if(!$project->isSubProject() && $project->getPhase() > 1){
                    $tabbedPage->addTab(new ProjectSubprojectsTab($project, $visibility));
                }
                if($project->getPhase() == 1){
                    $tabbedPage->addTab(new ProjectMilestonesTab($project, $visibility));
                }
                $tabbedPage->addTab(new ProjectDashboardTab($project, $visibility));
                if(!$project->isSubProject()){
                    $tabbedPage->addTab(new ProjectBudgetTab($project, $visibility));
                }
                $tabbedPage->addTab(new ProjectVisualisationsTab($project, $visibility));
                $tabbedPage->addTab(new ProjectWikiTab($project, $visibility));
                $tabbedPage->showPage();
                
                $wgOut->output();
                $wgOut->disable();
            }
        }
        return true;
    }
    
    // Adds the tabs for the user's projects
    function showTabs($skin, &$content_actions){
        global $wgServer, $wgScriptPath, $wgArticle, $wgUser, $wgRoles, $wgOut;
        if($wgArticle != null){
            $name = $wgArticle->getTitle()->getNsText();
            $title = $wgArticle->getTitle()->getText();
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
            $me = Person::newFromId($wgUser->getId());
            $project = Project::newFromHistoricName(str_replace("_Talk", "", $name));
            if($me->isMemberOf($project) || 
               ($project != null && $me->isMemberOf($project->getParent()))){
                foreach($me->getProjects() as $proj){
                    if($proj->isSubProject()){
                        continue;
                    }
                    if(str_replace("_Talk", "", $name) != $proj->getName()){
                        $class = false;
                    }
                    else{
                        $class = "selected";
                    }
                    $dropdown = null;
                    $title = "{$proj->getName()} (Phase{$proj->getPhase()})";
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
        }
        return true;
    }
}
?>
