<?php

class ManagePeopleReportItem extends StaticReportItem {

    function render(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        // Load the scripts for Manage People
        $managePeople = new ManagePeople();
        $scripts = array_merge($managePeople->loadTemplates(true),
                               $managePeople->loadModels(true),
                               $managePeople->loadViews(true));
        $emptyProject = new Project(array());
        $frozen = json_encode((!$me->isRoleAtLeast(STAFF) && $emptyProject->isFeatureFrozen("Manage People")));
        
        $view = "<style>
                    #managePeopleDescription { display: none; }
                 </style>
                 <link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/ManagePeople/style.css' type='text/css' rel='stylesheet' />"
                .implode("", $scripts)."
                 <div id='managePeople'></div>
                 <script type='text/javascript'>
                    var frozen = $frozen;
                    var project = new Project(".Project::newFromId($this->projectId)->toJSON().");
                    var people = project.members;
                    project.getMembers();
                    view = new ManagePeopleView({el: $('#managePeople'), model: people});
                 </script>";
        
        $item = $this->processCData($view);
        $wgOut->addHTML($item);
    }

    function renderForPDF(){
        global $wgOut, $wgUser;
        
        $item = $this->processCData($view);
        $wgOut->addHTML($item);
    }

}

?>
