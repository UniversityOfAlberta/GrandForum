<?php
/**
 * This Class is designed to allow for a queryable Budget.  
 * Functions like select, where, join and cube etc. can be used to create different types of budgets.
 * NOTE: This class creates an immutable object(unless accessing the instance variables directly),
 * which creates some performance overheads.  It would be useful in the future to have a 'BudgetBuilder'
 * which would be a mutable version of this class.
 * TODO: It would be extremely useful to have an SQL like parser for Budgets.  This could code which uses this class
 * cleaner, as well as it might be able to execute the actions faster since it could do some query optimizations.
 */
require_once("BudgetTypes.php");
require_once("MultiBudget.php");

class Budget extends QueryableTable{
    
    function __construct(){
        $this->id = "budget";
        $argv = func_get_args();
        switch(func_num_args()){
            case 0:
                self::EmptyBudget();
                break;
            case 1:
                self::FreeBudget($argv[0]);
                break;
            case 2:
                self::DerivedBudget($argv[0], $argv[1]);
                break;
            case 3:
                if($argv[0] == "CSV"){
                    self::CSVBudget($argv[1], $argv[2]);
                }
                else{
                    self::Budget($argv[1], $argv[2], 0);
                }
                break;
            case 4:
                self::Budget($argv[1], $argv[2], $argv[3]);
                break;
        }
    }
    
    // Creates a new Budget with no Structure (Structure is created on the fly)
    private function FreeBudget($data){
        global $budgetStructures;
        $this->QueryableTable();
        if(!$this->readCells($data)){
            // Some error happened when reading the data, try to recover
            $data = array();
            foreach($this->structure as $rowN => $row){
                $data[$rowN] = array();
                foreach($row as $colN => $cell){
                    $data[$rowN][$colN] = "";
                }
            }
            self::DerivedBudget($this->structure, $data);
            $this->errors[-1][-1] = "Error Reading File";
        }
    }
    
    // Creates a new Budget instance with the given person ID, structure type, and data set
    private function Budget($structure, $data, $sheet=0){
        global $budgetStructures;
        $this->QueryableTable();
        if(is_array($structure)){
            $this->structure = $this->preprocessStructure($structure);
        }
        else{
            $this->structure = @$this->preprocessStructure($budgetStructures[$structure]);
        }
        if(!$this->readCells($data, $sheet)){
            // Some error happened when reading the data, try to recover
            $data = array();
            foreach($this->structure as $rowN => $row){
                $data[$rowN] = array();
                foreach($row as $colN => $cell){
                    $data[$rowN][$colN] = "";
                }
            }
            self::DerivedBudget($this->structure, $data);
            $this->errors[-1][-1] = "Error Reading File";
        }
    }
    
    private function CSVBudget($structure, $data){
        global $budgetStructures;
        $this->QueryableTable();
        $data = str_replace("\r", '', $data);
        $data = str_replace('$', '', $data);
        $data = str_replace('%', '', $data);
        $data = utf8_encode($data);
        $data = utf8_decode(preg_replace('/[^\x{0000}-\x{007F}]/', ' ', $data));
        $data = preg_replace('/\s\s+/', ' ', $data);
        $rows = explode("\n", $data);
        $matrix = array();
        foreach($rows as $rowN => $row){
            $row = str_getcsv($row);
            foreach($row as $colN => $cell){
                $colN1 = $colN;
                $matrix[$rowN][$colN] = $cell;
            }
        }
        self::DerivedBudget($budgetStructures[$structure], $matrix);
    }
    
    // Creates a single cell'd budget, with an empty cell
    private function EmptyBudget(){
        $this->QueryableTable();
        $this->errors = array();
        $this->xls = array(array(new ReadCell("", "", "", 0, 0, $this)));
        $this->structure = array(array(READ));
    }
    
    // Used for derived tables
    private function DerivedBudget($structure, $matrix){
        $this->QueryableTable();
        $this->structure = $this->preprocessStructure($structure);
        $this->errors = array();
        $this->xls = array();
        foreach($this->structure as $rowN => $row){
            foreach($row as $colN => $cell){
                if(isset($matrix[$rowN][$colN])){
                    $params = array();
                    $origCellValue = $matrix[$rowN][$colN];
                    if(!is_numeric($cell)){
                        $splitCell = explode('(', $cell);
                        $cell = $splitCell[0];
                        $params = explode(',', str_replace(', ', ',', str_replace(')', '', $splitCell[1])));
                    }
                    else if($origCellValue instanceof Cell && count($origCellValue->params) > 0){
                        $params = $origCellValue->params;
                    }
                    $cellValue = $this->processCell($cell, $params, $origCellValue, $rowN, $colN);
                    if(!($cellValue instanceof NACell)){
                        $this->xls[$rowN][$colN] = $cellValue;
                    }
                }
            }
        }
    }
    
