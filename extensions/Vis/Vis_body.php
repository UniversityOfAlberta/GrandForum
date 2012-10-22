<?php

class Vis {
  
  function Vis() {
    self::loadMessages();
  }
  
  function loadMessages() {
    static $messagesLoaded = false;
    global $wgMessageCache;
    if ( $messagesLoaded ) return true;
    $messagesLoaded = true;
 
    require( dirname( __FILE__ ) . '/Vis.i18n.php' );
    foreach ( $allMessages as $lang => $langMessages ) {
      $wgMessageCache->addMessages( $langMessages, $lang );
    }
    return true;
  }

  /** Method for catching wiEGO requests and spitting back information */
  function efVisHandleRequest($action, $article){
    global $wgRequest;

    if (!isset($action))
      return true;
    
    $continue = self::handleWikiMapMessage($action, $article); //Will return false if hook processing is to stop
    if (!$continue)
      return false;
    //self::handleCourseDataMessage($action, $article); //Will exit if one is selected

    //self::handleLockMessage($action, $article, $title); //Will exit if one is selected
    //if (self::handleTomuMessage($action, $article))
    //return false;
	
    $title = $wgRequest->getVal( 'title' );
    
    if($action=='makeWiki'){
      require_once(dirname( __FILE__ ) . '/XmlParser.php' );
      $xml = new XmlParser($action);
      $xml->makeWiki();
      //print '<html>';
      //print $xml->makeWiki();
      //print '</html>';
      exit;
    }

    if ($action=='checkLogin'){
      require_once(dirname( __FILE__ ) . '/XmlParser.php' );
      $xml = new XmlParser($action);
      $xml->checkLogin();
      exit;
	
    }

    if ($action=='makeWiego'){
      require_once(dirname( __FILE__ ) . '/XmlParser.php' );
      $xml = new XmlParser($action);
      $xml->makeWiego($article);
      exit;
    }

    /*	 if($action =='makeGO'){ //xml
     require_once(dirname( __FILE__ ) . '/XmlParser.php' );		
     $xml = new XmlParser($action);
     $xml->makeGO($article);
     exit;
     }*/

    if ($action=='getUserName'){
      global $wgUser;

      print '<html>';
      print '[USERNAME:';
      print $wgUser->getName();
      print ']';
      print '</html>';
      exit;
    }
	
    if ($action=='justData'){
      global $wgUser, $wgParser, $wgScriptPath;
      $currentRev = Revision::newFromTitle($article->getTitle());
      $revText = $currentRev->revText();
      
      $safeScriptPath = str_replace('/', '\/', $wgScriptPath);
      
      $parseout = $wgParser->parse($revText, $article->getTitle(), ParserOptions::newFromUser($wgUser));
      $html = '<html><body>'.$parseout->getText().'</body></html>';
      
      $match = '/(<a href="'.$safeScriptPath.'\/index\.php)\/([\w:]*)"/i';
      $replace = '$1?title=$2&action=justData"';
      $html = preg_replace($match, $replace, $html);
      
      print $html;
      exit;
    }
    
    return true;
  }

  function handleWikiMapMessage($action, $article){
    global $wgRequest, $egVisEnableWikiMap, $wgOut, $wgServer, $wgScriptPath, $egVisEnableAuthorDisplay;
	 
    if ($action == 'displayWikiMap' && $egVisEnableWikiMap){
      $baseUrl = $wgServer.''.$wgScriptPath;
      $wikiURL = $baseUrl.'/index.php';
      $title = $article->getTitle();
      $fullTitle = $title->getFullText();

      switch ($title->getNamespace()) {
      case NS_USER:
        $type = "Author";
        break;
      case NS_CATEGORY:
        $type = "Category";
        $fullTitle = $title->getText(); //must not have the Category: prefix
        break;
      default:
        $type = "Article";
      }

      $url  = $baseUrl."/extensions/Vis/Vis_WikiMap/WikiMapFlexProject.html#baseURL=$wikiURL;view=$fullTitle;type=$type";

      if ($egVisEnableAuthorDisplay)
        $url .= ';aInfo=true';

      $html = '<a href="'.$url.'">Break WikiMap out of frame</a>'."\n";
      $html .= '<iframe style="height:100%; width:100%; min-height:600px" src="'.$url.'"></iframe>'."\n";
      
      $wgOut->clearHTML();
      $wgOut->addHTML($html);
      
      return false;
    }
    
    if($action == 'getWikiMap' ){
      require_once(dirname( __FILE__ ) . '/MapCreator.php' );
      $depth = $wgRequest->getVal( 'depth' );
      $weightingScheme = $wgRequest->getVal( 'weightingScheme' );
      $map = new MapCreator();
      $map->makeWikiMapXML($article, $depth, $weightingScheme);
      exit;
    }

    if($action == 'getAuthorMap' ){
      require_once(dirname( __FILE__ ) . '/MapCreator.php' );
      $depth = $wgRequest->getVal( 'depth' );
      $authorName = $wgRequest->getVal( 'author' );
      $weightingScheme = $wgRequest->getVal( 'weightingScheme' );
      $map = new MapCreator();
      $map->makeAuthorMapXML($authorName, $depth, $weightingScheme);
      exit;
    }

    if($action == 'getCategoryMap' ){
      require_once(dirname( __FILE__ ) . '/MapCreator.php' );
      $depth = $wgRequest->getVal( 'depth' );
      $cat = $wgRequest->getVal( 'category' );
      $weightingScheme = $wgRequest->getVal( 'weightingScheme' );
      $map = new MapCreator();
      $map->makeCategoryMapXML($cat, $depth, $weightingScheme);
      exit;
    }

    return true;
  }

  
  /*  function efVisAddVisLinks(&$article){
    global $wgOut;
    require_once( dirname( __FILE__ ) . '/Vis_Article.php' );
    $linksHTML = Vis_Article::getVisHTML($article);
    
    $wgOut->addHTML($linksHTML);
    
    return true;
    }*/

