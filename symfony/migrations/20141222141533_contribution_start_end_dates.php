<?php

use Phinx\Migration\AbstractMigration;

class ContributionStartEndDates extends AbstractMigration
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
        $table = $this->table('grand_contributions');
        if(!$table->hasColumn('start_date')){
            $table->addColumn('start_date', 'timestamp', array('after' => 'description',
                                                               'default' => '0000-00-00 00:00:00'))
                  ->save();
        }
        if(!$table->hasColumn('end_date')){
            $table->addColumn('end_date', 'timestamp', array('after' => 'start_date',
                                                             'default' => '0000-00-00 00:00:00'))
                  ->save();
        }
        if($table->hasColumn('year')){
            $sql = "UPDATE `grand_contributions`
                    SET `start_date` = CONCAT(`year`, '-04-01 00:00:00'),
                        `end_date`   = CONCAT(`year`, '-04-01 00:00:00')";
            $this->execute($sql);
            $sql = "ALTER TABLE `grand_contributions`
                    DROP COLUMN `year`";
            $this->execute($sql);
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
