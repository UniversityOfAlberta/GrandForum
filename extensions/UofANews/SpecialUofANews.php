<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialUofANews'] = 'SpecialUofANews'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SpecialUofANews'] = $dir . 'SpecialUofANews.i18n.php';
$wgSpecialPageGroups['SpecialUofANews'] = 'network-tools';

function runSpecialUofANews($par) {
    SpecialUofANews::execute($par);
}

class SpecialUofANews extends SpecialPage{

    function SpecialUofANews() {
        SpecialPage::__construct("SpecialUofANews", null, false, 'runSpecialUofANews');
    }
    
    function userCanExecute($user){
        return true;
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $news = UofANews::getAllNews();
        if(count($news) > 0){
            foreach($news as $article){
                $wgOut->addHTML("<div style='display: flex; border: 1px solid #EEEEEE; padding: 0 15px; margin-bottom: 15px;'>");
                $wgOut->addHTML("<div style='width: 100%; padding-right: 20px;'><h3><a href='{$article->getUrl()}' target='_blank'>{$article->getTitle()}</a></h3>");
                $wgOut->addHTML("<p>{$article->getFirstSentences()}</p>");
                $wgOut->addHTML("<p>Published: {$article->getDate()}</p></div>");
                if($article->getImg() != ""){
                    $wgOut->addHTML("<div style='width: 200px;'><img src='{$article->getImg()}' style='max-width: 200px; max-height: 200px; margin: 20px 20px 20px 0;' /></div>");
                }
                $wgOut->addHTML("</div>");
            }
        }
        if(isset($_GET['embed'])){
            $wgOut->addScript("<style> 
                #bodyContent { 
                    max-height: 600px;
                    overflow-y: auto;
                    overflow-x: hidden;
                }
            </style>");
        }
    }

}

?>
