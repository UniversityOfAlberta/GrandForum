<?php

use Phinx\Migration\AbstractMigration;

class ManagedPeople extends AbstractMigration
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
        $table = $this->table('grand_managed_people', array('id' => 'id'));
        if(!$table->exists()){
            $table->addColumn('user_id', 'integer')
                  ->addColumn('managed_id', 'integer')
                  ->addIndex(array('user_id'))
                  ->addIndex(array('managed_id'))
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
