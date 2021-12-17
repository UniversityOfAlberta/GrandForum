<?php

    class ProjectMultimediaCell extends DashboardCell {
        
        function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
            $this->label = "Multimedia";
        
            $start = "0000";
            $end = "2100";
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
                $project = $table->obj;
                $person = Person::newFromName($params[2]);
                if($person != null && $person->getName() != null){
                    $this->obj = $person;
                    $multimedia = $project->getMultimedia();
                    $values = array();
                    foreach($multimedia as $media){
                        $year = $media->getDate();
                        if($year >= $start && $year <= $end){
                            $people = $media->getPeople();
                            foreach($people as $p){
                                if($p instanceof Person && $p->getId() == $person->getId()){
                                    $type = $media->getHumanReadableType();
                                    $values[$type][] = $media->getId();
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
                $multimedia = $project->getMultimedia();
                $values = array();
                foreach($multimedia as $media){
                    $year = substr($media->getDate(), 0, 4);
                    if($year >= $start && $year <= $end){
                        $values['All'][] = $media->getId();
                    }
                }
                $this->setValues($values);
            }
        }
        
        function toString(){
            return $this->value;
        }
        
        function rasterize(){
            return array(PROJECT_MULTIMEDIA, $this);
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
            $count = count($this->values[$type]);
            $name = ($this->obj != null) ? $this->obj->getId() : "All";
            $idType = ($type == "All") ? str_replace(" ", "_", $this->label) : str_replace(" ", "_", $type);
            $row = <<<EOF
<tr>
    <td style='$style1' align='right'>
        <a name='div_{$name}_{$idType}_{$this->label}_{$id}' class='details_div_lnk'>$type</a>:
    </td>
    <td style='$style2' align='right'>
        <span>$count</span><div style='display:none;' id='div_{$name}_{$idType}_{$this->label}_{$id}'>$details</div>
    </td>
</tr>
EOF;
            return $row;
        }
        
        protected function simpleDashboardRow($type){
            $count = count($this->values[$type]);
            return "$type: $count";
        }
        
        function getHeaders(){
            return array("Date", "Projects", "People", "Title");
        }
        
        function detailsRow($item){
            global $wgServer, $wgScriptPath;
            $media = Material::newFromId($item);
            $date = $media->getDate();
            $projects = $media->getProjects();
            $people = $media->getPeople();
            $projs = array();
            $peoples = array();
            foreach($projects as $project){
                if(!$project->isSubProject()){
                    $projs[] = "<a href='{$project->getUrl()}' target='_blank'>{$project->getName()}</a>";
                }
            }
            foreach($people as $p){
                $peoples[] = "<a href='{$p->getUrl()}' target='_blank'>{$p->getNameForForms()}</a>";
            }
            $details = "<td style='white-space:nowrap;text-align:center;' class='pdfnodisplay'>{$date} </td><td class='pdfnodisplay'>".implode(", ", $projs)."<br /></td><td>".implode(", ", $peoples)." </td><td><a href='{$media->getUrl()}' target='_blank'><i>{$media->getTitle()}</i></a><span class='pdfOnly'>, {$date}</span><div class='pdfOnly' style='width:50%;margin-left:50%;text-align:right;'><i>".implode(", ", $projs)."</i></div></td>";
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
                    $details .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:AddMultimediaPage\");' value='Add Multimedia' />\n";
                }
                $table .= $this->dashboardRow($type, $details);
            }
            if(count($this->values) == 0){
                $table .= "<tr><td style='text-align:right;border-width:0px;'>0</td></tr>\n";
            }
            $table .= "</table>\n";
            return "$table";
        }
        
    }
    
?>
