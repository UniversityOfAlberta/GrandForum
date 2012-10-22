<?php
/**
 * File for Generating XML maps of wiki pages
 * 
 */

require_once( 'Article.php' );
require_once( 'Revision.php' );
require_once( 'Title.php' );
require_once( 'User.php' );

class MapCreator {
  static $weightingSchemes = array('IncomingLinks', 'OutgoingLinks', 'Authors', 'Revisions', 'Size', 'ViewCount');
  static $weightDivisions = 5;
  static $maxInLinks = null;
  static $maxOutLinks = null;
  static $maxAuthors = null;
  static $maxRevisions = null;
  static $maxCatCount = null;
  static $maxSize = null;
  static $maxViewCount = null;
  static $maxEdits = null;
  static $maxDepth = 4;
  static $userTableGood = null;

  var $curWeightingScheme = null;
  var $authorList = array();
  var $categoryList = array();

  function MapCreator(){
    global $wgShowExceptionDetails;
    $wgShowExceptionDetails = true;

    //self::completeUserEditCounts(); //Only needed if upgrading from old version of MW
  }

  public static function completeUserEditCounts(){
    self::fillInUserEditCounts();
  }
  
  function makeWikiMapXML($article, $depth, $weightingScheme){
    $this->checkUserTableStructure();
    $this->checkParameters($article, $depth, $weightingScheme);
    $this->curWeightingScheme = $weightingScheme;
    
    $mapXml = $this->makeHeaders();
    $mapXml .= $this->formatArticle($article, $depth);
    print $mapXml;

    $this->resetLists();
  }

  function makeAuthorMapXML($userName, $depth, $weightingScheme){
    $this->checkUserTableStructure();
    $this->checkParameters($userName, $depth, $weightingScheme);
    $this->curWeightingScheme = $weightingScheme;

    $mapXml = $this->makeHeaders();
    $user = User::newFromName($userName);
    $param = array();
    if ($user === null)
      $param[$userName] = 0;
    else
      $param[$userName] = $user->getID();

    $mapXml .= $this->formatAuthorList($param, $depth);
    print $mapXml;

    $this->resetLists();
  }

  function makeCategoryMapXML($cat, $depth, $weightingScheme){
    $this->checkUserTableStructure();
    $this->checkParameters($cat, $depth, $weightingScheme);
    $this->curWeightingScheme = $weightingScheme;

    $mapXml = $this->makeHeaders();
    $param = array($cat);
    $mapXml .= $this->formatCategoryList($param, $depth);
    print $mapXml;

    $this->resetLists();
  }

  private function resetLists(){
    unset($this->authorList);
    unset($this->categoryList);
    $this->authorList = array();
    $this->categoryList = array();
  }

  private function makeHeaders(){
    header ( "Content-type: text/xml" );
    $mapXml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
      
EOT;
    return $mapXml;
  }

  private function formatArticle($article, $userDepth, $curDepth=1, $parentArticle=null, $spacer=''){
    $attribs = array();
    $attribs['Name'] = $article->getTitle()->getFullText(); //getDBkey() underscores instead of spaces
    $attribs['Weight'] = $this->getWeight($article);
	 $attribs['Template'] = $this->getTemplate($article);
    $attribs['id'] = $article->getID();
    $mapXml = "";
		 $mapXml = $spacer.wfOpenElement('Article', $attribs)."\n";

		if ($curDepth <= $userDepth){
			$mapXml .= $spacer."\t".wfOpenElement('IncomingLinks')."\n";
			$inLinks = $this->getIncomingLinks($article, $parentArticle);
			$formattedInLinks = $this->formatLinks($inLinks, 'InLink', $userDepth, $curDepth, $article, $spacer."\t\t");
			if ($formattedInLinks != '')
				$mapXml .= $formattedInLinks;
			$mapXml .= $spacer."\t".wfCloseElement('IncomingLinks')."\n";
			
			$mapXml .= $spacer."\t".wfOpenElement('OutgoingLinks')."\n";
			$outLinks = $this->getOutgoingLinks($article, $parentArticle);
			$formattedOutLinks = $this->formatLinks($outLinks, 'OutLink', $userDepth, $curDepth, $article, $spacer."\t\t");
			if ($formattedOutLinks != '')
				$mapXml .= $formattedOutLinks;
			$mapXml .= $spacer."\t".wfCloseElement('OutgoingLinks')."\n";
			
			$mapXml .= $spacer."\t".wfOpenElement('Authors')."\n";
			$authors = $this->getAuthorList($article);
			$formattedAuthorList = $this->formatAuthorList($authors, $userDepth, $curDepth, $article, $spacer."\t\t");
			if ($formattedAuthorList != '')
				$mapXml .= $formattedAuthorList;
			$mapXml .= $spacer."\t".wfCloseElement('Authors')."\n";
			
			$mapXml .= $spacer."\t".wfOpenElement('Categories')."\n";
			$categories = $this->getCategoryList($article);
			$formattedCategoryList = $this->formatCategoryList($categories, $userDepth, $curDepth, $article, $spacer."\t\t");
			if ($formattedCategoryList != '')
				$mapXml .= $formattedCategoryList;
			$mapXml .= $spacer."\t".wfCloseElement('Categories')."\n";
		}
		$mapXml .= $spacer.wfCloseElement('Article')."\n";
    return $mapXml;
  }

