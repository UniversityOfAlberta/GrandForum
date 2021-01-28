<?php

class PublicationSearchAPI extends API{


    function __construct(){
        $this->addGET("keywords", true, "Delimited keywords to find in the title", "Keywords");
        $this->addGET("category", false, "The category of the Publication", "Artifact");
        $this->addGET("type", false, "The type of Publication", "Proceedings Paper");
        $this->addGET("status", false, "The status of the Publication", "Published");
	}

    function processParams($params){

    }

	function doAction(){
		$stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

	    header("Content-type: text/json");
	    $matchedPapers = array();
	    $cat = (isset($_GET['category'])) ? $_GET['category'] : "all";
        $papers = Paper::getAllPapers('all', $cat, 'both');
        $altTitle = str_replace("'", "&#39;", $_GET['keywords']);
    	// Should definitely be using the opensearch functions here.... ah well.
        // ugly split keyword input into individual words using any non-word delimiter
        $splitresult = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $_GET['keywords'] . ' ' . $altTitle, -1, PREG_SPLIT_NO_EMPTY);
        $keywords = array_unique($splitresult);
        // dump short keywords
		foreach ($keywords as $key=>&$value) {
// 			if (strlen($value) < 4) {
// 				unset($keywords[$key]);
// 			}
			$value = strtolower($value);
		}
		// remove stop words
		$keywords = array_diff($keywords, $stopwords); 
		$min_matches = count($keywords);
		if ( isset($_GET['generous']) ) {
			$min_matches /= 2;
		}
		if ( isset($_GET['loose']) ) {
			$min_matches = 1;
		}
		$this->addMessage("Searched for keywords: " . implode(',', $keywords) . ". Required $min_matches to match.");

        foreach($papers as $paper){
//         if( str_ireplace(array('string1', 'string2'), '', $str) != $str ) {
// 			echo 'a match was found';
// 		}
//      perhaps not the most efficient, but easiest: http://stackoverflow.com/questions/8468320/use-stristr-to-match-any-value-in-array-in-a-single-if-condition-without-multipl
			str_ireplace($keywords, '', $paper->getTitle(), $count); 
			
            if((isset($_GET['keywords']) && ($count >= $min_matches)) &&
               (!isset($_GET['category']) || $paper->getCategory() == $_GET['category']) &&
               (!isset($_GET['type']) || $paper->getType() == $_GET['type']) &&
               ($paper->getStatus() == "Published")){
                $matchedPapers[] = array('id' => $paper->getId(),
                						 'count' => $count,
                                         'title' => $paper->getTitle(),
                                         'category' => $paper->getCategory(),
                                         'type' => $paper->getType(),
                                         'status' => $paper->getStatus(),
                                         'authors' => $paper->getAuthorNames(),
                                         'projects' => $paper->getProjectNames(),
                                         'venue' => ( strlen($paper->getVenue()) > 0) ? $paper->getVenue() : "Unknown",
                                         // strangely, reset() returns the first element of an array, when [0] doesn't work. Crazy.
                                         'date' => reset(explode('-', $paper->getDate())),
                                         'url' => $paper->getUrl()); 
            }
        }

        $this->addData("matched", $matchedPapers);
	}
	
	function isLoginRequired(){
		return false;
	}
}

?>
