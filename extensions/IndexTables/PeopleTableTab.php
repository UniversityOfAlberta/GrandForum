<?php

    class PeopleTableTab extends AbstractTab {

	var $table;
	var $visibility;

	function __construct($table, $visibility){
	     parent::__construct($table);
	     $this->table = $table;
	     $this->visibility = $visibility;
	}

	function generateBody(){
                global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config, $wgRoleValues;
                $me = Person::newFromId($wgUser->getId());
                $data = Person::getAllPeople($this->table);
                $emailHeader = "";
                $idHeader = "";
                $contactHeader = "";
                $subRoleHeader = "";
                $projectsHeader = "";
                $committees = $config->getValue('committees');
        if($me->isRoleAtLeast(ADMIN)){
            $idHeader = "<th style='white-space: nowrap;'>User Id</th>";
        }
        $this->html .= "Below are all the current {$this->table} in {$config->getValue('networkName')}.  To search for someone in particular, use the search box below.  You can search by name, project or university.<br /><br />";
                $this->html .= "<table class='indexTable' style='display:none;' frame='box' rules='all'>
                            <thead>
                                <tr>
                                    <th style='white-space: nowrap;'>Name</th>
                                    <th style='white-space: nowrap;'>City</th>
                                    <th style='white-space: nowrap;'>Province</th>
                                    {$idHeader}</tr>
                                </thead>
                                <tbody>
";
                foreach($data as $person){

                        $this->html .= "
                            <tr>
                            <td align='left' style='white-space: nowrap;'>
                                <a href='{$person->getUrl()}'>{$person->getReversedName()}</a>
                            </td>
                            <td>
                                {$person->getCity()}
                            </td>
                            <td>
                                {$person->getProvince()}
                            </td>
                        ";
                        if($idHeader != ''){
                            $this->html .= "<td>{$person->getId()}</td>";
                        }
                        $this->html .= "</tr>";
                }
                $this->html .= "</tbody></table><script type='text/javascript'>$('.indexTable').dataTable({'iDisplayLength': 100, 'autoWidth':false});</script>";
	    return $this->html;
	}
    }

?>
