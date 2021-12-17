<?php

class Head1Cell extends Cell{
    
    var $footnotes = array();
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        if(isset($params[0])){
            $this->value = $params[0];
            foreach($params as $key => $param){
                if($key > 0){
                    $this->footnotes[$key-1] = $params[$key];
                }
            }
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        return array(HEAD1, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        $superScript = "";
        foreach($this->footnotes as $foot){
            if(class_exists("Report")){
                FootnoteReportItem::$nFootnotes++;
                $superScript .= "<sup title='$foot' class='tooltip'>[".FootnoteReportItem::$nFootnotes."]</sup>";
                PDFGenerator::addFootNote($foot);
            }
            else{
                self::$nFootnotes++;
                $superScript .= "<sup title='$foot' class='tooltip'>[".HeadCell::$nFootnotes."]</sup>";
            }
        }
        return "<b>{$this->value}</b>$superScript";
    }
}

?>
