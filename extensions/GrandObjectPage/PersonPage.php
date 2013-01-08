<?php

require_once('PersonPage/PersonVisualisationsTab.php');
autoload_register('GrandObjectPage/PersonPage');

$personPage = new PersonPage();
$wgHooks['ArticleViewHeader'][] = array($personPage, 'processPage');
$wgHooks['SkinTemplateTabs'][] = array($personPage, 'addTabs');

class PersonPage {

    function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgTitle, $wgRoleValues;
        $me = Person::newFromId($wgUser->getId());
        $nsText = str_replace("_", " ", $article->getTitle()->getNsText());
        if(!isset($wgRoleValues[$nsText])){
            // Namespace is not a role namespace
            return true;
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
                                                           $role == PL || $role == 'PL' ||
                                                           $role == COPL || $role == 'COPL' ||
                                                           $role == PM || $role == 'PM') && 
               $person->getName() != null && 
               $person != null && $person->isRole($role)){
                TabUtils::clearActions();
                $supervisors = $person->getSupervisors();
                
                $isMe = ($person->getId() == $me->getId() ||
                        $me->isRoleAtLeast(MANAGER));
                $isSupervisor = false;
                foreach($supervisors as $supervisor){
                    if($supervisor->getName() == $me->getName()){
                        $isSupervisor = true;
                        break;
                    }
                }
                $isChampion = $person->isRole(CHAMP);
                if($isChampion){
                    $isSupervisor = true;
                }
                $isSupervisor = ( $isSupervisor || (!FROZEN && $me->isRoleAtLeast(MANAGER)) );
                $isMe = ( $isMe && (!FROZEN || $me->isRoleAtLeast(MANAGER)) );
                $edit = (isset($_GET['edit']) && ($isMe || $isSupervisor || $isChampion));
                $edit = ( $edit && (!FROZEN || $me->isRoleAtLeast(MANAGER)) );
                
                $post = ((isset($_POST['submit']) && $_POST['submit'] == "Save Profile"));
                $post = ( $post && (!FROZEN || $me->isRoleAtLeast(MANAGER)) );
                $wgOut->clearHTML();
                
                /*
                 * Start the PersonPage
                 */
                
                $visibility = array();
                $visibility['edit'] = $edit;
                $visibility['isMe'] = $isMe;
                $visibility['isSupervisor'] = $isSupervisor;
                $visibility['isChampion'] = $isChampion;
                
                $this->showTitle($person, $visibility);

                $tabbedPage = new TabbedPage("person");
                $tabbedPage->addTab(new PersonContactTab($person, $visibility));
                $tabbedPage->addTab(new PersonProfileTab($person, $visibility));
                $tabbedPage->addTab(new PersonProjectTab($person, $visibility));
                $tabbedPage->addTab(new PersonRelationsTab($person, $visibility));
                $tabbedPage->addTab(new PersonDashboardTab($person, $visibility));
                if($person->isRoleAtLeast(CNI) && !$person->isRole(AR)){
                    $tabbedPage->addTab(new PersonBudgetTab($person, $visibility));
                }
                if($wgUser->isLoggedIn() && $person->isRole(INACTIVE) && $person->isRoleDuring(HQP, '0000-00-00 00:00:00', '2030-00-00 00:00:00')){
                    $tabbedPage->addTab(new HQPExitTab($person, $visibility));
                }
                $tabbedPage->addTab(new PersonAcknowledgementTab($person, $visibility));
                $tabbedPage->addTab(new PersonVisualisationsTab($person, $visibility));
                $tabbedPage->showPage();

                $this->showTitle($person, $visibility);
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
            else if(array_search($role, $wgRoles) !== false && stripos($wgTitle->getText(), "Mail") !== 0){
                // User does not exist
                TabUtils::clearActions();
                $wgOut->clearHTML();
                $wgOut->setPageTitle("User Does Not Exist");
                $wgOut->addHTML("There is no user '$role:$name'");
                $wgOut->output();
                $wgOut->disable();
            }
        }
        return true;
    }
    
    /*
     * Displays the title for this person
     */
    function showTitle($person, $visibility){
        global $wgOut;
        $roles = $person->getRoles();
        $roleNames = array();
        foreach($roles as $role){
            $roleNames[] = $role->getRole();
        }
        $pm = $person->isProjectManager();
        if($person->isProjectLeader() && !$pm){
            $roleNames[] = "PL";
        }
        if($person->isProjectCoLeader() && !$pm){
            $roleNames[] = "COPL";
        }
        if($pm){
            $roleNames[] = "PM";
        }
        foreach($roleNames as $key => $role){
            if($role == "Inactive"){
                if($person->isProjectManager() || $person->isProjectLeader() || $person->isProjectCoLeader()){
                    unset($roleNames[$key]);
                    continue;
                }
                $lastRole = $person->getLastRole();
                if($lastRole != null){
                    $roleNames[$key] = "Inactive-".$lastRole->getRole();
                }
            }
        }
        $wgOut->setPageTitle($person->getReversedName()." (".implode(", ", $roleNames).")");
    }
    
    function addTabs($skin, &$content_actions){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromId($wgUser->getId());
        if($me->isRole($wgTitle->getNSText()) && $me->getName() == $wgTitle->getText()){
            $content_actions = array();
            $content_actions[] = array('text' => $me->getNameForForms(),
                                       'class' => 'selected',
                                       'href' => "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}"
                                    
            );
        }
        return true;
    }
}
?>
