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
                $wgOut->addHTML("<div class='newsArticle' style='display: flex; border: 1px solid #EEEEEE; padding: 0 15px; margin-bottom: 15px;'>");
                $wgOut->addHTML("<div class='newsText' style='width: 100%; padding-right: 20px;'><h3><a href='{$article->getUrl()}' target='_blank'>{$article->getTitle()}</a></h3>");
                $wgOut->addHTML("<p>{$article->getFirstSentences()}</p>");
                $wgOut->addHTML("<p>Published: {$article->getDate()}</p></div>");
                if($article->getImg() != ""){
                    $wgOut->addHTML("<div class='newsImage' style='width: 200px;'><img src='{$article->getImg()}' style='max-width: 200px; max-height: 200px; margin: 20px 20px 20px 0;' /></div>");
                }
                $wgOut->addHTML("</div>");
            }
        }
        if(isset($_GET['embed'])){
            $wgOut->addScript("<style> 
                #bodyContent {
                    font-size: 13px;
                    max-height: 600px;
                    overflow-y: auto;
                    overflow-x: hidden;
                }
                
                .newsArticle {
                    display: block !important;
                    padding: 0 10px !important;
                    margin-bottom: 10px !important;
                }
                
                .newsText {
                    padding-right: 0 !important;
                }
                
                .newsText > h3 {
                    padding-top: 0 !important;
                }
                
                .newsImage {
                    width: 100% !important;
                }
                
                .newsImage img {
                    max-width: 100% !important;
                    max-height: 100% !important;
                    width: 100% !important;
                    margin: 0 0 10px 0 !important;
                }
            </style>");
        }
    }

}

?>
