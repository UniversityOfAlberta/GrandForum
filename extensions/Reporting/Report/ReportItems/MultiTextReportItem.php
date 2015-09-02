<?php

class MultiTextReportItem extends AbstractReportItem {
    
    function getIndices($labels){
        $indices = array();
        foreach($labels as $label){
            $index = strtolower($label);
            $index = strip_tags($index);
            $index = str_replace("-", "", $index);
            $index = str_replace(" ", "", $index);
            $index = str_replace(".", "", $index);
            $index = str_replace("'", "", $index);
            $index = str_replace("(", "", $index);
            $index = str_replace(")", "", $index);
            $index = str_replace("[", "", $index);
            $index = str_replace("]", "", $index);
            $index = str_replace(",", "", $index);
            if($index == ""){
                $index = "_";
            }
            $indices[] = $index;
        }
        return $indices;
    }
    
    function getNComplete(){
        $opt = $this->getAttr('optional', '0');
        if($opt == '1' || $opt == 'true'){
            return 0;
        }
        $values = $this->getBlobValue();
        if(isset($values[-1])){
            unset($values[-1]);
        }
        if(count($values) > 0){
            return 1;
        }
        return 0;
    }
    
    function getNFields(){
        $opt = $this->getAttr('optional', '0');
        if($opt == '1' || $opt == 'true'){
            return 0;
        }
        return 1;
    }
    
    function render(){
        global $wgOut;
        $multiple = (strtolower($this->getAttr('multiple', 'false')) == 'true');
        $maxEntries = $this->getAttr('max', 100);
        $labels = explode("|", $this->getAttr('labels', ''));
        $types = explode("|", $this->getAttr('types', ''));
        $indices = $this->getIndices($labels);
        $sizes = explode("|", $this->getAttr('sizes', ''));
        $class = $this->getAttr('class', 'wikitable');
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
                            $people = Person::getAllPeople(NI);
                            foreach($people as $person){
                                $names[$person->getNameForForms()] = $person->getNameForForms();
                            }
                            asort($names);
                            $combobox = new ComboBox("{$this->getPostId()}[\" + i + \"][$index]", "Project Leader", '', $names);
                            $item .= "\"<td><span>".$combobox->renderSelect()."</span></td>\" + \n";
                        }
                        else if(strtolower(@$types[$j]) == "integer"){
                            $item .= @"\"<td><input type='text' class='numeric' name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;' value='' /></td>\" + \n";
                        }
                        else if(strtolower(@$types[$j]) == "textarea"){
                            $item .= @"\"<td><textarea name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;height:60px;'></textarea></td>\" + \n";
                        }
                        else if(strstr(strtolower(@$types[$j]), "select") !== false){
                            $item .= @"\"<td align='center'><select class='raw' name='{$this->getPostId()}[\" + i + \"][$index]'>";
                            $matches = array();
                            preg_match("/.*\((.*)\)/", $types[$j], $matches);
                            $matches = @explode(",", $matches[1]);
                            foreach($matches as $match){
                                $match = trim($match);
                                $item .= "<option>{$match}</option>";
                            }
                            $item .= "</select></td>\" + \n";
                        }
                        else{
                            $item .= @"\"<td><input type='text' name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;' value='' /></td>\" + \n";
                        }
                    }
        $item .= <<<EOF
                        "<td><button type='button' onClick='removeObj{$this->getPostId()}(this);'>-</button></td>" +
                    "</tr>");
                $("#table_{$this->getPostId()} tr.obj:last select:not(.raw)").combobox();
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
                if($("#table_{$this->getPostId()} tr.obj").length == 0 && "$class" != "wikitable"){
                    $("#table_{$this->getPostId()}").hide();
                }
                else{
                    $("#table_{$this->getPostId()}").show();
                }
                $("input.numeric").forceNumeric({min: 0, max: 9999999999999999});
            }
        </script>
        <input type='hidden' name='{$this->getPostId()}[-1]' value='' />
EOF;
        $item .= "<table id='table_{$this->getPostId()}' class='$class'>";
        if(count($labels) > 0 && $labels[0] != ""){
            $item .= "<tr>";
            foreach($labels as $j => $label){
                $item .= @"<th style='width:{$sizes[$j]}px;'>{$label}</th>";
            }
            $item .= "<th style='width:51px;'></th></tr>";
        }
        $i = 0;
        foreach($values as $i => $value){
            if($i > -1){
                $item .= "<tr class='obj'>";
                foreach($indices as $j => $index){
                    if(@$types[$j] == "NI"){
                        $names = array("");
                        $people = Person::getAllPeople(NI);
                        foreach($people as $person){
                            $names[$person->getNameForForms()] = $person->getNameForForms();
                        }
                        asort($names);
                        $combobox = new ComboBox("{$this->getPostId()}[$i][$index]", "Project Leader", $value[$index], $names);
                        $item .= "<td>".$combobox->render()."</td>";
                    }
                    else if(strtolower(@$types[$j]) == "integer"){
                        $item .= @"<td><input type='text' class='numeric' name='{$this->getPostId()}[$i][$index]' style='width:{$sizes[$j]}px;' value='{$value[$index]}' /></td>";
                    }
                    else if(strtolower(@$types[$j]) == "textarea"){
                        $item .= @"<td><textarea name='{$this->getPostId()}[$i][$index]' style='width:{$sizes[$j]}px;height:65px;'>{$value[$index]}</textarea></td>";
                    }
                    else if(strstr(strtolower(@$types[$j]), "select") !== false){
                        $item .= @"<td align='center'><select class='raw' name='{$this->getPostId()}[$i][$index]'>";
                        $matches = array();
                        preg_match("/.*\((.*)\)/", $types[$j], $matches);
                        $matches = @explode(",", $matches[1]);
                        foreach($matches as $match){
                            $match = trim($match);
                            if($match == $value[$index]){
                                $item .= "<option selected>{$match}</option>";
                            }
                            else{
                                $item .= "<option>{$match}</option>";
                            }
                        }
                        $item .= "</select></td>";
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
        if($multiple){
            $item .= "<tfoot><tr><td colspan='".(count($indices)+1)."'>";
            $item .= "<button id='add_{$this->getPostId()}' onClick='addObj{$this->getPostId()}(max{$this->getPostId()});' type='button'>+</button>";
            $item .= "<script type='text/javascript'>
                updateTable{$this->getPostId()}();
            </script>";
            $item .= "</td></tr></tfoot>";
        }
        $item .= "</table>";
        $item = $this->processCData($item);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut;
        $multiple = (strtolower($this->getAttr('multiple', 'false')) == 'true');
        $maxEntries = $this->getAttr('max', 100);
        $labels = explode("|", $this->getAttr('labels', ''));
        $sizes = explode("|", $this->getAttr('sizes', ''));
        $showHeader = $this->getAttr('showHeader', 'true');
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
                $item = "<table id='table_{$this->getPostId()}' cellspacing='1' style='border: none;' width='100%'>";
                if(strtolower($showHeader) == 'true'){
                    $item .= " <tr>";
                    foreach($labels as $label){
                        $item .= "<th align='center'>{$label}</th>";
                    }
                    $item .= "</tr>";
                }
            }
            else{
                $item = "<table>";
            }
            $i = 0;
            $count = 0;
            foreach($values as $i => $value){
                if($i > -1 && $count < $maxEntries){
                    $item .= "<tr class='obj'>";
                    foreach($indices as $j => $index){
                        $size = (isset($sizes[$j])) ? "width:{$sizes[$j]};" : "";
                        $item .= "<td valign='top' style='padding:0 3px 0 3px; {$size}'>{$value[$index]}</td>";
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
