<?php

class ProjectSubprojectsTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectSubprojectsTab($project, $visibility){
        parent::AbstractTab("Sub-Projects");
        $this->project = $project;
        $this->visibility = $visibility;
        if(isset($_POST['edit']) && isset($_POST['create_subproject'])){
            unset($_POST['edit']);
        }
    }
    
    function generateBody(){
        global $wgUser, $wgOut, $wgMessage, $wgServer, $wgScriptPath;
        if($wgUser->isLoggedIn()){
            $project = $this->project;
            $me = Person::newFromId($wgUser->getId());
            if($me->isMemberOf($project) || $me->isRoleAtLeast(MANAGER)){
                if($this->visibility['isLead']){
                    if(isset($_POST['create_subproject'])){
                        $error = CreateProjectTab::handleEdit();
                        if($error != ""){
                            $wgMessage->addError($error);
                        }
                        else{
                            $wgMessage->addSuccess("The Sub-Project was created successfully");
                        }
                    }
                    $create = CreateProjectTab::createForm("new");
                    
                    $names = array("");
                    $people = array_merge($project->getAllPeople());
                    foreach($people as $person){
                        if($person->isRoleAtLeast(CNI)){
                            $names[$person->getName()] = $person->getNameForForms();
                        }
                    }
                    asort($names);
                    
                    $create->getElementById("new_pl")->options = $names;
                    
                    $create->getElementById("new_subproject_row")->remove();
                    $create->getElementById("new_subprojectdd_row")->remove();
                    $create->getElementById("new_copl_row")->hide();
                    $create->getElementById("new_status_row")->hide();
                    $create->getElementById("new_type_row")->hide();
                    $create->getElementById("new_phase_row")->hide();
                    $create->getElementById("new_bigbet_row")->hide();
                    $create->getElementById("new_problem_row")->hide();
                    $create->getElementById("new_solution_row")->hide();
                    $create->getElementById("new_challenges_set")->hide();
                    $this->html .= "<input type='hidden' name='new_subproject' value='Yes' />";
                    $this->html .= "<input type='hidden' name='new_parent_id' value='{$project->getId()}' />";
                    $this->html .= "<button id='new_subproject_button'>New Sub-Project</button><div id='new_subproject'>".$create->render()."<input type='submit' name='create_subproject' value='Create Sub-Project' /></div>";
                    
                    $this->html .= "<script type='text/javascript'>
                        $('#new_subproject').hide();
                        $('#new_subproject_button').click(function(){
                            $(this).remove();
                            $('#new_subproject').slideDown();
                        }); 
                    </script>";
                }
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
        
        $subprojects = $project->getSubProjects();
       
        $this->html .= "<h2>Current Sub-Projects</h2>";
                
        foreach($subprojects as $subproject){
            $key = $subproject->getId();
            $title = $subproject->getName();
            $type = $subproject->getType();
            $status = $subproject->getStatus();

            $description = nl2br($subproject->getDescription());
            $this->html .= "<div class='subprojects_accordion'>";
            $this->html .= "<h3><a href='#'>{$title}</a></h3>";
            $this->html .= "<div>";
            $this->html .= "<b>Type:</b> {$type}<br />
                            <b>Status:</b> {$status}<br />";
                            
            $this->html .= $this->showPeople($subproject);
            
            $this->html .= "<h2><span class='mw-headline'>Description</span></h2>";
        
            $this->html .= "<p>" . $this->sandboxParse($description) . "</p>";
            
            if($can_edit){
                $this->html .=<<<EOF
                <a class="button" href="{$subproject->getUrl()}?edit" target="_blank">Edit</a>
EOF;
            }
            $this->html .= "</div></div>";
  
        }
        
        $this->html .=<<<EOF
        <script type="text/javascript">
        $(document).ready(function() {
            $(".subprojects_accordion").accordion({active: true,
                                                   autoHeight: false,
                                                   collapsible: true});
        });    
        </script>
EOF;
  
    }

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

        $html .= "<h2><span class='mw-headline'>Leaders</span></h2>";
        $html .= "<table>";
        if(!empty($leaders)){
            foreach($leaders as $leader_id){
                $leader = Person::newFromId($leader_id);
                $html .= "<tr>";
                $leaderType = "Leader";
                if($leader->managementOf($project->getName())){
                    $leaderType = "Manager";
                }
                $html .= "<td align='right'><b>{$leaderType}:</b></td><td><a href='{$leader->getUrl()}'>{$leader->getReversedName()}</a></td></tr>";
            }    
        }
        $html .= "</table>";
        
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

    function sandboxParse($wikiText) {
        global $wgTitle, $wgUser;
        $myParser = new Parser();
        $myParserOptions = ParserOptions::newFromUser($wgUser);
        $result = $myParser->parse($wikiText, $wgTitle, $myParserOptions);
        return $result->getText();
    }
}    
    
?>
