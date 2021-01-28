<?php

class Vote {

	var $user;
	
	static function newFromId($id){
		$rows = DBFunctions::select(array('grand_poll_votes'),
		                            array('*'),
		                            array('vote_id' => EQ($id)));
		if(count($rows) > 0){
			$row = $rows[0];
			$user = User::newFromId($row['user_id']);
			
			$vote = new Vote($id, $user);
			return $vote;
		}
		else {
			return null;
		}
	}
	
	function __construct($id, $user){
		$this->id = $id;
		$this->user = $user;
	}
}

?>
