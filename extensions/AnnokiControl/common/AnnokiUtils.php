<?php

use MediaWiki\MediaWikiServices;

/** 
 * Convenience class for common Annoki functions used across numerous extensions.
 * @package Annoki
 * @subpackage AnnokiControl
 * @author Brendan Tansey
 */	

/** 
 * Functions that are commonly used in many Annoki extensions. 
 * @package Annoki
 * @subpackage AnnokiControl
 * @author Brendan Tansey
 */
class AnnokiUtils {
  /**
   * Get an array of all of the users in the database.
   * @return array An array of all users of the wiki.
   */
  static function getAllUsers() {
    $dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
    $ipblocks = $dbr->tableName('ipblocks');
    $userTable = $dbr->tableName('user');
    $result = $dbr->select("$userTable LEFT JOIN $ipblocks ON user_id = ipb_user", 'user_name, ipb_user');
     
    while ($row = $result->fetchRow()) {
      if ($row['ipb_user'] == null) {
	$users[] = $row['user_name'];
      }
    }

    return $users;
  }

  /**
   * Get an array of all users that have contributed to an article.
   * @param Title $title The title for which authors will be located.
   * @return array An array of user names as strings.
   */
  static function getAllAuthorsForTitle($title){
      $id = $title->getArticleID();
      
      $dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
      $result = $dbr->select('revision', 'distinct rev_user_text', "rev_page='$id'");
      $authors = array();
      
      while ($row = $result->fetchRow()){
	  $authors[] = $row['rev_user_text'];
      }

      return $authors;
  }
}

?>
