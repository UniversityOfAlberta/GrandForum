<?php
/** 
 * Convenience class for accessing the database.
 * @package Annoki
 * @subpackage AnnokiControl
 * @author Brendan Tansey
 */	

/** 
 * Functions that can be used for accessing the MediaWiki database. 
 * @package Annoki
 * @subpackage AnnokiControl
 * @author Brendan Tansey
 */
class AnnokiDatabaseFunctions {
  
  /**
   * Gets the results of a query in array form.  If $fieldName is given, only returns the contents of that particular field.
   * @param string $query The SQL query to be run.
   * @param string $fieldName If a single field is desired, rather than all of those listed in the query, place it here.  
   * Otherwise, leave this as false.
   * @param Database $dbLink The database to use.  Leave as null to use DB_REPLICA.
   * @return array The rows returned by the query in array form.
   */
  public static function getQueryResultsAsArray($query, $fieldName=false, $dbLink = null){
    if ($dbLink===null)
      $dbLink = wfGetDB(DB_REPLICA);

    $res = $dbLink->query($query);

    $results = array();

    while ($row = $dbLink->fetchRow($res)){
      if ($fieldName)
        $results[] = $row[$fieldName];
      else
        $results[] = $row;
    }

    $dbLink->freeResult($res);

    return $results;
  }

  /**
   * Determines if the given table exists in the current database.
   * Currently only works with MySQL.
   * @param string The name of the table for which to check.  Note that this does not prefix the table name with $wgDBprefix.
   * @return boolean True if the table exists, false if it does not exist, on error, or if using a database other than MySQL.
   */
  public static function doesTableExist($tableName){
      global $wgDBtype;
      if ($wgDBtype != 'mysql')
	  return false;

      $dbr = wfGetDB(DB_REPLICA);
      $query = "show tables like \"$tableName\"";
      $result = $dbr->query($query);
      $numRows = $dbr->numRows($results);
      $dbr->freeResult($results);

      return ($numRows === 1);
  }
}

?>
