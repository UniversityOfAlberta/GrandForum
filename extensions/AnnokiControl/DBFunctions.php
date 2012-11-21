<?php

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
    
    // Executes an sql statement.  By default a query is assumed, and processes the resultset into an array.
    // If $update is set to true, then an update is performed instead.
    static function execSQL($sql, $update=false){
        global $wgImpersonating, $wgRealUser, $wgUser, $wgOut;
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
		    $supervisesImpersonee = checkSupervisesImpersonee();
		    if($wgImpersonating && !$supervisesImpersonee){
		        return true;
		    }
			return DBFunctions::$dbw->query($sql);
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
	
	// Begins a Transaction
	static function begin(){
	    DBFunctions::initDB();
	    DBFunctions::$dbw->begin();
	}
	
	// Commits the transaction to the DB
	static function commit(){
	    DBFunctions::initDB();
		DBFunctions::$dbw->commit();
	}
	
	// Returns the number of rows returned in the last resultset
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
