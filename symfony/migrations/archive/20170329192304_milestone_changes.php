<?php

use Phinx\Migration\AbstractMigration;

class MilestoneChanges extends AbstractMigration
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
        $table = $this->table('grand_milestones');
        $table->addColumn('order', 'integer', array('after' => 'milestone_id'))
              ->addIndex(array('order'))
              ->save();
              
        $table = $this->table('grand_activities');
        $table->addColumn('order', 'integer', array('after' => 'project_id'))
              ->addIndex(array('order'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
