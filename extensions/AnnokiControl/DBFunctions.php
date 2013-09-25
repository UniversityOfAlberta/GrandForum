<?php

function COL($value){
    return "COL### $value";
}

function queryNumeric($value){
    if(!is_numeric($value)){
        if(strstr($value, "COL### ") !== false){
            $value = str_replace("COL### ", "", $value);
        }
        else{
            $value = "'".mysql_real_escape_string($value)."'";
        }
    }
    return $value;
}

function NEQ($value){
    $value = queryNumeric($value);
    return "### != {$value}";
}

function EQ($value){
    $value = queryNumeric($value);
    return "### = {$value}";
}

function GT($value){
    $value = queryNumeric($value);
    return "### > {$value}";
}

function LT($value){
    $value = queryNumeric($value);
    return "### < {$value}";
}

function GTEQ($value){
    $value = queryNumeric($value);
    return "### >= {$value}";
}

function LTEQ($value){
    $value = queryNumeric($value);
    return "### <= {$value}";
}

function LIKE($value){
    $value = queryNumeric($value);
    return "### LIKE {$value}";
}

function IN($values){
    foreach($values as $key => $value){
        $values[$key] = mysql_real_escape_string($value);
    }
    return "### IN ('".implode("','", $values)."')";
}

function NOT_IN($values){
    foreach($values as $key => $value){
        $values[$key] = mysql_real_escape_string($value);
    }
    return "### NOT IN ('".implode("','", $values)."')";
}

function WHERE_OR($value){
    return "### OR ".$value;
}

function WHERE_AND($value){
    return "### AND ".$value;
}

/**
 * @package AnnokiControl
 */
class DBFunctions {

    static $queryCount = 0;
    static $lastResult;
    static $dbr;
    static $dbw;
    static $queryDebug = false;
    
    static function initDB(){
        if(DBFunctions::$dbr == null){
            DBFunctions::$dbr = wfGetDB(DB_SLAVE);
            DBFunctions::$dbw = wfGetDB(DB_MASTER);
        }
    }
    
    // Returns the number of queries executed so far
    static function getQueryCount(){
	    return self::$queryCount;
	}
	
	static function DBWritable(){
	    global $wgImpersonating;
	    $me = Person::newFromWGUser();
	    $supervisesImpersonee = checkSupervisesImpersonee();
	    return (!($wgImpersonating && !$supervisesImpersonee) && (!FROZEN || $me->isRoleAtLeast(MANAGER)));
	}
    
