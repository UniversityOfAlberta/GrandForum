<?php

class PersonEmploymentTab extends AbstractEditableTab {

    var $person;
    var $visibility;
    var $category;
    var $startRange;
    var $endRange;

    function __construct($person, $visibility){
        global $config;
        parent::__construct("Education/Employment");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->tooltip = "Contains a table with a list of this Person's eduction/employment history.";
    }
    
    function handleEdit(){
        
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return ($me->isAllowedToEdit($this->person));
    }

    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isRegistered()){
            return "";
        }

        $education = array();
        $employment = array();
        foreach($this->person->getUniversities() as $university){
            $startYear = substr($university['start'], 0, 4)." - ";
            $endYear = substr($university['end'], 0, 4);
            if($startYear == "0000 - "){
                $startYear = "";
            }
            if($endYear == "0000"){
                $endYear = "Present";
            }
            if(in_array(strtolower($university['position']), array_merge(Person::$studentPositions['ugrad'],
                                                                         Person::$studentPositions['msc'], 
                                                                         Person::$studentPositions['phd']))){
                $education[$university['university']][] = "{$university['position']}, {$university['department']}<br />
                                                           {$startYear}{$endYear}<br />";
            }
            else{
                $employment[$university['university']][] = "{$university['position']}, {$university['department']}<br />
                                                            {$startYear}{$endYear}<br />";
            }
        }
        
        $this->html .= "<table width='100%'>
            <tr>
                <td valign='top' width='50%'>
                <h2>Education History</h2>";
        foreach($education as $key => $item){
            $this->html .= "<h3>{$key}</h3>".implode("<br style='line-height:0.5em;' />", $item);
        }
        $this->html .= "</td><td valign='top' width='50%'><h2>Employment History</h2>";
        foreach($employment as $key => $item){
            $this->html .= "<h3>{$key}</h3>".implode("<br style='line-height:0.5em;' />", $item);
        }
        $this->html .= "</td></tr></table>";
        
    }
    
    function generateEditBody(){
        global $wgServer, $wgScriptPath, $wgOut;
        // Load the scripts for Manage People so that the University editing can be used
        $managePeople = new ManagePeople();
        $managePeople->loadTemplates();
        $managePeople->loadModels();
        $managePeople->loadHelpers();
        $managePeople->loadViews();
        $wgOut->addScript("<link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/ManagePeople/style.css' type='text/css' rel='stylesheet' />");
        $this->html .= "<div id='editUniversities' style='border: 1px solid #AAAAAA;'></div><input type='button' id='addUniversity' value='Add Institution' />
        <script type='text/javascript'>
            var model = new Person({id: {$this->person->getId()}});
            var view = new ManagePeopleEditUniversitiesView({model: model.universities, person: model, el: $('#editUniversities')});
            $('#addUniversity').click(function(){
                view.addUniversity();
            });
            $('form').on('submit', function(e){
                if($('input[value=\"Save {$this->name}\"]').is(':visible')){
                    var requests = view.saveAll(false);
                    e.preventDefault();
                    $('input[value=\"Save {$this->name}\"]').prop('disabled', true);
                    $.when.apply($, requests).then(function(){
                        $('form').off('submit');
                        $('input[value=\"Save {$this->name}\"]').prop('disabled', false);
                        _.delay(function(){
                            $('input[value=\"Save {$this->name}\"]').click();
                        }, 25);
                    });
                }
            });
        </script>";
    }

}    
?>
