<?php
/** 
 * Contains the class TemplateEditor.
 * @package Annoki
 * @subpackage TemplateEditor
 * @author Brendan Tansey and Veselin Ganev
 */	

/**
 * This class is responsible for allowing pages that use templates to be edited using a series of custom edit fields rather than the regular mediawiki edit box
 * @package Annoki
 * @subpackage TemplateEditor
 * @author Brendan Tansey and Veselin Ganev
 */
class TemplateEditor {
  /** Templates used in a page.  Array of Template objects. */
  private $templates = array();

   /**
    * Receive and deal with action commands relating to the Template Editor.  Called by hook UnknownAction.
    * @param string $action The action command.
    * @param Article $article The associated Article.
    * @return boolean true (to continue hook execution).
    */
  public static function efTEHandleRequest($action, $article){
    global $wgRequest, $wgOut;
	  
    if (!isset($action))
      return true;

    if ($action=='getPagesForTemplate'){
      $template = $wgRequest->getVal('title');
      $title = Title::newFromDBkey($template);
      if (!$title || !$title->exists())
	return true;
 
      $pages = TemplateFunctions::getAllPagesUsingTemplate($title->getDBkey());
      $wgOut->clearHTML();
      $wgOut->addWikiText(TemplateFunctions::createPageListWikitext($title->getDBkey(), $pages));
      $wgOut->setPageTitle($title->getText().' Instance List');
      return false;
      //      exit;
    }

    if ($action=='refreshTemplateLinks'){
      self::regenerateTemplateLinksTable($article);
      return false;
    }

    if ($action=='createFromTemplate'){
      TemplateFunctions::createPageFromTemplate($article);
      return false;
    }

    /*    if ($action=='getAllTemplates'){//Testing only; TODO: Remove me once complete.
      print_r(TemplateFunctions::getAllTemplates());
      exit;
      } */
    
    /*    if ($action=='getAllEditableTemplates'){//Testing only; TODO: Remove me once complete.
      print_r(TemplateFunctions::getAllEditableTemplates());
      exit;
      }*/

    /*    if ($action=='getVariables'){ //Testing only; TODO: Remove me once complete.
      print_r(TemplateFunctions::getTemplateVariables('VWTemplate'));
      exit;
      }*/

    /*    if ($action=='getEditableTemplatesUsedInPage'){ //Testing only; TODO: Remove me once complete.
      $title = Title::newFromText('BT:Test');
      print_r(TemplateFunctions::getEditableTemplatesUsedInPage($title));
      exit;
      }*/

    return true;
  }

  /** 
	* Include JavaScript used in extension.  Called by hook BeforePageDisplay.
	* @param OutputPage &$out The page to be output.
	* @return boolean true (to continue hook execution).
	*/
  static function addJS(&$out){
    global $wgScriptPath;
    $out->addScript("\n         <script type='text/javascript' src='" .
		    $wgScriptPath . '/extensions/TemplateEditor/Templates.js' . "'></script>");
    return true;
  }

  /** 
	* Add 'edit templates' tab to pages that have editable templates and 'instance list' tab to template pages.  Called by hook SkinTemplateContentActions.
	* @param array &$content_actions The array of content actions.
	* @return boolean true (to continue hook execution).
	*/
  static function addTETabs($skin, &$content_actions ) {
    global $wgRequest, $wgTitle, $wgArticle;

    if ($wgTitle->getNamespace() == NS_TEMPLATE && $wgTitle->exists()) { //Page itself is a template
      $tabName = 'Instance List';
      $actionCmd = 'getPagesForTemplate';
      $check = $wgRequest->getVal('action') == 'getPagesForTemplate';
      $listAction =  array(
			   'class' => 'action',
			   'text' => $tabName,
			   'href' => $wgTitle->getLocalURL( 'action=' . $actionCmd)
			   );

      $content_actions['actions']['instance list'] = $listAction;

      return true;
    }

    if (!$wgTitle->exists()){
      $tabName = 'Create from template';
      $actionCmd = 'createFromTemplate';
      $check = $wgRequest->getVal('action') == $actionCmd;
      $createAction =  array(
                           'class' => 'action',
                           'text' => $tabName,
                           'href' => $wgTitle->getLocalURL( 'action=' . $actionCmd)
                           );

      $keys = array_keys($content_actions['views']);
      $index = array_search('edit', $keys);

      if ($index === false)
	$content_actions['views'][$tabName] = $createAction;
      else
	self::array_put_to_position($content_actions['views'], $createAction, $index+1, $tabName);

      return true;
    }

    if (!TemplateFunctions::doesPageContainEditableTemplate($wgTitle))
      return true;
    $tabName = 'Edit Template';
    $actionCmd = 'edit&editType=template';
    //$action = $wgRequest->getText( 'action' );
    $check = self::isTECall();
    $teAction = array(
		      'class' => 'action',
		      'text' => $tabName,
		      'href' => $wgTitle->getLocalURL( 'action=' . $actionCmd)
		      );
    
    $keys = array_keys($content_actions['views']);
    $index = array_search('edit', $keys);
    if ($index === false)
      return true;
    
    if ($check)
      //$content_actions['edit']['class'] = false;
      unset( $content_actions['views']['edit'] ); // only this to remove an action
    self::array_put_to_position($content_actions['views'], $teAction, $index+1, 'editTemplate');
    return true;
  }
  
