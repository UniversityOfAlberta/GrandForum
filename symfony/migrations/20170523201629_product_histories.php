<?php

use Phinx\Migration\AbstractMigration;

class ProductHistories extends AbstractMigration
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
        $table = $this->table('grand_product_histories', array("id"=>"id"));
        if(!$table->exists()){
            $table->addColumn('user_id','integer')
                  ->addColumn('year','integer')
                  ->addColumn('type','string', array('limit' => 32))
                  ->addColumn('value','string', array('limit' => 32))
                  ->addColumn('created', 'timestamp')
                  ->addColumn('updated', 'timestamp')
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
