<?php

class Researcher extends SerializedCache {
	
	var $name;
	var $key;
	var $firstName;
	var $secondName;
	
	function Researcher($name){
		$this->name = $name;
		parent::SerializedCache($name);
	}
	
	function run(){
		global $wgArticle;
		$name = $this->name;
		if($this->firstName == null || $this->secondName == null){
			if($name == null){
				$userTable = getTableName("user");
				$sql = "SELECT u.user_name as name
					FROM $userTable u
					WHERE user_name = '%{$wgArticle->getTitle()->getText()}'";
				$dbr = wfGetDB(DB_READ);
				$result = $dbr->query($sql);
				$row = $dbr->fetchRow($result);
				$name = $row['name'];
			}
			list($this->firstName, $this->secondName) = sscanf($name, "%s %s");
			$name = str_ireplace(" ", "+", $name);
			if  (in_array  ('curl', get_loaded_extensions())) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://dblp.uni-trier.de/search/author?author=$name");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_RANGE, '0-0');
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
				$line = curl_exec($ch);
				$url = curl_getInfo($ch, CURLINFO_EFFECTIVE_URL);
				$url = str_ireplace(".html", "", $url);
				$urlComp = explode("a-tree/", $url);
				if(array_key_exists(1, $urlComp)){
					$this->key = $urlComp[1];
				}
				curl_close($ch);
			}
		}
		return $this->key;
	}
	
	static function getReasonURL($key){
		return "http://hypatia.cs.ualberta.ca/reason/index.php?action=fromDBLP&DBLPKey=$key&type=researcher";
	}

}




?>
