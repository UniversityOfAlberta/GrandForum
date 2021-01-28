<?php
//this class started out as extending UserrightsPage which ended up being rewritten in 1.13
//TODO: it needs major refactoring (rewriting?)
require_once("includes/specialpage/SpecialPage.php");
require_once("specials/SpecialUserrights.php");

$wgExtensionFunctions[] = 'wfSetupGroupManager';

/**
 * This class implements the Groups Manager (our custom User Rights manager). Its purpose is to allow administrators to edit permissions
 * (groups/namespaces) that individual users are allowed to access
 *
 */
class GroupsManager extends UserrightsPage {
	/**
	 * Format a link to a group description page
	 * (we need to duplicate this because it is defined as private in the superclass)
	 *
	 * @param string $group
	 * @return string
	 */
	private static function buildGroupLink( $group ) {
		static $cache = array();
		if( !isset( $cache[$group] ) )
		  $cache[$group] = User::makeGroupLinkHtml( $group, User::getGroupMember( $group ) );
		return $cache[$group];
	}

	/**
	 * builds the two list boxes as well as the buttons for moving elements between the lists
	 *
	 * @param array $removable the groups that the user currently has access to
	 * @param array $addable the groups that are available in the system and could be assigned to the user
	 * @return unknown
	 */
	function createPermBoxes($removable, $addable, $showUsersCB = true, $filterUpdate = 'onkeyup', $size = 15, $leftToRight = true, $rightToLeft = true, $leftDisabled = false, $collisions = array()) {
		$usersCheckBox = "";
		$actualCollisions = array();
		foreach ($addable as $title) {
			if (in_array($title, $collisions)) {
				$actualCollisions[] = $title;
			}
		}
		if (count($actualCollisions) > 0) {
			$collisions = "new Array(" . $this->implode_wrapped("'", "'", ",", $actualCollisions ) . ")";
		}
		else {
			$collisions = 'null';
		}

		$userNamespaces = array();
		if ($showUsersCB) {
			global $wgExtraNamespaces, $wgUserNamespaces;
			$nsIdLookupArray = array_flip($wgExtraNamespaces); //BT
			
			$usersCheckBox = Xml::checkLabel("Show user namespaces", "showUserNs", "showUserNs", false, array('onChange' => 'dualList.setUserNSVisible(this.checked)'));

			foreach ($removable as $removableOption) {				
				if (is_array($removableOption)) {
					$removableOption = $removableOption[0];
				}
				if (array_key_exists($removableOption, $nsIdLookupArray)) { //BT
				  $nsId = $nsIdLookupArray[$removableOption];
				  if (array_key_exists($nsId, $wgUserNamespaces)){
				    $userNamespaces[] = $removableOption;
				  }
				}
			
			}
			
			foreach ($addable as $addableOption) {
				if (array_key_exists($addableOption, $nsIdLookupArray)) { //BT
				  $nsId = $nsIdLookupArray[$addableOption];
				  if (array_key_exists($nsId, $wgUserNamespaces)){
				    $userNamespaces[] = $addableOption;
				  }
				}
			}
			$userNamespaces = "new Array(" . $this->implode_wrapped("'", "'", ",", $userNamespaces ) . ")";
		}
		else {
			$userNamespaces = "null";
		}
		
		$ret = "
		<style> .highlighted { background: yellow; } </style>
		<table>
					<tr>
						<td width='47%'>" . $this->removeSelect( $removable, $filterUpdate, $size, $leftDisabled ) . "</td>
						<td width='5%'> ";
		if ($leftToRight) {
			$ret .= Xml::element('button', array('type'=>'button', 'onclick'=>'dualList.moveOptions("removable", "available"); return false'), ">>");
		}
		$ret .= "<br><p>";
		if ($rightToLeft) {
			$ret .= Xml::element('button', array('type'=>'button', 'onclick'=>'dualList.moveOptions("available", "removable"); return false'), "<<");
		}
		$ret .= "
						</td>
						<td width='48%'>" . $this->addSelect( $addable, $filterUpdate, $size, false) . "</td>
					</tr>
					<tr>
					<td></td>	
					<td colspan='2'> " . 
		$usersCheckBox .
					"</td>
					</tr>
				</table>
<script language= 'JavaScript'>
dualList = new FilterableDualList(document.getElementsByName('removable[]')[0], document.getElementsByName('available[]')[0], $userNamespaces, $collisions); 
if (isIE) {
	setDisabledColors();
}
</script>"; //apparently these variables must be declared after the form has been created
		//TODO userNS and collisions!
		return $ret;
	}
function implode_wrapped($before, $after, $glue, $array){
    $output = '';
    foreach($array as $item){
        $output .= $before . str_replace("'", "\\'", $item) . $after . $glue;
    }
    return substr($output, 0, -strlen($glue));
}
	/**
	 * Show the form to edit group memberships.
	 * This function is mostly duplicated from the superclass except where noted with comments
	 * @todo make all CSS-y and semantic
	 * @param $user      User or UserRightsProxy you're editing
	 * @param $groups    Array:  Array of groups the user is in
	 */
	protected function showEditUserGroupsForm( $user, $groups ) {
		global $wgOut, $wgUser, $wgScriptPath, $wgExtraNamespaces, $wgUserNamespaces;

		//		print_r($groups);
		//exit;
		
		/*$availableGroups = array_merge(AnnokiNamespaces::getExtraNamespaces(PROJECT_NS),
					       AnnokiNamespaces::getExtraNamespaces(USER_NS));
		$availableGroups = array_diff($availableGroups, $curGroups);
                $availableGroups = array_diff($availableGroups, array($nsId));
		*/


		list( $addable, $removable ) = $this->splitGroups( $groups );

		$userNS = UserNamespaces::getUserNamespace($user);
		if (array_key_exists($userNS, $wgExtraNamespaces)){
                  $userNS = $wgExtraNamespaces[$userNS];
                }

		$removable = array_merge($removable, $user->getGroups());
		$removable = array_merge(array(array($userNS,1)), $removable);
		$removable = array_unique($removable);
		
		$addable = array_merge($addable, array_merge(AnnokiNamespaces::getExtraNamespaces(PROJECT_NS),
							     AnnokiNamespaces::getExtraNamespaces(USER_NS)));
		$addable = array_diff($addable, $removable);
		$addable = array_diff($addable, array($userNS));
		$addable = array_unique($addable);

		/* there is no need to list the user's own namespace here */
		/* foreach ($addable as $key => $value) {
		  if ($value == $userNS) {
		    unset($addable[$key]);
		  }
		  }*/

		
		$list = array();
		/* if the group is a custom namespace then we want to show the name and not the namespace ID */
		foreach( $user->getGroups() as $group ) {
		  //if (is_numeric($group)) //BT
		  //$list[] = $wgExtraNamespaces[$group];
		  //else
		  $list[] = self::buildGroupLink( $group );
		}

		$grouplist = '';
		if( count( $list ) > 0 ) {
			$grouplist = '<p>' . wfMsgHtml( 'userrights-groupsmember' ) . ' ' . implode( ', ', $list ) . '</p>';
		}

		$wgOut->addHTML(
		/* here we are adding the onsubmit handler to select all items on submit so that they all get sent to the server */
		Xml::openElement( 'form', array( 'method' => 'post', 'action' => $this->getTitle()->escapeLocalURL(), 'name' => 'editGroup', 'onsubmit' => 'return dualList.prepareForSubmit()' ) ) .
		Xml::hidden( 'user', $user->getName() ) .
		Xml::hidden( 'wpEditToken', $wgUser->editToken( $user->getName() ) ) .
		Xml::openElement( 'fieldset' ) .
		Xml::element( 'legend', array(), wfMsg( 'userrights-editusergroup' ) ) .
		wfMsgExt( 'editinguser', array( 'parse' ),
		wfEscapeWikiText( $user->getName() ) ) .

		$grouplist .
		$this->createPermBoxes($removable, $addable) .
			"<table border='0'>
		
			<tr>
				<td>" .
		Xml::label( wfMsg( 'userrights-reason' ), 'wpReason' ) .
				"</td>
				<td>" .
		Xml::input( 'user-reason', 60, false, array( 'id' => 'wpReason', 'maxlength' => 255 ) ) .
				"</td>
			</tr>
			<tr>
				<td></td>
				<td>" .
		Xml::submitButton( wfMsg( 'saveusergroups' ), array( 'name' => 'saveusergroups' ) ) .
				"</td>
			</tr>
			</table>\n" .
		Xml::closeElement( 'fieldset' ) .
		Xml::closeElement( 'form' ) . "\n"
		);
	}