    // Reads the cells in the budget based on the specified structure
    private function readCells($data, $sheet=0){
        $dir = dirname(__FILE__);
        require_once($dir . '/../../../Classes/PHPExcel/IOFactory.php');
        if(!($data instanceof PHPExcel)){
            // 1. Create a temporary file and write the spreadsheet data into the file,
            // so that PHPExcel can use it.
            $tmpn = tempnam(sys_get_temp_dir(), 'XLS');
            if ($tmpn === false) {
                // Failed to reserve a temporary file.
                echo "Could not reserve temp file.";
                return false;
            }
            $tmpf = fopen($tmpn, 'w');
            if ($tmpf === false) {
                "Could not create temp file.";
                // TODO: log?
                unlink($tmpn);
                return false;
            }

            if (fwrite($tmpf, $data) === false) {
                // TODO: log?
                // Error writing to temporary file.
                echo "Could not write to temp file.";
                fclose($tmpf);
                unlink($tmpn);
                return false;
            }
            fclose($tmpf);
        }
        
        // 2. Instantiate the file as a PHPExcel IO object.
        try {
            if(!($data instanceof PHPExcel)){
                $objReader = PHPExcel_IOFactory::createReaderForFile($tmpn);
                $class = get_class($objReader);

                if($class != "PHPExcel_Reader_Excel5" && $class != "PHPExcel_Reader_Excel2007"){
                    return false;
                }
                $objReader->setReadDataOnly(true);
                $obj = $objReader->load($tmpn);
                $obj->setActiveSheetIndex($sheet);
            }
            else{
                $obj = $data;
            }
            
            $sheet = $obj->getActiveSheet();
            $maxCol = $sheet->getHighestColumn();
            $maxRow = $sheet->getHighestRow();
            $cells = @$sheet->toArray(null, true, false); // Explicitely read only values not style (3rd arg)
            
            if($this->structure == null){
                // Create a fake structure so that it doesn't fail
                $this->structure = array();
                foreach($cells as $rowN => $row){
                    foreach($row as $colN => $col){
                        $this->structure[$rowN][$colN] = READ;
                    }
                }
            }
            $rowN = 0;
            foreach($this->structure as $row){
                $colN = 0;
                if($maxRow < $rowN + 1){
                    break;
                }
                foreach($row as $cell){
                    if(ord($maxCol) < $colN + 1){
                        break;
                    }
                    $origCellValue = @$cells[$rowN][$colN];
                    $splitCell = explode("(", $cell);
                    $params = array();
                    $cell = $splitCell[0];
                    if(count($splitCell) > 1){
                        $params = explode(',', str_replace(', ', ',', str_replace(')', '', $splitCell[1])));
                    }
                    //$origCellValue = utf8_encode($origCellValue);
                    //$origCellValue = utf8_decode(preg_replace('/[^\x{0000}-\x{007F}]/', ' ', $origCellValue));
                    $origCellValue = preg_replace('/\s\s+/', ' ', $origCellValue);
                    $origCellValue = trim($origCellValue);
                    $cellValue = $this->processCell($cell, $params, $origCellValue, $rowN, $colN);
                    if(!($cellValue instanceof NACell)){
                        $this->xls[$rowN][$colN] = $cellValue;
                    }
                    ++$colN;
                }
                ++$rowN;
            }
            if(!($data instanceof PHPExcel)){
                $obj->disconnectWorksheets();
                PHPExcel_Calculation::getInstance()->clearCalculationCache();
                unset($objReader);
                unset($obj);
            }
        }
        catch (Exception $e) {
            // File is probably encrypted
            $this->errors[0][] = "There was an error reading this worksheet";
            $this->structure = array();
            $this->xls = array();
        }
        if(!($data instanceof PHPExcel)){
            unlink($tmpn);
        }
        return true;
    }
    
    static function union_tables($tables){
        return QueryableTable::union_t($tables, "Budget");
    }
    
    static function join_tables($tables){
        return QueryableTable::join_t($tables, "Budget");
    }
    
    // Returns a single cell'd budget containing the sum of all the cells in the budget
    function sum(){
        $total = 0;
        foreach($this->xls as $rowN => $row){
            foreach($row as $colN => $cell){
                if(is_numeric($cell->getValue()) && $cell->summable){
                    $total += $cell->getValue();
                }
            }
        }
        $this->xls = array(array(new MoneyCell("", "", $total, "", "", "")));
        $this->structure = array(array(MONEY));
        $this->updateDynamic();
        return $this;
    }
    
