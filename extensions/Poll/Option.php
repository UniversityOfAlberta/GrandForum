<?php


class Option {

    var $id;
    var $votes;
    var $name;
    
    static function newFromId($id){
        $rows = DBFunctions::select(array('grand_poll_options'),
                                    array('*'),
                                    array('option_id' => EQ($id)));
        if(count($rows) > 0){
            $row = $rows[0];
            $name = $row['option_name'];
            $votes = array();
            $rows1 = DBFunctions::select(array('grand_poll_votes'),
                                         array('vote_id'),
                                         array('option_id' => EQ($id)));
            foreach($rows1 as $row1){
                $votes[] = Vote::newFromId($row1['vote_id']);
            }
            
            $option = new Option($id, $name, $votes);
            return $option;
        }
        else {
            return null;
        }
    }
    
    function __construct($id, $name, $votes){
        $this->id = $id;
        $this->name = $name;
        $this->votes = $votes;
    }
    
    function addVote($user_id){
        DBFunctions::insert('grand_poll_votes',
                            array('user_id' => $user_id, 
                                  'option_id' => $this->id));
        $rows = DBFunctions::select(array('grand_poll_votes'),
                                    array('vote_id'),
                                    array('option_id' => EQ($this->id),
                                          'user_id' => EQ($user_id)));
        @$row = $rows[0];
        $this->votes[] = Vote::newFromId($row['vote_id']);
    }
    
    function getTotalVotes(){
        return count($this->votes);
    }
}
?>
