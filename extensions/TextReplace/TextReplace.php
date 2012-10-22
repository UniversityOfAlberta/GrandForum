<?php
/** \file
* \brief Contains setup code for the TextReplace Extension.
*/
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
}

$wgExtensionCredits['specialpage'][] = array(
					     'name' => 'TextReplace',
					     'author' =>'UofA: SERL',
					     //'url' => 'http://www.mediawiki.org/wiki/User:JDoe',
					     'description' => 'Automatically replaces any wikitext on a page that matches that specified in the TextReplace list.'
					     );


$wgAutoloadClasses['TextReplace'] = dirname(__FILE__) . '/TextReplace_body.php';
$wgSpecialPages['TextReplace'] = 'TextReplace';
$wgHooks['LoadAllMessages'][] = 'TextReplace::loadMessages'; 
$wgHooks['LangugeGetSpecialPageAliases'][] = 'TextReplaceLocalizedPageName'; # Add any aliases for the special page.
$wgHooks['ParserBeforeStrip'][] = 'TextReplace::replaceText';
$wgHooks['ParserAfterTidy'][] = 'TextReplace::externalLinks';

//$wgExtensionFunctions[] = "TextReplace::createTextReplacementTable";

define("EX_TEXT_REPLACE", true);


function TextReplaceLocalizedPageName(&$specialPageArray, $code) {
# The localized title of the special page is among the messages of the extension:
  TextReplace::loadMessages();
  return true;
}
?>
