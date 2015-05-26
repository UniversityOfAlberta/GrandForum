<?php

class MultiTextReportItem extends AbstractReportItem {
    
    function getIndices($labels){
        $indices = array();
        foreach($labels as $label){
            $index = strtolower($label);
            $index = str_replace("-", "", $index);
            $index = str_replace(" ", "", $index);
            $index = str_replace(".", "", $index);
            $index = str_replace("'", "", $index);
            if($index == ""){
                $index = "_";
            }
            $indices[] = $index;
        }
        return $indices;
    }
    
    function render(){
        global $wgOut;
        $multiple = (strtolower($this->getAttr('multiple', 'false')) == 'true');
        $maxEntries = $this->getAttr('max', 100);
        $labels = explode("|", $this->getAttr('labels', ''));
        $types = explode("|", $this->getAttr('types', ''));
        $indices = $this->getIndices($labels);
        $sizes = explode("|", $this->getAttr('sizes', ''));
        $values = $this->getBlobValue();
        if($values == null){
            $values = array();
            $max = -1;
        }
        else{
            $max = max(array_keys($values));
        }
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
        $item = <<<EOF
        <script type='text/javascript'>
            var max{$this->getPostId()} = {$max}+1;
            function addObj{$this->getPostId()}(i){
                $("#table_{$this->getPostId()}").append(
                    "<tr class='obj'>" +
EOF;
                    foreach($indices as $j => $index){
                        if(@$types[$j] == "NI"){
                            $names = array("");
                            $people = array_merge(Person::getAllPeople(PNI), Person::getAllPeople(CNI));
                            foreach($people as $person){
                                $names[$person->getNameForForms()] = $person->getNameForForms();
                            }
                            asort($names);
                            $combobox = new ComboBox("{$this->getPostId()}[\" + i + \"][$index]", "Project Leader", '', $names);
                            $item .= "\"<td><span>".$combobox->renderSelect()."</span></td>\" + \n";
                        }
                        else{
                            $item .= @"\"<td><input type='text' name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;' value='' /></td>\" + \n";
                        }
                    }
        $item .= <<<EOF
                        "<td><button type='button' onClick='removeObj{$this->getPostId()}(this);'>-</button></td>" +
                    "</tr>");
                $("#table_{$this->getPostId()} tr.obj:last select").combobox();
                max{$this->getPostId()}++;
                updateTable{$this->getPostId()}();
            }
            
            function removeObj{$this->getPostId()}(obj){
                $(obj).parent().parent().remove();
                updateTable{$this->getPostId()}();
            }
            
            function updateTable{$this->getPostId()}(){
                if($("#table_{$this->getPostId()} tr.obj").length >= {$maxEntries}){
                    $("#add_{$this->getPostId()}").prop('disabled', true);
                }
                else{
                    $("#add_{$this->getPostId()}").prop('disabled', false);
                }
                if($("#table_{$this->getPostId()} tr.obj").length == 0){
                    $("#table_{$this->getPostId()}").hide();
                }
                else{
                    $("#table_{$this->getPostId()}").show();
                }
            }
        </script>
        <input type='hidden' name='{$this->getPostId()}[-1]' value='' />
EOF;
        $item .= "<table id='table_{$this->getPostId()}' cellpadding='0' cellspacing='0'>";
        if(count($labels) > 0 && $labels[0] != ""){
            $item .= "<tr>";
            foreach($labels as $label){
                $item .= "<th>{$label}</th>";
            }
            $item .= "<th></th></tr>";
        }
        $i = 0;
        foreach($values as $i => $value){
            if($i > -1){
                $item .= "<tr class='obj'>";
                foreach($indices as $j => $index){
                    if(@$types[$j] == "NI"){
                        $names = array("");
                        $people = array_merge(Person::getAllPeople(PNI), Person::getAllPeople(CNI));
                        foreach($people as $person){
                            $names[$person->getNameForForms()] = $person->getNameForForms();
                        }
                        asort($names);
                        $combobox = new ComboBox("{$this->getPostId()}[$i][$index]", "Project Leader", $value[$index], $names);
                        $item .= "<td>".$combobox->render()."</td>";
                    }
                    else{
                        $item .= @"<td><input type='text' name='{$this->getPostId()}[$i][$index]' value='{$value[$index]}' style='width:{$sizes[$j]}px;' /></td>";
                    }
                }
                if($multiple){
                    $item .= "<td><button type='button' onClick='removeObj{$this->getPostId()}(this);'>-</button></td>";
                }
                $item .= "</tr>";
            }
        }
        if(!$multiple && count($values) == 0){
            $item .= "<tr class='obj'>";
            foreach($indices as $index){
                $item .= "<td><input type='text' name='{$this->getPostId()}[0][$index]' value='' /></td>";
            }
            $item .= "</tr>";
        }
        
        $item .= "</table>";
        if($multiple){
            $item .= "<button id='add_{$this->getPostId()}' onClick='addObj{$this->getPostId()}(max{$this->getPostId()});' type='button'>+</button>";
            $item .= "<script type='text/javascript'>
                updateTable{$this->getPostId()}();
            </script>";
        }
        $item = $this->processCData($item);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut;
        $multiple = (strtolower($this->getAttr('multiple', 'false')) == 'true');
        $maxEntries = $this->getAttr('max', 100);
        $labels = explode("|", $this->getAttr('labels', ''));
        $indices = $this->getIndices($labels);
        $values = $this->getBlobValue();
        if($values == null){
            $values = array();
            $max = -1;
        }
        else{
            $max = max(array_keys($values));
        }
        $item = "";
        if($max > -1){
            if(count($labels) > 0 && $labels[0] != ""){
                $item = "<table id='table_{$this->getPostId()}' cellspacing='1' cellpadding='3' style='border: none;' frame='box' rules='all' width='100%'>";
                $item .= " <tr>";
                foreach($labels as $label){
                    $item .= "<th>{$label}</th>";
                }
                $item .= "</tr>";
            }
            else{
                $item = "<table>";
            }
            $i = 0;
            $count = 0;
            foreach($values as $i => $value){
                if($i > -1 && $count < $maxEntries){
                    $item .= "<tr class='obj'>";
                    foreach($indices as $index){
                        $item .= "<td>{$value[$index]}</td>";
                    }
                    $item .= "</tr>";
                    $count++;
                }
            }
            $item .= "</table><br />";
        }
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
