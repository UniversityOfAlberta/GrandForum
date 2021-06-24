<?php

autoload_register('GrandObjectPage/PersonPage');

$wgHooks['UnknownAction'][] = 'PersonProfileTab::getPersonCloudData';
$wgHooks['UnknownAction'][] = 'PersonVisualizationsTab::getTimelineData';
$wgHooks['UnknownAction'][] = 'PersonVisualizationsTab::getDoughnutData';
$wgHooks['UnknownAction'][] = 'PersonVisualizationsTab::getChordData';

$wgHooks['ArticleViewHeader'][] = 'PersonPage::processPage';
$wgHooks['userCan'][] = 'PersonPage::userCanExecute';
$wgHooks['SubLevelTabs'][] = 'PersonPage::createSubTabs';

class PersonPage {

    function userCanExecute(&$title, &$user, $action, &$result){
        global $config;
        $name = $title->getNSText();
        $referrer = @$_SERVER['HTTP_REFERER'];
        $cameFromWebsite = (strstr(@$_SERVER['HTTP_REFERER'], $config->getValue('networkSite')) !== false ||
                            strstr(@$_SERVER['HTTP_REFERER'], $config->getValue('domain')) !== false);
        if($config->getValue('guestLockdown') && !$cameFromWebsite && !$user->isLoggedIn()){
            $result = false;
            return true;
        }
        if($name == HQP){
            $result = $user->isLoggedIn() || $config->getValue('hqpIsPublic');
        }
        return true;
    }

    function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgTitle, $wgRoleValues, $config;
        
