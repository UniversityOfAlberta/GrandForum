<?php

class PersonProfileTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Bio");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser, $config;
        $me = Person::newFromWgUser();
        $this->person->getLastRole();
        $this->html .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:5px;'>";
        $this->html .= "</td><td id='firstLeft' width='60%' valign='top'>";
        $this->showContact($this->person, $this->visibility);
        $crdc = $this->person->getCRDC(", ");
        $keywords = $this->person->getKeywords(", ");
        $statuses = "";
        if (!empty($config->getValue("userStatusOptions"))) {
            $statuses = $this->person->getStatuses(", ");
        }
        if($this->person->getProfile() != "" || $crdc != "" || $keywords != "" || $statuses != ""){
            $this->html .= "<h2 style='margin-top:0;padding-top:0;'>Profile</h2>
                            <table>";
            if($me->isRoleAtLeast(STAFF)){
                $this->html .= ($this->person->getFirstName() != "") ? "<tr><td valign='top' align='right' style='white-space: nowrap;'><b>First Name:</b></td><td>{$this->person->getFirstName()}</td></tr>" : "";
                $this->html .= ($this->person->getMiddleName() != "") ? "<tr><td valign='top' align='right' style='white-space: nowrap;'><b>Middle Name:</b></td><td>{$this->person->getMiddleName()}</td></tr>" : "";
                $this->html .= ($this->person->getLastName() != "") ? "<tr><td valign='top' align='right' style='white-space: nowrap;'><b>Last Name:</b></td><td>{$this->person->getLastName()}</td></tr>" : "";
                $this->html .= ($this->person->getEmployeeId() != "") ? "<tr><td valign='top' align='right' style='white-space: nowrap;'><b>Employee Id:</b></td><td>{$this->person->getEmployeeId()}</td></tr>" : "";
            }
            $this->html .= ($keywords != "") ? "<tr><td valign='top' align='right' style='white-space: nowrap;'><b>Keywords:</b></td><td>{$keywords}</td></tr>" : "";
            $this->html .= ($statuses != "") ? "<tr><td valign='top' align='right' style='white-space: nowrap;'><b>Status:</b></td><td>{$statuses}</td></tr>" : "";
            $this->html .= ($crdc != "") ? "<tr><td valign='top' align='right' style='white-space: nowrap;'><b>CRDC Codes:</b></td><td>{$crdc}</td></tr>" : "";
            $this->html .= "</table>";
            $this->showProfile($this->person, $this->visibility);
        }
        $this->html .= $this->showFundedProjects($this->person, $this->visibility);
        $this->html .= $this->showTable($this->person, $this->visibility);
        $extra = array();
        if(($config->getValue('wordCloudForEveryone') || 
            $this->person->isRole(NI) || 
            $this->person->isRole(HQP) || 
            $this->person->isRole(EXTERNAL)) && isExtensionEnabled("Visualizations")){
            // Only show the word cloud for 'researchers'
            $extra[] = $this->showCloud($this->person, $this->visibility);
        }
        if(isExtensionEnabled("Visualizations")){
            $extra[] = $this->showDoughnut($this->person, $this->visibility);
        }
        
        // Delete extra widgets which have no content
        foreach($extra as $key => $e){
            if($e == ""){
                unset($extra[$key]);
            }
        }
        $this->html .= "</td><td id='firstRight' valign='top' width='40%' style='padding-top:15px;padding-left:15px;'>".implode("<hr />", $extra)."</td></tr>";
        $this->html .= "</table>";
        $this->html .= "<script type='text/javascript'>
            setInterval(function(){
                var table = $('#personProducts').DataTable();
                if($('#bodyContent').width() < 650){
                    $('td#firstRight').hide();
                    $('.chordChart').hide();
                    
                    table.column(1).visible(false);
                    table.column(2).visible(false);
                    table.column(3).visible(false);
                }
                else{
                    $('td#firstRight').show();
                    $('.chordChart').show();
                    
                    table.column(1).visible(true);
                    table.column(2).visible(true);
                    table.column(3).visible(true);
                }
            }, 33);
            $(document).ready(function(){
                if($('#person_products').length > 0 || $('#funded_projects') > 0){
                    $('div#bio [name=submit]').clone().appendTo($('#profileText'));
                }
            });
        </script>";
        $this->showCCV($this->person, $this->visibility);
        return $this->html;
    }
    
    function generateEditBody(){
        $this->html .= "<table>";
        $this->showEditPhoto($this->person, $this->visibility);
        $this->html .= "</td><td style='padding-right:25px;' valign='top'>";
        $this->showEditContact($this->person, $this->visibility);
        $this->html .= "</td></tr></table>";
        $this->html .= "<h2>Profile</h2>";
        $this->showEditProfile($this->person, $this->visibility);
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return (($this->visibility['isMe'] || 
                 $this->visibility['isSupervisor'] ||
                 $this->visibility['isLeader']) &&
                $me->isAllowedToEdit($this->person));
    }
    
    function handleEdit(){
        $this->handleContactEdit();
        $_POST['user_name'] = $this->person->getName();
        
        $this->person->publicProfile = @$_POST['public_profile'];
        $this->person->privateProfile = @$_POST['private_profile'];
        $this->person->pronouns = @$_POST['pronouns'];
        if(isset($_POST['crdc'])){
            $this->person->setCRDC($_POST['crdc']);
        }
        $this->person->setKeywords(explode(",", $_POST['keywords']));
        $this->person->setAliases(explode(";", $_POST['aliases']));
        
        if (isset($_POST['user_status']) && is_array($_POST['user_status'])) {
            $this->person->setStatuses($_POST['user_status']);
        } else {
            $this->person->setStatuses([]);
        }
        $this->person->update();
        // Update Role Titles
        if(isset($_POST['role_title'])){
            foreach($this->person->getRoles() as $role){
                if(isset($_POST['role_title'][$role->getId()])){
                    $value = $_POST['role_title'][$role->getId()];
                    DBFunctions::update('grand_roles', 
                                        array('title' => $value),
                                        array('id' => $role->getId()));
                }
            }
            Cache::delete("personRolesDuring{$this->person->getId()}*", true);
            Cache::delete("rolesCache");
        }

        Person::$rolesCache = array();
        Person::$cache = array();
        Person::$namesCache = array();
        Person::$idsCache = array();
        
        $this->person = Person::newFromId($this->person->getId());
    }
    
    function handleContactEdit(){
        global $wgImpersonating, $config;
        $error = "";
        if(!$wgImpersonating && isset($_FILES['photo']) && $_FILES['photo']['tmp_name'] != ""){
            $type = $_FILES['photo']['type'];
            $size = $_FILES['photo']['size'];
            $tmp = $_FILES['photo']['tmp_name'];
            if($type == "image/jpeg" ||
               $type == "image/pjpeg" ||
               $type == "image/gif" || 
               $type == "image/png"){
                if($size <= 1024*1024*5){
                    //File is OK to upload
                    $fileName = "Photos/".str_replace(".", "_", $this->person->getName()).".jpg";
                    move_uploaded_file($tmp, $fileName);
                    
                    if($type == "image/jpeg" || $type == "image/pjpeg"){
                        $src_image = @imagecreatefromjpeg($fileName);
                    }
                    else if($type == "image/png"){
                        $src_image = @imagecreatefrompng($fileName);
                    }
                    else if($type == "image/gif"){
                        $src_image = @imagecreatefromgif($fileName);
                    }
                    if($src_image != false){
                        imagealphablending($src_image, true);
                        imagesavealpha($src_image, true);
                        $src_width = imagesx($src_image);
                        $src_height = imagesy($src_image);
                        $dst_width = 300;
                        $dst_height = ($src_height*300)/$src_width;
                        if($dst_height > 396){
                            $dst_height = 396;
                            $dst_width = ($src_width*396)/$src_height;
                        }
                        $dst_image = imagecreatetruecolor($dst_width, $dst_height);
                        imagealphablending($dst_image, true);
                        
                        imagesavealpha($dst_image, true);
                        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
                        imagedestroy($src_image);
                        
                        imagejpeg($dst_image, $fileName, 100);
                        imagedestroy($dst_image);
                    }
                    else{
                        //File is not an ok filetype
                        $error .= "The file you uploaded is not of the right type.  It should be either gif, png or jpeg";
                    }
                }
                else{
                    //File size is too large
                    $error .= "The file you uploaded is too large.  It should be smaller than 5MB.<br />";
                }
            }
            else{
                //File is not an ok filetype
                $error .= "The file you uploaded is not of the right type.  It should be either gif, png or jpeg.<br />";
            }
        }
        if($error == ""){
            // Insert the new data into the DB
            $_POST['user_name'] = $this->person->getName();
            $_POST['phone'] = @$_POST['phone'];

            $api = new UserPhoneAPI();
            $api->doAction(true);
            
            $this->person->firstName = @$_POST['first_name'];
            $this->person->middleName = @$_POST['middle_name'];
            $this->person->lastName = @$_POST['last_name'];
            $this->person->realname = @"{$_POST['first_name']} {$_POST['last_name']}";
            if(isset($_POST['employeeId'])){
                $this->person->employeeId = $_POST['employeeId'];
            }
            $this->person->gender = @$_POST['gender'];
            $this->person->twitter = @$_POST['twitter'];
            $this->person->website = @$_POST['website'];
            $this->person->linkedin = @$_POST['linkedin'];
            $this->person->googleScholar = @$_POST['googleScholarUrl'];
            $this->person->scopus = @$_POST['scopus'];
            $this->person->orcid = @$_POST['orcid'];
            $this->person->researcherId = @$_POST['researcherId'];
            $this->person->office = @$_POST['office'];
            $this->person->nationality = @$_POST['nationality'];
            $this->person->stakeholder = @$_POST['stakeholder'];
            $this->person->earlyCareerResearcher = @$_POST['earlyCareerResearcher'];
            $this->person->agencies = @$_POST['agencies'];
            $this->person->mitacs = @$_POST['mitacs'];
            if($config->getValue('crcEnabled')){
                $this->person->canadaResearchChair = array(
                    'rank' => @str_replace("'", "&#39;", $_POST['crc_rank']),
                    'title' => @str_replace("'", "&#39;", $_POST['crc_title']),
                    'date' => @$_POST['crc_date']
                );
            }
            
            $this->person->update();
            if(isset($_POST['email'])){
                $api = new UserEmailAPI();
                $api->doAction(true);
            }
        }
        
        //Reset the cache to use the changed data
        unset(Person::$cache[$this->person->id]);
        unset(Person::$cache[$this->person->getName()]);
        Person::$idsCache = array();
        Person::$namesCache = array();
        $this->person = Person::newFromId($this->person->id);
        return $error;
    }
    
    /**
     * Displays the profile for this user
     */
    function showProfile($person, $visibility){
        global $wgUser, $config;
        $this->html .= "<div id='profileText' style='text-align:justify;'>";
        $this->html .= $person->getProfile($wgUser->isRegistered());
        if($visibility['isMe'] || $visibility['isSupervisor']){
            $crc = "";
            $this->html .= "<ul>";
            if($config->getValue('crcEnabled')){
                $crcObj = $person->getCanadaResearchChair();
                if(@strstr($crcObj['rank'], "Yes") !== false){
                    $rank = "";                    
                    switch($crcObj['rank']){
                        case "Yes, I am a Tier 1 Canada Research Chair (CRC) or equivalent":
                            $rank = "CRCT1";
                            break;
                        case "Yes, I am a Tier 2 Canada Research Chair (CRC) or equivalent":
                            $rank = "CRCT2";
                            break;
                        case "Yes, I am a Canada Excellence Research Chair (CERC) or equivalent":
                            $rank = "CERC";
                            break;
                        case "Yes, I am a Canada 150 Research Chair (C150) or equivalent";
                            $rank = "C150";
                            break;
                    }
                    $this->html .= "<li>[{$crcObj['title']}] {$rank}, {$crcObj['date']}</li>";
                }
            }

            if($config->getValue('ecrEnabled')){
                if($person->getEarlyCareerResearcher() == "Yes"){
                    $this->html .= "<li>{$config->getValue('networkName')} ECR</li>";
                }
            }
            $agencies = "";
            if($config->getValue('agenciesEnabled')){
                $agencies = $person->getAgencies();
                if(count($agencies) > 0){
                    $this->html .= "<li>Applies for funding from:<ul>";
                    foreach($agencies as $agency){
                        $this->html .= "<li>{$agency}</li>";
                    }
                    $this->html .= "</ul></li>";
                }
            }
            $this->html .= "</ul>";
        }
        $this->html .= "</div>";
    }
    
    function showEditProfile($person, $visibility){
        global $config;
        
        if(count($config->getValue('crdcCodes')) > 0){
            $crdcField = new MultiSelectBox("crdc", "CRDC", $person->getCRDC(), $config->getValue('crdcCodes'));
            $this->html .= "
                <h3>CRDC Codes:</h3>
                {$crdcField->render()}
                <script type='text/javascript'>
                    $(document).ready(function(){
                        $(\"select[name='crdc[]'\").chosen();
                    });
                </script>";
        }
        $this->html .= "
                <h3>Keywords:</h3>
                <input class='keywords' type='text' name='keywords' value='' />";

        $allStatuses = $config->getValue("userStatusOptions");
        if (!empty($allStatuses)) {
            $selectedStatuses = $person->getStatuses();
            
            $statusField = new MultiSelectBox(
                "user_status",
                "User Status",
                $selectedStatuses,
                $allStatuses,
                null,
                'simple'
            );

            $this->html .= "
                    <h3>User Status:</h3>
                    {$statusField->render()}";
        }

        if($config->getValue("publicProfileOnly")){
            $this->html .= "
                <h3>Profile:</h3>
                <textarea class='profile' style='width:auto; height:200px;' name='public_profile'>{$person->getProfile(false)}</textarea>";
        }
        else{
            $this->html .= "
                <h3>Live on Website:</h3>
                <textarea class='profile' style='width:auto; height:200px;' name='public_profile'>{$person->getProfile(false)}</textarea><br />

                <h3>Live on Forum:</h3>
                <textarea class='profile' style='width:auto; height:200px;' name='private_profile'>{$person->getProfile(true)}</textarea>
             ";
         }
         $this->html .= "<script type='text/javascript'>
            $('input.keywords').val('".addslashes($person->getKeywords(","))."');
            $('input.keywords').tagit({
                allowSpaces: true
            });
            $('select[name=\"user_status[]\"]').chosen({width: '200px'});
            $('textarea.profile').tinymce({
                theme: 'modern',
                relative_urls : false,
                convert_urls: false,
                menubar: false,
                plugins: 'link image charmap lists table paste wordcount',
                toolbar: [
                    'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify'
                ],
                paste_postprocess: function(plugin, args) {
                    var p = $('p', args.node);
                    p.each(function(i, el){
                        $(el).css('line-height', 'inherit');
                    });
                }
            });
        </script>";

    }
    
    function showCloud($person, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getPersonCloudData&person={$person->getId()}";
        $wordle = new Wordle($dataUrl, true, '$("#personProducts_wrapper input").val(text); $("#personProducts_wrapper input").trigger("keyup")');
        $wordle->width = "100%";
        $wordle->height = 232;
        return $wordle->show()."<script type='text/javascript'>
                                    onLoad{$wordle->index}();
                                </script>";
    }
    
    static function getPersonCloudData($action, $article){
        global $wgServer, $wgScriptPath;
        if($action == "getPersonCloudData"){
            $text = "";
            $person = Person::newFromId($_GET['person']);
            $text .= $person->getKeywords(", ")."\n";
            $text .= $person->getProfile()."\n";
            
            $products = $person->getPapers("all", false, 'both', false, 'Public');
            foreach($products as $product){
                $text .= $product->getTitle()."\n";
                $text .= $product->getDescription()."\n";
            }
            if(isExtensionEnabled('UofANews')){
                $news = UofANews::getNewsForPerson($person);
                foreach($news as $article){
                    $text .= "{$article->getPartialTitle()}\n";
                }
            }
            CommonWords::$commonWords[] = strtolower($person->getFirstName());
            CommonWords::$commonWords[] = strtolower($person->getLastName());
            $data = Wordle::createDataFromText($text);
            $data = array_slice($data, 0, 75);
            header("Content-Type: application/json");
            echo json_encode($data);
            close();
        }
        return true;
    }

    function showDoughnut($person, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut, $wgUser;
        $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getDoughnutData&person={$person->getId()}";
        $fn = '$("#personProducts_wrapper input").val(text); $("#personProducts_wrapper input").trigger("keyup")';
        $doughnut = new Doughnut($dataUrl, true, $fn);
        $wgOut->addScript("<script type='text/javascript'>
                                $(document).ready(function(){
                                    if(!$('#vis{$doughnut->index}').is(':visible')){
                                        var interval = setInterval(function(){
                                            if($('#vis{$doughnut->index}').is(':visible')){
                                                $('#vis{$doughnut->index}').doughnut('{$doughnut->url}', true, function(text){ {$fn} });
                                                clearInterval(interval);
                                            }
                                        }, 100);
                                    }
                                });
                          </script>");
        return $doughnut->show();
    }
    
    function showChord($person, $visibility){
        global $wgServer, $wgScriptPath, $wgTitle, $wgOut;
        $dataUrl = "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}?action=getChordData&person={$person->getId()}";
        $html = "<div style='position:absolute; right:0; display:inline-block; text-align:right;'>";
        $chord = new Chord($dataUrl);
        $chord->width = 226;
        $chord->height = 226;
        $chord->options = false;
        $chord->fn = '$("#personProducts_wrapper input").val(data.labels[d.index]); $("#personProducts_wrapper input").trigger("keyup")';
        $html .= $chord->show();
        $html .= "</div>";
        return $html."<script type='text/javascript'>
                                $('#vis{$chord->index}').hide();
                                var maxWidth = {$chord->width};
                                var width = -1;
                                var height = {$chord->height};
                                var lastWidth = -1;
                                setInterval(function(){
                                    var leftWidth = $('#firstLeft').width();
                                    var cardWidth = $('#firstLeft div#card').width();
                                    var widthDiff = leftWidth - cardWidth;
                                    newWidth = Math.min(maxWidth, widthDiff);
                                    if($('#vis{$chord->index}').is(':visible') && (width != newWidth || $('#vis{$chord->index} svg').width() != width)){
                                        width = newWidth;
                                        height = width;
                                        if(width < 100){
                                            // Too small, just don't show it anymore
                                            $('#vis{$chord->index}').empty();
                                        }
                                        else{
                                            $('#vis{$chord->index}').empty();
                                            $('#vis{$chord->index}').show();
                                            render{$chord->index}(width, height);
                                        }
                                        $('#vis{$chord->index}').height(Math.max(1,height));
                                        $('#vis{$chord->index}').width(Math.max(1,width));
                                        lastWidth = $('#firstLeft').width();
                                        $('#contact').height(Math.max(172, Math.max(height, $('#contact > #card').height())));
                                    }
                                }, 100);
                          </script>";
    }
    
    function showFundedProjects($person, $visibility){
        global $config;
        $html = "";
        $projects = $person->getProjects(true);
        if(count($projects) > 0){
            $html .= "<div id='funded_projects'><h2>{$config->getValue('networkName')} Funded ".Inflect::pluralize($config->getValue('projectTerm'))."</h2><ul>";
            foreach($projects as $project){
                $completed = ($project->getStatus() == "Ended") ? " (completed)" : "";
                $html .= "<li><a class='projectUrl' data-projectId='{$project->getId()}' href='{$project->getUrl()}'>{$project->getFullName()} ({$project->getName()})</a>{$completed}</li>";
            }
            $html .= "</ul></div>";
        }
        return $html;
    }
    
    /**
     * Shows a table of this Person's products, and is filterable by the
     * visualizations which appear above it.
     */
    function showTable($person, $visibility){
        global $config;
        $me = Person::newFromWgUser();
        $products = $person->getPapers("all", false, 'both', true, "Public");
        $string = "";
        if(count($products) > 0){
            $string = "<div id='person_products'><h2>".Inflect::pluralize($config->getValue('productsTerm'))."</h2>";
            $string .= "<button id='showOnlyAuthor' type='button' style='margin-bottom: 0.25em;'>Show only Author</button>
            <table id='personProducts' rules='all' frame='box'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Authors</th>
                        <th style='display:none;'>Projects</th>
                    </tr>
                </thead>
                <tbody>";
            foreach($products as $paper){
                $projects = array();
                foreach($paper->getProjects() as $project){
                    $projects[] = "{$project->getName()}";
                }

                $names = array();
                foreach($paper->getAuthors() as $author){
                    if($author->getId() != 0 && $author->getUrl() != ""){
                        $names[] = "<a href='{$author->getUrl()}'>{$author->getNameForProduct()}</a>";
                    }
                    else{
                        $names[] = $author->getNameForForms();
                    }
                }
                
                $projects = array();
                foreach($paper->getProjects() as $project){
                    $projects[] = $project->getName();
                }
                
                $string .= "<tr>";
                $string .= "<td><span class='productTitle' data-id='{$paper->getId()}' data-href='{$paper->getUrl()}'>{$paper->getTitle()}</span><span style='display:none'>{$paper->getDescription()}".implode(", ", $projects)." ".implode(", ", $paper->getUniversities())."</span></td>";
                $string .= "<td>{$paper->getCategory()}</td>";
                $string .= "<td style='white-space: nowrap;'>{$paper->getDate()}</td>";
                $string .= "<td><div style='display: -webkit-box;-webkit-line-clamp: 3;-webkit-box-orient: vertical;overflow: hidden;'>".implode(", ", $names)."</div></td>";
                $string .= "<td style='display:none;'>".implode(", ", $projects)."</td>";
                
                $string .= "</tr>";
            }
            $string .= "</tbody>
                </table></div>
                <script type='text/javascript'>
                    var personProducts = $('#personProducts').dataTable({
                        order: [[ 2, 'desc' ]],
                        autoWidth: false,
                       'dom': 'Blfrtip',
                       'buttons': [
                            'excel', 'pdf'
                        ],
                        drawCallback: renderProductLinks
                    });
                    
                    $('#showOnlyAuthor').click(function(el){
                        var search = '';
                        if($(this).text() == 'Show only Author'){
                            search = ".json_encode("\"{$person->getNameForProduct()}\"").";
                            $(this).text('Show all');
                        }
                        else{
                            $(this).text('Show only Author');
                        }
                        $('#personProducts_wrapper input').val(search); 
                        $('#personProducts_wrapper input').trigger('keyup');
                    });
                </script>";
        }
        return $string;
    }
   
    /**
     * Displays the profile for this user
     */
    function showCCV($person, $visibility){
        global $wgUser, $wgServer, $wgScriptPath;
        return; //FIXME: Disabled for now (won't import into Canadian CCV)
        if(isExtensionEnabled('CCVExport')){
            $me = Person::newFromWgUser();
            if(($person->isRole(NI)) && $me->getId() == $person->getId()){
                $this->html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:CCVExport?getXML'>Download CCV</a>";
            }
        }
    }
    
    /**
     * Displays the photo for this person
     */
    function showPhoto($person, $visibility){
        $this->html .= "<tr><td style='padding-right:25px;' valign='top'>";
        if($person->getPhoto() != ""){
            $this->html .= "<img src='{$person->getPhoto()}' alt='{$person->getName()}' />";
        }
        $this->html .= "<div id=\"special_links\"></div>";
    }
    
    function showEditPhoto($person, $visibility){
        global $config;
        $me = Person::newFromWgUser();
        $this->html .= "<tr><td style='padding-right:25px;' valign='top' colspan='2'>";
        $this->html .= "<img src='{$person->getPhoto()}' alt='{$person->getName()}' style='max-width:100px;max-height:132px;' />";
        $this->html .= "<div id=\"special_links\"></div>";
        $this->html .= "</td></tr>";
        $this->html .= "<tr><td style='padding-right:25px;' valign='top'><table>";
        if($config->getValue('allowPhotoUpload') || $me->isRoleAtLeast(STAFF)){
            $this->html .= "<tr>
                                <td class='label'>Upload new Photo:</td>
                                <td class='value'><input type='file' name='photo' />
                                                  <small>
                                                    <ul>
                                                        <li>For best results, the image should be 300x396</li>
                                                        <li>Max file size is 5MB</li>
                                                        <li>File type must be <i>gif</i>, <i>png</i> or <i>jpeg</i></li>
                                                    </ul>
                                                    </small></td>
                            </tr>";
        }
        $this->html .= "    <tr>
                                <td class='label'>Website Url:</td>
                                <td class='value'><input type='text' size='30' name='website' value='".str_replace("'", "&#39;", $person->getWebsite())."' /></td>
                            </tr>
                            <tr>
                                <td class='label'>Google Scholar URL:</td>
                                <td class='value'><input type='text' size='30' name='googleScholarUrl' placeholder='https://scholar.google.ca/citations?user=XXXXXXXXX' value='".str_replace("'", "&#39;", $person->getGoogleScholar())."' /></td>
                            </tr>
                            <tr>
                                <td class='label'>Sciverse Id:</td>
                                <td class='value'><input type='text' size='30' name='scopus' placeholder='0000000000' value='".str_replace("'", "&#39;", $person->getScopus())."' /></td>
                            </tr>
                            <tr>
                                <td class='label'>ORCID:</td>
                                <td class='value'><input type='text' size='30' name='orcid' placeholder='0000-0000-0000-0000' value='".str_replace("'", "&#39;", $person->getOrcid())."' /></td>
                            </tr>
                            <tr>
                                <td class='label'>ResearcherID:</td>
                                <td class='value'><input type='text' size='30' name='researcherId' placeholder='H-0000-0000' value='".str_replace("'", "&#39;", $person->getResearcherId())."' /></td>
                            </tr>
                            <tr>
                                <td class='label'>LinkedIn Url:</td>
                                <td class='value'><input type='text' size='30' name='linkedin' value='".str_replace("'", "&#39;", $person->getLinkedIn())."' /></td>
                            </tr>
                            <tr>
                                <td class='label'>&#120143; Account:</td>
                                <td class='value'><input type='text' name='twitter' value='".str_replace("'", "&#39;", $person->getTwitter())."' /></td>
                            </tr>
                            <tr>
                                <td class='label'>Office Address:</td>
                                <td class='value'><input type='text' size='30' name='office' value='".str_replace("'", "&#39;", $person->getOffice())."' /></td>
                            </tr>
                            <tr>
                                <td class='label'>Phone Number:</td>
                                <td class='value'><input type='text' name='phone' value='".str_replace("'", "&#39;", $person->getPhoneNumber())."' /></td>
                            </tr>
                        </table></td>";
    }
    
   /**
    * Displays the contact information for this person
    */
    function showContact($person, $visibility){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $this->html .= "<div id='contact' style='white-space: nowrap;position:relative;min-height:172px'>";
        $this->html .= <<<EOF
            <div id='card' style='min-height:142px;display:inline-block;vertical-align:top;'></div>
            <script type='text/javascript'>
                $(document).ready(function(){
                    var person = new Person({$person->toJSON()});
                    var card = new LargePersonCardView({el: $("#card"), model: person});
                    card.render();
                });
            </script>
EOF;
        if(isExtensionEnabled("Visualizations")){
            $this->html .= $this->showChord($person, $visibility);
        }
        $this->html .= "</div>";
    }
    
    function showEditContact($person, $visibility){
        global $wgOut, $wgUser, $config, $wgServer, $wgScriptPath, $countries;
        $university = $person->getUniversity();
        $nationality = "";
        $me = Person::newFromWgUser();
        if($visibility['isMe'] || $visibility['isSupervisor']){
            $nationality = "";
            if($config->getValue("nationalityEnabled") && ($person->isMe() || $me->isRoleAtLeast(STAFF))){
                if($config->getValue("nationalityAll")){
                    $nationalityField = new SelectBox("nationality", "Nationality", $person->getNationality(), array_merge(array(""), array_values($countries)));
                    $nationality = "<tr>
                        <td class='label'>Nationality:
                            <small style='margin-top: -1em; display: block; font-weight:normal;'>Only visible to Staff</small>
                        </td>
                        <td class='value'>
                            {$nationalityField->render()}
                            <script type='text/javascript'>$(document).ready(function(){ $('#nationality').chosen(); });</script>
                        </td>
                    </tr>";
                }
                else{
                    $nationalityField = new SelectBox("nationality", "Nationality", $person->getNationality(), array("" => "---", 
                                                                                                                     "Canadian" => "Canadian/Landed Immigrant", 
                                                                                                                     "Foreign"));
                    $nationality = "<tr>
                        <td class='label'>Nationality:
                            <small style='margin-top: -1em; display: block; font-weight:normal;'>Only visible to Staff</small>
                        </td>
                        <td class='value'>{$nationalityField->render()}</td>
                    </tr>";
                }
            }
            $gender = "";
            if($config->getValue("genderEnabled") && ($person->isMe() || $me->isRoleAtLeast(STAFF))){
                $genderField = new SelectBox("gender", "Gender", $person->getGender(), array("" => "---", 
                                                                                             "Male", 
                                                                                             "Female",
                                                                                             "Gender-fluid",
                                                                                             "Non-binary",
                                                                                             "Two-spirit",
                                                                                             "Not disclosed"));
                $gender = "<tr>
                    <td class='label'>Gender:
                        <small style='margin-top: -1em; display: block; font-weight:normal;'>Only visible to Staff</small>
                    </td>
                    <td class='value'>{$genderField->render()}</td>
                </tr>";
                if($config->getValue('networkName') != 'FES'){
                    $pronounsField = new ComboBox("pronouns", "Pronouns", $person->getPronouns(), array("", "she/her", "he/him", "they/them"));
                    $gender .= "<tr>
                    <td class='label'>Pronouns:</td>
                    <td class='value'>{$pronounsField->render()}</td>
                </tr>";
                }
            }
            
            $stakeholderCategories = $config->getValue('stakeholderCategories');
            $stakeholder = "";
            if(count($stakeholderCategories) > 0){
                $stakeholderCategories = array_merge(array("" => "---"), $stakeholderCategories);
                $stakeholderField = new SelectBox("stakeholder", "Stakeholder", $person->getStakeholder(), $stakeholderCategories);
                $stakeholder = "<tr>
                    <td class='label'>{$config->getValue('stakeholderCategoryTerm')}:</td>
                    <td class='value'>{$stakeholderField->render()}</td>
                </tr>";
            }
            
            
            $crc = "";
            if($config->getValue('crcEnabled')){
                $crcObj = $person->getCanadaResearchChair();
                $crcOptions = array("" => "---",
                                    "No", 
                                    "Yes, I am a Tier 1 Canada Research Chair (CRC) or equivalent", 
                                    "Yes, I am a Tier 2 Canada Research Chair (CRC) or equivalent",
                                    "Yes, I am a Canada Excellence Research Chair (CERC) or equivalent",
                                    "Yes, I am a Canada 150 Research Chair (C150) or equivalent");
                $crcField = new SelectBox("crc_rank", "CRC Rank", @$crcObj['rank'], $crcOptions);
                $crc = @"<tr>
                            <td colspan='2'>
                                <fieldset>
                                    <legend>Are you currently a CRC, CERC, C150 (or equivalent)?</legend>
                                    {$crcField->render()}
                                    <div id='crc_title' style='display:none;'>
                                        <br />
                                        <b>Title of your Chair position</b><br />
                                        <input type='text' name='crc_title' value='{$crcObj['title']}' size='55' /><br />
                                        <b>Date</b><br />
                                        <input type='text' name='crc_date' value='{$crcObj['date']}' format='yy-mm-dd' size='10' />
                                    </div>
                                    </fieldset>
                                    
                                    <script type='text/javascript'>
                                        $('[name=crc_rank]').change(function(){
                                            if($(this).val() != '' && $(this).val() != 'No'){
                                                $('#crc_title').show();
                                            }
                                            else{
                                                $('#crc_title').hide();
                                            }
                                        });
                                        $('[name=crc_rank]').change();
                                        
                                        $('[name=crc_date]').datepicker({
                                            'dateFormat': $('[name=crc_date]').attr('format'),
                                            'defaultDate': $('[name=crc_date]').attr('value').substr(0, 10),
                                            'changeMonth': true,
                                            'changeYear': true,
                                            'showOn': 'both',
                                            'buttonImage': '{$wgServer}{$wgScriptPath}/skins/calendar.gif',
                                            'buttonImageOnly': true
                                        });
                                    </script>
                                </td>
                            </tr>";
            }
            
            
            $ecr = "";
            if($config->getValue('ecrEnabled')){
                $ecrField = new SingleCheckBox("earlyCareerResearcher", "ECR", $person->getEarlyCareerResearcher(), array("Yes"));
                $ecr = "<tr>
                            <td colspan='2'>
                                <fieldset>
                                    <legend>Was your first appointment as a professor within 5 years of the beginning of your {$config->getValue('networkName')} research?</legend>
                                    {$ecrField->render()}
                                    <small>CFREF defines an Early Career Researcher as a researcher who has five or less experience since their first research appointment, minus eligible leaves</small>
                                </fieldset>
                            </td>
                        </tr>";
            }
            $agencies = "";
            if($config->getValue('agenciesEnabled')){
                $checkbox = new VerticalCheckBox("agencies", "agencies", $person->getAgencies(), array("CFI","CIHR","NSERC","SSHRC"));
                $agencies = "<tr>
                            <td colspan='2'>
                                <fieldset>
                                    <legend>From which agencies or organizations do you apply for funding?</legend>
                                    {$checkbox->render()}
                                </fieldset>
                            </td>
                        </tr>";
            }
            $mitacs = "";
            if($config->getValue('mitacsEnabled')){
                $mitacsField = new SingleCheckBox("mitacs", "MITACS", $person->getMitacs(), array("Yes"));
                $mitacs = "<tr>
                            <td colspan='2'>
                                <fieldset>
                                    <legend>Are you interested in being contacted with MITACS and other research opportunities?</legend>
                                    {$mitacsField->render()}
                                </fieldset>
                            </td>
                        </tr>";
            }
        }
        if($config->getValue('networkType') == "CFREF"){
            $this->html .= "<b>Please add your name, middle name, and last name as per your employment records</b>";
        }
        $this->html .= "<table>
                            <tr>
                                <td class='label'>First Name:</td>
                                <td class='value'><input type='text' name='first_name' value='".str_replace("'", "&#39;", $person->getFirstName())."'></td>
                            </tr>
                            <tr>
                                <td class='label'>Middle Name:</td>
                                <td class='value'><input type='text' name='middle_name' value='".str_replace("'", "&#39;", $person->getMiddleName())."'></td>
                            </tr>
                            <tr>
                                <td class='label'>Last Name:</td>
                                <td class='value'><input type='text' name='last_name' value='".str_replace("'", "&#39;", $person->getLastName())."'></td>
                            </tr>
                            <tr>
                                <td class='label'>Aliases:
                                    <small style='margin-top:-1em; display: block; font-weight:normal;'>Can be used for alternate names</small>
                                    <small style='margin-top:-1em; display: block; font-weight:normal;'>to help match ".strtolower($config->getValue('productsTerm'))." authors</small>
                                </td>
                                <td class='value' style='max-width: 0;'><input type='text' name='aliases' value='".str_replace("'", "&#39;", implode(";", $person->getAliases()))."' /></td>
                            </tr>";
                   
        if($me->isRoleAtLeast(STAFF) && $config->getValue('networkType') == 'CFREF'){
            $this->html .= "<tr>
                                <td align='right'><b>Employee Id:</b></td>
                                <td><input size='10' type='text' name='employeeId' value='".str_replace("'", "&#39;", $person->getEmployeeId())."'></td>
                            </tr>";
        }
        $this->html .= "    <tr>
                                <td class='label'>Email:</td>";
        if((!isExtensionEnabled("Shibboleth") && !isExtensionEnabled("OpenIDConnect")) || $me->isRoleAtLeast(MANAGER)){
            $this->html .= "<td class='value'><input size='30' type='text' name='email' value='".str_replace("'", "&#39;", $person->getEmail())."' /></td>";
        }
        else{
            $this->html .= "<td class='value'>{$person->getEmail()}</td>";
        }
        $this->html .= "</tr>
                            {$nationality}
                            {$gender}
                            {$stakeholder}
                            {$crc}
                            {$ecr}
                            {$agencies}
                            {$mitacs}";
        
        $roles = $person->getRoles();
        if($me->isRoleAtLeast(STAFF) && $config->getValue("roleTitlesEnabled")){
            $this->html .= "<tr>
                                <td><b>Role Titles:</b></td>
                                <td><table>";
            $titles = array("", "Chair", "Vice-Chair", "Member", "Non-Voting");
            foreach($roles as $role){
                if($role->getId() > 0){
                    $roleTitleCombo = new ComboBox("role_title[{$role->getId()}]", "Title", $role->getTitle(), $titles);
                    $this->html .= "<tr>
                                        <td class='label'>{$role->getRole()}:</td>
                                        <td class='value'>{$roleTitleCombo->render()}</td>
                                    </tr>";
                }
            }
            $this->html .= "</table></td></tr>";
        }
        $this->html .= "</table>";
        
        // Load the scripts for Manage People so that the University editing can be used
        $managePeople = new ManagePeople();
        $managePeople->loadTemplates();
        $managePeople->loadModels();
        $managePeople->loadHelpers();
        $managePeople->loadViews();
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/ManagePeople/style.css' type='text/css' rel='stylesheet' />");
        $this->html .= "</td></tr><tr><td colspan='2'><div id='editUniversities' style='border: 1px solid #CCCCCC;'></div><input style='margin-top: 3px;' type='button' id='addUniversity' value='Add Institution' />
        <script type='text/javascript'>
            $('input[name=aliases]').tagit({
                allowSpaces: true,
                removeConfirmation: false,
                singleField: true,
                singleFieldDelimiter: ';',
            });
            
            var model = new Person({id: {$this->person->getId()}});
            var view = new ManagePeopleEditUniversitiesView({model: model.universities, person: model, el: $('#editUniversities')});
            $('#addUniversity').click(function(){
                view.addUniversity();
            });
            $('form').on('submit', function(e){
                if(this.submitted == 'Cancel'){
                    return true;
                }
                if($('button[value=\"Save {$this->name}\"]').is(':visible')){
                    var requests = view.saveAll();
                    e.preventDefault();
                    $('button[value=\"Save {$this->name}\"]').prop('disabled', true);
                    $.when.apply($, requests).then(function(){
                        $('form').off('submit');
                        $('button[value=\"Save {$this->name}\"]').prop('disabled', false);
                        _.delay(function(){
                            $('button[value=\"Save {$this->name}\"]').click();
                        }, 10);
                    });
                }
            });
        </script>";
    }
    
}
?>
