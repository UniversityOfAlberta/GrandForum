<?php

class ProjectSubprojectsTab extends AbstractTab {

    var $project;
    var $visibility;

    function __construct($project, $visibility){
        parent::__construct("Sub-Projects");
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
            if($me->isMemberOf($project) || $me->isRoleAtLeast(STAFF) || $me->isThemeLeaderOf($this->project) || $me->isThemeCoordinatorOf($this->project)){
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
                        if($person->isRoleAtLeast(NI)){
                            $names[$person->getName()] = $person->getNameForForms();
                        }
                    }
                    asort($names);
                    
                    $create->getElementById("new_pl")->options = $names;
                    
                    $create->getElementById("new_subproject_row")->remove();
                    $create->getElementById("new_subprojectdd_row")->remove();
                    //$create->getElementById("new_status_row")->hide();
                    $create->getElementById("new_type_row")->hide();
                    $create->getElementById("new_phase_row")->hide();
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
        if($can_edit){
            $this->html .= "<div id='end_diag' style='display:none;' title='End Sub-Project'>Are you sure you want to end this sub-project?</div><script type='text/javascript'>
                function endProject(name, id){
                    $('#end_diag').dialog({
                        width: 400,
                        modal: true,
                        buttons: {
                            'End Sub-Project': function(){
                                $.post('$wgServer$wgScriptPath/index.php?action=api.deleteProject', {'project': name}, function(response){
                                    clearAllMessages();
                                    if(response.errors.length == 0){
                                        addSuccess('The sub-project <b>' + name + '</b> was ended');
                                        $('#sub_' + id).parent().slideUp();
                                    }
                                    else{
                                        addError('There were errors ending the sub-project <b>' + name + '</b>');
                                    }
                                });
                                $(this).dialog('close');
                            },
                            'Cancel': function(){
                                $(this).dialog('close');
                            }
                        }
                    });
                }
            </script>";
        }
        foreach($subprojects as $subproject){
            $this->html .= "<div class='subprojects_accordion'>";
            $this->html .= "<h3><a href='#'>{$subproject->getName()}</a></h3>";
            $this->html .= "<div id='sub_{$subproject->getId()}'>";
            $tab = new ProjectMainTab($subproject, array('edit' => false,
                                                         'isLead' => false,
                                                         'isMember' => false,
                                                         'overrideEdit' => true));
            $this->html .= $tab->generateBody();
            $this->html .= "<a class='button' href='{$subproject->getUrl()}' target='_blank'>View Project Page</a>";
            if($can_edit){
                $this->html .=<<<EOF
                <a class="button" href="{$subproject->getUrl()}?edit" target="_blank">Edit Sub-Project</a>
                <button type="button" onClick="endProject('{$subproject->getName()}', '{$subproject->getId()}')">End Sub-Project</button>
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
