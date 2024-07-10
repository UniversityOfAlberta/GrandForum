<?php

class ProjectList extends MultiColumnVerticalCheckBox {
    
    function __construct($id, $name, $value, $options, $validations=VALIDATE_NOTHING){
        parent::__construct($id, $name, $value, $options, $validations);
        $this->attr('expand', false);
        $this->attr('reasons', true);
    }
    
    function render(){
        $reasons = $this->attr('reasons');
        $partialId = str_replace("_wpNS", "", $this->id);
        $html = "";
        $projects = $this->options;
        $otherThemes = 
        $themes = array();
        $otherThemes = array();
        foreach($projects as $project){
            $challenges = $project->getChallenges();
            foreach($challenges as $theme){
                if($theme->getAcronym() == "Not Specified" || $theme->getAcronym() == ""){
                    $otherThemes[] = $project;
                } else {
                    $themes["{$theme->getAcronym()} - {$theme->getName()}"][] = $project;
                }
            }
        }
        ksort($themes);
        if(count($otherThemes) > 0){
            if(count($themes) == 0){
                $themes[""] = $otherThemes;
            }
            else{
                $themes["Other"] = $otherThemes;
            }
        }
        $i = 0;
        foreach($themes as $theme => $projs){
            $count = ceil(count($projs)/3);
            $i = 0;
            $html .= "<div><div style='height:29px;line-height:29px;'><b>{$theme}</b></div>";
            foreach($projs as $key => $proj){
                if($i == 0){
                    $html .= "<div style='display:inline-block;margin-right:75px;vertical-align:top;'>";
                }
                $checked = "";
                if(count($this->value) > 0){
                    foreach($this->value as $value){
                        if($value == $proj->getName()){
                            $checked = " checked";
                            break;
                        }
                    }
                }
                $display = "none";
                if($checked != ""){
                    $display = "block";
                }
                $already = "";
                if($checked != ""){
                    $already = "already";
                }
                $html .= "<div>
                            <input class='{$this->id} {$already}' {$this->renderAttr()} type='checkbox' id='{$this->id}_{$proj->getName()}' name='{$this->id}[]' value='{$proj->getName()}' $checked />{$proj->getName()}";
                if($checked != "" && $reasons !== false){
                    $html .="<div style='display:none; padding-left:30px;'>
                                <fieldset><legend>Reasoning</legend>
                                    <p>Date Effective:<input type='text' class='datepicker' id='{$this->id}_datepicker{$proj->getName()}' name='{$partialId}_datepicker[{$proj->getName()}]' /></p>
                                    Additional Comments:<br />
                                    <textarea name='{$partialId}_comment[{$proj->getName()}]' cols='15' rows='4' style='height:auto;'></textarea>
                                </fieldset>
                             </div>";
                }
                $html .= "<div class='subprojects' style='margin-left:15px;display:$display;'>";
                foreach($proj->getSubProjects() as $subProj){
                    $subchecked = "";
                    if(!$subProj->isDeleted()){
                        if(count($this->value) > 0){
                            foreach($this->value as $value){
                                if($value == $subProj->getName()){
                                    $subchecked = " checked";
                                    break;
                                }
                            }
                        }
                        $already = "";
                        if($subchecked != ""){
                            $already = "already";
                        }
                        $html .= "<input class='{$this->id} {$already}' {$this->renderAttr()} type='checkbox' id='{$this->id}_{$subProj->getName()}' name='{$this->id}[]' value='{$subProj->getName()}' $subchecked />{$subProj->getName()}";
                        if($subchecked != "" && $reasons !== false){
                            $html .= "<div style='display:none; padding-left:30px;'>
                                        <fieldset><legend>Reasoning</legend>
                                            <p>Date Effective:<input type='text' class='datepicker' id='{$this->id}_datepicker{$subProj->getName()}' name='{$partialId}_datepicker[{$subProj->getName()}]' /></p>
                                            Additional Comments:<br />
                                            <textarea name='{$partialId}_comment[{$subProj->getName()}]' cols='15' rows='4' style='height:auto;' ></textarea>
                                            </fieldset>
                                      </div>";
                        }
                        $html .= "<br />";
                    }
                }
                $html .= "</div></div>";
                $i++;
                if($i == $count || $key == count($projs)-1){
                    $i=0;
                    $html .= "</div>";
                }
            }
            $html .= "</div>";
        }
        if($i != 0){
            $html .= "</div>";
        }
        if(!$this->attr('expand')){
            $html .= "<script type='text/javascript'>
                $('input.{$this->id}').change(function(){
                    if($('.subprojects input', $(this).parent()).length > 0){
                        if($('.subprojects', $(this).parent()).css('display') == 'block'){
                            $('.subprojects input', $(this).parent()).prop('checked', false);
                        }
                        $('.subprojects', $(this).parent()).slideToggle('fast');
                    }
                });
            </script>";
        }
        else{
            $html .= "<script type='text/javascript'>
                $(document).ready(function(){
                    $('.subprojects', $('input.{$this->id}').parent()).show();
                });
            </script>";
        }
        return $html;
    }
    
}

?>
