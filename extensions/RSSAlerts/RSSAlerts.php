<?php

require_once("RSSFeed.php");
require_once("RSSArticle.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['RSSAlerts'] = 'RSSAlerts'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['RSSAlerts'] = $dir . 'RSSAlerts.i18n.php';
$wgSpecialPageGroups['RSSAlerts'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'RSSAlerts::createSubTabs';

class RSSAlerts extends SpecialPage{

    function RSSAlerts() {
        parent::__construct("RSSAlerts", STAFF.'+', true);
    }
    
    function handleEdit(){
        global $wgServer, $wgScriptPath, $wgMessage;
        if(trim($_POST['new_feed']) != ""){
            $feed = new RSSFeed();
            $feed->url = $_POST['new_feed'];
            $feed->create();
            $wgMessage->addSuccess("RSS Feed Created");
        }
        if(isset($_POST['delete_feed'])){
            foreach($_POST['delete_feed'] as $id => $feed){
                $feed = RSSFeed::newFromId($id);
                $feed->delete();
            }
            $wgMessage->addSuccess("RSS Feed Deleted");
        }
        if(isset($_POST['delete_article'])){
            foreach($_POST['delete_article'] as $id => $article){
                $article = RSSArticle::newFromId($id);
                $article->delete();
            }
            $wgMessage->addSuccess("Articles Deleted");
        }
        if(isset($_POST['filter'])){
            foreach($_POST['filter'] as $id => $filter){
                $feed = RSSFeed::newFromId($id);
                $feed->filter = trim($filter);
                $feed->update();
            }
        }
        if(isset($_POST['keywords']) || isset($_POST['people']) || isset($_POST['projects'])){
            if(isset($_POST['people'])){
                foreach($_POST['people'] as $id => $people){
                    $article = RSSArticle::newFromId($id);
                    $article->people = $people;
                    $article->update();
                }
            }
            if(isset($_POST['projects'])){
                foreach($_POST['projects'] as $id => $projects){
                    $article = RSSArticle::newFromId($id);
                    $article->projects = $projects;
                    $article->update();
                }
            }
            if(isset($_POST['keywords'])){
                foreach($_POST['keywords'] as $id => $keywords){
                    $article = RSSArticle::newFromId($id);
                    $article->keywords = explode(",", $keywords);
                    $article->update();
                }
            }
            $wgMessage->addSuccess("Articles Saved");
        }
        redirect("{$wgServer}{$wgScriptPath}/index.php/Special:RSSAlerts");
    }
    
    // Returns RSSArticles
    function parseRSS($contents, $feed=null, $person=null){
        $articles = array();
        $xml = @simplexml_load_string($contents, null, LIBXML_NOCDATA);
        if($xml === false){
            return false;
        }
        $json = json_encode($xml);
        $array = json_decode($json, true);
        if(isset($array['entry'])){
            // Atom Format
            $entries = $array['entry'];
            if(isset($entries['title'])){
                // Only a single entry
                $entries = array($entries);
            }
            foreach($entries as $entry){
                $article = RSSArticle::newFromRSSId($entry['id']);
                if(!$article->exists()){
                    $article = new RSSArticle();
                    if($feed != null){
                        $article->feed = $feed->id;
                    }
                    $article->rssId = $entry['id'];
                    $article->title = $entry['title'];
                    $article->url = $entry['link']['@attributes']['href'];
                    $article->description = strip_tags($entry['content']);

                    foreach(Wordle::createDataFromText(strip_tags($article->title." ".$article->description)) as $keyword){
                        if($keyword['freq'] > 1){
                            $article->keywords[] = $keyword['word'];
                        }
                    }
                    
                    $article->date = $entry['published'];
                    $articles[] = $article;
                }
            }
        }
        else if(isset($array['channel']['item'])){
            // Old RSS Format
            $entries = $array['channel']['item'];
            if(isset($entries['title'])){
                // Only a single entry
                $entries = array($entries);
            }
            foreach($entries as $entry){
                $id = md5($entry['title']);
                if($person != null){
                    $id = $person->getId().":".$id;
                }
                $article = RSSArticle::newFromRSSId($id);
                if(!$article->exists()){
                    $article = new RSSArticle();
                    if($feed != null){
                        $article->feed = $feed->id;
                    }
                    $link = (is_string($entry['link'])) ? $entry['link'] : "";
                    $description = (is_string($entry['description'])) ? $entry['description'] : "";
                    
                    $article->rssId = $id;
                    $article->title = $entry['title'];
                    $article->url = $link;
                    $article->description = strip_tags($description);
                    
                    foreach(Wordle::createDataFromText(strip_tags($article->title." ".$description)) as $keyword){
                        if($keyword['freq'] > 1){
                            $article->keywords[] = $keyword['word'];
                        }
                    }
                    
                    $matches = array();
                    preg_match("/(([0-9]+) days ago)/", $description, $matches);
                    if(isset($matches[2])){
                        // Google Scholar
                        $article->date = date('Y-m-d', time() - $matches[2]*60*60*24);
                    }
                    else{
                        $article->date = date('Y-m-d');
                    }

                    if(isset($entry['creator'])){
                        foreach(explode(" and ", $entry['creator']) as $a){
                            $author = Person::newFromNameLike($a);
                            if($author == null || $author->getName() == null || $author->getName() == ""){
                                // The name might not match exactly what is in the db, try aliases
                                $author = Person::newFromAlias($a);
                            }
                            if($author != null && $author->getId() != 0){
                                $article->people[] = $author->getId();
                            }
                        }
                        if(count($article->people) == 0){
                            $article->people[] = $person->getId();
                        }
                    }
                    else if($person != null){
                        $article->people[] = $person->getId();
                    }
                    $articles[] = $article;
                }
            }
        }
        else{
            // Unknown Format
            return false;
        }
        // Run Filter
        $filteredArticles = array();
        foreach($articles as $article){
            $found = true;
            if($feed != null && $feed->filter != ""){
                $found = false;
                $ors = explode(" OR ", $feed->filter);
                foreach($ors as $or){
                    $ands = explode(" AND ", $or);
                    $foundAnd = true;
                    foreach($ands as $and){
                        $foundAnd = ($foundAnd && strstr(strtolower(strip_tags($article->title." ".$article->description)), strtolower(trim($and))) !== false);
                    }
                    $found = ($found || $foundAnd);
                }
            }
            if($found){
                $filteredArticles[] = $article;
            }
        }
        return $filteredArticles;
    }
    
    function handleImport(){
        global $wgMessage, $wgServer, $wgScriptPath, $config;
        $articles = array();
        $feeds = RSSFeed::getAllFeeds();
        $errors = array();
        foreach($feeds as $feed){
            $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
            $context = stream_context_create($opts);
            $contents = @file_get_contents($feed->url, false, $context);
            $parsed = $this->parseRSS($contents, $feed);
            if($parsed === false){
                $errors[] = $feed;
            }
            else{
                $articles = array_merge($articles, $parsed);
            }
        }
        
        if(count($errors) > 0){
            $wgMessage->addError("<b>".count($errors)."</b> RSS feeds could not be read");
        }
        
        $success = array();
        $errors = array();
        foreach($articles as $article){
            $status = $article->create();
            if($status){
                $success[] = $article;
            }
            else{
                $errors[] = $article;
            }
        }
        if(count($success) > 0){
            $wgMessage->addSuccess("<b>".count($success)."</b> articles were imported");
        }
        else{
            $wgMessage->addWarning("No articles were imported");
        }
        if(count($errors) > 0){
            $wgMessage->addError("<b>".count($errors)."</b> articles failed to imported");
        }
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        if(isset($_POST['save'])){
            $this->handleEdit();
        }
        if(isset($_POST['import'])){
            $this->handleImport();
            redirect("{$wgServer}{$wgScriptPath}/index.php/Special:RSSAlerts");
        }
        $feeds = RSSFeed::getAllFeeds();
        $articles = RSSArticle::getAllArticles();
        $wgOut->addHTML("<form id='rssAlerts' action='{$wgServer}{$wgScriptPath}/index.php/Special:RSSAlerts' method='post'>
                         <div id='hiddenForm' style='display:none;'></div>");
        
        // RSS Feeds
        $wgOut->addHTML("<h3>RSS/Atom Feeds</h3>
                        Urls to RSS Feeds can be added here.  The 'filter' can be used to only import articles which contain the words.  Basic boolean expressions can be used like AND/OR, all other characters will be treated literally.<br />
                        <i>To edit a cell, double click it (this can only be done on some cells)</i>");
        $wgOut->addHTML("<p><button id='new_feed' type='button'>Add RSS Feed</button></p>
                         <div id='new_feed_div' style='display:none;'>
                            <p><input type='text' size='80' name='new_feed' /> <input type='submit' name='save' value='Save' /></p>
                         </div>");
        $wgOut->addHTML("<table id='feeds' class='wikitable' width='100%' style='display:none;'>
                            <thead>
                                <tr>
                                    <th width='50%'>Url</th>
                                    <th width='50%'>Filter</th>
                                    <th width='1px'>Delete?</th>
                                </tr>
                            </thead>
                            <tbody>");
        foreach($feeds as $feed){
            $wgOut->addHTML("<tr data-id='{$feed->id}'>
                <td><a href='{$feed->url}'>{$feed->url}</a></td>
                <td class='filter'>{$feed->filter}</td>
                <td align='center'><input type='checkbox' name='delete_feed[{$feed->id}]' /></td>
            </tr>");
        }
        $wgOut->addHTML("</tbody></table><br />
            <span><input type='submit' name='save' value='Save' /></span>");
        
        // Articles
        $wgOut->addHTML("<h3>Articles</h3>
                         Articles are imported from the RSS Feeds.  Articles from Google Scholar are also imported, but are done automatically on a daily basis.<br />
                         <i>To edit a cell, double click it (this can only be done on some cells)</i>
                         <p><input type='submit' name='import' value='Import Articles' /></p>
                         <table id='articles' class='wikitable' width='100%' style='display:none;'>
                            <thead>
                                <tr>
                                    <th width='35%'>Article</th>
                                    <th>Feed</th>
                                    <th width='1px'>Date</th>
                                    <th style='min-width:100px;width:20%;'>People</th>
                                    <th style='min-width:100px;width:20%;'>Projects</th>
                                    <th style='min-width:100px;width:20%;'>Keywords</th>
                                    <th width='1px'>Delete?</th>
                                </tr>
                            </thead>
                            <tbody>");
        foreach($articles as $article){
            $people = array();$article->getPeople();
            $projects = array();$article->getProjects();
            foreach($article->getPeople() as $person){
                $people[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
            }
            foreach($article->getProjects() as $project){
                $projects[] = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
            }
            $wgOut->addHTML("<tr data-id='{$article->id}'>
                <td><a href='{$article->url}' target='_blank'>{$article->title}</a><br /><div style='max-height:100px;overflow-y:auto;'>{$article->description}</div></td>
                <td>{$article->getFeed()}</td>
                <td>{$article->getDate()}<br />Week {$article->getWeek()}</td>
                <td class='people'>".implode(", ", $people)."</td>
                <td class='projects'>".implode(", ", $projects)."</td>
                <td class='keywords'>".implode(", ", $article->keywords)."</td>
                <td align='center'><input type='checkbox' name='delete_article[{$article->id}]' /></td>
            </tr>");
        }
        $wgOut->addHTML("</tbody></table><br />
            <span><input type='submit' name='save' value='Save' /></span>
        </form>
        <script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/RSSAlerts/RSSAlerts.js' />");
    }
    
    static function createSubTabs(&$tabs){
        global $wgTitle, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "RSSAlerts") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("RSS Alerts", "$wgServer$wgScriptPath/index.php/Special:RSSAlerts", $selected);
        }
        return true;
    }
}

?>
