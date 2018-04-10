<?php

use Phinx\Migration\AbstractMigration;

class CollaborationAccessId extends AbstractMigration
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
        $collabs = $this->table("grand_collaborations");
        $collabs->addColumn('access_id', 'integer', array('after'=>'knowledge_user'))
            ->addIndex('access_id')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
