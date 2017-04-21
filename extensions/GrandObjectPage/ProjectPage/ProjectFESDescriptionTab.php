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
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $project = $this->project;
        
        $this->showDescription();
        
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
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function canEdit(){
        return $this->project->userCanEdit();
    }
    
    function showDescription(){
        global $wgServer, $wgScriptPath;
        
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        if(!$edit){
            $this->html .= "<h2>Project Objectives / Anticipated Outcomes</h2>
                            <h3>List the short-term project objectives (realizable in a three year time frame)</h3>
                            {$this->getBlobValue('SHORT_TERM')}
                            <h3>List the longer-term project objectives (realizable in seven years)</h3>
                            {$this->getBlobValue('LONG_TERM')}
                            <h3>List anticipated outcomes</h3>
                            {$this->getBlobValue('OUTCOMES')}
                            
                            <h2>Project Team Members / Roles / Intra- and Cross-Theme Integration</h2>
                            <h3>Provide comments on how the project members, as a team, address a systemic approach to energy production and delivery, and/or cross-theme benefits</h3>
                            {$this->getBlobValue('COMMENTS')}
                            
                            <h2>Project External Partners Collaborators and Their Roles</h2>
                            <h3>Provide brief comments on how the project partners, as a team, address a systemic approach to energy production and delivery, and/or cross-theme benefits.</h3>
                            {$this->getBlobValue('COMMENTS1')}
                            <h3>Provide brief comments on the level of confidence that the project cash resources will be realized</h3>
                            {$this->getBlobValue('COMMENTS2')}
                            <h3>Provide brief comments on potential / existing in-kind resources from partners that are need for project</h3>
                            {$this->getBlobValue('COMMENTS3')}
                            <h2>Project Key Decision Point</h2>
                            {$this->getBlobValue('DECISION')}";
        }
        else{
            $this->html .= "<h2>Project Objectives / Anticipated Outcomes</h2>
                            <h3>List the short-term project objectives (realizable in a three year time frame)</h3>
                            <textarea class='long_description' name='SHORT_TERM' style='height:200px;'>{$this->getBlobValue('SHORT_TERM')}</textarea>
                            <h3>List the longer-term project objectives (realizable in seven years)</h3>
                            <textarea class='long_description' name='LONG_TERM' style='height:200px;'>{$this->getBlobValue('LONG_TERM')}</textarea>
                            <h3>List anticipated outcomes</h3>
                            <textarea class='long_description' name='OUTCOMES' style='height:200px;'>{$this->getBlobValue('OUTCOMES')}</textarea>
                            
                            <h2>Project Team Members / Roles / Intra- and Cross-Theme Integration</h2>
                            <h3>Provide comments on how the project members, as a team, address a systemic approach to energy production and delivery, and/or cross-theme benefits</h3>
                            <textarea class='long_description' name='COMMENTS' style='height:200px;'>{$this->getBlobValue('COMMENTS')}</textarea>
                            
                            <h2>Project External Partners Collaborators and Their Roles</h2>
                            <h3>Provide brief comments on how the project partners, as a team, address a systemic approach to energy production and delivery, and/or cross-theme benefits.</h3>
                            <textarea class='long_description' name='COMMENTS1' style='height:200px;'>{$this->getBlobValue('COMMENTS1')}</textarea>
                            <h3>Provide brief comments on the level of confidence that the project cash resources will be realized</h3>
                            <textarea class='long_description' name='COMMENTS2' style='height:200px;'>{$this->getBlobValue('COMMENTS2')}</textarea>
                            <h3>Provide brief comments on potential / existing in-kind resources from partners that are need for project</h3>
                            <textarea class='long_description' name='COMMENTS3' style='height:200px;'>{$this->getBlobValue('COMMENTS3')}</textarea>
                            <h2>Project Key Decision Point</h2>
                            <textarea class='long_description' name='DECISION' style='height:200px;'>{$this->getBlobValue('DECISION')}</textarea>";
            $this->html .= "<script type='text/javascript'>
                $('textarea.long_description').tinymce({
                    theme: 'modern',
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
        $personId = array_values($this->project->getLeaders(true));
        if(!isset($personId[0])){
            return "";
        }
        $personId = $personId[0];
        $projectId = 0;
        
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
        $personId = array_values($this->project->getLeaders(true));
        if(!isset($personId[0])){
            return;
        }
        $personId = $personId[0];
        $projectId = 0;
        
        $blb = new ReportBlob($type, $year, $personId, $projectId);
        $addr = ReportBlob::create_address('RP_PROJECT_APPLICATION', $section, $blobItem, 0);
        $blb->store($value, $addr);
        
    }

}    
    
?>
