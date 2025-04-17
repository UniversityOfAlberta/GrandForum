<?php

class GrandAccess {

    static $alreadyDone = array();

    static function setupGrandAccess($user, &$aRights){
        global $wgRoleValues, $config;
        $hash = spl_object_hash($user)."\n";
        if($user->getId() == 0 && isset(self::$alreadyDone[$hash."_".$user->getId()])){
            return true;
        }
        self::$alreadyDone[$hash."_".$user->getId()] = true;
	    $me = Person::newFromId($user->getId());
	    $i = 1000;
	    $oldRights = $aRights;
	    foreach($oldRights as $right){
	        $aRights[$i++] = $right;
	    }
	    if($me->isRoleAtLeast(MANAGER)){
	        $aRights[$i++] = FEC;
	    }
	    if($me->isRole(NI)){
	        $aRights[$i++] = "NI";
	        $aRights[$i++] = "NI+";
	    }
	    foreach(array_keys($wgRoleValues) as $role){
	        if($me->isRoleAtLeast($role)){
	            $aRights[$i++] = $role.'+';
	        }
	    }
	    if(count($me->getRoles()) > 0){
	        foreach($me->getRoles() as $role){
	            $aRights[$i++] = $role->getRole();
	        }
	    }
	    return true;
	}
	
}

?>
