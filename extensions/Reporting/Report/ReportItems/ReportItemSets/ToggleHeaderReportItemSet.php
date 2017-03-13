<?php

class ToggleHeaderReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $tuple = self::createTuple();
        $data[] = $tuple;
        return $data;
    }
    
    function render(){
        global $wgOut;
        $level = $this->getAttr('level', '3');
        $changeColor = $this->getAttr('changeColor', "false");
        $title = $this->getAttr('title', "");
        $disabled = $this->getAttr('disabled', 'false');
        $sop = $this->getAttr('sop', 'false');

        if(strtolower($changeColor) == "true"){
            $this->addChangeColorScript();
        }
        
        $onclick = "$(\"#{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}\").slideToggle(200);";
        $disabled_class = "";
        if($disabled == "true"){
            $onclick = "";
            $disabled_class = "disabled_bg";
        }
	if($sop == 'true'){
	            $wgOut->addHTML("<div class='toggleHeader {$disabled_class}' onClick='{$onclick}' id='{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}_headDiv'><h$level id='{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}_head' style='margin:0;padding:0;color:#969696 !important;font-weight:normal;'>{$title}</h$level><span style='position:absolute; right:10px; top:4px;font-size:10px;'><i>[Show/Hide]</i></span></div>
                        <div id='{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}' class='toggleDiv_{$this->projectId}' style='display:none;'>");

	}
	else{
        $wgOut->addHTML("<div class='toggleHeader {$disabled_class}' onClick='{$onclick}' id='{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}_headDiv'><h$level id='{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}_head' style='margin:0;padding:0;color:#000000;font-weight:normal;'>{$title}</h$level><span style='position:absolute; right:10px; top:4px;font-size:10px;'><i>[Show/Hide]</i></span></div>
                        <div id='{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}' class='toggleDiv_{$this->projectId}' style='display:none;'>");
        }
        foreach($this->items as $item){
            $item->render();
        }
        $wgOut->addHTML("</div>");
    }
    
    function addChangeColorScript(){
        global $wgOut;
        $wgOut->addHTML("<script type='text/javascript'>
            $(document).ready(function(){
                var textareas = Array();
                $.each($('#{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId} textarea'), function(index, val){
                    textareas.push(val);
                });
                $('#{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}_head').multiLimit({$this->getLimit()}, $('#{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}_head'), textareas);
            });
        </script>");
    }
    
    function renderForPDF(){
        global $wgOut;
        $level = $this->getAttr('level', '3');
        $title = $this->getAttr('title', "");
        $wgOut->addHTML("<h$level>{$title}</h$level>
                        <div id='{$this->id}_{$this->projectId}_{$this->milestoneId}_{$this->personId}'>");
        foreach($this->items as $item){
            $item->renderForPDF();
        }
        $wgOut->addHTML("</div>");
    }
}

?>
