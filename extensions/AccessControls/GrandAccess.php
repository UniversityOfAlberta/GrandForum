<?php

class GrandAccess {

    static $alreadyDone = array();

    static function setupGrandAccess($user, &$aRights){
        global $wgRoleValues, $config;
        if(isset(self::$alreadyDone[$user->getId()])){
            return true;
        }
        self::$alreadyDone[$user->getId()] = true;
	    $me = Person::newFromId($user->getId());
	    $i = 1000;
	    $oldRights = $aRights;
	    foreach($oldRights as $right){
	        $aRights[$i++] = $right;
	    }
	    if($me->isRoleAtLeast(MANAGER)){
	        $aRights[$i++] = RMC;
	    }
	    if($me->isRole(NI)){
	        $aRights[$i++] = "Researcher";
	        $aRights[$i++] = "Researcher+";
	        $aRights[$i++] = "NI";
	        $aRights[$i++] = "NI+";
	    }
	    foreach(array_keys($wgRoleValues) as $role){
	        if($me->isRoleAtLeast($role)){
	            $aRights[$i++] = $role.'+';
	            $aRights[$i++] = $role.'During+';
	            if(($role == STAFF || $role == MANAGER || $role == ADMIN) && array_search('Researcher+', $aRights) === false){
	                $aRights[$i++] = 'Researcher+';
	            }
	        }
	    }
	    foreach($me->getRolesDuring(CYCLE_START, CYCLE_END) as $role){
	        if(!$me->isCandidate()){
	            $aRights[$i++] = $role->getRole().'During';
	            $aRights[$i++] = $role->getRole().'During+';
	        }
	    }
	    if(count($me->getRoles()) > 0){
	        foreach($me->getRoles() as $role){
	            $aRights[$i++] = $role->getRole();
	            $user->mGroups[] = $role->getRole().'_Wiki';
	        }
	    }
	    foreach($aRights as $right){
	        $user->mGroups[] = $right;
	    }
	    if($user->isLoggedIn()){
	        $user->mGroups[] = "Poster";
	        $user->mGroups[] = "Presentation";
	    }
	    return true;
	}
	
}

?>