  private function formatCategoryList($categories, $userDepth, $curDepth=0, $currentArticle=null, $spacer=''){
    $catString = '';

    foreach ($categories as $cat){
      $catWeight = $this->getWeightForCategory($cat);
      $catString .= $spacer.wfOpenElement('Category', array('Name' => $cat, 'Weight' => $catWeight))."\n";
      if ($curDepth < $userDepth)
	$catString .= $this->getCategorizedArticles($cat, $userDepth, $curDepth+1, $currentArticle, $spacer);
      $catString .= $spacer.wfCloseElement('Category')."\n";
    }

    return $catString;
  }

  private function formatLinks($links, $elementName, $userDepth, $curDepth, $currentArticle, $spacer=''){
    $linkString = '';

    if ($curDepth == $userDepth){
      foreach ($links as $article){
	$title = $article->getTitle();
	$linkWeight = $this->getWeight($article);
	$template = $this->getTemplate($article);
	$linkAge = $this->getArticleAgeRanking($article);//."-".$article->getTouched();
	
	$attribs = array('Name' => $title->getFullText(), 
						  'Weight' => $linkWeight, 
						  'id' => $article->getID(),
						  'Template' => $template,
						  'Age' => $linkAge);
			if($article->isRedirect() == false && $article->getTitle()->getNamespace() != NS_TEMPLATE){
				$linkString .= $spacer.wfElement($elementName, $attribs)."\n";
			}
      }
    }
    
    else{
      foreach ($links as $article){
			if($article->isRedirect() == false && $article->getTitle()->getNamespace() != NS_TEMPLATE){
				$linkString .= $this->formatArticle($article, $userDepth, $curDepth+1, $currentArticle, $spacer);
			}
      }
    }
    return $linkString;
  }

  private function formatAuthorList($authorList, $userDepth, $curDepth=0, $currentArticle=null, $spacer=''){
    $authorString = '';
    
    foreach ($authorList as $name => $id){
      //print "Author: $name\tid:$id\n";
      $authorWeight = $this->getWeightForAuthor($id);
      $isAnon = ($id == 0 ? 1 : 0);
      $authorString .= $spacer.wfOpenElement('Author', array('Name' => $name, 'Weight' => $authorWeight, 'IsAnon' => $isAnon))."\n";
      if ($curDepth < $userDepth)
	$authorString .= $this->getAuthoredArticles($id, $userDepth, $curDepth+1, $currentArticle, $spacer);
      //$authorString .= $spacer.wfElement('Author', array('Name' => $name, 'Weight' => $authorWeight))."\n";
      $authorString .= $spacer.wfCloseElement('Author')."\n";
    }
    //print $authorString;
    return $authorString;
  }


  //Returns array of category names
  private function getCategoryList($article){
    if (array_key_exists($article->getID(), $this->categoryList) && $this->categoryList[$article->getID()]!==null)
      return $this->categoryList[$article->getID()];

    $cNames = array();

    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->select( 'categorylinks', 'cl_to', 'cl_from='.$article->getID(), __METHOD__);

    while ( $row = $dbr->fetchObject( $res ) ) {
      $cNames[] = self::usToSpace($row->cl_to);
    }

    $dbr->freeResult( $res );

    //print_r($aNames);

    $this->categoryList[$article->getID()] = $cNames;
    return $cNames;
  }

  private function getAuthoredArticles($userID, $userDepth, $curDepth, $currentArticle, $spacer){
    $articleList = $spacer.wfOpenElement('Articles')."\n";;

    if ($userID == 0){
      $articleList .= $spacer.wfCloseElement('Articles')."\n";;
      return $articleList;
    }

    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->select( 'revision', 'rev_page', 'rev_user='.$userID, __METHOD__, array('DISTINCT' ));

   while ( $row = $dbr->fetchObject( $res ) ) {
      $articleID = $row->rev_page;
      //if ($currentArticle === null || $currentArticle->getID() != $articleID){
		$title = Title::newFromID($articleID);
		if ($title == null)
			continue;
		$article = new Article($title);
		if($article->isRedirect() == false && $article->getTitle()->getNamespace() != NS_TEMPLATE){
			$articleList .= $this->formatArticle($article, $userDepth, $curDepth+1, $currentArticle, $spacer."\t");
		}
   }

    $dbr->freeResult( $res );

    $articleList .= $spacer.wfCloseElement('Articles')."\n";;

    return $articleList;
  }

