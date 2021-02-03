<?php
// The purpose of this file is to simply include the other datastructures
define("WORKS_WITH", 'Works With');
define("SUPERVISES", 'Supervises');
define("MENTORS", 'Mentors');

UnknownAction::createAction('UserCreateRequest::stream');

autoload_register('GrandObjects');
autoload_register('GrandObjects/API');

global $apiRequest;
// Person
$apiRequest->addAction('Hidden','person/:id', 'PersonAPI');
$apiRequest->addAction('Hidden','person/:id/roles', 'PersonRolesAPI');
$apiRequest->addAction('Hidden','person/:id/products', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/private', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/:productId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','personRoleString/:id', 'PersonRoleStringAPI');
$apiRequest->addAction('Hidden','people', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/:university', 'PeopleAPI');
// Role
$apiRequest->addAction('Hidden','role', 'RoleAPI');
$apiRequest->addAction('Hidden','role/:id', 'RoleAPI');

// Product
$apiRequest->addAction('Hidden','product', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors', 'PersonProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors/:personId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','productDuplicates/:category/:title/:id', 'ProductDuplicatesAPI');
// University
$apiRequest->addAction('Hidden','university', 'UniversityAPI');
$apiRequest->addAction('Hidden','university/:id', 'UniversityAPI');
$apiRequest->addAction('Hidden','university/:lat/:long', 'UniversityNearestAPI');

// Wiki
$apiRequest->addAction('Hidden','wikipage/:id', 'WikiPageAPI');
$apiRequest->addAction('Hidden','wikipage/:namespace/:title', 'WikiPageAPI');
//Story
$apiRequest->addAction('Hidden','story', 'StoryAPI');
$apiRequest->addAction('Hidden','story/:id', 'StoryAPI');
$apiRequest->addAction('Hidden', 'story/:id/author', 'PersonStoryAPI');
$apiRequest->addAction('Hidden','stories', 'StoriesAPI');
$apiRequest->addAction('Hidden','storycomments', 'StoryCommentAPI');
$apiRequest->addAction('Hidden','storycomment', 'StoryCommentAPI');
$apiRequest->addAction('Hidden','storycomment/:id', 'StoryCommentAPI');

//Thread
$apiRequest->addAction('Hidden','thread', 'ThreadAPI');
$apiRequest->addAction('Hidden','thread/:id', 'ThreadAPI');
$apiRequest->addAction('Hidden','threads', 'ThreadsAPI');
$apiRequest->addAction('Hidden','threads/:search', 'ThreadsAPI');
//Post
$apiRequest->addAction('Hidden','post', 'PostAPI');
$apiRequest->addAction('Hidden','post/:id', 'PostAPI');
// NewSearch
$apiRequest->addAction('Hidden','globalSearch/:group/:search', 'GlobalSearchAPI');

function createModels(){
    global $wgServer, $wgScriptPath, $wgOut;
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RelationModel.js?".filemtime("extensions/GrandObjects/BackboneModels/RelationModel.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RangeCollection.js?".filemtime("extensions/GrandObjects/BackboneModels/RangeCollection.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Thread.js?".filemtime("extensions/GrandObjects/BackboneModels/Thread.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Post.js?".filemtime("extensions/GrandObjects/BackboneModels/Post.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Link.js?".filemtime("extensions/GrandObjects/BackboneModels/Link.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Person.js?".filemtime("extensions/GrandObjects/BackboneModels/Person.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Role.js?".filemtime("extensions/GrandObjects/BackboneModels/Role.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Product.js?".filemtime("extensions/GrandObjects/BackboneModels/Product.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/University.js?".filemtime("extensions/GrandObjects/BackboneModels/University.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/WikiPage.js?".filemtime("extensions/GrandObjects/BackboneModels/WikiPage.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/MailingList.js?".filemtime("extensions/GrandObjects/BackboneModels/MailingList.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Story.js?".filemtime("extensions/GrandObjects/BackboneModels/Story.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/StoryComment.js?".filemtime("extensions/GrandObjects/BackboneModels/StoryComment.js")."'></script>\n";
    return true;
}
?>