  function array_put_to_position(&$array, $object, $position, $name = null)
{
        $count = 0;
        $return = array();
        $inserted = false;
        foreach ($array as $k => $v) 
        {   
                // insert new object
                if ($count == $position)
                {   
                        if (!$name) $name = $count;
                        $return[$name] = $object;
                        $inserted = true;
                }   
                // insert old object
                $return[$k] = $v; 
                $count++;
        }   
        if (!$name) $name = $count;
        if (!$inserted) @$return[$name] = $object;
        $array = $return;
        return $array;
}

  /** 
	* Attach information to requests sent from the template editor that they should as well be treated as template editor requests. 
	* Called by hook EditPageBeforeEditButtons.
	* @param EditPage &$editpage The current EditPage object
	* @param array &$buttons An array of the edit buttons found below the editing box ("Save", "Preview", "Live", and "Diff").
	* @return boolean true (to continue hook execution).
	*/
  function modifySaveButton(&$editpage, &$buttons){
    if (TemplateEditor::isEditorNeeded($editpage->getArticle()->getTitle()))
      $buttons['save'] .= Xml::element('input', array('id' => 'teStatus', 'name' => 'teStatus', 'value' => 'true', 'type' => 'hidden'), '');
    return true;
  }

  /**
   * Checks if the custom editor is needed
   * @param Title $title The page for which the check is performed.
   * @deprecated All this function does is return the value of TemplateEditor::isTECall(), completely ignoring the $title parameter.
   */
  private static function isEditorNeeded($title) {	
    return TemplateEditor::isTECall();
    //return false;
    //return TemplateFunctions::doesPageContainEditableTemplate($title);
  }

  /** 
	* See if we are either the initial template editor call (by clicking the tab) or a subsequent call made from a template editor. 
	* @return boolean true if the current call requires the template editor, false otherwise.
	*/
  private static function isTECall(){
    global $wgRequest;

    $type = $wgRequest->getVal('editType');
    $check = $wgRequest->getCheck('teStatus');
  
    if ($type != 'template' && !$check)
      return false;

    return true;
  }
 
  /**
   * Updates the template calls in the given text to have the current values, as represented by the $this->templates array of Templates.
   * @param string $currentText The current text of the page.
   * @return string The updated text of the page.
   */
  private function updateTemplateCall($currentText) {
    foreach ($this->templates as $template){
      $currentText = TemplateFunctions::replaceTemplateString($currentText, $template);
    }
    
    return $currentText;
  }

  /**
   * Update the edit textbox (editPage->textbox1) from request values if this is a preview.  Called by hook EditPage::showEditForm:initial.
   * @param EditPage $editPage The current EditPage object.
   * @return true (to continue hook execution).
   */
  public function updateFromRequest(&$editPage) {
    global $wgRequest;
  	if (!TemplateEditor::isEditorNeeded($editPage->mTitle))
  		return true;

  	$this->templates = TemplateFunctions::getAllTemplateInfoAsObjects($editPage->mTitle);
  	if ($this->parseWgRequest()) {
  		$editPage->textbox1 = $this->updateTemplateCall($editPage->textbox1);
  	}
  	return true;
  }

  /**
   * Parses the mediawiki $wgRequest and updates the state of the editor with any values currently completed in the request.
   * This is needed for previews and for when there are errors in any of the fields (so that the user doesn't have to retype everything...)
   * @return boolean true if the request was posted, false otherwise.
   */
  private function parseWgRequest() {
  	global $wgRequest;

  	if (!$wgRequest->wasPosted())
  		return false;

  	foreach ($this->templates as &$template){
  		foreach ($template->mVars as $var => &$value){
  			$value = $wgRequest->getText($template->mName.$template->mInstance.'|'.$var.'MAIN');
  		}
  	}
  	 
  	return true;
  }

  /**
   * Builds and displays the custom edit fields.  Hides the regular text edit box.  Called by hook EditPage::showEditForm:fields. 
   * @param EditPage &$editPage The current EditPage object.
   * @param OutputPage &$OutputPage The current OutputPage.
   * @return boolean true (to continue hook execution).
   */
  public function showCustomFields(&$editPage, &$output) {
    if (TemplateEditor::isTECall() && TemplateEditor::isEditorNeeded($editPage->mTitle)){
      $editPage->editFormTextBeforeContent .= $this->getFormElements();
      //Hide main edit box and toolbar
      $editPage->editFormTextBottom .= "\n".'<iframe onload="hideEditBox()" width="0" height="0" style="display:none;"></iframe>';
      return true;
    }
    
    //$editPage->editFormTextBeforeContent .= "\n<a href=\"blah\">Add Template</a>\n";
    
    return true;
  }

