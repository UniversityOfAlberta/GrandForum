<?php

class PersonGrantTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonGrantTab($person, $visibility){
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        parent::AbstractTab("Grant");
	$this-> html .= "hello world";
	return $this->html;
    }
}
?>

