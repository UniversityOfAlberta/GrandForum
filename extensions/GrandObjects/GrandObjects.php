<?php
// The purpose of this file is to simply include the other datastructures
require_once(dirname(__FILE__)."/../Reporting/Addressing.php");

define("WORKS_WITH", 'Works With');
define("SUPERVISES", 'Supervises');
define("MENTORS", 'Mentors');

autoload_register('GrandObjects');
autoload_register('GrandObjects/API');
autoload_register('GrandObjects/API/Person');
autoload_register('GrandObjects/API/Role');
autoload_register('GrandObjects/API/Project');
autoload_register('GrandObjects/API/Product');
autoload_register('GrandObjects/API/University');
autoload_register('GrandObjects/API/Wiki');
autoload_register('GrandObjects/API/MessageBoard');
autoload_register('GrandObjects/API/PDF');
autoload_register('GrandObjects/API/MailingList');
autoload_register('GrandObjects/API/Search');
autoload_register('GrandObjects/API/Sop');
autoload_register('GrandObjects/API/Course');
autoload_register('GrandObjects/API/GsmsData');

require_once("AbstractSop.php");


global $apiRequest;
// Person
$apiRequest->addAction('Hidden','person/:id', 'PersonAPI');
$apiRequest->addAction('Hidden','person/:id/projects', 'PersonProjectsAPI');
$apiRequest->addAction('Hidden','person/:id/projects/:personProjectId', 'PersonProjectsAPI');
$apiRequest->addAction('Hidden','person/:id/universities', 'PersonUniversitiesAPI');
$apiRequest->addAction('Hidden','person/:id/universities/:personUniversityId', 'PersonUniversitiesAPI');
$apiRequest->addAction('Hidden','person/:id/roles', 'PersonRolesAPI');
$apiRequest->addAction('Hidden','person/:id/relations', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/relations/:relId', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/products', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/private', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/all', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/:productId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/contributions', 'PersonContributionsAPI');
$apiRequest->addAction('Hidden','person/:id/allocations', 'PersonAllocationsAPI');
$apiRequest->addAction('Hidden','personRoleString/:id', 'PersonRoleStringAPI');
$apiRequest->addAction('Hidden','people', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/managed', 'PeopleManagedAPI');
$apiRequest->addAction('Hidden','people/:role', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/:university', 'PeopleAPI');

// Role
$apiRequest->addAction('Hidden','role', 'RoleAPI');
$apiRequest->addAction('Hidden','role/:id', 'RoleAPI');

// Project
$apiRequest->addAction('Hidden','project', 'ProjectAPI');
$apiRequest->addAction('Hidden','project/:id', 'ProjectAPI');
$apiRequest->addAction('Hidden','project/:id/members', 'ProjectMembersAPI');
$apiRequest->addAction('Hidden','project/:id/members/:role', 'ProjectMembersAPI');
$apiRequest->addAction('Hidden','project/:id/contributions', 'ProjectContributionsAPI');
$apiRequest->addAction('Hidden','project/:id/allocations', 'ProjectAllocationsAPI');
$apiRequest->addAction('Hidden','project/:id/products', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','project/:id/products/:productId', 'ProjectProductAPI');

// Product
$apiRequest->addAction('Hidden','product', 'ProductAPI');
$apiRequest->addAction('Hidden','product/tags', 'ProductTagsAPI');
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand/:start/:count', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors', 'PersonProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors/:personId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','product/:id/projects', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','product/:id/projects/:personId', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','productDuplicates/:category/:title/:id', 'ProductDuplicatesAPI');
$apiRequest->addAction('Hidden','bibliography', 'BibliographyAPI');
$apiRequest->addAction('Hidden','bibliography/:id', 'BibliographyAPI');
$apiRequest->addAction('Hidden','bibliography/person/:person_id', 'BibliographyAPI');

// University
$apiRequest->addAction('Hidden','university', 'UniversityAPI');
$apiRequest->addAction('Hidden','university/:id', 'UniversityAPI');

// Wiki
$apiRequest->addAction('Hidden','wikipage/:id', 'WikiPageAPI');
$apiRequest->addAction('Hidden','wikipage/:namespace/:title', 'WikiPageAPI');

//Board
$apiRequest->addAction('Hidden','board/:id', 'BoardAPI');
$apiRequest->addAction('Hidden','boards', 'BoardsAPI');

//Thread
$apiRequest->addAction('Hidden','thread', 'ThreadAPI');
$apiRequest->addAction('Hidden','thread/:id', 'ThreadAPI');
$apiRequest->addAction('Hidden','threads/:board', 'ThreadsAPI');
$apiRequest->addAction('Hidden','threads/:board/:search', 'ThreadsAPI');

//Post
$apiRequest->addAction('Hidden','post', 'PostAPI');
$apiRequest->addAction('Hidden','post/:id', 'PostAPI');
$apiRequest->addAction('Hidden','posts', 'PostsAPI');

// PDF
$apiRequest->addAction('Hidden','pdf/:id', 'PDFAPI');

// MailingList
$apiRequest->addAction('Hidden','mailingList', 'MailingListAPI');
$apiRequest->addAction('Hidden','mailingList/:listId', 'MailingListAPI');
$apiRequest->addAction('Hidden','mailingList/:listId/rules', 'MailingListRuleAPI');
$apiRequest->addAction('Hidden','mailingList/:listId/rules/:ruleId', 'MailingListRuleAPI');

//Sop
$apiRequest->addAction('Hidden','sop/:id', 'SopAPI');
$apiRequest->addAction('Hidden','sops', 'SopsAPI');
$apiRequest->addAction('Hidden','sop/:sop_id/annotations', 'SopAnnotationAPI');
$apiRequest->addAction('Hidden','sop/:sop_id/annotations/:annotation_id', 'SopAnnotationAPI');
$apiRequest->addAction('Hidden','sop/annotations/:annotation_id', 'SopAnnotationAPI');
$apiRequest->addAction('Hidden','sop/annotations', 'SopAnnotationAPI');
// NewSearch
$apiRequest->addAction('Hidden','globalSearch/:group/:search', 'GlobalSearchAPI');
$apiRequest->addAction('Hidden','virtu', 'VirtuAPI');
//Course
$apiRequest->addAction('Hidden','course/:id', 'CourseAPI');
$apiRequest->addAction('Hidden','courses', 'CoursesAPI');
//GsmsData
$apiRequest->addAction('Hidden','gsmsdata/:id', 'GsmsDataAPI');
$apiRequest->addAction('Hidden','gsmsdata/:id/:year', 'GsmsDataAPI');
$apiRequest->addAction('Hidden','gsmsdatas', 'GsmsDataAllAPI');
$apiRequest->addAction('Hidden','gsmsdatas/:folder', 'GsmsDataAllAPI');
$apiRequest->addAction('Hidden','gsmsdatas/:folder/:program', 'GsmsDataAllAPI');
$apiRequest->addAction('Hidden','gsmsdatas/:folder/:program/:decision', 'GsmsDataAllAPI');
$apiRequest->addAction('Hidden','gsmsdatas/:folder/:program/:decision/:year', 'GsmsDataAllAPI');
$apiRequest->addAction('Hidden','sophidden/:id/year', 'SOPHiddenAPI');
$apiRequest->addAction('Hidden','sopfavorited/:id/year', 'SOPFavoritedAPI');

function createModels(){
    global $wgServer, $wgScriptPath, $wgOut;
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RelationModel.js?".filemtime("extensions/GrandObjects/BackboneModels/RelationModel.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RangeCollection.js?".filemtime("extensions/GrandObjects/BackboneModels/RangeCollection.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Thread.js?".filemtime("extensions/GrandObjects/BackboneModels/Thread.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Board.js?".filemtime("extensions/GrandObjects/BackboneModels/Board.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Post.js?".filemtime("extensions/GrandObjects/BackboneModels/Post.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Link.js?".filemtime("extensions/GrandObjects/BackboneModels/Link.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Person.js?".filemtime("extensions/GrandObjects/BackboneModels/Person.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Role.js?".filemtime("extensions/GrandObjects/BackboneModels/Role.js")."'></script>\n";
	echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Project.js?".filemtime("extensions/GrandObjects/BackboneModels/Project.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Product.js?".filemtime("extensions/GrandObjects/BackboneModels/Product.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Bibliography.js?".filemtime("extensions/GrandObjects/BackboneModels/Bibliography.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/University.js?".filemtime("extensions/GrandObjects/BackboneModels/University.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Virtu.js?".filemtime("extensions/GrandObjects/BackboneModels/Virtu.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/WikiPage.js?".filemtime("extensions/GrandObjects/BackboneModels/WikiPage.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/PDF.js?".filemtime("extensions/GrandObjects/BackboneModels/PDF.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/MailingList.js?".filemtime("extensions/GrandObjects/BackboneModels/MailingList.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Sop.js?".filemtime("extensions/GrandObjects/BackboneModels/Sop.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Course.js?".filemtime("extensions/GrandObjects/BackboneModels/Course.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/GsmsData.js?".filemtime("extensions/GrandObjects/BackboneModels/GsmsData.js")."'></script>\n";
    return true;
}
?>
