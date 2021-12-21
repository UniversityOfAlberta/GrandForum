<?php

class ProjectProductsTab extends AbstractTab {

    var $project;

    function __construct($project){
        parent::__construct("Products");
        $this->project = $project;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $products = $this->project->getPapers("all", "0000-00-00", EOT);
        $this->html = "";
        if(count($products) > 0){
            $this->html .= "<table id='projectProducts' rules='all' frame='box'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Authors</th>
                    </tr>
                </thead>
                <tbody>";
            foreach($products as $paper){
                $names = array();
                foreach($paper->getAuthors() as $author){
                    if($author->getId() != 0 && $author->getUrl() != ""){
                        $names[] = "<a href='{$author->getUrl()}'>{$author->getNameForProduct()}</a>";
                    }
                    else{
                        $names[] = $author->getNameForForms();
                    }
                }
                
                $this->html .= "<tr>";
                $this->html .= "<td><span class='productTitle' data-id='{$paper->getId()}' data-href='{$paper->getUrl()}'>{$paper->getTitle()}</span><span style='display:none'>{$paper->getDescription()} ".implode(", ", $paper->getUniversities())."</span></td>";
                $this->html .= "<td>{$paper->getCategory()}</td>";
                $this->html .= "<td style='white-space: nowrap;'>{$paper->getDate()}</td>";
                $this->html .= "<td>".implode(", ", $names)."</td>";
                
                $this->html .= "</tr>";
            }
            $this->html .= "</tbody>
                </table>
                <script type='text/javascript'>
                    var projectProducts = $('#projectProducts').dataTable({
                        order: [[ 2, 'desc' ]],
                        aLengthMenu: [
                            [25, 50, 100, 200, -1],
                            [25, 50, 100, 200, 'All']
                        ],
                        iDisplayLength: -1,
                        autoWidth: false,
                        drawCallback: renderProductLinks
                    });
                </script>";
        }
    }

}    
    
?>
