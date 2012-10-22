<?php
/**
 * Analyses the lexical units of the query
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */

include_once "Token.php";
include_once "ParserSociQL.php";


class Lexer {
    
    private $reservedWords = array("SELECT", "EXPLORE", "MAP", "FROM", "WHERE", "UNDEF", "AND", "AS", "ONT", "ORDER", "BY", "LIMIT");
    private $reservedSymbols = array("=", ">", "<", "!=", ">=", "<=", "><", "<>");
    private $groupMarks = array("(", ")");
    private $separationMarks = array(".", ",");
    private $rankingAlgs = array("INDEGREE", "OUTDEGREE", "DEGREE", "CLOSENESS", "BETWEENNESS", "PAGERANK");

    private $curLine = 0;
    private $curPos = 0;
    private $curRelPos = 0;
    	
    private $query = "";

    /**
     * Constructs a object for Lexical Analysis
     */
    public function __construct($string) {
    	$this->query = $string;
    }
    
    
    /**
     * Get the current line being analyzed
     * @return int Current line
     */
    public function getCurrentLine() {
    	return $this->curLine + 1;
    }
    
    /**
     * Get the current position being analyzed
     * @return int Current position
     */
    public function getCurrentPosition() {
    	return $this->curPos;
    }
    
    /**
     * Get the current relative position being analyzed
     * @return int Current relative position
     */
    public function getCurrentRelativePosition() {
    	return $this->curRelPos;
    }
    
    /**
     * Get the current snippet from the query
     * @return string Current snippet
     */
    public function getCurrentSnippet() {
        $leftPos = $this->curPos;
        if ($this->curPos > 10) {
            $leftPos = $this->curPos - 10;
        }

        return '...' . substr($this->query, $leftPos, 20) . '...';
    }
    
    /**
     * Get the next token from the query
     * @return Token token
     */
    public function getNextToken() {
	
       $tokenArray = array();
    	$startToken = $this->curPos;
    	$endToken = $this->curPos;
    	$openQuotation = false;
    	
    	if ($this->curPos < strlen($this->query)) {
	    	for ($i=$this->curPos; $i<strlen($this->query); $i++) {
	    		
	    		//process new line character
	    		while ($this->query[$i] == "\n" && $startToken == $i) {
	    			$this->curLine++;
	    			$startToken++;
	    			$endToken++;
	    			
	    			$curRelPos = 0;
	    			$i++;
	    		}
	    		
	    		while ($this->query[$i] == " " && $startToken == $i) {
	    			$startToken++;
	    			$endToken++;
	    			
	    			$curRelPos = 0;
	    			$i++;
	    		}
	    		
	    		//process quotation marks to treat the following text as a unit
	    		if ($this->query[$i] == "\"") {
	    			if ($openQuotation) {
	    				$openQuotation = false;
	    			} else {
	    				$openQuotation = true;
	    			}	
	    		}
	    		
	    		
	    		if (!$openQuotation) {
					
	    			//blank space separation
		    		if ($this->query[$i] == " " || $this->query[$i] == "\n") {
		    			
		    			$endToken = $i - 1;
		    			$curToken = substr($this->query, $startToken, $endToken-$startToken+1);
		    			
		    			if ($endToken >= $startToken && trim($curToken) != "") {
		    				$this->curPos = $i + 1;
		    				$curRelPos = $i + 1;
		    				
		    				return $this->identifyToken($curToken);
		    			} else {
		    				//echo "<BR>$i ++++";
		    				//$this->curPos = $i++;
		    				//$curRelPos = $i;
		    				//$i++;
		    				//echo " $i";
		    			}
		    		}
		    		
		    		//separation by punctuation
		    		//keep the punctuation and save previous token
		    		if ((in_array($this->query[$i], $this->reservedSymbols) || in_array($this->query[$i], $this->groupMarks) || 
		    			in_array($this->query[$i], $this->separationMarks) || $this->query[$i] == '!') && !$openQuotation) {
		    			
		    			if (($i - $startToken) > 0) {
			    			$endToken = $i - 1;
			    			$curToken = substr($this->query, $startToken, $endToken-$startToken+1);
			    			
			    			$this->curPos = $i;
			    			$curRelPos = $i;
			    			
			    			return $this->identifyToken($curToken);
			    			
		    			} else {
			    			$startToken = $i;
			    			
		    				//In case of a symbol of 2 chars
			    			if ($i < strlen($this->query) - 1) {
				    			if (in_array($this->query[$i+1], $this->reservedSymbols)) {
				    				$i++;	
				    			}
			    			}
			    			$endToken = $i;
			    			$curToken = substr($this->query, $startToken, $endToken-$startToken+1);
			    			
			    			$this->curPos = $i + 1;
			    			$curRelPos = $i + 1;
			    			
			    			return $this->identifyToken($curToken);
		    			}
		    		}
	    		}
	    		
	    		
	    		//final token
	    		if ($i==strlen($this->query)-1) {
	    			$curToken = substr($this->query, $startToken);
	    			
	    			if ($endToken >= $startToken && $i < strlen($this->query)) {
	    				$this->curPos = $i + 1;
	    				$curRelPos = $i + 1;
	    				
	    				return $this->identifyToken($curToken);
	    			
	    			} 
	    		}
	    	}
    	
    	} else { 
    		return null;
    	}
    	
    	//$this->printArray($stringArray);
    }
    
    
    /**
     * Identify the type of token represented by a string
     * @return Token token
     */
    public function identifyToken($stringToken) {
    	$stringToken = trim($stringToken);
    	
    	$regExpIdentifier = "[A-z][A-z0-9]*";
    	$regExpString = "\"(.)+\"";
    	
    	$capString = strtoupper($stringToken);
        $token = new Token();
            
        if (in_array($capString, $this->reservedWords)) {
            //is token a reserved word?
            $token->setType("GLOBAL_RES_WORD");
            $string = $capString;
            
        } else if (in_array($capString, $this->reservedSymbols)) {
            //is token a symbol?
            $token->setType("RES_SYMBOL");

        } else if (in_array($stringToken, $this->groupMarks)) {
            //is token a group mark? ()
            $token->setType("GROUP_MARK");

        } else if (in_array($stringToken, $this->separationMarks)) {
            //is token a separator? ,.
            $token->setType("SEPARATION_MARK");

        } else if (in_array($stringToken, $this->rankingAlgs)) {
            //
            $token->setType("ORDER_CRITERIA");

        } else if (is_numeric($stringToken)) {
            //is token a number?
            $token->setType("NUMERIC");

        } else if (preg_match("/^".$regExpString."$/", $stringToken)) {
            //is token a string?
            $token->setType("STRING");
            $stringToken = $this->formatString($stringToken);

        } else if (preg_match("/^".$regExpIdentifier."$/", $stringToken)) {
            //is token an identifier?
            $token->setType("IDENTIFIER");

        } else {
            $token->setType("UNDEFINED");
        }
        
        $token->setValue($stringToken);

        //echo "<br>".$token->getValue()." = ".$token->getType();
        return $token;
    }
        
	/**
     * Format the string to handle correctly the quotation marks
     * @return string Formatted string
     */
    private function formatString($string) {
    	$string = str_replace("'", "\'", $string);
    	$string = str_replace('"', "'", $string);
    	return $string;
    }
}


?>
