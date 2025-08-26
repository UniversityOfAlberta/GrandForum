<?php

class RoleProjectCheckBox extends VerticalCheckBox {
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $options, $validations);
    }
    
    function render(){
        global $config;
        $me = Person::newFromWgUser();
        $html = "";
        foreach($this->options as $key => $option){
            $label = $option;
            if(!is_numeric($key)){
                $label = $key;
            }
            $checked = "";
            if(count($this->value) > 0){
                foreach($this->value as $value){
                    if($value == $option){
                        $checked = " checked";
                        break;
                    }
                }
            }
            $html .= "<input {$this->renderAttr()} id='{$this->id}_{$option}' style='vertical-align:middle;' type='checkbox' name='{$this->id}[]' value='{$option}' $checked/><span style='vertical-align:middle; margin-left:0.5em;'>{$label}</span><br />";
            
            // Projects
            $projects = Project::getAllProjects();
            foreach($projects as $key => $project){
                if($project->getStatus() == "Proposed"){
                    unset($projects[$key]);
                }
                else if(!$me->isRoleAtLeast(STAFF) && !$person->isMemberOfDuring($project, "0000-00-00", EOT)){
                    unset($projects[$key]);
                }
            }
            $projectsField = new ProjectList("project_field", "Associated ".Inflect::pluralize($config->getValue('projectTerm')), array(), $projects, VALIDATE_NOTHING);
            $projectsField->role = $option;
            $html .= "<div id='{$this->id}_{$option}_projects' style='margin-left: 3px; display: none; margin-bottom: 1em;'><fieldset><legend>"."Associated ".Inflect::pluralize($config->getValue('projectTerm'))."</legend>{$projectsField->render()}</fieldset></div>";
        }
        $html .= "<script type='text/javascript'>
            $(\"[name='{$this->id}[]']\").change(function(){
                var value = $(this).val();
                if($(this).is(':checked')){
                    $('#{$this->id}_' + value + '_projects').slideDown();
                }
                else{
                    $('#{$this->id}_' + value + '_projects input').prop('checked', false);
                    $('#{$this->id}_' + value + '_projects').slideUp();
                }
            }).change();
        </script>";
        return $html;
    }
    
}


?>
