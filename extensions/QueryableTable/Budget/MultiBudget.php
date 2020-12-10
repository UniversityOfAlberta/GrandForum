<?php

class MultiBudget {
    
    var $budgets = array();
    var $sheetNames = array();
    
    function MultiBudget($structures, $data){
        $dir = dirname(__FILE__);
        require_once($dir . '/../../../Classes/PHPExcel/IOFactory.php');
        if($data == null || $data == ""){
            return false;
        }
        $tmpn = tempnam(sys_get_temp_dir(), 'XLS');
        if ($tmpn === false) {
            // Failed to reserve a temporary file.
            return false;
        }
        $tmpf = fopen($tmpn, 'w');
        if ($tmpf === false) {
            // TODO: log?
            unlink($tmpn);
            return false;
        }

        if (fwrite($tmpf, $data) === false) {
            // TODO: log?
            // Error writing to temporary file.
            fclose($tmpf);
            unlink($tmpn);
            return false;
        }
        fclose($tmpf);

        $objReader = PHPExcel_IOFactory::createReaderForFile($tmpn);
        $class = get_class($objReader);
        if($class != "PHPExcel_Reader_Excel5" && $class != "PHPExcel_Reader_Excel2007"){
            return;
        }
        $objReader->setReadDataOnly(true);
        $obj = $objReader->load($tmpn);
        $sheets = $obj->getAllSheets();
        $this->sheetNames = array_values($obj->getSheetNames());
        for($i=0; $i<count($sheets); $i++){
            $structure = $structures[min($i, count($structures)-1)];
            $obj->setActiveSheetIndex($i);
            $this->budgets[] = new Budget("XLS", $structure, $obj);
        }
        $obj->disconnectWorksheets();
        PHPExcel_Calculation::getInstance()->clearCalculationCache();
        unset($objReader);
        unset($obj);
        unlink($tmpn);
    }
    
    function getBudgets(){
        return $this->budgets;
    }
    
    function getBudget($i){
        return $this->budgets[min($i,$this->nBudgets()-1)];
    }
    
    function nBudgets(){
        return count($this->budgets);
    }
    
    function render(){
        $ret = "<div class='multiBudget'>";
        foreach($this->budgets as $key => $budget){
            if($key == 0 && $this->nBudgets() > 1){
                // First
                $ret .= "<div>";
                $ret .= "<a class='button disabledButton'>&lt;</a>&nbsp;
                         <a class='button' onClick='$(this).parent().hide();$(this).parent().next().show();'>&gt;</a>";
            }
            else if($key > 0 && $key < $this->nBudgets() - 1){
                // Middle
                $ret .= "<div style='display:none;'>";
                $ret .= "<a class='button' onClick='$(this).parent().hide();$(this).parent().prev().show();'>&lt;</a>&nbsp;
                         <a class='button' onClick='$(this).parent().hide();$(this).parent().next().show();'>&gt;</a>";
            }
            else if($key == 0 && $this->nBudgets() == 1){
                // Only Budget
                $ret .= "<div>";
                $ret .= "<a class='button disabledButton'>&lt;</a>&nbsp;
                         <a class='button disabledButton'>&gt;</a>";
            }
            else{
                // Last
                $ret .= "<div style='display:none;'>";
                $ret .= "<a class='button' onClick='$(this).parent().hide();$(this).parent().prev().show();'>&lt;</a>&nbsp;
                         <a class='button disabledButton'>&gt;</a>";
            }
            $ret .= "<h3 style='margin-left:30px;display:inline;'>{$this->sheetNames[$key]}</h3>";
            $ret .= "<div style='margin-top:3px;'>{$budget->render()}</div>";
            $ret .= "</div>";
        }
        $ret .= "</div>";
        return $ret;
    }
    
    function renderForPDF(){
        $ret = "";
        foreach($this->budgets as $key => $budget){
            $ret .= "<div style='page-break-inside: avoid;'>";
            $ret .= "<b>{$this->sheetNames[$key]}</b>";
            $ret .= $budget->renderForPDF();
            $ret .= "</div>";
        }
        return $ret;
    }
    
}

?>
