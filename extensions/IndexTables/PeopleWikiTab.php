<?php

class PeopleWikiTab extends AbstractTab {

    var $table;
    var $visibility;

    function PeopleWikiTab($table, $visibility){
        global $wgLang;
        if($wgLang->getCode() == 'en'){
            parent::AbstractTab("Resources");
        }
        else if($wgLang->getCode() == 'fr'){
            parent::AbstractTab("Ressources");
        }
        $this->table = $table;
        $this->visibility = $visibility;
    }
    
    function uploadFile(){
        global $wgRequest, $wgUser, $wgMessage, $wgServer, $wgScriptPath, $wgLang, $config;
        
        $name = $this->table." ".trim($_FILES['wpUploadFile']['name']);

        $wgRequest->setVal("wpUpload", true);
        $wgRequest->setVal("wpSourceType", 'file');
        $wgRequest->setVal("action", 'submit');
        $wgRequest->setVal("wpDestFile", $name);
        $wgRequest->setVal("wpDestFileWarningAck", true);
        $wgRequest->setVal("wpIgnoreWarning", true);
        $wgRequest->setVal("wpEditToken", $wgUser->getEditToken());

        $upload = new SpecialUpload($wgRequest);
        $upload->execute(null);
            $_POST['fileURL'] = trim($_POST['fileURL']);
            $_POST['realTitle'] = trim($_POST['realTitle']);
            $_POST['keywords'] = trim($_POST['keywords']);
        if($upload->mLocalFile != null){
            $data = DBFunctions::select(array('mw_an_upload_permissions'),
                                        array('*'),
                                        array("upload_name" => "File:".str_replace("_", " ", trim(ucfirst($name)))));
            if(count($data) == 0){
                DBFunctions::insert("mw_an_upload_permissions",
                                    array("upload_name" => "File:".str_replace("_", " ", trim(ucfirst($name))),
                                          "nsName" => str_replace(" ", "_", $this->table),
                                          "url" => $_POST['fileURL'],
                                          "title" => $_POST['realTitle'],
                                          "keywords" => $_POST['keywords']));
            }
            else{
                DBFunctions::update("mw_an_upload_permissions",
                                    array("upload_name" => "File:".str_replace("_", " ", trim(ucfirst($name))),
                                          "nsName" => str_replace(" ", "_", $this->table),
                                          "url" => $_POST['fileURL'],
                                          "title" => $_POST['realTitle'],
                                          "keywords" => $_POST['keywords']),
                                    array("upload_name" => "File:".str_replace("_", " ", ucfirst($name))));
            }
            $wgMessage->addSuccess("The file <b>{$_FILES['wpUploadFile']['name']}</b> was uploaded successfully");
        }
        elseif($_POST['fileURL'] != '' && $_POST['realTitle'] != ''){
            $wikipage= WikiPage::factory(Title::makeTitle(NS_IMAGE,str_replace(" ", "_", ucfirst($_POST['realTitle']))));
            $wikipage->doEdit('','',0,false,$wgUser);
            $data = DBFunctions::select(array('mw_an_upload_permissions'),
                                        array('*'),
                                        array("upload_name" => str_replace(" ", "_", ucfirst($_POST['realTitle']))));
            if(count($data) == 0){
                DBFunctions::insert("mw_an_upload_permissions",
                                    array("upload_name" => "File:".str_replace(" ", "_", ucfirst($_POST['realTitle'])),
                                          "nsName" => str_replace(" ", "_", $this->table),
                                          "url" => $_POST['fileURL'],
                                          "title" => $_POST['realTitle'],
                                          "keywords" => $_POST['keywords']));
            }
            else{
                DBFunctions::update("mw_an_upload_permissions",
                                    array("upload_name" => "File:".str_replace(" ", "_", ucfirst($_POST['realTitle'])),
                                          "nsName" => str_replace(" ", "_", $this->table),
                                          "url" => $_POST['fileURL'],
                                          "title" => $_POST['realTitle'],
                                          "keywords" => $_POST['keywords']),
                                    array("upload_name" => "File:".str_replace(" ", "_", ucfirst($_POST['realTitle']))));
            }
            $wgMessage->addSuccess("The file <b>{$_FILES['wpUploadFile']['name']}</b> was uploaded successfully");
        }
        else{
            $wgMessage->addError("There was a problem uploading the file");
        }
        redirect("{$wgServer}{$wgScriptPath}/index.php/{$config->getValue('networkName')}:ALL_{$this->table}?tab=wiki");
    }
    function generateBody(){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgLang, $config;
        $resources = array("Organizations", "Articles", "Patients", "Tools", "Clinical", "Resources");
        if(isset($_FILES['wpUploadFile'])){
            $this->uploadFile();
        }
        
        $table = $this->table;
        $me = Person::newFromWgUser();
        $edit = $this->visibility['edit'];
        
        if(!$this->visibility['isMember'] && false){
            return $this->html;
        }
        if(in_array($this->table, $resources)){
            $this->html .= "<div class='helpful_resources' style='display:inline-block; font-size:1.1em'>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Clinical'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/clinical_guidelines_files.png'></a><br /><span class='en'>Clinical Guidelines</span><span class='fr'>Lignes directrices cliniques</span></div>
                                     <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Tools'><img width='100px'  src='$wgServer$wgScriptPath/skins/icons/caps/tools_tips_files.png'></a><br /><span class='en'>Tools & Tips</span><span class='fr'>Outils et conseils</span></div>
                                     <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Organizations'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/organizations_files.png'></a><br /><span class='en'>Organizations</span><span class='fr'>Organizations</span></div>
                                     <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Articles'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/articles_files.png'></a><br /><span class='en'>Articles</span><span class='fr'>Des articles</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Patients'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/patient_resource_files.png'></a><br /><span class='en'>Patient Resources</span><span class='fr'>les ressources des patients</span></div>
                    </div>
       </div>";

        }
        $this->html .= "<br />
                    <div style='font-size: 1.5em;'>
                    <span class='en'>Below are all the <b>{$wgOut->getPageTitle()}</b> in {$config->getValue('networkName')}.  To search for a file or page in particular, use the search box below.  You can search by name, date last edited, and last editor.</span>
                    <span class='fr'><i>Ci-dessous sont tous les <b>{$wgOut->getPageTitle()}</b> dans CPCA. Pour rechercher un fichier ou une page en particulier, utiliser les champs de recherche ci-dessous. Vous pouvez rechercher par nom, date dernière édition , et le dernier éditeur.</span>
                    <br /><br /></div>";
        if($this->table == "Organizations"){
            $this->html .= "<span class='en'>
                <a target='_blank' href='http://www.nafcanada.org/'><img src='http://prochoice.org/wp-content/uploads/NAFlogoCanada-small.jpg' width='350'></a><br /><br />
                Click <a target='_blank' href='http://prochoice.org/health-care-professionals/naf-membership/'> here</a> to become a member.</span>

                <span class='fr'><a target='_blank' href='http://www.nafcanada.org/'><img src='http://prochoice.org/wp-content/uploads/NAFlogoCanada-small.jpg' width='350'></a><br /><br />
                Cliquez <a target='_blank' href='http://prochoice.org/health-care-professionals/naf-membership/'>ici</a> pour devenir membre.
                </span><br /><br />";
        }
        else if($this->table == "Clinical"){
            $this->html .= "
                <span class='en'><i>*For example, view these <a href='https://www.caps-cpca.ubc.ca/index.php/File:Clinical_Clinical_Practice_Guideline_2016.pdf' target='_blank'>clinical practice guidelines</a></i></span>
                <span class='fr'><i>*Par exemple, consultez ces <a href='https://www.caps-cpca.ubc.ca/index.php/File:Clinical_Clinical_Practice_Guideline_2016.pdf' target='_blank'>lignes directrices de pratique clinique</a></i></span>
                <br /><br />";
        }
        else if($this->table == "Patients"){
            $this->html .= "
                <span class='en'><i>*For example, view this easy to read <a href='https://www.caps-cpca.ubc.ca/index.php/File:Patients_early_abortion_options.pdf' target='_blank'>fact sheet</a> comparing medical and aspiration abortion</i></span>
                <span class='fr'><i>*Par exemple, consultez cette <a href='https://www.caps-cpca.ubc.ca/index.php/File:Patients_early_abortion_options.pdf' target='_blank'>fiche d'information</a> facile à lire comparant l'avortement médical et l'asthme</i></span>
                <br /><br />";
        }
 

        $button_val = "Upload";
        if($wgLang->getCode() == 'fr'){
            $button_val = "Télécharger";
        }
        $this->html .= "<script type='text/javascript'>
            function clickButton(){
                clearWarning();
                var title = $('#newPageTitle').val().trim();
                if(title == ''){
                    addError('The title must not be empty');
                }
                else if(title.indexOf('%') !== -1 ||
                        title.indexOf(':') !== -1 ||
                        title.indexOf('|') !== -1 ||
                        title.indexOf('.') !== -1 ||
                        title.indexOf('?') !== -1 ||
                        title.indexOf('[') !== -1 ||
                        title.indexOf(']') !== -1 ||
                        title.indexOf('{') !== -1 ||
                        title.indexOf('}') !== -1 ||
                        title.indexOf('<') !== -1 ||
                        title.indexOf('>') !== -1){
                    addError('The title must not contain the following characters: <b>%</b>, <b>:</b>, <b>|</b>, <b>.</b>, <b>?</b>, <b>&lt;</b>, <b>&gt;</b>, <b>[</b>, <b>]</b>, <b>{</b>, <b>}</b>');
                }
                else{ 
                    document.location = '$wgServer$wgScriptPath/index.php/{$this->table}_Wiki:' + title + '?action=edit';
                }
                return false;
            }
        </script>
        <a class='button' id='newWikiPage' style='display:none;'>New Wiki Page</a>&nbsp;<a class='button' id='newFilePage'>$button_val</a>
        <div id='newWikiPageDiv' style='display:none;'>
            <h2>Create New Wiki Page</h2>
            <form action='' onSubmit='clickButton'>
            <table>
                <tr>
                    <td><b>Title:</b></td><td><input id='newPageTitle' type='text' name='title' size='40' /></td><td><input type='submit' id='createPageButton' value='Create Page' /></td>
                </tr>
            </table>
            </form>
        </div>
        <div id='newFileDiv' style='display:none;'>
            <h2 class='en'>Upload File</h2>
            <h2 class='fr'>Téléverser un fichier</h2>
            <form action='$wgServer$wgScriptPath/index.php/{$config->getValue('networkName')}:ALL_{$this->table}?tab=wiki' method='post' enctype='multipart/form-data' onSubmit='clickButton'>
            <table>
                <tr>
                    <td align='right' class='en'><b>File:</b></td>
                    <td align='right' class='fr'><b>Fichier:</b></td>
                    <td><input id='newPageTitle' type='file' name='wpUploadFile' /></td>
                </tr>
                <tr>
                    <td align='right' class='en'><b>URL:</b></td>
                    <td align='right' class='fr'><b>URL:</b></td>
                    <td><input id='fileURL' type='text' name='fileURL' size='40'/></td>
                </tr>
                <tr>
                    <td align='right' class='en'><b>Title:</b></td>
                    <td align='right' class='fr'><b>Titre:</b></td>
                    <td><input id='realTitle' type='text' name='realTitle' size='40' /></td>
                </tr>
                <tr>
                    <td align='right' class='en'><b>Keywords:</b></td>
                    <td align='right' class='fr'><b>Mots clés:</b></td>
                    <td><input id='keywords' type='text' name='keywords'/></td>
                </tr>
                <tr>
                    <td colspan='2' align='right'><input type='submit' id='createPageButton' value='$button_val' /></td>
                </tr>
            </table>
            </form>
        </div>
        <script type='text/javascript'>
            $('#createPageButton').click(clickButton);
            $('#newWikiPage').click(function(){
                $(this).css('display', 'none');
                $('#newWikiPageDiv').show('fast');
            });
            $('#newFilePage').click(function(){
                $(this).css('display', 'none');
                $('#newFileDiv').show('fast');
            });
            $(\"input[name='keywords']\").tagit({});
        </script>";

        $pages = Wiki::getFiles($this->table);
        $this->html .= "<h2 class='en'>Uploaded Files</h2><h2 class='fr'>Les fichiers téléchargés</h2><table id='projectFiles' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'><thead><tr bgcolor='#F2F2F2'><th>Page Title</th><th>Keywords</th><th>Last Edited</th><th>Last Edited By</th></tr></thead>\n";
        $this->html .= "<tbody>\n";
        foreach($pages as $page){
            if($page->getTitle()->getText() != "Main"){
                $data = DBFunctions::select(array('mw_an_upload_permissions'),
                                            array('*'),
                                            array("upload_name" => "File:".str_replace(" ", "_", $page->getTitle()->getText())));
                if(count($data)==0){
                    $data = DBFunctions::select(array('mw_an_upload_permissions'),
                                                array('*'),
                                                array("upload_name" => "File:".$page->getTitle()->getText()));
                }

                $this->html .= "<tr>\n";
                $revId = $page->getRevIdFetched();
                $revision = Revision::newFromId($revId);
                $date = $revision->getTimestamp();
                $year = substr($date, 0, 4);
                $month = substr($date, 4, 2);
                $day = substr($date, 6, 2);
                $hour = substr($date, 8, 2);
                $minute = substr($date, 10, 2);
                $second = substr($date, 12, 2);
                $editor = Person::newFromId($revision->getRawUser());
                if($data[0]['title'] != ""){
                    $title = ucfirst($data[0]['title']);
                }
                else{
                    $title = $page->getTitle()->getText();
                }
                $keywords = $data[0]['keywords'];
                $this->html .= "<td><a href='$wgServer$wgScriptPath/index.php/File:".urlencode(str_replace(" ", "_", "{$page->getTitle()->getText()}"))."' target='_blank'>$title</a></td>\n";
                $this->html .= "<td>$keywords</td>";
                $this->html .= "<td>{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}</td>\n";
                $me = Person::newFromWgUser();
                if($me->isRoleAtLeast(MANAGER)){
                    $this->html .= "<td><a href='{$editor->getUrl()}'>{$editor->getNameForForms()}</a></td>\n";
                }
                else{
                    $this->html .= "<td>{$editor->getNameForForms()}</td>\n";
                }
                $this->html .= "</tr>\n";
            }
        }
        $this->html .= "</tbody></table>";
        $this->html .= "<script type='text/javascript'>
            $('#projectWikiPages').dataTable({'iDisplayLength': 100, 'autoWidth': false});
        </script>";
        $this->html .= "<script type='text/javascript'>
            $('#projectFiles').dataTable({'iDisplayLength': 100, 'autoWidth': false});
        </script>";
        return $this->html;
    }

}    
    
?>
