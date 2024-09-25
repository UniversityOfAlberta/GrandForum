<?php

class Completion2022Tab extends AbstractTab {

    function Completion2022Tab(){
        parent::AbstractTab("2022");
    }

    function generateBody(){
        $people = Person::getAllPeople();
        $this->html  = "<table id='peopleTable2022' class='wikitable' frame='box' rules='all' style='width:100%'>";
        $this->html .= "<thead>
                            <tr>
                                <th>Name</th>
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
                                <th>Q12</th>
                                <th>Q13</th>
                                <th>Q14</th>
                                <th>Q15</th>
                                <th>Q16</th>
                                <th>Q17</th>
                                <th>Q18</th>
                                <th>Q19</th>
                                <th>Q20</th>
                            </tr>
                         </thead>
                         <tbody>";
        foreach($people as $person){
            if(DiversitySurvey::isEligible($person)){
                $diversity = Diversity2022::newFromUserId($person->getId());
                if($diversity->canView() && $diversity->getId() != ""){
                    $decline = ($diversity->decline == 0) ? "No" : "Yes";
                    
                    $race = implode(", ", $diversity->getRaces());
                    $gender = implode(", ", $diversity->getGenders());
                    $orientation = implode(", ", $diversity->getOrientations());
                    $indigenousApply = implode(", ", $diversity->getIndigenousApply());
                    $disabilityVisibility = implode(", ", $diversity->getDisabilityVisibility());
                    $respected = implode(", ", $diversity->getRespected());
                    $leastRespected = implode(", ", $diversity->getLeastRespected());
                    $improve = implode(", ", $diversity->getImprove());
                    $preventsTraining = implode(", ", $diversity->getPreventsTraining());
                    $trainingTaken = implode(", ", $diversity->getTrainingTaken());

                    $this->html .= "<tr>
                                        <td>{$person->getNameForForms()}</td>
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
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$diversity->disability}<br />
                                                {$disabilityVisibility}
                                            </div>
                                        </td>
                                        <td>{$race}</td>
                                        <td>{$diversity->trueSelf}</td>
                                        <td>{$diversity->valued}</td>
                                        <td>{$diversity->space}</td>
                                        <td>{$respected}</td>
                                        <td>{$leastRespected}</td>
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$diversity->principles}<br />
                                                {$diversity->principlesDescribe}
                                            </div>
                                        </td>
                                        <td>{$diversity->statement}</td>
                                        <td>{$improve}</td>
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$diversity->training}<br />
                                                {$preventsTraining}
                                            </div>
                                        </td>
                                        <td>{$trainingTaken}</td>
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$diversity->implemented}
                                            </div>
                                        </td>
                                        <td>
                                            <div style='min-width:200px;max-height: 200px; overflow-y: auto;'>
                                                {$diversity->stem}
                                            </div>
                                        </td>
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
            $('#peopleTable2022').dataTable({
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