        $me = Person::newFromId($wgUser->getId());
        $nsText = ($article != null) ? str_replace("_", " ", $article->getTitle()->getNsText()) : "";
        if(!isset($wgRoleValues[$nsText])){
            // Namespace is not a role namespace
            return true;
        }
        if($article == null){
            return true;
        }
        $result = true;
        self::userCanExecute($wgTitle, $wgUser, "read", $result);
        if(!$result){
            permissionError();
        }
        if(!$wgOut->isDisabled()){
            $role = $nsText;
            $name = $article->getTitle()->getText();
            if($role == ""){
                $split = explode(":", $name);
                if(count($split) > 1){
                    $name = $split[1];
                }
                else{
                    $name = "";
                }
                $role = $split[0];
            }
            $person = Person::newFromName($name);
            if((array_search($role, $wgRoles) !== false || $role == INACTIVE || 
                                                           $role == PL || 
                                                           $role == TC || 
                                                           $role == TL) && 
               $person->getName() != null && 
               $person != null && ($person->isRole($role) || $person->isRole($role."-Candidate"))){
                TabUtils::clearActions();
                $supervisors = $person->getSupervisors(true);
                
                $isMe = ($person->isMe() ||
                        $me->isRoleAtLeast(STAFF));
                $isSupervisor = false;
                $isLeader = false;
                foreach($supervisors as $supervisor){
                    if($supervisor->getName() == $me->getName()){
                        $isSupervisor = true;
                        break;
                    }
                }
                $isChampion = $person->isRole(CHAMP);
                if($isChampion){
                    $creators = $person->getCreators();
                    foreach($creators as $creator){
                        if($creator->getId() == $me->getId()){
                            $isSupervisor = true;
                        }
                    }
                    foreach($person->getProjects() as $project){
                        if(($project->isSubProject() && $me->isRole(PL, $project->getParent())) || $me->isRole(PL, $project)){
                            $isSupervisor = true;
                        }
                    }
                }
                foreach($person->getProjects() as $project){
                    // Allow Project Assistants to edit
                    if($me->isRole(PA, $project) ||
                       $me->isRole(PS, $project)){
                        $isSupervisor = true;
                    }
                    if($person->isRole(HQP, $project) && $me->isRole(PL, $project)){
                        // Person is an HQP and $me is the leader of their project
                        $isLeader = true;
                    }
                }
                foreach($me->getThemeProjects() as $project){
                    if($person->isMemberOf($project)){
                        $isSupervisor = true;
                        break;
                    }
                }
                
                $edit = ((isset($_GET['edit']) || isset($_POST['edit'])) && ($isMe || $isSupervisor));
                $post = ((isset($_POST['submit']) && $_POST['submit'] == "Save Profile"));

                $wgOut->clearHTML();
                
                //Adding support for GET['edit']
                if(isset($_GET['edit'])){
                    $_POST['edit'] = true;
                    $_POST['submit'] = "Edit Main";
                }
                
                // Start the PersonPage
                $visibility = array();
                $visibility['edit'] = $edit;
                $visibility['isMe'] = $isMe;
                $visibility['isSupervisor'] = $isSupervisor;
                $visibility['isLeader'] = $isLeader;
                $visibility['isChampion'] = $isChampion;
                
                self::showTitle($person, $visibility);

                $tabbedPage = new TabbedPage("person");
                $tabbedPage->singleHeader = false;
                $tabbedPage->addTab(new PersonProfileTab($person, $visibility));
                if($config->getValue('networkName') != 'AI4Society'){
                    if($config->getValue('networkName') == 'ADA' || $config->getValue('networkName') == 'FES'){
                        $tabbedPage->addTab(new PersonDemographicsTab($person, $visibility));
                    }                
                    
                    if($config->getValue('networkName') == 'AGE-WELL' && ($person->isRole(HQP) || $person->isRole(HQP."-Candidate"))){
                        $tabbedPage->addTab(new HQPProfileTab($person, $visibility));
                    }
                    if($config->getValue('networkName') == 'AGE-WELL' && 
                       $person->isRoleDuring(HQP, '0000-00-00 00:00:00', '2100-00-00 00:00:00')){
                        if($person->isEpic2()){
                            $tabbedPage->addTab(new HQPEpicTab2($person, $visibility));
                        }
                        else{
                            $tabbedPage->addTab(new HQPEpicTab($person, $visibility));
                        }
                        $tabbedPage->addTab(new HQPDocsTab($person, $visibility));
                    }
                    if($wgUser->isLoggedIn() && $person->isRoleDuring(HQP, '0000-00-00 00:00:00', '2100-00-00 00:00:00')){
                        $tabbedPage->addTab(new HQPExitTab($person, $visibility));
                    }
                    if($config->getValue('projectsEnabled')){
                        $tabbedPage->addTab(new PersonProjectTab($person, $visibility));
                    }
                    $tabbedPage->addTab(new PersonRelationsTab($person, $visibility));
                    //$tabbedPage->addTab(new PersonProductsTab($person, $visibility));
                    $tabbedPage->addTab(new PersonDashboardTab($person, $visibility));
                    $tabbedPage->addTab(new PersonVisualizationsTab($person, $visibility));
                    $tabbedPage->addTab(new PersonDataQualityTab($person, $visibility));
                }
                if(isExtensionEnabled("UofANews")){
                    $tabbedPage->addTab(new PersonUofANewsTab($person, $visibility));
                }
                if($config->getValue('networkName') == 'AI4Society'){
                    $tabbedPage->addTab(new PersonPostersTab($person, $visibility));
                    $tabbedPage->addTab(new PersonMetricsTab($person, $visibility));
                }
                if($config->getValue('networkName') == 'GlycoNet'){
                    $tabbedPage->addTab(new PersonCertificatesTab($person, $visibility));
                }
                $tabbedPage->showPage();

                self::showTitle($person, $visibility);
                $wgOut->output();
                $wgOut->disable();
            }
            else if($person != null && 
                    $person->getName() != null && 
                    !$person->isRole($role)){
                TabUtils::clearActions();
                // User Exists, but it is probably the wrong Namespace
                $wgOut->clearHTML();
                $wgOut->setPageTitle("User Does Not Exist");
                $roles = $person->getRoles();
                $wgOut->addHTML("There is no user '$role:$name'");
                if(isset($roles[0])){
                    $wgOut->addHTML("<br />
                                    Did you mean <a href='$wgServer$wgScriptPath/index.php/{$roles[0]->getRole()}:{$person->getName()}'>{$roles[0]->getRole()}:{$person->getName()}</a>?");
                }
                $wgOut->output();
                $wgOut->disable();
            }
            else if(array_search($role, $wgRoles) !== false && $wgTitle->getText() != "Mail Index" && strstr($wgTitle->getText(), "MAIL ") === false){
                // User does not exist
                TabUtils::clearActions();
                $wgOut->clearHTML();
                $wgOut->setPageTitle("User Does Not Exist");
                $wgOut->addHTML("There is no user '$role:$name'");
                $wgOut->output();
                $wgOut->disable();
            }
            else if($wgTitle->getText() == "Mail Index"){
                TabUtils::clearActions();
            }
        }
        return true;
    }
    
    /**
     * Displays the title for this person
     */
    function showTitle($person, $visibility){
        global $wgOut;
        $wgOut->setPageTitle($person->getReversedName());
        $wgOut->addHTML("<script type='text/javascript'>
            $('.custom-title').hide();
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $selected = ($me->isRole($wgTitle->getNSText()) && $me->getName() == $wgTitle->getText()) ? "selected" : "";
            $tabs['Profile']['subtabs'][] = TabUtils::createSubTab($me->getNameForForms(), $me->getUrl(), $selected);
        }
        return true;
    }
}
?>
