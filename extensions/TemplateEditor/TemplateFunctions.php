<?php
/** 
 * Contains the class TemplateFunctions.
 * @package Annoki
 * @subpackage TemplateEditor
 * @author Brendan Tansey
 */	

/** 
 * Functions that can be used to deal with the use of templates on a wiki page. 
 * @package Annoki
 * @subpackage TemplateEditor
 * @author Brendan Tansey
 */
class TemplateFunctions {

  /** 
   * Determine if an editable template (a template with parameters that can be filled in) exists on the given page.
   * @param Title $title The Title object of the page.
   * @return boolean Returns true if the page contains an editable template, false otherwise.
   */
  static function doesPageContainEditableTemplate($title){
    if (count(self::getEditableTemplatesUsedInPage($title, false)) === 1)
      return true;
    return false;
  }

  /** 
   * Get a list of editable templates used in a given page.
   * @param Title $title The Title object of the page.
   * @param boolean $getAllTemplates If true, will return all templates; if false, will only return the first.  Defaults to true.
   * @return array An array of template names.
   */
  static function getEditableTemplatesUsedInPage($title, $getAllTemplates = true){
    $article = new Article($title);
    $content = $article->getContent();
    $matches = array();

    $pattern = '/\{\{([^|]*?)\s*\|[\s\S]*?=[\s\S]*?\}\}/';
    if ($getAllTemplates)
      preg_match_all($pattern, $content, $matches);
    else
      preg_match($pattern, $content, $matches);
    
    if (array_key_exists(1, $matches))
      return $matches[1];

    return $matches;
  }

  /**
   * Gets the set of templates used on a page and returns them as an array of Template objects.
   * @param Title $title The Title object of the page.
   * @return array An array of the Template objects use in on the page.
   */
  static function getAllTemplateInfoAsObjects($title){
    $templates = self::getEditableTemplatesUsedInPage($title);

    $templateObjs = array();
    $usedTemplates = array();

    foreach ($templates as $template){
      $templateObj = new Template($template);

      if (array_key_exists($template, $usedTemplates))
	$templateObj->mInstance = ++$usedTemplates[$template];
      else
	$usedTemplates[$template] = 0;

      $templateString = self::getTemplateString($title, $template, $templateObj->mInstance);

      $vars = self::getTemplateVariables($template);
      
      $values = array();

      foreach ($vars as $var){
        $values[$var] = self::getTemplateVarValue($template, $templateString, $var);
      }

      $templateObj->mInvalidVars = self::getInvalidVariablesUsedInTemplate($template, $templateString);
      $templateObj->mVars = $values;
      $templateObjs[] = $templateObj;
    }

    return $templateObjs;
  }

  /**
   * Get the WikiText for the given instance of the template name on a given page.
   * @param Title $title The page fom which the template string will be extracted.
   * @param string $templateName The name of the template.
   * @param int $instance The instance of the template (the first instance of a template is index 0).
   * @return string The WikiText string, as it is written on the page, that represents the template.
   */
  static function getTemplateString($title, $templateName, $instance){
    $article = new Article($title);
    $content = $article->getContent();
    $matches = array();
    $pattern = "/\{\{$templateName\s*\|[\s\S]*?=[\s\S]*?\}\}/";

    if ($instance == 0){
      preg_match($pattern, $content, $matches);
      if (array_key_exists(0, $matches)){
       	return $matches[0];
      }
    }
    else{
      preg_match_all($pattern, $content, $matches);
      if (array_key_exists(0, $matches)){
        $values = $matches[0];
	if (array_key_exists($instance, $values))
          return $values[$instance];
      }
    }
  }

  /**
   * Returns a list of variables that are used in a template string but are not actually used on the Template page.
   * @param string $templateName The name of the template.
   * @param string $templateString The WikiText representation of a template instance.
   * @return array An associative array of invalid variable names and their values.
   */
  static function getInvalidVariablesUsedInTemplate($templateName, $templateString){
    $invalidVars = array();
    $allVars = self::getAllVariablesUsedInTemplateInstance($templateString);
    $validVars = self::getTemplateVariables($templateName);
    
    foreach($allVars as $var){
      if (!in_array($var, $validVars))
	$invalidVars[$var] = self::getTemplateVarValue($templateName, $templateString, $var);
    }
    
    return $invalidVars;
  }

  /**
   * Get an array of all variables used in a given template string.  Includes invalid variables and 
   * variables that contain no content.
   * @param string $templateString The WikiText representation of a template instance.
   * @return array An indexed array of variable names.
   */
  static function getAllVariablesUsedInTemplateInstance($templateString){
    $matches = array();
    
    $pattern = "/[\s\S]*?\|\s*([\S]*?)\s*=/";

    preg_match_all($pattern, $templateString, $matches);
    
    if (array_key_exists(1, $matches))
      return $matches[1];
    return array();
  }

