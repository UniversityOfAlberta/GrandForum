<?php

class PersonPublicationsTab extends AbstractTab {

    var $person;
    var $visibility;
    var $category;

    function PersonPublicationsTab($person, $visibility, $category='all'){
        global $config;
        if($category == "all" || is_array($category)){
            parent::AbstractTab(Inflect::pluralize($config->getValue("productsTerm")));
        }
        else{
            parent::AbstractTab(Inflect::pluralize($category));
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
        $contributions = $this->person->getContributions();
       $this->html .= $this->showTable($this->person, $this->visibility);
    }

    function showTable($person, $visibility){
        global $config;
        $me = Person::newFromWgUser();
        if(is_array($this->category)){
            $products = array();
            foreach($this->category as $category){
                $products = array_merge($products, $person->getPapers($category, false, 'both', true, "Public"));
            }
        }
        else{
            $products = $person->getPapers($this->category, false, 'both', true, "Public");
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
