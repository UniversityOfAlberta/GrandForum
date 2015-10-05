<?php

require_once('../config/Config.php');

use Phinx\Migration\AbstractMigration;

class ChangeNiRole extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $allocations = array();
        $rows = $this->fetchAll("SELECT user_id, project_id FROM `grand_allocations`");
        foreach($rows as $row){
            $allocations[$row['user_id']] = $row;
        }
        
        $rows = $this->fetchAll("SELECT id, user_id FROM `grand_roles` WHERE role = '".NI."'");
        foreach($rows as $row){
            $roleId = $row['id'];
            $userId = $row['user_id'];
            if(isset($allocations[$userId])){
                // Person is a CI
                $projId = $allocations[$userId]['project_id'];
                $this->execute("UPDATE `grand_roles` SET role = '".CI."' WHERE user_id = '$userId' AND role = '".NI."'");
                $this->execute("INSERT INTO `grand_role_projects` (`role_id`, `project_id`) VALUES ('$roleId', '$projId')");
            }
            else{
                // Person is an AR
                $this->execute("UPDATE `grand_roles` SET role = '".AR."' WHERE user_id = '$userId' AND role = '".NI."'");
            }
        }
        
        $rows = $this->fetchAll("SELECT MAX(nsId) as nsId FROM `mw_an_extranamespaces`");
        $nsId = $rows[0]['nsId'];
        if($nsId % 2 == 0){
            $nsId = $nsId + 2;
        }
        else{
            $nsId = $nsId + 1;
        }
        $this->execute("INSERT INTO mw_an_extranamespaces (`nsId`, `nsName`, `nsUser`, `public`) VALUES ($nsId,'".CI."',NULL,1)");
        $nsId++;
        $this->execute("INSERT INTO mw_an_extranamespaces (`nsId`, `nsName`, `nsUser`, `public`) VALUES ($nsId,'".CI."_Talk',NULL,1)");
        $nsId++;
        $this->execute("INSERT INTO mw_an_extranamespaces (`nsId`, `nsName`, `nsUser`, `public`) VALUES ($nsId,'".AR."',NULL,1)");
        $nsId++;
        $this->execute("INSERT INTO mw_an_extranamespaces (`nsId`, `nsName`, `nsUser`, `public`) VALUES ($nsId,'".AR."_Talk',NULL,1)");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
