<?php

class Poll {

    var $id;
    var $name;
    var $options;
    
    static function newFromId($id){
        $rows = DBFunctions::select(array('grand_poll'),
                                    array('*'),
                                    array('poll_id' => EQ($id)));
        if(count($rows) > 0){
            $row = $rows[0];
            $name = $row['poll_name'];
            $options = array();
            $rows1 = DBFunctions::select(array('grand_poll_options'),
                                         array('option_id'),
                                         array('poll_id' => EQ($id)));
            foreach($rows1 as $row1){
                $options[] = Option::newFromId($row1['option_id']);
            }
            
            $poll = new Poll($id, $name, $options);
            return $poll;
        }
        else {
            return null;
        }
    }
    
    function __construct($id, $name, $options){
        $this->id = $id;
        $this->name = $name;
        $this->options = $options;
    }
    
    function getOption($id){
        foreach($this->options as $option){
            if($option->id == $id){
                return $option;
            }
        }
        return null;
    }
    
    function getTotalVotes(){
        $total = 0;
        foreach($this->options as $option){
            $total += $option->getTotalVotes();
        }
        return $total;
    }
    
    function getTotalOptions(){
        return count($this->options);
    }
    
    function getAvgVotes(){
        if($this->getTotalOptions() == 0){
            return 0;
        }
        else{
            return $this->getTotalVotes()/$this->getTotalOptions();
        }
    }
}

?>
