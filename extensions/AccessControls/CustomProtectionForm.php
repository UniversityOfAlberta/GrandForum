<?php
require_once("$IP/includes/ProtectionForm.php");
//require_once("CustomSpecialUserRights.php");
/**
 * This class is responsible for the custom protect action handler. When somebody clicks on the protect
 * tab on a page, the form will be rendered by this class instead of the regular ProtectionForm  
 *
 */
class CustomProtectionForm extends ProtectionForm {
		
	/**
	 * Reuses the permissions element from the Groups Manager special page to build the custom protection
	 * form which consists of two list boxes - one for current permissions
	 * and one for available and two buttons that allow items to be moved between the two lists.
	 * 
	 * 
	 * @return string the html for rendering the list boxes and the buttons
	 */
	function buildCustomProtection() {
	  global $egAnnokiNamespaces, $wgExtraNamespaces, $wgRequest;
		$gm = new GroupsManager();
		
		//TODO: make it so that the user namespace actually appears on top!
		$nsId = $this->mTitle->getNamespace();
				
		//		$curGroups = array(array($nsId,1));
		//$curGroups = array_merge($curGroups, getExtraPermissions($this->mTitle));

		$curGroups = getExtraPermissions($this->mTitle);

		$pageIsPublic = false;
		
		foreach ($curGroups as $index => $groupId){
		  if ($groupId == -1){
		    $pageIsPublic = true;
		    break;
		  }
		  if (array_key_exists($groupId, $wgExtraNamespaces)){
		    $curGroups[$index] = $wgExtraNamespaces[$groupId];
		  }
		}

		$nsUsers = $egAnnokiNamespaces->getAllUsersInNS($this->mTitle->getNsText());
		$ret = '<fieldset><legend>Namespace access</legend>'."\n";
		$ret .= "<br>Here you can specify which groups have additional access to this page (i.e. in addition to the
		users that already have access to the page's namespace" . ((count($nsUsers) > 0) ? ": " : "") . implode(", ", $nsUsers) . ")";

		//Add button for making article public/non-public.
		if ($pageIsPublic){
		  $ret .= '<br><br>This page is public and is accessible by all users.  To make this page non-public, click the button below.<br>';
		  $ret .= '<center>'.$this->makePublicButton(0).'</center><br>';
		  return $ret;
		}
		else {
		  $ret .= '<br><center>'.$this->makePublicButton(1).'</center>';
		}

		//Convert nsId from index to string name
		if (array_key_exists($nsId, $wgExtraNamespaces)){
		  $nsId = $wgExtraNamespaces[$nsId];
		}

		$curGroups = array_merge(array(array($nsId,1)), $curGroups);

		//$availableGroups = array_diff(User::getAllGroups(), $curGroups);
		//$availableGroups = array_diff($availableGroups, array($nsId));
		$availableGroups = array_merge(AnnokiNamespaces::getExtraNamespaces(PROJECT_NS), 
					       AnnokiNamespaces::getExtraNamespaces(USER_NS));
		$availableGroups = array_diff($availableGroups, $curGroups);
		$availableGroups = array_diff($availableGroups, array($nsId));
		
		
		/*print "<pre>";
		print_r($curGroups);
		print_r($availableGroups);*/
		
		$ret .= $gm->createPermBoxes($curGroups, $availableGroups);
		
		$ret .= '</fieldset>';
		
		return $ret;	
	}
	
	function makePublicButton($newPublicValue){
	  $ret = Xml::hidden('pageId', $this->mTitle->getArticleID()) .
	    Xml::hidden( 'newPublicValue',  $newPublicValue ) .
	    Xml::submitButton($newPublicValue==1?'Make this page public':'Make this page non-public', array('name' => 'togglePublic'));
	  
	  return $ret;
	}
	

	/**
	 * saves all changes made to the this page's permissions
	 *
	 * @return unknown
	 */
	function save() {
		if (!parent::save())
			return false;
		return $this->saveCustomProtection();
		
}
	function execute() {
		global $wgRequest, $wgOut;

		if( $wgRequest->wasPosted() ) {
		  if ($wgRequest->getBool("togglePublic"))
		      $this->togglePublicPage($wgRequest->getInt('pageId', -1), $wgRequest->getInt("newPublicValue", -1));

		  else if( $this->save() ) {
		    $article = new Article( $this->mTitle );
		    $q = 'action=protect';
		    $wgOut->redirect( $this->mTitle->getFullUrl( $q ) );
		   }
		} else {
		  $this->show();
		}
	}

