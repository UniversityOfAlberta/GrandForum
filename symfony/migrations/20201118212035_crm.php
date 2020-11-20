<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Crm extends AbstractMigration
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
        $table = $this->table('grand_crm_contact', array('id' => 'id'));
        $table->addColumn('title', 'string', array('limit' => 256)
              ->addColumn('owner', 'integer')
              ->addColumn('details', 'text')
              ->addIndex('title')
              ->addIndex('owner')
              ->create();
              
        $table = $this->table('grand_crm_opportunity', array('id' => 'id'));
        $table->addColumn('contact', 'integer')
              ->addColumn('category', 'string', array('limit' => 64))
              ->addIndex('contact')
              ->create();
              
        $table = $this->table('grand_crm_task', array('id' => 'id'));
        $table->addColumn('opportunity', 'integer')
              ->addColumn('description', 'text')
              ->addColumn('due_date', 'datetime')
              ->addColumn('transactions', 'text')
              ->addColumn('status', 'string', array('limit' => 64))
              ->addIndex('opportunity')
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
