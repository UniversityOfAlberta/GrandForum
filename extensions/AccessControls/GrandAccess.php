<?php

class GrandAccess {

    static function setupGrandAccess($user, &$aRights){
        global $wgRoleValues;
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
	    foreach(Project::getAllProjects() as $project){
	        if($me->isThemeLeaderOf($project) || ($project->isSubProject() && $me->isThemeLeaderOf($project->getParent()))){
	            $aRights[$i++] = $project->getName();
	        }
	    }
	    if($me->isRole(MANAGER)){
	        $aRights[$i++] = RMC;
	        $aRights[$i++] = PL;
	        $aRights[$i++] = COPL;
	    }
	    if(count($me->leadership()) > 0){
	        $aRights[$i++] = "Leadership";
	        $aRights[$i++] = "Leadership+";
	        if($me->isProjectLeader()){
	            $aRights[$i++] = PL;
	            $aRights[$i++] = PL.'+';
	        }
	        if($me->isProjectCoLeader()){
	            $aRights[$i++] = COPL;
	            $aRights[$i++] = COPL.'+';
	        }
	        if($me->isProjectManager()){
	            $aRights[$i++] = PM;
	            $aRights[$i++] = PM.'+';
	        }
	    }
	    if($me->isEvaluator()){
	        $aRights[$i++] = "Evaluator";
	        $aRights[$i++] = "Evaluator+";
	    }
	    if($me->isPNI() || $me->isCNI()){
	        $aRights[$i++] = "Researcher";
	        $aRights[$i++] = "Researcher+";
	    }
	    foreach(array_keys($wgRoleValues) as $role){
	        if($me->isRoleAtLeast($role)){
	            $aRights[$i++] = $role.'+';
	            $aRights[$i++] = $role.'During+';
	            if(($role == STAFF || $role == MANAGER) && array_search('Leadership+', $aRights) === false){
	                $aRights[$i++] = 'Leadership+';
	            }
	            if(($role == STAFF || $role == MANAGER) && array_search('Evaluator+', $aRights) === false){
	                $aRights[$i++] = 'Evaluator+';
	            }
	            if(($role == STAFF || $role == MANAGER) && array_search('Researcher+', $aRights) === false){
	                $aRights[$i++] = 'Researcher+';
	            }
	        }
	    }
	    foreach($me->getRolesDuring() as $role){
	        $aRights[$i++] = $role->getRole().'During';
	        $aRights[$i++] = $role->getRole().'During+';
	    }
	    if(count($me->getRoles()) > 0){
	        foreach($me->getRoles() as $role){
	            $aRights[$i++] = $role->getRole();
	            $user->mGroups[] = $role->getRole().'_Wiki';
	        }
	    }
	    if($user->isLoggedIn()){
	        $user->mGroups[] = "Poster";
	        $user->mGroups[] = "Presentation";
	    }
	    return true;
	}
	
}

?>
