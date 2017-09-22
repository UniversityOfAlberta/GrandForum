<?php

class PersonPublicationsTab extends AbstractTab {

    var $person;
    var $visibility;
    var $category;

    function PersonPublicationsTab($person, $visibility, $category='all'){
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
    }

    function generateBody(){
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return "";
        }
        $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
        $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
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
        $this->html .= $this->showTable($this->person, $this->visibility);
    }

    function showTable($person, $visibility){
        global $config;
        $me = Person::newFromWgUser();
        $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
        $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
        if(is_array($this->category)){
            $products = array();
            foreach($this->category as $category){
                $products = array_merge($products, $person->getPapersAuthored($category, $startRange, $endRange, true, false));
            }
        }
        else{
            $products = $person->getPapersAuthored($this->category, $startRange, $endRange, true, false);
        }
        $string = "";
        if(count($products) > 0){
            $string = "<table id='{$this->name}Pubs' rules='all' frame='box'>
                <thead>
                    <tr>
                        <th>{$config->getValue('productsTerm')}</th>";
            if(is_array($this->category) || $this->category == "all"){
                $string .= "<th>Category</th>";
            }
            $string .= "<th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>";
            foreach($products as $paper){
                $projects = array();
                if($config->getValue('projectsEnabled')){
                    foreach($paper->getProjects() as $project){
                        $projects[] = "{$project->getName()}";
                    }
                }

                $string .= "<tr>";
                $string .= "<td>{$paper->getCitation()}<span style='display:none'>{$paper->getDescription()}".implode(", ", $projects)."</span></td>";
                if(is_array($this->category) || $this->category == "all"){
                    $string .= "<td align=center>{$paper->getCategory()}</td>";
                }
                $string .= "<td align=center>{$paper->getType()}</td>";
                $string .= "<td style='white-space: nowrap;'>{$paper->getDate()}</td>";
                $string .= "</tr>";
            }
            $string .= "</tbody>
                </table>
                <script type='text/javascript'>
                    $('#{$this->name}Pubs').dataTable({
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