  /**
   * Converts the fields of each template in $templates to HTML form elements.
   * @return string The HTML form elements.
   */
  public function getFormElements() {        
    $ret = '<fieldset><legend><b>Template Editor</b> '."\n";
    $ret .= "</legend>\n<div id=\"templateEditor\" style=\"display: block\"><table width=\"100%\">\n";
		
    foreach ($this->templates as $template){
      $instanceName = $template->mName.$template->mInstance;
      $ret .= "<tr><td colspan=\"2\"><b>$template->mName (instance ".($template->mInstance + 1).')</b> ';
      //$ret .= "<a href=\"#\" onclick=\"showhide('$instanceName', 'block');\">[+/-]</a>";
      $ret .= "</td></tr>\n";
      $ret .= "<tr><tds><table width=\"100%\" id=\"$instanceName\"><tr><td>\n";
		  
      if (count($template->mInvalidVars) > 0){
	$ret .= '<tr><td colspan="2"><span style="color:red;"><b>Warning:</b> this template uses variables that are not defined in the template definition.  If this is not intentional, please move the contents to a valid variable or modify the template.  Invalid variables and their values are listed below in red.</span></td></tr>';
	$ret .= $this->createVarEditLines($template, $template->mInvalidVars, 'color:red; font-weight: bold;', true, true);
      }
      
      $ret .= $this->createVarEditLines($template, $template->mVars);
      $ret .= "<tr><td>&nbsp;</td></tr></table></td></tr>\n";
    }

    $ret .= "</table></div></fieldset>";
    return $ret;
  }

  /**
   * Creates a table row for each variable in the given array from the given template.
   * @param Template The template which contains the variables.
   * @param array The array of variables.  Usually either $template->mVars or $template->mInvalidVars.
   * @param string $textStyle CSS for the style entry for the text preceeding the edit box.
   * @param $readOnly true if the edit box is to be read only, false otherwise.
   * @param $skipEmpty true if variables that have no value are not to be displayed, false otherwise.
   * @return string HTML corresponding to the generated table row.
   */
  function createVarEditLines($template, $vars, $textStyle='color:black;', $readOnly=false, $skipEmpty=false){
    $textExtryBoxBegin = "%s<br />";
    $fieldFormat = "<input type='text' id='%s' name='%s' %s style='width:100%%; display:%s' onkeyup=\"copyText('%s', '%s');\" value='%s' />";
    $textAreaFormat = "<TEXTAREA id='%s' name='%s' rows='4' %s style='width:100%%;height:200px; display:%s' onkeyup=\"copyText('%s', '%s');\" >%s</TEXTAREA>";
    $textEntryBoxEnd = "<a id='%s' href=\"#%s\" onclick=\"swapElements('%s', '%s');\">[+/-]</a>\n";

    $ret = '';
    $sentenceTable = getTableName("sentence");
    $pageTable = getTableName("page");
    $nestedQuery = "SELECT MAX(ss.rev_id)
    		    FROM $sentenceTable ss, $pageTable pp
		    WHERE pp.page_namespace = '10'
		    AND pp.page_title = '{$template->mName}'
		    AND ss.page_id = pp.page_id";
    $sql ="SELECT s.content
           FROM $sentenceTable s, $pageTable p
           WHERE p.page_namespace = '10'
           AND p.page_title = '{$template->mName}'
           AND s.page_id = p.page_id
           AND s.rev_id IN ($nestedQuery)";
           
    $dbr = wfGetDB(DB_REPLICA);
	$result = $dbr->query($sql);

	$rows = array();
	while ($row = $dbr->fetchRow($result)) {
		$rows[] = $row;
	}
	$p = new Parser();
	$text = "__NOTOC__\n__NOEDITSECTION__";
   	foreach ($rows as $row){
    		$text .= $row['content'];
    	}
    	$parsedText = $p->parse($text, new Title(), new ParserOptions())->getText();

	
    if ($readOnly)
      $readOnlyText = "readonly='true'";
    else
      $readOnlyText = '';
    
    $lastPos = 0;
    foreach ($vars as $var => $value){
    	/*
      if(strpos($var, "checkbox") == 0){
      	$textAreaFormat = "<input type='checkbox' id='%s' name='%s' value ='true'" />";
      }
      */
      if ($skipEmpty && $value == '')
	continue;
      
      $entryBoxName = $template->mName.$template->mInstance.'|'.$var;
      
      if (strlen($value) > TEXTFIELD_CUTOFF || strpos($value, "\n") !== false){
	$fieldDisplay = 'none';
	$textAreaDisplay = 'block';
        $areaName = $entryBoxName.'MAIN';
	$fieldName = $entryBoxName.'SECONDARY';
      }
      else {
	$fieldDisplay = 'block';
	$textAreaDisplay = 'none';
	$fieldName = $entryBoxName.'MAIN';
	$areaName = $entryBoxName.'SECONDARY';
      }
      
      
      $newPos = stripos($parsedText, "{{{".$var."}}}");
      $substr = substr($parsedText, $lastPos, $newPos - $lastPos);
      $lastPos = ($newPos) + strlen("{{{".$var."}}}");
      
      $value = htmlspecialchars($value, ENT_QUOTES);
      $var = htmlspecialchars($var, ENT_QUOTES);
      
      $len = strlen($value);
      if($len > 0 && $value[$len - 1] == "\n"){
      	$value = substr($value, 0, $len - 1);
      }
      
      $ret .= sprintf($textExtryBoxBegin, $substr);
      $ret .= sprintf($textEntryBoxEnd, $var, $var, $fieldName, $areaName);
      $ret .= sprintf($fieldFormat, $fieldName, $fieldName, $readOnlyText, $fieldDisplay, $fieldName, $areaName, $value);
      $ret .= sprintf($textAreaFormat, $areaName, $areaName, $readOnlyText, $textAreaDisplay, $areaName, $fieldName, $value);
    }
    
    return $ret;
  }


