<?php
$cellTypes = array();
$arrayTypes = array();

// Basic Cell Types
define('NA', -100);
define('BLANK', -101);
define('READ', -102);
define('HEAD', -103);
define('HEAD_ROW', -104);
define('HEAD1', -105);
define('HEAD2', -106);
define('HEAD3', -107);
define('HEAD4', -108);
define('HEAD1_ROW', -109);
define('HEAD2_ROW', -110);
define('STRING', -111);
define('WRAP', -112);
// Complex Structure Types
define('GROUP_BY', -200);

$cellTypes[NA] = "NACell";
$cellTypes[BLANK] = "BlankCell";
$cellTypes[READ] = "ReadCell";
$cellTypes[WRAP] = "WrapCell";
$cellTypes[STRING] = "StringCell";
$cellTypes[HEAD] = "HeadCell";
$cellTypes[HEAD_ROW] = "HeadRowCell";
$cellTypes[HEAD1] = "Head1Cell";
$cellTypes[HEAD2] = "Head2Cell";
$cellTypes[HEAD3] = "Head3Cell";
$cellTypes[HEAD4] = "Head4Cell";
$cellTypes[HEAD1_ROW] = "Head1RowCell";
$cellTypes[HEAD2_ROW] = "Head2RowCell";

autoload_register('QueryableTable/Cells');
autoload_register('QueryableTable');

require_once('Budget/Budget.php');
require_once('DashboardTable/DashboardTable.php');
//require_once('MyQueryableTable/SpecialQueryableTable.php');

$nStructs = 0;

function STRUCT(){ // Useful for making multi-argument cell structures
    global $nStructs;
    $args = func_get_args();
    $nArgs = func_num_args();
    $cell = "";
    $params = array();
    for($i = 0; $i < $nArgs; $i++){
        $arg = $args[$i];
        if($i == 0){
            $cell = $args[0];
        }
        else{
            $params[] = $arg;
        }
    }
    if(count($params) > 0){
        $cell .= '('.implode(",", $params).")($nStructs)";
    }
    $nStructs++;
    return $cell;
}

/**
 * This Class is designed to allow for a queryable table.  
 * Functions like select, where, join etc. can be used to create different types of tables.
 * NOTE: This class creates a mutable object, which means that any queries made upon it will modify the data.
 * In most cases, a call to copy() should be made before any queries.
 * TODO: It would be extremely useful to have an SQL like parser for Budgets.  This could code which uses this class
 * cleaner, as well as it might be able to execute the actions faster since it could do some query optimizations.
 */
abstract class QueryableTable {
    
    static $idCounter = 0;
    
    var $id;
    var $structure;
    var $errors;
    var $xls;
    var $class;
    
    function QueryableTable(){
        self::$idCounter++;
        $this->class = get_class($this);
    }
    
    // Processes any complex structure elements
    protected function preprocessStructure($structure){
        global $arrayTypes;
        $newStructure = array();
        $rowOffset = 0;
        $colOffset = 0;
        $lastRowN = 0;
        foreach(@$structure as $rowN => $row){
            if(!is_numeric($rowN) || $rowN < 0){
                $params = array();
                if(!is_numeric($rowN)){
                    $splitRow = explode('(', $rowN);
                    $rowN = $splitRow[0];
                    $params = explode(',', str_replace(', ', ',', str_replace(')', '', $splitRow[1])));
                }
                switch($rowN){
                    case GROUP_BY:
                        if(count($params) >= 1){
                            $class = $arrayTypes[$params[0]];
                            $complex = new $class($this, $params);
                            $array = $complex->getArray();
                            $lastRowN++;
                            foreach($array as $item){
                                $newRow = array();
                                foreach($row as $colN => $cell){
                                    $cellParams = array();
                                    if(!is_numeric($cell)){
                                        $splitRow1 = explode('(', $cell);
                                        $cell = $splitRow1[0];
                                        $cellParams = explode(',', str_replace(', ', ',', str_replace(')', '', $splitRow1[1])));
                                    }
                                    $cellParams[] = $item;
                                    $cell .= '('.implode(',', $cellParams).')';
                                    $newRow[] = $cell;
                                }
                                $newStructure[$lastRowN + $rowOffset] = $newRow;
                                $rowOffset++;
                            }
                            $rowOffset++;
                        }
                        break;
                    default:
                        // Unknown Complex Structure type
                        $newStructure[$rowN + $rowOffset] = $row;
                        break;
                }
            }
            else{
                $newStructure[$rowN + $rowOffset] = $row;
                $lastRowN = $rowN;
            }
        }
        return $newStructure;
    }
    
