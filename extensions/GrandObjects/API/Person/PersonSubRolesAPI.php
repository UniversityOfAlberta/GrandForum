<?php

class PersonSubRolesAPI extends RESTAPI {

    function doGET(){
        global $config;
        $person = Person::newFromId($this->getParam('id'));
        $json = array();
        $subRoles = $person->getSubRoles();
        $json['userId'] = $person->getId();
        $json['subroles'] = array();
        foreach($config->getValue('subRoles') as $key => $subRole){
            $json['subroles'][$key] = (in_array($key, $subRoles));
        }
        return json_encode($json);
    }
    
    function doPOST(){
        return $this->doPUT();
    }
    
    function doPUT(){
        $me = Person::newFromWgUser();
        $person = Person::newFromId($this->getParam('id'));
        if($me->isRoleAtLeast(STAFF) && $person->getId() != 0){
            $subRoles = $this->POST('subroles');
            DBFunctions::begin();
            DBFunctions::delete('grand_role_subtype',
                                array('user_id' => EQ($person->getId())));
            foreach($subRoles as $key => $subRole){
                if($subRole == 1 || $subRole == true){
                    DBFunctions::insert('grand_role_subtype',
                                        array('user_id' => EQ($person->getId()),
                                              'sub_role' => EQ($key)));
                }
            }
            DBFunctions::commit();
            return $this->doGET();
        }
        else{
            $this->throwError("Could not modify Sub-Roles");
        }
    }
    
    function doDELETE(){
        return $this->doGET();
    }
}

?>