	/**
	 * Adds the <select> thingie where you can select what groups to remove
	 * (we need to duplicate this because it is defined as private in the superclass)
	 * @param array $groups The groups that can be removed
	 * @return string XHTML <select> element
	 */
	private function removeSelect( $groups, $filterUpdate, $size, $disabled = false, $collisions = array() ) {
		return $this->doSelect( $groups, 'removable', $filterUpdate, $size, $disabled, $collisions );
	}

	/**
	 * Adds the <select> thingie where you can select what groups to add
	 * (we need to duplicate this because it is defined as private in the superclass)
	 * @param array $groups The groups that can be added
	 * @return string XHTML <select> element
	 */
	private function addSelect( $groups, $filterUpdate, $size, $disabled = false, $collisions = array() ) {
		return $this->doSelect( $groups, 'available', $filterUpdate, $size, $disabled, $collisions );
	}

	/**
	 * Adds the <select> thingie where you can select what groups to add/remove
	 *
	 * @param array  $groups The groups that can be added/removed
	 * @param string $name   'removable' or 'available'
	 * @return string XHTML <select> element
	 */
	private function doSelect( $groups, $name, $filterUpdate, $size, $disabled = false, $collisions = array() ) {
		global $wgExtraNamespaces, $wgUserNamespaces;
		if ($name == "removable") {
			$columnTitle = "Current";
		}
		else if ($name == "available") {
			$columnTitle = "Available";
		}
		sort($groups);

		if ($collisions == null) {
			$collisions = array(); //TODO figure out why this occurs
		}
		$applyButton = "";
		if ($filterUpdate == 'onchange')
		$applyButton = Xml::element("Button", array('type' => 'button'), 'Apply');
		/*
		 * TODO: we should have different types of filters. For most things the substring is enough but for the case where we are listing pages
		 * it should have checkboxes (to simulate tabs) and then a div that has different fields/buttons. Specifically we need 2 Date range
		 * filters: one for last modified and one for created.
		 *
		 */
		$ret =
		"<center><h4>$columnTitle</h4></center><br>" .
		Xml::inputLabel("Filter: ", "${name}_filter", "${name}_filter", false, false, array($filterUpdate =>"dualList.applyFilterToList('$name', this.value)", 'onkeypress' => "return handleEnter(event, '$name', this.value)")) .
		'&nbsp; &nbsp; ' .
		$applyButton .

		Xml::openElement( 'select', array(
				'name' => "{$name}[]",
				'id'   => $name,
				'multiple' => 'multiple',
				'size' => "$size",
				'style' => 'width: 100%;'
				)
				);

		$nsIdLookupArray = array_flip($wgExtraNamespaces); //BT
				/* for each group:
				 1. if it is numeric then it is associated with a custom namespace so show the name of the namespace and not the numeric id
				 2. if it is a user namespace then mark it as such so that it can be hidden/shown dynamically
				 */
		foreach ($groups as $group) {
					if (is_array($group)) {
						$disabled = $group[1];
						$group = $group[0];
					}
					$groupName = $group;
					$divId = "normal";
					$defaultStyle = "";
					//if (is_numeric($group)) {
					if (array_key_exists($group, $nsIdLookupArray)) {
					  $nsId = $nsIdLookupArray[$group];
					  if (array_key_exists($nsId, $wgUserNamespaces)){
					    $userName = $wgUserNamespaces[$nsId]['name'];
					    $groupName = "$group ($userName)";
					  }
					}
					//}
					$options = array( 'value' => $group, 'name' => $divId, 'style' => $defaultStyle, 'id' => $group);
					if ($disabled) {
						$options['disabled'] = 'true';
					}
					$ret .= Xml::element( 'option', $options, $groupName ); //TODO here we should really try to make User::getGroupName return the group name
				}
				$ret .= Xml::closeElement( 'select' );
				return $ret;
	}