    // Executes an sql statement.  By default a query is assumed, and processes the resultset into an array.
    // If $update is set to true, then an update is performed instead.
    // If $rollback is set to true, the DB is rolledback in the event of an error
    static function execSQL($sql, $update=false, $rollback=false){
        global $wgImpersonating, $wgRealUser, $wgUser, $wgOut, $wgMessage;
        try{
            DBFunctions::initDB();
            if(self::$queryDebug){
                self::$queryCount++;
                $printedSql = str_replace("\n", " ", $sql);
                $printedSql = str_replace("\t", " ", $printedSql);
                while(strstr($printedSql, "  ") !== false){
                    $printedSql = str_replace("  ", " ", $printedSql);
                }
                $printedSql = "<!-- $printedSql -->\n";
                echo $printedSql;
                $wgOut->addHTML($printedSql);
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
		    self::$lastResult = $result;
	        $rows = array();
	        // I would like to use MYSQL_ASSOC here, but that causes breakage at the moment
	        if($result != null){
	            while ($row = mysql_fetch_array($result->result, MYSQL_BOTH)) {
		            $rows[] = $row;
	            }
	        }
		    return $rows;
		}
		catch (DBQueryError $e){
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
		        $wgMessage->addError("A Database error #{$e->errno} has occurred, please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.");
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
	 * TODO: This is not yet fully tested
	 */
	static function select($tables=array(), $cols=array(), $where=array(), $order=array(), $limit=array()){
	    $colSQL = array();
	    $fromSQL = array();
	    $whereSQL = array();
	    $orderSQL = array();
	    $limitSQL = array();
	    if(count($cols) > 0){
	        foreach($cols as $key => $col){
	            $key = mysql_real_escape_string($key);
	            $col = mysql_real_escape_string($col);
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
	        $key = mysql_real_escape_string($key);
	        $table = mysql_real_escape_string($table);
	        if(is_numeric($key)){
	            $fromSQL[] = "$table";
	        }
	        else{
	            $fromSQL[] = "$key $table";
	        }
	    }
	    foreach($where as $key => $value){
            $key = mysql_real_escape_string($key);
            if(strstr($value, "### ") !== false){
                $value = str_replace("### ", "", $value);
                $whereSQL[] = "{$key} {$value} ";
            }
            else{
                $value = mysql_real_escape_string($value);
                $whereSQL[] = "{$key} = '{$value}' ";
            }
        }
        foreach($order as $key => $value){
            $key = mysql_real_escape_string($key);
            $value = mysql_real_escape_string($value);
            $orderSQL[] = "{$key} {$value} ";
        }
        foreach($limit as $key => $value){
            $value = mysql_real_escape_string($value);
            $limitSQL[] = "{$value} ";
        }
        $sql = "SELECT ".implode(", ", $colSQL)." FROM ".implode(", ", $fromSQL)." ";
        if(count($whereSQL) > 0){
            $sql .= "WHERE ";
            foreach($whereSQL as $key => $where){
                if($key > 0){
                    if(strstr($where, "### OR ") !== false){
                        $where = str_replace("### OR ", " OR ", $where);
                    }
                    else if(strstr($where, "### AND ") !== false){
                        $where = str_replace("### AND ", " AND ", $where);
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
	 * TODO: This is not yet fully tested
	 */
	static function insert($table, $values=array(), $rollback=false){
	    $table = mysql_real_escape_string($table);
	    $sql = "INSERT INTO {$table} (";
	    $cols = array();
	    $vals = array();
        foreach($values as $key => $value){
            $key = mysql_real_escape_string($key);
            $cols[] = "{$key}";
            if(strstr($value, "### ") !== false){
                $value = str_replace("=", "", str_replace("### ", "", $value));
                $vals[] = "{$value}";
            }
            else{
                $value = mysql_real_escape_string($value);
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
	 * TODO: This is not yet fully tested
	 */
	static function delete($table, $where=array(), $rollback=false){
	    $whereSQL = array();
	    $table = mysql_real_escape_string($table);
	    foreach($where as $key => $value){
            $key = mysql_real_escape_string($key);
            if(strstr($value, "### ") !== false){
                $value = str_replace("### ", "", $value);
                $whereSQL[] = "{$key} {$value} ";
            }
            else{
                $value = mysql_real_escape_string($value);
                $whereSQL[] = "{$key} = '{$value}' ";
            }
        }
        $sql = "DELETE FROM $table ";
        if(count($whereSQL) > 0){
            $sql .= "WHERE ";
            foreach($whereSQL as $key => $where){
                if($key > 0){
                    if(strstr($where, "### OR ") !== false){
                        $where = str_replace("### OR ", " OR ", $where);
                    }
                    else if(strstr($where, "### AND ") !== false){
                        $where = str_replace("### AND ", " AND ", $where);
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
	 * TODO: This is not yet fully tested
	 */
    static function update($table, $values=array(), $where=array(), $limit=array(), $rollback=false){
        $table = mysql_real_escape_string($table);
        $limitSQL = array();
        $sql = "UPDATE {$table}\nSET ";
        $sets = array();
        foreach($values as $key => $value){
            $key = mysql_real_escape_string($key);
            if(strstr($value, "### ") !== false){
                $value = str_replace("### ", "", $value);
                $sets[] = "{$key} {$value} ";
            }
            else{
                $value = mysql_real_escape_string($value);
                $sets[] = "{$key} = '{$value}' ";
            }
        }
        $sql .= implode(",\n", $sets);
        $wheres = array();
        foreach($where as $key => $value){
            $key = mysql_real_escape_string($key);
            if(strstr($value, "### ") !== false){
                $value = str_replace("### ", "", $value);
                $wheres[] = "{$key} {$value} ";
            }
            else{
                $value = mysql_real_escape_string($value);
                $wheres[] = "{$key} = '{$value}' ";
            }
        }
        if(count($wheres) > 0){
            $sql .= "WHERE ";
            foreach($wheres as $key => $where){
                if($key > 0){
                    if(strstr($where, "### OR ") !== false){
                        $where = str_replace("### OR ", " OR ", $where);
                    }
                    else if(strstr($where, "### AND ") !== false){
                        $where = str_replace("### AND ", " AND ", $where);
                    }
                    else{
                        $where = " AND $where";
                    }
                }
                $sql .= $where."\n";
            }
        }
        foreach($limit as $key => $value){
            $value = mysql_real_escape_string($value);
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
	 * Returns the number of rows returned in the last resultset
	 */
	static function getNRows(){
	    if(self::$lastResult != null && self::$lastResult->result != null){
	        return mysql_num_rows(self::$lastResult->result);
	    }
	    else{
	        return 0;
	    }
	}
}
?>