	function togglePublicPage($pageId, $newValue) {
	  global $wgOut, $egAnnokiTablePrefix;
	  if ($newValue < 0 || $newValue > 1) {
	    return;
	  }

	  $dbw = wfGetDB( DB_MASTER );
	  if ($newValue == 1)
	    $dbw->insert("${egAnnokiTablePrefix}pagepermissions", array('page_id' => $pageId, 'group_id' => -1));
	  else
	    $dbw->delete("${egAnnokiTablePrefix}pagepermissions", array('page_id' => $pageId, 'group_id' => -1));
	  
	  $log = new LogPage( 'protect' );
	  $log->addEntry( $newValue==1?'unprotect':'protect', $this->mTitle, 'Made article '.($newValue==1?'public':'non-public'));

	  $this->show();
	}

	 /**
	  * Updates the custom page-specific permissions for this page
	  *
	  */
	 function saveCustomProtection() {
	   global $wgRequest, $wgExtraNamespaces;

	   $accessList = $newGroups = $wgRequest->getArray("removable");

	   $nsIdLookupArray = array_flip($wgExtraNamespaces);

	   $nsId = $this->mTitle->getNamespace();

	   if (array_key_exists($nsId, $wgExtraNamespaces)){
	     $nsId = $wgExtraNamespaces[$nsId];
	   }

	   foreach ($newGroups as $id => $name){
	     if ($nsId == $name)
	       unset($newGroups[$id]);
	     else if (array_key_exists($name, $nsIdLookupArray))
	       $newGroups[$id] = $nsIdLookupArray[$name];
	   }

	   updateExtraPermissions($this->mTitle, $newGroups);

	   $comment = $wgRequest->getText('mwProtect-reason');

	   if ($comment != '')
	     $comment = '. Comment: '.$comment;

	   $log = new LogPage( 'protect' );
	   $log->addEntry( 'protect', $this->mTitle, 'Set access list to {'.implode(',',$accessList).'}'.$comment);

	   return true;
	 }

