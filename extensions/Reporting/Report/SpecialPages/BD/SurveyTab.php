<?php

class SurveyTab extends AbstractTab {

    static $fields = array("age"          => array("label"  => "Age", 
                                                   "values" => array()), 
                           "gender"       => array("label"  => "Gender", 
                                                   "values" => array("Gender fluid",
                                                                     "Non-binary",
                                                                     "Two-spirit",
                                                                     "Trans man",
                                                                     "Trans woman",
                                                                     "Man",
                                                                     "Woman",
                                                                     "I do not identify with any option")), 
                           "sex"          => array("label"  => "Sexual orientation", 
                                                   "values" => array("Asexual",
                                                                     "Bisexual",
                                                                     "Pansexual",
                                                                     "Heterosexual",
                                                                     "Two-spirit",
                                                                     "Gay",
                                                                     "Lesbian",
                                                                     "Queer",
                                                                     "I do not identify with the options provided")), 
                           "identity"     => array("label"  => "Indigenous identity", 
                                                   "values" => array("Yes",
                                                                     "No")), 
                           "minority"     => array("label"  => "Visible minority/Racialized individual",
                                                   "values" => array("Yes",
                                                                     "No")),
                           "race"         => array("label"  => "Race or population group",
                                                   "values" => array("Black",
                                                                     "East Asian",
                                                                     "Indigenous",
                                                                     "Latin American",
                                                                     "South Asian",
                                                                     "Southeast Asian",
                                                                     "West Asian",
                                                                     "White",
                                                                     "Population group not listed/Other")), 
                           "immigrant"    => array("label"  => "Citizenship status",
                                                   "values" => array("Yes",
                                                                     "No")),
                           "disabilities" => array("label"  => "Disabilities",
                                                   "values" => array("Yes",
                                                                     "No")),
                           "language"     => array("label"  => "Language",
                                                   "values" => array("English",
                                                                     "French",
                                                                     "Another language")),
                           "education"    => array("label" => "University education",
                                                   "values" => array("Yes",
                                                                     "No"))
    );

    var $year;

    function __construct($year){
        $this->year = $year;
        parent::__construct(($year-1)."/".($year));
    }
    
    static function getHTML($year="", $project=null, $theme=null){
        if($year != ""){
            $start = "{$year}-04";
            $end = ($year+1)."-03";
        }
        else{
            $start = "1900-01";
            $end = "9999-01";
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
                                          AND rp_subitem BETWEEN '{$start}' AND '{$end}'
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
                if($snapshot['skip'] != ''){
                    $skipped++;
                    continue;
                }
                foreach($data as $i => $options){
                    $found = false;
                    if($i == 'age' && $snapshot[$i] != '' && is_numeric($snapshot[$i])){
                        $age = $year - $snapshot[$i];
                        @$data[$i]['counts'][$age]++;
                        $found = true;
                    }
                    foreach($options['values'] as $j => $value){
                        if($snapshot[$i] == $value){
                            @$data[$i]['counts'][$j]++;
                            $found = true;
                            break;
                        }
                    }
                    if(!$found){
                        @$data[$i]['counts']['none']++;
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
            foreach(self::$fields as $key => $options){
                $html .= "<div style='width:24%;'>
                            <h3>{$options['label']}</h3>
                            <table class='wikitable'>";
                if($key == 'age' && isset($data[$key]['counts']) && is_array($data[$key]['counts'])){
                    ksort($data[$key]['counts']);
                    foreach($data[$key]['counts'] as $j => $count){
                        $html .= "<tr><td><b>{$j}</b></td><td align='right' style='min-width: 3em;'>{$count}</td></tr>";
                    }
                }
                else{
                    foreach($options['values'] as $j => $value){
                        $val = isset($data[$key]['counts'][$j]) ? $data[$key]['counts'][$j] : 0;
                        $html .= "<tr><td><b>{$value}</b></td><td align='right' style='min-width: 3em;'>{$val}</td></tr>";
                    }
                }
                //$val = isset($data[$key]['counts']['none']) ? $data[$key]['counts']['none'] : 0;
                //$html .= "<tr><td><b>No Answer</b></td><td align='right' style='min-width: 3em;'>{$val}</td></tr>";
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
