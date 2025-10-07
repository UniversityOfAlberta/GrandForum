<?php

use Phinx\Migration\AbstractMigration;

class Activities extends AbstractMigration
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
        $activities_table = $this->table('grand_activities', array('id' => 'id'));
        if(!$activities_table->exists()){
            $activities_table->addColumn('name', 'text')
                             ->create();
        }
        $milestones_table = $this->table('grand_milestones');
        $milestones_table->addColumn('activity_id', 'integer', array('after' => 'identifier'))
                         ->addColumn('quarters', 'text', array('after' => 'edited_by'))
                         ->addIndex(array('activity_id'))
                         ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