    function copy(){
        $copy = new $this->class($this->structure, $this->xls);
        return $copy;
    }
    
    // Processes a single cell.  Checks for errors in that cell based on it's type.
    // If an error occurs, then it is added to the errors array.
    // Returns the processed Cell.
    function processCell($cellType, $params, $cellValue, $rowN, $colN){
        global $cellTypes;
        if($cellValue instanceof Cell){
            $cellValue = $cellValue->getValue();
        }
        if($cellValue == null){
            $cellValue = '';
        }
        $class = $cellTypes[$cellType];
        $cell = new $class($cellType, $params, $cellValue, $rowN, $colN, $this);
        $cell->params = $params;
        return $cell;
    }
    
    // Returns an associative array of parameters
    function parseParams($cell){
        $params = array();
        
        $chars = str_split($cell);
        $quoteFound = false;
        $strVars = array();
        $currentStr = "";
        foreach($chars as $key => $char){
            if($char == "\"" && ($key == 0 || $chars[$key-1] != "\\")){
                $quoteFound = !$quoteFound;
                if(!$quoteFound){
                    $strVars[] = str_replace("'", "&#39;", $currentStr."\"");
                    $currentStr = "";
                }
            }
            if($quoteFound){
                $currentStr .= $char;
            }
        }
        
        foreach($strVars as $key => $var){
            $cell = str_replace(str_replace("&#39;", "'", $var), "{\$".$key."}", $cell);
        }
        
        $splitCell = explode('(', $cell);
        $cell = $splitCell[0];
        $tmpParams = explode(',', str_replace(', ', ',', str_replace(')', '', $splitCell[1])));
        $params = array();
        $i = 0;
        foreach($tmpParams as $param){
            $exp = explode("=", $param);
            if(count($exp) > 1){
                $value = $exp[1];
                list($index) = sscanf($exp[1], "{\$%d}");
                if(isset($strVars[$index])){
                    $value = str_replace("\\\"", '"', substr($strVars[$index], 1, strlen($strVars[$index])-2));
                }
                $params[$exp[0]] = trim($value);
            }
            else{
                $value = $param;
                list($index) = sscanf($param, "{\$%d}");
                if(isset($strVars[$index])){
                    $value = str_replace("\\\"", '"', substr($strVars[$index], 1, strlen($strVars[$index])-2));
                }
                $params[$i++] = trim($value);
            }
        }
        return $params;
    }
    
    abstract static function union_tables($tables);
    
    abstract static function join_tables($tables);
    
    // Returns the status of the QueryableTable
    function status(){
        return ($this->xls !== false && !isset($this->errors[-1][-1]));
    }
    
    // Returns the number of columns in the QueryableTable
    function nCols(){
        $max = 0;
        foreach($this->structure as $rowN => $row){
            $max = max($max, count($this->structure[$rowN]));
        }
        return $max;
    }
    
    // Returns the number of rows in the QueryableTable
    function nRows(){
        return count($this->structure);
    }
    
    /**
     * Returns the number of cells in the QueryableTable
     * @return int The number of cells in the QueryableTabe
     */
    function size(){
        return $this->nCols()*$this->nRows();
    }
    
    // Updates the dynamic cells
    protected function updateDynamic(){
        foreach($this->xls as $rowN => $row){
            foreach($row as $colN => $cell){
                if($cell->dynamic){
                    $class = get_class($cell);
                    $this->xls[$rowN][$colN] = new $class("", "", "", $rowN, $colN, $this);
                }
            }
        }
    }
    
    // Query like functions below
	
	// Applies a projection on the xls sheet.
	// $key is a cell type, and will look at every instance of that cell type and compare it to $values
	// If there is a match, then that column is selected.
	function select($key, $values=array()){
	    $values = $this->parseValues($values);
        $resultSet = array();
        $structure = array();
        foreach($this->structure as $rowN => $row){
            $keys = array_keys($row, $key);
            foreach($keys as $colN){
                if(isset($this->xls[$rowN][$colN]) && $this->xls[$rowN][$colN]->getValue() != "" &&
                   (count($values) == 0 || $this->like($values, $this->xls[$rowN][$colN]->getValue()) !== false)){
                    $resultSet[$colN] = array_project($this->xls, $colN);
                    $structure[$colN] = array_project($this->structure, $colN);
                }
            }
        }
        $this->xls = array_transpose($resultSet);
        $this->structure = array_transpose($structure);
        $this->updateDynamic();
        return $this;
	}
	
