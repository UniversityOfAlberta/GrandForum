<?php

class ProjectFESDescriptionTab extends AbstractEditableTab {

    static $cache = array();
    
    var $project;
    var $visibility;

    function ProjectFESDescriptionTab($project, $visibility){
        parent::AbstractTab("Description");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(HQP) && ($me->isMemberOf($this->project) || !$me->isSubRole("UofC"))){
            $project = $this->project;
            $this->showDescription();
        }
        return $this->html;
    }
    
    function handleEdit(){
        global $wgOut, $wgMessage;
        @$this->saveBlobValue('SHORT_TERM', $_POST['SHORT_TERM']);
        @$this->saveBlobValue('LONG_TERM', $_POST['LONG_TERM']);
        @$this->saveBlobValue('OUTCOMES', $_POST['OUTCOMES']);
        @$this->saveBlobValue('COMMENTS', $_POST['COMMENTS']);
        @$this->saveBlobValue('COMMENTS1', $_POST['COMMENTS1']);
        @$this->saveBlobValue('COMMENTS2', $_POST['COMMENTS2']);
        @$this->saveBlobValue('COMMENTS3', $_POST['COMMENTS3']);
        @$this->saveBlobValue('DECISION', $_POST['DECISION']);
    }
    
    function generatePDFBody(){
        $this->generateBody();
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function canEdit(){
        return (!$this->project->isFeatureFrozen(FREEZE_DESCRIPTION) && $this->project->userCanEdit());
    }
    
    function canGeneratePDF(){
        return true;
    }
    
    function showDescription(){
        global $wgServer, $wgScriptPath;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        if(!$edit){
            $this->html .= "<h2>Project Objectives / Anticipated Outcomes</h2>
                            <h3>1. Short & Long Term Objectives</h3>
                            <div class='tinymce'>{$this->getBlobValue('SHORT_TERM')}</div>
                            <h3>2. Anticipated outcomes</h3>
                            <div class='tinymce'>{$this->getBlobValue('OUTCOMES')}</div>
                            
                            <h2>Project Team Members / Roles / Intra- and Cross-Theme Integration</h2>
                            <h3>1. How the project members, as a team, address a systemic approach to energy production and delivery, and/or cross-theme benefits</h3>
                            <div class='tinymce'>{$this->getBlobValue('COMMENTS')}</div>
                            
                            <h2>Project External Partners Collaborators and Their Roles</h2>
                            <h3>1. How the project partners, as a team, address a systemic approach to energy production and delivery, and/or cross-theme benefits.</h3>
                            <div class='tinymce'>{$this->getBlobValue('COMMENTS1')}</div>
                            <h3>2. Level of confidence that the project cash resources will be realized</h3>
                            <div class='tinymce'>{$this->getBlobValue('COMMENTS2')}</div>
                            <h3>3. Potential / existing in-kind resources from partners that are needed for the project</h3>
                            <div class='tinymce'>{$this->getBlobValue('COMMENTS3')}</div>
                            <h2>Project Key Decision Point</h2>
                            <h3>1. Project Key Decision Point</h3>
                            <div class='tinymce'>{$this->getBlobValue('DECISION')}</div>";
        }
        else{
            $this->html .= "<h2>Project Objectives / Anticipated Outcomes</h2>
                            <h3>1. Short & Long Term Objectives</h3>
                            <textarea class='long_description' name='SHORT_TERM' style='height:200px;'>{$this->getBlobValue('SHORT_TERM')}</textarea>
                            <h3>2. Anticipated Outcomes</h3>
                            <textarea class='long_description' name='OUTCOMES' style='height:200px;'>{$this->getBlobValue('OUTCOMES')}</textarea>
                            
                            <h2>Project Team Members / Roles / Intra- and Cross-Theme Integration</h2>
                            <h3>1. How the project members, as a team, address a systemic approach to energy production and delivery, and/or cross-theme benefits</h3>
                            <textarea class='long_description' name='COMMENTS' style='height:200px;'>{$this->getBlobValue('COMMENTS')}</textarea>
                            
                            <h2>Project External Partners Collaborators and Their Roles</h2>
                            <h3>1. How the project partners, as a team, address a systemic approach to energy production and delivery, and/or cross-theme benefits.</h3>
                            <textarea class='long_description' name='COMMENTS1' style='height:200px;'>{$this->getBlobValue('COMMENTS1')}</textarea>
                            <h3>2. Level of confidence that the project cash resources will be realized <span class='clicktooltip' title='Describe your team&#39;s confidence level in securing all necessary funding to support the project until its completion from both federal and non-federal sources.'>&#9432;</span></h3>
                            <textarea class='long_description' name='COMMENTS2' style='height:200px;'>{$this->getBlobValue('COMMENTS2')}</textarea>
                            <h3>3. Potential / existing in-kind resources from partners that are needed for the project</h3>
                            <textarea class='long_description' name='COMMENTS3' style='height:200px;'>{$this->getBlobValue('COMMENTS3')}</textarea>
                            <h2>Project Key Decision Point <span class='clicktooltip' title='The \"Project Key Decision Point\" is a critical milestone in the project&#39;s lifecycle that determines whether the project should proceed or be discontinued, in accordance with the original plan.'>&#9432;</span></h2>
                            <h3>1. Project Key Decision Point</h3>
                            <textarea class='long_description' name='DECISION' style='height:200px;'>{$this->getBlobValue('DECISION')}</textarea>";
            $this->html .= "<script type='text/javascript'>
                $('textarea.long_description').tinymce({
                    theme: 'modern',
                    relative_urls : false,
                    convert_urls: false,
                    menubar: false,
                    plugins: 'link image charmap lists table paste wordcount',
                    toolbar: [
                        'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify'
                    ],
                    paste_postprocess: function(plugin, args) {
                        var p = $('p', args.node);
                        p.each(function(i, el){
                            $(el).css('line-height', 'inherit');
                        });
                    }
                });
            </script>";
        }
        
    }
    
    function getBlobValue($blobItem, $type=BLOB_TEXT, $section='APPLICATION'){
        global $wgServer, $wgScriptPath;
        
        $data = "";
        $personId = 0;
        $projectId = $this->project->getId();
        
        if(!isset(self::$cache[$personId][$blobItem][$type][$section])){
        
            $year = 0; // Don't have a year so that it remains the same each year
            
            $blb = new ReportBlob($type, $year, $personId, $projectId);
            $addr = ReportBlob::create_address('RP_PROJECT_APPLICATION', $section, $blobItem, 0);
            $result = $blb->load($addr, true);
            $tmpdata = $blb->getData();
            
            if($data == ""){
                $data = $tmpdata;
            }
            if($type == BLOB_RAW && $data != null){
                $data = json_decode($data);
                $mime = $data->type;
                $md5 = $blb->getMD5();
                $data = "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime={$mime}'>Download</a>";
            }
            self::$cache[$personId][$blobItem][$type][$section] = $data;
        }
        else {
            $data = self::$cache[$personId][$blobItem][$type][$section];
        }
        
        return $data;
    }
    
    function saveBlobValue($blobItem, $value, $type=BLOB_TEXT, $section='APPLICATION'){
        if($type == BLOB_RAW){
            $contents = base64_encode(file_get_contents($value['tmp_name']));
            $hash = md5($contents);
            $name = $value['name'];
            $size = $value['size'];
            $fileType = $value['type'];
            $data = array('name' => $name,
                          'type' => $fileType,
                          'size' => $size,
                          'hash' => $hash,
                          'file' => $contents);
            $value = json_encode($data);
        }

        $year = 0; // Don't have a year so that it remains the same each year
        $personId = 0;
        $projectId = $this->project->getId();
        
        $blb = new ReportBlob($type, $year, $personId, $projectId);
        $addr = ReportBlob::create_address('RP_PROJECT_APPLICATION', $section, $blobItem, 0);
        $blb->store($value, $addr);
        
    }

}    
    
?>
