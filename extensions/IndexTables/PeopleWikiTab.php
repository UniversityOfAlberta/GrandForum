<?php

class PeopleWikiTab extends AbstractTab {

    var $table;
    var $visibility;

    function __construct($table, $visibility){
        global $wgLang;
        if($wgLang->getCode() == 'en'){
            parent::__construct("Resources");
        }
        else if($wgLang->getCode() == 'fr'){
            parent::__construct("Ressources");
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
            $wikipage= WikiPage::factory(Title::makeTitle(NS_FILE,str_replace(" ", "_", ucfirst($_POST['realTitle']))));
            $wikipage->doEdit('','',0,false,$wgUser);
            $data = DBFunctions::select(array('mw_an_upload_permissions'),
                                        array('*'),
                                        array("upload_name" => "File:".str_replace(" ", "_", ucfirst($_POST['realTitle']))));
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
        $resources = array("Organizations", "Articles", "Patients", "Tools", "Clinical", "Resources", "Canadian", "Nursing", "Formulaires_en_français");
        if(isset($_FILES['wpUploadFile'])){
            $this->uploadFile();
        }
        
        $table = $this->table;
        $me = Person::newFromWgUser();
        
        $extraText = "";
        if($table == "Articles"){
            $extraText = " selected by the CAPS team as recommended reading for our members";
        }
        else if("Organizations"){
            $extraText = " members may be most interested in becoming familiar with";
        }
        
        if(in_array($this->table, $resources)){
            $this->html .= "<div class='helpful_resources' style='display:inline-block; font-size:1.1em'>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Clinical'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/clinical_guidelines_files.png'></a><br /><span class='en'>Clinical Guidelines</span><span class='fr'>Lignes directrices cliniques</span></div>
                                     <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Tools'><img width='100px'  src='$wgServer$wgScriptPath/skins/icons/caps/tools_tips_files.png'></a><br /><span class='en'>Tools & Tips</span><span class='fr'>Outils et conseils</span></div>
                                     <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Organizations'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/organizations_files.png'></a><br /><span class='en'>Organizations</span><span class='fr'>Organizations</span></div>
                                     <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Articles'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/articles_files.png'></a><br /><span class='en'>Articles</span><span class='fr'>Des articles</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Patients'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/patient_resource_files.png'></a><br /><span class='en'>Patient Resources</span><span class='fr'>les ressources des patients</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Canadian'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/canadian.png'></a><br /><span class='en'>Canadian Resources</span><span class='fr'>les ressources Canadiennes</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center; vertical-align:top;'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Nursing'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/nursing.png'></a><br /><span class='en'>Advanced Nursing<br />Practice Resources</span><span class='fr'>Ressources de pratique<br />infirmière avancée</span></div>
                        <div style='margin-right:10px; display:inline-block; text-align:center'><a href='$wgServer$wgScriptPath/index.php/CAPS:ALL_Formulaires en français'><img width='100px' src='$wgServer$wgScriptPath/skins/icons/caps/french.png'></a><br /><span class='en'>Formulaires en français</span><span class='fr'>Formulaires en français</span></div>
                    </div>
            </div>";

        }
        if($this->table != "Organizations"){
            $this->html .= "<br />
                        <div style='font-size: 1.5em;'>
                        <span class='en'>Below are the <b>{$wgOut->getPageTitle()}</b> in {$config->getValue('networkName')}{$extraText}.  <span class='searchDesc'>To search for a file or page in particular, use the search box below.  You can search by name, date last edited, and last editor.</span></span>
                        <span class='fr'>Ci-dessous sont tous les <b>{$wgOut->getPageTitle()}</b> dans CPCA.  <span class='searchDesc'>Pour rechercher un fichier ou une page en particulier, utiliser les champs de recherche ci-dessous. Vous pouvez rechercher par nom, date dernière édition , et le dernier éditeur.</span></span>
                        <br /><br /></div>";
        }
        else {
            $this->html .= "<br />
                        <div style='font-size: 1.5em;'>
                            <span class='en'>Collaborating Organizations, and those that support medical abortion safe practices and/or guidelines</span>
                            <span class='fr'>Les organisations collaboratrices et celles qui soutiennent les pratiques et / ou les lignes directrices en matière d'avortement médical</span>
                        </div>";
        }
        if($this->table == "Clinical"){
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
        else if($this->table == "Tools"){
            $this->html .= "
                <span class='en'><i>*For example, the official Health Canada recommended <a href='https://www.caps-cpca.ubc.ca/index.php/File:Tools_mifegymisopatientconsentforme11_29_16.pdf' target='_blank'>Mifegymiso Consent Form for Patients</a></i></span>
                <span class='fr'><i>*Par exemple, le ministère de la Santé <a href='https://www.caps-cpca.ubc.ca/index.php/File:Tools_mifegymisopatientconsentforme11_29_16.pdf' target='_blank'>Formulaire de consentement Mifegymiso pour les patients</a></i></span>
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
        if($this->table == "Articles"){
            $theme1 = array();
            $theme2 = array();
            $theme3 = array();
            $theme4 = array();
            foreach($pages as $page){
                $data = DBFunctions::select(array('mw_an_upload_permissions'),
                                                array('*'),
                                                array("upload_name" => "File:".str_replace(" ", "_", $page->getTitle()->getText()),
                                                      WHERE_OR("upload_name") => "File:".$page->getTitle()->getText()
                                                ));
                if($data[0]['title'] != ""){
                    $title = ucfirst($data[0]['title']);
                }
                else{
                    $title = $page->getTitle()->getText();
                }
                $keywords = $data[0]['keywords'];
                $url = "<p><a href='$wgServer$wgScriptPath/index.php/File:".urlencode(str_replace(" ", "_", "{$page->getTitle()->getText()}"))."' target='_blank'><img src='$wgServer$wgScriptPath/skins/icons/caps/Very-Basic-Document-icon.png'>&nbsp;$title</a></p>";
                switch($keywords){
                    case "Theme 1":
                        $theme1[] = $url;
                        break;
                    case "Theme 2":
                        $theme2[] = $url;
                        break;
                    case "Theme 3":
                        $theme3[] = $url;
                        break;
                    case "Theme 4":
                        $theme4[] = $url;
                        break;
                }
            }
            $this->html .= "<script type='text/javascript'>
                $('.searchDesc').hide();
            </script>";
            $this->html .= "<br /><br />
                <div id='accordion'>
                    <h2>Adverse Events/Outcomes<br /><span style='font-size:0.75em;'>Keywords: significant adverse events, clostridium-associated toxic shock</span></h2>
                    <div>
                        ".implode("", $theme1)."
                    </div>
                    <h2>First Trimester Medical Abortion<br /><span style='font-size:0.75em;'>Keywords: Medical abortion comparisons to surgical abortion, gestational age up to 70days, medical management, and abortion service location in Canada</span></h2>
                    <div>
                        ".implode("", $theme2)."
                    </div>
                    <h2>Pharmacological Information<br /><span style='font-size:0.75em;'>Keywords: Pharmacological restrictions, pharmacokinetics, dosing, routes of administration </span></h2>
                    <div>
                        ".implode("", $theme3)."
                    </div>
                    <h2>Post Abortion Care<br /><span style='font-size:0.75em;'>Keywords: Counselling, attitudes, immigrant/non-immigrant, values</span></h2>
                    <div>
                        ".implode("", $theme4)."
                    </div>
                </div>";
            $this->html .= "<script type='text/javascript'>
                $(document).ready(function(){
                    $('#accordion').accordion({
                      collapsible: true,
                      autoHeight: false
                    });
                });
            </script>";
        }
        else if($table == "Organizations"){
            $this->html .= "<script type='text/javascript'>
                $('#newFilePage').hide();
                $('.searchDesc').hide();
            </script>";
            $canadian = array(array('img'    => 'Canadian1.jpg',
                                    'en'     => 'https://sogc.org/',
                                    'fr'     => 'https://sogc.org/fr/index.html',
                                    'enText' => 'The Society of Obstetricians and Gynecologists of Canada',
                                    'frText' => 'La Société Des Obstétriciens et Gynécologues Du Canada'),
                              array('img'    => 'Canadian2.png',
                                    'en'     => 'http://www.cfpc.ca/Home/',
                                    'fr'     => 'http://www.cfpc.ca/projectassets/templates/home.aspx?id=510&langType=3084',
                                    'enText' => 'The College of Family Physicians of Canada',
                                    'frText' => 'Le Collège Des Médecins De Famille Du Canada'),
                              array('img'    => 'Canadian3.png',
                                    'en'     => 'https://www.cma.ca/en/pages/cma_default.aspx',
                                    'fr'     => 'https://www.cma.ca/fr/pages/cma_default.aspx',
                                    'enText' => 'Canadian Medical Association',
                                    'frText' => 'Association Médicale Canadienne'),
                              array('img'    => 'Canadian4.jpg',
                                    'en'     => 'http://www.pharmacists.ca/',
                                    'enText' => 'Canadian Pharmacists Association'),
                              array('img'    => "Canadian5_{$wgLang->getCode()}.png",
                                    'en'     => 'https://www.sexualhealthandrights.ca/',
                                    'fr'     => 'https://www.sexualhealthandrights.ca/fr/',
                                    'enText' => 'Charitable Organization- Action Canada for Sexual Health & Rights',
                                    'frText' => 'Organisation caritative - Action Canada pour la santé & les droits sexuels')
            );
            
            $american = array(array('img'    => 'American2.png',
                                    'en'     => 'http://www.acog.org/Womens-Health/Abortion',
                                    'enText' => 'The American Congress of Obstetricians and Gynecologists'),
                              array('img'    => 'American3.png',
                                    'en'     => 'http://www.reproductiveaccess.org/',
                                    'enText' => 'Reproductive Health Access Project')
            );
            
            $international = array(array('img'    => 'International1.gif',
                                         'en'     => 'https://www.rcog.org.uk/',
                                         'enText' => 'A global women’s health network that works to improve the standard of care delivered to women and to encourage the study and advancement of practice in obstetrics and gynaecology'),
                                   array('img'    => 'International2.png',
                                         'en'     => 'http://www.teachtraining.org/',
                                         'enText' => ' Academic –community partnership that aims to implement abortion training into curricula and practice'),
                                   array('img'    => 'International3.jpg',
                                         'en'     => 'http://www.who.int/reproductivehealth/en/',
                                         'fr'     => 'http://www.who.int/reproductivehealth/fr/',
                                         'enText' => 'Reproductive Health Access Project',
                                         'frText' => 'Sante sexuelle et reproductive'),
                                   array('img'    => 'International4.jpg',
                                         'en'     => 'http://www.ipas.org/',
                                         'enText' => 'International Pregnancy Advisory Services')
            );
        
            $this->html .= "<h2>Canadian</h2>";
            $this->html .= "<div style='line-height:2em;'>
                    <a target='_blank' href='http://www.nafcanada.org/'><img src='http://prochoice.org/wp-content/uploads/NAFlogoCanada-small.jpg' style='width:300px;margin-right:20px;' /></a>
                    <div style='vertical-align:middle;display:inline-block;max-width:500px;'>
                        <span class='en'>Click <a target='_blank' href='http://prochoice.org/health-care-professionals/naf-membership/'> here</a> to become a member.</span>
                        <span class='fr'>Cliquez <a target='_blank' href='http://prochoice.org/health-care-professionals/naf-membership/'>ici</a> pour devenir membre.</span>
                    </div>
                </div><br /><br />";
            foreach($canadian as $o){
                $this->html .= "<div style='line-height:2em;'><img src='$wgServer$wgScriptPath/skins/{$o['img']}' style='width:300px;margin-right:20px;' /><div style='vertical-align:middle;display:inline-block;max-width:500px;'>";
                if(isset($o['en'])){
                    $this->html .= "<a target='_blank' href='{$o['en']}'>{$o['enText']}</a><br />";
                }
                if(isset($o['fr'])){
                    $this->html .= "<a target='_blank' href='{$o['fr']}'>{$o['frText']}</a><br />";
                }
                $this->html .= "</div></div><br /><br />";
            }
            $this->html .= "<h2>American</h2>";
            foreach($american as $o){
                $this->html .= "<div style='line-height:2em;'><img src='$wgServer$wgScriptPath/skins/{$o['img']}' style='width:220px;margin-right:20px;' /><div style='vertical-align:middle;display:inline-block;max-width:500px;'>";
                if(isset($o['en'])){
                    $this->html .= "<a target='_blank' href='{$o['en']}'>{$o['enText']}</a><br />";
                }
                if(isset($o['fr'])){
                    $this->html .= "<a target='_blank' href='{$o['fr']}'>{$o['frText']}</a><br />";
                }
                $this->html .= "</div></div><br /><br />";
            }
            $this->html .= "<h2>International</h2>";
            foreach($international as $o){
                $this->html .= "<div style='line-height:2em;'><img src='$wgServer$wgScriptPath/skins/{$o['img']}' style='width:220px;margin-right:20px;' /><div style='vertical-align:middle;display:inline-block;max-width:500px;'>";
                if(isset($o['en'])){
                    $this->html .= "<a target='_blank' href='{$o['en']}'>{$o['enText']}</a><br />";
                }
                if(isset($o['fr'])){
                    $this->html .= "<a target='_blank' href='{$o['fr']}'>{$o['frText']}</a><br />";
                }
                $this->html .= "</div></div><br /><br />";
            }
        }
        else{
            $this->html .= "<h2 class='en'>Uploaded Files</h2><h2 class='fr'>Les fichiers téléchargés</h2><table id='projectFiles' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'><thead><tr bgcolor='#F2F2F2'><th>Page Title</th><th>Keywords</th><th>Last Edited</th><th>Last Edited By</th></tr></thead>\n";
            $this->html .= "<tbody>\n";
            foreach($pages as $page){
                if($page->getTitle()->getText() != "Main"){
                    $data = DBFunctions::select(array('mw_an_upload_permissions'),
                                                array('*'),
                                                array("upload_name" => "File:".str_replace(" ", "_", $page->getTitle()->getText()),
                                                      WHERE_OR("upload_name") => "File:".$page->getTitle()->getText()
                                                ));

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
                    $editor = Person::newFromId($revision->getUser());
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
                $('#projectFiles').dataTable({'iDisplayLength': 100, 'autoWidth': false, order: [[2, 'desc']]});
            </script>";
        }
        return $this->html;
    }

}    
    
?>