	// Selects only the rows which have a cell which matches the key, values pair
	function where($key, $values=array()){
	    $values = $this->parseValues($values);
        $resultSet = array();
        $structure = array();
        foreach($this->structure as $rowN => $row){
            $keys = array_keys($row, $key);
            foreach($keys as $colN){
                if(isset($this->xls[$rowN][$colN]) && $this->xls[$rowN][$colN]->getValue() != "" &&
                   (count($values) == 0 || $this->like($values, $this->xls[$rowN][$colN]->getValue()) !== false)){
                    $resultSet[$rowN] = $this->xls[$rowN];
                    $structure[$rowN] = $this->structure[$rowN];
                    break;
                }
            }
        }
        $this->xls = $resultSet;
        $this->structure = $structure;
        $this->updateDynamic();
        return $this;
    }
    
    // Filters out all the rows which have a cell which matches the key, values pair
    function filter($key, $values=array()){
        $values = $this->parseValues($values);
        $resultSet = array();
        $structure = array();
        foreach($this->structure as $rowN => $row){
            $skip = true;
            foreach($row as $colN => $cell){
                if($cell == $key && (count($values) == 0 || $this->like($values, $this->xls[$rowN][$colN]->getValue()) !== false)){
                    $skip = true;
                    break;
                }
                else if($cell != NA){
                    $skip = false;
                }
            }
            if(!$skip){
                $resultSet[$rowN] = $this->xls[$rowN];
                $structure[$rowN] = $this->structure[$rowN];
            }
        }
        $this->xls = $resultSet;
        $this->structure = $structure;
        $this->updateDynamic();
        return $this;
    }
    
    // Filters out all the columns which have a cell which matches the key, values pair
    function filterCols($key, $values=array()){
        $this->transpose()->filter($key, $values)->transpose();
        return $this;
    }
    
    private function fillEmpty($table){
        if($this->nRows() < $table->nRows()){
            for($i = $this->nRows(); $i < $table->nRows(); $i++){
                $struct = array();
                $xls = array();
                for($j = 0; $j < $this->nCols(); $j++){
                    $struct[] = BLANK;
                    $xls[] = new BlankCell(BLANK, array(), "", $i, $j, $this);
                }
                $this->xls[$i] = $xls;
                $this->structure[$i] = $struct;
            }
        }
        return $table;
    }
	
	// Joins the two QueryableTables together
    function join($table, $fillEmpty=false){
        reset($table->structure);
        $resultSet = array();
        $structure = array();
        $rowNumber = 0;
        if($fillEmpty){
            $table = $this->fillEmpty($table);
        }
        foreach($this->structure as $nRow => $row){
            $colNumber = 0;
            foreach($row as $nCol => $cell){
                if(isset($this->xls[$nRow][$nCol])){
                    $resultSet[$rowNumber][$colNumber] = $this->xls[$nRow][$nCol];
                    $structure[$rowNumber][$colNumber] = $cell;
                    ++$colNumber;
                }
            }
            $row1 = current($table->structure);
            $nRow1 = key($table->structure);
            if($row1 !== false){
                foreach($row1 as $nCol1 => $cell){
                    if(isset($table->xls[$nRow1][$nCol1])){
                        $resultSet[$rowNumber][$colNumber] = $table->xls[$nRow1][$nCol1];
                        $structure[$rowNumber][$colNumber] = $cell;
                        ++$colNumber;
                    }
                }
                next($table->structure);
            }
            ++$rowNumber;
        }
        $this->xls = $resultSet;
        $this->structure = $structure;
        $this->updateDynamic();
        return $this;
    }
    
