<?php

$wgHooks['ArticleViewHeader'][] = 'ContributionPage::processPage';

class ContributionPage {

    function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $types, $wgTitle, $wgMessage;
        $me = Person::newFromId($wgUser->getId());
        if(!$wgOut->isDisabled()){
            $name = ($article != null) ? $article->getTitle()->getNsText() : "";
            if(isset($_GET['name'])){
                $title = @$_GET['name'];
            }
            else{
                $title = ($article != null) ? $article->getTitle()->getText() : "";
                if($name == ""){
                    $split = explode(":", $title);
                    if(count($split) > 1){
                        $title = $split[1];
                    }
                    else{
                        $title = "";
                    }
                    $name = $split[0];
                }
            }
            if($name != "Contribution"){
                return true;
            }
            if($wgUser->isLoggedIn() && $me->isRoleAtLeast(HQP)){
                $cName = $title;
                $contribution = Contribution::newFromId($cName);
                if($contribution != null && $contribution->getId() !== null && isset($_GET['create'])){
                    unset($_GET['create']);
                    $_GET['edit'] = "true";
                }
                
                $create = isset($_GET['create']);
                $edit = (isset($_GET['edit']) || $create);
                $post = (isset($_POST['submit']) && ($_POST['submit'] == "Save $name" || $_POST['submit'] == "Create $name"));
                if(($contribution->getId() != null) || $create){
                    TabUtils::clearActions();
                    if($post){
                        if(!$create){
                            $_POST['id'] = $contribution->getId();
                        }
                        
                        $_POST['users'] = array();
                        if(isset($_POST['researchers'])){
                            $researchers = array();
                            if(is_array($_POST['researchers'])){
                                foreach(array_unique($_POST['researchers']) as $researcher){
                                     $person = Person::newFromNameLike($researcher);
                                     if($person != null && $person->getName() != null){
                                        $researchers[] = $person->getId();
                                     }
                                     else{
                                        $researchers[] = $researcher;
                                     }
                                }
                            }
                            $_POST['users'] = $researchers;
                        }
                        if(isset($_POST['projects'])){
                            $projects = array();
                            foreach(array_unique($_POST['projects']) as $project){
                                $projects[] = Project::newFromName($project)->getId();
                            }
                            $_POST['projects'] = $projects;
                        }
                        $partners = array();
                        $type = array();
                        $subtype = array();
                        $cash = array();
                        $kind = array();
                        if(isset($_POST['partners'])){
                            foreach(array_unique($_POST['partners']) as $key => $partner){
                                if($partner != "" && @$_POST["type"][$key] != 'none'){
                                    if((@$_POST["type"][$key] == 'inki' || @$_POST["type"][$key] == 'caki') && (@$_POST["subtype$key"] == 'none' || 
                                       (@$_POST["subtype$key"] == 'othe' && $_POST["other_type$key"] == ""))){
                                        continue;
                                    }
                                    $partners[$key] = array("name" => $partner, "id" => Partner::newFromName($partner)->getId());
                                    $type[$key] = (isset($_POST["type"][$key])) ? $_POST["type"][$key] : "none";
                                    $subtype[$key] = (isset($_POST["subtype$key"])) ? $_POST["subtype$key"] : "none";
                                    if($subtype[$key] == "othe" && isset($_POST["other_type$key"]) && $_POST["other_type$key"] != ""){
                                        $subtype[$key] = str_replace("'", "&#39;", $_POST["other_type$key"]);
                                    }
                                    if($type[$key] == "cash" || 
                                       $type[$key] == "caki" || 
                                       $type[$key] == "grnt" || 
                                       $type[$key] == "char" || 
                                       $type[$key] == "scho" || 
                                       $type[$key] == "fell" || 
                                       $type[$key] == "cont" || 
                                       $type[$key] == "none"){
                                        $cash[$key] = (isset($_POST["cash"][$key])) ? $_POST["cash"][$key] : 0;
                                    }
                                    if($type[$key] == "inki" || 
                                       $type[$key] == "caki"){
                                        $kind[$key] = (isset($_POST["inKind"][$key])) ? $_POST["inKind"][$key] : 0;
                                    }
                                }
                            }
                        }
                        $_POST['partners'] = $partners;
                        $_POST['type'] = $type;
                        $_POST['subtype'] = $subtype;
                        $_POST['cash'] = $cash;
                        $_POST['kind'] = $kind;
                        $_POST['title'] = str_replace("'", "&#39;", $_POST['title']);
                        APIRequest::doAction('AddContribution', true);
                        Contribution::$cache = array();
                        $contribution = Contribution::newFromName($_POST['title']);
                        if(!$create && count($wgMessage->errors) == 0){
                            redirect($contribution->getUrl());
                        }
                        else if(count($wgMessage->errors) == 0){
                            redirect($contribution->getUrl());
                        }
                    }
                    $wgOut->clearHTML();
                    if(!$create){
                        $wgOut->setPageTitle("Contribution: ".str_replace("&#39;", "'", $contribution->getName()));
                    }
                    else{
                        $wgOut->setPageTitle("Contribution: $title");
                    }
                    if($edit){
                        $other_types = Contribution::getAllOtherSubTypes();
                        
                        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/scripts/switcheroo.js'></script>");
                        $wgOut->addScript("<script type='text/javascript'>
                                $(document).ready(function(){
                                    $('form[name=contribution]').submit(function(){
                                        var title = $('form[name=contribution] input[name=title]').val();
                                        if(title == ''){
                                            clearError();
                                            addError('The Contribution must not have an empty title');
                                            $('html, body').animate({ scrollTop: 0 });
                                            return false;
                                        }
                                    });
                                });
                        
                                var other_types = ['".implode("',\n'", $other_types)."'];
                        
                                function validatePartners(id){
                                    var value = $('.partners', $('#' + id));
                                    var warning = $('.warning', $(value).parent().parent().parent());
                                    var warningText = '';
                                    if($(value).val() == ''){
                                        warningText += 'This partner is missing a name<br />';
                                    }
                                    
                                    value = $('.type', $('#' + id));
                                    if($(value).val() == 'none'){
                                        warningText += 'Missing contribution type<br />';
                                    }
                                    else if($(value).val() == 'inki' || $(value).val() == 'caki'){
                                        value = $('.subtype', $('#' + id));
                                        if($(value).val() == 'none'){
                                            warningText += 'Missing in-kind type<br />';
                                        }
                                        else if($(value).val() == 'othe'){
                                            value = $('.other_type', $('#' + id));
                                            if($(value).val() == ''){
                                                warningText += 'Missing other type<br />';
                                            }
                                        }
                                    }
                                    
                                    if(warningText != ''){
                                        warningText += 'If you continue, this partner will be discarded.';
                                        $(warning).html(warningText);
                                        $(warning).css('display', 'block');
                                    }
                                    else{
                                        $(warning).css('display', 'none');
                                    }
                                }
                        
                                function stripAlphaChars(id){
                                    var str = $('#' + id).val();
                                    var out = new String(str); 
                                    out = out.replace(/[^0-9]/g, '');
                                    $('#' + id).attr('value', out);
                                    
                                    updateTotal();
                                    validatePartners($('#' + id).parent().parent().parent().parent().attr('id'));
                                }
                                
                                function updateTotal(){
                                    var start_date = new Date($('input[name=start_date]').val());
                                    var end_date = new Date($('input[name=end_date]').val())

                                    var diff = end_date.getTime() - start_date.getTime();
                                    var years = Math.floor(diff / (1000 * 60 * 60 * 24 * 365)) + 1;

                                    var sum = 0;
                                    $.each($('.money'), function(index, val){
                                        if($(val).is(':visible') && typeof $(val).val() != 'undefined' && $(val).val() != ''){
                                            sum += parseInt($(val).val());
                                        }
                                    });
                                    $('#contributionTotal').html(sum);
                                }
                                
                                $(document).ready(function(){
                                    $.each($('select.type'), function(index, val){
                                        changeFields($(val));
                                    });
                                    $('select.type').change(function(){
                                        changeFields($(this));
                                    });
                                    $('.partners').change(function(){
                                        validatePartners($(this).parent().parent().parent().parent().attr('id'));
                                    });
                                    $('.partners').keyup(function(){
                                        validatePartners($(this).parent().parent().parent().parent().attr('id'));
                                    });
                                });
                                
                                function changeFields(el){
                                    var id = $(el).attr('id');
                                    var value = $('#' + id).val();
                                    if(value == 'cash' || value == 'grnt' || value == 'char' || value == 'scho' || value == 'fell' || value == 'cont'){
                                        $('#inkind' + id).parent().parent().css('display','none');
                                        $('#cash' + id).parent().parent().css('display','table-row');
                                        $('#cash' + id).parent().parent().children('td[align=right]').html('<b>Cash:</b>');
                                        removeSubType(el);
                                    }
                                    else if(value == 'inki'){
                                        $('#inkind' + id).parent().parent().css('display','table-row');
                                        $('#cash' + id).parent().parent().css('display','none');
                                        $('#cash' + id).parent().parent().children('td[align=right]').html('<b>Cash:</b>');
                                        appendSubType(id, el);
                                    }
                                    else if(value == 'caki'){
                                        $('#inkind' + id).parent().parent().css('display','table-row');
                                        $('#cash' + id).parent().parent().css('display','table-row');
                                        $('#cash' + id).parent().parent().children('td[align=right]').html('<b>Cash:</b>');
                                        appendSubType(id, el);
                                    }
                                    else{
                                        $('#inkind' + id).parent().parent().css('display','none');
                                        $('#cash' + id).parent().parent().css('display','table-row');
                                        $('#cash' + id).parent().parent().children('td[align=right]').html('<b>Estimated Value:</b>');
                                        removeSubType(el);
                                    }
                                    validatePartners($(el).parent().parent().parent().parent().attr('id'));
                                    updateTotal();
                                }
                                
                                function appendSubType(id, el){
                                    function addOtherField(){
                                        var that = $('.subtype', $(el).parent());
                                        if($(that).val() == 'othe'){
                                            if($('input[name=other_type' + id + ']').length == 0){
                                                $(that).parent().append('<input class=\"other_type\" name=\"other_type' + id + '\" size=\"30\" type=\"text\" />');
                                                $('input[name=other_type' + id + ']').autocomplete({
                                                    source: other_types
                                                });
                                            }
                                        }
                                        else{
                                            $('input[name=other_type' + id + ']').remove();
                                        }
                                        $('.other_type', $(el).parent()).change(function(){
                                            validatePartners($(el).parent().parent().parent().parent().attr('id'));
                                        });
                                        $('.other_type', $(el).parent()).keyup(function(){
                                            validatePartners($(el).parent().parent().parent().parent().attr('id'));
                                        });
                                    }
                                
                                    var selected = $(el).attr('rel');
                                    if($('.subtype', $(el).parent()).length == 0){
                                        $(el).parent().append('<select name=\"subtype' + id + '\" class=\"subtype\">' +
                                                                  '<option value=\"none\" selected=\"selected\">[Select In-Kind Type]</option>' +
                                                                  '<option value=\"equi\">Equipment, Software</option>' +
                                                                  '<option value=\"mate\">Materials</option>' +
                                                                  '<option value=\"logi\">Logistical Support of Field Work</option>' +
                                                                  '<option value=\"srvc\">Provision of Services</option>' +
                                                                  '<option value=\"faci\">Use of Company Facilites</option>' +
                                                                  '<option value=\"sifi\">Salaries of Scientific Staff</option>' +
                                                                  '<option value=\"mngr\">Salaries of Managerial and Administrative Staff</option>' +
                                                                  '<option value=\"trvl\">Project-related Travel</option>' +
                                                                  '<option value=\"othe\">Other</option>' +
                                                              '</select>');
                                        $('.subtype', $(el).parent()).change(addOtherField);
                                    }
                                    if($('.subtype option[value=\"' + selected + '\"]', $(el).parent()).length == 0){
                                        $('.subtype option[value=othe]', $(el).parent()).attr('selected','selected');
                                        addOtherField();
                                        $('input[name=other_type' + id + ']').attr('value', selected);
                                    }
                                    else{
                                        $('.subtype option[value=\"' + selected + '\"]', $(el).parent()).attr('selected','selected');
                                    }
                                    $('.subtype', $(el).parent()).change(function(){
                                        validatePartners($(el).parent().parent().parent().parent().attr('id'));
                                    });
                                }
                                
                                function removeSubType(el){
                                    $('.subtype', $(el).parent()).remove();
                                    $('.other_type', $(el).parent()).remove();
                                }
                                
                                var nPartners = 0;
                                function addPartner(){
                                    $('#partners').append(\"<table id='table\" + nPartners + \"'><tbody><tr><td colspan='2'><div class='warning' style='display:none;width:700px;'></div></td></tr><tr><td style='width:120px;' align='right'><b>Partner:</b></td><td><input size='50' class='partners' type='text' name='partners[\" + nPartners + \"]' value='' /></td></tr><tr><td style='width:120px;' align='right'><b>Contact:</b><br /><small>(Name, email/phone#)</small></td><td><input size='50' class='contact' type='text' name='contacts[\" + nPartners + \"]' value='' /></td></tr><tr><td style='width:120px;' align='right'><b>Sector:</b></td><td><select name='industries[\" + nPartners + \"]'><option value=''>---</option><option>Industry</option><option>Community/Not for profit</option></select></td></tr><tr><td style='width:120px;' align='right'><b>Level:</b></td><td><select name='levels[\" + nPartners + \"]'><option value=''>---</option><option>Provincial</option><option>Federal</option></select></td></tr><tr><td align='right' valign='top' style='padding-top:5px;'><b>Type:</b></td><td><select style='display:block;' id='\" + nPartners + \"' class='type' name='type[\" + nPartners + \"]'><option value='none' selected='selected'>[Select Contribution Type]</option><option value='cash'>Cash</option><option value='caki' >Cash and In-Kind</option><option value='inki' >In-Kind</option><option value='grnt'>Grant</option><option value='char'>Research Chair</option><option value='scho'>Scholarship</option><option value='fell'>Fellowship</option><option value='cont'>Contract</option></select></td></tr><tr style='display:none;'><td align='right'><b>In-Kind:</b></td><td><input class='money' id='inkind\" + nPartners + \"' size='6' type='text' onKeyUp='stripAlphaChars(this.id)' name='inKind[\" + nPartners + \"]' value='0' />$</td></tr><tr style='display:none;'><td align='right'><b>Cash:</b></td><td><input class='money' id='cash\" + nPartners + \"' size='6' type='text' onKeyUp='stripAlphaChars(this.id)' name='cash[\" + nPartners + \"]' value='0' />$</td></tr><tr><td></td><td><a name='\" + nPartners + \"' id='delete\" + nPartners + \"' class='button'>Delete Partner</a></td></tr><tr><td colspan='2'><hr /></td></tr></tbody></table>\");
                                    $('#' + nPartners).change(function(){
                                        changeFields($(this));
                                    });
                                    $('.partners', $('#table' + nPartners)).autocomplete({
                                        source: partners
                                    });
                                    $('.partners', $('#table' + nPartners)).change(function(){
                                        var id = $(this).parent().parent().parent().parent().attr('id');
                                        validatePartners(id);
                                    });
                                    $('.partners', $('#table' + nPartners)).keyup(function(){
                                        var id = $(this).parent().parent().parent().parent().attr('id');
                                        validatePartners(id);
                                    });
                                    $('#delete' + nPartners).click(function(){
                                        deletePartner($(this).attr('name'));
                                    });
                                    nPartners++;
                                }
                                
                                function deletePartner(id){
                                    $('#table' + id).remove();
                                    updateTotal();
                                }
                            </script>");
			        }
                    
                    if($edit){
                        if(isset($_POST['title'])){
                            $titleValue = $_POST['title'];
                        }
                        if($create){
                            if(!isset($_POST['title'])){
                                $titleValue = $title;
                            }
                            $wgOut->addHTML("<form name='contribution' action='$wgServer$wgScriptPath/index.php/Contribution:New?name=".urlencode($cName)."&create' method='post'>
                                            <input type='hidden' name='access_id' value='0' />
                                            <b>Title:</b> <input size='35' type='text' name='title' value='".str_replace("'", "&#39;", $titleValue)."' />");
                        }
                        else{
                            if(!isset($_POST['title'])){
                                $titleValue = $contribution->getName();
                            }
                            $wgOut->addHTML("<form name='contribution' action='{$contribution->getUrl()}?edit' method='post'>
                                                <b>Title:</b> <input size='50' type='text' name='title' value='{$titleValue}' />");
                            
                        }
                    }
                    $people = $contribution->getPeople();
                    if($edit || !$edit && count($people) > 0){
                        $wgOut->addWikiText("== People ==
                                             __NOEDITSECTION__\n");
                        $i = 1;
                        $nPeople = count($people);
                        $personNames = array();
                        if(!$create){
                            foreach($people as $person){
                                if($person instanceof Person){
                                    $personNames[] = $person->getNameForForms();
                                }
                                else{
                                    $personNames[] = $person;
                                }
                                $i++;
                            }
                        }
                        if($edit){
                            if(isset($_POST['users'])){
                                $personNames = str_replace(" ", ".", $_POST['users']);
                            }
                            $allPeople = Person::getAllPeople('all');
                            foreach($allPeople as $person){
                                if(is_array($personNames) && array_search($person->getNameForForms(), $personNames) === false &&
                                   $person->getNameForForms() != "WikiSysop" &&
                                   $person->isRoleAtLeast(HQP)){
                                    $list[] = $person->getNameForForms();
                                }
                            }
                            $wgOut->addHTML("<div class='switcheroo' name='Researcher' id='researchers'>
                                                <div class='left'><span>".implode("</span>\n<span>", $personNames)."</span></div>
                                                <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                                            </div>");
                        }
                        else{
                            $texts = array();
                            foreach($people as $person){
                                if($person instanceof Person){
                                    if($person->getRoles() != null){
                                        $texts[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>";
                                    }
                                    else{
                                        $texts[] = $person->getNameForForms();
                                    }
                                }
                                else{
                                    $texts[] = str_replace('"', "", $person);
                                }
                            }
                            $wgOut->addHTML(implode(", ", $texts));
                        }
                    }
                    $wgOut->addWikiText("== Description ==
                                         __NOEDITSECTION__\n");
                    if($edit){
                        $description = isset($_POST['description']) ? $_POST['description'] : $contribution->getDescription();
                        $wgOut->addHTML("<textarea style='height:175px; width:650px;' name='description'>$description</textarea>");
                    }
                    else{
                        $wgOut->addWikiText($contribution->getDescription());
                    }
                    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : (($contribution->getName() != "") ? substr($contribution->getStartDate(), 0, 10) : date('Y').'-04-01');
                    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : (($contribution->getName() != "") ? substr($contribution->getEndDate(), 0, 10) : date('Y').'-04-02');
                    $wgOut->addHTML("<table>");
                    if($edit){
                        $wgOut->addHTML("<tr><td align='right'><b>Start Date:</b></td><td><input type='text' name='start_date' value='{$startDate}' readonly='readonly' /></td></tr>");
                        $wgOut->addHTML("<tr><td align='right'><b>End Date:</b></td><td><input type='text' name='end_date' value='{$endDate}' readonly='readonly' /></td></tr>");
                        $wgOut->addHTML("<script type='text/javascript'>
                            $('input[name=start_date]').datepicker({
                                defaultDate: '{$startDate}',
                                changeMonth: true,
                                changeYear: true,
                                numberOfMonths: 1,
                                dateFormat: 'yy-mm-dd',
                                onClose: function(selectedDate){
                                    $('input[name=end_date]').datepicker('option', 'minDate', selectedDate);
                                }
                            }).change(updateTotal);
                            $('input[name=end_date]').datepicker({
                                defaultDate: '{$endDate}',
                                changeMonth: true,
                                changeYear: true,
                                numberOfMonths: 1,
                                dateFormat: 'yy-mm-dd',
                                onClose: function(selectedDate){
                                    $('input[name=start_date]').datepicker('option', 'maxDate', selectedDate);
                                }
                            }).change(updateTotal);
                        </script>");
                    }
                    else{
                        $wgOut->addHTML("<tr>
                                            <td align='right'><b>Start Date:</b></td>
                                            <td>".time2date($startDate)."</td>
                                         </tr>
                                         <tr>
                                            <td align='right'><b>End Date:</b></td>
                                            <td>".time2date($endDate)."</td>
                                         </tr>");
                    }
                    $wgOut->addHTML("</table>");
                    
                    
                    $wgOut->addWikiText("== Partners ==
                                         __NOEDITSECTION__\n");
                    $wgOut->addHTML("<div id='partners'>");
                    $partners = $contribution->getPartners();
                    $partnerNames = array();
                    foreach(Partner::getAllPartners() as $part){
                        $partnerNames[$part->getOrganization()] = $part->getOrganization();
                    }
                    foreach(Contribution::getAllCustomPartners() as $part){
                        if(!isset($partnerNames[$part])){
                            $partnerNames[$part] = $part;
                        }
                    }
                    
                    if($edit){
                        $wgOut->addScript("<script type='text/javascript'>
                            var partners = [\"".implode("\",\n\"", $partnerNames)."\"];
                            $(document).ready(function(){
                                $('.partners').autocomplete({
                                    source: partners
                                });
                            });
                        </script>");
                    }
                    if(!$create){
                        foreach($contribution->getPartners() as $partner){
                            $id = md5(serialize($partner));
                            if(!$edit){
                                $wgOut->addWikiText("=== {$partner->getOrganization()} ===
                                                 __NOEDITSECTION__\n");
                            }
                            $wgOut->addHTML("<table id='table$id'>");
                            $type = $contribution->getTypeFor($partner);
                            $hrType = $contribution->getHumanReadableTypeFor($partner);
                            $hrSubType = $contribution->getHumanReadableSubTypeFor($partner);
                            if(!$contribution->getUnknownFor($partner)){
                                $cash = "\$".$contribution->getCashFor($partner);
                                $kind = "\$".$contribution->getKindFor($partner);
                            }
                            if(!$edit){
                                if(!$contribution->getUnknownFor($partner)){
                                    if($partner->getContact() != ""){
                                        $wgOut->addHTML("<tr><td align='right'><b>Contact:</b></td><td>{$partner->getContact()}</td></tr>");
                                    }
                                    if($partner->getIndustry() != ""){
                                        $wgOut->addHTML("<tr><td align='right'><b>Sector:</b></td><td>{$partner->getIndustry()}</td></tr>");
                                    }
                                    if($partner->getLevel() != ""){
                                        $wgOut->addHTML("<tr><td align='right'><b>Level:</b></td><td>{$partner->getLevel()}</td></tr>");
                                    }
                                    $wgOut->addHTML("<tr><td align='right'><b>Type:</b></td><td>{$hrType}</td></tr>");
                                    if($type == "inki" || $type == "caki"){
                                        $wgOut->addHTML("<tr><td align='right'><b>Sub-Type:</b></td><td>{$hrSubType}</td></tr>");
                                    }
                                    if($type == "inki"){
                                        $wgOut->addHTML("<tr><td align='right'><b>In-Kind:</b></td><td>{$kind}</td></tr>");
                                    }
                                    else if($type == "cash" || 
                                            $type == "grnt" || 
                                            $type == "char" || 
                                            $type == "scho" || 
                                            $type == "fell" || 
                                            $type == "cont"){
                                        $wgOut->addHTML("<tr><td align='right'><b>Cash:</b></td><td>{$cash}</td></tr>");
                                    }
                                    else if($type == "caki"){
                                        $wgOut->addHTML("<tr><td align='right'><b>In-Kind:</b></td><td>{$kind}</td></tr>");
                                        $wgOut->addHTML("<tr><td align='right'><b>Cash:</b></td><td>{$cash}</td></tr>");
                                    }
                                    else{
                                        $wgOut->addHTML("<tr><td align='right'><b>Estimated Value:</b></td><td>{$cash}</td></tr>");
                                    }
                                }
                            }
                            else{
                                $wgOut->addHTML("<tr><td colspan='2'><div class='warning' style='display:none;width:700px;'></div></td></tr>");
                                $wgOut->addHTML("<tr><td style='width:120px;' align='right'><b>Partner:</b></td><td><input size='50' class='partners' type='text' name='partners[$id]' value='".str_replace("'", "&#39;", $partner->getOrganization())."' /><td></tr>");
                                $wgOut->addHTML("<tr><td style='width:120px;' align='right'><b>Contact:</b><br /><small>(Name, email/phone#)</small></td><td><input size='50' class='contact' type='text' name='contacts[$id]' value='".str_replace("'", "&#39;", $partner->getContact())."' /><td></tr>");
                                
                                $wgOut->addHTML("<tr><td style='width:120px;' align='right'><b>Sector:</b></td><td><select name='industries[$id]'>");
                                
                                if($partner->getIndustry() == ""){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='' $selected>---</option>");
                                if($partner->getIndustry() == "Industry"){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option $selected>Industry</option>");
                                if($partner->getIndustry() == "Community/Not for profit"){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option $selected>Community/Not for profit</option>");
                                
                                $wgOut->addHTML("</select><td></tr>");
                                $wgOut->addHTML("<tr><td style='width:120px;' align='right'><b>Level:</b></td><td><select name='levels[$id]'>");
                                
                                if($partner->getLevel() == ""){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='' $selected>---</option>");
                                if($partner->getLevel() == "Provincial"){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option $selected>Provincial</option>");
                                if($partner->getLevel() == "Federal"){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option $selected>Federal</option>");
                                
                                $wgOut->addHTML("</select><td></tr>");
                                
                                $type = isset($_POST['type'][$id]) ? $_POST['type'][$id] : $contribution->getTypeFor($partner);
                                $subtype = str_replace("'", "&#39;", isset($_POST['subtype'][$id]) ? $_POST['subtype'][$id] : $contribution->getSubTypeFor($partner));
                                $inkind = isset($_POST['inKind'][$id]) ? $_POST['inKind'][$id] : $contribution->getKindFor($partner);
                                $cash = isset($_POST['cash'][$id]) ? $_POST['cash'][$id] : $contribution->getCashFor($partner);
                                
                                $wgOut->addHTML("<tr><td align='right' valign='top' style='padding-top:5px;'><b>Type:</b></td><td><select rel='$subtype' style='display:block;' id='$id' class='type' name='type[$id]'>");
                                if($type == 'none'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='none' $selected>[Select Contribution Type]</option>");
                                if($type == 'cash'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='cash' $selected>Cash</option>");
                                if($type == 'caki'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='caki' $selected>Cash and In-Kind</option>");
                                if($type == 'inki'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='inki' $selected>In-Kind</option>");
                                if($type == 'grnt'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='grnt' $selected>Grant</option>");
                                if($type == 'char'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='char' $selected>Research Chair</option>");
                                if($type == 'scho'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='scho' $selected>Scholarship</option>");
                                if($type == 'fell'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='fell' $selected>Fellowship</option>");
                                if($type == 'cont'){$selected="selected='selected'";}else{$selected="";} $wgOut->addHTML("<option value='cont' $selected>Contract</option>");
                                $wgOut->addHTML("</select></td></tr>");
                                $wgOut->addHTML("<tr><td align='right'><b>In-Kind:</b></td><td><input class='money' id='inkind$id' size='6' type='text' onKeyUp='stripAlphaChars(this.id)' name='inKind[$id]' value='{$inkind}' />\$</td></tr>");
                                $wgOut->addHTML("<tr><td align='right'><b>Cash:</b></td><td><input class='money' id='cash$id' size='6' type='text' onKeyUp='stripAlphaChars(this.id)' name='cash[$id]' value='{$cash}' />\$</td></tr>");
                            }
                            if($edit){
                                $wgOut->addHTML("<tr><td></td><td><a href='javascript:deletePartner(\"$id\");' class='button'>Delete Partner</a></td></tr>");
                                $wgOut->addHTML("<tr><td colspan='2'><hr /></td></tr>");
                            }
                            $wgOut->addHTML("</table>");
                        }
                    }
                    $wgOut->addHTML("</div>");
                    if($edit){
                        $wgOut->addHTML("<a href='javascript:addPartner();' class='button'>Add Partner</a>");
                    }
                    if(!$create){
                        $total = $contribution->getTotal();
                    }
                    else{
                        $total = 0;
                    }
                    $wgOut->addHTML("<div style='background:#EEE;padding:5px;'>");
                    $wgOut->addHTML("<h3 style='padding-top:0;'>Total: $<span id='contributionTotal'>{$total}</span></h3>");
                    $wgOut->addHTML("</div>");
                    
                    if($edit || !$edit && count($contribution->getProjects()) > 0){
                        $wgOut->addWikiText("== Projects ==
                                             __NOEDITSECTION__\n");
                    }
                    $projects = $contribution->getProjects();
                    $pProjects = array();
                    if(!$create){
                        foreach($projects as $project){
                            $pProjects[] = $project->getName();
                        }
                    }
                    if($edit){
                        if(isset($_POST['title'])){
                            if(isset($_POST['projects'])){
                                foreach($_POST['projects'] as $pId){
                                    $project = Project::newFromId($pId);
                                    $pProjects[] = $project->getName();
                                }
                            }
                        }
                        $projs = Project::getAllProjects();
                        $projs[] = Project::newFromId(-1);
                        
                        $projList = new ProjectList("projects", "Projects", $pProjects, $projs);
                        $wgOut->addHTML($projList->render());
                        if(count($projs) > 0){
                            foreach($projs as $project){
	                            // Add any deleted projects so that they remain as part of this project
	                            if($project->deleted){
	                                $wgOut->addHTML("<input style='display:none;' type='checkbox' name='projects[]' value='{$project->getName()}' checked='checked' />");
	                            }
	                        }
	                    }
                    }
                    else{
                        $projectList = array();
                        foreach($projects as $project){
                            if(!$project->deleted){
                                $projectList[] = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
                            }
                        }
                        $wgOut->addHTML(implode(", ", $projectList));
                    }
                    $wgOut->addHTML("<br />");
                    if($wgUser->isLoggedIn()){
                        if($create){
                            $wgOut->addHTML("<input type='submit' name='submit' value='Create Contribution' />");
                            $wgOut->addHTML("</form>");
                        }
                        else if($edit){
                            $wgOut->addHTML("<input type='submit' name='submit' value='Save Contribution' />");
                            $wgOut->addHTML("</form>");
                        }
                        $wgOut->addHTML("<input type='button' name='edit' value='Edit Contribution' onClick='document.location=\"{$contribution->getUrl()}?edit\";' />");
                    }
                    $wgOut->output();
                    $wgOut->disable();
                }
                else if($name == "Contribution"){
                    $wgOut->clearHTML();
                    
                    $wgOut->setPageTitle("Contribution Does Not Exist");
                    $wgOut->addHTML("The contribuiton '$title' does not exist. <a href='$wgServer$wgScriptPath/index.php/Contribution:$title?create'>Click Here</a> to create the contribution.");
                    
                    $wgOut->output();
                    $wgOut->disable();
                }
            }
            else if(!$wgUser->isLoggedIn()){
                $wgOut->setPageTitle("Permission Error");
                $wgOut->addHTML("This page is not public.  <a href='$wgScriptPath/index.php?title=Special:UserLogin&returnto={$wgTitle->getNsText()}:{$wgTitle->getText()}'>Click Here</a> to login.");
                $wgOut->output();
                $wgOut->disable();
            }
            else if(!$me->isRoleAtLeast(HQP)){
                $wgOut->setPageTitle("Permission Error");
                $wgOut->addHTML("You must be at least an HQP to view this page");
                $wgOut->output();
                $wgOut->disable();
            }
        }
        return true;
    }
}
?>
