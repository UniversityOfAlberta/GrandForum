<?php

use Phinx\Migration\AbstractMigration;

class MilestonePeople extends AbstractMigration
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
        $table->addColumn('people', 'text', array('after' => 'comment'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
