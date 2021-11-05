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
        $minEntries = $this->getAttr('min', 0);
        $maxEntries = $this->getAttr('max', 100);
        $labels = explode("|", $this->getAttr('labels', ''));
        $types = explode("|", $this->getAttr('types', ''));
        $indices = $this->getIndices($labels);
        $sizes = explode("|", $this->getAttr('sizes', ''));
        $class = $this->getAttr('class', 'wikitable');
        $orientation = $this->getAttr('orientation', 'horizontal');
        $isVertical = (strtolower($orientation) == 'vertical');
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
                    "<tr id='obj" + i + "' class='obj'>" +
EOF;
                    foreach($indices as $j => $index){
                        $align = "";
                        if($isVertical){
                            $item .= "\"<tr id='\" + i + \"'><td align='right'><b>{$labels[$j]}:</b></td>\" + \n";
                            $align = "left";
                        }
                        if(@$types[$j] == "NI"){
                            $names = array("");
                            $people = Person::getAllPeople(NI);
                            foreach($people as $person){
                                $names[$person->getNameForForms()] = $person->getNameForForms();
                            }
                            asort($names);
                            $combobox = new ComboBox("{$this->getPostId()}[\" + i + \"][$index]", "Project Leader", '', $names);
                            $item .= "\"<td align='$align'><span>".$combobox->renderSelect()."</span></td>\" + \n";
                        }
                        else if(strtolower(@$types[$j]) == "random"){
                            $item .= @"\"<td align='$align' style='display:none;'><input type='text' class='numeric' name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;' value='\" + _.random(1000000000) + \"' /></td>\" + \n";
                        }
                        else if(strtolower(@$types[$j]) == "integer"){
                            $item .= @"\"<td align='$align'><input type='text' class='numeric' name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;' value='' /></td>\" + \n";
                        }
                        else if(strtolower(@$types[$j]) == "checkbox"){
                            $item .= @"\"<td align='center'><input type='checkbox' name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;' value='1' /></td>\" + \n";
                        }
                        else if(strtolower(@$types[$j]) == "textarea"){
                            $item .= @"\"<td align='$align'><textarea name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;min-height:60px;height:100%;'></textarea></td>\" + \n";
                        }
                        else if(strstr(strtolower(@$types[$j]), "checkbox") !== false){
                            if(!$isVertical){
                                $align = "left";
                            }
                            $item .= @"\"<td align='$align'>";
                            $matches = array();
                            preg_match("/^(Checkbox)\((.*)\)$/i", $types[$j], $matches);
                            $matches = @explode(",", $matches[2]);
                            foreach($matches as $match){
                                $match = trim($match);
                                $item .= "<div><input type='checkbox' name='{$this->getPostId()}[\" + i + \"][$index][]' value='{$match}'> {$match}</div>";
                            }
                            $item .= "</td>\" + \n";
                        }
                        else if(strstr(strtolower(@$types[$j]), "radio") !== false){
                            if(!$isVertical){
                                $align = "left";
                            }
                            $item .= @"\"<td align='$align'>";
                            $matches = array();
                            preg_match("/^(Radio)\((.*)\)$/i", $types[$j], $matches);
                            $matches = @explode(",", $matches[2]);
                            foreach($matches as $match){
                                $match = trim($match);
                                $item .= "<div><input type='radio' name='{$this->getPostId()}[\" + i + \"][$index]' value='{$match}'> {$match}</div>";
                            }
                            $item .= "</td>\" + \n";
                        }
                        else if(strstr(strtolower(@$types[$j]), "select") !== false || 
                                strstr(strtolower(@$types[$j]), "combobox") !== false){
                            if(!$isVertical){
                                $align = "center";
                            }
                            $cls = (strstr(strtolower(@$types[$j]), "select") !== false) ? "raw" : "";
                            $item .= @"\"<td align='$align'><select style='max-width:{$sizes[$j]}px' class='{$cls}' name='{$this->getPostId()}[\" + i + \"][$index]'>";
                            $matches = array();
                            preg_match("/^(Select|ComboBox)\((.*)\)$/i", $types[$j], $matches);
                            $matches = @explode(",", $matches[2]);
                            if(array_search(@$value[$index], $matches) === false && @$value[$index] != ""){
                                $item .= @"<option selected>{$value[$index]}</option>";
                            }
                            foreach($matches as $match){
                                $match = trim($match);
                                $item .= "<option>{$match}</option>";
                            }
                            $item .= "</select></td>\" + \n";
                        }
                        else if(strstr(strtolower(@$types[$j]), "date") !== false){
                            preg_match("/^(Date)\((.*)\)$/i", $types[$j], $matches);
                            $dateFormat = (isset($matches[2])) ? $matches[2] : "yy-mm-dd";
                            $item .= @"\"<td align='$align'><input type='text' class='calendar' data-dateFormat='{$dateFormat}' name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;' value='' /></td>\" + \n";
                        }
                        else if(strstr(strtolower(@$types[$j]), "getarray") !== false){
                            $item .= @"\"<td align='$align'></td>\" + \n";
                        }
                        else{
                            $item .= @"\"<td align='$align'><input type='text' name='{$this->getPostId()}[\" + i + \"][$index]' style='width:{$sizes[$j]}px;' value='' /></td>\" + \n";
                        }
                        if($isVertical){
                            $item .= "\"</tr>\" + \n";
                        }
                    }
                    $colspan = 1;
                    if($isVertical){
                        $item .= "\"<tr id='obj\" + i + \"'>\" + \n";
                        $colspan = 2;
                    }
                    $item .= <<<EOF
                        "<td colspan='$colspan' align='center'><span class='delete-icon' onClick='removeObj{$this->getPostId()}(this);'></span><br /><span class='move-icon' style='margin-top:10px;'></span></td></tr>"
