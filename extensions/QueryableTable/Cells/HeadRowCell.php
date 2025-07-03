<?php

class HeadRowCell extends HeadCell{
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        parent::__construct($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(HEAD_ROW, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        $this->style = 'text-align:center;background-color:#DDDDDD;';
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
