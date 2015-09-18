<?php

class PersonCitationsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonCitationsTab($person, $visibility){
        parent::AbstractTab("Citations");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        if(!$this->visibility['isMe']){
                return "";
        }
        $papers = $this->person->getPapers();
           $this->html .= "<table id='citation_table' frame='box' rules='all'>
                        <thead><tr><th style='white-space:nowrap;'>Publication</th>
                        <th style='white-space:nowrap;'>Scopus Citation Count</th>
                        <th style='white-space:nowrap;'>Google Scholar Citation Count</th>
                        <th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
        foreach($papers as $paper){
            $this->html .= "<tr><td><a href='{$paper->getUrl()}'>{$contribution->getTitle()}</a></td>
                                <td>0</td>
				<td>0</td>
				<td>0</td>
                                 </tr>";}
        $this->html .= "</table></tbody><script type='text/javascript'>
                        $('#citation_table').dataTable();
        </script>";
    }
}
?>