  private function getCategorizedArticles($cat, $userDepth, $curDepth, $currentArticle, $spacer){
    $articleList = $spacer.wfOpenElement('Articles')."\n";

    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->select( 'categorylinks', 'cl_from', array('cl_to' => self::spaceToUS($cat)), __METHOD__, array('DISTINCT' ));

    while ( $row = $dbr->fetchObject( $res ) ) {
      $articleID = $row->cl_from;
      if ($currentArticle === null || $currentArticle->getID() != $articleID){
        $title = Title::newFromID($articleID);
        $article = new Article($title);
		  if($article->isRedirect() == false  && $article->getTitle()->getNamespace() != NS_TEMPLATE){
			$articleList .= $this->formatArticle($article, $userDepth, $curDepth+1, $currentArticle, $spacer."\t");
		  }
      }
    }

    $dbr->freeResult( $res );
    $articleList .= $spacer.wfCloseElement('Articles')."\n";;

    return $articleList;
  }


  private function getOutgoingLinks($article, $parentArticle){
    $links = array();

    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->select( 'pagelinks', 'pl_namespace, pl_title', 'pl_from='.$article->getID(), __METHOD__);

    while ( $row = $dbr->fetchObject( $res ) ) {
      $title = Title::makeTitle($row->pl_namespace, $row->pl_title);
      if ($title==null)
	continue;
      $linkedArticle = new Article($title);
      if ($parentArticle === null || $parentArticle->getID() != $linkedArticle->getID()){
	$redirectTitle = $linkedArticle->getRedirectTarget();
	if ($redirectTitle !== null)
	  $linkedArticle = new Article($redirectTitle);
	$links[] = $linkedArticle;
      }
    }

    $dbr->freeResult( $res );

    return $links;
  }

    private function getIncomingLinks($article, $parentArticle){
    $links = array();
    $title = $article->getTitle();

    $dbr =& wfGetDB( DB_SLAVE );
    $safeTitle = $dbr->strencode($title->getDBkey());
    $res = $dbr->select( 'pagelinks', 'pl_from', 
			 'pl_namespace=\''.mysql_real_escape_string($title->getNamespace()).'\' AND pl_title=\''.$safeTitle.'\'',
			  __METHOD__);
        
    while ( $row = $dbr->fetchObject( $res ) ) {
      $linkedTitle = Title::newFromID($row->pl_from);
      if ($linkedTitle == null)
	continue;
      
      $linkedArticle = new Article($linkedTitle);
      $redirectTitle = $linkedArticle->getRedirectTarget();
      if ($redirectTitle !== null)
	$linkedArticle = new Article($redirectTitle);
      //if ($parentArticle === null || $parentArticle->getID() != $linkedArticle->getID())
      $links[] = $linkedArticle;
    }

    $dbr->freeResult( $res );

    return $links;
  }

  //Caches author list until makeWikiMapXML is done.
  private function getAuthorList($article){
    if (array_key_exists($article->getID(), $this->authorList) && $this->authorList[$article->getID()]!==null)
      return $this->authorList[$article->getID()];

    $aNames = array();
    
    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->select( 'revision', 'rev_user_text, rev_user', 'rev_page='.$article->getID(), __METHOD__, array('DISTINCT' ));

    while ( $row = $dbr->fetchObject( $res ) ) {
      $aNames[$row->rev_user_text] = $row->rev_user;//[0]['rev_user_text'];
    }

    $dbr->freeResult( $res );

    //print_r($aNames);

    $this->authorList[$article->getID()] = $aNames;
    return $aNames;
  }

  private function getWeightForAuthor($id){
    if ($id==0)
      return 1;
    
    $user = User::newFromID($id);
    $count = $user->getEditCount();
    $maxEdits = $this->getMaxEdits();
       
    return $this->getGenericWeight($count, $maxEdits);
  }

  private function getWeightForCategory($cat){
    $dbr =& wfGetDB( DB_SLAVE );
    $count = $dbr->selectField('categorylinks', 'count(cl_from)', array('cl_to' => self::spaceToUS($cat)));
    $maxCatCount = $this->getMaxCatCount();
    return $this->getGenericWeight($count, $maxCatCount);
  }

