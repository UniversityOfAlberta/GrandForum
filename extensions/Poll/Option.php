<?php


class Option {

	var $id;
	var $votes;
	var $name;
	
	static function newFromId($id){
		$oTable = getTableName("an_poll_options");
		$sql = "SELECT * 
			FROM $oTable o
			WHERE o.option_id = '$id'";
		$rows = DBFunctions::execSQL($sql);
		if(count($rows) > 0){
			$row = $rows[0];
			$name = $row['option_name'];
			$votes = array();
			
			$vTable = getTableName("an_poll_votes");
			$sql = "SELECT v.vote_id
				FROM $vTable v
				WHERE v.option_id = '$id'";
			$rows1 = DBFunctions::execSQL($sql);
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
	
	function Option($id, $name, $votes){
		$this->id = $id;
		$this->name = $name;
		$this->votes = $votes;
	}
	
	function addVote($user_id){
		$vTable = getTableName("an_poll_votes");
		$sql = "INSERT INTO $vTable (`user_id`, `option_id`, `frozen`)
			VALUES ('$user_id', '{$this->id}', 'true')";
		DBFunctions::execSQL($sql, true);
		
		$sql = "SELECT v.vote_id
			FROM $vTable v
			WHERE v.option_id = '{$this->id}'
			AND v.user_id = '$user_id'";
		$rows = DBFunctions::execSQL($sql);
		@$row = $rows[0];
		$this->votes[] = Vote::newFromId($row['vote_id']);
	}
	
	function getTotalVotes(){
		return count($this->votes);
	}
}
?>
