<?php
// The purpose of this file is to simply include the other datastructures
require_once(dirname(__FILE__)."/../Reporting/Addressing.php");
require_once("Blob.php");
define("WORKS_WITH", 'Works With');
define("SUPERVISES", 'Supervises');
define("MENTORS", 'Mentors');

UnknownAction::createAction('UserCreateRequest::stream');

autoload_register('GrandObjects');
autoload_register('GrandObjects/API');

global $apiRequest;
// Person
$apiRequest->addAction('Hidden','person/:id', new PersonAPI());
$apiRequest->addAction('Hidden','person/:id/projects', new PersonProjectsAPI());
$apiRequest->addAction('Hidden','person/:id/roles', new PersonRolesAPI());
$apiRequest->addAction('Hidden','person/:id/products', new PersonProductAPI());
$apiRequest->addAction('Hidden','person/:id/products/private', new PersonProductAPI());
$apiRequest->addAction('Hidden','person/:id/products/:productId', new PersonProductAPI());
$apiRequest->addAction('Hidden','personRoleString/:id', new PersonRoleStringAPI());
$apiRequest->addAction('Hidden','people', new PeopleAPI());
$apiRequest->addAction('Hidden','people/:role', new PeopleAPI());
$apiRequest->addAction('Hidden','people/:role/:university', new PeopleAPI());
// Role
$apiRequest->addAction('Hidden','role', new RoleAPI());
$apiRequest->addAction('Hidden','role/:id', new RoleAPI());
// Project
$apiRequest->addAction('Hidden','project', new ProjectAPI());
$apiRequest->addAction('Hidden','project/:id', new ProjectAPI());
$apiRequest->addAction('Hidden','project/:id/members', new ProjectMembersAPI());
$apiRequest->addAction('Hidden','project/:id/members/:role', new ProjectMembersAPI());

$apiRequest->addAction('Hidden','project/:id/products', new ProjectProductAPI());
$apiRequest->addAction('Hidden','project/:id/products/:productId', new ProjectProductAPI());
// Product
$apiRequest->addAction('Hidden','product', new ProductAPI());
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand', new ProductAPI());
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand/:start/:count', new ProductAPI());
$apiRequest->addAction('Hidden','product/:id', new ProductAPI());
$apiRequest->addAction('Hidden','product/:id/authors', new PersonProductAPI());
$apiRequest->addAction('Hidden','product/:id/authors/:personId', new PersonProductAPI());
$apiRequest->addAction('Hidden','product/:id/projects', new ProjectProductAPI());
$apiRequest->addAction('Hidden','product/:id/projects/:personId', new ProjectProductAPI());
$apiRequest->addAction('Hidden','productDuplicates/:category/:title/:id', new ProductDuplicatesAPI());
// University
$apiRequest->addAction('Hidden','university', new UniversityAPI());
$apiRequest->addAction('Hidden','university/:id', new UniversityAPI());
$apiRequest->addAction('Hidden','university/:lat/:long', new UniversityNearestAPI());

// Wiki
$apiRequest->addAction('Hidden','wikipage/:id', new WikiPageAPI());
$apiRequest->addAction('Hidden','wikipage/:namespace/:title', new WikiPageAPI());
//Story
$apiRequest->addAction('Hidden','story', new StoryAPI());
$apiRequest->addAction('Hidden','story/:id', new StoryAPI());
$apiRequest->addAction('Hidden', 'story/:id/author', new PersonStoryAPI());
$apiRequest->addAction('Hidden','stories', new StoriesAPI());
$apiRequest->addAction('Hidden','storycomments', new StoryCommentAPI());
$apiRequest->addAction('Hidden','storycomment', new StoryCommentAPI());
$apiRequest->addAction('Hidden','storycomment/:id', new StoryCommentAPI());

//Thread
$apiRequest->addAction('Hidden','thread', new ThreadAPI());
$apiRequest->addAction('Hidden','thread/:id', new ThreadAPI());
$apiRequest->addAction('Hidden','threads', new ThreadsAPI());
$apiRequest->addAction('Hidden','threads/:search', new ThreadsAPI());
//Post
$apiRequest->addAction('Hidden','post', new PostAPI());
$apiRequest->addAction('Hidden','post/:id', new PostAPI());
$apiRequest->addAction('Hidden','posts', new PostsAPI());
// PDF
$apiRequest->addAction('Hidden','pdf/:id', new PDFAPI());
// MailingList
$apiRequest->addAction('Hidden','mailingList', new MailingListAPI());
$apiRequest->addAction('Hidden','mailingList/:listId', new MailingListAPI());
$apiRequest->addAction('Hidden','mailingList/:listId/rules', new MailingListRuleAPI());
$apiRequest->addAction('Hidden','mailingList/:listId/rules/:ruleId', new MailingListRuleAPI());
// NewSearch
$apiRequest->addAction('Hidden','globalSearch/:group/:search', new GlobalSearchAPI());
$apiRequest->addAction('Hidden','virtu', new VirtuAPI());

function createModels(){
    global $wgServer, $wgScriptPath, $wgOut;
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RelationModel.js?".filemtime("extensions/GrandObjects/BackboneModels/RelationModel.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RangeCollection.js?".filemtime("extensions/GrandObjects/BackboneModels/RangeCollection.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Thread.js?".filemtime("extensions/GrandObjects/BackboneModels/Thread.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Post.js?".filemtime("extensions/GrandObjects/BackboneModels/Post.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Link.js?".filemtime("extensions/GrandObjects/BackboneModels/Link.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Person.js?".filemtime("extensions/GrandObjects/BackboneModels/Person.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Role.js?".filemtime("extensions/GrandObjects/BackboneModels/Role.js")."'></script>\n";
	echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Project.js?".filemtime("extensions/GrandObjects/BackboneModels/Project.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Product.js?".filemtime("extensions/GrandObjects/BackboneModels/Product.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/University.js?".filemtime("extensions/GrandObjects/BackboneModels/University.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Virtu.js?".filemtime("extensions/GrandObjects/BackboneModels/Virtu.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/WikiPage.js?".filemtime("extensions/GrandObjects/BackboneModels/WikiPage.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/PDF.js?".filemtime("extensions/GrandObjects/BackboneModels/PDF.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/MailingList.js?".filemtime("extensions/GrandObjects/BackboneModels/MailingList.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Story.js?".filemtime("extensions/GrandObjects/BackboneModels/Story.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/StoryComment.js?".filemtime("extensions/GrandObjects/BackboneModels/StoryComment.js")."'></script>\n";
    return true;
}
?>
