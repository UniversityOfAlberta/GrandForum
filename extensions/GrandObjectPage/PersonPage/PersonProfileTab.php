<?php

$wgHooks['UnknownAction'][] = 'PersonProfileTab::getPersonCloudData';

class PersonProfileTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonProfileTab($person, $visibility){
        parent::AbstractEditableTab("Bio");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->tooltip = "Contains basic information about the faculty member, including a short bio, contact information, and output visualizations.";
    }

    function generateBody(){
        global $wgUser, $config;
        $me = Person::newFromWgUser();
        $this->person->getLastRole();
        $this->html .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:1px;'>";
        $this->html .= "</td><td id='firstLeft' width='60%' valign='top'>";
        $this->showContact($this->person, $this->visibility);
        $keywords = $this->person->getKeywords(", ");
        if($this->person->getProfile() != "" || $keywords != ""){
            $this->html .= "<h2 style='margin-top:0;padding-top:0;'>Profile</h2>";
            if($config->getValue('networkName') == "Faculty of Engineering"){
                $pengStatus = FECReflections::getBlobValue("RP_FEC", "PENG_STATUS", "PENG_STATUS", 0, 0, $this->person->getId());
                $pengStatusOther = ($pengStatus == "P.Eng.") ? " (".FECReflections::getBlobValue("RP_FEC", "PENG_STATUS", "PENG_STATUS_OTHER", 0, 0, $this->person->getId()).")" : "";
                $this->html .= ($keywords != "") ? "<b>P.Eng. Status:</b> {$pengStatus}{$pengStatusOther}<br />" : "";
            
                $area = FECReflections::getBlobValue("RP_FEC", "RESEARCH_AREA", "RESEARCH_AREA", 0, 0, $this->person->getId());
                $areaOther = (strstr($area, "Other") !== false) ? " (".FECReflections::getBlobValue("RP_FEC", "RESEARCH_AREA", "RESEARCH_AREA_OTHER", 0, 0, $this->person->getId()).")" : "";
                $this->html .= ($keywords != "") ? "<b>Research Area:</b> {$area}{$areaOther}<br />" : "";
            }
            $this->html .= ($keywords != "") ? "<b>Keywords:</b> {$keywords}" : "";
            $this->showProfile($this->person, $this->visibility);
        }
        $this->showTopProducts($this->person, $this->visibility, 5);
        $extra = array();
        if($this->visibility['isMe'] || $me->isSubRole('ViewProfile')){
            if($this->person->isRole(NI) || 
               $this->person->isRole(HQP) || 
               $this->person->isRole(EXTERNAL)){
                // Only show the word cloud for 'researchers'
                $extra[] = $this->showCloud($this->person, $this->visibility);
            }
            $extra[] = $this->showDoughnut($this->person, $this->visibility);
            $extra[] = $this->showTwitter($this->person, $this->visibility);
        }
        
        
        // Delete extra widgets which have no content
        foreach($extra as $key => $e){
            if($e == ""){
                unset($extra[$key]);
            }
        }
        $this->html .= "</td><td id='firstRight' valign='top' width='40%' style='padding-top:15px;padding-left:15px;'>".implode("<hr />", $extra)."</td></tr>";
        $this->html .= "</table>";
        $this->showCCV($this->person, $this->visibility);
        return $this->html;
    }
    
    function generateEditBody(){
        $this->html .= "<table>";
        $this->showEditPhoto($this->person, $this->visibility);
        $this->html .= "</td><td style='padding-right:25px;' valign='top'>";
        $this->showEditContact($this->person, $this->visibility);
        $this->html .= "</table>";
        if($this->person instanceof FullPerson){
            $this->html .= "<h2>Profile</h2>";
            $this->showEditProfile($this->person, $this->visibility);
            $this->showEditTopProducts($this->person, $this->visibility, 5);
        }
        
        $this->html .= "<script type='text/javascript'>
            $(document).ready(function(){
                $('select.chosen:visible').chosen();
                $('select.chosen').each(function(i, el){
                    var prevVal = $(el).val();
                    if(prevVal != ''){
                        $('option[value=' + prevVal + ']', $('select.chosen').not(el)).prop('disabled', true);
                    }
                    $('select.chosen').trigger('chosen:updated');
                    $(el).change(function(e, p){
                        var id = $(this).val();
                        if(prevVal != ''){
                            $('option[value=' + prevVal + ']', $('select.chosen').not(this)).prop('disabled', false);
                        }
                        if(id != ''){
                            $('option[value=' + id + ']', $('select.chosen').not(this)).prop('disabled', true);
                        }
                        $('select.chosen').trigger('chosen:updated');
                        prevVal = id;
                    });
                });
            });
        </script>";
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return $me->isAllowedToEdit($this->person);
    }
    
    function handleEdit(){
        $this->handleContactEdit();
        
        if(isset($_POST['role_title'])){
            foreach($this->person->getRoles() as $role){
                if(isset($_POST['role_title'][$role->getId()])){
                    $value = $_POST['role_title'][$role->getId()];
                    DBFunctions::update('grand_roles', 
                                        array('title' => $value),
                                        array('id' => $role->getId()));
                }
            }
        }
        
        if(isset($_POST['top_products']) && is_array($_POST['top_products'])){
            DBFunctions::delete('grand_top_products',
                                array('obj_id' => EQ($this->person->getId())));
            foreach($_POST['top_products'] as $product){
                if($product != ""){
                    $exploded = explode("_", $product);
                    $type = $exploded[0];
                    $productId = $exploded[1];
                    DBFunctions::insert('grand_top_products',
                                        array('obj_id' => $this->person->getId(),
                                              'product_id' => $productId));
                }
            }
        }
        
        Person::$rolesCache = array();
        Person::$cache = array();
        
        $this->person = Person::newFromId($this->person->getId());
        DBFunctions::commit();
        redirect($this->person->getUrl());
    }
    
    function handleContactEdit(){
        global $wgImpersonating;
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
            $_POST['email'] = @$_POST['email'];
            
            $api = new UserPhoneAPI();
            $api->doAction(true);

            $this->person->firstName = @$_POST['first_name'];
            $this->person->middleName = @$_POST['middle_name'];
            $this->person->lastName = @$_POST['last_name'];
            $this->person->realname = @"{$_POST['first_name']} {$_POST['last_name']}";
            $this->person->employeeId = @$_POST['employeeId'];
            $this->person->twitter = @$_POST['twitter'];
            $this->person->website = @$_POST['website'];
            $this->person->googleScholar = @$_POST['googleScholarUrl'];
            $this->person->sciverseId = @$_POST['sciverseId'];
            $this->person->orcId = @$_POST['orcId'];
            $this->person->wos = @$_POST['wos'];
            $this->person->publicProfile = @$_POST['public_profile'];
            $this->person->privateProfile = @$_POST['private_profile'];
            $this->person->update();
            $this->person->setKeywords(explode(",", $_POST['keywords']));
            $this->person->setAliases(explode(";", $_POST['aliases']));
            
            $api = new UserEmailAPI();
            $api->doAction(true);
            
            @FECReflections::saveBlobValue(implode(", ", $_POST['research_area']), "RP_FEC", "RESEARCH_AREA", "RESEARCH_AREA", 0, 0, $this->person->getId());
            @FECReflections::saveBlobValue($_POST['research_area_other'], "RP_FEC", "RESEARCH_AREA", "RESEARCH_AREA_OTHER", 0, 0, $this->person->getId());
            
            @FECReflections::saveBlobValue($_POST['peng_status'], "RP_FEC", "PENG_STATUS", "PENG_STATUS", 0, 0, $this->person->getId());
            @FECReflections::saveBlobValue($_POST['peng_status_other'], "RP_FEC", "PENG_STATUS", "PENG_STATUS_OTHER", 0, 0, $this->person->getId());
        }
        
        //Reset the cache to use the changed data
        unset(Person::$cache[$this->person->id]);
        unset(Person::$cache[$this->person->getName()]);
        $this->person = Person::newFromId($this->person->id);
        return $error;
    }
    
    /**
     * Displays the profile for this user
     */
    function showProfile($person, $visibility){
        global $wgUser;
        $this->html .= "<p style='text-align:justify;'>".$person->getProfile(false)."</p>";
    }
    
    /**
     * Displays the twitter widget for this user
     */
    function showTwitter($person, $visibility){
        $html = "";
        if($person->getTwitter() != ""){
            $twitter = str_replace("@", "", $person->getTwitter());
            $html = <<<EOF
                <br />
                <div id='twitter' style='display: block; width: 100%; text-align: right; overflow: hidden; position:relative;'>
                    <div>
                        <a class="twitter-timeline" width="100%" height="400" href="https://twitter.com/{$twitter}" data-screen-name="{$twitter}" data-widget-id="553303321864196097">Tweets by @{$twitter}</a>
                        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
                    </div>
                </div>
EOF;
        }
        return $html;
    }
    
    function showEditProfile($person, $visibility){
        global $config;
        if($config->getValue('networkName') == "Faculty of Engineering"){
            $pengStatus = FECReflections::getBlobValue("RP_FEC", "PENG_STATUS", "PENG_STATUS", 0, 0, $person->getId());
            $pengStatusOther = str_replace("'", "&#39;", FECReflections::getBlobValue("RP_FEC", "PENG_STATUS", "PENG_STATUS_OTHER", 0, 0, $person->getId()));
            $pengStatusField = new VerticalRadioBox("peng_status", "P.Eng. Status", $pengStatus, array("P.Eng. (APEGA)",
                                                                                                       "P.Eng.",
                                                                                                       "E.I.T",
                                                                                                       "Examinee",
                                                                                                       "Applicant in Process",
                                                                                                       "Have Not Applied"));
            
            $area = explode(", ", FECReflections::getBlobValue("RP_FEC", "RESEARCH_AREA", "RESEARCH_AREA", 0, 0, $person->getId()));
            $areaOther = str_replace("'", "&#39;", FECReflections::getBlobValue("RP_FEC", "RESEARCH_AREA", "RESEARCH_AREA_OTHER", 0, 0, $person->getId()));
            $areaField = new VerticalCheckBox("research_area", "Research Area", $area, array("Energy and Environment",
                                                                                             "Nanotechnology",
                                                                                             "ICT",
                                                                                             "Biomedical Engineering",
                                                                                             "Other"));
            $this->html .= "
                    
                    <style>
                        div#area input[type=text], 
                        div#peng input[type=text] {
                            vertical-align: -1px;
                        }
                    </style>
                    
                    <h3>P.Eng. Status:</h3>
                    <div id='peng' style='position:relative;'>
                        <div style='line-height: 2em;'>
                            {$pengStatusField->render()}
                        </div>
                        <div style='position:absolute; top:calc(1*(2em) - 3px); left: 53px; white-space:nowrap;'>
                            (<input type='text' placeholder='specify' name='peng_status_other' value='{$pengStatusOther}' />)
                        </div>
                    </div>
                    
                    <h3>Research Area:</h3>
                    <div id='area' style='position:relative;'>
                        <div style='line-height: 2em;'>
                            {$areaField->render()}
                        </div>
                        <div style='position:absolute; top:calc(4*(2em) - 3px); left: 55px; white-space:nowrap;'>
                            <input type='text' placeholder='specify' name='research_area_other' value='{$areaOther}' />
                        </div>
                    </div>";
        }
        $this->html .= "
                <h3>Keywords:</h3>
                <input class='keywords' type='text' name='keywords' value='' />";
        $this->html .= "<h3>Bio/Research Interests:</h3>
                        <textarea style='height:300px;' name='public_profile'>{$person->getProfile(false)}</textarea>
                        <textarea style='display: none; width:600px; height:150px;' name='private_profile'>{$person->getProfile(true)}</textarea>";
                        
        $this->html .= "<script type='text/javascript'>
            $('input.keywords').val('".addslashes($person->getKeywords(","))."');
            $('input.keywords').tagit({
                allowSpaces: true
            });
            $('textarea[name=public_profile]').tinymce({
                theme: 'modern',
                menubar: false,
                plugins: 'link image charmap lists table paste wordcount advlist',
                toolbar: [
                    'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | subscript superscript | alignleft aligncenter alignright alignjustify'
                ],
                paste_data_images: true,
                invalid_elements: 'h1, h2, h3, h4, h5, h6, h7, font',
                imagemanager_insert_template : '<img src=\"{\$url}\" width=\"{\$custom.width}\" height=\"{\$custom.height}\" />',
                paste_postprocess: function(plugin, args) {
                    var imgs = $('img', args.node);
                    imgs.each(function(i, el){
                        $(el).removeAttr('style');
                        $(el).attr('width', el.naturalWidth);
                        $(el).attr('height', el.naturalHeight);
                        $(el).css('width', el.naturalWidth);
                        $(el).css('height', el.naturalHeight);
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
        $wordle->height = 229;
        $wgOut->addScript("<script type='text/javascript'>
                                $(document).ready(function(){
                                    onLoad{$wordle->index}();
                                });
                          </script>");
        return $wordle->show();
    }
    
    static function getPersonCloudData($action, $article){
        global $wgServer, $wgScriptPath;
        if($action == "getPersonCloudData"){
            $text = "";
            $person = Person::newFromId($_GET['person']);
            $text .= $person->getProfile()."\n";
            
            $products = $person->getPapers("Publication", false, 'both', true, 'Public');
            $grants = $person->getGrants();
            foreach($products as $product){
                $text .= $product->getTitle()."\n";
                $text .= $product->getDescription()."\n";
            }
            foreach($grants as $grant){
                //$text .= $grant->getTitle()."\n";
                $text .= $grant->getDescription()."\n";
            }
            CommonWords::$commonWords[] = strtolower($person->getFirstName());
            CommonWords::$commonWords[] = strtolower($person->getLastName());
            $data = Wordle::createDataFromText($text);
            $data = array_slice($data, 0, 75);
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
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
        $html .= $chord->show();
        $html .= "</div>";
        $wgOut->addScript("<script type='text/javascript'>
                                $(document).ready(function(){
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
                                        if($('#vis{$chord->index}').is(':visible') && width != newWidth){
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
                                            $('#contact').height(Math.max(height, $('#contact > #card').height()));
                                        }
                                    }, 100);
                                });
                          </script>");
        return $html;
    }
 
    /**
     * Displays the profile for this user
     */
    function showCCV($person, $visibility){
        global $wgUser, $wgServer, $wgScriptPath;
        if(isExtensionEnabled('CCVExport')){
            $me = Person::newFromWgUser();
            if(($person->isRole(NI)) && $me->getId() == $person->getId()){
                //$this->html .= "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:CCVExport'>Download CCV</a>";
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
        $this->html .= "<tr><td style='padding-right:25px;' valign='top' colspan='2'>";
        $this->html .= "<img src='{$person->getPhoto()}' alt='{$person->getName()}' style='max-width:100px;max-height:132px;' />";
        $this->html .= "<div id='special_links'></div>";
        $this->html .= "</td></tr>";
        $this->html .= "<tr><td style='padding-right:25px;' valign='top'><table>
                            <tr>
                                <td align='right'><b>Upload new Photo:</b></td>
                                <td><input type='file' name='photo' /></td>
                            </tr>
                            <tr>
                                <td></td><td><small><li>For best results, the image should be 300x396</li>
                                                    <li>Max file size is 5MB</li>
                                                    <li>File type must be <i>gif</i>, <i>png</i> or <i>jpeg</i></li></small></td>
                            </tr>";
        if($person instanceof FullPerson){
            $this->html .= "<tr>
                                <td align='right'><b>Website URL:</b></td>
                                <td><input type='text' size='30' name='website' value='".str_replace("'", "&#39;", $person->getWebsite())."' /></td>
                            </tr>";
            if($config->getValue('singleUniversity')){
                    $this->html .= "<tr>
                                    <td align='right'><b>Google Scholar URL:</b></td>
                                    <td><input type='text' size='30' name='googleScholarUrl' placeholder='https://scholar.google.ca/citations?user=XXXXXXXXX' value='".str_replace("'", "&#39;", $person->getGoogleScholar())."' /></td>
                                </tr>";
                    $this->html .= "<tr>
                                    <td align='right'><b>Sciverse Id:</b></td>
                                    <td><input type='text' size='30' name='sciverseId' placeholder='0000000000' value='".str_replace("'", "&#39;", $person->getSciverseId())."' /></td>
                                </tr>";
                    $this->html .= "<tr>
                                    <td align='right'><b>ORCID:</b></td>
                                    <td><input type='text' size='30' name='orcId' placeholder='0000-0000-0000-0000' value='".str_replace("'", "&#39;", $person->getOrcId())."' /></td>
                                </tr>";
                    $this->html .= "<tr>
                                    <td align='right'><b>ResearcherID:</b></td>
                                    <td><input type='text' size='30' name='wos' placeholder='H-0000-0000' value='".str_replace("'", "&#39;", $person->getWOS())."' /></td>
                                </tr>";
            }
            $this->html .=  "<tr>
                                    <td align='right'><b>Twitter Account:</b></td>
                                    <td><input type='text' name='twitter' placeholder='@twitter' value='".str_replace("'", "&#39;", $person->getTwitter())."' /></td>
                                </tr>
                                <tr>
                                    <td align='right'><b>Phone Number:</b></td>
                                    <td><input type='text' name='phone' value='".str_replace("'", "&#39;", $person->getPhoneNumber())."' /></td>
                                </tr>";
        }
        $this->html .= "</table></td>";
    }
    
   /**
    * Displays the contact information for this person
    */
    function showContact($person, $visibility){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $this->html .= "<div id='contact' style='white-space: nowrap;position:relative;height:226px;min-height:150px'>";
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
        if($this->visibility['isMe'] || $me->isSubRole('ViewProfile')){
            $this->html .= $this->showChord($person, $visibility);
        }
        $this->html .= "</div>";
    }
    
    function showEditContact($person, $visibility){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        $university = $person->getUniversity();
        $me = Person::newFromWgUser();
        $this->html .= "<table>
                            <tr>
                                <td align='right'><b>First Name:</b></td>
                                <td><input type='text' name='first_name' value='".str_replace("'", "&#39;", $person->getFirstName())."'></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Middle Name:</b></td>
                                <td><input type='text' name='middle_name' value='".str_replace("'", "&#39;", $person->getMiddleName())."'></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Last Name:</b></td>
                                <td><input type='text' name='last_name' value='".str_replace("'", "&#39;", $person->getLastName())."'></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Employee Id:</b></td>
                                <td><input size='10' type='text' name='employeeId' value='".str_replace("'", "&#39;", $person->getEmployeeId())."'></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Email:</b></td>
                                <td><input size='30' type='text' name='email' value='".str_replace("'", "&#39;", $person->getEmail())."' /></td>
                            </tr>
                            <tr>
                                <td align='right' style='padding-top: 0.5em;'><b>Aliases:</b><br /><small style='line-height: 1.5em; display: inline-block;'>Can be used for alternate names<br />to help match ".strtolower($config->getValue('productsTerm'))." authors.<br />Should be in the format <i>First Last</i></small></td>
                                <td valign='top' style='max-width: 0;'><input type='text' name='aliases' value='".str_replace("'", "&#39;", implode(";", $person->getAliases()))."' /></td>
                            </tr>
                        </table>";
        
        $this->html .= "<script type='text/javascript'>
            $('input[name=employeeId]').forceNumeric({min: 0, max: 100000000000,includeCommas: false, decimals: 0});
            
            $('input[name=aliases]').tagit({
                allowSpaces: true,
                removeConfirmation: false,
                singleField: true,
                singleFieldDelimiter: ';',
            });
        </script>";
        
        $this->html .= "</td></tr>";
    }
    
    private function optGroup($products, $category, $value){
        $html = "";
        $plural = Inflect::pluralize($category);
        $html .= "<optgroup label='$plural'>";
        $count = 0;
        foreach($products as $product){
            if($category == $product->getCategory()){
                $selected = ($value == $product->getId()) ? "selected='selected'" : "";
                $year = substr($product->getDate(), 0, 4);
                $html .= "<option value='PRODUCT_{$product->getId()}' $selected>($year) {$product->getType()}: {$product->getTitle()}</option>";
                $count++;
            }
        }
        $html .= "</optgroup>";
        if($count > 0){
            return $html;
        }
        return "";
    }
    
    private function selectList($person, $value){
        $productStructure = Product::structure();
        $categories = @array_keys($productStructure['categories']);
        $allProducts = $person->getPapers('all', true, 'grand', true, 'Public', true, true);
        $products = array();
        foreach($allProducts as $product){
            $date = $product->getDate();
            $products[$date."_{$product->getId()}"] = $product;
        }
        ksort($products);
        $products = array_reverse($products);
        $html = "<select class='chosen' name='top_products[]' style='max-width:800px;'>";
        $html .= "<option value=''>---</option>";
        foreach($categories as $category){
            $html .= $this->optGroup($products, $category, $value);
        }
        $html .= "</select><br />";
        return $html;
    }
    
    function showEditTopProducts($person, $visibility, $max=5){
        global $config;
        $this->html .= "<h2>Select Research Outcomes</h2>";
        $this->html .= "<div style='margin-bottom: 0.5em; font-size: smaller;'>Select up to {$max} research outputs that you believe best showcase your research, to include in your profile. Your chosen outputs will be sorted according to their dates (most recent first).  You can choose publications, awards, start-ups, or presentations.</div>";
        $products = $person->getTopProducts();
        $i = 0;
        foreach($products as $product){
            if($i == $max){
                break;
            }
            $this->html .= $this->selectList($person, $product->getId());
            $i++;
        }
        for($i; $i < $max; $i++){
            $this->html .= $this->selectList($person, "");
        }
    }
    
    function showTopProducts($person, $visibility, $max=5){
        global $config, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(!$visibility['isMe'] || $me->isSubRole('ViewProfile')){
            return;
        }
        $products = $person->getTopProducts();
        $this->html .= "<h2>Select Research Outcomes</h2>";
        $date = date('M j, Y', strtotime($person->getTopProductsLastUpdated()));
        if(count($products) > 0){
            $this->html .= "<table class='dashboard' cellspacing='1' cellpadding='3' rules='all' frame='box' style='max-width: 800px;'>
                                <tr>
                                    <td align='center'><b>Year</b></td>
                                    <td align='center'><b>Category</b></td>
                                    <td align='center'><b>".$config->getValue('productsTerm')."</b></td>
                                </th>";
            $i = 0;
            foreach($products as $product){
                if($i == $max){
                    break;
                }
                $year = substr($product->getDate(), 0, 4);
                $category = $product->getCategory();
                $citation = $product->getCitation();
                if($year == "0000"){
                    $year = "";
                }
                $this->html .= "<tr>
                                    <td align='center'>{$year}</td>
                                    <td>{$category}</td>
                                    <td>{$citation}</td>
                                </tr>";
                $i++;
            }
            $this->html .= "</table><i>Last updated on: $date</i><br />";
        }
        else{
            $this->html .= "You have not entered any <i>Select Research Outcomes</i> yet<br />";
        }
        
        // Profile PDF Buttons
        if($this->person->isMe()){
            $profilePDF = new DummyReport("Profile", $this->person, null, YEAR);
            $check = $profilePDF->getPDF();
            $tok = (count($check) > 0) ? $check[0]['token'] : false;
        	$style1 = ($tok === false) ? "display:none;" : "";
            	
            $this->html .= "<br />
                            <button id='generateProfileButton' type='button' style='width:16em;'>&nbsp;Generate Profile PDF&nbsp;<span id='profileThrobber' style='display:none;position:absolute;margin-top:-2px;' class='throbber'></span></button>
                            <a id='downloadProfilePDF' style='width:12em;{$style1}' class='button' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf={$tok}'>Download Profile PDF</a>
                            <div style='display:none;' class='error' id='generate_error'></div>
                            <div style='display:none;' class='success' id='generate_success'></div>";
            $this->html .= "<script type='text/javascript'>
                $('#generateProfileButton').click(function(){
                    $('#generateProfileButton').prop('disabled', true);
                    $('#generate_success').html('');
                    $('#generate_success').css('display', 'none');
                    $('#generate_error').html('');
                    $('#generate_error').css('display', 'none');
                    $('#profileThrobber').css('display', 'inline-block');
                    $.ajax({
                        url : '$wgServer$wgScriptPath/index.php/Special:Report?report=MyProfile&generatePDF', 
                        success : function(data){
                            for(index in data){
                                var val = data[index];
                                if(typeof val.tok != 'undefined'){
                                    var tok = val.tok;

                                    $('#generate_button').attr('value', tok);
                                    $('#downloadProfilePDF').show();
                                    
                                    $('#generate_success').html('PDF Generated Successfully.');
                                    $('#generate_success').css('display', 'block');
                                    $('#downloadProfilePDF').attr('href', '{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf=' + tok);
                                }
                                else{
                                    $('#generate_error').html('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"{$config->getValue('supportEmail')}\">{$config->getValue('supportEmail')}</a>');
                                    $('#generate_error').css('display', 'block');
                                }
                            }
                            $('#profileThrobber').css('display', 'none');
                            $('#generateProfileButton').removeAttr('disabled');
                        },
                        error : function(response){
                            // Error
                            $('#generate_error').html('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"mailto:{$config->getValue('supportEmail')}\">{$config->getValue('supportEmail')}</a>');
                            $('#generate_error').css('display', 'block');
                            $('#generateProfileButton').removeAttr('disabled');
                            $('#profileThrobber').css('display', 'none');
                        }
                    });
                });
            </script>";
        }
    }
    
}
?>
