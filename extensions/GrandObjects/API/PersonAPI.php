<?php

class PersonAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) == 1){
            $person = Person::newFromId($this->getParam('id'));
            if($person == null || $person->getName() == "" || (!$me->isLoggedIn() && !$person->isRoleAtLeast(NI))){
                $this->throwError("This user does not exist");
            }
            return $person->toJSON();
        }
        else if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) > 1){
            $json = array();
            foreach(explode(",", $this->getParam('id')) as $id){
                $person = Person::newFromId($id);
                if(!($person == null || $person->getName() == "" || (!$me->isLoggedIn() && !$person->isRoleAtLeast(NI)))){
                    $json[] = $person->toArray();
                }
            }
            return large_json_encode($json);
        }
    }
    
    function doPOST(){
        $person = new Person(array());
        $person->email = $this->POST('email');
        $person->name = $this->POST('name');
        $person->twitter = $this->POST('twitter');
        $person->website = $this->POST('website');
        $person->gender = $this->POST('gender');
        $person->publicProfile = $this->POST('publicProfile');
        $person->privateProfile = $this->POST('privateProfile');
        $person->nationality = $this->POST('nationality');
        if($person->exists()){
            $this->throwError("A user by the name of <i>{$person->getName()}</i> already exists");
        }
        $status = $person->create();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be created");
        }
        $person = Person::newFromName($person->getName());
        return $person->toJSON();
    }
    
    function doPUT(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        $person->name = $this->POST('name');
        $person->realname = $this->POST('realName');
        $person->email = $this->POST('email');
        $person->name = $this->POST('name');
        $person->twitter = $this->POST('twitter');
        $person->website = $this->POST('website');
        $person->gender = $this->POST('gender');
        $person->publicProfile = $this->POST('publicProfile');
        $person->privateProfile = $this->POST('privateProfile');
        $person->nationality = $this->POST('nationality');
        $status = $person->update();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be updated");
        }
        $person = Person::newFromId($this->getParam('id'));
        return $person->toJSON();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        if($person == null || $person->getName() == ""){
            $this->throwError("This user does not exist");
        }
        $status = $person->delete();
        if(!$status){
            $this->throwError("The user <i>{$person->getName()}</i> could not be deleted");
        }
    }
}

class PeopleAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('role') != ""){
            $university = "";
            if($this->getParam('university') != ""){
                $university = $this->getParam('university');
            }
            $exploded = explode(",", $this->getParam('role'));
            $finalPeople = array();
            foreach($exploded as $role){
                $role = trim($role);
                $people = Person::getAllPeople($role);
                foreach($people as $person){
                    if($university == ""){
                        $finalPeople[$person->getReversedName()] = $person;
                    }
                    else {
                        $uni = $person->getUniversity();
                        if($uni['university'] == $university){
                            $finalPeople[$person->getReversedName()] = $person;
                        }
                    }
                }
            }
            ksort($finalPeople);
            $finalPeople = new Collection(array_values($finalPeople));
            return $finalPeople->toJSON();
        }
        else{
            $people = new Collection(Person::getAllPeople('all'));
            return $people->toJSON();
        }
    }
    
    function doPOST(){
        return false;
    }
    
    function doPUT(){
        return false;
    }
    
    function doDELETE(){
        return false;
    }

}

class PersonProjectsAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $json = array();
        $projects = $person->getProjects(true);
        foreach($projects as $project){
            if(!$project->isSubProject()){
                $json[] = array('projectId' => $project->getId(),
                                'personId' => $person->getId(),
                                'startDate' => $project->getJoinDate($person),
                                'endDate' => $project->getEndDate($person));
            }
        }
        return json_encode($json);
    }
    
    function doPOST(){
        return doGET();
    }
    
    function doPUT(){
        return doGET();
    }
    
    function doDELETE(){
        return doGET();
    }
}

class PersonRolesAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $json = array();
        $roles = $person->getRoles(true);
        foreach($roles as $role){
            $json[] = array('roleId' => $role->getId(),
                            'personId' => $person->getId(),
                            'startDate' => $role->getStartDate(),
                            'endDate' => $role->getEndDate());
        }
        return json_encode($json);
    }
    
    function doPOST(){
        return doGET();
    }
    
    function doPUT(){
        return doGET();
    }
    
    function doDELETE(){
        return doGET();
    }
}

class PersonProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam(0) == "person"){
            // Get Products
            $me = Person::newFromWgUser();
            $person = Person::newFromId($this->getParam('id'));
            $json = array();
            $onlyPublic = true;
            $showAll = false;
            if($this->getParam(3) == "private"){
                $onlyPublic = false;
            }
            if($this->getParam(3) == "all" && $me->isRoleAtLeast(ADMIN) && $me->getId() == $person->getId()){
                $showAll = true;
                $onlyPublic = false;
            }
            if($showAll){
                $products = Product::getAllPapers("all", true, 'both', $onlyPublic, 'Public');
            }
            else{
                $products = $person->getPapers("all", true, 'both', $onlyPublic, 'Public');
            }
            foreach($products as $product){
                $array = array('productId' => $product->getId(), 
                               'personId'=> $this->getParam('id'),
                               'startDate' => $product->getDate(),
                               'endDate' => $product->getDate());
                if($this->getParam('productId') != null && $product->getId() == $this->getParam('productId')){
                    return json_encode($array);
                }
                else if($this->getParam('productId') == null){
                    $json[] = $array;
                }
            }
            return json_encode($json);
        }
        else if($this->getParam(0) == "product"){
            // Get Authors
            $product = Paper::newFromId($this->getParam('id'));
            $json = array();
            $authors = $product->getAuthors(); 
            foreach($authors as $author){
                if($author->getId()){
                    $array = array('productId' => $this->getParam('id'), 
                                   'personId' => $author->getId(),
                                   'startDate' => $product->getDate(),
                                   'endDate' => $product->getDate());
                    if($this->getParam('personId') != null && $author->getId() == $this->getParam('personId')){
                        return json_encode($array);
                    }
                    else if($this->getParam('personId') == null){
                        $json[] = $array;
                    }
                }
            }
            return json_encode($json);
        }
        return null;
    }
    
    function doPOST(){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            if($this->getParam(0) == "person"){
                $person = Person::newFromId($this->getParam('id'));
                $product = Paper::newFromId($this->getParam('productId'));
            }
            else if($this->getParam(0) == "product"){
                $product = Paper::newFromId($this->getParam('id'));
                $person = Person::newFromId($this->getParam('personId'));
            }
            $serializedAuthors = $product->authors;
            $authors = $product->getAuthors();
            $found = false;
            foreach($authors as $author){
                if($author->getId() == $person->getId()){
                    $found = true;
                }
            }
            if(!$found){
                $authors = unserialize($serializedAuthors);
                $authors[] = $person->getId();
                DBFunctions::update('grand_products',
                                    array('authors' => serialize($authors)),
                                    array('id' => $product->getId()));
                Paper::$cache = array();
                Paper::$dataCache = array();
            }
        }
        else{
            $this->throwError("Author was not added");
        }
        return $this->doGET();
    }
    
    function doPUT(){
        return $this->doPOST();
    }
    
    function doDELETE(){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            if($this->getParam(0) == "person"){
                $person = Person::newFromId($this->getParam('id'));
                $product = Paper::newFromId($this->getParam('productId'));
            }
            else if($this->getParam(0) == "product"){
                $product = Paper::newFromId($this->getParam('id'));
                $person = Person::newFromId($this->getParam('personId'));
            }
            $serializedAuthors = $product->authors;
            $authors = $product->getAuthors();
            foreach($authors as $key => $author){
                if($author->getId() == $person->getId()){
                    $serializedAuthors = unserialize($serializedAuthors);
                    unset($serializedAuthors[$key]);
                    DBFunctions::update('grand_products',
                                        array('authors' => serialize($serializedAuthors)),
                                        array('id' => $product->getId()));
                    return;
                }
            }
        }
        else{
            $this->throwError("Author was not deleted");
        }
    }
    
}

class PersonRoleStringAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $json = array('id' => $person->getId(),
                      'roleString' => $person->getRoleString());
        return json_encode($json);
    }
    
    function doPOST(){
        return doGET();
    }
    
    function doPUT(){
        return doGET();
    }
    
    function doDELETE(){
        return doGET();
    }

}

?>
