<?php

class Head2RowCell extends HeadCell{
    
    function Head2RowCell($cellType, $params, $cellValue, $rowN, $colN, $table){
        parent::HeadCell($cellType, $params, $cellValue, $rowN, $colN, $table);
    }
    
    function rasterize(){
        return array(HEAD2_ROW, $this);
    }
    
    function toString(){
        return $this->value;
    }
    
    function render(){
        $this->style = 'background-color:#DDDDDD;';
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
                $superScript .= "<sup title='$foot' class='tooltip'>[".Head1RowCell::$nFootnotes."]</sup>";
            }
        }
        
        if($this->tooltip != ""){
            $tooltip = "class='tooltip' title='{$this->tooltip}'";
        }
        return "&nbsp;&nbsp;&nbsp;<b $tooltip>{$this->value}</b>$superScript";
    }
}

?>
