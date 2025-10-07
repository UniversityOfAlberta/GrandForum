<?php

use Phinx\Migration\AbstractMigration;

class MilestoneEnum extends AbstractMigration
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
        $sql = "ALTER TABLE `grand_milestones` 
                CHANGE `status` `status` ENUM( 'New', 'Revised', 'Continuing', 'Current', 'Closed', 'Abandoned' ) 
                CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
        $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
