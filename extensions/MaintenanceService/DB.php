<?php
/**
 * Manages the connection to the target database.
 * In the development of the project, three database management systems
 * were considered: Postgres, DB2 and MySQL.
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class DB {

    /**
     * Name of the dialect (database)
     * @access private
     * @var string
     */
    private static $dialect = "MySQL";

    /**
     * @desc Sets the dialect of the target database
     * @param string $dialect Name of the dialect
     */
    public static function setDialect($dialect) {
        self::$dialect = $dialect;
    }

    /**
     * @desc Gets the dialect of the target database
     * @return string Name of the dialect
     */
    public static function getDialect() {
        return self::$dialect;
    }

    /**
     * @desc Performs a query in the target database
     * @param string $sqlQuery SQL Query in the target dialect
     * @param link_identifier $conn Connection to the database
     * @return resultset
     */
    public static function query($sqlQuery, $conn) {
	
        if (self::$dialect == "Postgres")
	 {
            return pg_query($conn, $sqlQuery);
        } 
	 else if (self::$dialect == "MySQL") 
	 {
            $sqlResult = mysql_query($sqlQuery, $conn);
	     return $sqlResult;
        
        } 
	 else if (self::$dialect == "DB2") 
	 {
	     return db2_exec($conn, $sql_query);
        }
        
        return null;
    }


    /**
     * @desc Fetch a result row as an associative array
     * @param resource $sqlResult The result resource that is being evaluated. This result comes from a call to query().
     * @return array Returns an array that corresponds to the fetched row and moves the internal data pointer ahead.
     */
    public static function fetchAssoc($sqlResult) {
        if (self::$dialect == "Postgres") {
            return pg_fetch_assoc($sqlResult);

        } else if (self::$dialect == "MySQL") {
            return mysql_fetch_assoc($sqlResult);

        } else if (self::$dialect == "DB2") {
            return db2_fetch_assoc($sqlResult);
        }

        return null;
    }


    /**
     * @desc Get a result row as an enumerated array
     * @param resource $sqlResult The result resource that is being evaluated. This result comes from a call to query().
     * @return array Returns an array that corresponds to the fetched row and moves the internal data pointer ahead.
     */
    public static function fetchRow($sqlResult) {
        if (self::$dialect == "Postgres") {
            return pg_fetch_row($sqlResult);

        } else if (self::$dialect == "MySQL") {
            return mysql_fetch_row($sqlResult);

        } else if (self::$dialect == "DB2") {
            return db2_fetch_row($sqlResult);
        }

        return null;
    }


    /**
     * @desc Fetch a result row as an associative array, a numeric array, or both
     * @param resource $sqlResult The result resource that is being evaluated. This result comes from a call to query().
     * @return array Returns an array that corresponds to the fetched row and moves the internal data pointer ahead.
     */
    public static function fetchArray($sqlResult) {
        if (self::$dialect == "Postgres") {
            return pg_fetch_array($sqlResult);

        } else if (self::$dialect == "MySQL") {
            $fetch = mysql_fetch_array($sqlResult);
	     //print_r($fetch);
	     return $fetch;
        } else if (self::$dialect == "DB2") {
            return db2_fetch_array($sqlResult); //??
        }

        return null;
    }


    /**
     * @desc Get number of rows in result
     * @param resource $sqlResult The result resource that is being evaluated. This result comes from a call to query().
     * @return int Number of rows
     */
    public static function numRows($sqlResult) {
	
        if (self::$dialect == "Postgres") {
            return pg_num_rows($sqlResult);

        } else if (self::$dialect == "MySQL") {
            return mysql_num_rows($sqlResult);

        } else if (self::$dialect == "DB2") {
            //??
        }

        return null;
    }


    
    public static function close($conn) {
        if (self::$dialect == "Postgres") {
            pg_close($conn);

        } else if (self::$dialect == "MySQL") {
            mysql_close($conn);

        } else if (self::$dialect == "DB2") {
            //??
        }
    }
}
?>
