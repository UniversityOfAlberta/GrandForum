<?php

use Phinx\Migration\AbstractMigration;

class CcvEmploymentOutcome extends AbstractMigration
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
        $table = $this->table('grand_ccv_employment_outcome', array('id' => 'id'));
        if(!$table->exists()){
            $table->addColumn('supervisor_id', 'integer')
                  ->addColumn('hqp', 'string', array('limit' => 64))
                  ->addColumn('date', 'timestamp')
                  ->addColumn('present_position', 'string', array('limit' => 128) )
                  ->addColumn('institution', 'string', array('limit' => 64))
                  ->addColumn('status', 'string', array('limit' => 32))
                  ->addColumn('degree', 'string', array('limit' => 32))
                  ->addIndex('supervisor_id')
                  ->create();
        }
    }
}
