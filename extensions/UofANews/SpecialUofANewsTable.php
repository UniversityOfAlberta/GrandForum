<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialUofANewsTable'] = 'SpecialUofANewsTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SpecialUofANewsTable'] = $dir . 'SpecialUofANewsTable.i18n.php';
$wgSpecialPageGroups['SpecialUofANewsTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'SpecialUofANewsTable::createSubTabs';

function runSpecialUofANewsTable($par) {
    SpecialUofANewsTable::execute($par);
}

class SpecialUofANewsTable extends SpecialPage{

    function __construct(){
        SpecialPage::__construct("SpecialUofANewsTable", null, false, 'runSpecialUofANewsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(STAFF);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("University of Alberta News Table");
        $searchEngines = json_decode(file_get_contents("maintenance/searchEngines.json"), true);
        $allNews = UofANews::getAllNews();
        $wgOut->addHTML("<table id='table' class='wikitable'>
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Date</th>
                    <th>Google</th>
                    <th>Bing</th>
                    <th>Yahoo!</th>
                </tr>
            </thead>
            <tbody>");
        foreach($allNews as $news){
            if(strstr($news->getUrl(), "folio") !== false){
                $google = ($searchEngines[$news->getUrl()]['google']) ? "Found\n" : (($searchEngines[$news->getUrl()]['google'] === false) ? "Not Found\n" : "Error\n");
                $bing   = ($searchEngines[$news->getUrl()]['bing'])   ? "Found\n" : (($searchEngines[$news->getUrl()]['bing'] === false)   ? "Not Found\n" : "Error\n");
                $yahoo  = ($searchEngines[$news->getUrl()]['yahoo'])  ? "Found\n" : (($searchEngines[$news->getUrl()]['yahoo'] === false)  ? "Not Found\n" : "Error\n");
                $wgOut->addHTML("<tr>
                    <td><a href='{$news->getUrl()}' target='_blank'>{$news->getTitle()}</a></td>
                    <td><span style='display:none;'>{$news->date} </span>{$news->getDate()}</td>
                    <td align='center'><a href='https://www.google.com/search?q=site:{$news->getUrl()}' target='_blank'>{$google}</a></td>
                    <td align='center'><a href='https://www.bing.com/search?q=url:{$news->getUrl()}' target='_blank'>{$bing}</a></td>
                    <td align='center'><a href='https://search.yahoo.com/search?p=url:{$news->getUrl()}' target='_blank'>{$yahoo}</a></td>
                </tr>");
            }
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>
                            $('#table').dataTable({
                                'aLengthMenu': [[-1], ['All']],
                                'iDisplayLength': -1,
                                'aaSorting': [[1,'desc']],
                                'dom': 'Blfrtip',
                                'buttons': [
                                    'excel', 'pdf'
                                ]
                             });
                         </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "SpecialUofANewsTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("UofA News Table", "$wgServer$wgScriptPath/index.php/Special:SpecialUofANewsTable", $selected);
        }
        return true;
    }

}

?>
