<?php

class PositionTableTab extends PeopleTableTab {

    var $table;
    var $visibility;
    var $past;
    
    function __construct($table, $visibility, $past=false){
        parent::__construct($table, $visibility, $past);
    }

    function generateBody(){
        $this->html = "<span class='throbber'></span>";
    }
    
    function getHTML(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
        $html = "";
        $me = Person::newFromId($wgUser->getId());
        
        $start = "0000-00-00";
        $end = date('Y-m-d');
        
        $data = array();
        if(is_numeric($this->past)){
            $start = $this->past."-04-01";
            $end = ($this->past+1)."-03-31";
        }
        foreach(Person::getAllPeople() as $person){
            foreach($person->getUniversitiesDuring($start, $end) as $uni){
                if($uni['position'] == $this->table){
                    $data[] = $person;
                }
            }
        }

        $emailHeader = "";
        $idHeader = "";
        $contactHeader = "";
        $projectsHeader = "";
        $uniHeader = "";
        $committees = $config->getValue('committees');
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space: nowrap;'>User Id</th>";
        }

        $emailHeader = "<th style='white-space: nowrap;'>Email</th><th style='white-space: nowrap;'>Website</th>";

        if($config->getValue('projectsEnabled')){
            $projectsHeader = "<th style='white-space: nowrap;'>".Inflect::pluralize($config->getValue('projectTerm'))."</th>";
        }

        if(!isExtensionEnabled("Shibboleth")){
            $uniHeader = "<th style='white-space: nowrap; width:20%;'>Institutions</th>";
        }
        
        $html .= "<table class='indexTable {$this->id}' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th style='white-space: nowrap; width:15%;'>Name</th>
                                    <th style='display:none;'>First Name</th>
                                    <th style='display:none;'>Last Name</th>
                                    {$uniHeader}
                                    <th style='white-space: nowrap; width:15%;'>{$config->getValue('deptsTerm')}</th>
                                    <th style='white-space: nowrap; width:15%;'>Positions</th>
                                    <th style='white-space: nowrap; width:15%;'>Level of Study</th>
                                    <th style='white-space: nowrap; width:40%;'>Keywords / Bio</th>
                                    {$contactHeader}
                                    {$emailHeader}
                                    {$idHeader}
                                </tr>
                            </thead>
                            <tbody>
";
        $count = 0;
        foreach($data as $person){
            $count++;
            $html .= "
                <tr>
                    <td align='center' style='white-space: nowrap;'>
                        <a href='{$person->getUrl()}'><img src='{$person->getPhoto(true)}' style='max-width:100px;max-height:132px; border-radius: 5px;' /></a><br />
                        <a href='{$person->getUrl()}'>{$person->getReversedName()}</a>
                    </td>
                    <td align='left' style='white-space: nowrap;display:none;'>
                        {$person->getFirstName()}
                    </td>
                    <td align='left' style='white-space: nowrap;display:none;'>
                        {$person->getLastName()}
                    </td>";       
            
            // Universities
            $universities = $person->getUniversitiesDuring($start, $end);
            if($uniHeader != ''){
                $html .= "<td align='left' style='white-space:nowrap;'>".implode("<br />", array_unique(array_column($universities, 'university')))."</td>";
            }
            $html .= "<td align='left' style='white-space:nowrap;'>".implode("<br />", array_unique(array_column($universities, 'department')))."</td>";
            $html .= "<td align='left' style='white-space:nowrap;'>".implode("<br />", array_unique(array_column($universities, 'position')))."</td>";
            $html .= "<td align='left' style='white-space:nowrap;'>{$person->getExtra()['sub_position']}</td>";
            
            // Keywords / Bio / Contact
            $keywords = $person->getKeywords(', ');
            $bio = strip_tags(trim($person->getProfile()));
            if($bio != ""){
                $bio = "<div style='display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;'>{$bio}</div>";
            }
            if($keywords != "" && $bio != ""){
                $keywords .= "<br /><br />";
            }
            $html .= "<td align='left'>{$keywords}{$bio}</td>";
            if($contactHeader != ''){
                $html .= "<td align='left'><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td><td align='left'>{$person->getPhoneNumber()}</td>";
            }
            if($emailHeader != ''){
                $html .= ($person->getEmail() != "") ? "<td align='left'><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>" : "<td></td>";
                $html .= ($person->getWebsite() != "http://" && $person->getWebsite() != "https://") ? "<td align='left'><a href='{$person->getWebsite()}'>{$person->getWebsite()}</a></td>" : "<td></td>";
            }
            if($idHeader != ''){
                $html .= "<td>{$person->getId()}</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table><script type='text/javascript'>
            $('.indexTable.{$this->id}').dataTable({
                'aLengthMenu': [[100,-1], [100,'All']], 
                'iDisplayLength': 100, 
                'autoWidth':false,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
        if($count == 0){
            $html = "No people found for this time period";
        }
        return $html;
    }
    
}

?>