 /**
  * We need to add an onsubmit event to the form so that all items in the listboxes get selected when
  * submitting the form (which is needed for them to be transmitted to the server). Unfortunately to
  * do this we need to duplicate most of the code in the superclass. The only difference is the onsubmit
  * property in the form definition.
  * TODO: is it better to change the onsubmit with javascript instead?
  * @return string the html to render the form
  */ 
	function buildForm() {
		global $wgUser, $wgLang;

		$mProtectreasonother = Xml::label( wfMsg( 'protectcomment' ), 'wpProtectReasonSelection' );
		$mProtectreason = Xml::label( wfMsg( 'protect-otherreason' ), 'mwProtect-reason' );
		$this->disabled = false; //BT
		$out = '';
		if( !$this->disabled ) {
			$out .= $this->buildScript();
			$out .= Xml::openElement( 'form', array( 'method' => 'post', 
				'action' => $this->mTitle->getLocalUrl( 'action=protect' ), 
				'id' => 'mw-Protect-Form', 'onsubmit' => 'ProtectionForm.enableUnchainedInputs(true); dualList.prepareForSubmit()' ) );
			$out .= Xml::hidden( 'wpEditToken',$wgUser->editToken() );
		}

		$out .= Xml::openElement( 'fieldset' ) .
			Xml::element( 'legend', null, wfMsg( 'protect-legend' ) ) .
		  Xml::openElement( 'table', array( 'id' => 'mwProtectSet' ) ) .
		  Xml::openElement( 'tbody' );
		//		  .Xml::openElement( 'tr' ); //BT

		foreach( $this->mRestrictions as $action => $selected ) {
			/* Not all languages have V_x <-> N_x relation */
			$msg = wfMsg( 'restriction-' . $action );
			if( wfEmptyMsg( 'restriction-' . $action, $msg ) ) {
				$msg = $action;
			}
			$out .= "<tr><td>".
			//$out .= "<td style=\"vertical-align: top;\">". //BT
			Xml::openElement( 'fieldset' ) .
			Xml::element( 'legend', null, $msg ) .
			Xml::openElement( 'table', array( 'id' => "mw-protect-table-$action" ) ) .
				"<tr><td>" . $this->buildSelector( $action, $selected ) . "</td></tr><tr><td>";

			$reasonDropDown = Xml::listDropDown( 'wpProtectReasonSelection',
				wfMsgForContent( 'protect-dropdown' ),
				wfMsgForContent( 'protect-otherreason-op' ), 
				$this->mReasonSelection,
				'mwProtect-reason', 4 );
			$scExpiryOptions = wfMsgForContent( 'protect-expiry-options' );

			$showProtectOptions = ($scExpiryOptions !== '-' && !$this->disabled);

			$mProtectexpiry = Xml::label( wfMsg( 'protectexpiry' ), "mwProtectExpirySelection-$action" );
			$mProtectother = Xml::label( wfMsg( 'protect-othertime' ), "mwProtect-$action-expires" );

			$expiryFormOptions = '';
			if ( $this->mExistingExpiry[$action] && $this->mExistingExpiry[$action] != 'infinity' ) {
				$timestamp = $wgLang->timeanddate( $this->mExistingExpiry[$action] );
				$d = $wgLang->date( $this->mExistingExpiry[$action] );
				$t = $wgLang->time( $this->mExistingExpiry[$action] );
				$expiryFormOptions .= 
					Xml::option( 
						wfMsg( 'protect-existing-expiry', $timestamp, $d, $t ),
						'existing',
						$this->mExpirySelection[$action] == 'existing'
					) . "\n";
			}
			
			$expiryFormOptions .= Xml::option( wfMsg( 'protect-othertime-op' ), "othertime" ) . "\n";
			foreach( explode(',', $scExpiryOptions) as $option ) {
				if ( strpos($option, ":") === false ) {
					$show = $value = $option;
				} else {
					list($show, $value) = explode(":", $option);
				}
				$show = htmlspecialchars($show);
				$value = htmlspecialchars($value);
				$expiryFormOptions .= Xml::option( $show, $value, $this->mExpirySelection[$action] === $value ) . "\n";
			}
			# Add expiry dropdown
			if( $showProtectOptions && !$this->disabled ) {
				$out .= "
					<table><tr>
						<td class='mw-label'>
							{$mProtectexpiry}
						</td>
						<td class='mw-input'>" .
							Xml::tags( 'select',
								array(
									'id' => "mwProtectExpirySelection-$action",
									'name' => "wpProtectExpirySelection-$action",
									'onchange' => "ProtectionForm.updateExpiryList(this)",
									'tabindex' => '2' ) + $this->disabledAttrib,
								$expiryFormOptions ) .
						"</td>
					</tr></table>";
			}
			# Add custom expiry field
			$attribs = array( 'id' => "mwProtect-$action-expires",
				'onkeyup' => 'ProtectionForm.updateExpiry(this)' ) + $this->disabledAttrib;

			$out .= "<table><tr>
					<td class='mw-label'>" .
						$mProtectother .
					'</td>
					<td class="mw-input">' .
						Xml::input( "mwProtect-expiry-$action", 50, $this->mExpiry[$action], $attribs ) .
					'</td>
					</tr></table>';
			$out .= "</td></tr>" .
			Xml::closeElement( 'table' ) .
			Xml::closeElement( 'fieldset' ) .
			  "</td></tr>";
			  //"</td>"; //BT
		}

		$out .= Xml::closeElement( 'tbody' ) . Xml::closeElement( 'table' );

		// JavaScript will add another row with a value-chaining checkbox
		if( $this->mTitle->exists() ) {
			$out .= Xml::openElement( 'table', array( 'id' => 'mw-protect-table2' ) ) .
				Xml::openElement( 'tbody' );
			$out .= '<tr>
					<td></td>
					<td class="mw-input">' .
						Xml::checkLabel( wfMsg( 'protect-cascade' ), 'mwProtect-cascade', 'mwProtect-cascade', 
							$this->mCascade, $this->disabledAttrib ) .
					"</td>
				</tr>\n";
			$out .= Xml::closeElement( 'tbody' ) . Xml::closeElement( 'table' );
		}
		

		$out .= $this->buildCustomProtection(); //BT


		# Add manual and custom reason field/selects as well as submit
		if( !$this->disabled ) {
			$out .=  Xml::openElement( 'table', array( 'id' => 'mw-protect-table3' ) ) .
				Xml::openElement( 'tbody' );
			$out .= /*"
				<tr>
					<td class='mw-label'>
						{$mProtectreasonother}
					</td>
					<td class='mw-input'>
						{$reasonDropDown}
					</td>
					</tr>*/
				"<tr>
					<td class='mw-label'>
						{$mProtectreasonother}
					</td>
					<td class='mw-input'>" .
						Xml::input( 'mwProtect-reason', 60, $this->mReason, array( 'type' => 'text', 
							'id' => 'mwProtect-reason', 'maxlength' => 255 ) ) .
					"</td>
				</tr>
				<tr>
					<td></td>
					<td class='mw-input'>" .
						Xml::checkLabel( wfMsg( 'watchthis' ),
							'mwProtectWatch', 'mwProtectWatch',
							$this->mTitle->userIsWatching() || $wgUser->getOption( 'watchdefault' ) ) .
					"</td>
				</tr>
				<tr>
					<td></td>
					<td class='mw-submit'>" .
						Xml::submitButton( wfMsg( 'confirm' ), array( 'id' => 'mw-Protect-submit' ) ) .
					"</td>
				</tr>\n";
			$out .= Xml::closeElement( 'tbody' ) . Xml::closeElement( 'table' );
		}
		$out .= Xml::closeElement( 'fieldset' );

		if ( $wgUser->isAllowed( 'editinterface' ) ) {
			$linkTitle = Title::makeTitleSafe( NS_MEDIAWIKI, 'protect-dropdown' );
			$link = $wgUser->getSkin()->Link ( $linkTitle, wfMsgHtml( 'protect-edit-reasonlist' ) );
			$out .= '<p class="mw-protect-editreasons">' . $link . '</p>';
		}

		if ( !$this->disabled ) {
			$out .= Xml::closeElement( 'form' ) .
				$this->buildCleanupScript();
		}

		return $out;
	}

