<?php

class ProjectDashboardTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectDashboardTab($project, $visibility){
        parent::AbstractTab("Dashboard");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $this->showDashboard($this->project, $this->visibility);
        return $this->html;
    }
    
    function showDashboard($project, $visibility){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            $dashboard = new DashboardTable(PROJECT_PUBLIC_STRUCTURE, $project);
            if($dashboard != null){
                $this->html = $dashboard->render();
            }
        }
    }

    function showProductivity(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut;
        $edit = $this->visibility['edit'];
        $project = $this->project;
        
        if($edit || !$edit && count($project->getPapers("Publication", '0000-00-00 00:00:00', '2100-00-00 00:00:00')) > 0){
            $this->html .= "<h2><span class='mw-headline'>Publications</span></h2>";
        }
        $this->html .= "<ul>";
        foreach($project->getPapers("Publication", '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $publication){
            //if($edit){
            //    $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Publication:{$publication->getId()}?edit' target='_blank'>{$publication->getTitle()}</a></li>";
            //}
            //else{
                $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Publication:{$publication->getId()}'>{$publication->getTitle()}</a></li>";
            //}
        }
        $this->html .= "</ul>";
        //if($edit){
            $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:AddPublicationPage\");' value='Add Publication' />";
        //}
        if($edit || !$edit && count($project->getPapers("Artifact", '0000-00-00 00:00:00', '2100-00-00 00:00:00')) > 0){
            $this->html .= "<h2><span class='mw-headline'>Artifacts</span></h2>";
        }
        $this->html .= "<ul>";
        foreach($project->getPapers("Artifact", '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $artifact){
            //if($edit){
            //    $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Artifact:{$artifact->getId()}?edit' target='_blank'>{$artifact->getTitle()}</a></li>";
            //}
            //else{
                $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Artifact:{$artifact->getId()}'>{$artifact->getTitle()}</a></li>";
            //}
        }
        $this->html .= "</ul>";
        //if($edit){
            $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:AddArtifactPage\");' value='Add Artifact' />";
        //}
        if($edit || !$edit && count($project->getPapers("Activity", '0000-00-00 00:00:00', '2100-00-00 00:00:00')) > 0){
            $this->html .= "<h2><span class='mw-headline'>Activities</span></h2>";
        }
        $this->html .= "<ul>";
        foreach($project->getPapers("Activity", '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $activity){
            //if($edit){
            //    $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Activity:{$activity->getId()}?edit' target='_blank'>{$activity->getTitle()}</a></li>";
            //}
            //else{
                $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Activity:{$activity->getId()}'>{$activity->getTitle()}</a></li>";
            //}
        }
        $this->html .= "</ul>";
        
        //if($edit){
            $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:AddActivityPage\");' value='Add Activity' />";
        //}
        if($edit || !$edit && count($project->getPapers("Press", '0000-00-00 00:00:00', '2100-00-00 00:00:00')) > 0){
            $this->html .= "<h2><span class='mw-headline'>Press</span></h2>";
        }
        $this->html .= "<ul>";
        foreach($project->getPapers("Press", '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $press){
            //if($edit){
            //    $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Press:{$press->getId()}?edit' target='_blank'>{$press->getTitle()}</a></li>";
            //}
            //else{
                $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Press:{$press->getId()}'>{$press->getTitle()}</a></li>";
            //}
        }
        $this->html .= "</ul>";
        //if($edit){
            $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:AddPressPage\");' value='Add Press' />";
        //}
        if($edit || !$edit && count($project->getPapers("Award", '0000-00-00 00:00:00', '2100-00-00 00:00:00')) > 0){
            $this->html .= "<h2><span class='mw-headline'>Awards</span></h2>";
        }
        $this->html .= "<ul>";
        foreach($project->getPapers("Award", '0000-00-00 00:00:00', '2100-00-00 00:00:00') as $award){
            $includeEdit = "";
            //if($edit){
            //    $includeEdit = "?edit";
            //}
            $this->html .= "<li><a href='$wgServer$wgScriptPath/index.php/Award:{$award->getId()}$includeEdit'>{$award->getTitle()}</a></li>";
        }
        $this->html .= "</ul>";
        //if($edit){
            $this->html .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:AddAwardPage\");' value='Add Award' />";
        //}
        
    }

}    
    
?>
