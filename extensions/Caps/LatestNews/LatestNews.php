<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['LatestNews'] = 'LatestNews'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['LatestNews'] = $dir . 'LatestNews.i18n.php';
$wgSpecialPageGroups['LatestNews'] = 'network-tools';

class LatestNews extends SpecialPage{

    function LatestNews() {
        parent::__construct("LatestNews", STAFF.'+', true);
    }

    function execute($par){
        global $wgOut, $wgLang, $wgServer, $wgScriptPath;
        if(isset($_GET['getPDF'])){
            // Download the PDF
            header('Content-Type: application/pdf');
            $data = DBFunctions::select(array('grand_latest_news'),
                                        array($wgLang->getCode() => 'pdf'),
                                        array('id' => $_GET['getPDF']));
            echo $data[0]['pdf'];
        }
        // Actually Show the content
        
        $data = DBFunctions::select(array('grand_latest_news'),
                                    array('id', 'date'),
                                    array(),
                                    array('date' => 'DESC'));
        if(isset($data[0])){
            $wgOut->addHTML("<iframe src='https://docs.google.com/gview?url={$wgServer}{$wgScriptPath}/index.php/Special:LatestNews%3FgetPDF={$data[0]['id']}&embedded=true' style='width:718px; height:700px; frameborder='0'></iframe>");
            $wgOut->addHTML("<iframe src='?getPDF={$data[0]['id']}' width='100%' height='500px' frameborder='0'></iframe>");
        }
        
    }

}

?>
