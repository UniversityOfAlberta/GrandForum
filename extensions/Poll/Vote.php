<?php

class Vote {

	var $user;
	var $frozen;
	
	static function newFromId($id){
		$vTable = getTableName("an_poll_votes");
		$sql = "SELECT * 
                FROM $vTable v
                WHERE v.vote_id = '$id'";
		$rows = DBFunctions::execSQL($sql);
		if(count($rows) > 0){
			$row = $rows[0];
			$user = User::newFromId($row['user_id']);
			$frozen = $row['frozen'];
			
			$vote = new Vote($id, $user, $frozen);
			return $vote;
		}
		else {
			return null;
		}
	}
	
	function Vote($id, $user, $frozen){
		$this->id = $id;
		$this->user = $user;
		$this->frozen = $frozen;
	}
}

?>
