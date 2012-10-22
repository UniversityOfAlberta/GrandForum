<?php

class TextReplace extends SpecialPage{
  //TODO: Implement caching of lists so the database doesn't have to be queried for the entire list on every reload
  
  function TextReplace($listed = true) {
    SpecialPage::SpecialPage("TextReplace");
    self::loadMessages();
  }
  
  function loadMessages() {
    static $messagesLoaded = false;
    global $wgMessageCache;
    if ( $messagesLoaded ) return true;
    $messagesLoaded = true;
    
    require( dirname( __FILE__ ) . '/TextReplace.i18n.php' );
    foreach ( $allMessages as $lang => $langMessages ) {
      $wgMessageCache->addMessages( $langMessages, $lang );
    }
    return true;
  }

  public function isRestricted() {
    return true;
  }
  
  public function userCanExecute($user){
    if (in_array('sysop', $user->getGroups())){
      return true;
    }
    return false;
  }
  
  public static function createTextReplacementTable() {
    global $wgDBprefix, $wgUnitTestMode, $egAnnokiTablePrefix;
    
    $tableType = ""; //regular table by default
    if ($wgUnitTestMode) {
      $tableType = "TEMPORARY"; //for unit tests we want the table to only exist for the duration of the test
    }
    
    $query = "
                CREATE $tableType TABLE IF NOT EXISTS `${wgDBprefix}${egAnnokiTablePrefix}text_replacement` (
                `match_text` VARCHAR( 255 ) NOT NULL ,
                `replacement` VARCHAR( 255 ) NOT NULL ,
                PRIMARY KEY ( `match_text` )
                ) ENGINE = InnoDB DEFAULT CHARSET=utf8
                ";
                
    $dbw =& wfGetDB(DB_MASTER);
    $dbw->query($query);
  }

  function execute($par ) {
    global $wgRequest, $wgOut, $wgUser, $egAnnokiTablePrefix, $wgTitle;

    $this->setHeaders();

    $wgOut->setPageTitle(wfMsg('trTitle'));
    $wgOut->setSubtitle(wfMsg('trSub'));

    if (!in_array('sysop', $wgUser->getGroups())) {
      $wgOut->showPermissionsErrorPage( array(
					      $wgUser->isAnon()
					      ? 'userrights-nologin'
					      : 'userrights-notallowed' ) );
      return;
    }

    $this->makeBegin();			
	
    if($wgRequest->wasPosted() ) {

      if ($wgRequest->getCheck('addMatchRow')) {
	$match = $wgRequest->getVal('match');
	$replace = $wgRequest->getVal('replace');
	
	if (strlen($match) == 0 || strlen($replace) == 0){
	  return; //TODO: Print error message
	}
	
	$dbw =& wfGetDB(DB_MASTER);
	$success = $dbw->insert("${egAnnokiTablePrefix}text_replacement", array('match_text' => $match, 'replacement' => $replace));

	$wgOut->redirect( $wgTitle->getFullUrl() );
      }

      if ($wgRequest->getCheck('rmMatchRow')) {
	$id = $wgRequest->getVal('rm_id');

	$dbw =& wfGetDB(DB_MASTER);
        $success = $dbw->delete("${egAnnokiTablePrefix}text_replacement", array('match_text' => "$id"));
	$wgOut->redirect( $wgTitle->getFullUrl() );
      }
    }						
  }
			
  function makeBegin(){
    global $wgUser, $wgOut, $wgRequest;
    $wgOut->setRobotpolicy( 'noindex,nofollow' );

    $out = '';
    $out .='<br>';
    $out .='<tr><p><b>Replacements:</b></p></tr>';
    $out .='<table style="border-style: solid; border-width: 1px;">';
    $out .= '<tr><th>Match wiki text</th><th>Replacment wiki text</th></tr>';
    $out .= $this->buildMatchAdder();
    $out .= $this->buildMatchTable();
    $out .= "</table>";
		
    $wgOut->addHTML($out);
  }					

