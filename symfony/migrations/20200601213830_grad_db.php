<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class GradDb extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('grand_graddb_eligible', array("id"=>"id"));
        $table->addColumn('user_id', 'integer')
              ->addColumn('term', 'string')
              ->addColumn('eligible', 'boolean', array('null' => true))
              ->addIndex('user_id')
              ->create();
              
        $table = $this->table('grand_graddb', array("id"=>"id"));
        $table->addColumn('user_id', 'integer')
              ->addColumn('supervisor', 'integer')
              ->addColumn('term', 'string')
              ->addColumn('md5', 'string', array('limit' => 32))
              ->addColumn('account', 'string', array('limit' => 128))
              ->addColumn('type', 'string', array('limit' => 16))
              ->addColumn('hours', 'string', array('limit' => 16))
              ->addColumn('start', 'datetime')
              ->addColumn('end', 'datetime')
              ->addColumn('supervisorAccepted', 'datetime')
              ->addColumn('hqpAccepted', 'datetime')
              ->addColumn('pdf', 'blob', array('limit' => MysqlAdapter::BLOB_LONG))
              ->addIndex('user_id')
              ->addIndex('md5')
              ->addIndex('supervisor')
              ->addIndex('term')
              ->create();
    }
}
