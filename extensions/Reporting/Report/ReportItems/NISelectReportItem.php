<?php

class NISelectReportItem extends SelectReportItem {

    function NISelectReportItem(){
        parent::SelectReportItem();
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
        $wgOut->addHTML("<script type='text/javascript'>
            $(\"select[name='{$this->getPostId()}']\").attr('data-placeholder', 'Choose ".NI."...');
            $(\"select[name='{$this->getPostId()}']\").chosen();
        </script>");
    }
	
}

?>