    // Returns a cube aggregate budget.
    function cube(){
        $rowTotalResultSet = array();
        $rowTotalStructure = array();
        $colTotalResultSet = array();
        $colTotalStructure = array();
        $totalResultSet = array();
        $totalStructure = array();
        foreach($this->structure as $rowN => $row){
            foreach($row as $colN => $col){
                if(!isset($rowTotalResultSet[$rowN][0])){
                    $rowTotalResultSet[$rowN][0] = 0;
                    $rowTotalStructure[$rowN][0] = BLANK;
                }
                if(!isset($colTotalResultSet[0][$colN])){
                    $colTotalResultSet[0][$colN] = 0;
                    $colTotalStructure[0][$colN] = BLANK;
                }
                if(!isset($totalResultSet[0][0])){
                    $totalResultSet[0][0] = 0;
                    $totalStructure[0][0] = CUBE_TOTAL;
                }
                if(isset($this->xls[$rowN][$colN]) &&
                   is_numeric($this->xls[$rowN][$colN]->getValue())){
                    $cell = $this->xls[$rowN][$colN]->getValue();
                    if($this->xls[$rowN][$colN]->summable){
                        $rowTotalResultSet[$rowN][0] += $cell;
                        $colTotalResultSet[0][$colN] += $cell;
                        $totalResultSet[0][0] += $cell;
                    }
                    $rowTotalStructure[$rowN][0] = CUBE_ROW_TOTAL;
                    $colTotalStructure[0][$colN] = CUBE_COL_TOTAL;
                }
            }
        }
        $rowBudget = new Budget($rowTotalStructure, $rowTotalResultSet);
        $colBudget = new Budget($colTotalStructure, $colTotalResultSet);
        $totalBudget = new Budget($totalStructure, $totalResultSet);
        $copy = $this->copy();
        $cubedBudget = $copy->join($rowBudget)->union($colBudget->join($totalBudget));
        if($cubedBudget->structure[$this->nRows()][0] == BLANK){
            $cubedBudget->structure[$this->nRows()][0] = HEAD1;
            $cubedBudget->xls[$this->nRows()][0] = new Head1Cell("", "", "TOTAL", "", "", "");
        }
        if($cubedBudget->structure[0][$this->nCols()] == BLANK){
            $cubedBudget->structure[0][$this->nCols()] = HEAD;
            $cubedBudget->xls[0][$this->nCols()] = new HeadCell("", "", "TOTAL", "", "", "");
        }
        return $cubedBudget;
    }
    
    function uncube(){
        $copy = $this->copy();
        $copy->limit(0, $copy->nRows()-1);
        $copy->limitCols(0, $copy->nCols()-1);
        return $copy;
    }
    
    function renderForPDF($sortable=false){
        $dom = new SmartDOMDocument();
        $errorMsg = "";
        if($this->isError()){
            $errors = $this->showErrorsSimple();
            $errors = str_replace("<br />", "</span><br /><span class='inlineError'>", $errors);
            $errors = str_replace("<br /><span class='inlineError'></span>", "", $errors);
            $errorMsg = "<span class='inlineError'>$errors</span><br /><br />";
        }
        $dom->loadHTML($errorMsg.$this->render());
        $tabs = $dom->getElementsByTagName("table");
        foreach($tabs as $tab){
            foreach($tab->getElementsByTagName("td") as $td){
                $td->removeAttribute('width');
                $td->removeAttribute('nowrap');
                /*if(strstr($td->getAttribute('class'), "explicitSpan") === false){
                    $td->setAttribute('colspan', '1');
                }*/
                if(strstr($td->getAttribute('class'), "budgetError") === false && strstr($td->getAttribute('style'), "background") === false){
                    $td->setAttribute('style', $td->getAttribute('style').'background-color:#FFFFFF;');
                }
                $td->setAttribute('style', $td->getAttribute('style')."padding-top:".max(1, (0.5*DPI_CONSTANT))."px;padding-bottom:".max(1, (0.5*DPI_CONSTANT))."px;");
                $td->setAttribute('style', str_replace("width:6em;", "", $td->getAttribute('style')));
            }
            
            $tab->removeAttribute('rules');
            $tab->removeAttribute('boxes');
            $tab->removeAttribute('frame');
            $tab->setAttribute('style', "width:100%;background-color:#000000;border-color:#000000;margin-bottom:15px;border-spacing:".max(1, (0.5*DPI_CONSTANT))."px;");
            $tab->setAttribute('width', '100%');
            $tab->setAttribute('cellpadding', '1');
            $tab->setAttribute('cellspacing', '1');
            $tab->parentNode->removeAttribute('style');
        }
        $html = "$dom";
        return $html;
    }
}
?>
