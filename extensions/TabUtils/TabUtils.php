<?php

$wgHooks['SkinTemplateTabs'][1000] = 'TabUtils::removeTabs';

class TabUtils {

    static function removeTabs($skin, &$content_actions){
        global $wgTitle;
        $new_actions = array();
        foreach($content_actions as $action){
            if($action['class'] == 'selected'){
                $action['text'] = $wgTitle->getText();
                if(isset($_GET['action'])){
                    $action['class'] = false;
                }
                $new_actions[] = $action;
            }
        }
        $content_actions = $new_actions;
        return true;
    }
    
    static function grandTabs(&$content_actions){
        global $wgTitle, $wgServer, $wgScriptPath, $wgUser;
        $me = Person::newFromId($wgUser->getId());
        if(isset($_GET['action']) && $_GET['action'] == "viewNotifications"){
            $content_actions = array();
            if(isset($_GET['history']) && $_GET['history'] == "true"){
                $currentClass = false;
                $historyClass = 'selected';
            }
            else{
                $currentClass = 'selected';
                $historyClass = false;
            }
            $content_actions['current'] = array('class' => $currentClass,
                                            'text' => "Current",
                                            'href' => "$wgServer$wgScriptPath/index.php?action=viewNotifications");
            $content_actions['history'] = array('class' => $historyClass,
                                            'text' => "History",
                                            'href' => "$wgServer$wgScriptPath/index.php?action=viewNotifications&history=true");
            return;
        }

        //Admin related eval pages
        if($wgTitle->getText() == "EvaluationTable" ||
           $wgTitle->getText() == "AcknowledgementsTable" ||
           $wgTitle->getText() == "Impersonate" ||
           $wgTitle->getText() == "Duplicates" ||
           $wgTitle->getText() == "EmptyEmailList" ||
           $wgTitle->getText() == "ProjectEvolution" ||
           $wgTitle->getText() == "InactiveUsers"){
            $content_actions = array();
            $rmcClass = false;
            $nsercClass = false;
            if($wgTitle->getText() == "EvaluationTable"){
                if(isset($_GET['section']) && $_GET['section'] == "NSERC"){
                    $rmcClass = false;
                    $nsercClass = 'selected';
                }
                else {
                    $rmcClass = 'selected';
                    $nsercClass = false;
                }
            }
            if($me->isRole(MANAGER) || $me->getName() == "Admin"){
                $content_actions['rmc'] = array('class' => $rmcClass,
                                                'text' => "RMC Meeting",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=RMC");
                $content_actions['nserc'] = array('class' => $nsercClass,
                                                'text' => "NCE",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:EvaluationTable?section=NSERC");
                $content_actions['ack'] = array('class' => false,
                                                'text' => "Acknowledgements",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:AcknowledgementsTable");
                $content_actions['dupes'] = array('class' => false,
                                                'text' => "Duplicates",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:Duplicates");
                $content_actions['emptyemail'] = array('class' => false,
                                                'text' => "Empty Emails",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:EmptyEmailList");
                $content_actions['inactiveusers'] = array('class' => false,
                                                'text' => "Inactive Users",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:InactiveUsers");
                $content_actions['impersonate'] = array('class' => false,
                                                'text' => "Impersonate",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:Impersonate");
                $content_actions['projectevolution'] = array('class' => false,
                                                'text' => "Project Evolution",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:ProjectEvolution");
                if($wgTitle->getText() == "AcknowledgementsTable"){
                    $content_actions['ack']['class'] = 'selected';
                }
                else if($wgTitle->getText() == "Duplicates"){
                    $content_actions['dupes']['class'] = 'selected';
                }
                else if($wgTitle->getText() == "EmptyEmailList"){
                    $content_actions['emptyemail']['class'] = 'selected';
                }
                else if($wgTitle->getText() == "InactiveUsers"){
                    $content_actions['inactiveusers']['class'] = 'selected';
                }
                else if($wgTitle->getText() == "Impersonate"){
                    $content_actions['impersonate']['class'] = 'selected';
                }
            }
            return;
        }

        $me = Person::newFromId($wgUser->getId());
        $new_actions = array();
        $new_actions['projects'] = array('class' => false,
                                   'text' => "Projects",
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:Projects");
        $new_actions[HQP] = array('class' => false,
                                   'text' => HQP,
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_HQP");
        $new_actions[CNI] = array('class' => false,
                                   'text' => CNI.'s',
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_CNI");
        $new_actions[PNI] = array('class' => false,
                                   'text' => PNI.'s',
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_PNI");
        $new_actions[RMC] = array('class' => false,
                                   'text' => RMC,
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_RMC");
        if($wgUser->isLoggedIn()){
            $new_actions["Publications"] = array('class' => false,
                                       'text' => "Publications",
                                       'href' => "$wgServer$wgScriptPath/index.php/GRAND:Publications");
            $new_actions["Presentations"] = array('class' => false,
                                       'text' => "Presentations",
                                       'href' => "$wgServer$wgScriptPath/index.php/GRAND:Presentations");
            $new_actions["Artifacts"] = array('class' => false,
                                       'text' => "Artifacts",
                                       'href' => "$wgServer$wgScriptPath/index.php/GRAND:Artifacts");
        }
        $new_actions["Materials"] = array('class' => false,
                                       'text' => "Multimedia",
                                       'href' => "$wgServer$wgScriptPath/index.php/GRAND:Multimedia_Stories");
        $new_actions['themes'] = array('class' => false,
                                   'text' => "Themes",
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:Themes");
        $new_actions['conferences'] = array('class' => false,
                                   'text' => "Conferences",
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_Conferences");
        if((Project::newFromName($wgTitle->getNSText()) != null || $wgTitle->getText() == "Projects") && !$me->isMemberOf(Project::newFromName($wgTitle->getNSText()))){
            $new_actions['projects']['class'] = 'selected';
        }
        else if($wgTitle->getText() == "ALL HQP" || ($wgTitle->getNSText() == HQP && !($me->isRole(HQP) && $wgTitle->getText() == $me->getName()))){
            $new_actions[HQP]['class'] = 'selected';
        }
        else if($wgTitle->getText() == "ALL CNI" || ($wgTitle->getNSText() == CNI && !($me->isRole(CNI) && $wgTitle->getText() == $me->getName()))){
            $new_actions[CNI]['class'] = 'selected';
        }
        else if($wgTitle->getText() == "ALL PNI" || ($wgTitle->getNSText() == PNI && !($me->isRole(PNI) && $wgTitle->getText() == $me->getName()))){
            $new_actions[PNI]['class'] = 'selected';
        }
        else if($wgTitle->getText() == "ALL RMC" || ($wgTitle->getNSText() == RMC && !($me->isRole(RMC) && $wgTitle->getText() == $me->getName()))){
            $new_actions[RMC]['class'] = 'selected';
        }
        else if($wgTitle->getNSText() == INACTIVE && !($me->isRole(INACTIVE) && $wgTitle->getText() == $me->getName())){
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
                if($lastRole == RMC){
                    $new_actions[RMC]['class'] = 'selected';
                }
                else if($lastRole == PNI){
                    $new_actions[PNI]['class'] = 'selected';
                }
                else if($lastRole == CNI){
                    $new_actions[RMC]['class'] = 'selected';
                }
                else if($lastRole == HQP){
                    $new_actions[HQP]['class'] = 'selected';
                }
            }
        }
        else if($wgUser->isLoggedIn() && ($wgTitle->getText() == "Publications" || ($wgTitle->getNSText() == "Publication"))){
            $new_actions["Publications"]['class'] = 'selected';
        }
        else if($wgUser->isLoggedIn() && ($wgTitle->getText() == "Presentations" || ($wgTitle->getNSText() == "Presentation"))){
            $new_actions["Presentations"]['class'] = 'selected';
        }
        else if($wgUser->isLoggedIn() && ($wgTitle->getText() == "Artifacts" || ($wgTitle->getNSText() == "Artifact") ||
                                          $wgTitle->getNSText() == "Activity" ||
                                          $wgTitle->getNSText() == "Press" ||
                                          $wgTitle->getNSText() == "Award")){
            $new_actions["Artifacts"]['class'] = 'selected';
        }
        else if(($wgTitle->getText() == "Multimedia Stories" && $wgTitle->getNSText() == "GRAND") ||
                 $wgTitle->getNSText() == "Multimedia_Story" || $wgTitle->getNSText() == "Form"){
            $new_actions['Materials']['class'] = 'selected';
        }
        else if($wgTitle->getText() == "Theme1 - New Media Challenges and Opportunities" ||
                $wgTitle->getText() == "Theme2 - Games and Interactive Simulation" ||
                $wgTitle->getText() == "Theme3 - Animation, Graphics, and Imaging" ||
                $wgTitle->getText() == "Theme4 - Social, Legal, Economic, and Cultural Perspectives" ||
                $wgTitle->getText() == "Theme5 - Enabling Technologies and Methodologies" ||
                $wgTitle->getText() == "Themes"){
            $new_actions['themes']['class'] = 'selected';
        }
        else if($wgTitle->getNSText() == "Conference" || $wgTitle->getText() == "ALL Conferences"){
            $new_actions['conferences']['class'] = 'selected';
        }
        else if($wgTitle->getNSText() == "" || 
                $wgTitle->getNSText() == "Help" || 
                $wgTitle->getText() == "UserLogin" ||
                $wgTitle->getText() == "UserLogout"){
            // Do nothing
        }
        else{
            foreach($content_actions as $action){
                if($action['class'] == "selected"){
                    return;
                }
            }
            $new_actions = array_merge(array($content_actions[0]), $new_actions);
            unset($content_actions[0]);
            $merged_actions = array_merge($new_actions, $content_actions);
            foreach($merged_actions as $key => $action){
                if($action !== null){
                    $content_actions[$key] = $action;
                }
            }
            return;
        }
        
        unset($content_actions[0]);
        $content_actions = array_merge($new_actions, $content_actions);
    }
}

?>