  //From 1-5, inclusive
  private function getArticleAgeRanking($article){
    $touched = $article->getTouched();
    $time = date("YmdHis");
    $diff = $time - $touched;
    
    //echo $time."\n".$touched."\n".$diff."\n\n";

    if ($diff < 1000000)
      return 5; //One day (roughly)
    if ($diff < 7000000)
      return 4; //1 week
    if ($diff < 100000000)
      return 3; //1 month
    if ($diff < 600000000)
      return 2; //6 months
    
    return 1; //Older than 6 months
  }

  private function getMaxEdits(){
    if (self::$maxEdits===null){
      //      $query = 'select max(user_editcount) as max from user';
      $dbr =& wfGetDB( DB_SLAVE );
      $max = $dbr->selectField('user', 'max(user_editcount) as max');
      self::$maxEdits = $max;
    }

    return self::$maxEdits;
  }

  private function getMaxCatCount(){
    if (self::$maxCatCount==null){
      //$query = "select max(count) as max from (SELECT count(cl_from) as count FROM `mw_categorylinks` group by cl_to) a";
      $query = "SELECT count(cl_from) as count FROM `mw_categorylinks` group by cl_to";
      self::$maxCatCount = $this->getMaxFromQuery($query);
    }
    return self::$maxCatCount;
  }

  private function getMaxAuthors(){
    if (self::$maxAuthors==null){
      //"SELECT rev_page, count(distinct rev_user) as count FROM `mw_revision` group by rev_page" to list page associations
      //$query = select max(count) as max from (SELECT rev_page, count(distinct rev_user) as count FROM `mw_revision` group by rev_page) a";
      $query = "SELECT rev_page, count(distinct rev_user) as count FROM `mw_revision` group by rev_page";
      self::$maxAuthors = $this->getMaxFromQuery($query);
    }

    return self::$maxAuthors;
  }

  private function getMaxRevisions(){
    if (self::$maxRevisions==null){
    //$query = SELECT max(count) as max FROM (select count(rev_id) as count, rev_page from `mw_revision` group by rev_page) a;
      $query = "select count(rev_id) as count from `mw_revision` group by rev_page";
      self::$maxRevisions = $this->getMaxFromQuery($query);
    }
    return self::$maxRevisions;
  }

  private function getMaxInLinks(){
    if (self::$maxInLinks==null){
      //$query = select count(pl_from) as count, pl_namespace, pl_title from `mw_pagelinks` group by pl_namespace, pl_title
      $query = "select count(pl_from) as count from `mw_pagelinks` group by pl_namespace, pl_title";
      self::$maxInLinks = $this->getMaxFromQuery($query);
    }
    
    return self::$maxInLinks;
  }

  private function getMaxOutLinks(){
    if (self::$maxOutLinks==null){
      //$query = select count(pl_title), pl_from as count from `mw_pagelinks` group by pl_from
      $query = "select count(pl_title) as count from `mw_pagelinks` group by pl_from";
      self::$maxOutLinks = $this->getMaxFromQuery($query);
    }

    return self::$maxOutLinks;
  }


  //Column from which to get max must be called 'count'
  private function getMaxFromQuery($query){
    $dbr =& wfGetDB( DB_SLAVE );
    $query = "select max(count) as max from ($query) a";
    $res = $dbr->query($query);
    $row = $dbr->fetchObject( $res ); //Will only be one row
    $max = $row->max;
    $dbr->freeResult( $res );

    return $max;
  }

  //Ranges from 1 to 5 (least to most weight)
  private function getWeight($article){
    $weightFunction = 'get'.$this->curWeightingScheme.'WeightForPage';
    return $this->{$weightFunction}($article);
  }
  
  // Returns the first template that the article uses
  private function getTemplate($article){
		$templates = $article->getUsedTemplates();
		if(count($templates) > 0){
			return $templates[0];
		}
		else {
			return false;
		}
  }

  private function getGenericWeight($count, $max){
    $division = $max / self::$weightDivisions;
    return ceil($count / $division);
  }

  private function getIncomingLinksWeightForPage($article){
    $count = count($this->getIncomingLinks($article, null));
    $maxInLinks = $this->getMaxInLinks();
    return $this->getGenericWeight($count, $maxInLinks);
  }

  private function getOutgoingLinksWeightForPage($article){
    $count = count($this->getOutgoingLinks($article, null));
    $maxOutLinks = $this->getMaxOutLinks();
    return $this->getGenericWeight($count, $maxOutLinks);
  }
  
