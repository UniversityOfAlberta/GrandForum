<?php

    class AvoidFitbitAPI extends RESTAPI{  

        function doGET(){
            $me = Person::newFromWgUser();
            if($me->isRoleAtLeast(ADMIN) && isset($_GET['user'])){
                $me = Person::newFromEmail($_GET['user']);
                if($me == null){
                    $this->throwError("User does not exist", 500);
                }
            }
            $fitbitEnabled = ($me->getExtra('fitbit') != "" && time() < $me->getExtra('fitbit_expires'));
            if($fitbitEnabled){
                $data = DBFunctions::select(array('grand_fitbit_data'),
                                            array('*'),
                                            array('user_id' => $me->getId()));
                return json_encode($data);
            }
            else{
                $this->throwError("User has not authorized with Fitbit", 500);
            }
        }
        
        function doPOST(){
            return $this->doGET();
        }
        
        function doPUT(){
            return $this->doPUT();
        }
        
        function doDELETE(){
            return $this->doDELETE();
        }

        function isLoginRequired(){
            return true;
        }
    }
    
?>
