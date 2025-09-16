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
        $phaseNames = $config->getValue("projectPhaseNames") ?: [];
        $numPhases = count($phaseNames);

        if ($numPhases > 1) {
            // normal dropdown
            $phaseRow->append(new Label("{$pre}_phase_label", "Phase", "What project phase the new project belongs to", VALIDATE_NOT_NULL));
            $phaseOptions = array_combine($phaseNames, $phaseNames);
            $phaseField = new SelectBox("{$pre}_phase", "Phase", null, $phaseOptions, VALIDATE_NOT_NULL);
            $phaseRow->append($phaseField);
        } elseif ($numPhases === 1) {
            // if only 1 phase then just use it as default
            $defaultValue = reset($phaseNames);
            $hiddenInputHTML = "<input type='hidden' name='{$pre}_phase' value='{$defaultValue}'>";
            $phaseField = new CustomElement("{$pre}_phase_hidden", "", "", $hiddenInputHTML, VALIDATE_NOTHING);
            $phaseRow->append($phaseField);
            $phaseRow->hide();
        } else {
            $phaseRow->hide();
        }
        
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
        
        $longDescRow = new FormTableRow("{$pre}_long_description_row");
        $longDescRow->append(new Label("{$pre}_long_description_label", "Description", "The full description of the project", VALIDATE_NOTHING));
        $longDescEditorHTML = <<<EOF
        <textarea name="{$pre}_long_description" style="height: 400px; width: 100%;"></textarea>
        <script type="text/javascript">
        $(function() {
            $('textarea[name="{$pre}_long_description"]').tinymce({
                theme: 'modern',
                relative_urls : false,
                convert_urls: false,
                menubar: false,
                default_link_target: '_blank',
                rel_list: [
                    {title: 'No Referrer No Opener', value: 'noreferrer noopener'}
                ],                
                plugins: 'link image charmap lists table paste wordcount',
                toolbar: [
                    'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify'
                ],
                paste_postprocess: function(plugin, args) {
                    var p = $('p', args.node);
                    p.each(function(i, el){
                        $(el).css('line-height', 'inherit');
                    });
                }
            });
        });
        </script>
        EOF;
        $longDescRow->append(new CustomElement("{$pre}_long_description", "Description", "", $longDescEditorHTML, VALIDATE_NOTHING));
              
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
        if(count($challenges) === 0){
            $challengeFieldSet->hide();
        }
        if(!$config->getValue("projectLongDescription")){
            $longDescRow->hide();
        }
        if(!$config->getValue("showSubProject")){
            $subprojectRow->hide();
            $subprojectDDRow->hide();
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

        $sectionMap = $config->getValue('projectSectionMap');
        if ($sectionMap && is_array($sectionMap)) {
            $overviewHeaderRow = new FormTableRow("{$pre}_overview_header_row");
            $overviewHeaderRow->append(new Label("{$pre}_overview_header_label", "Project Overview", ""));
            $overviewHeaderRow->append(new CustomElement("{$pre}_overview_header_dummy", "", "", ""));
            $table->append($overviewHeaderRow);

            $overviewContentRow = new FormTableRow("{$pre}_overview_content_row");
            $overviewContentRow->append(new CustomElement("{$pre}_overview_spacer", "", "", ""));

            $editorsHtml = "";
            foreach ($sectionMap as $key => $value) {
                $title = htmlspecialchars($value[0]);
                $textareaName = "{$pre}_description[{$key}]";
                $editorsHtml .= <<<EOF
                    <div style="margin-bottom: 5px; font-weight: bold;">{$title}:</div>
                    <textarea name="{$textareaName}" style="height: 200px; width: 100%; margin-bottom: 20px;"></textarea>
                    <script type="text/javascript">
                    $(function() {
                        $('textarea[name="{$textareaName}"]').tinymce({
                            theme: 'modern',
                            relative_urls : false,
                            convert_urls: false,
                            menubar: false,
                            default_link_target: '_blank',
                            rel_list: [
                                {title: 'No Referrer No Opener', value: 'noreferrer noopener'}
                            ],
                            plugins: 'link image charmap lists table paste wordcount',
                            toolbar: [
                                'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify'
                            ],
                            paste_postprocess: function(plugin, args) {
                                var p = $('p', args.node);
                                p.each(function(i, el){
                                    $(el).css('line-height', 'inherit');
                                });
                            }
                        });
                    });
                    </script>
                EOF;
            }
            $overviewContentRow->append(new CustomElement("{$pre}_overview_editors", "", "", $editorsHtml, VALIDATE_NOTHING));
            $table->append($overviewContentRow);
        }

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
            $form->getElementById("new_long_description")->setPOST("long_description");
            $form->getElementById("new_challenge")->setPOST("challenge");
            $form->getElementById("new_parent_id")->setPOST("parent_id");

            if (isset($_POST['new_description']) && is_array($_POST['new_description'])) {
                foreach ($_POST['new_description'] as $key => $value) {
                    $_POST["description{$key}"] = $value;
                }
            }

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
