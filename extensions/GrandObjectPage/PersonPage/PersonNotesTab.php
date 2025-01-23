<?php

class PersonNotesTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Notes");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        $me = Person::newFromWgUser();
        $this->person->getFecPersonalInfo();
        
        // Files
        $magic = MimeMagic::singleton();
        if(isset($_FILES)){
            foreach(@$_FILES as $key => $file){
                foreach($file['tmp_name'] as $i => $name){
                    if($file['tmp_name'][$i] != ""){
                        $name = $file['name'][$i];
                        $size = $file['size'][$i];
                        $contents = base64_encode(file_get_contents($file['tmp_name'][$i]));
                        $mime = $magic->guessMimeType($file['tmp_name'][$i], false);
                        $hash = md5($contents);
                        $data = array('name' => $name,
                                      'type' => $mime,
                                      'size' => $size,
                                      'hash' => $hash,
                                      'file' => $contents);
                        $blb = new ReportBlob(BLOB_RAW, 0, $this->person->getId(), 0);
                        $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'HR_NOTE', $i);
                        $blb->store(json_encode($data), $addr);
                    }
                }
            }
        }
        if(isset($_POST['hr_note_label'])){
            foreach($_POST['hr_note_label'] as $i => $label){
                $blb = new ReportBlob(BLOB_TEXT, 0, $this->person->getId(), 0);
                $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'HR_NOTE_LABEL', $i);
                $blb->store($label, $addr);
            }
        }
        if(isset($_POST['hr_note_del'])){
            foreach($_POST['hr_note_del'] as $i => $label){
                $blb = new ReportBlob(BLOB_RAW, 0, $this->person->getId(), 0);
                $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'HR_NOTE', $i);
                $blb->delete($addr);
            
                $blb = new ReportBlob(BLOB_TEXT, 0, $this->person->getId(), 0);
                $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'HR_NOTE_LABEL', $i);
                $blb->delete($addr);
            }
        }
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->person->getId() || $me->isRoleAtLeast(STAFF));
    }
    
    function getFileIds(){
        $data = DBFunctions::execSQL("SELECT `rp_subitem`
                                      FROM `grand_report_blobs`
                                      WHERE `rp_type` = 'FEC_HISTORY'
                                      AND `rp_section` = 'FEC_HISTORY'
                                      AND `rp_item` = 'HR_NOTE'
                                      AND `user_id` = '{$this->person->getId()}'
                                      ORDER BY `rp_subitem` ASC");
        $data = new Collection($data);
        return $data->pluck('rp_subitem');
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        if(!$this->userCanView()){
            return "";
        }
        
        $this->html .= "<table>";
        foreach($this->getFileIds() as $i){
            $blb = new ReportBlob(BLOB_RAW, 0, $this->person->getId(), 0);
            $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'HR_NOTE', $i);
            $result = $blb->load($addr, true);
            $md5 = $blb->getMD5();
            
            $blb = new ReportBlob(BLOB_TEXT, 0, $this->person->getId(), 0);
            $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'HR_NOTE_LABEL', $i);
            $result = $blb->load($addr, true);
            $label = $blb->getData();
            $label = ($label != "") ? $label : "File";
            
            $link = ($md5 != "") ? "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}'>Download</a>" : "";
            $this->html .= ($link != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>{$label}:</b></td><td>{$link}</td></tr>" : "";
        }
        $this->html .= "</table>";
    }
    
    function generateEditBody(){
        global $facultyMap, $facultyMapSimple;
        if(!$this->userCanView()){
            return "";
        }
        $me = Person::newFromWgUser();
        $this->html .= "<table id='hr_notes'><tr>
            <th>Label</th>
            <th>File</th>
            <th>Delete?</th>
        </tr>";
        $found = false;
        foreach($this->getFileIds() as $i){
            $blb = new ReportBlob(BLOB_RAW, 0, $this->person->getId(), 0);
            $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'HR_NOTE', $i);
            $result = $blb->load($addr, true);
            $md5 = $blb->getMD5();
            
            $blb = new ReportBlob(BLOB_TEXT, 0, $this->person->getId(), 0);
            $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'HR_NOTE_LABEL', $i);
            $result = $blb->load($addr, true);
            $label = $blb->getData();
            
            $facultySelect = new SelectBox("hr_note_label[$i]", "hr_note_label[$i]", $label, array("", "HR Note", "COI"));
            if($md5 != ""){
                $found = true;
                $this->html .= "<tr>
                                    <td align='right'>{$facultySelect->render()}</td>
                                    <td><input type='file' name='hr_note[$i]' accept='application/pdf' /></td>
                                    <td align='center'><input type='checkbox' name='hr_note_del[$i]' value='1' /></td>
                                </tr>";
            }
        }
        $this->html .= "</table>";
        
        $this->html .= "<input id='addAnotherFile' type='button' value='Add another file' /><br />";
        $this->html .= "<script type='text/javascript'>
            $('#addAnotherFile').click(function(){
                $('#hr_notes').append(\"<tr>\" +
                                      \"    <td align='right'><select name='hr_note_label[]'><option></option><option>HR Note</option><option>COI</option></select></td>\" +
                                      \"    <td><input type='file' name='hr_note[]' accept='application/pdf' /></td>\" +
                                      \"    <td></td>\" +
                                      \"</tr>\");
            });
            if(!".var_export($found, true)."){
                $('#addAnotherFile').click();
            }
        </script>";
    }
    
    function canEdit(){
        return $this->userCanView();
    }
    
}
?>
