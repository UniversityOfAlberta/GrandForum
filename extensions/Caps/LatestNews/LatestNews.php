<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['LatestNews'] = 'LatestNews'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['LatestNews'] = $dir . 'LatestNews.i18n.php';
$wgSpecialPageGroups['LatestNews'] = 'network-tools';

$wgHooks['UnknownAction'][] = 'LatestNews::getPDF';

class LatestNews extends SpecialPage{

    function LatestNews() {
        parent::__construct("LatestNews", null, true);
    }

    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isLoggedIn();
    }
    
    static function getPDF($action){
        global $wgLang;
        if($action == 'getPDF' && isset($_GET['pdf']) && isset($_GET['lang'])){
            // Download the PDF
            header('Content-Type: application/pdf');
            $data = DBFunctions::select(array('grand_latest_news'),
                                        array($_GET['lang'] => 'pdf'),
                                        array('id' => $_GET['pdf']));
            echo $data[0]['pdf'];
            exit;
        }
        return true;
    }

    function execute($par){
        global $wgOut, $wgLang, $wgServer, $wgScriptPath, $wgMessage;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            if(isset($_POST['submit']) && $_FILES['en']['size'] > 0 && $_FILES['fr']['size'] > 0){
                // Handle Upload
                $en = file_get_contents($_FILES['en']['tmp_name']);
                $fr = file_get_contents($_FILES['fr']['tmp_name']);
                $date = $_POST['date'];
                DBFunctions::insert('grand_latest_news',
                                    array('date' => $date,
                                          'en' => $en,
                                          'fr' => $fr));
                $wgMessage->addSuccess("Files Uploaded");
                redirect("$wgServer$wgScriptPath/index.php/Special:LatestNews");
            }
            else if(isset($_POST['submit'])){
                $wgMessage->addError("There was a problem with the upload");
                redirect("$wgServer$wgScriptPath/index.php/Special:LatestNews");
            }
            // Show Upload Options
            $defaultDate = date('Y-m-d');
            $wgOut->addHTML("<button onclick='$(this).remove(); $(\"#uploadForm\").slideDown();'>Upload PDFs</button>
                <div id='uploadForm' style='display:none;'>
                <form action='$wgServer$wgScriptPath/index.php/Special:LatestNews' method='post' enctype='multipart/form-data'>
                    <table>
                        <tr>
                            <td align='right'><b>English:</b></td>
                            <td><input type='file' name='en' accept='.pdf' /></td>
                        </tr>
                        <tr>
                            <td align='right'><b>French:</b></td>
                            <td><input type='file' name='fr' accept='.pdf' /></td>
                        </tr>
                        <tr>
                            <td align='right'><b>Date:</b></td>
                            <td><input type='text' name='date' value='$defaultDate' /></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type='submit' name='submit' value='Upload' />
                            </td>
                        </tr>
                    </table>
                    <script type='text/javascript'>
                        $('input[name=date]').datepicker({
                            'dateFormat': 'yy-mm-dd'
                        });
                    </script>
                </form>
            </div><br /><br />");
        }
        $data = DBFunctions::select(array('grand_latest_news'),
                                    array('id', 'date'),
                                    array(),
                                    array('date' => 'DESC', 'id' => 'DESC'));
        if(isset($data[0])){
            $pdfId = (isset($_GET['pdf'])) ? $_GET['pdf'] : $data[0]['id'];
            $wgOut->addHTML("<iframe src='https://docs.google.com/viewer?url=$wgServer$wgScriptPath/index.php?action=getPDF%26pdf={$pdfId}%26lang={$wgLang->getCode()}&embedded=true' width='800px' height='610px' frameborder='0'></iframe>");
            if(count($data) > 1){
                if($wgLang->getCode() == 'en'){
                    $header = "Previous News";
                }
                else{
                    $header = "Nouvelles Antérieures";
                }
                $wgOut->addHTML("<div><h2>$header</h2><ul>");
                foreach($data as $key => $row){
                    if($key > 0){
                        $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/Special:LatestNews?pdf={$row['id']}' style='font-size: 1.25em;'>".substr($row['date'], 0, 10)."</a></li>");
                    }
                }
                $wgOut->addHTML("</ul></div>");
            }
        }
        
    }

}

?>
