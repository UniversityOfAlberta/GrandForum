<?php

function COL($value){
    return array("", $value);
}

function queryNumeric($value){
    if(!is_numeric($value)){
        if(is_array($value)){
            $value = $value[1];
        }
        else{
            $value = "'".DBFunctions::escape($value)."'";
        }
    }
    return $value;
}

function NEQ($value){
    $value = queryNumeric($value);
    return array("!=", $value);
}

function EQ($value){
    $value = queryNumeric($value);
    return array("=", $value);
}

function GT($value){
    $value = queryNumeric($value);
    return array(">", $value);
}

function LT($value){
    $value = queryNumeric($value);
    return array("<", $value);
}

function GTEQ($value){
    $value = queryNumeric($value);
    return array(">=", $value);
}

function LTEQ($value){
    $value = queryNumeric($value);
    return array("<=", $value);
}

function LIKE($value){
    $value = queryNumeric($value);
    return array("LIKE", $value);
}

function NOTLIKE($value){
    $value = queryNumeric($value);
    return array("NOT LIKE", $value);
}

function DURING($values){
    $i = 0;
    $start = "";
    $startKey = "";
    $end = "";
    $endKey = "";
    foreach($values as $key => $value){
        if($i == 0){
            $start = DBFunctions::escape($value);
            $startKey = $key;
        }
        else {
            $end = DBFunctions::escape($value);
            $endKey = $key;
        }
    }
    return "(
        (($endKey != '0000-00-00 00:00:00') AND
        (($startKey BETWEEN '$start' AND '$end') || ($endKey BETWEEN '$start' AND '$end') || ($startKey <= '$start' AND $endKey >= '$end') ))
        OR
        (($endKey = '0000-00-00 00:00:00') AND
        (($startKey <= '$end')))
    )";
}

function IN($values){
    foreach($values as $key => $value){
        $values[$key] = DBFunctions::escape($value);
    }
    return array("IN", "('".implode("','", $values)."')");
}

function NOT_IN($values){
    foreach($values as $key => $value){
        $values[$key] = DBFunctions::escape($value);
    }
    return array("NOT IN", "('".implode("','", $values)."')");
}

function WHERE_OR($value){
    return " OR ".$value;
}

function WHERE_AND($value){
    return " AND ".$value;
}

/**
 * @package AnnokiControl
 */
class DBFunctions {

    static $queryLength = 0;
    static $queryCount = 0;
    static $lastResult;
    static $dbr;
    static $dbw;
    static $mysqlnd = false;
    static $queryDebug = false;
    
    static function initDB(){
        if(DBFunctions::$dbr == null && DBFunctions::isReady()){
            DBFunctions::$dbr = wfGetDB(DB_REPLICA);
            DBFunctions::$dbw = wfGetDB(DB_PRIMARY);
            DBFunctions::$mysqlnd = function_exists('mysqli_fetch_all');
        }
    }
    
    static function isReady(){
        return function_exists("wfGetDB");
    }
    
    // Returns the number of queries executed so far
    static function getQueryCount(){
	    return self::$queryCount;
	}
	
	static function DBWritable(){
	    global $wgImpersonating;
	    $me = Person::newFromWGUser();
	    $supervisesImpersonee = false;
	    if(isExtensionEnabled('Impersonate')){
	     	$supervisesImpersonee = checkSupervisesImpersonee();
	    }
	    return !($wgImpersonating && !$supervisesImpersonee);
	}
	
	static function escape($string){
	    DBFunctions::initDB();
	    return DBFunctions::$dbr->strencode($string);
	}
	
	// Returns an escaped version of the $string for use in LIKE queries
	static function like($string){
	    return str_replace('_', '\_', str_replace('%', '\%', $string));
	}
    
