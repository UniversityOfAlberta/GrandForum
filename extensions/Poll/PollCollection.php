<?php

class PollCollection {

	var $id;
	var $author;
	var $name;
	var $polls;
	var $groups;
	var $created;
	var $timeLimit;
	var $selfVote;
	
	static function newFromId($id){
		$cTable = getTableName("an_poll_collection");
		$sql = "SELECT * 
			FROM $cTable c
			WHERE c.collection_id = '$id'";
		$rows = DBFunctions::execSQL($sql);
		if(count($rows) > 0){
			$row = $rows[0];
			$name = $row['collection_name'];
			$selfVote = $row['self_vote'];
			$created = $row['timestamp'];
			$timeLimit = $row['time_limit'];
			$author = User::newFromId($row['author_id']);
			$polls = array();
			$groups = array();
			$pTable = getTableName("an_poll");
			$gTable = getTableName("an_poll_groups");
			$sql = "SELECT p.poll_id
				FROM $pTable p
				WHERE p.collection_id = '$id'";
			$rows1 = DBFunctions::execSQL($sql);
			foreach($rows1 as $row1){
				$polls[] = Poll::newFromId($row1['poll_id']);
			}
			
			$sql = "SELECT g.group_name
				FROM $gTable g
				WHERE g.collection_id = '$id'";
			$rows1 = DBFunctions::execSQL($sql);
			foreach($rows1 as $row1){
				$groups[] = $row1['group_name'];
			}
			
			$poll = new PollCollection($id, $author, $name, $selfVote, $polls, $groups, $created, $timeLimit);
			return $poll;
		}
		else {
			return null;
		}
	}
	
	function isPollExpired(){
		if($this->timeLimit == 0){
			return false;
		}
		$today = time();
		if($today > $this->created + $this->timeLimit*60*60*24){
			return true;
		}
		else {
			return false;
		}
	}
	
	function canUserViewPoll($user){
		if($user->isLoggedIn()){
			$groups = $user->getGroups();
			foreach($this->groups as $group){
				if($group == "all"){
					return true;
				}
				if(array_search($group, $groups) !== false){
					return true;
				}
			}
		}
		return false;
	}
	
	function hasUserVoted($userId){
		if($userId == $this->author->getId() && $this->selfVote == 'false'){
			return true;
		}
		foreach($this->polls as $poll){
			$voted = false;
			foreach($poll->options as $option){
				foreach($option->votes as $vote){
					if($vote->user->getId() == $userId){
						$voted = true;
					}
				}
			}
			if($voted == false){
				return false;
			}
		}
		return true;
	}
	
	function getExpirationDate($format="F j, Y  h:i A O"){
		if($this->timeLimit == 0){
			return "Never";
		}
		// Returns the date/time that the poll expires
		$ts = $this->created + $this->timeLimit*60*60*24;
		$date = date($format, $ts);
		return $date; 
	}
	
	function getTotalVotes(){
		// A user must submit all questions, so the first question should be enough to determine the total votes
		$total = $this->polls[0]->getTotalVotes(); 
		return $total;
	}
	
	function getTotalPotentialVoters(){
		$ugTable = getTableName("user_groups");
		if(array_search('all', $this->groups) !== false){
			$sql = "SELECT DISTINCT ug.ug_user
				FROM $ugTable ug";
		}
		else {
			$sql = "SELECT DISTINCT ug.ug_user
				FROM $ugTable ug
				WHERE ug.ug_group IN ('".implode("','", $this->groups)."')";
		}
		$rows = DBFunctions::execSQL($sql);
		return count($rows);
	}
	
	function getPotentialVoters(){
		$ugTable = getTableName("user_groups");
		$uTable = getTableName("user");
		if(array_search('all', $this->groups) !== false){
			$sql = "SELECT DISTINCT u.user_id, u.user_name, u.user_email
				FROM $ugTable ug, $uTable u
				WHERE u.user_id = ug.ug_user";
		}
		else {
			$sql = "SELECT DISTINCT u.user_id, u.user_name, u.user_email
				FROM $ugTable ug, $uTable u
				WHERE u.user_id = ug.ug_user
				AND ug.ug_group IN ('".implode("','", $this->groups)."')";
		}
		$rows = DBFunctions::execSQL($sql);
		foreach($rows as $row){
			$users[] = $row;
		}
		return $users;
	}
	
	function PollCollection($id, $author, $name, $selfVote, $polls, $groups, $created, $timeLimit){
		$this->id = $id;
		$this->author = $author;
		$this->name = $name;
		$this->selfVote = $selfVote;
		$this->polls = $polls;
		$this->groups = $groups;
		$this->created = $created;
		$this->timeLimit = $timeLimit;
	}
}

?>
