<?php

abstract class DashboardCell extends Cell {

    static $id = 0;
    
    var $label = "";
    var $obj;
    var $values = array();
    
    // Returns an array of headers for the details table
    abstract function getHeaders();
    
    // Returns a string representing a row in the details table
    abstract function detailsRow($item);
    
    function setValues($values){
        $this->values = $values;
        $types = array();
        $simples = array();
        foreach($values as $type => $value){
            @$types[] = $type.';'.implode(';', $value);
            $simples[] = $this->simpleDashboardRow($type);
        }
        $this->value = implode("\n", $simples);
    }
    
    // Returns the start of the details table, using the array of headers
    function initDetailsTable($type, $headers){
        $extra = ($type == "All") ? "" : ' / '.$type;
        $name = ($this->obj != null) ? $this->obj->getName() : "All";
        if($this->obj instanceof Person){
            $name = $this->obj->getNameForForms();
        }
        
        $details = "<p><span class='label'>{$name} {$this->label}$extra:</span></p>";
        $details .= "<table style='background:#ffffff;border-color:#aaa;' cellspacing='1' cellpadding='3' frame='box' rules='all'><thead>\n";
        $details .= "<tr>\n";
        foreach($headers as $header){
            $details .= "<th>$header</th>";
        }
        $details .= "</tr></thead><tbody>\n";
        return $details;
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
        $count = count($this->values[$type]);
        $name = ($this->obj != null) ? str_replace(".", "", $this->obj->getName()) : "All";
        $idType = ($type == "All") ? str_replace("(", "", str_replace(")", "", str_replace("/", "", str_replace(".", "", str_replace(" ", "_", $this->label))))) : 
                                     str_replace("(", "", str_replace(")", "", str_replace("/", "", str_replace(".", "", str_replace(" ", "_", $type)))));
        $details_id = str_replace("\"", "'", str_replace("/", "", "div_{$name}_{$idType}_{$this->label}_{$id}"));
        $type = trim(str_replace("Misc:", "", $type));
        if($type == ""){
            $type = "Misc";
        }
        $row = <<<EOF
<tr>
<td style='$style1' align='right'><a name='$details_id' class='details_div_lnk'>$type</a>:</td>
<td style='$style2' align='right'><span>$count</span><div style='display:none;margin-bottom:15px;' id='$details_id'>$details</div></td>
</tr>
EOF;
        return $row;
    }
    
    // Creates a simplified dashboard row, used for storing in the values field.
    // This way doing select, where etc. queries feel more natural
    protected function simpleDashboardRow($type){
        $count = count($this->values[$type]);
        return "$type: $count";
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        $table = "<table width='100%'>\n";
        foreach($this->values as $type => $values){
            $details = "";
            if(!isset($_GET['generatePDF']) && !isset($_GET['evalPDF'])){
                $details = $this->initDetailsTable($type, $this->getHeaders());
                foreach($values as $item){
                    $details .= "<tr>".$this->detailsRow($item)."</tr>\n";
                }
                $details .= "</tbody></table><br /><br />\n";
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
