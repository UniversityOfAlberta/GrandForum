<?php

class GrandAccess {

    static $alreadyDone = array();

    static function setupGrandAccess($user, &$aRights){
        global $wgRoleValues;
        if(isset(self::$alreadyDone[$user->getId()])){
            $user->mGroups = self::$alreadyDone[$user->getId()];
            $aRights = $user->mGroups;
            return true;
        }
        
	    $me = Person::newFromId($user->getId());
	    $i = 1000;
	    $oldRights = $aRights;
	    foreach($oldRights as $right){
	        $aRights[$i++] = $right;
	    }
	    if(count($me->getProjects()) > 0){
	        foreach($me->getProjects() as $project){
	            $aRights[$i++] = $project->getName();
	        }
	    }
	    if($me->isThemeLeader() || $me->isThemeCoordinator()){
	        $aRights[$i++] = TL;
	        $aRights[$i++] = TC;
	    }
	    foreach(array_merge($me->getLeadThemes(), $me->getCoordThemes()) as $theme){
	        $aRights[$i++] = $theme->getAcronym();
	    }
	    foreach($me->getThemeProjects() as $project){
	        $aRights[$i++] = $project->getName();
	    }
	    if($me->isRoleAtLeast(STAFF)){
	        $aRights[$i++] = PL;
	        $aRights[$i++] = TL;
	        $aRights[$i++] = TC;
	    }
	    if($me->isEvaluator()){
	        $aRights[$i++] = "Evaluator";
	        $aRights[$i++] = "Evaluator+";
	    }
	    if($me->isRole(NI)){
	        $aRights[$i++] = "NI";
	        $aRights[$i++] = "NI+";
	    }
	    if($me->isRole(ADMIN)){
	        $aRights[$i++] = "sysop";
	        $aRights[$i++] = "bureaucrat";
	    }
	    foreach(array_keys($wgRoleValues) as $role){
	        if($me->isRoleAtLeast($role)){
	            $aRights[$i++] = $role.'+';
	            if(($role == STAFF || $role == MANAGER || $role == ADMIN) && array_search('Evaluator+', $aRights) === false){
	                $aRights[$i++] = 'Evaluator+';
	            }
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
	    self::$alreadyDone[$user->getId()] = $aRights;
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
