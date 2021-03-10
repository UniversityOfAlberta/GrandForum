<?php

autoload_register('GrandObjectPage/ThemePage');

$wgHooks['ArticleViewHeader'][] = 'ThemePage::processPage';

class ThemePage {

    function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgTitle, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $config;
        
        $me = Person::newFromId($wgUser->getId());
        if(!$wgOut->isDisabled()){
            $name = ($article != null) ? str_replace("_Talk", "", $article->getTitle()->getNsText()) : "";
            $name = str_replace("_", " ", $name);
            $title = ($article != null) ? $article->getTitle()->getText() : "";

            $theme = Theme::newFromName($name);
            
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
                if($theme != null && $theme->getId() != 0 &&
                   !$theme->userCanEdit()){
                    TabUtils::clearActions();
                    $wgOut->clearHTML();
                    $wgOut->permissionRequired('');
                    $wgOut->output();
                    exit;
                }
                return true;
            }

            // Project Exists and it is the right Namespace
            if($theme != null && $theme->getAcronym() != null){
                if($config->getValue('guestLockdown') && !$wgUser->isLoggedIn()){
                    permissionError();
                }
                $isLead = $theme->userCanEdit();
                
                //Adding support for GET['edit']
                if(isset($_GET['edit'])){
                    $_POST['edit'] = true;
                    $_POST['submit'] = "Edit Main";
                }

                $edit = (isset($_POST['edit']) && $isLead);
                TabUtils::clearActions();
                $wgOut->clearHTML();
                $wgOut->setPageTitle("{$theme->getName()} ({$theme->getAcronym()})");
                
                $visibility = array();
                
                $visibility['edit'] = $edit;
                $visibility['isLead'] = $isLead;
                
                $tabbedPage = new TabbedPage("theme");
                $tabbedPage->addTab(new ThemeMainTab($theme, $visibility));
                if(!$me->isSubRole('NOBUDGET')){
                    $tabbedPage->addTab(new ThemeBudgetTab($theme, $visibility));
                }
                if($isLead){
                    $tabbedPage->addTab(new ThemeDashboardTab($theme, $visibility));
                }
                /*$tabbedPage->addTab(new ProjectDescriptionTab($project, $visibility));
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
                if($config->getValue('wikiEnabled')){
                    $tabbedPage->addTab(new ProjectWikiTab($project, $visibility));
                }
                */
                $tabbedPage->showPage();
                
                $wgOut->output();
                $wgOut->disable();
            }
        }
        return true;
    }
}
?>
