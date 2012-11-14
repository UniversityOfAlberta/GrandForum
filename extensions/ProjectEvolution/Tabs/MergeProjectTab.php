<?php

class MergeProjectTab extends ProjectTab {

    function MergeProjectTab(){
        parent::ProjectTab("Merge");
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $yesSelected = "";
        $noSelected = " checked";
        if($this->proposed == "true"){
            $yesSelected = " checked";
            $noSelected = "";
        }
        $this->html = "<table>";
        $this->html .= "<tr><td class='tooltip label' title='The acronym/name for the project ie. MEOW'>Acronym<span style='color:red;'>*</span>:</td><td><input type='text' name='acronym' value='{$this->acronym}' /></td></tr>";
        $this->html .= "<tr><td class='tooltip label' title='The project&#39;s full name ie. Media Enabled Organizational Worldflow' class='tooltip'>Full Name<span style='color:red;'>*</span>:</td><td><input style='width:400px;' type='text' name='fullName' value='{$this->fullName}' /></td></tr>";
        $this->html .= "<tr><td class='tooltip label' title='Whether or not this project is proposed or not'>Proposed?<span style='color:red;'>*</span>:</td><td><input type='radio' name='proposed' value='true' $yesSelected />Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='proposed' value='false' $noSelected />No</td></tr>";
        $this->html .= "<tr><td class='tooltip label' title='The description of the project' class='tooltip'>Description:</td><td><textarea style='width:408px;height:100px;' name='description'>{$this->description}</textarea></td></tr>";
        $this->html .= "<tr><td colspan='2'><fieldset><legend>Themes</legend>
                            <table>
                                <tr><td class='label'>AnImage:</td><td><input type='text' name='theme1' size='3' value='{$this->theme1}' />%</td></tr>
                                <tr><td class='label'>GamSim:</td><td><input type='text' name='theme2' size='3' value='{$this->theme2}' />%</td></tr>
                                <tr><td class='label'>nMEDIA:</td><td><input type='text' name='theme3' size='3' value='{$this->theme3}' />%</td></tr>
                                <tr><td class='label'>SocLeg:</td><td><input type='text' name='theme4' size='3' value='{$this->theme4}' />%</td></tr>
                                <tr><td class='label'>TechMeth:</td><td><input type='text' name='theme5' size='3' value='{$this->theme5}' />%</td></tr>
                            </table></fieldset></td></tr>";
        $this->html .= "</table>";
        return $this->html;
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function handleEdit(){
        global $wgMessages;
        
    }
}    
    
?>
