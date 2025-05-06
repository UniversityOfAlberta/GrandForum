<?php

use MediaWiki\MediaWikiServices;

/**
 * This class is responsible for handling the custom namespaces in annoki
 *
 */
class AnnokiNamespaces {

	/*
	 * Initializes the Annoki Custom Namespaces object
	 */
	function __construct() {
        $this->registerHooks();
	}

	/**
	 * registers all hooks that are handled by this class
	 *
	 */
	function registerHooks() {
		global $wgHooks;
		$wgHooks['CanonicalNamespaces'][] = array($this, 'registerExtraNamespaces');
	}

    /**
     * Registers the custom namespaces with mediawiki
     */
    function registerExtraNamespaces(&$namespaces) {
	    global $wgContentNamespaces, $wgUserNamespaces;
	    $wgUserNamespaces = array();
	    $extraNamespaces = $this->retrieveAllExtraNamespaces();
	    foreach ($extraNamespaces as $extraNamespace) {
		    $nsId = $extraNamespace["nsId"];
		    $nsName = $extraNamespace["nsName"];

		    $namespaces[$nsId] = $nsName;
		    $wgContentNamespaces[] = $nsId;

		    if ($extraNamespace["nsUser"] != null) {
			    $wgUserNamespaces[$nsId] = array("id" => $extraNamespace["nsUser"], "name" => $extraNamespace["user_name"]);
		    }
		    if (!MediaWikiServices::getInstance()->getNamespaceInfo()->isTalk($nsId)) {
			    $talk = MediaWikiServices::getInstance()->getNamespaceInfo()->getTalk($nsId);
			    $namespaces[$talk] = "{$nsName}_Talk";
		    }
	    }
	    return true;
    }

    /**
     * retrieves all of the extra namespaces from the database
     *
     * @return an array containing all extra namespaces
     */
    function retrieveAllExtraNamespaces() {
        global $egAnnokiTablePrefix;
        if(!DBCache::exists("namespaces")){
	        $dbr = wfGetDB( DB_REPLICA );
	        $extraNSTable = $dbr->tableName("${egAnnokiTablePrefix}extranamespaces");
	        $userTable = $dbr->tableName("user");
	        $sql = "SELECT nsId, nsName, nsUser, user_name from $extraNSTable LEFT JOIN $userTable ON nsUser = user_id ORDER BY nsName";
	        $result = $dbr->query($sql);
	        $extraNS = array();
	        foreach ( $result as $row ) {
		        $extraNS[] = get_object_vars($row);
	        }
	        DBCache::store("namespaces", $extraNS);
	    }
	    else{
	        $extraNS = DBCache::fetch("namespaces");
	    }
	    return $extraNS;
    }

}
?>
