<?php

class PersonPublicationsTypesTab extends PersonPublicationsTab {

    var $person;
    var $visibility;
    var $category;

    function PersonPublicationsTypesTab($person, $visibility, $category='all'){
        parent::PersonPublicationsTab($person, $visibility, $category);
    }

    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isLoggedIn()){
            return "";
        }
        $me = Person::newFromWgUser();
        if(!isset($_GET['startRange']) && !isset($_GET['endRange']) && $me->getId() == $this->person->getId()){
            $startRange = ($me->getProfileStartDate() != "0000-00-00") ? $me->getProfileStartDate() : CYCLE_START;
            $endRange   = ($me->getProfileEndDate()   != "0000-00-00") ? $me->getProfileEndDate()   : CYCLE_END;
        }
        else{
            $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
            $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
        }
        $this->html .= "<div id='{$this->id}'>
                        <table>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><input type='datepicker' name='startRange' value='{$startRange}' size='10' /></td>
                                <td><input type='datepicker' name='endRange' value='{$endRange}' size='10' /></td>
                                <td><input type='button' value='Update' /></td>
                            </tr>
                        </table>
                        <script type='text/javascript'>
                            $('div#{$this->id} input[type=datepicker]').datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                yearRange: '1900:".(date('Y')+3)."'
                            });
                            $('div#{$this->id} input[type=button]').click(function(){
                                var startRange = $('div#{$this->id} input[name=startRange]').val();
                                var endRange = $('div#{$this->id} input[name=endRange]').val();
                                document.location = '{$this->person->getUrl()}?tab={$this->id}&startRange=' + startRange + '&endRange=' + endRange;
                            });
                        </script>
                        </div>";
        $structures = Product::structure();
        $types = $structures['categories'][$this->category]['types'];
        foreach($types as $type => $data){
            $this->html .= "<h3>{$type}</h3>";
            $this->html .= $this->showTable($this->person, $this->visibility, $type);
        }
        if($this->visibility['isMe'] || $this->visibility['isSupervisor']){
            $this->html .= "<br /><input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:ManageOutputs\");' value='Manage Outputs' />";
        }
    }

}    
?>
