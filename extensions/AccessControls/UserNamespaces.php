<?php

class UserNamespaces {

	/**
	 * Once the user has been successfully registered - create the namespace
	 *
	 * @param User $user
	 * @param boolean $byEmail
	 */
	function onAddNewAccount($user, $byEmail = false) {
		global $wgRequest, $egAnnokiNamespaces;
		$nsValue =  $wgRequest->getText("wpUserNS");

		//if we reach this point then we have already validated the namespace name, so here we just create the namespace
		$egAnnokiNamespaces->addNewNamespace($nsValue, $user);
		return true;
	}
	/**
	 * Validate the input in the namespace text field
	 * @param User $user
	 * @param string $message
	 */

	function onAbortNewAccount($user, &$message) {
		global $wgRequest;
		$nsValue =  $wgRequest->getText("wpUserNS");
		return AnnokiNamespaces::isValidNewNamespaceName($nsValue, $message);
	}
	/**
	 * Enter description here...
	 *
	 * @param QuickTemplate $template
	 * @return unknown
	 */
	function onUserCreateForm(&$template) {
		global $wgRequest;
		//TODO: figure out how to align it properly!
		$headerTxt = $template->text("header");
		$prefill = $wgRequest->getText("wpUserNS");
		#$nsField = <<<END
#<br>
#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
#<label for='wpUserNSLabel'>Namespace:</label>
#<input type='text' class='loginText' name="wpUserNS" id="wpUserNS"
#					tabindex="1"
#END;
#		$nsField .= " value = '$prefill'' size='20' />";

$nsField = <<<END
<table>
<tr>
  <td class="mw-input">
  <input type='hidden' class='loginText' name="wpUserNS" id="wpUserNS"
  tabindex="2" value="" size='20' />
  </td>
  </tr>
</table>
END;

		//TODO is this enough to prevent conflict with other extensions??
		$headerTxt .= $nsField;

		$template->set( 'header', $headerTxt );
		return true;
	}

	static function isUserNs($nsId) {
		global $wgUserNamespaces;

		return (array_key_exists($nsId, $wgUserNamespaces));
	}

	static function getUserNamespace($user) {
		global $wgUserNamespaces;
        if($wgUserNamespaces != null){
		    foreach ($wgUserNamespaces as $nsKey => $nsValue) {
			    if ($nsValue['id'] == $user->getID()) {
				    return $nsKey;
			    }
		    }
		}

		return null;

	}

	//TODO: This function is a bit of a hack to sort the Recent Changes namespace list, which lacks a hook in a decent place.
	//Once a hook exists, use it instead.
	function onSpecialRecentChangesPanel(&$extraOpts, $opts){
	  //print_r($extraOpts);
	  //	 close();
	  //print "\n\n\n";
	  
	  $namespaceString = $extraOpts['namespace'][1];
	  if (strstr($namespaceString, '<select id="namespace" name="namespace" class="namespaceselector">') === false)
	    return true;
	  
	  $namespaceList = explode("\n", $namespaceString);
	  
	  //if (count($namespaceList) < 19) //The first 18 entries are from MediaWiki
	  //return true;
	  
	  $keepers = array();
	  $toSort = array();
	  //$talkToSort = array();
	  $nsIDs = array();
  
	  $matches = array();
	  $pattern = '/\<option value="([\d]*)"\>([\s\S]*?)\<\/option\>/';
	  
	  foreach ($namespaceList as $nsOption){
	    $match = preg_match($pattern, $nsOption, $matches);
	    if ($match == 0)
	      $keepers[] = $nsOption;
	    else {
	      if (!array_key_exists(1, $matches) || $matches[1] < 100){ //Skip MediaWiki namespaces
		$keepers[] = $nsOption;
	      }
	      else {
		$nsIDs[$matches[1]] = $nsOption;
		if ($matches[1] % 2 == 0)
		  $toSort[$matches[1]] = $matches[2]; //$toSort[$nsOption] = $matches[2];
		//else
		//$talkToSort[$nsOption] = $matches[2];
	      }
	    }
	  }

	  /*	  if (count($toSort) !== count($talkToSort)) {//Something went horribly wrong
	    print "Error sorting namespaces.  Please report this to your system administrator.";
	    return true;
	    }*/
	    
	  natcasesort($toSort);
	  //natcasesort($talkToSort); //These should sort the same...
	  
	  //$toSort = array_keys($toSort);
	  //$talkToSort = array_keys($talkToSort);
	  
	  $finalLine = $keepers[count($keepers)-1];
	  unset($keepers[count($keepers)-1]);
	  
	  foreach ($toSort as $nsID => $nsName){
	    $keepers[] = $nsIDs[$nsID];
	    $keepers[] = $nsIDs[$nsID+1];
	  }

	  /*	  for ($i=0; $i < count($toSort); $i++){
	    $keepers[] = $toSort[$i];
	    $keepers[] = $talkToSort[$i];
	    }*/
	  
	  $keepers[] = $finalLine;

	  $namespaceString = implode("\n", $keepers);
	  $extraOpts['namespace'][1] = $namespaceString;

	  return true;
	}

	/**
	 * Retrieves the user who owns the given namespace
	 *
	 * @param string $nsName
	 * @return User the user for this namesapce
	 */
	static function getUserFromNamespace($nsName) {
	  global $egAnnokiNamespaces, $egAnnokiTablePrefix;
		$nsId = $egAnnokiNamespaces->getNsId($nsName);
		$dbr = wfGetDB( DB_REPLICA );
		$result = $dbr->select("${egAnnokiTablePrefix}extranamespaces", "nsUser", array("nsId" => $nsId) );

		$row = $dbr->fetchRow($result);

		if (!$row)
			return null;
		if ($row[0] == null) {
			return null;
		}
		return User::newFromId($row[0]);
	}
}
?>
