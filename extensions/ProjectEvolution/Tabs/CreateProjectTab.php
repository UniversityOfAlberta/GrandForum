<?php

class CreateProjectTab extends ProjectTab {

    function __construct(){
        ProjectTab::__construct("Create");
    }
    
    static function createForm($pre){
        global $config;
    
        $form = new FormContainer("{$pre}_form_container");
    
        $table = new FormTable("{$pre}_form_table");
        
        $acronymRow = new FormTableRow("{$pre}_acronym_row");
        $acronymLabel = new Label("{$pre}_acronym_label", "Identifier", "The identifier/acronym for the project ie. MEOW", VALIDATE_NOT_NULL);
        $acronymField = new TextField("{$pre}_acronym", "Identifier", "", VALIDATE_NOT_NULL + VALIDATE_NOT_PROJECT);
        $acronymRow->append($acronymLabel)->append($acronymField->attr('size', 12));
        
        $fullNameRow = new FormTableRow("{$pre}_full_name_row");
        $fullNameLabel = new Label("{$pre}_full_name_label", "Full Name", "The project's full name ie. Media Enabled Organizational Worldflow", VALIDATE_NOT_NULL);
        $fullNameField = new TextField("{$pre}_full_name", "Full Name", "", VALIDATE_NOT_NULL);
        $fullNameRow->append($fullNameLabel)->append($fullNameField->attr('size', 40));
        
        $subprojectRow = new FormTableRow("{$pre}_subproject_row");

        //Sub-project radio button + parent project drop-down
        $projectOptions = "<option value='0'></option>\n";
        foreach(Project::getAllProjects() as $project){
            $project_id = $project->getId();
            $project_name = $project->getName();
            $projectOptions .= "<option phase='{$project->getPhase()}' value='{$project_id}'>{$project_name}</option>\n";
        }
        $subp =<<<EOF
        <input type='radio' onclick='subReaction();' id='{$pre}_subproject_n'  name='{$pre}_subproject' value='No' checked='checked' />No
        &nbsp;&nbsp;&nbsp;&nbsp;
        <input type='radio' onclick='subReaction();' id='{$pre}_subproject_y' name='{$pre}_subproject' value='Yes' />Yes
EOF;
        $subprojectRow->append(new Label("{$pre}_subproject_label", "Sub-Project", "Is this a Sub-Project?", VALIDATE_NOT_NULL));
        $subprojectRow->append(new CustomElement("{$pre}_subproject", "", "", $subp, VALIDATE_NOT_NULL));

        $subprojectDDRow = new FormTableRow("{$pre}_subprojectdd_row");
        $subp_dd =<<<EOF
        <select id='{$pre}_subproject_parent_dd' name='new_parent_id' data-placeholder='Choose a Parent...'>
        {$projectOptions}
        </select>
        <script type='text/javascript'>
            var options = Array();
        
            $(document).ready(function(){
                $("#new_subproject_parent_dd").chosen();
                $("#new_subproject_parent_dd_chosen").hide();
            });
        
            function subReaction(){
                if($('#new_subproject_y').is(':checked')) { 
                     $("#new_subproject_parent_dd_chosen").show();
                }
                else{
                    $("#new_subproject_parent_dd_chosen").hide();
                }
            }
        </script>
EOF;
        $subprojectDDRow->append(new CustomElement("{$pre}_subproject_label", "", "", "", VALIDATE_NOTHING));
        $subprojectDDRow->append(new CustomElement("{$pre}_parent_id", "", "", $subp_dd, VALIDATE_NOT_NULL));
        
        $statusRow = new FormTableRow("{$pre}_status_row");
        $statusRow->append(new Label("{$pre}_status_label", "Status", "The status of this project", VALIDATE_NOT_NULL));
        $statusRow->append(new VerticalRadioBox("{$pre}_status", "Status", "Active", array("Proposed", "Deferred", "Active"), VALIDATE_NOT_NULL));
        
        $typeRow = new FormTableRow("{$pre}_type_row");
        $typeRow->append(new Label("{$pre}_type_label", "Type", "The type of this project", VALIDATE_NOT_NULL));
        $typeRow->append(new VerticalRadioBox("{$pre}_type", "Type", "Research", array("Research", "Administrative", "Strategic", "Innovation Hub"), VALIDATE_NOT_NULL));
        
        $phaseRow = new FormTableRow("{$pre}_phase_row");
        $phaseRow->append(new Label("{$pre}_phase_label", "Phase", "What project phase the new project belongs to", VALIDATE_NOT_NULL));
        $phaseRow->append(new SelectBox("{$pre}_phase", "Phase", PROJECT_PHASE, array_combine(range(PROJECT_PHASE, 1, -1), range(PROJECT_PHASE, 1, -1)) + array("Research" => "Research"), VALIDATE_NOT_NULL));
        
        $effectiveRow = new FormTableRow("{$pre}_effective_row");
        $effectiveRow->append(new Label("{$pre}_effective_label", "Effective Date", "When this action is to take place", VALIDATE_NOT_NULL));
        $effectiveRow->append(new CalendarField("{$pre}_effective", "Effective Date", date("Y-m-d"), VALIDATE_NOT_NULL));
        
        $names = array("");
        $people = array_merge(Person::getAllPeople(NI), Person::getAllPeople(PL));
        foreach($people as $person){
            $names[$person->getName()] = $person->getNameForForms();
        }
        asort($names);
        
        $plRow = new FormTableRow("{$pre}_pl_row");
        $plRow->append(new Label("{$pre}_pl_label", $config->getValue('projectTerm')." Leader", "The leader of this Project.  The person should be a valid person on this project.", VALIDATE_NOTHING));
        $plRow->append(new ComboBox("{$pre}_pl", $config->getValue('projectTerm')." Leader", "", $names, VALIDATE_NI));
        
        $descRow = new FormTableRow("{$pre}_description_row");
        $descRow->append(new Label("{$pre}_description_label", "Overview", "The overview of the project", VALIDATE_NOTHING));
        $descRow->append(new TextareaField("{$pre}_description", "Overview", "", VALIDATE_NOTHING));
        
        $longDescRow = new FormTableRow("{$pre}_long_description_row");
        $longDescRow->append(new Label("{$pre}_long_description_label", "Description", "The full description of the project", VALIDATE_NOTHING));
        $longDescRow->append(new TextareaField("{$pre}_long_description", "Description", "", VALIDATE_NOTHING));
              
        //Challenges
        $challengeFieldSet = new FieldSet("{$pre}_challenges_set", "Theme");
       
        $challengeNames = array();
        $challenges = Theme::getAllThemes();
        foreach($challenges as $challenge){
            $challengeNames[$challenge->getId()] = $challenge->getAcronym();
        }

        $challengeRadioBox = new VerticalCheckBox2("{$pre}_challenge", "", array(), $challengeNames, VALIDATE_NOTHING);
        $challengeFieldSet->append($challengeRadioBox);

        if(!$config->getValue("projectTypes")){
            $typeRow->hide();
        }
        if(!$config->getValue("projectStatus")){
            $statusRow->hide();
        }

        $table->append($acronymRow);
        $table->append($fullNameRow);
        $table->append($subprojectRow);
        $table->append($subprojectDDRow);
        $table->append($statusRow);
        $table->append($typeRow);
        $table->append($phaseRow);
        $table->append($effectiveRow);
        $table->append($plRow);
        $table->append($descRow);
        $table->append($longDescRow);
        
        $form->append($table);
        $form->append($challengeFieldSet);

        return $form;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $this->html = "'Create Project' will create a new project, and automatically set up the mailing list.  Once the project is completed, project leaders can be created from the Manage People page.<br />";
        $form = self::createForm('new');
        $this->html .= $form->render();
        return $this->html;
    }
    