    // Executes an sql statement.  By default a query is assumed, and processes the resultset into an array.
    // If $update is set to true, then an update is performed instead.
    // If $rollback is set to true, the DB is rolledback in the event of an error
    static function execSQL($sql, $update=false, $rollback=false){
        global $wgImpersonating, $wgRealUser, $wgUser, $wgOut, $wgMessage, $config;
        try{
            DBFunctions::initDB();
            if(self::$queryDebug){
                $start = microtime(true);
                self::$queryCount++;
                $printedSql = str_replace("\n", " ", $sql);
                $printedSql = str_replace("\t", " ", $printedSql);
                while(strstr($printedSql, "  ") !== false){
                    $printedSql = str_replace("  ", " ", $printedSql);
                }
                $peakMemBefore = memory_get_peak_usage(true)/1024/1024;
            }
		    if($update != false){
		        if(!DBFunctions::DBWritable()){
		            return true;
		        }
			    $status = DBFunctions::$dbw->query($sql);
			    if($rollback && !$status){
			        DBFunctions::rollback();
			    }
			    return $status;
		    }
		    
		    $result = DBFunctions::$dbr->query($sql);
		    
	        $rows = array();
	        if($result != null){
	            if(DBFunctions::$mysqlnd){
	                $rows = mysqli_fetch_all($result->getResult(), MYSQLI_ASSOC);
	            }
	            else{
	                while ($row = mysqli_fetch_array($result->getResult(), MYSQLI_ASSOC)) {
		                $rows[] = $row;
	                }
	            }
	        }
	        self::$lastResult = count($rows);
	        if(self::$queryDebug){
	            $peakMemAfter = memory_get_peak_usage(true)/1024/1024;
		        $end = microtime(true);
		        $diff = number_format(($end - $start)*1000, 5);
		        self::$queryLength += $diff;
		        
		        $printedSql = "<!-- ".self::$queryCount.": ($diff ms / ".count($rows)." / Before:{$peakMemBefore}MiB / After:{$peakMemAfter}MiB) $printedSql -->\n";
		        $wgOut->addHTML($printedSql);
		    }
		    return $rows;
		}
		catch (DBQueryError $e){
		    if(php_sapi_name() === 'cli'){
		        echo $e->getMessage();
		    }
		    else{
		        $me = Person::newFromUser($wgUser);
		        if($me->isRoleAtLeast(MANAGER)){
		            $traces = debug_backtrace();
		            foreach($traces as $trace){ 
		                $file = $trace['file'];
		                $line = $trace['line'];
		                if(strstr($file, "DBFunctions.php") === false){
		                    break;
		                }
		            }
		            $wgMessage->addError("<pre class='inlineError' style='font-weight:bold;background:none;border:none;padding:0;overflow:hidden;margin:0;'>".$e->getMessage()."in <i>{$file}</i> on line <i>{$line}</i></pre>");
		        }
		        else{
		            $wgMessage->addError("A Database error #{$e->errno} has occurred, please contact <a href='mailto:{$config->getValue('supportEmail')}'>{$config->getValue('supportEmail')}</a>.");
		        }
		    }
		    if($rollback){
		        DBFunctions::rollback();
		    }
		    if($update){
		        return false;
		    }
		    else{
		        return array();
		    }
		}
	}
	
	/**
	 * Performs a sanitized DB Selection
	 * @param array $tables The hash of tables to select (<b>key:</b> The name of the table; <b>value:<b> The alias of the table)
	 * @param array $cols The hash of columns to select (<b>key:</b> The name of the column; <b>value:</b> The alias of the column)
	 * @param array $where The hash of column/values for the where clause
	 * @param array $order The hash of columns to order by (<b>key:</b> The name of the column; <b>value:</b> DESC/ASC)
	 * @param array $limit How to limit results (array of 1 or 2 values)
	 * @return array Returns the result set
	 */
	static function select($tables=array(), $cols=array(), $where=array(), $order=array(), $limit=array()){
	    $colSQL = array();
	    $fromSQL = array();
	    $whereSQL = array();
	    $orderSQL = array();
	    $limitSQL = array();
	    if(count($cols) > 0){
	        foreach($cols as $key => $col){
	            $key = DBFunctions::escape($key);
	            $col = DBFunctions::escape($col);
	            if(is_numeric($key)){
	                $colSQL[] = "$col";
	            }
	            else{
	                $colSQL[] = "$key as $col";
	            }
	        }
	    }
	    else{
	        $colSQL[] = "*";
	    }
	    foreach($tables as $key => $table){
	        $key = DBFunctions::escape($key);
	        $table = DBFunctions::escape($table);
	        if(is_numeric($key)){
	            $fromSQL[] = "$table";
	        }
	        else{
	            $fromSQL[] = "$key $table";
	        }
	    }
	    foreach($where as $key => $value){
            $key = DBFunctions::escape($key);
            if(is_array($value)){
                $whereSQL[] = "{$key} {$value[0]} {$value[1]} ";
            }
            else{
                $value = DBFunctions::escape($value);
                $whereSQL[] = "{$key} = '{$value}' ";
            }
        }
        foreach($order as $key => $value){
            $key = DBFunctions::escape($key);
            $value = DBFunctions::escape($value);
            $orderSQL[] = "{$key} {$value} ";
        }
        foreach($limit as $key => $value){
            $value = DBFunctions::escape($value);
            $limitSQL[] = "{$value} ";
        }
        $sql = "SELECT ".implode(", ", $colSQL)." FROM ".implode(", ", $fromSQL)." ";
        if(count($whereSQL) > 0){
            $sql .= "WHERE ";
            foreach($whereSQL as $key => $where){
                if($key > 0){
                    if(strpos($where, " OR ") === 0){
                        $where = str_replace_first(" OR ", " OR ", $where);
                    }
                    else if(strpos($where, " AND ") === 0){
                        $where = str_replace_first(" AND ", " AND ", $where);
                    }
                    else{
                        $where = " AND $where";
                    }
                }
                $sql .= $where."\n";
            }
        }
        if(count($orderSQL) > 0){
            $sql .= "ORDER BY ".implode(", ", $orderSQL);
        }
        if(count($limitSQL) > 0){
            $sql .= "LIMIT ".implode(", ", $limitSQL);
        }
        return DBFunctions::execSQL($sql);
	}
	
