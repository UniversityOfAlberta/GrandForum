<?php

class PersonThemesAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $themes = $person->getPersonThemes();
        if($this->getParam('personThemeId') != ""){
            // Single Theme
            foreach($themes as $theme){
                if($theme['id'] == $this->getParam('personThemeId')){
                    return json_encode($theme);
                }
            }
        }
        else{
            // All Themes
            return json_encode($themes);
        }
    }
    
    function doPOST(){
        global $config, $wgScriptPath, $wgAdditionalMailParams;
        $person = Person::newFromId($this->getParam('id'));
        $theme = Theme::newFromName($this->POST('name'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedThemes = Theme::getAllowedThemes();
        if($theme == null || $theme->getAcronym() == ""){
            $this->throwError("This Theme does not exist");
        }
        if(!$me->isRoleAtLeast(STAFF)){
            $this->throwError("You are not allowed to add this person to that theme");
        }
        MailingList::unsubscribeAll($person);

        $status = DBFunctions::insert('grand_theme_leaders',
                                      array('user_id'     => $person->getId(),
                                            'theme'       => $theme->getId(),
                                            'co_lead'     => $this->POST('coLead'),
                                            'coordinator' => $this->POST('coordinator'),
                                            'start_date'  => $this->POST('startDate'),
                                            'end_date'    => $this->POST('endDate'),
                                            'comment'     => $this->POST('comment')));

        $id = DBFunctions::insertId();
        Person::$themeLeaderCache = array();
        $this->params['personThemeId'] = $id;
        
        Notification::addNotification($me, Person::newFromId(0), "Theme Leader Added", "Effective {$this->POST('startDate')} <b>{$person->getNameForForms()}</b> is a theme leader of <b>{$theme->getAcronym()}</b>", "{$person->getUrl()}");
        if($config->getValue("networkName") == "CFN" && $person->isRoleDuring(HQP, "1900-01-01", "2100-01-01") && $wgScriptPath == ""){
            mail("training@cfn-nce.ca", "Theme Leader Added", "Effective {$this->POST('startDate')} <b>{$person->getNameForForms()}</b> becomes a theme leader of <b>{$theme->getAcronym()}</b>", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")), $wgAdditionalMailParams);
        }
        Notification::addNotification($me, $person, "Theme Leader Added", "Effective {$this->POST('startDate')} you become a theme leader of <b>{$theme->getAcronym()}</b>", "{$person->getUrl()}");
        MailingList::subscribeAll($person);
        return $this->doGET();
    }
    
    function doPUT(){
        global $config, $wgScriptPath, $wgAdditionalMailParams;
        $person = Person::newFromId($this->getParam('id'));
        $theme = Theme::newFromName($this->POST('name'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedThemes = Theme::getAllowedThemes();
        if($theme == null || $theme->getAcronym() == ""){
            $this->throwError("This Theme does not exist");
        }
        if(!$me->isRoleAtLeast(STAFF)){
            $this->throwError("You are not allowed to add this person to that theme");
        }
        MailingList::unsubscribeAll($person);
        $status = DBFunctions::update('grand_theme_leaders',
                                      array('theme'       => $theme->getId(),
                                            'co_lead'     => $this->POST('coLead'),
                                            'coordinator' => $this->POST('coordinator'),
                                            'start_date'  => $this->POST('startDate'),
                                            'end_date'    => $this->POST('endDate'),
                                            'comment'     => $this->POST('comment')),
                                      array('id' => $this->getParam('personThemeId')));
        Person::$themeLeaderCache = array();
        Notification::addNotification($me, Person::newFromId(0), "Theme Leader Changed", "The theme leadership ({$theme->getAcronym()}) of <b>{$person->getNameForForms()}</b> has been changed", "{$person->getUrl()}");
        if($config->getValue("networkName") == "CFN" && $person->isRoleDuring(HQP, "1900-01-01", "2100-01-01") && $wgScriptPath == ""){
            mail("training@cfn-nce.ca", "Theme Leader Changed", "The theme leadership ({$theme->getAcronym()}) of <b>{$person->getNameForForms()}</b> has been changed", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")), $wgAdditionalMailParams);
        }
        if($this->POST('endDate') != '0000-00-00 00:00:00'){
            Notification::addNotification($me, $person, "Theme Leader Removed", "Effective {$this->POST('endDate')} you are no longer a leader of <b>{$theme->getAcronym()}</b>", "{$person->getUrl()}");
        }
        MailingList::subscribeAll($person);
        if(!$status){
            $this->throwError("The theme <i>{$theme->getAcronym()}</i> could not be updated");
        }
        return $this->doGET();
    }
    
    function doDELETE(){
        global $config, $wgScriptPath, $wgAdditionalMailParams;
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        $allowedThemes = Theme::getAllowedThemes();
        $data = DBFunctions::select(array('grand_theme_leaders'),
                                    array('theme'),
                                    array('id' => EQ($this->getParam('personThemeId'))));
        if(count($data) > 0){
            $theme = Theme::newFromId($data[0]['theme']);
            if(!in_array($theme->getAcronym(), $allowedThemes) || !$me->isRoleAtLeast(STAFF)){
                $this->throwError("You are not allowed to remove this person from that theme");
            }
        }
        else{
            $this->throwError("This Theme does not exist");
        }
        MailingList::unsubscribeAll($person);
        DBFunctions::delete('grand_theme_leaders',
                            array('id' => $this->getParam('personThemeId')));
        Person::$themeLeaderCache = array();
        Notification::addNotification($me, Person::newFromId(0), "Theme Leader Removed", "<b>{$person->getNameForForms()}</b> is no longer leader of <b>{$theme->getAcronym()}</b>", "{$person->getUrl()}");
        if($config->getValue("networkName") == "CFN" && $person->isRoleDuring(HQP, "1900-01-01", "2100-01-01") && $wgScriptPath == ""){
            mail("training@cfn-nce.ca", "Theme Leader Removed", "<b>{$person->getNameForForms()}</b> is no longer leader of <b>{$theme->getAcronym()}</b>", implode("\r\n", array('Content-type: text/html; charset=iso-8859-1',"From: {$config->getValue('supportEmail')}")), $wgAdditionalMailParams);
        }
        Notification::addNotification($me, $person, "Theme Leader Removed", "You are no longer a leader of <b>{$theme->getAcronym()}</b>", "{$person->getUrl()}");
        MailingList::subscribeAll($person);
        return json_encode(array());
    }
}

?>
