<?php

use Phinx\Migration\AbstractMigration;

class Delagate extends AbstractMigration
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
        $table = $this->table('grand_delegate', array('id' => 'id'));
        if(!$table->exists()){
            $table->addColumn('delegate', 'integer')
                  ->addColumn('user_id', 'integer')
                  ->addIndex(array('delegate'))
                  ->addIndex(array('user_id'))
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
