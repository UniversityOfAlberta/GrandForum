<?php

use Phinx\Migration\AbstractMigration;

class MilestoneModification extends AbstractMigration
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
        $table = $this->table("grand_milestones");
        $table->changeColumn('status', 'string', array('limit' => 16))
              ->addColumn('modification', 'string', array('limit' => 16, 'after' => 'status'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
