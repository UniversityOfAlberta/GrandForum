<?php

require_once('PersonPage/PersonProfileTab.php');
autoload_register('GrandObjectPage/PersonPage');

$personPage = new PersonPage();
$wgHooks['ArticleViewHeader'][] = array($personPage, 'processPage');
$wgHooks['userCan'][] = array($personPage, 'userCanExecute');

$wgHooks['SubLevelTabs'][] = 'PersonPage::createSubTabs';

class PersonPage {

    function userCanExecute(&$title, &$user, $action, &$result){
	global $config;
        $name = $title->getNSText();
        if($name == HQP){
            $result = $user->isLoggedIn() || $config->getValue('hqpIsPublic');
        }
        return true;
    }

    function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgTitle, $wgRoleValues, $config;
        $result = true;
        $this->userCanExecute($wgTitle, $wgUser, "read", $result);
        if(!$result){
            permissionError();
        }
        $me = Person::newFromId($wgUser->getId());
        $nsText = ($article != null) ? str_replace("_", " ", $article->getTitle()->getNsText()) : "";
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
	    if(is_numeric($name)){
            	$person = Person::newFromId($name);
	    }
	    else{
                $person = Person::newFromName($name);
	    }	
            if(!$person->isRole(MANAGER) && !$person->isRole(Expert) && !$me->isRoleAtLeast(MANAGER) && $person != $me){
               permissionError();
            }

            if((array_search($role, $wgRoles) !== false || $role == INACTIVE || 
                                                           $role == PL || $role == 'PL') && 
               $person->getName() != null && 
               $person != null && ($person->isRole($role) || $person->isRole($role."-Candidate"))){
                TabUtils::clearActions();
                $supervisors = $person->getSupervisors();
                
                $isMe = ($person->isMe() ||
                        $me->isRoleAtLeast(STAFF));
                $isSupervisor = false;
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
                        if(($project->isSubProject() && $me->leadershipOf($project->getParent())) || $me->leadershipOf($project)){
                            $isSupervisor = true;
                        }
                    }
                }
                foreach($me->getThemeProjects() as $project){
                    if($person->isMemberOf($project)){
                        $isSupervisor = true;
                        break;
                    }
                }
                $isSupervisor = ( $isSupervisor || (!FROZEN && $me->isRoleAtLeast(MANAGER)) );
                $isMe = ( $isMe && (!FROZEN || $me->isRoleAtLeast(MANAGER)) );
                $edit = ((isset($_GET['edit']) || isset($_POST['edit'])) && ($isMe || $isSupervisor));
                $edit = ( $edit && (!FROZEN || $me->isRoleAtLeast(MANAGER)) );
                
                $post = ((isset($_POST['submit']) && $_POST['submit'] == "Save Profile"));
                $post = ( $post && (!FROZEN || $me->isRoleAtLeast(MANAGER)) );
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
                $visibility['isChampion'] = $isChampion;
                
                $this->showTitle($person, $visibility);

                $tabbedPage = new TabbedPage("person");
                
                $tabbedPage->addTab(new PersonProfileTab($person, $visibility));
                if($config->getValue('networkName') == 'AGE-WELL' && ($person->isRole(HQP) || $person->isRole(HQP."-Candidate"))){
                    $tabbedPage->addTab(new HQPProfileTab($person, $visibility));
                    $tabbedPage->addTab(new HQPEpicTab($person, $visibility));
                }
                if($config->getValue('projectsEnabled')){
                    $tabbedPage->addTab(new PersonProjectTab($person, $visibility));
                }
                //$tabbedPage->addTab(new PersonRelationsTab($person, $visibility));
                //$tabbedPage->addTab(new PersonProductsTab($person, $visibility));
                if(isExtensionEnabled('Duplicates')){
                    $tabbedPage->addTab(new PersonDataQualityTab($person, $visibility));
                }
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
            else if(array_search($role, $wgRoles) !== false && $wgTitle->getText() != "Mail Index" && strstr($wgTitle->getText(), "MAIL ") === false){
                // User does not exist
                TabUtils::clearActions();
                $wgOut->clearHTML();
		if(!$wgUser->isLoggedIn()){
		    permissionError();
		}
		else{
                    $wgOut->setPageTitle("User Does Not Exist");
		}
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
