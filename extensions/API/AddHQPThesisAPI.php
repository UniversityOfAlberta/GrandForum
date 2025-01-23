<?php

class AddHQPThesisAPI extends API{

    function __construct(){
        $this->addPOST("id",true,"The id of the moved on", 13);
        $this->addPOST("name",true,"The User Name of the user","UserName");
        $this->addPOST("thesis",true,"The id of the thesis","231");
    }

    function processParams($params){
        $_POST['user'] = $_POST['name'];
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
		if($me->isAllowedToEdit($person)){
            if(is_numeric($_POST['id'])){
                DBFunctions::delete('grand_theses',
                                    array('moved_on' => EQ($_POST['id'])));
                if($_POST['thesis'] != "No Thesis"){
                    DBFunctions::insert('grand_theses',
                                        array('user_id' => $person->getId(),
                                              'publication_id' => $_POST['thesis'],
                                              'moved_on' => $_POST['id']));
                }
            }
            else if($_POST['thesis'] != "No Thesis"){
                $data = DBFunctions::select(array('grand_movedOn'),
                                            array('*'),
                                            array('user_id' => EQ($person->getId())));
                $id = 0;
                foreach($data as $row){
                    $id = max($id, $row['id']);
                }
                DBFunctions::insert('grand_theses',
                                    array('user_id' => $person->getId(),
                                          'moved_on' => $id,
                                          'publication_id' => $_POST['thesis']));
            }
            if(!$noEcho){
                echo "{$person->getName()} thesis added\n";
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
