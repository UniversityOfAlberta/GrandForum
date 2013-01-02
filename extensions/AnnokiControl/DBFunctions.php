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
		        $wgMessage->addError("<pre class='inlineError' style='font-weight:bold;background:none;border:none;padding:0;overflow:hidden;margin:0;'>".$e->getMessage()."</pre>");
		    }
		    else{
		        $wgMessage->addError("A Database error #{$e->errno} has occured, please contact <a href='mailto:support@grand-nce.ca'>support@grand-nce.ca</a>.");
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
	
	// Rolls the DB back to the previous state
	static function rollback(){
	    DBFunctions::initDB();
	    DBFunctions::$dbw->rollback();
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
