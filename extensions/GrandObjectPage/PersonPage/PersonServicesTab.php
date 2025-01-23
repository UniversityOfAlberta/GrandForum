<?php

class PersonServicesTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Service Roles");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->person->getId() || $me->isRoleAtLeast(STAFF) || $me->isRole(DEAN) || $me->isRole(VDEAN));
    }

    function generateBody(){
        if(!$this->userCanView()){
            return "";
        }
        $services = $this->person->getServiceRoles();
        if(count($services) > 0){
            $this->html .= "<table id='serviceRoles' class='wikitable'>
                                <thead>
                                    <tr>
                                        <th>Dept</th>
                                        <th>Role</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                    </tr>
                                </thead>
                                <tbody>";
            foreach($services as $service){
                $this->html .= "<tr>
                                    <td>{$service['dept']}</td>
                                    <td>{$service['role']}</td>
                                    <td>{$service['start']}</td>
                                    <td>{$service['end']}</td>
                                </tr>";
            }
            $this->html .= "    </tbody>
                            </table>";
            $this->html .= "<script type='text/javascript'>
                $('#serviceRoles').DataTable({
                    'order': [[ 2, 'desc' ], [3, 'desc']],
                    'autoWidth': false,
                    'iDisplayLength': 50
                });
            </script>";
        }
    }
    
}
?>