    // Stacks the $table below 'this' QueryableTable
    function union($table){
        $resultSet = array();
        $structure = array();
        $i = 0;
        $nCols1 = $this->nCols();
        $nCols2 = $table->nCols();
        foreach($this->structure as $rowN => $row){
            $j = 0;
            foreach($row as $colN => $cell){
                if(isset($this->xls[$rowN][$colN])){
                    $resultSet[$i][$j] = $this->xls[$rowN][$colN];
                }
                $structure[$i][$j] = $cell;
                ++$j;
            }
            for($extra = 0; $extra < ($nCols2 - $nCols1); ++$extra){
                $structure[$i][$j + $extra] = NA;
            }
            ++$i;
        }
        foreach($table->structure as $rowN => $row){
            $j = 0;
            foreach($row as $colN => $cell){
                if(isset($table->xls[$rowN][$colN])){
                    $resultSet[$i][$j] = $table->xls[$rowN][$colN];
                }
                $structure[$i][$j] = $cell;
                ++$j;
            }
            for($extra = 0; $extra < ($nCols1 - $nCols2); ++$extra){
                $structure[$i][$j + $extra] = NA;
            }
            ++$i;
        }
        $this->xls = $resultSet;
        $this->structure = $structure;
        $this->updateDynamic();
        return $this;
    }
    
    // Limits the number of rows which appear
    function limit($start, $amount){
        $resultSet = array();
        $structure = array();
        $i = 0;
        foreach($this->structure as $rowN => $row){
            if($i >= $start){
                if(isset($this->xls[$rowN])){
                    $resultSet[$rowN] = $this->xls[$rowN];
                }
                $structure[$rowN] = $this->structure[$rowN];
            }
            ++$i;
            if($i >= $amount + $start){
                break;
            }
        }
        $this->xls = $resultSet;
        $this->structure = $structure;
        $this->updateDynamic();
        return $this;
    }
    
    // Limits the number of cols which appear
    function limitCols($start, $amount){
        $resultSet = array();
        $structure = array();
        foreach($this->structure as $rowN => $row){
            $i = 0;
            foreach($row as $colN => $cell){
                if($i >= $start){
                    if(isset($this->xls[$rowN][$colN])){
                        $resultSet[$rowN][$colN] = $this->xls[$rowN][$colN];
                    }
                    $structure[$rowN][$colN] = $this->structure[$rowN][$colN];
                }
                ++$i;
                if($i >= $amount + $start){
                    break;
                }
            }
        }
        $this->xls = $resultSet;
        $this->structure = $structure;
        $this->updateDynamic();
        return $this;
    }
    
    function trim(){
        return $this->trimRows()->trimCols();
    }
    
    function trimCols(){
        $xls = array_transpose($this->xls);
        $structure = array_transpose($this->structure);     
        $firstNonEmpty = count($structure);
        $lastNonEmpty = 0;
        foreach($structure as $rowN => $row){
            $empty = true;
            foreach($row as $colN => $cell){
                if(isset($xls[$rowN][$colN]) && trim($xls[$rowN][$colN]->getValue()) != ""){
                    // Empty Cell
                    $empty = false;
                    break;
                }
            }
            if(!$empty){
                $firstNonEmpty = min($firstNonEmpty, $rowN);
                $lastNonEmpty = max($lastNonEmpty, $rowN);
            }
        }
        $this->limitCols($firstNonEmpty, ($lastNonEmpty - $firstNonEmpty)+1);
        return $this;
    }
    
    function trimRows(){
        $xls = $this->xls;
        $structure = $this->structure;
        $firstNonEmpty = count($structure);
        $lastNonEmpty = 0;
        foreach($structure as $rowN => $row){
            $empty = true;
            foreach($row as $colN => $cell){
                if(isset($xls[$rowN][$colN]) && trim($xls[$rowN][$colN]->getValue()) != ""){
                    // Empty Cell
                    $empty = false;
                    break;
                }
            }
            if(!$empty){
                $firstNonEmpty = min($firstNonEmpty, $rowN);
                $lastNonEmpty = max($lastNonEmpty, $rowN);
            }
        }
        $this->limit($firstNonEmpty, ($lastNonEmpty - $firstNonEmpty)+1);
        return $this;
    }
    
    // Returns a single cell'd QueryableTable containing the concatenated sheet
    function concat(){
        $str = $this->toString();
        $this->xls = array(array(new ReadCell("", "", $str, "", "", "")));
        $this->structure = array(array(READ));
        return $this;
    }
    
    // Returns a concatenated string version of the QueryableTable
    function toString(){
        $strings = array();
        foreach($this->xls as $rowN => $row){
            foreach($row as $colN => $cell){
                $type = $this->structure[$rowN][$colN];
                if($type != NA){
                    $strings[] = $cell->toString();
                }
            }
        }
        return implode(" ", $strings);
    }
    
