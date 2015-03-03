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
            $indices[] = $index;
        }
        return $indices;
    }
    
    function render(){
        global $wgOut;
        $multiple = (strtolower($this->getAttr('multiple', 'false')) == 'true');
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
        $width = (isset($this->attributes['width'])) ? $this->attributes['width'] : "150px";
        $item = <<<EOF
        <script type='text/javascript'>
            var max{$this->getPostId()} = {$max}+1;
            function addObj{$this->getPostId()}(i){
                $("#table_{$this->getPostId()}").append(
                    "<tr class='obj'>" +
EOF;
                    foreach($indices as $index){
                        $item .= "\"<td><input type='text' name='{$this->getPostId()}[\" + i + \"][$index]' value='' /></td>\" +\n";
                    }
        $item .= <<<EOF
                        "<td><button type='button' onClick='removeObj{$this->getPostId()}(this);'>-</button></td>" +
                    "</tr>");
                max{$this->getPostId()}++;
                updateTable{$this->getPostId()}();
            }
            
            function removeObj{$this->getPostId()}(obj){
                $(obj).parent().parent().remove();
                updateTable{$this->getPostId()}();
            }
            
            function updateTable{$this->getPostId()}(){
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
        $item .= "<table id='table_{$this->getPostId()}'>
            <tr>";
        foreach($labels as $label){
            $item .= "<th>{$label}</th>";
        }
        $item .= "<tr></th></tr>";
        $i = 0;
        foreach($values as $i => $value){
            if($i > -1){
                $item .= "<tr class='obj'>";
                    foreach($indices as $index){
                        $item .= "<td><input type='text' name='{$this->getPostId()}[$i][$index]' value='{$value[$index]}' /></td>";
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
            $item .= "<button onClick='addObj{$this->getPostId()}(max{$this->getPostId()});' type='button'>+</button>";
            $item .= "<script type='text/javascript'>
                updateTable{$this->getPostId()}();
            </script>";
        }
        $item = $this->processCData($item);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->processCData($this->getBlobValue());
        $wgOut->addHTML($item);
    }
}

?>
