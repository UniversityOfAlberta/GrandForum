<?php

class ComboBoxReportItem extends SelectReportItem {

    function render(){
        global $wgOut;
        if(@strstr($this->attributes['options'], $this->getBlobValue()) === false){
            $this->attributes['options'] .= "|{$this->getBlobValue()}";
        }
        parent::render();
        $wgOut->addHTML("<script type='text/javascript'>
            $('select[name=\"{$this->getPostId()}\"]').combobox();
        </script>");
    }
        
}

?>
