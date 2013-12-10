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
            if($me->isMemberOf($project) || $me->isRoleAtLeast(MANAGER) || $me->isThemeLeaderOf($this->project)){
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
            $this->html .= "<div class='subprojects_accordion'>";
            $this->html .= "<h3><a href='#'>{$subproject->getName()}</a></h3>";
            $this->html .= "<div>";
            $tab = new ProjectMainTab($subproject, array('overrideEdit' => true));
            $this->html .= $tab->generateBody();
            $this->html .= "<a class='button' href='{$subproject->getUrl()}' target='_blank'>View Project Page</a>";
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
}    
    
?>
