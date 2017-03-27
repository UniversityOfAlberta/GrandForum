<?php

class PersonPublicationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonPublicationsTab($person, $visibility){
        global $config;
        parent::AbstractTab(Inflect::pluralize($config->getValue("productsTerm")));
        $this->person = $person;
        $this->visibility = $visibility;
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
        $products = $person->getPapers("all", false, 'both', true, "Public");
        $string = "";
        if(count($products) > 0){
            $string = "<table id='personPubs' rules='all' frame='box'>
                <thead>
                    <tr>
                        <th>{$config->getValue('productsTerm')}</th><th>Category</th><th>Type</th><th>Year</th>
                    </tr>
                </thead>
                <tbody>";
            foreach($products as $paper){
                $projects = array();
                foreach($paper->getProjects() as $project){
                    $projects[] = "{$project->getName()}";
                }

                $names = array();
                foreach($paper->getAuthors() as $author){
                    if($author->getId() != 0 && $author->getUrl() != ""){
                        $names[] = "<a href='{$author->getUrl()}'>{$author->getNameForForms()}</a>";
                    }
                    else{
                        $names[] = $author->getNameForForms();
                    }
                }

                $string .= "<tr>";
                $string .= "<td>{$paper->getProperCitation()}<span style='display:none'>{$paper->getDescription()}".implode(", ", $projects)."</span></td>";
                $string  .= "<td align=center>{$paper->getCategory()}</td>";
                $string  .= "<td align=center>{$paper->getType()}</td>";
		$string .= "<td style='white-space: nowrap;'>{$paper->getDate()}</td>";

                $string .= "</tr>";
            }
            $string .= "</tbody>
                </table>
                <script type='text/javascript'>
                    $('#personPubs').dataTable({
                        'order': [[ 1, 'desc' ]],
                        'autoWidth': false
                    });
                </script>";
        }
        return $string;
    }

}    
?>
