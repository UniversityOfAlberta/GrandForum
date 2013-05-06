<?php

$wgHooks['SkinTemplateContentActions'][1000] = 'TabUtils::actionTabs';

class TabUtils {

    static $customActions = array();

    static function actionTabs(&$content_actions){
        global $wgTitle, $wgServer, $wgScriptPath, $wgOut;
        $new_actions = array();
        foreach($content_actions as $key => $action){
            if(strstr($action['class'], 'selected') !== false && !is_numeric($key)){
                continue;
            }
            if(!is_numeric($key)){
                $action['class'] = 'action';
            }
            $new_actions[$key] = $action;
        }
        foreach(self::$customActions as $key => $action){
            $new_actions[$key] = $action;
        }
        $wgOut->addHTML("<script type='text/javascript'>
            $('li.people').wrapAll('<ul class=\'people\'>');
            $('li.product').wrapAll('<ul class=\'products\'>');
            $('ul.people').wrapAll('<li class=\'invisible\'>');
            $('ul.products').wrapAll('<li class=\'invisible\'>');
            
            var productsSelected = false;
            if($('li.product').filter('.selected').length >= 1){
                productsSelected = true;
            }
            
            var peopleSelected = false;
            if($('li.people').filter('.selected').length >= 1){
                peopleSelected = true;
            }
            
            $('li.action').wrapAll('<ul class=\'actions\' />');
            $('div#submenu ul.products').dropdown({title: 'Products',
                                                  width: '125px' 
                                                  });
            $('div#submenu ul.people').dropdown({title: 'People',
                                                  width: '125px' 
                                                  });                             
            $('div#submenu ul.actions').dropdown({title: 'Actions',
                                                  width: '125px' 
                                                 });
            $('div#submenu ul.actions').css('padding-right', 0);
            $('div#submenu ul.actions li.actions').css('float', 'right');
            
            if(productsSelected){
                $('ul.products > li').addClass('selected');
                $('ul.products').imgDown();
            }
            if(peopleSelected){
                $('ul.people > li').addClass('selected');
                $('ul.people').imgDown();
            }
        </script>");
        $content_actions = $new_actions;
        return true;
    }
    
    static function clearTabs($skin, &$content_actions){
        unset($content_actions['protect']);
        unset($content_actions['watch']);
        unset($content_actions['unwatch']);
        unset($content_actions['create']);
        unset($content_actions['history']);
        unset($content_actions['delete']);
        unset($content_actions['talk']);
        unset($content_actions['move']);
        unset($content_actions['edit']);
        unset($content_actions['addsection']);
        unset($content_actions['editTemplate']);
        unset($content_actions['Create from template']);
        unset($content_actions['instance list']);
        return true;
    }
    
    /**
     * Adds an action to the sub-menu
     * @param string $text The visible text of the action
     * @param string $href The url of the action
     */
    static function addAction($text, $href){
        self::$customActions[str_replace(" ", "", $text)] = 
                                array('text' => $text,
                                      'href' => $href,
                                      'class' => 'action');
    }
    
    /**
     * Clears most of the built in wiki actions of the sub-menu
     */
    static function clearActions(){
        global $wgHooks;
        $wgHooks['SkinTemplateTabs'][] = 'TabUtils::clearTabs';
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
           $wgTitle->getText() == "AdminVisualizations" ||
           $wgTitle->getText() == "Impersonate" ||
           $wgTitle->getText() == "Duplicates" ||
           $wgTitle->getText() == "EmptyEmailList" ||
           $wgTitle->getText() == "ProjectEvolution" ||
           $wgTitle->getText() == "ReportStatsTable" ||
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
                $content_actions['adminvis'] = array('class' => false,
                                                'text' => "Visualizations",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:AdminVisualizations");
                $content_actions['dupes'] = array('class' => false,
                                                'text' => "Duplicates",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:Duplicates");
                $content_actions['emptyemail'] = array('class' => false,
                                                'text' => "Empty Emails",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:EmptyEmailList");
                $content_actions['inactiveusers'] = array('class' => false,
                                                'text' => "Inactive Users",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:InactiveUsers");
                $content_actions['reportstats'] = array('class' => false,
                                                'text' => "Reporting Stats",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:ReportStatsTable");
                $content_actions['impersonate'] = array('class' => false,
                                                'text' => "Impersonate",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:Impersonate");
                $content_actions['projectevolution'] = array('class' => false,
                                                'text' => "Project Evolution",
                                                'href' => "$wgServer$wgScriptPath/index.php/Special:ProjectEvolution");
                if($wgTitle->getText() == "AcknowledgementsTable"){
                    $content_actions['ack']['class'] = 'selected';
                }
                if($wgTitle->getText() == "AdminVisualizations"){
                    $content_actions['adminvis']['class'] = 'selected';
                }
                else if($wgTitle->getText() == "Duplicates"){
                    $content_actions['dupes']['class'] = 'selected';
                }
                else if($wgTitle->getText() == "EmptyEmailList"){
                    $content_actions['emptyemail']['class'] = 'selected';
                }
                else if($wgTitle->getText() == "ReportStatsTable"){
                    $content_actions['reportstats']['class'] = 'selected';
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
        $new_actions[HQP] = array('class' => 'people',
                                   'text' => HQP,
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_HQP");
        $new_actions[CNI] = array('class' => 'people',
                                   'text' => CNI.'s',
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_CNI");
        $new_actions[PNI] = array('class' => 'people',
                                   'text' => PNI.'s',
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_PNI");
        $new_actions[RMC] = array('class' => 'people',
                                   'text' => RMC,
                                   'href' => "$wgServer$wgScriptPath/index.php/GRAND:ALL_RMC");
        if($wgUser->isLoggedIn()){
            $new_actions["Publications"] = array('class' => 'product',
                                       'text' => "Publications",
                                       'href' => "$wgServer$wgScriptPath/index.php/Special:Products#/Publication");
            $new_actions["Artifacts"] = array('class' => 'product',
                                       'text' => "Artifacts",
                                       'href' => "$wgServer$wgScriptPath/index.php/Special:Products#/Artifact");
            $new_actions["Presentations"] = array('class' => 'product',
                                       'text' => "Presentations",
                                       'href' => "$wgServer$wgScriptPath/index.php/Special:Products#/Presentation");
            $new_actions["Activities"] = array('class' => 'product',
                                              'text' => "Activities",
                                              'href' => "$wgServer$wgScriptPath/index.php/Special:Products#/Activity");
            $new_actions["Press"] = array('class' => 'product',
                                          'text' => "Press",
                                          'href' => "$wgServer$wgScriptPath/index.php/Special:Products#/Press");
            $new_actions["Awards"] = array('class' => 'product',
                                           'text' => "Awards",
                                           'href' => "$wgServer$wgScriptPath/index.php/Special:Products#/Award");
            $new_actions["Multimedia"] = array('class' => 'product',
                                           'text' => "Multimedia",
                                           'href' => "$wgServer$wgScriptPath/index.php/GRAND:Multimedia_Stories");
        }
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
            $new_actions[HQP]['class'] = 'people selected';
        }
        else if($wgTitle->getText() == "ALL CNI" || ($wgTitle->getNSText() == CNI && !($me->isRole(CNI) && $wgTitle->getText() == $me->getName()))){
            $new_actions[CNI]['class'] = 'people selected';
        }
        else if($wgTitle->getText() == "ALL PNI" || ($wgTitle->getNSText() == PNI && !($me->isRole(PNI) && $wgTitle->getText() == $me->getName()))){
            $new_actions[PNI]['class'] = 'people selected';
        }
        else if($wgTitle->getText() == "ALL RMC" || ($wgTitle->getNSText() == RMC && !($me->isRole(RMC) && $wgTitle->getText() == $me->getName()))){
            $new_actions[RMC]['class'] = 'people selected';
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
        else if($wgUser->isLoggedIn() && ($wgTitle->getText() == "Products" || 
                                          $wgTitle->getNsText() == "Publication" ||
                                          $wgTitle->getNsText() == "Artifact" ||
                                          $wgTitle->getNsText() == "Presentation" ||
                                          $wgTitle->getNsText() == "Activity" ||
                                          $wgTitle->getNsText() == "Press" ||
                                          $wgTitle->getNsText() == "Award" ||
                                          $wgTitle->getNsText() == "Multimedia")){
            $new_actions["Publications"]['class'] = 'product selected';
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
