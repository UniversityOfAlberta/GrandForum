<?php

use Phinx\Migration\AbstractMigration;

class CollaborationChanged extends AbstractMigration
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
        $table = $this->table("grand_collaborations");
        $table->addColumn('changed', 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'after' => 'access_id'))
              ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
