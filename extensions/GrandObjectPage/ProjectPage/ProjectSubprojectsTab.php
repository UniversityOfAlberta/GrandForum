<?php

class ProjectSubprojectsTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectSubprojectsTab($project, $visibility){
        parent::AbstractTab("Sub-Projects");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath;
        if($wgUser->isLoggedIn()){
            $project = $this->project;
            $me = Person::newFromId($wgUser->getId());
            if($me->isMemberOf($project) || $me->isRoleAtLeast(MANAGER)){
                $edit = $this->visibility['edit'];
                $dataUrl = "$wgServer$wgScriptPath/index.php?action=getProjectMilestoneTimelineData&project={$project->getId()}";
                $timeline = new Simile($dataUrl);
                $timeline->interval = "50";
                $timeline->popupWidth = "500";
                $timeline->popupHeight = "300";
               
               
                $this->showSubprojects();
                return $this->html;
            }
        }
    }

    
    function showSubprojects(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut;
        $project = $this->project;
       
        $can_edit = $this->visibility['isLead'];

        $me = Person::newFromId($wgUser->getId());
        
        $subprojects = $project->getSubprojects();
       
        $this->html .=<<<EOF
            <h2>Current Sub-Projects</h2>
EOF;
                
        foreach($subprojects as $subproject){
            $key = $subproject->getId();
            $title = $subproject->getName();
            $type = $subproject->getType();
            $status = $subproject->getStatus();

            $description = nl2br($subproject->getDescription());
            $this->html .= "<div class='subprojects_accordion'>";
            $this->html .= "<h3>{$title}</h3>";
            $this->html .= "<div style='height: auto !important;'>";
            $this->html .= "<b>Type:</b> {$type}<br />
                            <b>Status:</b> {$status}<br />";
                            
            $this->html .= $this->showPeople($subproject);
            
            $this->html .= "<h2><span class='mw-headline'>Description</span></h2>";
        
            $this->html .= "<p>" . $this->sandboxParse($description) . "</p>";
            
            if($can_edit){
                $this->html .=<<<EOF
                <a  class="button" href="{$wgServer}{$wgScriptPath}/index.php/{$title}:Main?edit" target="_blank">Edit</a>
EOF;
            }
            $this->html .= "</div></div>";
  
        }// subprojects loop
        
          
        $this->html .=<<<EOF
        <script type="text/javascript">
        $(document).ready(function() {
            $( ".subprojects_accordion" ).accordion({active: false, collapsible: true, icons: false});
        });    
        </script>
        <style type="text/css">
            .ui-accordion-content {
                height: auto !important;
                padding: 1em !important;
            }
        </style>
EOF;
        //$wgOut->addScript($custom_js);
                    
    }


    //
    function showPeople($subproject){

        global $wgUser, $wgServer, $wgScriptPath;
        
        $edit = $this->visibility['edit'];
        $project = $subproject;
        
        $leaders = $project->getLeaders(true); //only get id's
        $coleaders = $project->getCoLeaders(true);
        $pnis = $project->getAllPeople(PNI);
        $cnis = $project->getAllPeople(CNI);
        $ars = $project->getAllPeople(AR);
        $hqps = $project->getAllPeople(HQP);
      
        $html = "";

        if(!$edit){
            $html .= "<h2><span class='mw-headline'>Sub-Project Leadership and Members</span></h2>";
            $html .= "<table>";
            if(!empty($leaders)){
                foreach($leaders as $leader_id){
                    $leader = Person::newFromId($leader_id);
                    $leaderRoles = $leader->getRoles();
                    $html .= "<tr>";
                    $leaderType = "Leader";
                    if($leader->managementOf($project->getName())){
                        $leaderType = "Manager";
                    }
                    if(count($leaderRoles) > 0){
                        $html .= "<td align='right'><b>{$leaderType}:</b></td><td><a href='$wgServer$wgScriptPath/index.php/{$leaderRoles[0]->getRole()}:{$leader->getName()}'>{$leader->getReversedName()}</a></td></tr>";
                    }
                    else{
                        $html .= "<td align='right'><b>{$leaderType}:</b></td><td>{$leader->getReversedName()}</td></td></tr>";
                    }
                }    
            }
            if(!empty($coleaders)){
                foreach($coleaders as $leader_id){
                    $leader = Person::newFromId($leader_id);
                    $leaderRoles = $leader->getRoles();
                    $html .= "<tr>";
                    $leaderType = "Co-Leader";
                    if($leader->managementOf($project->getName())){
                        $leaderType = "Manager";
                    }
                    if(count($leaderRoles) > 0){
                        $html .= "<td align='right'><b>{$leaderType}:</b></td><td><a href='$wgServer$wgScriptPath/index.php/{$leaderRoles[0]->getRole()}:{$leader->getName()}'>{$leader->getReversedName()}</a></td></tr>";
                    }
                    else{
                        $html .= "<td align='right'><b>{$leaderType}:</b></td><td>{$leader->getReversedName()}</td></td></tr>";
                    }
                }    
            }
            $html .= "</table>";
        }
        
        $html .= "<table width='100%'><tr><td valign='top' width='50%'>";
        if($edit || !$edit && count($pnis) > 0){
            $html .= "<h2><span class='mw-headline'>PNIs</span></h2>";
        }
        $html .= "<ul>";
        foreach($pnis as $pni){
            if((!empty($leaders) && in_array($pni->getId(), $leaders)) || (!empty($coleaders) && in_array($pni->getId(), $coleaders))){
                continue;
            }
            $target = "";
            if($edit){
                $target = " target='_blank'";
            }
            $html .= "<li><a href='{$pni->getUrl()}'$target>{$pni->getReversedName()}</a></li>";
        }
        
        $html .= "</ul>";
        if($edit || !$edit && count($cnis) > 0){
            $html .= "<h2><span class='mw-headline'>CNIs</span></h2>";
        }
        $html .= "<ul>";
        foreach($cnis as $cni){
            if((!empty($leaders) && in_array($cni->getId(), $leaders)) || (!empty($leaders) && in_array($cni->getId(), $leaders))){
                continue;
            }
            $target = "";
            if($edit){
                $target = " target='_blank'";
            }
            $html .= "<li><a href='{$cni->getUrl()}'$target>{$cni->getReversedName()}</a></li>";
        }
        $html .= "</ul>";
        if($edit || !$edit && count($ars) > 0){
            $html .= "<h2><span class='mw-headline'>Associated Researchers</span></h2>";
        }
        $html .= "<ul>";
        foreach($ars as $ar){
            if((!empty($leaders) && in_array($ar->getId(), $leaders)) || (!empty($coleaders) && in_array($ar->getId(), $coleaders))){
                continue;
            }
            $target = "";
            if($edit){
                $target = " target='_blank'";
            }
            $html .= "<li><a href='{$ar->getUrl()}'$target>{$ar->getReversedName()}</a></li>";
        }
        $html .= "</ul></td>";
        if($wgUser->isLoggedIn()){
            $html .= "<td width='50%' valign='top'>";
            if($edit || !$edit && count($hqps) > 0){
                $html .= "<h2><span class='mw-headline'>HQP</span></h2>";
            }
            $html .= "<ul>";
            foreach($hqps as $hqp){
                $target = ""; 
                if($edit){
                    $target = " target='_blank'";
                }
                $html .= "<li><a href='{$hqp->getUrl()}'$target>{$hqp->getReversedName()}</a></li>";
            }
            $html .= "</ul></td>";
        }
        $html .= "</tr></table>";
        
        return $html;
    }
    
    function date_picker($key, $date, $startyear=NULL, $endyear=NULL){
        if($key == "new"){
            $keyArray = "[]";
        }
        else{
            $keyArray = "";
        }
        $newDate = explode("-", $date);
        $year = @$newDate[0];
        $month = @$newDate[1];
        if($startyear==NULL){
            $startyear = date("Y")-100;
        }
        if($endyear==NULL){
            $endyear=date("Y")+50;
        }

        $months=array('','January','February','March','April','May',
        'June','July','August', 'September','October','November','December');

        // Month dropdown
        $html="<select name=\"m_{$key}_month$keyArray\">";

        for($i=1;$i<=12;$i++){
            $selected = "";
            if($month == $i){
                $selected = "selected='selected'";
            }
            if($i < 10){
                $id = "0".$i;
            }
            else{
                $id = $i;
            }
            $html.="<option $selected value='$id'>$months[$i]</option>";
        }
        $html.="</select> ";

        // Year dropdown
        $html.="<select name=\"m_{$key}_year$keyArray\">";

        for($i=$endyear;$i>=$startyear;$i--){ 
            $selected = "";
            if($year == $i){
                $selected = "selected='selected'";
            }     
            $html.="<option $selected value='$i'>$i</option>";
        }
        $html.="</select> ";

        return $html;
    }

    function sandboxParse($wikiText) {
        global $wgTitle, $wgUser;
        $myParser = new Parser();
        $myParserOptions = ParserOptions::newFromUser($wgUser);
        $result = $myParser->parse($wikiText, $wgTitle, $myParserOptions);
        return $result->getText();
    }
}    
    
?>
