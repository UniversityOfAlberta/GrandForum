<?php

require_once('../config/ForumConfig.php');

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
        $members = array();
        $rows = $this->fetchAll("SELECT user_id, project_id FROM `grand_allocations`");
        foreach($rows as $row){
            $allocations[$row['user_id']][$row['project_id']] = $row;
        }
        
        $rows = $this->fetchAll("SELECT user_id, project_id FROM `grand_project_members`");
        foreach($rows as $row){
            $members[$row['user_id']][$row['project_id']] = $row;
        }
        
        foreach($members as $userId => $member){
            if(count($this->fetchAll("SELECT * FROM `grand_roles` WHERE role = '".NI."' AND user_id = '$userId'")) == 0){
                continue;
            }
            foreach($member as $projId => $project){
                try {
                    if(isset($allocations[$userId][$projId])){
                        // Person is a CI
                        if(count($this->fetchAll("SELECT * FROM `grand_roles` WHERE role = '".CI."' AND user_id = '$userId'")) == 0){
                            $this->execute("INSERT INTO `grand_roles` (`user_id`, `role`, `start_date`) VALUES ('$userId', '".CI."', CURRENT_TIMESTAMP)");
                        }
                        $this->execute("INSERT INTO `grand_role_projects` (`role_id`, `project_id`) VALUES ((SELECT id FROM `grand_roles` WHERE role = '".CI."' AND user_id = '$userId'), '$projId')");
                    }
                    else if(count($this->fetchAll("SELECT * 
                                                   FROM `grand_roles` r, `grand_role_projects` rp 
                                                   WHERE (r.role != '".NI."' AND r.role != '".AR."' AND r.role != '".CI."')
                                                   AND r.user_id = '$userId'
                                                   AND rp.project_id = '$projId'
                                                   AND r.id = rp.role_id")) == 0){
                        // Person is an AR
                        if(count($this->fetchAll("SELECT * FROM `grand_roles` WHERE role = '".AR."' AND user_id = '$userId'")) == 0){
                            $this->execute("INSERT INTO `grand_roles` (`user_id`, `role`, `start_date`) VALUES ('$userId', '".AR."', CURRENT_TIMESTAMP)");
                        }
                        $this->execute("INSERT INTO `grand_role_projects` (`role_id`, `project_id`) VALUES ((SELECT id FROM `grand_roles` WHERE role = '".AR."' AND user_id = '$userId'), '$projId')");
                    }
                }
                catch(PDOException $e){
                    
                }
            }
        }
        $this->execute("DELETE FROM `grand_roles` WHERE role = '".NI."'");
        
        $rows = $this->fetchAll("SELECT MAX(nsId) as nsId FROM `mw_an_extranamespaces`");
        $nsId = $rows[0]['nsId'];
        if($nsId % 2 == 0){
            $nsId = $nsId + 2;
        }
        else{
            $nsId = $nsId + 1;
        }
        if(count($this->fetchAll("SELECT * FROM `mw_an_extranamespaces` WHERE nsName = '".CI."'")) == 0){
            $this->execute("INSERT INTO mw_an_extranamespaces (`nsId`, `nsName`, `nsUser`, `public`) VALUES ($nsId,'".CI."',NULL,1)");
            $nsId++;
            $this->execute("INSERT INTO mw_an_extranamespaces (`nsId`, `nsName`, `nsUser`, `public`) VALUES ($nsId,'".CI."_Talk',NULL,1)");
            $nsId++;
        }
        if(count($this->fetchAll("SELECT * FROM `mw_an_extranamespaces` WHERE nsName = '".AR."'")) == 0){
            $this->execute("INSERT INTO mw_an_extranamespaces (`nsId`, `nsName`, `nsUser`, `public`) VALUES ($nsId,'".AR."',NULL,1)");
            $nsId++;
            $this->execute("INSERT INTO mw_an_extranamespaces (`nsId`, `nsName`, `nsUser`, `public`) VALUES ($nsId,'".AR."_Talk',NULL,1)");
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