  function buildMatchAdder(){
    $out = Xml::openElement( 'form', array( 'method' => 'post',  'name' => 'addMatch' ) );	
    $out .= '<tr>';
    $out .= '<td>'.Xml::input('match').'</td><td>'.Xml::input('replace').'</td>';
    $out .= '<td width = "50">'.Xml::submitButton( 'Add match', array( 'name' => 'addMatchRow')).'</td>';
    $out .= '</tr>';
    $out .= Xml::closeElement('form');

    return $out;
  }

  // Display existing replacements
  function buildMatchTable(){
    global $egAnnokiTablePrefix;

    $dbr =& wfGetDB(DB_SLAVE);
    $matches = $dbr->select("${egAnnokiTablePrefix}text_replacement", 'match_text, replacement');
    $out = '';

    while ($row = $dbr->fetchObject($matches)){
      $out .= Xml::openElement( 'form', array( 'method' => 'post',  'name' => 'rmMatch' ) );
      $out .= '<tr>';
      $out .= '<td>'.$row->match_text.'</td><td>'.$row->replacement.'</td>';
      $out .= '<td width="50">'.Xml::submitButton( 'Remove', array( 'name' => 'rmMatchRow') ).'</td>';
      $out .= Xml::hidden('rm_id', $row->match_text);
      $out .= '</tr>';
      $out .= Xml::closeElement('form');
    }

    $dbr->freeResult($matches);


    return $out;
  }

  function replaceText(&$parser, &$text, &$strip_state) {
    global $egAnnokiTablePrefix, $wgArticle, $wgTitle;
    if($wgTitle == null || $wgArticle == null){
        return true;
    }

    //    $start = microtime(true);

    // Check to make sure this call is done on an edit
    $title = $parser->getTitle();
    $article = new Article($title);
    if ($article->getContent() != $text)
      return true;

    $matchArray = array();
    $replaceArray = array();

    $dbr =& wfGetDB(DB_SLAVE);
    $matches = $dbr->select("${egAnnokiTablePrefix}text_replacement", 'match_text, replacement');
    
    while ($row = $dbr->fetchObject($matches)){
      $matchArray[] = $row->match_text;
      $replaceArray[] = $row->replacement;
    }
    $dbr->freeResult($matches);
    
    $pageTable = getTableName("page");
    $nsTable = getTableName("an_extranamespaces");
    
    $title = str_replace("'", "&#39;", $wgTitle->getText());
	$title = str_replace("\"", "&quot;", $title);
    
    $matches2 = $dbr->query("SELECT page_title, nsName FROM $pageTable, $nsTable WHERE nsId = page_namespace AND page_is_redirect <> '1' AND ((nsID >= '122' AND nsID <= '135')) AND page_title <> '$title'");
    
    while ($row = $dbr->fetchObject($matches2)){
    	$match = str_replace("_", " ", $row->page_title);
    	$replace = "[[{$row->nsName}:{$row->page_title}| $match]]";
	if(stristr($text, $match) != false){
		$pos = 0;
		while(($pos = strpos($text, $match, $pos)) != false){
			$offset = 1;
			$isPartOfALink = false;
			$matchLen = strlen($match);
			while($pos - $offset >= 0 && $text[$pos - $offset] != ']'){
				if($offset == 1 && $pos + $matchLen < strlen($text)){
					if(ctype_alpha($text[$pos-$offset]) == true || ctype_alpha($text[$pos+$matchLen]) == true){
						$isPartOfALink = true;
						break;
					}
				}
				if($text[$pos - $offset] == '['){
					$isPartOfALink = true;
					break;
				}
				$offset++;
			}
			
			if(!$isPartOfALink){
				$text = substr_replace($text, $replace, $pos, $matchLen);
				$pos += strlen($replace);
    			}
    			else {
    				$pos++;
    			}
			
		}
	}
    }
    $dbr->freeResult($matches2);
    $text = str_replace($matchArray, $replaceArray, $text);

        //print microtime(true) - $start;

    return true;
  }
  
  function externalLinks(&$parser, &$text){
  	$text = str_replace("class=\"external free\"", "class=\"external free\" target=\"_blank\"", $text);
  	$text = str_replace("class=\"external text\"", "class=\"external free\" target=\"_blank\"", $text);
  	return true;
  }
  
}



?>
