<?php

class PersonPublicationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonPublicationsTab($person, $visibility){
        parent::AbstractTab("Publications");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $contributions = $this->person->getContributions();
	   $this->html .= $this->showTable($this->person, $this->visibility);
    }

    function showTable($person, $visibility){
        $me = Person::newFromWgUser();
        $products = $person->getPapers("all", false, 'both', true, "Public");
        $string = "";
        if(count($products) > 0){
            $string = "<table id='personPubs' rules='all' frame='box'>
                <thead>
                    <tr>
                        <th>Title</th><th>Keywords</th><th>Coauthors</th><th>Year</th><th>MetaData</th><th>Scopus Citations</th>
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
                $string .= "<td><a href='{$paper->getUrl()}'>{$paper->getTitle()}</a><span style='display:none'>{$paper->getDescription()}".implode(", ", $projects)."</span></td>";
                $string .= "<td></td>";
                $string .= "<td>".implode(", ", $names)."</td>";
                $string .= "<td style='white-space: nowrap;'>{$paper->getDate()}</td>";
		$string .= "<td></td>";
                $string .= "<td>{$paper->getCitationCount("Sciverse Scopus")}</td>";

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
