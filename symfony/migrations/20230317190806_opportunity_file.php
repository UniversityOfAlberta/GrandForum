<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class OpportunityFile extends AbstractMigration
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
        $table = $this->table('grand_lims_files', array('id' => 'id'));
        $table->addColumn('opportunity_id', 'integer')
              ->addColumn('filename', 'string', array('limit' => 128))
              ->addColumn('type', 'string', array('limit' => '64'))
              ->addColumn('data', 'text', array('limit' => MysqlAdapter::TEXT_MEDIUM))
              ->addIndex('opportunity_id')
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
