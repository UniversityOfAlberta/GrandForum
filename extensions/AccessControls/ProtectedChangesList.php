<?php
class ProtectedChangesList {
	static function isUserAllowed($rc) {
		//for now pages that are moved from a namespace that the user can access (e.g. the main namespace) to another namesapce will still be shown
		global $wgUser;
				
		$title = $rc->getTitle();
		onUserCan($title, $wgUser, 'read', $result);
		if ($result === true || $result === null)
			return true;
		else
			return false;
	}
}

class ProtectedEnhancedChangesList extends EnhancedChangesList {
	public function recentChangesLine( &$rc, $watched = false, $linenumber = NULL ) {
		if (ProtectedChangesList::isUserAllowed($rc)) {
			return parent::recentChangesLine($rc, $watched);
		}
		return "";
	}
}

class ProtectedOldChangesList extends OldChangesList {
	public function recentChangesLine( &$rc, $watched = false, $linenumber = NULL ) {
		if (ProtectedChangesList::isUserAllowed($rc)) {
			return parent::recentChangesLine($rc, $watched);
		}
		return "";
	}
}
?>
