<?php

class SurveyTab extends AbstractTab {

    static $fields = array("age"          => array("label"  => "Age", 
                                                   "values" => array("8-24",
                                                                     "25-34",
                                                                     "35-44",
                                                                     "45-54",
                                                                     "‭55-64",
                                                                     "‭65+",
                                                                     "Prefer not to answer")), 
                           "gender"       => array("label"  => "Gender", 
                                                   "values" => array("Cisgender man",
                                                                     "Cisgender woman",
                                                                     "Gender-fluid",
                                                                     "Non-binary",
                                                                     "Trans man",
                                                                     "Trans woman",
                                                                     "Two-spirit",
                                                                     "Other",
                                                                     "Prefer not to answer")), 
                           "sex"          => array("label"  => "Sexual orientation", 
                                                   "values" => array("Heterosexual/straight",
                                                                     "Gay",
                                                                     "Lesbian",
                                                                     "Queer",
                                                                     "Bisexual",
                                                                     "Pansexual",
                                                                     "Two-spirit",
                                                                     "Unsure/questioning",
                                                                     "Other",
                                                                     "Prefer not to answer")), 
                           "identity"     => array("label"  => "Indigenous identity", 
                                                   "values" => array("Yes",
                                                                     "No",
                                                                     "Prefer not to answer")), 
                           "minority"     => array("label"  => "Visible minority/Racialized individual",
                                                   "values" => array("Yes",
                                                                     "No",
                                                                     "Prefer not to answer")),
                           "race"         => array("label"  => "Race or population group",
                                                   "values" => array("Arabic/Middle Eastern/West Asian‬",
                                                                     "‭African/Caribbean/Black",
                                                                     "‭East Asian",
                                                                     "‭Indigenous (outside North America)‬",
                                                                     "‭Latinx‬",
                                                                     "‭South Asian",
                                                                     "‭Southeast Asian‬",
                                                                     "‭White",
                                                                     "‭Mixed race",
                                                                     "‭Other",
                                                                     "‭Prefer not to answer")), 
                           "immigrant"    => array("label"  => "Citizenship status",
                                                   "values" => array("Yes",
                                                                     "No",
                                                                     "Prefer not to answer")),
                           "disabilities" => array("label"  => "Disabilities",
                                                   "values" => array("Yes",
                                                                     "No",
                                                                     "Prefer not to answer")),
                           "language"     => array("label"  => "Language",
                                                   "values" => array("English",
                                                                     "French",
                                                                     "Another language",
                                                                     "Prefer not to answer")),
                           "education"    => array("label" => "University education",
                                                   "values" => array("Yes",
                                                                     "No",
                                                                     "Prefer not to answer")),
                           "feedback"    => array("label" => "Feedback",
                                                  "values" => array("Yes",
                                                                    "No",
                                                                    "Unsure",
                                                                    "Prefer not to answer"))
    );

    var $year;

    function __construct($year){
        $this->year = $year;
        parent::__construct(($year-1)."/".($year));
    }
    
