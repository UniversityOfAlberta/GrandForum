<?php

class PersonPublicationsTypesTab extends PersonPublicationsTab {

    function PersonPublicationsTypesTab($person, $visibility, $category='all', $startRange=SOT, $endRange=CYCLE_END){
        parent::PersonPublicationsTab($person, $visibility, $category, $startRange, $endRange);
    }

    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isLoggedIn()){
            return "";
        }
        $me = Person::newFromWgUser();
        $this->html .= "<div id='{$this->id}'>
                        <table>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><input type='datepicker' name='startRange' value='{$this->startRange}' size='10' /></td>
                                <td><input type='datepicker' name='endRange' value='{$this->endRange}' size='10' /></td>
                                <td><input type='button' value='Update' /></td>
                                <td id='manage{$this->id}cell'></td>
                            </tr>
                        </table>
                        <script type='text/javascript'>
                            $('div#{$this->id} input[type=datepicker]').datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                yearRange: '1900:".(date('Y')+3)."',
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
                            $('div#{$this->id} input[type=button]').click(function(){
                                var startRange = $('div#{$this->id} input[name=startRange]').val();
                                var endRange = $('div#{$this->id} input[name=endRange]').val();
                                document.location = '{$this->person->getUrl()}?tab={$this->id}&startRange=' + startRange + '&endRange=' + endRange;
                            });
                            $(document).ready(function(){
                                $('#manage{$this->id}').clone().appendTo('#manage{$this->id}cell');
                            });
                        </script>
                        </div>";
        $structures = Product::structure();
        $types = $structures['categories'][$this->category]['types'];
        foreach($types as $type => $data){
            $this->html .= "<h3>{$type}</h3>";
            $this->html .= $this->showTable($this->person, $this->visibility, $type);
        }
        if($me->isAllowedToEdit($this->person)){
            $this->html .= "<br /><a id='manage{$this->id}' href='$wgServer$wgScriptPath/index.php/Special:ManageProducts#/".urlencode($this->category)."' class='button'>Manage ".Inflect::pluralize($this->category)."</a>";
        }
    }

}    
?>
