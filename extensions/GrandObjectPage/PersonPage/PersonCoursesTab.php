<?php

class PersonCoursesTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonCoursesTab($person, $visibility){
        parent::AbstractTab("Courses");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        if(!$this->visibility['isMe']){
                return "";
        }
        $contributions = $this->person->getContributions();
           $this->html .= "<table id='courses_table' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Title</th>
                        <th style='white-space:nowrap;'>Number</th>
                        <th style='white-space:nowrap;'>Catalog Description</th>
                        <th style='white-space:nowrap;'>USRIs</th></tr></thead><tbody>";
        $this->html .= "</table></tbody><script type='text/javascript'>
                        $('#courses_table').dataTable();
        </script>";
    }
}
?>


