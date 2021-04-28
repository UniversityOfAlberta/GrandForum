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
        global $config;
        $me = Person::newFromWgUser();
        $person = Person::newFromId($this->getParam('id'));
        if($me->isRoleAtLeast(STAFF) && $person->getId() != 0){
            MailingList::unsubscribeAll($person);
            $subRoles = $this->POST('subroles');
            DBFunctions::begin();
            foreach($subRoles as $key => $subRole){
                if($subRole == 1 || $subRole == true){
                    if(count(DBFunctions::select(array('grand_role_subtype'),
                                                 array('*'),
                                                 array('user_id' => EQ($person->getId()),
                                                       'sub_role' => EQ($key)))) == 0){
                        // Add if doesn't exist yet
                        DBFunctions::insert('grand_role_subtype',
                                            array('user_id' => EQ($person->getId()),
                                                  'sub_role' => EQ($key)));
                    }
                }
                else{
                    // Delete
                    DBFunctions::delete('grand_role_subtype',
                                        array('user_id' => EQ($person->getId()),
                                              'sub_role' => EQ($key)));
                }
            }
            DBFunctions::commit();
            Person::$subRolesCache = array();
            MailingList::subscribeAll($person);
            return $this->doGET();
        }
        else{
            $this->throwError("Could not modify ".Inflect::pluralize($config->getValue('subRoleTerm')));
        }
    }
    
    function doDELETE(){
        return $this->doGET();
    }
}

?>
