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
	    if($me->isRoleAtLeast(MANAGER)){
	        $aRights[$i++] = RMC;
	    }
	    if($me->isRoleAtLeast(STAFF)){
	        $aRights[$i++] = PL;
	        $aRights[$i++] = TL;
	        $aRights[$i++] = TC;
	    }
	    $leadership = $me->leadership();
	    if(count($leadership) > 0){
	        $aRights[$i++] = "Leadership";
	        $aRights[$i++] = "Leadership+";
	        if($me->isProjectLeader()){
	            $aRights[$i++] = PL;
	            $aRights[$i++] = PL.'+';
	        }
	        foreach($leadership as $lead){
	            if($lead->isSubProject()){
	                $aRights[$i++] = "SUB-PL";
	                break;
	            }
	        }
	    }
	    if($me->isEvaluator()){
	        $aRights[$i++] = "Evaluator";
	        $aRights[$i++] = "Evaluator+";
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
