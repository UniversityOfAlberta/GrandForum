<?php

define('MY_STRUCTURE', 1);
$myQueryableTableStructures[NI_PUBLIC_PROFILE_STRUCTURE] =
    array(array(HEAD,HEAD,HEAD,HEAD),
          array(READ,READ,READ,READ),
          array(READ,READ,READ,READ)
    );

class MyQueryableTable extends QueryableTable {

    function __construct($structure, $matrix){
        global $myQueryableTableStructures;
        $this->id = "myqueryabletable".QueryableTable::$idCounter;
        parent::__construct();
        if(is_array($structure)){
            $this->structure = $structure;
        }
        else{
            $this->structure = $this->preprocessStructure($myQueryableTableStructures[$structure]);
        }
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
    
    static function union_tables($tables){
        return QueryableTable::union_t($tables, "MyQueryableTable");
    }
    
    static function join_tables($tables){
        return QueryableTable::join_t($tables, "MyQueryableTable");
    }
}
?>
