<?php

use Phinx\Migration\AbstractMigration;

class Allocations extends AbstractMigration
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
        $alloc_table = $this->table('grand_allocations', array('id' => 'id'));
        if(!$alloc_table->exists()){
            $alloc_table->addColumn('user_id', 'integer')
                        ->addColumn('project_id', 'integer')
                        ->addColumn('year', 'integer')
                        ->addColumn('amount', 'integer')
                        ->addIndex(array('user_id'))
                        ->addIndex(array('project_id'))
                        ->addIndex(array('year'))
                        ->create();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
