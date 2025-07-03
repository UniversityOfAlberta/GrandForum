<?php

class HeadCell extends Cell{
    
    var $footnotes = array();
    var $tooltip = "";
    
    static $nFootnotes = 0;
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        if(isset($params[0])){
            $this->value = $params[0];
            foreach($params as $key => $param){
                if(strcmp($key,"tooltip") == 0){
                    $this->tooltip = $params[$key];
                }
                else if($key > 0){
                    $this->footnotes[$key-1] = $params[$key];
                }
            }
        }
        else{
            $this->value = $cellValue;
        }
    }
    
    function rasterize(){
        return array(HEAD, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        $this->style = 'text-align:center;';
        $superScript = "";
        $tooltip = "";
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
        
        if($this->tooltip != ""){
            $tooltip = "class='tooltip' title='{$this->tooltip}'";
        }
        return "<b $tooltip>{$this->value}</b>$superScript";
    }
}

?>
