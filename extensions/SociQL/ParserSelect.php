<?php
/**
 *
 * Parse a Select statement of a SociQL query
 * @package ?
 * @author Diego Serrano
 * @since 23.05.2010 04:55:00
 */
include_once "ParserSociQL.php";

class ParserSelect {

	/**
	 * Parse the Select SociQL query
         * @access private
	 * @return array Tree structure of the query
	 */
	private static function doParse() {
		
		$tree = array('Command' => ParserSociQL::getCurrentToken()->getValue());  //usually select
		
		ParserSociQL::getNextToken();
		
		$tree = ParserSociQL::parseColumns($tree);
		
		if (ParserSociQL::getCurrentToken()->getValue() != 'FROM') {
			return ParserSociQL::raiseError('Expected "FROM"');
		}
		
		//FROM
		ParserSociQL::getNextToken();
		$tree['ObjectNames'] = array();
		
		if (ParserSociQL::getCurrentToken()->getType() == 'IDENTIFIER' ||
			(ParserSociQL::getCurrentToken()->getType() == 'GLOBAL_RES_WORD' && ParserSociQL::getCurrentToken()->getValue() == 'ONT')) {
			
			while (ParserSociQL::getCurrentToken() != null && (ParserSociQL::getCurrentToken()->getType() == 'IDENTIFIER' || (ParserSociQL::getCurrentToken()->getType() == 'GLOBAL_RES_WORD' && ParserSociQL::getCurrentToken()->getValue() == 'ONT'))){
							
				//Ontology prefix
				if (ParserSociQL::getCurrentToken()->getType() == 'GLOBAL_RES_WORD' && ParserSociQL::getCurrentToken()->getValue() == 'ONT') {
					
					$tree['ObjectTypes'][] = 'ONTOLOGY';
					
					ParserSociQL::getNextToken();
					
					if (!(ParserSociQL::getCurrentToken()->getType() == 'SEPARATION_MARK' && ParserSociQL::getCurrentToken()->getValue() == '.')) {
				    	return ParserSociQL::raiseError('Expected ontology delimiter');
				    }
				    
				    $nextToken = ParserSociQL::getNextToken();
				    if($nextToken == null)
				    {
					break 2;
				    }
				
				} else {
					$tree['ObjectTypes'][] = 'NETWORK';
				}
				
				//Detection of identifiers
			    $tree['ObjectNames'][] = ParserSociQL::getCurrentToken()->getValue();
				
			    ParserSociQL::getNextToken();
			    
			    if (ParserSociQL::getCurrentToken()->getType() == 'IDENTIFIER') {
			    	$tree['ObjectAliases'][] = ParserSociQL::getCurrentToken()->getValue();
			    } else {
			    	return ParserSociQL::raiseError('Expected object alias');
			    }
			    
			    ParserSociQL::getNextToken();
			    
			    $token = ParserSociQL::getCurrentToken();
			    if($token !=null)
			    {
				if (ParserSociQL::getCurrentToken()->getValue() == ',') {
					ParserSociQL::getNextToken();
				}

			    }
				
			}
		} else {
			return ParserSociQL::raiseError('Expected object name');
		}
		
		//WHERE
		$tree = array_merge($tree, ParserSociQL::parseSearchClause());
		
                //ORDER BY
                $tree = array_merge($tree, ParserSociQL::parseOrderClause());
                
                //LIMIT
		if (ParserSociQL::getCurrentToken() != null) {
                    if (ParserSociQL::getCurrentToken()->getType() == 'GLOBAL_RES_WORD' && ParserSociQL::getCurrentToken()->getValue() == 'LIMIT') {
                        ParserSociQL::getNextToken();

                        if (ParserSociQL::getCurrentToken()->getType() == "NUMERIC") {
                            $tree['Limit'] = ParserSociQL::getCurrentToken()->getValue();
                        } else {
                            return ParserSociQL::raiseError('Expected a numeric value');
                        }
                    }
                }
                
		//print_r ($tree);
		return $tree;

	}

        /**
	 * Calls doParse()
         * @access public
	 * @return array Tree structure of the query
	 */
	public static function parse(){
		return self::doParse();
	}	
}
?>