<?php
/** \file
* \brief Contains setup code for the Visualisation Extension.
*/

# Not a valid entry point, skip unless MEDIAWIKI is defined
  //if (!defined('MEDIAWIKI')) {
  // }

  // Following comment is a giant hack.  Should be moved to somewhere else.
  /*require_once("MapCreator.php");
   MapCreator::completeUserEditCounts();
   print "User edit counts filled in.";
   exit;*/

# Set up Vis extension
$egVisEnableWikiMap = true;
$egVisEnableWiego = true;
$egVisEnableAuthorDisplay = true;


define("EX_VIS", true);

$wgExtensionCredits['other'][] = array(
				       'name' => 'Vis',
				       'author' =>'UofA: SERL', 
				       //'url' => 'http://www.mediawiki.org/wiki/User:JDoe', 
				       'description' => 'Displays topics maps and wiEGO visualizations.'
				       );
$ecVisImagePath = 'extensions/Vis/images/';
$wgAutoloadClasses['Vis'] = dirname(__FILE__) . '/Vis_body.php';
					      //$wgAutoloadClasses['Vis_EditPage'] = dirname(__FILE__) . '/Vis_EditPage.php';
						       //$wgAutoloadClasses['Vis_Article'] = dirname(__FILE__) . '/Vis_Article.php';
						       //$wgSpecialPages['Vis'] = 'Vis';

$wgHooks['LoadAllMessages'][] = 'Vis::loadMessages'; 
$wgHooks['UnknownAction'][] = 'Vis::efVisHandleRequest'; //Catches '&' actions sent to php
//$wgHooks['SkinTemplateToolboxEnd'][] = 'Vis::efVisAddToolboxLinks'; //Adds 'Open wiEGO' link to toolbox
//$wgHooks['ArticleViewHeader'][] = 'Vis::efVisAddVisLinks'; //Adds 'Open wiEGO' and 'open topic map' links to each page
//$wgHooks['SkinBuildSidebar'][] = 'Vis::efVisAddSidebarLinks'; //Adds 'Open wiEGO' link to sidebar
$wgHooks['SkinTemplateContentActions'][] = 'Vis::efVisAddTabs'; //Adds 'Wiki Map' tab

$wgExtensionFunctions[] = 'Vis::efVisCreateTemplatePages'; //Create templates needed by wiEGO
?>
