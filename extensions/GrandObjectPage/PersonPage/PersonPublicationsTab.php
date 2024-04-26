<?php

class PersonPublicationsTab extends AbstractTab {

    var $person;
    var $visibility;
    var $category;
    var $startRange;
    var $endRange;

    function PersonPublicationsTab($person, $visibility, $category='all', $startRange="0000-00-00", $endRange=CYCLE_END){
        global $config;
        if($category == "all" || is_array($category)){
            parent::AbstractTab(ucwords(Inflect::pluralize($config->getValue("productsTerm")), " \t\r\n\f\v-/"));
        }
        else{
            parent::AbstractTab(ucwords(Inflect::pluralize($category), " \t\r\n\f\v-/"));
        }
        $this->person = $person;
        $this->visibility = $visibility;
        $this->category = $category;
        $this->startRange = $startRange;
        $this->endRange = $endRange;
        $this->tooltip = "Contains a table with a list of ".Inflect::pluralize($category)." between the specified start and end dates.";
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
        $this->html .= $this->showTable($this->person, $this->visibility);
        if($me->isAllowedToEdit($this->person)){
            $this->html .= "<br /><a id='manage{$this->id}' class='button' href='$wgServer$wgScriptPath/index.php/Special:ManageProducts#/".urlencode($this->category)."'>Manage ".Inflect::pluralize($this->category)."</a>";
        }
    }

    function showTable($person, $visibility, $type=null){
        global $config;
        if(is_array($this->category)){
            $products = array();
            foreach($this->category as $category){
                $products = array_merge($products, $person->getPapersAuthored($category, $this->startRange, $this->endRange, false, false));
            }
        }
        else{
            $products = $person->getPapersAuthored($this->category, $this->startRange, $this->endRange, false, false);
        }
        $string = "";
        if(count($products) > 0){
            $string = "<table id='{$this->id}Pubs".md5($type)."' rules='all' frame='box'>
                <thead>
                    <tr>
                        <th>{$config->getValue('productsTerm')}</th>";
            if(is_array($this->category) || $this->category == "all"){
                $string .= "<th>Category</th>";
            }
            if($type == null){
                $string .= "<th>Type</th>";
            }
            $string .= "<th>Date</th>
                    </tr>
                </thead>
                <tbody>";
            foreach($products as $paper){
                if($type != null && $paper->getType() != $type){
                    continue;
                }

                $string .= "<tr>";
                $string .= "<td>{$paper->getCitation(true, true, true, $person->getId(), true)}<span style='display:none'>{$paper->getDescription()}</span></td>";
                if(is_array($this->category) || $this->category == "all"){
                    $string .= "<td align='center'>{$paper->getCategory()}</td>";
                }
                if($type == null){
                    $string .= "<td align='center'>{$paper->getType()}</td>";
                }
                $string .= "<td style='white-space: nowrap;'>{$paper->getDate()}</td>";
                $string .= "</tr>";
            }
            $string .= "</tbody>
                </table>
                <script type='text/javascript'>
                    $('#{$this->id}Pubs".md5($type)."').dataTable({
                        'order': [[ 1, 'desc' ]],
                        'autoWidth': false,
                        'iDisplayLength': 50
                    });
                </script>";
        }
        return $string;
    }

}    
?>
