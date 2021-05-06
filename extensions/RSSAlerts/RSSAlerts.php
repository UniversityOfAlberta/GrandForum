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
        redirect("{$wgServer}{$wgScriptPath}/index.php/Special:RSSAlerts");
    }
    
    // Returns RSSArticles
    function parseRSS($contents, $feed=null, $person=null){
        $articles = array();
        $xml = @simplexml_load_string($contents);
        if($xml === false){
            return false;
        }
        $json = json_encode($xml);
        $array = json_decode($json, true);
        if(isset($array['entry'])){
            // Atom Format
            $entries = $array['entry'];
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
                    $article->description = $entry['content'];

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
                    $article->rssId = $id;
                    $article->title = $entry['title'];
                    $article->url = $entry['link'];
                    $article->description = $entry['description'];
                    
                    foreach(Wordle::createDataFromText(strip_tags($article->title." ".$article->description)) as $keyword){
                        if($keyword['freq'] > 1){
                            $article->keywords[] = $keyword['word'];
                        }
                    }
                    
                    $matches = array();
                    preg_match("/(([0-9]+) days ago)/", $entry['description'], $matches);
                    if(isset($matches[2])){
                        // Google Scholar
                        $article->date = date('Y-m-d', time() - $matches[2]*60*60*24);
                    }
                    else{
                        $article->date = date('Y-m-d');
                    }
                    if($person != null){
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
        return $articles;
    }
    
    function handleImport(){
        global $wgMessage, $wgServer, $wgScriptPath;
        $articles = array();
        $feeds = RSSFeed::getAllFeeds();
        $errors = array();
        foreach($feeds as $feed){
            $contents = file_get_contents($feed->url);
            $parsed = $this->parseRSS($contents, $feed);
            if($parsed === false){
                $errors[] = $feed;
            }
            else{
                $articles = array_merge($articles, $parsed);
            }
        }
        $nis = Person::getAllPeople(NI);
        foreach($nis as $ni){
            $contents = "";
            $result = 0;
            //$contents = file_get_contents("extensions/RSSAlerts/stroulia.rss");
            $gsUrl = "https://scholar.google.com/scholar?hl=en&as_sdt=2007&q=%22{$ni->getFirstName()}+{$ni->getLastName()}%22&scisbd=1";
            exec("./extensions/RSSAlerts/gscholar-rss \"{$gsUrl}\"", $contents, $result);
            if($result == 0){
                $parsed = $this->parseRSS($contents, null, $ni);
                if($parsed === false){
                    $errors[] = $ni;
                }
                else{
                    $articles = array_merge($articles, $parsed);
                }
            }
            else{
                $errors[] = $ni;
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
        redirect("{$wgServer}{$wgScriptPath}/index.php/Special:RSSAlerts");
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        if(isset($_POST['save'])){
            $this->handleEdit();
        }
        if(isset($_POST['import'])){
            $this->handleImport();
        }
        $feeds = RSSFeed::getAllFeeds();
        $articles = RSSArticle::getAllArticles();
        $wgOut->addHTML("<form action='{$wgServer}{$wgScriptPath}/index.php/Special:RSSAlerts' method='post'>");
        
        // RSS Feeds
        $wgOut->addHTML("<h3>RSS/Atom Feeds</h3>");
        $wgOut->addHTML("<p><button id='new_feed' type='button'>Add RSS Feed</button></p>
                         <div id='new_feed_div' style='display:none;'>
                            <p><input type='text' size='80' name='new_feed' /> <input type='submit' name='save' value='Save' /></p>
                         </div>");
        $wgOut->addHTML("<table id='feeds' class='wikitable' width='100%'>
                            <thead>
                                <tr>
                                    <th>Url</th>
                                    <th>Delete?</th>
                                </tr>
                            </thead>
                            <tbody>");
        foreach($feeds as $feed){
            $wgOut->addHTML("<tr>
                <td><a href='{$feed->url}'>{$feed->url}</a></td>
                <td align='center'><input type='checkbox' name='delete_feed[{$feed->id}]' /></td>
            </tr>");
        }
        $wgOut->addHTML("</tbody></table><br />
            <input type='submit' name='save' value='Save' />");
        
        // Articles
        $wgOut->addHTML("<h3>Articles</h3>
                         Articles are imported from the RSS Feeds, as well as from Google Scholar.  Importing can take a while.<br />
                         <p><input type='submit' name='import' value='Import Articles' /></p>
                         <table id='articles' class='wikitable' width='100%'>
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>People</th>
                                    <th>Projects</th>
                                    <th>Keywords</th>
                                    <th>Delete?</th>
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
                $projects[] = "<a href='{$project->getUrl()}'>{$project->getNameForForms()}</a>";
            }
            $wgOut->addHTML("<tr>
                <td><a href='{$article->url}' target='_blank'>{$article->title}</a></td>
                <td>{$article->description}</td>
                <td>{$article->getDate()}</td>
                <td>".implode(", ", $people)."</td>
                <td>".implode(", ", $projects)."</td>
                <td>".implode(", ", $article->keywords)."</td>
                <td align='center'><input type='checkbox' name='delete_article[{$article->id}]' /></td>
            </tr>");
        }
        $wgOut->addHTML("</tbody></table><br />
            <input type='submit' name='save' value='Save' />
        </form>
        <script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/RSSAlerts/RSSAlerts.js' />");
    }
    
    static function createSubTabs(&$tabs){
        global $wgTitle, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FEC")) ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("RSS Alerts", "$wgServer$wgScriptPath/index.php/Special:RSSAlerts", $selected);
        }
        return true;
    }
}

?>