EOF;
                    if($isVertical){
                        $item .= "+ \"<tr id='obj\" + i + \"'><td colspan='$colspan' style='background:#CCCCCC;'></td></tr>\"";
                    }
                    $item .= <<<EOF
                    );
                $("#table_{$this->getPostId()} tr.obj:last select:not(.raw)").combobox();
                max{$this->getPostId()}++;
                updateTable{$this->getPostId()}();
            }
            
            function removeObj{$this->getPostId()}(obj){
                var id = $(obj).parent().parent().attr('id');
                $('tr#' + id, $(obj).parent().parent().parent()).remove();
                updateTable{$this->getPostId()}();
            }
            
            function updateTable{$this->getPostId()}(){
                if($("#table_{$this->getPostId()} tr.obj").length >= {$maxEntries}){
                    $("#add_{$this->getPostId()}").prop('disabled', true);
                }
                else{
                    $("#add_{$this->getPostId()}").prop('disabled', false);
                }
                if($("#table_{$this->getPostId()} tr.obj").length < {$minEntries}){
                    $(".table_{$this->getPostId()}").show();
                    $(".table_{$this->getPostId()}").html('You must include at least {$minEntries} entries.');
                }
                else{
                    $(".table_{$this->getPostId()}").hide();
                }
                if($("#table_{$this->getPostId()} tr.obj").length == 0 && "$class" != "wikitable"){
                    $("#table_{$this->getPostId()}").hide();
                }
                else{
                    $("#table_{$this->getPostId()}").show();
                }
                $("input.numeric").forceNumeric({min: 0, max: 9999999999999999});
                $("input.calendar").each(function(i, el){
                    $(el).datepicker({
                        dateFormat: $(el).attr('data-dateFormat'),
                        onChangeMonthYear: function (year, month, inst) {
                            var curDate = $(this).datepicker('getDate');
                            if (curDate == null)
                                return;
                            if (curDate.getYear() != year || curDate.getMonth() != month - 1) {
                                curDate.setYear(year);
                                curDate.setMonth(month - 1);
                                while(curDate.getMonth() != month -1){
                                    curDate.setDate(curDate.getDate() - 1);
                                }
                                $(this).datepicker('setDate', curDate);
                                $(this).trigger('change');
                            }
                        }
                    });
                });
                $("#table_{$this->getPostId()} tbody").sortable({
                    axis: "y",
                    items: "tr:not(.header_row)"
                });
                $("#table_{$this->getPostId()} tr").css('background', '#F9F9F9');
                $("#table_{$this->getPostId()} tbody tr td").css('cursor', 'move');
            }
            $(document).ready(function(){
                $("#table_{$this->getPostId()} select:not(.raw)").combobox();
            });
        </script>
        <input type='hidden' name='{$this->getPostId()}[-1]' value='' />
