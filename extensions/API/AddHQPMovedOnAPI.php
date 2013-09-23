<?php

class AddHQPMovedOnAPI extends API{

    function AddHQPMovedOnAPI(){
        $this->addPOST("name",true,"The User Name of the user","UserName");
        $this->addPOST("where",false,"Where the hqp is now","Redmond");
        $this->addPOST("studies",false,"Where the hqp does any further studies","University of British Columbia");
        $this->addPOST("employer",false,"The HQP's employer","Microsoft");
        $this->addPOST("city",false,"The city where the HQP is now","Redmond");
        $this->addPOST("country",false,"The country where the HQP is now","United States");
    }

    function processParams($params){
        $_POST['user'] = str_replace("'", "&#39;", $_POST['name']);
        $_POST['where'] = str_replace("'", "&#39;", $_POST['where']);
        $_POST['studies'] = str_replace("'", "&#39;", $_POST['studies']);
        $_POST['employer'] = str_replace("'", "&#39;", $_POST['employer']);
        $_POST['city'] = str_replace("'", "&#39;", $_POST['city']);
        $_POST['country'] = str_replace("'", "&#39;", $_POST['country']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser;
		$groups = $wgUser->getGroups();
		$me = Person::newFromId($wgUser->getId());

		$person = Person::newFromName($_POST['user']);
		if(!$noEcho){
            if($person->getName() == null){
                echo "There is no person by the name of '{$_POST['user']}'\n";
                exit;
            }
        }
		$supervisors = $person->getSupervisors(true);
		$isSupervisor = false;
		foreach($supervisors as $supervisor){
		    if($supervisor->getName() == $me->getName()){
		        $isSupervisor = true;
		    }
		}
		if($me->isRole(STAFF) || $me->isRole(MANAGER) || count($me->leadership()) > 0 || $isSupervisor || $me->getId() == $person->getId()){
            // Actually Add the Project Member
            $data = DBFunctions::select(array('grand_movedOn'),
                                        array('*'),
                                        array('user_id' => EQ($person->getId())));
            if(count($data) > 0){
                DBFunctions::update('grand_movedOn',
                                    array('`where`' => $_POST['where'],
                                          'studies' => $_POST['studies'],
                                          'employer' => $_POST['employer'],
                                          'city' => $_POST['city'],
                                          'country' => $_POST['country']),
                                    array('user_id' => EQ($person->getId())));
            }
            else{
                DBFunctions::insert('grand_movedOn',
                                    array('user_id' => $person->getId(),
                                          '`where`' => $_POST['where'],
                                          'studies' => $_POST['studies'],
                                          'employer' => $_POST['employer'],
                                          'city' => $_POST['city'],
                                          'country' => $_POST['country']));
            }
            if(!$noEcho){
                echo "{$person->getName()} movedOn added\n";
            }
		}
		else {
		    if(!$noEcho){
			    echo "You do not have the correct permissions to edit this user\n";
			}
		}
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
