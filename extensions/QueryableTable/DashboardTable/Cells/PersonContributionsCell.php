<?php

    class PersonContributionsCell extends DashboardCell {
        
        function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
            $this->label = "Contributions";
        
            $start = "0000-00-00 00:00:00";
            $end = "2100-00-00 00:00:00";
            if(count($params) == 1){
                $params[2] = $params[0];
            }
            else{
                if(isset($params[0])){
                    // Start
                    $start = $params[0];
                }
                if(isset($params[1])){
                    // End
                    $end = $params[1];
                }
            }
            if(isset($params[2])){
                $project = Project::newFromName($params[2]);
                if($project != null && $project->getName() != null){
                    $this->obj = $project;
                    $person = $table->obj;
                    $contributions = $person->getContributions();
                    $values = array();
                    foreach($contributions as $contribution){
                        if($contribution->belongsToProject($project) && $contribution->getEndDate() >= $start && $contribution->getStartDate() <= $end){
                            foreach($contribution->getPartners() as $partner){
                                $type = $contribution->getHumanReadableTypeFor($partner);
                                $values[$type][$partner->getOrganization()][] = $contribution->getId();
                            }
                        }
                    }
                    $this->setValues($values);
                }
            }
            else{
                $person = $table->obj;
                $contributions = $person->getContributions();
                $values = array();
                foreach($contributions as $contribution){
                    if($contribution->getEndDate() >= $start && $contribution->getStartDate() <= $end){
                        $values['All'][""][] = $contribution->getId();
                    }
                }
                $this->setValues($values);
            }
        }
        
        function toString(){
            return $this->value;
        }
        
        function rasterize(){
            return array(PERSON_CONTRIBUTIONS, $this);
        }
        
        // Creates a row in the cell table
        protected function dashboardRow($type, $details){
            $id = DashboardCell::$id++;
            $style1 = "width:100%;border-width:0px;";
            $style2 = "border-width:0px;";
            if($type == "All"){
                $style1 = "width:100%;border-width:0px;";
                $style2 = "border-width:0px;";
            }
            $extra = ($type == "All") ? "" : ' / '.$type;
            $count = 0;
            foreach($this->values[$type] as $partnerOrg => $values){
                foreach($values as $value){
                    $partner = null;
                    if($partnerOrg != ""){
                        $partner = Partner::newFromName($partnerOrg);
                        $partner->organization = $partnerOrg;
                    }
                    $contribution = Contribution::newFromId($value);
                    $count += $contribution->getByType($type, $partner);
                }
            }
            $count = number_format($count);
            $name = ($this->obj != null) ? $this->obj->getId() : "All";
            $idType = ($type == "All") ? str_replace(" ", "_", $this->label) : str_replace(" ", "_", $type);
            $row = <<<EOF
<tr>
    <td style='$style1' align='right'>
        <a name='div_{$name}_{$idType}_{$this->label}_{$id}' class='details_div_lnk'>$type</a>:
    </td>
    <td style='$style2' align='right'>
        <span>\$$count</span><div style='display:none;' id='div_{$name}_{$idType}_{$this->label}_{$id}'>$details</div>
    </td>
</tr>
EOF;
            return $row;
        }
        
        protected function simpleDashboardRow($type){
            $count = 0;
            foreach($this->values[$type] as $partnerId => $values){
                foreach($values as $value){
                    $contribution = Contribution::newFromId($value);
                    $count += $contribution->getByType($type);
                }
            }
            $count = number_format($count);
            return "$type: \$$count";
        }
        
        function getHeaders(){
            return array("Year of Contribution", "Total", "Projects", "Partners", "Contribution");
        }
        
        function detailsRow($item){
            return $this->detailsRowWithType($item, "All", null);
        }
        
        function detailsRowWithType($item, $type, $partner){
            global $wgServer, $wgScriptPath;
            $contribution = Contribution::newFromId($item);
            $date = $contribution->getStartYear();
            $projects = $contribution->getProjects();
            $partners = $contribution->getPartners();
            $projs = array();
            $parts = array();
            $amounts = array();
            $types = array();
            foreach($projects as $project){
                if(!$project->isSubProject()){
                    $projs[] = "<a href='{$project->getUrl()}' target='_blank'>{$project->getName()}</a>";
                }
            }
            $amount = 0;
            foreach($partners as $part){
                if($type == "All" || $part->getOrganization() == $partner->getOrganization()){
                    $parts[] = $part->getOrganization();
                    $amt = number_format($contribution->getByType($contribution->getTypeFor($part), $part));
                    $amounts[] = "\$$amt";
                    $types[] = "&nbsp;({$contribution->getHumanReadableTypeFor($part)})";
                    $amount += $contribution->getByType($type, $partner);
                }
            }
            if($type == "All"){
                $amount = $contribution->getTotal();
            }
            $amount = number_format($amount);
            $details = "<td style='white-space:nowrap;text-align:center;' class='pdfnodisplay'>{$date} </td><td class='pdfnodisplay' style='text-align:right;'>\${$amount} </td><td class='pdfnodisplay'>".implode(", ", $projs)."<br /></td><td class='pdfnodisplay'>".implode(", ", $parts)."</td><td> <a href='{$contribution->getUrl()}' target='_blank'><i>{$contribution->getName()}</i></a><span class='pdfOnly'>, {$date}</span><div class='pdfOnly'><div style='display:inline-block;width: 17.5%;margin-left:2.5%;vertical-align:top;'>".implode("<br />", $parts)."</div><div style='display:inline-block;width: 10%;vertical-align:top;text-align:right;'>".implode("<br />", $amounts)."<br /><b>\${$amount}</b></div><div style='display:inline-block;vertical-align:top;width:20%;'>".implode("<br />", $types)."</div><div class='pdfOnly' style='width:50%;text-align:right;display:inline-block;'><i>".implode(", ", $projs)."</i></div></div></td>";
            return $details;
        }
        
        function render(){
            global $wgServer, $wgScriptPath;
            $table = "<table width='100%'>\n";
            foreach($this->values as $type => $values){
                $details = "";
                if(!isset($_GET['generatePDF']) && !isset($_GET['evalPDF'])){
                    $details = $this->initDetailsTable($type, $this->getHeaders());
                    foreach($values as $partnerOrg => $items){
                        $partner = null;
                        if($partnerOrg != ""){
                            $partner = Partner::newFromName($partnerOrg);
                            $partner->organization = $partnerOrg;
                        }
                        foreach($items as $item){
                            $details .= '<tr>'.$this->detailsRowWithType($item, $type, $partner)."</tr>\n";
                        }
                    }
                    $details .= "</tbody></table><br /><br />\n";
                    $details .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:Contributions\");' value='Manage Contributions' />\n";
                }
                $table .= $this->dashboardRow($type, $details);
            }
            if(count($this->values) == 0){
                $table .= "<tr><td style='text-align:right;border-width:0px;'>$0</td></tr>\n";
            }
            $table .= "</table>\n";
            return "$table";
        }
        
    }
    
?>
