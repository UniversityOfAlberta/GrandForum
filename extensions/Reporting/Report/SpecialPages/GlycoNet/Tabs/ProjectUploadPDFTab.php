<?php

class ProjectUploadPDFTab extends AbstractTab {

    var $project;
    var $rp;
    var $title;

    function ProjectUploadPDFTab($project, $title, $blob){
        parent::AbstractTab($title);
        $this->project = $project;
        $this->blob = $blob;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        if(isset($_POST['submit'])){
            $this->saveBlobValue();
        }
        $this->html .= "<p>
                            <b>Upload PDF:</b>
                            <input type='file' name='file_{$this->id}' accept='application/pdf' />
                            <input type='submit' name='submit' value='Upload' />
                        </p>";
        if($this->getMD5() != null){
            $this->html .= "<div>
                <iframe src='{$wgServer}{$wgScriptPath}/scripts/ViewerJS/#{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$this->getMD5()}&/' style='width:100%; height:600px;' frameborder='0' allowfullscreen='true'></iframe>
            </div>";
        }
    }
    
    function getMD5(){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = 0;
        $projectId = $this->project->getId();
        
        $blb = new ReportBlob(BLOB_RAW, $year, $personId, $projectId);
        $addr = ReportBlob::create_address("UPLOADS", "UPLOADS", $this->blob, 0);
        $result = $blb->load($addr);
        return $blb->getMD5();
    }
    
    function saveBlobValue(){
        if(isset($_FILES["file_{$this->id}"]) && $_FILES["file_{$this->id}"]['name'] != ""){
            $name = $_FILES["file_{$this->id}"]['name'];
            $size = $_FILES["file_{$this->id}"]['size'];
            $magic = MimeMagic::singleton();
            $mime = $magic->guessMimeType($_FILES["file_{$this->id}"]['tmp_name'], false);
            
            $contents = base64_encode(file_get_contents($_FILES["file_{$this->id}"]['tmp_name']));
            $hash = md5($contents);
            $data = array('name' => $name,
                          'type' => $mime,
                          'size' => $size,
                          'hash' => $hash,
                          'file' => $contents);
            $json = json_encode($data);
            $value = $json;
            
            $year = 0;
            $personId = 0;
            $projectId = $this->project->getId();
            
            $blb = new ReportBlob(BLOB_RAW, $year, $personId, $projectId);
            $addr = ReportBlob::create_address("UPLOADS", "UPLOADS", $this->blob, 0);
            $blb->store($value, $addr);
        }
    }

}    
    
?>