  private function getAuthorsWeightForPage($article){
    $count = count($this->getAuthorList($article));
    $maxAuthors = $this->getMaxAuthors();
    return $this->getGenericWeight($count, $maxAuthors);
  }

  private function getRevisionsWeightForPage($article){
    $dbr =& wfGetDB( DB_SLAVE );
    $count = $dbr->selectField('revision', 'count(rev_text_id)', 'rev_page='.$article->getID());
    $maxRevisions = $this->getMaxRevisions();
    return $this->getGenericWeight($count, $maxRevisions);
  }

  private function getSizeWeightForPage($article){
    if (self::$maxSize===null){
      $dbr =& wfGetDB( DB_SLAVE );
      self::$maxSize = $dbr->selectField('page', 'max(page_len)');
    }

    $dbr =& wfGetDB( DB_SLAVE );
    $count = $dbr->selectField('page', 'page_len', 'page_id='.$article->getID());
    return $this->getGenericWeight($count, self::$maxSize);
  }

  private function getViewCountWeightForPage($article){
    if (self::$maxViewCount===null){
      $dbr =& wfGetDB( DB_SLAVE );
      self::$maxViewCount = $dbr->selectField('page', 'max(page_counter)');
    }

    $views = $article->getCount();
    return $this->getGenericWeight($views, self::$maxViewCount);
  }

  private function checkParameters($identifier, $depth, $weightingScheme){
    if (!isset($identifier)){
      print "WikiMap.MapCreator.checkParameters(): Identifier (article, author, or category) is not set\n";
      exit;
    }
    
    if (!isset($depth) || !is_numeric($depth) || $depth < 1 || $depth > self::$maxDepth){
      print "WikiMap.MapCreator.checkParameters(): Depth is not set properly, not set at all, or not a number\n";
      exit;
    }

    if (!isset($weightingScheme) || !in_array($weightingScheme, self::$weightingSchemes)){
      print "WikiMap.MapCreator.checkParameters():Weighting scheme is not set properly (either not at all, or not to a valid scheme)\n";
      exit;
    }
  }

  //Exits if user table doesn't contain user_editcount column
  private function checkUserTableStructure(){
    if (self::$userTableGood !== null){
      if (self::$userTableGood)
	return;
      else {
	print '[ERROR: User table does not contain the column \'user_editcount\']';
	exit;
      }
    }

    global $wgDBprefix;
    $columns = array();
    
    $dbr =& wfGetDB( DB_SLAVE );
    $query = 'show columns from '.$dbr->tableName( 'user' );
    $res = $dbr->query($query);

    while ( $row = $dbr->fetchObject( $res ) ) {
      if ($row->Field == 'user_editcount'){
	self::$userTableGood = true;
	return;
      }
    }

    $dbr->freeResult( $res );
    self::$userTableGood = false;
    print '[ERROR: User table does not contain the column \'user_editcount\']';
    exit;
  }
  
  private static function fillInUserEditCounts(){
    $dbr =& wfGetDB( DB_SLAVE );
    $res = $dbr->select('user', 'user_id, user_editcount');
    while ($row = $dbr->fetchObject( $res )){
      print 'Examining user '.$row->user_id."<br>";
      if ($row->user_editcount === null || $row->user_editcount == 0){ //Don't update ones that already exist
	$user = $row->user_id;
	self::fillInEditCountsForUser($user);
      }
    }
    $dbr->freeResult( $res );
  }

  private static function fillInEditCountsForUser($userID){
    $user = User::newFromID($userID);
    //$count = User::edits($userID);
    //$user->mEditCount = $count;
    $count = $user->getEditCount(); //This updates the counts in the DB (though doesn't commit them) ...
    print "&nbsp&nbsp User ".$user->getName() ." has count $count <br>";
    $dbw =& wfGetDB( DB_MASTER );
    $dbw->commit(); // ... until this is called.

    //print "Got count $count for user $userID <br>";
  }
 
  static function usToSpace($string){
    $value = ereg_replace('_',' ',$string);
    return $value;
  }

  static function spaceToUS($string){
    $value = ereg_replace(' ','_',$string);
    return $value;
  }

  function stripTags($string){
    $value = ereg_replace("&","&amp;",$string);
    $value = ereg_replace(">","&gt;",$value);
    $value = ereg_replace("<","&lt;",$value);
    $value = ereg_replace("\n","<br>",$value);
    $value = ereg_replace("\t","&nbsp&nbsp&nbsp&nbsp",$value);
    return $value;
  }
  
  function makeStringSafe($string){
    $newString = preg_replace('/[^\w\s\d,-\.]/','',$string);
    return $newString;
  }
}
?>