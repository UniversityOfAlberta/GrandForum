<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class TimeUse extends AbstractMigration
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
        $table = $this->table('grand_graddb_timeuse', array("id"=>"id"));
        $table->addColumn('user_id', 'integer')
              ->addColumn('term', 'string')
              ->addColumn('md5', 'string', array('limit' => 32))
              ->addColumn('hours', 'string', array('limit' => 16))
              ->addColumn('start', 'datetime')
              ->addColumn('end', 'datetime')
              ->addColumn('gta', 'text')
              ->addColumn('gra', 'text')
              ->addColumn('graf', 'text')
              ->addColumn('vacation', 'text')
              ->addColumn('hqpAccepted', 'datetime')
              ->addColumn('supervisorsAccepted', 'text')
              ->addColumn('pdf', 'blob', array('limit' => MysqlAdapter::BLOB_LONG))
              ->addIndex('user_id')
              ->addIndex('md5')
              ->addIndex('term')
              ->create();
    }
}