  /**
   * Extracts the value of a variable from the given template.
   * @param string $templateName The name of the template.
   * @param string $templateString The WikiText representing the template instance.
   * @param string $variable The name of the variable for which the value should be extracted.
   * @return string The value of the variable on success, or an empty string if the variable is not found.
   */
  static function getTemplateVarValue($templateName, $templateString, $variable){
  
    $exploded = explode("{{".$templateName, $templateString);
    $exploded = explode("|$variable = ", $exploded[1]);
    if(count($exploded) > 0){
    	//echo $variable."<br />";
	    $subStr = $exploded[1];
	    $foundPipe = false;
	    $lastPipeIndex = 0;
	    for($i = 0; $i < strlen($subStr) - 1; $i++){
	    	if($subStr[$i].$subStr[$i+1] == "}}"){
	    		return substr($subStr, 0, $i);
	    	}
	    	if($subStr[$i] == "|"){
	    		$foundPipe = true;
	    		$lastPipeIndex = $i;
	    	}
	    	if($subStr[$i] == "=" && $foundPipe == true){
	    		return substr($subStr, 0, $lastPipeIndex);
	    	}
	    }
	}
    	// The following worked for smaller inputs (under 5000 characters), but seemed to break if that number.
	/*
	    $pattern = "/\{\{${templateName}[\s\S]*?\|\s*${variable}\s*=\s*"
	      .'((?:[\s\S]*?(?:\[\[[\s\S]*?]])*[\s\S]*?)*)'
	      .'(?:\}\}|\|)/';
	      
	    //print "backtrack: " . ini_get('pcre.backtrack_limit') . "<br>";
	    //print "pattern = $pattern<br>";
	    //print "template string length: " . strlen($templateString) . "<br>";
	    //ob_flush();
	    //flush();
	   preg_match($pattern, $templateString, $matches);
	    //print "preg_match successful, matches: " . count($matches) . "<br>";
	    
	    if (array_key_exists(1, $matches))
	      return trim($matches[1]);
	*/
    return '';
  }

  //Returns false if $varName is not a valid variable inside the template, or the new object otherwise.
  /**
   * Replaces the value of a variable with something else in a given template instance.
   * @param Template $templateObj The template instance
   * @param string $varName The name of the variable that will get its value replaced
   * @param string $newValue The new value for the variable
   * @return Template The updated template instance, or false if the requested variable doesn't exist in the template
   */
  static function replaceVarValue($templateObj, $varName, $newValue){
    if (!array_key_exists($varName, $templateObj->mVars))
      return false;
    $templateObj->mVars[$varName] = $newValue;
    return $templateObj;
  }

  //This is here as a closure hack.  MediaWiki currently doesn't support PHP 5.3.0+, which is when closures were introduced.
  static $newTemplateObj;
  static $templateMatchCount;

  /**
   * Replaces the previous instance of a template string embedded in $prevContent with the contents of $newTemplateObj.  
   * Returns new contents of article.
   * @param string $prevContent The initial contents of the page containing a template.
   * @param Template $newTemplateObj The template which will replace the existing value in the content string.  Before replacement
   * will occur, the template name and instance number must match.
   * @return string The new page contents.
   */
  static function replaceTemplateString($prevContent, $newTemplateObj){
    $templateName = $newTemplateObj->mName;
    $pattern = "/\{\{$templateName\s*\|[\s\S]*?=[\s\S]*?\}\}/";

    self::$newTemplateObj = $newTemplateObj;
    self::$templateMatchCount = array();

    $content = preg_replace_callback($pattern, 'TemplateFunctions::replaceNthMatch', $prevContent);
    
    //TODO: Once MediaWiki supports PHP 5.3.0+, use closures to do the callback in a much more elegant way.
    /*$callback = function($matches) use ($newTemplateObj, &$templateMatchCount) {
      };*/

    return $content;
  }

  /**
   * Replaces the value of a template string with new values.  Note that this function will be removed once MediaWiki suports PHP 5.3.0+.
   * @param array $matches The matches returned by preg_replace that contain all instances of a template string.
   * @return string The new contents of the page.
   */
  static function replaceNthMatch($matches){
    $templateName = self::$newTemplateObj->mName;

    if (!array_key_exists($templateName, self::$templateMatchCount))
      self::$templateMatchCount[$templateName] = 0;
    else
      self::$templateMatchCount[$templateName]++;

    if (self::$templateMatchCount[$templateName] == self::$newTemplateObj->mInstance)
      return self::$newTemplateObj->toWikiText(true);
    else
      return $matches[0];
  }

