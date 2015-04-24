<?php

abstract class AbstractSurveyTab {

    var $html;
    var $id;
    var $name;
    var $title;
    var $warnings;

    function AbstractSurveyTab($name){
        //$this->id = str_replace(" ", "-", strtolower($name));
        $this->id = htmlentities(str_replace(" ", "-", strtolower($name)));
        $this->name = $name;
        $this->html = "";
        $this->saved = 0;
        $this->warnings = false;
    }

    function getCompleted(){
        global $wgUser;
        $my_id = $wgUser->getId();

        $sql = "SELECT completed FROM survey_results WHERE user_id = {$my_id}";
        $data = DBFunctions::execSQL($sql);
        
        $completed = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0);
        if(isset($data[0])){
            $json = json_decode($data[0]['completed'], true);
            $completed = (!is_null($json))? $json : $completed;
        }

        return $completed;
    }

    static function isSubmitted(){
        global $wgUser;
        $my_id = $wgUser->getId();
        
        $sql = "SELECT submitted FROM survey_results WHERE user_id='{$my_id}'";
        $data = DBFunctions::execSQL($sql);

        if(isset($data[0]) && $data[0]['submitted'] == 1){
            return true;
        }
        else{
            return false;
        }
        
        return true;
    }
}

?>
