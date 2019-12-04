<?php

class PersonRelationsAPI extends RESTAPI {

    function doGET(){
        $person = Person::newFromId($this->getParam('id'));
        $relations = $person->getRelations('all', true);
        if($this->getParam('relId') != ""){
            // Single Relation
            foreach($relations as $type){
                foreach($type as $id => $relation){
                    if($id == $this->getParam('relId')){
                        return $relation->toJSON();
                    }
                }
            }
        }
        else{
            // All Relations
            $relations = new Collection(flatten($relations));
            return $relations->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        
        $relation = new Relationship(array());
        $relation->user1 = $this->POST('user1');
        $relation->user2 = $this->POST('user2');
        $relation->university = $this->POST('university');
        $relation->type = $this->POST('type');
        $relation->status = $this->POST('status');
        $relation->thesis = $this->POST('thesis');
        $relation->startDate = $this->POST('startDate');
        $relation->endDate = $this->POST('endDate');
        $relation->comment = $this->POST('comment');
        $relation->create();
        return $this->doGET();
    }
    
    function doPUT(){
        $person = Person::newFromId($this->getParam('id'));
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must be logged in");
        }
        
        $relation = Relationship::newFromId($this->getParam('relId'));
        if($relation->getId() == null){
            $this->throwError("This Relationship does not exist");
        }
        $relation->user1 = $this->POST('user1');
        $relation->user2 = $this->POST('user2');
        $relation->university = $this->POST('university');
        $relation->type = $this->POST('type');
        $relation->status = $this->POST('status');
        $relation->thesis = $this->POST('thesis');
        $relation->startDate = $this->POST('startDate');
        $relation->endDate = $this->POST('endDate');
        $relation->comment = $this->POST('comment');
        $relation->update();
        return $this->doGET();
    }
    
    function doDELETE(){
        $person = Person::newFromId($this->getParam('id'));
        $relation = Relationship::newFromId($this->getParam('relId'));
        if($relation->getId() == null){
            $this->throwError("This Relationship does not exist");
        }
        $relation->delete();
        return $relation->toJSON();
    }
}

?>
