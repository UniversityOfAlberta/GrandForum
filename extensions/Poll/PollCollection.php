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
		$rows = DBFunctions::select(array('grand_poll_collection'),
		                            array('*'),
		                            array('collection_id' => EQ($id)));
		if(count($rows) > 0){
			$row = $rows[0];
			$name = $row['collection_name'];
			$selfVote = $row['self_vote'];
			$created = $row['timestamp'];
			$timeLimit = $row['time_limit'];
			$author = User::newFromId($row['author_id']);
			$polls = array();
			$groups = array();
			$rows1 = DBFunctions::select(array('grand_poll_groups'),
			                             array('group_name'),
			                             array('collection_id' => EQ($id)));
			foreach($rows1 as $row1){
				$groups[] = $row1['group_name'];
			}
			
			$poll = new PollCollection($id, $author, $name, $selfVote, null, $groups, $created, $timeLimit);
			return $poll;
		}
		else {
			return null;
		}
	}

	static function getLatest(){
                $rows = DBFunctions::select(array('grand_poll_collection'),
                                            array('collection_id'),
					    array(),
					    array('collection_id'=>"desc"));
		if(count($rows)>0){
		    return self::newFromId($rows[0]['collection_id']);
		}
		return null;
	}

	static function getRandom(){
	        $weekNumber = datediffInWeeks('2000-01-01', date('Y-m-d'));
            $seedNumber = srand($weekNumber);
	        $randomNumber = rand();
            $data = DBFunctions::select(array('grand_poll_collection'),
                                            array('*'),
                                            array()); 
            $polls = array();
            foreach($data as $row){
                $poll = self::newFromId($row['collection_id']);
                if(!$poll->isPollExpired()){
                    $polls[] = $poll;
                }
            }
            $pollCount = count($polls);
            $position = $randomNumber % $pollCount;
            return $polls[$position];
            
        }

     	function getPolls(){
	    if($this->polls == null){
	        $rows = DBFunctions::select(array('grand_poll'),
	                                    array('poll_id'),
	                                    array('collection_id' => EQ($this->id)));
		    foreach($rows as $row){
			    $polls[] = Poll::newFromId($row['poll_id']);
		    }
		    $this->polls = $polls;
		}
		return $this->polls;
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
		    $person = Person::newFromUser($user);
			$groups = $user->getGroups();
			foreach($this->groups as $group){
				if($group == "all"){
					return true;
				}
				if($group == "Student" && ($person->isStudent() || $person->isRoleAtLeast(STAFF))){
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
		if($userId == $this->author->getId() && $this->selfVote == 0){
			return true;
		}
		foreach($this->getPolls() as $poll){
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
		$this->getPolls();
		$total = $this->polls[0]->getTotalVotes(); 
		return $total;
	}
	
	function getTotalPotentialVoters(){
		$users = $this->getPotentialVoters();
		return count($users);
	}
	
	function getPotentialVoters(){
		$ugTable = "mw_user_groups";
		$uTable = "mw_user";
		$users = array();
        $rows = array();
		if(array_search('all', $this->groups) !== false){
	        $rows = DBFunctions::select(array('mw_user_groups' => 'ug',
	                                          'mw_user' => 'u'),
	                                    array('DISTINCT u.user_id',
	                                          'u.user_id',
	                                          'u.user_name',
	                                          'u.user_email'),
	                                    array('u.user_id' => EQ(COL('ug.ug_user'))));
		}
		else if(array_search('Student', $this->groups) !== false){
		    $hqps = Person::getAllPeople(HQP);
		    foreach($hqps as $hqp){
		        if($hqp->isStudent()){
		            $users[] = array('user_id' => $hqp->getId(),
		                             'user_name' => $hqp->getName(),
		                             'user_email' => $hqp->getEmail());
		        }
		    }
		    return $users;
		}
		else {
		    $rows = DBFunctions::select(array('mw_user_groups' => 'ug',
		                                      'mw_user' => 'u'),
		                                array('DISTINCT u.user_id',
		                                      'u.user_id',
		                                      'u.user_name',
		                                      'u.user_email'),
		                                array('u.user_id' => EQ(COL('ug.ug_user')),
		                                      'ug.ug_group' => IN($this->groups)));
		}
		foreach($rows as $row){
			$users[] = $row;
		}
		return $users;
	}
	
	function __construct($id, $author, $name, $selfVote, $polls, $groups, $created, $timeLimit){
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
