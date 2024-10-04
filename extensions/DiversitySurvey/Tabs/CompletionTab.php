<?php

class CompletionTab extends AbstractTab {

    function CompletionTab(){
        parent::AbstractTab("2024");
    }

    function generateBody(){
        $people = Person::getAllPeople();
        $this->html  = "<table id='peopleTable2024' class='wikitable' frame='box' rules='all' style='width:100%'>";
        $this->html .= "<thead>
                            <tr>
                                <th>Decline?</th>
                                <th>Q1</th>
                                <th>Q2</th>
                                <th>Q3</th>
                                <th>Q4</th>
                                <th>Q5</th>
                                <th>Q6</th>
                                <th>Q7</th>
                                <th>Q8</th>
                                <th>Q9</th>
                                <th>Q10</th>
                                <th>Q11</th>
                            </tr>
                         </thead>
                         <tbody>";
        foreach($people as $person){
            if(DiversitySurvey::isEligible($person)){
                $diversity = Diversity::newFromUserId($person->getId());
                if($diversity->canView() && $diversity->getId() != ""){
                    $decline = ($diversity->decline == 0) ? "No" : "Yes";
                    
                    $race = implode(", ", $diversity->getRaces());
                    $population = implode(", ", $diversity->getPopulation());
                    $gender = implode(", ", $diversity->getGenders());
                    $orientation = implode(", ", $diversity->getOrientations());
                    $indigenousApply = implode(", ", $diversity->getIndigenousApply());
                    $disabilityVisibility = implode(", ", $diversity->getDisabilityVisibility());
                    $immigration = implode(", ", $diversity->getImmigration());
                    $language = implode(", ", $diversity->getLanguage());

                    $this->html .= "<tr>
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$decline}<br />
                                                {$diversity->reason}
                                            </div>
                                        </td>
                                        <td>{$diversity->affiliation}</td>
                                        <td>{$diversity->age}</td>
                                        <td>{$gender}</td>
                                        <td>{$orientation}</td>
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$diversity->indigenous}<br />
                                                {$indigenousApply}
                                            </div>
                                        </td>
                                        <td>{$race}</td>
                                        <td>{$population}</td>
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$diversity->disability}<br />
                                                {$disabilityVisibility}
                                            </div>
                                        </td>
                                        <td>{$immigration}</td>
                                        <td>{$language}</td>
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$diversity->comments}
                                            </div>
                                        </td>
                                     </tr>";
                }
            }
        }
        $this->html .= "</tbody>
                        </table>";
        $this->html .= "<script type='text/javascript'>
            $('#peopleTable2024').dataTable({
                'aLengthMenu': [[-1], ['All']],
                'iDisplayLength': -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
    }
}
?>