    function isError(){
        $isError = (count($this->errors) > 0);
        if(is_array($this->xls)){
            foreach($this->xls as $rowN => $row){
		        foreach($row as $colN => $cell){
		            if($cell->error != ""){
			            $isError = true;
			            break;
			        }
			    }
		    }
		}
		else{
		    $isError = true;
		}
		return $isError;
    }
    
    function showErrorsSimple(){
        $ret = "";
        if(is_array($this->xls)){
            foreach($this->xls as $rowN => $row){
		        foreach($row as $colN => $cell){
		            if($cell->error != ""){
			            $ret .= "{$cell->error}<br />\n";
			        }
			    }
		    }
		    if(count($this->errors) > 0){
		        foreach($this->errors as $rowN => $rowErrors){
		            foreach($rowErrors as $colN => $error){
		                $ret .= "$error<br />\n";
		            }
		        }
		    }
		}
		else{
		    $ret .= "This is not a valid worksheet<br />\n";
		}
		$ret = substr($ret, 0, strlen($ret) - strlen("<br />\n"));
		return $ret;
    }
    
    // Shows the errors which arose during readCells()
	function showErrors(){
	    $ret = "<div class='pdfnodisplay'>";
	    if ($this->status()){
			if (!$this->isError()) {
				// All OK.
				/*$ret .= "<li>" .
					"Please verify that the budget preview below is correct. If not, please contact support.</li>";*/
			}
			else {
				$ret .= "<div class='error'>\n";
				$ret .= $this->showErrorsSimple();
				$ret .= "</div>\n";
			}
		}
		else {
			$ret .= "<div class='error'>The spreadsheet could not be read by the system.</div>";
		}
		$ret .= "</div>\n";
		
		return $ret;
	}
    
    // Renders the QueryableTable as an html table.  Cells are formatted based on their type
    function render($sortable=false){
        $ret = array();
        $ret[] = $this->showErrors();
        if($this->status()){
            $sort = "";
            if($sortable){
                $sort = "class='sortable'";
            }
            if(is_array($this->xls)){
                $ret[] = "<table id='{$this->id}' class='dashboard' style='background:#ffffff;border-style:solid;' cellspacing='1' cellpadding='3' frame='box' rules='all' $sort>\n";
                foreach($this->xls as $rowN => $row){
                    $ret[] = "<tr>\n";
                    $i = 0;
                    foreach($row as $colN => $cell){
                        $class = "";
                        $errorMsg = "";
                        $errorMsgEnd = "";
                        $style = "";
                        $Cell = $cell;
                        if($Cell->error != ""){
                            $class .= " budgetError";
                            $errorMsg = "<span title='{$colN},{$rowN}: {$Cell->error}' class='tooltip'>";
                            $errorMsgEnd = "</span>";
                        }
                        
                        $cell = nl2br($Cell->render());
                        $style = $Cell->style;
                        $span = 1;
                        if(!isset($row[$colN + 1])){
                            $span = max(1, $this->nCols() - $colN);
                        }
                        $span = 1;
                        for($i=$colN+1; $i < $this->nCols(); $i++){
                            $c = $this->structure[$rowN][$i];
                            if($c == NA){
                                $span++;
                            }
                            else{
                                break;
                            }
                        }
                        if($Cell->span != null){
                            $span = $Cell->span;
                            $class .= " explicitSpan";
                        }
                        if($Cell->wrap){
                            $ret[] = "<td style='width:3em;$style' class='$class' colspan='$span' class='smaller'>{$errorMsg}{$cell}{$errorMsgEnd}</td>\n";
                        }
                        else{
                            $ret[] = "<td nowrap='nowrap' style='width:3em;white-space:nowrap;$style' class='$class' colspan='$span' class='smaller'>{$errorMsg}{$cell}{$errorMsgEnd}</td>\n";
                        }
                        
                        ++$i;
                    }
                    $ret[] = "</tr>\n";
                }
                $ret[] = "</table>\n";
            }
        }
        return implode("", $ret);
	}
	
	function renderForPDF($sortable=false){
	    $html = str_get_html($this->render(), false, false, DEFAULT_TARGET_CHARSET, false); // Create the dom object
	    foreach($html->find('.pdfnodisplay') as $el){
	        $el->outertext = "";
	    }
	    foreach($html->find('table') as $table){
	        foreach($table->find('tbody > tr > td') as $td){
	            $td->width = null;
                $td->style = $td->style.'background-color:#FFFFFF;';
	        }
	        $table->rules = null;
            $table->boxes = null;
            $table->frame = null;
            $table->style = 'width:100%;background-color:#000000;margin-bottom:15px;border-spacing:1px;';
            $table->width = '100%';
            $table->cellpadding = '1';
            $table->cellspacing = '1';
            break;
	    }
	    $html->save();
	    return $html;
	}
	
