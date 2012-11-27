<?php

    class ProjectContributionsCell extends DashboardCell {
        
        function ProjectContributionsCell($cellType, $params, $cellValue, $rowN, $colN, $table){
            $this->label = "Contributions";
        
            $start = "0000";
            $end = "2100";
            if(count($params) == 1){
                $params[2] = $params[0];
            }
            else{
                if(isset($params[0])){
                    // Start
                    $start = substr($params[0], 0, 4);
                }
                if(isset($params[1])){
                    // End
                    $end = substr($params[1], 0, 4);
                }
            }
            if(isset($params[2])){
                $project = $table->obj;
                $person = Person::newFromId($params[2]);
                if($person != null && $person->getName() != null){
                    $this->obj = $person;
                    $contributions = $project->getContributions();
                    $values = array();
                    foreach($contributions as $contribution){
                        if($contribution->getYear() >= $start && $contribution->getYear() <= $end){
                            $people = $contribution->getPeople();
                            foreach($people as $p){
                                if($p instanceof Person && $p->getId() == $person->getId()){
                                    $type = $contribution->getHumanReadableType();
                                    $values[$type][] = $contribution->getId();
                                    break;
                                }
                            }
                        }
                    }
                    $this->setValues($values);
                }
            }
            else{
                $project = $table->obj;
                $contributions = $project->getContributions();
                $values = array();
                foreach($contributions as $contribution){
                    if($contribution->getYear() >= $start && $contribution->getYear() <= $end){
                        $values['All'][] = $contribution->getId();
                    }
                }
                $this->setValues($values);
            }
        }
        
        function toString(){
            return $this->value;
        }
        
        function rasterize(){
            return array(PROJECT_CONTRIBUTIONS, $this);
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
            foreach($this->values[$type] as $value){
                $contribution = Contribution::newFromId($value);
                $count += $contribution->getTotal();
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
            foreach($this->values[$type] as $value){
                $contribution = Contribution::newFromId($value);
                $count += $contribution->getTotal();
            }
            $count = number_format($count);
            return "$type: \$$count";
        }
        
        function getHeaders(){
            return array("Year of Contribution", "Total", "Projects", "Partners", "Contribution");
        }
        
        function detailsRow($item){
            global $wgServer, $wgScriptPath;
            $contribution = Contribution::newFromId($item);
            $date = $contribution->getYear();
            $projects = $contribution->getProjects();
            $partners = $contribution->getPartners();
            $projs = array();
            $parts = array();
            foreach($projects as $project){
                $projs[] = "<a href='{$project->getUrl()}' target='_blank'>{$project->getName()}</a>";
            }
            foreach($partners as $partner){
                $parts[] = $partner->getOrganization();
            }
            $details = "<td style='white-space:nowrap;text-align:center;'>{$date} </td><td style='text-align:right;'>\$".number_format($contribution->getTotal())." </td><td>".implode(", ", $projs)."<br /></td><td>".implode(", ", $parts)."</td><td> <a href='{$contribution->getUrl()}' target='_blank'><i>{$contribution->getName()}</i></a><br /></td>";
            return $details;
        }
        
        function render(){
            global $wgServer, $wgScriptPath;
            $table = "<table width='100%'>\n";
            foreach($this->values as $type => $values){
                $details = "";
                if(!isset($_GET['generatePDF']) && !isset($_GET['evalPDF'])){
                    $details = $this->initDetailsTable($type, $this->getHeaders());
                    foreach($values as $item){
                        $details .= '<tr>'.$this->detailsRow($item)."</tr>\n";
                    }
                    $details .= "</tbody></table><br /><br />\n";
                    $details .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:AddContributionPage\");' value='Add Contribution' />\n";
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