  //Adds a link to wiEGO on the sidebar
  function efVisAddSidebarLinks($skin, &$bar){
    global $wgUser, $wgServer, $wgScriptPath, $egVisEnableWiego;
    // Hide sidebar for anonymous users
    if (!$wgUser->isLoggedIn())
      return true;

    if ($egVisEnableWiego){
      $baseUrl = $wgServer.$wgScriptPath;
      $wikiURL = $baseUrl.'/index.php';
      $url = $baseUrl.'/extensions/Vis/Vis_wiEGO/Wiego.html#wikiURL='.$wikiURL;
      
      $bar['navigation'][] = array('text'   => 'Open wiEGO editor',
				   'href'   => $url,
				   'id'     => 'open_wiego',
				   'active' => '');
    }
    return true;
  }

  //No longer used, as is generally skin-dependent
  function efVisAddToolboxLinks($tpl){
    global $wgUser, $wgServer, $wgScriptPath, $egVisEnableWiego, $egVisWiegoAdded;

    if (!$wgUser->isLoggedIn())
      return true;
  
    if ($egVisEnableWiego){ //Add wiEGO link
      $baseUrl = $wgServer.$wgScriptPath;
      $wikiURL = $baseUrl.'/index.php';
      $url = '<a href="'.$baseUrl.'/extensions/Vis/Vis_wiEGO/Wiego.html#wikiURL='.$wikiURL.'">Open wiEGO</a>';
      $url = '<li id="wiEGO">'.$url.'</li>';
      echo $url;
    }

    return true;
  }

  function efVisAddTabs(&$content_actions){
    global $wgUser, $wgRequest, $wgMessageCache, $wgTitle, $egVisEnableWikiMap;

    //$wgMessageCache->loadAllMessages(); //...and this is not done by the loadMessage() call in the constructor why?  Because strangely, the constructor isn't called before this method is executed by the hook.
	 
    if ($wgTitle->getNamespace() == NS_SPECIAL)
      return true;

    if ($wgUser->isLoggedIn() && $egVisEnableWikiMap){
      $action = $wgRequest->getText( 'action' );
      $content_actions['wikimap'] = array(
					  'class' => ($action == 'displayWikiMap' ? 'selected' : false),
					  'text' => 'Wiki Map',
					  'href' => $wgTitle->getLocalURL('action=displayWikiMap')
					);
    }
    return true;
  }

  /** Adds toolbars to the edit page of an article for creating visualizations. */
  /*  function efVisAddEditToolbars(&$editpage){
    require_once( dirname( __FILE__ ) . '/Vis_EditPage.php' );
    $newBars = new Vis_EditPage();
    $newBars->addToolbars($editpage);
    return true;
    }*/ 

  /** Adds "Save to wiEGO" button to Edit page */
  /* function efVisAddEditButtons(&$editpage, &$buttons){ 
       
    return true;
    } */

  /** Create initial template pages for wiEGO (Node and Edge)*/
  static function efVisCreateTemplatePages(){
    global $egAnnokiCommonPath;

    $templateTitle = Title::newFromText('Node', NS_TEMPLATE);
    
    if (!$templateTitle->exists()){
      require_once("$egAnnokiCommonPath/AnnokiArticleEditor.php");
      
      $templateArticle = new Article($templateTitle);
      $contents = "{{{level}}} {{{name}}} {{{level}}}

{{{text}}}";
      $summary = 'Vis: Created node template page';
      $success = AnnokiArticleEditor::createNewArticle($templateArticle, $contents, $summary);
      }

    $templateTitle = Title::newFromText('Edge', NS_TEMPLATE);

    if (!$templateTitle->exists()){
      require_once("$egAnnokiCommonPath/AnnokiArticleEditor.php");
      
      $templateArticle = new Article($templateTitle);
      $contents = 'temp contents';
      $summary = 'Vis: Created edge template page';
      $success = AnnokiArticleEditor::createNewArticle($templateArticle, $contents, $summary);
      if ($success)
	$success = AnnokiArticleEditor::editArticle($templateArticle, '', 'Vis: emptied edge template page');
    }
  }
}
?>