  /**
   * Called by the hook EditFilter before the page is updated. Lets mediawiki know if it should proceed with saving the page or if there
   * are any errors in the custom fields.
   * @param EditPage $editPage The current EditPage object.
   * @param string $text The contents of the edit box (not used)
   * @param string $section The section being edited (not used)
   * @param string $error An error message to return (not used)
   * @return true (to continue hook execution).
   */
  public function onEditFilter($editPage) {
    return $this->updateFromRequest($editPage); // I guess the ::initial hook doesn't get called in this case??
    /*if (!$this->validate()) {
     //TODO also show the error when previewing?
     $error .= "<p class='error'>There were some errors while processing the form you submitted. Please see below for more information</p>";
     //by setting $error to something it will cause mediawiki to display the edit form again (with our custom fields)
     //the actual errors will be shown beside the textfields 	
     }*/
  }

  /** 
   * Sometimes the templatelinks table gets completely clobbered (usually after running the maintenance script refreshLinks.php).
   * Running this function will rebuild the table properly, though only if run through the web interface, not the command line.
   * @param Article $article The page this function will redirect to once the regeneration is complete.
   */ 
  private static function regenerateTemplateLinksTable($article){
  	global $egAnnokiCommonPath, $wgDBprefix, $wgParser, $wgOut, $wgScriptPath, $wgServer;
  	require_once($egAnnokiCommonPath.'/AnnokiDatabaseFunctions.php');
  	$verbose = false; //Set me to true for testing.

  	if ($verbose)
  	print '<b>Re-generating templatelinks table.  This may take a few minutes; please be patient.</b><br/><br/>';

  	//$displayInterval = 100;
  	$query = "select page_id from ${wgDBprefix}page order by page_id";
  	$ids = AnnokiDatabaseFunctions::getQueryResultsAsArray($query, 'page_id');
    
  	foreach ($ids as $id){
  		$title = Title::newFromID($id);
  		$revision = Revision::newFromTitle($title);
  		$options = new ParserOptions;

  		$parserOutput = $wgParser->parse( $revision->getText(), $title, $options, true, true, $revision->getId());
  		$templates = $parserOutput->getTemplates();

  		$update = new LinksUpdate( $title, $parserOutput, false );
  		$update->doUpdate();
  		//$dbw->immediateCommit();

  		if ($verbose && !empty($templates) && array_key_exists(NS_TEMPLATE, $templates)){ //Only print out pages in Template namespace.
  			$templates = $templates[NS_TEMPLATE];
  			$print = false;
  			foreach ($templates as $templateName => $templateID)
  			if ($templateID != 0){
  				$print = true;
  				break;
  			}
  			 
  			if ($print){
  				print $title->getFullText()."<br>";
  				foreach($templates as $templateName => $templateID)
  				print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$templateName.": $templateID<br>";
  				print "<br>";
  			}
  		}
  	}

  	$title = $article->getTitle();
  	$suffix = '';
  	if ($title->getNamespace() == NS_TEMPLATE)
  	$suffix = '&action=getPagesForTemplate';

  	$redirect = "${wgServer}$wgScriptPath/index.php?title=".$title->getPrefixedDBkey().$suffix;

  	$wgOut->redirect($redirect);
  }
}
?>
