<?php

class Poll {

	var $id;
	var $name;
	var $options;
	
	static function newFromId($id){
		$pTable = getTableName("an_poll");
		$sql = "SELECT * 
                FROM $pTable p
                WHERE p.poll_id = '$id'";
		$rows = DBFunctions::execSQL($sql);
		if(count($rows) > 0){
			$row = $rows[0];
			$name = $row['poll_name'];
			$options = array();
			$oTable = getTableName("an_poll_options");
			$sql = "SELECT o.option_id
                    FROM $oTable o
                    WHERE o.poll_id = '$id'";
		    $rows1 = DBFunctions::execSQL($sql);
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
	
	function Poll($id, $name, $options){
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