    static function getHTML($year="", $project=null, $theme=null){
        if($year != ""){
            $start = ($year-1)."-04";
            $end = "{$year}-03";
            $start1 = "{$year}-04";
            $end1 = ($year+1)."-03";
        }
        else{
            $start = "1900-01";
            $end = "9999-01";
            $start1 = $start;
            $end1 = $end;
            $year = date('Y');
        }
        
        if($project != null){
            $people = $project->getAllPeople();
        }
        else if($theme != null){
            $people = $theme->getAllPeople();
        }
        else{
            $people = Person::getAllPeople();
        }
        
        $groups = array('All' => array(),
                        'Principal Researcher' => array(),
                        'Affiliated Researcher' => array(),
                        'HQP' => array(),
                        'Admin' => array());
        
        foreach($people as $person){
            $groups['All'][] = $person->getId();
            if($project == null && $theme == null){
                if($person->isRoleDuring(CI, $start."-01", $end."-31")){
                    $groups['Principal Researcher'][] = $person->getId();
                }
                if($person->isRoleDuring(AR, $start."-01", $end."-31")){
                    $groups['Affiliated Researcher'][] = $person->getId();
                }
                if($person->isRoleDuring(HQP, $start."-01", $end."-31")){
                    $groups['HQP'][] = $person->getId();
                }
                if($person->isRoleAtLeastDuring(STAFF, $start."-01", $end."-31")){
                    $groups['Admin'][] = $person->getId();
                }
            }
        }
        
        $html = "<div id='accordion{$year}'>";
        foreach($groups as $group => $peopleIds){
            if(count($peopleIds) == 0){
                continue;
            }
            if($project == null && $theme == null){
                $html .= "<h3>{$group}</h3>";
            }
            $html .= "<div>";
            $rows = DBFunctions::execSQL("SELECT *
                                          FROM grand_report_blobs
                                          WHERE rp_type = 'RP_SELF_IDENTIFICATION'
                                          AND rp_item = 'SNAPSHOT'
                                          AND rp_subitem BETWEEN '{$start1}' AND '{$end1}'
                                          AND user_id IN (".implode(",", $peopleIds).")
                                          ORDER BY rp_subitem DESC");
            $skipped = 0;
            $data = self::$fields;
            $alreadyDone = array();
            foreach($rows as $row){
                if(isset($alreadyDone[$row['user_id']])){
                    continue;
                }
                $alreadyDone[$row['user_id']] = true;
                $snapshot = json_decode(decrypt($row['data']), true);
                if($snapshot == null){
                    continue;
                }
                if(isset($snapshot['skip']) && $snapshot['skip'] != ''){
                    $skipped++;
                    continue;
                }
                foreach($data as $i => $options){
                    $found = false;
                    foreach($options['values'] as $j => $value){
                        if(!isset($snapshot[$i])){
                            continue;
                        }
                        if(!is_array($snapshot[$i]) && $snapshot[$i] == $value){
                            @$data[$i]['counts'][$j]++;
                            $found = true;
                            break;
                        }
                        else if(is_array($snapshot[$i])){
                            foreach($snapshot[$i] as $val){
                                if($val == $value){
                                    $found = true;
                                    @$data[$i]['counts'][$j]++;
                                }
                            }
                        }
                    }
                    if(!$found){
                        if(!isset($snapshot[$i])){
                            continue;
                        }
                        if(!is_array($snapshot[$i])){
                            if($snapshot[$i] != ""){
                                @$data[$i]['counts'][count($data[$i]['values'])]++;
                                $data[$i]['values'][] = $snapshot[$i];
                            }
                        }
                        else{
                            foreach($snapshot[$i] as $val){
                                if($snapshot[$i] != ""){
                                    @$data[$i]['counts'][count($data[$i]['values'])]++;
                                    $data[$i]['values'][] = $val;
                                }
                            }
                        }
                    }
                }
            }
            
            if($project == null && $theme == null){
                $html .= "<table>
                            <tr><td align='right'><b>Total:</b></td><td>".count($peopleIds)."</td></tr>
                            <tr><td align='right'><b>Submitted<sup>*</sup>:</b></td><td>".count($rows)."</td></tr>
                            <tr><td align='right'><b>Skipped:</b></td><td>{$skipped}</td></tr>
                          </table>
                          <small>*Submitted includes those who have skipped the survey</small>";
            }
            $html .= "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
            foreach($data as $key => $options){
                $html .= "<div style='width:24%;'>
                            <h3>{$options['label']}</h3>
                            <table class='wikitable'>";
                foreach($options['values'] as $j => $value){
                    $val = isset($data[$key]['counts'][$j]) ? $data[$key]['counts'][$j] : 0;
                    $html .= "<tr><td><b>{$value}</b></td><td align='right' style='min-width: 3em;'>{$val}</td></tr>";
                }
                $html .= "</table></div>";
            }
            $html .= "</div></div>";
        }
        $html .= "</div>";
        if($project == null && $theme == null){
            $html .= "<script type='text/javascript'>
                var interval{$year} = setInterval(function(){
                    if($('#accordion{$year}').is(':visible')){
                        $('#accordion{$year}').accordion({
                              collapsible: true
                        });
                        clearInterval(interval{$year});
                    };
                }, 100);
            </script>";
        }
        return $html;
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $this->html = self::getHTML($this->year);
    }
}
?>