	// Removes the previous structure, and converts everything to more basic types
    function rasterize(){
        foreach($this->xls as $rowN => $row){
            foreach($row as $colN => $cell){
                if($cell->dynamic){
                    $newCell = $cell->rasterize();
                    $this->structure[$rowN][$colN] = $newCell[0];
                    $this->xls[$rowN][$colN] = $newCell[1];
                }
            }
        }
        return $this;
    }
    
    // Switches the rows and the columns
    function transpose(){
        $structure = array();
        $resultSet = array();
        foreach(array_transpose($this->xls) as $rowN => $row){
            $resultSet[$rowN] = array();
            foreach($row as $colN => $col){
                $resultSet[$rowN][$colN] = $col;
            }
        }
        
        foreach(array_transpose($this->structure) as $rowN => $row){
            $structure[$rowN] = array();
            foreach($row as $colN => $col){
                $structure[$rowN][$colN] = $col;
            }
        }
        $this->xls = $resultSet;
        $this->structure = $structure;
        $this->updateDynamic();
        return $this;
    }
    
    // Returns a single cell'd QueryableTable containing the number of cells in the Table
    function count(){
        $total = 0;
        foreach($this->xls as $rowN => $row){
            $total += count($this->xls[$rowN]);
        }
        $this->xls = array(array(new ReadCell("", "", $total, "", "", "")));
        $this->structure = array(array(READ));
        return $this;
    }
    
    // Recursivly joins the array of QueryableTables
    static protected function join_t($tables, $class){
        $chunks = array_chunk($tables, 2);
        $newChunks = array();
        foreach($chunks as $key => $chunk){
            if(count($chunk) == 1){
                $newChunks[] = $chunk[0];
            }
            else{
                $tables = null;
                foreach($chunk as $table){
                    if($tables != null){
                        $tables = $tables->join($table);
                    }
                    else{
                        $tables = $table;
                    }
                }
                $newChunks[] = $tables;
            }
        }
        switch(count($newChunks)){
            case 0:
                return new $class();
                break;
            case 1:
                return $newChunks[0];
                break;
            default:
                return self::join_t($newChunks, $class);
                break;
        }
    }
    
    // Recursivly unions the array of QueryableTables
    static protected function union_t($tables, $class){
        $chunks = array_chunk($tables, 2);
        $newChunks = array();
        foreach($chunks as $key => $chunk){
            if(count($chunk) == 1){
                $newChunks[] = $chunk[0];
            }
            else{
                $tables = null;
                foreach($chunk as $table){
                    if($tables != null){
                        $tables = $tables->union($table);
                    }
                    else{
                        $tables = $table;
                    }
                }
                $newChunks[] = $tables;
            }
        }
        switch(count($newChunks)){
            case 0:
                return new $class();
                break;
            case 1:
                return $newChunks[0];
                break;
            default:
                return self::union_t($newChunks, $class);
                break;
        }
    }
    
    // Compares the array of patters with the given value
    // Returns true if the value was found, false if otherwise
    protected function like($patterns, $value){
        foreach($patterns as $pattern){
            $pattern = str_replace(")", "\\)", $pattern);
            $pattern = str_replace("(", "\\(", $pattern);
            if(preg_match($pattern, $value) > 0){
                return true;
            }
        }
        return false;
    }
    
    // Returns an array of 'regexified' search strings
    protected function parseValues($values){
        $newValues = array();
        foreach($values as $value){
            $newValues[] = '/^'.str_replace("\\.*", "%", str_replace("%", ".*", $value)).'$/i';
        }
        return $newValues;
    }
}

function array_project($matrix, $colN){
    $resultSet = array();
    foreach($matrix as $rowN => $row){
        if(isset($row[$colN])){
            $resultSet[$rowN] = $row[$colN];
        }
    }
    return $resultSet;
}

function array_transpose($matrix){
    $resultSet = array();
    foreach($matrix as $rowN => $row){
        foreach($row as $colN => $cell){
            $resultSet[$colN][$rowN] = $cell;
        }
    }
    return $resultSet;
}

?>