  /**
   * Replaces the previous instance of a template string that forms the content of the given article with the contents of $newTemplateObj.  
   * Returns new contents of article.
   * @param Title $title The page on which the replacement will occur.
   * @param Template $newTemplateObj The template which will replace the existing value in the content string.  Before replacement
   * will occur, the template name and instance number must match.
   * @return string The new page contents.
   */
  static function replaceTemplateStringForTitle($title, $newTemplateObj){
    $article = new Article($title);
    $content = $article->getContent();

    return self::replaceTemplateString($content, $templateObj);
  }

  /** 
   * Extract the names of all templates in use on the system from the database. 
   * @return array All of the titles of Template pages in the database that aren't redirects.
   */
  static function getAllTemplates(){
    $pages = array();
    $nsId = NS_TEMPLATE;
    $dbr = wfGetDB( DB_REPLICA );
    $result = $dbr->select('page', array('page_title'), array('page_namespace' => $nsId, 'page_is_redirect' => 0) );
    while ($row = $dbr->fetchRow($result)) {
      $pages[] = $row[0]; //str_replace('_', ' ', $row[0]);
    }
    $dbr->freeResult($result);

    return $pages;
  }

  /**
   * Find all templates that contain variables (ie, those templates that have editable instantiations).
   * This will be a subset of those templates returned from TemplateFunctions::getAllTemplates().
   * @return array All of the titles of Template pages in the database that aren't redirects and contain editable variables.
   **/
  static function getAllEditableTemplates(){
    $templates = self::getAllTemplates();
    $editableTemplates = array();
    
    foreach ($templates as $template){
      if(count(self::getTemplateVariables($template, false)) === 1)
	$editableTemplates[] = $template;
     }
    
    return $editableTemplates;
  }

  /**
   * Gets a list of the variables in a template.
   * @param string $templateName The name of the template (the page name in the Template namespace).
   * @param boolean $getAllVariables If false, just returns the first variable; if true, returns all variables.
   * @return array A list of variable names as strings.
   */
  static function getTemplateVariables($templateName, $getAllVariables = true){
    $title = Title::newFromText('Template:'.$templateName);
    if ($title ==  null || !$title->exists()) {
      print "Template $templateName does not exist.";
      die("Template $templateName does not exist.");
    }
    $article = new Article($title);
    $content = $article->getContent();
    $matches = array();

    $pattern = '/\{\{\{([^\}]*)\}\}\}/';
    if ($getAllVariables)
      preg_match_all($pattern, $content, $matches);
    else
      preg_match($pattern, $content, $matches);
    
    if (array_key_exists(1, $matches))
      return array_unique($matches[1]);

    return array_unique($matches);
  }

  /**
   * Get a list of all of the pages that use a given template.
   * @param string $templateName The name of the template for which to search.
   * @return array A list of all of the fully-qualified page names that use the given template.
   */
  static function getAllPagesUsingTemplate($templateName){
    global $egAnnokiCommonPath, $wgDBprefix;
    
    require_once("$egAnnokiCommonPath/AnnokiDatabaseFunctions.php");

    $templateName = DBFunctions::escape($templateName);
    $query = "select tl_from from ${wgDBprefix}templatelinks where tl_title='$templateName' and tl_namespace='".NS_TEMPLATE.'\'';
        
    $pageIDs = AnnokiDatabaseFunctions::getQueryResultsAsArray($query, 'tl_from');
    
    $pageNames = array();

    foreach ($pageIDs as $pageID){
      $title = Title::newFromID($pageID);
      if ($title->exists())
	$pageNames[] = $title->getFullText();
    }
    
    sort($pageNames);

    return $pageNames;
  }

  /**
   * Turns an array of page names that use a given template into a wiki-formatted list string.
   * @param string $templateName The name of the template
   * @param array $pages A list of fully-qualified pages that use the template
   * @return string The wiki-formatted list of pages, including links to each page.
   */
  static function createPageListWikiText($templateName, $pages){
    global $wgScriptPath, $wgServer;

    $wikitext = "== List of all pages that use the template $templateName ==\n";
    
    if (empty($pages))
      $wikitext .= "No pages found.\n
If this is not the result you were expecting, [${wgServer}$wgScriptPath/index.php?title=Template:$templateName&action=refreshTemplateLinks click here to refresh the template links table].\n
'''Warning''': This operation could take a long time on a wiki with many pages.\n";
    
    foreach ($pages as $page){
      $wikitext .= "* [[$page]]\n";
    }
    
    return $wikitext;
  }