	/**
	 * Performs a sanitized DB Insertion
	 * @param string $table The name of the table to insert
	 * @param array $values The hash of the column/values for the insertion
	 * @return boolean Returns whether the insertion was successful or not
	 */
	static function insert($table, $values=array(), $rollback=false){
	    $table = DBFunctions::escape($table);
	    $sql = "INSERT INTO {$table} (";
	    $cols = array();
	    $vals = array();
        foreach($values as $key => $value){
            $key = DBFunctions::escape($key);
            $cols[] = "{$key}";
            if(is_array($value)){
                $vals[] = "{$value[1]}";
            }
            else{
                $value = DBFunctions::escape($value);
                $vals[] = "'{$value}'";
            }
        }
        $sql .= implode(",", $cols).") VALUES(".implode(",", $vals).")";
        return DBFunctions::execSQL($sql, true, $rollback);
	}
	
	/**
	 * Performs a sanitized DB Deletion
	 * @param string $table The name of the table to delete
	 * @param array $where The hash of the column/values for the deletion
	 * @return boolean Returns whether the insertion was successful or not
	 */
	static function delete($table, $where=array(), $rollback=false){
	    $whereSQL = array();
	    $table = DBFunctions::escape($table);
	    foreach($where as $key => $value){
            $key = DBFunctions::escape($key);
            if(is_array($value)){
                $whereSQL[] = "{$key} {$value[0]} {$value[1]} ";
            }
            else{
                $value = DBFunctions::escape($value);
                $whereSQL[] = "{$key} = '{$value}' ";
            }
        }
        $sql = "DELETE FROM $table ";
        if(count($whereSQL) > 0){
            $sql .= "WHERE ";
            foreach($whereSQL as $key => $where){
                if($key > 0){
                    if(strpos($where, " OR ") === 0){
                        $where = str_replace_first(" OR ", " OR ", $where);
                    }
                    else if(strpos($where, " AND ") === 0){
                        $where = str_replace_first(" AND ", " AND ", $where);
                    }
                    else{
                        $where = " AND $where";
                    }
                }
                $sql .= $where."\n";
            }
        }
        return DBFunctions::execSQL($sql, true);
	}
	
	/**
	 * Performs a sanitized DB Update
	 * @param string $table The name of the table to update
	 * @param array $values The hash of column/values to update
	 * @param array $where The hash for column/values for the where clause
	 * @param boolean $rollback Whether or not to perform a rollback if the update fails
	 * @param array $limit How to limit results (array of 1 or 2 values)
	 * @return boolean Returns whether or not the update was successfull
	 */
    static function update($table, $values=array(), $where=array(), $limit=array(), $rollback=false){
        $table = DBFunctions::escape($table);
        $limitSQL = array();
        $sql = "UPDATE {$table}\nSET ";
        $sets = array();
        foreach($values as $key => $value){
            $key = DBFunctions::escape($key);
            if(is_array($value)){
                $sets[] = "{$key} {$value[0]} {$value[1]} ";
            }
            else{
                $value = DBFunctions::escape($value);
                $sets[] = "{$key} = '{$value}' ";
            }
        }
        $sql .= implode(",\n", $sets);
        $whereSQL = array();
        foreach($where as $key => $value){
            $key = DBFunctions::escape($key);
            if(is_array($value)){
                $whereSQL[] = "{$key} {$value[0]} {$value[1]} ";
            }
            else{
                $value = DBFunctions::escape($value);
                $whereSQL[] = "{$key} = '{$value}' ";
            }
        }
        if(count($whereSQL) > 0){
            $sql .= "WHERE ";
            foreach($whereSQL as $key => $where){
                if($key > 0){
                    if(strpos($where, " OR ") === 0){
                        $where = str_replace_first(" OR ", " OR ", $where);
                    }
                    else if(strpos($where, " AND ") === 0){
                        $where = str_replace_first(" AND ", " AND ", $where);
                    }
                    else{
                        $where = " AND $where";
                    }
                }
                $sql .= $where."\n";
            }
        }
        foreach($limit as $key => $value){
            $value = DBFunctions::escape($value);
            $limitSQL[] = "{$value} ";
        }
        if(count($limitSQL) > 0){
            $sql .= "\nLIMIT ".implode(", ", $limitSQL);
        }
        return DBFunctions::execSQL($sql, true, $rollback);
    }
	
	/**
	 * Begins a Transaction
	 */
	static function begin(){
	    DBFunctions::initDB();
	    DBFunctions::$dbw->begin();
	}
	
	/**
	 * Commits the transaction to the DB
	 */
	static function commit(){
	    DBFunctions::initDB();
		DBFunctions::$dbw->commit();
	}
	
	/**
	 * Rolls the DB back to the previous state
	 */
	static function rollback(){
	    DBFunctions::initDB();
	    DBFunctions::$dbw->rollback();
	}
	
	/**
	 * Returns the last insert id
	 * @return int The last insert id
	 */
	static function insertId(){
	    return self::$dbw->insertId();
	}
	
	/**
	 * Returns the number of rows returned in the last resultset
	 */
	static function getNRows(){
	    if(self::$lastResult != null){
	        return self::$lastResult;
	    }
	    else{
	        return 0;
	    }
	}
}
?>
