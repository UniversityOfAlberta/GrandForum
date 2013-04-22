<?php

$publicationPage = new PublicationPage();

$wgHooks['ArticleViewHeader'][] = array($publicationPage, 'processPage');


$publicationTypes = array("Proceedings Paper" => "an article written for submission to a workshop, symposium, or conference",
                          "Collections Paper" => "an article written as part of a collection in a specified subject area",
                          "Journal Paper" => "an article written for submission to a periodical in a specified subject area", 
                          "Journal Abstract" => "the summary abstract of a longer journal paper",
                          "Book" => "a published monograph of non-trivial length in a specified subject area",
                          "Edited Book" => "a compilation of chapters written by specialists",
                          "Book Chapter" => "a chapter written as part of a book",
                          "Book Review" => "critique of the content of a published book",
                          "Review Article" => "review of a publicly-available work or event",
                          "White Paper" => "an authoritative report that helps a reader understand, solve, or decide an issue",
                          "Magazine/Newspaper Article" => "appearing in a publicly-available format",
                          "PHD Thesis" => "as awarded by an accredited institution",
                          "Masters Thesis" => "as awarded by an accredited institution",
                          "Bachelors Thesis" => "as awarded by an accredited institution",
                          "Tech Report" => "internal to the author&#39s institution",
                          "Poster" => "written description in proceedings of a workshop, symposium, or conference",
                          "Manual" => "an instructional user guide",
                          "BibTex Article" => "FLAGS BIBTEX ARTICLE",
                          "BibTex Book" => "FLAGS BIBTEX BOOK",
                          "BibTex Collection" => "FLAGS BIBTEX COLLECTION",
                          "Misc" => "any item not fitting the other listed types:\nenter its type in the text box provided");

$activityTypes = array(/*"Panel" => "panel",
                       "Tutorial" => "tutorial",*/
                       "Event Organization" => "event",
                       "Misc" => "misc"); 

                       
$artifactTypes = array("Repository" => "repository",
                       "Open Software" => "open software",
                       "Patent" => "patent",
                       "Device/Machine" => "device/machine",
                       "Aesthetic Object" => "asthetic object",
                       "Misc" => "misc");


$presentationTypes = array( "Paper Session"=>"presented as part of a workshop, symposium, or conference",
                            "Panel"=>"participated in a public panel discussion",
                            "Tutorial"=>"taught a tutorial at a workshop, symposium, or conference",
                            "Keynote"=>"a keynote talk at a workshop, symposium, or conference",
                            "Distinguished Lecture"=>"presented as part of a distiguished lecture series",
                            "Departmental Lecture"=>"presented as part of a faculty lecture series",
                            "Departmental Seminar"=>"presented as visitor to a host department",
                            "Group Seminar"=>"presented as visitor to a host research group",
                            "2MM"=>"Two-Minute Madness",
                            "WIP"=>"Work in Progress",
                            "RNotes"=>"Research Notes",
                            "Misc" => "any item not fitting the other listed types:\nenter its type in the text box provided"
                            );

$pressTypes = array("University Press" => "university press",
                    "Provincial News" => "provincial news",
                    "National News" => "national news",
                    "International News" => "international news",
                    "Misc" => "misc");

$awardTypes = array("Award" => "award");

$types = array("Artifact" => $artifactTypes,
               "Publication" => $publicationTypes,
               "Activity" => $activityTypes,
               "Press" => $pressTypes,
               "Award" => $awardTypes,
               "Presentation" => $presentationTypes);

$bibtexTypes = array("BibTex Article", "BibTex Book", "BibTex Collection");

// Not currently used:
$refereedTypes = array("Proceedings Paper", "Collections Paper", "Journal Paper", "Book Chapter", "Book Review", "Review Article",
                       "White Paper", "Poster", "Misc");

$optionDefs = array("Address" => "the city, country of the publisher",
                    "Book Title" => "the title of the book",
                    "Department" => "the name of the department in which the author was enrolled",
                    "DOI" => "Digital Object Identifier",
                    "Edition" => "the name or number of the edition in which this item appears",
                    "Editor" => "the name of the editor of this item",
                    "Editors" => "the names of the editors of this publication",
                    "Event Title" => "the official name of the event",
                    "Event Location" => "the city, country where the event took place",
                    "How Published" => "the form of publication (text, video, etc.)",
                    "Host Institution" => "the university or company where presented",
                    "Host Department" => "the department where presented",
                    "Host Research Group" => "the research group to whom presented",
										"In Publication" => "the name of the publication in which the article appears",
                    "ISBN" => "International Standard Book Number",
                    "ISSN" => "International Standard Serial Number",
                    "Journal Title" => "the name of the journal in which the item appears",
                    "Magazine/Newspaper Title" => "the name of the publication in which this item appears",
                    "Number" => "the issue number of the journal in which this article appears",
                    "Organizing Body" => "the association organizing the event, e.g. ACM, GRAND, etc.",
                    "Pages" => "the item's page range in the publication",
                    "Published In" => "the name of the publication in which this item appears",
                    "Publisher" => "the name of the publishing company",
                    "Series" => "the name of the series in which this item appears",
                    "Venue" => "where this item appeared",
                    "Note" => "any additional information",
                    "University" => "the name of the university where this thesis was awarded",
                    "URL" => "link to a copy of this item\nDO NOT LINK TO ITEMS RESTRICTED BY COPYRIGHT",
                    "Volume" => "the volume number of the journal in which the article appears",
                    "" => ""
);

class PublicationPage {

    var $paper;

