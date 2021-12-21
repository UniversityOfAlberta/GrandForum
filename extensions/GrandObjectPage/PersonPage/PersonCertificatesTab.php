<?php

class PersonCertificatesTab extends AbstractEditableTab {

    static $files = array('BIAS' => "Unconscious Bias",
                          'FUNDAMENTALS' => "Diversity and Inclusion Fundamentals");

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Training Certificates");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function canEdit(){
        return ($this->person->isMe());
    }
    
    function handleEdit(){
        foreach(self::$files as $key => $title){
            $this->saveBlobValue($key);
        }
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        if($this->person->isMe() || $me->isRoleAtLeast(STAFF)){
            $this->html .= "This page is to upload your certificates of completion.<br />
                            For instructions on how to complete the training courses, click on the <a href='https://forum.glyconet.ca/index.php/EDITraining'>EDI > Training</a> tab.";
            foreach(self::$files as $key => $title){
                $this->html .= "<h3>{$title}</h3>";
                
                if($this->getMD5($key) != null){
                    $this->html .= "<div>
                        <iframe class='certificate_frame' src='{$wgServer}{$wgScriptPath}/scripts/ViewerJS/#{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$this->getMD5($key)}&/' style='width:100%; max-width: 550px; height:450px;' frameborder='0' allowfullscreen='true'></iframe>
                    </div>";
                }
                else{
                    $this->html .= "Certificate not yet uploaded";
                }
            }
            $this->html .= "<br />
                            <script type='text/javascript'>
                                $(document).ready(function(){
                                    $('#person').bind('tabsselect', function(event, ui) {
                                        if(ui.panel.id == 'training-certificates'){
                                            $('.certificate_frame').each(function(i, el){ el.contentDocument.location.reload(true) });
                                        }
                                    });
                                });
                            </script>";
        }
    }
    
    function generateEditBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $this->html .= "This page is to upload your certificates of completion.";
        foreach(self::$files as $key => $title){
            $this->html .= "<h3>$title</h3>";
            if($this->getMD5($key) != null){
                $this->html .= "<b>Current Certificate:</b>
                    <div>
                        <iframe class='certificate_frame' src='{$wgServer}{$wgScriptPath}/scripts/ViewerJS/#{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$this->getMD5($key)}&/' style='width:100%; max-width: 550px; height:450px;' frameborder='0' allowfullscreen='true'></iframe>
                    </div>";
            }
            $this->html .= "<input type='file' name='file_$key' accept='application/pdf' />";
        }
        $this->html .= "<br />";
    }
    
    function getMD5($blob){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        
        $blb = new ReportBlob(BLOB_RAW, $year, $personId, 0);
        $addr = ReportBlob::create_address("UPLOADS", "UPLOADS", $blob, 0);
        $result = $blb->load($addr);
        return $blb->getMD5();
    }
    
    function saveBlobValue($blob){
        $year = 0;
        $personId = $this->person->getId();
        if(isset($_FILES["file_{$blob}"]) && $_FILES["file_{$blob}"]['name'] != ""){
            $name = $_FILES["file_{$blob}"]['name'];
            $size = $_FILES["file_{$blob}"]['size'];
            $magic = MediaWiki\MediaWikiServices::getInstance()->getMimeAnalyzer();
            $mime = $magic->guessMimeType($_FILES["file_{$blob}"]['tmp_name'], false);
            
            $contents = base64_encode(file_get_contents($_FILES["file_{$blob}"]['tmp_name']));
            $hash = md5($contents);
            $data = array('name' => $name,
                          'type' => $mime,
                          'size' => $size,
                          'hash' => $hash,
                          'file' => $contents);
            $json = json_encode($data);
            $value = $json;
            
            $blb = new ReportBlob(BLOB_RAW, $year, $personId, 0);
            $addr = ReportBlob::create_address("UPLOADS", "UPLOADS", $blob, 0);
            $blb->store($value, $addr);
        }
    }

}    
    
?>
