<?php
/**
 * Entry point for TemplateEditor extension.  Defines hooks and constants.
 * @package Annoki
 * @subpackage TemplateEditor
 * @author Brendan Tansey 
 */

require_once('TemplateFunctions.php');
require_once("Template.php");

# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
 }

$wgAutoloadClasses['TemplateEditor'] = dirname(__FILE__) . '/TemplateEditor_body.php';
$editor = new TemplateEditor();
$wgHooks['EditPage::showEditForm:initial'][] = array($editor, 'updateFromRequest');
$wgHooks['EditPage::showEditForm:fields'][] = array($editor, 'showCustomFields');
$wgHooks['EditFilter'][] = $editor;
$wgHooks['BeforePageDisplay'][] = 'TemplateEditor::addJS';
$wgHooks['SkinTemplateTabs'][] = 'TemplateEditor::addTETabs'; //Adds template editor tab
$wgHooks['EditPageBeforeEditButtons'][] = 'TemplateEditor::modifySaveButton';
$wgHooks['UnknownAction'][] = 'TemplateEditor::efTEHandleRequest'; //Catches '&' actions sent to MediaWiki
$wgHooks['SkinTemplateTabs'][] = 'removeEditTab'; // Removes the 'Edit' tab when viewing a Template page


//define ( "TEXTFIELD", 0 );
//define ( "TEXTAREA", 1 );
/**
 * Any text shorter than this number of characters will be displayed by default as a texfield (one line), 
 * any longer string will be displayed as a textarea (multiple lines).
 */
define("TEXTFIELD_CUTOFF", 150);

/**
 * Allows other extensions to know that we exist and are enabled.
 */
define("EX_TEMPLATE_EDITOR", true);

$wgExtensionCredits['other'][] = array(
				       'name' => 'TemplateEditor',
				       'author' =>'UofA: SERL',
				       //'url' => 'http://www.mediawiki.org/wiki/User:JDoe',
				       'description' => 'Provides an easy-to-use interface for editing templates on pages and creating new pages from templates.'
				       );
function removeEditTab($skin, &$content_actions){
	global $wgTitle;
	if($wgTitle->getNsText() == "Template" || $wgTitle->getNsText() == "Template_Talk"){
		unset($content_actions['edit']);
		unset($content_actions['delete']);
		unset($content_actions['move']);
	}
	return true;
}				    


?>
