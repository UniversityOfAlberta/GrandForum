<?php

/**
 * @package GrandObjects
 */

class Form extends Material {
    
    // Returns the Form with the specified $id
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $sql = "SELECT *
                FROM grand_materials
                WHERE id = '".addslashes($id)."'
                AND type = 'form'";
        $data = DBFunctions::execSQL($sql);
        $form = new Form($data);
        self::$cache[$form->id] = &$form;
        self::$cache[$form->title] = &$form;
        return $form;
    }
    
    // Returns the Form with the specified $id
    static function newFromTitle($title){
        if(isset(self::$cache[$title])){
            return self::$cache[$title];
        }
        $sql = "SELECT *
                FROM grand_materials
                WHERE (title = '".addslashes($title)."'
                OR title = '".str_replace(" ", "_", addslashes($title))."')
                AND type = 'form'";
        $data = DBFunctions::execSQL($sql);
        $form = new Form($data);
        self::$cache[$form->id] = &$form;
        self::$cache[$form->title] = &$form;
        return $form;
    }
    
    // Returns an array of all the Forms
    static function getAllForms(){
        $sql = "SELECT id
                FROM `grand_materials`
                WHERE type = 'form'";
        $data = DBFunctions::execSQL($sql);
        $forms = array();
        foreach($data as $row){
            $forms[] = Form::newFromId($row['id']);
        }
        return $forms;
    }
    
    function Form($data){
        $this->Material($data);
    }
    
    // Returns the Person this Form is for
    function getPerson(){
        $people = $this->getPeople();
        if(count($people) == 1){
            return $people[0];
        }
        else return null;
    }
    
    // Returns the first name of the Person this Form is for
    function getFirstName(){
        $person = $this->getPerson();
        if($person != null){
            $split = $person->splitName();
            return $split['first'];
        }
        return "";
    }
    
    // Returns the last name of the Person this Form is for
    function getLastName(){
        $person = $this->getPerson();
        if($person != null){
            $split = $person->splitName();
            return $split['last'];
        }
        return "";
    }
    
    // Returns the Project this Form is for
    function getProject(){
        $projects = $this->getProjects();
        if(count($projects) == 1){
            return $projects[0];
        }
        return null;
    }
    
    // Returns the University this Form is for
    function getUniversity(){
        $people = $this->getPeople();
        if(count($people) == 1){
            $uni = $people[0]->getUniversity();
            if($uni != null && isset($uni['university'])){
                
                return $uni['university'];
            }
        }
        return "";
    }
    
    // Searches for the given phrase in the table of Forms
    // Returns an array of materials which fit the search
    static function search($phrase){
        $splitPhrase = explode(" ", $phrase);
        $sql = "SELECT title, id
                FROM(SELECT id, title
                           FROM `grand_materials`
                           WHERE title LIKE '%' 
                           AND type = 'form'\n";
        foreach($splitPhrase as $word){
            $sql .= "AND title LIKE '%$word%'\n";
        }
        $sql .= "GROUP BY id, title
                 ORDER BY id ASC) a
                 GROUP BY id";
        $data = DBFunctions::execSQL($sql);
        $materials = array();
        foreach($data as $row){
            $materials[] = array($row['id'], $row['title']);
        }
        $json = json_encode($materials);
        return $json;
    }
}

?>