EOF;
        $item .= "<div class='table_{$this->getPostId()} warning' style='display:none;'></div>";
        $item .= "<table id='table_{$this->getPostId()}' class='$class'>
                    ";
        if(!$isVertical){
            if(count($labels) > 0 && $labels[0] != ""){
                $item .= "<tr class='header_row'>";
                foreach($labels as $j => $label){
                    if(strtolower(@$types[$j]) == "random"){
                        continue;
                    }
                    $item .= @"<th style='width:{$sizes[$j]}px;'>{$label}</th>";
                }
                if($multiple){
                    $item .= "<th style='width:30px;'></th></tr>";
                }
            }
        }
        if(!$multiple){
            foreach($indices as $j => $index){
                if(!isset($values[0][$index])){
                    $values[0][$index] = "";
                }
            } 
        }
        //$item .= "</thead>";
        $i = 0;
        foreach($values as $i => $value){
            if($i > -1){
                $item .= "<tr id='obj$i' class='obj'>";
                foreach($indices as $j => $index){
                    $align = "";
                    if($isVertical){
                        $item .= "<tr id='obj$i' class='$i'><td align='right'><b>{$labels[$j]}:</b></td>";
                        $align = "left";
                    }
                    if(@$types[$j] == "NI"){
                        $names = array("");
                        $people = Person::getAllPeople(NI);
                        foreach($people as $person){
                            $names[$person->getNameForForms()] = $person->getNameForForms();
                        }
                        asort($names);
                        $combobox = new ComboBox("{$this->getPostId()}[$i][$index]", "Project Leader", $value[$index], $names);
                        $item .= "<td align='$align'>".$combobox->render()."</td>";
                    }
                    else if(strtolower(@$types[$j]) == "random"){
                        $item .= @"<td align='$align' style='display:none;'><input type='text' class='numeric' name='{$this->getPostId()}[$i][$index]' style='width:{$sizes[$j]}px;' value='{$value[$index]}' /></td>";
                    }
                    else if(strtolower(@$types[$j]) == "integer"){
                        $item .= @"<td align='$align'><input type='text' class='numeric' name='{$this->getPostId()}[$i][$index]' style='width:{$sizes[$j]}px;' value='{$value[$index]}' /></td>";
                    }
                    else if(strtolower(@$types[$j]) == "checkbox"){
                        $checked = (isset($value[$index]) && $value[$index] == "1") ? "checked" : "";
                        $item .= @"<td align='center'><input type='checkbox' name='{$this->getPostId()}[$i][$index]' style='width:{$sizes[$j]}px;' value='1' $checked /></td>";
                    }
                    else if(strtolower(@$types[$j]) == "textarea"){
                        $item .= @"<td align='$align'><textarea name='{$this->getPostId()}[$i][$index]' style='width:{$sizes[$j]}px;min-height:65px;height:100%;'>{$value[$index]}</textarea></td>";
                    }
                    else if(strstr(strtolower(@$types[$j]), "checkbox") !== false){
                        if(!$isVertical){
                            $align = "left";
                        }
                        $item .= @"<td align='$align'>";
                        $matches = array();
                        preg_match("/^(Checkbox)\((.*)\)$/i", $types[$j], $matches);
                        $matches = @explode(",", $matches[2]);
                        foreach($matches as $match){
                            $match = trim($match);
                            if((is_array(@$value[$index]) && in_array($match, @$value[$index])) || ($match == @$value[$index])){
                                $item .= "<div><input type='checkbox' name='{$this->getPostId()}[$i][$index][]' value='{$match}' checked> {$match}</div>";
                            }
                            else{
                                $item .= "<div><input type='checkbox' name='{$this->getPostId()}[$i][$index][]' value='{$match}'> {$match}</div>";
                            }
                        }
                        $item .= "</td>";
                    }
                    else if(strstr(strtolower(@$types[$j]), "radio") !== false){
                        if(!$isVertical){
                            $align = "left";
                        }
                        $item .= @"<td align='$align'>";
                        $matches = array();
                        preg_match("/^(Radio)\((.*)\)$/i", $types[$j], $matches);
                        $matches = @explode(",", $matches[2]);
                        foreach($matches as $match){
                            $match = trim($match);
                            if($match == @$value[$index]){
                                $item .= "<div><input type='radio' name='{$this->getPostId()}[$i][$index]' value='{$match}' checked> {$match}</div>";
                            }
                            else{
                                $item .= "<div><input type='radio' name='{$this->getPostId()}[$i][$index]' value='{$match}'> {$match}</div>";
                            }
                        }
                        $item .= "</td>";
                    }
                    else if(strstr(strtolower(@$types[$j]), "select") !== false || 
                            strstr(strtolower(@$types[$j]), "combobox") !== false){
                        if(!$isVertical){
                            $align = "center";
                        }
                        $cls = (strstr(strtolower(@$types[$j]), "select") !== false) ? "raw" : "";
                        $item .= @"<td align='$align'><select style='max-width:{$sizes[$j]}px' class='{$cls}' name='{$this->getPostId()}[$i][$index]'>";
                        $matches = array();
                        preg_match("/^(Select|ComboBox)\((.*)\)$/i", $types[$j], $matches);
                        $matches = @explode(",", $matches[2]);
                        if(array_search(@$value[$index], $matches) === false && @$value[$index] != ""){
                            $item .= @"<option selected>{$value[$index]}</option>";
                        }
                        foreach($matches as $match){
                            $match = trim($match);
                            if($match == @$value[$index]){
                                $item .= "<option selected>{$match}</option>";
                            }
                            else{
                                $item .= "<option>{$match}</option>";
                            }
                        }
                        $item .= "</select></td>";
                    }
                    else if(strstr(strtolower(@$types[$j]), "date") !== false){
                        $val = @str_replace("'", "&#39;", $value[$index]);
                        preg_match("/^(Date)\((.*)\)$/i", $types[$j], $matches);
                        $dateFormat = (isset($matches[2])) ? $matches[2] : "yy-mm-dd";
                        $item .= @"<td align='$align'><input type='text' class='calendar' data-dateFormat='{$dateFormat}' name='{$this->getPostId()}[$i][$index]' style='width:{$sizes[$j]}px;' value='{$val}' /></td>";
                    }
                    else if(strstr(strtolower(@$types[$j]), "getarray") !== false){
                        $fn = "{".$types[$j]."}";
                        $val = unserialize($this->varSubstitute($fn));
                        $val = @$val[$i][$this->id];
                        $item .= @"<td align='$align' valign='top'>{$val}</td>";
                    }
                    else{
                        $val = @str_replace("'", "&#39;", $value[$index]);
                        $item .= @"<td align='$align'><input type='text' name='{$this->getPostId()}[$i][$index]' value='{$val}' style='width:{$sizes[$j]}px;' /></td>";
                    }
                    if($isVertical){
                        $item .= "</tr>";
                    }
                }
                $colspan = 1;
                if($isVertical){
                    $item .= "<tr id='obj$i'>";
                    $colspan = 2;
                }
                if($multiple){
                    $item .= "<td colspan='$colspan' align='center'>
                                <span class='delete-icon' onClick='removeObj{$this->getPostId()}(this);'></span><br />
                                <span class='move-icon' style='margin-top:10px;'></span>
                              </td>";
                }
                $item .= "</tr>";
                if($isVertical){
                    $item .= "<tr id='obj$i'><td colspan='$colspan' style='background:#CCCCCC;'></td></tr>";
                }
            }
        }
        /*if(!$multiple && count($values) == 0){
            $item .= "<tr class='obj'>";
            foreach($indices as $j => $index){
                $item .= "<td><input type='text' name='{$this->getPostId()}[0][$index]' style='width:{$sizes[$j]}px;' value='' /></td>";
            }
            $item .= "</tr>";
        }*/
        if($multiple){
            $addText = $this->getAttr('addText', '+');
            $item .= "<tfoot><tr><td colspan='".(count($indices)+1)."'>";
            $item .= "<button id='add_{$this->getPostId()}' onClick='addObj{$this->getPostId()}(max{$this->getPostId()});' type='button'>{$addText}</button>";
            $item .= "</td></tr></tfoot>";
        }
        $item .= "</table>";
        $item .= "<script type='text/javascript'>
                      updateTable{$this->getPostId()}();
                  </script>";
        $item = $this->processCData($item);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut, $config;
        $multiple = (strtolower($this->getAttr('multiple', 'false')) == 'true');
        $maxEntries = $this->getAttr('max', 100);
        $types = explode("|", $this->getAttr('types', ''));
        $labels = explode("|", $this->getAttr('labels', ''));
        $types = explode("|", $this->getAttr('types', ''));
        $sizes = $this->getAttr('sizes', '');
        if($sizes != ""){
            $sizes = explode("|", $sizes);
        }
        else{
            $sizes = array();
        }
        $showHeader = $this->getAttr('showHeader', 'true');
        $showCount = $this->getAttr('showCount', 'false');
        $showBullets = $this->getAttr('showBullets', 'false');
        $orientation = $this->getAttr('orientation', 'horizontal');
        $isVertical = (strtolower($orientation) == 'vertical');
        $isList = (strtolower($orientation) == 'list');
        $isPlain = (strtolower($orientation) == 'plain');
        $pagebreak = (strtolower($this->getAttr('pagebreak', 'false')) == 'true'); 
        $class = $this->getAttr('class', ''); // Don't assume wikitable by default for pdfs
        $rules = "";
        $frame = "";
        if(strstr($class, 'wikitable') !== false){
            $rules = "all";
            $frame = "box";
        }
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
        
        if($max > -1 && $isPlain){
            $innerValues = array();
            $delimiter = $this->getAttr("delimiter", ", ");
            $showLabels = (strtolower($this->getAttr("showLabels", "false")) == "true");
            foreach($values as $vals){
                $innerVals = $vals;
                $innerValues[] = nl2br(implode(", ", $innerVals));
            }
            $item .= implode("<br /><br />", $innerValues);
        }
        else if($max > -1 && $isList){
            $innerValues = array();
            $delimiter = $this->getAttr("delimiter", ", ");
            $showLabels = (strtolower($this->getAttr("showLabels", "false")) == "true");
            foreach($values as $vals){
                $innerVals = $vals;
                if($showLabels){
                    $innerVals = array();
                    $indices = $this->getIndices($labels);
                    foreach($indices as $j => $index){
                        $innerVals[] = "{$vals[$index]}{$labels[$j]}";
                    }
                }
                $innerValues[] = implode($delimiter, $innerVals);
            }
            if(count($labels) > 1){
                $item .= implode("<br />", $innerValues);
            }
            else{
                $item .= implode($delimiter, $innerValues);
            }
        }
        else if($max > -1 && !$isList){
            if(!$isVertical){
                if(count($labels) > 0 && $labels[0] != ""){
                    $item = "<table id='table_{$this->getPostId()}' class='$class' rules='$rules' frame='$frame' width='100%'>";
                    if(strtolower($showHeader) == 'true'){
                        $item .= " <tr>";
                        if(strtolower($showCount) == 'true' || strtolower($showBullets) == 'true'){
                            $item .= "<th style='width:1px;'>&nbsp;</th>";
                        }
                        foreach($labels as $j => $label){
                            if(strtolower(@$types[$j]) == "random"){
                                continue;
                            }
                            $item .= "<th align='center'>{$label}</th>";
                        }
                        $item .= "</tr>";
                    }
                }
                else{
                    $item = "<table>";
                }
            }
            $i = 0;
            $count = 0;
            foreach($values as $i => $value){
                if($i > -1 && $count < $maxEntries){
                    if($isVertical){
                        $item .= "<table id='table_{$this->getPostId()}' class='$class' rules='$rules' frame='$frame' width='100%'>";
                    }
                    $item .= "<tr class='obj'>";
                    if(strtolower($showCount) == 'true'){
                        $item .= "<td style='width:1px;' valign='top'><b>{$i}.</b></td>";
                    }
                    if(strtolower($showBullets) == 'true'){
                        $fontSize = ($config->getValue('pdfFontSize')*DPI_CONSTANT);
                        $item .= "<td style='width:1px;' valign='top'><b style='display: block;margin:".($fontSize/2)."px 0 0 0;'>•</b></td>";
                    }
                    foreach($indices as $j => $index){
                        if($isVertical){
                            $item .= "<tr><td align='right'><b>{$labels[$j]}:</b></td>";
                        }
                        $size = (isset($sizes[$j])) ? "width:{$sizes[$j]};" : "";
                        if(strstr(strtolower(@$types[$j]), "select") !== false || 
                           strstr(strtolower(@$types[$j]), "combobox") !== false || 
                           strstr(strtolower(@$types[$j]), "radio") !== false){
                           $item .= @"<td align='center' valign='top' style='padding:0 3px 0 3px; {$size}'>{$value[$index]}</td>";
                        }
                        else if(strstr(strtolower(@$types[$j]), "checkbox") !== false){
                            $item .= @"<td align='center' valign='top' style='padding:0 3px 0 3px; {$size}'>".implode(";", $value[$index])."</td>";
                        }
                        else if(strtolower(@$types[$j]) == "random"){
                            //$item .= "<td align='right' valign='top' style='display:none; {$size}'>{$value[$index]}</td>";
                        }
                        else if(strtolower(@$types[$j]) == "integer"){
                            $item .= @"<td align='right' valign='top' style='padding:0 3px 0 3px; {$size}'>{$value[$index]}</td>";
                        }
                        else if(strtolower(@$types[$j]) == "checkbox"){
                            $check = "";
                            if(isset($value[$index]) && $value[$index] == "1"){
                                $check = "&#10003;";
                            }
                            $item .= @"<td align='center' valign='top' style='padding:0 3px 0 3px; {$size}'>{$check}</td>";
                        }
                        else if(strtolower(@$types[$j]) == "textarea"){
                            $item .= @"<td valign='top' style='padding:0 3px 0 3px; {$size}'>".nl2br($value[$index])."</td>";
                        }
                        else{
                            $item .= @"<td valign='top' style='padding:0 3px 0 3px; {$size}'>{$value[$index]}</td>";
                        }
                        if($isVertical){
                            $item .= "</tr>";
                        }
                    }
                    if(!$isVertical){
                        $item .= "</tr>";
                    }
                    $count++;
                    if($isVertical){
                        $item .= "</table><br />";
                        if($pagebreak && $count < count($values)){
                            $item .= "<div class='pagebreak'></div>";
                        }
                    }
                }
            }
            if(!$isVertical){
                $item .= "</table><br />";
            }
        }
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
        return $item;
    }

}

?>