    function processPage($article, $outputDone, $pcache){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $types, $bibtexTypes, $wgMessage;
        
        $me = Person::newFromId($wgUser->getId());
        if(!$wgOut->isDisabled()){
            $name = $article->getTitle()->getNsText();
            $title = $article->getTitle()->getText();
            
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
            if(!($name == "Activity" || $name == "Press" || $name == "Award" || $name == "Publication" || $name == "Artifact" || $name == "Presentation")){
                return true;
            }
            
            //Switching to use the actual product_id in the URL to identify the publication a.k.a. 'Product'
            if(is_numeric($title)){
                $product_id = $title;
                $paper = Paper::newFromId($product_id);
            }
            else{
                $title = str_replace("_", " ", $title);
                $paper = Paper::newFromTitle(str_replace(":", "&#58;", $title), $name);
                $product_id = $paper->getId();
            }
            
            /*if($paper->getTitle() != null && isset($_GET['create'])){
                unset($_GET['create']);
                $_GET['edit'] = "true";
            }*/
            $this->paper = $paper;
            if($this->paper->deleted){
                $wgMessage->addInfo("This publication has been deleted, and will not show up anywhere else on the forum.");
            }
            
            $create = isset($_GET['create']);
            $create = ( $create && (!FROZEN || $me->isRoleAtLeast(STAFF)) );
            $edit = (isset($_GET['edit']) || $create);
            $edit = ( $edit && (!FROZEN || $me->isRoleAtLeast(STAFF)) );
            $edit = ($edit && !$paper->deleted);
            
            $post = (isset($_POST['submit']) && ($_POST['submit'] == "Save $name" || $_POST['submit'] == "Create $name"));
            $post = ( $post && (!FROZEN || $me->isRoleAtLeast(STAFF)) );
            
            if(($name == "Activity" || $name == "Press" || $name == "Award" || $name == "Publication" || $name == "Artifact" || $name == "Presentation") && 
               (($paper->getTitle() != null && $paper->getCategory() == $name) || $create)){
                TabUtils::clearActions();
                $category = $name;
                $authorTitle = self::getAuthorTitle($category);
                if($post){
                    // The user has submitted the form
                    self::proccessPost($category);
                    if(!$create){
                        redirect("$wgServer$wgScriptPath/index.php/$category:".str_replace("?", "%3F", str_replace("&#39;", "'", $title)));
                    }
                    else{
                        redirect("$wgServer$wgScriptPath/index.php/$category:".str_replace("?", "%3F", $title));
                    }
                }
                $wgOut->clearHTML();
                if(!$create){
                    $wgOut->setPageTitle(str_replace("&#39;", "'", $paper->getTitle()));
                }
                else{
                    $wgOut->setPageTitle($title);
                }
                if($edit){
                    $misc_types = Paper::getAllMiscTypes($category);
                    
                    $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/scripts/switcheroo.js'></script>");
                    $wgOut->addScript('<script type="text/javascript">
                    var oldAttr = Array();
                    
                    var misc_types = ["'.implode("\",\n\"", $misc_types).'"];

                    $(document).ready(function(){
                        var type = "'.$paper->getType().'";
                        if(type == "Paper"){
                            type = "Proceedings Paper";
                        }
                        showHideAttr(type);

                        // Remove warning flag from selection options
                        $("select").one("click",function(e){
                          var text = $(this).prop("selectedindex",0).val();
                          if (text.indexOf("SELECT") == 0){
                            $("option:first",this).remove();
                          }
                            $(this).css("background-color", "white");
                        });
                    });
                    
                    function showHideAttr(type){
                        $.each($("tr.attr"), function(index, value){
                            if($(value).attr("name").toLowerCase() != "date"){
                                oldAttr[$(value).attr("name").toLowerCase()] = $(value).detach();
                            }
                        });
                        var category = "'.$name.'";
                        $("input[name=misc_type]").remove();
                        switch(category){
                            case "Activity":
                                switch(type){
                                    case "Event Organization":
                                        addAttr("Conference");
                                        addAttr("Location");
                                        addAttr("Organizing Body");
                                        addAttr("URL");
                                        break;
                                    default:
                                    case "Misc":
                                        $("select[name=type]").parent().append("<input type=\'text\' name=\'misc_type\' value=\''.str_replace("Misc: ", "", $paper->getType()).'\' />");
                                        $("input[name=misc_type]").autocomplete({
                                            source: misc_types
                                        });
                                        addAttr("Conference");
                                        addAttr("Location");
                                        addAttr("Organizing Body");
                                        addAttr("URL");
                                        break;
                                }
                                break;
                            case "Artifact":
                                switch(type){
                                    case "Repository":
                                        addAttr("URL");
                                        break;
                                    case "Open Software":
                                        addAttr("URL");
                                        break;
                                    case "Patent":
                                        addAttr("Number");
                                        break;
                                    case "Device/Machine":
                                        break;
                                    case "Aesthetic Object":
                                        break;
                                    default:
                                    case "Misc":
                                        $("select[name=type]").parent().append("<input type=\'text\' name=\'misc_type\' value=\''.str_replace("Misc: ", "", $paper->getType()).'\' />");
                                        $("input[name=misc_type]").autocomplete({
                                            source: misc_types
                                        });
                                        break;
                                }
                                break;
                            case "Press":
                                switch(type){
                                    case "University Press":
                                        addAttr("URL");
                                        break;
                                    case "Provincial News":
                                        addAttr("URL");
                                        break;
                                    case "National News":
                                        addAttr("URL");
                                        break;
                                    case "International News":
                                        addAttr("URL");
                                        break;
                                    default:
                                    case "Misc":
                                        $("select[name=type]").parent().append("<input type=\'text\' name=\'misc_type\' value=\''.str_replace("Misc: ", "", $paper->getType()).'\' />");
                                        $("input[name=misc_type]").autocomplete({
                                            source: misc_types
                                        });
                                        addAttr("URL");
                                        break;
                                }
                                break;
                            case "Award":
                                switch(type){
                                    default:
                                    case "Award":
                                        addAttr("URL");
                                        break;
                                }
                                break;
                            case "Presentation":
                                switch(type){
                                    case "Paper Session":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Organizing Body").');
                                        addAttr("URL");
                                        break;
                                    case "Panel":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Organizing Body").');
                                        addAttr("URL");
                                        break;
                                    case "Tutorial":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Organizing Body").');
                                        addAttr("URL");
                                        break;
                                    case "Keynote":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Organizing Body").');
                                        addAttr("URL");
                                        break;
                                    case "Distinguished Lecture":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Host Institution").');
                                        addAttrDefn('.$this->get_defn("Host Department").');
                                        addAttr("URL");
                                        break;
                                    case "Departmental Lecture":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Host Institution").');
                                        addAttrDefn('.$this->get_defn("Host Department").');
                                        addAttr("URL");
                                        break;
                                    case "Departmental Seminar":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Host Institution").');
                                        addAttrDefn('.$this->get_defn("Host Department").');
                                        addAttr("URL");
                                        break;
                                    case "Group Seminar":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Host Institution").');
                                        addAttrDefn('.$this->get_defn("Host Department").');
                                        addAttrDefn('.$this->get_defn("Host Research Group").');
                                        addAttr("URL");
                                        break;
                                    case "2mm":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Organizing Body").');
                                        addAttr("URL");
                                        break;
                                    case "WIP":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Organizing Body").');
                                        addAttr("URL");
                                        break;
                                    case "RNotes":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Organizing Body").');
                                        addAttr("URL");
                                        break;
                                    default:
                                    case "Misc":
                                        $("select[name=type]").parent().append("<input type=\'text\' name=\'misc_type\' value=\''.str_replace("Misc: ", "", $paper->getType()).'\' />");
                                        $("input[name=misc_type]").autocomplete({
                                            source: misc_types
                                        });
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Organizing Body").');
                                        addAttr("URL");
                                        break;
                                }
                                break;
                            case "Publication":
                                switch(type){
                                    case "BibTex Article":
                                        addAttrDefn('.$this->get_defn("Published In").');
                                        addAttrDefn('.$this->get_defn("Volume").');
                                        addAttrDefn('.$this->get_defn("Number").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "BibTex Collection":
                                        addAttrDefn('.$this->get_defn("Book Title").');
                                        addAttrDefn('.$this->get_defn("Editors").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttrDefn('.$this->get_defn("Address").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "BibTex Book":
                                    case "Book":
                                    case "Edited Book":
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Book Chapter":
                                        addAttrDefn('.$this->get_defn("Book Title").');
                                        addAttrDefn('.$this->get_defn("Editors").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Book Review":
                                        addAttrDefn('.$this->get_defn("Book Title").');
                                        addAttrDefn('.$this->get_defn("Published In").');
                                        addAttrDefn('.$this->get_defn("Volume").');
                                        addAttrDefn('.$this->get_defn("Number").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Review Article":
                                        addAttrDefn('.$this->get_defn("Published In").');
                                        addAttrDefn('.$this->get_defn("Volume").');
                                        addAttrDefn('.$this->get_defn("Number").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "White Paper":
                                        addAttrDefn('.$this->get_defn("Published In").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Editor").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("URL");
                                        break;
                                    case "Magazine/Newspaper Article":
                                        addAttrDefn('.$this->get_defn("Published In").');
                                        addAttrDefn('.$this->get_defn("Volume").');
                                        addAttrDefn('.$this->get_defn("Number").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Journal Paper":
                                        addAttrDefn('.$this->get_defn("Published In").');
                                        addAttrDefn('.$this->get_defn("Volume").');
                                        addAttrDefn('.$this->get_defn("Number").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Journal Abstract":
                                        addAttrDefn('.$this->get_defn("Published In").');
                                        addAttrDefn('.$this->get_defn("Volume").');
                                        addAttrDefn('.$this->get_defn("Number").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Proceedings Paper":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Collections Paper":
                                        addAttrDefn('.$this->get_defn("Book Title").');
                                        addAttrDefn('.$this->get_defn("Editors").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttrDefn('.$this->get_defn("Address").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "PHD Thesis":
                                        addAttrDefn('.$this->get_defn("University").');
                                        addAttrDefn('.$this->get_defn("Department").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Masters Thesis":
                                        addAttrDefn('.$this->get_defn("University").');
                                        addAttrDefn('.$this->get_defn("Department").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Bachelors Thesis":
                                        addAttrDefn('.$this->get_defn("University").');
                                        addAttrDefn('.$this->get_defn("Department").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Poster":
                                        addAttrDefn('.$this->get_defn("Event Title").');
                                        addAttrDefn('.$this->get_defn("Event Location").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Tech Report":
                                        addAttrDefn('.$this->get_defn("University").');
                                        addAttrDefn('.$this->get_defn("Department").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    case "Manual":
                                        addAttrDefn('.$this->get_defn("Volume").');
                                        addAttrDefn('.$this->get_defn("Edition").');
                                        addAttrDefn('.$this->get_defn("Series").');
                                        addAttrDefn('.$this->get_defn("Pages").');
                                        addAttrDefn('.$this->get_defn("Publisher").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                    default:
                                    case "Misc":
                                        $("select[name=type]").parent().append("<input type=\'text\' name=\'misc_type\' value=\''.str_replace("Misc: ", "", $paper->getType()).'\' />");
                                        $("input[name=misc_type]").autocomplete({
                                            source: misc_types
                                        });
                                        addAttrDefn('.$this->get_defn("Venue").');
                                        addAttrDefn('.$this->get_defn("How Published").');
                                        addAttrDefn('.$this->get_defn("Note").');
                                        addAttr("ISBN");
                                        addAttr("ISSN");
                                        addAttr("DOI");
                                        addAttr("URL");
                                        break;
                                break;
                            }
                        }
                    }
                    
                    function placeInHidden(){
                        $.each($("#authors").children(), function(index, val){
                            $("#hiddenAuthors").append("<input class=\'auth\' type=\'hidden\' name=\'authors[]\' value=\'" + val.innerHTML + "\' />");
                        });
                    }
                    
                    function addAttr(attr){
                        if(typeof oldAttr[attr.toLowerCase()] == "undefined"){
                            var newAttr = document.createElement("tr");
                            newAttr.setAttribute("name", attr.toLowerCase());
                            newAttr.setAttribute("class", "attr");
                            newAttr.innerHTML="<td align=\"right\"><b>" + attr + ":</b></td><td><input type=\"text\" size=\"50\" name=\"" + attr.toLowerCase() + "\" /></td>";
                            $("#attributes").append(newAttr);
                        }
                        else{
                            $("#attributes").append(oldAttr[attr.toLowerCase()]);
                        }
                    }

                    function addAttrDefn(attr, defn){   // MARK
                        if(typeof oldAttr[attr.toLowerCase()] == "undefined"){
                            var newAttr = document.createElement("tr");
                            newAttr.setAttribute("name", attr.toLowerCase());
                            newAttr.setAttribute("class", "attr");
                            newAttr.setAttribute("title", defn); 
                            newAttr.innerHTML="<td align=\"right\"><b>" + attr + ":</b></td><td><input type=\"text\" size=\"50\" name=\"" + attr.toLowerCase() + "\" /></td>";
                            $("#attributes").append(newAttr);
                        }
                        else{
                            $("#attributes").append(oldAttr[attr.toLowerCase()]);
                        }
                    }

                    function disableEnterKey(e){
                        var key;
                        
                        if(window.event){
                            key = window.event.keyCode;     //IE
                        }
                        else{
                            key = e.which;     //firefox
                        }
                        if(key == 13){      // ENTER
                            return false;
                        }
                        else{
                            return true;
                        }
                    }
                    
			        </script>');
				}
				else{
				    $wgOut->addScript('<script type="text/javascript">
				        $(document).ready(function(){
				            $("#delete_popup").dialog({autoOpen: false, 
				                                       position: "center", 
				                                       draggable: false, 
				                                       resizable: false,
				                                       modal: true });
                            $("#delete_button").click(function(){
                                $("#delete_popup").dialog("open");
                            });
                            
                            $("#delete_no").click(function(){
                                $("#delete_popup").dialog("close");
                            });
                            
                            $("#delete_yes").click(function(){
                                data = {"id" : '.$paper->getId().',
                                        "notify" : true
                                       };
                                $.post("'.$wgServer.$wgScriptPath.'/index.php?action=api.deletePaperRef", data, function(response){
                                    addAPIMessages(response);
                                    if(response.errors.length == 0){
                                        $("#delete_button").prop("disabled", true);
                                        $("#edit_button").prop("disabled", true);
                                    }
                                    $("#delete_popup").dialog("close");
                                });
                            });
                        });
                    </script>');
				}
                
                if($edit){
                    if($create){
                        $wgOut->addHTML("<form name='product' action='$wgServer$wgScriptPath/index.php/{$category}:".str_replace("?", "%3F", str_replace("'", "&#39;", $title))."?create' method='post'>
                                        <input type='hidden' name='title' value='".str_replace("'", "&#39;", $title)."' /><input type='hidden' name='product_id' value='$product_id' />");
                    }
                    else{
                        $wgOut->addHTML("<form name='product' action='{$paper->getURL()}?edit' method='post'>
                                            <input type='hidden' name='title' value='{$paper->getTitle()}' /><input type='hidden' name='product_id' value='$product_id' /><div style='font-weight:bold; font-size:14px;padding: 10px 0;'>Change Title: <input type='text' value='{$paper->getTitle()}' name='new_title' size='80' /></div>");
                    }
                    $wgOut->addHTML("<div id='dialog_duplicate' title='Possible Duplicate' style='display:none;'>
                                      <p>This $category looks like a duplicate of:<br /></p>
                                      <ul></ul>
                                      <button id='duplicate_create'>Save Anyways</button> <button id='duplicate_cancel'>Cancel</button>
                                    </div>");
                    $wgOut->addHTML("<script type='text/javascript'>
                        var validated = false;
                        $('#duplicate_create').click(function(){
                            validated = true;
                            $('#dialog_duplicate').dialog('close');
                            $('form[name=product] input[type=submit]').click();
                        });
                        $('#duplicate_cancel').click(function(){
                            $('#dialog_duplicate').dialog('close');
                        });
                        $('form[name=product]').submit(function(){
                            if(!validated){
                                var title = $('form[name=product] input[name=title]').attr('value');
                                if($('form[name=product] [name=new_title]').length > 0){
                                    title = $('form[name=product] input[name=new_title]').attr('value');
                                }
                                var category = '{$category}';
                                var type = $('form[name=product] [name=type]').attr('value');
                                if(type == 'Misc'){
                                    type = 'Misc: ' + $('form[name=product] [name=misc_type]').attr('value');
                                }
                                var status = $('form[name=product] [name=status]').attr('value');
                                $.get('{$wgServer}{$wgScriptPath}/index.php?action=api.getPublicationInfoByTitle&title=' + escape(title) + 
                                      '&category=' + escape(category) + 
                                      '&type=' + type + 
                                      '&status=' + status, 
                                      function(response){
                                    var matched = Array();
                                    for(pId in response.data.matched){
                                        var paper = response.data.matched[pId];
                                        if(paper.id != '{$paper->getId()}'){
                                            matched.push(paper);
                                        }
                                    }
                                    if(matched.length > 0){
                                        $('#dialog_duplicate ul').empty();
                                        for(pId in matched){
                                            var paper = matched[pId];
                                            $('#dialog_duplicate ul').append('<li><a href=\"' + paper.url + '\" target=\"_blank\">$category #' + paper.id + '</a></li>');
                                        }
                                        $('#dialog_duplicate').dialog({
                                          resizable: false,
                                          modal: true
                                        });
                                    }
                                    else{
                                        validated = true;
                                        $('form[name=product] input[type=submit]').click();
                                    }
                                });
                                return false;
                            }
                        });
                    </script>");
                }
                $wgOut->addWikiText("== {$authorTitle}s ==
                                     __NOEDITSECTION__\n");
                $authors = $paper->getAuthors();
                $i = 1;
                $nAuthors = count($authors);
                $authorNames = array();
                if(!$create){
                    foreach($authors as $author){
                        $authorNames[] = $author->getNameForForms();
                    }
                }
                if($edit){
                    $allPeople = Person::getAllPeople('all');
                    $list = array();
                    foreach($allPeople as $person){
                        if(array_search($person->getNameForForms(), $authorNames) === false){
                            $list[] = $person->getNameForForms();
                        }
                    }
                    $wgOut->addHTML("<div class='switcheroo' name='{$authorTitle}' id='authors'>
                                            <div class='left'><span>".implode("</span>\n<span>", $authorNames)."</span></div>
                                            <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                                        </div>");
                }
                else{
                    $texts = array();
                    foreach($authors as $author){
                        if($author->getRoles() != null){
                            $texts[] = "<a href='{$author->getUrl()}'>{$author->getNameForForms()}</a>";
                        }
                        else{
                            $texts[] = $author->getNameForForms();
                        }
                    }
                    $wgOut->addHTML("<span id=test_authors>".implode(", ", $texts)."</span>");
                }
                
                if($category == "Publication"){
                    if($edit || !$edit && $paper->getDescription() != ""){
                        $wgOut->addWikiText("== Abstract ==
                                            __NOEDITSECTION__\n");
                    }
                }
                else if($category == "Artifact" || $category == "Activity" || $category == "Press" || $category == "Award" || $category == "Presentation"){
                    if($edit || !$edit && $paper->getDescription() != ""){
                        $wgOut->addWikiText("== Description ==
                                            __NOEDITSECTION__\n");
                    }
                }
                if($edit){
                    $wgOut->addHTML("<textarea style='height:175px; width:650px;' name='description'>{$paper->getDescription()}</textarea>");
                }
                else{
                    $wgOut->addWikiText($paper->getDescription());
                }
                
                $date = $paper->getDate();
                $wgOut->addWikiText("== $category Information ==
                                     __NOEDITSECTION__\n");
                $wgOut->addHTML("<table border=0 id='attributes'>");

								$hasBibtex = false;
                                $typeOpts = "";
                if($category == "Publication"){

										$type = $paper->getType();
										if($create){
												$type = "Misc";
										}
                    if($edit){
												// BIBTEX: check here, add warnings as necessary
												if (strpos($type, "BibTex") === 0){
														$hasBibtex = true;
														$typeOpts = "<option selected=selected >SELECT ARTICLE TYPE</option>"; // DEFAULT
														if(strpos($type, "Book") > 0)
															$typeOpts = "<option selected=selected >Edited Book</option>";
														if(strpos($type, "Collection") > 0)
															$typeOpts = "<option selected=selected >Collections Paper</option>";
												}

												foreach($types[$category] as $pType => $pDescription){
														if(strpos($pType, "BibTex") === 0) // bibtex types should never be visible as options
                                continue;
														$selected = "";
														if($type == $pType || (strstr($type, "Misc") !== false && strstr($pType, "Misc") !== false)){
																$selected = " selected='selected'";
														}
														if (strpos($typeOpts, ">".$pType."<") === false) // skip duplicate bibtex default types, if any
															$typeOpts = $typeOpts."<option$selected title='$pDescription'>$pType</option>";
												}

												// STATUS
												$statusOpts = "";
                        if($hasBibtex){    // Pre-pend warning to option list
                            $statusOpts = "<option selected=selected >SELECT PUBLICATION STATUS</option>";
                        }
                        $submittedSelected = ($paper->getStatus() == "Submitted") ? " selected='selected'" : "";
                        $toappearSelected = ($paper->getStatus() == "To Appear") ? " selected='selected'" : "";
                        $revisionSelected = ($paper->getStatus() == "Under Revision") ? " selected='selected'" : "";
                        $publishedSelected = ($paper->getStatus() == "Published") ? " selected='selected'" : "";
                        $toAppearSelected = ($paper->getStatus() == "To Appear") ? " selected='selected'" : "";
                        $rejectedSelected = ($paper->getStatus() == "Rejected") ? " selected='selected'" : "";
                        $statusOpts = $statusOpts."<option$submittedSelected title='draft submitted to venue, not yet accepted'>Submitted</option>
                                                   <option$toappearSelected title='Accepted/In Press/To Appear: will be published, not yet released.'>To Appear</option>
                                                   <option$revisionSelected title='accepted and undergoing final changes'>Under Revision</option>
                                                   <option$publishedSelected title='appears in specified venue'>Published</option>
                                                   <option$rejectedSelected title='was not accepted for publication'>Rejected</option>";
                        $wgOut->addHTML("<tr title='the publication status of the paper'>
                                            <td align='right'><b>Status:</b></td>
                                            <td>");
												if($hasBibtex)
														$wgOut->addHTML("<select name='status' style='background-color:orange' >");
												else
														$wgOut->addHTML("<select name='status'>");
												$wgOut->addHTML($statusOpts);
												$wgOut->addHTML("</select>
                                                <a href='#' title='Mouse-over options to see definitions'
                                                            onclick='return false'><img src='../skins/common/images/q_white.png'></a>
                                            </td>
                                        </tr>");

                    } else {
                        $wgOut->addHTML("<tr>
                                            <td align='left'><b>Status:</b><br /></td>");
                        $status = $paper->getStatus();
                        if ($status !== '')
                            $wgOut->addHTML("<td>{$status}<br /></td> </tr>");
                        else
                            $wgOut->addHTML("<td style='background-color: orange'><br /></td> <td>&nbsp;&nbsp;&nbsp;&nbsp;Please click <b>Edit Publication</b> to specify the publication's status.</td></tr>");
                    }
                }
                else if($category == "Artifact"){
                    if($edit){
                        $peerReviewed = ($paper->getStatus() == "Peer Reviewed") ? " checked='checked'" : "checked='checked'";
                        $notPeerReviewed = ($paper->getStatus() == "Not Peer Reviewed") ? " checked='checked'" : "";
                        $wgOut->addHTML("<tr>
                                            <td align='right' valign='top'><b>Status:</b><br /><br /></td>
                                            <td>
                                                <input type='radio' name='status' value='Peer Reviewed'$peerReviewed /> Peer Reviewed<br />
                                                <input type='radio' name='status' value='Not Peer Reviewed'$notPeerReviewed /> Not Peer Reviewed<br /></br />
                                            </td>
                                        </tr>");
                    }
                    else{
                        $wgOut->addHTML("<tr>
                                            <td valign='top'><b>Status:</b><br /><br /></td>
                                            <td>{$paper->getStatus()}<br /><br /></td>
                                        </tr>");
                    }
                }

                // TYPE
                $align = ($edit) ? "align='right'" : "align='left'";
                $wgOut->addHTML("<tr title='the type of publication''>
                                        <td $align><b>Type:</b></td>");
                if($create){
                    $type = "Misc";
                }
                $currType = "";

                if($edit){
                  if($typeOpts == ""){
                    $type = $paper->getType();
                    foreach($types[$category] as $pType => $pDescription){                        
                        $selected = "";
                        if($type == $pType || (strstr($type, "Misc") !== false && strstr($pType, "Misc") !== false)){
                                $selected = " selected='selected'";
                        }
                        $typeOpts = $typeOpts."<option$selected title='$pDescription'>$pType</option>";
                    }
                  }

                  if($hasBibtex)
                      $wgOut->addHTML("<td><select style='background-color:orange' onChange='showHideAttr(this.value);' name='type'>");
                  else
                      $wgOut->addHTML("<td><select onChange='showHideAttr(this.value);' name='type'>");
                  $wgOut->addHTML($typeOpts);
                  $wgOut->addHTML("</select></td>");
                  if($hasBibtex && !strpos($type, "Article"))
                      $wgOut->addHTML("<td><b>Please ensure that the default type is correct.</b></td>");

                } else {
                    $type = $paper->getType();
                    if(strpos($type, "BibTex") === 0){
                        $wgOut->addHTML("<td style='background-color:orange'>".str_replace("Misc: ", "", $type)."</td>");
                        $wgOut->addHTML("<td>&nbsp;&nbsp;&nbsp;&nbsp;Please click <b>Edit Publication</b> to select the specific publication type.</td>");
                    } else {
                        $wgOut->addHTML("<td>".str_replace("Misc: ", "", $type)."</td>");
                    }
                }
                $wgOut->addHTML("</tr>");

								// PEER REVIEW
                if($category == "Publication"){
                    if($edit){
                        $arr = $paper->getData();
                        $value = (isset($arr['peer_reviewed']))? $arr['peer_reviewed'] : "";
                        $peerReviewed = ($value == "Yes") ? " checked='checked'" : "";
                        $notPeerReviewed = ($value == "No" || $value == "") ? " checked='checked'" : "";
                        $wgOut->addHTML("<tr title='whether or not published in a peer-reviewed publication'>
                                             <td align='right' valign='top'><b>Peer Reviewed:</b><br /><br /></td>
                                             <td>
                                                 <input type='radio' name='peer_reviewed' value='Yes'$peerReviewed /> Yes<br />
                                                 <input type='radio' name='peer_reviewed' value='No'$notPeerReviewed /> No<br />
                                             </td>
                                          </tr>");
                    } else {
                        $arr = $paper->getData();
                        $value = (isset($arr['peer_reviewed']))? $arr['peer_reviewed'] : "";
                        $this->addAttrRow("Peer Reviewed", $value);
                    }
                }

								// DATE
                if($edit){
                    $today = getdate();

                    if($category != "Publication"){
                        $today['year'] -= 2;
                    }
                    
                    $wgOut->addHTML("<tr title='the date of publication'>
                                        <td align='right'><b>Date:</b></td>
                                        <td>".$this->date_picker($date, 2000, $today['year'] + 2)."</td>
                                    </tr>");
                }
                else{
                    $exploded = explode("-", $date);
                    
                    $month = $exploded[1];
                    $day = $exploded[2];
                    $year = $exploded[0];
                    if($year == "0000"){
                        // Year is unknown, don't display the date at all
                        $html = "";
                    }
                    else if($month == "00"){
                        // Month is unknown, only display the year
                        $dateTime = new DateTime("$year-01-01");
                        $html = $dateTime->format('Y');
                    }
                    else if($day == "00"){
                        $dateTime = new DateTime("$year-$month-01");
                        $html = $dateTime->format('M, Y');
                    }
                    else{
                        $dateTime = new DateTime("$year-$month-$day");
                        $html = $dateTime->format('M j, Y');
                    }
                    if($html != ""){
                        $wgOut->addHTML("<tr title='the date of publication'>
                                            <td><b>Date:</b></td>
                                            <td>$html</td>
                                        </tr>");
                    }
                }

                // DEFAULT FIELDS for all types in category
                if($category == "Publication"){
                    $data = $paper->getData();
                    if(!isset($data['isbn'])){
                        $this->addAttrRow("ISBN","");
                    }
                    if(!isset($data['issn'])){
                        $this->addAttrRow("ISSN","");
                    }
                    if(!isset($data['doi'])){
                        $this->addAttrRow("DOI","");
                    }
                    if(!isset($data['url'])){
                        $this->addAttrRow("URL", "");
                    }
                }
                else if($category == "Artifact"){
                    //Nothing extra
                }
                else if($category == "Press"){
                    $data = $paper->getData();
                    if(!isset($data['url'])){
                        $this->addAttrRow("URL", "");
                    }
                }
                else if($category == "Award"){
                    $data = $paper->getData();
                    if(!isset($data['url'])){
                        $this->addAttrRow("URL", "");
                    }
                }
                else if($category == "Presentation"){
                    if($edit){
                        $invitedSelected = ($paper->getStatus() == "Invited") ? "checked='checked'" : "";
                        $notinvitedSelected = ($paper->getStatus() == "Not Invited") ? "checked='checked'" : "";
                        if($invitedSelected == "" && $notinvitedSelected == ""){
                            $notinvitedSelected = "checked='checked'";
                        }
                        $wgOut->addHTML("<tr title='whether or not the presentation was invited'>
                                            <td align='right'><b>Status:</b><br /><br /></td>
                                            <td>
                                                <input type='radio' name='status' value='Not Invited' $notinvitedSelected /> Not Invited<br />
                                                <input type='radio' name='status' value='Invited' $invitedSelected /> Invited
                                                </br />
                                            </td>
                                        </tr>");
                    }
                    else{
                        $wgOut->addHTML("<tr>
                                            <td><b>Status:</b></td>
                                            <td>{$paper->getStatus()}</td>
                                        </tr>");
                    }
                    $data = $paper->getData();
                    if(!isset($data['url'])){
                        $this->addAttrRow("URL", "");
                    }
                }
                else if($category == "Activity"){
                    $data = $paper->getData();
                    if(!isset($data['conference'])){
                        $this->addAttrRow("Conference", "");
                    }
                    if(!isset($data['location'])){
                        $this->addAttrRow("Location", "");
                    }
                    if(!isset($data['organizing_body'])){
                        $this->addAttrRow("Organizing Body", "");
                    }
                    if(!isset($data['url'])){
                        $this->addAttrRow("URL", "");
                    }
                }

                if(!$create){
                    foreach($paper->getData() as $attr => $value){
                        if ($attr == 'peer_reviewed'){  // want to guarantee placement as per $edit page
                            continue;
                        }
                        $attr = ucwords(str_replace("_", " ", $attr));
                        if($attr == "Isbn" ||
                           $attr == "Doi" ||
                           $attr == "Issn" ||
                           $attr == "Url"){
                            $attr = strtoupper($attr);   
                        }
                        $this->addAttrRow($attr, $value);
                    }
                }

                if($me->isLoggedIn()){
                    $rmcYears = $paper->getReportedYears('RMC');
                    $nceYears = $paper->getReportedYears('NCE');
                    $rmc = (count($rmcYears) > 0) ? implode(", ", $rmcYears) : "Never";
                    $nce = (count($nceYears) > 0) ? implode(", ", $nceYears) : "Never";

                    $reported = "<tr><td><b>Reported to RMC:</b></td><td>{$rmc}</td></tr><tr><td><b>Reported to NCE:</b></td><td>{$nce}</td></tr>";
                    $wgOut->addHTML($reported);
                }

                $wgOut->addHTML("</table>");
                $projects = $paper->getProjects();
                if($edit || !$edit && count($projects) > 0){
                    $wgOut->addWikiText("== Related Projects ==
                                         __NOEDITSECTION__\n");
                }
                
                $pProjects = array();
                if(!$create){
                    foreach($projects as $project){
                        $pProjects[] = $project->getName();
                    }
                }
                if($edit){
                    $projs = Project::orderProjects(Project::getAllProjects());
		            $pArray = array();
		            foreach($projs as $project){
		                $pArray[] = $project->getName();
		            }
		            $wgOut->addHTML("<table border='0' cellspacing='2'><tr>");
		            $i = 0;
		            foreach($pArray as $project){
                        if($i % 3 == 0){
		                    $wgOut->addHTML("</tr><tr>\n");
	                    }
	                    if(array_search($project, $pProjects) !== false){
	                        $wgOut->addHTML("<td style='min-width:150px;' valign='top'><input type='checkbox' name='projects[]' value='$project' checked='checked' /> $project<br /></td>\n");
	                    }
	                    else {
	                        $wgOut->addHTML("<td style='min-width:150px;' valign='top'><input type='checkbox' name='projects[]' value='$project' /> $project</td>\n");
	                    }
	                    $i++;
	                }
	                $wgOut->addHTML("</table>");
	                if(count($projects) > 0){
	                    foreach($projects as $project){
	                        // Add any deleted projects so that they remain as part of this project
	                        if($project->deleted){
	                            $wgOut->addHTML("<input style='display:none;' type='checkbox' name='projects[]' value='{$project->getName()}' checked='checked' />");
	                        }
	                    }
	                }
                }
                else{
                    $projectList = array();
                    if(count($projects) > 0){
                        foreach($projects as $project){
                            if(!$project->deleted){
                                $projectList[] = "<a href='{$project->getUrl()}'>{$project->getName()}</a>";
                            }
                        }
                    }
                    $wgOut->addHTML(implode(", ", $projectList));
                }
                $wgOut->addHTML("<br />");
                if($wgUser->isLoggedIn()){
                    if($create){
                        $wgOut->addHTML("<input type='submit' name='submit' value='Create $category' />");
                        $wgOut->addHTML("</form>");
                    }
                    else if($edit){
                        $wgOut->addHTML("<input type='submit' name='submit' value='Save $category' />");
                        $wgOut->addHTML("</form>");
                    }
                    else if( (!FROZEN || $me->isRoleAtLeast(STAFF)) ){
                        $wgOut->addHTML("<div title='Delete $category?' style='white-space: pre-line;' id='delete_popup'>
                            Are you sure you want to delete the $category <i>{$paper->getTitle()}</i>?<br />
                            <center><button id='delete_yes'>Yes</button> <button id='delete_no'>No</button></center>
                        </div>");
                        $disabled = "";
                        if($paper->deleted){
                            $disabled = " disabled='disabled'";
                        }
                        $wgOut->addHTML("<br /><input type='button' name='edit' id='edit_button' value='Edit $category' onClick='document.location=\"$wgServer$wgScriptPath/index.php/$category:".$paper->getId()."?edit\";' $disabled />");
                        $wgOut->addHTML("&nbsp;<input type='button' name='delete' id='delete_button' value='Delete $category' $disabled />");
                    }
                }
                $wgOut->output();
                $wgOut->disable();
            }
            else if($name == "Publication"){
                $wgOut->clearHTML();
                
                $wgOut->setPageTitle("Publication Does Not Exist");
                $wgOut->addHTML("The publication '$title' does not exist. <a href='$wgServer$wgScriptPath/index.php/Publication:$title?create'>Click Here</a> to create the publication.");
                
                $wgOut->output();
                $wgOut->disable();
            }
            else if($name == "Artifact"){
                $wgOut->clearHTML();
                
                $wgOut->setPageTitle("Artifact Does Not Exist");
                $wgOut->addHTML("The artifact '$title' does not exist. <a href='$wgServer$wgScriptPath/index.php/Artifact:$title?create'>Click Here</a> to create the artifact.");
                
                $wgOut->output();
                $wgOut->disable();
            }
            else if($name == "Activity"){
                $wgOut->clearHTML();
                
                $wgOut->setPageTitle("Activity Does Not Exist");
                $wgOut->addHTML("The activity '$title' does not exist. <a href='$wgServer$wgScriptPath/index.php/Activity:$title?create'>Click Here</a> to create the activity.");
                
                $wgOut->output();
                $wgOut->disable();
            }
            else if($name == "Press"){
                $wgOut->clearHTML();
                
                $wgOut->setPageTitle("Press Article Does Not Exist");
                $wgOut->addHTML("The press article '$title' does not exist. <a href='$wgServer$wgScriptPath/index.php/Press:$title?create'>Click Here</a> to create the article.");
                
                $wgOut->output();
                $wgOut->disable();
            }
            else if($name == "Award"){
                $wgOut->clearHTML();
                
                $wgOut->setPageTitle("Award Does Not Exist");
                $wgOut->addHTML("The Award '$title' does not exist. <a href='$wgServer$wgScriptPath/index.php/Award:$title?create'>Click Here</a> to create the award.");
                
                $wgOut->output();
                $wgOut->disable();
            }
            else if($name == "Presentation"){
                $wgOut->clearHTML();
                
                $wgOut->setPageTitle("Presentation Does Not Exist");
                $wgOut->addHTML("The Presentation '$title' does not exist. <a href='$wgServer$wgScriptPath/index.php/Presentation:$title?create'>Click Here</a> to create the presentation.");
                
                $wgOut->output();
                $wgOut->disable();
            }
        }
        return true;
    }
    
    function getAuthorTitle($category){
        switch($category){
            case "Publication":
                return "Author";
                break;
            case "Artifact":
                return "Author";
                break;
            case "Activity":
                return "Person";
                break;
            case "Presentation":
                return "Person";
                break;
            case "Press":
                return "Mentioned&nbsp;Name";
                break;
            case "Award":
                return "Recipient";
                break;
        }
    }
    
    function proccessPost($category){
        $_POST['date'] = $_POST['year']."-".$_POST['month']."-".$_POST['day'];
        $_POST['abstract'] = $_POST['description'];
        if(!isset($_POST['authors'])){
            $_POST['authors'] = " ";
        }
        else{
            $_POST['authors'] = @array_unique($_POST['authors']);
        }
        if(!isset($_POST['projects']) || $_POST['projects'] == null){
            $_POST['projects'] = " ";
        }
        switch($category){
            case "Activity":
                switch($_POST['type']){
                    default:
                    case "Event Organization":
                        $api = new EventOrganizationAPI(true);
                        break;
                    case "Misc":
                        $api = new MiscActivityAPI(true);
                        break;
                }
                break;
            case "Presentation":
                switch($_POST['type']){
                    default:
                    case "Paper Session":
                        $api = new PaperSessionAPI(true);
                        break;
                    case "Panel":
                        $api = new PanelAPI(true);
                        break;
                    case "Tutorial":
                        $api = new TutorialAPI(true);
                        break;
                    case "Keynote":
                        $api = new KeynoteAPI(true);
                        break;
                    case "2MM":
                        $api = new DistinguishedLectureAPI(true);
                        break;
                    case "Departmental Lecture":
                        $api = new DeptLectureAPI(true);
                        break;
                    case "Departmental Seminar":
                        $api = new DeptSeminarAPI(true);
                        break;
                    case "Group Seminar":
                        $api = new GroupSeminarAPI(true);
                        break;
                    case "2MM":
                        $api = new TwoMMAPI(true);
                        break;
                    case "WIP":
                        $api = new WIPAPI(true);
                        break;
                    case "RNotes":
                        $api = new RNotesAPI(true);
                        break;
                    case "Misc":
                        $api = new MiscPresAPI(true);
                        break;
                }
                break;
            case "Press":
                switch($_POST['type']){
                    default:
                    case "University Press":
                        $api = new UniversityPressAPI(true);
                        break;
                    case "Provincial News":
                        $api = new ProvincialPressAPI(true);
                        break;
                    case "National News":
                        $api = new NationalPressAPI(true);
                        break;
                    case "International News":
                        $api = new InternationalPressAPI(true);
                        break;
                    case "Misc":
                        $api = new PressAPI(true);
                        break;
                }
                break;
            case "Award":
                switch($_POST['type']){
                    default:
                    case "Award":
                        $api = new AwardsAPI(true);
                        break;
                }
                break;
            case "Artifact":
                switch($_POST['type']){
                    default:
                    case "Repository":
                        $api = new RepositoryAPI(true);
                        break;
                    case "Open Software":
                        $api = new SoftwareAPI(true);
                        break;
                    case "Patent":
                        $api = new PatentAPI(true);
                        break;
                    case "Device/Machine":
                        $api = new DeviceAPI(true);
                        break;
                    case "Aesthetic Object":
                        $api = new AestheticObjectAPI(true);
                        break;
                    case "Misc":
                        $api = new ArtifactAPI(true);
                        break;
                }
                break;
            case "Publication":
                switch($_POST['type']){
                    case "BibTex Article":
                        $api = new BibtexArticleAPI(true);
                        break;
                    case "BibTex Book":
                        $api = new BibtexArticleAPI(true);
                        break;
                    case "BibTex Collection":
                        $api = new BibtexArticleAPI(true);
                        break;
                    case "Book":
                        $api = new BookAPI(true);
                        break;
                    case "Edited Book":
                        $api = new EditedBookAPI(true);
                        break;
                    case "Book Chapter":
                        $api = new BookChapterAPI(true);
                        break;
                    case "Book Review":
                        $api = new BookReviewAPI(true);
                        break;
                    case "Review Article":
                        $api = new ReviewArticleAPI(true);
                        break;
                    case "White Paper":
                        $api = new WhitePaperAPI(true);
                        break;
                    case "Magazine/Newspaper Article":
                        $api = new MagazineAPI(true);
                        break;
                    case "Journal Paper":
                        $api = new JournalPaperAPI(true);
                        break;
                    case "Journal Abstract":
                        $api = new JournalAbstractAPI(true);
                        break;
                    case "Proceedings Paper":
                        $api = new ProceedingsPaperAPI(true);
                        break;
                    case "Collections Paper":
                        $api = new CollectionAPI(true);
                        break;
                    case "PHD Thesis":
                        $api = new PHDThesisAPI(true);
                        break;
                    case "Masters Thesis":
                        $api = new MastersThesisAPI(true);
                        break;
                    case "Bachelors Thesis":
                        $api = new BachelorsThesisAPI(true);
                        break;
                    case "Poster":
                        $api = new PosterAPI(true);
                        break;
                    case "Tech Report":
                        $api = new TechReportAPI(true);
                        break;
                    case "Manual":
                        $api = new ManualAPI(true);
                        break;
                    default:
                    case "Misc":
                        $api = new MiscAPI(true);
                        break;
                break;
            }
        }
        $api->doAction();
    }
    
    function date_picker($date, $startyear=NULL, $endyear=NULL){
        $newDate = explode("-", $date);
        $year = @$newDate[0];
        $month = @$newDate[1];
        $day = @$newDate[2];
        if($startyear==NULL){
            $startyear = date("Y")-100;
        }
        if($endyear==NULL){
            $endyear=date("Y")+50;
        }

        $months=array('','January','February','March','April','May',
        'June','July','August', 'September','October','November','December');

        // Month dropdown
        $html="<select name=\"month\">";

        if($month == "00"){
            $html.="<option selected value='00'>--</option>";
        }
        else{
            $html.="<option value='00'>--</option>";
        }
        for($i=1;$i<=12;$i++){
            $selected = "";
            if($month == $i){
                $selected = "selected='selected'";
            }
            $html.="<option $selected value='$i'>$months[$i]</option>";
        }
        $html.="</select> ";
       
        // Day dropdown
        $html.="<select name=\"day\">";
        if($day == "00"){
            $html.="<option selected value='00'>--</option>";
        }
        else{
            $html.="<option value='00'>--</option>";
        }
        for($i=1;$i<=31;$i++){
            $selected = "";
            if($day == $i){
                $selected = "selected='selected'";
            }
            $html.="<option $selected value='$i'>$i</option>";
        }
        $html.="</select> ";

        // Year dropdown
        $html.="<select name=\"year\">";
        if($year == "0000"){
            $html.="<option selected value='0000'>----</option>";
        }
        else{
            $html.="<option value='0000'>----</option>";
        }
        for($i=$endyear;$i>=$startyear;$i--){
            $selected = "";
            if($year == $i){
                $selected = "selected='selected'";
            }     
            $html.="<option $selected value='$i'>$i</option>";
        }
        $html.="</select> ";

        return $html;
    }

    function get_defn($key){
        global $optionDefs;
        return "\"".$key."\", \"".$optionDefs[$key]."\"";
    }
    
    function addAttrRow($attr, $value){
        global $wgOut, $wgUser, $optionDefs;
        $me = Person::newFromId($wgUser->getId());
        $edit = (isset($_GET['edit']) || isset($_GET['create']));
        $edit = ( $edit && (!FROZEN || $me->isRoleAtLeast(STAFF)) );
        if($this->paper != null){
            $edit = ($edit && !$this->paper->deleted);
        }
        
        if($edit || !$edit && $value != ""){
            $align = ($edit) ? "align='right'" : "align='left'";
            $wgOut->addHTML("<tr class='attr' name='$attr'>
                                <td $align><b>$attr:</b></td>");
        }
        if($edit){
            if (isset($optionDefs[$attr])){
                $wgOut->addHTML("<tr class='attr' name='$attr' title='$optionDefs[$attr]'>
                                   <td align='right'><b>$attr:</b></td>");
            } else {
                $wgOut->addHTML("<tr class='attr' name='$attr'>
                                   <td align='right'><b>$attr:</b></td>");
            }
        }
        if($edit){
            $wgOut->addHTML("<td><input size='50' name='".strtolower($attr)."' type='text' value='$value' /></td>");
        }
        else{
            if($value != ""){
                if($attr == "URL"){
                    $wgOut->addHTML("<td><a target='_blank' href='$value'>$value</a></td>");
                }
                else{
                    $wgOut->addHTML("<td>$value</td>");
                }
            }
        }
        $wgOut->addHTML("</tr>");
    }

}
?>
