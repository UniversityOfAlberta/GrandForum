<?php

class PersonProfileTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonProfileTab($person, $visibility){
        parent::AbstractEditableTab("Bio");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser;
        $this->person->getLastRole();
        $this->html .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:5px;'>";
        $this->html .= "</td><td id='firstLeft' width='60%' valign='top'>";
        $this->showContact($this->person, $this->visibility);
        if($this->person->getProfile() != ""){
            $this->html .= "<h2 style='margin-top:0;padding-top:0;'>Profile</h2>";
            $this->showProfile($this->person, $this->visibility);
        }
        $this->html .= $this->showFundedProjects($this->person, $this->visibility);
        $this->html .= $this->showTable($this->person, $this->visibility);
        $extra = array();
        if($this->person->isRole(NI) || 
           $this->person->isRole(HQP) || 
           $this->person->isRole(EXTERNAL)){
            // Only show the word cloud for 'researchers'
            $extra[] = $this->showCloud($this->person, $this->visibility);
        }
        $extra[] = $this->showDoughnut($this->person, $this->visibility);
        $extra[] = $this->showTwitter($this->person, $this->visibility);
        
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
                 $this->visibility['isSupervisor']) &&
                $me->isAllowedToEdit($this->person));
    }
    
    function handleEdit(){
        $this->handleContactEdit();
        $_POST['user_name'] = $this->person->getName();
        
        $this->person->publicProfile = $_POST['public_profile'];
        $this->person->privateProfile = $_POST['private_profile'];
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
            $this->person->lastName = @$_POST['last_name'];
            $this->person->realname = @"{$_POST['first_name']} {$_POST['last_name']}";
            $this->person->gender = @$_POST['gender'];
            $this->person->twitter = @$_POST['twitter'];
            $this->person->website = @$_POST['website'];
            $this->person->linkedin = @$_POST['linkedin'];
            $this->person->facebook = @$_POST['facebook'];
            $this->person->office = @$_POST['office'];
            $this->person->nationality = @$_POST['nationality'];
            $this->person->stakeholder = @$_POST['stakeholder'];
            $this->person->update();

            $address = $this->person->getMailingAddress();
            $address->type = 'Mailing';
            $address->line1 = @$_POST['address_line1'];
            $address->line2 = @$_POST['address_line2'];
            $address->line3 = @$_POST['address_line3'];
            $address->line4 = @$_POST['address_line4'];
            $address->city = @$_POST['address_city'];
            $address->province = @$_POST['address_province'];
            $address->country = @$_POST['address_country'];
            $address->code = @$_POST['address_code'];
            $this->person->updateMailingAddress($address);

            $api = new UserEmailAPI();
            $api->doAction(true);
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
        global $wgUser;
        $this->html .= "<div style='text-align:justify;'>".$person->getProfile($wgUser->isLoggedIn())."</div>";
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
        $this->html .= "
                           
                                <h3>Live on Website:</h3>
                                <textarea class='profile' style='width:100%; height:200px;' name='public_profile'>{$person->getProfile(false)}</textarea><br>
			  
                            
                                <h3>Live on Forum:</h3>
                                <textarea class='profile' style='width:100%; height:200px;' name='private_profile'>{$person->getProfile(true)}</textarea>
                            
                        ";
         $this->html .= "<script type='text/javascript'>
            $('textarea.profile').tinymce({
                theme: 'modern',
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
	        
	        $products = $person->getPapers("all", false, 'both', false, 'Public');
	        foreach($products as $product){
	            $text .= $product->getTitle()."\n";
	            $text .= $product->getDescription()."\n";
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
        $chord->fn = '$("#personProducts_wrapper input").val(data.labels[d.index]); $("#personProducts_wrapper input").trigger("keyup")';
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
                                });
                          </script>");
        return $html;
    }
    
    function showFundedProjects($person, $visibility){
        global $config;
        $html = "";
        $projects = $person->getProjects(true);
        if(count($projects) > 0){
            if($config->getValue('networkName') != "CS-CAN"){
                $html .= "<h2>{$config->getValue('networkName')} Funded Projects</h2><ul>";
            }
            else {
                $html .= "<h2>Department</h2><ul>";
            }
            foreach($projects as $project){
                $completed = ($project->getStatus() == "Ended") ? " (completed)" : "";
                $html .= "<li><a class='projectUrl' data-projectId='{$project->getId()}' href='{$project->getUrl()}'>{$project->getFullName()} ({$project->getName()})</a>{$completed}</li>";
            }
            $html .= "</ul>";
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
            $string = "<h2>".Inflect::pluralize($config->getValue('productsTerm'))."</h2>";
            $string .= "<table id='personProducts' rules='all' frame='box'>
                <thead>
                    <tr>
                        <th>Title</th><th>Category</th><th>Date</th><th>Authors</th>
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
                
                $string .= "<tr>";
                $string .= "<td><a href='{$paper->getUrl()}'>{$paper->getTitle()}</a><span style='display:none'>{$paper->getDescription()}".implode(", ", $projects)." ".implode(", ", $paper->getUniversities())."</span></td>";
                $string .= "<td>{$paper->getCategory()}</td>";
                $string .= "<td style='white-space: nowrap;'>{$paper->getDate()}</td>";
                $string .= "<td>".implode(", ", $names)."</td>";
                
                $string .= "</tr>";
            }
            $string .= "</tbody>
                </table>
                <script type='text/javascript'>
                    var personProducts = $('#personProducts').dataTable({
                        'order': [[ 2, 'desc' ]],
                        'autoWidth': false
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
        $address = $person->getMailingAddress();
        $this->html .= "<tr><td style='padding-right:25px;' valign='top' colspan='2'>";
        $this->html .= "<img src='{$person->getPhoto()}' alt='{$person->getName()}' style='max-width:100px;max-height:132px;' />";
        $this->html .= "<div id=\"special_links\"></div>";
        $this->html .= "</td></tr>";
        $this->html .= "<tr><td style='padding-right:25px;' valign='top'><table>";
        if($config->getValue('allowPhotoUpload') || $me->isRoleAtLeast(STAFF)){
            $this->html .= "<tr>
                                <td align='right'><b>Upload new Photo:</b></td>
                                <td><input type='file' name='photo' /></td>
                            </tr>
                            <tr>
                                <td></td><td><small><li>For best results, the image should be 300x396</li>
                                                    <li>Max file size is 5MB</li>
                                                    <li>File type must be <i>gif</i>, <i>png</i> or <i>jpeg</i></li></small></td>
                            </tr>";
        }
        $this->html .= "    <tr>
                                <td align='right'><b>Website Url:</b></td>
                                <td><input type='text' size='30' name='website' value='".str_replace("'", "&#39;", $person->getWebsite())."' /></td>
                            </tr>
                            <tr>
                                <td align='right'><b>LinkedIn Url:</b></td>
                                <td><input type='text' size='30' name='linkedin' value='".str_replace("'", "&#39;", $person->getLinkedIn())."' /></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Facebook Url:</b></td>
                                <td><input type='text' size='30' name='facebook' value='".str_replace("'", "&#39;", $person->getFacebook())."' /></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Twitter Account:</b></td>
                                <td><input type='text' name='twitter' value='".str_replace("'", "&#39;", $person->getTwitter())."' /></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Phone Number:</b></td>
                                <td><input type='text' name='phone' value='".str_replace("'", "&#39;", $person->getPhoneNumber())."' /></td>
                            </tr>
                            <tr>
                                <td align='right' valign='top'>
                                    <b>Mailing Address:</b>
                                </td>
                                <td align='right'>
                                    <small>
                                        <b>Line 1:</b><input type='text' size='28' name='address_line1' value='".str_replace("'", "&#39;", $address->getLine1())."' /><br />
                                        <b>Line 2:</b><input type='text' size='28' name='address_line2' value='".str_replace("'", "&#39;", $address->getLine2())."' /><br />
                                        <b>Line 3:</b><input type='text' size='28' name='address_line3' value='".str_replace("'", "&#39;", $address->getLine3())."' /><br />
                                        <b>Line 4:</b><input type='text' size='28' name='address_line4' value='".str_replace("'", "&#39;", $address->getLine4())."' /><br />
                                        <b>Postal Code:</b><input type='text' size='28' name='address_code' value='".str_replace("'", "&#39;", $address->getPostalCode())."' /><br />
                                        <b>City:</b><input type='text' size='28' name='address_city' value='".str_replace("'", "&#39;", $address->getCity())."' /><br />
                                        <b>Province:</b><input type='text' size='28' name='address_province' value='".str_replace("'", "&#39;", $address->getProvince())."' /><br />
                                        <b>Country:</b><input type='text' size='28' name='address_country' value='".str_replace("'", "&#39;", $address->getCountry())."' />
                                    </small>
                                </td>
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
        $this->html .= $this->showChord($person, $visibility);
        $this->html .= "</div>";
    }
    
    function showEditContact($person, $visibility){
        global $wgOut, $wgUser, $config, $wgServer, $wgScriptPath;
        $university = $person->getUniversity();
        $nationality = "";
        $me = Person::newFromWgUser();
        if($visibility['isMe'] || $visibility['isSupervisor']){
            $canSelected = ($person->getNationality() == "Canadian") ? "selected='selected'" : "";
            $amerSelected = ($person->getNationality() == "American") ? "selected='selected'" : "";
            $immSelected = ($person->getNationality() == "Landed Immigrant" || $person->getNationality() == "Foreign") ? "selected='selected'" : "";
            $visaSelected = ($person->getNationality() == "Visa Holder") ? "selected='selected'" : "";
            $interSelected = ($person->getNationality() == "International") ? "selected='selected'" : "";
            $nationality = "<tr>
                <td align='right'><b>Nationality:</b></td>
                <td>
                    <select name='nationality'>
                        <option value=''>---</option>
                        <option value='Canadian' $canSelected>Canadian</option>
                        <option value='American' $amerSelected>American</option>
                        <option value='Landed Immigrant' $immSelected>Landed Immigrant</option>
                        <option value='Visa Holder' $visaSelected>Visa Holder</option>
                        <option value='International' $interSelected>International</option>
                    </select>
                </td>
            </tr>";
            $gender = "";
            if($person->isMe() || $me->isRoleAtLeast(STAFF)){
                $blankSelected = ($person->getGender() == "") ? "selected='selected'" : "";
                $maleSelected = ($person->getGender() == "Male") ? "selected='selected'" : "";
                $femaleSelected = ($person->getGender() == "Female") ? "selected='selected'" : "";
                $genderFluidSelected = ($person->getGender() == "Gender-fluid") ? "selected='selected'" : "";
                $nonBinarySelected = ($person->getGender() == "Non-binary") ? "selected='selected'" : "";
                $twoSpiritSelected = ($person->getGender() == "Two-spirit") ? "selected='selected'" : "";
                $declinedSelected = ($person->getGender() == "Not disclosed") ? "selected='selected'" : "";
                $gender = "<tr>
                    <td align='right'><b>Gender:</b></td>
                    <td>
                        <select name='gender'>
                            <option value='' $blankSelected>---</option>
                            <option value='Male' $maleSelected>Male</option>
                            <option value='Female' $femaleSelected>Female</option>
                            <option value='Gender-fluid' $genderFluidSelected>Gender-fluid</option>
                            <option value='Non-binary' $nonBinarySelected>Non-binary</option>
                            <option value='Two-spirit' $twoSpiritSelected>Two-spirit</option>
                            <option value='Not disclosed' $declinedSelected>I prefer not to answer</option>
                        </select>
                    </td>
                </tr>";
            }
            
            $stakeholderCategories = $config->getValue('stakeholderCategories');
            $stakeholder = "";
            if(count($stakeholderCategories) > 0){
                $blankSelected = (!$person->isStakeholder()) ? "selected='selected'" : "";
                $stakeholder = "<tr>
                    <td align='right'><b>Stakeholder<br />Category:</b></td>
                    <td>
                        <select name='stakeholder'>
                            <option value='' $blankSelected>---</option>";
                foreach($stakeholderCategories as $category){
                    $selected = ($person->getStakeholder() == $category) ? "selected='selected'" : "";
                    $stakeholder .= "<option value='$category' $selected>$category</option>";
                }
                $stakeholder .= "</select>
                    </td>
                </tr>";
            }
        }
        
        $this->html .= "<table>
                            <tr>
                                <td align='right'><b>First Name:</b></td>
                                <td><input type='text' name='first_name' value='".str_replace("'", "&#39;", $person->getFirstName())."'></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Last Name:</b></td>
                                <td><input type='text' name='last_name' value='".str_replace("'", "&#39;", $person->getLastName())."'></td>
                            </tr>
                            <tr>
                                <td align='right'><b>Email:</b></td>
                                <td><input size='30' type='text' name='email' value='".str_replace("'", "&#39;", $person->getEmail())."' /></td>
                            </tr>
                            {$nationality}
                            {$gender}
                            {$stakeholder}";
        
        $roles = $person->getRoles();

        if($me->isRoleAtLeast(STAFF)){
            $this->html .= "<tr>
                                <td><b>Role Titles:</b></td>
                                <td><table>";
            $titles = array("", "Chair", "Vice-Chair", "Member", "Non-Voting");
            foreach($roles as $role){
                if($role->getId() > 0){
                    $roleTitleCombo = new ComboBox("role_title[{$role->getId()}]", "Title", $role->getTitle(), $titles);
                    $this->html .= "<tr>
                                        <td align='right'><b>{$role->getRole()}:</b></td>
                                        <td>{$roleTitleCombo->render()}</td>
                                    </tr>";
                }
            }
            $this->html .= "</table></td></tr>";
        }
        $this->html .= "</table>";
        
        // Load the scripts for Manage People so that the University editing can be used
        /*$managePeople = new ManagePeople();
        $managePeople->loadTemplates();
        $managePeople->loadModels();
        $managePeople->loadHelpers();
        $managePeople->loadViews();
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/ManagePeople/style.css' type='text/css' rel='stylesheet' />");
        $this->html .= "</td></tr><tr><td colspan='2'><div id='editUniversities' style='border: 1px solid #AAAAAA;'></div><input type='button' id='addUniversity' value='Add Institution' />
        <script type='text/javascript'>
            var model = new Person({id: {$this->person->getId()}});
            var view = new ManagePeopleEditUniversitiesView({model: model.universities, person: model, el: $('#editUniversities')});
            $('#addUniversity').click(function(){
                view.addUniversity();
            });
            $('form').on('submit', function(e){
                if($('input[value=\"Save {$this->name}\"]').is(':visible')){
                    var requests = view.saveAll();
                    e.preventDefault();
                    $('input[value=\"Save {$this->name}\"]').prop('disabled', true);
                    $.when.apply($, requests).then(function(){
                        $('form').off('submit');
                        $('input[value=\"Save {$this->name}\"]').prop('disabled', false);
                        _.delay(function(){
                            $('input[value=\"Save {$this->name}\"]').click();
                        }, 10);
                    });
                }
            });
        </script>";*/
    }
    
}
?>
