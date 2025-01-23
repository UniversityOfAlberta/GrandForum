<?php

class NISelectReportItem extends SelectReportItem {

    function __construct(){
        parent::__construct();
        $nis = Person::getAllPeople(NI);
        $names = array("");
        foreach($nis as $ni){
            $names[] = $ni->getNameForForms();
        }
        $this->attributes['options'] = implode("|", $names);
        $this->attributes['width'] = '';
    }
    
    function render(){
        global $wgOut;
        parent::render();
        $placeholder = $this->getAttr('placeholder', 'Choose '.NI.'...');
        $wgOut->addHTML("<script type='text/javascript'>
            $(\"select[name='{$this->getPostId()}']\").attr('data-placeholder', '{$placeholder}');
            $(\"select[name='{$this->getPostId()}']\").chosen();
        </script>");
    }
	
}

?>
