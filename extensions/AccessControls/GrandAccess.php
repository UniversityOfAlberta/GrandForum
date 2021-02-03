<?php

class GrandAccess {

    static $alreadyDone = array();

    static function setupGrandAccess($user, &$aRights){
        global $wgRoleValues;
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
	            if(($role == STAFF || $role == MANAGER || $role == ADMIN) && array_search('Leadership+', $aRights) === false){
	                $aRights[$i++] = 'Leadership+';
	            }
	            if(($role == STAFF || $role == MANAGER || $role == ADMIN) && array_search('Evaluator+', $aRights) === false){
	                $aRights[$i++] = 'Evaluator+';
	            }
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
	    if(!empty($me->getRoles())){
	        foreach($me->getRoles() as $role){
	            $aRights[$i++] = $role->getRole();
	            $aRights[$i++] = $role->getRole().'_Wiki';
	            //$user->mGroups[] = $role->getRole().'_Wiki';
	        }
	    }
	    foreach($aRights as $right){
	        //$user->mGroups[] = $right;
	    }
	    if($user->isLoggedIn()){
	        $aRights[$i++] = "Poster";
	        $aRights[$i++] = "Presentation";
	        //$user->mGroups[] = "Poster";
	        //$user->mGroups[] = "Presentation";
	    }
	    return true;
	}
	
	static function changeGroups($user, &$aRights){
        global $wgRoles;
        foreach($aRights as $key => $right){
            if($key >= 1000){
                continue;
            }
            unset($aRights[$key]);
        }
        $aRights[0] = 'read';
        return true;
    }
	
}

?>