  /**
   * Creates a new wiki page using a template.  Will prompt the user to select a Template, saves the page using that template,
   * then edits the page usng the graphical template editor.
   * @param Article $article The new (as of yet uncreated) page.
   */
  static function createPageFromTemplate($article){
    global $wgOut, $wgRequest;
    
    $wgOut->setPageTitle('Create page from template');
    $showChooser = true;
    $chooseTemplateErrorMessage = false;
    $selectedTemplate = '';
    $html = '';
    $wikiText = '';

    if($wgRequest->wasPosted()){
      $selectedTemplate = $wgRequest->getVal('templateChooser');
      if ($selectedTemplate == '')
	$chooseTemplateErrorMessage = true;
      else{
	if ($wgRequest->getCheck('previewTemplatePage'))
	  $wikiText = self::getPreviewPage($selectedTemplate);
	if ($wgRequest->getCheck('previewTemplateCode'))
	  $wikiText = self::getPreviewCode($selectedTemplate);
	if ($wgRequest->getCheck('chooseTemplate')){
	  $showChooser = false;
	  self::createPageAndEdit($selectedTemplate, $article);
	  return;
	}
      }
    }

    if ($showChooser){
      $html .= Xml::openElement('form', array('method' => 'post', 'name' => 'createFromTemplate'))."\n";
      
      if ($chooseTemplateErrorMessage)
	$html .= '<span style="color: red; font-weight:bold;">Please select a template from the following list before continuing</span><br/>'."\n";
      else
	$html .= '<b>Please select a template from the following list:</b><br/>';
      $html .= "<table>\n";
      $html .= '<tr><td rowspan=3>'.self::createHTMLTemplateList($selectedTemplate)."</td>\n";
      $html .= '<td style="vertical-align:bottom"><br/>'.Xml::submitButton( "Preview page", array( 'name' => 'previewTemplatePage'))."</td>\n";
      $html .= "</tr><tr>\n";
      $html .= '<td style="vertical-align:top">'.Xml::submitButton( "Preview wiki text", array( 'name' => 'previewTemplateCode'))."</td>\n";
      $html .= "</tr><tr>\n";
      $html .= '<td style="vertical-align:bottom">'.Xml::submitButton( "Create page using selected template", array( 'name' => 'chooseTemplate'))."</td>\n";
      $html .= "</tr>\n";
      $html .= "</table>\n";
      
      $html .= Xml::closeElement('form');
    }

    if ($html != '' && $wikiText != '')
      $html .= '<b>Note that the following is only a preview.  To create the page, click "Create page using selected template" above.</b><hr style="height:1px;"/><br/>';

    $wgOut->addHTML($html);
    $wgOut->addWikitext($wikiText);
  }

  /**
   * Returns HTML displaying the wiki text representation of a template.
   * @param string $templateName The name of the template for which the wiki text will be generated.
   * @return string An HTML representation of the wiki text (wiki text wrapped in pre tags).
   */
  static function getPreviewCode($templateName){
    $code = '<pre>'.self::getPreviewPage($templateName).'</pre>';
    return $code;
  }

  /**
   * Returns a wiki text representation of a template.
   * @param string $templateName The name of the template for which the wiki text will be generated.
   * @return string The wiki text representation of a template.
   */
  static function getPreviewPage($templateName){
    $template = new Template($templateName);
    $template->populateVars();

    return $template->toWikiText();
  }

  /**
   * Creates a new page with a template instance as contents, then opens the template editor window for that page.
   * @param string $templateName The name of the template to be used on the page.
   * @param Article $article The article to be created and edited.
   */
  static function createPageAndEdit($templateName, $article){
    global $egAnnokiCommonPath, $wgOut;
    require_once($egAnnokiCommonPath.'/AnnokiArticleEditor.php');

    $template = new Template($templateName);
    $template->populateVars();
    
    AnnokiArticleEditor::createNewArticle($article, $template->toWikiText(), 'Created article from template '.$templateName, null, 0);
    $title = $article->getTitle();
    $redirect = $title->getLocalURL('action=edit&editType=template');
    $wgOut->redirect($redirect);
  }

  /**
   * Creates a 'select' HTML element containing a list of all of the templates on the wiki.
   * @param string $selectedTemplate The template that should be selected by default.
   * @return string The HTML corresponding to the list of possible templates.
   */
  static function createHTMLTemplateList($selectedTemplate){
    $templates = self::getAllTemplates();
    $id = 'templateChooser';
    return AnnokiHTMLUtils::makeSelector($templates, $id, false, $selectedTemplate);
   }
}

?>