	/**
	 * commits the changes made to the user's groups (i.e. namespaces that the user has access to)
	 * Note: the removegroup and addgroup arrays describe how the groups should look like in the end but the function in the
	 * superclass expects the parameters to contain the groups that need to be removes and the ones that need to be added repsectively.
	 * We are going to respect that because otherwise some hooks are going to break.
	 *
	 * @param string $username
	 * @param array $removegroup
	 * @param array $addgroup
	 * @param string $reason
	 */
		function saveUserGroups( $username, $reason = '') {

		global $wgOut, $wgRequest;

		$user = $this->fetchUser($username);
		$id = $user->getID();
		$currentGroups = $user->getGroups();

		$targetGroups = $wgRequest->getArray("removable", array());
		if (!$targetGroups) {
			$targetGroups = array();
		}
		//the groups to be removed: subtract the target groups from the user's current groups
		$removegroup = array_diff($currentGroups, $targetGroups);
		//the groups to be added: subtract the current groups from the target groups
		$addgroup = array_diff($targetGroups, $currentGroups);

		foreach( $removegroup as $group ) {
			$user->removeGroup( $group );
		}
			
		foreach( $addgroup as $group ) {
			$user->addGroup( $group );
		}

		Hooks::run( 'UserRights', array( &$user, $addgroup, $removegroup ) );
		$this->addLogEntry( $user, $currentGroups, $user->getGroups() );


	}

	/**
	 * builds a string that lists all of the groups that are extra namespaces, separated by commas
	 * TODO: make sure that builtin groups such as sysop, etc. are also listed here!
	 * @param array $ids
	 * @return the group names separated by commas
	 */
		/*	function makeGroupNameList( $ids ) { //BT commented out
		global $wgExtraNamespaces;

		$names = array();
		foreach ($ids as $id) {
			if (is_numeric($id)) {
				$names[] = $wgExtraNamespaces[$id];
			}
			else {
				$names[] = $id;
			}
		}
		return implode( ', ', $names );
		} */
}

/**
 * Initializes the groups manager. Substitutes the regular mediawiki Special:Userrights with our custom class (which subclasses
 * the Userrights special page)
 *
 */
function wfSetupGroupManager() {
	global $wgGroupPermissions, $wgExtraNamespaces;
	SpecialPage::$mList['Userrights'] = "GroupsManager";
	if (!$wgExtraNamespaces)
	return;
	
	/* don't include talk namespaces because they always go together with the respective main namespaces */
	foreach ($wgExtraNamespaces as $nsID => $nsName) {
	  if (MWNamespace::isMain($nsID)){
	    $wgGroupPermissions[$nsName] = array();
	  }
	}
}


?>
