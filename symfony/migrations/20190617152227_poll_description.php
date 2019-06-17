<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class PollDescription extends AbstractMigration
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
        $table = $this->table('grand_poll_collection');
        $table->addColumn('description', 'text', array('after' => 'collection_name', 'limit' => MysqlAdapter::TEXT_LONG))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
