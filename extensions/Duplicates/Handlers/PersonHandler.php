<?php

class PersonHandler extends AbstractDuplicatesHandler {
        
    function __construct($id){
        $this->AbstractDuplicatesHandler($id);
    }
    
    static function init(){
        $personHandler = new PersonHandler('people');
    }
    
    function getArray(){
        $people = Person::getAllPeople();
        return $people;
    }
    
    function getArray2(){
        return $this->getArray();
    }
    
    function showResult($person1, $person2){
        global $wgServer, $wgScriptPath;
        if(!$this->areIgnored($person1->getId(), $person2->getId())){
            similar_text($person1->getName(), $person2->getName(), $percent1);
            similar_text($person1->getEmail(), $person2->getEmail(), $percent2);
            $percent = round(($percent1 + $percent2)/2);
            if($percent >= 75){
                $projs1 = $person1->getProjects();
                $projs2 = $person2->getProjects();
                $projects1 = array();
                $projects2 = array();
                foreach($projs1 as $proj){
                    $projects1[] = $proj->getName();
                }
                foreach($projs2 as $proj){
                    $projects2[] = $proj->getName();
                }
                
                $r1 = $person1->getRoles();
                $r2 = $person2->getRoles();
                $roles1 = array();
                $roles2 = array();
                foreach($r1 as $role){
                    $roles1[] = $role->getRole();
                }
                foreach($r2 as $role){
                    $roles2[] = $role->getRole();
                }
                
                $papers1 = $person1->getPapers();
                $papers2 = $person2->getPapers();
                $paperTitles1 = array();
                $paperTitles2 = array();
                foreach($papers1 as $paper){
                    $paperTitles1[] = $paper->getTitle();
                }
                foreach($papers2 as $paper){
                    $paperTitles2[] = $paper->getTitle();
                }
                sort($paperTitles1);
                sort($paperTitles2);
                
                $uni1 = $person1->getUniversity();
                $uni2 = $person2->getUniversity();
                
                $buffer = "";
                $buffer .= $this->beginTable($person1->getId(), $person2->getId(), $person1->getNameForForms());
                $buffer .= $this->addDiffHeadRow("{$person1->getNameForForms()}", "{$person2->getNameForForms()}", "{$person1->getUrl()}", "{$person2->getUrl()}");
                $buffer .= $this->addDiffRow($person1->getEmail(), $person2->getEmail());
                $buffer .= $this->addDiffRow(implode(" ", $roles1), implode(" ", $roles2));
                $buffer .= $this->addDiffRow(implode(" ", $projects1), implode(" ", $projects2));
                $buffer .= $this->addDiffRow($person1->getNationality(), $person2->getNationality());
                $buffer .= $this->addDiffRow($person1->getGender(), $person2->getGender());
                $buffer .= $this->addDiffRow($uni1['position'], $uni2['position']);
                $buffer .= $this->addDiffRow($uni1['university'], $uni2['university']);
                $buffer .= $this->addDiffRow($uni1['department'], $uni2['department']);
                $buffer .= $this->addDiffNLRow(implode("\n", $paperTitles1), implode("\n", $paperTitles2));
                $buffer .= $this->addDiffNLRow($person1->getProfile(), $person2->getProfile());
                $buffer .= $this->addControls($person1->getId(), $person2->getId());
                $buffer .= $this->endTable();
                return $buffer;
            }
        }
    }
    
    function handleDelete(){
        $sql = "UPDATE mw_user
                SET deleted = '1'
                WHERE user_id = '{$_POST['id']}'";
        DBFunctions::execSQL($sql, true);     
    }
}

?>