	/* function buildForm() {
		 global $wgUser;
		 $this->disabled = false;
		 $out = '';
		 if( !$this->disabled ) {
			 $out .= $this->buildScript();
			 // The submission needs to reenable the move permission selector
			 // if it's in locked mode, or some browsers won't submit the data.
			 $out .=	Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->mTitle->getLocalUrl( 'action=protect' ), 'id' => 'mw-Protect-Form', 'onsubmit' => 'protectEnable(true); dualList.prepareForSubmit()' ) ) .
				Xml::hidden( 'wpEditToken',$wgUser->editToken() );
		}

		$out .= Xml::openElement( 'fieldset' ) .
			Xml::element( 'legend', null, wfMsg( 'protect-legend' ) ) .
			Xml::openElement( 'table', array( 'id' => 'mwProtectSet' ) ) .
			Xml::openElement( 'tbody' ) .
			"<tr>\n";

		foreach( $this->mRestrictions as $action => $required ) {
			// Not all languages have V_x <-> N_x relation 
			$label = Xml::element( 'label',
					array( 'for' => "mwProtect-level-$action" ),
					wfMsg( 'restriction-' . $action ) );
			$out .= "<th>$label</th>";
		}
		$out .= "</tr>
			<tr>\n";
		foreach( $this->mRestrictions as $action => $selected ) {
			$out .= "<td>" .
					$this->buildSelector( $action, $selected ) .
				"</td>";
		}
		$out .= "</tr>\n";

		// JavaScript will add another row with a value-chaining checkbox

		$out .= Xml::closeElement( 'tbody' ) .
			Xml::closeElement( 'table' ) .
			Xml::openElement( 'table', array( 'id' => 'mw-protect-table2' ) ) .
			Xml::openElement( 'tbody' );

		if( $this->mTitle->exists() ) {
			$out .= '<tr>
					<td></td>
					<td class="mw-input">' .
						Xml::checkLabel( wfMsg( 'protect-cascade' ), 'mwProtect-cascade', 'mwProtect-cascade', $this->mCascade, $this->disabledAttrib ) .
					"</td>
				</tr>\n";
		}

		$attribs = array( 'id' => 'expires' ) + $this->disabledAttrib;
		$out .= "<tr><td colspan='2'>" . $this->buildCustomProtection() . "</tr>";

		if( !$this->disabled ) {
			$id = 'mwProtect-reason';
			$out .= "<tr>
					<td class='mw-label'>" .
						Xml::label( wfMsg( 'protectcomment' ), $id ) .
					'</td>
					<td class="mw-input">' .
						Xml::input( $id, 60, $this->mReason, array( 'type' => 'text', 'id' => $id, 'maxlength' => 255 ) ) .
					"</td>
				</tr>
				<tr>
					<td></td>
					<td class='mw-input'>" .
						Xml::checkLabel( wfMsg( 'watchthis' ),
							'mwProtectWatch', 'mwProtectWatch',
							$this->mTitle->userIsWatching() || $wgUser->getOption( 'watchdefault' ) ) .
					"</td>
				</tr>
				<tr>
					<td></td>
					<td class='mw-submit'>" .
						Xml::submitButton( wfMsg( 'confirm' ), array( 'id' => 'mw-Protect-submit' ) ) .
					"</td>
				</tr>\n";
		}

		$out .= Xml::closeElement( 'tbody' ) .
			Xml::closeElement( 'table' ) .
			Xml::closeElement( 'fieldset' );

		if ( !$this->disabled ) {
			$out .= Xml::closeElement( 'form' ) .
				$this->buildCleanupScript();
		}

		return $out;
	}*/	
}
?>
