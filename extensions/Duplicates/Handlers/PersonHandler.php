<?php

class PersonHandler extends AbstractDuplicatesHandler {
        
    function PersonHandler($id){
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
        if($person1->getId() != $person2->getId() && !$this->areIgnored($person1->getId(), $person2->getId())){
            if($person1->getName() == $person2->getName() ||
               $person1->getNameForForms() == $person2->getNameForForms()){
                $percent1 = 100;  
            }
            else{
                similar_text($person1->getName(), $person2->getName(), $percent1);
                similar_text($person1->getNameForForms(), $person2->getNameForForms(), $percent2);
                $percent1 = max($percent1, $percent2);
            }
            
            if ($person1->getEmail() == "" || $person2->getEmail() == ""){
                $percent = $percent1;
            }
            else{
                similar_text($person1->getEmail(), $person2->getEmail(), $percent3);
                $percent = round(($percent1 + $percent3)/2);
            }
            if($percent >= 80){
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
                
                $uni1 = $person1->getUniversity();
                $uni2 = $person2->getUniversity();
                
                $buffer = "";
                $buffer .= $this->beginTable($person1->getId(), $person2->getId(), $person1->getNameForForms());
                $buffer .= $this->addDiffHeadRow("{$person1->getNameForForms()}", "{$person2->getNameForForms()}", "{$person1->getUrl()}", "{$person2->getUrl()}");
                $buffer .= $this->addDiffRow("<b>Employee Id:</b> {$person1->getEmployeeId()}", "<b>Employee Id:</b> {$person2->getEmployeeId()}");
                $buffer .= $this->addDiffRow("<b>Email:</b> {$person1->getEmail()}", "<b>Email:</b> {$person2->getEmail()}");
                $buffer .= $this->addDiffRow("<b>Roles:</b> ".implode(" ", $roles1), "<b>Roles:</b> ".implode(" ", $roles2));
                $buffer .= $this->addDiffRow("<b>Title:</b> {$uni1['position']}", "<b>Title:</b> {$uni2['position']}");
                $buffer .= $this->addDiffRow("<b>University:</b> {$uni1['university']}", "<b>University:</b> {$uni2['university']}");
                $buffer .= $this->addDiffRow("<b>Department:</b> {$uni1['department']}", "<b>Department:</b> {$uni2['department']}");
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
    
    function addControls($id1, $id2){
        return "<tr>
                    <td class='duplicateControls'>
                        <input type='button' value='Delete' onClick='delete{$this->upperId}(this, {$id1});' ".CONTROLS_DISABLED." /><br />
                        <input type='button' value='Merge, keeping this user' onClick='merge{$this->upperId}(this, {$id1}, {$id2});' ".CONTROLS_DISABLED." />
                    </td>
                    <td class='duplicateControls'>
                        <input type='button' value='Delete' onClick='delete{$this->upperId}(this, {$id2});' ".CONTROLS_DISABLED." /><br />
                        <input type='button' value='Merge, keeping this user' onClick='merge{$this->upperId}(this, {$id2}, {$id1});' ".CONTROLS_DISABLED." />
                    </td>
                </tr>
                <tr>
                    <td colspan='2' class='duplicateControls'>
                        <input style='vertical-align:middle;' type='button' value='Not Duplicates' onClick='ignore{$this->upperId}(this, {$id1}, {$id2});' ".CONTROLS_DISABLED." />
                    </td>
                </tr>";
    }
    
    function handleMerge(){
        if(isset($_POST['id1']) && $_POST['id2']){
            $person1 = Person::newFromId($_POST['id1']);
            $person2 = Person::newFromId($_POST['id2']);
            Person::merge($person1, $person2);
        }
    }
}

?>
