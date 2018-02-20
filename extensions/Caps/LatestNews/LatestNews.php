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
                $enTitle = $_POST['enTitle'];
                $frTitle = $_POST['frTitle'];
                $type = $_POST['type'];
                if($type == 'CAPS Collaboration'){
                    $color = "#000000";
                }
                else if($type == 'URGENT News'){
                    $color = "#9DC3E3";
                }
                else if($type == 'Regulation/Policy'){
                    $color = "#8FABD9";
                }
                else if($type == 'Coverage/Access'){
                    $color = "#224F77";
                }
                else if($type == 'Resource'){
                    $color = "#224F77";
                }
                DBFunctions::insert('grand_latest_news',
                                    array('date' => $date,
                                          'en' => $en,
                                          'fr' => $fr,
                                          'enTitle' => $enTitle,
                                          'frTitle' => $frTitle,
                                          'type' => $type,
                                          'color'=>$color
                                      ));
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
                            <td align='right'><b>Title(en):</b></td>
                            <td><input type='text' name='enTitle'/></td>
                        </tr>
                        <tr>
                            <td align='right'><b>Title(fr):</b></td>
                            <td><input type='text' name='frTitle'/></td>
                        </tr>
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
                        <td align='right'><b>
                        Type: </b>
                        </td>
                        <td>
                        <select name='type'>
                          <option value=''>Choose type...</option>
                          <option value='CAPS Collaboration'>CAPS Collaboration</option>
                          <option value='Regulation/Policy'>Regulation/Policy</option>
                          <option value='Coverage/Access'>Coverage/Access</option>
                          <option value='URGENT News'>URGENT News</option>
                          <option value='Resource'>Resource</option>
                        </select>
                        </td>
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
            </div><br /><br/>");
        }
        $data = DBFunctions::select(array('grand_latest_news'),
                                    array('id','en', 'date','enTitle','frTitle','color','type','thumbnail','en','fr'),
                                    array(),
                                    array('date' => 'DESC', 'id' => 'DESC','enTitle' => 'DESC','frTitle' => 'DESC','type' => 'DESC','color' => 'DESC','thumbnail' => 'DESC','en' => 'DESC','fr'=>'DESC'));
        
        if(isset($data[0])){
            $pdfId = (isset($_GET['pdf'])) ? $_GET['pdf'] : $data[0]['id'];
            $wgOut->addHTML("<iframe src='https://docs.google.com/viewer?url=$wgServer$wgScriptPath/index.php?action=getPDF%26pdf={$pdfId}%26lang={$wgLang->getCode()}&embedded=true' width='800px' height='610px' frameborder='0'></iframe>");
            $wgOut->addHTML("<div>
                <fieldset>
                <legend>Color Code</legend>
                <div style='color:black'>CAPS Collaboration</div>
                <div style='color:#8FABD9'>Regulation/Policy</div>
                <div style='color:#224F77'>Coverage/Access</div>
                <div style='color:#9DC3E3'>URGENT News</div>
                <div style='color:#224F77'>Resource</div>
                </fieldset>
                </div>");
            if(count($data) > 1){
                if($wgLang->getCode() == 'en'){
                    $header = "Previous News";
                }
                else{
                    $header = "Nouvelles Antérieures";
                }
                $wgOut->addHTML("<div><h2>$header</h2><ul>");
                $olddate = null;
                foreach($data as $key => $row){
                    if ($row["thumbnail"]==""){

                        file_put_contents($row["id"]."temp.pdf", $row['en']);
                        
                        $im = new imagick();
                        $im->readimage($row["id"]."temp.pdf"); 
                        $im->setImageFormat('jpg');

                        $im->writeimage($row["id"]."temp.jpg");

                        DBFunctions::update('grand_latest_news',
                                    array('thumbnail' => $row["id"]."temp.jpg",
                                      ),array('id' => $row['id']));
                    }
                    if($key > 0){
                        $date = explode("-",substr($row['date'], 0, 10));
                        if (!is_null($olddate)){
                            if ($olddate[1] != $date[1] || $olddate[0] != $date[0]){
                                $wgOut->addHTML("</ul>");
                                $monthNumber = explode("-",substr($row['date'], 0, 10))[1];
                                $dateObj  = DateTime::createFromFormat('!m', $monthNumber);
                                $monthName = $dateObj->format('F');
                                $wgOut->addHTML("<h3>".$monthName." ".$date[0]."</h3>");
                            }
                        }
                        else{
                            $wgOut->addHTML("</ul>");
                            $monthNumber = explode("-",substr($row['date'], 0, 10))[1];
                            $dateObj  = DateTime::createFromFormat('!m', $monthNumber);
                            $monthName = $dateObj->format('F');
                            $wgOut->addHTML("<h3>".$monthName." ".$date[0]."</h3><ul>");
                        }
                        $olddate = $date;
                        

                        
                        $wgOut->addHTML("<img src='/".$row["thumbnail"]."'"."style='width:10%;height:10%;'>");
                        $wgOut->addHTML("<li> (".substr($row['date'], 0, 10).") <a href='$wgServer$wgScriptPath/index.php/Special:LatestNews?pdf={$row['id']}' style='font-size: 1.25em; color:".$row['color'].";'>".$row['type'].": ".$row['enTitle']."</a></li>");
                    }
                }
                $wgOut->addHTML("</ul></div>");
            }
        }
        
    }

}

?>