    function handleEdit(){
        global $wgMessages;
        $form = self::createForm('new');
        $status = $form->validate();
        if($status){
            // Call the API
            $form->getElementById("new_acronym")->setPOST("acronym");
            $form->getElementById("new_full_name")->setPOST("fullName");
            $form->getElementById("new_status")->setPOST("status");
            $form->getElementById("new_type")->setPOST("type");
            $form->getElementById("new_phase")->setPOST("phase");
            $form->getElementById("new_effective")->setPOST("effective_date");
            $form->getElementById("new_pl")->setPOST("pl");
            $form->getElementById("new_description")->setPOST("description");
            $form->getElementById("new_long_description")->setPOST("long_description");
            $form->getElementById("new_challenge")->setPOST("challenge");
            $form->getElementById("new_parent_id")->setPOST("parent_id");

            if(!APIRequest::doAction('CreateProject', true)){
                return "There was an error Creating the Project";
            }
            else{
                if($_POST['pl'] != ""){
                    $_POST['role'] = $_POST['acronym'];
                    $person = Person::newFromName($_POST['pl']);
                    $_POST['userId'] = $person->getId();
                    $_POST['name'] = PL;
                    $x = new stdClass();
                    $x->name = $_POST['acronym'];
                    $_POST['projects'] = array($x);
                    $_POST['startDate'] = $_POST['effective_date'];
                    
                    $api = new RoleAPI();
                    $api->doPOST();
                }
                $form->reset();
            }
        }
        else{
            return "The project was not created";
        }
    }
}    
    
?>
