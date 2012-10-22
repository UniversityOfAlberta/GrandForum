<?php
class ScoreParser {
	
	private $scores = array();
	
	function __construct($objectName, $query, $algorithm) {
		$url = "http://129.128.184.45:8686/getCentralityScore3.jsp?";
                $url .= "alg=$algorithm";
                $url .= "&query=".urlencode($query);
                $url .= "&object=$objectName";
                $url .= "&score=false";
                //echo $url;
                $this->loadScores($url);
	}
	
	public function loadScores($url) {
		
                //echo $url;
		$xml = simplexml_load_file($url);

                foreach($xml->children() as $child) {
			$attributes = $child->attributes();
			$id = (string) $attributes['id'];
			$score = (double) $attributes['score'];
			
			$this->scores[$id] = $score;
                        //echo "<BR>Add $id = $score";
		}
	}
	
	public function getScore($id) {
		if (isset($this->scores[$id])) {
			return $this->scores[$id];
		}
		
		return 0;
	}
}

//echo "DDD <BR>";
//$t = new ScoreParser('o1', 'SELECT o1.name, r1.name FROM organization o1, researcher r1 WHERE affiliated(r1, o1)', 'CLOSENESS');
?>