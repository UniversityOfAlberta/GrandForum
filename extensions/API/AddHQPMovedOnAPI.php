<?php

class AddHQPMovedOnAPI extends API{

    function __construct(){
        $this->addPOST("id",true,"The id of the moved On entry", 12);
        $this->addPOST("name",true,"The User Name of the user","UserName");
        $this->addPOST("studies",false,"Where the hqp does any further studies","University of British Columbia");
        $this->addPOST("employer",false,"The HQP's employer","Microsoft");
        $this->addPOST("city",false,"The city where the HQP is now","Redmond");
        $this->addPOST("country",false,"The country where the HQP is now","United States");
        $this->addPOST("position",false,"The position that the HQP is now","Professor");
        $this->addPOST("effective_date",false,"The date when the moved on occurs","2013-03-14");
    }

    function processParams($params){
        $_POST['id'] = str_replace("'", "&#39;", $_POST['id']);
        $_POST['user'] = str_replace("'", "&#39;", $_POST['name']);
        $_POST['studies'] = str_replace("'", "&#39;", $_POST['studies']);
        $_POST['employer'] = str_replace("'", "&#39;", $_POST['employer']);
        $_POST['city'] = str_replace("'", "&#39;", $_POST['city']);
        $_POST['country'] = str_replace("'", "&#39;", $_POST['country']);
        $_POST['position'] = str_replace("'", "&#39;", $_POST['position']);
        $_POST['effective_date'] = str_replace("'", "&#39;", $_POST['effective_date']);
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser;
		$groups = $wgUser->getGroups();
		$me = Person::newFromId($wgUser->getId());

		$person = Person::newFromName($_POST['user']);
		if(!$noEcho){
            if($person->getName() == null){
                echo "There is no person by the name of '{$_POST['user']}'\n";
                close();
            }
        }
		if($me->isAllowedToEdit($person)){
		    if($_POST['effective_date'] == ""){
		        $_POST['effective_date'] = EQ(COL('CURRENT_TIMESTAMP'));
		    }
            // Actually Add the Project Member
            if(is_numeric($_POST['id'])){
                DBFunctions::update('grand_movedOn',
                                    array('studies' => $_POST['studies'],
                                          'employer' => $_POST['employer'],
                                          'city' => $_POST['city'],
                                          'country' => $_POST['country'],
                                          'position' => $_POST['position'],
                                          'effective_date' => $_POST['effective_date'],
                                          'date_changed' => EQ(COL('CURRENT_TIMESTAMP'))),
                                    array('id' => EQ($_POST['id'])));
            }
            else{
                DBFunctions::insert('grand_movedOn',
                                    array('user_id' => $person->getId(),
                                          'studies' => $_POST['studies'],
                                          'employer' => $_POST['employer'],
                                          'city' => $_POST['city'],
                                          'country' => $_POST['country'],
                                          'position' => $_POST['position'],
                                          'effective_date' => $_POST['effective_date'],
                                          'date_changed' => EQ(COL('CURRENT_TIMESTAMP'))));
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
